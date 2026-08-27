<?php

declare(strict_types=1);

/**
 * Page hub /mots, appelee par public/index.php avec $hub (App\Search\ExploreHub). Trois
 * grilles completes vers les familles deja indexees et finies (longueur, commencant,
 * terminant -- 66 liens, D-017), chacune avec son compte reel. Corrige l'absence de lien
 * entrant vers ces pages, releve par l'audit SEO final (seo-technical-auditor, C4).
 *
 * "Contenant" n'a JAMAIS de grille ici (App\Seo\Family::NEVER_SITEMAP, combinaisons
 * infinies) -- seulement un outil de recherche borne a 3 lettres (decision produit), qui
 * soumet en GET vers /mots?contenant=... (repli sans JavaScript deja cable par
 * public/index.php, redirection pure vers la forme canonique /mots/contenant/{lettres}).
 *
 * Aucun credit de source (D-015). noindex/canonical deja resolus par public/index.php.
 */

require __DIR__ . '/helpers.php';

use App\Search\ExploreHub;

/** @var ExploreHub $hub */
/** @var bool $error */
/** @var \App\Seo\SeoMeta $seo */
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Explorer Tous Les Mots | WORD CHECKR</title>
<meta name="description" content="Parcourez les mots du Scrabble par longueur, par lettre de début ou de fin, ou cherchez les mots contenant une suite de lettres précise.">
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
  <nav class="breadcrumb" aria-label="Fil d’Ariane"><a href="/">Accueil</a> › Explorer tous les mots</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title explore-title">Explorer Tous Les Mots</h1>
      <p>Par longueur, par lettre de début ou de fin, ou par lettres contenues.</p>
<?php if ($error): ?>
      <div class="alert" role="alert">Contrainte non reconnue. Vérifiez votre saisie et réessayez.</div>
<?php endif; ?>
    </section>

    <section class="explore-group">
      <h2>Par Longueur</h2>
      <div class="related-links">
<?php foreach ($hub->byLength as $entry): ?>
        <a href="<?= e($entry['url']) ?>"><span class="explore-label"><?= e($entry['length']) ?> lettres</span> <span class="explore-count">(<?= e(number_format($entry['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="explore-group">
      <h2>Commençant Par</h2>
      <div class="related-links">
<?php foreach ($hub->byStart as $entry): ?>
        <a href="<?= e($entry['url']) ?>"><span class="explore-label"><?= e($entry['letter']) ?></span> <span class="explore-count">(<?= e(number_format($entry['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="explore-group">
      <h2>Terminant Par</h2>
      <div class="related-links">
<?php foreach ($hub->byEnd as $entry): ?>
        <a href="<?= e($entry['url']) ?>"><span class="explore-label"><?= e($entry['letter']) ?></span> <span class="explore-count">(<?= e(number_format($entry['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="explore-group">
      <h2>Contenant</h2>
      <form class="inline-check" action="/mots" method="get">
        <label class="sr-only" for="contenant">Lettres contenues (3 maximum)</label>
        <input class="field" type="text" id="contenant" name="contenant" maxlength="3" autocomplete="off" spellcheck="false" placeholder="Ex. CHA">
        <button class="btn btn-primary" type="submit">Chercher</button>
      </form>
      <p class="help">Jusqu’à 3 lettres, dans l’ordre où elles apparaissent dans le mot.</p>
    </section>

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
