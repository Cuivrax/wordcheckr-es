<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache de mesure "avec + longueur, 1 lettre" -- palier 1
 * de l'ouverture progressive en entonnoir de la famille "avec") -- PAS un script de build, ne
 * modifie rien, aucune ecriture sur storage/dictionary_fr.sqlite ni storage/seo_fr.sqlite (lecture
 * seule). Balaie la TOTALITE des combinaisons reelles de /mots/{N}-lettres/avec/{X} (longueur
 * explicite + EXACTEMENT une lettre "avec", occurrence unique, minCount=1) via le vrai solveur de
 * production (App\Search\WordListFilters::fromPath() + App\Search\WordListSolver::solve()),
 * jamais une requete SQL reconstruite a cote -- seule exception : EXPLAIN QUERY PLAN, qui rejoue
 * la MEME requete que solveBounded() en invoquant par reflexion les methodes privees
 * anchorClause()/extraPredicates(), pour ne pas dupliquer la logique de construction SQL. Meme
 * discipline que scripts/bench_position_full_sweep.php et
 * scripts/bench_combined_length_full_sweep.php (D-028, D-027).
 *
 * Perimetre : pour chaque longueur L de 2 a 15 (14 valeurs), pour chaque lettre A-Z (26) --
 * 14 x 26 = 364 combinaisons au maximum, verifie par ce meme script (voir le compteur imprime).
 * Le cas "avec" SANS longueur (Family::WORD_LIST_AVEC, non ancre, NEVER_SITEMAP permanent) est
 * HORS PERIMETRE de ce balayage -- jamais touche ici.
 *
 * Verifie EN PLUS (pas seulement la performance) la couverture du maillage interne reel : compare
 * l'univers list_counts (list_type = 'length_with', source de App\Search\LengthLinksBuilder::
 * build()->byWith, deja cablee depuis /mots/{N}-lettres, Family::WORD_LIST_LENGTH, deja indexee
 * D-017) a l'univers reellement trouve par le vrai solveur -- meme methode de verification que
 * celle qui a servi pour "position" (reports/query-plans/position-full-sweep.md,
 * reports/query-plans/position-length-maillage.md).
 *
 * Usage : php scripts/bench_avec_length_1letter_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_avec_length_1letter_full_sweep.php ne s'execute qu'en CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\LengthLinksBuilder;
use App\Search\WordListFilters;
use App\Search\WordListSolver;

$config = Config::load('fr');

if (!is_file($config->dictionaryPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$config->dictionaryPath}\n");
    exit(1);
}

$alphabet = str_split('abcdefghijklmnopqrstuvwxyz');

$combos = [];
for ($length = 2; $length <= 15; $length++) {
    foreach ($alphabet as $letter) {
        $combos[] = [$length, $letter];
    }
}

fwrite(STDERR, sprintf("combinaisons reelles construites : %d\n", count($combos)));

