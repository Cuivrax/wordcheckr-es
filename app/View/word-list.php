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

// Toggles estado/orden (D-022 ; mots-cles espagnols depuis ES-014, anciennement "statut"/"tri")
// : reconstruit l'URL de chaque variante en repartant du chemin canonique DEBARRASSE de tout
// segment "estado"/"orden" existant (toujours en fin d'ordre canonique, voir WordListFilters),
// puis en rajoutant la variante voulue -- jamais assemble a la main, toujours re-valide par
// WordListFilters::fromPath()->canonicalUrl() comme partout ailleurs sur cette page (memes
// garanties que $pageUrl ci-dessus). Les VALEURS ('admitida'/'no-admitida'/'puntos'/
// 'puntos-descendente') restent celles de WordListFilters::STATUS_VALUES/SORT_VALUES,
// traduites par ES-030 -- voir la note dediee dans WordListFilters ; toute divergence ici
// produirait un fromPath() null, donc un toggle silencieusement absent.
$basePath = $page->canonicalPath;
$baseSegments = $basePath === '' ? [] : explode('/', $basePath);

if (count($baseSegments) >= 2 && $baseSegments[count($baseSegments) - 2] === 'orden') {
    $baseSegments = array_slice($baseSegments, 0, -2);
}

if (count($baseSegments) >= 2 && $baseSegments[count($baseSegments) - 2] === 'estado') {
    $baseSegments = array_slice($baseSegments, 0, -2);
}

$refineUrl = static function (?string $status, ?string $sort) use ($baseSegments): ?string {
    $segments = $baseSegments;

    if ($status !== null) {
        $segments[] = 'estado';
        $segments[] = $status;
    }

    if ($sort !== null) {
        $segments[] = 'orden';
        $segments[] = $sort;
    }

    return WordListFilters::fromPath(implode('/', $segments))?->canonicalUrl();
};

$currentStatus = $filters?->status;
$currentSort = $filters?->sort;

$statusToggles = [
    ['label' => 'Todas', 'url' => $refineUrl(null, $currentSort), 'active' => $currentStatus === null],
    ['label' => 'Admitidas', 'url' => $refineUrl('admitida', $currentSort), 'active' => $currentStatus === 'admitida'],
    ['label' => 'No Admitidas', 'url' => $refineUrl('no-admitida', $currentSort), 'active' => $currentStatus === 'no-admitida'],
];

$sortToggles = $filters !== null && $filters->length !== null
    ? [
        ['label' => 'Alfabético', 'url' => $refineUrl($currentStatus, null), 'active' => $currentSort === null],
        ['label' => 'Puntos Ascendente', 'url' => $refineUrl($currentStatus, 'puntos'), 'active' => $currentSort === 'puntos'],
        ['label' => 'Puntos Descendente', 'url' => $refineUrl($currentStatus, 'puntos-descendente'), 'active' => $currentSort === 'puntos-descendente'],
    ]
    : [];

