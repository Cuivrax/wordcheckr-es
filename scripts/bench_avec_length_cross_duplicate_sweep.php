<?php

declare(strict_types=1);

/**
 * Jetable -- detection EXHAUSTIVE des doublons de contenu CROISES entre l'axe LONGUEUR
 * (App\Search\LengthLinksBuilder::byStartEnd, "/mots/{N}-lettres/commencant/{X}/terminant/{Y}",
 * Family::WORD_LIST_COMBINED) et l'axe AVEC (App\Search\StartEndWithLinksBuilder,
 * "/mots/commencant/{X}/terminant/{Y}/avec/{Z}", Family::WORD_LIST_COMBINED_WITH_LETTER), sur les
 * 611 paires commencant+terminant reelles (3e audit consolide de la serie, 2026-08-19, NO GO).
 *
 * Deux methodes independantes :
 * 1. egalite d'ensemble DIRECTE, via les VRAIS builders (LengthLinksBuilder pour les 14
 *    longueurs, StartEndWithLinksBuilder pour les 611 paires), panier recupere une seule fois par
 *    paire, tranche par longueur (strlen) et par lettre (str_contains), comparaison de tableau
 * 2. comptes SQL croises INDEPENDANTS (COUNT(length=L), COUNT(instr(Z)>0), COUNT(length=L AND
 *    instr(Z)>0)) pour chaque match trouve par la methode 1 -- confirmation par un algorithme
 *    different, sans jamais comparer de tableaux
 *
 * Resultat fige dans App\Search\StartEndWithLinksBuilder::CROSS_DUPLICATE_LENGTH_KEYS (333
 * entrees) -- voir reports/query-plans/avec-doublons-croises-longueur-correctif.md pour le detail
 * complet, la liste des 333 collisions et les mesures avant/apres.
 *
 * IMPORTANT -- double usage de ce script selon l'etat du code au moment ou il tourne :
 * - AVANT le correctif (etat historique, D-038) : StartEndWithLinksBuilder::build() ne filtrait
 *   pas encore CROSS_DUPLICATE_LENGTH_KEYS -- ce script trouvait 333 matches (methode 1),
 *   confirmes 333/333 par la methode 2. C'est l'execution qui a produit la liste figee.
 * - APRES le correctif (etat actuel de app/Search/StartEndWithLinksBuilder.php) : l'axe 2 ne
 *   produit plus jamais ces 333 lignes -- ce script doit desormais trouver 0 match, ce qui est le
 *   resultat ATTENDU et constitue une preuve d'exhaustivite independante (aucune collision
 *   residuelle au-dela de la liste figee), pas un echec de detection.
 *
 * Usage : php scripts/bench_avec_length_cross_duplicate_sweep.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/bench_avec_length_cross_duplicate_sweep.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/app/bootstrap.php';

use App\Database\Connection;
use App\Search\LengthLinksBuilder;
use App\Search\StartEndWithLinksBuilder;

$dbPath = $root . '/storage/dictionary_fr.sqlite';
$rawPdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// --- 1. 611 paires commencant+terminant reelles (D-024). ---
$pairs = [];
foreach ($rawPdo->query("SELECT list_key FROM list_counts WHERE list_type = 'start_end'") as $row) {
    $pairs[] = explode(':', (string) $row['list_key'], 2);
}
printf("paires reelles : %d\n", count($pairs));

// --- 2. Axe 1 survivants : le VRAI LengthLinksBuilder::build($length) pour les 14 longueurs,
// --- regroupe par paire (deja purge des 52 doublons D-025 par le code reel). ---
$connection = new Connection($dbPath);
$lengthBuilder = new LengthLinksBuilder($connection);

$axis1 = [];
for ($length = 2; $length <= 15; $length++) {
    $links = $lengthBuilder->build($length);
    foreach ($links->byStartEnd as $group) {
        $start = $group['start'];
        foreach ($group['letters'] as $entry) {
            $end = $entry['letter'];
            $axis1["{$start}:{$end}"][$length] = ['url' => $entry['url'], 'count' => $entry['count']];
        }
    }
}

$axis1PairCount = count($axis1);
$axis1TotalEntries = array_sum(array_map('count', $axis1));
printf(
    "axe 1 (length_start_end survivants apres D-025) : %d paires concernees, %d entrees (longueur,paire) au total\n",
    $axis1PairCount,
    $axis1TotalEntries
);

// --- 3. Axe 2 survivants : le VRAI StartEndWithLinksBuilder::build(), par paire (deja purge des
// --- 1198 degenerees D-032, 227 doublons-parent D-037, 428 doublons-soeurs D-038). ---
$withBuilder = new StartEndWithLinksBuilder($connection);

$axis2 = [];
$axis2TotalEntries = 0;
foreach ($pairs as [$start, $end]) {
    $links = $withBuilder->build($start, $end);
    if ($links->links === []) {
        continue;
    }
    foreach ($links->links as $entry) {
        $axis2["{$start}:{$end}"][$entry['letter']] = ['url' => $entry['url'], 'count' => $entry['count']];
        $axis2TotalEntries++;
    }
}
printf(
    "axe 2 (start_end_with survivants apres D-032+D-037+D-038) : %d paires concernees, %d entrees (lettre,paire) au total\n",
    count($axis2),
    $axis2TotalEntries
);

// --- 4. Methode 1 : egalite d'ensemble directe, pour chaque paire ayant des survivants sur les
// --- deux axes a la fois. ---
$matches = [];
$pairsCompared = 0;
$comparisonsDone = 0;

$basketStmt = $rawPdo->prepare(
    'SELECT normalized FROM terms WHERE substr(normalized, 1, 1) = ? AND substr(reversed, 1, 1) = ? ORDER BY normalized'
);

foreach ($pairs as [$start, $end]) {
    $key = "{$start}:{$end}";
    $lengths = $axis1[$key] ?? [];
    $letters = $axis2[$key] ?? [];

    if ($lengths === [] || $letters === []) {
        continue;
    }

    $pairsCompared++;

    $basketStmt->execute([$start, $end]);
    $basket = $basketStmt->fetchAll(PDO::FETCH_COLUMN);

    $lengthSlices = [];
    foreach (array_keys($lengths) as $length) {
        $lengthSlices[$length] = array_values(array_filter($basket, static fn (string $w): bool => strlen($w) === $length));
    }

    $letterSlices = [];
    foreach (array_keys($letters) as $letter) {
        $letterSlices[$letter] = array_values(array_filter($basket, static fn (string $w): bool => str_contains($w, $letter)));
    }

    foreach ($lengthSlices as $length => $lSlice) {
        $lCount = count($lSlice);
        foreach ($letterSlices as $letter => $zSlice) {
            $comparisonsDone++;
            if ($lCount !== count($zSlice)) {
                continue; // filtre bon marche avant comparaison complete
            }
            if ($lSlice === $zSlice) {
                $matches[] = [
                    'start' => $start,
                    'end' => $end,
                    'length' => $length,
                    'letter' => $letter,
                    'words' => $lSlice,
                ];
            }
        }
    }
}

printf("paires comparees (axe1 ET axe2 non vides) : %d\n", $pairsCompared);
printf("comparaisons (longueur,lettre) effectuees : %d\n", $comparisonsDone);
printf("MATCHES trouves (methode 1, egalite directe) : %d\n", count($matches));

// --- 5. Methode 2 (SQL, comptes croises, requete fraiche INDEPENDANTE du panier PHP de la
// --- methode 1) pour chacun des matches trouves. ---
$countStmt = $rawPdo->prepare(
    'SELECT '
    . 'SUM(CASE WHEN length = ? THEN 1 ELSE 0 END) c_length, '
    . 'SUM(CASE WHEN instr(normalized, ?) > 0 THEN 1 ELSE 0 END) c_letter, '
    . 'SUM(CASE WHEN length = ? AND instr(normalized, ?) > 0 THEN 1 ELSE 0 END) c_both '
    . 'FROM terms WHERE substr(normalized, 1, 1) = ? AND substr(reversed, 1, 1) = ?'
);

$verifiedCount = 0;
$divergences = 0;

foreach ($matches as $m) {
    $countStmt->execute([$m['length'], $m['letter'], $m['length'], $m['letter'], $m['start'], $m['end']]);
    $row = $countStmt->fetch();
    $cLength = (int) $row['c_length'];
    $cLetter = (int) $row['c_letter'];
    $cBoth = (int) $row['c_both'];

    if ($cLength === $cLetter && $cLetter === $cBoth && $cLength === count($m['words'])) {
        $verifiedCount++;
    } else {
        $divergences++;
        printf(
            "DIVERGENCE methode2 : %s:%s length=%d letter=%s (c_length=%d c_letter=%d c_both=%d attendu=%d)\n",
            $m['start'],
            $m['end'],
            $m['length'],
            $m['letter'],
            $cLength,
            $cLetter,
            $cBoth,
            count($m['words'])
        );
    }
}

printf("methode 2 (SQL comptes croises) : %d/%d confirmes, %d divergences\n", $verifiedCount, count($matches), $divergences);

$exactlyOne = 0;
foreach ($matches as $m) {
    if (count($m['words']) === 1) {
        $exactlyOne++;
    }
}
printf("dont a exactement 1 mot partage : %d\n", $exactlyOne);

$distinctPairs = [];
foreach ($matches as $m) {
    $distinctPairs[$m['start'] . ':' . $m['end']] = true;
}
printf("paires distinctes concernees : %d\n", count($distinctPairs));

if (count($matches) === 0) {
    echo "\n0 match : le correctif CROSS_DUPLICATE_LENGTH_KEYS est deja applique dans "
        . "StartEndWithLinksBuilder::build() -- ce resultat est ATTENDU (preuve d'exhaustivite "
        . "post-correctif), pas un echec de detection. Voir reports/query-plans/"
        . "avec-doublons-croises-longueur-correctif.md pour l'execution historique (pre-correctif, "
        . "333 matches trouves) qui a produit la liste figee.\n";
} else {
    echo "\n" . count($matches) . " match(es) trouve(s) : soit une execution pre-correctif, soit "
        . "une reconstruction de la base a fait apparaitre de nouvelles collisions -- comparer a "
        . "App\\Search\\StartEndWithLinksBuilder::CROSS_DUPLICATE_LENGTH_KEYS (333 entrees connues).\n";
}
