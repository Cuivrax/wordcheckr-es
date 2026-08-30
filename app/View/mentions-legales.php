<?php

declare(strict_types=1);

/**
 * Vue statique /aviso-legal (ES-020), appelee par public/index.php sans donnees de recherche
 * (page d'information pure, aucune requete SQLite). Meme gabarit que les autres vues
 * (header/footer identiques, .word-card/.direct reutilises tel quel pour chaque rubrique,
 * pas de nouveau motif visuel). Nom de fichier interne (mentions-legales.php) INCHANGE --
 * identifiant technique, pas une URL, meme convention que word.php/contact.php (ES-019).
 *
 * CONTENU REEL EN ESPAGNOL (ES-020, remplace le contenu francais precedent). Identite de
 * l'editeur (BIGBANG MEDIA) et de l'hebergeur (o2switch) reprises A L'IDENTIQUE de
 * mentions-legales.php cote francais (D-025ter, sources verifiees a l'epoque aupres de
 * RCS/INPI/Infogreffe, jamais inventees) -- meme personne morale, memes faits, traduits en
 * espagnol et restructures selon le formalisme habituel d'un Aviso Legal (LSSI-CE, art. 10)
 * plutot que traduits mot a mot depuis la structure francaise (LCEN). Nom personnel, adresse
 * complete du siege et email restent volontairement absents (meme demande explicite du
 * proprietaire du produit que D-025ter, reconduite ici a l'identique) -- cet ecart est signale
 * ci-dessous dans la rubrique "Editor", pas silencieusement comble. BIGBANG MEDIA est etablie
 * en France, seul Etat membre de l'UE ou elle a un etablissement -- aucun representant
 * espagnol au sens de l'article 27 RGPD n'est requis (cette obligation ne vise que les
 * responsables etablis HORS UE, pas un etablissement intra-UE proposant ses services dans un
 * autre Etat membre).
 *
 * Ponctuation espagnole : signes d'ouverture ¿/¡ omis ici (aucune question/exclamation dans ce
 * contenu factuel), pas de tiret cadratin medial (meme discipline typographique que le reste
 * du site ES).
 */

require __DIR__ . '/helpers.php';

