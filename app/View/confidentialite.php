<?php

declare(strict_types=1);

/**
 * Vue statique /privacidad (ES-020), appelee par public/index.php sans donnees de recherche
 * (page d'information pure, aucune requete SQLite). Meme gabarit que app/View/mentions-legales.php.
 * Nom de fichier interne (confidentialite.php) INCHANGE -- identifiant technique, pas une URL
 * (ES-019).
 *
 * CONTENU REEL EN ESPAGNOL (ES-020, remplace le contenu francais precedent), restructure
 * selon le formalisme habituel d'une Politica de Privacidad RGPD/LOPDGDD plutot que traduit
 * mot a mot depuis la structure RGPD francaise -- memes faits reels que confidentialite.php
 * cote francais (D-025ter) : aucun cookie, aucune session, seul le formulaire /contact
 * transmet une donnee saisie (mail() natif, rien de stocke cote serveur),
 * storage/dictionary_es.sqlite ouvert en lecture seule au runtime. BIGBANG MEDIA est etablie
 * en France (seul etablissement dans l'UE) : la CNIL reste l'autorite de controle CHEF DE FILE
 * au sens du "guichet unique" RGPD (article 56) -- mentionnee ci-dessous comme telle, PAS
 * remplacee par une autorite espagnole fictive. La rubrique reclamation (ex-#cnil) rappelle
 * neanmoins le droit de toute personne, garanti par l'article 77 RGPD, de saisir egalement
 * l'autorite de son propre Etat de residence (l'AEPD pour l'Espagne) -- exactitude juridique
 * verifiee avant redaction, pas supposee. LOPDGDD (loi organique espagnole 3/2018) citee en
 * complement du RGPD la ou pertinent, meme registre que la loi Informatique et Libertes cotee
 * cote francais.
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
<title>Política De Privacidad | WORD CHECKR</title>
<meta name="description" content="Política de privacidad completa de WORD CHECKR: datos recopilados, cookies, servicios de terceros y ejercicio de sus derechos.">
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
  <nav class="breadcrumb" aria-label="Migas de pan"><a href="/">Inicio</a> › Privacidad</nav>

  <article class="word-card">
    <section class="word-answer">
      <h1 class="word-title">Política De Privacidad</h1>
      <p>Qué datos se recopilan realmente, y cómo ejercer sus derechos.</p>
    </section>

    <section class="direct">
      <h2>Índice</h2>
      <ul class="legal-toc">
        <li><a href="#preambulo">Preámbulo</a></li>
        <li><a href="#responsable">Responsable Del Tratamiento</a></li>
        <li><a href="#datos-recopilados">Datos Recopilados</a></li>
        <li><a href="#base-legal">Base Legal De Los Tratamientos</a></li>
        <li><a href="#finalidades">Finalidades Del Tratamiento</a></li>
        <li><a href="#conservacion">Plazo De Conservación</a></li>
        <li><a href="#cookies">Cookies Y Rastreadores</a></li>
        <li><a href="#terceros">Servicios Y Scripts De Terceros</a></li>
        <li><a href="#destinatarios">Destinatarios De Los Datos</a></li>
        <li><a href="#transferencias">Transferencias Fuera De La UE</a></li>
        <li><a href="#seguridad">Seguridad De Los Datos</a></li>
        <li><a href="#derechos">Sus Derechos</a></li>
        <li><a href="#ejercicio">Cómo Ejercer Sus Derechos</a></li>
        <li><a href="#autoridad-control">Reclamación Ante Una Autoridad De Control</a></li>
        <li><a href="#menores">Datos De Menores</a></li>
        <li><a href="#modificaciones">Modificaciones De Esta Política</a></li>
        <li><a href="#glosario">Glosario</a></li>
      </ul>
    </section>

    <section class="direct" id="preambulo">
      <h2>Preámbulo</h2>
      <p>BIGBANG MEDIA concede especial importancia al respeto de la privacidad de las personas usuarias de WORD CHECKR. Esta política explica, en detalle y sin fórmulas vagas, qué datos se tratan realmente al utilizar el sitio, con qué finalidad, durante cuánto tiempo, y cómo ejercer los derechos que le otorga el Reglamento General de Protección de Datos (RGPD) y la Ley Orgánica 3/2018 de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD).</p>
      <p>Esta política complementa nuestro <a href="/mentions-legales">aviso legal</a>, que identifica al editor y al proveedor de alojamiento del sitio.</p>
    </section>

    <section class="direct" id="responsable">
      <h2>Responsable Del Tratamiento</h2>
      <p>El responsable del tratamiento de los datos, en el sentido del RGPD, es la sociedad BIGBANG MEDIA, EURL de derecho francés inscrita en el RCS de Laval con el número SIREN 917 929 382, cuyo domicilio social se encuentra en Laval (53000), Francia.</p>
      <p>BIGBANG MEDIA está establecida únicamente en Francia. Como responsable establecido en la Unión Europea que ofrece también sus servicios en otros Estados miembros, no resulta necesario, conforme al artículo 27 del RGPD, designar un representante adicional en España; esta obligación solo afecta a los responsables sin ningún establecimiento en la UE.</p>
    </section>

    <section class="direct" id="datos-recopilados">
      <h2>Datos Recopilados</h2>
      <p>Este sitio no dispone de ninguna cuenta de usuario, ningún perfil, ninguna cesta de la compra ni ninguna preferencia guardada de una visita a otra.</p>
      <p>Cada función del sitio (verificar una palabra, encontrar las palabras jugables con una tirada de letras, listar palabras según criterios de longitud, letras o posición) funciona mediante una simple dirección consultada en modo lectura, sin formulario que registre datos ni base de datos de uso. La búsqueda es procesada al vuelo por el servidor y olvidada inmediatamente después de enviar la respuesta; no se conserva en ninguna base de datos de la aplicación.</p>
      <p>El único formulario del sitio que transmite una información introducida por usted es el <a href="/contact">formulario de contacto</a>. Le solicita un mensaje, su dirección de correo electrónico (para poder responderle) y, si lo desea, su nombre. Este mensaje se transmite por correo electrónico al editor del sitio y a continuación no se conserva en ningún lugar de nuestros servidores; no existe ninguna base de datos de los mensajes enviados.</p>
      <p>Al margen de este formulario de contacto, el único dato técnicamente asociado a su visita es el descrito en la sección "Datos recopilados por el proveedor de alojamiento" más abajo, que no depende de una acción voluntaria por su parte.</p>
    </section>

    <section class="direct" id="base-legal">
      <h2>Base Legal De Los Tratamientos</h2>
      <p>El tratamiento del mensaje enviado a través del formulario de contacto se basa en su consentimiento explícito, materializado por el envío voluntario del formulario (artículo 6.1.a del RGPD).</p>
      <p>La conservación temporal de datos técnicos de conexión por parte del proveedor de alojamiento, descrita más adelante, se basa en el cumplimiento de una obligación legal a la que está sujeto dicho proveedor (artículo 6.1.c del RGPD, en relación con la ley francesa para la confianza en la economía digital), así como en el interés legítimo del editor y del proveedor de alojamiento en garantizar la seguridad del sitio (artículo 6.1.f del RGPD).</p>
    </section>

    <section class="direct" id="finalidades">
      <h2>Finalidades Del Tratamiento</h2>
      <p>Los datos del formulario de contacto se utilizan con el único fin de responder a su mensaje. No sirven para ninguna otra finalidad, en particular ni para prospección comercial, ni para elaboración de perfiles, ni para ninguna forma de segmentación de marketing.</p>
      <p>Los datos técnicos de conexión conservados por el proveedor de alojamiento sirven exclusivamente para la seguridad del servicio (detección de abusos, respuesta a un eventual requerimiento judicial) y nunca son utilizados por el editor del sitio con fines de análisis de audiencia o seguimiento individual.</p>
    </section>

    <section class="direct" id="conservacion">
      <h2>Plazo De Conservación</h2>
      <p>Los mensajes recibidos a través del formulario de contacto se conservan en el buzón de correo electrónico del editor durante el tiempo necesario para tramitar su solicitud, y a continuación se archivan o eliminan según las prácticas habituales de gestión de correspondencia, sin un plazo de conservación sistemático predefinido más allá de lo razonablemente útil para garantizar un seguimiento.</p>
      <p>Los datos técnicos de conexión conservados por el proveedor de alojamiento se conservan durante el plazo previsto por la normativa francesa aplicable a los proveedores de alojamiento, actualmente fijado en un año por la normativa vigente en materia de conservación de datos de conexión.</p>
    </section>

    <section class="direct" id="cookies">
      <h2>Cookies Y Rastreadores</h2>
      <p>Se distinguen varias categorías de cookies: las cookies estrictamente necesarias para el funcionamiento de un servicio (como una cookie de sesión para una cesta de la compra o un inicio de sesión), las cookies de preferencia, las cookies de medición de audiencia y las cookies publicitarias o de segmentación.</p>
      <p>Este sitio no utiliza ninguna de estas categorías. No se requiere ninguna cookie estrictamente necesaria, ya que el sitio no ofrece ni cuenta, ni cesta de la compra, ni inicio de sesión que conservar de una página a otra. Tampoco se instala ninguna cookie de preferencia, de medición de audiencia o publicitaria.</p>
      <p>No se utiliza ninguna tecnología equivalente a una cookie (almacenamiento local del navegador utilizado con fines de seguimiento, identificador generado en el lado del cliente, huella digital del dispositivo o "fingerprinting").</p>
      <p>Ante la ausencia de toda cookie o rastreador, no se muestra ningún banner de consentimiento: carecería de objeto, ya que la normativa (artículo 22 LSSI-CE, RGPD) solo exige recabar el consentimiento cuando efectivamente se instala una cookie no estrictamente necesaria.</p>
    </section>

    <section class="direct" id="terceros">
      <h2>Servicios Y Scripts De Terceros</h2>
      <p>Ningún script ni servicio de terceros se carga en este sitio con fines de seguimiento o elaboración de perfiles. Concretamente, el sitio no integra ni Google Analytics, ni Matomo, ni ninguna otra herramienta de medición de audiencia; ni Google Fonts ni ninguna otra fuente tipográfica alojada de forma remota; ni script publicitario, ni píxel de conversión, ni red de retargeting; ni botón o widget de red social; ni vídeo ni mapa alojado por un servicio de terceros; ni herramienta de chat o soporte al cliente de terceros; ni servicio de inicio de sesión único ("iniciar sesión con" una cuenta de terceros).</p>
      <p>El único agente técnico tercero implicado en el funcionamiento del sitio es su proveedor de alojamiento, o2switch, descrito en nuestro <a href="/mentions-legales">aviso legal</a>, así como el servicio de mensajería utilizado para transmitir los mensajes del formulario de contacto.</p>
      <p>Esta lista refleja el estado del sitio en la fecha de actualización de esta política, indicada al final de la página. Cualquier evolución futura que añada un servicio de terceros sería objeto de una actualización de esta sección antes de su puesta en marcha.</p>
    </section>

    <section class="direct" id="destinatarios">
      <h2>Destinatarios De Los Datos</h2>
      <p>Los mensajes enviados a través del formulario de contacto son recibidos únicamente por el editor del sitio, BIGBANG MEDIA. Ningún dato se vende, alquila, cede o comunica a un tercero con fines comerciales, publicitarios o estadísticos.</p>
      <p>Los datos técnicos conservados por el proveedor de alojamiento solo son accesibles para el propio proveedor y, en su caso, para una autoridad judicial o administrativa legalmente habilitada para requerirlos.</p>
    </section>

    <section class="direct" id="transferencias">
      <h2>Transferencias Fuera De La UE</h2>
      <p>La totalidad de los tratamientos descritos en esta política tiene lugar en Francia. El sitio está alojado en Francia por o2switch, y ningún dato se transmite a un prestador situado fuera de la Unión Europea. Por tanto, no se produce ninguna transferencia de datos fuera de la Unión Europea en el marco del uso de este sitio.</p>
    </section>

    <section class="direct" id="seguridad">
      <h2>Seguridad De Los Datos</h2>
      <p>El sitio está diseñado según un principio de minimización desde el diseño: la base de datos de palabras se abre exclusivamente en modo lectura durante la ejecución, lo que impide técnicamente cualquier escritura accidental o malintencionada en dicha base desde la parte pública del sitio. El sitio tampoco conserva ninguna base de datos de usuarios o mensajes, lo que reduce en la misma medida la superficie expuesta en caso de incidente de seguridad.</p>
      <p>Las comunicaciones entre su navegador y el servidor están protegidas mediante el protocolo HTTPS. El proveedor de alojamiento o2switch aplica sus propias medidas de seguridad físicas y lógicas en su infraestructura, descritas en su sitio oficial.</p>
    </section>

    <section class="direct" id="derechos">
      <h2>Sus Derechos</h2>
      <p>De conformidad con el RGPD y la LOPDGDD, usted dispone de los siguientes derechos sobre sus datos personales.</p>
      <ul class="legal-list">
        <li>Derecho de acceso: obtener la confirmación de si se están tratando datos que le conciernen, y obtener una copia de los mismos.</li>
        <li>Derecho de rectificación: hacer corregir un dato inexacto o incompleto que le concierna.</li>
        <li>Derecho de supresión ("derecho al olvido"): solicitar la eliminación de sus datos, en los casos previstos por el RGPD.</li>
        <li>Derecho a la limitación del tratamiento: solicitar la suspensión temporal de un tratamiento, en determinados casos previstos por el RGPD.</li>
        <li>Derecho de oposición: oponerse a un tratamiento basado en el interés legítimo, por motivos relacionados con su situación particular.</li>
        <li>Derecho a la portabilidad: recibir los datos que nos haya facilitado en un formato estructurado y de uso común, cuando este derecho sea aplicable.</li>
        <li>Derecho a retirar su consentimiento en cualquier momento, cuando el tratamiento se base en dicho consentimiento, sin que ello afecte a la licitud del tratamiento efectuado antes de dicha retirada.</li>
      </ul>
      <p>Dado que el sitio no trata ningún dato personal identificable fuera del formulario de contacto que usted decide libremente rellenar, el ejercicio de estos derechos afecta en la práctica esencialmente a los mensajes que nos haya podido dirigir.</p>
    </section>

    <section class="direct" id="ejercicio">
      <h2>Cómo Ejercer Sus Derechos</h2>
      <p>Puede ejercer el conjunto de los derechos descritos anteriormente escribiéndonos a través de nuestro <a href="/contact">formulario de contacto</a>, precisando el objeto de su solicitud y el derecho que desea ejercer.</p>
      <p>Con el fin de proteger sus datos frente a una solicitud fraudulenta formulada en su nombre, podemos pedirle que confirme su identidad mediante la dirección de correo electrónico utilizada en un intercambio anterior, antes de dar curso a su solicitud.</p>
    </section>

    <section class="direct" id="autoridad-control">
      <h2>Reclamación Ante Una Autoridad De Control</h2>
      <p>BIGBANG MEDIA está establecida únicamente en Francia; la autoridad de control principal competente en el sentido del mecanismo de "ventanilla única" del RGPD (artículo 56) es, por tanto, la Commission Nationale de l'Informatique et des Libertés (CNIL) francesa. Si considera, tras habernos contactado, que sus derechos no han sido respetados, puede presentar una reclamación ante la CNIL.</p>
      <p>Sitio oficial de la CNIL: <a href="https://www.cnil.fr">cnil.fr</a>. Dirección postal: CNIL, 3 Place de Fontenoy, TSA 80715, 75334 Paris Cedex 07, Francia.</p>
      <p>Independientemente de ello, el artículo 77 del RGPD le reconoce el derecho a presentar también una reclamación ante la autoridad de control de su propio Estado de residencia. Para España, esta es la Agencia Española de Protección de Datos (AEPD), C/ Jorge Juan, 6, 28001 Madrid, <a href="https://www.aepd.es">aepd.es</a>.</p>
    </section>

    <section class="direct" id="menores">
      <h2>Datos De Menores</h2>
      <p>Este sitio es una herramienta de uso general que no se dirige específicamente a un público menor de edad y nunca solicita información relativa a la edad de sus personas visitantes. El formulario de contacto sigue siendo, no obstante, accesible a cualquier persona, incluidas las menores de edad, que desee escribirnos; en ese caso, se aplican los mismos principios de minimización de datos descritos en esta política.</p>
    </section>

    <section class="direct" id="modificaciones">
      <h2>Modificaciones De Esta Política</h2>
      <p>Esta política de privacidad puede actualizarse para reflejar una evolución del sitio, de sus funcionalidades, o de la normativa aplicable. La versión vigente es siempre la publicada en esta página.</p>
      <p>Última actualización: agosto de 2026.</p>
    </section>

    <section class="direct" id="glosario">
      <h2>Glosario</h2>
      <p>"Dato personal" designa cualquier información relativa a una persona física identificada o identificable, directa o indirectamente.</p>
      <p>"Tratamiento" designa cualquier operación relativa a datos personales, como su recopilación, conservación o supresión.</p>
      <p>"Responsable del tratamiento" designa a la persona u organismo que determina los fines y medios de un tratamiento de datos personales.</p>
      <p>"RGPD" designa el Reglamento General de Protección de Datos, reglamento europeo en vigor desde el 25 de mayo de 2018.</p>
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
