<?php

declare(strict_types=1);

/**
 * Premier palier de rollout SEO du site espagnol (docs/DECISIONS.md ES-009) : pages de
 * structure (home + hub de navigation /palabras) et listes par longueur
 * (/palabras/{N}-letras). Appliqué via :
 *
 *     php scripts/apply_seo_batch.php scripts/seo-batches/home-and-length-2026-08-28.php --force --prune
 *
 * La famille word_admitted (661 221 lignes, bien trop volumineuse pour un tableau PHP
 * littéral comme celui-ci) est appliquée séparément par
 * scripts/apply_word_admitted_rollout.php, en insertion directe en flux.
 *
 * CORRECTIF (même journée, avant tout audit externe) : la version initiale de ce lot
 * contenait les 14 pages /palabras/{N}-letras (2 à 15 lettres). Vérification en direct sur un
 * vrai serveur PHP (php -S) après application : le hub /palabras (App\Search\
 * ExploreHubBuilder) lit la table `list_counts`, VIDE sur ce dépôt (ES-001, décision produit
 * explicite -- hors périmètre de l'agent seo-registry, réservée à l'agent data-engine) --
 * les 3 sections "Por Longitud"/"Empiezan Por"/"Terminan En" du hub rendent donc 0 lien
 * actuellement, quel que soit le contenu du registre SEO. Seules DEUX longueurs ont un lien
 * interne réel et vérifié à ce jour : `/palabras/7-letras` (app/View/home.php,
 * $contextLinkSpecs, lien statique "Palabras De 7 Letras") et `/palabras/9-letras`
 * (app/View/home.php, phrase descriptive "$phraseLink('9-letras', ...)"). Les 12 autres
 * longueurs (2,3,4,5,6,8,10,11,12,13,14,15) n'ont AUCUN lien interne entrant à ce jour --
 * contrainte dure du rôle seo-registry ("Refuse orphan pages marked index — if nothing links
 * to it internally, it should not be indexable, full stop"), retirées de ce lot. Réduit de 16
 * à 4 lignes ('/', '/palabras', '/palabras/7-letras', '/palabras/9-letras'). Les 12 autres
 * longueurs restent noindex,follow par défaut (absentes du registre) jusqu'à ce que
 * list_counts soit peuplée (restaure le hub) ou qu'un autre maillage réel soit construit --
 * signalé à l'agent data-engine, voir le rapport de session de l'agent seo-registry et
 * docs/DECISIONS.md ES-009/ES-010.
 *
 * result_count pour word_list_length = nombre RÉEL de lignes de storage/dictionary_es.sqlite
 * pour cette longueur, TOUTES statuts confondus (App\Search\WordListSolver n'applique le
 * filtre is_admitted que si l'URL porte un segment /statut/..., absent ici) -- vérifié par
 * requête directe le 2026-08-28.
 */
return [
    'batch_id' => 'home-and-length-tier1-2026-08-28',
    'added_at' => '2026-08-28',
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
            'robots' => 'index,follow',
            'canonical_path' => '/palabras',
            'sitemap_fragment' => 'core-0001',
            'result_count' => null,
            'notes' => 'Hub de navegacion, enlazado desde la home ("Explorar Todas Las Palabras") y desde el encabezado de cada ficha de palabra y cada lista. Contiene 2 formularios funcionales reales (buscar por letras contenidas, verificar una palabra) mas texto explicativo -- las 3 secciones de rejilla (Por Longitud/Empiezan Por/Terminan En) dependen de list_counts, vacia en este deposito (ES-001), por lo que no enlazan actualmente a ninguna pagina (0 filas). No es una pagina de resultado vacio (herramientas funcionales reales), pero su contenido de enlaces es hoy mas delgado de lo previsto -- senalado a data-engine.',
        ],
        [
            'route_path' => '/palabras/7-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/7-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 56565,
            'notes' => 'Enlazada desde la home (app/View/home.php, $contextLinkSpecs, enlace estatico "Palabras De 7 Letras") -- verificado en vivo. Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
        [
            'route_path' => '/palabras/9-letras',
            'family' => 'word_list_length',
            'robots' => 'index,follow',
            'canonical_path' => '/palabras/9-letras',
            'sitemap_fragment' => 'letters-0001',
            'result_count' => 112998,
            'notes' => 'Enlazada desde la home (app/View/home.php, frase descriptiva "$phraseLink(\'9-letras\', ...)") -- verificado en vivo. Enlaza a cada palabra individual (admitida o no) via App\\Search\\WordListSolver/word-list.php, hasta 3 paginas de paginacion seguidas (rel=next/prev, PAGE_SIZE=50).',
        ],
    ],
];