/** @var \App\Seo\SeoMeta $seo */
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="<?= e($seo->robots) ?>">
<title>Aviso Legal | WORD CHECKR</title>
<meta name="description" content="Aviso legal de WORD CHECKR: editor, alojamiento, propiedad intelectual, cookies e información legal completa del sitio.">
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
  <nav class="breadcrumb" aria-label="Migas de pan"><a href="/">Inicio</a> › Aviso Legal</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title">Aviso Legal</h1>
      <p>Editor, alojamiento, propiedad intelectual e información legal completa del sitio.</p>
    </section>

    <section class="direct">
      <h2>Índice</h2>
      <ul class="legal-toc">
        <li><a href="#editor">Editor Del Sitio</a></li>
        <li><a href="#director">Director De La Publicación</a></li>
        <li><a href="#alojamiento">Alojamiento</a></li>
        <li><a href="#desarrollo">Diseño Y Desarrollo</a></li>
        <li><a href="#propiedad-intelectual">Propiedad Intelectual</a></li>
        <li><a href="#enlaces">Enlaces Externos</a></li>
        <li><a href="#cookies">Cookies Y Rastreadores</a></li>
        <li><a href="#terceros">Aplicaciones Y Servicios De Terceros</a></li>
        <li><a href="#datos">Datos Personales</a></li>
        <li><a href="#accesibilidad">Accesibilidad</a></li>
        <li><a href="#disponibilidad">Disponibilidad Y Mantenimiento</a></li>
        <li><a href="#modificaciones">Modificaciones De Este Aviso</a></li>
        <li><a href="#derecho-aplicable">Derecho Aplicable Y Litigios</a></li>
        <li><a href="#definiciones">Definiciones</a></li>
      </ul>
    </section>

    <section class="direct" id="editor">
      <h2>Editor Del Sitio</h2>
      <p>El presente sitio WORD CHECKR, accesible en www.wordcheckr.es, está editado por la sociedad BIGBANG MEDIA.</p>
      <p>Denominación social: BIGBANG MEDIA.</p>
      <p>Forma jurídica: EURL (sociedad unipersonal de responsabilidad limitada de derecho francés), con un capital social de 1.000 €.</p>
      <p>Inscripción: RCS Laval, SIREN 917 929 382, SIRET 917 929 382 00013 (registro mercantil francés, equivalente al Registro Mercantil español).</p>
      <p>Actividad principal declarada: código APE/NAF 6201Z, programación informática. El objeto social abarca la creación, gestión, posicionamiento y explotación de sitios web.</p>
      <p>Domicilio social: 53000 Laval, Francia. Por motivos de privacidad, la dirección completa del domicilio no se publica voluntariamente en esta página; permanece consultable en los registros públicos oficiales franceses (Infogreffe, INPI, directorio de empresas data.gouv.fr) para cualquier persona que desee verificarla por esa vía. Esta ausencia deliberada frente a la habitual exhaustividad de un aviso legal (LSSI-CE, art. 10) ha sido comunicada al propietario del sitio, no resuelta en silencio; el <a href="/contact">formulario de contacto</a> ofrece un canal de contacto real, sin publicar nunca una dirección de correo electrónico.</p>
    </section>

    <section class="direct" id="director">
      <h2>Director De La Publicación</h2>
      <p>El director de la publicación es el representante legal de la sociedad BIGBANG MEDIA, designado por su función y no nominalmente en esta página, por decisión de privacidad del propietario del sitio.</p>
      <p>Cualquier pregunta relativa a la dirección de la publicación puede dirigirse a través de nuestro <a href="/contact">formulario de contacto</a>.</p>
    </section>

    <section class="direct" id="alojamiento">
      <h2>Alojamiento</h2>
      <p>El sitio está alojado por la sociedad o2switch.</p>
      <p>Denominación social: o2switch.</p>
      <p>Forma jurídica: SAS (sociedad por acciones simplificada de derecho francés), con un capital social de 100.000 €.</p>
      <p>Domicilio social: Chemin des Pardiaux, 63000 Clermont-Ferrand, Francia.</p>
      <p>Inscripción: RCS Clermont-Ferrand, SIREN 510 909 807, SIRET 510 909 807 00032.</p>
      <p>Teléfono: +33 4 44 44 60 40.</p>
      <p>Sitio oficial: <a href="https://www.o2switch.fr">o2switch.fr</a>.</p>
      <p>El servidor físico y la totalidad de los datos del sitio se encuentran en Francia, en el territorio de la Unión Europea.</p>
    </section>

    <section class="direct" id="desarrollo">
      <h2>Diseño Y Desarrollo</h2>
      <p>El diseño, el desarrollo y el mantenimiento técnico del sitio corren directamente a cargo de BIGBANG MEDIA, sin recurrir a una agencia externa ni a un proveedor externo para el código de la aplicación.</p>
      <p>El sitio está desarrollado en PHP, sin framework de aplicación, con una base de datos local en modo solo lectura y un mínimo de JavaScript en el navegador, únicamente para mejoras progresivas (autocompletado de búsqueda, visualización de las fichas de letras) que nunca impiden el funcionamiento del sitio sin JavaScript activado.</p>
    </section>

    <section class="direct" id="propiedad-intelectual">
      <h2>Propiedad Intelectual</h2>
      <p>La estructura del sitio, su motor de búsqueda, el algoritmo de cálculo de puntuaciones, la organización y estructuración de la base de palabras, los textos, el diseño, el código fuente, las hojas de estilo y el conjunto de elementos técnicos y editoriales del sitio son propiedad exclusiva de BIGBANG MEDIA, salvo indicación contraria.</p>
      <p>Esta protección se ejerce en particular en virtud del derecho de autor francés (Code de la propriété intellectuelle, art. L111-1 y siguientes), equivalente al derecho de autor español (Real Decreto Legislativo 1/1996), y, para la estructuración y organización de la base de palabras, en virtud del derecho sui generis sobre bases de datos (Code de la propriété intellectuelle, art. L341-1 y siguientes), equivalente a los artículos 133 y siguientes de la misma ley española.</p>
      <p>El idioma español y el estatus de sus palabras respecto a los diccionarios oficiales del Scrabble no son propiedad de nadie. Este sitio no reivindica ningún derecho sobre las palabras en sí mismas, únicamente sobre su propia construcción técnica y editorial, es decir, la forma en que esta información se organiza, calcula y presenta.</p>
      <p>Queda prohibida toda reproducción, representación, modificación, publicación o adaptación de la totalidad o parte de los elementos del sitio, cualquiera que sea el medio o procedimiento utilizado, sin la autorización escrita previa de BIGBANG MEDIA, salvo para un uso estrictamente personal y no comercial, dentro de los límites previstos por la normativa de propiedad intelectual aplicable.</p>
      <p>El nombre WORD CHECKR, así como los elementos gráficos distintivos del sitio, no pueden utilizarse sin autorización previa.</p>
    </section>

    <section class="direct" id="enlaces">
      <h2>Enlaces Externos</h2>
      <p>El sitio contiene un número deliberadamente reducido de enlaces salientes, esencialmente hacia instituciones oficiales (como la CNIL) o hacia su proveedor de alojamiento. BIGBANG MEDIA no ejerce ningún control sobre el contenido de los sitios de terceros así enlazados y declina toda responsabilidad sobre su contenido, disponibilidad o sus propias prácticas en materia de datos personales.</p>
      <p>El establecimiento de un enlace de hipertexto hacia este sitio es en principio libre, siempre que dicho enlace no perjudique los intereses de BIGBANG MEDIA y se retire ante una simple solicitud. La técnica de enlaces profundos o de integración del sitio en un marco ("framing") sin autorización previa no está permitida.</p>
    </section>

    <section class="direct" id="cookies">
      <h2>Cookies Y Rastreadores</h2>
      <p>Este sitio no instala ninguna cookie, ya sea estrictamente necesaria, funcional, de medición de audiencia o publicitaria. No se utiliza ningún rastreador, píxel invisible ni tecnología equivalente, bajo ninguna forma.</p>
      <p>Por tanto, no se muestra ningún banner de consentimiento de cookies en este sitio: esta formalidad carecería de objeto en ausencia de todo depósito de cookie o rastreador en el sentido del artículo 22 de la Ley de Servicios de la Sociedad de la Información (LSSI-CE) y del RGPD.</p>
      <p>El detalle completo de esta ausencia de recopilación figura en nuestra <a href="/confidentialite">política de privacidad</a>.</p>
    </section>

    <section class="direct" id="terceros">
      <h2>Aplicaciones Y Servicios De Terceros</h2>
      <p>Por decisión deliberada, este sitio no integra ningún servicio de terceros susceptible de recopilar datos o de ralentizar su visualización. Concretamente, a fecha de redacción de esta página, el sitio no utiliza:</p>
      <ul class="legal-list">
        <li>ninguna herramienta de medición de audiencia o análisis estadístico (como Google Analytics, Matomo o equivalente);</li>
        <li>ninguna fuente tipográfica alojada de forma remota (como Google Fonts), todas las fuentes utilizadas son fuentes del sistema ya presentes en el dispositivo de la persona visitante;</li>
        <li>ninguna red de distribución de contenido externa (CDN) para la carga del código, los estilos o las imágenes del sitio;</li>
        <li>ningún módulo de red social integrado (botón de compartir, widget de "me gusta" o comentarios);</li>
        <li>ningún vídeo ni mapa alojado por un servicio de terceros (como YouTube o Google Maps);</li>
        <li>ninguna herramienta de mensajería instantánea o chat en línea proporcionada por un tercero;</li>
        <li>ninguna central publicitaria ni red de retargeting publicitario;</li>
        <li>ningún servicio de inicio de sesión único de terceros (como "iniciar sesión con Google" o "iniciar sesión con Facebook"), el sitio no ofrece por otra parte ninguna cuenta de usuario;</li>
        <li>ningún servicio de pago en línea, el sitio es totalmente gratuito y sin funcionalidad de venta.</li>
      </ul>
      <p>El único agente tercero implicado en el funcionamiento del sitio es su proveedor de alojamiento, o2switch, descrito en la sección "Alojamiento" anterior, así como el servicio de mensajería utilizado para el envío de los mensajes desde nuestro <a href="/contact">formulario de contacto</a>.</p>
    </section>

    <section class="direct" id="datos">
      <h2>Datos Personales</h2>
      <p>El tratamiento de los datos personales, las categorías de datos afectadas, su base legal, su plazo de conservación y las modalidades de ejercicio de sus derechos se detallan íntegramente en nuestra <a href="/confidentialite">política de privacidad</a>.</p>
    </section>

    <section class="direct" id="accesibilidad">
      <h2>Accesibilidad</h2>
      <p>Este sitio está diseñado para seguir siendo utilizable sin JavaScript, con un contraste de colores cuidado, una navegación por teclado funcional y una estructura de encabezados coherente. Todavía no es objeto de una declaración de accesibilidad formal en el sentido de las Pautas de Accesibilidad para el Contenido Web (WCAG), pero la accesibilidad sigue siendo un objetivo perseguido en el diseño del sitio.</p>
      <p>Si encuentra alguna dificultad de accesibilidad al utilizar este sitio, puede comunicárnoslo a través de nuestro <a href="/contact">formulario de contacto</a>.</p>
    </section>

    <section class="direct" id="disponibilidad">
      <h2>Disponibilidad Y Mantenimiento Del Sitio</h2>
      <p>BIGBANG MEDIA se esfuerza por garantizar un acceso continuo al sitio, sin garantía absoluta de disponibilidad permanente. El sitio puede interrumpirse temporalmente por operaciones de mantenimiento, una actualización técnica o por cualquier causa ajena al control razonable del editor (avería del proveedor de alojamiento, incidente de red).</p>
      <p>La información mostrada por el sitio (admisibilidad al Scrabble, puntuaciones, listas de palabras) se proporciona con fines orientativos y puede, en casos excepcionales, contener un error u omisión pese al cuidado puesto en su elaboración.</p>
    </section>

    <section class="direct" id="modificaciones">
      <h2>Modificaciones De Este Aviso</h2>
      <p>BIGBANG MEDIA se reserva el derecho de modificar el presente aviso legal en cualquier momento, en particular para adaptarse a una evolución legislativa o reglamentaria, o para reflejar un cambio en la organización del sitio. Le invitamos a consultar esta página regularmente.</p>
      <p>Última actualización: agosto de 2026.</p>
    </section>

    <section class="direct" id="derecho-aplicable">
      <h2>Derecho Aplicable Y Litigios</h2>
      <p>El presente aviso legal se rige por el derecho francés, con exclusión de cualquier otra legislación. Cualquier litigio relativo al uso del sitio corresponde, a falta de resolución amistosa previa, a la competencia exclusiva de los tribunales franceses.</p>
    </section>

    <section class="direct" id="definiciones">
      <h2>Definiciones</h2>
      <p>"Editor" designa a la persona jurídica responsable del contenido publicado en el sitio, en este caso BIGBANG MEDIA.</p>
      <p>"Proveedor de alojamiento" designa a la sociedad que garantiza el almacenamiento técnico del sitio en sus servidores, en este caso o2switch.</p>
      <p>"Cookie" o "rastreador" designa cualquier archivo o información depositada en el equipo de una persona usuaria durante su navegación, que permite reconocerla posteriormente.</p>
      <p>"Usuario" o "visitante" designa a cualquier persona que consulte el sitio, sea cual sea su modo de acceso.</p>
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
    <span>Herramienta independiente para juegos de palabras.</span>
    <span class="footer-links"><a href="/aviso-legal">Aviso Legal</a> · <a href="/privacidad">Privacidad</a> · <a href="/contact">Contacto</a></span>
  </div>
</footer>
</body>
</html>
