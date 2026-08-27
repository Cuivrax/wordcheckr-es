<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache de mesure "avec + longueur, 3 lettres" -- palier 3
 * de l'ouverture progressive en entonnoir de la famille "avec") -- PAS un script de build, ne
 * modifie rien, aucune ecriture sur storage/dictionary_fr.sqlite ni storage/seo_fr.sqlite (lecture
 * seule). Balaie la TOTALITE des combinaisons reelles de /mots/{N}-lettres/avec/{X}/{Y}/{Z}
 * (longueur explicite + EXACTEMENT trois lettres "avec" DISTINCTES, chacune minCount=1) via le
 * vrai solveur de production (App\Search\WordListFilters::fromPath() + App\Search\WordListSolver::
 * solve()), jamais une requete SQL reconstruite a cote -- seule exception : EXPLAIN QUERY PLAN, qui
 * rejoue la MEME requete que solveBounded() en invoquant par reflexion les methodes privees
 * anchorClause()/extraPredicates(), pour ne pas dupliquer la logique de construction SQL. Meme
 * discipline que scripts/bench_avec_length_2letters_full_sweep.php (D-030, palier 2).
 *
 * Perimetre : pour chaque longueur L de 2 a 15 (14 valeurs), pour chaque TRIPLET de lettres
 * distinctes A-Z (C(26,3) = 2600) -- 14 x 2600 = 36400 combinaisons au maximum, verifie par ce
 * meme script (voir le compteur imprime). Les cas "avec" a une ou deux lettres (paliers 1/2, deja
 * mesures) et le cas general "avec" (multiset non borne, NEVER_SITEMAP permanent) sont HORS
 * PERIMETRE de ce balayage.
 *
 * UN SEUL balayage complet cette fois (demande produit explicite, 2026-08-18) : le bruit de
 * mesure observe sur le palier 2 (D-030) a ete trace a une contention entre agents concurrents
 * lisant/ecrivant storage/dictionary_fr.sqlite en parallele, pas a la requete elle-meme (EXPLAIN
 * QUERY PLAN identique et stable sur toutes les executions du palier 2). Aucun autre agent
 * n'ecrit sur ce fichier pendant cette tache -- un seul balayage propre suffit.
 *
 * Verifie EN PLUS (pas seulement la performance) la couverture du maillage interne reel, DANS LES
 * TROIS SENS possibles (contrairement au palier 2, qui n'en avait que deux -- une paire source a
 * TROIS positions possibles dans le triplet trie stocke, voir App\Search\
 * AvecThreeLettersLinksBuilder pour le detail) : compare l'univers list_counts (list_type =
 * 'length_with_triple', source de App\Search\AvecThreeLettersLinksBuilder) a l'univers reellement
 * trouve par le vrai solveur, puis verifie que CHAQUE triplet eligible recoit bien un lien depuis
 * SES TROIS pages source palier 2 (avec/{X}/{Y}, avec/{X}/{Z} ET avec/{Y}/{Z}), pas seulement une
 * ou deux des trois.
 *
 * Chiffre EN PLUS la surface de pagination (I-2, condition posee par l'audit seo-technical-auditor
 * sur D-030 avant toute proposition de palier 3) : pour chaque page eligible (total >= 1), le
 * nombre de pages /page/N que la pagination generait -- ceil(min(total, 10000) / 50) -- somme sur
 * les ~36 400 combinaisons de ce palier.
 *
 * Usage : php scripts/bench_avec_length_3letters_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_avec_length_3letters_full_sweep.php ne s'execute qu'en CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\AvecThreeLettersLinksBuilder;
use App\Search\WordListFilters;
use App\Search\WordListSolver;

const PAGE_SIZE_FOR_PAGINATION = 50;
const CEILING_FOR_PAGINATION = 10_000;

$config = Config::load('fr');

if (!is_file($config->dictionaryPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$config->dictionaryPath}\n");
    exit(1);
}

$alphabet = str_split('abcdefghijklmnopqrstuvwxyz');

$combos = [];
for ($length = 2; $length <= 15; $length++) {
    for ($i = 0; $i < count($alphabet); $i++) {
        for ($j = $i + 1; $j < count($alphabet); $j++) {
            for ($k = $j + 1; $k < count($alphabet); $k++) {
                $combos[] = [$length, $alphabet[$i], $alphabet[$j], $alphabet[$k]];
            }
        }
    }
}

fwrite(STDERR, sprintf("combinaisons reelles construites : %d\n", count($combos)));

