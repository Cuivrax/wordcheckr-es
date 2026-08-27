<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache de mesure "commencant/{X}/avec/{Y}", SANS
 * longueur -- nouvel axe d'ouverture SEO jamais explore, distinct de l'entonnoir "avec"
 * existant D-029/D-030/D-031 qui ancre toujours sur la longueur) -- PAS un script de build, ne
 * modifie rien, aucune ecriture sur storage/dictionary_fr.sqlite ni storage/seo_fr.sqlite
 * (lecture seule). Balaie la TOTALITE des combinaisons reelles de /mots/commencant/{X}/avec/{Y}
 * (prefixe d'UNE lettre + UNE lettre "avec", occurrence unique, minCount=1, SANS longueur) via le
 * vrai solveur de production (App\Search\WordListFilters::fromPath() +
 * App\Search\WordListSolver::solve()), jamais une requete SQL reconstruite a cote -- seule
 * exception : EXPLAIN QUERY PLAN, qui rejoue la MEME requete que solveBounded() en invoquant par
 * reflexion les methodes privees anchorClause()/extraPredicates(), pour ne pas dupliquer la
 * logique de construction SQL. Meme discipline que scripts/bench_avec_length_1letter_full_sweep.php,
 * scripts/bench_avec_length_2letters_full_sweep.php, scripts/bench_avec_length_3letters_full_sweep.php,
 * scripts/bench_position_full_sweep.php, scripts/bench_combined_length_full_sweep.php.
 *
 * Perimetre : pour chaque prefixe A-Z (26), pour chaque lettre "avec" A-Z (26) -- 26 x 26 = 676
 * combinaisons au maximum, verifie par ce meme script (voir le compteur imprime). INCLUT
 * volontairement les 26 cas ou prefixe === lettre "avec" (ex. commencant/a/avec/a) : la tache
 * demande de tester explicitement ce cas degenere, pas de le filtrer a priori.
 *
 * Usage : php scripts/bench_commencant_avec_no_length_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_commencant_avec_no_length_full_sweep.php ne s'execute qu'en CLI.\n");
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

$combos = [];
foreach ($alphabet as $prefix) {
    foreach ($alphabet as $letter) {
        $combos[] = [$prefix, $letter];
    }
}

fwrite(STDERR, sprintf("combinaisons reelles construites : %d\n", count($combos)));

/**
 * Meme reconstruction que les balayages precedents (voir leur commentaire complet) -- dupliquee
 * ici volontairement : scripts jetables independants, pas de bibliotheque partagee a creer pour
 * une mesure ponctuelle.
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
// "chaud", CLAUDE.md). 40 requetes dispersees sur la plage reelle (676 combinaisons), jamais
// comptabilisees.
$warmupSamples = [];
for ($i = 0; $i < 40 && $i < count($combos); $i++) {
    $warmupSamples[] = $combos[(int) ($i * count($combos) / 40)];
}
foreach ($warmupSamples as [$prefix, $letter]) {
    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);
    $solver->solve("commencant/{$prefix}/avec/{$letter}");
}

$results = [];
$anomalies = [];
$zeroResultCount = 0;
$oneResultCount = 0;
$sameLetterResults = [];

foreach ($combos as [$prefix, $letter]) {
    $rawPath = "commencant/{$prefix}/avec/{$letter}";

    $filters = WordListFilters::fromPath($rawPath);
    if ($filters === null) {
        $anomalies[] = "fromPath() a renvoye null pour {$rawPath}";
        continue;
    }

    // Sanity check syntaxique (pas seulement suppose) : longueur absente, prefixe d'une seule
    // lettre, exactement un "avec" a occurrence unique (minCount=1), rien d'autre.
    $withLettersCopy = $filters->withLetters;
    if (
        $filters->length !== null
        || $filters->prefix === null || strlen($filters->prefix) !== 1
        || $filters->suffix !== null
        || count($withLettersCopy) !== 1 || reset($withLettersCopy) !== 1
    ) {
        $anomalies[] = "filtre inattendu pour {$rawPath} : " . json_encode([
            'length' => $filters->length,
            'prefix' => $filters->prefix,
            'suffix' => $filters->suffix,
            'withLetters' => $filters->withLetters,
        ]);
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

    $result = [
        'path' => $rawPath,
        'prefix' => strtoupper($prefix),
        'letter' => strtoupper($letter),
        'elapsedMs' => $elapsedMs,
        'total' => $page->total,
        'truncated' => $page->truncated,
        'queryCount' => $page->queryCount,
    ];

    $results[] = $result;

    if (strtoupper($prefix) === strtoupper($letter)) {
        $sameLetterResults[] = $result;
    }
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

echo "=== BALAYAGE -- commencant/{X}/avec/{Y}, SANS longueur, complet ===\n";
printf("n=%d anomalies=%d\n", $n, count($anomalies));
printf("min=%.3f ms  p50=%.3f ms  p95=%.3f ms  p99=%.3f ms  max=%.3f ms\n", $min, $p50, $p95, $p99, $max);
printf("cas au-dessus de 250 ms : %d / %d\n", count($overBudget), $n);
printf("combinaisons a 0 resultat : %d / %d\n", $zeroResultCount, $n);
printf("combinaisons a exactement 1 resultat : %d / %d\n", $oneResultCount, $n);

$zeroResultList = array_values(array_filter($results, static fn (array $r): bool => $r['total'] === 0));
$oneResultList = array_values(array_filter($results, static fn (array $r): bool => $r['total'] === 1));

echo "\n--- COMBINAISONS A 0 RESULTAT (candidates a l'exclusion) ---\n";
foreach ($zeroResultList as $r) {
    printf("/mots/%s\n", $r['path']);
}

echo "\n--- COMBINAISONS A EXACTEMENT 1 RESULTAT (a garder) ---\n";
foreach ($oneResultList as $r) {
    printf("/mots/%s\n", $r['path']);
}

if ($anomalies !== []) {
    echo "\n--- ANOMALIES ---\n";
    foreach ($anomalies as $a) {
        echo $a . "\n";
    }
}

echo "\n--- CAS AU-DESSUS DE 250 ms ---\n";
if (count($overBudget) > 0 && count($overBudget) < 40) {
    foreach ($overBudget as $r) {
        printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $r['path'], $r['total'], $r['truncated'] ? 'oui' : 'non', $r['queryCount'], $r['elapsedMs']);
    }
} elseif (count($overBudget) >= 40) {
    echo "(30 pires uniquement, " . count($overBudget) . " au total)\n";
    foreach (array_slice($overBudget, 0, 30) as $r) {
        printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $r['path'], $r['total'], $r['truncated'] ? 'oui' : 'non', $r['queryCount'], $r['elapsedMs']);
    }
} else {
    echo "(aucun)\n";
}

echo "\n--- 30 PIRES CAS (toutes valeurs confondues) ---\n";
foreach (array_slice($results, 0, 30) as $r) {
    printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $r['path'], $r['total'], $r['truncated'] ? 'oui' : 'non', $r['queryCount'], $r['elapsedMs']);
}

// --- Cas degenere prefixe === lettre "avec" (26 combinaisons, ex. commencant/a/avec/a) ---
//
// Verifie explicitement (pas suppose) : WordListFilters::fromPath() accepte-t-il ce cas ? Le
// resultat converge-t-il vers exactement la meme liste que /mots/commencant/{X} seul (puisque
// "avec X" avec minCount=1 est trivialement vrai des que le mot commence par X) ?

echo "\n=== CAS DEGENERE : prefixe === lettre avec (26 combinaisons) ===\n";
$degenerateMismatch = [];
foreach ($sameLetterResults as $r) {
    $baselinePath = 'commencant/' . strtolower($r['prefix']);
    $baselineFilters = WordListFilters::fromPath($baselinePath);
    $baselineConnection = new Connection($config->dictionaryPath);
    $baselineSolver = new WordListSolver($baselineConnection);
    $baselinePage = $baselineSolver->solve($baselinePath);

    $matches = $baselinePage !== null && $baselinePage->total === $r['total'];
    if (!$matches) {
        $degenerateMismatch[] = $r['path'];
    }

    printf(
        "/mots/%-24s total=%-6d  vs  /mots/%-16s total=%-6d  %s\n",
        $r['path'],
        $r['total'],
        $baselinePath,
        $baselinePage->total ?? -1,
        $matches ? 'IDENTIQUE' : 'DIVERGENT'
    );
}
printf("divergences totalpermatch : %d / %d\n", count($degenerateMismatch), count($sameLetterResults));
printf("fromPath() a accepte les 26 cas degeneres (aucun null) : %s\n", count($sameLetterResults) === 26 ? 'oui' : 'NON -- ' . count($sameLetterResults) . '/26');

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

// --- Reproductibilite du pire cas (isolation, cache deja chaud) ---

if ($worst !== null) {
    echo "\n=== REPRODUCTIBILITE DU PIRE CAS (15 executions isolees) ===\n";
    $times = [];
    for ($i = 0; $i < 15; $i++) {
        $connection = new Connection($config->dictionaryPath);
        $solver = new WordListSolver($connection);
        $start = hrtime(true);
        $solver->solve($worst['path']);
        $times[] = (hrtime(true) - $start) / 1_000_000.0;
    }
    sort($times);
    printf(
        "/mots/%s : min=%.3f ms  p50=%.3f ms  max=%.3f ms (n=15)\n",
        $worst['path'],
        $times[0],
        $times[(int) floor(count($times) / 2)],
        $times[count($times) - 1]
    );
}

// --- Maillage potentiel : /mots/commencant/{X} (26 pages, deja indexee D-017) -> 26 variantes
// avec/{Y} chacune. Verifie SEULEMENT la faisabilite (comptage), aucun code de maillage n'est
// construit ici -- une requete GROUP BY equivalente a 'start_end' (D-024) sur (prefixe, lettre
// avec) donnerait le nombre exact de variantes non vides par page source ; approxime ici par le
// nombre de combinaisons a >= 1 resultat par prefixe, deja calcule ci-dessus.

echo "\n=== MAILLAGE POTENTIEL : fan-out par page /mots/commencant/{X} ===\n";
$byPrefix = [];
foreach ($results as $r) {
    if ($r['total'] > 0) {
        $byPrefix[$r['prefix']] = ($byPrefix[$r['prefix']] ?? 0) + 1;
    }
}
ksort($byPrefix);
foreach ($byPrefix as $prefix => $count) {
    printf("%s : %d / 26 variantes avec >= 1 resultat\n", $prefix, $count);
}
$fanoutValues = array_values($byPrefix);
printf(
    "fan-out moyen=%.2f  min=%d  max=%d (sur %d pages source /mots/commencant/{X})\n",
    count($fanoutValues) > 0 ? array_sum($fanoutValues) / count($fanoutValues) : 0.0,
    count($fanoutValues) > 0 ? min($fanoutValues) : 0,
    count($fanoutValues) > 0 ? max($fanoutValues) : 0,
    count($fanoutValues)
);

echo "\nFIN\n";
