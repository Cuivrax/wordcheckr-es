<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache de dimensionnement "commencant/terminant
 * multi-lettres", 2026-08-18) -- PAS un script de build, ne modifie rien, n'ecrit rien sur
 * storage/dictionary_fr.sqlite ni storage/seo_fr.sqlite (lecture seule). Balaie la TOTALITE des
 * prefixes/suffixes REELS de longueur 2, 3 et 4 (pas le theorique 26^2+26^3+26^4 = 493 052 --
 * DEUX GROUP BY directs sur `terms` etablissent d'abord la liste reelle, mesuree a 21 734
 * prefixes + 17 805 suffixes = 39 539 combinaisons au 2026-08-18, voir
 * reports/query-plans/commencant-terminant-multi-lettres-dimensionnement.md) via le vrai solveur
 * de production (App\Search\WordListFilters::fromPath() + App\Search\WordListSolver::solve()),
 * jamais une requete SQL reconstruite a cote -- seule exception : EXPLAIN QUERY PLAN, qui rejoue
 * la MEME requete que solveExact()/solveBounded() en invoquant par reflexion les methodes privees
 * exactWhereClause() (commencant, regime EXACT) ou anchorClause()/extraPredicates() (terminant,
 * regime BORNE), pour ne pas dupliquer la logique de construction SQL. Meme discipline que
 * scripts/bench_avec_length_3letters_full_sweep.php (D-031) et scripts/bench_position_full_sweep.php
 * (D-028).
 *
 * Perimetre : commencant/{prefixe} et terminant/{suffixe} SEULS (aucune longueur, aucune autre
 * contrainte) pour chaque prefixe/suffixe REEL de longueur 2, 3 ou 4 -- la longueur 1 (26+26
 * pages) est deja indexee depuis D-017, hors perimetre de cette tache. Verifie EN PLUS (pas
 * seulement la performance) :
 *   - coherence stricte entre le total du GROUP BY (verite terrain SQL) et $page->total renvoye
 *     par le vrai solveur (force brute, sur les 39 539 combinaisons, pas un echantillon) ;
 *   - couverture du maillage en entonnoir (App\Search\PrefixExtensionLinksBuilder /
 *     SuffixExtensionLinksBuilder) : chaque prefixe/suffixe de longueur N+1 doit avoir un lien
 *     RETOUR-VERIFIABLE depuis son parent de longueur N (prefixe/suffixe tronque d'une lettre) --
 *     verifie par construction mathematique ET par un balayage reel des builders, pas seulement
 *     suppose.
 *
 * Usage : php scripts/bench_commencant_terminant_multilettres_full_sweep.php [commencant|terminant|all]
 *   (par defaut : all -- les deux balayages a la suite, ~39 539 combinaisons)
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_commencant_terminant_multilettres_full_sweep.php ne s'execute qu'en CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\PrefixExtensionLinksBuilder;
use App\Search\SuffixExtensionLinksBuilder;
use App\Search\WordListFilters;
use App\Search\WordListSolver;

$scope = $argv[1] ?? 'all';
if (!in_array($scope, ['commencant', 'terminant', 'all'], true)) {
    fwrite(STDERR, "usage : php scripts/bench_commencant_terminant_multilettres_full_sweep.php [commencant|terminant|all]\n");
    exit(1);
}

$config = Config::load('fr');

if (!is_file($config->dictionaryPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$config->dictionaryPath}\n");
    exit(1);
}

$pdo = (new Connection($config->dictionaryPath))->pdo();

/**
 * Reconstruit la requete de comptage EXACT (regime "commencant" seul, isExactMode() === true --
 * jamais un ORDER BY/LIMIT necessaire pour le COUNT, uniquement le WHERE) en invoquant
 * exactWhereClause() par reflexion. N'existe QUE pour EXPLAIN QUERY PLAN, jamais utilisee pour la
 * mesure de temps elle-meme (qui appelle uniquement WordListSolver::solve()).
 *
 * @return array{sql: string, params: list<int|string>}
 */
function reconstructExactCountQuery(WordListSolver $solver, WordListFilters $filters): array
{
    $ref = new ReflectionClass($solver);
    $method = $ref->getMethod('exactWhereClause');
    [$where, $params] = $method->invoke($solver, $filters);
    $whereSql = $where === '' ? '' : 'WHERE ' . $where;

    return ['sql' => "SELECT COUNT(*) c FROM terms $whereSql", 'params' => $params];
}

/**
 * Reconstruit la requete BORNE (regime "terminant" seul, ancre sur reversed, 2 requetes -- ici la
 * requete de plafond, celle qui determine truncated) -- meme fonction que dans les scripts de
 * balayage precedents (D-028/D-031), dupliquee ici volontairement (scripts jetables independants).
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

    $sql = "SELECT COUNT(*) c FROM (SELECT id FROM terms $whereSql ORDER BY $anchorOrder LIMIT ?)";
    $params[] = WordListSolver::ROW_EXAMINATION_CEILING + 1;

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

/**
 * Balaie une direction (commencant ou terminant) sur les combinaisons REELLES de longueur 2, 3, 4
 * -- construites ici par GROUP BY direct sur `terms` (verite terrain SQL, jamais le theorique
 * 26^N), pas relues depuis list_counts (verification independante du precalcul de
 * scripts/build_explore_hub_counts.php, pas une confiance aveugle dans son propre resultat).
 *
 * @param 'commencant'|'terminant' $direction
 */
function sweepDirection(string $direction, \PDO $pdo, Config $config): array
{
    $groundTruth = [];

    if ($direction === 'commencant') {
        foreach ([2, 3, 4] as $n) {
            $stmt = $pdo->query(
                "SELECT substr(normalized, 1, {$n}) c, COUNT(*) cnt FROM terms WHERE length >= {$n} GROUP BY c ORDER BY c"
            );
            foreach ($stmt as $row) {
                $groundTruth[] = [(string) $row['c'], (int) $row['cnt']];
            }
        }
    } else {
        foreach ([2, 3, 4] as $n) {
            $stmt = $pdo->query(
                "SELECT substr(reversed, 1, {$n}) c, COUNT(*) cnt FROM terms WHERE length >= {$n} GROUP BY c ORDER BY c"
            );
            foreach ($stmt as $row) {
                $groundTruth[] = [strrev((string) $row['c']), (int) $row['cnt']];
            }
        }
    }

    fwrite(STDERR, sprintf("[%s] combinaisons reelles (GROUP BY, verite terrain SQL) : %d\n", $direction, count($groundTruth)));

    // Chauffe le cache avant toute mesure retenue (TTFB "chaud", CLAUDE.md). 100 requetes
    // dispersees sur la plage reelle, jamais comptabilisees.
    $total = count($groundTruth);
    $warmupCount = min(100, $total);
    for ($i = 0; $i < $warmupCount; $i++) {
        [$value] = $groundTruth[(int) ($i * $total / $warmupCount)];
        $connection = new Connection($config->dictionaryPath);
        $solver = new WordListSolver($connection);
        $solver->solve($direction . '/' . strtolower($value));
    }

    $results = [];
    $anomalies = [];
    $mismatches = [];
    $zeroResultCount = 0;
    $oneResultCount = 0;

    $sweepStart = microtime(true);

    foreach ($groundTruth as [$value, $expectedCount]) {
        $rawPath = $direction . '/' . strtolower($value);

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

        if ($page->canonicalPath !== $rawPath) {
            $anomalies[] = "canonicalPath divergent pour {$rawPath} : {$page->canonicalPath}";
        }

        // Force brute : le total rendu par le vrai solveur doit correspondre exactement au
        // GROUP BY (regime EXACT, jamais tronque) ou a min(GROUP BY, ROW_EXAMINATION_CEILING)
        // (regime BORNE, peut etre tronque).
        $expectedRendered = $direction === 'commencant'
            ? $expectedCount
            : min($expectedCount, WordListSolver::ROW_EXAMINATION_CEILING);
        $expectedTruncated = $direction === 'terminant' && $expectedCount > WordListSolver::ROW_EXAMINATION_CEILING;

        if ($page->total !== $expectedRendered || $page->truncated !== $expectedTruncated) {
            $mismatches[] = sprintf(
                '%s : attendu total=%d truncated=%s, obtenu total=%d truncated=%s',
                $rawPath,
                $expectedRendered,
                $expectedTruncated ? 'oui' : 'non',
                $page->total,
                $page->truncated ? 'oui' : 'non',
            );
        }

        if ($page->total === 0) {
            $zeroResultCount++;
        } elseif ($page->total === 1) {
            $oneResultCount++;
        }

        $results[] = [
            'path' => $rawPath,
            'value' => strtoupper($value),
            'length' => strlen($value),
            'elapsedMs' => $elapsedMs,
            'total' => $page->total,
            'truncated' => $page->truncated,
            'queryCount' => $page->queryCount,
        ];
    }

    $sweepElapsedS = microtime(true) - $sweepStart;

    $elapsedList = array_map(static fn (array $r): float => $r['elapsedMs'], $results);
    sort($elapsedList);
    $n = count($elapsedList);

    $overBudget = array_values(array_filter($results, static fn (array $r): bool => $r['elapsedMs'] > 250.0));
    usort($overBudget, static fn (array $a, array $b): int => $b['elapsedMs'] <=> $a['elapsedMs']);

    $sortedByTime = $results;
    usort($sortedByTime, static fn (array $a, array $b): int => $a['elapsedMs'] <=> $b['elapsedMs']);
    $representative = $sortedByTime[(int) floor($n / 2)] ?? null;
    $worst = $sortedByTime[$n - 1] ?? null;

    return [
        'direction' => $direction,
        'n' => $n,
        'anomalies' => $anomalies,
        'mismatches' => $mismatches,
        'zeroResultCount' => $zeroResultCount,
        'oneResultCount' => $oneResultCount,
        'sweepElapsedS' => $sweepElapsedS,
        'min' => $n > 0 ? $elapsedList[0] : NAN,
        'max' => $n > 0 ? $elapsedList[$n - 1] : NAN,
        'p50' => percentile($elapsedList, 50),
        'p95' => percentile($elapsedList, 95),
        'p99' => percentile($elapsedList, 99),
        'overBudget' => $overBudget,
        'representative' => $representative,
        'worst' => $worst,
        'results' => $results,
        'byLength' => (static function (array $results): array {
            $byLength = [];
            foreach ($results as $r) {
                $byLength[$r['length']][] = $r['elapsedMs'];
            }
            $out = [];
            foreach ($byLength as $len => $times) {
                sort($times);
                $cnt = count($times);
                $out[$len] = [
                    'n' => $cnt,
                    'min' => $times[0],
                    'p50' => percentile($times, 50),
                    'max' => $times[$cnt - 1],
                ];
            }
            ksort($out);
            return $out;
        })($results),
    ];
}

function printSummary(array $r): void
{
    echo "\n=== BALAYAGE -- {$r['direction']}, multi-lettres (2-4), complet ===\n";
    printf("n=%d anomalies=%d mismatches=%d duree totale du balayage=%.1fs\n", $r['n'], count($r['anomalies']), count($r['mismatches']), $r['sweepElapsedS']);
    printf("min=%.3f ms  p50=%.3f ms  p95=%.3f ms  p99=%.3f ms  max=%.3f ms\n", $r['min'], $r['p50'], $r['p95'], $r['p99'], $r['max']);
    printf("cas au-dessus de 250 ms : %d / %d\n", count($r['overBudget']), $r['n']);
    printf("combinaisons a 0 resultat : %d / %d\n", $r['zeroResultCount'], $r['n']);
    printf("combinaisons a exactement 1 resultat : %d / %d\n", $r['oneResultCount'], $r['n']);

    echo "--- par longueur de prefixe/suffixe ---\n";
    foreach ($r['byLength'] as $len => $stats) {
        printf("  %d lettres : n=%d min=%.3f ms p50=%.3f ms max=%.3f ms\n", $len, $stats['n'], $stats['min'], $stats['p50'], $stats['max']);
    }

    if ($r['anomalies'] !== []) {
        echo "\n--- ANOMALIES ---\n";
        foreach ($r['anomalies'] as $a) {
            echo $a . "\n";
        }
    }

    if ($r['mismatches'] !== []) {
        echo "\n--- MISMATCHES (force brute, max 30 affiches) ---\n";
        foreach (array_slice($r['mismatches'], 0, 30) as $m) {
            echo $m . "\n";
        }
    }

    echo "\n--- CAS AU-DESSUS DE 250 ms ---\n";
    if (count($r['overBudget']) > 0 && count($r['overBudget']) < 30) {
        foreach ($r['overBudget'] as $row) {
            printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $row['path'], $row['total'], $row['truncated'] ? 'oui' : 'non', $row['queryCount'], $row['elapsedMs']);
        }
    } elseif (count($r['overBudget']) >= 30) {
        echo "(20 pires uniquement, " . count($r['overBudget']) . " au total)\n";
        foreach (array_slice($r['overBudget'], 0, 20) as $row) {
            printf("/mots/%s  total=%d  truncated=%s  queryCount=%d  %.3f ms\n", $row['path'], $row['total'], $row['truncated'] ? 'oui' : 'non', $row['queryCount'], $row['elapsedMs']);
        }
    } else {
        echo "(aucun)\n";
    }
}

