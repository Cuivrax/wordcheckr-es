<?php

declare(strict_types=1);

use App\Search\RackPage;
use Tests\Support\Assert;

/**
 * Rend app/View/play.php avec des fiches RackPage synthetiques (les trois cas
 * distincts : capped, vide, trouve -- voir App\Search\RackPage) sans serveur HTTP ni
 * base de donnees -- verifie des invariants structurels sur le HTML produit :
 * - capped = true ne rend jamais "0" resultat comme si rien n'avait ete trouve ;
 * - matches vide et non capped affiche un message "aucun mot", distinct du cas capped ;
 * - chaque mot liste lie vers /mot/{slug}, avec les badges ODS8/ODS9 (.edition-badge,
 *   composant deja unifie en Phase 1, reutilise tel quel) ;
 * - truncated = true mentionne le total reel et la limite d'affichage ;
 * - mots de longueur extreme (2 et 15 lettres, D-010) ne cassent pas le rendu ;
 * - aucune mention de source de donnees (D-015) ;
 * - le formulaire de repli reste un GET natif vers /jouer (fonctionne sans JavaScript).
 */
return function (): void {
    require __DIR__ . '/../../app/bootstrap.php';

    $tileScores = require __DIR__ . '/../../config/sites/fr.php';
    $tileScores = $tileScores['tile_scores'];

    $render = static function (RackPage $page) use ($tileScores): string {
        $seo = \App\Seo\SeoMeta::noindex('https://exemple.fr/jouer/' . $page->slug);

        ob_start();
        (static function (RackPage $page, array $tileScores, \App\Seo\SeoMeta $seo): void {
            require __DIR__ . '/../../app/View/play.php';
        })($page, $tileScores, $seo);

        return (string) ob_get_clean();
    };

    // Cas 1 : plafond declenche (13 lettres + 2 jokers) -- aucune requete executee,
    // totalMatches est null (inconnu), pas zero. Ne doit jamais ressembler a "0 mot".
    $capped = new RackPage(
        slug: 'abcdefghijklm**',
        letterCounts: array_fill_keys(str_split('ABCDEFGHIJKLM'), 1),
        jokerCount: 2,
        capped: true,
        matches: [],
        totalMatches: null,
        truncated: false,
        displayLimit: 300,
        candidateSignatureCount: 0,
        queryCount: 0,
    );

    $htmlCapped = $render($capped);
    Assert::true(str_contains($htmlCapped, 'status-badge--not-admitted'), 'capped : badge distinct, ni admitted ni unknown');
    Assert::true(!str_contains($htmlCapped, '<strong>0</strong>'), 'capped : totalMatches null ne doit jamais afficher 0');
    Assert::true(!str_contains($htmlCapped, 'class="rack-results"'), 'capped : aucune liste de resultats rendue');

    // Cas 2 : zero mot jouable, plafond non declenche -- distinct du cas capped.
    $empty = new RackPage(
        slug: 'zz',
        letterCounts: ['Z' => 2],
        jokerCount: 0,
        capped: false,
        matches: [],
        totalMatches: 0,
        truncated: false,
        displayLimit: 300,
        candidateSignatureCount: 1,
        queryCount: 1,
    );

    $htmlEmpty = $render($empty);
    Assert::true(str_contains($htmlEmpty, 'status-badge--unknown'), 'vide : badge distinct du cas capped');
    Assert::true(str_contains($htmlEmpty, '<strong>0</strong>'), 'vide : totalMatches = 0 doit etre affiche explicitement');
    Assert::true(!str_contains($htmlEmpty, 'class="rack-results"'), 'vide : aucune liste de resultats rendue');

    // Cas 3 : mots trouves, tronque -- mots de longueur extreme (2 et 15 lettres).
    $matches = [
        ['normalized' => 'OS', 'slug' => 'os', 'score' => 2, 'length' => 2, 'isOds8' => true, 'isOds9' => true],
        ['normalized' => 'ABANDONNATRICES', 'slug' => 'abandonnatrices', 'score' => 20, 'length' => 15, 'isOds8' => true, 'isOds9' => false],
    ];

    $found = new RackPage(
        slug: 'aabcdenorst*',
        letterCounts: array_count_values(str_split('AABCDENORST')),
        jokerCount: 1,
        capped: false,
        matches: $matches,
        totalMatches: 500,
        truncated: true,
        displayLimit: 300,
        candidateSignatureCount: 12000,
        queryCount: 3,
    );

    $htmlFound = $render($found);
    Assert::true(str_contains($htmlFound, 'status-badge--admitted'), 'trouve : badge positif');
    Assert::true(str_contains($htmlFound, '<strong>500</strong>'), 'trouve : totalMatches reel affiche, jamais limite par displayLimit');
    Assert::true(str_contains($htmlFound, '300'), 'tronque : la limite d\'affichage doit apparaitre');
    Assert::true(str_contains($htmlFound, '500'), 'tronque : le total reel doit apparaitre');

    foreach ($matches as $match) {
        Assert::true(
            str_contains($htmlFound, '<a class="rack-result-word" href="/mot/' . $match['slug'] . '">' . $match['normalized'] . '</a>'),
            $match['normalized'] . ' : doit lier vers sa fiche /mot/{slug}',
        );
    }

    $listItemCount = substr_count($htmlFound, 'class="rack-result-row"');
    Assert::same(count($matches), $listItemCount, 'une ligne par mot jouable, ni plus ni moins');

    // Badges ODS8/ODS9 : composant deja unifie (Phase 1), reutilise tel quel, jamais
    // redouble par une variante concurrente.
    Assert::true(str_contains($htmlFound, 'edition-badge active ods8'), 'ODS8 actif doit apparaitre pour OS');
    Assert::true(str_contains($htmlFound, 'edition-badge inactive'), 'ODS9 inactif doit apparaitre pour ABANDONNATRICES');

    foreach ([$htmlCapped, $htmlEmpty, $htmlFound] as $html) {
        // Le formulaire de repli doit rester un GET natif vers /jouer, meme nom de
        // champ que le repli lu par public/index.php (?lettres=..).
        Assert::true(
            str_contains($html, '<form class="inline-check" action="/jouer" method="get">'),
            'le formulaire de repli doit rester un GET natif sans JavaScript',
        );
        Assert::true(str_contains($html, 'name="lettres"'), 'le champ doit se nommer "lettres", lu par public/index.php');
        Assert::true(str_contains($html, 'noindex,follow'), 'noindex,follow par defaut (D-005)');

        // D-015 : aucun credit de source publie, nulle part dans le HTML servi.
        $forbidden = ['kartmaan', 'hbenbel', 'github.com', 'larousse'];
        $lowerHtml = mb_strtolower($html);
        foreach ($forbidden as $needle) {
            Assert::true(!str_contains($lowerHtml, $needle), 'mention de source interdite (D-015) : ' . $needle);
        }
    }
};
