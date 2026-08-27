<?php

declare(strict_types=1);

use App\Search\LengthLinks;
use App\Search\WordListPage;
use Tests\Support\Assert;

/**
 * Rend app/View/word-list.php avec des paniers synthetiques (aucun serveur HTTP, aucune base
 * de donnees -- WordListFilters::fromPath() utilise par la vue est un parsing pur, meme
 * principe que WordViewTest.php pour app/View/word.php) :
 * - toggles statut/tri (D-022) : "Alphabétique"/"Tous" actifs par defaut, tri masque sans
 *   longueur explicite, URL de chaque variante correcte et re-canonicalisee ;
 * - maillage interne (D-022, App\Search\LengthLinks) : section absente si $lengthLinks est
 *   null, presente avec les bons libelles/URLs sinon, jamais de section vide.
 */
return function (): void {
    require __DIR__ . '/../../app/bootstrap.php';

    $render = static function (WordListPage $page, ?array $refine = null, ?LengthLinks $lengthLinks = null): string {
        $seo = \App\Seo\SeoMeta::noindex('https://exemple.fr/mots/' . $page->canonicalPath);

        ob_start();
        (static function (WordListPage $page, ?array $refine, ?LengthLinks $lengthLinks, \App\Seo\SeoMeta $seo): void {
            require __DIR__ . '/../../app/View/word-list.php';
        })($page, $refine, $lengthLinks, $seo);

        return (string) ob_get_clean();
    };

    $item = static function (string $normalized, string $status = 'admitted'): array {
        return [
            'normalized' => $normalized,
            'slug' => strtolower($normalized),
            'score' => 10,
            'length' => strlen($normalized),
            'isOds8' => $status === 'admitted',
            'isOds9' => $status === 'admitted',
            'status' => $status,
        ];
    };

    // -------------------------------------------------------------------
    // Longueur seule, sans statut/tri actif : toggles "Tous"/"Alphabétique"
    // actifs par defaut, tri visible (longueur presente).
    // -------------------------------------------------------------------
    $lengthPage = new WordListPage(
        canonicalPath: '13-lettres',
        page: 1,
        pageSize: 50,
        items: [$item('ABACTERIENNES', 'french_not_admitted')],
        total: 91138,
        exact: true,
        truncated: false,
        hasNextPage: true,
        hasPreviousPage: false,
        queryCount: 2,
    );
    $htmlLength = $render($lengthPage);

    Assert::true(str_contains($htmlLength, 'Affiner La Liste'), 'section toggles attendue');
    Assert::true(str_contains($htmlLength, '<a href="/mots/13-lettres" aria-current="page">Tous</a>'), '"Tous" actif par defaut');
    Assert::true(str_contains($htmlLength, '<a href="/mots/13-lettres/statut/admis">Admis</a>'), 'lien "Admis" non actif');
    Assert::true(str_contains($htmlLength, '<a href="/mots/13-lettres/statut/non-admis">Non Admis</a>'), 'lien "Non Admis" non actif');
    Assert::true(str_contains($htmlLength, 'Trier la liste'), 'groupe tri attendu (longueur presente)');
    Assert::true(str_contains($htmlLength, '<a href="/mots/13-lettres" aria-current="page">Alphabétique</a>'), '"Alphabétique" actif par defaut');
    Assert::true(str_contains($htmlLength, '<a href="/mots/13-lettres/tri/points">Points Croissants</a>'));
    Assert::true(str_contains($htmlLength, '<a href="/mots/13-lettres/tri/points-desc">Points Décroissants</a>'));

    // Aucun maillage interne sans $lengthLinks.
    Assert::true(!str_contains($htmlLength, 'Commençant Par'), 'aucune section de maillage sans $lengthLinks');
    Assert::true(!str_contains($htmlLength, 'Toutes Les Longueurs Et Lettres'), 'aucun lien hub sans $lengthLinks');

    // -------------------------------------------------------------------
    // Prefixe seul (pas de longueur) : pas de groupe tri (tri exige une longueur).
    // -------------------------------------------------------------------
    $prefixPage = new WordListPage(
        canonicalPath: 'commencant/ch',
        page: 1,
        pageSize: 50,
        items: [$item('CHAT')],
        total: 12037,
        exact: true,
        truncated: false,
        hasNextPage: true,
        hasPreviousPage: false,
        queryCount: 2,
    );
    $htmlPrefix = $render($prefixPage);
    Assert::true(str_contains($htmlPrefix, 'Affiner La Liste'), 'toggle statut toujours present sans longueur');
    Assert::true(!str_contains($htmlPrefix, 'Trier la liste'), 'aucun groupe tri sans longueur explicite');

    // -------------------------------------------------------------------
    // Statut actif (admis) + tri actif (points-desc) : les DEUX toggles actifs
    // pointent vers l'URL courante, les variantes preservent l'autre dimension.
    // -------------------------------------------------------------------
    $statusSortPage = new WordListPage(
        canonicalPath: '13-lettres/statut/admis/tri/points-desc',
        page: 1,
        pageSize: 50,
        items: [$item('ABACTERIENNES')],
        total: 32987,
        exact: true,
        truncated: false,
        hasNextPage: true,
        hasPreviousPage: false,
        queryCount: 2,
    );
    $htmlStatusSort = $render($statusSortPage);
    Assert::true(str_contains($htmlStatusSort, '<a href="/mots/13-lettres/statut/admis/tri/points-desc" aria-current="page">Admis</a>'), '"Admis" actif');
    Assert::true(str_contains($htmlStatusSort, '<a href="/mots/13-lettres/tri/points-desc">Tous</a>'), '"Tous" preserve le tri actif en le retirant du seul statut');
    Assert::true(str_contains($htmlStatusSort, '<a href="/mots/13-lettres/statut/admis/tri/points-desc" aria-current="page">Points Décroissants</a>'), '"Points Décroissants" actif');
    Assert::true(str_contains($htmlStatusSort, '<a href="/mots/13-lettres/statut/admis">Alphabétique</a>'), '"Alphabétique" preserve le statut actif en retirant seulement le tri');

    // -------------------------------------------------------------------
    // Maillage interne (D-022) : trois groupes + lien hub, aucune section vide.
    // -------------------------------------------------------------------
    $lengthLinks = new LengthLinks(
        byStart: [
            ['letter' => 'A', 'url' => '/mots/13-lettres/commencant/a', 'count' => 4777],
            ['letter' => 'B', 'url' => '/mots/13-lettres/commencant/b', 'count' => 3122],
        ],
        byEnd: [
            ['letter' => 'E', 'url' => '/mots/13-lettres/terminant/e', 'count' => 9663],
        ],
        byWith: [],
        byPosition: [
            ['position' => 3, 'letters' => [
                ['letter' => 'R', 'url' => '/mots/13-lettres/position/3/r', 'count' => 1234],
            ]],
        ],
        byStartEnd: [],
        queryCount: 1,
    );
    $htmlWithLinks = $render($lengthPage, null, $lengthLinks);

    Assert::true(str_contains($htmlWithLinks, 'Mots De 13 Lettres Commençant Par'), 'titre commencant attendu');
    Assert::true(str_contains($htmlWithLinks, '<span class="explore-label">A</span> <span class="explore-count">(4 777)</span>'), 'lien A avec compte formate attendu');
    Assert::true(str_contains($htmlWithLinks, 'href="/mots/13-lettres/commencant/a"'), 'URL du lien A attendue');
    Assert::true(str_contains($htmlWithLinks, 'Mots De 13 Lettres Terminant Par'), 'titre terminant attendu');
    Assert::true(str_contains($htmlWithLinks, '(9 663)'), 'compte terminant formate attendu');
    Assert::true(!str_contains($htmlWithLinks, 'Mots De 13 Lettres Avec'), 'byWith vide -- aucune section rendue (jamais de groupe vide)');
    Assert::true(str_contains($htmlWithLinks, 'Mots De 13 Lettres Par Position De Lettre'), 'titre position attendu (C1, audit D-028)');
    Assert::true(str_contains($htmlWithLinks, '<summary>3e Lettre (1)</summary>'), 'sommaire replie par groupe de position attendu');
    Assert::true(str_contains($htmlWithLinks, 'href="/mots/13-lettres/position/3/r"'), 'URL du lien position attendue');
    Assert::true(str_contains($htmlWithLinks, 'href="/mots">Toutes Les Longueurs Et Lettres</a>'), 'lien hub vers /mots attendu quand $lengthLinks est fourni');

    // -------------------------------------------------------------------
    // Plafond de profondeur de pagination sur les listes ancrees (D-030, audit
    // seo-technical-auditor, constat I-2) : follow pour les 3 premieres pages
    // (1<->2<->3), nofollow au-dela -- jamais un changement d'indexation, seul le
    // suivi du lien change (chaque page /page/N reste noindex,follow par ailleurs).
    // -------------------------------------------------------------------
    $anchoredPageTwo = new WordListPage(
        canonicalPath: '13-lettres',
        page: 2,
        pageSize: 50,
        items: [$item('ABACTERIENNES', 'french_not_admitted')],
        total: 91138,
        exact: true,
        truncated: false,
        hasNextPage: true,
        hasPreviousPage: true,
        queryCount: 2,
    );
    $htmlAnchoredTwo = $render($anchoredPageTwo);
    Assert::true(str_contains($htmlAnchoredTwo, '<a href="/mots/13-lettres">← Précédent</a>'), 'page 2->1 (profondeur <= 3) : follow, sans rel');
    Assert::true(str_contains($htmlAnchoredTwo, '<a href="/mots/13-lettres/page/3">Suivant →</a>'), 'page 2->3 (profondeur <= 3) : follow, sans rel');

    $anchoredPageFour = new WordListPage(
        canonicalPath: '13-lettres',
        page: 4,
        pageSize: 50,
        items: [$item('ABACTERIENNES', 'french_not_admitted')],
        total: 91138,
        exact: true,
        truncated: false,
        hasNextPage: true,
        hasPreviousPage: true,
        queryCount: 2,
    );
    $htmlAnchoredFour = $render($anchoredPageFour);
    Assert::true(str_contains($htmlAnchoredFour, '<a href="/mots/13-lettres/page/3">← Précédent</a>'), 'page 4->3 (profondeur <= 3) : follow, sans rel');
    Assert::true(str_contains($htmlAnchoredFour, '<a href="/mots/13-lettres/page/5" rel="nofollow">Suivant →</a>'), 'page 4->5 (profondeur > 3) : nofollow');

    $unanchoredPage = new WordListPage(
        canonicalPath: 'contenant/cha',
        page: 1,
        pageSize: 50,
        items: [$item('CHAT')],
        total: 3000,
        exact: false,
        truncated: false,
        hasNextPage: true,
        hasPreviousPage: false,
        queryCount: 1,
    );
    $htmlUnanchored = $render($unanchoredPage);
    Assert::true(str_contains($htmlUnanchored, '<a href="/mots/contenant/cha/page/2" rel="nofollow">Suivant →</a>'), 'liste non ancree : nofollow des la page 2, quelle que soit la profondeur (I-1 historique)');

    // -------------------------------------------------------------------
    // Meta title/description enrichis (audit D-031, constat I-3) : citent le(s) mot(s)
    // reel(s) plutot qu'une phrase entierement templatee, pour les listes courtes.
    // -------------------------------------------------------------------
    $onePage = new WordListPage(
        canonicalPath: '3-lettres/avec/a/b/e',
        page: 1,
        pageSize: 50,
        items: [$item('ABE', 'french_not_admitted')],
        total: 1,
        exact: true,
        truncated: false,
        hasNextPage: false,
        hasPreviousPage: false,
        queryCount: 1,
    );
    $htmlOne = $render($onePage);
    Assert::true(str_contains($htmlOne, '<title>ABE - Mots De 3 Lettres Avec A, B, E | WORD CHECKR</title>'), 'title enrichi du mot reel pour 1 seul resultat');
    Assert::true(str_contains($htmlOne, '<meta name="description" content="ABE est l’unique mot de 3 lettres avec A, B, E, non admis au Scrabble.">'), 'description enrichie du mot et de son statut reel');
    Assert::true(str_contains($htmlOne, '<h1 class="word-title explore-title">Mots De 3 Lettres Avec A, B, E</h1>'), 'H1 reste la categorie generale, jamais le mot d\'une seule ligne');

    // Page hors bornes (total = 1 mais items vide, ex. ".../page/2" sur une liste a 1
    // resultat) : repli sur la phrase generique, jamais un crash sur $page->items[0].
    $oneOutOfRangePage = new WordListPage(
        canonicalPath: '3-lettres/avec/a/b/e',
        page: 2,
        pageSize: 50,
        items: [],
        total: 1,
        exact: true,
        truncated: false,
        hasNextPage: false,
        hasPreviousPage: true,
        queryCount: 1,
    );
    $htmlOneOob = $render($oneOutOfRangePage);
    Assert::true(str_contains($htmlOneOob, '<title>Mots De 3 Lettres Avec A, B, E | WORD CHECKR</title>'), 'title generique en repli quand $page->items est vide');
    Assert::true(str_contains($htmlOneOob, '<meta name="description" content="Il y a 1 mot de 3 lettres avec A, B, E.">'), 'description generique en repli quand $page->items est vide');

    // Liste courte (2 a 5 resultats) : description enumere les mots reels.
    $shortListPage = new WordListPage(
        canonicalPath: '4-lettres/avec/q/x',
        page: 1,
        pageSize: 50,
        items: [$item('QUXE'), $item('AXQU', 'french_not_admitted')],
        total: 2,
        exact: true,
        truncated: false,
        hasNextPage: false,
        hasPreviousPage: false,
        queryCount: 1,
    );
    $htmlShortList = $render($shortListPage);
    Assert::true(str_contains($htmlShortList, '<meta name="description" content="QUXE et AXQU sont les 2 mots de 4 lettres avec Q, X recensés au Scrabble.">'), 'description d\'une liste courte enumere les mots reels, sans affirmer un statut commun (mots admis et non admis melanges)');
    Assert::true(str_contains($htmlShortList, '<title>Mots De 4 Lettres Avec Q, X | WORD CHECKR</title>'), 'title non enrichi au-dela de 1 seul resultat (categorie generale conservee)');
};
