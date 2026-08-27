<?php

declare(strict_types=1);

/**
 * Vue listes de mots /mots/..., appelee par public/index.php avec $page
 * (App\Search\WordListPage, Phase 3). Meme patron que app/View/play.php et
 * app/View/word.php (reponse directe presente sans JavaScript, .status-badge
 * reutilise tel quel, aucun credit de source -- D-015).
 *
 * Deux regimes distincts (voir App\Search\WordListPage) :
 * - exact = true  : $page->total est un compte EXACT, pagination fiable.
 *   total = 0 est un cas distinct ("aucun mot"), jamais confondu avec le cas
 *   tronque ci-dessous.
 * - exact = false (donc $page->truncated = true) : $page->total est un compte
 *   trouve dans une fenetre bornee (WordListSolver::ROW_EXAMINATION_CEILING),
 *   jamais presente comme un chiffre definitif -- formulation "au moins N mots"
 *   dans le meme esprit que le cas RackPage::$capped de /jouer/{lettres}.
 *
 * Le H1 et le paragraphe de reponse directe sont construits a partir de
 * App\Search\WordListFilters::fromPath($page->canonicalPath) -- reparse pure
 * (aucun acces base), qui redonne les contraintes actives dans l'ordre
 * canonique impose (docs/05) pour produire un titre lisible combinant
 * plusieurs contraintes. Les liens de pagination reutilisent
 * WordListFilters::canonicalUrl() plutot que de reconstruire l'URL a la main,
 * pour rester byte-identiques a ce que public/index.php attend comme forme
 * canonique (evite tout aller-retour de redirection 301).
 *
 * Aucun formulaire de construction de requete ici (aucune UI de ce type
 * documentee dans docs/04) -- page de resultats uniquement, atteinte par URL
 * directe. Le mini-formulaire "verifier un mot" est repris tel quel (meme
 * composant .inline-check que sur toutes les autres vues), pas un
 * constructeur de filtres.
 */

require __DIR__ . '/helpers.php';

use App\Search\AvecSansLengthLinks;
use App\Search\AvecThreeLettersLinks;
use App\Search\AvecTwoLettersLinks;
use App\Search\LengthCombinedLinks;
use App\Search\LengthLinks;
use App\Search\LetterCombinedLinks;
use App\Search\PositionLinks;
use App\Search\PrefixAvecLinks;
use App\Search\PrefixExtensionLinks;
use App\Search\StartEndWithLinks;
use App\Search\SuffixExtensionLinks;
use App\Search\TermPage;
use App\Search\WordListFilters;
use App\Search\WordListPage;

/** @var WordListPage $page */
/** @var \App\Seo\SeoMeta $seo */
/**
 * PROTOTYPE (maillage prefixe -> longueur, en discussion, pas encore une decision
 * d'indexation) : $refine, quand fourni, ajoute une section de navigation apres les
 * resultats -- 'byLength' (liste de ['url'=>..., 'label'=>..., 'count'=>...] pour filtrer une
 * page commencant/{X} par longueur). Calcule en requetes live pour la demonstration -- une
 * version definitive devrait precalculer ces comptes hors ligne (meme principe que
 * list_counts) avant tout rollout reel, comme le reste du site l'impose deja pour toute page a
 * fort trafic.
 *
 * CORRECTIF (2026-08-18) : ce mecanisme portait aussi un champ 'continuations' (approfondir le
 * prefixe/suffixe d'une lettre) -- retire, desormais couvert par le maillage precalcule et
 * verifie App\Search\PrefixExtensionLinks/SuffixExtensionLinks ci-dessus (etendu a 1-3 lettres,
 * pas seulement 1, et sans requete live), qui rendait ce champ du prototype strictement
 * redondant (les deux produisaient la meme section "Continuer Le Prefixe", doublon constate en
 * direct sur /mots/commencant/a).
 *
 * @var array{byLength: list<array{url: string, label: string, count: int}>}|null $refine
 */
$refine ??= null;

/**
 * Maillage interne des pages "mots de {N} lettres" (D-022, decision produit prise, pas un
 * prototype) -- non null des qu'une longueur est presente, quelle que soit la combinaison
 * d'autres contraintes actives (public/index.php). Precalcule (App\Search\LengthLinksBuilder),
 * jamais de requete live.
 *
 * @var LengthLinks|null $lengthLinks
 */
$lengthLinks ??= null;

