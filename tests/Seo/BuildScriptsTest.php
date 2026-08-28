<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/build_seo_registry.php, scripts/apply_seo_batch.php et scripts/build_sitemaps.php
 * -- boite noire, un vrai sous-processus PHP par appel, JAMAIS contre les vrais
 * storage/seo_es.sqlite / public/ du depot : SCRABBLE_SEO_DB_PATH et SCRABBLE_PUBLIC_DIR
 * redirigent les trois scripts vers un dossier temporaire propre a ce test, supprime a la fin
 * (meme discipline que tests/Seo/RegistryTest.php).
 *
 * Verifie que les regles dures documentees dans scripts/apply_seo_batch.php sont appliquees
 * par l'OUTIL -- un lot qui les viole doit echouer a l'execution (exit code != 0), pas
 * seulement etre deconseille en commentaire.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $tmpDir = sys_get_temp_dir() . '/scrabble_seo_es_scripts_test_' . bin2hex(random_bytes(4));
    mkdir($tmpDir);
    mkdir($tmpDir . '/public');

    $dbPath = $tmpDir . '/seo_es.sqlite';
    $publicDir = $tmpDir . '/public';

    $run = static function (string $script, array $args = []) use ($root, $dbPath, $publicDir): array {
        $cmd = array_merge([PHP_BINARY, $root . '/scripts/' . $script], $args);

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = array_merge(
            getenv() === false ? [] : getenv(),
            [
                'SCRABBLE_SEO_DB_PATH' => $dbPath,
                'SCRABBLE_PUBLIC_DIR' => $publicDir,
                // Plafond de production volontairement trop grand pour re-generer des
                // dizaines de lignes dans un test -- repli borne a 3 pour exercer reellement
                // le refus R6 (voir plus bas).
                'SCRABBLE_SEO_MAX_SPANISH_NOT_ADMITTED' => '3',
            ],
        );

        $process = proc_open($cmd, $descriptors, $pipes, $root, $env);
        Assert::true($process !== false, "impossible de lancer {$script}");

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [$exitCode, $stdout, $stderr];
    };

    $writeBatch = static function (string $path, array $rows, string $batchId = 'test-batch') use ($tmpDir): void {
        $export = var_export(['batch_id' => $batchId, 'added_at' => '2026-08-28', 'rows' => $rows], true);
        file_put_contents($path, "<?php\nreturn {$export};\n");
    };

    try {
        // --- build_seo_registry.php : schema pose, 0 ligne, integrity ok. ---
        [$exitCode, $stdout] = $run('build_seo_registry.php');
        Assert::same(0, $exitCode, 'build_seo_registry.php aurait du reussir');
        Assert::true(is_file($dbPath));
        Assert::true(str_contains($stdout, 'integrity_check = ok'));
        Assert::true(str_contains($stdout, 'lignes dans `registry` : 0'));

        $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $count = $pdo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
        Assert::same(0, (int) $count, 'un build neuf ne doit jamais poser de ligne (jamais d\'indexation par omission)');
        unset($pdo);

        // --- Rejeu sans --reset : ne touche pas une base existante. ---
        [$exitCode, $stdout] = $run('build_seo_registry.php');
        Assert::same(0, $exitCode);
        Assert::true(str_contains($stdout, 'deja presente'));

        // --- apply_seo_batch.php : lot valide accepte (home, hub, une longueur). ---
        $goodBatch = $tmpDir . '/good_batch.php';
        $writeBatch($goodBatch, [
            [
                'route_path' => '/',
                'family' => 'home',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'core-0001',
                'notes' => 'pagina de inicio',
            ],
            [
                'route_path' => '/palabras',
                'family' => 'home',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'core-0001',
                'notes' => 'hub de navegacion',
            ],
            [
                'route_path' => '/palabras/9-letras',
                'family' => 'word_list_length',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'letters-0001',
                'result_count' => 112998,
                'notes' => 'maillage interno real desde el hub /palabras',
            ],
        ]);

        [$exitCode, $stdout] = $run('apply_seo_batch.php', [$goodBatch]);
        Assert::same(0, $exitCode, "lot valide refuse a tort : {$stdout}");
        Assert::true(str_contains($stdout, '3 ligne(s)'));

        // --- R5 : resultat vide jamais indexable. Route_path bien formee pour sa famille. ---
        $emptyResultBatch = $tmpDir . '/empty_result_batch.php';
        $writeBatch($emptyResultBatch, [
            [
                'route_path' => '/palabras/8-letras',
                'family' => 'word_list_length',
                'robots' => 'index,follow',
                'result_count' => 0,
                'notes' => 'ne doit jamais passer',
            ],
        ], 'bad-r5');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$emptyResultBatch]);
        Assert::true($exitCode !== 0, 'R5 aurait du refuser ce lot');
        Assert::true(str_contains($stderr, 'R5'));

        // --- R4 : famille combinatoire infinie jamais index,follow ni sitemap. ---
        $infiniteFamilyBatch = $tmpDir . '/infinite_family_batch.php';
        $writeBatch($infiniteFamilyBatch, [
            [
                'route_path' => '/palabras/contenant/che',
                'family' => 'word_list_contenant',
                'robots' => 'index,follow',
                'notes' => 'ne doit jamais passer',
            ],
        ], 'bad-r4');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$infiniteFamilyBatch]);
        Assert::true($exitCode !== 0, 'R4 aurait du refuser ce lot');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4b : forme totalement etrangere a la famille declaree (route home invalide). ---
        $wrongShapeBatch = $tmpDir . '/wrong_shape_batch.php';
        $writeBatch($wrongShapeBatch, [
            [
                'route_path' => '/inicio',
                'family' => 'home',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'core-0001',
                'notes' => 'ne doit jamais passer -- forme home invalide',
            ],
        ], 'bad-r4b-wrong-shape');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$wrongShapeBatch]);
        Assert::true($exitCode !== 0, 'R4b aurait du refuser une forme etrangere a la famille home');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4b : forme word_list_length avec un segment francais residuel (rejet explicite
        // de l'ancien schema, meme discipline que ES-004 sur le routeur reel). ---
        $frenchShapeBatch = $tmpDir . '/french_shape_batch.php';
        $writeBatch($frenchShapeBatch, [
            [
                'route_path' => '/mots/9-lettres',
                'family' => 'word_list_length',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'letters-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- ancien segment francais',
            ],
        ], 'bad-r4b-french-shape');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$frenchShapeBatch]);
        Assert::true($exitCode !== 0, 'R4b aurait du refuser un ancien segment francais');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R3 : alias indexable (canonical different de route_path). ---
        $aliasBatch = $tmpDir . '/alias_batch.php';
        $writeBatch($aliasBatch, [
            [
                'route_path' => '/palabra/poser-bis',
                'family' => 'word_admitted',
                'robots' => 'index,follow',
                'canonical_path' => '/palabra/poser',
                'notes' => 'ne doit jamais passer',
            ],
        ], 'bad-r3');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$aliasBatch]);
        Assert::true($exitCode !== 0, 'R3 aurait du refuser ce lot');
        Assert::true(str_contains($stderr, 'R3'));

        // --- R6 : plafond dur sur l'espagnol non admis (repli de test = 3). ---
        $tooManyRows = [];
        for ($i = 0; $i < 4; $i++) {
            $tooManyRows[] = [
                'route_path' => '/palabra/test' . $i,
                'family' => 'word_spanish_not_admitted',
                'robots' => 'index,follow',
                'notes' => 'verifie manuellement',
            ];
        }
        $spanishBatch = $tmpDir . '/spanish_batch.php';
        $writeBatch($spanishBatch, $tooManyRows, 'bad-r6');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$spanishBatch]);
        Assert::true($exitCode !== 0, 'R6 aurait du refuser ce lot (plafond depasse)');
        Assert::true(str_contains($stderr, 'R6'));

        // --- R7 : index,follow sans note de maillage. ---
        $noNotesBatch = $tmpDir . '/no_notes_batch.php';
        $writeBatch($noNotesBatch, [
            [
                'route_path' => '/palabra/sinnota',
                'family' => 'word_admitted',
                'robots' => 'index,follow',
                'notes' => '',
            ],
        ], 'bad-r7');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$noNotesBatch]);
        Assert::true($exitCode !== 0, 'R7 aurait du refuser ce lot');
        Assert::true(str_contains($stderr, 'R7'));

        // --- Verifie qu'aucun des lots refuses n'a laisse de trace (transaction unique). ---
        $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $count = $pdo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
        Assert::same(3, (int) $count, 'seul le lot valide (3 lignes : home "/" + hub "/palabras" + word_list_length) doit rester en base');
        unset($pdo);

        // --- build_sitemaps.php : fragments generes avec le bon prefixe par famille. ---
        [$exitCode, $stdout] = $run('build_sitemaps.php', ['--base-url=https://exemple-test.invalid']);
        Assert::same(0, $exitCode, "build_sitemaps.php aurait du reussir : {$stdout}");
        Assert::true(is_file($publicDir . '/sitemaps/core-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/letters-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemap-index.xml'));

        $coreFragment = file_get_contents($publicDir . '/sitemaps/core-0001.xml');
        Assert::true(str_contains($coreFragment, 'https://exemple-test.invalid/'));
        Assert::true(str_contains($coreFragment, 'https://exemple-test.invalid/palabras<'));

        $lettersFragment = file_get_contents($publicDir . '/sitemaps/letters-0001.xml');
        Assert::true(str_contains($lettersFragment, 'https://exemple-test.invalid/palabras/9-letras'));

        $index = file_get_contents($publicDir . '/sitemap-index.xml');
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/core-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/letters-0001.xml'));

        // --- build_sitemaps.php sans --base-url : refuse plutot que publier un domaine faux. ---
        [$exitCode, , $stderr] = $run('build_sitemaps.php');
        Assert::true($exitCode !== 0, '--base-url devrait etre obligatoire');
        Assert::true(str_contains($stderr, '--base-url'));
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
