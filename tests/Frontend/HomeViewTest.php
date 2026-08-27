<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * Rend app/View/home.php avec les bornes reelles (config/sites/fr.php) sans serveur
 * HTTP -- verifie l'ordre impose de la home (docs/04_UI_PAGES.md) et l'absence de
 * credit de source (D-015).
 */
return function (): void {
    $config = require __DIR__ . '/../../config/sites/fr.php';

    $render = static function (int $minTermLength, int $maxTermLength, bool $error = false): string {
        $seo = \App\Seo\SeoMeta::noindex('https://exemple.fr/');

        ob_start();
        (static function (int $minTermLength, int $maxTermLength, bool $error, \App\Seo\SeoMeta $seo): void {
            require __DIR__ . '/../../app/View/home.php';
        })($minTermLength, $maxTermLength, $error, $seo);

        return (string) ob_get_clean();
    };

    $html = $render($config['min_term_length'], $config['max_term_length']);

    // Ordre impose, sans paragraphe entre le H1 et le formulaire : header -> badge ->
    // H1 -> formulaire -> ... -> footer.
    $headerPos = strpos($html, '<header');
    $badgePos = strpos($html, 'class="eyebrow"');
    $h1Pos = strpos($html, '<h1>');
    $formPos = strpos($html, '<form');
    $contextPos = strpos($html, 'class="context-copy"');
    $footerPos = strpos($html, '<footer');

    Assert::true($headerPos !== false && $badgePos !== false && $h1Pos !== false && $formPos !== false && $contextPos !== false && $footerPos !== false, 'toutes les sections attendues doivent etre presentes');
    Assert::true($headerPos < $badgePos, 'header avant le badge');
    Assert::true($badgePos < $h1Pos, 'badge avant le H1');
    Assert::true($h1Pos < $formPos, 'H1 avant le formulaire, aucun paragraphe entre les deux');
    Assert::true($formPos < $contextPos, 'formulaire avant les paragraphes de contexte');
    Assert::true($contextPos < $footerPos, 'paragraphes de contexte avant le footer');

    // Rien entre la fermeture du H1 et l'ouverture du formulaire hormis les balises
    // de mise en page (aucun <p> autonome).
    $between = substr($html, $h1Pos, $formPos - $h1Pos);
    Assert::true(!str_contains($between, '<p>') && !str_contains($between, '<p '), 'aucun paragraphe entre le H1 et le formulaire');

    // Bornes reelles injectees, pas de valeur codee en dur qui divergerait de
    // config/sites/fr.php.
    Assert::true(str_contains($html, (string) $config['min_term_length']), 'la borne minimale doit apparaitre dans le HTML');
    Assert::true(str_contains($html, (string) $config['max_term_length']), 'la borne maximale doit apparaitre dans le HTML');
    Assert::true(
        str_contains($html, 'maxlength="' . $config['max_term_length'] . '"'),
        'le champ de saisie doit porter la meme borne maximale que la configuration',
    );

    // Un seul champ de saisie principal (rapprochement de prototype/index.html,
    // Phase 2) : GET natif, action /verifier par defaut, nomme "q" (repli additif lu
    // par public/index.php en plus de "mot"/"lettres" existants -- voir l'entete de
    // public/index.php). Deux <form> distincts depuis l'audit final (bloquant F1) :
    // le constructeur de contraintes a son propre formulaire, jamais partage avec
    // "Trouver"/"Vérifier" -- une simple touche Entree dans un champ de contrainte
    // declenchait sinon le premier bouton submit du DOCUMENT ("Trouver" -> /jouer),
    // perdant toute la saisie.
    Assert::true(str_contains($html, '<form action="/verifier" method="get">'), 'le formulaire de recherche doit avoir /verifier comme action GET par defaut');
    Assert::true(str_contains($html, '<form action="/mots" method="get">'), 'le constructeur de contraintes doit avoir son propre formulaire, action /mots');
    Assert::true(str_contains($html, 'name="q"'), 'le champ unique doit se nommer "q", lu en repli par public/index.php');
    Assert::true(
        substr_count($html, '<form') === 2,
        'exactement deux formulaires sur la home : recherche principale + constructeur de contraintes',
    );
    Assert::true(
        substr_count($html, 'name="q"') === 1,
        'un seul champ de saisie principal sur la home',
    );

    // Deux boutons de soumission natifs sur le meme champ (type="submit" +
    // formaction), sans JavaScript requis : "Trouver" vers /jouer, "Vérifier" vers
    // /verifier -- exactement le layout de prototype/index.html.
    Assert::true(
        str_contains($html, 'type="submit" formaction="/jouer">Trouver</button>'),
        'le bouton Trouver doit soumettre en GET vers /jouer via formaction',
    );
    Assert::true(
        str_contains($html, 'type="submit" formaction="/verifier">Vérifier</button>'),
        'le bouton Vérifier doit soumettre en GET vers /verifier via formaction',
    );

    // D-015 : aucun credit de source publie.
    $forbidden = ['kartmaan', 'hbenbel', 'github.com', 'larousse'];
    $lowerHtml = mb_strtolower($html);
    foreach ($forbidden as $needle) {
        Assert::true(!str_contains($lowerHtml, $needle), 'mention de source interdite (D-015) : ' . $needle);
    }

    // Liens contextuels (maillage interne vers /mots/..., signale par l'agent
    // seo-registry en Phase 6, D-017) : rapproches du formulaire (comme
    // prototype/index.html : chips attachees au champ, pas une section separee plus
    // bas) -- places dans le formulaire, apres l'aide du champ, avant sa fermeture.
    $contextLinksPos = strpos($html, 'context-links');
    Assert::true($contextLinksPos !== false, 'la section liens contextuels doit etre presente');
    Assert::true($formPos < $contextLinksPos, 'liens contextuels apres l\'ouverture du formulaire');
    Assert::true($contextLinksPos < $contextPos, 'liens contextuels avant les paragraphes de contexte');

    // Reutilise .related-links tel quel (meme composant que "Recherches Liees" sur
    // app/View/word.php et app/View/play.php), pas un nouveau motif visuel.
    Assert::true(str_contains($html, 'related-links'), 'les liens contextuels doivent reutiliser le composant .related-links existant');

    // Nombre raisonnable de liens (esprit de retenue du projet, pas un mur de liens) :
    // au moins un, jamais plus d'une dizaine.
    $contextLinksBlock = substr($html, $contextLinksPos, $contextPos - $contextLinksPos);
    $linkCount = substr_count($contextLinksBlock, '<a href="/mots/');
    Assert::true($linkCount >= 1 && $linkCount <= 10, 'entre 1 et 10 liens contextuels, jamais un mur de liens : ' . $linkCount);

    // Chaque lien contextuel pointe vers une URL canonique /mots/... (jamais construite
    // a la main -- App\Search\WordListFilters::canonicalUrl(), meme discipline que
    // app/View/word.php) et reste un simple <a> sans JavaScript requis.
    Assert::true((bool) preg_match_all('#<a href="(/mots/[^"]+)">#', $contextLinksBlock, $m) && $m[1] !== [], 'les liens contextuels doivent pointer vers /mots/...');
    foreach ($m[1] as $href) {
        $filters = \App\Search\WordListFilters::fromPath(preg_replace('#^/mots/?#', '', $href) ?? '');
        Assert::true($filters !== null && $filters->canonicalUrl() === $href, 'chaque lien contextuel doit deja etre sous forme canonique : ' . $href);
    }

    // Etat d'erreur visible (audit final, bloquant F3, WCAG 3.3.1) : absent par defaut,
    // rendu comme bandeau role="alert" dans le search-card quand public/index.php detecte
    // une saisie invalide sur /verifier ou /jouer (?erreur=1) -- jamais une section vide.
    Assert::true(!str_contains($html, 'role="alert"'), 'aucun bandeau erreur sans indicateur erreur=true');

    $htmlWithError = $render($config['min_term_length'], $config['max_term_length'], true);
    Assert::true(str_contains($htmlWithError, 'class="alert" role="alert"'), 'le bandeau erreur doit apparaitre quand error=true');
    Assert::true(
        strpos($htmlWithError, 'role="alert"') < strpos($htmlWithError, '<form'),
        'le bandeau erreur doit precede les formulaires',
    );
};