/**
 * Maillage commencant+terminant (D-024, decision produit prise, pas un prototype) -- non null
 * uniquement depuis une page mono-lettre sans longueur ni autre contrainte
 * (/mots/commencant/{X} ou /mots/terminant/{Y}, public/index.php). Precalcule
 * (App\Search\LetterCombinedLinksBuilder), jamais de requete live.
 *
 * @var LetterCombinedLinks|null $letterCombinedLinks
 */
$letterCombinedLinks ??= null;

/**
 * Maillage commencant+avec, SANS terminant ni longueur (2026-08-18, dimensionnement) -- non
 * null uniquement depuis une page commencant SEULE (une lettre, aucune longueur, aucun
 * suffixe, aucune autre contrainte, public/index.php). Precalcule
 * (App\Search\PrefixAvecLinksBuilder), jamais de requete live.
 *
 * @var PrefixAvecLinks|null $prefixAvecLinks
 */
$prefixAvecLinks ??= null;

/**
 * Maillage en entonnoir commencant multi-lettres (2026-08-18, dimensionnement) -- non null
 * uniquement depuis une page commencant SEULE (1 a 3 lettres, aucune longueur, aucune autre
 * contrainte, public/index.php). Precalcule (App\Search\PrefixExtensionLinksBuilder), jamais de
 * requete live.
 *
 * @var PrefixExtensionLinks|null $prefixExtensionLinks
 */
$prefixExtensionLinks ??= null;

/**
 * Meme principe, symetrique cote terminant (App\Search\SuffixExtensionLinksBuilder).
 *
 * @var SuffixExtensionLinks|null $suffixExtensionLinks
 */
$suffixExtensionLinks ??= null;

/**
 * Maillage commencant+terminant AVEC longueur (D-027) -- non null uniquement depuis une page
 * longueur + UNE SEULE lettre commencant/terminant, sans l'autre cote (/mots/{N}-lettres/
 * commencant/{X} ou /mots/{N}-lettres/terminant/{Y}, public/index.php). Precalcule
 * (App\Search\LengthCombinedLinksBuilder), jamais de requete live.
 *
 * @var LengthCombinedLinks|null $lengthCombinedLinks
 */
$lengthCombinedLinks ??= null;

/**
 * Maillage commencant+terminant+avec (2026-08-18, dimensionnement) -- non null uniquement
 * depuis une page commencant ET terminant, tous deux d'une seule lettre, SANS longueur, sans
 * autre contrainte (/mots/commencant/{X}/terminant/{Y}, public/index.php). Precalcule
 * (App\Search\StartEndWithLinksBuilder), jamais de requete live.
 *
 * @var StartEndWithLinks|null $startEndWithLinks
 */
$startEndWithLinks ??= null;

/**
 * Maillage "avec {X}" -> position exacte (D-023bis, decision produit prise, pas un prototype)
 * -- non null uniquement depuis une page longueur + une seule lettre "avec" (occurrence
 * unique, sans autre contrainte, public/index.php). Precalcule
 * (App\Search\PositionLinksBuilder), jamais de requete live.
 *
 * @var PositionLinks|null $positionLinks
 */
$positionLinks ??= null;

/**
 * Maillage "avec {X}" -> "avec {X} {Y}" (palier 2 de l'ouverture en entonnoir de "avec", D-030)
 * -- non null uniquement depuis une page longueur + une seule lettre "avec" (occurrence
 * unique, sans autre contrainte, public/index.php). Precalcule
 * (App\Search\AvecTwoLettersLinksBuilder), jamais de requete live.
 *
 * @var AvecTwoLettersLinks|null $avecTwoLettersLinks
 */
$avecTwoLettersLinks ??= null;

/**
 * Maillage "avec {X} {Y}" -> "avec {X} {Y} {Z}" (palier 3 de l'ouverture en entonnoir de
 * "avec", D-031) -- non null uniquement depuis une page longueur + EXACTEMENT DEUX lettres
 * "avec" (occurrence unique chacune, sans autre contrainte, public/index.php). Precalcule
 * (App\Search\AvecThreeLettersLinksBuilder), jamais de requete live.
 *
 * @var AvecThreeLettersLinks|null $avecThreeLettersLinks
 */
$avecThreeLettersLinks ??= null;

/**
 * Maillage "avec {X} sans {Y}" -> longueur (D-024bis, decision produit prise, pas un
 * prototype) -- non null uniquement depuis une page SANS longueur, une seule lettre "avec" et
 * une seule lettre "sans" (public/index.php). Precalcule
 * (App\Search\AvecSansLengthLinksBuilder), jamais de requete live.
 *
 * @var AvecSansLengthLinks|null $avecSansLengthLinks
 */
