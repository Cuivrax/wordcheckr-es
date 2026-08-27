<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/propose_seo_batch.php, cas 'combined_with_length' (axe 1 -- D-027 App\Search\
 * LengthLinksBuilder::build()->byStartEnd, variante AVEC longueur de App\Seo\Family::
 * WORD_LIST_COMBINED, arbitrage de volume de liens tranche par l'agent seo-registry le
 * 2026-08-18) -- boite noire, un vrai sous-processus PHP, jamais contre le vrai
 * storage/seo_fr.sqlite (ce script ne l'ouvre de toute facon jamais, uniquement
 * storage/dictionary_fr.sqlite en lecture seule -- voir l'entete du script).
 *
 * Meme discipline que tests/Seo/ProposeSeoBatchAvecThreeLettersTest.php : ce cas genere une
 * PROPOSITION nouvelle a chaque execution (batch_id/added_at dynamiques), ce test ne verifie
 * donc PAS un batch_id exact, seulement sa forme et la structure des lignes.
 *
 * CORRECTIF (D-041, 2026-08-21, isD041Excluded()) : 5 141 -> 4 849 lignes (292 exclusions
 * supplementaires) -- detecteur generique scripts/check_combinatorial_duplicates.php, regle de
 * priorite resolveDuplicateWinner() (scripts/lib/seo_duplicate_priority.php). Meme constante
 * D041_EXCLUDED_ROUTE_PATHS['word_list_combined'] que le cas 'combined' (sans longueur) --
 * partagee car meme famille App\Seo\Family::WORD_LIST_COMBINED.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $dictPath = $root . '/storage/dictionary_fr.sqlite';
    Assert::true(is_file($dictPath), 'base manquante : ' . $dictPath);

    $process = proc_open(
        [PHP_BINARY, $root . '/scripts/propose_seo_batch.php', 'combined_with_length'],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        $root,
    );
    Assert::true($process !== false, 'impossible de lancer propose_seo_batch.php');

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    Assert::same(0, $exitCode, "propose_seo_batch.php combined_with_length aurait du reussir : {$stderr}");
    Assert::true(str_contains($stderr, '4849 ligne(s) proposee(s)'), $stderr);

    $tmpFile = tempnam(sys_get_temp_dir(), 'combined_with_length_batch_');
    file_put_contents($tmpFile, $stdout);

    try {
        $batch = require $tmpFile;

        Assert::true(str_starts_with($batch['batch_id'], 'combined_with_length-proposed-'), 'batch_id dynamique attendu (nouvelle proposition)');
        Assert::same(4_849, count($batch['rows']), '5 193 combinaisons length_start_end reelles moins 52 (D-025) moins 292 (D-041, doublons croises avec d\'autres familles combinatoires) = 4 849');

        // --- Forme exacte de la route : longueur + commencant + terminant, chacun d'une seule
        // --- lettre (jamais /mots/commencant/{X}/terminant/{Y} seul, jamais un prefixe/suffixe
        // --- multi-lettres). ---
        $seen = [];
        $singleResultCount = 0;

        foreach ($batch['rows'] as $row) {
            Assert::true(preg_match('#^/mots/(\d+)-lettres/commencant/([a-z])/terminant/([a-z])\z#', $row['route_path'], $m) === 1, $row['route_path']);
            $length = (int) $m[1];
            Assert::true($length >= 2 && $length <= 15, "longueur hors bornes : {$row['route_path']}");

            // --- R1-R7 respectees par construction. ---
            Assert::same('word_list_combined', $row['family'], 'AUCUNE nouvelle classification -- meme famille que la variante sans longueur (D-025)');
            Assert::same('index,follow', $row['robots']);
            Assert::same($row['route_path'], $row['canonical_path'], 'R3 : jamais d\'alias indexable');
            Assert::same('combined-0002', $row['sitemap_fragment'], 'nouveau fragment, combined-0001 deja occupe par la variante sans longueur (D-025)');
            Assert::true($row['result_count'] >= 1, 'R5 : jamais 0 resultat pour une ligne index,follow');
            Assert::true($row['result_count'] <= 10_000, 'plafond ROW_EXAMINATION_CEILING (D-019, regime BORNE des que le suffixe est present)');
            Assert::true(trim($row['notes']) !== '', 'R7 : note de maillage obligatoire');
            Assert::true(!isset($seen[$row['route_path']]), 'R2 : aucun doublon dans le lot');
            $seen[$row['route_path']] = true;

            if ($row['result_count'] === 1) {
                $singleResultCount++;
            }
        }

        Assert::same(725, $singleResultCount, 'pages a exactement 1 resultat parmi les 4 849, GARDEES (docs/05, jamais sur le seul compteur)');

        // --- Les 52 paires a contenu strictement duplique avec la variante sans longueur
        // --- (D-025, I-1) ne doivent JAMAIS apparaitre -- verifie sur un echantillon connu, pas
        // --- seulement suppose. Liste complete : App\Search\LengthLinksBuilder::
        // --- DUPLICATE_START_END_KEYS. ---
        $knownDuplicates = [
            '/mots/3-lettres/commencant/a/terminant/j',
            '/mots/2-lettres/commencant/e/terminant/j',
            '/mots/12-lettres/commencant/x/terminant/x',
        ];

        foreach ($knownDuplicates as $duplicatePath) {
            Assert::true(!isset($seen[$duplicatePath]), "doublon connu ne devrait jamais etre propose : {$duplicatePath}");
        }

        // --- Cas connus, verifies contre list_counts (voir le rapport AFTER pour la
        // --- verification independante complete, agent seo-registry). ---
        $byPath = [];

        foreach ($batch['rows'] as $row) {
            $byPath[$row['route_path']] = $row;
        }

        Assert::true(isset($byPath['/mots/2-lettres/commencant/a/terminant/a']), 'AA, pas un doublon connu');
        Assert::same(1, $byPath['/mots/2-lettres/commencant/a/terminant/a']['result_count']);
    } finally {
        unlink($tmpFile);
    }
};