/**
 * Meme reconstruction que scripts/bench_avec_length_2letters_full_sweep.php (voir ce fichier pour
 * le commentaire complet) -- dupliquee ici volontairement : scripts jetables independants, pas de
 * bibliotheque partagee a creer pour une mesure ponctuelle.
 *
 * @return array{sql: string, params: list<int|string>, anchorType: string, order: string}
 */
function reconstructBoundedQuery(WordListSolver $solver, WordListFilters $filters): array
{
    $ref = new ReflectionClass($solver);
    $anchorMethod = $ref->getMethod('anchorClause');
    $extraMethod = $ref->getMethod('extraPredicates');

    [$anchorWhere, $anchorParams, $anchorOrder, $anchorType] = $anchorMethod->invoke($solver, $filters);
    [$extraWhere, $extraParams] = $extraMethod->invoke($solver, $filters, $anchorType);

    $conditions = array_values(array_filter([$anchorWhere, $extraWhere], static fn (string $c): bool => $c !== ''));
    $whereSql = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
    $params = [...$anchorParams, ...$extraParams];

    if ($anchorOrder === 'normalized') {
        $sql = "SELECT normalized, score, length, is_ods8, is_ods9 FROM terms $whereSql ORDER BY normalized LIMIT ?";
        $params[] = WordListSolver::ROW_EXAMINATION_CEILING + 1;
    } else {
        $sql = "SELECT COUNT(*) c FROM (SELECT id FROM terms $whereSql ORDER BY $anchorOrder LIMIT ?)";
        $params[] = WordListSolver::ROW_EXAMINATION_CEILING + 1;
    }

    return ['sql' => $sql, 'params' => $params, 'anchorType' => $anchorType, 'order' => $anchorOrder];
}

/**
 * @param list<int|string> $params
 * @return list<array<string, mixed>>
 */
function explainPlan(\PDO $pdo, string $sql, array $params): array
{
    $statement = $pdo->prepare('EXPLAIN QUERY PLAN ' . $sql);
    $statement->execute($params);

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
}

// Chauffe le cache avant toute mesure retenue (TTFB "chaud", CLAUDE.md). 80 requetes dispersees
// sur la plage reelle (36 400 combinaisons), jamais comptabilisees.
$warmupSamples = [];
for ($i = 0; $i < 80 && $i < count($combos); $i++) {
    $warmupSamples[] = $combos[(int) ($i * count($combos) / 80)];
}
foreach ($warmupSamples as [$length, $l1, $l2, $l3]) {
    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);
    $solver->solve("{$length}-lettres/avec/{$l1}/{$l2}/{$l3}");
}

$results = [];
$anomalies = [];
$zeroResultCount = 0;
$oneResultCount = 0;
$paginationPages = 0;

$sweepStart = microtime(true);

foreach ($combos as [$length, $l1, $l2, $l3]) {
    $rawPath = "{$length}-lettres/avec/{$l1}/{$l2}/{$l3}";

    $filters = WordListFilters::fromPath($rawPath);
    if ($filters === null) {
        $anomalies[] = "fromPath() a renvoye null pour {$rawPath}";
        continue;
    }

    // Sanity check syntaxique : le palier 3 doit toujours produire exactement une longueur +
    // trois lettres "avec" DISTINCTES a occurrence unique (minCount=1 chacune), rien d'autre.
    $withLettersCopy = $filters->withLetters;
    if ($filters->length !== $length || count($withLettersCopy) !== 3 || array_sum($withLettersCopy) !== 3) {
        $anomalies[] = "filtre inattendu pour {$rawPath} : " . json_encode($filters->withLetters);
        continue;
    }

    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);

    $start = hrtime(true);
    $page = $solver->solve($rawPath);
    $elapsedMs = (hrtime(true) - $start) / 1_000_000.0;

    if ($page === null) {
        $anomalies[] = "solve() a renvoye null pour {$rawPath}";
        continue;
    }

    if ($page->canonicalPath !== $rawPath) {
        $anomalies[] = "canonicalPath divergent pour {$rawPath} : {$page->canonicalPath}";
    }

    if ($page->total === 0) {
        $zeroResultCount++;
    } else {
        if ($page->total === 1) {
            $oneResultCount++;
        }

        // Surface de pagination (I-2) : $page->total EST DEJA min(compte reel, ROW_EXAMINATION_CEILING)
        // en regime BORNE (WordListSolver::solveBounded(), anchorOrder === 'normalized' : LIMIT
        // CEILING + 1 puis array_pop() -- voir WordListSolver.php) -- exactement la meme donnee que
        // le formulaire demande, aucun recalcul necessaire.
        $paginationPages += (int) ceil(min($page->total, CEILING_FOR_PAGINATION) / PAGE_SIZE_FOR_PAGINATION);
    }

    $results[] = [
        'path' => $rawPath,
        'length' => $length,
        'letter1' => strtoupper($l1),
        'letter2' => strtoupper($l2),
        'letter3' => strtoupper($l3),
        'elapsedMs' => $elapsedMs,
        'total' => $page->total,
        'truncated' => $page->truncated,
        'queryCount' => $page->queryCount,
    ];
}

