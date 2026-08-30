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

    // --- byEnd : REVISE (ES-022) -- 1 caractere, comme byStart, pas 2. ES-017 avait choisi 2
    // caracteres pour matcher directement la famille alors indexee (terminan-en 2 lettres,
    // ES-016) ; discussion produit directe (2026-08-30) a etabli que FR/DE restent a 1
    // caractere -- c'est ES qui divergeait, pas l'inverse -- et que le hub est une source de
    // lien reelle DISTINCTE de RelationsFinder, qui justifie desormais un palier 1 lettre pour
    // terminan-en aussi (ES-022). La famille 2 lettres deja indexee (ES-016) reste inchangee,
    // construite independamment de list_counts.
    Assert::same(27, count($hub->byEnd), '26 lettres + Ñ, symetrique a byStart (1 caractere desormais, ES-022) -- ' . count($hub->byEnd) . ' trouves');

    // Aucun bucket tuile CH/LL/RR ici non plus (meme decision caractere que byStart) --
    // a 1 caractere ces sequences n'existent de toute facon plus comme cle possible.
    foreach (['CH', 'LL', 'RR'] as $tile) {
        $tileBucket = array_values(array_filter($hub->byEnd, static fn (array $l): bool => $l['letter'] === $tile));
        Assert::true($tileBucket === [], "aucun bucket tuile '$tile' ne doit exister dans byEnd (decision caractere, pas tuile)");
    }

    // Ñ cote byEnd : verifie a 1 caractere desormais (mots finissant PAR Ñ, ex. MACUÑ), compte
    // verifie independamment par requete directe (substr(reversed,1,1), pas de risque de
    // corruption a 1 seul caractere, mais on verifie quand meme que la cle rendue est bien "Ñ"
    // et pas une sequence d'octets invalide).
    $endEnye = array_values(array_filter($hub->byEnd, static fn (array $l): bool => $l['letter'] === 'Ñ'));
    Assert::true(count($endEnye) === 1, 'bucket Ñ attendu dans byEnd (mots finissant par Ñ, ex. MACUÑ)');
    $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE substr(reversed, 1, 1) = ?');
    $stmt->execute(['Ñ']);
    $expectedEnyeReal = (int) $stmt->fetch()['c'];
    Assert::same($expectedEnyeReal, $endEnye[0]['count'], 'compte verifie independamment par requete directe');
    Assert::same('/palabras/terminan-en/ñ', $endEnye[0]['url']);

    // Aucune chaine corrompue (octets UTF-8 invalides) ne doit jamais apparaitre comme cle.
    foreach ($hub->byEnd as $entry) {
        Assert::true(mb_check_encoding($entry['letter'], 'UTF-8'), "cle byEnd invalide en UTF-8 : " . bin2hex($entry['letter']));
    }
};
