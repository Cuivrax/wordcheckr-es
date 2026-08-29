<?php

declare(strict_types=1);

/**
 * Applique la famille word_admitted dans storage/seo_es.sqlite PAR VAGUE EXPLICITE (une ou
 * plusieurs longueurs nommées en ligne de commande), jamais la famille entière par défaut.
 *
 * RÉÉCRITURE COMPLÈTE (docs/DECISIONS.md ES-011, correctif du blocage C-2, audit
 * seo-technical-auditor sur la version précédente de ce script) : la version d'origine
 * (phase-es-14) codait en dur robots = 'index,follow' pour les 661 221 lignes de la famille en
 * UN SEUL appel, sans jamais repasser par R1-R7 (juste affirmées en commentaire) et sans aucune
 * notion de vague -- exactement le rollout non maîtrisé que le rôle seo-registry interdit
 * ("never propose indexing an entire word family at once without discussing batch size first").
 * Deux corrections distinctes, toutes deux nécessaires :
 *   1. Règles dures : chaque ligne passe maintenant par scripts/seo_batch_rules.php
 *      (seoValidateBatchRow()), LE MÊME CODE que scripts/apply_seo_batch.php -- en flux (un
 *      curseur PDO, jamais un tableau des 661 221 lignes en mémoire, même raison que la version
 *      précédente : épuisement mémoire CLI).
 *   2. Vague explicite : --lengths=7,9 est OBLIGATOIRE (2 à 15, virgule-séparé). Aucune valeur
 *      par défaut n'ouvre "tout" -- une invocation sans --lengths refuse de s'exécuter. Rien
 *      n'empêche techniquement un futur appel d'énumérer les 14 longueurs explicitement, mais
 *      ce serait un choix ENTIÈREMENT explicite et documenté au moment de l'appel, jamais un
 *      comportement par défaut silencieux.
 *
 * Usage :
 *     php scripts/apply_word_admitted_rollout.php --lengths=7,9
 *     php scripts/apply_word_admitted_rollout.php --lengths=7,9 --reset-family
 *     php scripts/apply_word_admitted_rollout.php --lengths=4
 *     php scripts/apply_word_admitted_rollout.php --lengths=2,3,4,5,6,8,10,11,12,13,14,15 --dry-run
 *
 * --dry-run : parcourt et valide (R1-R7, seoValidateBatchRow()) EXACTEMENT le meme flux que
 *     l'application reelle, ligne par ligne, meme requete SELECT, meme fonction de validation --
 *     mais SANS transaction et SANS aucune ecriture dans storage/seo_es.sqlite (ni INSERT, ni
 *     --reset-family, qui reste refuse en combinaison avec --dry-run). Imprime le meme rapport
 *     par longueur, PLUS le compte total qu'aurait le registre APRES application (lu -- jamais
 *     ecrit) pour permettre une verification arithmetique avant tout engagement. Ajoute
 *     2026-08-29 (aucune vague reelle appliquee par ce correctif) : ni --confirm-full-rollout ni
 *     plafond automatique ne sont ajoutes ici -- le role seo-registry ne construit pas son propre
 *     mecanisme de contournement de la regle "jamais une famille entiere sans discussion de
 *     volume au prealable" (contrainte dure du role), voir docs/DECISIONS.md ES-013.
 *
 * --reset-family : SUPPRIME d'abord TOUTES les lignes existantes de family='word_admitted'
 *     (jamais une autre famille) avant d'appliquer la vague demandée -- utilisé une fois pour
 *     corriger le lot initial non maîtrisé (docs/DECISIONS.md ES-011), sinon réservé à un
 *     redémarrage assumé de cette seule famille.
 *
 * Pas de drapeau --force ici (contrairement à scripts/apply_seo_batch.php) : chaque route_path
 * produit par ce script est ENTIÈREMENT dérivé du dictionnaire (/palabra/{mot admis de la
 * longueur demandée}) -- deux invocations pour la MÊME longueur touchent nécessairement
 * exactement le même ensemble de route_path, jamais une collision avec un contenu étranger.
 * Rejouer une longueur déjà appliquée est donc une mise à jour sûre (INSERT OR REPLACE), pas un
 * écrasement accidentel au sens où scripts/apply_seo_batch.php le redoute (lots arbitraires,
 * route_path saisis à la main, qui PEUVENT se recouper avec un autre batch_id sans rapport).
 *
 * Chaque longueur demandée devient son PROPRE ensemble de lignes 'index,follow' au sein d'un
 * batch_id unique couvrant l'invocation entière (ex. 'word_admitted-lengths-7-9-2026-08-29') --
 * le rapport imprimé donne le compte par longueur, pas seulement le total, pour que le volume de
 * CHAQUE vague reste visible (exigence de rapport quantifié du rôle seo-registry : "Volume of
 * the batch being proposed for rollout").
 *
 * result_count reste NULL pour cette famille (R5 ne s'applique pas : /palabra/{mot} n'a pas de
 * notion de "nombre de résultats", même raisonnement que la version précédente de ce script).
 *
 * DÉLIBÉRÉMENT NE COUVRE PAS word_spanish_not_admitted (86 944 mots) : contrainte de rôle
 * ("never propose indexing these in bulk" / Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED = 50),
 * tenue séparée par choix explicite -- voir docs/DECISIONS.md ES-009/ES-010.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/apply_word_admitted_rollout.php ne s'execute qu'en CLI, hors ligne.\n");
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

$lengthsArg = null;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--lengths=')) {
        $lengthsArg = substr($arg, strlen('--lengths='));
    }
}

if ($lengthsArg === null || trim($lengthsArg) === '') {
    fwrite(STDERR, "--lengths=N,N,... est obligatoire (ex. --lengths=7,9) -- aucune vague par defaut,\n");
    fwrite(STDERR, "voir le docblock de ce fichier et docs/DECISIONS.md ES-011 (plan de vagues).\n");
    exit(1);
}

$lengths = [];

foreach (explode(',', $lengthsArg) as $raw) {
    $raw = trim($raw);

    if (!preg_match('/^\d{1,2}\z/', $raw)) {
        fwrite(STDERR, "longueur invalide dans --lengths : '{$raw}'\n");
        exit(1);
    }

    $length = (int) $raw;

    if ($length < 2 || $length > 15) {
        fwrite(STDERR, "longueur hors bornes (2 a 15) dans --lengths : {$length}\n");
        exit(1);
    }

    $lengths[] = $length;
}

$lengths = array_values(array_unique($lengths));
sort($lengths);

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
$batchId = 'word_admitted-lengths-' . implode('-', $lengths) . '-' . $addedAt;

$notesTemplate = 'Palabra admitida (Lexicon FILE 2017 y/o FISE-2). Alcanzada desde /palabras/{N}-letras '
    . '(vague dediee, voir docs/DECISIONS.md ES-011), la navegacion palabra anterior/siguiente '
    . '(App\\Search\\TermLookup::neighbours(), cadena alfabetica completa sobre TODA la tabla terms, '
    . 'admitidas y no admitidas) y hasta 10 categorias de relaciones internas hacia otras fichas '
    . '/palabra/... (App\\Search\\RelationsFinder, admitidas unicamente).';

if ($resetFamily) {
    $deleted = $seo->prepare('DELETE FROM registry WHERE family = ?');
    $deleted->execute([Family::WORD_ADMITTED]);
    echo "--reset-family : {$deleted->rowCount()} ligne(s) existante(s) de word_admitted supprimee(s)\n";
}

// EXPLAIN QUERY PLAN (verifie manuellement avant ce correctif, storage/dictionary_es.sqlite
// reel, longueur 7) :
//   is_admitted = 1                -> SEARCH terms USING COVERING INDEX
//                                      idx_terms_length_admitted_normalized (length=? AND is_admitted=?)
//                                      11,48 ms (longueur 7, 50 488 lignes), 12,47 ms (longueur 9, 99 716 lignes)
//   is_ods8 = 1 OR is_ods9 = 1     -> SEARCH terms USING INDEX idx_terms_length_normalized (length=?)
//                                      puis filtre non indexe ligne a ligne -- 457,21 ms (longueur 7)
// is_admitted (colonne precalculee, schema.sql) verifiee STRICTEMENT equivalente a
// (is_ods8 = 1 OR is_ods9 = 1) sur storage/dictionary_es.sqlite reel (0 ligne de divergence dans
// les deux sens) avant de faire ce changement -- jamais suppose. ORDER BY normalized reste
// couvert par le meme index (troisieme colonne).
$selectByLength = $dict->prepare(
    'SELECT normalized FROM terms WHERE length = ? AND is_admitted = 1 ORDER BY normalized'
);

// Continuite de numerotation des fragments : reprend apres le plus grand index deja utilise par
// la famille word_admitted (permet d'appliquer des vagues successives dans le temps sans
// collision de nom de fichier sitemap) -- sauf --reset-family, ou la numerotation repart de 1.
$fragmentIndex = 1;

if (!$resetFamily) {
    $maxFragment = $seo->query(
        "SELECT sitemap_fragment FROM registry WHERE family = 'word_admitted' "
        . "ORDER BY sitemap_fragment DESC LIMIT 1"
    )->fetch();

    if ($maxFragment !== false && preg_match('/^words-(\d+)\z/', (string) $maxFragment['sitemap_fragment'], $m) === 1) {
        $fragmentIndex = ((int) $m[1]) + 1;
    }
}

$insert = $dryRun ? null : $seo->prepare(
    'INSERT OR REPLACE INTO registry '
    . '(route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
    . 'VALUES (?, ?, "index,follow", ?, ?, ?, NULL, ?, ?)'
);

$totalApplied = 0;
$perLengthCounts = [];
$countInFragment = 0;
$seenPaths = [];
$spanishNotAdmittedIndexCount = 0; // sans objet ici (famille toujours word_admitted), fourni pour l'API partagee.

if ($dryRun) {
    echo "--dry-run : aucune ecriture, storage/seo_es.sqlite lu uniquement (transaction jamais ouverte).\n";
} else {
    $seo->beginTransaction();
}

foreach ($lengths as $length) {
    $selectByLength->execute([$length]);

    $lengthCount = 0;

    foreach ($selectByLength as $row) {
        $slug = mb_strtolower($row['normalized'], 'UTF-8');
        $routePath = '/palabra/' . $slug;

        $candidate = [
            'route_path' => $routePath,
            'family' => Family::WORD_ADMITTED,
            'robots' => 'index,follow',
            'canonical_path' => $routePath,
            'sitemap_fragment' => null, // assigne juste apres, une fois le numero de fragment connu
            'result_count' => null,
            'notes' => str_replace('{N}', (string) $length, $notesTemplate),
        ];

        $label = "palabra '{$row['normalized']}' (longitud {$length})";

        [$error, $normalizedRow] = seoValidateBatchRow($candidate, $label, $seenPaths, $spanishNotAdmittedIndexCount);

        if ($error !== null) {
            if (!$dryRun) {
                $seo->rollBack();
            }
            fwrite(STDERR, "vague refusee (R1-R7) : {$error}\n");
            exit(1);
        }

        if ($countInFragment >= FRAGMENT_SIZE) {
            $fragmentIndex++;
            $countInFragment = 0;
        }
        $countInFragment++;

        $fragment = sprintf('words-%04d', $fragmentIndex);

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

        $lengthCount++;
        $totalApplied++;
    }

    $perLengthCounts[$length] = $lengthCount;
    echo '  longitud ' . $length . ' : ' . $lengthCount . ' palabra(s) admitida(s) '
        . ($dryRun ? 'validada(s) (R1-R7, non appliquee)' : 'aplicada(s)') . "\n";
}

if (!$dryRun) {
    $seo->commit();
}

echo ($dryRun ? "[DRY-RUN] vague '{$batchId}' validee" : "vague '{$batchId}' aplicada") . " : {$totalApplied} linea(s) en total ("
    . count($lengths) . ' longitud(es) : ' . implode(', ', $lengths) . ")\n";

$totalCount = (int) $seo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
$indexCount = (int) $seo->query("SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow'")->fetch()['c'];
$wordAdmittedCount = (int) $seo->query(
    "SELECT COUNT(*) c FROM registry WHERE family = 'word_admitted'"
)->fetch()['c'];

if ($dryRun) {
    // Etat REEL actuel (jamais ecrit par ce run) + projection de ce que l'application produirait --
    // les deux sont imprimes separement pour qu'aucune ambiguite ne subsiste sur ce qui a
    // effectivement ete ecrit (rien) contre ce qui serait ecrit si --dry-run etait retire.
    echo "registre ACTUEL (inchange par ce dry-run) : {$totalCount} lignes au total, {$indexCount} en 'index,follow', "
        . "{$wordAdmittedCount} dans la famille word_admitted\n";
    echo 'registre PROJETE si cette vague etait appliquee (non ecrit) : ' . ($totalCount + $totalApplied)
        . ' lignes au total, ' . ($indexCount + $totalApplied) . " en 'index,follow', "
        . ($wordAdmittedCount + $totalApplied) . " dans la famille word_admitted\n";
} else {
    echo "registre apres application : {$totalCount} lignes au total, {$indexCount} en 'index,follow', "
        . "{$wordAdmittedCount} dans la famille word_admitted\n";
}