$avecSansLengthLinks ??= null;

$filters = WordListFilters::fromPath($page->canonicalPath);

// Toggles statut/tri (D-022) : reconstruit l'URL de chaque variante en repartant du chemin
// canonique DEBARRASSE de tout segment "statut"/"tri" existant (toujours en fin d'ordre
// canonique, voir WordListFilters), puis en rajoutant la variante voulue -- jamais assemble a
// la main, toujours re-valide par WordListFilters::fromPath()->canonicalUrl() comme partout
// ailleurs sur cette page (memes garanties que $pageUrl ci-dessus).
$basePath = $page->canonicalPath;
$baseSegments = $basePath === '' ? [] : explode('/', $basePath);

if (count($baseSegments) >= 2 && $baseSegments[count($baseSegments) - 2] === 'tri') {
    $baseSegments = array_slice($baseSegments, 0, -2);
}

if (count($baseSegments) >= 2 && $baseSegments[count($baseSegments) - 2] === 'statut') {
    $baseSegments = array_slice($baseSegments, 0, -2);
}

$refineUrl = static function (?string $status, ?string $sort) use ($baseSegments): ?string {
    $segments = $baseSegments;

    if ($status !== null) {
        $segments[] = 'statut';
        $segments[] = $status;
    }

    if ($sort !== null) {
        $segments[] = 'tri';
        $segments[] = $sort;
    }

    return WordListFilters::fromPath(implode('/', $segments))?->canonicalUrl();
};

$currentStatus = $filters?->status;
$currentSort = $filters?->sort;

$statusToggles = [
    ['label' => 'Tous', 'url' => $refineUrl(null, $currentSort), 'active' => $currentStatus === null],
    ['label' => 'Admis', 'url' => $refineUrl('admis', $currentSort), 'active' => $currentStatus === 'admis'],
    ['label' => 'Non Admis', 'url' => $refineUrl('non-admis', $currentSort), 'active' => $currentStatus === 'non-admis'],
];

$sortToggles = $filters !== null && $filters->length !== null
    ? [
        ['label' => 'Alphabétique', 'url' => $refineUrl($currentStatus, null), 'active' => $currentSort === null],
        ['label' => 'Points Croissants', 'url' => $refineUrl($currentStatus, 'points'), 'active' => $currentSort === 'points'],
        ['label' => 'Points Décroissants', 'url' => $refineUrl($currentStatus, 'points-desc'), 'active' => $currentSort === 'points-desc'],
    ]
    : [];

$pageUrl = static function (int $targetPage) use ($page): string {
    $path = $page->canonicalPath . ($targetPage > 1 ? '/page/' . $targetPage : '');
    $targetFilters = WordListFilters::fromPath($path);

    return $targetFilters?->canonicalUrl() ?? '/mots';
};

// Chaine de pagination en nofollow quand la liste n'a AUCUN ancrage indexe (ni longueur, ni
// debut, ni fin) -- audit final, 4e passe, code-reviewer, constat I-1 : sans ancrage,
// WordListSolver::solveBounded() parcourt l'index dans son integralite (exception bornee et
// documentee, docs/DECISIONS.md D-019) ; suivre Precedent/Suivant sur ces listes rejoue ce
// parcours a chaque page (jusqu'a 200 pages), rouvrant automatiquement pour un robot le meme
// risque de crawl que les liens auto-generes deja retires ailleurs (RelationsFinder). Les
// listes ancrees (longueur/debut/fin) restent en follow : elles servent de chemin de crawl
// legitime vers les fiches mots (D-017), et leur cout par page est deja borne par un index.
//
// CORRECTIF (audit D-030, seo-technical-auditor, constat I-2, 2026-08-18) : le follow des
// listes ancrees n'avait AUCUN plafond de profondeur -- mesure exacte sur les 3 paliers "avec"
// seuls (D-029/D-030) : 1 049 502 pages /page/N potentiellement crawlables au total, jamais
// indexables (noindex,follow) mais un vrai cout de crawl sur hebergement mutualise (CLAUDE.md,
// plusieurs workers PHP concurrents partages). Une page ancree tres profonde (ex. page 150 sur
// 200) rejoue le meme cout de requete borne (jusqu'a 10 000 lignes examinees) qu'une page 1 pour
// une poignee de resultats marginaux -- suivre le lien au-dela d'une profondeur raisonnable ne
// sert plus la decouverte, seulement le budget de crawl gaspille. Plafond retenu : les 3
// premieres pages (1<->2<->3) restent un chemin de crawl suivi, au-dela la chaine passe en
// nofollow -- aucun changement d'indexation (chaque page /page/N reste noindex,follow dans les
// deux cas, seul le suivi du LIEN change), verifie par tests/Frontend/WordListViewTest.php.
$isAnchored = $filters !== null && ($filters->length !== null || $filters->prefix !== null || $filters->suffix !== null);
$paginationFollowDepth = 3;
$paginationRelFor = static function (int $targetPage) use ($isAnchored, $paginationFollowDepth): string {
    return ($isAnchored && $targetPage <= $paginationFollowDepth) ? '' : ' rel="nofollow"';
};