$sweepElapsedS = microtime(true) - $sweepStart;

// Dump CSV optionnel (variable d'environnement BENCH_CSV_OUT), pour analyse hors ligne du detail
// complet -- jamais active par defaut, jamais lu par aucun autre script.
$csvOut = getenv('BENCH_CSV_OUT');
if ($csvOut !== false && $csvOut !== '') {
    $fh = fopen($csvOut, 'w');
    fputcsv($fh, ['length', 'letter1', 'letter2', 'letter3', 'elapsedMs', 'total', 'truncated', 'queryCount'], escape: '\\');
    foreach ($results as $r) {
        fputcsv($fh, [$r['length'], $r['letter1'], $r['letter2'], $r['letter3'], $r['elapsedMs'], $r['total'], $r['truncated'] ? 1 : 0, $r['queryCount']], escape: '\\');
    }
    fclose($fh);
    fwrite(STDERR, "CSV ecrit : {$csvOut}\n");
}

// --- Statistiques ---

$elapsedList = array_map(static fn (array $r): float => $r['elapsedMs'], $results);
sort($elapsedList);
$n = count($elapsedList);

function percentile(array $sorted, float $p): float
{
    $n = count($sorted);
    if ($n === 0) {
        return NAN;
    }
    $rank = (int) ceil($p / 100.0 * $n);
    $rank = max(1, min($n, $rank));

    return $sorted[$rank - 1];
}

$min = $n > 0 ? $elapsedList[0] : NAN;
$max = $n > 0 ? $elapsedList[$n - 1] : NAN;
$p50 = percentile($elapsedList, 50);
$p95 = percentile($elapsedList, 95);
$p99 = percentile($elapsedList, 99);

$overBudget = array_values(array_filter($results, static fn (array $r): bool => $r['elapsedMs'] > 250.0));
usort($overBudget, static fn (array $a, array $b): int => $b['elapsedMs'] <=> $a['elapsedMs']);

usort($results, static fn (array $a, array $b): int => $b['elapsedMs'] <=> $a['elapsedMs']);
$worst = $results[0] ?? null;

$sortedByTime = $results;
usort($sortedByTime, static fn (array $a, array $b): int => $a['elapsedMs'] <=> $b['elapsedMs']);
$representative = $sortedByTime[(int) floor($n / 2)] ?? null;

$nonZero = array_values(array_filter($sortedByTime, static fn (array $r): bool => $r['total'] > 0));
$representativeNonZero = $nonZero[(int) floor(count($nonZero) / 2)] ?? null;

echo "=== BALAYAGE -- avec + longueur, 3 lettres (palier 3), complet ===\n";
printf("n=%d anomalies=%d duree totale du balayage=%.1fs\n", $n, count($anomalies), $sweepElapsedS);
printf("min=%.3f ms  p50=%.3f ms  p95=%.3f ms  p99=%.3f ms  max=%.3f ms\n", $min, $p50, $p95, $p99, $max);
printf("cas au-dessus de 250 ms : %d / %d\n", count($overBudget), $n);
printf("combinaisons a 0 resultat : %d / %d\n", $zeroResultCount, $n);
printf("combinaisons a exactement 1 resultat : %d / %d\n", $oneResultCount, $n);
printf("surface de pagination (palier 3 seul) : %d pages /page/N cumulees (ceil(min(total,10000)/50) par page eligible)\n", $paginationPages);

if ($anomalies !== []) {
    echo "\n--- ANOMALIES ---\n";
    foreach ($anomalies as $a) {
        echo $a . "\n";
    }
}

