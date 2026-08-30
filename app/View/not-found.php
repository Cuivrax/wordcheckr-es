<?php

declare(strict_types=1);

/**
 * Vue 404, appelee par public/index.php avec $requestPath pour :
 * - une route qui ne correspond a aucune des trois routes de la Phase 1 ;
 * - un segment /mot/{...} dont la forme normalisee n'est pas valide (hors bornes
 *   de longueur 2-15, ou caracteres hors A-Z apres normalisation) : ce n'est pas
 *   le statut "terme inconnu" (qui a sa propre fiche, voir app/View/word.php),
 *   c'est une erreur de saisie sur laquelle aucune fiche ne peut etre construite
 *   (App\Search\TermLookup::find() renvoie null avant toute requete SQLite).
 *
 * http_response_code(404) est deja fixe par public/index.php avant l'inclusion de
 * cette vue.
 */

require __DIR__ . '/helpers.php';

/** @var string $requestPath */
/** @var \App\Seo\SeoMeta $seo */
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Página No Encontrada | WORD CHECKR</title>
<meta name="description" content="Esta página no existe. Verifica una palabra o encuentra las palabras que puedes formar con tus letras.">
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
  <article class="word-card">
    <section class="word-answer">
      <span class="status-badge status-badge--unknown">Página no encontrada</span>
      <h1 class="word-title">404</h1>
      <p>Esta página no existe.</p>
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
    <span class="footer-links"><a href="/mentions-legales">Aviso Legal</a> · <a href="/confidentialite">Privacidad</a> · <a href="/contact">Contacto</a></span>
  </div>
</footer>
</body>
</html>
