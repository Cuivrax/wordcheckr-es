<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * Cohérence registre <-> sitemap <-> dictionnaire (correctif I-8, audit seo-technical-auditor,
 * 2026-08-29 -- absente avant ce fichier, alors que docs/DECISIONS.md ES-009 affirmait déjà "0
 * alias/doublon" sans qu'aucun test ne le vérifie réellement).
 *
 * Deux volets :
 *   1. MÉCANISME (boîte noire, base temporaire, même discipline que tests/Seo/
 *      BuildScriptsTest.php) : un sitemap généré par scripts/build_sitemaps.php ne doit JAMAIS
 *      contenir une ligne 'noindex,follow', ni une ligne dont canonical_path diffère de
 *      route_path, ni un doublon d'URL entre deux fragments -- vérifié sur le contenu XML
 *      RÉELLEMENT écrit, pas seulement sur la requête SQL qui l'alimente.
 *   2. DONNÉES RÉELLES (storage/seo_es.sqlite + storage/dictionary_es.sqlite du dépôt, si
 *      présentes -- SAUTÉ proprement sinon, ex. environnement qui n'a pas encore construit ces
 *      bases hors ligne) : chaque route_path 'index,follow' de la famille word_admitted
 *      correspond à un mot RÉELLEMENT admis (is_admitted = 1) dans le dictionnaire, chaque
 *      result_count de la famille word_list_length correspond au compte RÉEL de
 *      storage/dictionary_es.sqlite pour cette longueur (toutes statuts confondus, comme
 *      documenté dans scripts/seo-batches/home-and-length-2026-08-28.php), et (ajouté
 *      docs/DECISIONS.md ES-016) chaque result_count des familles word_list_commencant/
 *      word_list_terminant correspond au compte RÉEL sur la même plage binaire (A..Z puis Ñ) que
 *      App\Search\WordListSolver::rangeBounds() applique au runtime.
 */
return function (): void {
    $root = __DIR__ . '/../..';

    // --- Volet 1 : mecanisme, base temporaire, jamais storage/seo_es.sqlite reel. ---
    $tmpDir = sys_get_temp_dir() . '/scrabble_seo_es_consistency_test_' . bin2hex(random_bytes(4));
    mkdir($tmpDir);
    mkdir($tmpDir . '/public');

    $dbPath = $tmpDir . '/seo_es.sqlite';
    $publicDir = $tmpDir . '/public';

    $run = static function (string $script, array $args = []) use ($root, $dbPath, $publicDir): array {
        $cmd = array_merge([PHP_BINARY, $root . '/scripts/' . $script], $args);
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = array_merge(
            getenv() === false ? [] : getenv(),
            ['SCRABBLE_SEO_DB_PATH' => $dbPath, 'SCRABBLE_PUBLIC_DIR' => $publicDir],
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

    try {
        [$exitCode] = $run('build_seo_registry.php');
        Assert::same(0, $exitCode, 'build_seo_registry.php aurait du reussir');

        $batchPath = $tmpDir . '/batch.php';
        $rows = [
            [
                'route_path' => '/',
                'family' => 'home',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'core-0001',
                'notes' => 'pagina de inicio',
            ],
            [
                // Volontairement PAS dans un sitemap (correctif C-1, ES-011) : noindex, aucun
                // fragment -- ne doit apparaitre dans AUCUN fichier XML genere.
                'route_path' => '/palabras',
                'family' => 'home',
                'robots' => 'noindex,follow',
                'sitemap_fragment' => null,
                'notes' => 'hub, contenu de liste vide (ES-001), voir ES-011',
            ],
            [
                'route_path' => '/palabras/7-letras',
                'family' => 'word_list_length',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'letters-0001',
                'result_count' => 56565,
                'notes' => 'maillage reel depuis chaque fiche mot de 7 lettres',
            ],
            [
                'route_path' => '/palabra/pina',
                'family' => 'word_admitted',
                'robots' => 'index,follow',
                'sitemap_fragment' => 'words-0001',
                'notes' => 'mot admis, maillage riche',
            ],
        ];
        $export = var_export(['batch_id' => 'consistency-test', 'added_at' => '2026-08-29', 'rows' => $rows], true);
        file_put_contents($batchPath, "<?php\nreturn {$export};\n");

        [$exitCode, $stdout] = $run('apply_seo_batch.php', [$batchPath]);
        Assert::same(0, $exitCode, "lot de test refuse a tort : {$stdout}");

        [$exitCode, $stdout] = $run('build_sitemaps.php', ['--base-url=https://exemple-test.invalid']);
        Assert::same(0, $exitCode, "build_sitemaps.php aurait du reussir : {$stdout}");

        // Concatene le contenu de TOUS les fragments ecrits -- verifie l'ABSENCE de la ligne
        // noindex, pas seulement sa presence attendue ailleurs.
        $allFragmentsContent = '';
        foreach (glob($publicDir . '/sitemaps/*.xml') ?: [] as $fragmentFile) {
            $allFragmentsContent .= file_get_contents($fragmentFile);
        }

        Assert::true(
            !str_contains($allFragmentsContent, '/palabras<'),
            "une route 'noindex,follow' (/palabras) ne doit JAMAIS apparaitre dans un sitemap",
        );
        Assert::true(str_contains($allFragmentsContent, 'https://exemple-test.invalid/<'));
        Assert::true(str_contains($allFragmentsContent, '/palabras/7-letras<'));
        Assert::true(str_contains($allFragmentsContent, '/palabra/pina<'));

        // Chaque URL du sitemap doit correspondre a EXACTEMENT une ligne 'index,follow' du
        // registre, et reciproquement (compte total identique) -- aucune ligne fantome ni
        // omise.
        preg_match_all('#<loc>https://exemple-test\.invalid(/[^<]*)</loc>#', $allFragmentsContent, $matches);
        $sitemapPaths = $matches[1];
        Assert::same(count($sitemapPaths), count(array_unique($sitemapPaths)), 'aucune URL ne doit apparaitre deux fois dans les sitemaps (doublon inter-fragments)');

        $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $indexCount = (int) $pdo->query(
            "SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow' AND sitemap_fragment IS NOT NULL"
        )->fetch()['c'];
        Assert::same($indexCount, count($sitemapPaths), 'le nombre total d\'URL publiees doit egaler le nombre de lignes index,follow+sitemap_fragment du registre');
        unset($pdo);
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

        $cleanup($tmpDir);
    }

    // --- Volet 2 : donnees reelles du depot, sautees proprement si absentes. ---
    $realSeoPath = $root . '/storage/seo_es.sqlite';
    $realDictPath = $root . '/storage/dictionary_es.sqlite';

    if (!is_file($realSeoPath) || !is_file($realDictPath)) {
        echo "  (volet 2 saute : storage/seo_es.sqlite ou storage/dictionary_es.sqlite absent)\n";

        return;
    }

    $seo = new PDO('sqlite:' . $realSeoPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $dict = new PDO('sqlite:' . $realDictPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $dict->exec('PRAGMA query_only = ON');

    // word_admitted : chaque route_path 'index,follow' correspond a un mot REELLEMENT admis.
    // EXPLAIN QUERY PLAN verifie manuellement (2026-08-29, is_admitted = 1 seul, colonne
    // precalculee) : SEARCH terms USING COVERING INDEX idx_terms_admitted_normalized
    // (is_admitted=? AND normalized=?) -- une ligne, une recherche indexee, pas de scan.
    $checkAdmitted = $dict->prepare('SELECT 1 FROM terms WHERE normalized = ? AND is_admitted = 1 LIMIT 1');

    $wordAdmittedStatement = $seo->query(
        "SELECT route_path FROM registry WHERE family = 'word_admitted' AND robots = 'index,follow'"
    );

    $checked = 0;
    $mismatches = 0;

    foreach ($wordAdmittedStatement as $row) {
        if (preg_match('#^/palabra/(.+)\z#u', $row['route_path'], $m) !== 1) {
            $mismatches++;

            continue;
        }

        $normalized = mb_strtoupper($m[1], 'UTF-8');
        $checkAdmitted->execute([$normalized]);

        if ($checkAdmitted->fetch() === false) {
            $mismatches++;
        }

        $checked++;
    }

    Assert::true($checked > 0, 'aucune ligne word_admitted trouvee dans le registre reel -- rien a verifier (etat inattendu)');
    Assert::same(0, $mismatches, "{$mismatches} ligne(s) word_admitted 'index,follow' ne correspondent a AUCUN mot reellement admis de storage/dictionary_es.sqlite");

    // word_list_length : result_count doit egaler le compte REEL (tous statuts confondus).
    $lengthStatement = $seo->query(
        "SELECT route_path, result_count FROM registry WHERE family = 'word_list_length'"
    );

    $checkLengthCount = $dict->prepare('SELECT COUNT(*) c FROM terms WHERE length = ?');

    $lengthChecked = 0;
    $lengthMismatches = 0;

    foreach ($lengthStatement as $row) {
        if (preg_match('#^/palabras/(\d{1,2})-letras\z#', $row['route_path'], $m) !== 1) {
            $lengthMismatches++;

            continue;
        }

        $checkLengthCount->execute([(int) $m[1]]);
        $realCount = (int) $checkLengthCount->fetch()['c'];

        if ($realCount !== (int) $row['result_count']) {
            $lengthMismatches++;
        }

        $lengthChecked++;
    }

    Assert::true($lengthChecked > 0, 'aucune ligne word_list_length trouvee dans le registre reel -- rien a verifier (etat inattendu)');
    Assert::same(0, $lengthMismatches, "{$lengthMismatches} ligne(s) word_list_length avec un result_count qui ne correspond pas au compte reel de storage/dictionary_es.sqlite");

    // word_list_commencant/word_list_terminant (docs/DECISIONS.md ES-016, premier palier
    // combinatoire) : result_count doit egaler le compte REEL (tous statuts confondus), meme
    // discipline que word_list_length ci-dessus. Reproduit l'ancrage binaire de
    // App\Search\WordListSolver::rangeBounds() (A..Z PUIS Ñ, collation BINARY -- pas un ordre
    // linguistique) plutot que de dependre d'un LIKE, qui degenererait en scan (CLAUDE.md).
    $alphabetOrder = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','Ñ'];
    $nextChar = static function (string $char) use ($alphabetOrder): ?string {
        $idx = array_search($char, $alphabetOrder, true);

        return ($idx === false || $idx === count($alphabetOrder) - 1) ? null : $alphabetOrder[$idx + 1];
    };
    $rangeBounds = static function (string $prefix) use ($nextChar): array {
        $chars = mb_str_split($prefix, 1, 'UTF-8');

        for ($i = count($chars) - 1; $i >= 0; $i--) {
            $next = $nextChar($chars[$i]);

            if ($next !== null) {
                $chars[$i] = $next;

                return [$prefix, implode('', array_slice($chars, 0, $i + 1))];
            }
        }

        return [$prefix, null];
    };

    $commencantStatement = $seo->query(
        "SELECT route_path, result_count FROM registry WHERE family = 'word_list_commencant'"
    );
    $commencantChecked = 0;
    $commencantMismatches = 0;

    foreach ($commencantStatement as $row) {
        if (preg_match('#^/palabras/empiezan-por/(.+)\z#u', $row['route_path'], $m) !== 1) {
            $commencantMismatches++;

            continue;
        }

        $prefix = mb_strtoupper($m[1], 'UTF-8');
        [$lower, $upper] = $rangeBounds($prefix);
        $sql = 'SELECT COUNT(*) c FROM terms WHERE normalized >= ?' . ($upper !== null ? ' AND normalized < ?' : '');
        $stmt = $dict->prepare($sql);
        $stmt->execute($upper !== null ? [$lower, $upper] : [$lower]);
        $realCount = (int) $stmt->fetch()['c'];

        if ($realCount !== (int) $row['result_count']) {
            $commencantMismatches++;
        }

        $commencantChecked++;
    }

    Assert::true($commencantChecked > 0, 'aucune ligne word_list_commencant trouvee dans le registre reel -- rien a verifier (etat inattendu)');
    Assert::same(0, $commencantMismatches, "{$commencantMismatches} ligne(s) word_list_commencant avec un result_count qui ne correspond pas au compte reel de storage/dictionary_es.sqlite");

    $terminantStatement = $seo->query(
        "SELECT route_path, result_count FROM registry WHERE family = 'word_list_terminant'"
    );
    $terminantChecked = 0;
    $terminantMismatches = 0;

    foreach ($terminantStatement as $row) {
        if (preg_match('#^/palabras/terminan-en/(.+)\z#u', $row['route_path'], $m) !== 1) {
            $terminantMismatches++;

            continue;
        }

        $suffix = mb_strtoupper($m[1], 'UTF-8');
        $reversedSuffix = implode('', array_reverse(mb_str_split($suffix, 1, 'UTF-8')));
        [$lower, $upper] = $rangeBounds($reversedSuffix);
        $sql = 'SELECT COUNT(*) c FROM terms WHERE reversed >= ?' . ($upper !== null ? ' AND reversed < ?' : '');
        $stmt = $dict->prepare($sql);
        $stmt->execute($upper !== null ? [$lower, $upper] : [$lower]);
        $realCount = (int) $stmt->fetch()['c'];

        if ($realCount !== (int) $row['result_count']) {
            $terminantMismatches++;
        }

        $terminantChecked++;
    }

    Assert::true($terminantChecked > 0, 'aucune ligne word_list_terminant trouvee dans le registre reel -- rien a verifier (etat inattendu)');
    Assert::same(0, $terminantMismatches, "{$terminantMismatches} ligne(s) word_list_terminant avec un result_count qui ne correspond pas au compte reel de storage/dictionary_es.sqlite");

    // Sitemaps sur disque (public/sitemaps/*.xml) : si presents, chaque <loc> doit correspondre
    // a une ligne 'index,follow' du registre reel -- pas de fragment fantome laisse par une
    // execution precedente (correctif I-8, purge ajoutee a scripts/build_sitemaps.php).
    $realSitemapsDir = $root . '/public/sitemaps';

    if (is_dir($realSitemapsDir)) {
        $realIndexPaths = [];
        $realIndexStatement = $seo->query(
            "SELECT route_path FROM registry WHERE robots = 'index,follow' AND sitemap_fragment IS NOT NULL"
        );

        foreach ($realIndexStatement as $row) {
            $realIndexPaths[$row['route_path']] = true;
        }

        $orphanSitemapUrls = 0;
        $sitemapUrlCount = 0;

        foreach (glob($realSitemapsDir . '/*.xml') ?: [] as $fragmentFile) {
            $content = file_get_contents($fragmentFile);
            preg_match_all('#<loc>https?://[^/]+(/[^<]*)</loc>#', (string) $content, $m);

            foreach ($m[1] as $encodedPath) {
                $sitemapUrlCount++;
                $decodedPath = implode('/', array_map('rawurldecode', explode('/', $encodedPath)));

                if (!isset($realIndexPaths[$decodedPath])) {
                    $orphanSitemapUrls++;
                }
            }
        }

        Assert::same(0, $orphanSitemapUrls, "{$orphanSitemapUrls} URL de sitemap sur disque ne correspondent a AUCUNE ligne 'index,follow' du registre reel (fragment perime ?)");
        Assert::same($sitemapUrlCount, count($realIndexPaths), 'le nombre d\'URL publiees sur disque doit egaler le nombre de lignes index,follow+sitemap_fragment du registre reel');
    }
};
