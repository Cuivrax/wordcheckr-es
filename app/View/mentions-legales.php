<?php

declare(strict_types=1);

/**
 * Vue statique /mentions-legales, appelee par public/index.php sans donnees de recherche
 * (page d'information pure, aucune requete SQLite). Meme gabarit que les autres vues
 * (header/footer identiques, .word-card/.direct reutilises tel quel pour chaque rubrique,
 * pas de nouveau motif visuel).
 *
 * Identite de l'editeur (BIGBANG MEDIA) et de l'hebergeur (o2switch) verifiees aupres de
 * sources publiques (RCS/INPI/Infogreffe, CGV publiees par o2switch) au moment de la
 * redaction, jamais inventees. Nom personnel, adresse complete du siege et email
 * volontairement absents (demande explicite du proprietaire du produit) : le siege social
 * n'apparait qu'au niveau ville/code postal, le directeur de la publication est designe par
 * sa fonction plutot que nomme. Cet ecart a la complétude habituelle d'une mention legale
 * (LCEN art. 6-III) a ete signale au proprietaire du produit, pas silencieusement comble --
 * le formulaire /contact (D-025ter) referme partiellement cet ecart en donnant enfin un
 * canal de contact reel, sans jamais publier d'adresse email.
 *
 * Ponctuation : aucun tiret cadratin, aucun deux-points en milieu de phrase (demande
 * explicite du proprietaire du produit) -- seuls les couples etiquette/valeur factuels
 * (ex. "SIREN : 917 929 382") gardent un deux-points, meme convention que le reste du site
 * (ex. "Il y a N mots"). Espace insecable avant "?"/"!" (typographie francaise, meme
 * convention que app/View/home.php).
 */

require __DIR__ . '/helpers.php';

