<?php

declare(strict_types=1);

/**
 * Génère les fragments de sitemap et sitemap-index.xml depuis storage/seo_fr.sqlite
 * (Phase 6, docs/08). Hors ligne uniquement (CLI) -- jamais appelé au runtime, même principe
 * que scripts/build_seo_registry.php et scripts/apply_seo_batch.php.
 *
 * Usage :
 *     php scripts/build_sitemaps.php --base-url=https://exemple.fr
 *
 * --base-url est OBLIGATOIRE : aucun domaine par défaut n'est supposé (le domaine réel du
 * site n'est pas encore fixé dans ce dépôt -- voir le rapport AFTER de l'agent seo-registry).
 * Un domaine faux publié dans un sitemap serait pire qu'aucun sitemap.
 *
 * Ne lit QUE storage/seo_fr.sqlite : les colonnes route_path/canonical_path/sitemap_fragment
 * suffisent, aucun accès à storage/dictionary_fr.sqlite n'est nécessaire ici (D-002 -- les
 * deux bases restent indépendantes même à la génération des sitemaps).
 *
 * Règles dures appliquées (docs/05_URL_SEO_INDEXATION.md, section Sitemaps) :
 *   - seules les lignes robots = 'index,follow' ET sitemap_fragment NOT NULL sont émises ;
 *     une ligne 'noindex,follow' n'apparaît JAMAIS dans un sitemap, même si sitemap_fragment
 *     était renseigné par erreur (défendu aussi en amont par scripts/apply_seo_batch.php, R4) ;
 *   - 40 000 URL au plus par fragment -- vérifié ici en sortie, pas seulement supposé respecté
 *     par la donnée en entrée (défense en profondeur : si un fragment dépasse la limite, le
 *     script s'arrête en erreur plutôt que d'écrire un fragment non conforme) ;
 *   - la famille détermine le PRÉFIXE attendu du fragment (words-, invalid-french-, starts-,
 *     ends-, contains-, letters-, core-) -- un fragment dont le préfixe ne correspond pas à la
 *     famille de toutes ses lignes est un signal d'incohérence de nommage, rejeté ici plutôt
 *     que publié silencieusement.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/build_sitemaps.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

const MAX_URLS_PER_FRAGMENT = 40_000;

/** @var array<string, string> */
const FAMILY_FRAGMENT_PREFIXES = [
    'home' => 'core',
    'word_admitted' => 'words',
    'word_french_not_admitted' => 'invalid-french',
    'word_list_length' => 'letters',
    'word_list_commencant' => 'starts',
    'word_list_terminant' => 'ends',
    'word_list_contenant' => 'contains',
    // word_list_combined (D-024 correctif, D-025) : retiree de NEVER_SITEMAP le 2026-08-09,
    // espace borne (26x26 ou 14x26x26), pas une combinaison infinie -- prefixe 'combined',
    // ajoute a docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot.
    'word_list_combined' => 'combined',
    // word_list_position (D-023, jamais dans NEVER_SITEMAP) : une seule lettre connue a une
    // seule position, espace borne par construction (2 366 combinaisons reelles au total,
    // WordListFilters::readPosition() n'accepte qu'un seul couple position/lettre) -- prefixe
    // 'position', ajoute a docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot.
    // Balayage complet des 2 366 combinaisons : 0 au-dessus du budget TTFB, voir
    // reports/query-plans/position-full-sweep.md.
    'word_list_position' => 'position',
    // word_list_avec_single_letter (demande produit du 2026-08-17, jamais dans NEVER_SITEMAP) :
    // PALIER 1 de l'ouverture en entonnoir de "avec" -- longueur explicite ET exactement une
    // lettre "avec" (occurrence unique), 14 x 26 = 364 combinaisons au plus, borne de la famille
    // elle-meme sur ce perimetre precis -- distincte en permanence de word_list_avec (multiensemble
    // general, ci-dessous, NEVER_SITEMAP) et de tout futur palier (2 lettres, 3 lettres...), qui
    // devra recevoir sa PROPRE constante Family et son propre prefixe, jamais celui-ci. Prefixe
    // 'avec-single', ajoute a docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot.
    // Balayage complet des 364 combinaisons : 0 au-dessus du budget TTFB, voir
    // reports/query-plans/avec-length-1-letter-full-sweep.md et app/Seo/Family.php.
    'word_list_avec_single_letter' => 'avec-single',
    // word_list_avec_two_letters (demande produit du 2026-08-17, jamais dans NEVER_SITEMAP) :
    // PALIER 2 de l'ouverture en entonnoir de "avec" -- longueur explicite ET exactement deux
    // lettres "avec" DISTINCTES (occurrence unique chacune), 14 x C(26,2) = 4 550 combinaisons au
    // plus, borne de la famille elle-meme sur ce perimetre precis -- distincte en permanence de
    // word_list_avec_single_letter (palier 1, ci-dessus) et de word_list_avec (multiensemble
    // general, ci-dessous, NEVER_SITEMAP). Prefixe 'avec-pair', ajoute a
    // docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot. Balayage complet des
    // 4 550 combinaisons (agent data-engine, 3 runs) : voir reports/query-plans/
    // avec-length-2-letters-full-sweep.md et app/Seo/Family.php pour le detail complet, dont la
    // re-verification independante effectuee par l'agent seo-registry avant application.
    'word_list_avec_two_letters' => 'avec-pair',
    // word_list_avec_three_letters (demande produit du 2026-08-18, jamais dans NEVER_SITEMAP) :
    // PALIER 3 de l'ouverture en entonnoir de "avec" -- longueur explicite ET exactement trois
    // lettres "avec" DISTINCTES (occurrence unique chacune), 14 x C(26,3) = 36 400 combinaisons au
    // plus, borne de la famille elle-meme sur ce perimetre precis -- distincte en permanence de
    // word_list_avec_single_letter (palier 1), word_list_avec_two_letters (palier 2, ci-dessus) et
    // de word_list_avec (multiensemble general, ci-dessous, NEVER_SITEMAP). Prefixe 'avec-triple',
    // ajoute a docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot. Balayage complet
    // des 36 400 combinaisons (agent data-engine, un seul passage) : 28 827 a >= 1 resultat, voir
    // reports/query-plans/avec-length-3-letters-full-sweep.md et app/Seo/Family.php pour le detail
    // complet, dont l'investigation d'un pic de latence isole juge non structurel.
    'word_list_avec_three_letters' => 'avec-triple',
    // word_list_combined_with_letter (demande produit du 2026-08-18, jamais dans NEVER_SITEMAP,
    // D-033) : NOUVELLE classification, DISTINCTE de word_list_combined -- prefixe ET suffixe
    // chacun d'une seule lettre, SANS longueur, PLUS une lettre "avec" d'occurrence unique,
    // 611 paires commencant+terminant reelles x 26 lettres = 15 886 combinaisons candidates au
    // plus, borne de la famille elle-meme sur ce perimetre precis. Prefixe 'combined-with',
    // ajoute a docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot. Balayage
    // complet des 15 886 combinaisons : 0 au-dessus du budget TTFB, voir reports/query-plans/
    // commencant-terminant-avec-full-sweep.md et app/Seo/Family.php.
    'word_list_combined_with_letter' => 'combined-with',
    // word_list_commencant_with_letter (demande produit du 2026-08-18, dernier des quatre axes
    // commencant/terminant/avec travailles ce jour, jamais dans NEVER_SITEMAP, D-032) : NOUVELLE
    // classification, distincte de word_list_commencant (prefixe seul) ET de
    // word_list_combined_with_letter (ci-dessus, prefixe+terminant+avec) -- prefixe d'une seule
    // lettre, SANS longueur, SANS terminant, PLUS une lettre "avec" d'occurrence unique, 26
    // prefixes reels x 26 lettres = 676 combinaisons brutes au plus, borne de la famille
    // elle-meme sur ce perimetre precis. Prefixe 'commencant-avec', ajoute a
    // docs/05_URL_SEO_INDEXATION.md (section Sitemaps) dans le meme lot. Balayage complet des
    // 650 combinaisons non degenerees : 0 au-dessus du budget TTFB, voir reports/query-plans/
    // commencant-avec-no-length-full-sweep.md, reports/query-plans/commencant-avec-maillage.md
    // et app/Seo/Family.php.
    'word_list_commencant_with_letter' => 'commencant-avec',
    // word_list_avec, word_list_sans, word_list_motif, rack : absents volontairement --
    // App\Seo\Family::NEVER_SITEMAP, jamais de prefixe de fragment.
];

