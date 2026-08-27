<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/propose_seo_batch.php, cas 'avec_single_letter' (App\Seo\Family::
 * WORD_LIST_AVEC_SINGLE_LETTER, palier 1 de l'ouverture en entonnoir de "avec" a
 * l'indexation, demande produit du 2026-08-17) -- boite noire, un vrai sous-processus PHP,
 * jamais contre le vrai storage/seo_fr.sqlite (ce script ne l'ouvre de toute facon jamais,
 * uniquement storage/dictionary_fr.sqlite en lecture seule -- voir l'entete du script).
 *
 * Contrairement au cas 'position' (regularisation d'un lot deja applique, batch_id/added_at
 * FIXES), ce cas genere une PROPOSITION nouvelle a chaque execution (batch_id/added_at
 * dynamiques, meme comportement que 'length'/'commencant'/'terminant') -- ce test ne verifie
 * donc PAS un batch_id exact, seulement sa forme et la structure des 364 lignes.
 *
 * VERIFIE (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3) : ce palier est la RACINE
 * de la hierarchie de l'entonnoir "avec" (aucun parent AU SEIN de l'entonnoir "avec" ne peut le
 * dupliquer) -- calcul independant confirme 0 impact sur ce cas par les deux methodes decrites au
 * docblock de AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES (scripts/propose_seo_batch.php).
 *
 * CORRECTIF (D-041, 2026-08-21, isD041Excluded()) : la regle de priorite globale (resolveDuplicateWinner(),
 * scripts/lib/seo_duplicate_priority.php) compare desormais AUSSI contre des familles combinatoires
 * hors de l'entonnoir "avec" (commencant/terminant/combined) -- /mots/2-lettres/avec/w (1 composant
 * de plus que /mots/terminant/wu, 1 seul mot WU partage entre 3 familles) perd desormais contre
 * word_list_terminant (moins de composants). 1 exclusion, 364 -> 363.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $dictPath = $root . '/storage/dictionary_fr.sqlite';
    Assert::true(is_file($dictPath), 'base manquante : ' . $dictPath);

    $process = proc_open(
        [PHP_BINARY, $root . '/scripts/propose_seo_batch.php', 'avec_single_letter'],
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

    Assert::same(0, $exitCode, "propose_seo_batch.php avec_single_letter aurait du reussir : {$stderr}");
    Assert::true(str_contains($stderr, '363 ligne(s) proposee(s)'), $stderr);

    $tmpFile = tempnam(sys_get_temp_dir(), 'avec_single_letter_batch_');
    file_put_contents($tmpFile, $stdout);

    try {
        $batch = require $tmpFile;

        Assert::true(str_starts_with($batch['batch_id'], 'avec_single_letter-proposed-'), 'batch_id dynamique attendu (nouvelle proposition, pas une regularisation)');
        Assert::same(363, count($batch['rows']), '14 longueurs x 26 lettres, moins 1 exclusion D-041 (/mots/2-lettres/avec/w)');

        // --- Forme exacte de la route : longueur + "avec" + UNE SEULE lettre, rien d'autre
        // --- (jamais /mots/avec/{X} sans longueur, jamais /mots/{N}-lettres/avec/{X}/{Y}). ---
        foreach ($batch['rows'] as $row) {
            Assert::true(preg_match('#^/mots/(\d+)-lettres/avec/[a-z]$#', $row['route_path'], $m) === 1, $row['route_path']);
            $length = (int) $m[1];
            Assert::true($length >= 2 && $length <= 15, "longueur hors bornes : {$row['route_path']}");
        }

        // --- R1-R7 respectees par construction (sitemap_fragment unique, canonical = route,
        // --- notes non vide, result_count jamais 0 pour une ligne index,follow). ---
        $seen = [];
        $singleResultCount = 0;
        foreach ($batch['rows'] as $row) {
            Assert::same('word_list_avec_single_letter', $row['family']);
            Assert::same('index,follow', $row['robots']);
            Assert::same($row['route_path'], $row['canonical_path'], 'R3 : jamais d\'alias indexable');
            Assert::same('avec-single-0001', $row['sitemap_fragment']);
            Assert::true($row['result_count'] >= 1, 'R5 : jamais 0 resultat pour une ligne index,follow');
            Assert::true($row['result_count'] <= 10_000, 'plafond ROW_EXAMINATION_CEILING (D-019)');
            Assert::true(trim($row['notes']) !== '', 'R7 : note de maillage obligatoire');
            Assert::true(!isset($seen[$row['route_path']]), 'R2 : aucun doublon dans le lot');
            $seen[$row['route_path']] = true;

            if ($row['result_count'] === 1) {
                $singleResultCount++;
            }
        }

        Assert::same(1, $singleResultCount, '1 page a exactement 1 resultat (/mots/2-lettres/avec/z, ZA) -- avec/w (WU) exclu par D-041, voir ci-dessous');

        // --- Cas connus, verifies ligne par ligne (deja mesures dans reports/query-plans/
        // --- avec-length-1-letter-full-sweep.md). ---
        $byPath = [];
        foreach ($batch['rows'] as $row) {
            $byPath[$row['route_path']] = $row;
        }

        // --- D-041 (2026-08-21) : /mots/2-lettres/avec/w (WU, 1 mot) perd contre
        // --- /mots/terminant/wu (moins de composants) dans un groupe croise a 3 familles --
        // --- exclu de ce lot, voir isD041Excluded() (scripts/propose_seo_batch.php). ---
        Assert::true(!isset($byPath['/mots/2-lettres/avec/w']), 'D-041 : doublon de contenu avec /mots/terminant/wu (WU, seul mot), la forme la plus generale (terminant, 1 composant) gagne');

        Assert::true(isset($byPath['/mots/2-lettres/avec/z']));
        Assert::same(1, $byPath['/mots/2-lettres/avec/z']['result_count'], 'ZA, seul mot de 2 lettres avec un Z');

        Assert::true(isset($byPath['/mots/10-lettres/avec/z']));
        Assert::same(10_000, $byPath['/mots/10-lettres/avec/z']['result_count'], 'plafonne (13 959 correspondances reelles), pire cas mesure du balayage');
    } finally {
        unlink($tmpFile);
    }
};
