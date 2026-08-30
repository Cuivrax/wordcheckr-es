<?php

declare(strict_types=1);

/**
 * Page hub /mots, appelee par public/index.php avec $hub (App\Search\ExploreHub). Trois
 * grilles completes vers les familles deja indexees et finies (longueur, commencant,
 * terminant -- 66 liens, D-017), chacune avec son compte reel. Corrige l'absence de lien
 * entrant vers ces pages, releve par l'audit SEO final (seo-technical-auditor, C4).
 *
 * "Contiene" n'a JAMAIS de grille ici (App\Seo\Family::NEVER_SITEMAP, combinaisons
 * infinies) -- seulement un outil de recherche borne a 3 lettres (decision produit), qui
 * soumet en GET vers /palabras?contienen=... (repli sans JavaScript deja cable par
 * public/index.php, redirection pure vers la forme canonique /palabras/contienen/{letras} --
 * mot-cle d'URL traduit par ES-014, NOM du champ GET aussi localise par ES-019).
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
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Explorar Todas Las Palabras | WORD CHECKR</title>
<meta name="description" content="Explora las palabras del Scrabble por longitud, por letra inicial o final, o busca las palabras que contienen una secuencia de letras precisa.">
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
  <nav class="breadcrumb" aria-label="Migas de pan"><a href="/">Inicio</a> › Explorar todas las palabras</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title explore-title">Explorar Todas Las Palabras</h1>
      <p>Por longitud, por letra inicial o final, o por letras contenidas.</p>
<?php if ($error): ?>
      <div class="alert" role="alert">Restricción no reconocida. Verifica tu entrada e inténtalo de nuevo.</div>
<?php endif; ?>
    </section>

    <section class="explore-group">
      <h2>Por Longitud</h2>
      <div class="related-links">
<?php foreach ($hub->byLength as $entry): ?>
        <a href="<?= e($entry['url']) ?>"><span class="explore-label"><?= e($entry['length']) ?> letras</span> <span class="explore-count">(<?= e(number_format($entry['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="explore-group">
      <h2>Empiezan Por</h2>
      <div class="related-links">
<?php foreach ($hub->byStart as $entry): ?>
        <a href="<?= e($entry['url']) ?>"><span class="explore-label"><?= e($entry['letter']) ?></span> <span class="explore-count">(<?= e(number_format($entry['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="explore-group">
      <h2>Terminan En</h2>
      <div class="related-links">
<?php foreach ($hub->byEnd as $entry): ?>
        <a href="<?= e($entry['url']) ?>"><span class="explore-label"><?= e($entry['letter']) ?></span> <span class="explore-count">(<?= e(number_format($entry['count'], 0, ',', ' ')) ?>)</span></a>
<?php endforeach; ?>
      </div>
    </section>

    <section class="explore-group">
      <h2>Contiene</h2>
      <form class="inline-check" action="/palabras" method="get">
        <label class="sr-only" for="contienen">Letras contenidas (3 máximo)</label>
        <input class="field" type="text" id="contienen" name="contienen" maxlength="3" autocomplete="off" spellcheck="false" placeholder="Ej. CHA">
        <button class="btn btn-primary" type="submit">Buscar</button>
      </form>
      <p class="help">Hasta 3 letras, en el orden en que aparecen en la palabra.</p>
    </section>

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