// Titre lisible, ordre canonique impose (docs/05) : longueur -> commencant ->
// contenant -> terminant -> avec -> sans -> motif ("position" hors perimetre).
$titleParts = [];

if ($filters !== null && $filters->length !== null) {
    $titleParts[] = sprintf('de %d lettre%s', $filters->length, $filters->length > 1 ? 's' : '');
}

if ($filters !== null && $filters->prefix !== null) {
    $titleParts[] = 'commençant par ' . $filters->prefix;
}

if ($filters !== null && $filters->contains !== null) {
    $titleParts[] = 'contenant ' . $filters->contains;
}

if ($filters !== null && $filters->suffix !== null) {
    $titleParts[] = 'terminant par ' . $filters->suffix;
}

if ($filters !== null && $filters->position !== null) {
    // Position 1 (1re) n'apparait jamais ici : WordListFilters::fromPath() la collapse
    // toujours vers "commencant" (D-023, evite le contenu duplique) -- seule la forme "Ne"
    // (2e, 3e...) est necessaire, meme convention que les personnes de conjugaison (D-018,
    // helpers.php).
    $titleParts[] = 'avec ' . $filters->positionLetter . ' en ' . $filters->position . 'e position';
}

if ($filters !== null && $filters->withLetters !== []) {
    $withLetters = [];
    foreach ($filters->withLetters as $letter => $count) {
        for ($k = 0; $k < $count; $k++) {
            $withLetters[] = $letter;
        }
    }
    $titleParts[] = 'avec ' . implode(', ', $withLetters);
}

if ($filters !== null && $filters->withoutLetters !== []) {
    $titleParts[] = 'sans ' . implode(', ', $filters->withoutLetters);
}

if ($filters !== null && $filters->pattern !== null) {
    $titleParts[] = 'au motif ' . $filters->pattern;
}

$descriptor = implode(' ', $titleParts);
// $descriptor reste en minuscules (hors "Mots") : reutilise tel quel dans les phrases de
// $statusMeta['direct'] ci-dessous ("Il y a 5 mots de 7 lettres..."), ou un Title Case serait
// grammaticalement faux en milieu de phrase. $pageTitle (title, breadcrumb, H1) suit la
// convention Title Case du reste du site (M5, audit final) -- mb_convert_case gere
// correctement les mots accentues francais (commençant -> Commençant) et laisse les lettres
// deja en majuscule (A, TION, C--E-) inchangees.
$pageTitle = mb_convert_case(trim('Mots ' . $descriptor), MB_CASE_TITLE, 'UTF-8');
// Correctif position (D-023) : mb_convert_case() traite toute frontiere chiffre/lettre comme
// un debut de "mot" et capitalise la lettre qui suit -- "3e" devient "3E", jamais souhaite
// pour l'ordinal francais ("3e position", pas "3E Position"). Corrige apres coup plutot que
// d'echapper le fragment avant mb_convert_case() (aucun moyen simple de le faire ignorer une
// seule frontiere sans risquer d'affecter les autres mots du titre).
$pageTitle = (string) preg_replace('/(\d+)E\b/', '$1e', $pageTitle);

/**
 * Enumeration naturelle "A", "A et B", "A, B et C" (jamais de virgule d'Oxford avant "et",
 * convention francaise) -- utilisee par $statusMeta ci-dessous pour la liste 2 a 5 mots.
 *
 * @param list<string> $items
 */
$naturalList = static function (array $items): string {
    if (count($items) === 1) {
        return $items[0];
    }

    $last = array_pop($items);

    return implode(', ', $items) . ' et ' . $last;
};

