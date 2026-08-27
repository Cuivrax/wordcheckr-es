<?php

declare(strict_types=1);

/**
 * Construit storage/seo_fr.sqlite depuis app/Seo/schema.sql (Phase 6, docs/08).
 *
 * Hors ligne uniquement (CLI), jamais appelé au runtime — même séparation que scripts/*.py
 * pour storage/dictionary_fr.sqlite (D-007), mais en PHP plutôt qu'en Python : ce script ne
 * traite aucune des sources linguistiques brutes visées par D-007 (ODS8, ODS9, Kartmaan,
 * hbenbel), il construit une base de curation SEO qui change par nature bien plus souvent
 * (D-002) et qui doit rester dans le même langage que la couche app/Seo/ qui la consomme.
 *
 * Usage :
 *     php scripts/build_seo_registry.php            construit si absent, ne touche pas une
 *                                                     base déjà présente
 *     php scripts/build_seo_registry.php --reset     recrée le schéma à vide, PERTE de tout
 *                                                     lot déjà appliqué -- à utiliser
 *                                                     uniquement en développement ou pour
 *                                                     repartir d'un registre vide assumé
 *
 * Ce script ne pose JAMAIS aucune ligne dans `registry` : le construire ne doit pas, par
 * lui-même, rendre la moindre route indexable (D-005, "aucune indexation par omission" --
 * une base neuve reste équivalente à une base absente pour App\Seo\Registry::resolve()).
 * L'application d'un lot de rollout est un acte séparé et explicite, voir
 * scripts/apply_seo_batch.php.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/build_seo_registry.php ne s'exécute qu'en CLI, hors ligne.\n");
    exit(1);
}

$reset = in_array('--reset', $argv, true);

$root = dirname(__DIR__);
$schemaPath = $root . '/app/Seo/schema.sql';
// SCRABBLE_SEO_DB_PATH : reserve aux tests (tests/Seo/), jamais defini en usage normal --
// permet de pointer ce script vers un fichier temporaire plutot que le registre reel, pour
// verifier son comportement sans jamais toucher storage/seo_fr.sqlite pendant la suite de
// tests (voir tests/Seo/BuildScriptsTest.php).
$dbPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_fr.sqlite';

if (!is_file($schemaPath)) {
    fwrite(STDERR, "schema introuvable : {$schemaPath}\n");
    exit(1);
}

if ($reset && is_file($dbPath)) {
    unlink($dbPath);
    echo "base existante supprimee (--reset) : {$dbPath}\n";
}

$alreadyExists = is_file($dbPath);

if ($alreadyExists && !$reset) {
    echo "base deja presente, aucune action (utiliser --reset pour repartir a vide) : {$dbPath}\n";
    exit(0);
}

$storageDir = dirname($dbPath);

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec('PRAGMA journal_mode = DELETE');

$schema = file_get_contents($schemaPath);

if ($schema === false) {
    fwrite(STDERR, "lecture du schema impossible : {$schemaPath}\n");
    exit(1);
}

$pdo->exec($schema);

$builtAt = gmdate('Y-m-d');
$insertMeta = $pdo->prepare('INSERT INTO build_metadata ("key", "value") VALUES (?, ?)');
$insertMeta->execute(['built_at', $builtAt]);
$insertMeta->execute(['schema_source', 'app/Seo/schema.sql']);
$insertMeta->execute(['registry_row_count', '0']);

$integrity = $pdo->query('PRAGMA integrity_check')->fetch();
$ok = is_array($integrity) && ($integrity['integrity_check'] ?? null) === 'ok';

echo "storage/seo_fr.sqlite construit : {$dbPath}\n";
echo 'integrity_check = ' . ($ok ? 'ok' : 'ECHEC') . "\n";
echo "lignes dans `registry` : 0 (aucun lot applique -- voir scripts/apply_seo_batch.php)\n";

if (!$ok) {
    exit(1);
}
