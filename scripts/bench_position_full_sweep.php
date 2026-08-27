<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache d'analyse d'ouverture SEO position/combined) --
 * PAS un script de build, ne modifie rien, n'ecrit rien sur storage/dictionary_fr.sqlite (lecture
 * seule, PDO::SQLITE_ATTR_OPEN_FLAGS non force ici mais aucune requete d'ecriture n'est jamais
 * emise). Balaie la TOTALITE des combinaisons reelles de la famille "position"
 * (App\Search\WordListFilters, D-023) via le vrai solveur de production
 * (App\Search\WordListSolver::solve()), jamais une requete SQL reconstruite a cote --
 * seule exception : EXPLAIN QUERY PLAN reconstruit la MEME requete que solveBounded() en
 * appelant anchorClause()/extraPredicates() par reflexion (methodes privees), pour ne pas avoir
 * a dupliquer la logique de construction SQL dans ce script.
 *
 * Perimetre : pour chaque longueur L de 2 a 15, pour chaque position P de 2 a L-1 (1 et L sont
 * degeneres, collapses vers commencant/terminant par WordListFilters::fromPath(), donc hors
 * perimetre "position"), pour chaque lettre A-Z -- 2 366 combinaisons reelles (verifie par ce
 * meme script, voir le compteur imprime).
 *
 * Usage : php scripts/bench_position_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_position_full_sweep.php ne s'execute qu'en CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\WordListFilters;
use App\Search\WordListSolver;

$config = Config::load('fr');

if (!is_file($config->dictionaryPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$config->dictionaryPath}\n");
    exit(1);
}

$alphabet = str_split('abcdefghijklmnopqrstuvwxyz');

// Construit la liste complete des combinaisons reelles avant toute mesure (permet de verifier
// le compte exact avant de lancer quoi que ce soit de couteux).
$combos = [];
for ($length = 2; $length <= 15; $length++) {
    for ($position = 2; $position <= $length - 1; $position++) {
        foreach ($alphabet as $letter) {
            $combos[] = [$length, $position, $letter];
        }
    }
}

fwrite(STDERR, sprintf("combinaisons reelles construites : %d\n", count($combos)));

/**
 * Reconstruit la meme requete que WordListSolver::solveBounded() (chemin anchorOrder ===
 * 'normalized', le seul emprunte par la famille "position" -- toujours ancree sur `length`,
 * jamais sur `reversed`) en appelant les methodes privees anchorClause()/extraPredicates() par
 * reflexion. N'existe QUE pour produire un EXPLAIN QUERY PLAN fidele, jamais utilise pour la
 * mesure de temps elle-meme (qui appelle uniquement WordListSolver::solve()).
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

// Chauffe le cache (pages OS + SQLite) avant toute mesure retenue -- CLAUDE.md vise un TTFB
// "chaud" (p95 < 250 ms), pas un premier appel a froid sur un fichier de 236 Mo. 60 requetes
// dispersees sur la plage reelle, jamais comptabilisees.
$warmupSamples = [];
for ($i = 0; $i < 60 && $i < count($combos); $i++) {
    $warmupSamples[] = $combos[(int) ($i * count($combos) / 60)];
}
foreach ($warmupSamples as [$length, $position, $letter]) {
    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);
    $solver->solve("{$length}-lettres/position/{$position}/{$letter}");
}

$results = [];
$anomalies = [];
$zeroResultCount = 0;

foreach ($combos as [$length, $position, $letter]) {
    $rawPath = "{$length}-lettres/position/{$position}/{$letter}";

    $filters = WordListFilters::fromPath($rawPath);
    if ($filters === null) {
        $anomalies[] = "fromPath() a renvoye null pour {$rawPath}";
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

    if ($page->total === 0) {
        $zeroResultCount++;
    }

    $results[] = [
        'path' => $rawPath,
        'length' => $length,
        'position' => $position,
        'letter' => $letter,
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

// Cas representatif : mediane exacte de la liste triee par temps (pas la premiere combinaison
// arbitraire) -- reflete un cas "typique", pas un cas choisi a la main.
$sortedByTime = $results;
usort($sortedByTime, static fn (array $a, array $b): int => $a['elapsedMs'] <=> $b['elapsedMs']);
$representative = $sortedByTime[(int) floor($n / 2)] ?? null;

echo "=== BALAYAGE 1 -- position, complet ===\n";
printf("n=%d anomalies=%d\n", $n, count($anomalies));
printf("min=%.3f ms  p50=%.3f ms  p95=%.3f ms  p99=%.3f ms  max=%.3f ms\n", $min, $p50, $p95, $p99, $max);
printf("cas au-dessus de 250 ms : %d / %d\n", count($overBudget), $n);
printf("combinaisons a 0 resultat : %d / %d\n", $zeroResultCount, $n);

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

if ($representative !== null) {
    echo "\n--- EXPLAIN QUERY PLAN : cas representatif (mediane des temps mesures) ---\n";
    echo "/mots/{$representative['path']}  ({$representative['elapsedMs']} ms, total={$representative['total']})\n";
    $filters = WordListFilters::fromPath($representative['path']);
    $solver = new WordListSolver(new Connection($config->dictionaryPath));
    $q = reconstructBoundedQuery($solver, $filters);
    echo "SQL: {$q['sql']}\n";
    echo "anchorType={$q['anchorType']} order={$q['order']}\n";
    foreach (explainPlan($pdo, $q['sql'], $q['params']) as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

if ($worst !== null) {
    echo "\n--- EXPLAIN QUERY PLAN : pire cas trouve ---\n";
    echo "/mots/{$worst['path']}  ({$worst['elapsedMs']} ms, total={$worst['total']})\n";
    $filters = WordListFilters::fromPath($worst['path']);
    $solver = new WordListSolver(new Connection($config->dictionaryPath));
    $q = reconstructBoundedQuery($solver, $filters);
    echo "SQL: {$q['sql']}\n";
    echo "anchorType={$q['anchorType']} order={$q['order']}\n";
    foreach (explainPlan($pdo, $q['sql'], $q['params']) as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

echo "\nFIN\n";