/** @var \App\Seo\SeoMeta $seo */
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Mentions Légales | WORD CHECKR</title>
<meta name="description" content="Mentions légales de WORD CHECKR, éditeur, hébergeur, propriété intellectuelle, cookies et informations légales complètes du site.">
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
  <nav class="breadcrumb" aria-label="Fil d’Ariane"><a href="/">Accueil</a> › Mentions Légales</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title">Mentions Légales</h1>
      <p>Éditeur, hébergeur, propriété intellectuelle et informations légales complètes du site.</p>
    </section>

    <section class="direct">
      <h2>Sommaire</h2>
      <ul class="legal-toc">
        <li><a href="#editeur">Éditeur du site</a></li>
        <li><a href="#directeur">Directeur de la publication</a></li>
        <li><a href="#hebergement">Hébergement</a></li>
        <li><a href="#conception">Conception et développement</a></li>
        <li><a href="#propriete">Propriété intellectuelle</a></li>
        <li><a href="#liens">Liens hypertextes</a></li>
        <li><a href="#cookies">Cookies et traceurs</a></li>
        <li><a href="#tiers">Applications et services tiers</a></li>
        <li><a href="#donnees">Données personnelles</a></li>
        <li><a href="#accessibilite">Accessibilité</a></li>
        <li><a href="#disponibilite">Disponibilité et maintenance</a></li>
        <li><a href="#modification">Modification des mentions légales</a></li>
        <li><a href="#droit">Droit applicable et litiges</a></li>
        <li><a href="#definitions">Définitions</a></li>
      </ul>
    </section>

    <section class="direct" id="editeur">
      <h2>Éditeur Du Site</h2>
      <p>Le présent site WORD CHECKR, accessible à l’adresse www.wordcheckr.fr, est édité par la société BIGBANG MEDIA.</p>
      <p>Dénomination sociale : BIGBANG MEDIA.</p>
      <p>Forme juridique : EURL (entreprise unipersonnelle à responsabilité limitée), au capital social de 1 000 €.</p>
      <p>Immatriculation : RCS Laval, SIREN 917 929 382, SIRET 917 929 382 00013.</p>
      <p>Activité principale déclarée : code APE/NAF 6201Z, programmation informatique. L’objet social couvre la création, la gestion, le référencement et la valorisation de sites internet.</p>
      <p>Siège social : 53000 Laval, France. Par choix de confidentialité, l’adresse complète du siège n’est volontairement pas publiée sur cette page ; elle reste consultable auprès des registres publics officiels (Infogreffe, INPI, annuaire des entreprises data.gouv.fr) pour toute personne souhaitant la vérifier par ce canal.</p>
    </section>

    <section class="direct" id="directeur">
      <h2>Directeur De La Publication</h2>
      <p>Le directeur de la publication est le représentant légal de la société BIGBANG MEDIA, désigné par sa fonction plutôt que nommément sur cette page, par choix de confidentialité du propriétaire du site.</p>
      <p>Toute question relative à la direction de la publication peut être adressée via notre <a href="/contact">formulaire de contact</a>.</p>
    </section>

    <section class="direct" id="hebergement">
      <h2>Hébergement</h2>
      <p>Le site est hébergé par la société o2switch.</p>
      <p>Dénomination sociale : o2switch.</p>
      <p>Forme juridique : SAS (société par actions simplifiée), au capital social de 100 000 €.</p>
      <p>Siège social : Chemin des Pardiaux, 63000 Clermont-Ferrand, France.</p>
      <p>Immatriculation : RCS Clermont-Ferrand, SIREN 510 909 807, SIRET 510 909 807 00032.</p>
      <p>Téléphone : 04 44 44 60 40.</p>
      <p>Site officiel : <a href="https://www.o2switch.fr">o2switch.fr</a>.</p>
      <p>Le serveur physique et l’ensemble des données du site sont situés en France, sur le territoire de l’Union européenne.</p>
    </section>

    <section class="direct" id="conception">
      <h2>Conception Et Développement</h2>
      <p>La conception, le développement et la maintenance technique du site sont assurés directement par BIGBANG MEDIA, sans recours à une agence tierce ni à un prestataire externe pour le code applicatif.</p>
      <p>Le site est développé en PHP, sans framework applicatif, avec une base de données locale en lecture seule et un minimum de JavaScript côté navigateur, uniquement pour des améliorations progressives (autocomplétion de recherche, affichage des tuiles de lettres) qui n’empêchent jamais le site de fonctionner sans JavaScript activé.</p>
    </section>

    <section class="direct" id="propriete">
      <h2>Propriété Intellectuelle</h2>
      <p>La structure du site, son moteur de recherche, l’algorithme de calcul des scores, l’organisation et la structuration de la base de mots, les textes, la mise en page, le code source, les feuilles de style et l’ensemble des éléments techniques et éditoriaux du site sont la propriété exclusive de BIGBANG MEDIA, sauf mention contraire.</p>
      <p>Cette protection s’exerce notamment au titre du droit d’auteur (code de la propriété intellectuelle, articles L111-1 et suivants) et, pour la structuration et l’organisation de la base de mots, au titre du droit sui generis des producteurs de bases de données (code de la propriété intellectuelle, articles L341-1 et suivants).</p>
      <p>La langue française et le statut de ses mots au regard des dictionnaires officiels du Scrabble ne sont la propriété de personne. Ce site ne revendique aucun droit sur les mots eux-mêmes, uniquement sur sa propre construction technique et éditoriale, à savoir la façon dont ces informations sont organisées, calculées et présentées.</p>
      <p>Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sans l’autorisation écrite préalable de BIGBANG MEDIA, sauf pour un usage strictement personnel et non commercial, dans les limites prévues par le code de la propriété intellectuelle (notamment la copie ou la reproduction à usage privé).</p>
      <p>Le nom WORD CHECKR ainsi que les éléments graphiques distinctifs du site ne peuvent être utilisés sans autorisation préalable.</p>
    </section>

    <section class="direct" id="liens">
      <h2>Liens Hypertextes</h2>
      <p>Le site contient un nombre volontairement restreint de liens sortants, essentiellement vers des institutions officielles (comme la CNIL) ou vers son hébergeur. BIGBANG MEDIA n’exerce aucun contrôle sur le contenu des sites tiers ainsi liés et décline toute responsabilité quant à leur contenu, leur disponibilité ou leurs propres pratiques en matière de données personnelles.</p>
      <p>La mise en place d’un lien hypertexte pointant vers ce site est en principe libre, à condition que ce lien ne porte pas atteinte aux intérêts de BIGBANG MEDIA et qu’il soit retiré sur simple demande. La technique de liens profonds ou d’intégration du site dans un cadre (« framing ») sans autorisation préalable n’est pas autorisée.</p>
    </section>

    <section class="direct" id="cookies">
      <h2>Cookies Et Traceurs</h2>
      <p>Ce site ne dépose aucun cookie, qu’il soit strictement nécessaire, fonctionnel, de mesure d’audience ou publicitaire. Aucun traceur, pixel invisible ou technologie assimilée n’est utilisé, sous quelque forme que ce soit.</p>
      <p>Aucune bannière de consentement aux cookies n’est donc affichée sur ce site, cette formalité n’ayant pas lieu d’être en l’absence de tout dépôt de cookie ou de traceur au sens de l’article 82 de la loi Informatique et Libertés.</p>
      <p>Le détail complet de cette absence de collecte figure dans notre <a href="/confidentialite">politique de confidentialité</a>.</p>
    </section>

    <section class="direct" id="tiers">
      <h2>Applications Et Services Tiers</h2>
      <p>Par choix délibéré, ce site n’intègre aucun service tiers susceptible de collecter des données ou de ralentir son affichage. Concrètement, à la date de rédaction de cette page, le site n’utilise :</p>
      <ul class="legal-list">
        <li>aucun outil de mesure d’audience ou d’analyse statistique (comme Google Analytics, Matomo ou équivalent) ;</li>
        <li>aucune police de caractères hébergée à distance (comme Google Fonts), toutes les polices utilisées étant des polices système déjà présentes sur l’appareil du visiteur ;</li>
        <li>aucun réseau de diffusion de contenu externe (CDN) pour le chargement du code, des styles ou des images du site ;</li>
        <li>aucun module de réseau social intégré (bouton de partage, widget de like ou de commentaire) ;</li>
        <li>aucune vidéo ni carte hébergée par un service tiers (comme YouTube ou Google Maps) ;</li>
        <li>aucun outil de messagerie instantanée ou de chat en ligne fourni par un tiers ;</li>
        <li>aucune régie publicitaire ni aucun réseau de reciblage publicitaire ;</li>
        <li>aucun service tiers de connexion unique (comme « se connecter avec Google » ou « se connecter avec Facebook »), le site ne proposant d’ailleurs aucun compte utilisateur ;</li>
        <li>aucun service de paiement en ligne, le site étant entièrement gratuit et sans fonctionnalité de vente.</li>
      </ul>
      <p>Le seul acteur tiers impliqué dans le fonctionnement du site est son hébergeur, o2switch, décrit dans la rubrique « Hébergement » ci-dessus, ainsi que le service de messagerie utilisé pour l’acheminement des messages envoyés depuis notre <a href="/contact">formulaire de contact</a>.</p>
    </section>

    <section class="direct" id="donnees">
      <h2>Données Personnelles</h2>
      <p>Le traitement des données personnelles, les catégories de données concernées, leur base légale, leur durée de conservation et les modalités d’exercice de vos droits sont détaillés intégralement dans notre <a href="/confidentialite">politique de confidentialité</a>.</p>
    </section>

    <section class="direct" id="accessibilite">
      <h2>Accessibilité</h2>
      <p>Ce site est conçu pour rester utilisable sans JavaScript, avec un contraste de couleurs travaillé, une navigation au clavier fonctionnelle et une structure de titres cohérente. Il ne fait pas encore l’objet d’une déclaration d’accessibilité formelle au sens du référentiel général d’amélioration de l’accessibilité (RGAA), mais l’accessibilité reste un objectif suivi dans la conception du site.</p>
      <p>Si vous rencontrez une difficulté d’accessibilité en utilisant ce site, vous pouvez nous le signaler via notre <a href="/contact">formulaire de contact</a>.</p>
    </section>

    <section class="direct" id="disponibilite">
      <h2>Disponibilité Et Maintenance Du Site</h2>
      <p>BIGBANG MEDIA s’efforce d’assurer un accès continu au site, sans garantie absolue de disponibilité permanente. Le site peut être interrompu temporairement pour des opérations de maintenance, une mise à jour technique ou pour toute cause échappant au contrôle raisonnable de l’éditeur (panne de l’hébergeur, incident réseau).</p>
      <p>Les informations affichées par le site (admissibilité au Scrabble, scores, listes de mots) sont fournies à titre indicatif et peuvent, dans de rares cas, comporter une erreur ou une omission malgré le soin apporté à leur construction.</p>
    </section>

    <section class="direct" id="modification">
      <h2>Modification Des Mentions Légales</h2>
      <p>BIGBANG MEDIA se réserve le droit de modifier les présentes mentions légales à tout moment, notamment pour se conformer à une évolution législative ou réglementaire, ou pour refléter un changement dans l’organisation du site. Nous vous invitons à consulter cette page régulièrement.</p>
      <p>Dernière mise à jour : août 2026.</p>
    </section>

    <section class="direct" id="droit">
      <h2>Droit Applicable Et Litiges</h2>
      <p>Les présentes mentions légales sont soumises au droit français, à l’exclusion de toute autre législation. Tout litige relatif à l’utilisation du site relève, à défaut de résolution amiable préalable, de la compétence exclusive des tribunaux français.</p>
    </section>

    <section class="direct" id="definitions">
      <h2>Définitions</h2>
      <p>« Éditeur » désigne la personne morale responsable du contenu publié sur le site, ici BIGBANG MEDIA.</p>
      <p>« Hébergeur » désigne la société assurant le stockage technique du site sur ses serveurs, ici o2switch.</p>
      <p>« Cookie » ou « traceur » désigne tout fichier ou toute information déposée sur l’équipement d’un utilisateur lors de sa navigation, permettant de le reconnaître ultérieurement.</p>
      <p>« Utilisateur » ou « visiteur » désigne toute personne consultant le site, quel que soit son mode d’accès.</p>
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
