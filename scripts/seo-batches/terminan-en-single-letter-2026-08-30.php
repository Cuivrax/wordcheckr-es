<?php

declare(strict_types=1);

/**
 * Palier 1-letra de word_list_terminant (ES-022, docs/DECISIONS.md), simetrico a empiezan-por
 * 1 letra (ya indexado, ES-016). Enlace interno real desde el hub /palabras
 * (App\Search\ExploreHubBuilder, seccion "Terminan En") -- esta fuente de enlace NO EXISTIA
 * cuando ES-016 cerro esta familia a 1 letra (list_counts estaba vacio en ese momento,
 * cronologia confirmada). Decision de producto explicita de reabrir (2026-08-30, en directo en
 * la conversacion).
 *
 * 23 de las 27 letras posibles (A-Z + Ñ) -- 4 EXCLUIDAS por 0 palabra ADMITIDA (K, Q, W, Ñ),
 * misma disciplina que empiezan-por K/W (ES-016) : la pagina en si funcionaria (todos los
 * estatus se muestran), pero no se abre a indexacion sin al menos 1 resultado admitido.
 * result_count usa TODOS los estatus (verificado en directo que la pagina real muestra el
 * conteo total, no solo los admitidos -- ES-016 filtraba solo la DECISION de apertura por
 * letra, no el contenido mostrado).
 *
 * 0 doblez de contenido encontrado (comparacion programada contra los 2-letra ya indexados,
 * list_counts 'end' vs 'suffix2', ambos en todos los estatus).
 *
 * TTFB verificado en directo (calentamiento + mediana de 3 ejecuciones, metodologia ES-018)
 * en los 6 buckets mas pesados : 93-106 ms, muy por debajo del presupuesto 250 ms pese al
 * truncamiento ROW_EXAMINATION_CEILING=10000 en los mas grandes.
 *
 * Aplicado via :
 *     php scripts/apply_seo_batch.php scripts/seo-batches/terminan-en-single-letter-2026-08-30.php
 */
return
array (
  'batch_id' => 'terminan-en-single-letter-2026-08-30',
  'added_at' => '2026-08-30',
  'rows' => 
  array (
    0 => 
    array (
      'route_path' => '/palabras/terminan-en/a',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/a',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 96665,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 96665 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 86267 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    1 => 
    array (
      'route_path' => '/palabras/terminan-en/b',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/b',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 45,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 45 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 16 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    2 => 
    array (
      'route_path' => '/palabras/terminan-en/c',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/c',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 92,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 92 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 36 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    3 => 
    array (
      'route_path' => '/palabras/terminan-en/d',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/d',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 12584,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 12584 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 11561 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    4 => 
    array (
      'route_path' => '/palabras/terminan-en/e',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/e',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 77577,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 77577 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 53994 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    5 => 
    array (
      'route_path' => '/palabras/terminan-en/f',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/f',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 52,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 52 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 22 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    6 => 
    array (
      'route_path' => '/palabras/terminan-en/g',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/g',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 125,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 125 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 34 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    7 => 
    array (
      'route_path' => '/palabras/terminan-en/h',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/h',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 88,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 88 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 27 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    8 => 
    array (
      'route_path' => '/palabras/terminan-en/i',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/i',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 2302,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 2302 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 1739 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    9 => 
    array (
      'route_path' => '/palabras/terminan-en/j',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/j',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 31,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 31 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 23 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    10 => 
    array (
      'route_path' => '/palabras/terminan-en/l',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/l',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 3277,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 3277 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 2878 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    11 => 
    array (
      'route_path' => '/palabras/terminan-en/m',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/m',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 176,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 176 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 88 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    12 => 
    array (
      'route_path' => '/palabras/terminan-en/n',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/n',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 100460,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 100460 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 93335 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    13 => 
    array (
      'route_path' => '/palabras/terminan-en/o',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/o',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 66413,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 66413 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 59031 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    14 => 
    array (
      'route_path' => '/palabras/terminan-en/p',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/p',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 88,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 88 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 29 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    15 => 
    array (
      'route_path' => '/palabras/terminan-en/r',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/r',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 17410,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 17410 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 15708 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    16 => 
    array (
      'route_path' => '/palabras/terminan-en/s',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/s',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 369168,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 369168 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- TRUNCADO EN PANTALLA a 10000 filas (ROW_EXAMINATION_CEILING), mismo precedente aceptado ES-016/ES-018, mensaje honesto "al menos 10000". TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 335382 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    17 => 
    array (
      'route_path' => '/palabras/terminan-en/t',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/t',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 305,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 305 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 128 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    18 => 
    array (
      'route_path' => '/palabras/terminan-en/u',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/u',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 290,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 290 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 164 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    19 => 
    array (
      'route_path' => '/palabras/terminan-en/v',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/v',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 19,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 19 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 3 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    20 => 
    array (
      'route_path' => '/palabras/terminan-en/x',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/x',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 110,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 110 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 64 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    21 => 
    array (
      'route_path' => '/palabras/terminan-en/y',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/y',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 254,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 254 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 182 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
    22 => 
    array (
      'route_path' => '/palabras/terminan-en/z',
      'family' => 'word_list_terminant',
      'robots' => 'index,follow',
      'canonical_path' => '/palabras/terminan-en/z',
      'sitemap_fragment' => 'ends-0001',
      'result_count' => 559,
      'notes' => 'Palier 1 lettra de word_list_terminant (ES-022), simetrico a empiezan-por 1 letra ya indexado (ES-016). Enlace interno real desde /palabras (hub, noindex,follow -- App\\Search\\ExploreHubBuilder, seccion "Terminan En", list_counts \'end\'), verificado en directo (php -S). 559 resultado(s) reales (todos los estatus, igual que la pagina real; result_count almacena el conteo REAL, nunca el limite de examen) -- por debajo del limite de truncamiento. TTFB medido en directo (calentamiento + mediana de 3 ejecuciones) en los buckets mas pesados (S/N/A/O/E/D) : 93-106 ms, muy por debajo del presupuesto 250 ms pese al truncamiento. 510 de esos resultados son palabras admitidas (umbral de apertura respetado, misma disciplina que empiezan-por K/W).',
    ),
  ),
);