// Reponse directe : trois cas distincts, jamais confondus (voir doc de tete).
// $page->truncated est teste EN PREMIER, avant $page->total === 0 : un panier
// tronque avec 0 resultat DANS LA FENETRE EXAMINEE n'est pas la meme chose
// qu'un "aucun mot" exact -- confondre les deux affirmerait a tort une absence
// definitive alors que d'autres correspondances pourraient exister au-dela de
// WordListSolver::ROW_EXAMINATION_CEILING.
$statusMeta = match (true) {
    $page->truncated => [
        'modifier' => 'admitted',
        'badge' => 'Liste Partielle',
        'subtitle' => 'Liste partielle, non exhaustive.',
        'direct' => $page->total > 0
            ? sprintf(
                'Au moins %d mot%s %s %s trouvé%s dans la partie examinée. La liste n’est pas garantie complète au-delà de cette limite.',
                $page->total,
                $page->total > 1 ? 's' : '',
                $descriptor,
                $page->total > 1 ? 'ont été' : 'a été',
                $page->total > 1 ? 's' : '',
            )
            : sprintf(
                'Aucun mot %s trouvé dans la partie examinée. La liste n’est pas garantie complète au-delà de cette limite.',
                $descriptor,
            ),
    ],
    $page->total === 0 => [
        'modifier' => 'unknown',
        'badge' => 'Aucun Mot',
        'subtitle' => 'Aucun mot trouvé.',
        'direct' => sprintf('Aucun mot %s n’a été trouvé dans la base.', $descriptor),
    ],
    $page->total === 1 => [
        'modifier' => 'admitted',
        'badge' => 'Mot Trouvé',
        'subtitle' => 'Liste classée par ordre alphabétique.',
        // Meta description enrichie (audit D-031, constat I-3) : cite le mot reel plutot
        // qu'une phrase generique -- donnee deja chargee pour le tableau de resultats,
        // aucune requete supplementaire. Repli sur la phrase generique si $page->items est
        // vide : total = 1 ne garantit PAS $page->items[0] (page demandee au-dela de la
        // derniere page existante, ex. ".../page/2" sur une liste a 1 resultat -- meme cas
        // que "Aucun mot sur cette page." plus bas, jamais suppose absent).
        // Phrase sans ":" (demande produit, 2026-08-24) -- "X est l'unique mot ... admis au
        // Scrabble" plutot que "il y a 1 mot ... : X, admis".
        'direct' => $page->items !== []
            ? sprintf(
                '%s est l’unique mot %s, %s.',
                $page->items[0]['normalized'],
                $descriptor,
                $page->items[0]['status'] === TermPage::STATUS_ADMITTED ? 'admis au Scrabble' : 'non admis au Scrabble',
            )
            : sprintf('Il y a 1 mot %s.', $descriptor),
    ],
    $page->total >= 2 && $page->total <= 5 => [
        'modifier' => 'admitted',
        'badge' => 'Mots Trouvés',
        'subtitle' => 'Liste classée par ordre alphabétique.',
        // Meme correctif I-3 : liste courte entierement contenue dans $page->items (PAGE_SIZE
        // = 50, toujours superieur a 5) SI la page demandee est la premiere -- meme repli que
        // ci-dessus pour une page hors bornes. Enumeration naturelle ("A et B" / "A, B et C"),
        // sans ":" (demande produit, 2026-08-24). Ne dit PAS "admis au Scrabble" pour
        // l'ensemble : une liste courte peut melanger admis et non admis (ex. commencant/X/
        // terminant/Y), le statut individuel reste dans .status-badge par ligne -- "recenses"
        // reste vrai quel que soit le statut de chaque mot.
        'direct' => $page->items !== []
            ? sprintf(
                '%s sont les %d mots %s recensés au Scrabble.',
                $naturalList(array_map(static fn (array $item): string => (string) $item['normalized'], $page->items)),
                $page->total,
                $descriptor,
            )
            : sprintf('Il y a %d mots %s.', $page->total, $descriptor),
    ],
    default => [
        'modifier' => 'admitted',
        'badge' => 'Mots Trouvés',
        'subtitle' => 'Liste classée par ordre alphabétique.',
        // Gabarit enrichi (demande produit, 2026-08-24) : mentionne explicitement le Scrabble
        // et les dictionnaires officiels plutot qu'un simple compte brut ("Il y a N mots de
        // X.") -- "dictionnaires officiels" plutot que les sigles ODS8/ODS9 (jargon technique,
        // peu recherche tel quel), coherent avec home.php.
        'direct' => sprintf(
            'Découvrez les %d mots %s, admis dans les dictionnaires officiels du Scrabble. Triez par points ou parcourez par ordre alphabétique.',
            $page->total,
            $descriptor,
        ),
    ],
};

