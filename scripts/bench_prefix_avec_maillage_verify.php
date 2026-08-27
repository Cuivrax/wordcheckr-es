<?php

declare(strict_types=1);

/**
 * Script de verification JETABLE (data-engine, tache "maillage commencant+avec sans longueur",
 * 2026-08-18) -- PAS un script de build, ne modifie rien, aucune ecriture. Confirme, depuis le
 * vrai code de production (App\Search\WordListFilters::fromPath()) ET une requete directe sur
 * `terms` (lecture seule), les chiffres REELS post-D-032 sur les 676 combinaisons
 * /mots/commencant/{X}/avec/{Y} (26 x 26, prefixe d'une lettre + une lettre "avec", occurrence
 * unique, SANS longueur) -- ne fait confiance a aucun chiffre annonce a l'avance.
 *
 * Usage : php scripts/bench_prefix_avec_maillage_verify.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_prefix_avec_maillage_verify.php ne s'execute qu'en CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\WordListFilters;

$config = Config::load('fr');

if (!is_file($config->dictionaryPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$config->dictionaryPath}\n");
    exit(1);
}

$connection = new Connection($config->dictionaryPath);
$pdo = $connection->pdo();

$alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

$raw = 0;
$degenerate = 0;
$nonDegenerate = 0;
$zeroResult = 0;
$exactlyOne = 0;
$moreThanOne = 0;

$countStmt = $pdo->prepare(
    "SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = ? AND instr(normalized, ?) > 0"
);

$degenerateSample = [];
$zeroSample = [];

foreach ($alphabet as $x) {
    $parentUrl = WordListFilters::fromPath('commencant/' . strtolower($x))?->canonicalUrl();

    foreach ($alphabet as $y) {
        $raw++;

        $candidateUrl = WordListFilters::fromPath('commencant/' . strtolower($x) . '/avec/' . strtolower($y))?->canonicalUrl();

        if ($x === $y) {
            // Cas diagonal : DOIT etre collapse (D-032) vers la page parente elle-meme.
            if ($candidateUrl !== $parentUrl) {
                fwrite(STDERR, "ANOMALIE : commencant/{$x}/avec/{$y} (diagonal) N'EST PAS collapse -- attendu {$parentUrl}, obtenu {$candidateUrl}\n");
            } else {
                $degenerate++;
            }
            continue;
        }

        // Cas non diagonal : ne doit JAMAIS etre collapse.
        $expectedUrl = '/mots/commencant/' . strtolower($x) . '/avec/' . strtolower($y);
        if ($candidateUrl !== $expectedUrl) {
            fwrite(STDERR, "ANOMALIE : commencant/{$x}/avec/{$y} (non diagonal) URL inattendue -- attendu {$expectedUrl}, obtenu " . ($candidateUrl ?? 'null') . "\n");
        }

        $nonDegenerate++;

        $countStmt->execute([$x, $y]);
        $count = (int) $countStmt->fetch()['c'];

        if ($count === 0) {
            $zeroResult++;
            $zeroSample[] = "{$x}+{$y}";
        } elseif ($count === 1) {
            $exactlyOne++;
        } else {
            $moreThanOne++;
        }
    }
}

printf("combinaisons brutes (26x26)                    : %d\n", $raw);
printf("degenerees (X=Y, collapsees D-032)              : %d\n", $degenerate);
printf("non degenerees (676 - degenerees)               : %d\n", $nonDegenerate);
printf("  dont a 0 resultat                             : %d\n", $zeroResult);
printf("  dont a exactement 1 resultat                  : %d\n", $exactlyOne);
printf("  dont a plus d'1 resultat                      : %d\n", $moreThanOne);
printf("candidats reels maillables (non degenerees, >=1) : %d\n", $nonDegenerate - $zeroResult);
printf("\nechantillon 0 resultat : %s\n", implode(', ', $zeroSample));
