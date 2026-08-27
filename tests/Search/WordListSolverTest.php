<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\WordListFilters;
use App\Search\WordListSolver;
use Tests\Support\Assert;

/**
 * Exerce App\Search\WordListSolver sur la vraie base storage/dictionary_fr.sqlite (lecture
 * seule) : correction croisee par force brute pour chaque contrainte et plusieurs
 * combinaisons, comportement de pagination, et le plafond de securite
 * (WordListSolver::ROW_EXAMINATION_CEILING) -- meme methodologie que RackSolverTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $solver = new WordListSolver($connection);
    $pdo = $connection->pdo();

    // --- Entree invalide ou hors perimetre : aucune liste, meme convention que
    // --- TermLookup::find() et RackSolver::solve(). ---
    Assert::null($solver->solve('inconnu/valeur'));
    Assert::null($solver->solve('position/3/r'), '"position" hors perimetre de cette phase');
    Assert::null($solver->solve(''), '/mots seul (aucune contrainte) refuse explicitement');

    // --- Longueur seule : EXACT, total = COUNT() direct sur idx_terms_length_normalized. ---
    $byLength = $solver->solve('7-lettres');
    Assert::notNull($byLength);
    Assert::true($byLength->exact);
    Assert::true(!$byLength->truncated);
    Assert::same(2, $byLength->queryCount);
    $expectedLengthCount = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE length = 7')->fetch()['c'];
    Assert::same($expectedLengthCount, $byLength->total);
    Assert::same(WordListSolver::PAGE_SIZE, count($byLength->items));
    for ($i = 1; $i < count($byLength->items); $i++) {
        Assert::true($byLength->items[$i - 1]['normalized'] <= $byLength->items[$i]['normalized'], 'ordre alphabetique attendu');
    }
    foreach ($byLength->items as $item) {
        Assert::same(7, $item['length']);
        Assert::true(in_array($item['status'], ['admitted', 'french_not_admitted'], true), 'jamais STATUS_UNKNOWN sur une ligne de `terms` (D-013)');
    }

    // --- Prefixe seul : EXACT, verifie par force brute (pas un echantillon). ---
    $byPrefix = $solver->solve('commencant/qi');
    Assert::notNull($byPrefix);
    Assert::true($byPrefix->exact);
    $bruteForcePrefix = [];
    foreach ($pdo->query("SELECT normalized FROM terms WHERE normalized LIKE 'QI%'") as $row) {
        if (str_starts_with($row['normalized'], 'QI')) {
            $bruteForcePrefix[] = $row['normalized'];
        }
    }
    sort($bruteForcePrefix);
    Assert::same(count($bruteForcePrefix), $byPrefix->total);

    // --- Longueur + prefixe combines : intersection exacte. ---
    $comboPage = $solver->solve('7-lettres/commencant/ch');
    Assert::notNull($comboPage);
    Assert::true($comboPage->exact);
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 7 AND normalized >= 'CH' AND normalized < 'CI'");
    $stmt->execute();
    $expectedCombo = (int) $stmt->fetch()['c'];
    Assert::same($expectedCombo, $comboPage->total);
    foreach ($comboPage->items as $item) {
        Assert::same(7, $item['length']);
        Assert::true(str_starts_with($item['normalized'], 'CH'));
    }

    // --- Terminant seul : verifie par force brute sur reversed. ---
    $bySuffix = $solver->solve('terminant/tion');
    Assert::notNull($bySuffix);
    foreach ($bySuffix->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'TION'));
    }
    $stmtSuffix = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%TION'");
    $stmtSuffix->execute();
    $expectedSuffixTotal = (int) $stmtSuffix->fetch()['c'];
    // La liste bornee peut etre tronquee si le panier ancre depasse le plafond -- pas le cas
    // ici (2 907 correspondances mesurees, tres en-dessous de ROW_EXAMINATION_CEILING).
    Assert::true(!$bySuffix->truncated, 'le panier de longueur "TION" ne doit pas depasser le plafond');
    Assert::same($expectedSuffixTotal, $bySuffix->total);

    // --- Regression index idx_terms_length_reversed (analyse SEO longue traine, 2026-08-08) :
    // --- "longueur + terminant" combine n'avait jamais ete mesure avant cette date -- sans
    // --- l'index compose (length, reversed), SQLite ancre sur la plage reversed GLOBALE
    // --- (toutes longueurs) et lit chaque ligne candidate en table pour verifier la longueur --
    // --- mesure a 1 779 ms sur "7-lettres/terminant/s" avant correctif (S = 338 308 mots toutes
    // --- longueurs), 100,7 ms apres. Verifie ici par force brute que le resultat reste correct,
    // --- et que le budget de requetes ne change pas (toujours 2, ancrage reversed non fusionne).
    $lengthSuffix = $solver->solve('7-lettres/terminant/s');
    Assert::notNull($lengthSuffix);
    Assert::same(2, $lengthSuffix->queryCount);
    foreach ($lengthSuffix->items as $item) {
        Assert::same(7, $item['length']);
        Assert::true(str_ends_with($item['normalized'], 'S'));
    }
    $expectedLengthSuffixTotal = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 7 AND normalized LIKE '%S'")->fetch()['c'];
    Assert::true($expectedLengthSuffixTotal > WordListSolver::ROW_EXAMINATION_CEILING, 'sanity check : "7-lettres/terminant/s" doit reellement depasser le plafond, obtenu ' . $expectedLengthSuffixTotal);
    Assert::true($lengthSuffix->truncated, 'panier reellement au-dessus du plafond -> truncated attendu');

    // --- Contenant : verifie par force brute (instr() cote SQL, str_contains() cote PHP). ---
    $contains = $solver->solve('contenant/che');
    Assert::notNull($contains);
    foreach ($contains->items as $item) {
        Assert::true(str_contains($item['normalized'], 'CHE'));
    }

    // --- Regression C1 (audit final, code-reviewer, bloquant) : "contenant" SEUL, sans aucun
    // --- ancrage (longueur/prefixe/suffixe), doit trouver TOUTES les correspondances de toute
    // --- la base, pas seulement celles situees dans les ROW_EXAMINATION_CEILING premiers mots
    // --- de l'ordre alphabetique. Avant la correction, "contenant/xyl" renvoyait total = 0
    // --- (aucune des 270 correspondances reelles ne figure parmi les 10 000 premiers mots
    // --- alphabetiques de la base) -- exactement le bug reproduit et corrige. XYL choisi pour
    // --- son total REEL mesure sous ROW_EXAMINATION_CEILING (270 < 10 000) : le total renvoye
    // --- doit donc etre EXACT, pas seulement "non tronque".
    $unanchoredContains = $solver->solve('contenant/xyl');
    Assert::notNull($unanchoredContains);
    $bruteForceXyl = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE instr(normalized, \'XYL\') > 0')->fetch()['c'];
    Assert::true($bruteForceXyl > 0, 'sanity check : XYL doit avoir des correspondances reelles dans la base');
    Assert::true(!$unanchoredContains->truncated, 'XYL (' . $bruteForceXyl . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceXyl, $unanchoredContains->total, 'C1 : "contenant" sans ancrage doit trouver TOUTES les correspondances, pas seulement celles des 10 000 premiers mots alphabetiques');
    foreach ($unanchoredContains->items as $item) {
        Assert::true(str_contains($item['normalized'], 'XYL'));
    }

    // --- Regression C1, variante "avec" (minCount = 1, chemin optimise instr()) : meme
    // --- verification par force brute, plusieurs lettres combinees, sans aucun ancrage. ---
    $unanchoredWith = $solver->solve('avec/x/y/z');
    Assert::notNull($unanchoredWith);
    $bruteForceXyz = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE instr(normalized, \'X\') > 0 AND instr(normalized, \'Y\') > 0 AND instr(normalized, \'Z\') > 0')->fetch()['c'];
    Assert::true(!$unanchoredWith->truncated, 'avec/x/y/z (' . $bruteForceXyz . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceXyz, $unanchoredWith->total, 'C1 : "avec" sans ancrage doit trouver TOUTES les correspondances');
    foreach ($unanchoredWith->items as $item) {
        Assert::true(str_contains($item['normalized'], 'X') && str_contains($item['normalized'], 'Y') && str_contains($item['normalized'], 'Z'));
    }

    // --- Avec, repetitions comptees : verifie par force brute (array_count_values). ---
    $withLetters = $solver->solve('avec/a/a/r');
    Assert::notNull($withLetters);
    foreach ($withLetters->items as $item) {
        $counts = array_count_values(str_split($item['normalized']));
        Assert::true(($counts['A'] ?? 0) >= 2, $item['normalized'] . ' doit contenir au moins 2 A');
        Assert::true(($counts['R'] ?? 0) >= 1, $item['normalized'] . ' doit contenir au moins 1 R');
    }
    Assert::true($withLetters->total > 0, 'sanity check : au moins un mot avec 2 A et 1 R doit exister');

    // --- Palier 2 de l'ouverture en entonnoir de "avec" (longueur explicite + EXACTEMENT deux
    // --- lettres "avec", chacune minCount=1) : verifie ici que anchorClause() ignore
    // --- TOUJOURS withLetters (seul extraPredicates() l'utilise, en predicat residuel) --
    // --- ancrage sur `length = ?` (idx_terms_length_normalized), jamais un ancrage "avec",
    // --- exactement comme le palier 1 (D-029) -- verifie dans le code avant ce test, pas
    // --- seulement suppose ici (voir reports/query-plans/avec-length-2-letters-full-sweep.md).
    $avecTwoLetters = $solver->solve('9-lettres/avec/q/x');
    Assert::notNull($avecTwoLetters);
    Assert::same(1, $avecTwoLetters->queryCount, 'ancrage normalized (length=?) : fusionne a 1 seule requete, comme le palier 1');
    $bruteForceQX9 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, 'Q') > 0 AND instr(normalized, 'X') > 0")->fetch()['c'];
    Assert::true(!$avecTwoLetters->truncated, '9-lettres/avec/q/x (' . $bruteForceQX9 . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceQX9, $avecTwoLetters->total, 'correction verifiee par force brute');
    foreach ($avecTwoLetters->items as $item) {
        Assert::same(9, $item['length']);
        Assert::true(str_contains($item['normalized'], 'Q') && str_contains($item['normalized'], 'X'));
    }

    // --- L'ordre des segments dans le chemin BRUT ne doit rien changer (ksort() sur
    // --- $withLetters, WordListFiltersTest.php le verifie deja au niveau du parsing -- rev
    // --- revérifie ici via le vrai solveur bout en bout) : "avec/x/q" doit produire exactement
    // --- le meme total et le meme canonicalPath que "avec/q/x". ---
    $avecTwoLettersReversedInput = $solver->solve('9-lettres/avec/x/q');
    Assert::notNull($avecTwoLettersReversedInput);
    Assert::same($avecTwoLetters->total, $avecTwoLettersReversedInput->total, 'ordre de saisie des deux lettres "avec" sans effet sur le total');
    Assert::same($avecTwoLetters->canonicalPath, $avecTwoLettersReversedInput->canonicalPath, 'meme canonicalPath quel que soit l\'ordre de saisie');
    Assert::same('9-lettres/avec/q/x', $avecTwoLetters->canonicalPath, 'ordre alphabetique impose par canonicalPath()');

    // --- Cas pathologique plausible (deux lettres tres frequentes a la fois, meme longueur
    // --- que le pire cas deja documente pour "position"/"avec" seul, D-023/D-029) : doit
    // --- rester ancre sur `length = ?` et ne jamais depasser le budget de requetes, meme
    // --- lorsque le panier ancre est tronque par ROW_EXAMINATION_CEILING. ---
    $avecTwoLettersFrequent = $solver->solve('11-lettres/avec/e/s');
    Assert::notNull($avecTwoLettersFrequent);
    Assert::same(1, $avecTwoLettersFrequent->queryCount, 'toujours 1 seule requete, meme avec deux lettres tres frequentes');
    foreach ($avecTwoLettersFrequent->items as $item) {
        Assert::same(11, $item['length']);
        Assert::true(str_contains($item['normalized'], 'E') && str_contains($item['normalized'], 'S'));
    }

    // --- Palier 3 de l'ouverture en entonnoir de "avec" (longueur explicite + EXACTEMENT trois
    // --- lettres "avec", chacune minCount=1) : anchorClause() ignore TOUJOURS withLetters, quel
    // --- que soit son nombre d'entrees (1, 2 ou 3) -- ancrage sur `length = ?`
    // --- (idx_terms_length_normalized), jamais un ancrage "avec", exactement comme les paliers 1
    // --- et 2 (D-029/D-030) -- verifie dans le code avant ce test, pas seulement suppose ici
    // --- (voir reports/query-plans/avec-length-3-letters-full-sweep.md).
    $avecThreeLetters = $solver->solve('9-lettres/avec/q/x/z');
    Assert::notNull($avecThreeLetters);
    Assert::same(1, $avecThreeLetters->queryCount, 'ancrage normalized (length=?) : fusionne a 1 seule requete, comme les paliers 1 et 2');
    $bruteForceQXZ9 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, 'Q') > 0 AND instr(normalized, 'X') > 0 AND instr(normalized, 'Z') > 0")->fetch()['c'];
    Assert::true(!$avecThreeLetters->truncated, '9-lettres/avec/q/x/z (' . $bruteForceQXZ9 . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceQXZ9, $avecThreeLetters->total, 'correction verifiee par force brute');
    foreach ($avecThreeLetters->items as $item) {
        Assert::same(9, $item['length']);
        Assert::true(str_contains($item['normalized'], 'Q') && str_contains($item['normalized'], 'X') && str_contains($item['normalized'], 'Z'));
    }

    // --- L'ordre des segments dans le chemin BRUT ne doit rien changer (ksort() sur
    // --- $withLetters, WordListFiltersTest.php le verifie deja au niveau du parsing -- revérifie
    // --- ici via le vrai solveur bout en bout) : "avec/z/q/x" doit produire exactement le meme
    // --- total et le meme canonicalPath que "avec/q/x/z". ---
    $avecThreeLettersReversedInput = $solver->solve('9-lettres/avec/z/q/x');
    Assert::notNull($avecThreeLettersReversedInput);
    Assert::same($avecThreeLetters->total, $avecThreeLettersReversedInput->total, 'ordre de saisie des trois lettres "avec" sans effet sur le total');
    Assert::same($avecThreeLetters->canonicalPath, $avecThreeLettersReversedInput->canonicalPath, 'meme canonicalPath quel que soit l\'ordre de saisie');
    Assert::same('9-lettres/avec/q/x/z', $avecThreeLetters->canonicalPath, 'ordre alphabetique impose par canonicalPath()');

    // --- Cas pathologique plausible (trois lettres tres frequentes a la fois) : doit rester
    // --- ancre sur `length = ?` et ne jamais depasser le budget de requetes, meme lorsque le
    // --- panier ancre est tronque par ROW_EXAMINATION_CEILING. ---
    $avecThreeLettersFrequent = $solver->solve('11-lettres/avec/e/s/t');
    Assert::notNull($avecThreeLettersFrequent);
    Assert::same(1, $avecThreeLettersFrequent->queryCount, 'toujours 1 seule requete, meme avec trois lettres tres frequentes');
    foreach ($avecThreeLettersFrequent->items as $item) {
        Assert::same(11, $item['length']);
        Assert::true(str_contains($item['normalized'], 'E') && str_contains($item['normalized'], 'S') && str_contains($item['normalized'], 'T'));
    }

    // --- Longueur trop courte pour trois lettres distinctes (2 lettres au total) : le solveur
    // --- doit repondre correctement (0 resultat), pas planter -- sanity check structurel du
    // --- perimetre du palier 3, jamais un scan complet (longueur reste l'ancrage). ---
    $avecThreeLettersTooShort = $solver->solve('2-lettres/avec/a/e/i');
    Assert::notNull($avecThreeLettersTooShort);
    Assert::same(0, $avecThreeLettersTooShort->total, 'un mot de 2 lettres ne peut jamais contenir 3 lettres distinctes');
    Assert::same(1, $avecThreeLettersTooShort->queryCount);

    // --- Sans : aucune occurrence de la lettre exclue. ---
    $without = $solver->solve('sans/z');
    Assert::notNull($without);
    foreach ($without->items as $item) {
        Assert::true(!str_contains($item['normalized'], 'Z'));
    }

    // --- Motif : cases connues respectees position par position. ---
    $motif = $solver->solve('5-lettres/motif/c--e-');
    Assert::notNull($motif);
    Assert::true($motif->total > 0);
    foreach ($motif->items as $item) {
        Assert::same(5, strlen($item['normalized']));
        Assert::same('C', $item['normalized'][0]);
        Assert::same('E', $item['normalized'][3]);
    }

    // --- Combinaison prefixe + terminant : suffixe applique en predicat supplementaire.
    // --- Le panier ANCRE seul (prefixe "CH", 12 037 lignes) depasse ROW_EXAMINATION_CEILING,
    // --- mais depuis la correction C1 le plafond porte sur le panier ANCRE ET FILTRE combine
    // --- (9 correspondances reelles, verifie par force brute) : exact/non tronque desormais,
    // --- alors que l'ancien code (bloquant C1) l'aurait marque a tort "truncated" en ne
    // --- regardant que l'ancrage seul. ---
    $prefixSuffix = $solver->solve('commencant/ch/terminant/tion');
    Assert::notNull($prefixSuffix);
    foreach ($prefixSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'CH'));
        Assert::true(str_ends_with($item['normalized'], 'TION'));
    }
    Assert::true(in_array('CHELATION', array_column($prefixSuffix->items, 'normalized'), true), 'CHELATION doit apparaitre (commence par CH, termine par TION)');
    $bruteForceChTion = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'CH' AND normalized < 'CI' AND normalized LIKE '%TION'")->fetch()['c'];
    Assert::true(!$prefixSuffix->truncated, 'C1 : le panier combine (CH + TION, ' . $bruteForceChTion . ' correspondances reelles) est sous le plafond, ne doit plus etre tronque a tort du seul fait que le panier ANCRE seul (CH, 12 037 lignes) le depasse');
    Assert::same($bruteForceChTion, $prefixSuffix->total, 'total exact attendu, panier combine sous le plafond');

    // --- Regression D-025bis (audit du lot D-025, bloquant) : prefixe ET suffixe D'UNE SEULE
    // --- LETTRE CHACUN doivent ancrer sur idx_terms_startletter_endletter_normalized (egalite
    // --- combinee), jamais sur une plage residuelle. Avant ce correctif (deux iterations,
    // --- voir schema.sql et reports/query-plans/prefix-suffix-anchor-fix.md) : "commencant/r/
    // --- terminant/h" (R = 224 205 mots, prefixe le plus frequent de la base) ancrait
    // --- systematiquement sur R et appliquait "termine par H" comme predicat residuel sur tout
    // --- ce panier -- mesure jusqu'a 1 211 ms (commencant/p/terminant/h) ; une premiere
    // --- iteration (choix par frequence) corrigeait ce cas mais laissait 53 des 611 pages du
    // --- lot au-dessus du budget des que les deux lettres sont frequentes (jusqu'a 6 675 ms,
    // --- commencant/z/terminant/s) -- corrige en profondeur par l'index combine, 1 seule
    // --- requete desormais quel que soit le couple de lettres.
    $frequentPrefixRareSuffix = $solver->solve('commencant/r/terminant/h');
    Assert::notNull($frequentPrefixRareSuffix);
    $bruteForceRH = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'R' AND normalized < 'S' AND normalized LIKE '%H'")->fetch()['c'];
    Assert::same($bruteForceRH, $frequentPrefixRareSuffix->total, 'correction verifiee par force brute');
    foreach ($frequentPrefixRareSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'R') && str_ends_with($item['normalized'], 'H'));
    }
    Assert::same(1, $frequentPrefixRareSuffix->queryCount, 'prefixe+suffixe d\'une seule lettre chacun : egalite combinee, 1 seule requete fusionnee');

    // --- Meme index, sens inverse (prefixe rare Q = 2 658 mots, suffixe frequent S = 338 308
    // --- mots) : doit rester a 1 requete aussi, symetrique par construction (l'index ne
    // --- "choisit" plus aucun cote, il sert les deux egalites a la fois). ---
    $rarePrefixFrequentSuffix = $solver->solve('commencant/q/terminant/s');
    Assert::notNull($rarePrefixFrequentSuffix);
    $bruteForceQS = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Q' AND normalized < 'R' AND normalized LIKE '%S'")->fetch()['c'];
    Assert::same($bruteForceQS, $rarePrefixFrequentSuffix->total, 'correction verifiee par force brute');
    foreach ($rarePrefixFrequentSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'Q') && str_ends_with($item['normalized'], 'S'));
    }
    Assert::same(1, $rarePrefixFrequentSuffix->queryCount, 'prefixe+suffixe d\'une seule lettre chacun : egalite combinee, 1 seule requete fusionnee');

    // --- Cas qui a revele le vrai defaut (balayage complet des 611 combinaisons du lot D-025,
    // --- pas seulement un exemple isole) : quand les DEUX lettres sont frequentes, AUCUN choix
    // --- d'ancrage (prefixe ou suffixe) n'evite un grand panier residuel -- seul l'index
    // --- combine resout ce cas. Z = 2 657 mots au debut (rare), S = 338 308 mots a la fin
    // --- (tres frequent) : mesure a 6 675 ms avec le choix par frequence seul (Z choisi comme
    // --- ancrage, mais sans borne superieure -- 'Z' est la derniere lettre de l'alphabet,
    // --- rangeBounds() ne peut pas produire de borne haute -- le plan SQLite basculait alors
    // --- sur idx_terms_reversed, parcourant les 338 308 mots en S). L'index combine ne depend
    // --- d'aucune borne de plage, seulement d'une egalite : sans effet sur ce cas degenere. ---
    $bothFrequent = $solver->solve('commencant/z/terminant/s');
    Assert::notNull($bothFrequent);
    $bruteForceZS = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Z' AND normalized LIKE '%S'")->fetch()['c'];
    Assert::same($bruteForceZS, $bothFrequent->total, 'correction verifiee par force brute (cas degenere Z, sans borne superieure)');
    foreach ($bothFrequent->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'Z') && str_ends_with($item['normalized'], 'S'));
    }
    Assert::same(1, $bothFrequent->queryCount, 'meme les deux lettres frequentes a la fois restent a 1 seule requete avec l\'index combine');

    // --- Plafond de securite, toujours actif sur le panier COMBINE quand il depasse
    // --- reellement ROW_EXAMINATION_CEILING (pas seulement l'ancrage) : "CH" + "sans Z"
    // --- laisse 10 993 correspondances reelles (mesure), au-dessus du plafond. ---
    $anchoredTruncated = $solver->solve('commencant/ch/sans/z');
    Assert::notNull($anchoredTruncated);
    $bruteForceChSansZ = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'CH' AND normalized < 'CI' AND instr(normalized, 'Z') = 0")->fetch()['c'];
    Assert::true($bruteForceChSansZ > WordListSolver::ROW_EXAMINATION_CEILING, 'sanity check : le panier combine CH + sans Z doit reellement depasser le plafond pour que ce test ait un sens, obtenu ' . $bruteForceChSansZ);
    Assert::true($anchoredTruncated->truncated, 'panier combine reellement au-dessus du plafond -> truncated attendu');
    Assert::true(!$anchoredTruncated->exact, 'total non garanti exhaustif quand truncated = true');
    foreach ($anchoredTruncated->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'CH'));
        Assert::true(!str_contains($item['normalized'], 'Z'));
    }

    // --- Pagination : page 2 renvoie des elements differents, coherents avec page 1. ---
    $page1 = $solver->solve('7-lettres');
    $page2 = $solver->solve('7-lettres/page/2');
    Assert::notNull($page1);
    Assert::notNull($page2);
    Assert::same(1, $page1->page);
    Assert::same(2, $page2->page);
    Assert::true($page1->hasNextPage);
    Assert::true(!$page1->hasPreviousPage);
    Assert::true($page2->hasPreviousPage);
    Assert::same($page1->total, $page2->total, 'meme total sur les deux pages');
    $page1Words = array_column($page1->items, 'normalized');
    $page2Words = array_column($page2->items, 'normalized');
    Assert::same([], array_intersect($page1Words, $page2Words), 'aucun mot en commun entre page 1 et page 2');
    Assert::true(max($page1Words) < min($page2Words), 'page 2 suit alphabetiquement la page 1');

    // --- Budget de requetes : au plus 2, quelle que soit la combinaison de contraintes.
    // --- Regime EXACT (longueur/prefixe seuls ou combines) : toujours 2 (COUNT + LIMIT/OFFSET).
    // --- Regime BORNE : 1 requete quand l'ancrage est deja l'ordre d'affichage (normalized) --
    // --- fusion mesuree (audit final, code-optimizer, constat I-1), voir l'entete de
    // --- solveBounded() -- 2 requetes seulement pour l'ancrage sur suffixe (reversed), ou
    // --- l'ordre d'ancrage differe de l'ordre d'affichage. ---
    foreach ([$byLength, $byPrefix, $comboPage] as $result) {
        Assert::same(2, $result->queryCount, 'regime EXACT : toujours 2 requetes');
    }
    Assert::same(2, $bySuffix->queryCount, 'suffixe seul : ancrage reversed, 2 requetes (non fusionne)');
    foreach ([$contains, $unanchoredContains, $unanchoredWith, $withLetters, $without, $motif, $anchoredTruncated, $avecTwoLetters, $avecTwoLettersReversedInput, $avecTwoLettersFrequent, $avecThreeLetters, $avecThreeLettersReversedInput, $avecThreeLettersFrequent, $avecThreeLettersTooShort] as $result) {
        Assert::same(1, $result->queryCount, 'regime BORNE, ancrage normalized (ou aucun ancrage) : fusionne a 1 requete');
    }
    // prefixe ET suffixe explicites tous deux presents (D-025bis) : 1 requete de plus pour
    // choisir la lettre la moins frequente comme ancrage (voir anchorClause()) -- ici N (fin
    // de TION) s'avere moins frequente que C (debut de CH), donc ancrage reversed -> regime a
    // 2 requetes de base + 1 = 3, pas le chemin fusionne a 1 requete des autres cas BORNE.
    Assert::same(3, $prefixSuffix->queryCount, 'prefixe+suffixe explicites : 2 requetes de base (ancrage reversed) + 1 requete de frequence');
    foreach ([$byLength, $byPrefix, $comboPage, $bySuffix, $contains, $unanchoredContains, $unanchoredWith, $withLetters, $without, $motif, $prefixSuffix, $anchoredTruncated, $avecTwoLetters, $avecTwoLettersReversedInput, $avecTwoLettersFrequent, $avecThreeLetters, $avecThreeLettersReversedInput, $avecThreeLettersFrequent, $avecThreeLettersTooShort] as $result) {
        Assert::true($result->queryCount <= 10, 'budget de requetes indexees depasse');
    }

    // --- Redirection canonique geree par WordListFilters, deja testee par
    // --- WordListFiltersTest.php -- pas reteste ici pour eviter la duplication. ---

    // --- Statut (D-022), regime EXACT (longueur seule) : is_admitted precalcule, verifie par
    // --- force brute contre (is_ods8 OR is_ods9). ---
    $admittedOnly = $solver->solve('9-lettres/statut/admis');
    Assert::notNull($admittedOnly);
    Assert::true($admittedOnly->exact);
    Assert::same(2, $admittedOnly->queryCount, 'regime EXACT : is_admitted est un predicat de plus dans la meme clause WHERE, toujours 2 requetes');
    $expectedAdmitted9 = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE length = 9 AND (is_ods8 = 1 OR is_ods9 = 1)')->fetch()['c'];
    Assert::same($expectedAdmitted9, $admittedOnly->total);
    foreach ($admittedOnly->items as $item) {
        Assert::same(9, $item['length']);
        Assert::same('admitted', $item['status']);
    }

    $notAdmittedOnly = $solver->solve('9-lettres/statut/non-admis');
    Assert::notNull($notAdmittedOnly);
    $expectedNotAdmitted9 = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE length = 9 AND is_ods8 = 0 AND is_ods9 = 0')->fetch()['c'];
    Assert::same($expectedNotAdmitted9, $notAdmittedOnly->total);
    foreach ($notAdmittedOnly->items as $item) {
        Assert::same('french_not_admitted', $item['status']);
    }
    $expectedLength9Total = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE length = 9')->fetch()['c'];
    Assert::same($expectedLength9Total, $expectedAdmitted9 + $expectedNotAdmitted9, 'sanity check : admis + non admis = total de la longueur');

    // --- Statut (D-022), regime BORNE (combine a un ancrage prefixe non EXACT-eligible via
    // --- suffixe, verifie par force brute) : predicat is_admitted ajoute au meme cout que les
    // --- autres, jamais besoin d'index dedie ici. ---
    $boundedStatus = $solver->solve('terminant/tion/statut/admis');
    Assert::notNull($boundedStatus);
    $expectedBoundedStatus = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%TION' AND (is_ods8 = 1 OR is_ods9 = 1)")->fetch()['c'];
    Assert::true(!$boundedStatus->truncated, 'sanity check : panier "TION" + admis reste sous le plafond');
    Assert::same($expectedBoundedStatus, $boundedStatus->total);
    foreach ($boundedStatus->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'TION'));
        Assert::same('admitted', $item['status']);
    }

    // --- Tri par points (D-022), regime EXACT : ordre croissant puis decroissant, verifie sur
    // --- la totalite de la longueur (pas seulement la page courante) via une requete separee. ---
    $sortedAsc = $solver->solve('9-lettres/tri/points');
    Assert::notNull($sortedAsc);
    Assert::true($sortedAsc->exact);
    for ($i = 1; $i < count($sortedAsc->items); $i++) {
        Assert::true($sortedAsc->items[$i - 1]['score'] <= $sortedAsc->items[$i]['score'], 'ordre croissant par points attendu');
    }
    $expectedFirstScore = (int) $pdo->query('SELECT MIN(score) c FROM terms WHERE length = 9')->fetch()['c'];
    Assert::same($expectedFirstScore, $sortedAsc->items[0]['score'], 'le premier mot de la page 1 doit porter le score minimal de la longueur');

    $sortedDesc = $solver->solve('9-lettres/tri/points-desc');
    Assert::notNull($sortedDesc);
    for ($i = 1; $i < count($sortedDesc->items); $i++) {
        Assert::true($sortedDesc->items[$i - 1]['score'] >= $sortedDesc->items[$i]['score'], 'ordre decroissant par points attendu');
    }
    $expectedMaxScore = (int) $pdo->query('SELECT MAX(score) c FROM terms WHERE length = 9')->fetch()['c'];
    Assert::same($expectedMaxScore, $sortedDesc->items[0]['score'], 'le premier mot de la page 1 doit porter le score maximal de la longueur');

    // --- Tri par points (D-022), regime BORNE (longueur + suffixe, ancrage reversed, tri PHP
    // --- applique sur le panier deja borne par ROW_EXAMINATION_CEILING) : meme verification. ---
    $boundedSorted = $solver->solve('9-lettres/terminant/s/tri/points-desc');
    Assert::notNull($boundedSorted);
    for ($i = 1; $i < count($boundedSorted->items); $i++) {
        Assert::true($boundedSorted->items[$i - 1]['score'] >= $boundedSorted->items[$i]['score'], 'ordre decroissant par points attendu meme en regime BORNE');
    }
    foreach ($boundedSorted->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'S'));
        Assert::same(9, $item['length']);
    }

    // --- Statut + tri combines : les deux raffinements s'appliquent ensemble sans interference. ---
    $statusAndSort = $solver->solve('9-lettres/statut/admis/tri/points-desc');
    Assert::notNull($statusAndSort);
    Assert::same($expectedAdmitted9, $statusAndSort->total, 'meme total que le filtre statut seul (le tri ne change pas le panier)');
    foreach ($statusAndSort->items as $item) {
        Assert::same('admitted', $item['status']);
    }
    for ($i = 1; $i < count($statusAndSort->items); $i++) {
        Assert::true($statusAndSort->items[$i - 1]['score'] >= $statusAndSort->items[$i]['score']);
    }

    // --- Budget de requetes : les nouveaux cas restent dans les memes regimes deja verifies
    // --- ci-dessus (EXACT = 2, BORNE ancrage normalized = 1, BORNE ancrage reversed = 2). ---
    foreach ([$admittedOnly, $notAdmittedOnly, $sortedAsc, $sortedDesc, $statusAndSort] as $result) {
        Assert::same(2, $result->queryCount, 'regime EXACT inchange par statut/tri');
    }
    Assert::same(2, $boundedStatus->queryCount, 'regime BORNE ancrage reversed (terminant seul, suffixe) inchange par statut');
    Assert::same(2, $boundedSorted->queryCount, 'regime BORNE ancrage reversed (suffixe) inchange par tri');

    // --- Position (D-023) : une lettre connue a une position precise, verifiee par force
    // --- brute (substr() cote SQL, position PHP equivalente cote test). Toujours regime
    // --- BORNE (needsUnindexedPredicates() = true), ancre sur la longueur seule -> 1 requete
    // --- fusionnee (meme mecanisme que "sans"/"contenant" ancres par longueur). ---
    $byPosition = $solver->solve('9-lettres/position/3/a');
    Assert::notNull($byPosition);
    // queryCount = 1 distingue le regime BORNE fusionne (ancrage normalized) du regime EXACT
    // (toujours 2) -- $page->exact ne le distingue PAS a lui seul : il vaut simplement
    // !$truncated dans les deux regimes (voir WordListPage), donc reste vrai ici (panier sous
    // le plafond) sans que ce soit un regime EXACT.
    Assert::same(1, $byPosition->queryCount, 'regime BORNE, ancrage longueur seule -> fusionne a 1 requete');
    $expectedByPosition = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 3, 1) = 'A'")->fetch()['c'];
    Assert::true(!$byPosition->truncated, 'sanity check : panier "9-lettres, A en 3e position" reste sous le plafond');
    Assert::same($expectedByPosition, $byPosition->total);
    foreach ($byPosition->items as $item) {
        Assert::same(9, $item['length']);
        Assert::same('A', substr($item['normalized'], 2, 1), $item['normalized'] . ' doit avoir A en 3e position (index 2, 0-based)');
    }

    // --- Position combinee a un prefixe explicite : les deux predicats s'appliquent ensemble. ---
    $positionWithPrefix = $solver->solve('9-lettres/commencant/c/position/3/a');
    Assert::notNull($positionWithPrefix);
    $expectedPositionWithPrefix = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized >= 'C' AND normalized < 'D' AND substr(normalized, 3, 1) = 'A'")->fetch()['c'];
    Assert::same($expectedPositionWithPrefix, $positionWithPrefix->total);
    foreach ($positionWithPrefix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'C'));
        Assert::same('A', substr($item['normalized'], 2, 1));
    }

    // --- Collapse des positions degenerees (D-023) : position 1 et position = longueur
    // --- doivent produire EXACTEMENT le meme resultat que commencant/terminant seuls --
    // --- verifie ici via le vrai solveur, pas seulement au niveau du parsing (deja couvert
    // --- par WordListFiltersTest.php). Cette verification a directement mis en evidence le
    // --- probleme de contenu duplique existant sur "motif" (voir reports/query-plans/
    // --- position-family.md) -- non reproduit ici grace au collapse.
    $collapsedFirst = $solver->solve('5-lettres/position/1/a');
    $equivalentPrefix = $solver->solve('5-lettres/commencant/a');
    Assert::notNull($collapsedFirst);
    Assert::notNull($equivalentPrefix);
    Assert::same($equivalentPrefix->total, $collapsedFirst->total, 'position/1/a doit collapser vers un resultat identique a commencant/a');
    Assert::same($equivalentPrefix->canonicalPath, $collapsedFirst->canonicalPath, 'meme chemin canonique -- une seule URL indexable pour cette liste');

    $collapsedLast = $solver->solve('5-lettres/position/5/a');
    $equivalentSuffix = $solver->solve('5-lettres/terminant/a');
    Assert::notNull($collapsedLast);
    Assert::notNull($equivalentSuffix);
    Assert::same($equivalentSuffix->total, $collapsedLast->total, 'position/5/a doit collapser vers un resultat identique a terminant/a');
    Assert::same($equivalentSuffix->canonicalPath, $collapsedLast->canonicalPath);

    // --- Regression "commencant/terminant multi-lettres" (tache de dimensionnement 2026-08-18) :
    // --- la grammaire d'URL fonctionnait deja pour un prefixe/suffixe de 1 a 15 lettres
    // --- (WordListFilters::readSingleLetterRun()), mais seule la longueur 1 avait ete mesuree
    // --- sur la forme reellement publiee jusqu'ici (D-017). Verifie ici, par force brute, les
    // --- longueurs 3 et 4 (2 lettres deja couvertes ci-dessus, "commencant/qi") -- balayage
    // --- complet des 39 539 combinaisons reelles (21 734 prefixes + 17 805 suffixes) fait
    // --- separement par scripts/bench_commencant_terminant_multilettres_full_sweep.php, voir
    // --- reports/query-plans/commencant-terminant-multi-lettres-dimensionnement.md -- ce test-ci
    // --- couvre seulement quelques cas representatifs pour la suite de regression rapide.
    $prefix3 = $solver->solve('commencant/ant');
    Assert::notNull($prefix3);
    Assert::true($prefix3->exact, 'commencant seul reste toujours en regime EXACT');
    Assert::same(2, $prefix3->queryCount);
    $expectedPrefix3 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'ANT' AND normalized < 'ANU'")->fetch()['c'];
    Assert::same($expectedPrefix3, $prefix3->total);
    foreach ($prefix3->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'ANT'));
    }

    $prefix4 = $solver->solve('commencant/anti');
    Assert::notNull($prefix4);
    Assert::true($prefix4->exact);
    $expectedPrefix4 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'ANTI' AND normalized < 'ANTJ'")->fetch()['c'];
    Assert::same($expectedPrefix4, $prefix4->total);

    $suffix3 = $solver->solve('terminant/ing');
    Assert::notNull($suffix3);
    Assert::same(2, $suffix3->queryCount, 'regime BORNE ancrage reversed (terminant seul), quel que soit le nombre de lettres');
    $expectedSuffix3 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%ING'")->fetch()['c'];
    Assert::same($expectedSuffix3, $suffix3->total);
    foreach ($suffix3->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'ING'));
    }

    $suffix4 = $solver->solve('terminant/zing');
    Assert::notNull($suffix4);
    $expectedSuffix4 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%ZING'")->fetch()['c'];
    Assert::same($expectedSuffix4, $suffix4->total);

    // --- Cas degenere proche de Z (rangeBounds() sans borne superieure quand le prefixe/suffixe
    // --- n'est fait que de 'Z' -- deja identifie comme un risque de regression de PERFORMANCE
    // --- par D-025bis, mais dans un contexte different : prefixe ET suffixe combines, choisis par
    // --- une heuristique de frequence. Ici une SEULE contrainte a la fois (commencant OU
    // --- terminant seul, jamais combines) -- structurellement le meme chemin EXACT/BORNE deja
    // --- exerce ci-dessus pour "commencant/qi"/"terminant/tion", pas de nouveau risque
    // --- structurel, mais verifie explicitement plutot que suppose. "terminant/zz" est un cas
    // --- REEL (JAZZ, BUZZ, FIZZ...), "commencant/zzzz" n'existe pas reellement (0 mot francais ne
    // --- commence par 4 Z) -- les deux doivent rester rapides et corrects. ---
    $suffixZZ = $solver->solve('terminant/zz');
    Assert::notNull($suffixZZ);
    $expectedSuffixZZ = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%ZZ'")->fetch()['c'];
    Assert::same($expectedSuffixZZ, $suffixZZ->total);
    Assert::same(5, $suffixZZ->total, 'sanity check : exactement 5 mots se terminent par ZZ');
    foreach ($suffixZZ->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'ZZ'));
    }

    $prefixZZZZ = $solver->solve('commencant/zzzz');
    Assert::notNull($prefixZZZZ);
    Assert::same(0, $prefixZZZZ->total, 'aucun mot francais ne commence par ZZZZ -- rangeBounds() sans borne superieure doit rester correct (panier vide, pas une erreur)');

    // --- Collapse "avec/X" redondant avec un commencant/terminant d'une seule lettre X (D-032),
    // --- verifie ici via le VRAI solveur (pas seulement le parsing, deja couvert par
    // --- WordListFiltersTest.php) : force brute sur les 26 lettres pour chaque famille. Avant ce
    // --- correctif, "commencant/X/avec/X" (17/26 lettres) basculait a tort en regime BORNE
    // --- plafonne (ROW_EXAMINATION_CEILING = 10 000) alors que le vrai total, deja disponible
    // --- sans plafond via le regime EXACT de "commencant/X" seul, peut atteindre 224 205 (R) --
    // --- reports/query-plans/commencant-avec-no-length-full-sweep.md section 5. Verifie que
    // --- solve('commencant/X/avec/X') et solve('commencant/X') produisent maintenant des
    // --- WordListPage strictement identiques sur total, truncated, exact, canonicalPath et
    // --- queryCount -- pas seulement des totaux egaux.
    foreach (range('A', 'Z') as $x) {
        $degeneratePrefix = $solver->solve('commencant/' . strtolower($x) . '/avec/' . strtolower($x));
        $simplePrefix = $solver->solve('commencant/' . strtolower($x));
        Assert::notNull($degeneratePrefix);
        Assert::notNull($simplePrefix);
        Assert::same($simplePrefix->total, $degeneratePrefix->total, "commencant/$x/avec/$x doit avoir le meme total que commencant/$x seul");
        Assert::same($simplePrefix->truncated, $degeneratePrefix->truncated, "commencant/$x/avec/$x : meme statut truncated que commencant/$x seul");
        Assert::same($simplePrefix->exact, $degeneratePrefix->exact, "commencant/$x/avec/$x : meme regime exact que commencant/$x seul");
        Assert::same($simplePrefix->canonicalPath, $degeneratePrefix->canonicalPath, "commencant/$x/avec/$x doit collapser vers le meme canonicalPath que commencant/$x");
        Assert::same($simplePrefix->queryCount, $degeneratePrefix->queryCount, "commencant/$x/avec/$x : meme budget de requetes que commencant/$x seul");
        Assert::true(!$degeneratePrefix->truncated, "commencant/$x/avec/$x ne doit plus jamais etre tronque a tort ($x)");

        $degenerateSuffix = $solver->solve('terminant/' . strtolower($x) . '/avec/' . strtolower($x));
        $simpleSuffix = $solver->solve('terminant/' . strtolower($x));
        Assert::notNull($degenerateSuffix);
        Assert::notNull($simpleSuffix);
        Assert::same($simpleSuffix->total, $degenerateSuffix->total, "terminant/$x/avec/$x doit avoir le meme total que terminant/$x seul");
        Assert::same($simpleSuffix->truncated, $degenerateSuffix->truncated, "terminant/$x/avec/$x : meme statut truncated que terminant/$x seul");
        Assert::same($simpleSuffix->canonicalPath, $degenerateSuffix->canonicalPath, "terminant/$x/avec/$x doit collapser vers le meme canonicalPath que terminant/$x");
        Assert::same($simpleSuffix->queryCount, $degenerateSuffix->queryCount, "terminant/$x/avec/$x : meme budget de requetes que terminant/$x seul");
    }

    // Cas emblematique du rapport (pire divergence mesuree avant correctif) : R, prefixe le plus
    // frequent de la base. Avant D-032, "commencant/r/avec/r" plafonnait a 10 000 (regime BORNE)
    // alors que le vrai total (regime EXACT de "commencant/r" seul) vaut 224 205 -- verifie ici
    // que le vrai total exact est desormais renvoye, sans aucun plafond.
    $worstCaseR = $solver->solve('commencant/r/avec/r');
    Assert::notNull($worstCaseR);
    $bruteForceR = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'R' AND normalized < 'S'")->fetch()['c'];
    Assert::same(224205, $bruteForceR, 'sanity check : R doit rester le prefixe le plus frequent mesure (224 205), sinon ce test ne prouve plus rien');
    Assert::same($bruteForceR, $worstCaseR->total, 'commencant/r/avec/r doit desormais renvoyer le vrai total exact, jamais plafonne a 10 000');
    Assert::true(!$worstCaseR->truncated);
    Assert::true($worstCaseR->exact, 'regime EXACT retrouve une fois le avec redondant retire');
    Assert::same('commencant/r', $worstCaseR->canonicalPath, 'WordListPage::$canonicalPath ne porte jamais le prefixe "/mots" (voir WordListFilters::canonicalPath())');

    // --- Non-regression : lettre "avec" DIFFERENTE du prefixe -- doit rester en regime BORNE
    // --- plafonne exactement comme avant (vrai predicat, jamais retire). "commencant/r/avec/y"
    // --- choisi car sous ROW_EXAMINATION_CEILING (total reel 6 360, deja mesure non tronque --
    // --- reports/query-plans/commencant-avec-no-length-full-sweep.md section 4) : preuve d'un
    // --- total EXACT, pas seulement "non tronque a tort". ---
    // Note : $page->exact vaut !$truncated dans les DEUX regimes (voir plus haut dans ce fichier),
    // il ne distingue donc pas EXACT de BORNE a lui seul -- queryCount le fait (2 en regime
    // EXACT, 1 en regime BORNE fusionne, voir le bloc "Budget de requetes" plus bas). Verifie ici
    // via WordListFilters directement que "avec/y" n'a PAS ete collapse (contrairement a
    // "avec/r"), plutot qu'une inference indirecte sur $page->exact.
    $filtersNonRedundant = WordListFilters::fromPath('commencant/r/avec/y');
    Assert::notNull($filtersNonRedundant);
    Assert::same(['Y' => 1], $filtersNonRedundant->withLetters, 'avec/y non redondant avec commencant/r : jamais retire');

    $realConstraintPrefix = $solver->solve('commencant/r/avec/y');
    Assert::notNull($realConstraintPrefix);
    $bruteForceRY = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'R' AND normalized < 'S' AND instr(normalized, 'Y') > 0")->fetch()['c'];
    Assert::true(!$realConstraintPrefix->truncated, 'sanity check : commencant/r/avec/y (' . $bruteForceRY . ' correspondances) doit rester sous le plafond');
    Assert::same($bruteForceRY, $realConstraintPrefix->total, 'total correct pour un "avec" non redondant, jamais collapse');

    // --- Non-regression : minCount >= 2 pour la meme lettre que le prefixe ("avec/x/x", un
    // --- DEUXIEME X exige) -- jamais retire, reste un vrai predicat en regime BORNE. ---
    $minCountTwoPrefix = $solver->solve('commencant/a/avec/a/a');
    Assert::notNull($minCountTwoPrefix);
    $bruteForceAA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'A' AND normalized < 'B' AND (LENGTH(normalized) - LENGTH(REPLACE(normalized, 'A', ''))) >= 2")->fetch()['c'];
    // A avec au moins 2 A (31 950 mots) depasse reellement ROW_EXAMINATION_CEILING : truncated
    // attendu, total plafonne a 10 000 -- toujours un panier REELLEMENT restreint par rapport a
    // "commencant/a" seul (54 232), jamais collapse a tort comme avant D-032 le faisait pour
    // minCount=1. Meme discipline que $anchoredTruncated plus haut dans ce fichier (min() explicite
    // plutot qu'un total brut suppose sous le plafond).
    Assert::true($bruteForceAA > WordListSolver::ROW_EXAMINATION_CEILING, 'sanity check : avec/a/a doit reellement depasser le plafond pour que ce test ait un sens, obtenu ' . $bruteForceAA);
    Assert::true($minCountTwoPrefix->truncated, 'avec/a/a (minCount=2) reste un vrai predicat non collapse : panier reellement au-dessus du plafond');
    Assert::same(WordListSolver::ROW_EXAMINATION_CEILING, $minCountTwoPrefix->total, 'total plafonne, jamais le vrai total exact -- preuve que ce cas n\'est PAS collapse comme avec/a (minCount=1) l\'est');
    $simpleAPrefix = $solver->solve('commencant/a');
    Assert::true($minCountTwoPrefix->total < $simpleAPrefix->total, 'exiger un deuxieme A doit reellement restreindre le panier par rapport a commencant/a seul');
};
