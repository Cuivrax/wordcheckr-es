<?php

declare(strict_types=1);

/**
 * Jetable -- balayage complet des 26 pages sources reelles (/mots/commencant/{X}, une lettre,
 * deja indexees D-017) pour App\Search\PrefixAvecLinksBuilder::build() (maillage commencant+avec
 * sans terminant ni longueur, tache 2026-08-18). Connexion PDO neuve a chaque prefixe (fidele a
 * une vraie requete HTTP, D-016), meme patron que scripts/bench_start_end_with_links_full_sweep.php.
 * Aucune ecriture, jamais execute en production (D-007).
 *
 * Usage : php scripts/bench_prefix_avec_links_full_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_prefix_avec_links_full_sweep.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/app/bootstrap.php';

use App\Database\Connection;
use App\Search\PrefixAvecLinksBuilder;

$dbPath = $root . '/storage/dictionary_fr.sqlite';

$alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

printf("pages sources reelles (commencant, une lettre) : %d\n", count($alphabet));

// Chauffe (dispersee, jamais comptabilisee).
foreach (['A', 'F', 'K', 'P', 'U', 'Z', 'M', 'R'] as $p) {
    $connection = new Connection($dbPath);
    (new PrefixAvecLinksBuilder($connection))->build($p);
}

$timings = [];
$maxLinks = 0;
$maxLinksPrefix = '';
$anomalies = 0;
$totalLinks = 0;
$queryCounts = [];

foreach ($alphabet as $prefix) {
    $connection = new Connection($dbPath);
    $builder = new PrefixAvecLinksBuilder($connection);

    $t0 = microtime(true);
    $links = $builder->build($prefix);
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
        $maxLinksPrefix = $prefix;
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
printf("page avec le plus de liens : %s (%d liens)\n", $maxLinksPrefix, $maxLinks);
printf("moyenne liens/page : %.2f\n", $totalLinks / $n);
