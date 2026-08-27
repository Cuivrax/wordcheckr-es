<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LetterCombinedLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\LetterCombinedLinksBuilder (D-024) : maillage commencant+terminant depuis une page
 * mono-lettre -- lu depuis list_counts (precalcule par scripts/build_explore_hub_counts.php),
 * jamais un GROUP BY sur `terms` au runtime. Verifie par force brute sur la vraie base (lecture
 * seule), meme methodologie que LengthLinksBuilderTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new LetterCombinedLinksBuilder($connection);

    // --- buildForStart : verifie par force brute contre COUNT(*) reel. ---
    $fromA = $builder->buildForStart('A');
    Assert::same(1, $fromA->queryCount);
    Assert::true($fromA->links !== [], 'sanity check : des mots commencant par A et terminant par une lettre existent');

    $expectedAtoE = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'A%' AND normalized LIKE '%E'")->fetch()['c'];
    $atoE = array_values(array_filter($fromA->links, static fn (array $l): bool => $l['letter'] === 'E'));
    Assert::true(count($atoE) === 1, 'une seule entree E attendue');
    Assert::same($expectedAtoE, $atoE[0]['count']);
    Assert::same('/mots/commencant/a/terminant/e', $atoE[0]['url']);

    // --- buildForEnd : meme verification, direction inverse. ---
    $toE = $builder->buildForEnd('E');
    Assert::same(1, $toE->queryCount);
    $bToE = array_values(array_filter($toE->links, static fn (array $l): bool => $l['letter'] === 'A'));
    Assert::true(count($bToE) === 1);
    Assert::same($expectedAtoE, $bToE[0]['count'], 'meme compte, quel que soit le sens de lecture');
    Assert::same('/mots/commencant/a/terminant/e', $bToE[0]['url'], 'meme URL canonique, quel que soit le sens de lecture');

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ([$fromA->links, $toE->links] as $group) {
        foreach ($group as $link) {
            Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
        }
    }

    // --- Tri alphabetique par lettre. ---
    foreach ([$fromA->links, $toE->links] as $group) {
        for ($i = 1; $i < count($group); $i++) {
            Assert::true($group[$i - 1]['letter'] < $group[$i]['letter'], 'ordre alphabetique attendu');
        }
    }

    // --- Coherence globale : la somme des liens depuis A doit egaler le total de la page
    // --- /mots/commencant/a (chaque mot commencant par A finit par exactement une lettre). ---
    $expectedTotalA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE 'A%'")->fetch()['c'];
    $sumFromA = array_sum(array_column($fromA->links, 'count'));
    Assert::same($expectedTotalA, $sumFromA, 'la somme des comptes par terminaison doit egaler le total commencant par A');
};