/**
 * Meme reconstruction que scripts/bench_position_full_sweep.php / bench_combined_length_full_sweep.php
 * (voir ces fichiers pour le commentaire complet) -- dupliquee ici volontairement : scripts
 * jetables independants, pas de bibliotheque partagee a creer pour une mesure ponctuelle.
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

// Chauffe le cache avant toute mesure retenue -- meme raison que les balayages precedents (TTFB
// "chaud", CLAUDE.md). 40 requetes dispersees sur la plage reelle (364 combinaisons, plage plus
// petite que les balayages precedents), jamais comptabilisees.
$warmupSamples = [];
for ($i = 0; $i < 40 && $i < count($combos); $i++) {
    $warmupSamples[] = $combos[(int) ($i * count($combos) / 40)];
}
foreach ($warmupSamples as [$length, $letter]) {
    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);
    $solver->solve("{$length}-lettres/avec/{$letter}");
}

$results = [];
$anomalies = [];
$zeroResultCount = 0;
$oneResultCount = 0;

foreach ($combos as [$length, $letter]) {
    $rawPath = "{$length}-lettres/avec/{$letter}";

    $filters = WordListFilters::fromPath($rawPath);
    if ($filters === null) {
        $anomalies[] = "fromPath() a renvoye null pour {$rawPath}";
        continue;
    }

    // Sanity check syntaxique (pas seulement suppose) : la voie palier 1 doit toujours produire
    // exactement une longueur + un seul "avec" a occurrence unique (minCount=1), rien d'autre.
    $withLettersCopy = $filters->withLetters;
    if ($filters->length !== $length || count($withLettersCopy) !== 1 || reset($withLettersCopy) !== 1) {
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
    } elseif ($page->total === 1) {
        $oneResultCount++;
    }

    $results[] = [
        'path' => $rawPath,
        'length' => $length,
        'letter' => strtoupper($letter),
        'elapsedMs' => $elapsedMs,
        'total' => $page->total,
        'truncated' => $page->truncated,
        'queryCount' => $page->queryCount,
    ];
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

echo "=== BALAYAGE -- avec + longueur, 1 lettre (palier 1), complet ===\n";
printf("n=%d anomalies=%d\n", $n, count($anomalies));
printf("min=%.3f ms  p50=%.3f ms  p95=%.3f ms  p99=%.3f ms  max=%.3f ms\n", $min, $p50, $p95, $p99, $max);
printf("cas au-dessus de 250 ms : %d / %d\n", count($overBudget), $n);
printf("combinaisons a 0 resultat : %d / %d\n", $zeroResultCount, $n);
printf("combinaisons a exactement 1 resultat : %d / %d\n", $oneResultCount, $n);

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

// --- Couverture du maillage (LengthLinksBuilder::byWith), verifiee exhaustivement ---
//
// Compare TROIS univers, tous les 364 (ou moins si anomalies), pas un echantillon :
//   1. "live"        : combinaisons avec total >= 1 d'apres le vrai solveur (ci-dessus)
//   2. "list_counts" : cles list_type='length_with' presentes dans storage/dictionary_fr.sqlite
//      (source de LengthLinksBuilder::build()->byWith, deja cablee depuis /mots/{N}-lettres,
//      Family::WORD_LIST_LENGTH, deja indexee D-017 -- app/Search/LengthLinksBuilder.php)
//   3. "linked"      : combinaisons effectivement presentes dans byWith quand on appelle
//      LengthLinksBuilder::build($length) pour chacune des 14 longueurs (le vrai code, pas une
//      relecture de list_counts a la main)

echo "\n=== COUVERTURE DU MAILLAGE (LengthLinksBuilder::byWith) ===\n";

$liveNonZero = [];
foreach ($results as $r) {
    if ($r['total'] > 0) {
        $liveNonZero[$r['length'] . ':' . $r['letter']] = true;
    }
}

$listCountsStatement = $pdo->prepare("SELECT list_key FROM list_counts WHERE list_type = 'length_with'");
$listCountsStatement->execute();
$listCountsKeys = [];
foreach ($listCountsStatement as $row) {
    $listCountsKeys[(string) $row['list_key']] = true;
}
// Ne retenir que les cles list_counts a l'interieur du perimetre 2-15 lettres / A-Z (list_counts
// peut en theorie contenir d'autres longueurs si le schema evoluait -- verification defensive,
// pas suppose).
$listCountsKeysInScope = array_intersect_key($listCountsKeys, array_flip(array_map(
    static fn (array $r): string => $r['length'] . ':' . $r['letter'],
    $results
)));

$linkedKeys = [];
$linkedBuilder = new LengthLinksBuilder(new Connection($config->dictionaryPath));
$builderQueryCount = 0;
for ($length = 2; $length <= 15; $length++) {
    $links = $linkedBuilder->build($length);
    $builderQueryCount += $links->queryCount;
    foreach ($links->byWith as $link) {
        $linkedKeys[$length . ':' . $link['letter']] = true;
    }
}

$onlyLive = array_diff_key($liveNonZero, $listCountsKeysInScope);
$onlyListCounts = array_diff_key($listCountsKeysInScope, $liveNonZero);
$onlyLiveNotLinked = array_diff_key($liveNonZero, $linkedKeys);
$onlyLinkedNotLive = array_diff_key($linkedKeys, $liveNonZero);

printf("univers live (total >= 1, vrai solveur)       : %d\n", count($liveNonZero));
printf("univers list_counts (list_type='length_with') : %d\n", count($listCountsKeysInScope));
printf("univers linked (LengthLinksBuilder->byWith)    : %d\n", count($linkedKeys));
printf("divergence live vs list_counts (live seul)     : %d\n", count($onlyLive));
printf("divergence live vs list_counts (list_counts seul) : %d\n", count($onlyListCounts));
printf("divergence live vs linked (live non lie)       : %d\n", count($onlyLiveNotLinked));
printf("divergence live vs linked (lie non live)       : %d\n", count($onlyLinkedNotLive));
printf("requetes LengthLinksBuilder::build() cumulees sur les 14 longueurs : %d\n", $builderQueryCount);

if ($onlyLive !== []) {
    echo "--- LIVE SEUL (absent de list_counts) ---\n";
    foreach (array_keys($onlyLive) as $k) {
        echo $k . "\n";
    }
}
if ($onlyListCounts !== []) {
    echo "--- LIST_COUNTS SEUL (absent du live) ---\n";
    foreach (array_keys($onlyListCounts) as $k) {
        echo $k . "\n";
    }
}
if ($onlyLiveNotLinked !== []) {
    echo "--- LIVE NON LIE (absent de byWith) ---\n";
    foreach (array_keys($onlyLiveNotLinked) as $k) {
        echo $k . "\n";
    }
}
if ($onlyLinkedNotLive !== []) {
    echo "--- LIE MAIS NON LIVE (byWith contient une combinaison a 0 resultat) ---\n";
    foreach (array_keys($onlyLinkedNotLive) as $k) {
        echo $k . "\n";
    }
}

echo "\nFIN\n";
