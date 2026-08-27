<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\Suggester;
use App\Search\TermPage;
use Tests\Support\Assert;

/**
 * Exerce App\Search\Suggester sur la vraie base storage/dictionary_fr.sqlite (lecture seule) :
 * bornes d'entree (0/1/2 caracteres), prefixe exact verifie par force brute, plafond de 8
 * entrees, ordre alphabetique deterministe, jamais d'exception sur une entree malformee, et le
 * modele a trois statuts (jamais STATUS_UNKNOWN pour une ligne presente en base, D-013).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $suggester = new Suggester($connection);

    // --- Bornes : 0 et 1 caractere normalise -> tableau vide, aucune requete necessaire
    // --- (Normalizer::isValid() coupe avant toute ouverture de curseur). ---
    Assert::same([], $suggester->suggest(''), '0 caractere -> tableau vide');
    Assert::same([], $suggester->suggest('a'), '1 lettre normalisee -> tableau vide');
    Assert::same([], $suggester->suggest('  '), 'espaces seuls -> tableau vide');
    Assert::same([], $suggester->suggest("é"), "diacritique seul, 1 lettre normalisee -> tableau vide");

    // --- Entree non exploitable : jamais d'exception, jamais d'erreur, tableau vide. ---
    Assert::same([], $suggester->suggest("d'a"), "apostrophe -> hors A-Z -> tableau vide");
    Assert::same([], $suggester->suggest('12'), 'chiffres -> hors A-Z -> tableau vide');
    Assert::same([], $suggester->suggest("\xFF\xFE"), 'UTF-8 invalide -> tableau vide, jamais d\'exception');
    Assert::same(
        [],
        $suggester->suggest(str_repeat('A', 20)),
        '20 lettres > MAX_LENGTH (15, D-010) -- aucune ligne ne peut jamais commencer par un prefixe plus long que la plus longue forme retenue'
    );

    // --- Prefixe exact, 3 lettres : verifie par force brute (pas un echantillon), plafond de
    // --- 8 entrees, ordre alphabetique. "KYU" choisi : peu de correspondances en base reelle
    // --- (mesure : 4, < 8), verifie le panier complet, pas seulement les 8 premieres. ---
    $kyu = $suggester->suggest('kyu');
    $bruteForceKyu = [];
    foreach ($pdo->query("SELECT normalized FROM terms WHERE normalized LIKE 'KYU%'") as $row) {
        if (str_starts_with($row['normalized'], 'KYU')) {
            $bruteForceKyu[] = $row['normalized'];
        }
    }
    sort($bruteForceKyu, SORT_STRING);
    Assert::true(count($bruteForceKyu) <= Suggester::MAX_RESULTS, 'KYU choisi car sous le plafond -- sinon adapter le test');
    Assert::same($bruteForceKyu, array_column($kyu, 'normalized'), 'prefixe KYU, panier complet identique a la force brute');
    foreach ($kyu as $item) {
        Assert::true(str_starts_with($item['normalized'], 'KYU'), 'prefixe EXACT uniquement');
        Assert::same(strtolower($item['normalized']), $item['slug']);
        Assert::true(in_array($item['status'], [TermPage::STATUS_ADMITTED, TermPage::STATUS_FRENCH_NOT_ADMITTED], true), 'jamais STATUS_UNKNOWN sur une ligne de `terms` (D-013)');
        Assert::same($item['isOds8'] || $item['isOds9'], $item['status'] === TermPage::STATUS_ADMITTED, 'statut coherent avec isOds8/isOds9');
    }

    // --- Prefixe tres frequent ("RE", pire cas mesure de la base reelle) : plafond de 8
    // --- entrees respecte, jamais un scan deguise (verifie separement par EXPLAIN QUERY PLAN,
    // --- reports/query-plans/phase5.md), ordre alphabetique strictement croissant. ---
    $re = $suggester->suggest('re');
    Assert::true(count($re) <= Suggester::MAX_RESULTS, 'jamais plus de MAX_RESULTS entrees');
    Assert::true(count($re) === Suggester::MAX_RESULTS, 'RE compte plus de 8 correspondances en base reelle -- plafond attendu atteint');
    for ($i = 0; $i < count($re); $i++) {
        Assert::true(str_starts_with($re[$i]['normalized'], 'RE'), 'prefixe EXACT uniquement');

        if ($i > 0) {
            Assert::true($re[$i - 1]['normalized'] < $re[$i]['normalized'], 'ordre alphabetique strictement croissant (normalized est UNIQUE, aucune egalite possible)');
        }
    }
    // Determinisme : deux appels successifs renvoient EXACTEMENT la meme sequence.
    $reAgain = $suggester->suggest('re');
    Assert::same(array_column($re, 'normalized'), array_column($reAgain, 'normalized'), 'reponse triee de facon deterministe, deux appels identiques renvoient la meme sequence');

    // --- Prefixe le plus long possible (15 lettres, D-010) : au plus une correspondance
    // --- (le mot lui-meme), jamais d'erreur. ---
    $fifteen = $suggester->suggest('abandonneraient');
    Assert::true(count($fifteen) <= 1);
    foreach ($fifteen as $item) {
        Assert::same('ABANDONNERAIENT', $item['normalized']);
    }

    // --- Insensibilite a la casse et aux diacritiques d'entree, meme regle que partout
    // --- ailleurs (D-009) : "ré" normalise en "RE", memes resultats que 'RE' saisi direct. ---
    $reAccent = $suggester->suggest('ré');
    Assert::same(array_column($re, 'normalized'), array_column($reAccent, 'normalized'), 'normalisation identique a la saisie directe (D-009)');
};