$pageUrl = static function (int $targetPage) use ($page): string {
    $path = $page->canonicalPath . ($targetPage > 1 ? '/page/' . $targetPage : '');
    $targetFilters = WordListFilters::fromPath($path);

    return $targetFilters?->canonicalUrl() ?? '/palabras';
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

// Titre lisible, ordre canonique impose (docs/05), inchange par ES-014 (seuls les MOTS-CLES
// d'URL ont ete traduits, jamais leur rang) : longueur -> empiezan-por -> contienen ->
// terminan-en -> posicion -> con-letras -> sin -> patron.
//
// Traduction : formulation NOMINALE/PREPOSITIONNELLE (pas "que empieza/empiezan por" a
// verbe conjugue) choisie deliberement ici, alors que "empiezan por"/"terminan en" restent
// la terminologie du site partout ailleurs (URLs ES-004, titres <h2> de maillage plus bas
// dans ce meme fichier, libelles de home.php/word.php). Raison : $descriptor est reutilise
// tel quel dans des phrases au singulier ET au pluriel (voir $statusMeta plus bas, ex. "X
// est l'unique mot..." vs "les N mots...") sans jamais varier lui-meme -- un participe
// francais est invariable ("commençant par" convient aux deux cas), mais l'equivalent
// espagnol a verbe conjugue ("que empieza por" / "que empiezan por") NE l'est PAS et
// desaccorderait la phrase dans un des deux cas. Une formulation prepositionnelle ("con
// inicio en", "con final en") est invariable en nombre, evite ce risque d'accord fautif
// sans reecrire la structure de generation du descriptor.
$titleParts = [];

if ($filters !== null && $filters->length !== null) {
    $titleParts[] = sprintf('de %d letra%s', $filters->length, $filters->length > 1 ? 's' : '');
}

if ($filters !== null && $filters->prefix !== null) {
    $titleParts[] = 'con inicio en ' . $filters->prefix;
}

if ($filters !== null && $filters->contains !== null) {
    $titleParts[] = 'con la secuencia ' . $filters->contains;
}

if ($filters !== null && $filters->suffix !== null) {
    $titleParts[] = 'con final en ' . $filters->suffix;
}

if ($filters !== null && $filters->position !== null) {
    // Position 1 (1ª) n'apparait jamais ici : WordListFilters::fromPath() la collapse
    // toujours vers "commencant" (D-023, evite le contenu duplique) -- seule la forme "Nª"
    // (2ª, 3ª...) est necessaire, meme convention que les personnes de conjugaison (D-018,
    // helpers.php). Ordinal feminin ("posición"/"letra" sont feminins en espagnol), pas
    // masculin.
    $titleParts[] = 'con ' . $filters->positionLetter . ' en la posición ' . $filters->position . 'ª';
}

if ($filters !== null && $filters->withLetters !== []) {
    $withLetters = [];
    foreach ($filters->withLetters as $letter => $count) {
        for ($k = 0; $k < $count; $k++) {
            $withLetters[] = $letter;
        }
    }
    $titleParts[] = 'con ' . implode(', ', $withLetters);
}

if ($filters !== null && $filters->withoutLetters !== []) {
    $titleParts[] = 'sin ' . implode(', ', $filters->withoutLetters);
}

if ($filters !== null && $filters->pattern !== null) {
    $titleParts[] = 'con el patrón ' . $filters->pattern;
}

$descriptor = implode(' ', $titleParts);
// $descriptor reste en minuscules (hors "Palabras") : reutilise tel quel dans les phrases de
// $statusMeta['direct'] ci-dessous ("Hay 5 palabras de 7 letras..."), ou un Title Case serait
// grammaticalement faux en milieu de phrase. $pageTitle (title, breadcrumb, H1) suit la
// convention Title Case du reste du site (M5, audit final) -- mb_convert_case gere
// correctement les mots accentues espagnols (posición -> Posición) et laisse les lettres
// deja en majuscule (A, CION, C--E-) inchangees.
$pageTitle = mb_convert_case(trim('Palabras ' . $descriptor), MB_CASE_TITLE, 'UTF-8');

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

    return implode(', ', $items) . ' y ' . $last;
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
        'badge' => 'Lista Parcial',
        'subtitle' => 'Lista parcial, no exhaustiva.',
        'direct' => $page->total > 0
            ? sprintf(
                'Se %s al menos %d palabra%s %s en la parte examinada. La lista no está garantizada completa más allá de este límite.',
                $page->total > 1 ? 'encontraron' : 'encontró',
                $page->total,
                $page->total > 1 ? 's' : '',
                $descriptor,
            )
            : sprintf(
                'No se encontró ninguna palabra %s en la parte examinada. La lista no está garantizada completa más allá de este límite.',
                $descriptor,
            ),
    ],
    $page->total === 0 => [
        'modifier' => 'unknown',
        'badge' => 'Ninguna Palabra',
        'subtitle' => 'Ninguna palabra encontrada.',
        'direct' => sprintf('No se ha encontrado ninguna palabra %s en la base de datos.', $descriptor),
    ],
    $page->total === 1 => [
        'modifier' => 'admitted',
        'badge' => 'Palabra Encontrada',
        'subtitle' => 'Lista ordenada alfabéticamente.',
        // Meta description enrichie (audit D-031, constat I-3) : cite le mot reel plutot
        // qu'une phrase generique -- donnee deja chargee pour le tableau de resultats,
        // aucune requete supplementaire. Repli sur la phrase generique si $page->items est
        // vide : total = 1 ne garantit PAS $page->items[0] (page demandee au-dela de la
        // derniere page existante, ex. ".../page/2" sur une liste a 1 resultat -- meme cas
        // que "Aucun mot sur cette page." plus bas, jamais suppose absent).
        // Phrase sans ":" (demande produit, 2026-08-24) -- "X est l'unique mot ... admis au
        // Scrabble" plutot que "il y a 1 mot ... : X, admis". "válida de Scrabble" reprend le
        // meme vocabulaire SERP-verifie que app/View/word.php (rapports/es-serp-terminology-
        // research.md, section 2.1), jamais "admitida"/"admis" ici pour rester coherent.
        'direct' => $page->items !== []
            ? sprintf(
                '%s es la única palabra %s, %s.',
                $page->items[0]['normalized'],
                $descriptor,
                $page->items[0]['status'] === TermPage::STATUS_ADMITTED ? 'válida de Scrabble' : 'no válida de Scrabble',
            )
            : sprintf('Hay 1 palabra %s.', $descriptor),
    ],
    $page->total >= 2 && $page->total <= 5 => [
        'modifier' => 'admitted',
        'badge' => 'Palabras Encontradas',
        'subtitle' => 'Lista ordenada alfabéticamente.',
        // Meme correctif I-3 : liste courte entierement contenue dans $page->items (PAGE_SIZE
        // = 50, toujours superieur a 5) SI la page demandee est la premiere -- meme repli que
        // ci-dessus pour une page hors bornes. Enumeration naturelle ("A y B" / "A, B y C"),
        // sans ":" (demande produit, 2026-08-24). Ne dit PAS "válidas de Scrabble" pour
        // l'ensemble : une liste courte peut melanger admis et non admis (ex. commencant/X/
        // terminant/Y), le statut individuel reste dans .status-badge par ligne -- "registradas"
        // reste vrai quel que soit le statut de chaque mot.
        'direct' => $page->items !== []
            ? sprintf(
                '%s son las %d palabras %s registradas en Scrabble.',
                $naturalList(array_map(static fn (array $item): string => (string) $item['normalized'], $page->items)),
                $page->total,
                $descriptor,
            )
            : sprintf('Hay %d palabras %s.', $page->total, $descriptor),
    ],
    default => [
        'modifier' => 'admitted',
        'badge' => 'Palabras Encontradas',
        'subtitle' => 'Lista ordenada alfabéticamente.',
        // Gabarit enrichi (demande produit, 2026-08-24) : mentionne explicitement le Scrabble
        // et les dictionnaires officiels plutot qu'un simple compte brut ("Il y a N mots de
        // X.") -- "dictionnaires officiels" plutot que les sigles ODS8/ODS9 (jargon technique,
        // peu recherche tel quel), coherent avec home.php.
        //
        // Correctif (audit seo-technical-auditor, ES-011 I-4, 2026-08-29) : la formulation
        // d'origine affirmait "admitidas en los diccionarios oficiales del Scrabble", mais
        // $page->total (WordListSolver) compte TOUS les statuts is_french = 1, admis ET non
        // admis (D-006/D-011, meme modele que la fiche mot) -- affirmation fausse des qu'une
        // page de liste mele les deux (ex. commencant/terminant sans filtre statut). Remplace
        // par "registradas en Scrabble", deja le terme neutre etabli plus haut dans ce meme
        // fichier pour exactement la meme raison (cas 2-5 resultats ci-dessus, voir son propre
        // commentaire) -- vrai quel que soit le melange de statuts affiche, y compris quand un
        // filtre statut=admis est actif (rester en retrait plutot que sur-affirmer, jamais
        // l'inverse). Corrige le texte plutot que d'ajouter un compte filtre sur les mots admis
        // uniquement : cela exigerait une requete/donnee supplementaire sur WordListPage
        // (App\Search\, hors perimetre de cette passe), alors que la formulation neutre est
        // deja etablie ailleurs dans ce fichier et ne fausse rien.
        'direct' => sprintf(
            'Descubre las %d palabras %s, registradas en Scrabble. Ordénalas por puntos o recórrelas alfabéticamente.',
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

// Correctif (audit seo-technical-auditor, ES-011 I-6, 2026-08-29) : title/description ne
// variaient jamais selon $page->page -- chaque page /page/N d'une meme liste (deja
// noindex,follow par defaut au registre, D-005) partageait le titre ET la description mot pour
// mot de la page 1, contenu duplique pour tout robot qui les visite malgre tout. Suffixe
// "— Página N" ajoute aux DEUX balises des que $page->page > 1, jamais sur la page 1 (aucun
// changement pour le cas le plus courant). $metaDescription distinct de $statusMeta['direct'] :
// le suffixe ne doit affecter QUE la balise <meta description>, jamais le paragraphe "Respuesta
// Directa" affiche a l'ecran (deja repete par ailleurs dans la pagination visible, <span
// class="help">Página N</span> plus bas) -- $statusMeta['direct'] reste donc utilise tel quel
// pour le rendu visible.
$metaDescription = $statusMeta['direct'];
if ($page->page > 1) {
    $metaTitle .= ' — Página ' . $page->page;
    $metaDescription .= sprintf(' Página %d.', $page->page);
}

// Correctif (audit seo-technical-auditor round 4, ES-018) : le mot reel prefixe
// ($metaTitle ci-dessus) peut faire depasser 60 caracteres une fois le suffixe de marque
// ajoute, notamment sur les pages a 1 resultat des familles longueur+empiezan-por/
// terminan-en (mots longs, ex. "DESENSOBERBECED... - Palabras De 15 Letras Con Final En
// XX | WORD CHECKR"). Retire le suffixe de marque UNIQUEMENT quand la combinaison
// depasserait le budget -- le contenu distinctif (mot + categorie) prime sur la marque,
// jamais l'inverse. N'affecte que ce gabarit precis, pas les autres vues.
$titleSuffix = ' | WORD CHECKR';
if (mb_strlen($metaTitle . $titleSuffix, 'UTF-8') > 60) {
    $titleSuffix = '';
}

// Statut par ligne : memes trois valeurs fermees que la fiche mot (jamais
// STATUS_UNKNOWN ici, voir WordListSolver::toItems()). Texte minimal, a
// confirmer par l'agent microcopy -- meme convention que app/View/word.php.
$rowStatusMeta = static fn (string $status): array => $status === TermPage::STATUS_ADMITTED
    ? ['modifier' => 'admitted', 'label' => 'Admitida']
    : ['modifier' => 'not-admitted', 'label' => 'No Admitida'];

$showPagination = $page->hasPreviousPage || $page->hasNextPage;
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title><?= e($metaTitle . $titleSuffix) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
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
<a class="skip-link" href="#main">Ir al contenido</a>
<header class="header">
  <div class="site header-row">
    <a class="logo" href="/"><img class="logo-mark" src="/assets/img/logo.png" alt="" width="32" height="32">WORD CHECKR</a>
    <nav class="nav" aria-label="Navegación principal"><a href="/">Nueva búsqueda</a></nav>
  </div>
</header>

<main class="word-shell main" id="main">
  <nav class="breadcrumb" aria-label="Migas de pan"><a href="/">Inicio</a> › <?= e($pageTitle) ?></nav>

  <article class="word-card">
    <section class="word-answer">
      <span class="status-badge status-badge--<?= e($statusMeta['modifier']) ?>"><?= e($statusMeta['badge']) ?></span>
      <h1 class="word-title explore-title"><?= e($pageTitle) ?></h1>
      <p><?= e($statusMeta['subtitle']) ?></p>
    </section>

    <section class="direct">
      <h2>Respuesta Directa</h2>
      <p><?= e($statusMeta['direct']) ?></p>
    </section>

    <section class="explore-group refine-toggles">
      <h2>Afinar La Lista</h2>
      <!-- Correctif (audit seo-technical-auditor, ES-011 I-9, 2026-08-29) : rel="nofollow" sur
           les bascules estado/orden, meme traitement et meme raison que la pagination profonde
           ci-dessus ($paginationRelFor) -- ces URL ("/estado/admis", "/orden/points"... mots-cles
           traduits par ES-014, valeurs volontairement inchangees) sont des
           variantes quasi identiques d'une page deja noindex par defaut (D-005), jamais une
           destination de crawl utile en elle-meme. Inconditionnel (pas de plafond de profondeur
           ici, contrairement a la pagination) : il n'y a que 2-3 variantes par groupe, pas une
           chaine profonde -- y compris le toggle actif (aria-current), lien vers soi-meme, ne
           necessite pas de faire suivre son propre poids de lien. -->
      <div class="related-links" role="group" aria-label="Filtrar por estado">
<?php foreach ($statusToggles as $toggle): ?>
<?php if ($toggle['url'] !== null): ?>
        <a href="<?= e($toggle['url']) ?>" rel="nofollow"<?= $toggle['active'] ? ' aria-current="page"' : '' ?>><?= e($toggle['label']) ?></a>
<?php endif; ?>
<?php endforeach; ?>
      </div>
<?php if ($sortToggles !== []): ?>
      <div class="related-links" role="group" aria-label="Ordenar la lista">
<?php foreach ($sortToggles as $toggle): ?>
<?php if ($toggle['url'] !== null): ?>
        <a href="<?= e($toggle['url']) ?>" rel="nofollow"<?= $toggle['active'] ? ' aria-current="page"' : '' ?>><?= e($toggle['label']) ?></a>
<?php endif; ?>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </section>

<?php if ($page->items !== []): ?>
    <section class="rack-results">
<?php if ($page->truncated): ?>
      <p class="help rack-results-note">Resultados encontrados en una ventana acotada, no exhaustivos más allá de este límite.</p>
<?php endif; ?>
      <div class="rack-result-head" aria-hidden="true">
        <span>Palabra</span><span class="rack-result-head-center">Estado</span><span class="rack-result-head-right">Puntos</span><span class="rack-result-head-length">Letras</span>
      </div>
      <ul class="rack-result-list">
<?php foreach ($page->items as $item): ?>
<?php $rowStatus = $rowStatusMeta($item['status']); ?>
        <li class="rack-result-row">
          <a class="rack-result-word" href="/palabra/<?= e($item['slug']) ?>"><?= e($item['normalized']) ?></a>
          <span class="status-badge status-badge--<?= e($rowStatus['modifier']) ?>"><?= e($rowStatus['label']) ?></span>
          <span class="rack-result-points" aria-label="<?= e($item['score']) ?> puntos"><?= e($item['score']) ?></span>
          <span class="rack-result-length" aria-label="<?= e($item['length']) ?> letras"><?= e($item['length']) ?></span>
        </li>
<?php endforeach; ?>
      </ul>
    </section>
<?php elseif ($page->total > 0): ?>
    <!-- Page demandee au-dela de la derniere page existante (total > 0 mais
         cette page precise n'a aucune ligne) : message distinct du cas "aucun
         mot" (qui ne s'affiche que lorsque total = 0, voir $statusMeta
         ci-dessus) -- evite une section resultats silencieusement vide. -->
    <p class="help rack-results-note">Ninguna palabra en esta página.</p>
<?php endif; ?>

<?php if ($showPagination): ?>
    <nav class="word-nav" aria-label="Paginación">
<?php if ($page->hasPreviousPage): ?>
      <a href="<?= e($pageUrl($page->page - 1)) ?>"<?= $paginationRelFor($page->page - 1) ?>>← Anterior</a>
<?php else: ?>
      <span></span>
<?php endif; ?>
      <span class="help">Página <?= e($page->page) ?></span>
<?php if ($page->hasNextPage): ?>
      <a href="<?= e($pageUrl($page->page + 1)) ?>"<?= $paginationRelFor($page->page + 1) ?>>Siguiente →</a>
<?php else: ?>
      <span></span>
<?php endif; ?>
    </nav>
<?php endif; ?>

<?php if ($refine !== null && $refine['byLength'] !== []): ?>
    <section class="explore-group">
      <h2>Filtrar Por Longitud</h2>
      <div class="related-links">
<?php foreach ($refine['byLength'] as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['label']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks !== null): ?>
<?php $lengthLabel = $filters->length . ' Letra' . ($filters->length > 1 ? 's' : ''); ?>
<?php if ($lengthLinks->byStart !== []): ?>
    <section class="explore-group">
      <h2>Palabras De <?= e($lengthLabel) ?> Que Empiezan Por</h2>
      <div class="related-links">
<?php foreach ($lengthLinks->byStart as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byEnd !== []): ?>
    <section class="explore-group">
      <h2>Palabras De <?= e($lengthLabel) ?> Que Terminan En</h2>
      <div class="related-links">
<?php foreach ($lengthLinks->byEnd as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byWith !== []): ?>
    <section class="explore-group">
      <h2>Palabras De <?= e($lengthLabel) ?> Con</h2>
      <div class="related-links">
<?php foreach ($lengthLinks->byWith as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthLinks->byPosition !== []): ?>
    <section class="explore-group">
      <h2>Palabras De <?= e($lengthLabel) ?> Por Posición De Letra</h2>
<?php foreach ($lengthLinks->byPosition as $group): ?>
      <div class="explore-subgroup">
        <p class="explore-subgroup-label"><?= e($group['position']) ?>ª Letra (<?= e(count($group['letters'])) ?>)</p>
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
      <h2>Palabras De <?= e($lengthLabel) ?> Que Empiezan Y Terminan Por</h2>
<?php foreach ($lengthLinks->byStartEnd as $group): ?>
      <div class="explore-subgroup">
        <p class="explore-subgroup-label">Empiezan Por <?= e($group['start']) ?> (<?= e(count($group['letters'])) ?>)</p>
        <div class="related-links">
<?php foreach ($group['letters'] as $link): ?>
          <a href="<?= e($link['url']) ?>"><span class="explore-label">Terminan En <?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
        </div>
      </div>
<?php endforeach; ?>
    </section>
<?php endif; ?>

    <section class="explore-group">
      <h2>Explorar</h2>
      <div class="related-links">
        <a href="/palabras">Todas Las Longitudes Y Letras</a>
      </div>
    </section>
<?php endif; ?>

<?php if ($letterCombinedLinks !== null && $letterCombinedLinks->links !== []): ?>
<?php $combinedHeading = $filters->prefix !== null
    ? 'Empiezan Por ' . $filters->prefix . ' Y Terminan En'
    : 'Terminan En ' . $filters->suffix . ' Y Empiezan Por'; ?>
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
      <h2>Empiezan Por <?= e($filters->prefix) ?>, Con</h2>
      <div class="related-links">
<?php foreach ($prefixAvecLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['letter']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($prefixExtensionLinks !== null && $prefixExtensionLinks->links !== []): ?>
    <section class="explore-group">
      <h2>Continuar El Prefijo <?= e($filters->prefix) ?></h2>
      <div class="related-links">
<?php foreach ($prefixExtensionLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['prefix']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($suffixExtensionLinks !== null && $suffixExtensionLinks->links !== []): ?>
    <section class="explore-group">
      <h2>Continuar El Sufijo <?= e($filters->suffix) ?></h2>
      <div class="related-links">
<?php foreach ($suffixExtensionLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['suffix']) ?></span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

<?php if ($lengthCombinedLinks !== null && $lengthCombinedLinks->links !== []): ?>
<?php $lengthCombinedHeading = $filters->prefix !== null
    ? 'Empiezan Por ' . $filters->prefix . ' Y Terminan En'
    : 'Terminan En ' . $filters->suffix . ' Y Empiezan Por'; ?>
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
      <h2>Empiezan Por <?= e($filters->prefix) ?> Y Terminan En <?= e($filters->suffix) ?>, Con</h2>
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
            return '1.ª';
        }
        if ($position === $length) {
            return 'Última';
        }

        return $position . 'ª';
    };
?>
    <section class="explore-group">
      <h2>Posición De <?= e($positionWithLetter) ?> En La Palabra</h2>
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
      <h2>Palabras De <?= e($lengthLabel) ?> Con <?= e($avecFirstLetter) ?> Y</h2>
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
      <h2>Palabras De <?= e($lengthLabel) ?> Con <?= e($avecFirstTwoLetters[0]) ?> <?= e($avecFirstTwoLetters[1]) ?> Y</h2>
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
      <h2>Con <?= e($avecSansLetter) ?> Sin <?= e($sansOnlyLetter) ?>, Por Longitud</h2>
      <div class="related-links">
<?php foreach ($avecSansLengthLinks->links as $link): ?>
        <a href="<?= e($link['url']) ?>"><span class="explore-label"><?= e($link['length']) ?> Letras</span> <span class="explore-count">(<?= e(number_format($link['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

    <form class="inline-check" action="/verificar" method="get">
      <label class="sr-only" for="palabra-check">Verificar una palabra</label>
      <input class="field" type="text" id="palabra-check" name="palabra" maxlength="15" autocomplete="off" spellcheck="false" placeholder="Verificar una palabra">
      <button class="btn btn-primary" type="submit">Verificar</button>
    </form>
  </article>
</main>

<footer class="footer">
  <div class="word-shell footer-row">
    <span>Herramienta independiente de ayuda para los juegos de letras.</span>
    <span class="footer-links"><a href="/aviso-legal">Aviso Legal</a> · <a href="/privacidad">Privacidad</a> · <a href="/contact">Contacto</a></span>
  </div>
</footer>
</body>
</html>
