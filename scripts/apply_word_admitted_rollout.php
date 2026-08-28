<?php

declare(strict_types=1);

/**
 * Applique en une passe la famille word_admitted au complet dans storage/seo_es.sqlite
 * (premier palier de rollout SEO du site espagnol, docs/DECISIONS.md ES-009).
 *
 * Adapté de scripts/apply_full_word_rollout.php du dépôt français cousin (D-017) --
 * N'UTILISE PAS scripts/apply_seo_batch.php (var_export d'un tableau PHP à cette échelle --
 * plus de 660 000 lignes -- épuise la mémoire CLI par défaut, même constat que sur le dépôt
 * français). Insertion directe en flux (curseur PDO, jamais de fetchAll), mêmes règles
 * R1/R3/R4/R5/R7 respectées par construction (données générées ici depuis
 * storage/dictionary_es.sqlite, pas relues d'un fichier externe non fiable) :
 * - route_path = canonical_path partout (jamais d'alias, R3) ;
 * - robots = 'index,follow' uniquement pour des mots réellement admis (is_ods8 = 1 OR
 *   is_ods9 = 1, storage/dictionary_es.sqlite) -- jamais un résultat vide (R5 ne s'applique
 *   pas : cette famille n'a pas de notion de "nombre de résultats", result_count reste NULL,
 *   comme /palabra/{mot} partout ailleurs) ;
 * - note non vide sur chaque ligne (R7), une formulation partagée par famille plutôt qu'une
 *   attestation individuelle par mot -- vérifier individuellement 661 221 mots un par un
 *   n'est pas faisable ; la vérification porte sur la SOURCE (Lexicon FILE 2017/FISE-2, deux
 *   lexiques officiels d'admissibilité déjà importés et filtrés) et le PRINCIPE (chaque page
 *   a un contenu réel -- badge, score, tuiles, réponse directe, jusqu'à 10 catégories de
 *   relations, navigation mot précédent/suivant -- jamais une coquille vide), pas sur chaque
 *   mot un par un.
 *
 * DÉLIBÉRÉMENT NE COUVRE PAS word_spanish_not_admitted (86 944 mots espagnols non retenus aux
 * lexiques d'admissibilité) : contrainte de rôle du garde-fou seo-registry ("never propose
 * indexing an entire word family at once without discussing batch size first" /
 * App\Seo\Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED = 50) -- tenue séparée de word_admitted
 * par choix explicite, voir docs/DECISIONS.md ES-009/ES-010. Si une décision produit future
 * l'ouvre, elle devra passer par un lot dédié, plafonné, avec sa propre justification -- ce
 * script n'inclut PAS de code pour cette famille afin qu'il ne puisse pas être élargi
 * silencieusement par une simple décommentation.
 *
 * Usage : php scripts/apply_word_admitted_rollout.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/apply_word_admitted_rollout.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
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

/**
 * @param string $whereClause SQL, deja bornee (colonnes is_ods8/is_ods9 uniquement)
 */
$applyFamily = static function (
    PDO $dict,
    PDO $seo,
    string $whereClause,
    string $family,
    string $fragmentPrefix,
    string $batchId,
    string $notes,
) use ($addedAt): int {
    $statement = $dict->query("SELECT normalized FROM terms WHERE {$whereClause} ORDER BY normalized");

    $insert = $seo->prepare(
        'INSERT OR REPLACE INTO registry '
        . '(route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
        . 'VALUES (?, ?, "index,follow", ?, ?, ?, NULL, ?, ?)'
    );

    $seo->beginTransaction();

    $count = 0;
    $fragmentIndex = 1;
    $countInFragment = 0;

    foreach ($statement as $row) {
        if ($countInFragment >= FRAGMENT_SIZE) {
            $fragmentIndex++;
            $countInFragment = 0;
        }
        $countInFragment++;
        $count++;

        $slug = mb_strtolower($row['normalized'], 'UTF-8');
        $routePath = "/palabra/{$slug}";
        $fragment = sprintf('%s-%04d', $fragmentPrefix, $fragmentIndex);

        $insert->execute([$routePath, $family, $routePath, $fragment, $batchId, $notes, $addedAt]);
    }

    $seo->commit();

    return $count;
};

echo "application word_admitted...\n";
$admittedCount = $applyFamily(
    $dict,
    $seo,
    'is_ods8 = 1 OR is_ods9 = 1',
    'word_admitted',
    'words',
    'word_admitted-tier1-' . $addedAt,
    'Palabra admitida (Lexicon FILE 2017 y/o FISE-2). Alcanzada desde /palabras/{N}-letras '
        . '(ya indexada, mismo lote), la navegacion palabra anterior/siguiente (App\\Search\\'
        . 'TermLookup::neighbours(), cadena alfabetica completa sobre TODA la tabla terms, '
        . 'admitidas y no admitidas) y hasta 10 categorias de relaciones internas hacia otras '
        . 'fichas /palabra/... (App\\Search\\RelationsFinder, admitidas unicamente).',
);
echo "  {$admittedCount} lignes\n";

$totalCount = $seo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
$indexCount = $seo->query("SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow'")->fetch()['c'];

echo "registre apres application : {$totalCount} lignes au total, {$indexCount} en 'index,follow'\n";
