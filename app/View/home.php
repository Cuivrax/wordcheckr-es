<?php

declare(strict_types=1);

/**
 * Vue home, appelee par public/index.php avec $minTermLength et $maxTermLength
 * (App\Config, config/sites/fr.php).
 *
 * Ordre impose (docs/04_UI_PAGES.md), sans paragraphe entre le H1 et le formulaire :
 * header -> badge -> H1 -> formulaire -> resultats -> liens contextuels ->
 * deux paragraphes courts -> footer.
 *
 * Phase 1 n'a livre que la verification d'un mot (route /verifier, redirection pure
 * vers /mot/{slug} -- voir reports/query-plans/phase1.md : 0 requete SQLite pour /).
 * Phase 2 ajoute le solveur par chevalet (route /jouer/{lettres}, App\Search\RackSolver).
 *
 * Refonte du bloc de recherche (Phase 2, rapprochement de prototype/index.html a la
 * demande du coordinateur) : un seul champ <input name="q">, deux boutons de
 * soumission natifs (type="submit" + formaction) sur le meme <form> plutot que deux
 * cartes de formulaire separees. HTML pur : deux formaction differentes sur un seul
 * form sont un mecanisme standard, aucun JavaScript requis. Ce champ unique ne peut
 * porter qu'un seul nom -- "mot" et "lettres" restent lus en priorite par
 * public/index.php (repli existant, non supprime), "q" est le repli additif qui
 * permet a un seul input de nourrir /verifier ET /jouer (voir l'entete de
 * public/index.php pour le detail, fichier partage, ajout signale).
 *
 * Les contraintes (longueur, prefixe, motif...) restent Phase 3 : tant que ces routes
 * n'existent pas, aucune UI n'est rendue pour elles -- un champ sans reponse possible
 * violerait l'exigence "fonctionne sans JavaScript" en promettant un resultat qu'aucune
 * route ne peut produire. La section "resultats" de l'ordre impose n'a donc toujours
 * rien a afficher dans cette phase : aucune section vide n'est rendue (meme principe
 * que les relations de la fiche mot, docs/01_MASTER_BRIEF.md). Pas de tableau de
 * resultats sur la home (divergence assumee du prototype) : chaque reponse a sa propre
 * URL canonique indexable (docs/05), la soumission redirige toujours vers /mot/{mot}
 * ou /jouer/{lettres}.
 *
 * Liens contextuels (remontee du coordinateur, apres le rapport de l'agent seo-registry
 * en Phase 6) : /mots/... est desormais indexe (D-017) mais la home n'y liait jamais --
 * mauvais maillage interne, ces pages dependaient uniquement du sitemap pour leur
 * decouverte. Selection volontairement restreinte (esprit de retenue du projet, pas une
 * liste exhaustive) : quelques longueurs parmi les plus recherchees au Scrabble (7
 * lettres = le tirage complet) et quelques lettres initiales frequentes, en
 * /mots/{N}-lettres et /mots/commencant/{lettre} (App\Search\WordListFilters, Phase 3).
 * URL toujours obtenue via WordListFilters::canonicalUrl(), jamais construite a la main
 * (meme discipline que app/View/word.php). Simples liens <a>, aucun JavaScript requis --
 * reutilise .related-links tel quel (meme composant que "Recherches Liees" sur
 * app/View/word.php et app/View/play.php), pas un nouveau motif visuel.
 *
 * Autocompletion (Phase 5, docs/08) : la combobox ARIA (role="combobox" sur #q,
 * listbox de suggestions, flèches/Entree/Echap) est entierement construite par
 * public/assets/js/suggest.js, charge en "defer" a la suite de tiles.js -- aucun
 * attribut ARIA de combobox ni element de liste n'est present dans ce gabarit
 * server-rendu. Sans JavaScript, #q reste l'input texte simple deja decrit
 * ci-dessus, strictement inchange. Consomme GET /api/suggest?q=.. (deja cable par
 * public/index.php, App\Search\Suggester -- voir l'entete de ce fichier).
 */

require __DIR__ . '/helpers.php';

use App\Search\WordListFilters;

/** @var int $minTermLength */
/** @var int $maxTermLength */
/** @var bool $error */
/** @var \App\Seo\SeoMeta $seo */

// Liens contextuels : liste fixe, courte, choisie par le frontend (pas une donnee
// calculee par le backend comme les relations de app/View/word.php). Chaque chemin est
// repasse par WordListFilters::fromPath()->canonicalUrl() plutot que concatene a la
// main, pour ne jamais servir un lien qui divergerait de la forme canonique attendue par
// le routeur (public/index.php) si l'ordre des mots-cles venait a changer.
//
// Simplifie (audit SEO final, C4/C5) au profit de la page hub /mots (App\Search\ExploreHub) :
// celle-ci couvre desormais les 66 pages longueur/commencant/terminant de facon exhaustive,
// avec leurs comptes reels -- la home n'a plus besoin de dupliquer une selection partielle,
// juste d'illustrer puis de renvoyer vers le hub complet.
$contextLinkSpecs = [
    ['path' => '7-lettres', 'label' => 'Mots De 7 Lettres'],
    ['path' => 'commencant/a', 'label' => 'Commençant Par A'],
    ['path' => 'terminant/s', 'label' => 'Terminant Par S'],
];