// Balise <title> enrichie pour les listes a 1 seul resultat (audit D-031, constat I-3,
// demande produit) : cite le mot reel en tete -- distinct de $pageTitle (fil d'Ariane, H1),
// qui reste la categorie generale de la page, jamais le contenu d'une seule ligne. Meme repli
// que $statusMeta ci-dessus si $page->items est vide (page hors bornes).
// Tiret court plutot que ":" ou un tiret cadratin (demande produit, 2026-08-24).
$metaTitle = ($page->total === 1 && $page->items !== [])
    ? $page->items[0]['normalized'] . ' - ' . $pageTitle
    : $pageTitle;

// Statut par ligne : memes trois valeurs fermees que la fiche mot (jamais
// STATUS_UNKNOWN ici, voir WordListSolver::toItems()). Texte minimal, a
// confirmer par l'agent microcopy -- meme convention que app/View/word.php.
$rowStatusMeta = static fn (string $status): array => $status === TermPage::STATUS_ADMITTED
    ? ['modifier' => 'admitted', 'label' => 'Admis']
    : ['modifier' => 'not-admitted', 'label' => 'Non Admis'];

$showPagination = $page->hasPreviousPage || $page->hasNextPage;
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title><?= e($metaTitle) ?> | WORD CHECKR</title>
<meta name="description" content="<?= e($statusMeta['direct']) ?>">
<?php if ($seo->canonicalUrl !== null): ?>
<link rel="canonical" href="<?= e($seo->canonicalUrl) ?>">
<?php endif; ?>
<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="shortcut icon" href="/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<meta name="apple-mobile-web-app-title" content="WordCheckr">
<link rel="manifest" href="/site.webmanifest">
<link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu</a>
<header class="header">
  <div class="site header-row">
    <a class="logo" href="/"><img class="logo-mark" src="/assets/img/logo.png" alt="" width="32" height="32">WORD CHECKR</a>
    <nav class="nav" aria-label="Navigation principale"><a href="/">Nouvelle recherche</a></nav>
  </div>
</header>

<main class="word-shell main" id="main">
  <nav class="breadcrumb" aria-label="Fil d’Ariane"><a href="/">Accueil</a> › <?= e($pageTitle) ?></nav>

  <article class="word-card">
    <section class="word-answer">
      <span class="status-badge status-badge--<?= e($statusMeta['modifier']) ?>"><?= e($statusMeta['badge']) ?></span>
      <h1 class="word-title explore-title"><?= e($pageTitle) ?></h1>
      <p><?= e($statusMeta['subtitle']) ?></p>
    </section>

    <section class="direct">
      <h2>Réponse Directe</h2>
      <p><?= e($statusMeta['direct']) ?></p>
    </section>

    <section class="explore-group refine-toggles">
      <h2>Affiner La Liste</h2>
      <div class="related-links" role="group" aria-label="Filtrer par statut">
<?php foreach ($statusToggles as $toggle): ?>
<?php if ($toggle['url'] !== null): ?>
        <a href="<?= e($toggle['url']) ?>"<?= $toggle['active'] ? ' aria-current="page"' : '' ?>><?= e($toggle['label']) ?></a>
<?php endif; ?>
<?php endforeach; ?>
      </div>
<?php if ($sortToggles !== []): ?>
      <div class="related-links" role="group" aria-label="Trier la liste">
<?php foreach ($sortToggles as $toggle): ?>
<?php if ($toggle['url'] !== null): ?>
        <a href="<?= e($toggle['url']) ?>"<?= $toggle['active'] ? ' aria-current="page"' : '' ?>><?= e($toggle['label']) ?></a>
<?php endif; ?>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </section>

<?php if ($page->items !== []): ?>
    <section class="rack-results">
<?php if ($page->truncated): ?>
      <p class="help rack-results-note">Résultats trouvés dans une fenêtre bornée, non exhaustifs au-delà de cette limite.</p>
