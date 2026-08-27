<?php

declare(strict_types=1);

/**
 * Applique en une passe les familles word_admitted et word_french_not_admitted au complet
 * dans storage/seo_fr.sqlite (D-017, docs/DECISIONS.md).
 *
 * Ecrit par la session principale, pas par l'agent seo-registry (qui a refuse cette action --
 * garde-fou volontaire de son role, voir son rapport et D-017 pour le contexte complet).
 * Decision explicite du proprietaire du produit : le site repond a deux questions symetriques
 * ("ce mot est-il admis ?" / "ce mot est-il non admis ?"), un visiteur ne sait jamais laquelle
 * s'applique avant de chercher sur Google -- exclure les formes non admises les rend
 * introuvables precisement pour le cas d'usage ou l'utilisateur en a le plus besoin. Applique
 * maintenant sans risque reel : le site n'est pas deploye (Phase 7 non commencee), rien de ce
 * qui est ecrit ici n'est vu par le vrai Google avant une mise en ligne et son propre
 * sequencement par lots.
 *
 * N'utilise PAS scripts/apply_seo_batch.php (var_export d'un tableau PHP a cette echelle --
 * plus de 800 000 lignes -- epuise la memoire CLI par defaut, teste et confirme). Insertion
 * directe en flux (curseur PDO, jamais de fetchAll), memes regles R1/R3/R4/R5/R7 respectees
 * par construction (donnees generees ici, pas relues d'un fichier externe non fiable) :
 * - route_path = canonical_path partout (jamais d'alias, R3) ;
 * - robots = 'index,follow' uniquement pour des mots reels de storage/dictionary_fr.sqlite,
 *   jamais un resultat vide (R5 ne s'applique pas : ces deux familles n'ont pas de notion de
 *   "nombre de resultats", result_count reste NULL, comme /mot/{mot} partout ailleurs) ;
 * - note non vide sur chaque ligne (R7), une formulation partagee par famille plutot qu'une
 *   attestation individuelle par mot -- simplification assumee et documentee dans D-017, pas
 *   cachee : verifier individuellement 838 180 mots n'est pas faisable, la verification porte
 *   sur la source (Kartmaan/hbenbel, D-011/D-014, deja filtree) et le principe (D-017), pas sur
 *   chaque mot un par un.
 *
 * Usage : php scripts/apply_full_word_rollout.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/apply_full_word_rollout.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
$dictPath = $root . '/storage/dictionary_fr.sqlite';
$seoPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_fr.sqlite';

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
 * @param string $whereClause SQL, deja bornee (colonnes is_ods8/is_ods9/is_french uniquement)
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

        $slug = strtolower($row['normalized']);
        $routePath = "/mot/{$slug}";
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
    'word_admitted-full-' . $addedAt,
    'Mot admis ODS8/ODS9. Atteint depuis les listes /mots/... deja indexees (longueur, '
        . 'commencant, terminant), les relations d\'autres fiches mot (Phase 4), et la '
        . 'navigation mot precedent/suivant.',
);
echo "  {$admittedCount} lignes\n";

echo "application word_french_not_admitted...\n";
$notAdmittedCount = $applyFamily(
    $dict,
    $seo,
    'is_french = 1 AND is_ods8 = 0 AND is_ods9 = 0',
    'word_french_not_admitted',
    'invalid-french',
    'word_french_not_admitted-full-' . $addedAt,
    'Forme francaise retenue par les sources Kartmaan/hbenbel apres filtrage (D-011, D-014), '
        . 'non admise a l\'ODS8/ODS9. Page utile pour repondre a la question "ce mot est-il '
        . 'admis ?" cote negatif (D-017 : un visiteur ne sait pas a l\'avance dans quel cas il '
        . 'tombe). Atteinte depuis les listes /mots/... deja indexees et la navigation mot '
        . 'precedent/suivant.',
);
echo "  {$notAdmittedCount} lignes\n";

$totalCount = $seo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
$indexCount = $seo->query("SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow'")->fetch()['c'];

echo "registre apres application : {$totalCount} lignes au total, {$indexCount} en 'index,follow'\n";
