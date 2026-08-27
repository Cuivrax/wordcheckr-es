<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\Suggester;
use App\Search\TermPage;
use Tests\Support\Assert;

/**
 * Exerce App\Search\Suggester sur la vraie base storage/dictionary_es.sqlite (lecture seule) :
 * bornes d'entree (0/1/2 caracteres), prefixe exact verifie par force brute, plafond de 8
 * entrees, ordre alphabetique deterministe, jamais d'exception sur une entree malformee, et le
 * modele a trois statuts (jamais STATUS_UNKNOWN pour une ligne presente en base).
 *
 * Adapte de tests/Search/SuggesterTest.php (site francais) -- prefixes/mots pivots remplaces
 * par des equivalents espagnols reels (statistiques recalculees sur storage/dictionary_es.sqlite).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
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
    Assert::same([], $suggester->suggest("d'a"), "apostrophe -> hors A-Z/Ñ -> tableau vide");
    Assert::same([], $suggester->suggest('12'), 'chiffres -> hors A-Z/Ñ -> tableau vide');
    Assert::same([], $suggester->suggest("\xFF\xFE"), 'UTF-8 invalide -> tableau vide, jamais d\'exception');
    Assert::same(
        [],
        $suggester->suggest(str_repeat('A', 20)),
        '20 lettres > MAX_LENGTH (15) -- aucune ligne ne peut jamais commencer par un prefixe plus long que la plus longue forme retenue'
    );

    // --- Prefixe exact, 3 lettres : verifie par force brute (pas un echantillon), plafond de
    // --- 8 entrees, ordre alphabetique. "WAT" choisi : peu de correspondances en base reelle
    // --- (mesure : 6, < 8), verifie le panier complet, pas seulement les 8 premieres. ---
    $wat = $suggester->suggest('wat');
    $bruteForceWat = [];
    foreach ($pdo->query("SELECT normalized FROM terms WHERE normalized LIKE 'WAT%'") as $row) {
        if (str_starts_with($row['normalized'], 'WAT')) {
            $bruteForceWat[] = $row['normalized'];
        }
    }
    sort($bruteForceWat, SORT_STRING);
    Assert::true(count($bruteForceWat) <= Suggester::MAX_RESULTS, 'WAT choisi car sous le plafond -- sinon adapter le test');
    Assert::same($bruteForceWat, array_column($wat, 'normalized'), 'prefixe WAT, panier complet identique a la force brute');
    foreach ($wat as $item) {
        Assert::true(str_starts_with($item['normalized'], 'WAT'), 'prefixe EXACT uniquement');
        Assert::same(mb_strtolower($item['normalized'], 'UTF-8'), $item['slug']);
        Assert::true(in_array($item['status'], [TermPage::STATUS_ADMITTED, TermPage::STATUS_FRENCH_NOT_ADMITTED], true), 'jamais STATUS_UNKNOWN sur une ligne de `terms`');
        Assert::same($item['isOds8'] || $item['isOds9'], $item['status'] === TermPage::STATUS_ADMITTED, 'statut coherent avec isOds8/isOds9');
    }

    // --- Prefixe tres frequent ("RE", 38 472 correspondances mesurees) : plafond de 8 entrees
    // --- respecte, jamais un scan deguise, ordre alphabetique strictement croissant. ---
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

    // --- Prefixe le plus long possible (15 lettres) : au plus une correspondance (le mot
    // --- lui-meme), jamais d'erreur. ---
    $fifteen = $suggester->suggest('abaldonadamente');
    Assert::true(count($fifteen) <= 1);
    foreach ($fifteen as $item) {
        Assert::same('ABALDONADAMENTE', $item['normalized']);
    }

    // --- Insensibilite a la casse et aux diacritiques d'entree, meme regle que partout
    // --- ailleurs : "ré" normalise en "RE", memes resultats que 'RE' saisi direct. ---
    $reAccent = $suggester->suggest('ré');
    Assert::same(array_column($re, 'normalized'), array_column($reAccent, 'normalized'), 'normalisation identique a la saisie directe');

    // =====================================================================
    // Ñ -- regression specifique espagnole. Avant le correctif rangeBounds()/
    // ALPHABET_ORDER (voir WordListSolver::rangeBounds()), Ñ n'etait pas correctement
    // borne en fin de plage (traite comme un caractere quelconque au lieu de la
    // derniere lettre effective de l'alphabet sur cette colonne).
    // =====================================================================
    $enye = $suggester->suggest('ño');
    $bruteForceEnye = [];
    foreach ($pdo->query("SELECT normalized FROM terms WHERE normalized LIKE 'ÑO%' ORDER BY normalized LIMIT " . Suggester::MAX_RESULTS) as $row) {
        $bruteForceEnye[] = $row['normalized'];
    }
    Assert::same($bruteForceEnye, array_column($enye, 'normalized'), 'suggest(ño) doit rester correct (prefixe Ñ, borne de plage propre au site espagnol)');
    foreach ($enye as $item) {
        Assert::true(str_starts_with($item['normalized'], 'ÑO'), 'prefixe EXACT uniquement');
        Assert::same(mb_strtolower($item['normalized'], 'UTF-8'), $item['slug'], 'slug doit correctement abaisser Ñ (mb_strtolower, pas strtolower)');
    }

    // "ZZ" ne doit JAMAIS inclure de mot commencant par Ñ dans ses suggestions (bug reel
    // possible si rangeBounds() traitait encore Z comme la derniere lettre de l'alphabet :
    // la plage serait alors restee ouverte et aurait pu remonter des mots en Ñ apres les
    // ZZ*, selon l'ordre alphabetique -- verifie explicitement plutot que suppose).
    $zz = $suggester->suggest('zz');
    foreach ($zz as $item) {
        Assert::true(str_starts_with($item['normalized'], 'ZZ'), 'suggest(zz) ne doit jamais renvoyer un mot hors du prefixe ZZ (jamais de mot en Ñ)');
    }
};
