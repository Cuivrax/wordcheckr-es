<?php

declare(strict_types=1);

/**
 * Correctif de rollout (docs/DECISIONS.md ES-018, suivi 2026-08-30) : re-inclut les 220 pages
 * `word_list_combined` (famille "longueur+terminan-en") exclues par le lot initial
 * (scripts/seo-batches/combined-length-start-end-tier1-2026-08-30.php) pour deux raisons
 * aujourd'hui levees :
 *
 *   - 183 pages : <title> > 60 caracteres (ES-012/D-031, page a 1 seul resultat dont le mot
 *     prefixe le titre) -- corrige par app/View/word-list.php (commit cc7a5e6), qui omet
 *     desormais le suffixe de marque quand le titre depasse 60 caracteres. Les 183 candidats
 *     ont ete RE-DERIVES depuis storage/dictionary_es.sqlite (list_counts 'length_end'/'end',
 *     memes cles que scripts/build_explore_hub_counts.php) puis RE-VERIFIES en direct sur un
 *     vrai serveur PHP (curl-equivalent via file_get_contents, php -S) : tous a exactement
 *     60 caracteres ou moins apres correctif, aucun rejet.
 *   - 37 pages : risque de TTFB mesure par ES-018 (158-245 ms, mode BORNE, proche du plafond
 *     dur CLAUDE.md 250 ms). Decision explicite du proprietaire du produit (2026-08-30) :
 *     indexer malgre la marge reduite ("meme a 245ms on est bon, je veux des pages a
 *     indexer"). RE-MESURE en direct AVANT d'appliquer ce lot (pas une confiance aveugle dans
 *     la decision produit ni dans la mesure ES-018) : une premiere passe sans montee en
 *     charge affichait des pics jusqu'a 1654 ms (artefact de demarrage a froid du serveur php
 *     -S fraichement relance) ; une seconde passe avec un appel de rechauffement prealable et
 *     une mediane de 3 executions par page confirme min=98,5 ms max=207,1 ms median=176,9 ms
 *     p95=203,1 ms sur les 37 pages -- confortablement sous le plafond de 250 ms, coherent
 *     avec la mesure originale ES-018.
 *
 * Les 88 pages "doublon de contenu reel" et les 27 paires K/W (famille empiezan-por, jamais
 * concernees par ce correctif) restent noindex,follow -- non re-derivees ici, exclusion
 * toujours valide (RE-VERIFIE : les 88 candidats "doublon" retrouves par la meme requete
 * list_counts NE sont PAS dans ce lot, la logique de detection duplicate == end les a
 * correctement ecartes avant construction de ce fichier).
 *
 * Mesures completes (comptes, distribution timing, echantillon de titres) : voir le rapport
 * de cette tache dans la session principale, resume dans docs/DECISIONS.md ES-018 (note de
 * suivi 2026-08-30).
 *
 * Applique via :
 *     php scripts/apply_seo_batch.php scripts/seo-batches/combined-reinclusion-2026-08-30.php
 */