$allResults = [];

if ($scope === 'commencant' || $scope === 'all') {
    $r = sweepDirection('commencant', $pdo, $config);
    printSummary($r);
    $allResults['commencant'] = $r;
}

if ($scope === 'terminant' || $scope === 'all') {
    $r = sweepDirection('terminant', $pdo, $config);
    printSummary($r);
    $allResults['terminant'] = $r;
}

// --- EXPLAIN QUERY PLAN : cas representatif + pire cas, par direction ---

foreach ($allResults as $direction => $r) {
    foreach (['representative' => $r['representative'], 'worst' => $r['worst']] as $label => $case) {
        if ($case === null) {
            continue;
        }
        echo "\n--- EXPLAIN QUERY PLAN [{$direction}] : {$label} ---\n";
        echo "/mots/{$case['path']}  ({$case['elapsedMs']} ms, total={$case['total']})\n";
        $filters = WordListFilters::fromPath($case['path']);
        $solver = new WordListSolver(new Connection($config->dictionaryPath));

        if ($direction === 'commencant') {
            $q = reconstructExactCountQuery($solver, $filters);
            echo "SQL (COUNT): {$q['sql']}\n";
        } else {
            $q = reconstructBoundedQuery($solver, $filters);
            echo "SQL (plafond): {$q['sql']}\n";
            echo "anchorType={$q['anchorType']} order={$q['order']}\n";
        }
        foreach (explainPlan($pdo, $q['sql'], $q['params']) as $row) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}

// --- Cas degeneres proches de Z, testes explicitement (rangeBounds() sans borne superieure --
// --- voir D-025bis) meme s'ils sont deja couverts par le balayage complet ci-dessus ---

echo "\n=== CAS DEGENERES PROCHES DE Z (rangeBounds() sans borne superieure) ===\n";
$degenerateCases = [
    ['commencant', 'zy'], ['commencant', 'zz'], ['commencant', 'zzz'], ['commencant', 'zzzz'],
    ['terminant', 'zz'], ['terminant', 'zzz'], ['terminant', 'zzzz'], ['terminant', 'az'], ['terminant', 'za'],
];
foreach ($degenerateCases as [$direction, $value]) {
    $solver = new WordListSolver(new Connection($config->dictionaryPath));
    $start = hrtime(true);
    $page = $solver->solve($direction . '/' . $value);
    $elapsedMs = (hrtime(true) - $start) / 1_000_000.0;
    if ($page === null) {
        printf("%s/%s : aucune correspondance reelle (page absente du balayage, filtre valide mais 0 mot -- ou combinaison inexistante)\n", $direction, $value);
        continue;
    }
    printf("%s/%s : total=%d truncated=%s queryCount=%d %.3f ms\n", $direction, $value, $page->total, $page->truncated ? 'oui' : 'non', $page->queryCount, $elapsedMs);
}

// --- Couverture du maillage en entonnoir : PrefixExtensionLinksBuilder / SuffixExtensionLinksBuilder
// --- verifiees exhaustivement (chaque prefixe/suffixe de longueur N+1 doit etre atteint depuis
// --- son parent de longueur N), pas seulement demontrees mathematiquement ---

echo "\n=== COUVERTURE DU MAILLAGE EN ENTONNOIR ===\n";

function meshCoverage(string $direction, \PDO $pdo, Config $config): void
{
    $connection = new Connection($config->dictionaryPath);

    if ($direction === 'commencant') {
        $builder = new PrefixExtensionLinksBuilder($connection);
        $childField = 'prefix';
    } else {
        $builder = new SuffixExtensionLinksBuilder($connection);
        $childField = 'suffix';
    }

    // Univers "reel" par longueur, depuis list_counts (deja verifie identique au GROUP BY direct
    // par le balayage de performance ci-dessus -- ici on verifie le MAILLAGE, pas le total).
    $listTypePrefix = $direction === 'commencant' ? 'prefix' : 'suffix';

    $reachedFromParent = [];
    $sourceCountsByLength = [];

    // Niveau 0 -> 1 : les 26 pages mono-lettre deja indexees (D-017) sont la racine du funnel,
    // pas construites par ce balayage (deja verifiees exhaustivement en D-017) -- point de depart.
    $roots = range('A', 'Z');

    foreach ([1, 2, 3] as $sourceLength) {
        $sources = $sourceLength === 1
            ? $roots
            : array_map(
                static fn (array $row): string => (string) $row['list_key'],
                $pdo->query("SELECT list_key FROM list_counts WHERE list_type = '{$listTypePrefix}{$sourceLength}'")->fetchAll(),
            );

        $sourceCountsByLength[$sourceLength] = count($sources);
        $builderQueryCount = 0;

        foreach ($sources as $source) {
            $links = $builder->build($source);
            $builderQueryCount += $links->queryCount;

            foreach ($links->links as $link) {
                $reachedFromParent[$link[$childField]] = true;
            }
        }

        fwrite(STDERR, sprintf(
            "[%s] niveau %d -> %d : %d pages source, %d requetes builder cumulees\n",
            $direction, $sourceLength, $sourceLength + 1, count($sources), $builderQueryCount,
        ));
    }

    // Univers "reel" total (prefix2+prefix3+prefix4 ou suffix2+suffix3+suffix4), depuis list_counts.
    $realTotal = [];
    foreach ([2, 3, 4] as $len) {
        $stmt = $pdo->query("SELECT list_key FROM list_counts WHERE list_type = '{$listTypePrefix}{$len}'");
        foreach ($stmt as $row) {
            $realTotal[(string) $row['list_key']] = true;
        }
    }

    $onlyReal = array_diff_key($realTotal, $reachedFromParent);
    $onlyReached = array_diff_key($reachedFromParent, $realTotal);

    printf(
        "[%s] univers reel (prefix/suffix 2+3+4, list_counts) : %d -- atteint depuis un parent : %d -- divergence (reel sans lien) : %d -- divergence (lien sans reel) : %d\n",
        $direction, count($realTotal), count($reachedFromParent), count($onlyReal), count($onlyReached),
    );

    if ($onlyReal !== []) {
        echo "  --- REEL SANS LIEN (max 20 affiches) ---\n";
        foreach (array_slice(array_keys($onlyReal), 0, 20) as $k) {
            echo '  ' . $k . "\n";
        }
    }
    if ($onlyReached !== []) {
        echo "  --- LIEN SANS REEL (max 20 affiches) ---\n";
        foreach (array_slice(array_keys($onlyReached), 0, 20) as $k) {
            echo '  ' . $k . "\n";
        }
    }
}

if ($scope === 'commencant' || $scope === 'all') {
    meshCoverage('commencant', $pdo, $config);
}
if ($scope === 'terminant' || $scope === 'all') {
    meshCoverage('terminant', $pdo, $config);
}

// --- Contenu duplique entre un parent et SON UNIQUE enfant (meme lecon que D-025, constat I-1 :
// --- "52 paires a contenu strictement duplique" pour la famille commencant+terminant) : si un
// --- prefixe/suffixe de longueur N a EXACTEMENT UNE extension reelle a N+1 lettres ET que le
// --- compte de cette extension EGALE le compte du parent, les deux pages listent EXACTEMENT les
// --- memes mots (aucun mot du parent n'a de suite differente de celle-la) -- contenu duplique
// --- entre deux URL canoniques distinctes. Le compte du parent est ajuste pour exclure un
// --- eventuel mot EXACTEMENT egal au prefixe/suffixe lui-meme (ex. "AN", D-010 -- ce mot est
// --- "consomme" par la page parente, jamais etendu, voir PrefixExtensionLinksBuilderTest.php).
// --- Decision d'arbitrage (quelle URL reste index,follow) HORS PERIMETRE de ce script -- meme
// --- division du travail que D-025 (arbitrage fait par seo-registry au moment de l'indexation,
// --- pas par data-engine au moment du dimensionnement).

echo "\n=== CONTENU DUPLIQUE PARENT/ENFANT (meme lecon que D-025, I-1) ===\n";

function findDuplicateParentChildPairs(string $listPrefix, \PDO $pdo): array
{
    $duplicates = [];
    $checked = 0;

    $roots = range('A', 'Z');

    foreach ([1, 2, 3] as $sourceLength) {
        if ($sourceLength === 1) {
            $items = [];
            foreach ($roots as $letter) {
                if ($listPrefix === 'prefix') {
                    $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE normalized >= ? AND normalized < ?');
                } else {
                    $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE reversed >= ? AND reversed < ?');
                }
                $stmt->execute([$letter, chr(ord($letter) + 1)]);
                $items[] = [$letter, (int) $stmt->fetch()['c']];
            }
        } else {
            $stmt = $pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = '{$listPrefix}{$sourceLength}'");
            $items = [];
            foreach ($stmt as $row) {
                $items[] = [(string) $row['list_key'], (int) $row['count']];
            }
        }

        foreach ($items as [$parentKey, $parentTotal]) {
            $childListType = $listPrefix . ($sourceLength + 1);
            $pattern = $listPrefix === 'prefix' ? $parentKey . '_' : '_' . $parentKey;

            $stmt = $pdo->prepare('SELECT list_key, count FROM list_counts WHERE list_type = ? AND list_key LIKE ?');
            $stmt->execute([$childListType, $pattern]);
            $children = $stmt->fetchAll();
            $checked++;

            if (count($children) === 1 && (int) $children[0]['count'] === $parentTotal) {
                $duplicates[] = [
                    'parent' => $parentKey,
                    'parentLength' => $sourceLength,
                    'child' => (string) $children[0]['list_key'],
                    'count' => $parentTotal,
                ];
            }
        }
    }

    return ['checked' => $checked, 'duplicates' => $duplicates];
}

foreach (['prefix' => 'commencant', 'suffix' => 'terminant'] as $listPrefix => $direction) {
    if ($scope !== 'all' && $scope !== $direction) {
        continue;
    }

    $result = findDuplicateParentChildPairs($listPrefix, $pdo);
    $byLevel = [];
    foreach ($result['duplicates'] as $d) {
        $byLevel[$d['parentLength']] = ($byLevel[$d['parentLength']] ?? 0) + 1;
    }
    ksort($byLevel);

    printf(
        "[%s] paires parent/enfant verifiees : %d, duplicatas trouves : %d (%.2f%%)\n",
        $direction, $result['checked'], count($result['duplicates']),
        $result['checked'] > 0 ? 100.0 * count($result['duplicates']) / $result['checked'] : 0.0,
    );
    foreach ($byLevel as $level => $n) {
        printf("  niveau %d -> %d : %d duplicatas\n", $level, $level + 1, $n);
    }
    echo "  --- 15 premiers exemples ---\n";
    foreach (array_slice($result['duplicates'], 0, 15) as $d) {
        printf("  %s (%d) == %s (%d)\n", $d['parent'], $d['count'], $d['child'], $d['count']);
    }
}

echo "\nFIN\n";