$baseUrl = null;

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--base-url=')) {
        $baseUrl = substr($arg, strlen('--base-url='));
    }
}

if ($baseUrl === null || $baseUrl === '') {
    fwrite(STDERR, "--base-url=https://... est obligatoire\n");
    exit(1);
}

$baseUrl = rtrim($baseUrl, '/');

$root = dirname(__DIR__);
// SCRABBLE_SEO_DB_PATH / SCRABBLE_PUBLIC_DIR : reserves aux tests (tests/Seo/), jamais
// definis en usage normal -- meme raison que scripts/apply_seo_batch.php : permet de verifier
// ce script sans jamais ecrire dans le vrai public/ pendant la suite de tests.
$dbPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_fr.sqlite';
$publicDir = getenv('SCRABBLE_PUBLIC_DIR') ?: $root . '/public';

if (!is_file($dbPath)) {
    fwrite(STDERR, "registre introuvable : {$dbPath}\nlancer d'abord scripts/build_seo_registry.php puis scripts/apply_seo_batch.php\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Iteration en flux (PDOStatement parcouru directement, jamais fetchAll) : un registre a
// l'echelle du dictionnaire (plus de 800 000 lignes en famille word_admitted +
// word_french_not_admitted, D-017) epuise la memoire CLI par defaut (128 Mo) si tout est
// charge en tableau avant traitement -- teste et confirme. La requete trie deja par
// sitemap_fragment : un seul fragment (au plus MAX_URLS_PER_FRAGMENT lignes) est jamais
// retenu en memoire a la fois, jamais le registre entier.
$statement = $pdo->query(
    "SELECT route_path, family, canonical_path, sitemap_fragment FROM registry "
    . "WHERE robots = 'index,follow' AND sitemap_fragment IS NOT NULL "
    . 'ORDER BY sitemap_fragment, route_path'
);

$sitemapsDir = $publicDir . '/sitemaps';

if (!is_dir($sitemapsDir)) {
    mkdir($sitemapsDir, 0777, true);
}

$fragmentFiles = [];
$totalUrls = 0;

$currentFragment = null;
/** @var list<array<string, string>> */
$currentRows = [];

$flushFragment = static function (?string $fragment, array $rows) use ($sitemapsDir, $baseUrl, &$fragmentFiles): void {
    if ($fragment === null || $rows === []) {
        return;
    }

    if (count($rows) > MAX_URLS_PER_FRAGMENT) {
        fwrite(STDERR, sprintf(
            "fragment '%s' depasse %d URL (%d) -- rescinder le lot en amont, jamais publier tel quel\n",
            $fragment,
            MAX_URLS_PER_FRAGMENT,
            count($rows),
        ));
        exit(1);
    }

    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('urlset');
    $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    foreach ($rows as $row) {
        $xml->startElement('url');
        $xml->writeElement('loc', $baseUrl . $row['route_path']);
        $xml->endElement();
    }

    $xml->endElement();
    $xml->endDocument();

    $fileName = $fragment . '.xml';
    $filePath = $sitemapsDir . '/' . $fileName;
    file_put_contents($filePath, $xml->outputMemory());
    $fragmentFiles[] = $fileName;

    printf("%s : %d URL\n", $fileName, count($rows));
};

foreach ($statement as $row) {
    // Une URL non canonique (canonical_path != route_path) ne doit jamais apparaitre dans un
    // sitemap : chaque entree de sitemap doit "repondre 200, index, canonical autonome" (docs/05)
    // -- une ligne qui pointe son canonical ailleurs qu'elle-meme n'est PAS le gagnant.
    if ($row['canonical_path'] !== $row['route_path']) {
        fwrite(STDERR, sprintf(
            "ignore (canonical non autonome) : %s -> %s\n",
            $row['route_path'],
            $row['canonical_path'],
        ));

        continue;
    }

    $expectedPrefix = FAMILY_FRAGMENT_PREFIXES[$row['family']] ?? null;

    if ($expectedPrefix === null) {
        fwrite(STDERR, sprintf(
            "famille '%s' sans prefixe de sitemap autorise (route %s) -- ligne ignoree, verifier apply_seo_batch.php (R4)\n",
            $row['family'],
            $row['route_path'],
        ));

        continue;
    }

    if (!str_starts_with($row['sitemap_fragment'], $expectedPrefix . '-')) {
        fwrite(STDERR, sprintf(
            "fragment '%s' ne correspond pas au prefixe attendu '%s-' pour la famille '%s' (route %s)\n",
            $row['sitemap_fragment'],
            $expectedPrefix,
            $row['family'],
            $row['route_path'],
        ));
        exit(1);
    }

    if ($row['sitemap_fragment'] !== $currentFragment) {
        $flushFragment($currentFragment, $currentRows);
        $totalUrls += count($currentRows);
        $currentRows = [];
        $currentFragment = $row['sitemap_fragment'];
    }

    $currentRows[] = $row;
}

$flushFragment($currentFragment, $currentRows);
$totalUrls += count($currentRows);

sort($fragmentFiles);

$indexXml = new XMLWriter();
$indexXml->openMemory();
$indexXml->setIndent(true);
$indexXml->startDocument('1.0', 'UTF-8');
$indexXml->startElement('sitemapindex');
$indexXml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($fragmentFiles as $fileName) {
    $indexXml->startElement('sitemap');
    $indexXml->writeElement('loc', $baseUrl . '/sitemaps/' . $fileName);
    $indexXml->endElement();
}

$indexXml->endElement();
$indexXml->endDocument();

file_put_contents($publicDir . '/sitemap-index.xml', $indexXml->outputMemory());

printf("sitemap-index.xml : %d fragment(s), %d URL au total\n", count($fragmentFiles), $totalUrls);
