<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\SuffixExtensionLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\SuffixExtensionLinksBuilder (tâche de dimensionnement "commencant/terminant
 * multi-lettres", 2026-08-18) : maillage en entonnoir "terminant/{suffixe}" -> "terminant/
 * {suffixe étendu d'une lettre EN TÊTE}" -- lu depuis list_counts (list_type 'suffix2'/'suffix3'/
 * 'suffix4', précalculé par scripts/build_explore_hub_counts.php), jamais un GROUP BY sur
 * `terms` au runtime. Vérifié par force brute sur la vraie base (lecture seule).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new SuffixExtensionLinksBuilder($connection);

    // --- Niveau 1 -> 2 : depuis "G" (mono-lettre, déjà indexée D-017) -- l'extension ajoute une
    // --- lettre EN TÊTE du suffixe (contrairement au préfixe), vérifié par force brute. ---
    $fromG = $builder->build('G');
    Assert::same(1, $fromG->queryCount);
    Assert::true($fromG->links !== [], 'sanity check : des mots terminant par G existent');

    foreach ($fromG->links as $link) {
        Assert::same(2, strlen($link['suffix']), 'extension d\'une seule lettre attendue');
        Assert::true(str_ends_with($link['suffix'], 'G'), 'toute extension doit se terminer par G');

        $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE normalized LIKE ?');
        $stmt->execute(['%' . $link['suffix']]);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], "terminant/{$link['suffix']} : verifie par force brute");
        Assert::same('/mots/terminant/' . strtolower($link['suffix']), $link['url']);
    }

    // ============================================================================================
    // Doublons de contenu CROISES avec une famille EXTERIEURE (D-041, garde-fou structurel demande
    // par le constat C-4 du 4e audit consolide, docs/DECISIONS.md D-040) -- balayage GENERIQUE de
    // tout le registre (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 :
    // 1 656 groupes, 2 089 pages en exces). Applique ICI : une page "terminant/{suffixe}" a
    // TOUJOURS 1 seul composant, mais peut neanmoins perdre face a "commencant/{prefixe}" (l'autre
    // famille a 1 seul composant, departagee par l'ordre canonique -- "commencant" precede
    // "terminant"). Les 639 clés se répartissent en pertes TOUTES face a commençant multi-lettres
    // (voir App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES pour le detail).
    // ============================================================================================
    $suffixReflection = new ReflectionClass(SuffixExtensionLinksBuilder::class);
    $externalDuplicateSuffixes = $suffixReflection->getConstant('EXTERNAL_DUPLICATE_SUFFIXES');
    Assert::same(639, count($externalDuplicateSuffixes), 'exactement 639 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');
    Assert::same(count($externalDuplicateSuffixes), count(array_unique($externalDuplicateSuffixes)), 'aucun doublon dans la liste figee elle-meme');
    $externalDuplicateSuffixSet = array_fill_keys($externalDuplicateSuffixes, true);

    foreach (['LG', 'SG', 'VG'] as $excludedSuffix) {
        $found = array_values(array_filter($fromG->links, static fn (array $l): bool => $l['suffix'] === $excludedSuffix));
        Assert::true($found === [], "{$excludedSuffix} est un doublon de contenu croise (D-041) -- ne doit jamais etre produit depuis terminant/g");
        $rawCount = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'suffix2' AND list_key = '{$excludedSuffix}'")->fetch()['count'];
        Assert::true($rawCount > 0, "sanity check : {$excludedSuffix} existe bien dans list_counts (precalcul brut inchange, seule la sortie du builder est filtree)");
    }

    // La somme des extensions doit egaler le total du parent DONT la longueur est STRICTEMENT
    // superieure a la longueur du suffixe source -- un mot EXACTEMENT egal au suffixe source
    // (ex. "AN", D-010 : MIN_LENGTH = 2, ne s'applique pas a un suffixe d'1 lettre puisqu'aucun
    // mot d'1 lettre n'existe, mais le meme raisonnement s'applique en general, voir
    // PrefixExtensionLinksBuilderTest.php pour le cas ou il se manifeste reellement) est
    // "consomme" par la page du suffixe lui-meme, jamais etendu davantage -- MOINS les extensions
    // exclues par EXTERNAL_DUPLICATE_SUFFIXES (D-041) : LG, SG, VG pour "G" (3 exclusions).
    $rawTotalG = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%G' AND length > 1")->fetch()['c'];
    $excludedCountG = 0;
    foreach (['LG', 'SG', 'VG'] as $excludedSuffix) {
        $excludedCountG += (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%{$excludedSuffix}'")->fetch()['c'];
    }
    $expectedTotalG = $rawTotalG - $excludedCountG;
    Assert::same($expectedTotalG, array_sum(array_column($fromG->links, 'count')), 'somme des extensions = total du parent (mots plus longs que le suffixe) MOINS les 3 extensions exclues (D-041)');

    // --- Niveau 2 -> 3, puis 3 -> 4 : "NG" -> "xNG" -> "xxNG". ---
    $fromNG = $builder->build('NG');
    Assert::true($fromNG->links !== [], 'sanity check : des mots terminant par NG existent');
    foreach ($fromNG->links as $link) {
        Assert::same(3, strlen($link['suffix']));
        Assert::true(str_ends_with($link['suffix'], 'NG'));
    }
    $expectedTotalNG = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%NG' AND length > 2")->fetch()['c'];
    Assert::same($expectedTotalNG, array_sum(array_column($fromNG->links, 'count')));

    $fromING = $builder->build('ING');
    Assert::true($fromING->links !== [], 'sanity check : des mots terminant par ING existent');
    foreach ($fromING->links as $link) {
        Assert::same(4, strlen($link['suffix']));
        Assert::true(str_ends_with($link['suffix'], 'ING'));
    }
    $expectedTotalING = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%ING' AND length > 3")->fetch()['c'];
    Assert::same($expectedTotalING, array_sum(array_column($fromING->links, 'count')));

    // --- Bornes explicites, mêmes règles que PrefixExtensionLinksBuilder. ---
    $fromZINC = $builder->build('ZINC');
    Assert::same([], $fromZINC->links);
    Assert::same(0, $fromZINC->queryCount);

    $fromEmpty = $builder->build('');
    Assert::same([], $fromEmpty->links);
    Assert::same(0, $fromEmpty->queryCount);

    // --- R5, tri alphabétique. ---
    foreach ([$fromG->links, $fromNG->links, $fromING->links] as $group) {
        foreach ($group as $link) {
            Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
        }
        for ($i = 1; $i < count($group); $i++) {
            Assert::true($group[$i - 1]['suffix'] < $group[$i]['suffix'], 'ordre alphabetique attendu');
        }
    }

    // --- Cas dégénéré proche de Z (rangeBounds() sans borne supérieure sur reversed --
    // --- Normalizer::reverse('ZZ') = 'ZZ', voir D-025bis) : "ZZ" est un vrai suffixe (JAZZ, BUZZ,
    // --- FIZZ...) -- vérifie que l'extension à 3 lettres reste correcte malgré ce cas limite. ---
    $fromZZ = $builder->build('ZZ');
    Assert::true($fromZZ->links !== [], 'sanity check : des mots terminant par ZZ existent (ex. JAZZ, BUZZ)');
    foreach ($fromZZ->links as $link) {
        Assert::true(str_ends_with($link['suffix'], 'ZZ'));
    }

    // --- IZZ est exclu (D-041, doublon croise avec une famille exterieure) -- ne doit jamais
    // --- apparaitre parmi les extensions de ZZ. ---
    $foundIZZ = array_values(array_filter($fromZZ->links, static fn (array $l): bool => $l['suffix'] === 'IZZ'));
    Assert::true($foundIZZ === [], 'IZZ est un doublon de contenu croise (D-041) -- ne doit jamais etre produit depuis terminant/zz');

    $rawTotalZZ = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%ZZ' AND length > 2")->fetch()['c'];
    $excludedCountIZZ = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE normalized LIKE '%IZZ'")->fetch()['c'];
    $expectedTotalZZ = $rawTotalZZ - $excludedCountIZZ;
    Assert::same($expectedTotalZZ, array_sum(array_column($fromZZ->links, 'count')), 'somme des extensions = total du parent MOINS l\'extension exclue IZZ (D-041)');
    Assert::same(5, $rawTotalZZ, 'sanity check : exactement 5 mots se terminent par ZZ dans la base actuelle, aucun n\'est exactement egal a ZZ');
    Assert::same(1, $excludedCountIZZ, 'sanity check : 1 mot se termine par IZZ (exclu, D-041)');
    Assert::same(4, $expectedTotalZZ, 'sanity check : 4 mots restants apres exclusion de IZZ');
};
