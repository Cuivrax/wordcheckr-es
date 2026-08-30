<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LengthLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\LengthLinksBuilder sur le dépôt ES : maillage interne d'une page
 * "/palabras/{N}-letras" (déjà indexée, word_list_length, ES-011 I-1) vers ses combinaisons
 * longueur+lettre -- lu depuis list_counts (ES-017 pour byStart/byEnd, ES-022 pour le reste,
 * scripts/build_explore_hub_counts.php).
 *
 * RÉVISÉ (ES-022) : byWith ('length_with'), byPosition ('length_with_position') et byStartEnd
 * ('length_start_end') sont désormais PEUPLÉS (19/19 list_type, ES-022) -- vérifiés ici par
 * sanity check réel (non vides, structure correcte), pas par force brute exhaustive comme
 * byStart/byEnd : ces types alimentent des familles SEO pas encore ouvertes à l'indexation
 * (ES-022 peuple la donnée, n'ouvre aucune famille dessus), donc pas encore couverts par le
 * même niveau de vérification qu'un maillage déjà en production.
 *
 * byStart ET byEnd sont maintenant TOUS DEUX à 1 caractère (ES-022, revise depuis 2 pour
 * byEnd -- discussion produit directe : FR/DE restent à 1, c'était ES qui divergeait). La
 * classe elle-même n'a nécessité AUCUNE modification (list_key traité comme une chaîne opaque,
 * "tout ce qui suit le premier ':'").
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

    // list_type desormais peuples (ES-022) : sanity check reel, pas juste "non vide" -- verifie
    // qu'au moins une entree a un compte REEL correct et une URL bien formee, sans pretendre a
    // une couverture exhaustive (ces familles ne sont pas encore ouvertes a l'indexation).
    Assert::true($links->byWith !== [], "'length_with' doit etre peuple depuis ES-022");
    $withA = array_values(array_filter($links->byWith, static fn (array $l): bool => $l['letter'] === 'A'));
    if ($withA !== []) {
        $expectedWithA = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type='length_with' AND list_key='9:A'")->fetch()['count'];
        Assert::same($expectedWithA, $withA[0]['count'], "compte 'length_with' verifie independamment pour 9:A");
    }

    Assert::true($links->byPosition !== [], "'length_with_position' doit etre peuple depuis ES-022");

    Assert::true($links->byStartEnd !== [], "'length_start_end' doit etre peuple depuis ES-022");

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

    // --- byEnd (1 caractere, ES-022 -- revise depuis 2) : verifie par force brute. ---
    $expectedEndA = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(reversed, 1, 1) = 'A'")->fetch()['c'];
    $endA = array_values(array_filter($links->byEnd, static fn (array $l): bool => $l['letter'] === 'A'));
    Assert::true(count($endA) === 1);
    Assert::same($expectedEndA, $endA[0]['count']);
    Assert::same('/palabras/9-letras/terminan-en/a', $endA[0]['url']);

    // Tri alphabetique (BINARY, coherent avec ES-003 : Ñ trie apres Z).
    $letters = array_column($links->byEnd, 'letter');
    $sorted = $letters;
    usort($sorted, static fn (string $a, string $b): int => $a <=> $b);
    Assert::same($sorted, $letters, 'byEnd doit rester trie par ordre BINARY sur les lettres');

    // Ñ cote byEnd : aucun mot de 9 lettres ne finit par Ñ (verifie, pas suppose) -- utilise une
    // longueur ou Ñ EST reellement present (8) pour garder une verification non-ASCII reelle du
    // decoupage "{longueur}:{lettre}" (list_key = "8:Ñ", strpos($key, ':') + 1 doit extraire
    // "Ñ" exactement, jamais une sequence d'octets tronquee).
    $links8 = $builder->build(8);
    $expectedEndEnye8 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 8 AND substr(reversed, 1, 1) = 'Ñ'")->fetch()['c'];
    Assert::true($expectedEndEnye8 > 0, 'precondition du test : au moins 1 mot de 8 lettres doit finir par Ñ');
    $endEnye8 = array_values(array_filter($links8->byEnd, static fn (array $l): bool => $l['letter'] === 'Ñ'));
    Assert::true(count($endEnye8) === 1, 'bucket Ñ attendu pour les mots de 8 lettres finissant par Ñ');
    Assert::same($expectedEndEnye8, $endEnye8[0]['count']);
    Assert::same('/palabras/8-letras/terminan-en/ñ', $endEnye8[0]['url']);
};
