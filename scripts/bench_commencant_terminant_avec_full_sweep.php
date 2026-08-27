<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache de mesure "commencant + terminant + avec, SANS
 * longueur", nouvel axe SEO propose par le proprietaire du produit, jamais explore) -- PAS un
 * script de build, ne modifie rien, aucune ecriture sur storage/dictionary_fr.sqlite ni
 * storage/seo_fr.sqlite (lecture seule). Balaie les combinaisons reelles de
 * /mots/commencant/{X}/terminant/{Z}/avec/{Y} (prefixe 1 lettre + suffixe 1 lettre + UNE lettre
 * "avec" a occurrence unique, minCount=1, SANS longueur) via le vrai solveur de production
 * (App\Search\WordListFilters::fromPath() + App\Search\WordListSolver::solve()), jamais une
 * requete SQL reconstruite a cote -- seule exception : EXPLAIN QUERY PLAN, qui rejoue la MEME
 * requete que solveBounded() en invoquant par reflexion les methodes privees
 * anchorClause()/extraPredicates(). Meme discipline que scripts/bench_avec_length_3letters_full_sweep.php
 * et scripts/bench_combined_length_full_sweep.php.
 *
 * Perimetre : 26 x 26 x 26 = 17 576 combinaisons BRUTES au maximum (commencant x terminant x
 * avec). Raccourci de mesure justifie par la monotonie des predicats AND : si le panier
 * "commencant/{X}/terminant/{Z}" SEUL (sans "avec") est deja a 0 resultat, alors AJOUTER un
 * predicat "avec" supplementaire (instr(normalized, ?) > 0) ne peut jamais faire remonter le
 * total au-dessus de 0 -- toute combinaison commencant+terminant+avec derivee d'une paire
 * commencant+terminant a 0 resultat est donc CERTAINEMENT a 0 resultat, sans avoir besoin
 * d'executer la requete. list_counts (list_type='start_end', D-024) donne deja les 611 paires
 * commencant+terminant reellement non vides sur les 676 possibles (26x26) -- ce script balaie
 * REELEMENT, via le vrai solveur, les 611 x 26 = 15 886 combinaisons issues de ces 611 paires
 * (le sur-ensemble qui peut valoir >= 0), et compte separement, sans les executer, les
 * 65 x 26 = 1 690 combinaisons derivees des 65 paires a 0 resultat (total forcement 0). Somme :
 * 15 886 + 1 690 = 17 576, la totalite brute annoncee par la tache.
 *
 * Usage : php scripts/bench_commencant_terminant_avec_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_commencant_terminant_avec_full_sweep.php ne s'execute qu'en CLI.\n");
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

$pdoBootstrap = (new Connection($config->dictionaryPath))->pdo();
$startEndStatement = $pdoBootstrap->prepare("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end'");
$startEndStatement->execute();

$nonZeroPairs = [];
foreach ($startEndStatement as $row) {
    [$start, $end] = explode(':', (string) $row['list_key'], 2);
    $nonZeroPairs[] = [$start, $end, (int) $row['count']];
}

fwrite(STDERR, sprintf("paires commencant+terminant non vides (list_counts start_end) : %d / 676\n", count($nonZeroPairs)));

$allPairsCount = count($alphabet) * count($alphabet);
$zeroPairsCount = $allPairsCount - count($nonZeroPairs);
$skippedCombosCount = $zeroPairsCount * count($alphabet);

$combos = [];
foreach ($nonZeroPairs as [$start, $end, $pairCount]) {
    foreach ($alphabet as $with) {
        $combos[] = [strtolower($start), strtolower($end), $with, $pairCount];
    }
}

fwrite(STDERR, sprintf(
    "combinaisons reellement executees (611 paires x 26 lettres avec) : %d\n",
    count($combos)
));
fwrite(STDERR, sprintf(
    "combinaisons deduites a 0 sans execution (65 paires a 0 resultat x 26) : %d\n",
    $skippedCombosCount
));
fwrite(STDERR, sprintf("total brut (executees + deduites) : %d / 17 576 attendues\n", count($combos) + $skippedCombosCount));

