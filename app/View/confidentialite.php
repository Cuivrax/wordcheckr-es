<?php

declare(strict_types=1);

/**
 * Vue statique /confidentialite, appelee par public/index.php sans donnees de recherche
 * (page d'information pure, aucune requete SQLite). Meme gabarit que app/View/mentions-legales.php.
 *
 * Contenu verifie contre le code reel avant redaction (pas suppose) : aucun cookie
 * (public/assets/js/, app/View/ inspectes), aucune session PHP, aucun formulaire en POST
 * hormis /contact (D-025ter, mail() natif, aucune donnee stockee), aucune table applicative
 * de comptes/donnees utilisateur dans schema.sql -- toutes les routes de recherche restent
 * des GET, la base storage/dictionary_fr.sqlite est ouverte en lecture seule au runtime
 * (D-001/D-016). Cette politique reflete donc un etat reel, pas un modele de texte generique.
 *
 * Ponctuation : aucun tiret cadratin, aucun deux-points en milieu de phrase (demande
 * explicite du proprietaire du produit) -- voir l'entete de mentions-legales.php pour le
 * detail de cette convention.
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
<title>Politique De Confidentialité | WORD CHECKR</title>
<meta name="description" content="Politique de confidentialité complète de WORD CHECKR, données collectées, cookies, services tiers et exercice de vos droits.">
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
  <nav class="breadcrumb" aria-label="Fil d’Ariane"><a href="/">Accueil</a> › Confidentialité</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title">Politique De Confidentialité</h1>
      <p>Quelles données sont réellement collectées, et comment exercer vos droits.</p>
    </section>

    <section class="direct">
      <h2>Sommaire</h2>
      <ul class="legal-toc">
        <li><a href="#preambule">Préambule</a></li>
        <li><a href="#responsable">Responsable du traitement</a></li>
        <li><a href="#donnees-collectees">Données collectées</a></li>
        <li><a href="#base-legale">Base légale des traitements</a></li>
        <li><a href="#finalites">Finalités du traitement</a></li>
        <li><a href="#conservation">Durée de conservation</a></li>
        <li><a href="#cookies">Cookies et traceurs</a></li>
        <li><a href="#tiers">Services et scripts tiers</a></li>
        <li><a href="#destinataires">Destinataires des données</a></li>
        <li><a href="#transferts">Transferts hors Union européenne</a></li>
        <li><a href="#securite">Sécurité des données</a></li>
        <li><a href="#droits">Vos droits</a></li>
        <li><a href="#exercice">Comment exercer vos droits</a></li>
        <li><a href="#cnil">Réclamation auprès de la CNIL</a></li>
        <li><a href="#mineurs">Données des mineurs</a></li>
        <li><a href="#modifications">Modifications de la politique</a></li>
        <li><a href="#glossaire">Glossaire</a></li>
      </ul>
    </section>

    <section class="direct" id="preambule">
      <h2>Préambule</h2>
      <p>BIGBANG MEDIA attache une importance particulière au respect de la vie privée des utilisateurs de WORD CHECKR. Cette politique explique, en détail et sans formule vague, quelles données sont réellement traitées lors de l’utilisation du site, dans quel but, pendant combien de temps, et comment exercer les droits que vous tenez du règlement général sur la protection des données (RGPD) et de la loi Informatique et Libertés.</p>
      <p>Cette politique complète nos <a href="/mentions-legales">mentions légales</a>, qui identifient l’éditeur et l’hébergeur du site.</p>
    </section>

    <section class="direct" id="responsable">
      <h2>Responsable Du Traitement</h2>
      <p>Le responsable du traitement des données, au sens du RGPD, est la société BIGBANG MEDIA, EURL immatriculée au RCS de Laval sous le SIREN 917 929 382, dont le siège est situé à Laval (53000), France.</p>
    </section>

    <section class="direct" id="donnees-collectees">
      <h2>Données Collectées</h2>
      <p>Ce site ne dispose d’aucun compte utilisateur, d’aucun profil, d’aucun panier ni d’aucune préférence enregistrée d’une visite à l’autre.</p>
      <p>Chaque fonctionnalité du site (vérifier un mot, trouver les mots jouables avec un tirage de lettres, lister des mots selon des critères de longueur, de lettres ou de position) fonctionne par une simple adresse consultée en lecture, sans formulaire enregistrant de données ni base de données d’usage. La recherche est traitée à la volée par le serveur puis oubliée aussitôt la réponse envoyée ; elle n’est conservée dans aucune base de données applicative.</p>
      <p>Le seul formulaire du site qui transmet une information saisie par vous est le <a href="/contact">formulaire de contact</a>. Il vous demande un message, votre adresse email (pour pouvoir vous répondre) et, si vous le souhaitez, votre nom. Ce message est transmis par email à l’éditeur du site puis n’est conservé nulle part sur nos serveurs ; il n’existe aucune base de données des messages envoyés.</p>
      <p>En dehors de ce formulaire de contact, la seule donnée techniquement associée à votre visite est celle décrite dans la rubrique « Données collectées par l’hébergeur » ci-dessous, qui ne dépend pas d’une action volontaire de votre part.</p>
    </section>

    <section class="direct" id="base-legale">
      <h2>Base Légale Des Traitements</h2>
      <p>Le traitement du message envoyé via le formulaire de contact repose sur votre consentement explicite, matérialisé par l’envoi volontaire du formulaire (article 6.1.a du RGPD).</p>
      <p>La conservation temporaire de données techniques de connexion par l’hébergeur, décrite plus loin, repose sur le respect d’une obligation légale à laquelle est soumis l’hébergeur (article 6.1.c du RGPD, en lien avec l’article 6.II de la loi pour la confiance dans l’économie numérique), ainsi que sur l’intérêt légitime de l’éditeur et de l’hébergeur à assurer la sécurité du site (article 6.1.f du RGPD).</p>
    </section>

    <section class="direct" id="finalites">
      <h2>Finalités Du Traitement</h2>
      <p>Les données du formulaire de contact sont utilisées dans l’unique but de répondre à votre message. Elles ne servent à aucune autre finalité, notamment ni à de la prospection commerciale, ni à du profilage, ni à une quelconque forme de segmentation marketing.</p>
      <p>Les données techniques de connexion conservées par l’hébergeur servent exclusivement à la sécurité du service (détection d’abus, réponse à une éventuelle réquisition judiciaire) et ne sont jamais exploitées par l’éditeur du site à des fins d’analyse d’audience ou de suivi individuel.</p>
    </section>

    <section class="direct" id="conservation">
      <h2>Durée De Conservation</h2>
      <p>Les messages reçus via le formulaire de contact sont conservés dans la boîte email de l’éditeur le temps nécessaire au traitement de votre demande, puis archivés ou supprimés selon les pratiques usuelles de gestion de correspondance, sans durée de conservation systématique prédéfinie au-delà de ce qui est raisonnablement utile pour assurer un suivi.</p>
      <p>Les données techniques de connexion conservées par l’hébergeur le sont pour la durée prévue par la réglementation française applicable aux hébergeurs, actuellement fixée à un an par les textes en vigueur relatifs à la conservation des données de connexion.</p>
    </section>

    <section class="direct" id="cookies">
      <h2>Cookies Et Traceurs</h2>
      <p>La Commission Nationale de l’Informatique et des Libertés (CNIL) distingue plusieurs catégories de cookies : les cookies strictement nécessaires au fonctionnement d’un service (comme un cookie de session pour un panier d’achat ou une connexion), les cookies de préférence, les cookies de mesure d’audience et les cookies publicitaires ou de ciblage.</p>
      <p>Ce site n’utilise aucune de ces catégories. Aucun cookie strictement nécessaire n’est requis, le site ne proposant ni compte, ni panier, ni connexion à conserver d’une page à l’autre. Aucun cookie de préférence, de mesure d’audience ou publicitaire n’est déposé non plus.</p>
      <p>Aucune technologie assimilée à un cookie (stockage local du navigateur utilisé à des fins de suivi, identifiant généré côté client, empreinte de terminal ou « fingerprinting ») n’est utilisée par ce site.</p>
      <p>En l’absence de tout cookie ou traceur, aucune bannière de consentement n’est affichée : elle n’aurait pas d’objet, la loi n’exigeant un recueil du consentement que lorsqu’un cookie non strictement nécessaire est effectivement déposé.</p>
    </section>

    <section class="direct" id="tiers">
      <h2>Services Et Scripts Tiers</h2>
      <p>Aucun script ni service tiers n’est chargé sur ce site à des fins de suivi ou de profilage. Concrètement, le site n’intègre ni Google Analytics, ni Matomo, ni aucun autre outil de mesure d’audience ; ni Google Fonts ni aucune autre police hébergée à distance ; ni script publicitaire, ni pixel de conversion, ni réseau de reciblage ; ni bouton ou widget de réseau social ; ni vidéo ni carte hébergée par un service tiers ; ni outil de chat ou de support client tiers ; ni service de connexion unique (« se connecter avec » un compte tiers).</p>
      <p>Le seul acteur technique tiers impliqué dans le fonctionnement du site est son hébergeur, o2switch, décrit dans nos <a href="/mentions-legales">mentions légales</a>, ainsi que le service de messagerie utilisé pour transmettre les messages du formulaire de contact.</p>
      <p>Cette liste reflète l’état du site à la date de mise à jour de cette politique, indiquée en bas de page. Toute évolution future ajoutant un service tiers ferait l’objet d’une mise à jour de cette section avant sa mise en service.</p>
    </section>

    <section class="direct" id="destinataires">
      <h2>Destinataires Des Données</h2>
      <p>Les messages envoyés via le formulaire de contact sont reçus uniquement par l’éditeur du site, BIGBANG MEDIA. Aucune donnée n’est vendue, louée, cédée ou communiquée à un tiers à des fins commerciales, publicitaires ou statistiques.</p>
      <p>Les données techniques conservées par l’hébergeur ne sont accessibles qu’à l’hébergeur lui-même et, le cas échéant, à une autorité judiciaire ou administrative légalement habilitée à les requérir.</p>
    </section>

    <section class="direct" id="transferts">
      <h2>Transferts Hors Union Européenne</h2>
      <p>L’ensemble des traitements décrits dans cette politique a lieu en France. Le site est hébergé en France par o2switch, et aucune donnée n’est transmise à un prestataire situé hors de l’Union européenne. Aucun transfert de données hors de l’Union européenne n’a donc lieu dans le cadre de l’utilisation de ce site.</p>
    </section>

    <section class="direct" id="securite">
      <h2>Sécurité Des Données</h2>
      <p>Le site est conçu selon un principe de minimisation par construction : la base de données des mots est ouverte en lecture seule au moment de l’exécution, ce qui empêche techniquement toute écriture accidentelle ou malveillante sur cette base depuis le site public. Le site ne conserve par ailleurs aucune base de données d’utilisateurs ou de messages, ce qui réduit d’autant la surface exposée en cas d’incident de sécurité.</p>
      <p>Les échanges entre votre navigateur et le serveur sont sécurisés par le protocole HTTPS. L’hébergeur o2switch applique ses propres mesures de sécurité physiques et logiques sur son infrastructure, décrites sur son site officiel.</p>
    </section>

    <section class="direct" id="droits">
      <h2>Vos Droits</h2>
      <p>Conformément au RGPD et à la loi Informatique et Libertés, vous disposez des droits suivants sur vos données personnelles.</p>
      <ul class="legal-list">
        <li>Droit d’accès : obtenir la confirmation qu’une donnée vous concernant est traitée, et en obtenir une copie.</li>
        <li>Droit de rectification : faire corriger une donnée inexacte ou incomplète vous concernant.</li>
        <li>Droit à l’effacement (« droit à l’oubli ») : demander la suppression de vos données, dans les cas prévus par le RGPD.</li>
        <li>Droit à la limitation du traitement : demander la suspension temporaire d’un traitement, dans certains cas prévus par le RGPD.</li>
        <li>Droit d’opposition : vous opposer à un traitement fondé sur l’intérêt légitime, pour des raisons tenant à votre situation particulière.</li>
        <li>Droit à la portabilité : recevoir les données que vous nous avez fournies dans un format structuré et couramment utilisé, lorsque ce droit est applicable.</li>
        <li>Droit de retirer votre consentement à tout moment, lorsque le traitement repose sur ce consentement, sans que cela affecte la licéité du traitement effectué avant ce retrait.</li>
      </ul>
      <p>Le site n’exploitant aucune donnée personnelle identifiable en dehors du formulaire de contact que vous choisissez librement de remplir, l’exercice de ces droits concerne en pratique essentiellement les messages que vous nous auriez adressés.</p>
    </section>

    <section class="direct" id="exercice">
      <h2>Comment Exercer Vos Droits</h2>
      <p>Vous pouvez exercer l’ensemble des droits décrits ci-dessus en nous écrivant via notre <a href="/contact">formulaire de contact</a>, en précisant l’objet de votre demande et le droit que vous souhaitez exercer.</p>
      <p>Afin de protéger vos données contre une demande frauduleuse formulée en votre nom, nous pouvons être amenés à vous demander de confirmer votre identité par l’adresse email utilisée lors d’un échange précédent, avant de donner suite à votre demande.</p>
    </section>

    <section class="direct" id="cnil">
      <h2>Réclamation Auprès De La CNIL</h2>
      <p>Si vous estimez, après nous avoir contactés, que vos droits ne sont pas respectés, vous disposez du droit d’introduire une réclamation auprès de la Commission Nationale de l’Informatique et des Libertés (CNIL), autorité française de contrôle en matière de protection des données.</p>
      <p>Site officiel de la CNIL : <a href="https://www.cnil.fr">cnil.fr</a>. Adresse postale : CNIL, 3 Place de Fontenoy, TSA 80715, 75334 Paris Cedex 07, France.</p>
    </section>

    <section class="direct" id="mineurs">
      <h2>Données Des Mineurs</h2>
      <p>Ce site est un outil grand public qui ne cible pas spécifiquement un public mineur et ne demande jamais d’information relative à l’âge de ses visiteurs. Le formulaire de contact reste néanmoins accessible à toute personne, y compris mineure, qui souhaiterait nous écrire ; dans ce cas, les mêmes principes de minimisation des données décrits dans cette politique s’appliquent.</p>
    </section>

    <section class="direct" id="modifications">
      <h2>Modifications De La Politique</h2>
      <p>Cette politique de confidentialité peut être mise à jour pour refléter une évolution du site, de ses fonctionnalités, ou de la réglementation applicable. La version en vigueur est toujours celle publiée sur cette page.</p>
      <p>Dernière mise à jour : août 2026.</p>
    </section>

    <section class="direct" id="glossaire">
      <h2>Glossaire</h2>
      <p>« Donnée personnelle » désigne toute information se rapportant à une personne physique identifiée ou identifiable, directement ou indirectement.</p>
      <p>« Traitement » désigne toute opération portant sur des données personnelles, comme leur collecte, leur conservation ou leur suppression.</p>
      <p>« Responsable du traitement » désigne la personne ou l’organisme qui détermine les finalités et les moyens d’un traitement de données personnelles.</p>
      <p>« RGPD » désigne le règlement général sur la protection des données, règlement européen entré en application le 25 mai 2018.</p>
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
