<?php

declare(strict_types=1);

use App\Config;
use App\Search\Normalizer;
use Tests\Support\Assert;

/**
 * scripts/check_combinatorial_duplicates.php (D-041) -- boite noire, un vrai sous-processus PHP,
 * JAMAIS contre les vrais storage/dictionary_fr.sqlite / storage/seo_fr.sqlite du depot :
 * SCRABBLE_DICTIONARY_DB_PATH et SCRABBLE_SEO_DB_PATH redirigent le script vers des fichiers
 * temporaires propres a ce test, supprimes a la fin (meme discipline que
 * tests/Seo/BuildScriptsTest.php).
 *
 * Jeu de donnees synthetique volontairement minuscule (3 mots, quelques lignes de registre) --
 * pas la vraie base de 838 180 termes -- pour verifier le COMPORTEMENT de l'outil (positif
 * detecte, negatif propre, anomalie de tracabilite signalee), pas ses performances sur le vrai
 * volume (voir le rapport AFTER de cette tache pour le balayage complet mesure sur le vrai
 * registre).
 *
 * Scenario "positif" (cas croise, meme classe de defaut que D-039/C-2) : un seul mot XALAM
 * (commence par X, termine par M, contient A) rend deux familles DIFFERENTES -- word_list_combined
 * (commencant+terminant, avec longueur) et word_list_combined_with_letter (commencant+terminant+
 * avec) -- strictement identiques en CONTENU (le meme et seul mot), sans que l'outil ait besoin
 * de connaitre a l'avance cette paire de familles precise. Un second mot ZAZOU (independant,
 * commence par Z) sert de temoin NEGATIF : /mots/5-lettres (word_list_length, contient les DEUX
 * mots) a un ensemble different des deux pages ci-dessus (2 mots contre 1) -- ne doit jamais
 * apparaitre dans un groupe de doublons.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $tmpDir = sys_get_temp_dir() . '/scrabble_combinatorial_duplicates_test_' . bin2hex(random_bytes(4));
    mkdir($tmpDir);

    $dictionaryPath = $tmpDir . '/dictionary_fr.sqlite';
    $registryPathPositive = $tmpDir . '/seo_fr_positive.sqlite';
    $registryPathClean = $tmpDir . '/seo_fr_clean.sqlite';

    try {
        // --- Dictionnaire synthetique : XALAM, ZAZOU, AB -- construit avec les VRAIES formules
        // (App\Search\Normalizer::score()/signature()/reverse()), jamais des valeurs tapees a la
        // main, pour que le WHERE reconstruit par reflexion (meme code que le solveur reel)
        // trouve les bonnes lignes. ---
        $schemaSql = file_get_contents($root . '/schema.sql');
        Assert::true($schemaSql !== false, 'schema.sql introuvable');

        $dictionaryPdo = new PDO('sqlite:' . $dictionaryPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dictionaryPdo->exec($schemaSql);

        $tileScores = Config::load('fr')->tileScores;
        $insertTerm = $dictionaryPdo->prepare(
            'INSERT INTO terms (display_term, normalized, is_french, is_ods8, is_ods9, is_admitted, score, length, signature, reversed) '
            . 'VALUES (?, ?, 1, 0, 0, 0, ?, ?, ?, ?)'
        );

        foreach (['XALAM', 'ZAZOU', 'AB'] as $word) {
            $insertTerm->execute([
                $word,
                $word,
                Normalizer::score($word, $tileScores),
                strlen($word),
                Normalizer::signature($word),
                Normalizer::reverse($word),
            ]);
        }
        unset($dictionaryPdo, $insertTerm);

        // --- Registre "positif" : deux pages de familles differentes, meme mot unique (XALAM),
        // plus une page temoin (word_list_length, 2 mots -- ensemble different). ---
        $seoSchemaSql = file_get_contents($root . '/app/Seo/schema.sql');
        Assert::true($seoSchemaSql !== false, 'app/Seo/schema.sql introuvable');

        $registryPdo = new PDO('sqlite:' . $registryPathPositive, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $registryPdo->exec($seoSchemaSql);

        $insertRow = $registryPdo->prepare(
            'INSERT INTO registry (route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
            . 'VALUES (?, ?, \'index,follow\', ?, NULL, \'test-batch\', ?, \'test\', \'2026-08-21\')'
        );

        $insertRow->execute(['/mots/5-lettres/commencant/x/terminant/m', 'word_list_combined', '/mots/5-lettres/commencant/x/terminant/m', 1]);
        $insertRow->execute(['/mots/commencant/x/terminant/m/avec/a', 'word_list_combined_with_letter', '/mots/commencant/x/terminant/m/avec/a', 1]);
        $insertRow->execute(['/mots/5-lettres', 'word_list_length', '/mots/5-lettres', 2]);
        unset($registryPdo, $insertRow);

        // --- Registre "propre" : deux pages, ensembles disjoints et de tailles differentes --
        // aucun doublon possible. ---
        $cleanPdo = new PDO('sqlite:' . $registryPathClean, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $cleanPdo->exec($seoSchemaSql);
        $insertClean = $cleanPdo->prepare(
            'INSERT INTO registry (route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
            . 'VALUES (?, ?, \'index,follow\', ?, NULL, \'test-batch\', ?, \'test\', \'2026-08-21\')'
        );
        $insertClean->execute(['/mots/5-lettres', 'word_list_length', '/mots/5-lettres', 2]);
        $insertClean->execute(['/mots/2-lettres', 'word_list_length', '/mots/2-lettres', 1]);
        unset($cleanPdo, $insertClean);

        $run = static function (string $dbPath) use ($root, $dictionaryPath): array {
            $cmd = [PHP_BINARY, $root . '/scripts/check_combinatorial_duplicates.php', '--quiet'];
            $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $env = array_merge(
                getenv() === false ? [] : getenv(),
                [
                    'SCRABBLE_DICTIONARY_DB_PATH' => $dictionaryPath,
                    'SCRABBLE_SEO_DB_PATH' => $dbPath,
                ],
            );

            $process = proc_open($cmd, $descriptors, $pipes, $root, $env);
            Assert::true($process !== false, 'impossible de lancer check_combinatorial_duplicates.php');

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            return [$exitCode, $stdout, $stderr];
        };

        // --- Cas positif : le doublon croise doit etre detecte, la page temoin (ensemble
        // different) ne doit apparaitre dans AUCUN groupe. ---
        [$exitCode, $stdout, $stderr] = $run($registryPathPositive);

        Assert::same(1, $exitCode, "l'outil doit sortir en erreur quand un doublon est trouve : {$stderr}");
        Assert::true(str_contains($stdout, 'groupes de doublons trouves : 1'), "1 groupe de doublons attendu :\n{$stdout}");
        Assert::true(str_contains($stdout, 'lignes en exces (doublons)  : 1'), "1 ligne en exces attendue :\n{$stdout}");
        Assert::true(
            str_contains($stdout, '/mots/5-lettres/commencant/x/terminant/m  [word_list_combined]'),
            "la page word_list_combined devrait figurer dans le groupe de doublons :\n{$stdout}"
        );
        Assert::true(
            str_contains($stdout, '/mots/commencant/x/terminant/m/avec/a  [word_list_combined_with_letter]'),
            "la page word_list_combined_with_letter devrait figurer dans le groupe de doublons :\n{$stdout}"
        );
        Assert::true(str_contains($stdout, 'CROISE (familles differentes)'), "le groupe doit etre signale CROISE (deux familles distinctes) :\n{$stdout}");
        Assert::true(
            !str_contains($stdout, '/mots/5-lettres  [word_list_length]'),
            "la page temoin (ensemble de 2 mots, distinct) ne doit apparaitre dans AUCUN groupe :\n{$stdout}"
        );
        Assert::true(str_contains($stdout, 'anomalies de tracabilite    : 0'), "aucune anomalie attendue sur ce jeu de donnees valide :\n{$stdout}");

        // --- Cas negatif propre : aucun doublon, code de sortie 0, message explicite. ---
        [$exitCodeClean, $stdoutClean, $stderrClean] = $run($registryPathClean);

        Assert::same(0, $exitCodeClean, "l'outil doit sortir proprement quand aucun doublon n'existe : {$stderrClean}");
        Assert::true(str_contains($stdoutClean, 'groupes de doublons trouves : 0'), "0 groupe de doublons attendu :\n{$stdoutClean}");
        Assert::true(str_contains($stdoutClean, 'RESULTAT : propre'), "message de resultat propre attendu :\n{$stdoutClean}");
    } finally {
        // Nettoyage recursif du dossier temporaire.
        $cleanup = static function (string $dir) use (&$cleanup): void {
            if (!is_dir($dir)) {
                return;
            }

            foreach (scandir($dir) as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $path = $dir . '/' . $entry;

                if (is_dir($path)) {
                    $cleanup($path);
                } else {
                    unlink($path);
                }
            }

            rmdir($dir);
        };

        $cleanup($tmpDir);
    }
};
