<?php

declare(strict_types=1);

/**
 * Vue statique /contact, appelee par public/index.php avec $error/$success (booleens issus
 * de ?erreur=1 / ?envoye=1, meme convention F3 que app/View/home.php). Formulaire POST natif
 * vers /contact -- aucun JavaScript requis, fonctionne integralement sans (CLAUDE.md).
 *
 * Champ "site_web" cache (piege a bots, voir public/index.php) : aria-hidden, hors du flux
 * visuel (CSS), hors de l'ordre de tabulation -- invisible et inaccessible a un visiteur
 * humain, y compris au clavier ou au lecteur d'ecran, mais present dans le DOM pour les bots
 * qui remplissent tous les champs sans distinction.
 *
 * L'adresse de destination n'apparait nulle part ici ni dans public/index.php (demande
 * utilisateur, anti-spam) -- configuree uniquement cote serveur (variable d'environnement
 * SCRABBLE_CONTACT_EMAIL).
 */

require __DIR__ . '/helpers.php';

/** @var bool $error */
/** @var bool $success */
/** @var \App\Seo\SeoMeta $seo */
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Contact | WORD CHECKR</title>
<meta name="description" content="Contactez WORD CHECKR par formulaire, pour une question, un signalement ou une demande liée à vos données personnelles.">
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
  <nav class="breadcrumb" aria-label="Fil d’Ariane"><a href="/">Accueil</a> › Contact</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title">Contact</h1>
      <p>Posez-nous une question, signalez un mot manquant ou un problème technique, ou faites une demande liée à vos données personnelles.</p>
    </section>

    <section class="direct">
<?php if ($success): ?>
      <div class="alert" role="alert">Message envoyé. Merci, nous vous répondrons dès que possible à l’adresse indiquée.</div>
<?php endif; ?>
<?php if ($error): ?>
      <div class="alert" role="alert">L’envoi a échoué. Vérifiez votre adresse email et votre message (5000 caractères maximum), puis réessayez.</div>
<?php endif; ?>
      <form action="/contact" method="post">
        <div class="hp-field" aria-hidden="true">
          <label for="site_web">Site web</label>
          <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
        </div>

        <div class="constraint-panel">
          <div class="constraint-field">
            <label class="label" for="nom">Nom (Facultatif)</label>
            <input class="field" type="text" id="nom" name="nom" maxlength="100" autocomplete="name">
          </div>
          <div class="constraint-field">
            <label class="label" for="email">Votre Email</label>
            <input class="field" type="email" id="email" name="email" maxlength="254" required autocomplete="email" placeholder="vous@exemple.fr">
            <p class="help">Utilisée uniquement pour vous répondre, jamais publiée ni transmise à un tiers.</p>
          </div>
          <div class="constraint-field constraint-field-wide">
            <label class="label" for="message">Message</label>
            <textarea class="field" id="message" name="message" rows="6" maxlength="5000" required></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Envoyer</button>
        </div>
      </form>
    </section>
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
