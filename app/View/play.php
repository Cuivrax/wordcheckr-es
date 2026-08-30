<?php

declare(strict_types=1);

/**
 * Vue solveur /jouer/{lettres}, appelee par public/index.php avec $page
 * (App\Search\RackPage, Phase 2). Meme gabarit que app/View/word.php (statut,
 * tuiles, reponse directe, formulaire de repli) -- reutilise a l'identique les
 * composants deja unifies (.status-badge, .letter-tile, .edition-badge,
 * .inline-check), etendus uniquement d'une liste de resultats.
 *
 * Trois cas distincts (voir App\Search\RackPage) :
 * - capped = true       : aucune requete executee, pas d'erreur -- message
 *                         explicite invitant a preciser le tirage.
 * - matches = [] non capped : zero mot jouable avec ce tirage.
 * - matches non vide    : liste triee, chaque mot lie vers sa fiche /mot/{slug}.
 *   totalMatches est le compte REEL (jamais limite par displayLimit) ; si
 *   truncated, une mention courte indique que seuls displayLimit resultats
 *   sont affiches.
 *
 * Aucune relation, aucune contrainte avancee (longueur, prefixe...) : Phase 3,
 * hors perimetre ici. Aucun credit de source (D-015).
 */

require __DIR__ . '/helpers.php';

use App\Search\RackPage;

/** @var RackPage $page */
/** @var array<string, int> $tileScores */
/** @var array<int, array{column: string, badge: string}> $lexicons */
/** @var \App\Seo\SeoMeta $seo */

$letters = '';

foreach ($page->letterCounts as $letter => $count) {
    $letters .= str_repeat($letter, $count);
}

$rackDisplay = $letters . str_repeat('?', $page->jokerCount);
$rackTileCount = array_sum($page->letterCounts) + $page->jokerCount;

$tileLabelParts = [];

foreach ($page->letterCounts as $letter => $count) {
    for ($i = 0; $i < $count; $i++) {
        $tileLabelParts[] = $letter;
    }
}

for ($i = 0; $i < $page->jokerCount; $i++) {
    $tileLabelParts[] = 'Joker';
}

$tilesAriaLabel = implode(' + ', $tileLabelParts);

$statusMeta = match (true) {
    $page->capped => [
        'modifier' => 'not-admitted',
        'badge' => 'Demasiadas Posibilidades',
        'subtitle' => 'Precisa tu tirada.',
        'direct' => sprintf(
            'La tirada %s ofrece demasiadas combinaciones para calcularla aquí. Reduce el número de letras o de comodines para obtener una respuesta.',
            $rackDisplay,
        ),
    ],
    $page->matches === [] => [
        'modifier' => 'unknown',
        'badge' => 'Ninguna Palabra',
        'subtitle' => 'No se encontró ninguna palabra jugable.',
        'direct' => sprintf(
            'Ninguna palabra válida de Scrabble puede formarse con %s.',
            $rackDisplay,
        ),
    ],
    $page->totalMatches === 1 => [
        'modifier' => 'admitted',
        'badge' => 'Palabra Encontrada',
        'subtitle' => 'Puedes jugarla.',
        'direct' => sprintf('Con %s, es posible 1 palabra válida de Scrabble.', $rackDisplay),
    ],
    default => [
        'modifier' => 'admitted',
        'badge' => 'Palabras Encontradas',
        'subtitle' => 'Puedes jugarlas.',
        'direct' => sprintf(
            'Con %s, son posibles %d palabras válidas de Scrabble.',
            $rackDisplay,
            $page->totalMatches,
        ),
    ],
};
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Jugar <?= e($rackDisplay) ?> | WORD CHECKR</title>
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
<a class="skip-link" href="#main">Ir al contenido</a>
<header class="header">
  <div class="site header-row">
    <a class="logo" href="/"><img class="logo-mark" src="/assets/img/logo.png" alt="" width="32" height="32">WORD CHECKR</a>
    <nav class="nav" aria-label="Navegación principal"><a href="/">Nueva búsqueda</a></nav>
  </div>
</header>

<main class="word-shell main" id="main">
  <nav class="breadcrumb" aria-label="Migas de pan"><a href="/">Inicio</a> › Jugar <?= e($rackDisplay) ?></nav>

  <article class="word-card">
    <section class="word-answer">
      <span class="status-badge status-badge--<?= e($statusMeta['modifier']) ?>"><?= e($statusMeta['badge']) ?></span>
      <h1 class="word-title"><?= e($rackDisplay) ?></h1>
      <p><?= e($statusMeta['subtitle']) ?></p>
    </section>

    <section class="facts">
      <div class="fact">
        <strong><?= $page->totalMatches !== null ? e($page->totalMatches) : '—' ?></strong>
        <span>Palabras Encontradas</span>
      </div>
      <div class="fact">
        <strong><?= e($rackTileCount) ?></strong>
        <span>Letras En El Atril</span>
      </div>
      <div class="fact fact-letters">
        <div class="letter-tiles" role="img" aria-label="<?= e($tilesAriaLabel) ?>">
<?php foreach ($page->letterCounts as $letter => $count): ?>
<?php for ($i = 0; $i < $count; $i++): ?>
          <span class="letter-tile" aria-hidden="true"><?= e($letter) ?><small><?= e($tileScores[$letter] ?? 0) ?></small></span>
<?php endfor; ?>
<?php endforeach; ?>
<?php for ($i = 0; $i < $page->jokerCount; $i++): ?>
          <span class="letter-tile" aria-hidden="true">?<small>0</small></span>
<?php endfor; ?>
        </div>
        <span>Atril Usado</span>
      </div>
    </section>

    <section class="direct">
      <h2>Respuesta Directa</h2>
      <p><?= e($statusMeta['direct']) ?></p>
    </section>

<?php if ($page->matches !== []): ?>
    <section class="rack-results">
<?php if ($page->truncated): ?>
      <p class="help rack-results-note">Mejores <?= e($page->displayLimit) ?> palabras mostradas, de <?= e($page->totalMatches) ?> en total.</p>
<?php endif; ?>
      <div class="rack-result-head" aria-hidden="true">
        <span>Palabra</span><span class="rack-result-head-center">Ediciones</span><span class="rack-result-head-right">Puntos</span><span class="rack-result-head-length">Letras</span>
      </div>
      <ul class="rack-result-list">
<?php foreach ($page->matches as $match): ?>
        <li class="rack-result-row">
          <a class="rack-result-word" href="/palabra/<?= e($match['slug']) ?>"><?= e($match['normalized']) ?></a>
          <span class="edition-badges">
<?php foreach ($lexicons as $lexiconIndex => $lexicon): ?>
            <span class="edition-badge <?= ($lexiconIndex === 0 ? $match['isOds8'] : $match['isOds9']) ? 'active ods' . ($lexiconIndex + 8) : 'inactive' ?>"><?= e($lexicon['badge']) ?></span>
<?php endforeach; ?>
          </span>
          <span class="rack-result-points" aria-label="<?= e($match['score']) ?> puntos"><?= e($match['score']) ?></span>
          <span class="rack-result-length" aria-label="<?= e($match['length']) ?> letras"><?= e($match['length']) ?></span>
        </li>
<?php endforeach; ?>
      </ul>
    </section>
<?php endif; ?>

    <form class="inline-check" action="/buscador-de-palabras" method="get">
      <label class="sr-only" for="letras-check">Probar otra tirada</label>
      <input class="field" type="text" id="letras-check" name="letras" maxlength="15" autocomplete="off" spellcheck="false" placeholder="Probar otra tirada">
      <button class="btn btn-primary" type="submit">Jugar</button>
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
