<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LengthCombinedLinksBuilder;
use App\Search\LengthLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\LengthLinksBuilder (D-022) : maillage interne des pages "mots de {N} lettres" --
 * lu depuis list_counts (precalcule par scripts/build_explore_hub_counts.php), jamais un
 * GROUP BY sur `terms` au runtime. Verifie par force brute sur la vraie base (lecture seule),
 * meme methodologie que WordListSolverTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new LengthLinksBuilder($connection);

    $links = $builder->build(13);

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts, jamais de GROUP BY sur terms');
    Assert::true($links->byStart !== [], 'sanity check : des mots de 13 lettres existent pour au moins une lettre de debut');
    Assert::true($links->byEnd !== []);
    Assert::true($links->byWith !== []);
    Assert::true($links->byPosition !== [], 'sanity check : longueur 13 a des positions 2..12');

    // --- byStart : verifie par force brute contre COUNT(*) reel, plus URL/tri. ---
    $expectedStartA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 13 AND normalized LIKE 'A%'")->fetch()['c'];
    $startA = array_values(array_filter($links->byStart, static fn (array $l): bool => $l['letter'] === 'A'));
    Assert::true(count($startA) === 1, 'une seule entree A attendue');
    Assert::same($expectedStartA, $startA[0]['count']);
    Assert::same('/mots/13-lettres/commencant/a', $startA[0]['url']);

    // --- byEnd : verifie par force brute sur normalized (pas besoin de reversed cote test). ---
    $expectedEndE = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 13 AND normalized LIKE '%E'")->fetch()['c'];
    $endE = array_values(array_filter($links->byEnd, static fn (array $l): bool => $l['letter'] === 'E'));
    Assert::true(count($endE) === 1);
    Assert::same($expectedEndE, $endE[0]['count']);
    Assert::same('/mots/13-lettres/terminant/e', $endE[0]['url']);

    // --- byWith : lettre presente n'importe ou dans le mot, pas seulement debut/fin --
    // --- verifie par force brute avec instr(). ---
    $expectedWithK = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 13 AND instr(normalized, 'K') > 0")->fetch()['c'];
    $withK = array_values(array_filter($links->byWith, static fn (array $l): bool => $l['letter'] === 'K'));
    Assert::true(count($withK) === 1);
    Assert::same($expectedWithK, $withK[0]['count']);
    Assert::same('/mots/13-lettres/avec/k', $withK[0]['url']);

    // --- byWith, doublon de contenu CROISE avec une famille EXTERIEURE (D-041, balayage du
    // --- 2026-08-21) : "2-lettres/avec/w" (WU, seul mot) perd face a "terminant/wu" (1 composant)
    // --- -- ne doit jamais etre produit par byWith depuis /mots/2-lettres. ---
    $externalWithKeys = (new ReflectionClass(LengthLinksBuilder::class))->getConstant('EXTERNAL_DUPLICATE_WITH_KEYS');
    Assert::same(['2:W'], $externalWithKeys, 'exactement 1 doublon croise avec une famille exterieure attendu pour byWith (D-041)');
    $links2 = $builder->build(2);
    $withW = array_values(array_filter($links2->byWith, static fn (array $l): bool => $l['letter'] === 'W'));
    Assert::true($withW === [], '2:W est un doublon de contenu croise (D-041, perd face a terminant/wu) -- ne doit jamais etre produit');
    $rawCount2W = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'length_with' AND list_key = '2:W'")->fetch()['count'];
    Assert::true($rawCount2W > 0, 'sanity check : 2:W existe bien dans list_counts (precalcul brut inchange, seule la sortie du builder est filtree)');

    // --- byPosition (C1, audit D-028) : groupe par position, verifie par force brute avec
    // --- substr(). ---
    $positionGroup9 = array_values(array_filter($links->byPosition, static fn (array $g): bool => $g['position'] === 9));
    Assert::true(count($positionGroup9) === 1);
    $letterR = array_values(array_filter($positionGroup9[0]['letters'], static fn (array $l): bool => $l['letter'] === 'R'));
    Assert::true(count($letterR) === 1);
    $expectedPos9R = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 13 AND substr(normalized, 9, 1) = 'R'")->fetch()['c'];
    Assert::same($expectedPos9R, $letterR[0]['count']);
    Assert::same('/mots/13-lettres/position/9/r', $letterR[0]['url']);

    // --- Aucune position degeneree (1 ou longueur) -- deja couvertes par byStart/byEnd. ---
    foreach ($links->byPosition as $group) {
        Assert::true($group['position'] > 1 && $group['position'] < 13, 'positions degenerees jamais dans byPosition');
    }

    // --- Ordre croissant par position, puis alphabetique par lettre dans chaque groupe. ---
    for ($i = 1; $i < count($links->byPosition); $i++) {
        Assert::true($links->byPosition[$i - 1]['position'] < $links->byPosition[$i]['position']);
    }

    foreach ($links->byPosition as $group) {
        for ($i = 1; $i < count($group['letters']); $i++) {
            Assert::true($group['letters'][$i - 1]['letter'] < $group['letters'][$i]['letter'], 'ordre alphabetique attendu dans chaque groupe de position');
        }

        foreach ($group['letters'] as $link) {
            Assert::true($link['count'] > 0, 'aucune entree a 0 attendue dans byPosition');
        }
    }

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0, meme pour une lettre
    // --- rare a une longueur donnee -- verifie sur toute la liste, pas seulement un echantillon. ---
    foreach ([$links->byStart, $links->byEnd, $links->byWith] as $group) {
        foreach ($group as $link) {
            Assert::true($link['count'] > 0, 'aucune entree a 0 attendue (list_counts n\'insere jamais de ligne vide)');
        }
    }

    // --- Tri alphabetique par lettre, dans chaque groupe. ---
    foreach ([$links->byStart, $links->byEnd, $links->byWith] as $group) {
        for ($i = 1; $i < count($group); $i++) {
            Assert::true($group[$i - 1]['letter'] < $group[$i]['letter'], 'ordre alphabetique attendu');
        }
    }

    // --- Longueur sans aucune correspondance plausible (hors bornes reelles, 2 est le minimum --
    // --- utilise ici une longueur valide mais dont on ne suppose rien d'autre) : le contrat
    // --- reste correct meme si un groupe est vide (pas d'exception, tableaux vides). ---
    $linksTwo = $builder->build(2);
    Assert::same(1, $linksTwo->queryCount);

    // =========================================================================================
    // byStartEnd (maillage commencant+terminant AVEC longueur, ce lot) : groupe par lettre de
    // debut, verifie exhaustivement contre list_counts (pas un echantillon), dans les deux sens
    // -- meme discipline que byPosition (D-028bis).
    // =========================================================================================

    Assert::true($links->byStartEnd !== [], 'sanity check : longueur 13 a des paires commencant/terminant');

    // --- Reflection sur la liste figee des 52 doublons (D-025, I-1) -- meme pattern deja
    // --- accepte sur ce projet pour verifier un detail d'implementation prive (reports/
    // --- query-plans/combined-with-length-full-sweep.md, EXPLAIN QUERY PLAN par reflexion). ---
    $reflection = new ReflectionClass(LengthLinksBuilder::class);
    $duplicateKeys = $reflection->getConstant('DUPLICATE_START_END_KEYS');
    Assert::same(52, count($duplicateKeys), 'exactement 52 paires dupliquees attendues (D-025, I-1)');
    Assert::same(count($duplicateKeys), count(array_unique($duplicateKeys)), 'aucun doublon dans la liste figee elle-meme');

    // --- Verification independante de la liste figee, recalculee depuis list_counts (0
    // --- divergence attendue dans les deux sens) : une entree length_start_end est un doublon
    // --- de contenu si et seulement si son compte est EGAL au compte start_end (sans longueur)
    // --- correspondant -- reproduit exactement la definition de D-025 ("tous les mots de la
    // --- paire partagent la meme longueur"). ---
    $startEndTotals = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end'") as $row) {
        $startEndTotals[$row['list_key']] = (int) $row['count'];
    }

    $computedDuplicates = [];
    $expectedLinks = [];

    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_start_end'") as $row) {
        $key = (string) $row['list_key'];
        [, $start, $end] = explode(':', $key, 3);
        $count = (int) $row['count'];

        if (($startEndTotals[$start . ':' . $end] ?? null) === $count) {
            $computedDuplicates[] = $key;
            continue;
        }

        $expectedLinks[$key] = $count;
    }

    sort($computedDuplicates);
    $sortedDeclared = $duplicateKeys;
    sort($sortedDeclared);
    Assert::same($sortedDeclared, $computedDuplicates, 'DUPLICATE_START_END_KEYS doit etre identique aux doublons recalcules depuis list_counts (0 divergence, pas un echantillon)');

    // ============================================================================================
    // Doublons de contenu CROISES avec une famille EXTERIEURE (D-041, garde-fou structurel demande
    // par le constat C-4 du 4e audit consolide, docs/DECISIONS.md D-040) -- balayage GENERIQUE de
    // tout le registre (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 :
    // 1 656 groupes, 2 089 pages en exces). Source de verite unique :
    // App\Search\LengthCombinedLinksBuilder::EXTERNAL_DUPLICATE_KEYS (partagee avec byStartEnd, qui
    // cible la MEME famille Family::WORD_LIST_COMBINED avec longueur depuis une page source
    // differente).
    // ============================================================================================
    $externalDuplicateKeys = LengthCombinedLinksBuilder::EXTERNAL_DUPLICATE_KEYS;
    Assert::same(292, count($externalDuplicateKeys), 'exactement 292 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');
    Assert::same(count($externalDuplicateKeys), count(array_unique($externalDuplicateKeys)), 'aucun doublon dans la liste figee elle-meme');
    $externalDuplicateSet = array_fill_keys($externalDuplicateKeys, true);
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, array_fill_keys($duplicateKeys, true))), 'EXTERNAL_DUPLICATE_KEYS et DUPLICATE_START_END_KEYS doivent rester deux ensembles disjoints');

    foreach ($expectedLinks as $key => $count) {
        if (isset($externalDuplicateSet[$key])) {
            unset($expectedLinks[$key]);
        }
    }

    // --- Balayage exhaustif des 14 longueurs reelles (2 a 15, D-010) : chaque lien produit par
    // --- byStartEnd correspond a une ligne list_counts length_start_end reelle non exclue, avec
    // --- le meme compte et la meme URL canonique ; chaque ligne non exclue apparait EXACTEMENT
    // --- une fois -- verifie dans les deux sens, jamais un echantillon. ---
    $producedLinks = [];

    for ($length = 2; $length <= 15; $length++) {
        $lengthLinksForSweep = $length === 13 ? $links : $builder->build($length);

        foreach ($lengthLinksForSweep->byStartEnd as $group) {
            Assert::true($group['letters'] !== [], 'aucun groupe vide attendu (lettre de debut sans terminant valide)');

            for ($i = 1; $i < count($group['letters']); $i++) {
                Assert::true($group['letters'][$i - 1]['letter'] < $group['letters'][$i]['letter'], 'ordre alphabetique attendu par lettre de fin, dans chaque groupe');
            }

            foreach ($group['letters'] as $link) {
                $key = $length . ':' . $group['start'] . ':' . $link['letter'];

                Assert::true(!isset($producedLinks[$key]), 'aucun lien en double attendu : ' . $key);
                Assert::true(!in_array($key, $duplicateKeys, true), 'lien exclu (doublon D-025) produit a tort : ' . $key);
                Assert::true(!isset($externalDuplicateSet[$key]), 'lien exclu (doublon croise D-041) produit a tort : ' . $key);
                Assert::true($link['count'] > 0, 'aucune entree a 0 attendue (list_counts n\'insere jamais de ligne vide)');
                Assert::same(
                    sprintf('/mots/%d-lettres/commencant/%s/terminant/%s', $length, strtolower($group['start']), strtolower($link['letter'])),
                    $link['url'],
                    'URL canonique attendue pour ' . $key,
                );

                $producedLinks[$key] = $link['count'];
            }
        }

        for ($i = 1; $i < count($lengthLinksForSweep->byStartEnd); $i++) {
            Assert::true($lengthLinksForSweep->byStartEnd[$i - 1]['start'] < $lengthLinksForSweep->byStartEnd[$i]['start'], 'ordre alphabetique attendu par lettre de debut, entre groupes');
        }
    }

    Assert::same(count($expectedLinks), count($producedLinks), 'meme nombre de liens produits (14 longueurs) que de lignes list_counts length_start_end non exclues');

    foreach ($expectedLinks as $key => $count) {
        Assert::true(isset($producedLinks[$key]), 'lien manquant, attendu depuis list_counts : ' . $key);
        Assert::same($count, $producedLinks[$key], 'compte divergent pour ' . $key);
    }
};
