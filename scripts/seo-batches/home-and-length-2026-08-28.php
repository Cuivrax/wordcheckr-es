<?php

declare(strict_types=1);

/**
 * Premier palier de rollout SEO du site espagnol (docs/DECISIONS.md ES-009) : pages de
 * structure (home + hub de navigation /palabras) et listes par longueur
 * (/palabras/{N}-letras). Appliqué via :
 *
 *     php scripts/apply_seo_batch.php scripts/seo-batches/home-and-length-2026-08-28.php --force --prune
 *
 * La famille word_admitted (des dizaines de milliers de lignes par vague, bien trop volumineuse
 * pour un tableau PHP littéral comme celui-ci) est appliquée séparément, PAR VAGUE EXPLICITE,
 * par scripts/apply_word_admitted_rollout.php (voir docs/DECISIONS.md ES-011).
 *
 * CORRECTIF 1 (même journée que la création de ce lot, avant tout audit externe) : la version
 * initiale de ce lot contenait les 14 pages /palabras/{N}-letras (2 à 15 lettres). Vérification
 * en direct sur un vrai serveur PHP (php -S) après application : le hub /palabras (App\Search\
 * ExploreHubBuilder) lit la table `list_counts`, VIDE sur ce dépôt (ES-001, décision produit
 * explicite -- hors périmètre de l'agent seo-registry, réservée à l'agent data-engine) -- les 3
 * sections "Por Longitud"/"Empiezan Por"/"Terminan En" du hub rendent donc 0 lien actuellement,
 * quel que soit le contenu du registre SEO. Réduit à ce moment-là à 2 des 14 longueurs
 * ('/palabras/7-letras', '/palabras/9-letras') -- PRÉMISSE ERRONÉE, voir CORRECTIF 2.
 *
 * CORRECTIF 2 (2026-08-29, audit seo-technical-auditor NO GO consolidé, constat I-1, "le plus
 * important à vérifier en premier") : la prémisse du CORRECTIF 1 ("seules 2 longueurs ont un
 * lien entrant") était FAUSSE. App\Search\RelationsFinder::relatedSearches() (ligne ~779) émet
 * INCONDITIONNELLEMENT un lien vers /palabras/{N}-letras depuis CHAQUE fiche /palabra/{mot}
 * RENDUE -- et public/index.php rend (HTTP 200) toute fiche mot trouvée par
 * App\Search\TermLookup::find() INDÉPENDAMMENT du registre SEO (le registre ne pilote que la
 * balise robots/canonical via $render($view, $data, $status, $canonicalPath), jamais le rendu
 * lui-même -- vérifié dans le code de public/index.php, pas supposé). Autrement dit : les 14
 * longueurs reçoivent TOUTES un lien interne réel et suivable depuis chaque mot de cette
 * longueur présent dans storage/dictionary_es.sqlite, que ce mot soit ou non actuellement
 * 'index,follow' dans le registre. Comptes réels vérifiés par requête directe
 * (storage/dictionary_es.sqlite, 2026-08-29, TOUS statuts confondus) : 2→149, 3→822, 4→3627,
 * 5→12470, 6→29210, 7→56565, 8→87622, 9→112998, 10→123379, 11→113734, 12→89320, 13→62161,
 * 14→36786, 15→19322 -- aucune longueur à 0 lien entrant, aucune orpheline. Les 12 longueurs
 * retirées par erreur au CORRECTIF 1 sont donc réintégrées ici (14 lignes word_list_length au
 * total, fragment letters-0001, 40 000 URL max par fragment très loin d'être atteint).
 *
 * CORRECTIF 3 (2026-08-29, même audit, blocage C-1) : '/palabras' elle-même repasse en
 * noindex,follow. App\View\explore-hub.php n'a AUCUN garde d'état vide (contrairement à
 * app/View/word-list.php, qui garde chaque bloc derrière `if ($refine['byLength'] !== [])`) --
 * les 3 sections de grille rendent un <h2> suivi d'un <div class="related-links"> totalement
 * vide tant que list_counts reste vide (ES-001). Corriger PROPREMENT (garde d'état vide + repli
 * de contenu réel) touche app/View/ (frontend) et potentiellement app/Search/ (data-engine) --
 * hors périmètre de l'agent seo-registry (app/Seo/, scripts/build_sitemaps*, tests/Seo/,
 * public/robots.txt uniquement) : signalé pour routage vers ces agents, pas corrigé ici. En
 * attendant, la page reste RENDUE (2 formulaires fonctionnels réels : recherche "contenant",
 * vérification de mot) mais NE DOIT PAS être indexée tant que son contenu de liste reste vide --
 * retirée du sitemap (sitemap_fragment => null) et repassée en noindex,follow. '/' (home) N'EST
 * PAS concernée : son contenu n'a jamais dépendu de list_counts, reste index,follow.
 *
 * result_count pour word_list_length = nombre RÉEL de lignes de storage/dictionary_es.sqlite
 * pour cette longueur, TOUTES statuts confondus (App\Search\WordListSolver n'applique le filtre
 * is_admitted que si l'URL porte un segment /statut/..., absent ici) -- vérifié par requête
 * directe le 2026-08-29 (comptes cités au CORRECTIF 2 ci-dessus).
 */
