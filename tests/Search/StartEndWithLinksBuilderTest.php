<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LengthLinksBuilder;
use App\Search\StartEndWithLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\StartEndWithLinksBuilder (maillage commencant+terminant+avec, tache 2026-08-18,
 * voir reports/query-plans/commencant-terminant-avec-maillage.md) : depuis une page
 * /mots/commencant/{X}/terminant/{Y} (deja indexee, Family::WORD_LIST_COMBINED, D-024/D-025),
 * liens vers chaque variante /mots/commencant/{X}/terminant/{Y}/avec/{Z} qui a au moins un
 * resultat -- lu depuis list_counts (list_type 'start_end_with', precalcule par
 * scripts/build_explore_hub_counts.php), jamais un calcul sur `terms` au runtime. Verifie par
 * force brute sur la vraie base (lecture seule), meme methodologie que
 * LengthCombinedLinksBuilderTest.php / AvecTwoLettersLinksBuilderTest.php, PLUS une verification
 * exhaustive du maillage sur les 611 pages sources reelles (pas un echantillon).
 *
 * CORRECTIF (D-032, correction du defaut "avec/X redondant avec commencant/X ou terminant/X
 * d'une seule lettre" -- WordListFilters::fromPath()) : list_counts 'start_end_with' contient,
 * par construction du precalcul (count_chars() liste TOUTES les lettres distinctes du mot,
 * debut et fin inclus, sans exclusion), 1 198 lignes DEGENEREES ou la lettre "avec" vaut la
 * lettre de debut ou de fin elle-meme (ex. "R:E:R", "R:E:E") -- leur compte est alors toujours
 * egal au total du panier commencant+terminant, pas une vraie restriction. Depuis que
 * WordListFilters::fromPath() collapse silencieusement "avec/X" dans ce cas (D-032),
 * WordListFilters::fromPath('commencant/r/terminant/e/avec/r')?->canonicalUrl() renvoie
 * desormais la MEME URL que la page parente elle-meme -- StartEndWithLinksBuilder::build()
 * detecte ce cas et EXCLUT deliberement ces lettres degenerees de sa sortie (sinon deux lettres
 * "avec" differentes menant chacune vers la MEME URL que leur page source, un doublon trompeur).
 * Tests ci-dessous ajustes en consequence : les lignes degenerees restent verifiees comme
 * REELLEMENT presentes dans list_counts (le precalcul lui-meme n'a pas change), mais absentes de
 * la sortie du builder -- exclusion deliberee et verifiee, jamais une perte de donnees
 * accidentelle.
 *
 * CORRECTIF 2 (audit consolide 2026-08-18, NO GO) : au-dela des lignes DEGENEREES ci-dessus (URL
 * enfant IDENTIQUE a l'URL parente), 227 lignes 'start_end_with' non degenerees produisent une
 * URL DIFFERENTE de la page parente mais un CONTENU strictement identique -- TOUS les mots de la
 * paire commencant+terminant contiennent deja cette lettre "avec" (ex. F:Q ne contient que FAQ :
 * "avec/a" ne retire aucun mot). Voir StartEndWithLinksBuilder::DUPLICATE_CONTENT_KEYS pour la
 * regle de detection et les deux methodes de verification independantes. Tests ci-dessous
 * etendus en consequence : ces 227 lignes restent verifiees comme REELLEMENT presentes dans
 * list_counts (non degenerees au sens D-032), mais absentes de la sortie du builder.
 *
 * CORRECTIF 3 (3e audit consolide, 2026-08-19, NO GO) : 333 lignes 'start_end_with' -- ni
 * degenerees (D-032), ni doublons-parent (CORRECTIF 2/D-037), ni doublons-soeurs (D-038) --
 * produisent neanmoins un contenu strictement identique a une page d'une AUTRE famille :
 * /mots/{N}-lettres/commencant/{X}/terminant/{Y} (App\Search\LengthLinksBuilder::byStartEnd,
 * Family::WORD_LIST_COMBINED). Exemple : la paire X:M (2 mots, XALAM et XENODOCHIUM) --
 * "/mots/5-lettres/commencant/x/terminant/m" et "/mots/commencant/x/terminant/m/avec/a"
 * contiennent toutes deux EXACTEMENT {XALAM}. Voir StartEndWithLinksBuilder::
 * CROSS_DUPLICATE_LENGTH_KEYS et reports/query-plans/avec-doublons-croises-longueur-correctif.md.
 * Regle de priorite : la variante LONGUEUR (axe 1) reste candidate, la variante "avec" (axe 2,
 * ce builder) est retiree ici -- LengthLinksBuilder n'est pas modifie.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new StartEndWithLinksBuilder($connection);

    // --- Cas representatif : la plus grande paire commencant+terminant de la base (R:E,
    // --- 20 979 mots, D-025bis) -- chaque lien verifie par force brute (substr() double +
    // --- instr()), plus l'URL canonique attendue. ---
    $linksRE = $builder->build('R', 'E');
    Assert::same(1, $linksRE->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($linksRE->links !== [], 'sanity check : R:E (20 979 mots) doit avoir des lettres "avec" presentes');

    foreach ($linksRE->links as $link) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = ? AND substr(reversed, 1, 1) = ? AND instr(normalized, ?) > 0'
        );
        $stmt->execute(['R', 'E', $link['letter']]);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], "R:E avec {$link['letter']} : verifie par force brute");
        Assert::same('/mots/commencant/r/terminant/e/avec/' . strtolower($link['letter']), $link['url']);
    }

    // --- Cas degenere (D-032, correctif applique en aval de WordListFilters::fromPath() --
    // --- collapse "avec/X" quand X est deja garanti par un commencant/terminant d'une seule
    // --- lettre, meme mecanisme que "position", D-023) : la lettre "avec" est l'une des deux
    // --- lettres deja garanties par construction (R au debut, E a la fin) -- ces deux lettres
    // --- SONT presentes dans list_counts (le script de precalcul liste toutes les lettres
    // --- distinctes du mot, debut et fin inclus, sans exclusion cote precalcul), mais
    // --- StartEndWithLinksBuilder::build() les detecte desormais (URL degenere vers celle de la
    // --- page parente elle-meme) et les EXCLUT de $links -- sinon deux lettres "avec"
    // --- differentes (R et E) meneraient chacune vers la MEME URL que leur propre page source,
    // --- un doublon trompeur. Verifie ici que ni R ni E n'apparaissent comme lettre "avec" dans
    // --- le maillage produit, bien qu'ils existent reellement dans list_counts (preuve que
    // --- l'exclusion est deliberee, pas une perte de donnees accidentelle).
    $totalRE = (int) $pdo->query(
        "SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = 'R' AND substr(reversed, 1, 1) = 'E'"
    )->fetch()['c'];
    $letterR = array_values(array_filter($linksRE->links, static fn (array $l): bool => $l['letter'] === 'R'));
    $letterE = array_values(array_filter($linksRE->links, static fn (array $l): bool => $l['letter'] === 'E'));
    Assert::true($letterR === [], 'R (lettre de debut garantie) ne doit plus apparaitre comme lettre "avec" -- URL degeneree vers la page parente elle-meme');
    Assert::true($letterE === [], 'E (lettre de fin garantie) ne doit plus apparaitre comme lettre "avec" -- URL degeneree vers la page parente elle-meme');

    $rawCountR = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'start_end_with' AND list_key = 'R:E:R'")->fetch()['count'];
    $rawCountE = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'start_end_with' AND list_key = 'R:E:E'")->fetch()['count'];
    Assert::same($totalRE, $rawCountR, 'sanity check : R:E:R existe bien dans list_counts (precalcul brut, aucune exclusion cote precalcul) et vaut le total du panier');
    Assert::same($totalRE, $rawCountE, 'sanity check : R:E:E existe bien dans list_counts (precalcul brut) et vaut le total du panier');

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ($linksRE->links as $link) {
        Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
    }

    // --- Tri alphabetique par lettre "avec". ---
    for ($i = 1; $i < count($linksRE->links); $i++) {
        Assert::true($linksRE->links[$i - 1]['letter'] < $linksRE->links[$i]['letter'], 'ordre alphabetique attendu');
    }

    // ============================================================================================
    // Doublons de CONTENU (CORRECTIF 2, audit consolide 2026-08-18, NO GO) : les deux exemples
    // cites tels quels par l'audit, reproduits directement contre la vraie base.
    // ============================================================================================

    // --- F:Q (longueur 3) ne contient que FAQ : "avec/a" est un doublon de contenu strict de la
    // --- page parente /mots/commencant/f/terminant/q -- jamais produit par le builder. ---
    $linksFQ = $builder->build('F', 'Q');
    Assert::same(1, $linksFQ->queryCount);
    $letterA = array_values(array_filter($linksFQ->links, static fn (array $l): bool => $l['letter'] === 'A'));
    Assert::true($letterA === [], 'F:Q:A est un doublon de contenu (FAQ est le seul mot, "avec/a" ne retire rien) -- ne doit jamais etre produit');

    $rawCountFQA = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'start_end_with' AND list_key = 'F:Q:A'")->fetch()['count'];
    $rawCountFQ = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'start_end' AND list_key = 'F:Q'")->fetch()['count'];
    Assert::same(1, $rawCountFQ, 'sanity check : F:Q ne contient bien que FAQ (1 mot)');
    Assert::same($rawCountFQ, $rawCountFQA, 'sanity check : F:Q:A existe dans list_counts (non degenere, F et A sont distincts) et vaut le meme total que F:Q');

    // --- X:O (longueur 5) ne contient que XIPHO : "avec/h", "avec/i" et "avec/p" sont TOUTES des
    // --- doublons de contenu strict, entre elles ET avec la page parente -- aucune des trois ne
    // --- doit jamais etre produite par le builder. ---
    $linksXO = $builder->build('X', 'O');
    Assert::same(1, $linksXO->queryCount);
    foreach (['H', 'I', 'P'] as $degenerateLetter) {
        $found = array_values(array_filter($linksXO->links, static function (array $l) use ($degenerateLetter): bool {
            return $l['letter'] === $degenerateLetter;
        }));
        Assert::true($found === [], "X:O:{$degenerateLetter} est un doublon de contenu (XIPHO est le seul mot) -- ne doit jamais etre produit");

        $rawCount = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'start_end_with' AND list_key = 'X:O:{$degenerateLetter}'")->fetch()['count'];
        Assert::same(1, $rawCount, "sanity check : X:O:{$degenerateLetter} existe dans list_counts (non degenere) et vaut 1");
    }

    $rawCountXO = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'start_end' AND list_key = 'X:O'")->fetch()['count'];
    Assert::same(1, $rawCountXO, 'sanity check : X:O ne contient bien que XIPHO (1 mot)');

    // ============================================================================================
    // Reflection sur la liste figee des 227 doublons de contenu (CORRECTIF 2) -- meme pattern deja
    // accepte sur ce projet pour verifier un detail d'implementation prive (voir
    // LengthLinksBuilderTest.php, DUPLICATE_START_END_KEYS).
    // ============================================================================================
    $reflection = new ReflectionClass(StartEndWithLinksBuilder::class);
    $duplicateContentKeys = $reflection->getConstant('DUPLICATE_CONTENT_KEYS');
    Assert::same(227, count($duplicateContentKeys), 'exactement 227 triples doublons de contenu attendus (audit consolide 2026-08-18)');
    Assert::same(count($duplicateContentKeys), count(array_unique($duplicateContentKeys)), 'aucun doublon dans la liste figee elle-meme');
    Assert::true(in_array('F:Q:A', $duplicateContentKeys, true), 'F:Q:A doit figurer dans la liste figee (exemple cite par l\'audit)');
    Assert::true(in_array('X:O:H', $duplicateContentKeys, true), 'X:O:H doit figurer dans la liste figee (exemple cite par l\'audit)');
    Assert::true(in_array('X:O:I', $duplicateContentKeys, true), 'X:O:I doit figurer dans la liste figee (exemple cite par l\'audit)');
    Assert::true(in_array('X:O:P', $duplicateContentKeys, true), 'X:O:P doit figurer dans la liste figee (exemple cite par l\'audit)');

    // --- Verification independante de la liste figee, recalculee depuis list_counts (0
    // --- divergence attendue dans les deux sens) : une entree start_end_with NON degeneree
    // --- (D-032) est un doublon de contenu si et seulement si son compte est EGAL au compte
    // --- start_end (sans lettre "avec") correspondant -- meme regle que DUPLICATE_START_END_KEYS
    // --- (LengthLinksBuilderTest.php), appliquee ici a start_end_with/start_end. ---
    $startEndTotalsForContent = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end'") as $row) {
        $startEndTotalsForContent[(string) $row['list_key']] = (int) $row['count'];
    }

    $computedContentDuplicates = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end_with'") as $row) {
        $key = (string) $row['list_key'];
        [$start, $end, $letter] = explode(':', $key, 3);

        if ($letter === $start || $letter === $end) {
            continue; // degenere D-032, hors perimetre de DUPLICATE_CONTENT_KEYS
        }

        $count = (int) $row['count'];

        if (($startEndTotalsForContent[$start . ':' . $end] ?? null) === $count) {
            $computedContentDuplicates[] = $key;
        }
    }

    sort($computedContentDuplicates);
    $sortedDeclaredContent = $duplicateContentKeys;
    sort($sortedDeclaredContent);
    Assert::same($sortedDeclaredContent, $computedContentDuplicates, 'DUPLICATE_CONTENT_KEYS doit etre identique aux doublons recalcules depuis list_counts (0 divergence, pas un echantillon)');

    // ============================================================================================
    // Doublons de contenu entre pages SOEURS "avec" (I-A, 2e audit consolide, 2026-08-18, GO avec
    // ce point non bloquant) -- reports/query-plans/avec-doublons-soeurs-correctif.md.
    // ============================================================================================
    $siblingDuplicateKeys = $reflection->getConstant('SIBLING_DUPLICATE_KEYS');
    Assert::same(428, count($siblingDuplicateKeys), 'exactement 428 cles doublons soeurs attendues (283 groupes, 169 paires affectees)');
    Assert::same(count($siblingDuplicateKeys), count(array_unique($siblingDuplicateKeys)), 'aucun doublon dans la liste figee elle-meme (une lettre n\'appartient qu\'a un seul groupe)');
    $siblingDuplicateSet = array_fill_keys($siblingDuplicateKeys, true);
    // Verification de disjonction avec $degenerateKeys/$contentDuplicateKeys, et recalcul complet
    // independant (methodes A/B) : voir plus bas, une fois ces deux ensembles disponibles
    // (declares dans le bloc "Verification EXHAUSTIVE" ci-dessous).

    // --- Exemple cite par la tache (paire A:B, 6 mots : AB, ACHEB, AEROCLUB, ANTIPUB, APLOMB,
    // --- AUTOLUB) : "avec/c" et "avec/e" isolent EXACTEMENT le meme sous-ensemble {ACHEB,
    // --- AEROCLUB} -- C (plus petite lettre) reste candidate, E est exclue. ---
    Assert::true(in_array('A:B:E', $siblingDuplicateKeys, true), 'A:B:E doit figurer dans la liste figee (C et E isolent le meme sous-ensemble {ACHEB, AEROCLUB})');
    $linksAB = $builder->build('A', 'B');
    $letterC = array_values(array_filter($linksAB->links, static fn (array $l): bool => $l['letter'] === 'C'));
    $letterEinAB = array_values(array_filter($linksAB->links, static fn (array $l): bool => $l['letter'] === 'E'));
    Assert::true($letterC !== [], 'C (lettre canonique du groupe, la plus petite alphabetiquement) doit rester dans la sortie du builder');
    Assert::true($letterEinAB === [], 'E (doublon soeur de C sur A:B) ne doit plus etre produite par le builder');
    $wordsAB = $pdo->query(
        "SELECT normalized FROM terms WHERE substr(normalized,1,1) = 'A' AND substr(reversed,1,1) = 'B' ORDER BY normalized"
    )->fetchAll(PDO::FETCH_COLUMN);
    Assert::same(['AB', 'ACHEB', 'AEROCLUB', 'ANTIPUB', 'APLOMB', 'AUTOLUB'], $wordsAB, 'sanity check : panier A:B exact, verifie sur pieces');
    $subsetC = array_values(array_filter($wordsAB, static fn (string $w): bool => str_contains($w, 'C')));
    $subsetE = array_values(array_filter($wordsAB, static fn (string $w): bool => str_contains($w, 'E')));
    Assert::same(['ACHEB', 'AEROCLUB'], $subsetC, 'sanity check : sous-ensemble "avec c" de A:B');
    Assert::same(['ACHEB', 'AEROCLUB'], $subsetE, 'sanity check : sous-ensemble "avec e" de A:B -- identique au sous-ensemble "avec c"');

    // --- Cas rare, panier de 4 mots (cas representatif non nul du balayage complet,
    // --- reports/query-plans/commencant-terminant-avec-full-sweep.md). ---
    $linksZO = $builder->build('Z', 'O');
    Assert::same(1, $linksZO->queryCount);
    foreach ($linksZO->links as $link) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = ? AND substr(reversed, 1, 1) = ? AND instr(normalized, ?) > 0'
        );
        $stmt->execute(['Z', 'O', $link['letter']]);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], "Z:O avec {$link['letter']} : verifie par force brute");
        Assert::same('/mots/commencant/z/terminant/o/avec/' . strtolower($link['letter']), $link['url']);
    }

    // --- Paire absente de start_end (0 resultat, ex. commencant/x/terminant/q -- combinaison
    // --- jamais mesuree non vide dans D-024) : aucun lien, jamais une erreur. ---
    $linksAbsent = $builder->build('X', 'Q');
    Assert::same(1, $linksAbsent->queryCount, 'meme cout meme sans aucun resultat');
    $stmtAbsent = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = 'X' AND substr(reversed, 1, 1) = 'Q'");
    $stmtAbsent->execute();
    if ((int) $stmtAbsent->fetch()['c'] === 0) {
        Assert::same([], $linksAbsent->links, 'paire commencant+terminant a 0 resultat : aucun lien "avec" possible');
    }

    // ============================================================================================
    // Verification EXHAUSTIVE du maillage sur les 611 pages sources reelles (list_type
    // 'start_end', D-024) -- pas un echantillon, meme discipline que tous les paliers precedents
    // (D-027/D-028bis byStartEnd, D-030/D-031 "avec"). Compare le vrai code (StartEndWithLinksBuilder,
    // via list_counts) au recalcul independant depuis list_counts lui-meme -- prouve que le
    // builder reproduit EXACTEMENT les lignes precalculees NI degenerees (D-032) NI doublons de
    // contenu (CORRECTIF 2), dans les DEUX sens : aucun lien produit qui n'existe pas dans
    // list_counts (sens 1), aucune ligne list_counts eligible jamais oubliee par le builder
    // (sens 2). Les lignes DEGENEREES (URL identique a la page parente, D-032) ET les lignes
    // DOUBLONS DE CONTENU (URL differente mais contenu identique, CORRECTIF 2) sont exclues
    // DELIBEREMENT : verifiees ci-dessous comme systematiquement absentes de la sortie, jamais
    // comme un manque accidentel.
    // ============================================================================================
    $pairsStatement = $pdo->query("SELECT list_key FROM list_counts WHERE list_type = 'start_end'");
    $pairs = [];
    foreach ($pairsStatement as $row) {
        $pairs[] = explode(':', (string) $row['list_key'], 2);
    }
    Assert::same(611, count($pairs), 'sanity check : 611 paires commencant+terminant reelles (D-024)');

    $expectedStatement = $pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end_with'");
    $expected = [];
    $degenerateKeys = [];
    foreach ($expectedStatement as $row) {
        $key = (string) $row['list_key'];
        $expected[$key] = (int) $row['count'];

        [$start, $end, $letter] = explode(':', $key, 3);
        if ($letter === $start || $letter === $end) {
            $degenerateKeys[$key] = true;
        }
    }
    Assert::same(11348, count($expected), 'sanity check : 11 348 lignes start_end_with reelles, precalcul brut inchange (commencant-terminant-avec-maillage.md)');
    Assert::same(1198, count($degenerateKeys), 'sanity check : 1 198 lignes degenerees (lettre "avec" = debut ou fin) parmi les 11 348, mesure directe D-032');

    $contentDuplicateKeys = array_fill_keys($duplicateContentKeys, true);
    Assert::same(0, count(array_intersect_key($degenerateKeys, $contentDuplicateKeys)), 'DUPLICATE_CONTENT_KEYS et les lignes degenerees D-032 doivent rester deux ensembles disjoints (perimetres distincts par construction)');

    // ========================================================================================
    // Doublons de contenu entre pages SOEURS "avec" (I-A, 2e audit consolide, 2026-08-18) --
    // disjonction avec les deux ensembles precedents, PUIS recalcul independant et EXHAUSTIF
    // (pas un echantillon) sur les 611 paires reelles, DEUX methodes independantes sur le MEME
    // panier de mots recupere une seule fois par paire :
    //   A) egalite d'ensemble DIRECTE : cle = liste triee des mots concernes, jointe par un
    //      separateur non ambigu -- comparaison de chaines completes, jamais un hash approximatif
    //   B) propriete de COINCIDENCE, algorithme different (suggere par l'audit, I-A) : union-find
    //      sur la relation "presence(Z1) == presence(Z2) pour tous les mots du panier"
    // Les deux methodes doivent produire EXACTEMENT les memes classes d'equivalence entre elles,
    // ET EXACTEMENT la meme liste d'exclusions (lettre gardee = la plus petite de chaque classe)
    // que SIBLING_DUPLICATE_KEYS.
    // ========================================================================================
    Assert::same(0, count(array_intersect_key($siblingDuplicateSet, $degenerateKeys)), 'SIBLING_DUPLICATE_KEYS et les lignes degenerees D-032 doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($siblingDuplicateSet, $contentDuplicateKeys)), 'SIBLING_DUPLICATE_KEYS et DUPLICATE_CONTENT_KEYS (doublon PARENT) doivent rester deux ensembles disjoints -- perimetres distincts par construction (un doublon soeur ne peut jamais aussi etre un doublon parent : son compte est strictement inferieur au panier complet)');

    $computedSiblingExcluded = [];
    $methodDivergences = 0;

    foreach ($pairs as [$start, $end]) {
        $candidateStatement = $pdo->prepare(
            "SELECT list_key FROM list_counts WHERE list_type = 'start_end_with' AND list_key LIKE ?"
        );
        $candidateStatement->execute(["{$start}:{$end}:%"]);

        $candidateLetters = [];
        foreach ($candidateStatement as $row) {
            $key = (string) $row['list_key'];
            if (isset($degenerateKeys[$key]) || isset($contentDuplicateKeys[$key])) {
                continue; // deja exclues par les deux filtres precedents, hors perimetre ici
            }
            [, , $letter] = explode(':', $key, 3);
            $candidateLetters[] = $letter;
        }

        if (count($candidateLetters) < 2) {
            continue; // aucun regroupement possible avec moins de 2 lettres candidates
        }

        $basketStmt = $pdo->prepare(
            'SELECT normalized FROM terms WHERE substr(normalized, 1, 1) = ? AND substr(reversed, 1, 1) = ?'
        );
        $basketStmt->execute([$start, $end]);
        $basket = $basketStmt->fetchAll(PDO::FETCH_COLUMN);
        sort($basket);

        // Methode A : egalite d'ensemble directe.
        $bySetKey = [];
        foreach ($candidateLetters as $letter) {
            $subset = array_values(array_filter($basket, static fn (string $w): bool => str_contains($w, $letter)));
            $bySetKey[implode("\x1F", $subset)][] = $letter;
        }
        $groupsA = [];
        foreach ($bySetKey as $lettersInGroup) {
            if (count($lettersInGroup) >= 2) {
                sort($lettersInGroup);
                $groupsA[] = $lettersInGroup;
            }
        }

        // Methode B : propriete de coincidence, union-find (algorithme different).
        $parentOf = array_combine($candidateLetters, $candidateLetters);
        $find = static function (string $x) use (&$parentOf, &$find): string {
            return $parentOf[$x] === $x ? $x : ($parentOf[$x] = $find($parentOf[$x]));
        };
        $n = count($candidateLetters);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $l1 = $candidateLetters[$i];
                $l2 = $candidateLetters[$j];
                $tied = true;
                foreach ($basket as $w) {
                    if (str_contains($w, $l1) !== str_contains($w, $l2)) {
                        $tied = false;
                        break;
                    }
                }
                if ($tied) {
                    $r1 = $find($l1);
                    $r2 = $find($l2);
                    if ($r1 !== $r2) {
                        $parentOf[$r1] = $r2;
                    }
                }
            }
        }
        $classes = [];
        foreach ($candidateLetters as $letter) {
            $classes[$find($letter)][] = $letter;
        }
        $groupsB = [];
        foreach ($classes as $lettersInGroup) {
            if (count($lettersInGroup) >= 2) {
                sort($lettersInGroup);
                $groupsB[] = $lettersInGroup;
            }
        }

        $normA = $groupsA;
        sort($normA);
        $normB = $groupsB;
        sort($normB);
        if ($normA !== $normB) {
            $methodDivergences++;
        }

        foreach ($groupsA as $group) {
            foreach (array_slice($group, 1) as $excludedLetter) {
                $computedSiblingExcluded[] = "{$start}:{$end}:{$excludedLetter}";
            }
        }
    }

    Assert::same(0, $methodDivergences, 'methode A (egalite directe) et methode B (union-find coincidence) doivent produire les memes groupes sur les 611 paires, 0 divergence');

    sort($computedSiblingExcluded);
    $sortedDeclaredSibling = $siblingDuplicateKeys;
    sort($sortedDeclaredSibling);
    Assert::same($sortedDeclaredSibling, $computedSiblingExcluded, 'SIBLING_DUPLICATE_KEYS doit etre identique aux doublons soeurs recalcules depuis `terms` (0 divergence, 611 paires, pas un echantillon)');

    // ========================================================================================
    // Doublons de contenu CROISES avec l'AUTRE famille (longueur, App\Search\LengthLinksBuilder::
    // byStartEnd) -- CORRECTIF 3, 3e audit consolide, 2026-08-19. Disjonction avec les trois
    // ensembles precedents (degeneres D-032, doublons-parent D-037, doublons-soeurs D-038), PUIS
    // recalcul independant et EXHAUSTIF (pas un echantillon) sur les 611 paires reelles, DEUX
    // methodes independantes sur le MEME panier recupere une seule fois par paire (meme discipline
    // que le bloc SIBLING_DUPLICATE_KEYS ci-dessus, methode A/B sur un panier partage) :
    //   1) egalite d'ensemble DIRECTE, memes VRAIS builders que le runtime -- LengthLinksBuilder
    //      pour les 14 longueurs (2 a 15, deja purge des 52 doublons D-025), lettres "avec"
    //      candidates deja purgees des trois filtres precedents -- tranche par longueur (strlen)
    //      et par lettre (str_contains), comparaison de tableau complete (===)
    //   2) egalite par COMPTES CROISES, algorithme different (meme principe que la verification
    //      SQL independante utilisee pour decouvrir cette liste, reports/query-plans/
    //      avec-doublons-croises-longueur-correctif.md section 3, methode 2 : COUNT(length=L) ==
    //      COUNT(contient Z) == COUNT(length=L ET contient Z) -- ici recalcule par intersection de
    //      tableaux plutot que par egalite structurelle, jamais le meme code que la methode 1)
    // Verification SQL ADDITIONNELLE et INDEPENDANTE (fraiche requete, hors PHP) : voir
    // reports/query-plans/avec-doublons-croises-longueur-correctif.md, section 3 methode 2 --
    // executee une fois pour chacun des 333 couples trouves (333/333 confirmes, 0 divergence),
    // non repetee ici pour rester dans un budget de test raisonnable (101 383 couples au total).
    // ========================================================================================
    $crossDuplicateKeys = $reflection->getConstant('CROSS_DUPLICATE_LENGTH_KEYS');
    Assert::same(333, count($crossDuplicateKeys), 'exactement 333 doublons croises attendus (3e audit consolide, 2026-08-19)');
    Assert::same(count($crossDuplicateKeys), count(array_unique($crossDuplicateKeys)), 'aucun doublon dans la liste figee elle-meme');
    $crossDuplicateSet = array_fill_keys($crossDuplicateKeys, true);
    Assert::same(0, count(array_intersect_key($crossDuplicateSet, $degenerateKeys)), 'CROSS_DUPLICATE_LENGTH_KEYS et les lignes degenerees D-032 doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($crossDuplicateSet, $contentDuplicateKeys)), 'CROSS_DUPLICATE_LENGTH_KEYS et DUPLICATE_CONTENT_KEYS (doublon PARENT) doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($crossDuplicateSet, $siblingDuplicateSet)), 'CROSS_DUPLICATE_LENGTH_KEYS et SIBLING_DUPLICATE_KEYS (doublon SOEUR) doivent rester deux ensembles disjoints');

    // --- Exemple cite par la tache (paire X:M, 2 mots au total : XALAM, XENODOCHIUM) --
    // --- "/mots/5-lettres/commencant/x/terminant/m" (axe 1) et "/mots/commencant/x/terminant/m/
    // --- avec/a" (axe 2, A = lettre canonique survivante du groupe soeur {A,L}, D-038)
    // --- contiennent toutes deux EXACTEMENT {XALAM}. ---
    Assert::true(in_array('X:M:A', $crossDuplicateKeys, true), 'X:M:A doit figurer dans la liste figee (exemple cite par la tache)');
    $linksXM = $builder->build('X', 'M');
    $letterAinXM = array_values(array_filter($linksXM->links, static fn (array $l): bool => $l['letter'] === 'A'));
    Assert::true($letterAinXM === [], 'X:M:A (doublon croise avec /mots/5-lettres/commencant/x/terminant/m) ne doit plus etre produite par le builder');
    $wordsXM = $pdo->query(
        "SELECT normalized FROM terms WHERE substr(normalized,1,1) = 'X' AND substr(reversed,1,1) = 'M' ORDER BY normalized"
    )->fetchAll(PDO::FETCH_COLUMN);
    Assert::same(['XALAM', 'XENODOCHIUM'], $wordsXM, 'sanity check : panier X:M exact, verifie sur pieces');

    // --- Axe 1 survivant au moment ou CROSS_DUPLICATE_LENGTH_KEYS a ete calculee (D-039,
    // --- 2026-08-19) : liste list_counts 'length_start_end' purgee UNIQUEMENT des 52 doublons
    // --- D-025 (DUPLICATE_START_END_KEYS) -- PAS du filtre EXTERNAL_DUPLICATE_KEYS (D-041,
    // --- 2026-08-21, App\Search\LengthCombinedLinksBuilder), ajoute APRES coup et pour une raison
    // --- sans rapport (doublon avec une TROISIEME famille, pas avec l'axe 2 "avec" verifie ici).
    // --- Lire list_counts directement plutot que d'appeler LengthLinksBuilder::build()->byStartEnd
    // --- (qui applique DESORMAIS aussi EXTERNAL_DUPLICATE_KEYS) est le seul moyen de garder cette
    // --- verification historique decouplee du nouveau filtre D-041, orthogonal a celle-ci. ---
    $axis1Lengths = [];
    $duplicateStartEndKeys = (new ReflectionClass(LengthLinksBuilder::class))->getConstant('DUPLICATE_START_END_KEYS');
    foreach ($pdo->query("SELECT list_key FROM list_counts WHERE list_type = 'length_start_end'") as $row) {
        $key = (string) $row['list_key'];

        if (in_array($key, $duplicateStartEndKeys, true)) {
            continue;
        }

        [$length, $start, $end] = explode(':', $key, 3);
        $axis1Lengths[$start . ':' . $end][(int) $length] = true;
    }

    $crossCandidateStmt = $pdo->prepare(
        "SELECT list_key FROM list_counts WHERE list_type = 'start_end_with' AND list_key LIKE ?"
    );
    $crossBasketStmt = $pdo->prepare(
        'SELECT normalized FROM terms WHERE substr(normalized, 1, 1) = ? AND substr(reversed, 1, 1) = ? ORDER BY normalized'
    );

    $computedCrossExcluded = [];
    $crossMethodDivergences = 0;

    foreach ($pairs as [$start, $end]) {
        $lengthsForPair = $axis1Lengths["{$start}:{$end}"] ?? [];

        $crossCandidateStmt->execute(["{$start}:{$end}:%"]);
        $candidateLettersForCross = [];
        foreach ($crossCandidateStmt as $row) {
            $key = (string) $row['list_key'];
            if (isset($degenerateKeys[$key]) || isset($contentDuplicateKeys[$key]) || isset($siblingDuplicateSet[$key])) {
                continue; // deja exclues par les trois filtres precedents, hors perimetre ici
            }
            [, , $letter] = explode(':', $key, 3);
            $candidateLettersForCross[] = $letter;
        }

        if ($lengthsForPair === [] || $candidateLettersForCross === []) {
            continue; // aucune comparaison possible sans survivant des deux cotes
        }

        $crossBasketStmt->execute([$start, $end]);
        $basket2 = $crossBasketStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach (array_keys($lengthsForPair) as $length) {
            $lengthSlice = array_values(array_filter($basket2, static fn (string $w): bool => strlen($w) === $length));

            foreach ($candidateLettersForCross as $letter) {
                $letterSlice = array_values(array_filter($basket2, static fn (string $w): bool => str_contains($w, $letter)));

                // Methode 1 : egalite d'ensemble directe (tableaux deja tries par construction,
                // le panier source est ORDER BY normalized).
                $method1Match = count($lengthSlice) === count($letterSlice) && $lengthSlice === $letterSlice;

                // Methode 2 : egalite par comptes croises -- algorithme different (intersection
                // plutot que comparaison structurelle sequentielle) : COUNT(longueur) ==
                // COUNT(lettre) == COUNT(les deux a la fois) implique une egalite d'ensemble
                // (sous-ensemble dans les deux sens), sans jamais utiliser l'operateur === sur les
                // tableaux complets comme la methode 1.
                $intersectCount = count(array_intersect($lengthSlice, $letterSlice));
                $method2Match = count($lengthSlice) > 0
                    && count($lengthSlice) === count($letterSlice)
                    && count($letterSlice) === $intersectCount;

                if ($method1Match !== $method2Match) {
                    $crossMethodDivergences++;
                }

                if ($method1Match && $method2Match) {
                    $computedCrossExcluded[] = "{$start}:{$end}:{$letter}";
                }
            }
        }
    }

    Assert::same(0, $crossMethodDivergences, 'methode 1 (egalite directe) et methode 2 (comptes croises par intersection) doivent s\'accorder sur les 611 paires, 0 divergence');

    sort($computedCrossExcluded);
    $sortedDeclaredCross = $crossDuplicateKeys;
    sort($sortedDeclaredCross);
    Assert::same($sortedDeclaredCross, $computedCrossExcluded, 'CROSS_DUPLICATE_LENGTH_KEYS doit etre identique aux doublons croises recalcules depuis `terms` (0 divergence, 611 paires, pas un echantillon)');

    // ========================================================================================
    // Doublons de contenu CROISES avec une famille EXTERIEURE (D-041, balayage generique du
    // 2026-08-21, scripts/check_combinatorial_duplicates.php) -- meme pattern de verification que
    // les quatre blocs precedents : reflection sur la liste figee, puis disjonction avec les
    // ensembles deja exclus.
    // ========================================================================================
    $externalDuplicateKeys = $reflection->getConstant('EXTERNAL_DUPLICATE_KEYS');
    Assert::same(314, count($externalDuplicateKeys), 'exactement 314 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');
    Assert::same(count($externalDuplicateKeys), count(array_unique($externalDuplicateKeys)), 'aucun doublon dans la liste figee elle-meme');
    $externalDuplicateSet = array_fill_keys($externalDuplicateKeys, true);
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, $degenerateKeys)), 'EXTERNAL_DUPLICATE_KEYS et les lignes degenerees D-032 doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, $contentDuplicateKeys)), 'EXTERNAL_DUPLICATE_KEYS et DUPLICATE_CONTENT_KEYS doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, $siblingDuplicateSet)), 'EXTERNAL_DUPLICATE_KEYS et SIBLING_DUPLICATE_KEYS doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, $crossDuplicateSet)), 'EXTERNAL_DUPLICATE_KEYS et CROSS_DUPLICATE_LENGTH_KEYS doivent rester deux ensembles disjoints');

    $excludedKeys = $degenerateKeys + $contentDuplicateKeys + $siblingDuplicateSet + $crossDuplicateSet + $externalDuplicateSet;

    $producedKeys = [];
    $totalLinksProduced = 0;
    $exactlyOne = 0;

    foreach ($pairs as [$start, $end]) {
        $links = $builder->build($start, $end);
        Assert::same(1, $links->queryCount, "queryCount doit rester 1 pour {$start}:{$end}");

        $previousLetter = null;
        foreach ($links->links as $link) {
            $key = $start . ':' . $end . ':' . $link['letter'];

            Assert::true(isset($expected[$key]), "cle produite par le builder absente de list_counts : {$key}");
            Assert::true(!isset($degenerateKeys[$key]), "cle degeneree produite par erreur, aurait du etre exclue (D-032) : {$key}");
            Assert::true(!isset($contentDuplicateKeys[$key]), "cle doublon de contenu produite par erreur, aurait du etre exclue (CORRECTIF 2) : {$key}");
            Assert::true(!isset($siblingDuplicateSet[$key]), "cle doublon soeur produite par erreur, aurait du etre exclue (I-A) : {$key}");
            Assert::true(!isset($crossDuplicateSet[$key]), "cle doublon croise (longueur) produite par erreur, aurait du etre exclue (CORRECTIF 3) : {$key}");
            Assert::true(!isset($externalDuplicateSet[$key]), "cle doublon croise (famille exterieure) produite par erreur, aurait du etre exclue (D-041) : {$key}");
            Assert::same($expected[$key], $link['count'], "compte divergent pour {$key}");
            Assert::same(
                '/mots/commencant/' . strtolower($start) . '/terminant/' . strtolower($end) . '/avec/' . strtolower($link['letter']),
                $link['url'],
                "URL divergente pour {$key}"
            );
            Assert::true($link['count'] > 0, "R5 : aucune entree a 0 attendue ({$key})");

            if ($previousLetter !== null) {
                Assert::true($previousLetter < $link['letter'], "ordre alphabetique attendu pour {$start}:{$end}");
            }
            $previousLetter = $link['letter'];

            Assert::true(!isset($producedKeys[$key]), "doublon de lien produit : {$key}");
            $producedKeys[$key] = true;
            $totalLinksProduced++;

            if ($link['count'] === 1) {
                $exactlyOne++;
            }
        }
    }

    // Sens 1 -> 2 : chaque lien produit par le vrai code correspond a une ligne list_counts
    // eligible reelle (ni degeneree, ni doublon de contenu PARENT, ni doublon de contenu SOEUR),
    // sans exception, sans doublon, sans lien mort, sans jamais reproduire une ligne exclue --
    // deja verifie ligne par ligne ci-dessus. Volume total : exactement les lignes eligibles, ni
    // plus ni moins.
    $expectedEligibleCount = count($expected) - count($excludedKeys);
    Assert::same(8848, $expectedEligibleCount, 'sanity check : 8 848 lignes eligibles (11 348 brutes - 1 198 degenerees D-032 - 227 doublons de contenu PARENT D-037 - 428 doublons de contenu SOEUR I-A - 333 doublons de contenu CROISES CORRECTIF 3 - 314 doublons croises famille exterieure D-041)');
    Assert::same($expectedEligibleCount, $totalLinksProduced, 'total des liens produits doit egaler les lignes list_counts start_end_with eligibles (ni degenerees ni doublons de contenu parent/soeur/croise)');
    Assert::same($expectedEligibleCount, count($producedKeys), 'aucun doublon, chaque cle eligible produite une seule fois');

    // Sens 2 -> 1, en DEUX temps : chaque ligne list_counts start_end_with eligible reelle est
    // produite par le builder depuis sa page source reelle (les 611 pages, pas un echantillon) ;
    // chaque ligne EXCLUE (degeneree D-032, doublon de contenu PARENT D-037, doublon de contenu
    // SOEUR I-A, ou doublon de contenu CROISE CORRECTIF 3), a l'inverse, n'est JAMAIS produite
    // (exclusion deliberee, verifiee explicitement plutot que supposee).
    foreach (array_keys($expected) as $key) {
        if (isset($excludedKeys[$key])) {
            Assert::true(!isset($producedKeys[$key]), "ligne exclue (degeneree ou doublon de contenu parent/soeur/croise) produite par erreur : {$key}");
        } else {
            Assert::true(isset($producedKeys[$key]), "ligne list_counts eligible jamais produite par le builder depuis sa page source : {$key}");
        }
    }

    // Consigne produit deja connue (rapport de balayage complet, commencant-terminant-avec-full-
    // sweep.md), AJUSTEE apres exclusion des lignes degenerees (D-032), PUIS des doublons de
    // contenu PARENT (D-037), PUIS des doublons de contenu SOEUR (I-A), PUIS des doublons de
    // contenu CROISES (CORRECTIF 3), PUIS des doublons croises famille exterieure (D-041) :
    // 1 638 combinaisons a exactement 1 resultat parmi les 11 348 lignes precalculees brutes,
    // dont 91 degenerees (D-032), 162 doublons de contenu parent a exactement 1 resultat (parmi
    // les 227, ex. F:Q:A -- FAQ est le seul mot ET F:Q ne contient qu'un mot), 325 doublons de
    // contenu soeur a exactement 1 resultat (parmi les 428, ex. X:M:D/E/H/I/N/O/U -- XENODOCHIUM
    // est le seul mot du sous-ensemble partage par ces sept lettres), 306 doublons de contenu
    // croises a exactement 1 resultat (parmi les 333, ex. X:M:A -- XALAM est le seul mot partage
    // avec la page longueur), et 306 doublons croises famille exterieure a exactement 1 resultat
    // (parmi les 314, D-041) -- 1 638 - 91 - 162 - 325 - 306 - 306 = 448 restantes, effectivement
    // produites par le builder.
    $expectedExactlyOneEligible = 0;
    foreach ($expected as $key => $count) {
        if ($count === 1 && !isset($excludedKeys[$key])) {
            $expectedExactlyOneEligible++;
        }
    }
    Assert::same(448, $expectedExactlyOneEligible, 'sanity check : 448 combinaisons eligibles a exactement 1 resultat (1 638 brutes - 91 degenerees - 162 doublons de contenu parent - 325 doublons de contenu soeur - 306 doublons de contenu croises - 306 doublons croises famille exterieure)');
    Assert::same($expectedExactlyOneEligible, $exactlyOne, 'consigne produit deja connue (GARDEES, meme consigne que tous les paliers "avec" precedents)');
};
