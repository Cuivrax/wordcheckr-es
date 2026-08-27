<?php

declare(strict_types=1);

/**
 * Jetable -- mesure la methode de calcul du nouveau list_type 'start_end_with' (maillage
 * commencant+terminant+avec, tache 2026-08-18) AVANT de choisir l'approche retenue dans
 * scripts/build_explore_hub_counts.php : GROUP BY SQL (26 requetes, une par lettre "avec",
 * filtree par instr()) contre UN SEUL parcours PHP (meme principe que 'length_with'/
 * 'length_with_pair'/'length_with_triple'). Lecture seule sur storage/dictionary_fr.sqlite,
 * aucune ecriture -- jamais execute en production (D-007).
 *
 * Usage : php scripts/bench_start_end_with_build.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_start_end_with_build.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

// --- Methode A : 26 requetes GROUP BY SQL, une par lettre "avec", filtrees par instr(). ---
$startA = microtime(true);
$rowsA = 0;
$resultA = [];

foreach ($alphabet as $letter) {
    $stmt = $pdo->prepare(
        "SELECT substr(normalized, 1, 1) s, substr(reversed, 1, 1) e, COUNT(*) n FROM terms"
        . ' WHERE instr(normalized, ?) > 0 GROUP BY s, e'
    );
    $stmt->execute([$letter]);

    foreach ($stmt as $row) {
        $key = $row['s'] . ':' . $row['e'] . ':' . $letter;
        $resultA[$key] = (int) $row['n'];
        $rowsA++;
    }
}

$elapsedA = microtime(true) - $startA;

printf("Methode A (26x GROUP BY SQL, filtre instr())   : %.3f s, %d lignes non vides\n", $elapsedA, $rowsA);

// --- Methode B : un seul parcours PHP sequentiel de `terms`. ---
$startB = microtime(true);
$counts = [];

$allTermsStatement = $pdo->query('SELECT normalized, reversed FROM terms');
foreach ($allTermsStatement as $row) {
    $normalized = (string) $row['normalized'];
    $start = $normalized[0];
    $end = (string) $row['reversed'];
    $end = $end[0];
    $distinctLetters = str_split(count_chars($normalized, 3));

    foreach ($distinctLetters as $letter) {
        $key = $start . ':' . $end . ':' . $letter;
        $counts[$key] = ($counts[$key] ?? 0) + 1;
    }
}

$elapsedB = microtime(true) - $startB;
$rowsB = count($counts);

printf("Methode B (1 parcours PHP sequentiel)          : %.3f s, %d lignes non vides\n", $elapsedB, $rowsB);

// --- Coherence : les deux methodes doivent produire EXACTEMENT le meme jeu de lignes/comptes. ---
ksort($resultA);
ksort($counts);

$divergences = 0;
foreach ($resultA as $key => $n) {
    if (($counts[$key] ?? null) !== $n) {
        $divergences++;
    }
}
foreach ($counts as $key => $n) {
    if (($resultA[$key] ?? null) !== $n) {
        $divergences++;
    }
}

printf("Lignes methode A == lignes methode B           : %s (A=%d, B=%d)\n", $rowsA === $rowsB ? 'oui' : 'NON', $rowsA, $rowsB);
printf("Divergences de compte (deux sens)               : %d\n", $divergences);
printf("Methode retenue                                : %s\n", $elapsedB <= $elapsedA ? 'B (PHP)' : 'A (SQL)');
