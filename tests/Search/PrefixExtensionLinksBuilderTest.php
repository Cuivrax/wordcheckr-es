<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\PrefixExtensionLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\PrefixExtensionLinksBuilder (tâche de dimensionnement "commencant/terminant
 * multi-lettres", 2026-08-18) : maillage en entonnoir "commencant/{préfixe}" -> "commencant/
 * {préfixe étendu d'une lettre}" -- lu depuis list_counts (list_type 'prefix2'/'prefix3'/
 * 'prefix4', précalculé par scripts/build_explore_hub_counts.php), jamais un GROUP BY sur
 * `terms` au runtime. Vérifié par force brute sur la vraie base (lecture seule), même
 * méthodologie que LetterCombinedLinksBuilderTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new PrefixExtensionLinksBuilder($connection);

    // --- Niveau 1 -> 2 : depuis "A" (mono-lettre, déjà indexée D-017), toutes les extensions à
    // --- 2 lettres réelles doivent apparaître -- vérifié par force brute contre COUNT(*) réel. ---
    $fromA = $builder->build('A');
    Assert::same(1, $fromA->queryCount);
    Assert::true($fromA->links !== [], 'sanity check : des mots commencant par A existent');

    foreach ($fromA->links as $link) {
        Assert::same(2, strlen($link['prefix']), 'extension d\'une seule lettre attendue');
        Assert::true(str_starts_with($link['prefix'], 'A'), 'toute extension doit commencer par A');

        $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE normalized LIKE ?');
        $stmt->execute([$link['prefix'] . '%']);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], "commencant/{$link['prefix']} : verifie par force brute");
        Assert::same('/mots/commencant/' . strtolower($link['prefix']), $link['url']);
    }

    // La somme des comptes des extensions à 2 lettres doit égaler le total de "commencant/a" DONT
    // la longueur est STRICTEMENT superieure a 1 -- un mot EXACTEMENT egal au prefixe source (ici
    // impossible, MIN_LENGTH = 2, D-010, donc aucun mot d'une seule lettre) est "consomme" par la
    // page du prefixe lui-meme, jamais etendu davantage.
    $expectedTotalA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'A%' AND length > 1")->fetch()['c'];
    Assert::same($expectedTotalA, array_sum(array_column($fromA->links, 'count')), 'somme des extensions = total du parent (mots plus longs que le prefixe)');

    // --- Niveau 2 -> 3, puis 3 -> 4 : même vérification, deux paliers plus loin. ---
    $fromAN = $builder->build('AN');
    Assert::true($fromAN->links !== [], 'sanity check : des mots commencant par AN existent');
    foreach ($fromAN->links as $link) {
        Assert::same(3, strlen($link['prefix']));
        Assert::true(str_starts_with($link['prefix'], 'AN'));
    }
    // AN (longueur 2) est lui-meme un mot francais reel ("AN", annee) : compte dans le total
    // "AN%" mais n'a aucune extension a 3 lettres -- length > 2 exclut ce cas exact, meme
    // raisonnement que ci-dessus. Verifie explicitement le cas qui a revele ce raisonnement
    // (pas suppose) : sans le filtre length > 2, la somme des extensions serait EXACTEMENT 1 de
    // moins que le total "AN%" brut.
    $rawTotalAN = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'AN%'")->fetch()['c'];
    $expectedTotalAN = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'AN%' AND length > 2")->fetch()['c'];
    Assert::same(1, $rawTotalAN - $expectedTotalAN, 'sanity check : AN lui-meme est le seul mot exactement egal au prefixe source dans ce cas');
    Assert::same($expectedTotalAN, array_sum(array_column($fromAN->links, 'count')));

    $fromANT = $builder->build('ANT');
    Assert::true($fromANT->links !== [], 'sanity check : des mots commencant par ANT existent');
    foreach ($fromANT->links as $link) {
        Assert::same(4, strlen($link['prefix']));
        Assert::true(str_starts_with($link['prefix'], 'ANT'));
    }
    $expectedTotalANT = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'ANT%' AND length > 3")->fetch()['c'];
    Assert::same($expectedTotalANT, array_sum(array_column($fromANT->links, 'count')));

    // --- Bornes explicites : un préfixe de 4 lettres est déjà la dernière extension ouverte par
    // --- cette tâche (2 à 4 lettres) -- aucune extension à 5 lettres, liste vide attendue, jamais
    // --- une requête inutile (queryCount = 0). Même chose pour une entrée vide ou trop longue. ---
    $fromANTI = $builder->build('ANTI');
    Assert::same([], $fromANTI->links);
    Assert::same(0, $fromANTI->queryCount, 'aucune requete pour un prefixe deja a la longueur maximale ouverte');

    $fromEmpty = $builder->build('');
    Assert::same([], $fromEmpty->links);
    Assert::same(0, $fromEmpty->queryCount);

    $fromTooLong = $builder->build('ANTIC');
    Assert::same([], $fromTooLong->links);
    Assert::same(0, $fromTooLong->queryCount);

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ([$fromA->links, $fromAN->links, $fromANT->links] as $group) {
        foreach ($group as $link) {
            Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
        }
    }

    // --- Tri alphabetique. ---
    foreach ([$fromA->links, $fromAN->links, $fromANT->links] as $group) {
        for ($i = 1; $i < count($group); $i++) {
            Assert::true($group[$i - 1]['prefix'] < $group[$i]['prefix'], 'ordre alphabetique attendu');
        }
    }

    // --- Cas dégénéré proche de Z (rangeBounds() sans borne supérieure, voir D-025bis) : depuis
    // --- "ZY" (2 lettres, existe reellement), les extensions à 3 lettres restent correctes malgre
    // --- la proximite de la fin de l'alphabet. ---
    $fromZY = $builder->build('ZY');
    Assert::true($fromZY->links !== [], 'sanity check : des mots commencant par ZY existent (ex. ZYGOTE)');
    foreach ($fromZY->links as $link) {
        Assert::true(str_starts_with($link['prefix'], 'ZY'));
    }
    $expectedTotalZY = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'ZY%' AND length > 2")->fetch()['c'];
    Assert::same($expectedTotalZY, array_sum(array_column($fromZY->links, 'count')));
};