return
array (
  'batch_id' => 'combined-reinclusion-2026-08-30',
  'added_at' => '2026-08-30',
  'rows' => 
  array (
    0 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/an',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/an',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 8648,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 171.4 ms (ejecuciones: 154.5,171.4,172.7 ms), 8648 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    1 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 17174,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 203.1 ms (ejecuciones: 184.4,203.1,226.9 ms), 17174 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    2 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/at',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/at',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MAGNIFICAT - Palabras De 10 Letras Con Final En At</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    3 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/bs',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/bs',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>VIDEOCLUBS - Palabras De 10 Letras Con Final En Bs</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    4 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ck',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ck',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TETRABRICK - Palabras De 10 Letras Con Final En Ck</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    5 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/cu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/cu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SUCUSUMUCU - Palabras De 10 Letras Con Final En Cu</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    6 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/do',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/do',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 5193,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 103.1 ms (ejecuciones: 100.9,103.1,103.5 ms), 5193 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    7 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/em',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/em',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CABLEMODEM - Palabras De 10 Letras Con Final En Em</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    8 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/en',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/en',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 6544,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 133.1 ms (ejecuciones: 124.1,133.1,153.2 ms), 6544 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    9 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/es',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/es',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 9903,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 196.8 ms (ejecuciones: 172.8,196.8,201.4 ms), 9903 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    10 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/fi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/fi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CHANTAPUFI - Palabras De 10 Letras Con Final En Fi</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    11 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/fs',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/fs',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALMOTAZAFS - Palabras De 10 Letras Con Final En Fs</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    12 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ig',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ig',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PAGBALILIG - Palabras De 10 Letras Con Final En Ig</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    13 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ip',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ip',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MICROSTRIP - Palabras De 10 Letras Con Final En Ip</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    14 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 14803,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 188.7 ms (ejecuciones: 174.2,188.7,215.1 ms), 14803 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    15 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ji',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ji',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>KAPIJIBAJI - Palabras De 10 Letras Con Final En Ji</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    16 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ju',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ju',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MUSHIMANJU - Palabras De 10 Letras Con Final En Ju</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    17 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ke',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ke',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CHEESECAKE - Palabras De 10 Letras Con Final En Ke</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    18 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ki',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ki',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TEPPANYAKI - Palabras De 10 Letras Con Final En Ki</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    19 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ks',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ks',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MAGICLICKS - Palabras De 10 Letras Con Final En Ks</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    20 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ll',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ll',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PICKLEBALL - Palabras De 10 Letras Con Final En Ll</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    21 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/lt',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/lt',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>LEBENSWELT - Palabras De 10 Letras Con Final En Lt</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    22 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/mi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/mi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ENTREDORMI - Palabras De 10 Letras Con Final En Mi</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    23 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/nt',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/nt',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>VOLAVERUNT - Palabras De 10 Letras Con Final En Nt</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    24 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/oe',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/oe',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SUPERHEROE - Palabras De 10 Letras Con Final En Oe</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    25 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/oi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/oi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALIQUINDOI - Palabras De 10 Letras Con Final En Oi</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    26 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 18489,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 191.9 ms (ejecuciones: 187.5,191.9,206.6 ms), 18489 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    27 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/rd',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/rd',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PROTOBOARD - Palabras De 10 Letras Con Final En Rd</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    28 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ru',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ru',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ACHACHAIRU - Palabras De 10 Letras Con Final En Ru</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    29 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/se',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/se',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 5105,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 106.5 ms (ejecuciones: 101,106.5,141.5 ms), 5105 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    30 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/si',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/si',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>BANGLADESI - Palabras De 10 Letras Con Final En Si</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    31 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ul',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ul',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>VICECONSUL - Palabras De 10 Letras Con Final En Ul</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    32 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ur',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ur',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>IMPRIMATUR - Palabras De 10 Letras Con Final En Ur</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    33 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ut',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ut',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>HITBODEDUT - Palabras De 10 Letras Con Final En Ut</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    34 => 
    array (
      'route_path' => '/palabras/10-letras/terminan-en/ze',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/10-letras/terminan-en/ze',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ESTRECHEZE - Palabras De 10 Letras Con Final En Ze</title>, 50 caracteres. Enlace interno real desde /palabras/10-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    35 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/an',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/an',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 8476,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 161.7 ms (ejecuciones: 160.8,161.7,219.6 ms), 8476 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    36 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ao',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ao',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CUENTACACAO - Palabras De 11 Letras Con Final En Ao</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    37 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 15874,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 197.7 ms (ejecuciones: 181.1,197.7,236.3 ms), 15874 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    38 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ax',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ax',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CEFALOTORAX - Palabras De 11 Letras Con Final En Ax</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    39 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ay',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ay',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRAESTAY - Palabras De 11 Letras Con Final En Ay</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    40 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ch',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ch',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MICROSWITCH - Palabras De 11 Letras Con Final En Ch</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    41 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/cs',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/cs',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ATRAPACLICS - Palabras De 11 Letras Con Final En Cs</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    42 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/em',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/em',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MONOCEROTEM - Palabras De 11 Letras Con Final En Em</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    43 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/en',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/en',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 5860,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 125.7 ms (ejecuciones: 117.7,125.7,135.3 ms), 5860 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    44 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/es',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/es',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 8780,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 191.2 ms (ejecuciones: 173,191.2,193.3 ms), 8780 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    45 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/et',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/et',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SPLINTERNET - Palabras De 11 Letras Con Final En Et</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    46 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ey',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ey',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DETIENEBUEY - Palabras De 11 Letras Con Final En Ey</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    47 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/fe',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/fe',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>EPANASTROFE - Palabras De 11 Letras Con Final En Fe</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    48 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ik',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ik',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>REALPOLITIK - Palabras De 11 Letras Con Final En Ik</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    49 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 17047,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 180.7 ms (ejecuciones: 179.3,180.7,235.1 ms), 17047 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    50 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ka',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ka',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PERESTROIKA - Palabras De 11 Letras Con Final En Ka</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    51 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ki',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ki',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>OKONOMIYAKI - Palabras De 11 Letras Con Final En Ki</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    52 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/mi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/mi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESCOMPRIMI - Palabras De 11 Letras Con Final En Mi</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    53 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/nd',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/nd',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>UNDERGROUND - Palabras De 11 Letras Con Final En Nd</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    54 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/oj',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/oj',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>RADIORRELOJ - Palabras De 11 Letras Con Final En Oj</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    55 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 21093,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 189.7 ms (ejecuciones: 181,189.7,212.9 ms), 21093 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    56 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ot',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ot',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRAFAGOT - Palabras De 11 Letras Con Final En Ot</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    57 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/rd',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/rd',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MOTHERBOARD - Palabras De 11 Letras Con Final En Rd</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    58 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/si',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/si',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>METAMORFOSI - Palabras De 11 Letras Con Final En Si</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    59 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ss',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ss',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MASTERCLASS - Palabras De 11 Letras Con Final En Ss</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    60 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/st',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/st',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>POLTERGEIST - Palabras De 11 Letras Con Final En St</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    61 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/tl',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/tl',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>QUEXQUEMETL - Palabras De 11 Letras Con Final En Tl</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    62 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ts',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ts',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MAGNIFICATS - Palabras De 11 Letras Con Final En Ts</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    63 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/ug',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/ug',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ZWISCHENZUG - Palabras De 11 Letras Con Final En Ug</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    64 => 
    array (
      'route_path' => '/palabras/11-letras/terminan-en/un',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/11-letras/terminan-en/un',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MADREVILCUN - Palabras De 11 Letras Con Final En Un</title>, 51 caracteres. Enlace interno real desde /palabras/11-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    65 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/an',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/an',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 6238,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 115.9 ms (ejecuciones: 107.9,115.9,122.7 ms), 6238 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    66 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 11578,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 177.7 ms (ejecuciones: 174.8,177.7,188.4 ms), 11578 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    67 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/bi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/bi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CIRCUNSCRIBI - Palabras De 12 Letras Con Final En Bi</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    68 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/es',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/es',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 6412,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 118.6 ms (ejecuciones: 107.9,118.6,130.8 ms), 6412 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    69 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/hi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/hi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>GUACHIGUACHI - Palabras De 12 Letras Con Final En Hi</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    70 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 16753,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 199.4 ms (ejecuciones: 196.4,199.4,251.5 ms), 16753 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    71 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/iz',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/iz',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CHOCHAPERDIZ - Palabras De 12 Letras Con Final En Iz</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    72 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/ka',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/ka',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CAIPIRIVODKA - Palabras De 12 Letras Con Final En Ka</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    73 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/li',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/li',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>BRILLIBRILLI - Palabras De 12 Letras Con Final En Li</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    74 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/mi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/mi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SOBREIMPRIMI - Palabras De 12 Letras Con Final En Mi</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    75 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/oj',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/oj',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRARRELOJ - Palabras De 12 Letras Con Final En Oj</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    76 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/oo',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/oo',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ESPERMATOZOO - Palabras De 12 Letras Con Final En Oo</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    77 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 20512,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 179.7 ms (ejecuciones: 161.8,179.7,194.6 ms), 20512 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    78 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/ti',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/ti',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>INCONTINENTI - Palabras De 12 Letras Con Final En Ti</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    79 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/tl',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/tl',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>YOLLOXOCHITL - Palabras De 12 Letras Con Final En Tl</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    80 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/ts',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/ts',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRAFAGOTS - Palabras De 12 Letras Con Final En Ts</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    81 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/ud',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/ud',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CORDOBESITUD - Palabras De 12 Letras Con Final En Ud</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    82 => 
    array (
      'route_path' => '/palabras/12-letras/terminan-en/uo',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/12-letras/terminan-en/uo',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESCONCEPTUO - Palabras De 12 Letras Con Final En Uo</title>, 52 caracteres. Enlace interno real desde /palabras/12-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    83 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/am',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/am',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>FLUNITRAZEPAM - Palabras De 13 Letras Con Final En Am</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    84 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 7148,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 151.5 ms (ejecuciones: 114.7,151.5,176.9 ms), 7148 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    85 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/bo',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/bo',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>GUARDAMANCEBO - Palabras De 13 Letras Con Final En Bo</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    86 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/ci',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/ci',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>AUTOCOMPADECI - Palabras De 13 Letras Con Final En Ci</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    87 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/in',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/in',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRAESCOTIN - Palabras De 13 Letras Con Final En In</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    88 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 12380,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 193.7 ms (ejecuciones: 182.9,193.7,247.4 ms), 12380 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    89 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/ja',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/ja',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESCALANDRAJA - Palabras De 13 Letras Con Final En Ja</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    90 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/li',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/li',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CACAHUACENTLI - Palabras De 13 Letras Con Final En Li</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    91 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/ng',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/ng',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>WIKIMARKETING - Palabras De 13 Letras Con Final En Ng</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    92 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/nt',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/nt',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ESTABLISHMENT - Palabras De 13 Letras Con Final En Nt</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    93 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 17870,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 176.9 ms (ejecuciones: 175.2,176.9,209 ms), 17870 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    94 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/pa',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/pa',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRAESCARPA - Palabras De 13 Letras Con Final En Pa</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    95 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/po',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/po',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DAGUERREOTIPO - Palabras De 13 Letras Con Final En Po</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    96 => 
    array (
      'route_path' => '/palabras/13-letras/terminan-en/ts',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/13-letras/terminan-en/ts',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALMICANTARATS - Palabras De 13 Letras Con Final En Ts</title>, 53 caracteres. Enlace interno real desde /palabras/13-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    97 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/ae',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/ae',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PAPILIONOIDEAE - Palabras De 14 Letras Con Final En Ae</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    98 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/ci',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/ci',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESENSOBERBECI - Palabras De 14 Letras Con Final En Ci</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    99 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/ee',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/ee',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRABALANCEE - Palabras De 14 Letras Con Final En Ee</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    100 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/fa',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/fa',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CROMOLITOGRAFA - Palabras De 14 Letras Con Final En Fa</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    101 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/ge',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/ge',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>LARINGOFARINGE - Palabras De 14 Letras Con Final En Ge</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    102 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/ha',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/ha',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TRASQUILIMOCHA - Palabras De 14 Letras Con Final En Ha</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    103 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/il',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/il',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>INFANTOJUVENIL - Palabras De 14 Letras Con Final En Il</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    104 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/in',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/in',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MAESTREPASQUIN - Palabras De 14 Letras Con Final En In</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    105 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 7903,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 152.5 ms (ejecuciones: 140.5,152.5,159.3 ms), 7903 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    106 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/iz',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/iz',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>FOTOCONDUCTRIZ - Palabras De 14 Letras Con Final En Iz</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    107 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/je',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/je',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>FOTORREPORTAJE - Palabras De 14 Letras Con Final En Je</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    108 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/me',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/me',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ESFENISCIFORME - Palabras De 14 Letras Con Final En Me</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    109 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 12127,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 200.4 ms (ejecuciones: 176.7,200.4,235 ms), 12127 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    110 => 
    array (
      'route_path' => '/palabras/14-letras/terminan-en/um',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/14-letras/terminan-en/um',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ANTICURRICULUM - Palabras De 14 Letras Con Final En Um</title>, 54 caracteres. Enlace interno real desde /palabras/14-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    111 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/ed',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/ed',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESENSOBERBECED - Palabras De 15 Letras Con Final En Ed</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    112 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/er',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/er',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESENSOBERBECER - Palabras De 15 Letras Con Final En Er</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    113 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/id',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/id',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CAPITIDISMINUID - Palabras De 15 Letras Con Final En Id</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    114 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/il',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/il',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TERMORRETRACTIL - Palabras De 15 Letras Con Final En Il</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    115 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/in',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/in',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CONTRAPALANQUIN - Palabras De 15 Letras Con Final En In</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    116 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/ir',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/ir',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CAPITIDISMINUIR - Palabras De 15 Letras Con Final En Ir</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    117 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/me',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/me',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ESTRUCIONIFORME - Palabras De 15 Letras Con Final En Me</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    118 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/ne',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/ne',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SOBREDIMENSIONE - Palabras De 15 Letras Con Final En Ne</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    119 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 7383,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 128.4 ms (ejecuciones: 122.5,128.4,188.6 ms), 7383 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    120 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/po',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/po',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>FANTASMATOSCOPO - Palabras De 15 Letras Con Final En Po</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    121 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/ue',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/ue',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESPORRONDINGUE - Palabras De 15 Letras Con Final En Ue</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    122 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/um',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/um',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SANCTASANCTORUM - Palabras De 15 Letras Con Final En Um</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    123 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/us',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/us',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ARGENTINOSAURUS - Palabras De 15 Letras Con Final En Us</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    124 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/ya',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/ya',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CAPITIDISMINUYA - Palabras De 15 Letras Con Final En Ya</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    125 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/ye',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/ye',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CAPITIDISMINUYE - Palabras De 15 Letras Con Final En Ye</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    126 => 
    array (
      'route_path' => '/palabras/15-letras/terminan-en/yo',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/15-letras/terminan-en/yo',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CAPITIDISMINUYO - Palabras De 15 Letras Con Final En Yo</title>, 55 caracteres. Enlace interno real desde /palabras/15-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    127 => 
    array (
      'route_path' => '/palabras/7-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/7-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 6103,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 137.6 ms (ejecuciones: 122.3,137.6,155.6 ms), 6103 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/7-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    128 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ag',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ag',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MICROBAG - Palabras De 8 Letras Con Final En Ag</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    129 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ah',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ah',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CHUTZPAH - Palabras De 8 Letras Con Final En Ah</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    130 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/aj',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/aj',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MANIBLAJ - Palabras De 8 Letras Con Final En Aj</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    131 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ak',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ak',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>KAARSAAK - Palabras De 8 Letras Con Final En Ak</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    132 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/an',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/an',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 5302,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 98.5 ms (ejecuciones: 94.8,98.5,111.7 ms), 5302 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    133 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 10905,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 197.8 ms (ejecuciones: 187.9,197.8,223.1 ms), 10905 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    134 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/aw',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/aw',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>COLESLAW - Palabras De 8 Letras Con Final En Aw</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    135 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/bu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/bu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALMORABU - Palabras De 8 Letras Con Final En Bu</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    136 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/cu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/cu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TACUTACU - Palabras De 8 Letras Con Final En Cu</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    137 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/du',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/du',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DIYERIDU - Palabras De 8 Letras Con Final En Du</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    138 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ej',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ej',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALMOFREJ - Palabras De 8 Letras Con Final En Ej</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    139 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/em',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/em',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>HUILQUEM - Palabras De 8 Letras Con Final En Em</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    140 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/es',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/es',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 7160,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 141.8 ms (ejecuciones: 132.4,141.8,143.3 ms), 7160 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    141 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ex',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ex',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ROTAFLEX - Palabras De 8 Letras Con Final En Ex</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    142 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/fs',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/fs',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>GROMBIFS - Palabras De 8 Letras Con Final En Fs</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    143 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/gs',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/gs',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ICEBERGS - Palabras De 8 Letras Con Final En Gs</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    144 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/hu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/hu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>FUMANCHU - Palabras De 8 Letras Con Final En Hu</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    145 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ic',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ic',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>WEBCOMIC - Palabras De 8 Letras Con Final En Ic</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    146 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ig',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ig',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>THERBLIG - Palabras De 8 Letras Con Final En Ig</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    147 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/im',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/im',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>AJARONIM - Palabras De 8 Letras Con Final En Im</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    148 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 5706,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 100.2 ms (ejecuciones: 100.2,100.2,120.3 ms), 5706 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    149 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/iu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/iu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>INTERVIU - Palabras De 8 Letras Con Final En Iu</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    150 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ix',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ix',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MIREPOIX - Palabras De 8 Letras Con Final En Ix</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    151 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ko',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ko',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PACHINKO - Palabras De 8 Letras Con Final En Ko</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    152 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/lf',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/lf',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MINIGOLF - Palabras De 8 Letras Con Final En Lf</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    153 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/lm',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/lm',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TELEFILM - Palabras De 8 Letras Con Final En Lm</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    154 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ls',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ls',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>NAHUATLS - Palabras De 8 Letras Con Final En Ls</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    155 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/lt',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/lt',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CINSAULT - Palabras De 8 Letras Con Final En Lt</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    156 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/nk',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/nk',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>AONIKENK - Palabras De 8 Letras Con Final En Nk</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    157 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ns',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ns',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>OURAGANS - Palabras De 8 Letras Con Final En Ns</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    158 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/oc',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/oc',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MONOBLOC - Palabras De 8 Letras Con Final En Oc</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    159 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/oe',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/oe',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>LIGNALOE - Palabras De 8 Letras Con Final En Oe</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    160 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/om',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/om',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ANGSTROM - Palabras De 8 Letras Con Final En Om</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    161 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/op',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/op',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>AUTOSTOP - Palabras De 8 Letras Con Final En Op</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    162 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 8439,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 202.9 ms (ejecuciones: 163.3,202.9,213.1 ms), 8439 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    163 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ow',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ow',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>BUNGALOW - Palabras De 8 Letras Con Final En Ow</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    164 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/rc',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/rc',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ANTICIRC - Palabras De 8 Letras Con Final En Rc</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    165 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/rf',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/rf',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>WINDSURF - Palabras De 8 Letras Con Final En Rf</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    166 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/rl',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/rl',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SKINGIRL - Palabras De 8 Letras Con Final En Rl</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    167 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/rm',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/rm',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>INCOTERM - Palabras De 8 Letras Con Final En Rm</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    168 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ry',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ry',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DELIVERY - Palabras De 8 Letras Con Final En Ry</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    169 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/sh',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/sh',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>COCOWASH - Palabras De 8 Letras Con Final En Sh</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    170 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/uf',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/uf',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALACALUF - Palabras De 8 Letras Con Final En Uf</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    171 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ut',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ut',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>UPPERCUT - Palabras De 8 Letras Con Final En Ut</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    172 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/wa',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/wa',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CHICHEWA - Palabras De 8 Letras Con Final En Wa</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    173 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/wk',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/wk',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TOMAHAWK - Palabras De 8 Letras Con Final En Wk</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    174 => 
    array (
      'route_path' => '/palabras/8-letras/terminan-en/ze',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/8-letras/terminan-en/ze',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>KAMIKAZE - Palabras De 8 Letras Con Final En Ze</title>, 47 caracteres. Enlace interno real desde /palabras/8-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    175 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ae',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ae',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>RETROTRAE - Palabras De 9 Letras Con Final En Ae</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    176 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/an',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/an',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 7607,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 151.3 ms (ejecuciones: 141.4,151.3,160.2 ms), 7607 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    177 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/as',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/as',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 15134,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 184.1 ms (ejecuciones: 166.4,184.1,204.8 ms), 15134 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    178 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/at',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/at',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>VIDEOCHAT - Palabras De 9 Letras Con Final En At</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    179 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/cu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/cu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SIRVINACU - Palabras De 9 Letras Con Final En Cu</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    180 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/du',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/du',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DIDYERIDU - Palabras De 9 Letras Con Final En Du</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    181 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ei',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ei',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>DESPROVEI - Palabras De 9 Letras Con Final En Ei</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    182 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ej',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ej',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PLETZALEJ - Palabras De 9 Letras Con Final En Ej</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    183 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/en',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/en',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 6155,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 118 ms (ejecuciones: 115.8,118,158.2 ms), 6155 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    184 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/es',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/es',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 9331,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 207.1 ms (ejecuciones: 174.3,207.1,257.6 ms), 9331 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    185 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/et',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/et',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CABRIOLET - Palabras De 9 Letras Con Final En Et</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    186 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ex',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ex',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>NEOCORTEX - Palabras De 9 Letras Con Final En Ex</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    187 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/hl',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/hl',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>PORTAKOHL - Palabras De 9 Letras Con Final En Hl</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    188 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ht',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ht',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>COPYRIGHT - Palabras De 9 Letras Con Final En Ht</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    189 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ib',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ib',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CELECOXIB - Palabras De 9 Letras Con Final En Ib</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    190 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/if',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/if',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>LEITMOTIF - Palabras De 9 Letras Con Final En If</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    191 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/is',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/is',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 10346,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 174.7 ms (ejecuciones: 169.1,174.7,178.8 ms), 10346 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    192 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/iv',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/iv',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>LEITMOTIV - Palabras De 9 Letras Con Final En Iv</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    193 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ix',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ix',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ANTIHELIX - Palabras De 9 Letras Con Final En Ix</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    194 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ji',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ji',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ENTRETEJI - Palabras De 9 Letras Con Final En Ji</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    195 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ke',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ke',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MILKSHAKE - Palabras De 9 Letras Con Final En Ke</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    196 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ko',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ko',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MARMITAKO - Palabras De 9 Letras Con Final En Ko</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    197 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/lm',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/lm',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MICROFILM - Palabras De 9 Letras Con Final En Lm</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    198 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/nn',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/nn',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>KARKADANN - Palabras De 9 Letras Con Final En Nn</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    199 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ns',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ns',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>AFRIKAANS - Palabras De 9 Letras Con Final En Ns</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    200 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/nt',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/nt',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CROISSANT - Palabras De 9 Letras Con Final En Nt</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    201 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/oc',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/oc',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>LENGUADOC - Palabras De 9 Letras Con Final En Oc</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    202 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/oe',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/oe',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ANTIHEROE - Palabras De 9 Letras Con Final En Oe</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    203 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/op',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/op',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>AUTOESTOP - Palabras De 9 Letras Con Final En Op</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    204 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/os',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/os',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 13310,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras nueva medicion en directo con metodologia corregida (calentamiento previo + mediana de 3 ejecuciones en un servidor PHP recien reiniciado -- una primera medicion sin calentamiento mostraba picos de arranque en frio no representativos). Decision explicita del propietario del producto: indexar con margen ajustado al presupuesto duro ("incluso a 245 ms estamos bien, quiero paginas para indexar"). Medicion real: mediana 178.1 ms (ejecuciones: 173.3,178.1,179.5 ms), 13310 resultado(s), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed) -- por debajo del presupuesto duro CLAUDE.md (TTFB p95 < 250 ms) con margen real, no truncada (< ROW_EXAMINATION_CEILING=10000). Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Antigua razon de exclusion (riesgo de TTFB, ES-018) reevaluada y descartada por medicion real.',
    ),
    205 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/oy',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/oy',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MORRONGOY - Palabras De 9 Letras Con Final En Oy</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    206 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/pf',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/pf',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TOTENKOPF - Palabras De 9 Letras Con Final En Pf</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    207 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/rs',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/rs',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>YOUTUBERS - Palabras De 9 Letras Con Final En Rs</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    208 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ru',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ru',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ISHKUPURU - Palabras De 9 Letras Con Final En Ru</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    209 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/sh',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/sh',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>SPANGLISH - Palabras De 9 Letras Con Final En Sh</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    210 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/tu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/tu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>IMPROMPTU - Palabras De 9 Letras Con Final En Tu</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    211 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/tz',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/tz',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MEGAHERTZ - Palabras De 9 Letras Con Final En Tz</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    212 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ub',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ub',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>VIDEOCLUB - Palabras De 9 Letras Con Final En Ub</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    213 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/uj',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/uj',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALMORADUJ - Palabras De 9 Letras Con Final En Uj</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    214 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/up',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/up',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>CHUPACHUP - Palabras De 9 Letras Con Final En Up</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    215 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/ux',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/ux',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALMORADUX - Palabras De 9 Letras Con Final En Ux</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    216 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/wn',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/wn',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>TOUCHDOWN - Palabras De 9 Letras Con Final En Wn</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    217 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/xi',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/xi',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>RADIOTAXI - Palabras De 9 Letras Con Final En Xi</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    218 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/yu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/yu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>MANGURUYU - Palabras De 9 Letras Con Final En Yu</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
    219 => 
    array (
      'route_path' => '/palabras/9-letras/terminan-en/zu',
      'family' => 'word_list_combined',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/9-letras/terminan-en/zu',
      'sitemap_fragment' => 'combined-0001',
      'result_count' => 1,
      'notes' => 'CORRECCION 2026-08-30: reincluida tras corregir la plantilla de <title> (app/View/word-list.php, commit cc7a5e6) que ahora omite el sufijo de marca cuando el titulo supera 60 caracteres. Verificado EN DIRECTO en un servidor PHP real: <title>ALCUZCUZU - Palabras De 9 Letras Con Final En Zu</title>, 48 caracteres. Enlace interno real desde /palabras/9-letras (ya indexada, word_list_length) via App\\Search\\LengthLinksBuilder::byEnd (list_counts \'length_end\', ES-017/ES-018). Granularidad: 2 caracteres (coherente con word_list_terminant sin longitud, ES-016 -- Normalizer::MIN_LENGTH=2). 1 resultado (todos los estatus), modo BORNE (App\\Search\\WordListSolver::solveBounded(), ancla \'reversed\', idx_terms_length_reversed). Antigua razon de exclusion (titulo demasiado largo, ES-018) ya no aplica.',
    ),
  ),
);
