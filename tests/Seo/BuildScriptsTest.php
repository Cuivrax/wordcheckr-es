<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/build_seo_registry.php, scripts/apply_seo_batch.php et scripts/build_sitemaps.php
 * (Phase 6, docs/08) -- boite noire, un vrai sous-processus PHP par appel, JAMAIS contre les
 * vrais storage/seo_fr.sqlite / public/ du depot : SCRABBLE_SEO_DB_PATH et
 * SCRABBLE_PUBLIC_DIR redirigent les trois scripts vers un dossier temporaire propre a ce
 * test, supprime a la fin (meme discipline que tests/Seo/RegistryTest.php).
 *
 * Verifie que les regles dures documentees dans scripts/apply_seo_batch.php sont appliquees
 * par l'OUTIL -- un lot qui les viole doit echouer a l'execution (exit code != 0), pas
 * seulement etre deconseille en commentaire.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $tmpDir = sys_get_temp_dir() . '/scrabble_seo_scripts_test_' . bin2hex(random_bytes(4));
    mkdir($tmpDir);
    mkdir($tmpDir . '/public');

    $dbPath = $tmpDir . '/seo_fr.sqlite';
    $publicDir = $tmpDir . '/public';

    $run = static function (string $script, array $args = []) use ($root, $dbPath, $publicDir): array {
        $cmd = array_merge([PHP_BINARY, $root . '/scripts/' . $script], $args);

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = array_merge(
            getenv() === false ? [] : getenv(),
            [
                'SCRABBLE_SEO_DB_PATH' => $dbPath,
                'SCRABBLE_PUBLIC_DIR' => $publicDir,
                // Plafond de production (D-017) volontairement trop grand pour re-generer
                // des centaines de milliers de lignes dans un test -- repli borne a 50 pour
                // exercer reellement le refus R6 (voir plus bas).
                'SCRABBLE_SEO_MAX_FRENCH_NOT_ADMITTED' => '50',
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
        $export = var_export(['batch_id' => $batchId, 'added_at' => '2026-08-04', 'rows' => $rows], true);
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

        // --- apply_seo_batch.php : lot valide accepte, y compris word_list_position
        // (App\Seo\Family::WORD_LIST_POSITION, D-023, ajoutee hors de NEVER_SITEMAP le
        // 2026-08-10 -- analyse d'ouverture position/commencant+terminant avec longueur),
        // word_list_avec_single_letter (App\Seo\Family::WORD_LIST_AVEC_SINGLE_LETTER, palier 1
        // de l'ouverture en entonnoir de "avec", demande produit du 2026-08-17),
        // word_list_avec_two_letters (App\Seo\Family::WORD_LIST_AVEC_TWO_LETTERS, palier 2,
        // meme demande produit), word_list_avec_three_letters (App\Seo\Family::
        // WORD_LIST_AVEC_THREE_LETTERS, palier 3, demande produit du 2026-08-18),
        // word_list_combined_with_letter (App\Seo\Family::WORD_LIST_COMBINED_WITH_LETTER, axe 2,
        // D-033/R4c -- lettre "avec" DIFFERENTE du debut ET de la fin, correctif 2026-08-19) et
        // word_list_commencant_with_letter (App\Seo\Family::WORD_LIST_COMMENCANT_WITH_LETTER,
        // dernier des quatre axes commencant/terminant/avec, meme demande produit, R4d --
        // correctif 2026-08-19). ---
        $goodBatch = $tmpDir . '/good_batch.php';
        $writeBatch($goodBatch, [
            [
                'route_path' => '/mot/poser',
                'family' => 'word_admitted',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'words-0001',
                'notes' => 'mot frequent, maillage riche',
            ],
            [
                'route_path' => '/mots/9-lettres/position/3/a',
                'family' => 'word_list_position',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'position-0001',
                'result_count' => 7992,
                'notes' => 'maillage interne reel depuis /mots/9-lettres/avec/a (D-023bis, App\\Search\\PositionLinksBuilder), elle-meme atteinte depuis /mots/9-lettres (deja indexee, D-017).',
            ],
            [
                'route_path' => '/mots/9-lettres/avec/a',
                'family' => 'word_list_avec_single_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'avec-single-0001',
                'result_count' => 7992,
                'notes' => 'maillage interne reel depuis /mots/9-lettres (deja indexee, Family::WORD_LIST_LENGTH, D-017) via App\\Search\\LengthLinksBuilder::build()->byWith. Palier 1 de Family::WORD_LIST_AVEC_SINGLE_LETTER.',
            ],
            [
                'route_path' => '/mots/9-lettres/avec/a/b',
                'family' => 'word_list_avec_two_letters',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'avec-pair-0001',
                'result_count' => 123,
                'notes' => 'maillage interne reel depuis /mots/9-lettres/avec/a ET /mots/9-lettres/avec/b (palier 1, deja indexe) via App\\Search\\AvecTwoLettersLinksBuilder. Palier 2 de Family::WORD_LIST_AVEC_TWO_LETTERS.',
            ],
            [
                'route_path' => '/mots/9-lettres/avec/a/b/c',
                'family' => 'word_list_avec_three_letters',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'avec-triple-0001',
                'result_count' => 12,
                'notes' => 'maillage interne reel depuis /mots/9-lettres/avec/a/b ET /mots/9-lettres/avec/a/c ET /mots/9-lettres/avec/b/c (palier 2, deja indexe) via App\\Search\\AvecThreeLettersLinksBuilder. Palier 3 de Family::WORD_LIST_AVEC_THREE_LETTERS.',
            ],
            [
                'route_path' => '/mots/commencant/a/terminant/e/avec/b',
                'family' => 'word_list_combined_with_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'combined-with-0001',
                'result_count' => 4521,
                'notes' => 'maillage interne reel depuis /mots/commencant/a/terminant/e (deja indexee, Family::WORD_LIST_COMBINED, D-024/D-025) via App\\Search\\StartEndWithLinksBuilder. Nouvelle famille Family::WORD_LIST_COMBINED_WITH_LETTER, axe 2 des quatre axes commencant/terminant/avec. Lettre avec (b) distincte du debut (a) et de la fin (e), non degeneree (D-032), non doublon de contenu (C-1).',
            ],
            [
                'route_path' => '/mots/commencant/a/avec/b',
                'family' => 'word_list_commencant_with_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'commencant-avec-0001',
                'result_count' => 7695,
                'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder. Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER, dernier des quatre axes commencant/terminant/avec.',
            ],
        ]);

        [$exitCode, $stdout] = $run('apply_seo_batch.php', [$goodBatch]);
        Assert::same(0, $exitCode, "lot valide refuse a tort : {$stdout}");
        Assert::true(str_contains($stdout, "7 ligne(s)"));

        // --- R5 : resultat vide jamais indexable. Route_path bien formee pour sa famille (voir
        // R4b, durci le 2026-08-18 -- une forme mal accordee a sa famille declaree serait
        // desormais refusee AVANT meme d'atteindre le controle R5, ce n'est pas ce que ce test
        // verifie). ---
        $emptyResultBatch = $tmpDir . '/empty_result_batch.php';
        $writeBatch($emptyResultBatch, [
            [
                'route_path' => '/mots/commencant/zz',
                'family' => 'word_list_commencant',
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
                'route_path' => '/mots/contenant/che',
                'family' => 'word_list_contenant',
                'robots' => 'index,follow',
                'notes' => 'ne doit jamais passer',
            ],
        ], 'bad-r4');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$infiniteFamilyBatch]);
        Assert::true($exitCode !== 0, 'R4 aurait du refuser ce lot');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4b (durci, D-030 constat I-4) : forme de route_path incoherente avec la famille
        // declaree -- lettres "avec" du palier 2 PAS triees alphabetiquement (X > Y). Un tel
        // route_path ne serait jamais la forme reellement servie en 200 (redirection 301 vers
        // la forme canonique triee), donc jamais une ligne 'index,follow' valide. ---
        $unsortedAvecPairBatch = $tmpDir . '/unsorted_avec_pair_batch.php';
        $writeBatch($unsortedAvecPairBatch, [
            [
                'route_path' => '/mots/9-lettres/avec/b/a',
                'family' => 'word_list_avec_two_letters',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'avec-pair-0001',
                'result_count' => 123,
                'notes' => 'ne doit jamais passer -- lettres non triees',
            ],
        ], 'bad-r4b-unsorted');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$unsortedAvecPairBatch]);
        Assert::true($exitCode !== 0, 'R4b aurait du refuser des lettres avec non triees');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4b : forme totalement etrangere a la famille declaree (une route "position" pour
        // une famille "avec"). ---
        $wrongShapeBatch = $tmpDir . '/wrong_shape_batch.php';
        $writeBatch($wrongShapeBatch, [
            [
                'route_path' => '/mots/9-lettres/position/3/a',
                'family' => 'word_list_avec_single_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'avec-single-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- forme position sous une famille avec',
            ],
        ], 'bad-r4b-wrong-shape');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$wrongShapeBatch]);
        Assert::true($exitCode !== 0, 'R4b aurait du refuser une forme etrangere a la famille');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4b : palier 3 avec quatre lettres (forme du palier 3 exige EXACTEMENT trois). ---
        $tooManyLettersBatch = $tmpDir . '/too_many_letters_batch.php';
        $writeBatch($tooManyLettersBatch, [
            [
                'route_path' => '/mots/9-lettres/avec/a/b/c/d',
                'family' => 'word_list_avec_three_letters',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'avec-triple-0001',
                'result_count' => 5,
                'notes' => 'ne doit jamais passer -- quatre lettres sous le palier 3',
            ],
        ], 'bad-r4b-four-letters');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$tooManyLettersBatch]);
        Assert::true($exitCode !== 0, 'R4b aurait du refuser quatre lettres sous le palier 3');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4d : forme etrangere a word_list_commencant_with_letter -- un terminant present
        // (forme de word_list_combined_with_letter, R4c) ne doit jamais passer sous cette
        // famille-ci (deux segments de lettre attendus, pas trois, aucun terminant). ---
        $wrongShapeCommencantAvecBatch = $tmpDir . '/wrong_shape_commencant_avec_batch.php';
        $writeBatch($wrongShapeCommencantAvecBatch, [
            [
                'route_path' => '/mots/commencant/a/terminant/e/avec/b',
                'family' => 'word_list_commencant_with_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'commencant-avec-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- terminant present sous word_list_commencant_with_letter',
            ],
        ], 'bad-r4d-wrong-shape');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$wrongShapeCommencantAvecBatch]);
        Assert::true($exitCode !== 0, 'R4d aurait du refuser un terminant sous word_list_commencant_with_letter');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4c (durci, 2026-08-19, audit seo-technical-auditor consolide, constat I-1) :
        // lettre "avec" degeneree, EGALE au debut -- une telle forme collapse en 301 vers la
        // page parente /mots/commencant/{X}/terminant/{Y} elle-meme (D-032), jamais servie en
        // 200, donc jamais une ligne 'index,follow' valide. ---
        $degenerateAvecEqualsStartBatch = $tmpDir . '/degenerate_avec_equals_start_batch.php';
        $writeBatch($degenerateAvecEqualsStartBatch, [
            [
                'route_path' => '/mots/commencant/a/terminant/e/avec/a',
                'family' => 'word_list_combined_with_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'combined-with-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- lettre avec egale le debut (D-032)',
            ],
        ], 'bad-r4c-avec-equals-start');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$degenerateAvecEqualsStartBatch]);
        Assert::true($exitCode !== 0, 'R4c aurait du refuser une lettre avec egale au debut');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4c : meme controle, lettre "avec" EGALE a la fin cette fois. ---
        $degenerateAvecEqualsEndBatch = $tmpDir . '/degenerate_avec_equals_end_batch.php';
        $writeBatch($degenerateAvecEqualsEndBatch, [
            [
                'route_path' => '/mots/commencant/a/terminant/e/avec/e',
                'family' => 'word_list_combined_with_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'combined-with-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- lettre avec egale la fin (D-032)',
            ],
        ], 'bad-r4c-avec-equals-end');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$degenerateAvecEqualsEndBatch]);
        Assert::true($exitCode !== 0, 'R4c aurait du refuser une lettre avec egale a la fin');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4d (durci, 2026-08-19, meme passe et meme raison que R4c ci-dessus) : lettre
        // "avec" degeneree, EGALE au prefixe -- collapse elle aussi en 301 vers la page parente
        // /mots/commencant/{X} elle-meme (D-032). ---
        $degenerateAvecEqualsPrefixBatch = $tmpDir . '/degenerate_avec_equals_prefix_batch.php';
        $writeBatch($degenerateAvecEqualsPrefixBatch, [
            [
                'route_path' => '/mots/commencant/a/avec/a',
                'family' => 'word_list_commencant_with_letter',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'commencant-avec-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- lettre avec egale le prefixe (D-032)',
            ],
        ], 'bad-r4d-avec-equals-prefix');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$degenerateAvecEqualsPrefixBatch]);
        Assert::true($exitCode !== 0, 'R4d aurait du refuser une lettre avec egale au prefixe');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4e (durci, 2026-08-22, 5e audit consolide, constat I-4) : position/1 (premiere
        // lettre) degeneree -- App\Search\WordListFilters::fromPath() (D-023) collapse
        // silencieusement TOUTE position P=1 vers "commencant", quelle que soit la longueur --
        // une telle forme ne repond donc jamais 200, seulement une redirection 301, exactement
        // le meme defaut deja corrige cote "avec" par R4c/R4d (I-1, D-037). ---
        $degeneratePositionFirstLetterBatch = $tmpDir . '/degenerate_position_first_letter_batch.php';
        $writeBatch($degeneratePositionFirstLetterBatch, [
            [
                'route_path' => '/mots/9-lettres/position/1/a',
                'family' => 'word_list_position',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'position-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- position 1 (premiere lettre), collapse vers commencant (D-023)',
            ],
        ], 'bad-r4e-position-first-letter');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$degeneratePositionFirstLetterBatch]);
        Assert::true($exitCode !== 0, 'R4e aurait du refuser position/1 (premiere lettre)');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R4e : meme controle, position = N (derniere lettre) cette fois -- collapse vers
        // "terminant" (D-023). Le route_path porte volontairement une longueur a deux chiffres
        // (15) pour verifier que la backreference PCRE sur le groupe N compare bien la VALEUR
        // entiere de la longueur, pas seulement son premier chiffre. ---
        $degeneratePositionLastLetterBatch = $tmpDir . '/degenerate_position_last_letter_batch.php';
        $writeBatch($degeneratePositionLastLetterBatch, [
            [
                'route_path' => '/mots/15-lettres/position/15/a',
                'family' => 'word_list_position',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'position-0001',
                'result_count' => 10,
                'notes' => 'ne doit jamais passer -- position = N (derniere lettre), collapse vers terminant (D-023)',
            ],
        ], 'bad-r4e-position-last-letter');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$degeneratePositionLastLetterBatch]);
        Assert::true($exitCode !== 0, 'R4e aurait du refuser position/N (derniere lettre)');
        Assert::true(str_contains($stderr, 'R4'));

        // --- R3 : alias indexable (canonical different de route_path). ---
        $aliasBatch = $tmpDir . '/alias_batch.php';
        $writeBatch($aliasBatch, [
            [
                'route_path' => '/mot/poser-bis',
                'family' => 'word_admitted',
                'robots' => 'index,follow',
                'canonical_path' => '/mot/poser',
                'notes' => 'ne doit jamais passer',
            ],
        ], 'bad-r3');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$aliasBatch]);
        Assert::true($exitCode !== 0, 'R3 aurait du refuser ce lot');
        Assert::true(str_contains($stderr, 'R3'));

        // --- R6 : plafond dur sur le francais non admis. ---
        $tooManyRows = [];
        for ($i = 0; $i < 51; $i++) {
            $tooManyRows[] = [
                'route_path' => '/mot/test' . $i,
                'family' => 'word_french_not_admitted',
                'robots' => 'index,follow',
                'notes' => 'verifie manuellement',
            ];
        }
        $frenchBatch = $tmpDir . '/french_batch.php';
        $writeBatch($frenchBatch, $tooManyRows, 'bad-r6');

        [$exitCode, , $stderr] = $run('apply_seo_batch.php', [$frenchBatch]);
        Assert::true($exitCode !== 0, 'R6 aurait du refuser ce lot (plafond depasse)');
        Assert::true(str_contains($stderr, 'R6'));

        // --- R7 : index,follow sans note de maillage. ---
        $noNotesBatch = $tmpDir . '/no_notes_batch.php';
        $writeBatch($noNotesBatch, [
            [
                'route_path' => '/mot/sansnote',
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
        Assert::same(7, (int) $count, 'seul le lot valide (7 lignes : word_admitted + word_list_position + word_list_avec_single_letter + word_list_avec_two_letters + word_list_avec_three_letters + word_list_combined_with_letter + word_list_commencant_with_letter) doit rester en base');
        unset($pdo);

        // --- build_sitemaps.php : fragments generes avec le bon prefixe par famille, y
        // compris 'position-' pour word_list_position (D-023), 'avec-single-' pour
        // word_list_avec_single_letter (palier 1 de "avec", 2026-08-17), 'avec-pair-' pour
        // word_list_avec_two_letters (palier 2, meme date), 'avec-triple-' pour
        // word_list_avec_three_letters (palier 3, 2026-08-18), 'combined-with-' pour
        // word_list_combined_with_letter (axe 2, D-033) et 'commencant-avec-' pour
        // word_list_commencant_with_letter (dernier des quatre axes, 2026-08-18). ---
        [$exitCode, $stdout] = $run('build_sitemaps.php', ['--base-url=https://exemple-test.invalid']);
        Assert::same(0, $exitCode, "build_sitemaps.php aurait du reussir : {$stdout}");
        Assert::true(is_file($publicDir . '/sitemaps/words-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/position-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/avec-single-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/avec-pair-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/avec-triple-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/combined-with-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemaps/commencant-avec-0001.xml'));
        Assert::true(is_file($publicDir . '/sitemap-index.xml'));

        $fragment = file_get_contents($publicDir . '/sitemaps/words-0001.xml');
        Assert::true(str_contains($fragment, 'https://exemple-test.invalid/mot/poser'));

        $positionFragment = file_get_contents($publicDir . '/sitemaps/position-0001.xml');
        Assert::true(str_contains($positionFragment, 'https://exemple-test.invalid/mots/9-lettres/position/3/a'));

        $avecSingleFragment = file_get_contents($publicDir . '/sitemaps/avec-single-0001.xml');
        Assert::true(str_contains($avecSingleFragment, 'https://exemple-test.invalid/mots/9-lettres/avec/a'));

        $avecPairFragment = file_get_contents($publicDir . '/sitemaps/avec-pair-0001.xml');
        Assert::true(str_contains($avecPairFragment, 'https://exemple-test.invalid/mots/9-lettres/avec/a/b'));

        $avecTripleFragment = file_get_contents($publicDir . '/sitemaps/avec-triple-0001.xml');
        Assert::true(str_contains($avecTripleFragment, 'https://exemple-test.invalid/mots/9-lettres/avec/a/b/c'));

        $combinedWithFragment = file_get_contents($publicDir . '/sitemaps/combined-with-0001.xml');
        Assert::true(str_contains($combinedWithFragment, 'https://exemple-test.invalid/mots/commencant/a/terminant/e/avec/b'));

        $commencantAvecFragment = file_get_contents($publicDir . '/sitemaps/commencant-avec-0001.xml');
        Assert::true(str_contains($commencantAvecFragment, 'https://exemple-test.invalid/mots/commencant/a/avec/b'));

        $index = file_get_contents($publicDir . '/sitemap-index.xml');
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/words-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/position-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/avec-single-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/avec-pair-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/avec-triple-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/combined-with-0001.xml'));
        Assert::true(str_contains($index, 'https://exemple-test.invalid/sitemaps/commencant-avec-0001.xml'));

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