return [
    'batch_id' => 'home-and-length-tier1-2026-08-28',
    'added_at' => '2026-08-29',
    'rows' => [
        [
            'route_path' => '/',
            'family' => 'home',
            'robots' => 'index,follow',
            'canonical_path' => '/',
            'sitemap_fragment' => 'core-0001',
            'result_count' => null,
            'notes' => 'Pagina de inicio, destino de todo enlace "WORD CHECKR" del encabezado y de las migas de pan en cada ficha de palabra y cada lista (maillage total = 100% de las paginas del sitio). Enlaza al hub /palabras ("Explorar Todas Las Palabras") y a los formularios de verificacion/buscador.',
        ],
        [
            'route_path' => '/palabras',
            'family' => 'home',
            'robots' => 'noindex,follow',
            'canonical_path' => '/palabras',
            'sitemap_fragment' => null,
            'result_count' => null,
            'notes' => 'CORRECTIF 3 (2026-08-29, audit NO GO, C-1) : repassee en noindex,follow -- les 3 secciones de rejilla (Por Longitud/Empiezan Por/Terminan En) dependen de list_counts, VACIA en este deposito (ES-001), por lo que rinden un <h2> seguido de un <div class="related-links"> totalmente vacio, sin ningun guardado de estado vacio en app/View/explore-hub.php (a diferencia de app/View/word-list.php). Pagina SIGUE RENDIDA (2 formularios funcionales reales : buscar por letras contenidas, verificar una palabra), pero no debe indexarse mientras su contenido de listas siga vacio. Correccion propia (guardado de estado vacio + contenido de repliegue real, o poblado de list_counts) fuera del alcance del agente seo-registry (app/View/, app/Search/) -- senalado para su enrutamiento a los agentes frontend/data-engine.',
        ],
        [
            'route_path' => '/palabras/2-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/2-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 149,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 2 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/3-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/3-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 822,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 3 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/4-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/4-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 3627,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 4 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/5-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/5-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 12470,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 5 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/6-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/6-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 29210,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 6 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/7-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/7-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 56565,
            'notes' => 'Enlazada desde la home (app/View/home.php, $contextLinkSpecs, enlace estatico "Palabras De 7 Letras") -- verificado en vivo. Enlace interno adicional (I-1) desde CADA ficha /palabra/{palabra} de 7 letras (App\\Search\\RelationsFinder::relatedSearches()). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/8-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/8-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 87622,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 8 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/9-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/9-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 112998,
            'notes' => 'Enlazada desde la home (app/View/home.php, frase descriptiva "$phraseLink(\'9-letras\', ...)") -- verificado en vivo. Enlace interno adicional (I-1) desde CADA ficha /palabra/{palabra} de 9 letras (App\\Search\\RelationsFinder::relatedSearches()). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/10-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/10-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 123379,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 10 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/11-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/11-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 113734,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 11 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/12-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/12-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 89320,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 12 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/13-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/13-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 62161,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 13 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/14-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/14-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 36786,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 14 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/15-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/15-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 19322,
            'notes' => 'CORRECTIF 2 (2026-08-29, audit NO GO, I-1) : reabierta. Enlace interno real desde CADA ficha /palabra/{palabra} de 15 letras (App\\Search\\RelationsFinder::relatedSearches(), enlace "length" incondicional) -- la ficha se renderiza (HTTP 200) independientemente de su estado en el registro SEO (verificado en public/index.php). Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
    ],
];