$contextLinks = [];
foreach ($contextLinkSpecs as $spec) {
    $url = WordListFilters::fromPath($spec['path'])?->canonicalUrl();

    if ($url !== null) {
        $contextLinks[] = ['url' => $url, 'label' => $spec['label']];
    }
}

// Liens dans la phrase d'aide (retour utilisateur, 2026-08-09) : chaque type de contrainte
// mentionne dans le paragraphe pointe vers un exemple reel plutot que de rester du texte plat
// -- meme discipline que $contextLinks ci-dessus (WordListFilters::fromPath()->canonicalUrl(),
// jamais une URL construite a la main). Un aller-retour a ete fait vers un lien unique "/mots"
// pour les huit (retour utilisateur : "n'a aucun sens", huit ancres de texte vers la meme URL
// n'apporte rien) -- revenu a des exemples concrets differencies : "longueur"/"debut"/"fin" ont
// une vraie grille dans le hub /mots, mais contenant/avec/sans/position n'en ont AUCUNE par
// conception (D-012, combinaisons non bornees) -- un exemple reel reste le seul moyen concret
// de les montrer. Question posee et tranchee au meme moment : un hub dedie "choisir la lettre
// de debut PUIS la lettre de fin" (grille 26x26) n'apporterait rien au-dela d'eviter les pages
// orphelines, deja resolu autrement (D-024, maillage /mots -> /mots/commencant/{X} ->
// /mots/commencant/{X}/terminant/{Y}) -- pas construit, juste redondant avec ce cheminement en
// deux etapes deja en place. Un mot-cle sans URL valide (ne devrait jamais arriver ici, tous
// ces chemins sont des contraintes de base deja couvertes par les tests WordListFiltersTest)
// reste du texte simple plutot que d'interrompre le rendu.
$phraseLink = static function (string $path, string $label): string {
    $url = WordListFilters::fromPath($path)?->canonicalUrl();

    return $url !== null ? '<a href="' . e($url) . '">' . e($label) . '</a>' : e($label);
};
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Quel Mot Pouvez-Vous Jouer&nbsp;? | WORD CHECKR</title>
<meta name="description" content="Vérifiez si un mot est admis dans les dictionnaires officiels du Scrabble ou trouvez les mots jouables avec vos lettres, avec le score de chacun.">
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
  </div>
</header>

<main class="site main" id="main">
  <section class="hero">
    <span class="eyebrow">Réponse immédiate</span>
    <h1>Quel Mot Pouvez-Vous Jouer&nbsp;?</h1>

    <div class="search-card">
<?php if ($error): ?>
      <div class="alert" role="alert">Saisie non reconnue. Vérifiez le mot ou le tirage ci-dessous.</div>
<?php endif; ?>
      <!-- Deux formulaires distincts (audit final, code-reviewer/design-consistency-reviewer,
           bloquant F1) : un seul formulaire partage entre "Trouver"/"Vérifier" et le
           constructeur de contraintes faisait qu'une simple touche Entree dans un champ de
           contrainte declenchait le PREMIER bouton submit du document ("Trouver" -> /jouer),
           perdant toute la saisie. Chaque mecanisme a maintenant son propre formulaire, la
           soumission implicite (Entree) reste scopee au bon bouton dans chaque cas. -->
      <form action="/verifier" method="get">
        <label class="label" for="q">Vos lettres ou le mot à vérifier</label>
        <div class="rack-wrap" data-tile-preview>
          <input
            class="rack"
            type="text"
            id="q"
            name="q"
            maxlength="<?= e($maxTermLength) ?>"
            autocomplete="off"
            spellcheck="false"
            placeholder="Exemple POSER ou AEINRST"
            aria-describedby="q-help"
          >
          <div class="tiles" aria-hidden="true"></div>
        </div>
        <div class="btn-row">
          <button class="btn btn-primary" type="submit" formaction="/jouer">Trouver</button>
          <button class="btn btn-soft" type="submit" formaction="/verifier">Vérifier</button>
        </div>
        <p class="help" id="q-help">De <?= e($minTermLength) ?> à <?= e($maxTermLength) ?> lettres. Ajoutez ? ou * pour un joker, deux au maximum. Les accents sont retirés automatiquement.</p>
      </form>

      <!-- Constructeur de contraintes, repris de prototype/index.html (champs exacts,
           labels, maxlength, placeholders) -- <details>/<summary> natif, aucun JavaScript
           requis pour l'ouverture/fermeture ni pour la recherche : soumission GET native
           vers /mots (public/index.php assemble les segments et redirige vers la forme
           canonique, App\Search\WordListFilters). "avec"/"sans" eclatent chaque lettre en
           segment individuel cote serveur -- un seul champ texte suffit ici. "Cases connues"
           utilise '-' pour une case inconnue (docs/05), pas '.' comme dans le prototype :
           adapte a la grammaire d'URL reelle du site plutot que copiee telle quelle. -->
      <form action="/mots" method="get">
        <details class="constraint-builder">
          <summary class="constraint-toggle">+ Ajouter Une Contrainte</summary>
          <p class="constraint-panel-intro">Recherche indépendante du champ ci-dessus : parcourt tous les mots correspondant aux contraintes choisies.</p>
          <div class="constraint-panel">
            <div class="constraint-field">
              <label class="label" for="longueur">Longueur</label>
              <select class="select" id="longueur" name="longueur">
                <option value="">Toutes</option>
