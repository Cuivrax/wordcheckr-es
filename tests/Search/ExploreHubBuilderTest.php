<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\ExploreHubBuilder;
use Tests\Support\Assert;

/**
 * App\Search\ExploreHubBuilder (hub /palabras) : lu depuis list_counts, précalculé par
 * scripts/build_explore_hub_counts.php (ES-017). Vérifié par force brute contre la vraie base
 * (lecture seule), même méthodologie que TermLookupTest.php.
 *
 * Portée ES-017 (voir le docblock de scripts/build_explore_hub_counts.php pour la décision
 * complète) : SEULEMENT 3 list_type sur les 19 du site français -- 'length', 'start', 'end'.
 * Deux décisions critiques vérifiées explicitement ici :
 *   1. granularité CARACTÈRE, pas TUILE -- 'start' compte 1 caractère (CHOZA est dans le
 *      bucket "C", jamais un bucket "CH" séparé) ;
 *   2. granularité ASYMÉTRIQUE -- 'start' = 1 caractère, 'end' = 2 caractères (conséquence de
 *      Normalizer::MIN_LENGTH = 2, voir ES-017).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new ExploreHubBuilder($connection);

    $hub = $builder->build();

    Assert::same(1, $hub->queryCount, 'une seule requete triviale sur list_counts, jamais de GROUP BY sur terms');
    Assert::same(14, count($hub->byLength), '14 longueurs (2 a 15, D-010-equivalent)');
    Assert::same(27, count($hub->byStart), '26 lettres + N -- K et W INCLUS (donnee brute, pas filtree par le statut SEO de word_list_commencant)');

    // --- byLength : verifie par force brute. ---
    $length9 = array_values(array_filter($hub->byLength, static fn (array $l): bool => $l['length'] === 9));
    Assert::true(count($length9) === 1);
    $expectedLength9 = (int) $pdo->query('SELECT COUNT(*) c FROM terms WHERE length = 9')->fetch()['c'];
    Assert::same($expectedLength9, $length9[0]['count']);
    Assert::same('/palabras/9-letras', $length9[0]['url']);

    // --- byStart : DECISION CRITIQUE 1 -- granularite CARACTERE, pas TUILE. ---
    // CHOZA (tuile de depart CH) doit etre compte dans le bucket "C", pas un bucket "CH"
    // separe -- aucune entree "CH"/"LL"/"RR" ne doit exister dans byStart.
    foreach (['CH', 'LL', 'RR'] as $tile) {
        $tileBucket = array_values(array_filter($hub->byStart, static fn (array $l): bool => $l['letter'] === $tile));
        Assert::true($tileBucket === [], "aucun bucket tuile '$tile' ne doit exister dans byStart (decision caractere, pas tuile)");
    }

    $startC = array_values(array_filter($hub->byStart, static fn (array $l): bool => $l['letter'] === 'C'));
    Assert::true(count($startC) === 1);
    $expectedStartC = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = 'C'")->fetch()['c'];
    Assert::same($expectedStartC, $startC[0]['count'], 'le bucket C doit inclure les mots commencant par la tuile CH (ex. CHOZA), comptes caractere par caractere');
    Assert::same('/palabras/empiezan-por/c', $startC[0]['url']);

    // K et W : 0 lien SEO reel (ES-016, RelationsFinder n'emet jamais 'startsWith' vers ces
    // pages car 0 mot ADMIS ne commence par l'une ou l'autre) mais des donnees BRUTES reelles
    // et non nulles (428/172 mots tous statuts) -- ce script ne doit PAS les exclure : ce
    // n'est pas son role (decision de rollout SEO separee, deja appliquee ailleurs).
    $startK = array_values(array_filter($hub->byStart, static fn (array $l): bool => $l['letter'] === 'K'));
    Assert::true(count($startK) === 1, 'K doit etre present dans list_counts (donnee brute), meme sans lien SEO reel aujourd\'hui');
    $expectedStartK = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = 'K'")->fetch()['c'];
    Assert::same($expectedStartK, $startK[0]['count']);
    Assert::true($expectedStartK > 0, 'sanity check : K a bien des mots (428 attendus)');

    // Ñ : lettre espagnole normale a part entiere (ES-003), jamais fusionnee avec N -- doit
    // apparaitre comme un bucket 'start' distinct.
    $startEnye = array_values(array_filter($hub->byStart, static fn (array $l): bool => $l['letter'] === 'Ñ'));
    Assert::true(count($startEnye) === 1, 'Ñ doit avoir son propre bucket start, distinct de N');
    Assert::same(805, $startEnye[0]['count'], 'compte verifie independamment (registre SEO word_list_commencant, ES-016)');
    Assert::same('/palabras/empiezan-por/ñ', $startEnye[0]['url']);

    // --- byEnd : DECISION CRITIQUE 2 -- 2 caracteres, pas 1 (contrairement a byStart). ---
    Assert::true(count($hub->byEnd) > 100, 'byEnd doit avoir beaucoup plus de buckets que byStart (2 caracteres, pas 1) -- ' . count($hub->byEnd) . ' trouves');

    // CH/LL/RR apparaissent ICI (byEnd), mais PAS comme un bucket "tuile" -- comme n'importe
    // quelle sequence de 2 CARACTERES litteraux, exactement comme 'AN' ou 'OS'. Verifie
    // directement par force brute (substr(reversed,1,2), sans jamais utiliser strrev() cote
    // test -- reversed('CH')='HC', pas d'ambiguite Ñ sur ces 3 buckets ASCII).
    foreach (['CH' => 34, 'LL' => 15, 'RR' => 2] as $suffix => $expectedCount) {
        $entry = array_values(array_filter($hub->byEnd, static fn (array $l): bool => $l['letter'] === $suffix));
        Assert::true(count($entry) === 1, "bucket '$suffix' attendu dans byEnd");
        $rawSuffix = strrev($suffix); // ASCII pur ici (C,H,L,R), strrev() est sur d'aucune ambiguite Ñ.
        $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE substr(reversed, 1, 2) = ?');
        $stmt->execute([$rawSuffix]);
        $expectedReal = (int) $stmt->fetch()['c'];
        Assert::same($expectedReal, $entry[0]['count']);
        Assert::same($expectedCount, $entry[0]['count'], "compte verifie independamment pour '$suffix'");
        Assert::same('/palabras/terminan-en/' . mb_strtolower($suffix, 'UTF-8'), $entry[0]['url']);
    }

    // Ñ cote byEnd : verifie que le mb-safe reverse reconstruit bien l'ordre de lecture
    // normal (bug reel evite, voir docblock du script de build -- strrev() sur des octets
    // aurait corrompu Ñ, 2 octets UTF-8).
    $endEnyeA = array_values(array_filter($hub->byEnd, static fn (array $l): bool => $l['letter'] === 'ÑA'));
    Assert::true(count($endEnyeA) === 1, 'bucket ÑA attendu dans byEnd (mots finissant par ...ÑA, ex. DOÑA)');
    Assert::same(779, $endEnyeA[0]['count']);
    Assert::same('/palabras/terminan-en/ña', $endEnyeA[0]['url']);

    // Reciproque : un mot finissant par ...EÑ (Ñ en avant-derniere position, pas en derniere)
    // doit produire le bucket "EÑ", jamais "ÑE" ni une chaine corrompue.
    $endEEnye = array_values(array_filter($hub->byEnd, static fn (array $l): bool => $l['letter'] === 'EÑ'));
    Assert::true(count($endEEnye) === 1, 'bucket EÑ attendu (Ñ en avant-derniere position)');
    Assert::same(1, $endEEnye[0]['count']);

    // Aucune chaine corrompue (octets UTF-8 invalides) ne doit jamais apparaitre comme cle.
    foreach ($hub->byEnd as $entry) {
        Assert::true(mb_check_encoding($entry['letter'], 'UTF-8'), "cle byEnd invalide en UTF-8 : " . bin2hex($entry['letter']));
    }
};
