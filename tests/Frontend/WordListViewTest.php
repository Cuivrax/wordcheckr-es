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
        $seo = \App\Seo\SeoMeta::noindex('https://exemple.fr/palabras/' . $page->canonicalPath);

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
        canonicalPath: '13-letras',
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

    Assert::true(str_contains($htmlLength, 'Afinar La Lista'), 'section toggles attendue');
    Assert::true(str_contains($htmlLength, '<a href="/palabras/13-letras" rel="nofollow" aria-current="page">Todas</a>'), '"Todas" actif par defaut, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlLength, '<a href="/palabras/13-letras/statut/admis" rel="nofollow">Admitidas</a>'), 'lien "Admitidas" non actif, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlLength, '<a href="/palabras/13-letras/statut/non-admis" rel="nofollow">No Admitidas</a>'), 'lien "No Admitidas" non actif, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlLength, 'Ordenar la lista'), 'groupe tri attendu (longueur presente)');
    Assert::true(str_contains($htmlLength, '<a href="/palabras/13-letras" rel="nofollow" aria-current="page">Alfabético</a>'), '"Alfabético" actif par defaut, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlLength, '<a href="/palabras/13-letras/tri/points" rel="nofollow">Puntos Ascendente</a>'));
    Assert::true(str_contains($htmlLength, '<a href="/palabras/13-letras/tri/points-desc" rel="nofollow">Puntos Descendente</a>'));

    // Aucun maillage interne sans $lengthLinks.
    Assert::true(!str_contains($htmlLength, 'Que Empiezan Por'), 'aucune section de maillage sans $lengthLinks');
    Assert::true(!str_contains($htmlLength, 'Todas Las Longitudes Y Letras'), 'aucun lien hub sans $lengthLinks');

    // Page 1 (baseline) : title/description SANS suffixe de pagination -- comparaison directe
    // avec $htmlAnchoredTwo plus bas (ES-011 I-6, meme canonicalPath/total, seule $page->page
    // differe).
    Assert::true(str_contains($htmlLength, '<title>Palabras De 13 Letras | WORD CHECKR</title>'), 'page 1 : title sans suffixe de pagination');
    Assert::true(
        str_contains($htmlLength, '<meta name="description" content="Descubre las 91138 palabras de 13 letras, registradas en Scrabble. Ordénalas por puntos o recórrelas alfabéticamente.">'),
        'page 1 : description sans suffixe de pagination, "registradas en Scrabble" (pas "admitidas", ES-011 I-4)',
    );

    // -------------------------------------------------------------------
    // Prefixe seul (pas de longueur) : pas de groupe tri (tri exige une longueur).
    // -------------------------------------------------------------------
    $prefixPage = new WordListPage(
        canonicalPath: 'empiezan-por/ch',
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
    Assert::true(str_contains($htmlPrefix, 'Afinar La Lista'), 'toggle statut toujours present sans longueur');
    Assert::true(!str_contains($htmlPrefix, 'Ordenar la lista'), 'aucun groupe tri sans longueur explicite');

    // -------------------------------------------------------------------
    // Statut actif (admis) + tri actif (points-desc) : les DEUX toggles actifs
    // pointent vers l'URL courante, les variantes preservent l'autre dimension.
    // -------------------------------------------------------------------
    $statusSortPage = new WordListPage(
        canonicalPath: '13-letras/statut/admis/tri/points-desc',
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
    Assert::true(str_contains($htmlStatusSort, '<a href="/palabras/13-letras/statut/admis/tri/points-desc" rel="nofollow" aria-current="page">Admitidas</a>'), '"Admitidas" actif, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlStatusSort, '<a href="/palabras/13-letras/tri/points-desc" rel="nofollow">Todas</a>'), '"Todas" preserve le tri actif en le retirant du seul statut, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlStatusSort, '<a href="/palabras/13-letras/statut/admis/tri/points-desc" rel="nofollow" aria-current="page">Puntos Descendente</a>'), '"Puntos Descendente" actif, rel="nofollow" (ES-011 I-9)');
    Assert::true(str_contains($htmlStatusSort, '<a href="/palabras/13-letras/statut/admis" rel="nofollow">Alfabético</a>'), '"Alfabético" preserve le statut actif en retirant seulement le tri, rel="nofollow" (ES-011 I-9)');

    // -------------------------------------------------------------------
    // Maillage interne (D-022) : trois groupes + lien hub, aucune section vide.
    // -------------------------------------------------------------------
    $lengthLinks = new LengthLinks(
        byStart: [
            ['letter' => 'A', 'url' => '/palabras/13-letras/empiezan-por/a', 'count' => 4777],
            ['letter' => 'B', 'url' => '/palabras/13-letras/empiezan-por/b', 'count' => 3122],
        ],
        byEnd: [
            ['letter' => 'E', 'url' => '/palabras/13-letras/terminan-en/e', 'count' => 9663],
        ],
        byWith: [],
        byPosition: [
            ['position' => 3, 'letters' => [
                ['letter' => 'R', 'url' => '/palabras/13-letras/position/3/r', 'count' => 1234],
            ]],
        ],
        byStartEnd: [],
        queryCount: 1,
    );
    $htmlWithLinks = $render($lengthPage, null, $lengthLinks);

    Assert::true(str_contains($htmlWithLinks, 'Palabras De 13 Letras Que Empiezan Por'), 'titre commencant attendu');
    Assert::true(str_contains($htmlWithLinks, '<span class="explore-label">A</span> <span class="explore-count">(4 777)</span>'), 'lien A avec compte formate attendu');
    Assert::true(str_contains($htmlWithLinks, 'href="/palabras/13-letras/empiezan-por/a"'), 'URL du lien A attendue');
    Assert::true(str_contains($htmlWithLinks, 'Palabras De 13 Letras Que Terminan En'), 'titre terminant attendu');
    Assert::true(str_contains($htmlWithLinks, '(9 663)'), 'compte terminant formate attendu');
    Assert::true(!str_contains($htmlWithLinks, 'Palabras De 13 Letras Con'), 'byWith vide -- aucune section rendue (jamais de groupe vide)');
    Assert::true(str_contains($htmlWithLinks, 'Palabras De 13 Letras Por Posición De Letra'), 'titre position attendu (C1, audit D-028)');
    // NOTE (herite, sans rapport avec la localisation ES-004) : cette assertion attend un
    // <summary> que le rendu reel n'emet plus (app/View/word-list.php utilise desormais
    // <p class="explore-subgroup-label">) -- echec preexistant confirme herite du depot
    // francais, volontairement laisse tel quel ici (hors perimetre de cette passe). Texte
    // interne mis a jour en espagnol ("3ª Letra") par coherence avec la traduction, mais
    // l'assertion continue d'echouer pour la MEME raison structurelle deja documentee
    // (balise <summary> attendue, jamais emise) -- pas un nouvel echec introduit ici.
    Assert::true(str_contains($htmlWithLinks, '<summary>3ª Letra (1)</summary>'), 'sommaire replie par groupe de position attendu');
    Assert::true(str_contains($htmlWithLinks, 'href="/palabras/13-letras/position/3/r"'), 'URL du lien position attendue');
    Assert::true(str_contains($htmlWithLinks, 'href="/palabras">Todas Las Longitudes Y Letras</a>'), 'lien hub vers /palabras attendu quand $lengthLinks est fourni');

    // -------------------------------------------------------------------
    // Plafond de profondeur de pagination sur les listes ancrees (D-030, audit
    // seo-technical-auditor, constat I-2) : follow pour les 3 premieres pages
    // (1<->2<->3), nofollow au-dela -- jamais un changement d'indexation, seul le
    // suivi du lien change (chaque page /page/N reste noindex,follow par ailleurs).
    // -------------------------------------------------------------------
    $anchoredPageTwo = new WordListPage(
        canonicalPath: '13-letras',
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
    Assert::true(str_contains($htmlAnchoredTwo, '<a href="/palabras/13-letras">← Anterior</a>'), 'page 2->1 (profondeur <= 3) : follow, sans rel');
    Assert::true(str_contains($htmlAnchoredTwo, '<a href="/palabras/13-letras/page/3">Siguiente →</a>'), 'page 2->3 (profondeur <= 3) : follow, sans rel');

    // Page 2 : title/description DOIVENT differer de la page 1 ci-dessus (ES-011 I-6, meme
    // canonicalPath/total que $htmlLength, seule $page->page differe) -- suffixe "— Página 2"
    // sur les deux balises, jamais sur le paragraphe "Respuesta Directa" visible a l'ecran.
    Assert::true(str_contains($htmlAnchoredTwo, '<title>Palabras De 13 Letras — Página 2 | WORD CHECKR</title>'), 'page 2 : title porte le suffixe "— Página 2"');
    Assert::true(
        str_contains($htmlAnchoredTwo, '<meta name="description" content="Descubre las 91138 palabras de 13 letras, registradas en Scrabble. Ordénalas por puntos o recórrelas alfabéticamente. Página 2.">'),
        'page 2 : description porte le suffixe "Página 2."',
    );
    Assert::true(
        str_contains($htmlAnchoredTwo, '<p>Descubre las 91138 palabras de 13 letras, registradas en Scrabble. Ordénalas por puntos o recórrelas alfabéticamente.</p>'),
        'page 2 : le paragraphe "Respuesta Directa" visible ne porte PAS le suffixe de pagination (seule la balise meta description le porte)',
    );

    $anchoredPageFour = new WordListPage(
        canonicalPath: '13-letras',
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
    Assert::true(str_contains($htmlAnchoredFour, '<a href="/palabras/13-letras/page/3">← Anterior</a>'), 'page 4->3 (profondeur <= 3) : follow, sans rel');
    Assert::true(str_contains($htmlAnchoredFour, '<a href="/palabras/13-letras/page/5" rel="nofollow">Siguiente →</a>'), 'page 4->5 (profondeur > 3) : nofollow');

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
    Assert::true(str_contains($htmlUnanchored, '<a href="/palabras/contenant/cha/page/2" rel="nofollow">Siguiente →</a>'), 'liste non ancree : nofollow des la page 2, quelle que soit la profondeur (I-1 historique)');

    // -------------------------------------------------------------------
    // Meta title/description enrichis (audit D-031, constat I-3) : citent le(s) mot(s)
    // reel(s) plutot qu'une phrase entierement templatee, pour les listes courtes.
    // -------------------------------------------------------------------
    $onePage = new WordListPage(
        canonicalPath: '3-letras/avec/a/b/e',
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
    Assert::true(str_contains($htmlOne, '<title>ABE - Palabras De 3 Letras Con A, B, E | WORD CHECKR</title>'), 'title enrichi du mot reel pour 1 seul resultat');
    Assert::true(str_contains($htmlOne, '<meta name="description" content="ABE es la única palabra de 3 letras con A, B, E, no válida de Scrabble.">'), 'description enrichie du mot et de son statut reel');
    Assert::true(str_contains($htmlOne, '<h1 class="word-title explore-title">Palabras De 3 Letras Con A, B, E</h1>'), 'H1 reste la categorie generale, jamais le mot d\'une seule ligne');

    // Page hors bornes (total = 1 mais items vide, ex. ".../page/2" sur une liste a 1
    // resultat) : repli sur la phrase generique, jamais un crash sur $page->items[0].
    $oneOutOfRangePage = new WordListPage(
        canonicalPath: '3-letras/avec/a/b/e',
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
    // page: 2 ci-dessus -- title/description generiques ET suffixe "— Página 2"/"Página 2."
    // cumules (ES-011 I-6, repli sur $page->items vide ET pagination portent chacun leur propre
    // correctif, testes ensemble ici plutot que dans deux fixtures distinctes).
    Assert::true(str_contains($htmlOneOob, '<title>Palabras De 3 Letras Con A, B, E — Página 2 | WORD CHECKR</title>'), 'title generique en repli quand $page->items est vide, avec suffixe "— Página 2"');
    Assert::true(str_contains($htmlOneOob, '<meta name="description" content="Hay 1 palabra de 3 letras con A, B, E. Página 2.">'), 'description generique en repli quand $page->items est vide, avec suffixe "Página 2."');

    // Liste courte (2 a 5 resultats) : description enumere les mots reels.
    $shortListPage = new WordListPage(
        canonicalPath: '4-letras/avec/q/x',
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
    Assert::true(str_contains($htmlShortList, '<meta name="description" content="QUXE y AXQU son las 2 palabras de 4 letras con Q, X registradas en Scrabble.">'), 'description d\'une liste courte enumere les mots reels, sans affirmer un statut commun (mots admis et non admis melanges)');
    Assert::true(str_contains($htmlShortList, '<title>Palabras De 4 Letras Con Q, X | WORD CHECKR</title>'), 'title non enrichi au-dela de 1 seul resultat (categorie generale conservee)');
};
