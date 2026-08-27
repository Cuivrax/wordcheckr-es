<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\PositionLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\PositionLinksBuilder (D-023bis) : maillage "avec {X}" -> position exacte depuis
 * une page "mots de {N} lettres avec {X}" -- lu depuis list_counts (precalcule par
 * scripts/build_explore_hub_counts.php), jamais un calcul sur `terms` au runtime. Verifie par
 * force brute sur la vraie base (lecture seule), meme methodologie que LengthLinksBuilderTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new PositionLinksBuilder($connection);

    $links = $builder->build(9, 'W');

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($links->links !== [], 'sanity check : des mots de 9 lettres avec W existent');

    // --- Position 1 doit pointer vers commencant, jamais vers position/1/... (collapse D-023). ---
    $first = array_values(array_filter($links->links, static fn (array $l): bool => $l['position'] === 1));
    Assert::true(count($first) === 1);
    Assert::same('/mots/9-lettres/commencant/w', $first[0]['url']);
    $expectedFirst = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized LIKE 'W%'")->fetch()['c'];
    Assert::same($expectedFirst, $first[0]['count'], 'verifie par force brute');

    // --- Derniere position doit pointer vers terminant, jamais vers position/9/... ---
    $last = array_values(array_filter($links->links, static fn (array $l): bool => $l['position'] === 9));
    Assert::true(count($last) === 1);
    Assert::same('/mots/9-lettres/terminant/w', $last[0]['url']);
    $expectedLast = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized LIKE '%W'")->fetch()['c'];
    Assert::same($expectedLast, $last[0]['count'], 'verifie par force brute');

    // --- Position intermediaire : URL position/{P}/{X}, verifiee par force brute (substr). ---
    $middle = array_values(array_filter($links->links, static fn (array $l): bool => $l['position'] === 3));
    Assert::true(count($middle) === 1);
    Assert::same('/mots/9-lettres/position/3/w', $middle[0]['url']);
    $expectedMiddle = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 3, 1) = 'W'")->fetch()['c'];
    Assert::same($expectedMiddle, $middle[0]['count'], 'verifie par force brute');

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ($links->links as $link) {
        Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
    }

    // --- Tri par position croissante. ---
    for ($i = 1; $i < count($links->links); $i++) {
        Assert::true($links->links[$i - 1]['position'] < $links->links[$i]['position'], 'ordre par position attendu');
    }

    // --- Coherence : chaque position <= longueur, jamais au-dela. ---
    foreach ($links->links as $link) {
        Assert::true($link['position'] >= 1 && $link['position'] <= 9);
    }

    // ============================================================================================
    // Doublons de contenu CROISES avec une famille EXTERIEURE a "position" (D-041, garde-fou
    // structurel demande par le constat C-4 du 4e audit consolide, docs/DECISIONS.md D-040) --
    // balayage GENERIQUE de tout le registre (scripts/check_combinatorial_duplicates.php, balayage
    // du 2026-08-21 : 1 656 groupes, 2 089 pages en exces). Voir
    // PositionLinksBuilder::EXTERNAL_DUPLICATE_KEYS pour le detail complet des deux cles.
    // ============================================================================================
    $reflection = new ReflectionClass(PositionLinksBuilder::class);
    $externalDuplicateKeys = $reflection->getConstant('EXTERNAL_DUPLICATE_KEYS');
    Assert::same(['13:W:10', '15:W:10'], $externalDuplicateKeys, 'exactement 2 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');

    // --- 13-lettres/position/10/w perd face a 13-lettres/commencant/c/terminant/h
    // --- (Family::WORD_LIST_COMBINED avec longueur, 3 composants, "commencant" precede "position"
    // --- dans l'ordre canonique) -- ne doit jamais etre produit par le builder depuis sa page
    // --- source reelle /mots/13-lettres/avec/w. ---
    $links13W = $builder->build(13, 'W');
    Assert::same(1, $links13W->queryCount);
    $position10 = array_values(array_filter($links13W->links, static fn (array $l): bool => $l['position'] === 10));
    Assert::true($position10 === [], '13:W:10 est un doublon de contenu croise (D-041) -- ne doit jamais etre produit');

    $rawCount13W10 = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'length_with_position' AND list_key = '13:W:10'")->fetch()['count'];
    Assert::true($rawCount13W10 > 0, 'sanity check : 13:W:10 existe bien dans list_counts (precalcul brut inchange, seule la sortie du builder est filtree)');

    // --- 15-lettres/position/10/w perd face a /mots/commencant/sask (1 composant). ---
    $links15W = $builder->build(15, 'W');
    Assert::same(1, $links15W->queryCount);
    $position10At15 = array_values(array_filter($links15W->links, static fn (array $l): bool => $l['position'] === 10));
    Assert::true($position10At15 === [], '15:W:10 est un doublon de contenu croise (D-041) -- ne doit jamais etre produit');
};
