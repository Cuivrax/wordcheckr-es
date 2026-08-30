<?php

declare(strict_types=1);

/**
 * Applique la famille word_spanish_not_admitted (86 944 mots, storage/dictionary_es.sqlite,
 * is_spanish=1 AND is_ods8=0 AND is_ods9=0) dans storage/seo_es.sqlite, EN UN SEUL LOT.
 *
 * ES-024 (docs/DECISIONS.md) : decision explicite du proprietaire du produit d'ouvrir cette
 * famille au complet, meme raisonnement que D-017 cote francais -- le site repond a deux
 * questions symetriques ("ce mot est-il admis ?"/"ce mot est-il non admis ?"), un visiteur ne
 * sait jamais laquelle s'applique avant de chercher sur Google. Le blocage d'origine
 * (ES-009/ES-010, "aucune analyse de maillage entrant") est leve : App\Search\TermLookup::
 * neighbours() (navigation mot precedent/suivant) parcourt DEJA la chaine alphabetique
 * complete de terms, admis ET non admis confondus (verifie dans
 * scripts/apply_word_admitted_rollout.php, meme fonction) -- chaque mot non admis recoit donc
 * au moins 2 liens internes reels des sa mise en ligne, avant meme ce lot.
 *
 * MEME DISCIPLINE que scripts/apply_word_admitted_rollout.php (ES-011) : chaque ligne passe
 * par scripts/seo_batch_rules.php (seoValidateBatchRow()), en flux (curseur PDO, jamais un
 * tableau de 86 944 lignes en memoire), plafond Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED
 * (releve 50 -> 100 000 par ES-024) applique explicitement ligne par ligne.
 *
 * PAS de decoupage par longueur ici (contrairement a word_admitted) : ES-024 ouvre la famille
 * entiere en un seul lot, comme D-017 l'a fait pour le francais (838 180 pages en un lot, a
 * l'epoque) -- 86 944 reste un volume raisonnable pour un lot unique attesté par une note
 * partagee (meme simplification assumee que D-017, pas une verification mot par mot).
 *
 * --dry-run : parcourt et valide (R1-R7) le meme flux, sans transaction ni ecriture. Imprime
 *     l'etat actuel et l'etat projete.
 * --reset-family : supprime d'abord toute ligne existante de family='word_spanish_not_admitted'
 *     avant d'appliquer (redemarrage assume de cette seule famille).
 *
 * result_count reste NULL (R5 ne s'applique pas : /palabra/{mot} n'a pas de notion de
 * "nombre de resultats").
 *
 * Usage :
 *     php scripts/apply_word_spanish_not_admitted_rollout.php --dry-run
 *     php scripts/apply_word_spanish_not_admitted_rollout.php
 *     php scripts/apply_word_spanish_not_admitted_rollout.php --reset-family
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/apply_word_spanish_not_admitted_rollout.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

ini_set('memory_limit', '512M');

$root = dirname(__DIR__);
require_once $root . '/app/Seo/Family.php';
require_once __DIR__ . '/seo_batch_rules.php';

use App\Seo\Family;

$args = array_slice($argv, 1);
$resetFamily = in_array('--reset-family', $args, true);
$dryRun = in_array('--dry-run', $args, true);

if ($dryRun && $resetFamily) {
    fwrite(STDERR, "--dry-run et --reset-family sont incompatibles (--reset-family ecrit, --dry-run n'ecrit jamais).\n");
    exit(1);
}

$dictPath = getenv('SCRABBLE_DICTIONARY_DB_PATH') ?: $root . '/storage/dictionary_es.sqlite';
$seoPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_es.sqlite';

if (!is_file($dictPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dictPath}\n");
    exit(1);
}

if (!is_file($seoPath)) {
    fwrite(STDERR, "registre introuvable, lancer d'abord : php scripts/build_seo_registry.php\n");
    exit(1);
}

$dict = new PDO('sqlite:' . $dictPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$dict->exec('PRAGMA query_only = ON');

$seo = new PDO('sqlite:' . $seoPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

const FRAGMENT_SIZE = 40_000;

$addedAt = gmdate('Y-m-d');
$batchId = 'word_spanish_not_admitted-full-' . $addedAt;

$maxBatchSize = (int) (getenv('SCRABBLE_SEO_MAX_SPANISH_NOT_ADMITTED') ?: Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED);

$notes = 'Forma espanola real retenida por las fuentes kaikki.org/eswiktionary (is_spanish=1), '
    . 'no admitida en los lexicos FILE 2017 / FISE-2 (ES-024). Pagina util para responder a la '
    . 'pregunta "esta palabra es valida ?" en su vertiente negativa (D-017 del sitio frances, '
    . 'mismo razonamiento: un visitante no sabe de antemano en cual de los dos casos cae). '
    . 'Alcanzada desde la navegacion palabra anterior/siguiente (App\\Search\\TermLookup::'
    . 'neighbours(), cadena alfabetica completa sobre TODA la tabla terms, admitidas y no '
    . 'admitidas).';

if ($resetFamily) {
    $deleted = $seo->prepare('DELETE FROM registry WHERE family = ?');
    $deleted->execute([Family::WORD_SPANISH_NOT_ADMITTED]);
    echo "--reset-family : {$deleted->rowCount()} ligne(s) existante(s) de word_spanish_not_admitted supprimee(s)\n";
}

// EXPLAIN QUERY PLAN attendu (meme index que apply_word_admitted_rollout.php, colonnes
// is_spanish/is_ods8/is_ods9 toutes couvertes par idx_terms_length_admitted_normalized ou un
// balayage complet de la table -- ici pas de filtre par longueur, balayage complet attendu et
// acceptable : script hors ligne, execute une seule fois pour ce lot).
$select = $dict->prepare(
    'SELECT normalized FROM terms WHERE is_spanish = 1 AND is_ods8 = 0 AND is_ods9 = 0 ORDER BY normalized'
);

$fragmentIndex = 1;

if (!$resetFamily) {
    $maxFragment = $seo->query(
        "SELECT sitemap_fragment FROM registry WHERE family = 'word_spanish_not_admitted' "
        . "ORDER BY sitemap_fragment DESC LIMIT 1"
    )->fetch();

    if ($maxFragment !== false && preg_match('/^invalid-(\d+)\z/', (string) $maxFragment['sitemap_fragment'], $m) === 1) {
        $fragmentIndex = ((int) $m[1]) + 1;
    }
}

$insert = $dryRun ? null : $seo->prepare(
    'INSERT OR REPLACE INTO registry '
    . '(route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
    . 'VALUES (?, ?, "index,follow", ?, ?, ?, NULL, ?, ?)'
);

$totalApplied = 0;
$countInFragment = 0;
$seenPaths = [];
$spanishNotAdmittedIndexCount = 0;

if ($dryRun) {
    echo "--dry-run : aucune ecriture, storage/seo_es.sqlite lu uniquement (transaction jamais ouverte).\n";
} else {
    $seo->beginTransaction();
}

$select->execute();

foreach ($select as $row) {
    $slug = mb_strtolower($row['normalized'], 'UTF-8');
    $routePath = '/palabra/' . $slug;

    $candidate = [
        'route_path' => $routePath,
        'family' => Family::WORD_SPANISH_NOT_ADMITTED,
        'robots' => 'index,follow',
        'canonical_path' => $routePath,
        'sitemap_fragment' => null,
        'result_count' => null,
        'notes' => $notes,
    ];

    $label = "palabra no admitida '{$row['normalized']}'";

    [$error, $normalizedRow] = seoValidateBatchRow($candidate, $label, $seenPaths, $spanishNotAdmittedIndexCount);

    if ($error !== null) {
        if (!$dryRun) {
            $seo->rollBack();
        }
        fwrite(STDERR, "lot refuse (R1-R7) : {$error}\n");
        exit(1);
    }

    if ($spanishNotAdmittedIndexCount > $maxBatchSize) {
        if (!$dryRun) {
            $seo->rollBack();
        }
        fwrite(
            STDERR,
            "lot refuse : plafond Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED depasse "
                . "({$spanishNotAdmittedIndexCount} > {$maxBatchSize})\n",
        );
        exit(1);
    }

    if ($countInFragment >= FRAGMENT_SIZE) {
        $fragmentIndex++;
        $countInFragment = 0;
    }
    $countInFragment++;

    $fragment = sprintf('invalid-%04d', $fragmentIndex);

    if (!$dryRun) {
        $insert->execute([
            $normalizedRow['route_path'],
            $normalizedRow['family'],
            $normalizedRow['canonical_path'],
            $fragment,
            $batchId,
            $normalizedRow['notes'],
            $addedAt,
        ]);
    }

    $totalApplied++;
}

if (!$dryRun) {
    $seo->commit();
}

echo ($dryRun ? "[DRY-RUN] lot '{$batchId}' validee" : "lot '{$batchId}' aplicado") . " : {$totalApplied} linea(s)\n";

$totalCount = (int) $seo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
$indexCount = (int) $seo->query("SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow'")->fetch()['c'];
$familyCount = (int) $seo->query(
    "SELECT COUNT(*) c FROM registry WHERE family = 'word_spanish_not_admitted'"
)->fetch()['c'];

if ($dryRun) {
    echo "registre ACTUAL (sin cambios) : {$totalCount} lineas en total, {$indexCount} en 'index,follow', "
        . "{$familyCount} en word_spanish_not_admitted\n";
    echo 'registre PROYECTADO si se aplicara este lote (no escrito) : ' . ($totalCount + $totalApplied)
        . ' lineas en total, ' . ($indexCount + $totalApplied) . " en 'index,follow', "
        . ($familyCount + $totalApplied) . " en word_spanish_not_admitted\n";
} else {
    echo "registre despues de la aplicacion : {$totalCount} lineas en total, {$indexCount} en 'index,follow', "
        . "{$familyCount} en word_spanish_not_admitted\n";
}
