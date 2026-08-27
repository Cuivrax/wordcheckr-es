<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\AvecSansLengthLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\AvecSansLengthLinksBuilder (D-024bis) : maillage "avec {X} sans {Y}" -> longueur
 * depuis la page generique sans longueur -- lu depuis list_counts (precalcule par
 * scripts/build_explore_hub_counts.php), jamais un calcul sur `terms` au runtime. Verifie par
 * force brute sur la vraie base (lecture seule), meme methodologie que PositionLinksBuilderTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new AvecSansLengthLinksBuilder($connection);

    $links = $builder->build('Q', 'U');

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($links->links !== [], 'sanity check : des mots avec Q sans U existent');

    // --- Verifie chaque longueur par force brute (instr()). ---
    foreach ($links->links as $link) {
        $expected = (int) $pdo->query(
            "SELECT COUNT(*) c FROM terms WHERE length = {$link['length']}"
            . " AND instr(normalized, 'Q') > 0 AND instr(normalized, 'U') = 0"
        )->fetch()['c'];
        Assert::same($expected, $link['count'], "longueur {$link['length']} : verifie par force brute");
        Assert::same('/mots/' . $link['length'] . '-lettres/avec/q/sans/u', $link['url']);
    }

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ($links->links as $link) {
        Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
    }

    // --- Tri par longueur croissante. ---
    for ($i = 1; $i < count($links->links); $i++) {
        Assert::true($links->links[$i - 1]['length'] < $links->links[$i]['length'], 'ordre par longueur attendu');
    }

    // --- Coherence : la somme des comptes par longueur doit egaler le total sans longueur. ---
    $expectedTotal = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE instr(normalized, 'Q') > 0 AND instr(normalized, 'U') = 0")->fetch()['c'];
    $sum = array_sum(array_column($links->links, 'count'));
    Assert::same($expectedTotal, $sum, 'la somme des comptes par longueur doit egaler le total avec Q sans U');
};
