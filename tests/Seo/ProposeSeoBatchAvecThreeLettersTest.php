<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/propose_seo_batch.php, cas 'avec_three_letters' (App\Seo\Family::
 * WORD_LIST_AVEC_THREE_LETTERS, palier 3 de l'ouverture en entonnoir de "avec" a
 * l'indexation, demande produit du 2026-08-18) -- boite noire, un vrai sous-processus PHP,
 * jamais contre le vrai storage/seo_fr.sqlite (ce script ne l'ouvre de toute facon jamais,
 * uniquement storage/dictionary_fr.sqlite en lecture seule -- voir l'entete du script).
 *
 * Meme discipline que tests/Seo/ProposeSeoBatchAvecTwoLettersTest.php (palier 2) : ce cas
 * genere une PROPOSITION nouvelle a chaque execution (batch_id/added_at dynamiques), ce test ne
 * verifie donc PAS un batch_id exact, seulement sa forme et la structure des lignes.
 *
 * CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3) : 28 827 -> 28 167
 * lignes. scripts/propose_seo_batch.php exclut desormais les triplets dont le contenu duplique
 * EXACTEMENT celui d'un parent palier 1/2 (426 lignes) ou d'un triplet SOEUR de meme longueur
 * (234 lignes) -- voir AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES (scripts/
 * propose_seo_batch.php) pour le detail complet et les deux methodes de verification
 * independantes, 0 divergence. Preuve sur pieces citee par l'audit, confirmee : les 6 variantes
 * /mots/10-lettres/avec/{a,e,n,o,s,t}/w/x (1 mot chacune) dupliquent toutes exactement
 * /mots/10-lettres/avec/w/x (palier 2, deja indexe) et sont exclues de ce lot.
 *
 * CORRECTIF (D-041, 2026-08-21, isD041Excluded()) : 28 167 -> 27 501 lignes (666 exclusions
 * supplementaires) -- detecteur generique scripts/check_combinatorial_duplicates.php, regle de
 * priorite resolveDuplicateWinner() (scripts/lib/seo_duplicate_priority.php). Ces triplets perdent
 * desormais contre une autre famille combinatoire (word_list_combined, word_list_combined_with_letter,
 * word_list_commencant/terminant...) tranchant le MEME panier avec MOINS de composants.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $dictPath = $root . '/storage/dictionary_fr.sqlite';
    Assert::true(is_file($dictPath), 'base manquante : ' . $dictPath);

    $process = proc_open(
        [PHP_BINARY, $root . '/scripts/propose_seo_batch.php', 'avec_three_letters'],
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

    Assert::same(0, $exitCode, "propose_seo_batch.php avec_three_letters aurait du reussir : {$stderr}");
    Assert::true(str_contains($stderr, '27501 ligne(s) proposee(s)'), $stderr);

    $tmpFile = tempnam(sys_get_temp_dir(), 'avec_three_letters_batch_');
    file_put_contents($tmpFile, $stdout);

    try {
        // 28 827 lignes (~6,7x le palier 2) : le `require` ci-dessous compile un litteral PHP de
        // pres de 240 000 lignes DANS LE MEME PROCESSUS que tests/run.php -- epuise le
        // memory_limit CLI par defaut (128 Mo) sans ce relevement, meme raison exacte que
        // scripts/propose_seo_batch.php et scripts/apply_seo_batch.php (voir leurs ini_set()
        // respectifs). Persiste pour le reste du processus de test (ini_set() n'est jamais
        // remis a zero automatiquement) -- sans consequence, aucun autre test de cette suite
        // n'est sensible au memory_limit.
        ini_set('memory_limit', '512M');

        $batch = require $tmpFile;

        Assert::true(str_starts_with($batch['batch_id'], 'avec_three_letters-proposed-'), 'batch_id dynamique attendu (nouvelle proposition, pas une regularisation)');
        Assert::same(27_501, count($batch['rows']), '28 827 combinaisons a >= 1 resultat, moins 660 (C-3) moins 666 (D-041, doublons croises avec d\'autres familles combinatoires)');

        // --- Forme exacte de la route : longueur + "avec" + EXACTEMENT trois lettres distinctes,
        // --- toujours dans l'ordre alphabetique (jamais /mots/{N}-lettres/avec/{X}/{Y} seul,
        // --- jamais quatre lettres, jamais sans longueur). ---
        foreach ($batch['rows'] as $row) {
            Assert::true(preg_match('#^/mots/(\d+)-lettres/avec/([a-z])/([a-z])/([a-z])\z#', $row['route_path'], $m) === 1, $row['route_path']);
            $length = (int) $m[1];
            Assert::true($length >= 2 && $length <= 15, "longueur hors bornes : {$row['route_path']}");
            Assert::true($m[2] < $m[3] && $m[3] < $m[4], "lettres pas dans l'ordre alphabetique : {$row['route_path']}");
        }

        // --- R1-R7 respectees par construction (sitemap_fragment unique, canonical = route,
        // --- notes non vide, result_count jamais 0 pour une ligne index,follow). ---
        $seen = [];
        $singleResultCount = 0;
        foreach ($batch['rows'] as $row) {
            Assert::same('word_list_avec_three_letters', $row['family']);
            Assert::same('index,follow', $row['robots']);
            Assert::same($row['route_path'], $row['canonical_path'], 'R3 : jamais d\'alias indexable');
            Assert::same('avec-triple-0001', $row['sitemap_fragment']);
            Assert::true($row['result_count'] >= 1, 'R5 : jamais 0 resultat pour une ligne index,follow');
            Assert::true($row['result_count'] <= 10_000, 'plafond ROW_EXAMINATION_CEILING (D-019)');
            Assert::true(trim($row['notes']) !== '', 'R7 : note de maillage obligatoire');
            Assert::true(!isset($seen[$row['route_path']]), 'R2 : aucun doublon dans le lot');
            $seen[$row['route_path']] = true;

            if ($row['result_count'] === 1) {
                $singleResultCount++;
            }
        }

        Assert::same(740, $singleResultCount, '1 383 pages a 1 resultat apres C-3, moins 643 exclues par D-041 (paniers minuscules disproportionnellement dupliques par une autre famille) = 740');

        // --- Cas connus, verifies ligne par ligne contre le vrai solveur (voir le rapport AFTER
        // --- pour la verification independante complete, agent seo-registry). ---
        $byPath = [];
        foreach ($batch['rows'] as $row) {
            $byPath[$row['route_path']] = $row;
        }

        Assert::true(isset($byPath['/mots/3-lettres/avec/a/b/e']), 'BEA, plus petit mot possible avec trois lettres distinctes');
        Assert::same(1, $byPath['/mots/3-lettres/avec/a/b/e']['result_count']);
        Assert::true(isset($byPath['/mots/8-lettres/avec/e/s/t']));
        Assert::same(10_000, $byPath['/mots/8-lettres/avec/e/s/t']['result_count'], 'plafonne (ROW_EXAMINATION_CEILING, D-019)');

        // --- Aucune combinaison de longueur 2 (un mot de 2 lettres ne peut jamais contenir trois
        // --- lettres distinctes) -- 2 600 combinaisons automatiquement a 0 resultat, jamais
        // --- proposees ici (voir reports/query-plans/avec-length-3-letters-full-sweep.md). ---
        foreach ($batch['rows'] as $row) {
            Assert::true(!str_starts_with($row['route_path'], '/mots/2-lettres/'), "longueur 2 ne devrait jamais apparaitre : {$row['route_path']}");
        }

        // --- CORRECTIF C-3 (4e audit seo-technical-auditor, 2026-08-20) : les 6 variantes citees
        // --- sur pieces par l'audit (memes 1 mot que /mots/10-lettres/avec/w/x, palier 2, deja
        // --- indexe) ne doivent plus jamais apparaitre dans ce lot -- voir
        // --- AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES (scripts/propose_seo_batch.php) pour le
        // --- detail complet. ---
        foreach (['a', 'e', 'n', 'o', 's', 't'] as $extra) {
            Assert::true(
                !isset($byPath["/mots/10-lettres/avec/{$extra}/w/x"]),
                "doublon de contenu avec /mots/10-lettres/avec/w/x : /mots/10-lettres/avec/{$extra}/w/x"
            );
        }

        // --- Meme preuve a 15 lettres (8 variantes citees par l'audit, memes 1 mot que
        // --- /mots/15-lettres/avec/w/x, palier 2, deja indexe). ---
        foreach (['b', 'e', 'i', 'l', 'o', 'r', 's', 'u'] as $extra) {
            Assert::true(
                !isset($byPath["/mots/15-lettres/avec/{$extra}/w/x"]),
                "doublon de contenu avec /mots/15-lettres/avec/w/x : /mots/15-lettres/avec/{$extra}/w/x"
            );
        }
    } finally {
        unlink($tmpFile);
    }
};