<?php endif; ?>
      <div class="rack-result-head" aria-hidden="true">
        <span>Mot</span><span class="rack-result-head-center">Statut</span><span class="rack-result-head-right">Points</span><span class="rack-result-head-length">Lettres</span>
      </div>
      <ul class="rack-result-list">
<?php foreach ($page->items as $item): ?>
<?php $rowStatus = $rowStatusMeta($item['status']); ?>
        <li class="rack-result-row">
          <a class="rack-result-word" href="/mot/<?= e($item['slug']) ?>"><?= e($item['normalized']) ?></a>
          <span class="status-badge status-badge--<?= e($rowStatus['modifier']) ?>"><?= e($rowStatus['label']) ?></span>
          <span class="rack-result-points" aria-label="<?= e($item['score']) ?> points"><?= e($item['score']) ?></span>
          <span class="rack-result-length" aria-label="<?= e($item['length']) ?> lettres"><?= e($item['length']) ?></span>
        </li>
<?php endforeach; ?>
      </ul>
    </section>
<?php elseif ($page->total > 0): ?>
    <!-- Page demandee au-dela de la derniere page existante (total > 0 mais
         cette page precise n'a aucune ligne) : message distinct du cas "aucun
         mot" (qui ne s'affiche que lorsque total = 0, voir $statusMeta
         ci-dessus) -- evite une section resultats silencieusement vide. -->
    <p class="help rack-results-note">Aucun mot sur cette page.</p>
<?php endif; ?>

<?php if ($showPagination): ?>
    <nav class="word-nav" aria-label="Pagination">
<?php if ($page->hasPreviousPage): ?>
      <a href="<?= e($pageUrl($page->page - 1)) ?>"<?= $paginationRelFor($page->page - 1) ?>>← Précédent</a>
<?php else: ?>
      <span></span>
<?php endif; ?>
      <span class="help">Page <?= e($page->page) ?></span>
<?php if ($page->hasNextPage): ?>
      <a href="<?= e($pageUrl($page->page + 1)) ?>"<?= $paginationRelFor($page->page + 1) ?>>Suivant →</a>
<?php else: ?>
      <span></span>
<?php endif; ?>
    </nav>
<?php endif; ?>

<?php if ($refine !== null && $refine['byLength'] !== []): ?>
    <section class="explore-group">
      <h2>Filtrer Par Longueur</h2>
      <div class="related-links">
<?php foreach ($refine['byLength'] as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['label']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks !== null): ?>
<?php $lengthLabel = $filters->length . ' Lettre' . ($filters->length > 1 ? 's' : ''); ?>
<?php if ($lengthLinks->byStart !== []): ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Commençant Par</h2>
      <div class="related-links">
<?php foreach ($lengthLinks->byStart as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byEnd !== []): ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Terminant Par</h2>
      <div class="related-links">
<?php foreach ($lengthLinks->byEnd as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byWith !== []): ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Avec</h2>
      <div class="related-links">
<?php foreach ($lengthLinks->byWith as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byPosition !== []): ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Par Position De Lettre</h2>
<?php foreach ($lengthLinks->byPosition as $group): ?>
      <div class="explore-subgroup">
        <p class="explore-subgroup-label"><?= e($group['position']) ?>e Lettre (<?= e(count($group['letters'])) ?>)</p>
        <div class="related-links">
<?php foreach ($group['letters'] as $link): ?>
          <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
        </div>
      </div>
<?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byStartEnd !== []): ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Commençant Et Terminant Par</h2>
<?php foreach ($lengthLinks->byStartEnd as $group): ?>
      <div class="explore-subgroup">
        <p class="explore-subgroup-label">Commençant Par <?= e($group['start']) ?> (<?= e(count($group['letters'])) ?>)</p>
        <div class="related-links">
<?php foreach ($group['letters'] as $link): ?>
          <a href="<?= e($link['url']) ?>"><span class="explore-label">Terminant Par <?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
        </div>
      </div>
<?php endforeach; ?>
    </section>
<?php endif; ?>

    <section class="explore-group">
      <h2>Explorer</h2>
      <div class="related-links">
        <a href="/mots">Toutes Les Longueurs Et Lettres</a>
      </div>
    </section>
<?php endif; ?>

<?php if ($letterCombinedLinks !== null && $letterCombinedLinks->links !== []): ?>
<?php $combinedHeading = $filters->prefix !== null
    ? 'Commençant Par ' . $filters->prefix . ' Et Terminant Par'
    : 'Terminant Par ' . $filters->suffix . ' Et Commençant Par'; ?>
    <section class="explore-group">
      <h2><?= e($combinedHeading) ?></h2>
      <div class="related-links">
