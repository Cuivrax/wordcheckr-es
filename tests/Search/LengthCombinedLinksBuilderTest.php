<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LengthCombinedLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\LengthCombinedLinksBuilder (proposition en cours, list_type 'length_start_end' --
 * voir reports/query-plans/length-combined-links.md) : maillage commencant+terminant AVEC
 * longueur depuis une page longueur+prefixe seul (ou longueur+suffixe seul) -- lu depuis
 * list_counts (precalcule par scripts/build_explore_hub_counts.php), jamais un GROUP BY sur
 * `terms` au runtime. Verifie par force brute sur la vraie base (lecture seule), meme
 * methodologie que LetterCombinedLinksBuilderTest.php et PositionLinksBuilderTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new LengthCombinedLinksBuilder($connection);

    // --- buildForStart : verifie par force brute contre COUNT(*) reel (9-lettres/commencant/R). ---
    $fromR9 = $builder->buildForStart(9, 'R');
    Assert::same(1, $fromR9->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($fromR9->links !== [], 'sanity check : des mots de 9 lettres commencant par R et terminant par une lettre existent');

    $expectedRtoE = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized LIKE 'R%' AND normalized LIKE '%E'")->fetch()['c'];
    $rToE = array_values(array_filter($fromR9->links, static fn (array $l): bool => $l['letter'] === 'E'));
    Assert::true(count($rToE) === 1, 'une seule entree E attendue');
    Assert::same($expectedRtoE, $rToE[0]['count'], 'verifie par force brute');
    Assert::same('/mots/9-lettres/commencant/r/terminant/e', $rToE[0]['url']);

    // --- buildForEnd : meme verification, direction inverse, meme compte et meme URL. ---
    $toE9 = $builder->buildForEnd(9, 'E');
    Assert::same(1, $toE9->queryCount);
    $bToE = array_values(array_filter($toE9->links, static fn (array $l): bool => $l['letter'] === 'R'));
    Assert::true(count($bToE) === 1);
    Assert::same($expectedRtoE, $bToE[0]['count'], 'meme compte, quel que soit le sens de lecture');
    Assert::same('/mots/9-lettres/commencant/r/terminant/e', $bToE[0]['url'], 'meme URL canonique, quel que soit le sens de lecture');

    // --- Cas courts (longueur 2) et longs (longueur 15), bornes du plafond D-010. ---
    $fromA2 = $builder->buildForStart(2, 'A');
    Assert::same(1, $fromA2->queryCount);
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 2 AND normalized LIKE 'A%' AND normalized LIKE ?");
    foreach ($fromA2->links as $link) {
        $stmt->execute(['%' . $link['letter']]);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], 'longueur 2 : verifie par force brute, lettre ' . $link['letter']);
    }

    $fromA15 = $builder->buildForStart(15, 'A');
    Assert::same(1, $fromA15->queryCount);
    $stmt15 = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 15 AND normalized LIKE 'A%' AND normalized LIKE ?");
    foreach ($fromA15->links as $link) {
        $stmt15->execute(['%' . $link['letter']]);
        Assert::same((int) $stmt15->fetch()['c'], $link['count'], 'longueur 15 : verifie par force brute, lettre ' . $link['letter']);
        Assert::same('/mots/15-lettres/commencant/a/terminant/' . strtolower($link['letter']), $link['url']);
    }

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ([$fromR9->links, $toE9->links, $fromA2->links, $fromA15->links] as $group) {
        foreach ($group as $link) {
            Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
        }
    }

    // --- Tri alphabetique par lettre. ---
    foreach ([$fromR9->links, $toE9->links, $fromA2->links, $fromA15->links] as $group) {
        for ($i = 1; $i < count($group); $i++) {
            Assert::true($group[$i - 1]['letter'] < $group[$i]['letter'], 'ordre alphabetique attendu');
        }
    }

    // --- Coherence globale : la somme des liens depuis 9-lettres/commencant/R doit egaler le
    // --- total reel de la page /mots/9-lettres/commencant/r (chaque mot de cette page se
    // --- termine par exactement une lettre). ---
    $expectedTotalR9 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized LIKE 'R%'")->fetch()['c'];
    $sumFromR9 = array_sum(array_column($fromR9->links, 'count'));
    Assert::same($expectedTotalR9, $sumFromR9, 'la somme des comptes par terminaison doit egaler le total 9-lettres/commencant/r');

    // --- Meme coherence pour buildForEnd. ---
    $expectedTotalE9 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized LIKE '%E'")->fetch()['c'];
    $sumToE9 = array_sum(array_column($toE9->links, 'count'));
    Assert::same($expectedTotalE9, $sumToE9, 'la somme des comptes par debut doit egaler le total 9-lettres/terminant/e');

    // --- Cas rare (Q, peu de mots de 9 lettres commencant par Q en francais) : queryCount et
    // --- URL toujours corrects meme avec peu ou pas de resultats. ---
    $fromQ9 = $builder->buildForStart(9, 'Q');
    Assert::same(1, $fromQ9->queryCount);
    $stmtQ = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 9 AND normalized LIKE 'Q%' AND normalized LIKE ?");
    foreach ($fromQ9->links as $link) {
        $stmtQ->execute(['%' . $link['letter']]);
        Assert::same((int) $stmtQ->fetch()['c'], $link['count'], 'longueur 9, prefixe Q : verifie par force brute, lettre ' . $link['letter']);
    }
};
