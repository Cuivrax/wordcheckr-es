<?php

declare(strict_types=1);

/**
 * Jetable -- balayage complet des 611 pages sources reelles (/mots/commencant/{X}/terminant/{Y},
 * list_type 'start_end', D-024) pour App\Search\StartEndWithLinksBuilder::build() (maillage
 * commencant+terminant+avec, tache 2026-08-18). Connexion PDO neuve a chaque combinaison (fidele
 * a une vraie requete HTTP, D-016), meme patron que les balayages precedents
 * (bench_combined_length_full_sweep.php, bench_avec_length_3letters_full_sweep.php). Aucune
 * ecriture, jamais execute en production (D-007).
 *
 * Usage : php scripts/bench_start_end_with_links_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_start_end_with_links_full_sweep.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/app/bootstrap.php';

use App\Database\Connection;
use App\Search\StartEndWithLinksBuilder;

$dbPath = $root . '/storage/dictionary_fr.sqlite';

$readPairs = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pairs = [];
foreach ($readPairs->query("SELECT list_key FROM list_counts WHERE list_type = 'start_end'") as $row) {
    $pairs[] = explode(':', (string) $row['list_key'], 2);
}
unset($readPairs);

printf("pages sources reelles (list_type 'start_end') : %d\n", count($pairs));

// Chauffe (dispersee, jamais comptabilisee).
foreach (array_slice($pairs, 0, 20) as [$s, $e]) {
    $connection = new Connection($dbPath);
    (new StartEndWithLinksBuilder($connection))->build($s, $e);
}

$timings = [];
$maxLinks = 0;
$maxLinksPair = '';
$anomalies = 0;
$totalLinks = 0;
$queryCounts = [];

foreach ($pairs as [$start, $end]) {
    $connection = new Connection($dbPath);
    $builder = new StartEndWithLinksBuilder($connection);

    $t0 = microtime(true);
    $links = $builder->build($start, $end);
    $elapsedMs = (microtime(true) - $t0) * 1000;

    $timings[] = $elapsedMs;
    $queryCounts[$links->queryCount] = ($queryCounts[$links->queryCount] ?? 0) + 1;

    if ($links->queryCount !== 1) {
        $anomalies++;
    }

    $count = count($links->links);
    $totalLinks += $count;

    if ($count > $maxLinks) {
        $maxLinks = $count;
        $maxLinksPair = "{$start}:{$end}";
    }
}

sort($timings);
$n = count($timings);

function percentile(array $sorted, float $p): float
{
    $n = count($sorted);
    $rank = (int) round($p * ($n - 1));

    return $sorted[$rank];
}

printf("n = %d (executees)          anomalies queryCount != 1 = %d\n", $n, $anomalies);
printf("min  = %.3f ms\n", $timings[0]);
printf("p50  = %.3f ms\n", percentile($timings, 0.50));
printf("p95  = %.3f ms\n", percentile($timings, 0.95));
printf("p99  = %.3f ms\n", percentile($timings, 0.99));
printf("max  = %.3f ms\n", $timings[$n - 1]);

$overBudget = 0;
foreach ($timings as $t) {
    if ($t > 250.0) {
        $overBudget++;
    }
}
printf("cas au-dessus de 250 ms budget TTFB : %d / %d\n", $overBudget, $n);

printf("repartition queryCount : %s\n", json_encode($queryCounts));
printf("total liens produits (toutes pages) : %d\n", $totalLinks);
printf("page avec le plus de liens : %s (%d liens)\n", $maxLinksPair, $maxLinks);
printf("moyenne liens/page : %.2f\n", $totalLinks / $n);
