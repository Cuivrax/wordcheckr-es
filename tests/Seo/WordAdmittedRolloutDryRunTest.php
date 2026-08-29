<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/apply_word_admitted_rollout.php --dry-run (ajoute 2026-08-29, docs/DECISIONS.md
 * ES-013) -- boite noire, un vrai sous-processus PHP par appel, JAMAIS contre les vrais
 * storage/dictionary_es.sqlite / storage/seo_es.sqlite du depot : SCRABBLE_DICTIONARY_DB_PATH
 * et SCRABBLE_SEO_DB_PATH redirigent le script vers des bases temporaires propres a ce test,
 * supprimees a la fin (meme discipline que tests/Seo/BuildScriptsTest.php).
 *
 * Verifie la garantie centrale de --dry-run : le meme flux de validation R1-R7 s'execute
 * (mêmes comptes par longueur qu'une application reelle), mais storage/seo_es.sqlite n'est
 * JAMAIS ecrit -- puis qu'une invocation SANS --dry-run, juste apres, ecrit reellement les
 * memes lignes (le dry-run n'est pas un chemin de code distinct qui pourrait diverger).
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $tmpDir = sys_get_temp_dir() . '/scrabble_seo_es_dryrun_test_' . bin2hex(random_bytes(4));
    mkdir($tmpDir);

    $dictPath = $tmpDir . '/dictionary_es.sqlite';
    $seoPath = $tmpDir . '/seo_es.sqlite';

    $run = static function (array $args) use ($root, $dictPath, $seoPath): array {
        $cmd = array_merge([PHP_BINARY, $root . '/scripts/apply_word_admitted_rollout.php'], $args);

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = array_merge(
            getenv() === false ? [] : getenv(),
            [
                'SCRABBLE_DICTIONARY_DB_PATH' => $dictPath,
                'SCRABBLE_SEO_DB_PATH' => $seoPath,
            ],
        );

        $process = proc_open($cmd, $descriptors, $pipes, $root, $env);
        Assert::true($process !== false, 'impossible de lancer apply_word_admitted_rollout.php');

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [$exitCode, $stdout, $stderr];
    };

    try {
        // --- Fixture dictionnaire minimale : 3 mots admis longueur 2, 2 mots admis longueur
        // 3, 1 mot longueur 2 NON admis (ne doit jamais apparaitre dans les comptes). ---
        $dict = new PDO('sqlite:' . $dictPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dict->exec(<<<'SQL'
            CREATE TABLE terms (
                id           INTEGER PRIMARY KEY,
                display_term TEXT    NOT NULL,
                normalized   TEXT    NOT NULL UNIQUE,
                is_spanish   INTEGER NOT NULL DEFAULT 0,
                is_ods8      INTEGER NOT NULL DEFAULT 0,
                is_ods9      INTEGER NOT NULL DEFAULT 0,
                is_admitted  INTEGER NOT NULL DEFAULT 0,
                score        INTEGER NOT NULL,
                length       INTEGER NOT NULL,
                signature    TEXT    NOT NULL,
                reversed     TEXT    NOT NULL
            )
            SQL);

        $insert = $dict->prepare(
            'INSERT INTO terms (display_term, normalized, is_ods8, is_admitted, score, length, signature, reversed) '
            . 'VALUES (?, ?, 1, 1, 1, ?, ?, ?)'
        );

        foreach (['AS', 'YO', 'TE'] as $word) {
            $insert->execute([$word, $word, 2, $word, strrev($word)]);
        }
        foreach (['SOL', 'MAR'] as $word) {
            $insert->execute([$word, $word, 3, $word, strrev($word)]);
        }

        $notAdmitted = $dict->prepare(
            'INSERT INTO terms (display_term, normalized, is_ods8, is_admitted, score, length, signature, reversed) '
            . 'VALUES (?, ?, 0, 0, 1, 2, ?, ?)'
        );
        $notAdmitted->execute(['ZZ', 'ZZ', 'ZZ', 'ZZ']);
        // PDOStatement conserve une reference implicite a son PDO parent -- les deux doivent
        // etre liberes AVANT $dict pour que SQLite relache reellement le verrou de fichier sous
        // Windows (sinon le nettoyage recursif en fin de test echoue, "Resource temporarily
        // unavailable").
        unset($insert, $notAdmitted, $dict);
        gc_collect_cycles();

        // --- registre vide, meme schema que storage/seo_es.sqlite (repris de app/Seo/schema.sql). ---
        $seo = new PDO('sqlite:' . $seoPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $seo->exec(file_get_contents($root . '/app/Seo/schema.sql'));
        unset($seo);

        // --- --lengths obligatoire : refuse sans lui, dry-run ou non. ---
        [$exitCode, , $stderr] = $run(['--dry-run']);
        Assert::true($exitCode !== 0, '--lengths manquant aurait du etre refuse');
        Assert::true(str_contains($stderr, '--lengths'));

        // --- --dry-run + --reset-family : combinaison refusee (dry-run n'ecrit jamais, reset
        // ecrit toujours -- incompatibles par construction). ---
        [$exitCode, , $stderr] = $run(['--lengths=2,3', '--dry-run', '--reset-family']);
        Assert::true($exitCode !== 0, '--dry-run et --reset-family auraient du etre incompatibles');
        Assert::true(str_contains($stderr, 'incompatibles'));

        // --- --dry-run : sort 0, rapporte les BONS comptes par longueur (3 puis 2, jamais le
        // mot non admis), mais storage/seo_es.sqlite reste a 0 ligne. ---
        [$exitCode, $stdout] = $run(['--lengths=2,3', '--dry-run']);
        Assert::same(0, $exitCode, "dry-run valide aurait du reussir : {$stdout}");
        Assert::true(str_contains($stdout, 'longitud 2 : 3 palabra'), $stdout);
        Assert::true(str_contains($stdout, 'longitud 3 : 2 palabra'), $stdout);
        Assert::true(str_contains($stdout, 'DRY-RUN'), $stdout);

        $seo = new PDO('sqlite:' . $seoPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $countAfterDryRun = (int) $seo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
        Assert::same(0, $countAfterDryRun, '--dry-run n\'aurait jamais du ecrire dans le registre');
        unset($seo);

        // --- Meme invocation SANS --dry-run : ecrit reellement exactement les memes comptes
        // (5 lignes : 3 + 2), preuve que le dry-run n'est pas un chemin de code divergent. ---
        [$exitCode, $stdout] = $run(['--lengths=2,3']);
        Assert::same(0, $exitCode, "application reelle aurait du reussir : {$stdout}");
        Assert::true(str_contains($stdout, 'longitud 2 : 3 palabra'), $stdout);
        Assert::true(str_contains($stdout, 'longitud 3 : 2 palabra'), $stdout);
        Assert::true(!str_contains($stdout, 'DRY-RUN'), $stdout);

        $seo = new PDO('sqlite:' . $seoPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $countAfterApply = (int) $seo->query(
            "SELECT COUNT(*) c FROM registry WHERE family = 'word_admitted' AND robots = 'index,follow'"
        )->fetch()['c'];
        Assert::same(5, $countAfterApply, 'application reelle aurait du ecrire exactement 5 lignes (3 + 2)');
        unset($seo);
    } finally {
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

        gc_collect_cycles();
        $cleanup($tmpDir);
    }
};