/**
 * Meme reconstruction que les balayages precedents (voir scripts/bench_combined_length_full_sweep.php
 * pour le commentaire complet) -- dupliquee ici volontairement : scripts jetables independants,
 * pas de bibliotheque partagee a creer pour une mesure ponctuelle.
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
// "chaud", CLAUDE.md). 80 requetes dispersees sur la plage reelle, jamais comptabilisees.
$warmupSamples = [];
for ($i = 0; $i < 80 && $i < count($combos); $i++) {
    $warmupSamples[] = $combos[(int) ($i * count($combos) / 80)];
}
foreach ($warmupSamples as [$start, $end, $with]) {
    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);
    $solver->solve("commencant/{$start}/terminant/{$end}/avec/{$with}");
}

$results = [];
$anomalies = [];
$zeroResultCount = 0;
$oneResultCount = 0;
$queryCounts = [];

foreach ($combos as [$start, $end, $with, $pairCount]) {
    $rawPath = "commencant/{$start}/terminant/{$end}/avec/{$with}";

    $filters = WordListFilters::fromPath($rawPath);
    if ($filters === null) {
        $anomalies[] = "fromPath() a renvoye null pour {$rawPath}";
        continue;
    }

    // Sanity check syntaxique (pas seulement suppose) : prefixe 1 lettre, suffixe 1 lettre,
    // exactement 1 lettre "avec" a occurrence unique, aucune longueur.
    $withLettersCopy = $filters->withLetters;
    if (
        $filters->length !== null
        || $filters->prefix === null || strlen($filters->prefix) !== 1
        || $filters->suffix === null || strlen($filters->suffix) !== 1
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

    $start_t = hrtime(true);
    $page = $solver->solve($rawPath);
    $elapsedMs = (hrtime(true) - $start_t) / 1_000_000.0;

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

    $queryCounts[$page->queryCount] = ($queryCounts[$page->queryCount] ?? 0) + 1;

    $results[] = [
        'path' => $rawPath,
        'start' => strtoupper($start),
        'end' => strtoupper($end),
        'with' => strtoupper($with),
        'pairCount' => $pairCount,
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

echo "=== BALAYAGE -- commencant + terminant + avec (1 lettre chacun), SANS longueur, complet sur les 611 paires non vides ===\n";
printf("n=%d anomalies=%d\n", $n, count($anomalies));
printf("min=%.3f ms  p50=%.3f ms  p95=%.3f ms  p99=%.3f ms  max=%.3f ms\n", $min, $p50, $p95, $p99, $max);
printf("cas au-dessus de 250 ms : %d / %d\n", count($overBudget), $n);
printf("combinaisons executees a 0 resultat : %d / %d\n", $zeroResultCount, $n);
printf("combinaisons executees a exactement 1 resultat : %d / %d\n", $oneResultCount, $n);
printf("combinaisons deduites a 0 sans execution (paires commencant+terminant deja a 0) : %d\n", $skippedCombosCount);
printf("total brut (executees + deduites) : %d\n", $n + $skippedCombosCount);
printf("total a 0 resultat (executees + deduites) : %d\n", $zeroResultCount + $skippedCombosCount);
echo "repartition queryCount : " . json_encode($queryCounts) . "\n";

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

// Cas "pire panier commencant+terminant SEUL" (le plus grand des 611, avant tout "avec") : le
// pire cas structurel possible pour ce nouvel axe -- deja identifie par D-025bis
// (commencant/p/terminant/h ou proche). Verifie ici directement, pas suppose.
$biggestPair = null;
foreach ($nonZeroPairs as [$s, $e, $c]) {
    if ($biggestPair === null || $c > $biggestPair[2]) {
        $biggestPair = [$s, $e, $c];
    }
}
if ($biggestPair !== null) {
    [$bs, $be, $bc] = $biggestPair;
    printf("\nPaire commencant+terminant la plus grande (sans avec) : %s:%s, count=%d\n", strtoupper($bs), strtoupper($be), $bc);
}

echo "\nFIN\n";
