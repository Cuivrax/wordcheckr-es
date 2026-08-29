<?php

declare(strict_types=1);

/**
 * Génère les fragments de sitemap et sitemap-index.xml depuis storage/seo_es.sqlite.
 * Hors ligne uniquement (CLI) -- jamais appelé au runtime, même principe que
 * scripts/build_seo_registry.php et scripts/apply_seo_batch.php. Adapté du dépôt français
 * cousin (scripts/build_sitemaps.php, FR) -- URLs wordcheckr.es, jamais wordcheckr.fr
 * (erreur déjà trouvée et corrigée une fois sur ce dépôt, ES-006 : sitemaps hérités de la
 * copie FR->ES supprimés, ne pas la réintroduire).
 *
 * Usage :
 *     php scripts/build_sitemaps.php --base-url=https://www.wordcheckr.es
 *
 * --base-url est OBLIGATOIRE : aucun domaine par défaut n'est supposé. Un domaine faux publié
 * dans un sitemap serait pire qu'aucun sitemap.
 *
 * Ne lit QUE storage/seo_es.sqlite : les colonnes route_path/canonical_path/sitemap_fragment
 * suffisent, aucun accès à storage/dictionary_es.sqlite n'est nécessaire ici (les deux bases
 * restent indépendantes même à la génération des sitemaps).
 *
 * Règles dures appliquées (docs/05_URL_SEO_INDEXATION.md, section Sitemaps) :
 *   - seules les lignes robots = 'index,follow' ET sitemap_fragment NOT NULL sont émises ;
 *     une ligne 'noindex,follow' n'apparaît JAMAIS dans un sitemap, même si sitemap_fragment
 *     était renseigné par erreur (défendu aussi en amont par scripts/apply_seo_batch.php, R4) ;
 *   - 40 000 URL au plus par fragment -- vérifié ici en sortie, pas seulement supposé respecté
 *     par la donnée en entrée (défense en profondeur : si un fragment dépasse la limite, le
 *     script s'arrête en erreur plutôt que d'écrire un fragment non conforme) ;
 *   - la famille détermine le PRÉFIXE attendu du fragment -- un fragment dont le préfixe ne
 *     correspond pas à la famille de toutes ses lignes est un signal d'incohérence de
 *     nommage, rejeté ici plutôt que publié silencieusement.
 *
 * Correctif I-7 (audit seo-technical-auditor, 2026-08-29) : route_path est stocké et manipulé
 * PARTOUT ailleurs sous forme de caractères UTF-8 décodés (ex. '/palabra/piña') -- correct pour
 * le HTML servi (charset UTF-8), mais XMLWriter::writeElement() écrit ce texte tel quel dans
 * <loc>, produisant un octet Ñ MULTI-OCTETS BRUT, jamais percent-encodé. Valide en XML pur (tout
 * caractère Unicode l'est), mais le protocole sitemaps.org exige que <loc> soit une URI conforme
 * RFC 3986 -- un caractère non-ASCII brut n'en est PAS une (16 022 <loc> concernées sur le
 * dépôt réel avant ce correctif, toutes les routes contenant Ñ). seoSitemapEncodePath()
 * pourcent-encode CHAQUE SEGMENT du chemin (jamais le chemin entier d'un bloc, qui percent-
 * encoderait aussi les '/' séparateurs de segments).
 */

/**
 * Pourcent-encode un route_path pour publication dans <loc>, un segment à la fois -- rawurlencode()
 * (RFC 3986, jamais urlencode() qui encode l'espace en '+', jamais pertinent ici : aucun segment
 * de route ne contient d'espace) laisse les caractères non réservés (A-Z a-z 0-9 - _ . ~)
 * inchangés et pourcent-encode tout le reste, Ñ/ñ compris. Le '/' séparateur de segments doit
 * rester un '/' littéral -- jamais encodé en %2F, qui casserait la structure du chemin -- d'où
 * l'découpage par segment plutôt qu'un rawurlencode() global sur route_path entier.
 */
function seoSitemapEncodePath(string $routePath): string
{
    return implode('/', array_map('rawurlencode', explode('/', $routePath)));
}

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/build_sitemaps.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

const MAX_URLS_PER_FRAGMENT = 40_000;

/**
 * Familles REELLEMENT peuplees sur ce depot a ce stade (docs/DECISIONS.md ES-009/ES-016). Toute
 * famille combinatoire future (contenant/avec/sans/motif/position/combined...) devra ajouter sa
 * propre entree ici au moment ou elle sera reellement ouverte -- jamais avant, meme discipline
 * que le depot francais cousin (voir son FAMILY_FRAGMENT_PREFIXES).
 *
 * @var array<string, string>
 */
const FAMILY_FRAGMENT_PREFIXES = [
    // home (racine '/' + hub de navigation '/palabras') : 2 pages au total, ES-009.
    'home' => 'core',
    // word_admitted (/palabra/{mot}, mots admis Lexicon FILE 2017/FISE-2) : ES-009.
    'word_admitted' => 'words',
    // word_list_length (/palabras/{N}-letras) : ES-009.
    'word_list_length' => 'letters',
    // word_list_commencant (/palabras/empiezan-por/{lettres}) : ES-016, premier palier
    // combinatoire -- prefixe distinct de 'letters' pour que build_sitemaps.php detecte tout
    // fragment mal etiquete (R4 de scripts/apply_seo_batch.php, meme discipline que les autres
    // entrees ci-dessous).
    'word_list_commencant' => 'starts',
    // word_list_terminant (/palabras/terminan-en/{lettres}) : ES-016, premier palier
    // combinatoire.
    'word_list_terminant' => 'ends',
    // word_spanish_not_admitted, rack, contenant/avec/sans/motif, et toute famille
    // position/combined future : absents volontairement -- soit App\Seo\Family::NEVER_SITEMAP
    // (jamais de prefixe), soit non encore ouverts (ES-009/ES-010/ES-016).
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
$dbPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_es.sqlite';
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
// l'echelle du dictionnaire (plusieurs centaines de milliers de lignes en famille
// word_admitted, ES-009) epuise la memoire CLI par defaut (128 Mo) si tout est charge en
// tableau avant traitement -- meme constat que le depot francais cousin. La requete trie deja
// par sitemap_fragment : un seul fragment (au plus MAX_URLS_PER_FRAGMENT lignes) est jamais
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

// Correctif I-8 (audit seo-technical-auditor, 2026-08-29) : ce script ecrit UNIQUEMENT les
// fragments correspondant au contenu ACTUEL du registre, mais n'a jamais supprime les fragments
// d'une execution PRECEDENTE devenus obsoletes (ex. un lot reduit -- docs/DECISIONS.md ES-011 --
// laisse sinon trainer des fragments words-0005.xml a words-0017.xml, contenant des URL qui ne
// sont plus dans sitemap-index.xml mais restent publiees et VERSIONNEES sur le disque -- trouve
// reellement sur ce depot apres l'application de la vague reduite word_admitted). Purge complete
// du dossier avant regeneration : ce script reconstruit l'INTEGRALITE de l'etat sitemap depuis
// storage/seo_es.sqlite a chaque execution (aucun fragment n'est jamais mis a jour partiellement
// ailleurs), une purge prealable est donc strictement equivalente a "aucun fragment perime ne
// survit", jamais une perte d'information puisque tout est regenere dans la meme execution.
foreach (glob($sitemapsDir . '/*.xml') ?: [] as $staleFile) {
    unlink($staleFile);
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
        $xml->writeElement('loc', $baseUrl . seoSitemapEncodePath($row['route_path']));
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