echo "\n--- CAS AU-DESSUS DE 250 ms ---\n";
if (count($overBudget) > 0 && count($overBudget) < 30) {
    foreach ($overBudget as $r) {
        printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $r['path'], $r['total'], $r['truncated'] ? 'oui' : 'non', $r['queryCount'], $r['elapsedMs']);
    }
} elseif (count($overBudget) >= 30) {
    echo "(20 pires uniquement, " . count($overBudget) . " au total)\n";
    foreach (array_slice($overBudget, 0, 20) as $r) {
        printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $r['path'], $r['total'], $r['truncated'] ? 'oui' : 'non', $r['queryCount'], $r['elapsedMs']);
    }
} else {
    echo "(aucun)\n";
}

echo "\n--- 20 PIRES CAS (toutes valeurs confondues) ---\n";
foreach (array_slice($results, 0, 20) as $r) {
    printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $r['path'], $r['total'], $r['truncated'] ? 'oui' : 'non', $r['queryCount'], $r['elapsedMs']);
}

// --- EXPLAIN QUERY PLAN : cas representatif + pire cas ---

$pdo = (new Connection($config->dictionaryPath))->pdo();

$dumpPlan = static function (string $label, ?array $case) use ($pdo, $config): void {
    if ($case === null) {
        return;
    }
    echo "\n--- EXPLAIN QUERY PLAN : {$label} ---\n";
    echo "/mots/{$case['path']}  ({$case['elapsedMs']} ms, total={$case['total']})\n";
    $filters = WordListFilters::fromPath($case['path']);
    $solver = new WordListSolver(new Connection($config->dictionaryPath));
    $q = reconstructBoundedQuery($solver, $filters);
    echo "SQL: {$q['sql']}\n";
    echo "anchorType={$q['anchorType']} order={$q['order']}\n";
    foreach (explainPlan($pdo, $q['sql'], $q['params']) as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
};

$dumpPlan('cas representatif (mediane brute, toutes valeurs confondues)', $representative);
$dumpPlan('cas representatif avec resultats non nuls (mediane du sous-ensemble > 0 resultat)', $representativeNonZero);
$dumpPlan('pire cas trouve', $worst);

// --- Couverture du maillage (AvecThreeLettersLinksBuilder), verifiee exhaustivement, DANS LES
// --- TROIS SENS (une paire source a TROIS positions possibles dans le triplet trie) ---
//
// Compare TROIS univers, tous les ~36 400 (ou moins si anomalies), pas un echantillon :
//   1. "live"        : combinaisons avec total >= 1 d'apres le vrai solveur (ci-dessus)
//   2. "list_counts" : cles list_type='length_with_triple' presentes dans
//      storage/dictionary_fr.sqlite (source de AvecThreeLettersLinksBuilder)
//   3. "linked"      : pour CHAQUE (longueur, lettre1, lettre2) du perimetre palier 2 (4 276
//      pages reellement appliquees, D-030), AvecThreeLettersLinksBuilder::build(longueur,
//      lettre1, lettre2) est appele -- verifie que CHAQUE triplet eligible apparait bien depuis
//      SES TROIS pages source palier 2 (les trois sous-paires du triplet), pas seulement une ou
//      deux des trois.

echo "\n=== COUVERTURE DU MAILLAGE (AvecThreeLettersLinksBuilder), TROIS SENS ===\n";

$liveNonZero = [];
foreach ($results as $r) {
    if ($r['total'] > 0) {
        $liveNonZero[$r['length'] . ':' . $r['letter1'] . ':' . $r['letter2'] . ':' . $r['letter3']] = true;
    }
}

$listCountsStatement = $pdo->prepare("SELECT list_key FROM list_counts WHERE list_type = 'length_with_triple'");
$listCountsStatement->execute();
$listCountsKeys = [];
foreach ($listCountsStatement as $row) {
    $listCountsKeys[(string) $row['list_key']] = true;
}
$listCountsKeysInScope = array_intersect_key($listCountsKeys, array_flip(array_map(
    static fn (array $r): string => $r['length'] . ':' . $r['letter1'] . ':' . $r['letter2'] . ':' . $r['letter3'],
    $results,
)));

// Univers des pages source palier 2 REELLEMENT appliquees (D-030) : list_counts, list_type
// 'length_with_pair' -- exactement les 4 276 pages deja indexees, pas un sous-ensemble arbitraire.
$palier2PairsStatement = $pdo->query("SELECT list_key FROM list_counts WHERE list_type = 'length_with_pair'");
$palier2Pairs = [];
foreach ($palier2PairsStatement as $row) {
    [$len, $a, $b] = explode(':', (string) $row['list_key'], 3);
    $palier2Pairs[] = [(int) $len, $a, $b];
}

fwrite(STDERR, sprintf("pages source palier 2 reellement appliquees (D-030) : %d\n", count($palier2Pairs)));

// Pour chaque triplet trouve, l'ensemble des "paires appelantes" (deux des trois lettres du
// triplet) qui ont effectivement mene a ce triplet via AvecThreeLettersLinksBuilder::build().
$linkedTagsByTriple = [];
$builderQueryCount = 0;
$linkedBuilder = new AvecThreeLettersLinksBuilder(new Connection($config->dictionaryPath));

foreach ($palier2Pairs as [$len, $a, $b]) {
    $links = $linkedBuilder->build($len, $a, $b);
    $builderQueryCount += $links->queryCount;

    $callingPair = [$a, $b];
    sort($callingPair, SORT_STRING);
    $callingTag = $callingPair[0] . $callingPair[1];

    foreach ($links->links as $link) {
        $triple = [$a, $b, $link['letter']];
        sort($triple, SORT_STRING);
        $key = $len . ':' . $triple[0] . ':' . $triple[1] . ':' . $triple[2];

        $linkedTagsByTriple[$key][$callingTag] = true;
    }
}

$linkedAllThreeSides = [];
$linkedPartialSides = [];
foreach ($linkedTagsByTriple as $key => $tags) {
    if (count($tags) === 3) {
        $linkedAllThreeSides[$key] = true;
    } else {
        $linkedPartialSides[$key] = $tags;
    }
}

$onlyLive = array_diff_key($liveNonZero, $listCountsKeysInScope);
$onlyListCounts = array_diff_key($listCountsKeysInScope, $liveNonZero);
$onlyLiveNotLinkedThreeSides = array_diff_key($liveNonZero, $linkedAllThreeSides);
$onlyLinkedNotLive = array_diff_key($linkedAllThreeSides, $liveNonZero);

printf("univers live (total >= 1, vrai solveur)                : %d\n", count($liveNonZero));
printf("univers list_counts (list_type='length_with_triple')   : %d\n", count($listCountsKeysInScope));
printf("univers linked, TROIS sens confirmes                   : %d\n", count($linkedAllThreeSides));
printf("triplets lies de MOINS de trois cotes (defaut partiel) : %d\n", count($linkedPartialSides));
printf("divergence live vs list_counts (live seul)              : %d\n", count($onlyLive));
printf("divergence live vs list_counts (list_counts seul)       : %d\n", count($onlyListCounts));
printf("divergence live vs linked trois sens (live non lie)     : %d\n", count($onlyLiveNotLinkedThreeSides));
printf("divergence live vs linked trois sens (lie non live)     : %d\n", count($onlyLinkedNotLive));
printf("requetes AvecThreeLettersLinksBuilder::build() cumulees sur les %d pages palier 2 : %d\n", count($palier2Pairs), $builderQueryCount);

if ($onlyLive !== []) {
    echo "--- LIVE SEUL (absent de list_counts, max 20 affiches) ---\n";
    foreach (array_slice(array_keys($onlyLive), 0, 20) as $k) {
        echo $k . "\n";
    }
}
if ($onlyListCounts !== []) {
    echo "--- LIST_COUNTS SEUL (absent du live, max 20 affiches) ---\n";
    foreach (array_slice(array_keys($onlyListCounts), 0, 20) as $k) {
        echo $k . "\n";
    }
}
if ($linkedPartialSides !== []) {
    echo "--- LIE DE MOINS DE TROIS COTES (defaut de couverture partiel, max 20 affiches) ---\n";
    foreach (array_slice($linkedPartialSides, 0, 20, true) as $k => $tags) {
        echo $k . ' : cotes trouves = ' . implode(',', array_keys($tags)) . "\n";
    }
}
if ($onlyLiveNotLinkedThreeSides !== []) {
    echo "--- LIVE NON LIE DES TROIS COTES (max 20 affiches) ---\n";
    foreach (array_slice(array_keys($onlyLiveNotLinkedThreeSides), 0, 20) as $k) {
        echo $k . "\n";
    }
}
if ($onlyLinkedNotLive !== []) {
    echo "--- LIE DES TROIS COTES MAIS NON LIVE (triplet a 0 resultat pourtant lie) ---\n";
    foreach (array_keys($onlyLinkedNotLive) as $k) {
        echo $k . "\n";
    }
}

echo "\nFIN\n";