<?php foreach ($letterCombinedLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($prefixAvecLinks !== null && $prefixAvecLinks->links !== []): ?>
    <section class="explore-group">
      <h2>Commençant Par <?= e($filters->prefix) ?>, Avec</h2>
      <div class="related-links">
<?php foreach ($prefixAvecLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($prefixExtensionLinks !== null && $prefixExtensionLinks->links !== []): ?>
    <section class="explore-group">
      <h2>Continuer Le Préfixe <?= e($filters->prefix) ?></h2>
      <div class="related-links">
<?php foreach ($prefixExtensionLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['prefix']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($suffixExtensionLinks !== null && $suffixExtensionLinks->links !== []): ?>
    <section class="explore-group">
      <h2>Continuer Le Suffixe <?= e($filters->suffix) ?></h2>
      <div class="related-links">
<?php foreach ($suffixExtensionLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['suffix']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthCombinedLinks !== null && $lengthCombinedLinks->links !== []): ?>
<?php $lengthCombinedHeading = $filters->prefix !== null
    ? 'Commençant Par ' . $filters->prefix . ' Et Terminant Par'
    : 'Terminant Par ' . $filters->suffix . ' Et Commençant Par'; ?>
    <section class="explore-group">
      <h2><?= e($lengthCombinedHeading) ?></h2>
      <div class="related-links">
<?php foreach ($lengthCombinedLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($startEndWithLinks !== null && $startEndWithLinks->links !== []): ?>
    <section class="explore-group">
      <h2>Commençant Par <?= e($filters->prefix) ?> Et Terminant Par <?= e($filters->suffix) ?>, Avec</h2>
      <div class="related-links">
<?php foreach ($startEndWithLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($positionLinks !== null && $positionLinks->links !== []): ?>
<?php
    $positionWithLetter = array_key_first($filters->withLetters);
    $positionLabel = static function (int $position, int $length): string {
        if ($position === 1) {
            return '1re';
        }
        if ($position === $length) {
            return 'Dernière';
        }

        return $position . 'e';
    };
?>
    <section class="explore-group">
      <h2>Position De <?= e($positionWithLetter) ?> Dans Le Mot</h2>
      <div class="related-links">
<?php foreach ($positionLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($positionLabel($link['position'], $filters->length)) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($avecTwoLettersLinks !== null && $avecTwoLettersLinks->links !== []): ?>
<?php $avecFirstLetter = array_key_first($filters->withLetters); ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Avec <?= e($avecFirstLetter) ?> Et</h2>
      <div class="related-links">
<?php foreach ($avecTwoLettersLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($avecThreeLettersLinks !== null && $avecThreeLettersLinks->links !== []): ?>
<?php $avecFirstTwoLetters = array_keys($filters->withLetters); ?>
    <section class="explore-group">
      <h2>Mots De <?= e($lengthLabel) ?> Avec <?= e($avecFirstTwoLetters[0]) ?> <?= e($avecFirstTwoLetters[1]) ?> Et</h2>
      <div class="related-links">
<?php foreach ($avecThreeLettersLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($avecSansLengthLinks !== null && $avecSansLengthLinks->links !== []): ?>
<?php
    $avecSansLetter = array_key_first($filters->withLetters);
    $sansOnlyLetter = $filters->withoutLetters[0];
?>
    <section class="explore-group">
      <h2>Avec <?= e($avecSansLetter) ?> Sans <?= e($sansOnlyLetter) ?>, Par Longueur</h2>
      <div class="related-links">
<?php foreach ($avecSansLengthLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['length']) ?> Lettres</span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

    <form class="inline-check" action="/verifier" method="get">
      <label class="sr-only" for="mot-check">Vérifier un mot</label>
      <input class="field" type="text" id="mot-check" name="mot" maxlength="15" autocomplete="off" spellcheck="false" placeholder="Vérifier un mot">
      <button class="btn btn-primary" type="submit">Vérifier</button>
    </form>
  </article>
</main>

<footer class="footer">
  <div class="word-shell footer-row">
    <span>Outil indépendant d’aide aux jeux de lettres.</span>
    <span class="footer-links"><a href="/mentions-legales">Mentions Légales</a> · <a href="/confidentialite">Confidentialité</a> · <a href="/contact">Contact</a></span>
  </div>
</footer>
</body>
</html>
