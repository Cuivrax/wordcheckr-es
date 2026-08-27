<?php

declare(strict_types=1);

use App\Seo\Registry;
use Tests\Support\Assert;

/**
 * App\Seo\Registry : la route de test ne touche jamais storage/seo_fr.sqlite reel -- chaque
 * cas construit sa propre base temporaire depuis app/Seo/schema.sql, effacee a la fin (meme
 * discipline que tests/Database/ConnectionTest.php pour ne jamais laisser de trace).
 *
 * Contrat central verifie ici (D-005, docs/05) : une route absente du registre reste
 * noindex,follow, que ce soit parce que le FICHIER est absent ou parce que la LIGNE est
 * absente -- les deux cas doivent produire exactement le meme SeoMeta.
 */
return function (): void {
    $schemaPath = __DIR__ . '/../../app/Seo/schema.sql';
    Assert::true(is_file($schemaPath), 'schema introuvable : ' . $schemaPath);

    $baseUrl = 'https://exemple-test.invalid';

    // --- Cas 1 : fichier de base absent -- comportement par defaut, jamais une erreur. ---
    $missingPath = sys_get_temp_dir() . '/scrabble_seo_test_missing_' . bin2hex(random_bytes(4)) . '.sqlite';
    Assert::true(!is_file($missingPath));

    $registryMissing = new Registry($missingPath, $baseUrl);
    $metaMissing = $registryMissing->resolve('/mot/poser');

    Assert::same('noindex,follow', $metaMissing->robots);
    Assert::same($baseUrl . '/mot/poser', $metaMissing->canonicalUrl);
    Assert::true(!$metaMissing->inSitemap);
    Assert::true(!is_file($missingPath), 'resolve() ne doit jamais creer de fichier (lecture seule)');

    // --- Cas 2 : base presente, mais aucune ligne pour ce chemin -- meme comportement. ---
    $dbPath = sys_get_temp_dir() . '/scrabble_seo_test_' . bin2hex(random_bytes(4)) . '.sqlite';

    $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $schema = file_get_contents($schemaPath);
    Assert::true($schema !== false);
    $pdo->exec($schema);

    try {
        $registryEmpty = new Registry($dbPath, $baseUrl);
        $metaAbsent = $registryEmpty->resolve('/mot/inconnu');

        Assert::same('noindex,follow', $metaAbsent->robots);
        Assert::same($baseUrl . '/mot/inconnu', $metaAbsent->canonicalUrl);
        Assert::true(!$metaAbsent->inSitemap);

        // --- Cas 3 : ligne explicite index,follow, self-canonique, dans un sitemap. ---
        $pdo->prepare(
            'INSERT INTO registry (route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute(['/mot/poser', 'word_admitted', 'index,follow', '/mot/poser', 'words-0001', 'test-batch', null, 'test', '2026-08-04']);

        $registry = new Registry($dbPath, $baseUrl);
        $meta = $registry->resolve('/mot/poser');

        Assert::same('index,follow', $meta->robots);
        Assert::same($baseUrl . '/mot/poser', $meta->canonicalUrl);
        Assert::true($meta->inSitemap);

        // --- Cas 4 : ligne noindex avec canonical pointant ailleurs (alias documente). ---
        $pdo->prepare(
            'INSERT INTO registry (route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute(['/mots/commencant/CH/page/1', 'word_list_commencant', 'noindex,follow', '/mots/commencant/ch', null, 'test-batch', null, 'alias page 1', '2026-08-04']);

        $aliasMeta = $registry->resolve('/mots/commencant/CH/page/1');
        Assert::same('noindex,follow', $aliasMeta->robots);
        Assert::same($baseUrl . '/mots/commencant/ch', $aliasMeta->canonicalUrl);

        // --- resolveMany() : plusieurs chemins en une seule requete, chemins absents omis. ---
        $many = $registry->resolveMany(['/mot/poser', '/mot/absent']);
        Assert::same(1, count($many));
        Assert::true(isset($many['/mot/poser']));
        Assert::same('index,follow', $many['/mot/poser']->robots);

        Assert::same([], $registry->resolveMany([]));
    } finally {
        // Registry ouvre sa propre connexion PDO vers $dbPath (cachee en interne, distincte de
        // $pdo ci-dessus) : tant qu'un objet Registry reste en portee, Windows garde le
        // descripteur de fichier ouvert et unlink() echoue avec "Resource temporarily
        // unavailable" -- constate en developpement, corrige en liberant explicitement toutes
        // les references avant de supprimer le fichier temporaire.
        unset($pdo, $registryMissing, $registryEmpty, $registry);

        if (is_file($dbPath)) {
            unlink($dbPath);
        }
    }
};
