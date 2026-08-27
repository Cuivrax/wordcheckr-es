<?php

declare(strict_types=1);

// PROPOSITION generee par scripts/propose_seo_batch.php -- NON appliquee.
// 642 ligne(s). Relire chaque 'notes', ajuster sitemap_fragment si necessaire
// (40 000 URL max par fragment, docs/05), PUIS lancer scripts/apply_seo_batch.php sur ce
// fichier -- jamais avant validation humaine explicite du lot (CLAUDE.md).

return array (
  'batch_id' => 'commencant_avec-proposed-2026-08-18',
  'added_at' => '2026-08-18',
  'rows' => 
  array (
    0 => 
    array (
      'route_path' => '/mots/commencant/a/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7695,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    1 => 
    array (
      'route_path' => '/mots/commencant/a/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    2 => 
    array (
      'route_path' => '/mots/commencant/a/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8025,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    3 => 
    array (
      'route_path' => '/mots/commencant/a/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    4 => 
    array (
      'route_path' => '/mots/commencant/a/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4576,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    5 => 
    array (
      'route_path' => '/mots/commencant/a/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8937,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    6 => 
    array (
      'route_path' => '/mots/commencant/a/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5846,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    7 => 
    array (
      'route_path' => '/mots/commencant/a/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    8 => 
    array (
      'route_path' => '/mots/commencant/a/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 824,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    9 => 
    array (
      'route_path' => '/mots/commencant/a/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 286,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    10 => 
    array (
      'route_path' => '/mots/commencant/a/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    11 => 
    array (
      'route_path' => '/mots/commencant/a/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    12 => 
    array (
      'route_path' => '/mots/commencant/a/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    13 => 
    array (
      'route_path' => '/mots/commencant/a/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    14 => 
    array (
      'route_path' => '/mots/commencant/a/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8789,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    15 => 
    array (
      'route_path' => '/mots/commencant/a/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3155,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    16 => 
    array (
      'route_path' => '/mots/commencant/a/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    17 => 
    array (
      'route_path' => '/mots/commencant/a/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    18 => 
    array (
      'route_path' => '/mots/commencant/a/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    19 => 
    array (
      'route_path' => '/mots/commencant/a/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    20 => 
    array (
      'route_path' => '/mots/commencant/a/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4214,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    21 => 
    array (
      'route_path' => '/mots/commencant/a/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 44,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    22 => 
    array (
      'route_path' => '/mots/commencant/a/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1045,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    23 => 
    array (
      'route_path' => '/mots/commencant/a/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2280,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    24 => 
    array (
      'route_path' => '/mots/commencant/a/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/a/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5255,
      'notes' => 'maillage interne reel depuis /mots/commencant/a (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    25 => 
    array (
      'route_path' => '/mots/commencant/b/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    26 => 
    array (
      'route_path' => '/mots/commencant/b/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6602,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    27 => 
    array (
      'route_path' => '/mots/commencant/b/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5448,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    28 => 
    array (
      'route_path' => '/mots/commencant/b/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    29 => 
    array (
      'route_path' => '/mots/commencant/b/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1911,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    30 => 
    array (
      'route_path' => '/mots/commencant/b/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5048,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    31 => 
    array (
      'route_path' => '/mots/commencant/b/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3753,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    32 => 
    array (
      'route_path' => '/mots/commencant/b/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    33 => 
    array (
      'route_path' => '/mots/commencant/b/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 402,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    34 => 
    array (
      'route_path' => '/mots/commencant/b/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 835,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    35 => 
    array (
      'route_path' => '/mots/commencant/b/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    36 => 
    array (
      'route_path' => '/mots/commencant/b/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3605,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    37 => 
    array (
      'route_path' => '/mots/commencant/b/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    38 => 
    array (
      'route_path' => '/mots/commencant/b/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    39 => 
    array (
      'route_path' => '/mots/commencant/b/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 989,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    40 => 
    array (
      'route_path' => '/mots/commencant/b/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2462,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    41 => 
    array (
      'route_path' => '/mots/commencant/b/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    42 => 
    array (
      'route_path' => '/mots/commencant/b/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    43 => 
    array (
      'route_path' => '/mots/commencant/b/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    44 => 
    array (
      'route_path' => '/mots/commencant/b/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    45 => 
    array (
      'route_path' => '/mots/commencant/b/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2477,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    46 => 
    array (
      'route_path' => '/mots/commencant/b/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 84,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    47 => 
    array (
      'route_path' => '/mots/commencant/b/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 490,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    48 => 
    array (
      'route_path' => '/mots/commencant/b/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1825,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    49 => 
    array (
      'route_path' => '/mots/commencant/b/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/b/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4288,
      'notes' => 'maillage interne reel depuis /mots/commencant/b (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    50 => 
    array (
      'route_path' => '/mots/commencant/c/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    51 => 
    array (
      'route_path' => '/mots/commencant/c/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5992,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    52 => 
    array (
      'route_path' => '/mots/commencant/c/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7809,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    53 => 
    array (
      'route_path' => '/mots/commencant/c/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    54 => 
    array (
      'route_path' => '/mots/commencant/c/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4099,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    55 => 
    array (
      'route_path' => '/mots/commencant/c/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6041,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    56 => 
    array (
      'route_path' => '/mots/commencant/c/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    57 => 
    array (
      'route_path' => '/mots/commencant/c/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    58 => 
    array (
      'route_path' => '/mots/commencant/c/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 491,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    59 => 
    array (
      'route_path' => '/mots/commencant/c/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 410,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    60 => 
    array (
      'route_path' => '/mots/commencant/c/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    61 => 
    array (
      'route_path' => '/mots/commencant/c/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    62 => 
    array (
      'route_path' => '/mots/commencant/c/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    63 => 
    array (
      'route_path' => '/mots/commencant/c/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    64 => 
    array (
      'route_path' => '/mots/commencant/c/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    65 => 
    array (
      'route_path' => '/mots/commencant/c/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3693,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    66 => 
    array (
      'route_path' => '/mots/commencant/c/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    67 => 
    array (
      'route_path' => '/mots/commencant/c/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    68 => 
    array (
      'route_path' => '/mots/commencant/c/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    69 => 
    array (
      'route_path' => '/mots/commencant/c/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    70 => 
    array (
      'route_path' => '/mots/commencant/c/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4933,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    71 => 
    array (
      'route_path' => '/mots/commencant/c/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 112,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    72 => 
    array (
      'route_path' => '/mots/commencant/c/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 829,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    73 => 
    array (
      'route_path' => '/mots/commencant/c/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3318,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    74 => 
    array (
      'route_path' => '/mots/commencant/c/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/c/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6323,
      'notes' => 'maillage interne reel depuis /mots/commencant/c (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    75 => 
    array (
      'route_path' => '/mots/commencant/d/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    76 => 
    array (
      'route_path' => '/mots/commencant/d/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9499,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    77 => 
    array (
      'route_path' => '/mots/commencant/d/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    78 => 
    array (
      'route_path' => '/mots/commencant/d/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    79 => 
    array (
      'route_path' => '/mots/commencant/d/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7880,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    80 => 
    array (
      'route_path' => '/mots/commencant/d/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    81 => 
    array (
      'route_path' => '/mots/commencant/d/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6628,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    82 => 
    array (
      'route_path' => '/mots/commencant/d/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    83 => 
    array (
      'route_path' => '/mots/commencant/d/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 847,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    84 => 
    array (
      'route_path' => '/mots/commencant/d/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 535,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    85 => 
    array (
      'route_path' => '/mots/commencant/d/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    86 => 
    array (
      'route_path' => '/mots/commencant/d/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    87 => 
    array (
      'route_path' => '/mots/commencant/d/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    88 => 
    array (
      'route_path' => '/mots/commencant/d/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    89 => 
    array (
      'route_path' => '/mots/commencant/d/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    90 => 
    array (
      'route_path' => '/mots/commencant/d/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3216,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    91 => 
    array (
      'route_path' => '/mots/commencant/d/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    92 => 
    array (
      'route_path' => '/mots/commencant/d/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    93 => 
    array (
      'route_path' => '/mots/commencant/d/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    94 => 
    array (
      'route_path' => '/mots/commencant/d/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    95 => 
    array (
      'route_path' => '/mots/commencant/d/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6130,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    96 => 
    array (
      'route_path' => '/mots/commencant/d/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 146,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    97 => 
    array (
      'route_path' => '/mots/commencant/d/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1010,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    98 => 
    array (
      'route_path' => '/mots/commencant/d/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2694,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    99 => 
    array (
      'route_path' => '/mots/commencant/d/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/d/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9364,
      'notes' => 'maillage interne reel depuis /mots/commencant/d (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    100 => 
    array (
      'route_path' => '/mots/commencant/e/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    101 => 
    array (
      'route_path' => '/mots/commencant/e/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8970,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    102 => 
    array (
      'route_path' => '/mots/commencant/e/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    103 => 
    array (
      'route_path' => '/mots/commencant/e/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6218,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    104 => 
    array (
      'route_path' => '/mots/commencant/e/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5485,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    105 => 
    array (
      'route_path' => '/mots/commencant/e/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9439,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    106 => 
    array (
      'route_path' => '/mots/commencant/e/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7903,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    107 => 
    array (
      'route_path' => '/mots/commencant/e/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    108 => 
    array (
      'route_path' => '/mots/commencant/e/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1013,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    109 => 
    array (
      'route_path' => '/mots/commencant/e/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 138,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    110 => 
    array (
      'route_path' => '/mots/commencant/e/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    111 => 
    array (
      'route_path' => '/mots/commencant/e/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    112 => 
    array (
      'route_path' => '/mots/commencant/e/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    113 => 
    array (
      'route_path' => '/mots/commencant/e/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    114 => 
    array (
      'route_path' => '/mots/commencant/e/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    115 => 
    array (
      'route_path' => '/mots/commencant/e/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4107,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    116 => 
    array (
      'route_path' => '/mots/commencant/e/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    117 => 
    array (
      'route_path' => '/mots/commencant/e/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    118 => 
    array (
      'route_path' => '/mots/commencant/e/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    119 => 
    array (
      'route_path' => '/mots/commencant/e/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    120 => 
    array (
      'route_path' => '/mots/commencant/e/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5469,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    121 => 
    array (
      'route_path' => '/mots/commencant/e/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 68,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    122 => 
    array (
      'route_path' => '/mots/commencant/e/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5050,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    123 => 
    array (
      'route_path' => '/mots/commencant/e/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1808,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    124 => 
    array (
      'route_path' => '/mots/commencant/e/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/e/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7473,
      'notes' => 'maillage interne reel depuis /mots/commencant/e (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    125 => 
    array (
      'route_path' => '/mots/commencant/f/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    126 => 
    array (
      'route_path' => '/mots/commencant/f/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1446,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    127 => 
    array (
      'route_path' => '/mots/commencant/f/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4675,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    128 => 
    array (
      'route_path' => '/mots/commencant/f/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2370,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    129 => 
    array (
      'route_path' => '/mots/commencant/f/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    130 => 
    array (
      'route_path' => '/mots/commencant/f/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3057,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    131 => 
    array (
      'route_path' => '/mots/commencant/f/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1384,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    132 => 
    array (
      'route_path' => '/mots/commencant/f/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    133 => 
    array (
      'route_path' => '/mots/commencant/f/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 159,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    134 => 
    array (
      'route_path' => '/mots/commencant/f/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 284,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    135 => 
    array (
      'route_path' => '/mots/commencant/f/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9792,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    136 => 
    array (
      'route_path' => '/mots/commencant/f/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3335,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    137 => 
    array (
      'route_path' => '/mots/commencant/f/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    138 => 
    array (
      'route_path' => '/mots/commencant/f/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    139 => 
    array (
      'route_path' => '/mots/commencant/f/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 525,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    140 => 
    array (
      'route_path' => '/mots/commencant/f/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 901,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    141 => 
    array (
      'route_path' => '/mots/commencant/f/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    142 => 
    array (
      'route_path' => '/mots/commencant/f/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    143 => 
    array (
      'route_path' => '/mots/commencant/f/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    144 => 
    array (
      'route_path' => '/mots/commencant/f/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8785,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    145 => 
    array (
      'route_path' => '/mots/commencant/f/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 633,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    146 => 
    array (
      'route_path' => '/mots/commencant/f/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 93,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    147 => 
    array (
      'route_path' => '/mots/commencant/f/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 523,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    148 => 
    array (
      'route_path' => '/mots/commencant/f/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 865,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    149 => 
    array (
      'route_path' => '/mots/commencant/f/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/f/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2217,
      'notes' => 'maillage interne reel depuis /mots/commencant/f (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    150 => 
    array (
      'route_path' => '/mots/commencant/g/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    151 => 
    array (
      'route_path' => '/mots/commencant/g/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1795,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    152 => 
    array (
      'route_path' => '/mots/commencant/g/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2852,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    153 => 
    array (
      'route_path' => '/mots/commencant/g/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2770,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    154 => 
    array (
      'route_path' => '/mots/commencant/g/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    155 => 
    array (
      'route_path' => '/mots/commencant/g/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1183,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    156 => 
    array (
      'route_path' => '/mots/commencant/g/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1673,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    157 => 
    array (
      'route_path' => '/mots/commencant/g/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    158 => 
    array (
      'route_path' => '/mots/commencant/g/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 185,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    159 => 
    array (
      'route_path' => '/mots/commencant/g/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 91,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    160 => 
    array (
      'route_path' => '/mots/commencant/g/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8586,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    161 => 
    array (
      'route_path' => '/mots/commencant/g/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3088,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    162 => 
    array (
      'route_path' => '/mots/commencant/g/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    163 => 
    array (
      'route_path' => '/mots/commencant/g/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    164 => 
    array (
      'route_path' => '/mots/commencant/g/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1578,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    165 => 
    array (
      'route_path' => '/mots/commencant/g/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 531,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    166 => 
    array (
      'route_path' => '/mots/commencant/g/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    167 => 
    array (
      'route_path' => '/mots/commencant/g/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    168 => 
    array (
      'route_path' => '/mots/commencant/g/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9113,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    169 => 
    array (
      'route_path' => '/mots/commencant/g/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7543,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    170 => 
    array (
      'route_path' => '/mots/commencant/g/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1481,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    171 => 
    array (
      'route_path' => '/mots/commencant/g/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 53,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    172 => 
    array (
      'route_path' => '/mots/commencant/g/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 211,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    173 => 
    array (
      'route_path' => '/mots/commencant/g/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 999,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    174 => 
    array (
      'route_path' => '/mots/commencant/g/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/g/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2246,
      'notes' => 'maillage interne reel depuis /mots/commencant/g (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    175 => 
    array (
      'route_path' => '/mots/commencant/h/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8673,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    176 => 
    array (
      'route_path' => '/mots/commencant/h/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1345,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    177 => 
    array (
      'route_path' => '/mots/commencant/h/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2376,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    178 => 
    array (
      'route_path' => '/mots/commencant/h/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2295,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    179 => 
    array (
      'route_path' => '/mots/commencant/h/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    180 => 
    array (
      'route_path' => '/mots/commencant/h/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 363,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    181 => 
    array (
      'route_path' => '/mots/commencant/h/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1754,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    182 => 
    array (
      'route_path' => '/mots/commencant/h/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9332,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    183 => 
    array (
      'route_path' => '/mots/commencant/h/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 60,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    184 => 
    array (
      'route_path' => '/mots/commencant/h/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 261,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    185 => 
    array (
      'route_path' => '/mots/commencant/h/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4037,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    186 => 
    array (
      'route_path' => '/mots/commencant/h/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3223,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    187 => 
    array (
      'route_path' => '/mots/commencant/h/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6081,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    188 => 
    array (
      'route_path' => '/mots/commencant/h/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8002,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    189 => 
    array (
      'route_path' => '/mots/commencant/h/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3176,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    190 => 
    array (
      'route_path' => '/mots/commencant/h/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 802,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    191 => 
    array (
      'route_path' => '/mots/commencant/h/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8100,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    192 => 
    array (
      'route_path' => '/mots/commencant/h/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8624,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    193 => 
    array (
      'route_path' => '/mots/commencant/h/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6239,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    194 => 
    array (
      'route_path' => '/mots/commencant/h/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4106,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    195 => 
    array (
      'route_path' => '/mots/commencant/h/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 639,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    196 => 
    array (
      'route_path' => '/mots/commencant/h/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 47,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    197 => 
    array (
      'route_path' => '/mots/commencant/h/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 346,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    198 => 
    array (
      'route_path' => '/mots/commencant/h/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2722,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    199 => 
    array (
      'route_path' => '/mots/commencant/h/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/h/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1042,
      'notes' => 'maillage interne reel depuis /mots/commencant/h (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    200 => 
    array (
      'route_path' => '/mots/commencant/i/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    201 => 
    array (
      'route_path' => '/mots/commencant/i/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1956,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    202 => 
    array (
      'route_path' => '/mots/commencant/i/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4708,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    203 => 
    array (
      'route_path' => '/mots/commencant/i/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3203,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    204 => 
    array (
      'route_path' => '/mots/commencant/i/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    205 => 
    array (
      'route_path' => '/mots/commencant/i/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2164,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    206 => 
    array (
      'route_path' => '/mots/commencant/i/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2397,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    207 => 
    array (
      'route_path' => '/mots/commencant/i/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 779,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    208 => 
    array (
      'route_path' => '/mots/commencant/i/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 295,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    209 => 
    array (
      'route_path' => '/mots/commencant/i/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 77,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    210 => 
    array (
      'route_path' => '/mots/commencant/i/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6227,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    211 => 
    array (
      'route_path' => '/mots/commencant/i/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4765,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    212 => 
    array (
      'route_path' => '/mots/commencant/i/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    213 => 
    array (
      'route_path' => '/mots/commencant/i/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7202,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    214 => 
    array (
      'route_path' => '/mots/commencant/i/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3138,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    215 => 
    array (
      'route_path' => '/mots/commencant/i/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 889,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    216 => 
    array (
      'route_path' => '/mots/commencant/i/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    217 => 
    array (
      'route_path' => '/mots/commencant/i/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    218 => 
    array (
      'route_path' => '/mots/commencant/i/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    219 => 
    array (
      'route_path' => '/mots/commencant/i/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5196,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    220 => 
    array (
      'route_path' => '/mots/commencant/i/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1925,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    221 => 
    array (
      'route_path' => '/mots/commencant/i/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 52,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    222 => 
    array (
      'route_path' => '/mots/commencant/i/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 491,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    223 => 
    array (
      'route_path' => '/mots/commencant/i/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 210,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    224 => 
    array (
      'route_path' => '/mots/commencant/i/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/i/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1455,
      'notes' => 'maillage interne reel depuis /mots/commencant/i (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    225 => 
    array (
      'route_path' => '/mots/commencant/j/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4043,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    226 => 
    array (
      'route_path' => '/mots/commencant/j/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 532,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    227 => 
    array (
      'route_path' => '/mots/commencant/j/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 991,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    228 => 
    array (
      'route_path' => '/mots/commencant/j/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 692,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    229 => 
    array (
      'route_path' => '/mots/commencant/j/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4358,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    230 => 
    array (
      'route_path' => '/mots/commencant/j/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 124,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    231 => 
    array (
      'route_path' => '/mots/commencant/j/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 589,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    232 => 
    array (
      'route_path' => '/mots/commencant/j/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 224,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    233 => 
    array (
      'route_path' => '/mots/commencant/j/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3631,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    234 => 
    array (
      'route_path' => '/mots/commencant/j/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 173,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    235 => 
    array (
      'route_path' => '/mots/commencant/j/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1309,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    236 => 
    array (
      'route_path' => '/mots/commencant/j/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 574,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    237 => 
    array (
      'route_path' => '/mots/commencant/j/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3024,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    238 => 
    array (
      'route_path' => '/mots/commencant/j/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2881,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    239 => 
    array (
      'route_path' => '/mots/commencant/j/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 559,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    240 => 
    array (
      'route_path' => '/mots/commencant/j/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 213,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    241 => 
    array (
      'route_path' => '/mots/commencant/j/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2381,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    242 => 
    array (
      'route_path' => '/mots/commencant/j/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3635,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    243 => 
    array (
      'route_path' => '/mots/commencant/j/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2121,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    244 => 
    array (
      'route_path' => '/mots/commencant/j/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2538,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    245 => 
    array (
      'route_path' => '/mots/commencant/j/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 465,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    246 => 
    array (
      'route_path' => '/mots/commencant/j/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    247 => 
    array (
      'route_path' => '/mots/commencant/j/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 115,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    248 => 
    array (
      'route_path' => '/mots/commencant/j/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 146,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    249 => 
    array (
      'route_path' => '/mots/commencant/j/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/j/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 586,
      'notes' => 'maillage interne reel depuis /mots/commencant/j (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    250 => 
    array (
      'route_path' => '/mots/commencant/k/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1379,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    251 => 
    array (
      'route_path' => '/mots/commencant/k/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 237,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    252 => 
    array (
      'route_path' => '/mots/commencant/k/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 392,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    253 => 
    array (
      'route_path' => '/mots/commencant/k/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 179,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    254 => 
    array (
      'route_path' => '/mots/commencant/k/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1511,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    255 => 
    array (
      'route_path' => '/mots/commencant/k/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 186,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    256 => 
    array (
      'route_path' => '/mots/commencant/k/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 174,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    257 => 
    array (
      'route_path' => '/mots/commencant/k/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 441,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    258 => 
    array (
      'route_path' => '/mots/commencant/k/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1519,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    259 => 
    array (
      'route_path' => '/mots/commencant/k/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 32,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    260 => 
    array (
      'route_path' => '/mots/commencant/k/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 672,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    261 => 
    array (
      'route_path' => '/mots/commencant/k/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 326,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    262 => 
    array (
      'route_path' => '/mots/commencant/k/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 975,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    263 => 
    array (
      'route_path' => '/mots/commencant/k/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1020,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    264 => 
    array (
      'route_path' => '/mots/commencant/k/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 233,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    265 => 
    array (
      'route_path' => '/mots/commencant/k/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 61,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    266 => 
    array (
      'route_path' => '/mots/commencant/k/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 903,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    267 => 
    array (
      'route_path' => '/mots/commencant/k/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1375,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    268 => 
    array (
      'route_path' => '/mots/commencant/k/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 846,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    269 => 
    array (
      'route_path' => '/mots/commencant/k/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 422,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    270 => 
    array (
      'route_path' => '/mots/commencant/k/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 72,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    271 => 
    array (
      'route_path' => '/mots/commencant/k/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 71,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    272 => 
    array (
      'route_path' => '/mots/commencant/k/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 57,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    273 => 
    array (
      'route_path' => '/mots/commencant/k/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 190,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    274 => 
    array (
      'route_path' => '/mots/commencant/k/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/k/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 199,
      'notes' => 'maillage interne reel depuis /mots/commencant/k (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    275 => 
    array (
      'route_path' => '/mots/commencant/l/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    276 => 
    array (
      'route_path' => '/mots/commencant/l/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1787,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    277 => 
    array (
      'route_path' => '/mots/commencant/l/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3171,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    278 => 
    array (
      'route_path' => '/mots/commencant/l/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1807,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    279 => 
    array (
      'route_path' => '/mots/commencant/l/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    280 => 
    array (
      'route_path' => '/mots/commencant/l/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 656,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    281 => 
    array (
      'route_path' => '/mots/commencant/l/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3200,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    282 => 
    array (
      'route_path' => '/mots/commencant/l/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1509,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    283 => 
    array (
      'route_path' => '/mots/commencant/l/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    284 => 
    array (
      'route_path' => '/mots/commencant/l/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 64,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    285 => 
    array (
      'route_path' => '/mots/commencant/l/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 254,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    286 => 
    array (
      'route_path' => '/mots/commencant/l/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2577,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    287 => 
    array (
      'route_path' => '/mots/commencant/l/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9111,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    288 => 
    array (
      'route_path' => '/mots/commencant/l/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8301,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    289 => 
    array (
      'route_path' => '/mots/commencant/l/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1447,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    290 => 
    array (
      'route_path' => '/mots/commencant/l/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 746,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    291 => 
    array (
      'route_path' => '/mots/commencant/l/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7338,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    292 => 
    array (
      'route_path' => '/mots/commencant/l/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    293 => 
    array (
      'route_path' => '/mots/commencant/l/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6468,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    294 => 
    array (
      'route_path' => '/mots/commencant/l/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5697,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    295 => 
    array (
      'route_path' => '/mots/commencant/l/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1492,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    296 => 
    array (
      'route_path' => '/mots/commencant/l/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 30,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    297 => 
    array (
      'route_path' => '/mots/commencant/l/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 373,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    298 => 
    array (
      'route_path' => '/mots/commencant/l/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1130,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    299 => 
    array (
      'route_path' => '/mots/commencant/l/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/l/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1400,
      'notes' => 'maillage interne reel depuis /mots/commencant/l (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    300 => 
    array (
      'route_path' => '/mots/commencant/m/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    301 => 
    array (
      'route_path' => '/mots/commencant/m/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1453,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    302 => 
    array (
      'route_path' => '/mots/commencant/m/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7699,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    303 => 
    array (
      'route_path' => '/mots/commencant/m/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5141,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    304 => 
    array (
      'route_path' => '/mots/commencant/m/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    305 => 
    array (
      'route_path' => '/mots/commencant/m/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2019,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    306 => 
    array (
      'route_path' => '/mots/commencant/m/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5079,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    307 => 
    array (
      'route_path' => '/mots/commencant/m/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3965,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    308 => 
    array (
      'route_path' => '/mots/commencant/m/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    309 => 
    array (
      'route_path' => '/mots/commencant/m/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 403,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    310 => 
    array (
      'route_path' => '/mots/commencant/m/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 351,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    311 => 
    array (
      'route_path' => '/mots/commencant/m/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    312 => 
    array (
      'route_path' => '/mots/commencant/m/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    313 => 
    array (
      'route_path' => '/mots/commencant/m/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    314 => 
    array (
      'route_path' => '/mots/commencant/m/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2313,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    315 => 
    array (
      'route_path' => '/mots/commencant/m/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2078,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    316 => 
    array (
      'route_path' => '/mots/commencant/m/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    317 => 
    array (
      'route_path' => '/mots/commencant/m/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    318 => 
    array (
      'route_path' => '/mots/commencant/m/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    319 => 
    array (
      'route_path' => '/mots/commencant/m/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    320 => 
    array (
      'route_path' => '/mots/commencant/m/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1631,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    321 => 
    array (
      'route_path' => '/mots/commencant/m/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 52,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    322 => 
    array (
      'route_path' => '/mots/commencant/m/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 871,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    323 => 
    array (
      'route_path' => '/mots/commencant/m/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2054,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    324 => 
    array (
      'route_path' => '/mots/commencant/m/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/m/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3232,
      'notes' => 'maillage interne reel depuis /mots/commencant/m (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    325 => 
    array (
      'route_path' => '/mots/commencant/n/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6173,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    326 => 
    array (
      'route_path' => '/mots/commencant/n/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 608,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    327 => 
    array (
      'route_path' => '/mots/commencant/n/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1858,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    328 => 
    array (
      'route_path' => '/mots/commencant/n/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 897,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    329 => 
    array (
      'route_path' => '/mots/commencant/n/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7831,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    330 => 
    array (
      'route_path' => '/mots/commencant/n/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 551,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    331 => 
    array (
      'route_path' => '/mots/commencant/n/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1120,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    332 => 
    array (
      'route_path' => '/mots/commencant/n/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 719,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    333 => 
    array (
      'route_path' => '/mots/commencant/n/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6939,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    334 => 
    array (
      'route_path' => '/mots/commencant/n/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 40,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    335 => 
    array (
      'route_path' => '/mots/commencant/n/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 272,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    336 => 
    array (
      'route_path' => '/mots/commencant/n/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3000,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    337 => 
    array (
      'route_path' => '/mots/commencant/n/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1654,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    338 => 
    array (
      'route_path' => '/mots/commencant/n/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5369,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    339 => 
    array (
      'route_path' => '/mots/commencant/n/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 911,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    340 => 
    array (
      'route_path' => '/mots/commencant/n/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 432,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    341 => 
    array (
      'route_path' => '/mots/commencant/n/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4613,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    342 => 
    array (
      'route_path' => '/mots/commencant/n/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6108,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    343 => 
    array (
      'route_path' => '/mots/commencant/n/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4274,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    344 => 
    array (
      'route_path' => '/mots/commencant/n/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2942,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    345 => 
    array (
      'route_path' => '/mots/commencant/n/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1218,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    346 => 
    array (
      'route_path' => '/mots/commencant/n/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 123,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    347 => 
    array (
      'route_path' => '/mots/commencant/n/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 147,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    348 => 
    array (
      'route_path' => '/mots/commencant/n/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 413,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    349 => 
    array (
      'route_path' => '/mots/commencant/n/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/n/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 810,
      'notes' => 'maillage interne reel depuis /mots/commencant/n (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    350 => 
    array (
      'route_path' => '/mots/commencant/o/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5667,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    351 => 
    array (
      'route_path' => '/mots/commencant/o/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1626,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    352 => 
    array (
      'route_path' => '/mots/commencant/o/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2630,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    353 => 
    array (
      'route_path' => '/mots/commencant/o/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1280,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    354 => 
    array (
      'route_path' => '/mots/commencant/o/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8463,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    355 => 
    array (
      'route_path' => '/mots/commencant/o/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 590,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    356 => 
    array (
      'route_path' => '/mots/commencant/o/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1669,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    357 => 
    array (
      'route_path' => '/mots/commencant/o/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1083,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    358 => 
    array (
      'route_path' => '/mots/commencant/o/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7382,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    359 => 
    array (
      'route_path' => '/mots/commencant/o/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 153,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    360 => 
    array (
      'route_path' => '/mots/commencant/o/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 139,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    361 => 
    array (
      'route_path' => '/mots/commencant/o/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3140,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    362 => 
    array (
      'route_path' => '/mots/commencant/o/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1527,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    363 => 
    array (
      'route_path' => '/mots/commencant/o/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5216,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    364 => 
    array (
      'route_path' => '/mots/commencant/o/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1853,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    365 => 
    array (
      'route_path' => '/mots/commencant/o/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 496,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    366 => 
    array (
      'route_path' => '/mots/commencant/o/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6093,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    367 => 
    array (
      'route_path' => '/mots/commencant/o/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6749,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    368 => 
    array (
      'route_path' => '/mots/commencant/o/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4978,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    369 => 
    array (
      'route_path' => '/mots/commencant/o/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3007,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    370 => 
    array (
      'route_path' => '/mots/commencant/o/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1062,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    371 => 
    array (
      'route_path' => '/mots/commencant/o/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 38,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    372 => 
    array (
      'route_path' => '/mots/commencant/o/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 430,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    373 => 
    array (
      'route_path' => '/mots/commencant/o/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 576,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    374 => 
    array (
      'route_path' => '/mots/commencant/o/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/o/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 894,
      'notes' => 'maillage interne reel depuis /mots/commencant/o (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    375 => 
    array (
      'route_path' => '/mots/commencant/p/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    376 => 
    array (
      'route_path' => '/mots/commencant/p/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2056,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    377 => 
    array (
      'route_path' => '/mots/commencant/p/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    378 => 
    array (
      'route_path' => '/mots/commencant/p/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6113,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    379 => 
    array (
      'route_path' => '/mots/commencant/p/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    380 => 
    array (
      'route_path' => '/mots/commencant/p/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3281,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    381 => 
    array (
      'route_path' => '/mots/commencant/p/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5815,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    382 => 
    array (
      'route_path' => '/mots/commencant/p/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6704,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    383 => 
    array (
      'route_path' => '/mots/commencant/p/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    384 => 
    array (
      'route_path' => '/mots/commencant/p/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 388,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    385 => 
    array (
      'route_path' => '/mots/commencant/p/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 423,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    386 => 
    array (
      'route_path' => '/mots/commencant/p/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    387 => 
    array (
      'route_path' => '/mots/commencant/p/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7938,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    388 => 
    array (
      'route_path' => '/mots/commencant/p/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    389 => 
    array (
      'route_path' => '/mots/commencant/p/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    390 => 
    array (
      'route_path' => '/mots/commencant/p/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3092,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    391 => 
    array (
      'route_path' => '/mots/commencant/p/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    392 => 
    array (
      'route_path' => '/mots/commencant/p/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    393 => 
    array (
      'route_path' => '/mots/commencant/p/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    394 => 
    array (
      'route_path' => '/mots/commencant/p/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    395 => 
    array (
      'route_path' => '/mots/commencant/p/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3008,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    396 => 
    array (
      'route_path' => '/mots/commencant/p/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 26,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    397 => 
    array (
      'route_path' => '/mots/commencant/p/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 929,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    398 => 
    array (
      'route_path' => '/mots/commencant/p/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4152,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    399 => 
    array (
      'route_path' => '/mots/commencant/p/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/p/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4903,
      'notes' => 'maillage interne reel depuis /mots/commencant/p (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    400 => 
    array (
      'route_path' => '/mots/commencant/q/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1759,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    401 => 
    array (
      'route_path' => '/mots/commencant/q/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 156,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    402 => 
    array (
      'route_path' => '/mots/commencant/q/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 442,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    403 => 
    array (
      'route_path' => '/mots/commencant/q/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 491,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    404 => 
    array (
      'route_path' => '/mots/commencant/q/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2188,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    405 => 
    array (
      'route_path' => '/mots/commencant/q/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 133,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    406 => 
    array (
      'route_path' => '/mots/commencant/q/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 126,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    407 => 
    array (
      'route_path' => '/mots/commencant/q/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 87,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    408 => 
    array (
      'route_path' => '/mots/commencant/q/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2012,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    409 => 
    array (
      'route_path' => '/mots/commencant/q/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 19,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    410 => 
    array (
      'route_path' => '/mots/commencant/q/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 18,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    411 => 
    array (
      'route_path' => '/mots/commencant/q/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 668,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    412 => 
    array (
      'route_path' => '/mots/commencant/q/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 243,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    413 => 
    array (
      'route_path' => '/mots/commencant/q/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1416,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    414 => 
    array (
      'route_path' => '/mots/commencant/q/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 921,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    415 => 
    array (
      'route_path' => '/mots/commencant/q/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 193,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    416 => 
    array (
      'route_path' => '/mots/commencant/q/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1371,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    417 => 
    array (
      'route_path' => '/mots/commencant/q/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1503,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    418 => 
    array (
      'route_path' => '/mots/commencant/q/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1442,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    419 => 
    array (
      'route_path' => '/mots/commencant/q/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2576,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    420 => 
    array (
      'route_path' => '/mots/commencant/q/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 77,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    421 => 
    array (
      'route_path' => '/mots/commencant/q/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    422 => 
    array (
      'route_path' => '/mots/commencant/q/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 26,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    423 => 
    array (
      'route_path' => '/mots/commencant/q/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 72,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    424 => 
    array (
      'route_path' => '/mots/commencant/q/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/q/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 235,
      'notes' => 'maillage interne reel depuis /mots/commencant/q (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    425 => 
    array (
      'route_path' => '/mots/commencant/r/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    426 => 
    array (
      'route_path' => '/mots/commencant/r/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    427 => 
    array (
      'route_path' => '/mots/commencant/r/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    428 => 
    array (
      'route_path' => '/mots/commencant/r/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    429 => 
    array (
      'route_path' => '/mots/commencant/r/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    430 => 
    array (
      'route_path' => '/mots/commencant/r/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    431 => 
    array (
      'route_path' => '/mots/commencant/r/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    432 => 
    array (
      'route_path' => '/mots/commencant/r/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    433 => 
    array (
      'route_path' => '/mots/commencant/r/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    434 => 
    array (
      'route_path' => '/mots/commencant/r/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3714,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    435 => 
    array (
      'route_path' => '/mots/commencant/r/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2222,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    436 => 
    array (
      'route_path' => '/mots/commencant/r/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    437 => 
    array (
      'route_path' => '/mots/commencant/r/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    438 => 
    array (
      'route_path' => '/mots/commencant/r/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    439 => 
    array (
      'route_path' => '/mots/commencant/r/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    440 => 
    array (
      'route_path' => '/mots/commencant/r/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    441 => 
    array (
      'route_path' => '/mots/commencant/r/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    442 => 
    array (
      'route_path' => '/mots/commencant/r/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    443 => 
    array (
      'route_path' => '/mots/commencant/r/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    444 => 
    array (
      'route_path' => '/mots/commencant/r/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    445 => 
    array (
      'route_path' => '/mots/commencant/r/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    446 => 
    array (
      'route_path' => '/mots/commencant/r/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 630,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    447 => 
    array (
      'route_path' => '/mots/commencant/r/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3733,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    448 => 
    array (
      'route_path' => '/mots/commencant/r/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6360,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    449 => 
    array (
      'route_path' => '/mots/commencant/r/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/r/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/r (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    450 => 
    array (
      'route_path' => '/mots/commencant/s/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    451 => 
    array (
      'route_path' => '/mots/commencant/s/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4322,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    452 => 
    array (
      'route_path' => '/mots/commencant/s/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    453 => 
    array (
      'route_path' => '/mots/commencant/s/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5221,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    454 => 
    array (
      'route_path' => '/mots/commencant/s/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    455 => 
    array (
      'route_path' => '/mots/commencant/s/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3959,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    456 => 
    array (
      'route_path' => '/mots/commencant/s/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5226,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    457 => 
    array (
      'route_path' => '/mots/commencant/s/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4429,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    458 => 
    array (
      'route_path' => '/mots/commencant/s/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    459 => 
    array (
      'route_path' => '/mots/commencant/s/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 419,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    460 => 
    array (
      'route_path' => '/mots/commencant/s/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1096,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    461 => 
    array (
      'route_path' => '/mots/commencant/s/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    462 => 
    array (
      'route_path' => '/mots/commencant/s/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7629,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    463 => 
    array (
      'route_path' => '/mots/commencant/s/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    464 => 
    array (
      'route_path' => '/mots/commencant/s/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    465 => 
    array (
      'route_path' => '/mots/commencant/s/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8565,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    466 => 
    array (
      'route_path' => '/mots/commencant/s/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1796,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    467 => 
    array (
      'route_path' => '/mots/commencant/s/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    468 => 
    array (
      'route_path' => '/mots/commencant/s/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    469 => 
    array (
      'route_path' => '/mots/commencant/s/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    470 => 
    array (
      'route_path' => '/mots/commencant/s/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3173,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    471 => 
    array (
      'route_path' => '/mots/commencant/s/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 312,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    472 => 
    array (
      'route_path' => '/mots/commencant/s/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1179,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    473 => 
    array (
      'route_path' => '/mots/commencant/s/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2627,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    474 => 
    array (
      'route_path' => '/mots/commencant/s/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/s/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4024,
      'notes' => 'maillage interne reel depuis /mots/commencant/s (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    475 => 
    array (
      'route_path' => '/mots/commencant/t/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    476 => 
    array (
      'route_path' => '/mots/commencant/t/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 3002,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    477 => 
    array (
      'route_path' => '/mots/commencant/t/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5199,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    478 => 
    array (
      'route_path' => '/mots/commencant/t/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2594,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    479 => 
    array (
      'route_path' => '/mots/commencant/t/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    480 => 
    array (
      'route_path' => '/mots/commencant/t/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1894,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    481 => 
    array (
      'route_path' => '/mots/commencant/t/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2874,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    482 => 
    array (
      'route_path' => '/mots/commencant/t/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4283,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    483 => 
    array (
      'route_path' => '/mots/commencant/t/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    484 => 
    array (
      'route_path' => '/mots/commencant/t/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 111,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    485 => 
    array (
      'route_path' => '/mots/commencant/t/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 529,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    486 => 
    array (
      'route_path' => '/mots/commencant/t/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    487 => 
    array (
      'route_path' => '/mots/commencant/t/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5178,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    488 => 
    array (
      'route_path' => '/mots/commencant/t/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    489 => 
    array (
      'route_path' => '/mots/commencant/t/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    490 => 
    array (
      'route_path' => '/mots/commencant/t/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4011,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    491 => 
    array (
      'route_path' => '/mots/commencant/t/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1742,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    492 => 
    array (
      'route_path' => '/mots/commencant/t/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    493 => 
    array (
      'route_path' => '/mots/commencant/t/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    494 => 
    array (
      'route_path' => '/mots/commencant/t/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    495 => 
    array (
      'route_path' => '/mots/commencant/t/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1555,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    496 => 
    array (
      'route_path' => '/mots/commencant/t/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 234,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    497 => 
    array (
      'route_path' => '/mots/commencant/t/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 729,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    498 => 
    array (
      'route_path' => '/mots/commencant/t/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1365,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    499 => 
    array (
      'route_path' => '/mots/commencant/t/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/t/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2821,
      'notes' => 'maillage interne reel depuis /mots/commencant/t (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    500 => 
    array (
      'route_path' => '/mots/commencant/u/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1291,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    501 => 
    array (
      'route_path' => '/mots/commencant/u/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 202,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    502 => 
    array (
      'route_path' => '/mots/commencant/u/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 402,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    503 => 
    array (
      'route_path' => '/mots/commencant/u/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 264,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    504 => 
    array (
      'route_path' => '/mots/commencant/u/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1869,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    505 => 
    array (
      'route_path' => '/mots/commencant/u/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 193,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    506 => 
    array (
      'route_path' => '/mots/commencant/u/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 203,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    507 => 
    array (
      'route_path' => '/mots/commencant/u/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 100,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    508 => 
    array (
      'route_path' => '/mots/commencant/u/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1684,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    509 => 
    array (
      'route_path' => '/mots/commencant/u/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 88,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    510 => 
    array (
      'route_path' => '/mots/commencant/u/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 901,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    511 => 
    array (
      'route_path' => '/mots/commencant/u/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 314,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    512 => 
    array (
      'route_path' => '/mots/commencant/u/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1331,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    513 => 
    array (
      'route_path' => '/mots/commencant/u/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 788,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    514 => 
    array (
      'route_path' => '/mots/commencant/u/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 367,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    515 => 
    array (
      'route_path' => '/mots/commencant/u/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 92,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    516 => 
    array (
      'route_path' => '/mots/commencant/u/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1452,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    517 => 
    array (
      'route_path' => '/mots/commencant/u/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1563,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    518 => 
    array (
      'route_path' => '/mots/commencant/u/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 964,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    519 => 
    array (
      'route_path' => '/mots/commencant/u/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 148,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    520 => 
    array (
      'route_path' => '/mots/commencant/u/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 16,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    521 => 
    array (
      'route_path' => '/mots/commencant/u/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 62,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    522 => 
    array (
      'route_path' => '/mots/commencant/u/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 51,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    523 => 
    array (
      'route_path' => '/mots/commencant/u/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/u/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 159,
      'notes' => 'maillage interne reel depuis /mots/commencant/u (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    524 => 
    array (
      'route_path' => '/mots/commencant/v/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    525 => 
    array (
      'route_path' => '/mots/commencant/v/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 838,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    526 => 
    array (
      'route_path' => '/mots/commencant/v/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2697,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    527 => 
    array (
      'route_path' => '/mots/commencant/v/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2151,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    528 => 
    array (
      'route_path' => '/mots/commencant/v/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    529 => 
    array (
      'route_path' => '/mots/commencant/v/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 550,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    530 => 
    array (
      'route_path' => '/mots/commencant/v/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2306,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    531 => 
    array (
      'route_path' => '/mots/commencant/v/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 383,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    532 => 
    array (
      'route_path' => '/mots/commencant/v/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    533 => 
    array (
      'route_path' => '/mots/commencant/v/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 87,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    534 => 
    array (
      'route_path' => '/mots/commencant/v/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 27,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    535 => 
    array (
      'route_path' => '/mots/commencant/v/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7247,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    536 => 
    array (
      'route_path' => '/mots/commencant/v/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1613,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    537 => 
    array (
      'route_path' => '/mots/commencant/v/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 8519,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    538 => 
    array (
      'route_path' => '/mots/commencant/v/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7751,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    539 => 
    array (
      'route_path' => '/mots/commencant/v/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 901,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    540 => 
    array (
      'route_path' => '/mots/commencant/v/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 489,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    541 => 
    array (
      'route_path' => '/mots/commencant/v/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 9094,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    542 => 
    array (
      'route_path' => '/mots/commencant/v/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 10000,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    543 => 
    array (
      'route_path' => '/mots/commencant/v/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6745,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    544 => 
    array (
      'route_path' => '/mots/commencant/v/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 4273,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    545 => 
    array (
      'route_path' => '/mots/commencant/v/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 261,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    546 => 
    array (
      'route_path' => '/mots/commencant/v/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 619,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    547 => 
    array (
      'route_path' => '/mots/commencant/v/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/v/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1397,
      'notes' => 'maillage interne reel depuis /mots/commencant/v (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    548 => 
    array (
      'route_path' => '/mots/commencant/w/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 734,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    549 => 
    array (
      'route_path' => '/mots/commencant/w/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 150,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    550 => 
    array (
      'route_path' => '/mots/commencant/w/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 215,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    551 => 
    array (
      'route_path' => '/mots/commencant/w/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 106,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    552 => 
    array (
      'route_path' => '/mots/commencant/w/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 941,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    553 => 
    array (
      'route_path' => '/mots/commencant/w/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 93,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    554 => 
    array (
      'route_path' => '/mots/commencant/w/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 201,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    555 => 
    array (
      'route_path' => '/mots/commencant/w/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 287,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    556 => 
    array (
      'route_path' => '/mots/commencant/w/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 886,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    557 => 
    array (
      'route_path' => '/mots/commencant/w/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 110,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    558 => 
    array (
      'route_path' => '/mots/commencant/w/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 390,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    559 => 
    array (
      'route_path' => '/mots/commencant/w/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 241,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    560 => 
    array (
      'route_path' => '/mots/commencant/w/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 650,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    561 => 
    array (
      'route_path' => '/mots/commencant/w/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 582,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    562 => 
    array (
      'route_path' => '/mots/commencant/w/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 86,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    563 => 
    array (
      'route_path' => '/mots/commencant/w/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 31,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    564 => 
    array (
      'route_path' => '/mots/commencant/w/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 493,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    565 => 
    array (
      'route_path' => '/mots/commencant/w/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 827,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    566 => 
    array (
      'route_path' => '/mots/commencant/w/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 457,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    567 => 
    array (
      'route_path' => '/mots/commencant/w/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 186,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    568 => 
    array (
      'route_path' => '/mots/commencant/w/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 47,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    569 => 
    array (
      'route_path' => '/mots/commencant/w/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 6,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    570 => 
    array (
      'route_path' => '/mots/commencant/w/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 52,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    571 => 
    array (
      'route_path' => '/mots/commencant/w/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/w/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 83,
      'notes' => 'maillage interne reel depuis /mots/commencant/w (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    572 => 
    array (
      'route_path' => '/mots/commencant/x/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 209,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    573 => 
    array (
      'route_path' => '/mots/commencant/x/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 17,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    574 => 
    array (
      'route_path' => '/mots/commencant/x/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 38,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    575 => 
    array (
      'route_path' => '/mots/commencant/x/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 30,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    576 => 
    array (
      'route_path' => '/mots/commencant/x/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 384,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    577 => 
    array (
      'route_path' => '/mots/commencant/x/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 7,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    578 => 
    array (
      'route_path' => '/mots/commencant/x/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 111,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    579 => 
    array (
      'route_path' => '/mots/commencant/x/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 206,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    580 => 
    array (
      'route_path' => '/mots/commencant/x/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 289,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    581 => 
    array (
      'route_path' => '/mots/commencant/x/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 182,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    582 => 
    array (
      'route_path' => '/mots/commencant/x/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 69,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    583 => 
    array (
      'route_path' => '/mots/commencant/x/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 201,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    584 => 
    array (
      'route_path' => '/mots/commencant/x/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 319,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    585 => 
    array (
      'route_path' => '/mots/commencant/x/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 175,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    586 => 
    array (
      'route_path' => '/mots/commencant/x/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 34,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    587 => 
    array (
      'route_path' => '/mots/commencant/x/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 181,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    588 => 
    array (
      'route_path' => '/mots/commencant/x/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 226,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    589 => 
    array (
      'route_path' => '/mots/commencant/x/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 161,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    590 => 
    array (
      'route_path' => '/mots/commencant/x/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 59,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    591 => 
    array (
      'route_path' => '/mots/commencant/x/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 17,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    592 => 
    array (
      'route_path' => '/mots/commencant/x/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/x/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 148,
      'notes' => 'maillage interne reel depuis /mots/commencant/x (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    593 => 
    array (
      'route_path' => '/mots/commencant/y/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 539,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    594 => 
    array (
      'route_path' => '/mots/commencant/y/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 65,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    595 => 
    array (
      'route_path' => '/mots/commencant/y/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 111,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    596 => 
    array (
      'route_path' => '/mots/commencant/y/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 154,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    597 => 
    array (
      'route_path' => '/mots/commencant/y/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 637,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    598 => 
    array (
      'route_path' => '/mots/commencant/y/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 14,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    599 => 
    array (
      'route_path' => '/mots/commencant/y/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 75,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    600 => 
    array (
      'route_path' => '/mots/commencant/y/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 79,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    601 => 
    array (
      'route_path' => '/mots/commencant/y/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 498,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    602 => 
    array (
      'route_path' => '/mots/commencant/y/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 5,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    603 => 
    array (
      'route_path' => '/mots/commencant/y/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 73,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    604 => 
    array (
      'route_path' => '/mots/commencant/y/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 191,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    605 => 
    array (
      'route_path' => '/mots/commencant/y/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 112,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    606 => 
    array (
      'route_path' => '/mots/commencant/y/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 323,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    607 => 
    array (
      'route_path' => '/mots/commencant/y/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 656,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    608 => 
    array (
      'route_path' => '/mots/commencant/y/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 53,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    609 => 
    array (
      'route_path' => '/mots/commencant/y/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 28,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    610 => 
    array (
      'route_path' => '/mots/commencant/y/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 360,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    611 => 
    array (
      'route_path' => '/mots/commencant/y/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 644,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    612 => 
    array (
      'route_path' => '/mots/commencant/y/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 423,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    613 => 
    array (
      'route_path' => '/mots/commencant/y/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 316,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    614 => 
    array (
      'route_path' => '/mots/commencant/y/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 83,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    615 => 
    array (
      'route_path' => '/mots/commencant/y/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 18,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    616 => 
    array (
      'route_path' => '/mots/commencant/y/avec/z',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/y/avec/z',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 81,
      'notes' => 'maillage interne reel depuis /mots/commencant/y (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    617 => 
    array (
      'route_path' => '/mots/commencant/z/avec/a',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/a',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1495,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    618 => 
    array (
      'route_path' => '/mots/commencant/z/avec/b',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/b',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 255,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    619 => 
    array (
      'route_path' => '/mots/commencant/z/avec/c',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/c',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 248,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    620 => 
    array (
      'route_path' => '/mots/commencant/z/avec/d',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/d',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 233,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    621 => 
    array (
      'route_path' => '/mots/commencant/z/avec/e',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/e',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 2129,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    622 => 
    array (
      'route_path' => '/mots/commencant/z/avec/f',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/f',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 73,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    623 => 
    array (
      'route_path' => '/mots/commencant/z/avec/g',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/g',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 513,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    624 => 
    array (
      'route_path' => '/mots/commencant/z/avec/h',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/h',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 321,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    625 => 
    array (
      'route_path' => '/mots/commencant/z/avec/i',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/i',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1795,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    626 => 
    array (
      'route_path' => '/mots/commencant/z/avec/j',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/j',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 12,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    627 => 
    array (
      'route_path' => '/mots/commencant/z/avec/k',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/k',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 91,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    628 => 
    array (
      'route_path' => '/mots/commencant/z/avec/l',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/l',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 518,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    629 => 
    array (
      'route_path' => '/mots/commencant/z/avec/m',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/m',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 464,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    630 => 
    array (
      'route_path' => '/mots/commencant/z/avec/n',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/n',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1219,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    631 => 
    array (
      'route_path' => '/mots/commencant/z/avec/o',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/o',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1427,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    632 => 
    array (
      'route_path' => '/mots/commencant/z/avec/p',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/p',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 457,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    633 => 
    array (
      'route_path' => '/mots/commencant/z/avec/q',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/q',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 135,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    634 => 
    array (
      'route_path' => '/mots/commencant/z/avec/r',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/r',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1047,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    635 => 
    array (
      'route_path' => '/mots/commencant/z/avec/s',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/s',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1519,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    636 => 
    array (
      'route_path' => '/mots/commencant/z/avec/t',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/t',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 1059,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    637 => 
    array (
      'route_path' => '/mots/commencant/z/avec/u',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/u',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 738,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    638 => 
    array (
      'route_path' => '/mots/commencant/z/avec/v',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/v',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 40,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    639 => 
    array (
      'route_path' => '/mots/commencant/z/avec/w',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/w',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 85,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    640 => 
    array (
      'route_path' => '/mots/commencant/z/avec/x',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/x',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 26,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
    641 => 
    array (
      'route_path' => '/mots/commencant/z/avec/y',
      'family' => 'word_list_commencant_with_letter',
      'robots' => 'index,follow',
      'canonical_path' => '/mots/commencant/z/avec/y',
      'sitemap_fragment' => 'commencant-avec-0001',
      'result_count' => 259,
      'notes' => 'maillage interne reel depuis /mots/commencant/z (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type \'start_with\') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts \'start\') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- 0 ligne concernee, verifie exhaustivement sur les 26 prefixes, compte final 646/646.',
    ),
  ),
);
