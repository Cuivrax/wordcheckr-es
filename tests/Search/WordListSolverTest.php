<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\WordListFilters;
use App\Search\WordListSolver;
use Tests\Support\Assert;

/**
 * Exerce App\Search\WordListSolver sur la vraie base storage/dictionary_es.sqlite (lecture
 * seule) : correction croisee par force brute pour chaque contrainte et plusieurs
 * combinaisons, comportement de pagination, et le plafond de securite
 * (WordListSolver::ROW_EXAMINATION_CEILING) -- meme methodologie que RackSolverTest.php.
 *
 * Adapte de tests/Search/WordListSolverTest.php (site francais) -- mots/lettres pivots
 * remplaces par des equivalents espagnols reels (statistiques recalculees directement sur
 * storage/dictionary_es.sqlite, jamais recopiees du site francais), PLUS une section dediee
 * aux regressions Ñ/tuiles digrammes propres a ce depot (voir la fin du fichier).
 *
 * Difference structurelle importante par rapport au site francais : rangeBounds()
 * (WordListSolver::ALPHABET_ORDER) traite desormais Ñ comme la DERNIERE lettre de
 * l'alphabet (elle trie apres Z sous la collation BINARY de SQLite, verifie sur la base
 * reelle), pas Z -- les tests "cas degenere pres de Z" du site francais sont donc repris
 * ICI avec Ñ comme lettre terminale, PLUS un test explicite que Z n'est plus traite a tort
 * comme terminale (regression trouvee et corrigee avant tout import, voir
 * WordListSolver::rangeBounds()).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $solver = new WordListSolver($connection);
    $pdo = $connection->pdo();

    // --- Entree invalide ou hors perimetre : aucune liste, meme convention que
    // --- TermLookup::find() et RackSolver::solve(). ---
    Assert::null($solver->solve('inconnu/valeur'));
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
        Assert::true(in_array($item['status'], ['admitted', 'french_not_admitted'], true), 'jamais STATUS_UNKNOWN sur une ligne de `terms`');
    }

    // --- Prefixe seul : EXACT, verifie par force brute (pas un echantillon). "QI" existe en
    // --- espagnol (ex. "quiosco" non, mais des formes verbales rares en QI-) : 1 seul mot,
    // --- assez rare pour rester un bon cas limite comme "commencant/qi" du site francais. ---
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

    // --- Longueur + prefixe combines : intersection exacte. CH existe comme prefixe en
    // --- espagnol (chico, chorro...) independamment de son statut de tuile digramme --
    // --- normalized reste du texte ordinaire, jamais tokenise en tuiles pour ce genre de
    // --- recherche (voir Normalizer::reverse()/normalize()). ---
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

    // --- Terminant seul : verifie par force brute sur reversed. "CION" (equivalent espagnol
    // --- du francais "-tion", ex. "nacion" -> "NACION") est un bon suffixe frequent. ---
    $bySuffix = $solver->solve('terminant/cion');
    Assert::notNull($bySuffix);
    foreach ($bySuffix->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'CION'));
    }
    $stmtSuffix = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%CION'");
    $stmtSuffix->execute();
    $expectedSuffixTotal = (int) $stmtSuffix->fetch()['c'];
    Assert::true(!$bySuffix->truncated, 'le panier de longueur "CION" ne doit pas depasser le plafond');
    Assert::same($expectedSuffixTotal, $bySuffix->total);

    // --- Regression index idx_terms_length_reversed : "longueur + terminant" combine doit
    // --- rester correct et rapide (index compose, pas un ancrage reversed global + filtre
    // --- residuel sur toutes longueurs). "CION" choisi ici (pas "S", qui depasse
    // --- ROW_EXAMINATION_CEILING des 7 lettres en espagnol -- pluriels tres frequents en
    // --- -S, verifie : 17 637 correspondances reelles -- un vrai plafond BORNE, teste
    // --- separement plus bas, pas une regression de CE test-ci) : 38 correspondances
    // --- reelles, sous le plafond, verifie ici que le total EXACT reste correct. ---
    $lengthSuffix = $solver->solve('7-lettres/terminant/cion');
    Assert::notNull($lengthSuffix);
    Assert::same(2, $lengthSuffix->queryCount);
    Assert::true(!$lengthSuffix->truncated, 'sanity check : 7-lettres/terminant/cion doit rester sous le plafond');
    foreach ($lengthSuffix->items as $item) {
        Assert::same(7, $item['length']);
        Assert::true(str_ends_with($item['normalized'], 'CION'));
    }
    $expectedLengthSuffixTotal = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 7 AND normalized LIKE '%CION'")->fetch()['c'];
    Assert::same($expectedLengthSuffixTotal, $lengthSuffix->total, 'total exact attendu (index compose length+reversed)');
    Assert::same(38, $lengthSuffix->total, 'sanity check : 38 mots de 7 lettres se terminent par CION, verifie directement sur la base');

    // --- Meme index, cas REELLEMENT au-dessus du plafond (pluriels en -S tres frequents en
    // --- espagnol) : doit rester correct (total plafonne, marque truncated), jamais une
    // --- erreur ou un total silencieusement faux. ---
    $lengthSuffixTruncated = $solver->solve('7-lettres/terminant/s');
    Assert::notNull($lengthSuffixTruncated);
    Assert::same(2, $lengthSuffixTruncated->queryCount);
    $bruteForce7S = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 7 AND normalized LIKE '%S'")->fetch()['c'];
    Assert::true($bruteForce7S > WordListSolver::ROW_EXAMINATION_CEILING, 'sanity check : 7-lettres/terminant/s doit reellement depasser le plafond, obtenu ' . $bruteForce7S);
    Assert::true($lengthSuffixTruncated->truncated, 'panier reellement au-dessus du plafond -> truncated attendu');
    Assert::same(WordListSolver::ROW_EXAMINATION_CEILING, $lengthSuffixTruncated->total, 'total plafonne, jamais silencieusement faux');
    foreach ($lengthSuffixTruncated->items as $item) {
        Assert::same(7, $item['length']);
        Assert::true(str_ends_with($item['normalized'], 'S'));
    }

    // --- Contenant : verifie par force brute (instr() cote SQL, str_contains() cote PHP). ---
    $contains = $solver->solve('contenant/che');
    Assert::notNull($contains);
    foreach ($contains->items as $item) {
        Assert::true(str_contains($item['normalized'], 'CHE'));
    }

    // --- Regression C1 (heritee du site francais, code-reviewer, bloquant) : "contenant" SEUL,
    // --- sans aucun ancrage, doit trouver TOUTES les correspondances de toute la base, pas
    // --- seulement celles situees dans les ROW_EXAMINATION_CEILING premiers mots de l'ordre
    // --- alphabetique. "XIL" choisi ici (equivalent du "XYL" francais) : 307 correspondances
    // --- reelles mesurees, toutes hors des 10 000 premiers mots alphabetiques (qui s'arretent
    // --- a "ACADEMIZADA", verifie directement) -- le total renvoye doit donc etre EXACT.
    $unanchoredContains = $solver->solve('contenant/xil');
    Assert::notNull($unanchoredContains);
    $bruteForceXil = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE instr(normalized, \'XIL\') > 0')->fetch()['c'];
    Assert::true($bruteForceXil > 0, 'sanity check : XIL doit avoir des correspondances reelles dans la base');
    Assert::true(!$unanchoredContains->truncated, 'XIL (' . $bruteForceXil . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceXil, $unanchoredContains->total, 'C1 : "contenant" sans ancrage doit trouver TOUTES les correspondances, pas seulement celles des 10 000 premiers mots alphabetiques');
    foreach ($unanchoredContains->items as $item) {
        Assert::true(str_contains($item['normalized'], 'XIL'));
    }

    // --- Regression C1, variante "avec" (minCount = 1, chemin optimise instr()) : meme
    // --- verification par force brute, plusieurs lettres combinees, sans aucun ancrage. C, K,
    // --- W (equivalent du "x/y/z" francais -- X/Y/Z ne co-occurrent jamais dans un seul mot
    // --- espagnol de cette base, verifie : 0 correspondance). ---
    $unanchoredWith = $solver->solve('avec/c/k/w');
    Assert::notNull($unanchoredWith);
    $bruteForceCKW = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE instr(normalized, \'C\') > 0 AND instr(normalized, \'K\') > 0 AND instr(normalized, \'W\') > 0')->fetch()['c'];
    Assert::true($bruteForceCKW > 0, 'sanity check : au moins un mot avec C, K et W doit exister');
    Assert::true(!$unanchoredWith->truncated, 'avec/c/k/w (' . $bruteForceCKW . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceCKW, $unanchoredWith->total, 'C1 : "avec" sans ancrage doit trouver TOUTES les correspondances');
    foreach ($unanchoredWith->items as $item) {
        Assert::true(str_contains($item['normalized'], 'C') && str_contains($item['normalized'], 'K') && str_contains($item['normalized'], 'W'));
    }

    // --- Avec, repetitions comptees : verifie par force brute (array_count_values, mb-safe). ---
    $withLetters = $solver->solve('avec/a/a/r');
    Assert::notNull($withLetters);
    foreach ($withLetters->items as $item) {
        $counts = array_count_values(mb_str_split($item['normalized'], 1, 'UTF-8'));
        Assert::true(($counts['A'] ?? 0) >= 2, $item['normalized'] . ' doit contenir au moins 2 A');
        Assert::true(($counts['R'] ?? 0) >= 1, $item['normalized'] . ' doit contenir au moins 1 R');
    }
    Assert::true($withLetters->total > 0, 'sanity check : au moins un mot avec 2 A et 1 R doit exister');

    // --- Palier 2 de "avec" (longueur explicite + exactement deux lettres, minCount=1) :
    // --- ancrage sur length = ? (idx_terms_length_normalized), jamais un ancrage "avec". ---
    $avecTwoLetters = $solver->solve('9-lettres/avec/q/x');
    Assert::notNull($avecTwoLetters);
    Assert::same(1, $avecTwoLetters->queryCount, 'ancrage normalized (length=?) : fusionne a 1 seule requete');
    $bruteForceQX9 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, 'Q') > 0 AND instr(normalized, 'X') > 0")->fetch()['c'];
    Assert::true(!$avecTwoLetters->truncated, '9-lettres/avec/q/x (' . $bruteForceQX9 . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceQX9, $avecTwoLetters->total, 'correction verifiee par force brute');
    foreach ($avecTwoLetters->items as $item) {
        Assert::same(9, $item['length']);
        Assert::true(str_contains($item['normalized'], 'Q') && str_contains($item['normalized'], 'X'));
    }

    $avecTwoLettersReversedInput = $solver->solve('9-lettres/avec/x/q');
    Assert::notNull($avecTwoLettersReversedInput);
    Assert::same($avecTwoLetters->total, $avecTwoLettersReversedInput->total, 'ordre de saisie des deux lettres "avec" sans effet sur le total');
    Assert::same($avecTwoLetters->canonicalPath, $avecTwoLettersReversedInput->canonicalPath, 'meme canonicalPath quel que soit l\'ordre de saisie');
    Assert::same('9-lettres/avec/q/x', $avecTwoLetters->canonicalPath, 'ordre alphabetique impose par canonicalPath()');

    $avecTwoLettersFrequent = $solver->solve('11-lettres/avec/e/s');
    Assert::notNull($avecTwoLettersFrequent);
    Assert::same(1, $avecTwoLettersFrequent->queryCount, 'toujours 1 seule requete, meme avec deux lettres tres frequentes');
    foreach ($avecTwoLettersFrequent->items as $item) {
        Assert::same(11, $item['length']);
        Assert::true(str_contains($item['normalized'], 'E') && str_contains($item['normalized'], 'S'));
    }

    // --- Palier 3 de "avec" (longueur explicite + exactement trois lettres, minCount=1). ---
    $avecThreeLetters = $solver->solve('9-lettres/avec/q/x/z');
    Assert::notNull($avecThreeLetters);
    Assert::same(1, $avecThreeLetters->queryCount, 'ancrage normalized (length=?) : fusionne a 1 seule requete');
    $bruteForceQXZ9 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, 'Q') > 0 AND instr(normalized, 'X') > 0 AND instr(normalized, 'Z') > 0")->fetch()['c'];
    Assert::true(!$avecThreeLetters->truncated, '9-lettres/avec/q/x/z (' . $bruteForceQXZ9 . ' correspondances) est sous le plafond, ne doit pas etre tronque');
    Assert::same($bruteForceQXZ9, $avecThreeLetters->total, 'correction verifiee par force brute');
    foreach ($avecThreeLetters->items as $item) {
        Assert::same(9, $item['length']);
        Assert::true(str_contains($item['normalized'], 'Q') && str_contains($item['normalized'], 'X') && str_contains($item['normalized'], 'Z'));
    }

    $avecThreeLettersReversedInput = $solver->solve('9-lettres/avec/z/q/x');
    Assert::notNull($avecThreeLettersReversedInput);
    Assert::same($avecThreeLetters->total, $avecThreeLettersReversedInput->total, 'ordre de saisie des trois lettres "avec" sans effet sur le total');
    Assert::same($avecThreeLetters->canonicalPath, $avecThreeLettersReversedInput->canonicalPath, 'meme canonicalPath quel que soit l\'ordre de saisie');
    Assert::same('9-lettres/avec/q/x/z', $avecThreeLetters->canonicalPath, 'ordre alphabetique impose par canonicalPath()');

    $avecThreeLettersFrequent = $solver->solve('11-lettres/avec/e/s/t');
    Assert::notNull($avecThreeLettersFrequent);
    Assert::same(1, $avecThreeLettersFrequent->queryCount, 'toujours 1 seule requete, meme avec trois lettres tres frequentes');
    foreach ($avecThreeLettersFrequent->items as $item) {
        Assert::same(11, $item['length']);
        Assert::true(str_contains($item['normalized'], 'E') && str_contains($item['normalized'], 'S') && str_contains($item['normalized'], 'T'));
    }

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
        Assert::same(5, mb_strlen($item['normalized'], 'UTF-8'));
        Assert::same('C', mb_substr($item['normalized'], 0, 1, 'UTF-8'));
        Assert::same('E', mb_substr($item['normalized'], 3, 1, 'UTF-8'));
    }

    // --- Combinaison prefixe + terminant : suffixe applique en predicat supplementaire. ---
    $prefixSuffix = $solver->solve('commencant/ch/terminant/cion');
    Assert::notNull($prefixSuffix);
    foreach ($prefixSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'CH'));
        Assert::true(str_ends_with($item['normalized'], 'CION'));
    }
    $bruteForceChCion = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'CH' AND normalized < 'CI' AND normalized LIKE '%CION'")->fetch()['c'];
    Assert::true(!$prefixSuffix->truncated, 'C1 : le panier combine (CH + CION, ' . $bruteForceChCion . ' correspondances reelles) est sous le plafond');
    Assert::same($bruteForceChCion, $prefixSuffix->total, 'total exact attendu, panier combine sous le plafond');

    // --- Regression D-025bis (heritee) : prefixe ET suffixe D'UNE SEULE LETTRE CHACUN doivent
    // --- ancrer sur idx_terms_startletter_endletter_normalized (egalite combinee), jamais sur
    // --- une plage residuelle -- 1 seule requete quel que soit le couple de lettres. ---
    $frequentPrefixRareSuffix = $solver->solve('commencant/r/terminant/h');
    Assert::notNull($frequentPrefixRareSuffix);
    $bruteForceRH = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'R' AND normalized < 'S' AND normalized LIKE '%H'")->fetch()['c'];
    Assert::same($bruteForceRH, $frequentPrefixRareSuffix->total, 'correction verifiee par force brute');
    foreach ($frequentPrefixRareSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'R') && str_ends_with($item['normalized'], 'H'));
    }
    Assert::same(1, $frequentPrefixRareSuffix->queryCount, 'prefixe+suffixe d\'une seule lettre chacun : egalite combinee, 1 seule requete fusionnee');

    $rarePrefixFrequentSuffix = $solver->solve('commencant/q/terminant/s');
    Assert::notNull($rarePrefixFrequentSuffix);
    $bruteForceQS = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Q' AND normalized < 'R' AND normalized LIKE '%S'")->fetch()['c'];
    Assert::same($bruteForceQS, $rarePrefixFrequentSuffix->total, 'correction verifiee par force brute');
    foreach ($rarePrefixFrequentSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'Q') && str_ends_with($item['normalized'], 'S'));
    }
    Assert::same(1, $rarePrefixFrequentSuffix->queryCount, 'prefixe+suffixe d\'une seule lettre chacun : egalite combinee, 1 seule requete fusionnee');

    // --- Plafond de securite, toujours actif sur le panier COMBINE quand il depasse
    // --- reellement ROW_EXAMINATION_CEILING (pas seulement l'ancrage). ---
    $anchoredTruncated = $solver->solve('commencant/ch/sans/z');
    Assert::notNull($anchoredTruncated);
    $bruteForceChSansZ = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'CH' AND normalized < 'CI' AND instr(normalized, 'Z') = 0")->fetch()['c'];
    Assert::true($bruteForceChSansZ > WordListSolver::ROW_EXAMINATION_CEILING, 'sanity check : le panier combine CH + sans Z doit reellement depasser le plafond, obtenu ' . $bruteForceChSansZ);
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

    // --- Budget de requetes : au plus 2, quelle que soit la combinaison de contraintes. ---
    foreach ([$byLength, $byPrefix, $comboPage] as $result) {
        Assert::same(2, $result->queryCount, 'regime EXACT : toujours 2 requetes');
    }
    Assert::same(2, $bySuffix->queryCount, 'suffixe seul : ancrage reversed, 2 requetes (non fusionne)');
    foreach ([$contains, $unanchoredContains, $unanchoredWith, $withLetters, $without, $motif, $anchoredTruncated, $avecTwoLetters, $avecTwoLettersReversedInput, $avecTwoLettersFrequent, $avecThreeLetters, $avecThreeLettersReversedInput, $avecThreeLettersFrequent, $avecThreeLettersTooShort] as $result) {
        Assert::same(1, $result->queryCount, 'regime BORNE, ancrage normalized (ou aucun ancrage) : fusionne a 1 requete');
    }
    Assert::true($prefixSuffix->queryCount <= 3, 'prefixe+suffixe explicites : au plus 3 requetes (2 de base + 1 de frequence eventuelle)');
    foreach ([$byLength, $byPrefix, $comboPage, $bySuffix, $contains, $unanchoredContains, $unanchoredWith, $withLetters, $without, $motif, $prefixSuffix, $anchoredTruncated, $avecTwoLetters, $avecTwoLettersReversedInput, $avecTwoLettersFrequent, $avecThreeLetters, $avecThreeLettersReversedInput, $avecThreeLettersFrequent, $avecThreeLettersTooShort] as $result) {
        Assert::true($result->queryCount <= 10, 'budget de requetes indexees depasse');
    }

    // --- Statut, regime EXACT (longueur seule) : is_admitted precalcule, verifie par force
    // --- brute contre (is_ods8 OR is_ods9). ---
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

    $boundedStatus = $solver->solve('terminant/cion/statut/admis');
    Assert::notNull($boundedStatus);
    $expectedBoundedStatus = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%CION' AND (is_ods8 = 1 OR is_ods9 = 1)")->fetch()['c'];
    Assert::true(!$boundedStatus->truncated, 'sanity check : panier "CION" + admis reste sous le plafond');
    Assert::same($expectedBoundedStatus, $boundedStatus->total);
    foreach ($boundedStatus->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'CION'));
        Assert::same('admitted', $item['status']);
    }

    // --- Tri par points, regime EXACT : ordre croissant puis decroissant. ---
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

    $boundedSorted = $solver->solve('9-lettres/terminant/s/tri/points-desc');
    Assert::notNull($boundedSorted);
    for ($i = 1; $i < count($boundedSorted->items); $i++) {
        Assert::true($boundedSorted->items[$i - 1]['score'] >= $boundedSorted->items[$i]['score'], 'ordre decroissant par points attendu meme en regime BORNE');
    }
    foreach ($boundedSorted->items as $item) {
        Assert::true(str_ends_with($item['normalized'], 'S'));
        Assert::same(9, $item['length']);
    }

    $statusAndSort = $solver->solve('9-lettres/statut/admis/tri/points-desc');
    Assert::notNull($statusAndSort);
    Assert::same($expectedAdmitted9, $statusAndSort->total, 'meme total que le filtre statut seul (le tri ne change pas le panier)');
    foreach ($statusAndSort->items as $item) {
        Assert::same('admitted', $item['status']);
    }
    for ($i = 1; $i < count($statusAndSort->items); $i++) {
        Assert::true($statusAndSort->items[$i - 1]['score'] >= $statusAndSort->items[$i]['score']);
    }

    foreach ([$admittedOnly, $notAdmittedOnly, $sortedAsc, $sortedDesc, $statusAndSort] as $result) {
        Assert::same(2, $result->queryCount, 'regime EXACT inchange par statut/tri');
    }
    Assert::same(2, $boundedStatus->queryCount, 'regime BORNE ancrage reversed (terminant seul, suffixe) inchange par statut');
    Assert::same(2, $boundedSorted->queryCount, 'regime BORNE ancrage reversed (suffixe) inchange par tri');

    // --- Position : une lettre connue a une position precise, verifiee par force brute. ---
    $byPosition = $solver->solve('9-lettres/position/3/a');
    Assert::notNull($byPosition);
    Assert::same(1, $byPosition->queryCount, 'regime BORNE, ancrage longueur seule -> fusionne a 1 requete');
    $expectedByPosition = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 3, 1) = 'A'")->fetch()['c'];
    Assert::true(!$byPosition->truncated, 'sanity check : panier "9-lettres, A en 3e position" reste sous le plafond');
    Assert::same($expectedByPosition, $byPosition->total);
    foreach ($byPosition->items as $item) {
        Assert::same(9, $item['length']);
        Assert::same('A', mb_substr($item['normalized'], 2, 1, 'UTF-8'), $item['normalized'] . ' doit avoir A en 3e position (index 2, 0-based)');
    }

    $positionWithPrefix = $solver->solve('9-lettres/commencant/c/position/3/a');
    Assert::notNull($positionWithPrefix);
    $expectedPositionWithPrefix = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized >= 'C' AND normalized < 'D' AND substr(normalized, 3, 1) = 'A'")->fetch()['c'];
    Assert::same($expectedPositionWithPrefix, $positionWithPrefix->total);
    foreach ($positionWithPrefix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'C'));
        Assert::same('A', mb_substr($item['normalized'], 2, 1, 'UTF-8'));
    }

    // --- Collapse des positions degenerees (D-023) : position 1 et position = longueur
    // --- doivent produire EXACTEMENT le meme resultat que commencant/terminant seuls. ---
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

    // --- Prefixe/suffixe multi-lettres (2 a 4 lettres). ---
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

    // =====================================================================
    // Regressions rangeBounds()/ALPHABET_ORDER -- SPECIFIQUES A CE DEPOT : Ñ trie APRES Z
    // (verifie sur la base reelle), pas l'inverse. Z n'est donc PLUS traite comme la
    // derniere lettre de l'alphabet (contrairement au site francais, qui s'arrete a Z) --
    // c'etait un bug reel trouve et corrige avant tout import (sans le correctif, un
    // prefixe/suffixe finissant par Z produisait une plage SANS borne superieure et
    // incluait a tort tous les mots commencant par Ñ).
    // =====================================================================

    // Prefixe Z SEUL doit desormais avoir une borne superieure (Ñ) : ne doit PLUS inclure
    // les mots commencant par Ñ (805 mots, deja verifie independamment par
    // WordListFiltersTest.php-equivalent -- ici verifie bout en bout via le vrai solveur).
    $prefixZ = $solver->solve('commencant/z');
    Assert::notNull($prefixZ);
    $bruteForceZBounded = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Z' AND normalized < 'Ñ'")->fetch()['c'];
    $bruteForceZUnbounded = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Z'")->fetch()['c'];
    Assert::true($bruteForceZUnbounded > $bruteForceZBounded, 'sanity check : il doit exister au moins un mot commencant par Ñ pour que ce test ait un sens');
    Assert::same($bruteForceZBounded, $prefixZ->total, 'commencant/z ne doit JAMAIS inclure les mots commencant par Ñ (bug reel trouve et corrige)');
    foreach ($prefixZ->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'Z'), $item['normalized'] . ' ne devrait jamais apparaitre dans commencant/z');
    }

    // Prefixe Ñ (la vraie derniere lettre de l'alphabet sur cette colonne) : doit rester
    // sans borne superieure (rien ne trie apres Ñ), comme Z le faisait a tort sur le site
    // francais -- meme mecanisme de repli (upper = null), juste la bonne lettre desormais.
    $prefixEnye = $solver->solve('commencant/ñ');
    Assert::notNull($prefixEnye);
    $bruteForceEnye = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Ñ'")->fetch()['c'];
    Assert::same($bruteForceEnye, $prefixEnye->total, 'commencant/ñ (derniere lettre de l\'alphabet effectif) doit trouver tous les mots commencant par Ñ');
    Assert::same(805, $prefixEnye->total, 'sanity check : 805 mots commencant par Ñ, verifie directement sur la base');
    foreach ($prefixEnye->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'Ñ'));
    }

    // Prefixe ET suffixe combines pres de la frontiere Z/Ñ : le suffixe doit rester
    // correctement borne (pas de pollution par les mots en Ñ), meme raisonnement que
    // "commencant/z/terminant/s" du site francais mais avec la bonne borne ici.
    $zPrefixSSuffix = $solver->solve('commencant/z/terminant/s');
    Assert::notNull($zPrefixSSuffix);
    $bruteForceZS = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'Z' AND normalized < 'Ñ' AND normalized LIKE '%S'")->fetch()['c'];
    Assert::same($bruteForceZS, $zPrefixSSuffix->total, 'correction verifiee par force brute (Ñ est la vraie borne superieure, pas Z)');
    foreach ($zPrefixSSuffix->items as $item) {
        Assert::true(str_starts_with($item['normalized'], 'Z') && str_ends_with($item['normalized'], 'S'));
    }
    Assert::same(1, $zPrefixSSuffix->queryCount, 'prefixe+suffixe d\'une seule lettre chacun : egalite combinee, 1 seule requete fusionnee');

    // Prefixe purement fait de Ñ (motif degenere symetrique au "ZZZZ" du site francais) :
    // doit rester correct (panier vide ou non selon les donnees reelles), jamais une erreur.
    $prefixEnyeEnye = $solver->solve('commencant/ññññ');
    Assert::notNull($prefixEnyeEnye);
    $bruteForceEnyeEnye = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'ÑÑÑÑ' AND normalized < 'ÑÑÑÑ' || 'A'")->fetch()['c'];
    Assert::same(0, $bruteForceEnyeEnye, 'sanity check : aucun mot espagnol ne commence par ÑÑÑÑ dans cette base');
    Assert::same(0, $prefixEnyeEnye->total, 'panier vide, pas une erreur, meme cas degenere que ZZZZ sur le site francais');

    // =====================================================================
    // Collapse "avec/X" redondant avec un commencant/terminant d'une seule lettre X,
    // pour toutes les lettres de l'alphabet EFFECTIF (A-Z puis Ñ) -- verifie ici via le
    // VRAI solveur, pas seulement le parsing (deja couvert par WordListFiltersTest.php).
    // =====================================================================
    foreach (array_merge(range('A', 'Z'), ['Ñ']) as $x) {
        $lowerX = mb_strtolower($x, 'UTF-8');
        $degeneratePrefix = $solver->solve('commencant/' . $lowerX . '/avec/' . $lowerX);
        $simplePrefix = $solver->solve('commencant/' . $lowerX);
        Assert::notNull($degeneratePrefix);
        Assert::notNull($simplePrefix);
        Assert::same($simplePrefix->total, $degeneratePrefix->total, "commencant/$x/avec/$x doit avoir le meme total que commencant/$x seul");
        Assert::same($simplePrefix->truncated, $degeneratePrefix->truncated, "commencant/$x/avec/$x : meme statut truncated que commencant/$x seul");
        Assert::same($simplePrefix->canonicalPath, $degeneratePrefix->canonicalPath, "commencant/$x/avec/$x doit collapser vers le meme canonicalPath que commencant/$x");
        Assert::same($simplePrefix->queryCount, $degeneratePrefix->queryCount, "commencant/$x/avec/$x : meme budget de requetes que commencant/$x seul");

        $degenerateSuffix = $solver->solve('terminant/' . $lowerX . '/avec/' . $lowerX);
        $simpleSuffix = $solver->solve('terminant/' . $lowerX);
        Assert::notNull($degenerateSuffix);
        Assert::notNull($simpleSuffix);
        Assert::same($simpleSuffix->total, $degenerateSuffix->total, "terminant/$x/avec/$x doit avoir le meme total que terminant/$x seul");
        Assert::same($simpleSuffix->truncated, $degenerateSuffix->truncated, "terminant/$x/avec/$x : meme statut truncated que terminant/$x seul");
        Assert::same($simpleSuffix->canonicalPath, $degenerateSuffix->canonicalPath, "terminant/$x/avec/$x doit collapser vers le meme canonicalPath que terminant/$x");
        Assert::same($simpleSuffix->queryCount, $degenerateSuffix->queryCount, "terminant/$x/avec/$x : meme budget de requetes que terminant/$x seul");
    }

    // Cas emblematique : A, prefixe le plus frequent de la base espagnole (115 806 mots,
    // verifie -- PAS R comme sur le site francais, les statistiques de frequence des
    // lettres different reellement d'une langue a l'autre et ne doivent jamais etre
    // recopiees sans revérification).
    $worstCaseA = $solver->solve('commencant/a/avec/a');
    Assert::notNull($worstCaseA);
    $bruteForceA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'A' AND normalized < 'B'")->fetch()['c'];
    Assert::same(115806, $bruteForceA, 'sanity check : A doit rester le prefixe le plus frequent mesure sur cette base (115 806), sinon ce test ne prouve plus rien');
    Assert::same($bruteForceA, $worstCaseA->total, 'commencant/a/avec/a doit renvoyer le vrai total exact, jamais plafonne');
    Assert::true(!$worstCaseA->truncated);
    Assert::true($worstCaseA->exact, 'regime EXACT retrouve une fois le avec redondant retire');
    Assert::same('commencant/a', $worstCaseA->canonicalPath);

    // --- Non-regression : lettre "avec" DIFFERENTE du prefixe -- doit rester en regime BORNE
    // --- plafonne exactement comme avant (vrai predicat, jamais retire). ---
    $filtersNonRedundant = WordListFilters::fromPath('commencant/r/avec/y');
    Assert::notNull($filtersNonRedundant);
    Assert::same(['Y' => 1], $filtersNonRedundant->withLetters, 'avec/y non redondant avec commencant/r : jamais retire');

    $realConstraintPrefix = $solver->solve('commencant/r/avec/y');
    Assert::notNull($realConstraintPrefix);
    $bruteForceRY = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'R' AND normalized < 'S' AND instr(normalized, 'Y') > 0")->fetch()['c'];
    Assert::true(!$realConstraintPrefix->truncated, 'sanity check : commencant/r/avec/y (' . $bruteForceRY . ' correspondances) doit rester sous le plafond');
    Assert::same($bruteForceRY, $realConstraintPrefix->total, 'total correct pour un "avec" non redondant, jamais collapse');

    // --- Non-regression : minCount >= 2 pour la meme lettre que le prefixe -- jamais retire. ---
    $minCountTwoPrefix = $solver->solve('commencant/a/avec/a/a');
    Assert::notNull($minCountTwoPrefix);
    $bruteForceAA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized >= 'A' AND normalized < 'B' AND (LENGTH(normalized) - LENGTH(REPLACE(normalized, 'A', ''))) >= 2")->fetch()['c'];
    Assert::true($bruteForceAA > WordListSolver::ROW_EXAMINATION_CEILING, 'sanity check : avec/a/a doit reellement depasser le plafond pour que ce test ait un sens, obtenu ' . $bruteForceAA);
    Assert::true($minCountTwoPrefix->truncated, 'avec/a/a (minCount=2) reste un vrai predicat non collapse : panier reellement au-dessus du plafond');
    Assert::same(WordListSolver::ROW_EXAMINATION_CEILING, $minCountTwoPrefix->total, 'total plafonne, jamais le vrai total exact -- preuve que ce cas n\'est PAS collapse comme avec/a (minCount=1) l\'est');
    Assert::true($minCountTwoPrefix->total < $worstCaseA->total, 'exiger un deuxieme A doit reellement restreindre le panier par rapport a commencant/a seul');

    // =====================================================================
    // Ñ combine a d'autres contraintes (prefixe/suffixe/motif/avec), verifie bout en bout
    // via le vrai solveur -- complement de WordListFiltersTest.php (qui ne fait aucun
    // acces base).
    // =====================================================================
    $motifEnye = $solver->solve('4-lettres/motif/a-ñ-');
    Assert::notNull($motifEnye);
    $bruteForceMotifEnye = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 4 AND substr(normalized, 1, 1) = 'A' AND substr(normalized, 3, 1) = 'Ñ'")->fetch()['c'];
    Assert::same($bruteForceMotifEnye, $motifEnye->total, 'motif avec Ñ comme case connue APRES la premiere case inconnue doit rester correct (bug le plus severe trouve, position et lettre corrompues avant correctif)');
    foreach ($motifEnye->items as $item) {
        Assert::same(4, $item['length']);
        Assert::same('A', mb_substr($item['normalized'], 0, 1, 'UTF-8'));
        Assert::same('Ñ', mb_substr($item['normalized'], 2, 1, 'UTF-8'));
    }

    $suggestWithEnye = $solver->solve('avec/ñ/sans/a');
    Assert::notNull($suggestWithEnye);
    foreach ($suggestWithEnye->items as $item) {
        Assert::true(str_contains($item['normalized'], 'Ñ') && !str_contains($item['normalized'], 'A'));
    }
};
