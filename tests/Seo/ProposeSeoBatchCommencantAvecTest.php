<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/propose_seo_batch.php, cas 'commencant_avec' (dernier des quatre axes commençant/
 * terminant/avec travaillés le 2026-08-18, App\Search\PrefixAvecLinksBuilder,
 * App\Seo\Family::WORD_LIST_COMMENCANT_WITH_LETTER, NOUVELLE classification distincte à la fois
 * de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER) -- boîte noire, un vrai
 * sous-processus PHP, jamais contre le vrai storage/seo_fr.sqlite (ce script ne l'ouvre de toute
 * façon jamais, uniquement storage/dictionary_fr.sqlite en lecture seule -- voir l'entête du
 * script).
 *
 * Même discipline que tests/Seo/ProposeSeoBatchCombinedWithLetterTest.php : ce cas génère une
 * PROPOSITION nouvelle à chaque exécution (batch_id/added_at dynamiques), ce test ne vérifie
 * donc PAS un batch_id exact, seulement sa forme et la structure des 646 lignes.
 *
 * CORRECTIF C-1 (2026-08-19, audit seo-technical-auditor consolidé sur D-035/D-036, bloquant) :
 * le même contrôle de doublon de contenu ajouté au cas 'combined_with_letter' (ligne parente
 * SANS lettre "avec" au même compte exact) a été ajouté ici aussi, par discipline -- mais 0
 * ligne de ce lot précis est concernée (vérifié indépendamment ci-dessous, section
 * "Régression C-1") : aucun préfixe réel de ce projet n'a la totalité de ses mots contenant
 * systématiquement une même lettre distincte. Le compte de ce test reste donc à 646, inchangé.
 *
 * CORRECTIF I-A (2026-08-19, 2e audit seo-technical-auditor sur D-037, non bloquant) : même
 * contrôle de doublon de contenu ENTRE LETTRES AVEC SOEURS du même prefixe (findSiblingContent
 * Duplicates()) ajouté ici aussi, par discipline -- 0 groupe trouvé sur les 26 prefixes (paniers
 * bien plus grands qu'un panier commencant+terminant, rendant une coïncidence exacte bien plus
 * rare), recalculé indépendamment ci-dessous (section "Régression I-A"). Le compte reste donc à
 * 646, inchangé.
 *
 * CORRECTIF (D-041, 2026-08-21, isD041Excluded()) : 646 -> 642 lignes (4 exclusions
 * supplémentaires) -- détecteur générique scripts/check_combinatorial_duplicates.php, règle de
 * priorité resolveDuplicateWinner() (scripts/lib/seo_duplicate_priority.php). L'unique page à 1
 * résultat du lot précédent (W+J) fait partie d'un groupe croisé à QUATRE familles/quatre pages
 * partageant un seul et même mot : /mots/10-lettres/avec/j/w (3 composants),
 * /mots/commencant/w/avec/j (2 composants), /mots/commencant/w/terminant/l (2 composants) et
 * /mots/commencant/webj (préfixe multi-lettres, 1 composant -- gagnant). commencant/w/avec/j perd
 * (2 composants > 1) -- 0 page à 1 résultat après ce correctif.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $dictPath = $root . '/storage/dictionary_fr.sqlite';
    Assert::true(is_file($dictPath), 'base manquante : ' . $dictPath);

    $process = proc_open(
        [PHP_BINARY, $root . '/scripts/propose_seo_batch.php', 'commencant_avec'],
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

    Assert::same(0, $exitCode, "propose_seo_batch.php commencant_avec aurait du reussir : {$stderr}");
    Assert::true(str_contains($stderr, '642 ligne(s) proposee(s)'), $stderr);

    $tmpFile = tempnam(sys_get_temp_dir(), 'commencant_avec_batch_');
    file_put_contents($tmpFile, $stdout);

    try {
        $batch = require $tmpFile;

        Assert::true(str_starts_with($batch['batch_id'], 'commencant_avec-proposed-'), 'batch_id dynamique attendu (nouvelle proposition)');
        Assert::same(642, count($batch['rows']), '676 combinaisons brutes moins 26 diagonales (D-032) moins 4 a 0 resultat moins 4 (D-041, doublons croises)');

        // --- Forme exacte de la route : commencant + avec, chacun d'une seule lettre, SANS
        // --- longueur, SANS terminant -- et jamais la lettre "avec" degeneree (D-032). ---
        $seen = [];
        $singleResultCount = 0;
        $cappedCount = 0;

        foreach ($batch['rows'] as $row) {
            Assert::true(preg_match('#^/mots/commencant/([a-z])/avec/([a-z])\z#', $row['route_path'], $m) === 1, $row['route_path']);
            Assert::true($m[2] !== $m[1], "lettre avec degeneree (D-032) ne devrait jamais apparaitre : {$row['route_path']}");

            // --- R1-R7 respectees par construction. ---
            Assert::same('word_list_commencant_with_letter', $row['family']);
            Assert::same('index,follow', $row['robots']);
            Assert::same($row['route_path'], $row['canonical_path'], 'R3 : jamais d\'alias indexable');
            Assert::same('commencant-avec-0001', $row['sitemap_fragment']);
            Assert::true($row['result_count'] >= 1, 'R5 : jamais 0 resultat pour une ligne index,follow');
            Assert::true($row['result_count'] <= 10_000, 'plafond ROW_EXAMINATION_CEILING (D-019, regime BORNE des que "avec" est present)');
            Assert::true(trim($row['notes']) !== '', 'R7 : note de maillage obligatoire');
            Assert::true(!isset($seen[$row['route_path']]), 'R2 : aucun doublon dans le lot');
            $seen[$row['route_path']] = true;

            if ($row['result_count'] === 1) {
                $singleResultCount++;
            }

            if ($row['result_count'] === 10_000) {
                $cappedCount++;
            }
        }

        Assert::same(0, $singleResultCount, '0 page a exactement 1 resultat -- W+J (seul cas a 1 resultat avant D-041) exclu, voir docblock de fichier');
        Assert::same(150, $cappedCount, '150/642 combinaisons plafonnees a ROW_EXAMINATION_CEILING (reports/query-plans/commencant-avec-maillage.md), inchange par D-041 (aucune combinaison plafonnee n\'est concernee)');

        // --- Cas connus, verifies contre list_counts (voir le rapport AFTER pour la
        // --- verification independante complete, agent seo-registry). ---
        $byPath = [];

        foreach ($batch['rows'] as $row) {
            $byPath[$row['route_path']] = $row;
        }

        Assert::true(isset($byPath['/mots/commencant/a/avec/b']));
        Assert::true(!isset($byPath['/mots/commencant/a/avec/a']), 'lettre avec = prefixe, degeneree, jamais proposee (D-032)');
        // --- D-041 (2026-08-21) : W+J (1 mot) fait partie d'un groupe croise a 4 familles --
        // --- /mots/commencant/webj (prefixe multi-lettres, 1 composant) gagne, commencant/w/avec/j
        // --- (2 composants) est exclu, voir isD041Excluded() (scripts/propose_seo_batch.php). ---
        Assert::true(!isset($byPath['/mots/commencant/w/avec/j']), 'D-041 : doublon de contenu avec /mots/commencant/webj (moins de composants)');
        Assert::true(isset($byPath['/mots/commencant/r/avec/e']));
        Assert::same(10_000, $byPath['/mots/commencant/r/avec/e']['result_count'], 'plafonne (ROW_EXAMINATION_CEILING, D-019 -- 219 076 mots reels)');
        Assert::true(!isset($byPath['/mots/commencant/v/avec/w']), 'V+W, 0 resultat, jamais proposee (R5)');
        Assert::true(!isset($byPath['/mots/commencant/x/avec/j']), 'X+J, 0 resultat, jamais proposee (R5)');
        Assert::true(!isset($byPath['/mots/commencant/x/avec/k']), 'X+K, 0 resultat, jamais proposee (R5)');
        Assert::true(!isset($byPath['/mots/commencant/x/avec/w']), 'X+W, 0 resultat, jamais proposee (R5)');

        // --- Regression C-1 (audit consolide, bloquant) : recalcule independamment, directement
        // --- contre le dictionnaire (jamais contre le script sous test lui-meme), que AUCUNE
        // --- des 646 lignes n'est un doublon de contenu avec sa page parente SANS lettre "avec"
        // --- (list_counts, list_type 'start' -- meme regle que 'combined_with_letter'). ---
        $dictPdo = new PDO('sqlite:' . $dictPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $dictPdo->exec('PRAGMA query_only = ON');

        $startCounts = [];
        foreach ($dictPdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start'")->fetchAll() as $row) {
            $startCounts[strtolower((string) $row['list_key'])] = (int) $row['count'];
        }

        $startWithCounts = [];
        foreach ($dictPdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_with'")->fetchAll() as $row) {
            $startWithCounts[strtolower((string) $row['list_key'])] = (int) $row['count'];
        }

        $duplicateFound = 0;

        foreach ($startWithCounts as $key => $n) {
            [$prefix] = explode(':', $key, 2);

            if (isset($startCounts[$prefix]) && $startCounts[$prefix] === $n) {
                $duplicateFound++;
            }
        }

        Assert::same(0, $duplicateFound, 'aucune ligne start_with ne devrait egaler son parent start (verifie independamment sur les 650 combinaisons non degenerees)');

        // --- Regression I-A (2e audit, non bloquant), recalculee INDEPENDAMMENT (empreinte SQL
        // --- GROUP_CONCAT+sha1, structurellement differente du filtre PHP sur panier en cache
        // --- utilise par findSiblingContentDuplicates() dans le script sous test) : pour CHAQUE
        // --- prefixe du lot, aucune lettre "avec" ne doit designer EXACTEMENT le meme ensemble
        // --- de mots qu'une autre lettre "avec" soeur du meme prefixe. ---
        $lettersByPrefixForCheck = [];
        foreach ($batch['rows'] as $row) {
            if (preg_match('#^/mots/commencant/([a-z])/avec/([a-z])\z#', $row['route_path'], $mm) === 1) {
                $lettersByPrefixForCheck[$mm[1]][] = $mm[2];
            }
        }

        $fingerprintStmt = $dictPdo->prepare(
            "SELECT group_concat(normalized, '|') AS fp
             FROM (SELECT normalized FROM terms WHERE substr(normalized,1,1) = ? AND instr(normalized, ?) > 0 ORDER BY normalized)"
        );

        $remainingSiblingDuplicates = 0;

        foreach ($lettersByPrefixForCheck as $prefixKey => $letters) {
            if (count($letters) < 2) {
                continue;
            }

            $seenFingerprints = [];

            foreach ($letters as $letter) {
                $fingerprintStmt->execute([strtoupper($prefixKey), strtoupper($letter)]);
                $hash = sha1((string) $fingerprintStmt->fetch()['fp']);

                if (isset($seenFingerprints[$hash])) {
                    $remainingSiblingDuplicates++;
                }

                $seenFingerprints[$hash] = true;
            }
        }

        Assert::same(0, $remainingSiblingDuplicates, 'I-A : aucune lettre avec ne devrait designer le meme ensemble de mots qu une lettre soeur du meme prefixe, recalcule independamment sur les ' . count($lettersByPrefixForCheck) . ' prefixes du lot');
    } finally {
        unlink($tmpFile);
    }
};
