<?php

declare(strict_types=1);

/**
 * Script de mesure JETABLE (data-engine, tache de mesure "avec + longueur, 2 lettres" -- palier 2
 * de l'ouverture progressive en entonnoir de la famille "avec") -- PAS un script de build, ne
 * modifie rien, aucune ecriture sur storage/dictionary_fr.sqlite ni storage/seo_fr.sqlite (lecture
 * seule). Balaie la TOTALITE des combinaisons reelles de /mots/{N}-lettres/avec/{X}/{Y} (longueur
 * explicite + EXACTEMENT deux lettres "avec" DISTINCTES, chacune minCount=1) via le vrai solveur
 * de production (App\Search\WordListFilters::fromPath() + App\Search\WordListSolver::solve()),
 * jamais une requete SQL reconstruite a cote -- seule exception : EXPLAIN QUERY PLAN, qui rejoue
 * la MEME requete que solveBounded() en invoquant par reflexion les methodes privees
 * anchorClause()/extraPredicates(), pour ne pas dupliquer la logique de construction SQL. Meme
 * discipline que scripts/bench_avec_length_1letter_full_sweep.php (D-029, palier 1).
 *
 * Perimetre : pour chaque longueur L de 2 a 15 (14 valeurs), pour chaque PAIRE de lettres
 * distinctes A-Z (C(26,2) = 325) -- 14 x 325 = 4550 combinaisons au maximum, verifie par ce meme
 * script (voir le compteur imprime). Le cas "avec" a UNE seule lettre (palier 1, D-029, deja
 * mesure) et le cas general "avec" (multiset non borne, NEVER_SITEMAP permanent) sont HORS
 * PERIMETRE de ce balayage.
 *
 * Verifie EN PLUS (pas seulement la performance) la couverture du maillage interne reel, DANS LES
 * DEUX SENS (lecon retenue de D-028/D-028bis, appliquee des le depart cette fois, pas apres un
 * NO GO) : compare l'univers list_counts (list_type = 'length_with_pair', source de
 * App\Search\AvecTwoLettersLinksBuilder, cablage propose depuis /mots/{N}-lettres/avec/{X}
 * (palier 1, DEJA indexe, D-029)) a l'univers reellement trouve par le vrai solveur, puis verifie
 * que CHAQUE paire eligible recoit bien un lien depuis SES DEUX pages source palier 1 (avec/{X} ET
 * avec/{Y}), pas seulement une des deux.
 *
 * Usage : php scripts/bench_avec_length_2letters_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_avec_length_2letters_full_sweep.php ne s'execute qu'en CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\AvecTwoLettersLinksBuilder;
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
    for ($i = 0; $i < count($alphabet); $i++) {
        for ($j = $i + 1; $j < count($alphabet); $j++) {
            $combos[] = [$length, $alphabet[$i], $alphabet[$j]];
        }
    }
}

fwrite(STDERR, sprintf("combinaisons reelles construites : %d\n", count($combos)));

/**
 * Meme reconstruction que scripts/bench_avec_length_1letter_full_sweep.php (voir ce fichier pour
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

// Chauffe le cache avant toute mesure retenue (TTFB "chaud", CLAUDE.md). 60 requetes dispersees
// sur la plage reelle (4550 combinaisons), jamais comptabilisees.
$warmupSamples = [];
for ($i = 0; $i < 60 && $i < count($combos); $i++) {
    $warmupSamples[] = $combos[(int) ($i * count($combos) / 60)];
}
foreach ($warmupSamples as [$length, $l1, $l2]) {
    $connection = new Connection($config->dictionaryPath);
    $solver = new WordListSolver($connection);
    $solver->solve("{$length}-lettres/avec/{$l1}/{$l2}");
}

$results = [];
$anomalies = [];
$zeroResultCount = 0;
$oneResultCount = 0;

$sweepStart = microtime(true);

foreach ($combos as [$length, $l1, $l2]) {
    $rawPath = "{$length}-lettres/avec/{$l1}/{$l2}";

    $filters = WordListFilters::fromPath($rawPath);
    if ($filters === null) {
        $anomalies[] = "fromPath() a renvoye null pour {$rawPath}";
        continue;
    }

    // Sanity check syntaxique : le palier 2 doit toujours produire exactement une longueur +
    // deux lettres "avec" DISTINCTES a occurrence unique (minCount=1 chacune), rien d'autre.
    $withLettersCopy = $filters->withLetters;
    if ($filters->length !== $length || count($withLettersCopy) !== 2 || array_sum($withLettersCopy) !== 2) {
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
        'letter1' => strtoupper($l1),
        'letter2' => strtoupper($l2),
        'elapsedMs' => $elapsedMs,
        'total' => $page->total,
        'truncated' => $page->truncated,
        'queryCount' => $page->queryCount,
    ];
}

$sweepElapsedS = microtime(true) - $sweepStart;

// Dump CSV optionnel (variable d'environnement BENCH_CSV_OUT), pour analyse hors ligne du detail
// complet (pas seulement le top 20 imprime plus bas) -- jamais active par defaut, jamais lu par
// aucun autre script.
$csvOut = getenv('BENCH_CSV_OUT');
if ($csvOut !== false && $csvOut !== '') {
    $fh = fopen($csvOut, 'w');
    fputcsv($fh, ['length', 'letter1', 'letter2', 'elapsedMs', 'total', 'truncated', 'queryCount'], escape: '\\');
    foreach ($results as $r) {
        fputcsv($fh, [$r['length'], $r['letter1'], $r['letter2'], $r['elapsedMs'], $r['total'], $r['truncated'] ? 1 : 0, $r['queryCount']], escape: '\\');
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

echo "=== BALAYAGE -- avec + longueur, 2 lettres (palier 2), complet ===\n";
printf("n=%d anomalies=%d duree totale du balayage=%.1fs\n", $n, count($anomalies), $sweepElapsedS);
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

// --- Couverture du maillage (AvecTwoLettersLinksBuilder), verifiee exhaustivement, DANS LES
// --- DEUX SENS (lecon D-028/D-028bis appliquee des le depart) ---
//
// Compare TROIS univers, tous les 4550 (ou moins si anomalies), pas un echantillon :
//   1. "live"        : combinaisons avec total >= 1 d'apres le vrai solveur (ci-dessus)
//   2. "list_counts" : cles list_type='length_with_pair' presentes dans
//      storage/dictionary_fr.sqlite (source de AvecTwoLettersLinksBuilder)
//   3. "linked"      : pour CHAQUE (longueur, lettre) du perimetre palier 1 (364 pages),
//      AvecTwoLettersLinksBuilder::build(longueur, lettre) est appele -- verifie que CHAQUE
//      paire eligible apparait bien des DEUX cotes (depuis la page de sa 1re lettre ET depuis
//      la page de sa 2e lettre), pas seulement un des deux sens.

echo "\n=== COUVERTURE DU MAILLAGE (AvecTwoLettersLinksBuilder), DEUX SENS ===\n";

$liveNonZero = [];
foreach ($results as $r) {
    if ($r['total'] > 0) {
        $liveNonZero[$r['length'] . ':' . $r['letter1'] . ':' . $r['letter2']] = true;
    }
}

$listCountsStatement = $pdo->prepare("SELECT list_key FROM list_counts WHERE list_type = 'length_with_pair'");
$listCountsStatement->execute();
$listCountsKeys = [];
foreach ($listCountsStatement as $row) {
    $listCountsKeys[(string) $row['list_key']] = true;
}
$listCountsKeysInScope = array_intersect_key($listCountsKeys, array_flip(array_map(
    static fn (array $r): string => $r['length'] . ':' . $r['letter1'] . ':' . $r['letter2'],
    $results,
)));

// Construit l'univers "linked" en appelant le vrai builder pour chacune des 364 pages palier 1
// (14 longueurs x 26 lettres) -- une paire (L, A, B) est jugee "couverte des deux cotes" si elle
// apparait a la fois dans build(L, A)->links (sous la forme letter=B) ET dans build(L, B)->links
// (sous la forme letter=A).
$linkedFromLetter1 = []; // cle "L:A:B" (A<B) presente si trouvee en appelant build(L, A)
$linkedFromLetter2 = []; // cle "L:A:B" (A<B) presente si trouvee en appelant build(L, B)
$builderQueryCount = 0;
$linkedBuilder = new AvecTwoLettersLinksBuilder(new Connection($config->dictionaryPath));

for ($length = 2; $length <= 15; $length++) {
    foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $sourceLetter) {
        $links = $linkedBuilder->build($length, $sourceLetter);
        $builderQueryCount += $links->queryCount;

        foreach ($links->links as $link) {
            $partner = $link['letter'];
            $pair = [$sourceLetter, $partner];
            sort($pair, SORT_STRING);
            $key = $length . ':' . $pair[0] . ':' . $pair[1];

            if ($sourceLetter === $pair[0]) {
                $linkedFromLetter1[$key] = true;
            } else {
                $linkedFromLetter2[$key] = true;
            }
        }
    }
}

$linkedBothSides = array_intersect_key($linkedFromLetter1, $linkedFromLetter2);
$linkedOnlyOneSide = array_diff_key(
    array_merge($linkedFromLetter1, $linkedFromLetter2),
    $linkedBothSides,
);

$onlyLive = array_diff_key($liveNonZero, $listCountsKeysInScope);
$onlyListCounts = array_diff_key($listCountsKeysInScope, $liveNonZero);
$onlyLiveNotLinkedBothSides = array_diff_key($liveNonZero, $linkedBothSides);
$onlyLinkedNotLive = array_diff_key($linkedBothSides, $liveNonZero);

printf("univers live (total >= 1, vrai solveur)             : %d\n", count($liveNonZero));
printf("univers list_counts (list_type='length_with_pair')  : %d\n", count($listCountsKeysInScope));
printf("univers linked, DEUX sens confirmes                 : %d\n", count($linkedBothSides));
printf("paires liees d'UN SEUL cote (defaut de couverture)  : %d\n", count($linkedOnlyOneSide));
printf("divergence live vs list_counts (live seul)          : %d\n", count($onlyLive));
printf("divergence live vs list_counts (list_counts seul)   : %d\n", count($onlyListCounts));
printf("divergence live vs linked deux sens (live non lie)  : %d\n", count($onlyLiveNotLinkedBothSides));
printf("divergence live vs linked deux sens (lie non live)  : %d\n", count($onlyLinkedNotLive));
printf("requetes AvecTwoLettersLinksBuilder::build() cumulees sur les 364 pages palier 1 : %d\n", $builderQueryCount);

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
if ($linkedOnlyOneSide !== []) {
    echo "--- LIE D'UN SEUL COTE (defaut de couverture, max 20 affiches) ---\n";
    foreach (array_slice(array_keys($linkedOnlyOneSide), 0, 20) as $k) {
        echo $k . "\n";
    }
}
if ($onlyLiveNotLinkedBothSides !== []) {
    echo "--- LIVE NON LIE DES DEUX COTES (max 20 affiches) ---\n";
    foreach (array_slice(array_keys($onlyLiveNotLinkedBothSides), 0, 20) as $k) {
        echo $k . "\n";
    }
}
if ($onlyLinkedNotLive !== []) {
    echo "--- LIE DES DEUX COTES MAIS NON LIVE (paire a 0 resultat pourtant liee) ---\n";
    foreach (array_keys($onlyLinkedNotLive) as $k) {
        echo $k . "\n";
    }
}

echo "\nFIN\n";
