<?php

declare(strict_types=1);

/**
 * Applique un lot de rollout au registre storage/seo_es.sqlite.
 *
 * Hors ligne uniquement (CLI). Adapté du dépôt français cousin (scripts/apply_seo_batch.php,
 * FR) : mêmes règles dures R1-R7, mêmes garanties de transaction unique. Les règles de FORME
 * (R4b et suivantes sur le dépôt français) ne couvrent ici QUE les familles réellement
 * ouvertes sur ce dépôt à ce stade (Family::HOME, Family::WORD_LIST_LENGTH) — les paliers
 * combinatoires du dépôt français (avec/position/combined...) n'ont pas d'équivalent mesuré
 * ici, voir docs/DECISIONS.md ES-009.
 *
 * Usage :
 *     php scripts/apply_seo_batch.php path/to/batch.php [--force]
 *
 * Pour un lot à l'échelle du dictionnaire (des centaines de milliers de lignes, ex. la
 * famille word_admitted), utiliser scripts/apply_word_and_length_rollout.php plutôt que ce
 * script : un tableau PHP littéral de cette taille épuise la mémoire CLI par défaut (même
 * constat que scripts/apply_full_word_rollout.php sur le dépôt français). Ce script-ci reste
 * l'outil générique pour des lots de taille raisonnable (families combinatoires futures,
 * corrections ciblées).
 *
 * Format d'un fichier de lot (PHP, jamais exécuté au runtime) : retourne un tableau
 *
 *   [
 *       'batch_id' => 'admitted-2026-08-28-pilot',
 *       'added_at' => '2026-08-28',           // optionnel, defaut = date du jour (UTC)
 *       'rows' => [
 *           [
 *               'route_path' => '/palabra/poser',
 *               'family' => App\Seo\Family::WORD_ADMITTED,
 *               'robots' => 'index,follow',
 *               'canonical_path' => '/palabra/poser',   // optionnel, defaut = route_path
 *               'sitemap_fragment' => 'words-0001',     // optionnel, defaut = null
 *               'result_count' => null,                 // optionnel, uniquement pour /palabras/...
 *               'notes' => 'mot tres frequent, maillage riche',
 *           ],
 *           // ...
 *       ],
 *   ]
 *
 * Règles dures appliquées ICI, en plus de la contrainte CHECK du schéma (robots fermé à deux
 * valeurs) -- un lot qui viole l'une d'entre elles est refusé EN BLOC, aucune ligne n'est
 * écrite (transaction unique) :
 *
 *   R1  route_path doit commencer par '/', family doit être une valeur connue
 *       (App\Seo\Family::ALL), robots doit être 'index,follow' ou 'noindex,follow'.
 *   R2  aucun doublon de route_path DANS le lot.
 *   R3  une ligne 'index,follow' dont canonical_path != route_path est refusée -- ce registre
 *       ne sert jamais de mécanisme d'alias indexable (chaque permutation non canonique est
 *       déjà éliminée par une redirection 301 en amont, App\Search\WordListFilters::
 *       canonicalPath() / App\Search\TermLookup::find()->slug -- si canonical_path diverge
 *       ici, c'est que la route N'EST PAS le gagnant canonique, donc jamais 'index,follow').
 *   R4  DEUX controles distincts, tous deux geres ici :
 *       R4a une famille de App\Seo\Family::NEVER_SITEMAP (contenant, avec, sans, motif,
 *           /buscador-de-palabras/{letras} -- combinaisons infinies, docs/05 n'en documente
 *           d'ailleurs aucun fragment de sitemap) ne peut JAMAIS recevoir robots='index,follow'
 *           ni sitemap_fragment non nul, quel que soit le lot.
 *       R4b pour chaque famille COUVERTE par seoBatchRouteShapeError() (scripts/seo_batch_rules.php),
 *           route_path doit correspondre EXACTEMENT à la grammaire de cette famille --
 *           App\Search\WordListFilters::canonicalPath() est la source de vérité de cette
 *           grammaire pour word_list_length. Une ligne dont la forme ne correspond pas à sa
 *           famille déclarée est refusée À L'ÉCRITURE, pas seulement documentée en prose.
 *           Familles couvertes à ce stade : home, word_list_length. Familles NON couvertes,
 *           documentées plutôt qu'oubliées : word_admitted / word_spanish_not_admitted (des
 *           centaines de milliers de lignes potentielles, grammaire du slug dérivée de
 *           App\Search\Normalizer plutôt que de WordListFilters) et toutes les familles
 *           combinatoires non encore mesurées sur ce dépôt (rack déjà bloquée à la racine par
 *           R4a) -- à instruire séparément si un futur lot le justifie, même discipline que le
 *           dépôt français (voir son docblock équivalent).
 *   R5  result_count === 0 avec robots='index,follow' est refusé (page à résultat vide jamais
 *       indexable). result_count === 1 N'EST PAS refusé (docs/05 : jamais sur le seul compteur)
 *       -- seulement compté séparément dans le rapport imprimé par ce script.
 *   R6  une famille de App\Seo\Family::SPANISH_NOT_ADMITTED ne peut pas dépasser
 *       App\Seo\Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED lignes 'index,follow' dans UN SEUL
 *       lot ("Never propose indexing these in bulk"), et chaque ligne de cette famille doit
 *       porter une note non vide (attestation de vérification manuelle -- genuinely Spanish,
 *       absent des lexiques FILE 2017/FISE-2, utile, recherché ou fréquent).
 *   R7  toute ligne 'index,follow' doit porter une note non vide décrivant au moins son
 *       maillage interne prévu -- attestation humaine, PAS une vérification automatique du
 *       graphe de liens.
 *
 * Sans --force, une ligne dont route_path existe déjà en base avec un batch_id DIFFÉRENT est
 * refusée (protège l'historique d'un lot précédent contre un écrasement accidentel par un
 * lot mal ciblé). --force autorise explicitement le remplacement.
 *
 * --prune : ce script ne fait que de l'INSERT OR REPLACE -- un lot régénéré avec MOINS de
 * lignes qu'avant laisse sinon les anciennes lignes orphelines en base, toujours
 * 'index,follow', toujours dans le sitemap. --prune supprime, DANS LA MÊME TRANSACTION que
 * l'application du lot, toute ligne dont batch_id == $batchId mais dont route_path n'apparaît
 * plus dans les lignes du lot en cours d'application -- jamais une ligne d'un AUTRE batch_id.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/apply_seo_batch.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

ini_set('memory_limit', '512M');

require_once dirname(__DIR__) . '/app/Seo/Family.php';
// Regles R1-R7 EXTRAITES ici (correctif C-2, docs/DECISIONS.md ES-011) : seoValidateBatchRow()/
// seoBatchRouteShapeError() sont desormais partagees avec scripts/apply_word_admitted_rollout.php
// -- une seule implementation des regles dures, jamais deux copies susceptibles de diverger.
require_once __DIR__ . '/seo_batch_rules.php';

use App\Seo\Family;

$args = array_slice($argv, 1);
$force = in_array('--force', $args, true);
$prune = in_array('--prune', $args, true);
$args = array_values(array_filter(
    $args,
    static fn (string $a): bool => $a !== '--force' && $a !== '--prune',
));

if (count($args) !== 1) {
    fwrite(STDERR, "usage : php scripts/apply_seo_batch.php path/to/batch.php [--force] [--prune]\n");
    exit(1);
}

$batchPath = $args[0];

if (!is_file($batchPath)) {
    fwrite(STDERR, "fichier de lot introuvable : {$batchPath}\n");
    exit(1);
}

$batch = require $batchPath;

if (!is_array($batch) || !isset($batch['batch_id'], $batch['rows']) || !is_array($batch['rows'])) {
    fwrite(STDERR, "format de lot invalide : attendu ['batch_id' => string, 'rows' => list<array>]\n");
    exit(1);
}

$batchId = (string) $batch['batch_id'];
$addedAt = isset($batch['added_at']) ? (string) $batch['added_at'] : gmdate('Y-m-d');
$rows = $batch['rows'];

if ($rows === []) {
    fwrite(STDERR, "lot vide : aucune ligne a appliquer\n");
    exit(1);
}

// SCRABBLE_SEO_MAX_SPANISH_NOT_ADMITTED : meme reserve aux tests que SCRABBLE_SEO_DB_PATH
// (tests/Seo/BuildScriptsTest.php) -- permet de verifier le mecanisme de refus R6 sans
// generer des dizaines de lignes reelles pour depasser le plafond de production
// (Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED).
$maxSpanishNotAdmitted = getenv('SCRABBLE_SEO_MAX_SPANISH_NOT_ADMITTED') !== false
    ? (int) getenv('SCRABBLE_SEO_MAX_SPANISH_NOT_ADMITTED')
    : Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED;

$errors = [];
$seenPaths = [];
$normalizedRows = [];
$spanishNotAdmittedIndexCount = 0;

foreach ($rows as $i => $row) {
    if (!is_array($row)) {
        $errors[] = "ligne {$i} : pas un tableau";

        continue;
    }

    $routePathForLabel = $row['route_path'] ?? null;
    $label = "ligne {$i} (" . (is_string($routePathForLabel) ? $routePathForLabel : '?') . ')';

    // R1, R2, R3, R4 (a+b), R5, R6 (par ligne), R7 : voir scripts/seo_batch_rules.php,
    // extrait pour etre partage avec scripts/apply_word_admitted_rollout.php (correctif C-2,
    // docs/DECISIONS.md ES-011) -- meme code, jamais deux implementations paralleles.
    [$error, $normalizedRow] = seoValidateBatchRow($row, $label, $seenPaths, $spanishNotAdmittedIndexCount);

    if ($error !== null) {
        $errors[] = $error;

        continue;
    }

    $normalizedRows[] = $normalizedRow;
}

// R6, plafond global du lot.
if ($spanishNotAdmittedIndexCount > $maxSpanishNotAdmitted) {
    $errors[] = sprintf(
        "lot refuse (R6) : %d lignes 'index,follow' en espagnol non admis, plafond %d par lot",
        $spanishNotAdmittedIndexCount,
        $maxSpanishNotAdmitted,
    );
}

if ($errors !== []) {
    fwrite(STDERR, "lot refuse, " . count($errors) . " erreur(s) :\n");

    foreach ($errors as $error) {
        fwrite(STDERR, "  - {$error}\n");
    }

    exit(1);
}

$root = dirname(__DIR__);
// SCRABBLE_SEO_DB_PATH : meme reserve aux tests que dans scripts/build_seo_registry.php.
$dbPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_es.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "registre introuvable, lancer d'abord : php scripts/build_seo_registry.php\n{$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if (!$force) {
    $checkStatement = $pdo->prepare('SELECT batch_id FROM registry WHERE route_path = ?');

    foreach ($normalizedRows as $row) {
        $checkStatement->execute([$row['route_path']]);
        $existing = $checkStatement->fetch();

        if ($existing !== false && $existing['batch_id'] !== $batchId) {
            fwrite(STDERR, sprintf(
                "route_path '%s' existe deja sous le lot '%s' -- utiliser --force pour remplacer\n",
                $row['route_path'],
                $existing['batch_id'] ?? '(aucun)',
            ));
            exit(1);
        }
    }
}

$pdo->beginTransaction();

$prunedCount = 0;

if ($prune) {
    $existingStatement = $pdo->prepare('SELECT route_path FROM registry WHERE batch_id = ?');
    $existingStatement->execute([$batchId]);
    $existingPaths = $existingStatement->fetchAll(PDO::FETCH_COLUMN);

    $keptPaths = array_flip(array_column($normalizedRows, 'route_path'));
    $stalePaths = array_filter($existingPaths, static fn (string $p): bool => !isset($keptPaths[$p]));

    if ($stalePaths !== []) {
        $delete = $pdo->prepare('DELETE FROM registry WHERE batch_id = ? AND route_path = ?');

        foreach ($stalePaths as $stalePath) {
            $delete->execute([$batchId, $stalePath]);
            $prunedCount++;
        }
    }
}

$insert = $pdo->prepare(
    'INSERT OR REPLACE INTO registry '
    . '(route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
    . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

foreach ($normalizedRows as $row) {
    $insert->execute([
        $row['route_path'],
        $row['family'],
        $row['robots'],
        $row['canonical_path'],
        $row['sitemap_fragment'],
        $batchId,
        $row['result_count'],
        $row['notes'],
        $addedAt,
    ]);
}

$pdo->commit();

$totalCount = $pdo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
$indexCount = $pdo->query("SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow'")->fetch()['c'];
$singleResultCount = $pdo->query('SELECT COUNT(*) c FROM registry WHERE result_count = 1')->fetch()['c'];

echo "lot '{$batchId}' applique : " . count($normalizedRows) . " ligne(s)\n";

if ($prune) {
    echo "lignes obsoletes retirees (--prune) : {$prunedCount}\n";
}

echo "registre apres application : {$totalCount} lignes au total, {$indexCount} en 'index,follow'\n";
echo "pages a exactement 1 resultat dans le registre (toutes familles) : {$singleResultCount}\n";