<?php for ($len = $minTermLength; $len <= $maxTermLength; $len++): ?>
                <option value="<?= e($len) ?>"><?= e($len) ?></option>
<?php endfor; ?>
              </select>
            </div>
            <div class="constraint-field">
              <label class="label" for="commencant">Commence Par</label>
              <input class="field" type="text" id="commencant" name="commencant" maxlength="5" placeholder="CH" autocomplete="off" spellcheck="false">
            </div>
            <div class="constraint-field">
              <label class="label" for="terminant">Termine Par</label>
              <input class="field" type="text" id="terminant" name="terminant" maxlength="5" placeholder="TION" autocomplete="off" spellcheck="false">
            </div>
            <div class="constraint-field">
              <label class="label" for="contenant">Contient La Suite</label>
              <input class="field" type="text" id="contenant" name="contenant" maxlength="5" placeholder="CHE" autocomplete="off" spellcheck="false">
            </div>
            <div class="constraint-field">
              <label class="label" for="avec">Lettres Obligatoires</label>
              <input class="field" type="text" id="avec" name="avec" maxlength="8" placeholder="AAR" autocomplete="off" spellcheck="false">
            </div>
            <div class="constraint-field">
              <label class="label" for="sans">Sans Les Lettres</label>
              <input class="field" type="text" id="sans" name="sans" maxlength="8" placeholder="XZ" autocomplete="off" spellcheck="false">
            </div>
            <div class="constraint-field constraint-field-wide">
              <label class="label" for="motif">Cases Connues</label>
              <input class="field" type="text" id="motif" name="motif" maxlength="15" placeholder="C--E-" autocomplete="off" spellcheck="false">
              <p class="help">Un tiret représente une case inconnue.</p>
            </div>
            <button class="btn btn-primary" type="submit">Rechercher</button>
          </div>
        </details>
      </form>
<?php if ($contextLinks !== []): ?>
      <div class="context-links related-links">
<?php foreach ($contextLinks as $link): ?>
        <a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a>
<?php endforeach; ?>
        <a href="/mots">Explorer Tous Les Mots →</a>
      </div>
<?php endif; ?>
    </div>
  </section>

  <section class="context-copy">
    <h2>Une Aide Rapide Pour Les Jeux De Lettres</h2>
    <p>Utilisez cet outil pour le Scrabble, les mots croisés, les mots fléchés et les autres jeux de lettres. Vérifiez si un mot est admis, recherchez ses anagrammes ou trouvez les meilleurs mots jouables avec votre tirage.</p>
    <p>
      Vous pouvez aussi préciser <?= $phraseLink('9-lettres', 'une longueur') ?>,
      <?= $phraseLink('commencant/a', 'un début') ?>,
      <?= $phraseLink('terminant/s', 'une fin') ?>,
      <?= $phraseLink('commencant/a/terminant/e', 'un début et une fin combinés') ?>,
      <?= $phraseLink('contenant/ch', 'une suite de lettres') ?>,
      <?= $phraseLink('avec/e', 'des lettres obligatoires') ?>,
      <?= $phraseLink('sans/e', 'des lettres à exclure') ?>
      ou même <?= $phraseLink('9-lettres/position/3/a', 'une lettre à une position précise') ?>
      dans le mot, afin d’obtenir une réponse plus précise.
    </p>
  </section>
</main>

<footer class="footer">
  <div class="site footer-row">
    <span>Outil indépendant d’aide aux jeux de lettres.</span>
    <span class="footer-links"><a href="/mentions-legales">Mentions Légales</a> · <a href="/confidentialite">Confidentialité</a> · <a href="/contact">Contact</a></span>
  </div>
</footer>
<script src="/assets/js/tiles.js" defer></script>
<script src="/assets/js/suggest.js" defer></script></body>
</html>
