<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LengthLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\LengthLinksBuilder sur le dépôt ES : maillage interne d'une page
 * "/palabras/{N}-letras" (déjà indexée, word_list_length, ES-011 I-1) vers ses combinaisons
 * longueur+lettre -- lu depuis list_counts (ES-017, scripts/build_explore_hub_counts.php).
 *
 * Portée ES-017 : SEULEMENT byStart ('length_start') et byEnd ('length_end') sont peuplés
 * dans cette passe. byWith ('length_with'), byPosition ('length_with_position') et
 * byStartEnd ('length_start_end') ne le sont PAS -- doivent donc rester des tableaux VIDES ici
 * (list_type absents de list_counts, pas une erreur), contrairement au test équivalent côté
 * français (LengthLinksBuilderTest.php, 5 list_type peuplés).
 *
 * Vérifie aussi la conséquence directe de la décision de granularité asymétrique (ES-017) :
 * byStart a une entrée par lettre (1 caractère), byEnd une entrée par suffixe de 2 caractères
 * -- la classe elle-même n'a nécessité AUCUNE modification (list_key traité comme une chaîne
 * opaque, "tout ce qui suit le premier ':'").
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new LengthLinksBuilder($connection);

    $links = $builder->build(9);

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts, jamais de GROUP BY sur terms');
    Assert::true($links->byStart !== [], 'sanity check : des mots de 9 lettres existent pour au moins une lettre de debut');
    Assert::true($links->byEnd !== []);

    // list_type non peuples dans cette passe (ES-017) : doivent rester vides, pas planter.
    Assert::same([], $links->byWith, "'length_with' non construit dans cette passe (ES-017)");
    Assert::same([], $links->byPosition, "'length_with_position' non construit dans cette passe (ES-017)");
    Assert::same([], $links->byStartEnd, "'length_start_end' non construit dans cette passe (ES-017)");

    // --- byStart (1 caractere) : verifie par force brute. ---
    $expectedStartC = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 1, 1) = 'C'")->fetch()['c'];
    $startC = array_values(array_filter($links->byStart, static fn (array $l): bool => $l['letter'] === 'C'));
    Assert::true(count($startC) === 1);
    Assert::same($expectedStartC, $startC[0]['count']);
    Assert::same('/palabras/9-letras/empiezan-por/c', $startC[0]['url']);

    // K : present ici (donnee brute), meme si /palabras/9-letras/empiezan-por/k n'est pas
    // encore indexee -- ES-017 ne prejuge d'aucune decision d'ouverture SEO.
    $stmtK = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 1, 1) = 'K'");
    $stmtK->execute();
    $expectedStartK = (int) $stmtK->fetch()['c'];
    $startK = array_values(array_filter($links->byStart, static fn (array $l): bool => $l['letter'] === 'K'));
    if ($expectedStartK > 0) {
        Assert::true(count($startK) === 1, 'K attendu dans byStart, donnee brute (' . $expectedStartK . ' mots)');
        Assert::same($expectedStartK, $startK[0]['count']);
    }

    // --- byEnd (2 caracteres) : verifie par force brute, mb-safe reverse cote test. ---
    $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(reversed, 1, 2) = ?');
    $stmt->execute([strrev('AN')]); // 'AN' est ASCII pur, strrev() sur d'ici.
    $expectedEndAN = (int) $stmt->fetch()['c'];
    $endAN = array_values(array_filter($links->byEnd, static fn (array $l): bool => $l['letter'] === 'AN'));
    Assert::true(count($endAN) === 1);
    Assert::same($expectedEndAN, $endAN[0]['count']);
    Assert::same('/palabras/9-letras/terminan-en/an', $endAN[0]['url']);

    // Ñ cote byEnd, longueur precise : verifie que le decoupage "{longueur}:{suffixe 2 car.}"
    // (list_key = "9:ÑA") est correctement extrait par substr($key, strpos($key, ':') + 1) --
    // en particulier que strpos() trouve bien le PREMIER ':' seulement (le suffixe lui-meme ne
    // contient jamais ':', mais Ñ est un multi-octet, pas un multi-caractere -- verification de
    // non-regression explicite).
    $stmtEnye = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(reversed, 1, 2) = ?');
    $stmtEnye->execute(["\x41\xC3\x91"]); // substr(reversed,1,2) brut de "...ÑA" = 'A' + Ñ (2 octets)
    $expectedEndEnyeA = (int) $stmtEnye->fetch()['c'];

    if ($expectedEndEnyeA > 0) {
        $endEnyeA = array_values(array_filter($links->byEnd, static fn (array $l): bool => $l['letter'] === 'ÑA'));
        Assert::true(count($endEnyeA) === 1, 'bucket ÑA attendu pour au moins un mot de 9 lettres finissant par ...ÑA');
        Assert::same($expectedEndEnyeA, $endEnyeA[0]['count']);
        Assert::same('/palabras/9-letras/terminan-en/ña', $endEnyeA[0]['url']);
    }

    // Tri alphabetique (BINARY, coherent avec ES-003 : Ñ trie apres Z).
    $letters = array_column($links->byEnd, 'letter');
    $sorted = $letters;
    usort($sorted, static fn (string $a, string $b): int => $a <=> $b);
    Assert::same($sorted, $letters, 'byEnd doit rester trie par ordre BINARY sur le suffixe complet');
};
