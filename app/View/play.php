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
        'badge' => 'Trop De Possibilités',
        'subtitle' => 'Précisez votre tirage.',
        'direct' => sprintf(
            'Le tirage %s propose trop de combinaisons pour être calculé ici. Réduisez le nombre de lettres ou de jokers pour obtenir une réponse.',
            $rackDisplay,
        ),
    ],
    $page->matches === [] => [
        'modifier' => 'unknown',
        'badge' => 'Aucun Mot',
        'subtitle' => 'Aucun mot jouable trouvé.',
        'direct' => sprintf(
            'Aucun mot admis au Scrabble ne peut être formé avec %s.',
            $rackDisplay,
        ),
    ],
    $page->totalMatches === 1 => [
        'modifier' => 'admitted',
        'badge' => 'Mot Trouvé',
        'subtitle' => 'Vous pouvez le jouer.',
        'direct' => sprintf('Avec %s, 1 mot admis au Scrabble est possible.', $rackDisplay),
    ],
    default => [
        'modifier' => 'admitted',
        'badge' => 'Mots Trouvés',
        'subtitle' => 'Vous pouvez les jouer.',
        'direct' => sprintf(
            'Avec %s, %d mots admis au Scrabble sont possibles.',
            $rackDisplay,
            $page->totalMatches,
        ),
    ],
};
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Jouer <?= e($rackDisplay) ?> | WORD CHECKR</title>
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
  <nav class="breadcrumb" aria-label="Fil d’Ariane"><a href="/">Accueil</a> › Jouer <?= e($rackDisplay) ?></nav>

  <article class="word-card">
    <section class="word-answer">
      <span class="status-badge status-badge--<?= e($statusMeta['modifier']) ?>"><?= e($statusMeta['badge']) ?></span>
      <h1 class="word-title"><?= e($rackDisplay) ?></h1>
      <p><?= e($statusMeta['subtitle']) ?></p>
    </section>

    <section class="facts">
      <div class="fact">
        <strong><?= $page->totalMatches !== null ? e($page->totalMatches) : '—' ?></strong>
        <span>Mots Trouvés</span>
      </div>
      <div class="fact">
        <strong><?= e($rackTileCount) ?></strong>
        <span>Lettres Au Chevalet</span>
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
        <span>Chevalet Utilisé</span>
      </div>
    </section>

    <section class="direct">
      <h2>Réponse Directe</h2>
      <p><?= e($statusMeta['direct']) ?></p>
    </section>

<?php if ($page->matches !== []): ?>
    <section class="rack-results">
<?php if ($page->truncated): ?>
      <p class="help rack-results-note">Meilleurs <?= e($page->displayLimit) ?> mots affichés, sur <?= e($page->totalMatches) ?> au total.</p>
<?php endif; ?>
      <div class="rack-result-head" aria-hidden="true">
        <span>Mot</span><span class="rack-result-head-center">Éditions</span><span class="rack-result-head-right">Points</span><span class="rack-result-head-length">Lettres</span>
      </div>
      <ul class="rack-result-list">
<?php foreach ($page->matches as $match): ?>
        <li class="rack-result-row">
          <a class="rack-result-word" href="/mot/<?= e($match['slug']) ?>"><?= e($match['normalized']) ?></a>
          <span class="edition-badges">
            <span class="edition-badge <?= $match['isOds8'] ? 'active ods8' : 'inactive' ?>">ODS8</span>
            <span class="edition-badge <?= $match['isOds9'] ? 'active ods9' : 'inactive' ?>">ODS9</span>
          </span>
          <span class="rack-result-points" aria-label="<?= e($match['score']) ?> points"><?= e($match['score']) ?></span>
          <span class="rack-result-length" aria-label="<?= e($match['length']) ?> lettres"><?= e($match['length']) ?></span>
        </li>
<?php endforeach; ?>
      </ul>
    </section>
<?php endif; ?>

    <form class="inline-check" action="/jouer" method="get">
      <label class="sr-only" for="lettres-check">Essayer un autre tirage</label>
      <input class="field" type="text" id="lettres-check" name="lettres" maxlength="15" autocomplete="off" spellcheck="false" placeholder="Essayer un autre tirage">
      <button class="btn btn-primary" type="submit">Jouer</button>
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
