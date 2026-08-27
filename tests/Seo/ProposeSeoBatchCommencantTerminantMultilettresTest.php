<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/propose_seo_batch.php, cas 'commencant_terminant_multilettres' (axe 3 --
 * dimensionnement des prefixes/suffixes reels de longueur 2 a 4, extension de App\Seo\Family::
 * WORD_LIST_COMMENCANT / WORD_LIST_TERMINANT, AUCUNE nouvelle classification, demande produit
 * du 2026-08-18) -- boite noire, un vrai sous-processus PHP, jamais contre le vrai
 * storage/seo_fr.sqlite (ce script ne l'ouvre de toute facon jamais, uniquement
 * storage/dictionary_fr.sqlite en lecture seule -- voir l'entete du script).
 *
 * Meme discipline que tests/Seo/ProposeSeoBatchAvecThreeLettersTest.php : ce cas genere une
 * PROPOSITION nouvelle a chaque execution (batch_id/added_at dynamiques), ce test ne verifie
 * donc PAS un batch_id exact, seulement sa forme et la structure des lignes.
 *
 * CORRECTIF (D-041, 2026-08-21, isD041Excluded()) : 37 557 -> 36 918 lignes (639 exclusions,
 * TOUTES cote word_list_terminant -- word_list_commencant reste a 20 712, 0 exclusion : un prefixe
 * multi-lettres a toujours au maximum autant de composants que n'importe quelle autre famille de
 * ce projet, il ne perd donc jamais). Detecteur generique scripts/check_combinatorial_duplicates.php,
 * regle de priorite resolveDuplicateWinner() (scripts/lib/seo_duplicate_priority.php) -- CE cas est
 * la source reelle de la tres grande majorite des exclusions word_list_terminant (les 26 routes
 * mono-lettre du cas 'terminant' ne sont, elles, jamais assez etroites pour dupliquer une autre
 * famille).
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $dictPath = $root . '/storage/dictionary_fr.sqlite';
    Assert::true(is_file($dictPath), 'base manquante : ' . $dictPath);

    $process = proc_open(
        [PHP_BINARY, $root . '/scripts/propose_seo_batch.php', 'commencant_terminant_multilettres'],
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

    Assert::same(0, $exitCode, "propose_seo_batch.php commencant_terminant_multilettres aurait du reussir : {$stderr}");
    Assert::true(str_contains($stderr, '36918 ligne(s) proposee(s)'), $stderr);

    $tmpFile = tempnam(sys_get_temp_dir(), 'commencant_terminant_multilettres_batch_');
    file_put_contents($tmpFile, $stdout);

    try {
        ini_set('memory_limit', '512M');

        $batch = require $tmpFile;

        Assert::true(str_starts_with($batch['batch_id'], 'commencant_terminant_multilettres-proposed-'), 'batch_id dynamique attendu (nouvelle proposition)');
        Assert::same(36_918, count($batch['rows']), '39 539 combinaisons reelles moins 1 982 (doublons parent immediat) moins 639 (D-041, doublons croises avec d\'autres familles combinatoires)');

        $seen = [];
        $singleResultCount = 0;
        $familyCounts = [];
        $fragmentCounts = [];

        foreach ($batch['rows'] as $row) {
            Assert::true(
                preg_match('#^/mots/(commencant|terminant)/([a-z]{2,4})\z#', $row['route_path'], $m) === 1,
                $row['route_path'],
            );
            $direction = $m[1];

            // --- R1-R7 respectees par construction. ---
            Assert::same($direction === 'commencant' ? 'word_list_commencant' : 'word_list_terminant', $row['family'], 'AUCUNE nouvelle classification -- meme famille que le mono-lettre deja indexe (D-017)');
            Assert::same('index,follow', $row['robots']);
            Assert::same($row['route_path'], $row['canonical_path'], 'R3 : jamais d\'alias indexable');
            Assert::same($direction === 'commencant' ? 'starts-0002' : 'ends-0002', $row['sitemap_fragment']);
            Assert::true($row['result_count'] >= 1, 'R5 : jamais 0 resultat pour une ligne index,follow');
            Assert::true(trim($row['notes']) !== '', 'R7 : note de maillage obligatoire');
            Assert::true(!isset($seen[$row['route_path']]), 'R2 : aucun doublon dans le lot');
            $seen[$row['route_path']] = true;

            $familyCounts[$row['family']] = ($familyCounts[$row['family']] ?? 0) + 1;
            $fragmentCounts[$row['sitemap_fragment']] = ($fragmentCounts[$row['sitemap_fragment']] ?? 0) + 1;

            if ($row['result_count'] === 1) {
                $singleResultCount++;
            }
        }

        Assert::same(20_712, $familyCounts['word_list_commencant'] ?? 0, '21 734 prefixes reels moins 1 022 doublons (D-025-like arbitrage), 0 exclusion D-041 (un prefixe multi-lettres ne perd jamais)');
        Assert::same(16_206, $familyCounts['word_list_terminant'] ?? 0, '17 805 suffixes reels moins 960 doublons moins 639 (D-041)');
        Assert::same(20_712, $fragmentCounts['starts-0002'] ?? 0);
        Assert::same(16_206, $fragmentCounts['ends-0002'] ?? 0);
        Assert::same(7_240, $singleResultCount, 'pages a exactement 1 resultat parmi les 36 918 (7 879 avant D-041, moins 639 exclues -- toutes les exclusions D-041 de ce lot etaient a 1 resultat), GARDEES (docs/05, jamais sur le seul compteur)');

        // --- Regime EXACT (commencant, jamais tronque) vs BORNE (terminant, plafonne a
        // --- ROW_EXAMINATION_CEILING = 10 000, D-019) -- verifie sur un cas connu de chaque
        // --- cote (reports/query-plans/commencant-terminant-multi-lettres-dimensionnement.md). ---
        $byPath = [];

        foreach ($batch['rows'] as $row) {
            $byPath[$row['route_path']] = $row;
        }

        Assert::true(isset($byPath['/mots/commencant/re']));
        Assert::same(202_995, $byPath['/mots/commencant/re']['result_count'], 'EXACT, jamais tronque meme tres au-dela de 10 000');

        Assert::true(isset($byPath['/mots/terminant/ons']));
        Assert::same(10_000, $byPath['/mots/terminant/ons']['result_count'], 'BORNE, plafonne (ROW_EXAMINATION_CEILING, D-019 -- 79 948 mots reels)');

        // --- Contenu duplique avec la page parente immediate (constat data-engine, section 7 du
        // --- rapport de dimensionnement, arbitrage tranche par seo-registry) : la page la PLUS
        // --- COURTE reste index,follow canonique (deja presente dans ce lot), la PLUS LONGUE de
        // --- chaque paire est exclue -- verifie sur des exemples reels connus, pas seulement
        // --- suppose. ---
        Assert::true(isset($byPath['/mots/commencant/aq']), 'AQ (201 mots) : page courte, gagnante canonique, doit rester dans le lot');
        Assert::true(!isset($byPath['/mots/commencant/aqu']), 'AQU (201 mots, meme contenu que AQ) : doublon, jamais propose');

        Assert::true(isset($byPath['/mots/terminant/bc']), 'BC (1 mot) : page courte, gagnante canonique, doit rester dans le lot');
        Assert::true(!isset($byPath['/mots/terminant/abc']), 'ABC (1 mot, meme contenu que BC) : doublon, jamais propose');
    } finally {
        unlink($tmpFile);
    }
};
