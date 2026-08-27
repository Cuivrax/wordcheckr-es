<?php

declare(strict_types=1);

/**
 * Propose un fichier de lot pour scripts/apply_seo_batch.php, avec des result_count REELS
 * calcules depuis storage/dictionary_fr.sqlite (Phase 6, docs/08) -- jamais tapes a la main.
 * Ce script ne modifie ni n'ouvre storage/seo_fr.sqlite : il ne fait que PROPOSER, en lecture
 * seule sur le dictionnaire (D-001). L'application reste un acte separe et explicite
 * (scripts/apply_seo_batch.php), qui revalide de toute facon chaque ligne independamment.
 *
 * Usage :
 *     php scripts/propose_seo_batch.php home > batch.php
 *     php scripts/propose_seo_batch.php length > batch.php
 *     php scripts/propose_seo_batch.php commencant > batch.php
 *     php scripts/propose_seo_batch.php terminant > batch.php
 *     php scripts/propose_seo_batch.php combined > batch.php
 *     php scripts/propose_seo_batch.php position > batch.php
 *     php scripts/propose_seo_batch.php avec_single_letter > batch.php
 *     php scripts/propose_seo_batch.php avec_two_letters > batch.php
 *     php scripts/propose_seo_batch.php avec_three_letters > batch.php
 *     php scripts/propose_seo_batch.php combined_with_length > batch.php
 *     php scripts/propose_seo_batch.php combined_with_letter > batch.php
 *     php scripts/propose_seo_batch.php commencant_terminant_multilettres > batch.php
 *     php scripts/propose_seo_batch.php commencant_avec > batch.php
 *     php scripts/propose_seo_batch.php word_admitted --limit=2000 --offset=0 > batch.php
 *
 * Familles volontairement NON proposables par ce script, quelle que soit l'option :
 *   - word_french_not_admitted : "genuinely French, verified manually, useful, searched or
 *     frequent" ne peut pas se deduire d'une requete SQL -- ce script refuse categoriquement
 *     de generer cette famille, meme un seul mot (contrainte dure du projet : jamais en masse,
 *     et jamais automatiquement).
 *   - word_list_contenant / avec / sans / motif / rack : combinaisons infinies, jamais
 *     indexables par defaut -- refuse ici aussi, en plus du refus dans
 *     scripts/apply_seo_batch.php (R4), pour qu'aucun outil de la chaine ne les propose meme
 *     par erreur de frappe sur le nom de famille.
 *
 * 'combined' (D-024 correctif, D-025) genere UNIQUEMENT les combinaisons commencant+terminant
 * SANS longueur (au plus 26x26 = 676, 611 avec au moins un resultat mesures le 2026-08-09) :
 * seul ce sous-ensemble a un maillage interne reel a ce jour (App\Search\
 * LetterCombinedLinksBuilder, depuis /mots/commencant/{X} et /mots/terminant/{Y}, deja
 * indexees D-017). Les combinaisons AVEC longueur (5 193 avec au moins un resultat, jusqu'a
 * 14x26x26 = 9 464) restent volontairement hors de ce script : aucun maillage interne reel ne
 * les couvre encore (portee partielle assumee, D-024) -- les generer ici les rendrait
 * proposables alors qu'elles resteraient des pages orphelines si un lot les appliquait par
 * erreur. A ajouter dans un futur cas distinct seulement apres qu'un maillage reel existe pour
 * elles, jamais en les glissant dans le cas 'combined' existant.
 *
 * 'position' (D-023, D-028) : regularise un lot DEJA APPLIQUE MANUELLEMENT le 2026-08-11 sur
 * storage/seo_fr.sqlite (2 329 lignes, App\Seo\Family::WORD_LIST_POSITION) -- ce n'est PAS une
 * nouvelle decision de volume, seulement la version reproductible d'un lot existant. Source de
 * verite : list_counts, list_type 'length_with_position' (D-023bis, deja precalcule par
 * scripts/build_explore_hub_counts.php), jamais un GROUP BY direct sur `terms` ici. Positions
 * degenerees (1re et derniere lettre, deja collapsees vers commencant/terminant par
 * App\Search\WordListFilters::fromPath(), D-023) et combinaisons a 0 resultat sont exclues.
 * result_count reprend exactement min(compte_reel, 10 000) -- ROW_EXAMINATION_CEILING de
 * App\Search\WordListSolver (D-019), meme plafond que celui reellement servi par la page, pas le
 * compte brut de list_counts au-dela de ce plafond (verifie : 0 divergence sur les 2 329 lignes
 * reelles face a storage/seo_fr.sqlite, voir reports/query-plans/position-batch-
 * reproducibility.md). batch_id et added_at sont FIXES (pas la date du jour comme les autres cas)
 * pour reproduire exactement le lot deja en place -- un futur lot 'position' distinct (jamais
 * genere par ce cas tel quel) devra utiliser son propre batch_id.
 *
 * 'avec_single_letter' (demande produit du 2026-08-17, App\Seo\Family::WORD_LIST_AVEC_SINGLE_LETTER,
 * PALIER 1 de l'ouverture en entonnoir de "avec" a l'indexation) : longueur explicite ET
 * EXACTEMENT une lettre "avec" (occurrence unique, minCount=1), sans aucune autre contrainte --
 * 14 longueurs x 26 lettres = 364 combinaisons au plus. Source de verite : list_counts,
 * list_type 'length_with' (D-022, deja precalcule par scripts/build_explore_hub_counts.php),
 * jamais un parcours direct de `terms` ici -- meme discipline que le cas 'position'. Balayage
 * complet mesure AVANT ce lot (reports/query-plans/avec-length-1-letter-full-sweep.md, agent
 * data-engine) : 364/364 combinaisons a >= 1 resultat, 0/364 au-dessus du budget TTFB. Ce cas
 * genere une PROPOSITION nouvelle (batch_id/added_at dynamiques, comme 'length'/'commencant'/
 * 'terminant' -- PAS une regularisation comme 'position' ci-dessus, ce lot n'a jamais ete
 * applique avant). Ne genere QUE ce palier precis : ne genere jamais /mots/avec/{X} (sans
 * longueur, toujours bloque, Family::WORD_LIST_AVEC), ni /mots/{N}-lettres/avec/{X}/{Y} (2
 * lettres, futur palier 2, jamais mesure), ni le multiensemble general -- garanti PAR
 * CONSTRUCTION par la forme de list_key ("{longueur}:{lettre}", un seul segment lettre) de
 * 'length_with', jamais par un filtrage a cote.
 * CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3, calcul independant de
 * l'agent seo-registry) : ce palier est la RACINE de la hierarchie de l'entonnoir "avec" -- aucun
 * parent plus simple n'existe pour lui, 0 exclusion possible par construction. Confirme par deux
 * methodes de verification independantes (0 divergence) contre les paliers 2 et 3 -- voir le
 * docblock de AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES plus bas dans ce fichier pour le detail
 * complet des quatre controles. Compte inchange a 364/364, aucun changement de code dans ce cas.
 *
 * 'avec_two_letters' (demande produit du 2026-08-17, App\Seo\Family::WORD_LIST_AVEC_TWO_LETTERS,
 * PALIER 2 de l'ouverture en entonnoir de "avec" a l'indexation, mesure/maillage par l'agent
 * data-engine -- reports/query-plans/avec-length-2-letters-full-sweep.md -- classification et
 * application du lot par l'agent seo-registry) : longueur explicite ET EXACTEMENT DEUX lettres
 * "avec" DISTINCTES (occurrence unique chacune, minCount=1 sur chacune) -- 14 longueurs x
 * C(26,2) = 325 paires = 4 550 combinaisons au plus. Source de verite : list_counts, list_type
 * 'length_with_pair' (deja precalcule par scripts/build_explore_hub_counts.php, agent
 * data-engine) -- jamais un parcours direct de `terms` ici, meme discipline que les cas
 * 'position'/'avec_single_letter' ci-dessus. list_key = "{longueur}:{lettre1}:{lettre2}"
 * (lettre1 < lettre2 alphabetiquement, une seule ligne par paire non ordonnee) garantit PAR
 * CONSTRUCTION que seule la forme longueur + EXACTEMENT deux lettres distinctes est generee.
 * Balayage complet mesure AVANT ce lot (agent data-engine, 3 executions independantes) : 4 276
 * combinaisons a >= 1 resultat sur 4 550 possibles (274 a 0 resultat, exclues -- jamais
 * inserees dans list_counts, R5). Re-verifie une 4e fois (et une 5e, deux balayages complets du
 * sous-ensemble longueur 12+13) par l'agent seo-registry avant application -- voir le rapport
 * AFTER de ce lot pour le detail complet du signal de bruit de mesure transitoire trouve,
 * investigue et juge non bloquant (jamais reproduit en verification isolee repetee, EXPLAIN
 * QUERY PLAN inchange). Ce cas genere une PROPOSITION nouvelle (batch_id/added_at dynamiques,
 * comme 'avec_single_letter' ci-dessus) : ce lot n'a jamais ete applique avant. Ne genere QUE ce
 * palier precis : ne genere jamais /mots/{N}-lettres/avec/{X} (1 lettre, palier 1, deja
 * indexe), ni /mots/{N}-lettres/avec/{X}/{Y}/{Z} (3 lettres, futur palier, jamais mesure), ni le
 * multiensemble general -- garanti PAR CONSTRUCTION par la forme de list_key (deux segments
 * lettre exactement) de 'length_with_pair', jamais par un filtrage a cote.
 * CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3) : 4 paires exclues (voir
 * AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES plus bas dans ce fichier pour le detail complet des
 * quatre controles et des deux methodes de verification independantes, 0 divergence) --
 * 4272/4276 pages reellement indexables apres ce correctif, jamais 4276.
 *
 * 'avec_three_letters' (demande produit du 2026-08-18, App\Seo\Family::WORD_LIST_AVEC_THREE_LETTERS,
 * PALIER 3 de l'ouverture en entonnoir de "avec" a l'indexation, mesure/maillage/chiffrage de la
 * surface de pagination par l'agent data-engine -- reports/query-plans/
 * avec-length-3-letters-full-sweep.md -- classification et application du lot par l'agent
 * seo-registry) : longueur explicite ET EXACTEMENT TROIS lettres "avec" DISTINCTES (occurrence
 * unique chacune, minCount=1 sur chacune) -- 14 longueurs x C(26,3) = 2 600 triplets = 36 400
 * combinaisons au plus. Source de verite : list_counts, list_type 'length_with_triple' (deja
 * precalcule par scripts/build_explore_hub_counts.php, agent data-engine) -- jamais un parcours
 * direct de `terms` ici, meme discipline que les cas 'position'/'avec_single_letter'/
 * 'avec_two_letters' ci-dessus. list_key = "{longueur}:{lettre1}:{lettre2}:{lettre3}"
 * (lettre1 < lettre2 < lettre3 alphabetiquement, une seule ligne par triplet non ordonne) garantit
 * PAR CONSTRUCTION que seule la forme longueur + EXACTEMENT trois lettres distinctes est generee.
 * Balayage complet mesure AVANT ce lot (agent data-engine, UN SEUL passage complet, demande
 * produit explicite) : 28 827 combinaisons a >= 1 resultat sur 36 400 possibles (7 573 a 0
 * resultat, exclues -- jamais inserees dans list_counts, R5 ; 1 682 a exactement 1 resultat,
 * conservees, meme consigne produit que les paliers 1/2). Voir le rapport AFTER de ce lot pour le
 * detail complet de l'investigation d'un pic de latence isole (longueur 8, contention transitoire
 * coincidant avec un redemarrage de l'environnement de developpement, sans rapport avec le plan de
 * requete -- EXPLAIN QUERY PLAN inchange, jamais un SCAN complet -- ni avec la production). Ce cas
 * genere une PROPOSITION nouvelle (batch_id/added_at dynamiques, comme 'avec_single_letter'/
 * 'avec_two_letters' ci-dessus) : ce lot n'a jamais ete applique avant. Ne genere QUE ce palier
 * precis : ne genere jamais /mots/{N}-lettres/avec/{X}/{Y} (2 lettres, palier 2, deja indexe), ni
 * /mots/{N}-lettres/avec/{X}/{Y}/{Z}/{W} (4 lettres, futur palier, jamais mesure), ni le
 * multiensemble general -- garanti PAR CONSTRUCTION par la forme de list_key (trois segments
 * lettre exactement) de 'length_with_triple', jamais par un filtrage a cote.
 * CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3) : 660 triplets exclus
 * (426 doublons parent/enfant contre les paliers 1/2, 234 doublons soeurs entre triplets de
 * meme longueur -- voir AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES plus bas dans ce fichier
 * pour le detail complet des quatre controles et des deux methodes de verification
 * independantes, 0 divergence) -- 28167/28827 pages reellement indexables apres ce correctif,
 * jamais 28827. 299/1682 pages a 1 resultat etaient concernees par ce correctif (1383/28167
 * restent a 1 resultat, toujours conservees, meme consigne produit).
 *
 * 'combined_with_length' (demande produit du 2026-08-18, axe 1 -- D-027 App\Search\
 * LengthCombinedLinksBuilder/App\Search\LengthLinksBuilder::build()->byStartEnd, arbitrage de
 * volume de liens tranche par l'agent seo-registry) : variante AVEC longueur de
 * App\Seo\Family::WORD_LIST_COMBINED -- AUCUNE nouvelle classification (deja hors NEVER_SITEMAP
 * depuis D-025, meme famille que la variante sans longueur deja indexee). Source : list_counts,
 * list_type 'length_start_end' (D-027, deja precalcule) -- jamais un parcours direct de `terms`
 * ici, meme discipline que les cas 'position'/'avec_*' ci-dessus. Exclut les 52 paires a
 * contenu strictement duplique avec la variante SANS longueur (D-025, I-1 -- deja gagnante
 * canonique permanente, notes du registre) : une ligne 'length_start_end' "{N}:{X}:{Y}" est
 * dupliquee ssi son compte EGALE celui de la ligne 'start_end' "{X}:{Y}" correspondante,
 * recalcule ici independamment (0 divergence face aux 52 lignes deja annotees dans le
 * registre). Balayage complet mesure AVANT ce lot (reports/query-plans/
 * combined-with-length-full-sweep.md, agent data-engine) : 0/9464 au-dessus du budget TTFB.
 * Maillage interne construit et verifie exhaustivement (reports/query-plans/
 * combined-length-maillage.md) : 5141/5141 depuis /mots/{N}-lettres (deja indexee, D-017) via
 * App\Search\LengthLinksBuilder::build()->byStartEnd -- volume de liens (jusqu'a 477/page)
 * juge acceptable par l'agent seo-registry (repli <details> deja applique, meme mecanisme que
 * byPosition/D-028bis ; le volume de LIENS n'affecte jamais le nombre d'URL sitemap, voir le
 * rapport de ce lot pour l'arbitrage complet).
 *
 * 'combined_with_letter' (demande produit du 2026-08-18, axe 2 -- D-033 App\Search\
 * StartEndWithLinksBuilder, classification et arbitrage par l'agent seo-registry) :
 * App\Seo\Family::WORD_LIST_COMBINED_WITH_LETTER, NOUVELLE classification, DISTINCTE de
 * WORD_LIST_COMBINED (voir app/Seo/Family.php pour la justification complete) -- prefixe ET
 * suffixe chacun d'une seule lettre, SANS longueur, PLUS une lettre "avec" d'occurrence unique.
 * Source : list_counts, list_type 'start_end_with' (D-033, deja precalcule) -- jamais un
 * parcours direct de `terms` ici. Exclut les 1 198 lignes DEGENEREES (lettre avec == debut OU
 * == fin, collapsees vers la page parente par App\Search\WordListFilters::fromPath(), D-032) --
 * meme exclusion que App\Search\StartEndWithLinksBuilder applique deja au maillage.
 * CORRECTIF (2026-08-19, audit seo-technical-auditor consolide sur D-035/D-036, bloquant C-1) :
 * exclut EN PLUS les lignes a contenu strictement duplique avec la page parente SANS lettre
 * "avec" (App\Seo\Family::WORD_LIST_COMBINED, list_type 'start_end') -- meme regle et meme
 * patron que le cas 'combined_with_length' ci-dessus (ligne ~704) : une ligne 'start_end_with'
 * "{X}:{Y}:{Z}" est un doublon de contenu ssi son compte EGALE EXACTEMENT celui de la ligne
 * 'start_end' "{X}:{Y}" correspondante -- preuve concrete verifiee (audit) : F:Q (1 seul mot,
 * FAQ) + avec/a, ou X:O (1 seul mot, XIPHO) + avec/{h,i,p}, listent le meme contenu que leur
 * page parente deja indexee. 227 lignes supplementaires exclues par cette regle (en plus des
 * 1 198 degenerees D-032, jamais un double comptage : verifie que les deux exclusions ne se
 * recouvrent jamais, une ligne degeneree D-032 est deja retiree avant ce controle) -- 9 923
 * pages reellement indexables (10 150 - 227). Recalcule ici independamment de App\Search\
 * StartEndWithLinksBuilder (agent data-engine, meme discipline de double verification que les
 * 52 paires originales de D-025).
 * Balayage complet mesure AVANT ce lot (reports/query-plans/commencant-terminant-avec-full-sweep.md,
 * agent data-engine) : 0/15886 au-dessus du budget TTFB. Maillage interne construit et verifie
 * exhaustivement (reports/query-plans/commencant-terminant-avec-maillage.md) : 10150/10150
 * depuis /mots/commencant/{X}/terminant/{Y} (deja indexees, Family::WORD_LIST_COMBINED,
 * D-024/D-025) via App\Search\StartEndWithLinksBuilder -- couverture de maillage inchangee par ce
 * correctif (les 227 pages retirees n'etaient de toute facon jamais indexables, R3).
 * CORRECTIF I-A (2026-08-19, 2e audit seo-technical-auditor sur D-037, non bloquant) : le
 * controle C-1 ci-dessus ne compare une ligne "avec" qu'A SA PROPRE page parente (verticale,
 * parent/enfant) -- jamais aux AUTRES lettres "avec" DU MEME parent ENTRE ELLES (horizontale,
 * soeurs). Pour un panier parent petit, plusieurs lettres "avec" DISTINCTES (chacune deja
 * differente du debut/de la fin -- donc pas degeneree D-032 -- et chacune deja differente du
 * compte du parent -- donc pas doublon C-1) peuvent neanmoins isoler EXACTEMENT le meme mot ou
 * ensemble de mots entre elles (exemple cite par l'audit, confirme : paire X:M -- XALAM derriere
 * avec/a ET avec/l, memes 1 mot ; XENODOCHIUM derriere 8 lettres distinctes c/d/e/h/i/n/o/u,
 * memes 1 mot). Detecte par la fonction findSiblingContentDuplicates() (voir plus haut dans ce
 * fichier) : UNE requete par paire distincte (panier complet), jamais list_counts seul (un
 * compte identique ne suffit pas a prouver un ensemble identique). Regle de canonicalisation :
 * la lettre "avec" alphabetiquement la PLUS PETITE de chaque groupe reste seule candidate, les
 * autres sont exclues -- meme convention alphabetique que lettre1 < lettre2 pour
 * WORD_LIST_AVEC_TWO_LETTERS/THREE_LETTERS (D-029/D-030/D-031). 283 groupes de doublons soeurs
 * trouves sur 564 paires ayant >= 2 lettres avec en registre (9919 lettres verifiees), 428
 * lignes exclues -- verifie par 3 methodes independantes (panier PHP en cache vs requete SQL
 * directe par lettre, 0 divergence sur 711 verifications croisees ; empreinte SQL
 * GROUP_CONCAT+sha1, 0 divergence sur les 283 groupes) : 9923 - 428 = 9495 apres I-A.
 * CORRECTIF C-2 (2026-08-19, 3e audit seo-technical-auditor consolide de la serie, bloquant) : ni
 * C-1 ni I-A ne comparaient jamais une tranche de CE cas (une lettre "avec", axe 2) a une tranche
 * DE LA FAMILLE SOEUR word_list_combined (une longueur, axe 1, cas 'combined_with_length'
 * ci-dessus, D-027/D-035) du MEME panier commencant+terminant -- les deux familles tranchent le
 * meme panier selon deux axes distincts. Preuve concrete (exemple cite par l'audit, confirme) :
 * paire X:M -- /mots/5-lettres/commencant/x/terminant/m (axe 1) ET
 * /mots/commencant/x/terminant/m/avec/a (axe 2, gagnant I-A du groupe {a,l}) listent EXACTEMENT
 * XALAM ; /mots/11-lettres/commencant/x/terminant/m ET .../avec/c (gagnant I-A de l'autre groupe)
 * listent EXACTEMENT XENODOCHIUM -- les DEUX gagnants I-A de cette paire sont en realite des
 * doublons croises avec l'axe 1. Regle de priorite (tranchee cote produit, coherente D-025 -- la
 * forme la plus simple/generale gagne) : la tranche longueur (axe 1) reste seule candidate, la
 * tranche "avec" (axe 2, ce cas) est exclue. Detecte par findLengthAvecContentCollisions() (voir
 * plus haut dans ce fichier) : UNE requete de panier complet ('normalized','length') par paire
 * ayant a la fois des tranches axe 1 ET axe 2 candidates -- verifie par DEUX methodes
 * independantes (0 divergence sur les 611 paires reelles, pas un echantillon -- l'audit n'avait
 * lui-meme sonde que 9 paires a 5 lettres) : (1) panier partage filtre en PHP pour les deux axes ;
 * (2) requete SQL directe par tranche (GROUP_CONCAT+sha1). 333 collisions trouvees sur 191 paires
 * distinctes : 9495 - 333 = 9162 pages reellement indexables apres C-2. Voir le rapport AFTER de
 * ce correctif pour le detail complet.
 *
 * 'commencant_terminant_multilettres' (demande produit du 2026-08-18, axe 3 -- dimensionnement
 * par l'agent data-engine, reports/query-plans/commencant-terminant-multi-lettres-
 * dimensionnement.md, arbitrage des doublons tranche par l'agent seo-registry) : extension de
 * App\Seo\Family::WORD_LIST_COMMENCANT / WORD_LIST_TERMINANT (deja hors NEVER_SITEMAP, AUCUNE
 * nouvelle classification necessaire -- R4b accepte deja 1 a 15 lettres) aux prefixes/suffixes
 * REELS de longueur 2, 3 et 4 (la longueur 1, 26+26 pages, est deja indexee depuis D-017,
 * jamais re-proposee ici). Source : list_counts, list_type 'prefix2'/'prefix3'/'prefix4'/
 * 'suffix2'/'suffix3'/'suffix4' (deja precalcule par scripts/build_explore_hub_counts.php) pour
 * les niveaux 2 a 4 ; niveau 1 (mono-lettre) lu directement sur `terms` (meme requete que les
 * cas 'commencant'/'terminant' ci-dessus), uniquement pour detecter un eventuel doublon
 * niveau 1 -> 2 (aucun trouve en pratique). Genere les DEUX directions dans un seul lot (families
 * distinctes par ligne, sitemap_fragment distinct par direction) -- le produit a demande un
 * dimensionnement unique pour cet axe (39 539 combinaisons brutes, commencant + terminant).
 * Exclut les 1 982 pages (1 022 prefixe + 960 suffixe) a contenu STRICTEMENT identique a leur
 * page parente immediate (constat de l'agent data-engine, section 7 du rapport de
 * dimensionnement) : arbitrage tranche par l'agent seo-registry -- la page la PLUS COURTE reste
 * index,follow canonique, la PLUS LONGUE de chaque paire reste noindex,follow en permanence (R3,
 * jamais deux pages index,follow pour un contenu identique), meme logique que les 52 paires deja
 * tranchees pour Family::WORD_LIST_COMBINED (D-025). Detection recalculee ici independamment (un
 * parent de longueur N est duplique par son enfant de longueur N+1 ssi il n'a QU'UNE SEULE
 * extension reelle ET que son compte EGALE celui du parent) -- 0 divergence face au constat de
 * data-engine (1 022 + 960 = 1 982). result_count distingue les deux regimes du solveur
 * (WordListSolver::isExactMode()) : commencant seul est TOUJOURS EXACT (jamais tronque, compte
 * brut) ; terminant seul est BORNE (ancre sur reversed, plafonne a ROW_EXAMINATION_CEILING =
 * 10 000, D-019, meme convention que les cas 'position'/'avec_*'/'combined_with_length'
 * ci-dessus). Balayage complet mesure AVANT ce lot (agent data-engine, deux balayages
 * independants) : 0/39539 au 1er passage, 2/39539 au 2e passage (investigues, non reproduits en
 * isolation, contention transitoire deja documentee pour ce projet, D-030/D-031). Maillage
 * interne construit et verifie exhaustivement dans les deux sens (App\Search\
 * PrefixExtensionLinksBuilder/SuffixExtensionLinksBuilder, entonnoir hierarchique depuis les
 * 26+26 pages mono-lettre deja indexees) : 39539/39539 (100%).
 *
 * 'commencant_avec' (demande produit du 2026-08-18, dernier des quatre axes commencant/
 * terminant/avec travailles ce jour, App\Seo\Family::WORD_LIST_COMMENCANT_WITH_LETTER, NOUVELLE
 * classification, DISTINCTE a la fois de WORD_LIST_COMMENCANT (prefixe seul) et de
 * WORD_LIST_COMBINED_WITH_LETTER (prefixe+terminant+avec, forme de route syntaxiquement
 * differente) -- mesure/maillage par l'agent data-engine, reports/query-plans/
 * commencant-avec-no-length-full-sweep.md et reports/query-plans/commencant-avec-maillage.md,
 * classification et application du lot par l'agent seo-registry) : prefixe d'une seule lettre,
 * SANS longueur, SANS terminant, PLUS une lettre "avec" d'occurrence unique (minCount=1) -- 26
 * prefixes reels x 26 lettres = 676 combinaisons brutes au maximum. Source : list_counts,
 * list_type 'start_with' (deja precalcule par scripts/build_explore_hub_counts.php, agent
 * data-engine) -- jamais un parcours direct de `terms` ici, meme discipline que les cas
 * 'combined_with_letter'/'avec_*' ci-dessus. Contrairement a 'combined_with_letter' (D-033), les
 * 26 combinaisons DEGENEREES (lettre avec == prefixe, D-032) sont deja exclues AU PRECALCUL
 * (jamais inserees dans list_counts) : aucune exclusion supplementaire necessaire dans ce cas,
 * list_key = "{prefixe}:{lettre}" garantit PAR CONSTRUCTION que seule la forme prefixe + une
 * seule lettre "avec" distincte du prefixe est generee.
 * CORRECTIF (2026-08-19, audit seo-technical-auditor consolide sur D-035/D-036, bloquant C-1,
 * meme regle que 'combined_with_letter' ci-dessus) : exclut EN PLUS toute ligne 'start_with'
 * "{X}:{Y}" a contenu strictement duplique avec sa page parente SANS lettre "avec" (App\Seo\
 * Family::WORD_LIST_COMMENCANT, list_type 'start') -- ssi son compte EGALE EXACTEMENT celui de
 * la ligne 'start' "{X}" correspondante. Recalcule ici independamment : 0/650 lignes concernees
 * (verifie exhaustivement -- aucune lettre de prefixe reel de ce projet n'a la totalite de ses
 * mots contenant systematiquement une meme lettre distincte) -- le compte de ce cas reste donc
 * inchange a 646, cette correction n'a retire aucune ligne ici, seulement ajoute le controle
 * manquant pour que ce cas suive la meme discipline que 'combined_with_length'/
 * 'combined_with_letter' plutot que d'en dependre implicitement.
 * Balayage complet mesure AVANT ce lot
 * (reports/query-plans/commencant-avec-no-length-full-sweep.md, agent data-engine) : 0/650
 * au-dessus du budget TTFB (ancrage prefixe, jamais un parcours complet). Maillage interne
 * construit et verifie exhaustivement (reports/query-plans/commencant-avec-maillage.md) :
 * 646/646 depuis /mots/commencant/{X} (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via
 * App\Search\PrefixAvecLinksBuilder. 4/650 a 0 resultat (exclues), 1/650 a exactement 1 resultat
 * (conservee, consigne produit explicite) -- 646 pages reellement indexables.
 * CORRECTIF I-A (2026-08-19, 2e audit seo-technical-auditor sur D-037, non bloquant, meme regle
 * et meme fonction findSiblingContentDuplicates() que 'combined_with_letter' ci-dessus) :
 * recalcule sur les 26 prefixes (basket complet par prefixe, jusqu'a 219 076 mots pour le
 * prefixe R) -- 0 groupe de doublons soeurs trouve sur les 26 groupes verifies (646 lettres avec
 * au total), confirme par 3 methodes independantes. Compte inchange a 646/646 -- contrairement a
 * 'combined_with_letter' (paniers plus petits, paire commencant+terminant), les paniers par
 * PREFIXE SEUL sont en moyenne bien plus grands (des dizaines a des centaines de milliers de
 * mots), ce qui rend statistiquement bien plus rare que deux lettres distinctes induisent
 * EXACTEMENT le meme sous-ensemble -- resultat negatif verifie, pas simplement suppose.
 *
 * Chaque ligne proposee porte 'notes' commencant par "A COMPLETER" : ce script ne PREND
 * aucune decision de maillage interne reel, il expose juste la structure -- la note doit etre
 * relue et completee par un humain avant tout scripts/apply_seo_batch.php.
 * Exception : les cas 'position', 'avec_single_letter', 'avec_two_letters', 'avec_three_letters',
 * 'combined_with_length', 'combined_with_letter', 'commencant_terminant_multilettres' et
 * 'commencant_avec' ci-dessus portent des notes deja completees (maillage interne reel deja
 * verifie exhaustivement par balayage complet avant proposition, pas une note generique a
 * relire).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/propose_seo_batch.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

/**
 * D-041 -- true si $routePath doit etre exclu de la famille $family par la REGLE DE PRIORITE
 * GLOBALE (resolveDuplicateWinner(), scripts/lib/seo_duplicate_priority.php) contre un doublon de
 * contenu detecte entre familles combinatoires jamais comparees avant (scripts/
 * check_combinatorial_duplicates.php, 2026-08-21 : 1 656 groupes, 2 089 pages en exces). Verifie
 * contre la constante D041_EXCLUDED_ROUTE_PATHS (plus bas dans ce fichier) -- computee hors ligne
 * en appliquant resolveDuplicateWinner() a chacun des 1 656 groupes reels trouves (voir le rapport
 * AFTER de cette tache pour la methode complete et la reproductibilite). Applique a TOUS les cas
 * combinatoires concernes ci-dessous, y compris ceux a 0 exclusion actuellement (ex. 'commencant'),
 * par coherence structurelle -- jamais suppose sans exclusion juste parce que le lot actuel n'en a
 * pas trouve.
 */
function isD041Excluded(string $family, string $routePath): bool
{
    return isset(D041_EXCLUDED_ROUTE_PATHS[$family][$routePath]);
}

// Le cas 'avec_three_letters' (palier 3, 28 827 lignes) epuise le memory_limit CLI par defaut
// (128 Mo) lors du var_export() final -- meme cause structurelle que celle deja rencontree et
// corrigee dans scripts/build_sitemaps.php (D-017 amendement) pour la LECTURE du registre, mais
// pas la meme solution possible ici : ce script doit renvoyer UN SEUL tableau PHP litteral
// (contrat de scripts/apply_seo_batch.php, require $batchPath), un flux ligne par ligne
// romprait ce contrat. Relever le plafond ICI, dans un script CLI hors ligne qui ne touche
// jamais public/ ni le runtime (D-007), reste sans consequence sur le budget de performance
// runtime -- contrairement au cas de build_sitemaps.php, augmenter cette limite ne cache aucun
// defaut structurel, le cout est simplement proportionnel au volume de ce lot precis.
ini_set('memory_limit', '512M');

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once __DIR__ . '/lib/seo_duplicate_priority.php';

use App\Seo\Family;

/**
 * Detecte les groupes de lettres "avec" SOEURES (meme parent commencant[+terminant]) qui
 * induisent EXACTEMENT le meme sous-ensemble de mots -- correctif I-A (2e audit
 * seo-technical-auditor sur D-037, 2026-08-18) : le controle de doublon parent/enfant deja en
 * place (D-037) ne compare une ligne "avec" qu'A SA PROPRE page parente (commencant+terminant ou
 * commencant seul), jamais aux AUTRES lettres "avec" du meme parent entre elles. Deux lettres
 * distinctes qui isolent le meme mot (ou le meme ensemble de mots, ex. paire X:M citee par
 * l'audit -- XALAM derriere avec/a ET avec/l, XENODOCHIUM derriere 8 lettres differentes)
 * publieraient plusieurs pages 'index,follow' pour un contenu identique, sans canonical
 * designant un gagnant -- meme famille de defaut que le doublon parent/enfant (D-037), mais
 * HORIZONTAL (entre lignes soeurs) plutot que VERTICAL (parent/enfant).
 *
 * Necessite le contenu REEL (pas seulement un compte) : deux lettres peuvent avoir exactement le
 * meme compte sans designer le meme ensemble de mots -- exige donc un parcours direct de `terms`
 * (deroge ICI, deliberement et uniquement pour cette detection, a la discipline "jamais un
 * parcours direct de terms" suivie ailleurs dans ce fichier pour les controles fondes sur un
 * simple compte list_counts). Cout maitrise : UNE requete par groupe (le panier COMPLET du
 * parent, ancree sur l'index compose deja mesure et valide, D-025bis pour l'axe 2 /
 * idx_terms_length_normalized-equivalent prefixe pour l'axe 4), jamais une requete par ligne --
 * proportionnel au nombre de groupes distincts (564 paires non degenerees pour
 * combined_with_letter, 26 prefixes pour commencant_avec au 2026-08-19), jamais au nombre de
 * lignes du lot. Verifie par deux methodes independantes supplementaires avant integration ici
 * (filtre PHP sur panier en cache vs requete SQL directe par lettre, 0 divergence sur 711
 * verifications croisees ; troisieme methode par empreinte SQL GROUP_CONCAT+sha1, 0 divergence)
 * -- voir le rapport AFTER de ce correctif.
 *
 * Regle de canonicalisation : la lettre "avec" alphabetiquement la PLUS PETITE du groupe reste
 * candidate a l'indexation, les autres sont exclues -- meme convention alphabetique deja
 * appliquee partout ailleurs dans ce projet pour ordonner des lettres interchangeables
 * (lettre1 < lettre2 pour avec_two_letters/avec_three_letters, D-029/D-030/D-031).
 *
 * @param array<string, list<string>> $lettersByGroup cle de groupe (ex. "a:b" ou "a") => liste
 *     des lettres "avec" actuellement candidates pour ce groupe (deja filtrees des degenerees
 *     D-032 et des doublons parent/enfant D-037 par l'appelant)
 * @param callable(string): PDOStatement $basketQuery renvoie, pour une cle de groupe donnee, le
 *     panier COMPLET des mots du parent (colonne 'normalized', triee), execute() deja appele
 * @return array{
 *     excluded: array<string, true>,
 *     groups: list<array{group: string, letters: list<string>, winner: string}>,
 * } excluded est indexe par "{groupKey}:{letter}" (minuscule, meme convention que route_path)
 */
function findSiblingContentDuplicates(array $lettersByGroup, callable $basketQuery): array
{
    $excluded = [];
    $duplicateGroups = [];

    foreach ($lettersByGroup as $groupKey => $letters) {
        if (count($letters) < 2) {
            continue;
        }

        $basket = array_column($basketQuery($groupKey)->fetchAll(), 'normalized');
        $byFingerprint = [];

        foreach ($letters as $letter) {
            $letterUpper = strtoupper($letter);
            $subset = [];

            foreach ($basket as $word) {
                if (str_contains($word, $letterUpper)) {
                    $subset[] = $word;
                }
            }

            // Empreinte = liste exacte des mots (deja triee, le panier est ORDER BY normalized
            // et le filtrage preserve l'ordre) -- deux lettres avec la meme empreinte designent
            // EXACTEMENT le meme ensemble de mots, pas seulement le meme compte.
            $byFingerprint[implode('|', $subset)][] = $letter;
        }

        foreach ($byFingerprint as $siblingLetters) {
            if (count($siblingLetters) < 2) {
                continue;
            }

            sort($siblingLetters);
            $winner = $siblingLetters[0];

            foreach (array_slice($siblingLetters, 1) as $loser) {
                $excluded["{$groupKey}:{$loser}"] = true;
            }

            $duplicateGroups[] = ['group' => $groupKey, 'letters' => $siblingLetters, 'winner' => $winner];
        }
    }

    return ['excluded' => $excluded, 'groups' => $duplicateGroups];
}

/**
 * Detecte les collisions de CONTENU entre une tranche AXE 1 (longueur, App\Seo\Family::
 * WORD_LIST_COMBINED variante AVEC longueur, D-027/D-035, cas 'combined_with_length' ci-dessus)
 * et une tranche AXE 2 (lettre "avec", App\Seo\Family::WORD_LIST_COMBINED_WITH_LETTER, ce cas,
 * D-033/D-035/D-037/D-038) du MEME panier commencant+terminant -- correctif C-2 (3e audit
 * seo-technical-auditor consolide de la serie, 2026-08-19). Meme famille de defaut que C-1
 * (doublon VERTICAL avec le parent sans lettre avec, D-037) et I-A (doublon HORIZONTAL entre
 * lettres avec SOEURS, D-038), mais CROISE entre deux familles differentes qui decoupent le MEME
 * panier selon deux axes distincts (longueur vs lettre) plutot qu'entre deux lignes de la meme
 * famille -- ni C-1 ni I-A ne pouvaient detecter ce cas, aucun des deux ne compare jamais a une
 * tranche de la famille SOEUR word_list_combined (variante avec longueur).
 *
 * Preuve concrete (exemple cite par l'audit, confirme) : paire X:M, 2 mots au total (XALAM,
 * 5 lettres, et XENODOCHIUM, 11 lettres) -- /mots/5-lettres/commencant/x/terminant/m (axe 1,
 * deja indexee, D-027/D-035) ET /mots/commencant/x/terminant/m/avec/a (axe 2, gagnant du groupe
 * sibling {a,l} apres I-A) listent EXACTEMENT le meme contenu ({XALAM}) ; meme chose pour la
 * tranche 11-lettres et avec/c (gagnant du groupe sibling a 8 lettres) : {XENODOCHIUM} des deux
 * cotes. Aucune des deux pages n'a de canonical designant l'autre comme gagnante avant ce
 * correctif.
 *
 * Regle de priorite (tranchee cote produit, coherente avec D-025 -- la forme la plus
 * simple/generale gagne) : en cas de collision, la tranche LONGUEUR (axe 1) reste candidate a
 * l'indexation, la tranche "avec" (axe 2) est exclue -- jamais l'inverse.
 *
 * Necessite le contenu REEL du panier (un compte identique ne suffit pas a prouver un ensemble
 * identique), exactement comme findSiblingContentDuplicates() ci-dessus -- UNE requete par paire
 * distincte ayant a la fois au moins une tranche axe 1 ET au moins une lettre axe 2 candidates,
 * jamais par ligne.
 *
 * Verifie par DEUX methodes independantes avant integration ici (0 divergence sur les 611 paires
 * reelles, pas un echantillon -- l'audit n'avait lui-meme sonde que 9 paires a 5 lettres) :
 * 1. panier COMPLET ('normalized','length') recupere UNE FOIS par paire, filtre en PHP pour les
 *    deux axes (fonction ci-dessous)
 * 2. requete SQL DIRECTE par tranche, structurellement independante (GROUP_CONCAT ordonne +
 *    sha1, jamais de panier partage entre tranches ni entre paires)
 * 333 collisions trouvees sur 191 paires distinctes -- 9 162 pages reellement indexables
 * (9 495 - 333).
 *
 * @param array<string, list<int>> $lengthsByPair cle "{debut}:{fin}" => longueurs AXE 1
 *     actuellement candidates pour cette paire (deja filtrees des 52 doublons D-025, meme regle
 *     que le cas 'combined_with_length' ci-dessus)
 * @param array<string, list<string>> $lettersByPair cle "{debut}:{fin}" => lettres AXE 2
 *     actuellement candidates pour cette paire (deja filtrees D-032 + C-1 + I-A)
 * @param callable(string): PDOStatement $basketQuery renvoie, pour une cle de paire donnee, le
 *     panier COMPLET ('normalized','length') du parent, execute() deja appele, ORDER BY normalized
 * @return array{
 *     excluded: array<string, true>,
 *     matches: list<array{pair: string, length: int, letter: string, word_count: int}>,
 * } excluded est indexe par "{pairKey}:{letter}" (meme convention que findSiblingContentDuplicates())
 */
function findLengthAvecContentCollisions(array $lengthsByPair, array $lettersByPair, callable $basketQuery): array
{
    $excluded = [];
    $matches = [];

    foreach ($lettersByPair as $pairKey => $letters) {
        $lengths = $lengthsByPair[$pairKey] ?? [];

        if ($lengths === [] || $letters === []) {
            continue;
        }

        $basket = $basketQuery($pairKey)->fetchAll();

        $byLength = [];

        foreach ($basket as $row) {
            $byLength[(int) $row['length']][] = $row['normalized'];
        }

        $lengthFingerprints = [];

        foreach ($lengths as $length) {
            $words = $byLength[$length] ?? [];
            // Deja triees par normalized (la requete du panier est ORDER BY normalized, et le
            // regroupement par longueur preserve l'ordre) -- meme convention que
            // findSiblingContentDuplicates() ci-dessus : deux tranches avec la meme empreinte
            // designent EXACTEMENT le meme ensemble de mots, pas seulement le meme compte.
            $lengthFingerprints[implode('|', $words)] = $length;
        }

        foreach ($letters as $letter) {
            $letterUpper = strtoupper($letter);
            $subset = [];

            foreach ($basket as $row) {
                if (str_contains($row['normalized'], $letterUpper)) {
                    $subset[] = $row['normalized'];
                }
            }

            $fingerprint = implode('|', $subset);

            if (isset($lengthFingerprints[$fingerprint])) {
                $excluded["{$pairKey}:{$letter}"] = true;
                $matches[] = [
                    'pair' => $pairKey,
                    'length' => $lengthFingerprints[$fingerprint],
                    'letter' => $letter,
                    'word_count' => count($subset),
                ];
            }
        }
    }

    return ['excluded' => $excluded, 'matches' => $matches];
}

/**
 * Exclusions VERIFIEES (4e audit seo-technical-auditor, point bloquant C-3, calcul independant
 * de l'agent seo-registry le 2026-08-20) : doublons de contenu ENTRE LES PALIERS de l'entonnoir
 * "avec" (D-029/D-030/D-031) -- aucun controle parent/enfant (comme D-037), soeurs (comme
 * D-038) ni croise (comme D-039) n'existait jamais entre avec_single_letter/avec_two_letters/
 * avec_three_letters avant cette passe, alors que ces trois familles partagent EXACTEMENT le
 * meme mecanisme de defaut deja corrige ailleurs pour WORD_LIST_COMBINED_WITH_LETTER. Preuve sur
 * pieces citee par l'audit, confirmee ici par un calcul complet (pas un echantillon) :
 * /mots/10-lettres/avec/w/x (1 mot) a le MEME contenu que chacune de ses 6 variantes a trois
 * lettres (avec/{a,e,n,o,s,t}/w/x) ; /mots/2-lettres/avec/w a le meme contenu que
 * /mots/2-lettres/avec/u/w.
 *
 * Methode IDENTIQUE en discipline a D-037/D-038/D-039 : empreinte de contenu (ensemble EXACT de
 * mots, jamais un simple compte), calculee par DEUX methodes structurellement independantes,
 * 0 divergence sur la totalite des 4 276 + 28 827 = 33 103 combinaisons candidates reelles
 * (pas un echantillon) :
 * 1. decomposition par mot, UN SEUL panier COMPLET par LONGUEUR (14 requetes sur `terms`,
 *    jamais une requete par combinaison) -- chaque mot distribue ses lettres DISTINCTES vers
 *    ses empreintes simple/paire/triplet, cout borne au nombre reel de lettres distinctes par
 *    mot (<= longueur <= 15), jamais a la taille de l'alphabet (26/325/2600) ;
 * 2. requete SQL DIRECTE par combinaison candidate (GROUP_CONCAT ordonne + sha1, ancree sur
 *    length = ?, 33 467 requetes au total y compris les 364 simples et les 2 329 combinaisons
 *    position du controle secondaire ci-dessous).
 *
 * Regle de priorite (coherente D-025/D-037/D-039 -- la forme la plus simple/generale gagne),
 * quatre controles distincts :
 * A. PARENT/ENFANT palier1 <-> palier2 : une paire dont le contenu egale EXACTEMENT celui d'un
 *    de ses deux parents a une lettre (palier1, deja indexe D-029) est exclue -- 4 paires
 *    (2-lettres/avec/u/w == avec/w ; 2-lettres/avec/a/z == avec/z ; 14 et 15-lettres/avec/q/u ==
 *    avec/q -- Q et U quasi-indissociables en francais a ces longueurs).
 * B. PARENT/ENFANT (palier1 et palier2) <-> palier3 : un triplet dont le contenu egale
 *    EXACTEMENT celui d'un de ses parents (3 simples OU 3 paires possibles, palier1/2, deja
 *    indexes D-029/D-030) est exclu -- 426 triplets, TOUS via une paire (0 via un simple
 *    directement : la paire est systematiquement le parent le plus proche qui capture deja le
 *    doublon en premier).
 * C. SOEURS palier2 (apres A) : deux paires distinctes de meme longueur au contenu strictement
 *    identique -- 0 groupe trouve (paniers palier2 en moyenne trop grands pour qu'une
 *    coincidence exacte entre deux paires distinctes se produise, resultat NEGATIF verifie, pas
 *    simplement suppose).
 * D. SOEURS palier3 (apres B) : deux triplets distincts ou plus, meme longueur, contenu
 *    strictement identique -- 189 groupes, 234 triplets perdants (le triplet alphabetiquement
 *    le plus petit du groupe reste seul candidat, meme convention que lettre1 < lettre2 <
 *    lettre3 deja appliquee a la construction de list_key, D-029/D-030/D-031).
 *
 * CONTROLE SECONDAIRE (demande explicite, non bloquant) : Family::WORD_LIST_POSITION (D-023/
 * D-028, deja applique, 2 329 URL) compare a Family::WORD_LIST_AVEC_SINGLE_LETTER (palier 1) sur
 * les 2 329 combinaisons reelles, par les DEUX methodes ci-dessus -- 0/2 329 duplicata trouve.
 * Resultat CONCLUANT et negatif (pas une absence de verification, une exhaustivite complete
 * confirmee deux fois) : une page position/P/X n'egale jamais avec/X en contenu reel sur ce
 * perimetre. Aucune exclusion necessaire pour le cas 'position' de ce script, aucun changement
 * apporte a ce cas.
 *
 * Total : 4 + 426 + 234 = 664 lignes retirees (4 palier2, 660 palier3) -- 4 276 -> 4 272
 * (palier2, cas 'avec_two_letters' ci-dessous), 28 827 -> 28 167 (palier3, cas
 * 'avec_three_letters' ci-dessous). Palier 1 (avec_single_letter, 364 URL, cas
 * 'avec_single_letter' ci-dessous) est la RACINE de la hierarchie : aucun parent plus simple
 * n'existe pour lui, 0 exclusion possible par construction, confirme par les deux methodes
 * ci-dessus (aucun changement necessaire dans ce cas).
 *
 * IMPORTANT -- etat au moment ou ce commentaire est ecrit : ces exclusions sont calculees et
 * integrees ICI (generateur de LOT, jamais storage/seo_fr.sqlite ni les sitemaps reels -- ce
 * script ne fait que PROPOSER, voir l'entete du fichier). Calcul mene de facon INDEPENDANTE par
 * l'agent seo-registry, en cours de comparaison avec un calcul independant equivalent mene en
 * parallele par l'agent data-engine directement sur storage/dictionary_fr.sqlite -- meme
 * protocole que D-037/D-038/D-039. L'application reelle (scripts/apply_seo_batch.php sur le lot
 * regenere, puis reconstruction des sitemaps) reste distincte et posterieure, apres comparaison
 * des deux calculs et coordination explicite (jamais automatique depuis ce script).
 *
 * @var array<string, true> cle "{longueur}:{lettre1}:{lettre2}" (lettres MINUSCULES, ordre
 *     alphabetique -- meme convention que route_path)
 */
const AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES = [
    '14:q:u' => true, '15:q:u' => true, '2:a:z' => true, '2:u:w' => true,
];

/**
 * Voir le docblock de AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES juste au-dessus pour la
 * justification complete (controles B et D). 660 cles au format
 * "{longueur}:{lettre1}:{lettre2}:{lettre3}" (lettres MINUSCULES, ordre alphabetique).
 *
 * @var array<string, true>
 */
const AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES = [
    '10:a:j:w' => true, '10:a:w:x' => true, '10:b:j:w' => true, '10:d:q:u' => true, '10:e:j:w' => true,
    '10:e:q:w' => true, '10:e:v:w' => true, '10:e:w:x' => true, '10:e:w:z' => true, '10:e:x:z' => true,
    '10:f:q:u' => true, '10:g:j:y' => true, '10:g:q:u' => true, '10:g:w:y' => true, '10:i:v:w' => true,
    '10:j:l:w' => true, '10:j:l:y' => true, '10:j:n:w' => true, '10:j:o:w' => true, '10:j:q:u' => true,
    '10:j:r:w' => true, '10:j:u:w' => true, '10:k:q:u' => true, '10:l:q:u' => true, '10:m:w:z' => true,
    '10:n:w:x' => true, '10:o:w:x' => true, '10:p:q:u' => true, '10:q:s:w' => true, '10:q:u:v' => true,
    '10:q:u:w' => true, '10:q:u:x' => true, '10:q:u:y' => true, '10:s:w:x' => true, '10:t:w:x' => true,
    '11:c:q:w' => true, '11:d:q:u' => true, '11:e:q:w' => true, '11:e:v:w' => true, '11:e:w:x' => true,
    '11:f:q:u' => true, '11:g:j:y' => true, '11:g:k:w' => true, '11:g:q:u' => true, '11:h:q:u' => true,
    '11:h:w:x' => true, '11:i:q:w' => true, '11:i:v:w' => true, '11:i:w:x' => true, '11:j:m:x' => true,
    '11:j:q:u' => true, '11:j:x:y' => true, '11:k:q:u' => true, '11:l:q:u' => true, '11:l:w:x' => true,
    '11:m:w:x' => true, '11:n:q:w' => true, '11:o:q:w' => true, '11:o:w:x' => true, '11:q:r:w' => true,
    '11:q:s:w' => true, '11:q:u:v' => true, '11:q:u:w' => true, '11:q:u:x' => true, '11:q:u:y' => true,
    '11:q:v:w' => true, '11:s:w:x' => true, '12:c:k:x' => true, '12:d:q:u' => true, '12:e:j:k' => true,
    '12:e:k:x' => true, '12:e:q:w' => true, '12:e:v:w' => true, '12:e:w:x' => true, '12:e:x:z' => true,
    '12:f:j:x' => true, '12:f:q:u' => true, '12:g:j:y' => true, '12:g:q:u' => true, '12:h:q:u' => true,
    '12:i:q:w' => true, '12:i:v:w' => true, '12:i:w:x' => true, '12:j:q:u' => true, '12:j:u:x' => true,
    '12:k:l:x' => true, '12:k:n:x' => true, '12:k:o:x' => true, '12:k:p:w' => true, '12:k:q:u' => true,
    '12:k:u:x' => true, '12:l:q:u' => true, '12:l:w:x' => true, '12:m:w:x' => true, '12:o:w:x' => true,
    '12:p:w:y' => true, '12:q:u:v' => true, '12:q:u:w' => true, '12:q:u:x' => true, '12:q:u:y' => true,
    '12:r:w:x' => true, '12:s:w:x' => true, '13:a:j:x' => true, '13:a:k:x' => true, '13:b:e:w' => true,
    '13:b:q:u' => true, '13:c:q:u' => true, '13:d:q:u' => true, '13:e:g:j' => true, '13:e:j:k' => true,
    '13:e:j:y' => true, '13:e:j:z' => true, '13:e:k:x' => true, '13:e:m:w' => true, '13:e:p:w' => true,
    '13:e:q:w' => true, '13:e:q:x' => true, '13:e:q:z' => true, '13:e:v:w' => true, '13:e:w:x' => true,
    '13:e:w:y' => true, '13:e:w:z' => true, '13:e:x:z' => true, '13:f:k:v' => true, '13:f:q:u' => true,
    '13:g:j:y' => true, '13:g:q:u' => true, '13:h:q:u' => true, '13:i:q:w' => true, '13:i:v:w' => true,
    '13:i:w:x' => true, '13:i:w:y' => true, '13:j:k:l' => true, '13:j:k:u' => true, '13:j:m:x' => true,
    '13:j:p:y' => true, '13:j:q:u' => true, '13:j:q:x' => true, '13:j:s:y' => true, '13:k:l:x' => true,
    '13:k:n:x' => true, '13:k:o:x' => true, '13:k:q:u' => true, '13:l:q:u' => true, '13:l:w:x' => true,
    '13:m:q:u' => true, '13:m:w:x' => true, '13:o:w:x' => true, '13:p:q:u' => true, '13:p:s:w' => true,
    '13:p:w:z' => true, '13:q:s:w' => true, '13:q:u:v' => true, '13:q:u:w' => true, '13:q:u:x' => true,
    '13:q:u:y' => true, '13:q:u:z' => true, '13:s:w:x' => true, '13:u:w:x' => true, '13:w:y:z' => true,
    '14:a:j:k' => true, '14:a:j:x' => true, '14:a:k:x' => true, '14:a:q:u' => true, '14:b:q:u' => true,
    '14:c:j:k' => true, '14:c:q:u' => true, '14:d:q:u' => true, '14:e:g:w' => true, '14:e:h:j' => true,
    '14:e:j:v' => true, '14:e:k:q' => true, '14:e:k:w' => true, '14:e:k:z' => true, '14:e:p:w' => true,
    '14:e:q:u' => true, '14:e:q:w' => true, '14:e:q:z' => true, '14:e:v:w' => true, '14:e:v:x' => true,
    '14:e:v:z' => true, '14:e:w:x' => true, '14:e:w:y' => true, '14:e:w:z' => true, '14:e:x:z' => true,
    '14:f:k:v' => true, '14:f:q:u' => true, '14:g:q:u' => true, '14:h:j:p' => true, '14:h:j:y' => true,
    '14:h:q:u' => true, '14:i:j:q' => true, '14:i:j:y' => true, '14:i:k:w' => true, '14:i:p:w' => true,
    '14:i:q:u' => true, '14:i:v:w' => true, '14:i:w:x' => true, '14:j:k:m' => true, '14:j:q:u' => true,
    '14:k:l:x' => true, '14:k:n:x' => true, '14:k:o:x' => true, '14:k:p:v' => true, '14:k:p:w' => true,
    '14:k:q:u' => true, '14:k:r:x' => true, '14:k:w:y' => true, '14:l:q:u' => true, '14:l:w:x' => true,
    '14:m:q:u' => true, '14:n:q:u' => true, '14:o:q:u' => true, '14:o:w:x' => true, '14:p:q:u' => true,
    '14:p:u:w' => true, '14:p:w:y' => true, '14:p:w:z' => true, '14:q:r:u' => true, '14:q:s:u' => true,
    '14:q:t:u' => true, '14:q:u:v' => true, '14:q:u:w' => true, '14:q:u:x' => true, '14:q:u:y' => true,
    '14:q:u:z' => true, '14:r:w:x' => true, '14:s:w:x' => true, '14:u:w:x' => true, '14:u:w:y' => true,
    '14:w:y:z' => true, '15:a:j:k' => true, '15:a:j:x' => true, '15:a:k:x' => true, '15:a:q:u' => true,
    '15:b:e:j' => true, '15:b:e:w' => true, '15:b:i:w' => true, '15:b:o:w' => true, '15:b:q:u' => true,
    '15:b:w:x' => true, '15:c:j:k' => true, '15:c:j:y' => true, '15:c:q:u' => true, '15:d:e:k' => true,
    '15:d:j:y' => true, '15:d:q:u' => true, '15:e:f:w' => true, '15:e:f:x' => true, '15:e:f:z' => true,
    '15:e:g:k' => true, '15:e:g:w' => true, '15:e:g:x' => true, '15:e:h:j' => true, '15:e:j:k' => true,
    '15:e:j:l' => true, '15:e:j:m' => true, '15:e:j:q' => true, '15:e:j:v' => true, '15:e:j:y' => true,
    '15:e:j:z' => true, '15:e:k:p' => true, '15:e:k:q' => true, '15:e:k:w' => true, '15:e:k:x' => true,
    '15:e:k:z' => true, '15:e:m:w' => true, '15:e:p:w' => true, '15:e:q:u' => true, '15:e:q:w' => true,
    '15:e:q:y' => true, '15:e:q:z' => true, '15:e:r:w' => true, '15:e:t:w' => true, '15:e:u:w' => true,
    '15:e:v:w' => true, '15:e:v:x' => true, '15:e:w:x' => true, '15:e:w:y' => true, '15:e:w:z' => true,
    '15:e:x:z' => true, '15:f:j:s' => true, '15:f:k:v' => true, '15:f:p:w' => true, '15:f:q:u' => true,
    '15:g:j:p' => true, '15:g:q:u' => true, '15:g:v:w' => true, '15:h:i:j' => true, '15:h:j:p' => true,
    '15:h:j:y' => true, '15:h:q:u' => true, '15:h:v:w' => true, '15:i:j:q' => true, '15:i:j:v' => true,
    '15:i:j:y' => true, '15:i:j:z' => true, '15:i:k:w' => true, '15:i:m:w' => true, '15:i:p:w' => true,
    '15:i:q:u' => true, '15:i:v:w' => true, '15:i:w:x' => true, '15:i:w:z' => true, '15:j:k:m' => true,
    '15:j:o:y' => true, '15:j:q:s' => true, '15:j:q:u' => true, '15:j:r:x' => true, '15:j:r:y' => true,
    '15:j:s:x' => true, '15:j:s:y' => true, '15:j:u:x' => true, '15:k:l:x' => true, '15:k:n:x' => true,
    '15:k:o:v' => true, '15:k:o:x' => true, '15:k:p:v' => true, '15:k:q:u' => true, '15:k:r:x' => true,
    '15:k:s:x' => true, '15:k:w:y' => true, '15:l:q:u' => true, '15:l:w:x' => true, '15:m:q:u' => true,
    '15:n:q:u' => true, '15:o:p:w' => true, '15:o:q:u' => true, '15:o:w:x' => true, '15:o:w:y' => true,
    '15:p:q:u' => true, '15:p:q:w' => true, '15:p:r:w' => true, '15:p:u:w' => true, '15:p:w:y' => true,
    '15:p:w:z' => true, '15:q:r:u' => true, '15:q:s:u' => true, '15:q:t:u' => true, '15:q:u:v' => true,
    '15:q:u:w' => true, '15:q:u:x' => true, '15:q:u:y' => true, '15:q:u:z' => true, '15:r:w:x' => true,
    '15:s:w:x' => true, '15:u:w:x' => true, '15:u:w:y' => true, '15:w:y:z' => true, '3:a:c:k' => true,
    '3:a:d:w' => true, '3:a:f:y' => true, '3:a:j:m' => true, '3:a:j:n' => true, '3:a:j:p' => true,
    '3:a:j:z' => true, '3:a:m:v' => true, '3:a:p:w' => true, '3:a:p:x' => true, '3:a:q:t' => true,
    '3:a:s:z' => true, '3:a:t:w' => true, '3:a:w:x' => true, '3:a:y:z' => true, '3:b:k:o' => true,
    '3:b:m:w' => true, '3:c:m:q' => true, '3:c:u:y' => true, '3:d:i:k' => true, '3:d:i:x' => true,
    '3:e:f:z' => true, '3:e:g:x' => true, '3:e:k:z' => true, '3:e:m:z' => true, '3:e:n:z' => true,
    '3:e:p:q' => true, '3:e:v:x' => true, '3:f:i:v' => true, '3:g:o:w' => true, '3:h:i:k' => true,
    '3:h:i:v' => true, '3:h:l:m' => true, '3:h:o:w' => true, '3:i:n:y' => true, '4:a:d:w' => true,
    '4:a:g:x' => true, '4:a:j:w' => true, '4:a:q:w' => true, '4:a:v:z' => true, '4:a:w:y' => true,
    '4:b:i:w' => true, '4:b:j:u' => true, '4:b:s:w' => true, '4:c:g:o' => true, '4:c:g:p' => true,
    '4:c:n:q' => true, '4:c:p:q' => true, '4:c:q:s' => true, '4:c:r:z' => true, '4:c:u:z' => true,
    '4:c:v:y' => true, '4:d:f:q' => true, '4:d:h:p' => true, '4:d:k:u' => true, '4:d:l:p' => true,
    '4:d:m:t' => true, '4:d:s:z' => true, '4:e:j:l' => true, '4:e:j:x' => true, '4:e:p:w' => true,
    '4:e:x:z' => true, '4:f:i:q' => true, '4:f:j:u' => true, '4:f:l:y' => true, '4:f:m:t' => true,
    '4:f:o:w' => true, '4:f:p:q' => true, '4:f:q:u' => true, '4:f:q:w' => true, '4:f:q:z' => true,
    '4:f:s:v' => true, '4:f:u:z' => true, '4:g:i:q' => true, '4:g:i:w' => true, '4:g:l:t' => true,
    '4:g:n:q' => true, '4:g:u:x' => true, '4:h:i:q' => true, '4:h:k:y' => true, '4:h:l:z' => true,
    '4:h:p:q' => true, '4:h:t:y' => true, '4:i:j:v' => true, '4:i:j:w' => true, '4:i:n:q' => true,
    '4:i:t:y' => true, '4:i:u:z' => true, '4:i:w:z' => true, '4:j:k:u' => true, '4:j:l:o' => true,
    '4:j:m:u' => true, '4:j:r:y' => true, '4:j:u:x' => true, '4:j:u:y' => true, '4:k:l:v' => true,
    '4:k:m:u' => true, '4:k:r:z' => true, '4:k:s:z' => true, '4:k:u:z' => true, '4:l:n:y' => true,
    '4:l:q:u' => true, '4:l:u:z' => true, '4:l:x:y' => true, '4:m:r:w' => true, '4:m:t:y' => true,
    '4:m:u:w' => true, '4:o:p:q' => true, '4:o:q:s' => true, '4:o:q:y' => true, '4:o:u:w' => true,
    '4:p:r:w' => true, '4:p:s:v' => true, '4:q:u:y' => true, '4:q:u:z' => true, '4:r:t:w' => true,
    '4:t:w:z' => true, '4:t:x:y' => true, '4:v:y:z' => true, '5:a:j:q' => true, '5:a:j:x' => true,
    '5:a:q:w' => true, '5:a:w:x' => true, '5:a:w:y' => true, '5:b:o:q' => true, '5:b:o:w' => true,
    '5:b:s:w' => true, '5:c:j:m' => true, '5:d:q:u' => true, '5:d:u:w' => true, '5:e:m:w' => true,
    '5:e:q:x' => true, '5:e:x:z' => true, '5:f:i:q' => true, '5:f:j:r' => true, '5:f:n:p' => true,
    '5:f:o:w' => true, '5:g:i:q' => true, '5:g:n:q' => true, '5:g:q:s' => true, '5:g:w:z' => true,
    '5:h:i:q' => true, '5:h:j:u' => true, '5:h:q:s' => true, '5:h:q:u' => true, '5:h:r:w' => true,
    '5:h:v:y' => true, '5:i:k:q' => true, '5:i:k:x' => true, '5:i:w:x' => true, '5:j:q:u' => true,
    '5:j:s:y' => true, '5:j:t:v' => true, '5:j:u:x' => true, '5:k:l:x' => true, '5:k:o:v' => true,
    '5:k:o:w' => true, '5:k:q:r' => true, '5:k:q:u' => true, '5:k:r:w' => true, '5:k:x:y' => true,
    '5:l:q:y' => true, '5:m:s:w' => true, '5:m:u:w' => true, '5:m:w:x' => true, '5:m:x:y' => true,
    '5:n:o:q' => true, '5:p:q:u' => true, '5:q:s:w' => true, '5:q:u:v' => true, '5:q:u:x' => true,
    '5:q:u:y' => true, '5:r:u:w' => true, '5:r:w:z' => true, '5:s:w:y' => true, '5:s:w:z' => true,
    '5:u:x:z' => true, '6:a:j:w' => true, '6:a:k:x' => true, '6:b:j:w' => true, '6:c:d:w' => true,
    '6:c:w:x' => true, '6:c:w:y' => true, '6:e:q:x' => true, '6:f:m:y' => true, '6:f:n:w' => true,
    '6:g:q:u' => true, '6:g:q:y' => true, '6:i:j:w' => true, '6:i:k:q' => true, '6:j:o:w' => true,
    '6:j:p:y' => true, '6:j:q:r' => true, '6:k:l:x' => true, '6:k:m:p' => true, '6:k:n:x' => true,
    '6:k:o:x' => true, '6:k:q:u' => true, '6:k:u:w' => true, '6:k:w:y' => true, '6:n:u:w' => true,
    '6:o:w:x' => true, '6:o:w:y' => true, '6:p:u:w' => true, '6:p:w:x' => true, '6:q:s:x' => true,
    '6:q:t:w' => true, '6:q:u:v' => true, '6:q:u:w' => true, '6:q:u:x' => true, '6:q:w:y' => true,
    '6:r:w:y' => true, '6:t:w:y' => true, '6:x:y:z' => true, '7:a:j:w' => true, '7:a:v:w' => true,
    '7:a:w:x' => true, '7:b:j:w' => true, '7:b:k:w' => true, '7:b:q:u' => true, '7:e:q:x' => true,
    '7:e:v:w' => true, '7:f:n:w' => true, '7:f:q:u' => true, '7:i:j:w' => true, '7:i:v:w' => true,
    '7:j:k:x' => true, '7:j:o:w' => true, '7:j:p:y' => true, '7:j:q:u' => true, '7:k:q:t' => true,
    '7:k:q:u' => true, '7:l:q:w' => true, '7:l:w:x' => true, '7:m:w:x' => true, '7:n:v:w' => true,
    '7:o:w:z' => true, '7:q:t:w' => true, '7:q:u:w' => true, '7:q:u:x' => true, '7:q:w:y' => true,
    '7:r:v:w' => true, '7:t:w:x' => true, '7:w:x:y' => true, '8:a:j:w' => true, '8:a:k:x' => true,
    '8:a:v:w' => true, '8:a:w:x' => true, '8:b:q:u' => true, '8:e:v:w' => true, '8:e:x:z' => true,
    '8:f:h:w' => true, '8:f:q:u' => true, '8:g:q:u' => true, '8:h:j:y' => true, '8:h:k:x' => true,
    '8:i:j:w' => true, '8:i:k:q' => true, '8:j:n:w' => true, '8:j:o:w' => true, '8:j:q:u' => true,
    '8:j:s:w' => true, '8:j:w:y' => true, '8:k:q:u' => true, '8:k:r:x' => true, '8:k:x:y' => true,
    '8:l:v:w' => true, '8:m:w:x' => true, '8:n:v:w' => true, '8:o:v:w' => true, '8:p:q:u' => true,
    '8:q:r:w' => true, '8:q:u:v' => true, '8:q:u:x' => true, '8:q:u:y' => true, '8:r:v:w' => true,
    '8:s:v:w' => true, '8:s:w:x' => true, '8:w:x:y' => true, '9:a:j:w' => true, '9:a:k:x' => true,
    '9:a:w:x' => true, '9:d:q:u' => true, '9:f:h:w' => true, '9:f:q:u' => true, '9:g:k:w' => true,
    '9:g:q:u' => true, '9:h:w:x' => true, '9:h:w:y' => true, '9:i:q:w' => true, '9:j:l:y' => true,
    '9:j:n:w' => true, '9:j:o:w' => true, '9:j:p:v' => true, '9:j:q:u' => true, '9:j:r:w' => true,
    '9:j:t:w' => true, '9:j:w:z' => true, '9:k:q:u' => true, '9:n:q:w' => true, '9:n:w:x' => true,
    '9:o:w:x' => true, '9:p:q:u' => true, '9:q:r:w' => true, '9:q:u:v' => true, '9:q:u:w' => true,
    '9:q:u:x' => true, '9:q:u:y' => true, '9:r:w:x' => true, '9:s:w:x' => true, '9:t:w:x' => true,
];

const D041_EXCLUDED_ROUTE_PATHS = [
    'word_list_avec_single_letter' => [
        '/mots/2-lettres/avec/w' => true,
    ],
    'word_list_avec_three_letters' => [
        '/mots/10-lettres/avec/c/f/w' => true, '/mots/10-lettres/avec/f/g/w' => true,
        '/mots/10-lettres/avec/f/p/w' => true, '/mots/10-lettres/avec/f/q/x' => true,
        '/mots/10-lettres/avec/g/j/k' => true, '/mots/10-lettres/avec/j/v/x' => true,
        '/mots/10-lettres/avec/k/p/x' => true, '/mots/11-lettres/avec/h/k/x' => true,
        '/mots/11-lettres/avec/j/k/p' => true, '/mots/11-lettres/avec/k/x/y' => true,
        '/mots/12-lettres/avec/b/j/y' => true, '/mots/12-lettres/avec/f/j/m' => true,
        '/mots/12-lettres/avec/f/v/w' => true, '/mots/12-lettres/avec/g/h/j' => true,
        '/mots/12-lettres/avec/h/w/x' => true, '/mots/13-lettres/avec/d/j/k' => true,
        '/mots/13-lettres/avec/g/j/x' => true, '/mots/14-lettres/avec/f/j/x' => true,
        '/mots/14-lettres/avec/m/q/w' => true, '/mots/15-lettres/avec/f/k/y' => true,
        '/mots/15-lettres/avec/g/k/y' => true, '/mots/3-lettres/avec/a/b/f' => true,
        '/mots/3-lettres/avec/a/b/j' => true, '/mots/3-lettres/avec/a/c/h' => true,
        '/mots/3-lettres/avec/a/d/z' => true, '/mots/3-lettres/avec/a/e/o' => true,
        '/mots/3-lettres/avec/a/f/p' => true, '/mots/3-lettres/avec/a/g/m' => true,
        '/mots/3-lettres/avec/a/g/t' => true, '/mots/3-lettres/avec/a/g/z' => true,
        '/mots/3-lettres/avec/a/h/t' => true, '/mots/3-lettres/avec/a/i/z' => true,
        '/mots/3-lettres/avec/a/j/r' => true, '/mots/3-lettres/avec/a/k/w' => true,
        '/mots/3-lettres/avec/a/k/y' => true, '/mots/3-lettres/avec/a/s/x' => true,
        '/mots/3-lettres/avec/a/u/v' => true, '/mots/3-lettres/avec/a/u/w' => true,
        '/mots/3-lettres/avec/a/u/x' => true, '/mots/3-lettres/avec/a/u/y' => true,
        '/mots/3-lettres/avec/b/c/n' => true, '/mots/3-lettres/avec/b/d/s' => true,
        '/mots/3-lettres/avec/b/d/u' => true, '/mots/3-lettres/avec/b/e/p' => true,
        '/mots/3-lettres/avec/b/e/w' => true, '/mots/3-lettres/avec/b/e/z' => true,
        '/mots/3-lettres/avec/b/g/h' => true, '/mots/3-lettres/avec/b/g/o' => true,
        '/mots/3-lettres/avec/b/h/u' => true, '/mots/3-lettres/avec/b/i/m' => true,
        '/mots/3-lettres/avec/b/j/o' => true, '/mots/3-lettres/avec/b/l/x' => true,
        '/mots/3-lettres/avec/b/m/o' => true, '/mots/3-lettres/avec/b/o/r' => true,
        '/mots/3-lettres/avec/b/o/x' => true, '/mots/3-lettres/avec/b/o/z' => true,
        '/mots/3-lettres/avec/b/p/z' => true, '/mots/3-lettres/avec/c/d/g' => true,
        '/mots/3-lettres/avec/c/d/r' => true, '/mots/3-lettres/avec/c/e/z' => true,
        '/mots/3-lettres/avec/c/g/s' => true, '/mots/3-lettres/avec/c/g/t' => true,
        '/mots/3-lettres/avec/c/i/z' => true, '/mots/3-lettres/avec/c/j/u' => true,
        '/mots/3-lettres/avec/c/l/p' => true, '/mots/3-lettres/avec/c/m/r' => true,
        '/mots/3-lettres/avec/c/m/x' => true, '/mots/3-lettres/avec/c/n/s' => true,
        '/mots/3-lettres/avec/c/o/q' => true, '/mots/3-lettres/avec/c/o/x' => true,
        '/mots/3-lettres/avec/c/p/r' => true, '/mots/3-lettres/avec/c/p/t' => true,
        '/mots/3-lettres/avec/c/p/u' => true, '/mots/3-lettres/avec/c/p/v' => true,
        '/mots/3-lettres/avec/c/t/v' => true, '/mots/3-lettres/avec/d/e/g' => true,
        '/mots/3-lettres/avec/d/e/j' => true, '/mots/3-lettres/avec/d/e/p' => true,
        '/mots/3-lettres/avec/d/e/u' => true, '/mots/3-lettres/avec/d/e/v' => true,
        '/mots/3-lettres/avec/d/e/y' => true, '/mots/3-lettres/avec/d/f/p' => true,
        '/mots/3-lettres/avec/d/f/s' => true, '/mots/3-lettres/avec/d/g/n' => true,
        '/mots/3-lettres/avec/d/g/p' => true, '/mots/3-lettres/avec/d/h/i' => true,
        '/mots/3-lettres/avec/d/h/r' => true, '/mots/3-lettres/avec/d/i/v' => true,
        '/mots/3-lettres/avec/d/i/z' => true, '/mots/3-lettres/avec/d/l/m' => true,
        '/mots/3-lettres/avec/d/m/r' => true, '/mots/3-lettres/avec/d/n/p' => true,
        '/mots/3-lettres/avec/d/n/s' => true, '/mots/3-lettres/avec/d/n/t' => true,
        '/mots/3-lettres/avec/d/n/u' => true, '/mots/3-lettres/avec/d/o/p' => true,
        '/mots/3-lettres/avec/d/o/y' => true, '/mots/3-lettres/avec/d/o/z' => true,
        '/mots/3-lettres/avec/d/p/t' => true, '/mots/3-lettres/avec/d/p/v' => true,
        '/mots/3-lettres/avec/d/r/s' => true, '/mots/3-lettres/avec/d/s/t' => true,
        '/mots/3-lettres/avec/d/u/y' => true, '/mots/3-lettres/avec/e/f/m' => true,
        '/mots/3-lettres/avec/e/f/t' => true, '/mots/3-lettres/avec/e/j/t' => true,
        '/mots/3-lettres/avec/e/j/u' => true, '/mots/3-lettres/avec/e/k/l' => true,
        '/mots/3-lettres/avec/e/k/p' => true, '/mots/3-lettres/avec/e/l/z' => true,
        '/mots/3-lettres/avec/e/m/p' => true, '/mots/3-lettres/avec/e/o/x' => true,
        '/mots/3-lettres/avec/e/o/z' => true, '/mots/3-lettres/avec/e/q/u' => true,
        '/mots/3-lettres/avec/e/s/x' => true, '/mots/3-lettres/avec/e/s/y' => true,
        '/mots/3-lettres/avec/e/u/x' => true, '/mots/3-lettres/avec/e/u/y' => true,
        '/mots/3-lettres/avec/f/g/i' => true, '/mots/3-lettres/avec/f/g/m' => true,
        '/mots/3-lettres/avec/f/g/o' => true, '/mots/3-lettres/avec/f/i/o' => true,
        '/mots/3-lettres/avec/f/l/s' => true, '/mots/3-lettres/avec/f/n/u' => true,
        '/mots/3-lettres/avec/f/o/q' => true, '/mots/3-lettres/avec/f/o/r' => true,
        '/mots/3-lettres/avec/f/p/t' => true, '/mots/3-lettres/avec/g/h/u' => true,
        '/mots/3-lettres/avec/g/i/v' => true, '/mots/3-lettres/avec/g/i/z' => true,
        '/mots/3-lettres/avec/g/l/o' => true, '/mots/3-lettres/avec/g/l/p' => true,
        '/mots/3-lettres/avec/g/m/o' => true, '/mots/3-lettres/avec/g/m/s' => true,
        '/mots/3-lettres/avec/g/m/u' => true, '/mots/3-lettres/avec/g/n/r' => true,
        '/mots/3-lettres/avec/g/o/y' => true, '/mots/3-lettres/avec/g/p/s' => true,
        '/mots/3-lettres/avec/g/s/t' => true, '/mots/3-lettres/avec/g/t/v' => true,
        '/mots/3-lettres/avec/h/o/r' => true, '/mots/3-lettres/avec/h/p/u' => true,
        '/mots/3-lettres/avec/i/j/r' => true, '/mots/3-lettres/avec/i/j/s' => true,
        '/mots/3-lettres/avec/i/k/l' => true, '/mots/3-lettres/avec/i/k/r' => true,
        '/mots/3-lettres/avec/i/l/z' => true, '/mots/3-lettres/avec/i/n/q' => true,
        '/mots/3-lettres/avec/i/o/v' => true, '/mots/3-lettres/avec/i/p/u' => true,
        '/mots/3-lettres/avec/i/p/z' => true, '/mots/3-lettres/avec/i/q/s' => true,
        '/mots/3-lettres/avec/i/q/u' => true, '/mots/3-lettres/avec/i/s/t' => true,
        '/mots/3-lettres/avec/j/s/t' => true, '/mots/3-lettres/avec/k/n/o' => true,
        '/mots/3-lettres/avec/k/o/p' => true, '/mots/3-lettres/avec/k/o/w' => true,
        '/mots/3-lettres/avec/k/r/u' => true, '/mots/3-lettres/avec/k/s/u' => true,
        '/mots/3-lettres/avec/k/s/y' => true, '/mots/3-lettres/avec/k/u/y' => true,
        '/mots/3-lettres/avec/l/m/t' => true, '/mots/3-lettres/avec/l/m/u' => true,
        '/mots/3-lettres/avec/l/n/u' => true, '/mots/3-lettres/avec/l/r/u' => true,
        '/mots/3-lettres/avec/l/u/x' => true, '/mots/3-lettres/avec/l/u/y' => true,
        '/mots/3-lettres/avec/m/n/t' => true, '/mots/3-lettres/avec/m/o/p' => true,
        '/mots/3-lettres/avec/m/o/u' => true, '/mots/3-lettres/avec/m/o/x' => true,
        '/mots/3-lettres/avec/m/o/y' => true, '/mots/3-lettres/avec/m/p/t' => true,
        '/mots/3-lettres/avec/m/p/u' => true, '/mots/3-lettres/avec/n/o/w' => true,
        '/mots/3-lettres/avec/n/p/v' => true, '/mots/3-lettres/avec/n/q/s' => true,
        '/mots/3-lettres/avec/o/s/u' => true, '/mots/3-lettres/avec/o/u/z' => true,
        '/mots/3-lettres/avec/p/s/t' => true, '/mots/3-lettres/avec/p/s/y' => true,
        '/mots/3-lettres/avec/p/u/y' => true, '/mots/3-lettres/avec/p/u/z' => true,
        '/mots/3-lettres/avec/q/s/u' => true, '/mots/3-lettres/avec/r/t/v' => true,
        '/mots/3-lettres/avec/r/u/z' => true, '/mots/3-lettres/avec/s/u/v' => true,
        '/mots/3-lettres/avec/s/u/w' => true, '/mots/3-lettres/avec/t/u/z' => true,
        '/mots/4-lettres/avec/a/b/f' => true, '/mots/4-lettres/avec/a/b/j' => true,
        '/mots/4-lettres/avec/a/b/w' => true, '/mots/4-lettres/avec/a/c/z' => true,
        '/mots/4-lettres/avec/a/f/h' => true, '/mots/4-lettres/avec/a/f/w' => true,
        '/mots/4-lettres/avec/a/f/y' => true, '/mots/4-lettres/avec/a/f/z' => true,
        '/mots/4-lettres/avec/a/g/j' => true, '/mots/4-lettres/avec/a/g/k' => true,
        '/mots/4-lettres/avec/a/g/w' => true, '/mots/4-lettres/avec/a/h/z' => true,
        '/mots/4-lettres/avec/a/j/v' => true, '/mots/4-lettres/avec/a/j/z' => true,
        '/mots/4-lettres/avec/a/o/w' => true, '/mots/4-lettres/avec/a/r/w' => true,
        '/mots/4-lettres/avec/a/v/y' => true, '/mots/4-lettres/avec/a/x/z' => true,
        '/mots/4-lettres/avec/b/c/g' => true, '/mots/4-lettres/avec/b/c/m' => true,
        '/mots/4-lettres/avec/b/c/p' => true, '/mots/4-lettres/avec/b/d/g' => true,
        '/mots/4-lettres/avec/b/d/m' => true, '/mots/4-lettres/avec/b/e/w' => true,
        '/mots/4-lettres/avec/b/f/r' => true, '/mots/4-lettres/avec/b/g/t' => true,
        '/mots/4-lettres/avec/b/i/k' => true, '/mots/4-lettres/avec/b/i/v' => true,
        '/mots/4-lettres/avec/b/j/o' => true, '/mots/4-lettres/avec/b/k/l' => true,
        '/mots/4-lettres/avec/b/k/n' => true, '/mots/4-lettres/avec/b/k/s' => true,
        '/mots/4-lettres/avec/b/l/m' => true, '/mots/4-lettres/avec/b/l/n' => true,
        '/mots/4-lettres/avec/b/m/r' => true, '/mots/4-lettres/avec/b/m/z' => true,
        '/mots/4-lettres/avec/b/n/z' => true, '/mots/4-lettres/avec/b/r/z' => true,
        '/mots/4-lettres/avec/b/s/x' => true, '/mots/4-lettres/avec/b/t/z' => true,
        '/mots/4-lettres/avec/b/u/x' => true, '/mots/4-lettres/avec/c/d/g' => true,
        '/mots/4-lettres/avec/c/d/h' => true, '/mots/4-lettres/avec/c/d/l' => true,
        '/mots/4-lettres/avec/c/d/m' => true, '/mots/4-lettres/avec/c/d/p' => true,
        '/mots/4-lettres/avec/c/d/q' => true, '/mots/4-lettres/avec/c/e/j' => true,
        '/mots/4-lettres/avec/c/e/w' => true, '/mots/4-lettres/avec/c/f/p' => true,
        '/mots/4-lettres/avec/c/f/t' => true, '/mots/4-lettres/avec/c/g/n' => true,
        '/mots/4-lettres/avec/c/h/q' => true, '/mots/4-lettres/avec/c/h/v' => true,
        '/mots/4-lettres/avec/c/h/z' => true, '/mots/4-lettres/avec/c/i/j' => true,
        '/mots/4-lettres/avec/c/i/q' => true, '/mots/4-lettres/avec/c/i/x' => true,
        '/mots/4-lettres/avec/c/j/n' => true, '/mots/4-lettres/avec/c/k/y' => true,
        '/mots/4-lettres/avec/c/l/y' => true, '/mots/4-lettres/avec/c/m/r' => true,
        '/mots/4-lettres/avec/c/m/t' => true, '/mots/4-lettres/avec/c/m/y' => true,
        '/mots/4-lettres/avec/c/n/u' => true, '/mots/4-lettres/avec/c/n/v' => true,
        '/mots/4-lettres/avec/c/n/z' => true, '/mots/4-lettres/avec/c/o/q' => true,
        '/mots/4-lettres/avec/c/o/w' => true, '/mots/4-lettres/avec/c/o/z' => true,
        '/mots/4-lettres/avec/c/p/t' => true, '/mots/4-lettres/avec/c/r/v' => true,
        '/mots/4-lettres/avec/c/r/w' => true, '/mots/4-lettres/avec/c/r/y' => true,
        '/mots/4-lettres/avec/c/s/v' => true, '/mots/4-lettres/avec/c/u/y' => true,
        '/mots/4-lettres/avec/d/e/h' => true, '/mots/4-lettres/avec/d/f/m' => true,
        '/mots/4-lettres/avec/d/g/l' => true, '/mots/4-lettres/avec/d/h/o' => true,
        '/mots/4-lettres/avec/d/h/u' => true, '/mots/4-lettres/avec/d/h/y' => true,
        '/mots/4-lettres/avec/d/i/x' => true, '/mots/4-lettres/avec/d/j/s' => true,
        '/mots/4-lettres/avec/d/k/n' => true, '/mots/4-lettres/avec/d/k/r' => true,
        '/mots/4-lettres/avec/d/k/y' => true, '/mots/4-lettres/avec/d/l/v' => true,
        '/mots/4-lettres/avec/d/l/y' => true, '/mots/4-lettres/avec/d/m/p' => true,
        '/mots/4-lettres/avec/d/n/z' => true, '/mots/4-lettres/avec/d/o/v' => true,
        '/mots/4-lettres/avec/d/o/z' => true, '/mots/4-lettres/avec/d/q/u' => true,
        '/mots/4-lettres/avec/d/s/v' => true, '/mots/4-lettres/avec/d/u/w' => true,
        '/mots/4-lettres/avec/d/u/x' => true, '/mots/4-lettres/avec/e/f/j' => true,
        '/mots/4-lettres/avec/e/f/y' => true, '/mots/4-lettres/avec/e/g/k' => true,
        '/mots/4-lettres/avec/e/h/j' => true, '/mots/4-lettres/avec/e/h/k' => true,
        '/mots/4-lettres/avec/e/h/w' => true, '/mots/4-lettres/avec/e/h/x' => true,
        '/mots/4-lettres/avec/e/i/y' => true, '/mots/4-lettres/avec/e/j/m' => true,
        '/mots/4-lettres/avec/e/j/v' => true, '/mots/4-lettres/avec/e/j/z' => true,
        '/mots/4-lettres/avec/e/k/m' => true, '/mots/4-lettres/avec/e/k/v' => true,
        '/mots/4-lettres/avec/e/k/z' => true, '/mots/4-lettres/avec/e/m/v' => true,
        '/mots/4-lettres/avec/e/n/w' => true, '/mots/4-lettres/avec/e/o/w' => true,
        '/mots/4-lettres/avec/e/q/z' => true, '/mots/4-lettres/avec/f/g/r' => true,
        '/mots/4-lettres/avec/f/g/t' => true, '/mots/4-lettres/avec/f/g/u' => true,
        '/mots/4-lettres/avec/f/h/o' => true, '/mots/4-lettres/avec/f/h/q' => true,
        '/mots/4-lettres/avec/f/h/u' => true, '/mots/4-lettres/avec/f/i/j' => true,
        '/mots/4-lettres/avec/f/i/m' => true, '/mots/4-lettres/avec/f/i/v' => true,
        '/mots/4-lettres/avec/f/i/w' => true, '/mots/4-lettres/avec/f/l/w' => true,
        '/mots/4-lettres/avec/f/m/s' => true, '/mots/4-lettres/avec/f/o/q' => true,
        '/mots/4-lettres/avec/f/o/z' => true, '/mots/4-lettres/avec/f/q/s' => true,
        '/mots/4-lettres/avec/f/s/z' => true, '/mots/4-lettres/avec/f/u/v' => true,
        '/mots/4-lettres/avec/g/h/n' => true, '/mots/4-lettres/avec/g/h/r' => true,
        '/mots/4-lettres/avec/g/h/u' => true, '/mots/4-lettres/avec/g/h/w' => true,
        '/mots/4-lettres/avec/g/h/z' => true, '/mots/4-lettres/avec/g/i/j' => true,
        '/mots/4-lettres/avec/g/i/y' => true, '/mots/4-lettres/avec/g/j/p' => true,
        '/mots/4-lettres/avec/g/k/o' => true, '/mots/4-lettres/avec/g/l/v' => true,
        '/mots/4-lettres/avec/g/o/p' => true, '/mots/4-lettres/avec/g/o/w' => true,
        '/mots/4-lettres/avec/g/o/z' => true, '/mots/4-lettres/avec/g/p/r' => true,
        '/mots/4-lettres/avec/g/p/u' => true, '/mots/4-lettres/avec/g/r/t' => true,
        '/mots/4-lettres/avec/g/r/z' => true, '/mots/4-lettres/avec/g/s/z' => true,
        '/mots/4-lettres/avec/g/u/z' => true, '/mots/4-lettres/avec/h/i/v' => true,
        '/mots/4-lettres/avec/h/i/z' => true, '/mots/4-lettres/avec/h/j/o' => true,
        '/mots/4-lettres/avec/h/k/p' => true, '/mots/4-lettres/avec/h/k/r' => true,
        '/mots/4-lettres/avec/h/m/r' => true, '/mots/4-lettres/avec/h/m/s' => true,
        '/mots/4-lettres/avec/h/m/y' => true, '/mots/4-lettres/avec/h/o/q' => true,
        '/mots/4-lettres/avec/h/o/v' => true, '/mots/4-lettres/avec/h/o/x' => true,
        '/mots/4-lettres/avec/h/p/w' => true, '/mots/4-lettres/avec/h/p/z' => true,
        '/mots/4-lettres/avec/h/u/x' => true, '/mots/4-lettres/avec/h/u/z' => true,
        '/mots/4-lettres/avec/i/j/r' => true, '/mots/4-lettres/avec/i/j/s' => true,
        '/mots/4-lettres/avec/i/j/t' => true, '/mots/4-lettres/avec/i/k/u' => true,
        '/mots/4-lettres/avec/i/k/v' => true, '/mots/4-lettres/avec/i/k/z' => true,
        '/mots/4-lettres/avec/i/m/p' => true, '/mots/4-lettres/avec/i/m/z' => true,
        '/mots/4-lettres/avec/i/p/z' => true, '/mots/4-lettres/avec/i/q/z' => true,
        '/mots/4-lettres/avec/i/r/w' => true, '/mots/4-lettres/avec/i/s/w' => true,
        '/mots/4-lettres/avec/i/v/z' => true, '/mots/4-lettres/avec/i/x/z' => true,
        '/mots/4-lettres/avec/j/k/n' => true, '/mots/4-lettres/avec/j/k/r' => true,
        '/mots/4-lettres/avec/j/m/p' => true, '/mots/4-lettres/avec/j/m/s' => true,
        '/mots/4-lettres/avec/j/o/r' => true, '/mots/4-lettres/avec/j/p/s' => true,
        '/mots/4-lettres/avec/k/l/t' => true, '/mots/4-lettres/avec/k/l/u' => true,
        '/mots/4-lettres/avec/k/l/w' => true, '/mots/4-lettres/avec/k/m/s' => true,
        '/mots/4-lettres/avec/k/n/r' => true, '/mots/4-lettres/avec/k/n/t' => true,
        '/mots/4-lettres/avec/k/o/z' => true, '/mots/4-lettres/avec/k/s/v' => true,
        '/mots/4-lettres/avec/k/t/y' => true, '/mots/4-lettres/avec/l/m/p' => true,
        '/mots/4-lettres/avec/l/m/r' => true, '/mots/4-lettres/avec/l/m/y' => true,
        '/mots/4-lettres/avec/l/n/r' => true, '/mots/4-lettres/avec/l/n/v' => true,
        '/mots/4-lettres/avec/l/n/x' => true, '/mots/4-lettres/avec/l/s/w' => true,
        '/mots/4-lettres/avec/l/t/y' => true, '/mots/4-lettres/avec/l/t/z' => true,
        '/mots/4-lettres/avec/l/v/x' => true, '/mots/4-lettres/avec/l/w/y' => true,
        '/mots/4-lettres/avec/m/n/x' => true, '/mots/4-lettres/avec/m/p/r' => true,
        '/mots/4-lettres/avec/m/p/t' => true, '/mots/4-lettres/avec/m/p/v' => true,
        '/mots/4-lettres/avec/m/r/y' => true, '/mots/4-lettres/avec/m/s/v' => true,
        '/mots/4-lettres/avec/m/u/y' => true, '/mots/4-lettres/avec/n/p/r' => true,
        '/mots/4-lettres/avec/n/p/x' => true, '/mots/4-lettres/avec/n/q/s' => true,
        '/mots/4-lettres/avec/n/r/s' => true, '/mots/4-lettres/avec/n/r/z' => true,
        '/mots/4-lettres/avec/n/s/z' => true, '/mots/4-lettres/avec/n/u/y' => true,
        '/mots/4-lettres/avec/o/r/z' => true, '/mots/4-lettres/avec/o/t/x' => true,
        '/mots/4-lettres/avec/o/t/z' => true, '/mots/4-lettres/avec/o/v/x' => true,
        '/mots/4-lettres/avec/o/y/z' => true, '/mots/4-lettres/avec/p/r/v' => true,
        '/mots/4-lettres/avec/p/r/x' => true, '/mots/4-lettres/avec/p/r/y' => true,
        '/mots/4-lettres/avec/p/t/x' => true, '/mots/4-lettres/avec/q/s/t' => true,
        '/mots/4-lettres/avec/q/t/u' => true, '/mots/4-lettres/avec/r/s/z' => true,
        '/mots/4-lettres/avec/r/t/x' => true, '/mots/4-lettres/avec/r/u/w' => true,
        '/mots/4-lettres/avec/r/u/x' => true, '/mots/4-lettres/avec/r/x/y' => true,
        '/mots/4-lettres/avec/s/u/x' => true, '/mots/4-lettres/avec/s/v/x' => true,
        '/mots/4-lettres/avec/s/w/y' => true, '/mots/4-lettres/avec/s/x/y' => true,
        '/mots/4-lettres/avec/s/y/z' => true, '/mots/4-lettres/avec/t/u/y' => true,
        '/mots/4-lettres/avec/u/x/y' => true, '/mots/4-lettres/avec/u/y/z' => true,
        '/mots/5-lettres/avec/a/q/z' => true, '/mots/5-lettres/avec/a/w/z' => true,
        '/mots/5-lettres/avec/b/c/j' => true, '/mots/5-lettres/avec/b/c/q' => true,
        '/mots/5-lettres/avec/b/c/v' => true, '/mots/5-lettres/avec/b/d/k' => true,
        '/mots/5-lettres/avec/b/d/m' => true, '/mots/5-lettres/avec/b/f/z' => true,
        '/mots/5-lettres/avec/b/g/h' => true, '/mots/5-lettres/avec/b/h/j' => true,
        '/mots/5-lettres/avec/b/h/m' => true, '/mots/5-lettres/avec/b/i/w' => true,
        '/mots/5-lettres/avec/b/l/w' => true, '/mots/5-lettres/avec/b/m/w' => true,
        '/mots/5-lettres/avec/b/n/p' => true, '/mots/5-lettres/avec/b/n/q' => true,
        '/mots/5-lettres/avec/b/q/r' => true, '/mots/5-lettres/avec/b/r/w' => true,
        '/mots/5-lettres/avec/c/d/j' => true, '/mots/5-lettres/avec/c/d/v' => true,
        '/mots/5-lettres/avec/c/f/m' => true, '/mots/5-lettres/avec/c/f/v' => true,
        '/mots/5-lettres/avec/c/f/y' => true, '/mots/5-lettres/avec/c/g/k' => true,
        '/mots/5-lettres/avec/c/g/p' => true, '/mots/5-lettres/avec/c/g/z' => true,
        '/mots/5-lettres/avec/c/j/y' => true, '/mots/5-lettres/avec/c/k/m' => true,
        '/mots/5-lettres/avec/c/k/q' => true, '/mots/5-lettres/avec/c/n/q' => true,
        '/mots/5-lettres/avec/c/q/s' => true, '/mots/5-lettres/avec/c/v/y' => true,
        '/mots/5-lettres/avec/d/f/g' => true, '/mots/5-lettres/avec/d/f/j' => true,
        '/mots/5-lettres/avec/d/f/m' => true, '/mots/5-lettres/avec/d/f/p' => true,
        '/mots/5-lettres/avec/d/g/p' => true, '/mots/5-lettres/avec/d/h/k' => true,
        '/mots/5-lettres/avec/d/h/p' => true, '/mots/5-lettres/avec/d/h/z' => true,
        '/mots/5-lettres/avec/d/j/m' => true, '/mots/5-lettres/avec/d/j/t' => true,
        '/mots/5-lettres/avec/d/k/v' => true, '/mots/5-lettres/avec/d/l/w' => true,
        '/mots/5-lettres/avec/d/n/q' => true, '/mots/5-lettres/avec/d/o/q' => true,
        '/mots/5-lettres/avec/d/r/x' => true, '/mots/5-lettres/avec/d/s/w' => true,
        '/mots/5-lettres/avec/d/t/w' => true, '/mots/5-lettres/avec/e/j/x' => true,
        '/mots/5-lettres/avec/e/q/y' => true, '/mots/5-lettres/avec/f/h/m' => true,
        '/mots/5-lettres/avec/f/h/w' => true, '/mots/5-lettres/avec/f/j/o' => true,
        '/mots/5-lettres/avec/f/k/w' => true, '/mots/5-lettres/avec/f/k/y' => true,
        '/mots/5-lettres/avec/f/m/p' => true, '/mots/5-lettres/avec/f/q/w' => true,
        '/mots/5-lettres/avec/f/t/w' => true, '/mots/5-lettres/avec/f/y/z' => true,
        '/mots/5-lettres/avec/g/h/w' => true, '/mots/5-lettres/avec/g/h/z' => true,
        '/mots/5-lettres/avec/g/k/p' => true, '/mots/5-lettres/avec/g/k/w' => true,
        '/mots/5-lettres/avec/g/m/z' => true, '/mots/5-lettres/avec/g/n/x' => true,
        '/mots/5-lettres/avec/g/r/w' => true, '/mots/5-lettres/avec/g/s/x' => true,
        '/mots/5-lettres/avec/g/v/y' => true, '/mots/5-lettres/avec/h/k/z' => true,
        '/mots/5-lettres/avec/h/l/w' => true, '/mots/5-lettres/avec/h/m/v' => true,
        '/mots/5-lettres/avec/h/n/w' => true, '/mots/5-lettres/avec/h/t/w' => true,
        '/mots/5-lettres/avec/h/t/x' => true, '/mots/5-lettres/avec/i/q/y' => true,
        '/mots/5-lettres/avec/j/l/m' => true, '/mots/5-lettres/avec/j/m/t' => true,
        '/mots/5-lettres/avec/j/n/y' => true, '/mots/5-lettres/avec/j/o/v' => true,
        '/mots/5-lettres/avec/j/o/y' => true, '/mots/5-lettres/avec/j/p/r' => true,
        '/mots/5-lettres/avec/j/q/r' => true, '/mots/5-lettres/avec/j/y/z' => true,
        '/mots/5-lettres/avec/k/m/p' => true, '/mots/5-lettres/avec/k/m/y' => true,
        '/mots/5-lettres/avec/k/t/w' => true, '/mots/5-lettres/avec/k/u/v' => true,
        '/mots/5-lettres/avec/l/m/v' => true, '/mots/5-lettres/avec/l/n/w' => true,
        '/mots/5-lettres/avec/l/p/w' => true, '/mots/5-lettres/avec/l/q/t' => true,
        '/mots/5-lettres/avec/l/r/w' => true, '/mots/5-lettres/avec/l/t/w' => true,
        '/mots/5-lettres/avec/l/u/w' => true, '/mots/5-lettres/avec/m/n/v' => true,
        '/mots/5-lettres/avec/m/p/x' => true, '/mots/5-lettres/avec/n/q/s' => true,
        '/mots/5-lettres/avec/n/r/w' => true, '/mots/5-lettres/avec/n/r/x' => true,
        '/mots/5-lettres/avec/o/v/x' => true, '/mots/5-lettres/avec/p/w/z' => true,
        '/mots/5-lettres/avec/q/r/z' => true, '/mots/5-lettres/avec/q/u/w' => true,
        '/mots/5-lettres/avec/r/w/y' => true, '/mots/6-lettres/avec/b/d/w' => true,
        '/mots/6-lettres/avec/b/f/k' => true, '/mots/6-lettres/avec/b/f/y' => true,
        '/mots/6-lettres/avec/b/g/w' => true, '/mots/6-lettres/avec/b/k/p' => true,
        '/mots/6-lettres/avec/b/n/w' => true, '/mots/6-lettres/avec/b/w/y' => true,
        '/mots/6-lettres/avec/b/w/z' => true, '/mots/6-lettres/avec/c/g/x' => true,
        '/mots/6-lettres/avec/c/m/w' => true, '/mots/6-lettres/avec/c/p/v' => true,
        '/mots/6-lettres/avec/c/q/y' => true, '/mots/6-lettres/avec/c/u/w' => true,
        '/mots/6-lettres/avec/d/f/k' => true, '/mots/6-lettres/avec/d/f/x' => true,
        '/mots/6-lettres/avec/d/g/k' => true, '/mots/6-lettres/avec/d/h/v' => true,
        '/mots/6-lettres/avec/d/h/w' => true, '/mots/6-lettres/avec/d/j/q' => true,
        '/mots/6-lettres/avec/d/l/q' => true, '/mots/6-lettres/avec/d/m/q' => true,
        '/mots/6-lettres/avec/d/w/y' => true, '/mots/6-lettres/avec/e/k/q' => true,
        '/mots/6-lettres/avec/f/g/h' => true, '/mots/6-lettres/avec/f/g/k' => true,
        '/mots/6-lettres/avec/f/h/k' => true, '/mots/6-lettres/avec/f/i/w' => true,
        '/mots/6-lettres/avec/f/p/x' => true, '/mots/6-lettres/avec/f/t/w' => true,
        '/mots/6-lettres/avec/f/u/w' => true, '/mots/6-lettres/avec/g/h/q' => true,
        '/mots/6-lettres/avec/g/k/l' => true, '/mots/6-lettres/avec/g/k/v' => true,
        '/mots/6-lettres/avec/g/k/z' => true, '/mots/6-lettres/avec/g/m/w' => true,
        '/mots/6-lettres/avec/g/w/y' => true, '/mots/6-lettres/avec/h/j/m' => true,
        '/mots/6-lettres/avec/h/j/p' => true, '/mots/6-lettres/avec/h/n/q' => true,
        '/mots/6-lettres/avec/h/p/v' => true, '/mots/6-lettres/avec/h/r/w' => true,
        '/mots/6-lettres/avec/h/w/y' => true, '/mots/6-lettres/avec/j/k/l' => true,
        '/mots/6-lettres/avec/j/k/m' => true, '/mots/6-lettres/avec/j/m/v' => true,
        '/mots/6-lettres/avec/j/m/y' => true, '/mots/6-lettres/avec/j/r/y' => true,
        '/mots/6-lettres/avec/j/x/y' => true, '/mots/6-lettres/avec/j/y/z' => true,
        '/mots/6-lettres/avec/k/m/v' => true, '/mots/6-lettres/avec/k/o/q' => true,
        '/mots/6-lettres/avec/k/t/w' => true, '/mots/6-lettres/avec/k/u/v' => true,
        '/mots/6-lettres/avec/l/q/v' => true, '/mots/6-lettres/avec/l/v/x' => true,
        '/mots/6-lettres/avec/m/u/w' => true, '/mots/6-lettres/avec/n/q/w' => true,
        '/mots/6-lettres/avec/p/w/z' => true, '/mots/6-lettres/avec/q/r/y' => true,
        '/mots/6-lettres/avec/q/s/w' => true, '/mots/6-lettres/avec/q/w/z' => true,
        '/mots/6-lettres/avec/u/w/z' => true, '/mots/6-lettres/avec/w/y/z' => true,
        '/mots/7-lettres/avec/b/d/w' => true, '/mots/7-lettres/avec/b/g/p' => true,
        '/mots/7-lettres/avec/b/h/w' => true, '/mots/7-lettres/avec/b/j/k' => true,
        '/mots/7-lettres/avec/b/u/w' => true, '/mots/7-lettres/avec/c/f/j' => true,
        '/mots/7-lettres/avec/c/g/w' => true, '/mots/7-lettres/avec/c/u/w' => true,
        '/mots/7-lettres/avec/d/f/j' => true, '/mots/7-lettres/avec/d/h/q' => true,
        '/mots/7-lettres/avec/d/j/p' => true, '/mots/7-lettres/avec/d/m/w' => true,
        '/mots/7-lettres/avec/d/q/x' => true, '/mots/7-lettres/avec/e/w/x' => true,
        '/mots/7-lettres/avec/f/g/w' => true, '/mots/7-lettres/avec/f/h/w' => true,
        '/mots/7-lettres/avec/f/k/p' => true, '/mots/7-lettres/avec/f/k/x' => true,
        '/mots/7-lettres/avec/f/k/y' => true, '/mots/7-lettres/avec/f/m/w' => true,
        '/mots/7-lettres/avec/f/t/w' => true, '/mots/7-lettres/avec/f/w/y' => true,
        '/mots/7-lettres/avec/g/h/q' => true, '/mots/7-lettres/avec/g/j/v' => true,
        '/mots/7-lettres/avec/g/j/x' => true, '/mots/7-lettres/avec/h/j/k' => true,
        '/mots/7-lettres/avec/h/p/w' => true, '/mots/7-lettres/avec/i/k/x' => true,
        '/mots/7-lettres/avec/i/q/w' => true, '/mots/7-lettres/avec/i/w/x' => true,
        '/mots/7-lettres/avec/j/k/l' => true, '/mots/7-lettres/avec/j/s/w' => true,
        '/mots/7-lettres/avec/j/w/y' => true, '/mots/7-lettres/avec/k/m/q' => true,
        '/mots/7-lettres/avec/k/p/v' => true, '/mots/7-lettres/avec/k/u/w' => true,
        '/mots/7-lettres/avec/p/v/y' => true, '/mots/7-lettres/avec/u/w/z' => true,
        '/mots/8-lettres/avec/c/f/w' => true, '/mots/8-lettres/avec/c/k/x' => true,
        '/mots/8-lettres/avec/d/j/w' => true, '/mots/8-lettres/avec/d/w/y' => true,
        '/mots/8-lettres/avec/g/j/x' => true, '/mots/8-lettres/avec/g/k/x' => true,
        '/mots/8-lettres/avec/j/k/y' => true, '/mots/8-lettres/avec/k/m/v' => true,
        '/mots/8-lettres/avec/k/t/x' => true, '/mots/8-lettres/avec/k/u/x' => true,
        '/mots/8-lettres/avec/k/v/w' => true, '/mots/8-lettres/avec/w/y/z' => true,
        '/mots/9-lettres/avec/c/k/q' => true, '/mots/9-lettres/avec/c/w/x' => true,
        '/mots/9-lettres/avec/d/g/k' => true, '/mots/9-lettres/avec/d/j/x' => true,
        '/mots/9-lettres/avec/f/j/p' => true, '/mots/9-lettres/avec/f/k/x' => true,
        '/mots/9-lettres/avec/g/j/k' => true, '/mots/9-lettres/avec/g/p/w' => true,
        '/mots/9-lettres/avec/h/j/v' => true, '/mots/9-lettres/avec/h/k/x' => true,
        '/mots/9-lettres/avec/i/j/w' => true, '/mots/9-lettres/avec/j/l/x' => true,
        '/mots/9-lettres/avec/j/m/w' => true, '/mots/9-lettres/avec/t/v/w' => true,
    ],
    'word_list_avec_two_letters' => [
        '/mots/10-lettres/avec/j/w' => true, '/mots/2-lettres/avec/a/d' => true,
        '/mots/2-lettres/avec/a/f' => true, '/mots/2-lettres/avec/a/j' => true, '/mots/2-lettres/avec/a/l' => true,
        '/mots/2-lettres/avec/a/p' => true, '/mots/2-lettres/avec/a/t' => true, '/mots/2-lettres/avec/a/y' => true,
        '/mots/2-lettres/avec/b/d' => true, '/mots/2-lettres/avec/b/e' => true, '/mots/2-lettres/avec/b/g' => true,
        '/mots/2-lettres/avec/b/i' => true, '/mots/2-lettres/avec/b/m' => true, '/mots/2-lettres/avec/b/n' => true,
        '/mots/2-lettres/avec/b/p' => true, '/mots/2-lettres/avec/b/r' => true, '/mots/2-lettres/avec/b/u' => true,
        '/mots/2-lettres/avec/b/y' => true, '/mots/2-lettres/avec/c/d' => true, '/mots/2-lettres/avec/c/e' => true,
        '/mots/2-lettres/avec/c/h' => true, '/mots/2-lettres/avec/c/i' => true, '/mots/2-lettres/avec/c/m' => true,
        '/mots/2-lettres/avec/c/o' => true, '/mots/2-lettres/avec/c/p' => true, '/mots/2-lettres/avec/c/u' => true,
        '/mots/2-lettres/avec/c/v' => true, '/mots/2-lettres/avec/d/g' => true, '/mots/2-lettres/avec/d/j' => true,
        '/mots/2-lettres/avec/d/o' => true, '/mots/2-lettres/avec/d/p' => true, '/mots/2-lettres/avec/e/g' => true,
        '/mots/2-lettres/avec/e/k' => true, '/mots/2-lettres/avec/e/l' => true, '/mots/2-lettres/avec/e/m' => true,
        '/mots/2-lettres/avec/e/p' => true, '/mots/2-lettres/avec/e/r' => true, '/mots/2-lettres/avec/e/u' => true,
        '/mots/2-lettres/avec/e/y' => true, '/mots/2-lettres/avec/f/m' => true, '/mots/2-lettres/avec/f/s' => true,
        '/mots/2-lettres/avec/g/o' => true, '/mots/2-lettres/avec/g/p' => true, '/mots/2-lettres/avec/h/i' => true,
        '/mots/2-lettres/avec/h/p' => true, '/mots/2-lettres/avec/h/s' => true, '/mots/2-lettres/avec/h/t' => true,
        '/mots/2-lettres/avec/i/j' => true, '/mots/2-lettres/avec/i/m' => true, '/mots/2-lettres/avec/i/o' => true,
        '/mots/2-lettres/avec/i/q' => true, '/mots/2-lettres/avec/i/r' => true, '/mots/2-lettres/avec/i/t' => true,
        '/mots/2-lettres/avec/i/v' => true, '/mots/2-lettres/avec/i/x' => true, '/mots/2-lettres/avec/i/y' => true,
        '/mots/2-lettres/avec/k/p' => true, '/mots/2-lettres/avec/k/u' => true, '/mots/2-lettres/avec/l/t' => true,
        '/mots/2-lettres/avec/l/u' => true, '/mots/2-lettres/avec/m/o' => true, '/mots/2-lettres/avec/m/p' => true,
        '/mots/2-lettres/avec/m/r' => true, '/mots/2-lettres/avec/m/s' => true, '/mots/2-lettres/avec/m/u' => true,
        '/mots/2-lettres/avec/o/r' => true, '/mots/2-lettres/avec/o/s' => true, '/mots/2-lettres/avec/o/t' => true,
        '/mots/2-lettres/avec/o/u' => true, '/mots/2-lettres/avec/o/y' => true, '/mots/2-lettres/avec/p/s' => true,
        '/mots/2-lettres/avec/p/u' => true, '/mots/2-lettres/avec/p/v' => true, '/mots/2-lettres/avec/q/u' => true,
        '/mots/2-lettres/avec/r/s' => true, '/mots/2-lettres/avec/r/u' => true, '/mots/2-lettres/avec/r/y' => true,
        '/mots/2-lettres/avec/s/v' => true, '/mots/2-lettres/avec/t/v' => true, '/mots/2-lettres/avec/u/v' => true,
        '/mots/3-lettres/avec/b/j' => true, '/mots/3-lettres/avec/b/k' => true, '/mots/3-lettres/avec/c/j' => true,
        '/mots/3-lettres/avec/c/k' => true, '/mots/3-lettres/avec/c/y' => true, '/mots/3-lettres/avec/c/z' => true,
        '/mots/3-lettres/avec/d/f' => true, '/mots/3-lettres/avec/d/k' => true, '/mots/3-lettres/avec/d/x' => true,
        '/mots/3-lettres/avec/f/v' => true, '/mots/3-lettres/avec/f/x' => true, '/mots/3-lettres/avec/f/y' => true,
        '/mots/3-lettres/avec/g/w' => true, '/mots/3-lettres/avec/g/x' => true, '/mots/3-lettres/avec/h/l' => true,
        '/mots/3-lettres/avec/h/n' => true, '/mots/3-lettres/avec/h/v' => true, '/mots/3-lettres/avec/h/w' => true,
        '/mots/3-lettres/avec/i/w' => true, '/mots/3-lettres/avec/j/n' => true, '/mots/3-lettres/avec/j/p' => true,
        '/mots/3-lettres/avec/j/z' => true, '/mots/3-lettres/avec/k/z' => true, '/mots/3-lettres/avec/m/q' => true,
        '/mots/3-lettres/avec/m/v' => true, '/mots/3-lettres/avec/m/w' => true, '/mots/3-lettres/avec/m/x' => true,
        '/mots/3-lettres/avec/n/r' => true, '/mots/3-lettres/avec/n/v' => true, '/mots/3-lettres/avec/n/w' => true,
        '/mots/3-lettres/avec/p/q' => true, '/mots/3-lettres/avec/p/w' => true, '/mots/3-lettres/avec/p/x' => true,
        '/mots/3-lettres/avec/p/y' => true, '/mots/3-lettres/avec/q/t' => true, '/mots/3-lettres/avec/s/v' => true,
        '/mots/3-lettres/avec/s/z' => true, '/mots/3-lettres/avec/t/w' => true, '/mots/3-lettres/avec/t/x' => true,
        '/mots/3-lettres/avec/t/y' => true, '/mots/3-lettres/avec/v/x' => true, '/mots/3-lettres/avec/w/x' => true,
        '/mots/4-lettres/avec/g/q' => true, '/mots/4-lettres/avec/g/x' => true, '/mots/4-lettres/avec/j/w' => true,
        '/mots/4-lettres/avec/j/x' => true, '/mots/4-lettres/avec/j/y' => true, '/mots/4-lettres/avec/l/q' => true,
        '/mots/4-lettres/avec/m/w' => true, '/mots/4-lettres/avec/q/w' => true, '/mots/4-lettres/avec/q/y' => true,
        '/mots/4-lettres/avec/w/z' => true, '/mots/5-lettres/avec/k/q' => true, '/mots/5-lettres/avec/k/x' => true,
        '/mots/5-lettres/avec/q/x' => true, '/mots/5-lettres/avec/w/x' => true, '/mots/6-lettres/avec/j/w' => true,
        '/mots/6-lettres/avec/w/x' => true,
    ],
    'word_list_combined' => [
        '/mots/10-lettres/commencant/b/terminant/k' => true, '/mots/10-lettres/commencant/f/terminant/y' => true,
        '/mots/10-lettres/commencant/i/terminant/k' => true, '/mots/10-lettres/commencant/j/terminant/o' => true,
        '/mots/10-lettres/commencant/j/terminant/p' => true, '/mots/10-lettres/commencant/j/terminant/y' => true,
        '/mots/10-lettres/commencant/k/terminant/m' => true, '/mots/10-lettres/commencant/k/terminant/y' => true,
        '/mots/10-lettres/commencant/m/terminant/y' => true, '/mots/10-lettres/commencant/o/terminant/p' => true,
        '/mots/10-lettres/commencant/y/terminant/o' => true, '/mots/10-lettres/commencant/y/terminant/u' => true,
        '/mots/11-lettres/commencant/c/terminant/o' => true, '/mots/11-lettres/commencant/j/terminant/c' => true,
        '/mots/11-lettres/commencant/k/terminant/o' => true, '/mots/11-lettres/commencant/k/terminant/v' => true,
        '/mots/11-lettres/commencant/m/terminant/k' => true, '/mots/11-lettres/commencant/m/terminant/u' => true,
        '/mots/11-lettres/commencant/r/terminant/k' => true, '/mots/11-lettres/commencant/t/terminant/m' => true,
        '/mots/11-lettres/commencant/t/terminant/y' => true, '/mots/12-lettres/commencant/p/terminant/y' => true,
        '/mots/12-lettres/commencant/y/terminant/i' => true, '/mots/13-lettres/commencant/b/terminant/m' => true,
        '/mots/13-lettres/commencant/i/terminant/o' => true, '/mots/14-lettres/commencant/w/terminant/g' => true,
        '/mots/15-lettres/commencant/k/terminant/n' => true, '/mots/2-lettres/commencant/c/terminant/d' => true,
        '/mots/2-lettres/commencant/c/terminant/v' => true, '/mots/2-lettres/commencant/f/terminant/m' => true,
        '/mots/2-lettres/commencant/g/terminant/d' => true, '/mots/2-lettres/commencant/l/terminant/t' => true,
        '/mots/2-lettres/commencant/m/terminant/c' => true, '/mots/2-lettres/commencant/m/terminant/r' => true,
        '/mots/2-lettres/commencant/n/terminant/b' => true, '/mots/2-lettres/commencant/p/terminant/b' => true,
        '/mots/2-lettres/commencant/p/terminant/d' => true, '/mots/2-lettres/commencant/p/terminant/k' => true,
        '/mots/2-lettres/commencant/p/terminant/m' => true, '/mots/2-lettres/commencant/q/terminant/i' => true,
        '/mots/2-lettres/commencant/q/terminant/u' => true, '/mots/2-lettres/commencant/s/terminant/r' => true,
        '/mots/2-lettres/commencant/u/terminant/a' => true, '/mots/2-lettres/commencant/v/terminant/s' => true,
        '/mots/2-lettres/commencant/w/terminant/u' => true, '/mots/2-lettres/commencant/y/terminant/i' => true,
        '/mots/2-lettres/commencant/z/terminant/a' => true, '/mots/3-lettres/commencant/a/terminant/k' => true,
        '/mots/3-lettres/commencant/a/terminant/y' => true, '/mots/3-lettres/commencant/b/terminant/w' => true,
        '/mots/3-lettres/commencant/c/terminant/h' => true, '/mots/3-lettres/commencant/c/terminant/o' => true,
        '/mots/3-lettres/commencant/c/terminant/x' => true, '/mots/3-lettres/commencant/c/terminant/y' => true,
        '/mots/3-lettres/commencant/d/terminant/g' => true, '/mots/3-lettres/commencant/d/terminant/j' => true,
        '/mots/3-lettres/commencant/e/terminant/c' => true, '/mots/3-lettres/commencant/e/terminant/p' => true,
        '/mots/3-lettres/commencant/f/terminant/b' => true, '/mots/3-lettres/commencant/f/terminant/g' => true,
        '/mots/3-lettres/commencant/f/terminant/k' => true, '/mots/3-lettres/commencant/f/terminant/p' => true,
        '/mots/3-lettres/commencant/f/terminant/y' => true, '/mots/3-lettres/commencant/f/terminant/z' => true,
        '/mots/3-lettres/commencant/g/terminant/b' => true, '/mots/3-lettres/commencant/g/terminant/o' => true,
        '/mots/3-lettres/commencant/g/terminant/p' => true, '/mots/3-lettres/commencant/i/terminant/c' => true,
        '/mots/3-lettres/commencant/i/terminant/d' => true, '/mots/3-lettres/commencant/i/terminant/g' => true,
        '/mots/3-lettres/commencant/i/terminant/h' => true, '/mots/3-lettres/commencant/i/terminant/m' => true,
        '/mots/3-lettres/commencant/j/terminant/d' => true, '/mots/3-lettres/commencant/j/terminant/i' => true,
        '/mots/3-lettres/commencant/j/terminant/p' => true, '/mots/3-lettres/commencant/j/terminant/z' => true,
        '/mots/3-lettres/commencant/k/terminant/b' => true, '/mots/3-lettres/commencant/k/terminant/d' => true,
        '/mots/3-lettres/commencant/k/terminant/e' => true, '/mots/3-lettres/commencant/k/terminant/l' => true,
        '/mots/3-lettres/commencant/m/terminant/p' => true, '/mots/3-lettres/commencant/n/terminant/b' => true,
        '/mots/3-lettres/commencant/n/terminant/g' => true, '/mots/3-lettres/commencant/n/terminant/k' => true,
        '/mots/3-lettres/commencant/o/terminant/h' => true, '/mots/3-lettres/commencant/p/terminant/d' => true,
        '/mots/3-lettres/commencant/p/terminant/g' => true, '/mots/3-lettres/commencant/q/terminant/m' => true,
        '/mots/3-lettres/commencant/q/terminant/t' => true, '/mots/3-lettres/commencant/s/terminant/b' => true,
        '/mots/3-lettres/commencant/s/terminant/f' => true, '/mots/3-lettres/commencant/s/terminant/w' => true,
        '/mots/3-lettres/commencant/s/terminant/z' => true, '/mots/3-lettres/commencant/t/terminant/z' => true,
        '/mots/3-lettres/commencant/u/terminant/f' => true, '/mots/3-lettres/commencant/u/terminant/l' => true,
        '/mots/3-lettres/commencant/u/terminant/m' => true, '/mots/3-lettres/commencant/v/terminant/d' => true,
        '/mots/3-lettres/commencant/v/terminant/h' => true, '/mots/3-lettres/commencant/v/terminant/m' => true,
        '/mots/3-lettres/commencant/w/terminant/d' => true, '/mots/3-lettres/commencant/w/terminant/h' => true,
        '/mots/3-lettres/commencant/w/terminant/i' => true, '/mots/3-lettres/commencant/w/terminant/k' => true,
        '/mots/3-lettres/commencant/w/terminant/s' => true, '/mots/3-lettres/commencant/w/terminant/u' => true,
        '/mots/3-lettres/commencant/w/terminant/x' => true, '/mots/3-lettres/commencant/y/terminant/e' => true,
        '/mots/3-lettres/commencant/z/terminant/d' => true, '/mots/3-lettres/commencant/z/terminant/k' => true,
        '/mots/3-lettres/commencant/z/terminant/y' => true, '/mots/4-lettres/commencant/a/terminant/d' => true,
        '/mots/4-lettres/commencant/a/terminant/p' => true, '/mots/4-lettres/commencant/a/terminant/v' => true,
        '/mots/4-lettres/commencant/c/terminant/q' => true, '/mots/4-lettres/commencant/d/terminant/w' => true,
        '/mots/4-lettres/commencant/e/terminant/f' => true, '/mots/4-lettres/commencant/e/terminant/x' => true,
        '/mots/4-lettres/commencant/f/terminant/f' => true, '/mots/4-lettres/commencant/g/terminant/b' => true,
        '/mots/4-lettres/commencant/g/terminant/x' => true, '/mots/4-lettres/commencant/h/terminant/k' => true,
        '/mots/4-lettres/commencant/h/terminant/m' => true, '/mots/4-lettres/commencant/i/terminant/k' => true,
        '/mots/4-lettres/commencant/i/terminant/x' => true, '/mots/4-lettres/commencant/j/terminant/h' => true,
        '/mots/4-lettres/commencant/j/terminant/x' => true, '/mots/4-lettres/commencant/j/terminant/y' => true,
        '/mots/4-lettres/commencant/k/terminant/d' => true, '/mots/4-lettres/commencant/k/terminant/h' => true,
        '/mots/4-lettres/commencant/k/terminant/m' => true, '/mots/4-lettres/commencant/k/terminant/p' => true,
        '/mots/4-lettres/commencant/k/terminant/r' => true, '/mots/4-lettres/commencant/k/terminant/u' => true,
        '/mots/4-lettres/commencant/k/terminant/y' => true, '/mots/4-lettres/commencant/l/terminant/b' => true,
        '/mots/4-lettres/commencant/l/terminant/l' => true, '/mots/4-lettres/commencant/m/terminant/f' => true,
        '/mots/4-lettres/commencant/n/terminant/b' => true, '/mots/4-lettres/commencant/n/terminant/m' => true,
        '/mots/4-lettres/commencant/n/terminant/x' => true, '/mots/4-lettres/commencant/p/terminant/b' => true,
        '/mots/4-lettres/commencant/p/terminant/z' => true, '/mots/4-lettres/commencant/q/terminant/e' => true,
        '/mots/4-lettres/commencant/q/terminant/g' => true, '/mots/4-lettres/commencant/q/terminant/y' => true,
        '/mots/4-lettres/commencant/q/terminant/z' => true, '/mots/4-lettres/commencant/r/terminant/b' => true,
        '/mots/4-lettres/commencant/s/terminant/z' => true, '/mots/4-lettres/commencant/u/terminant/f' => true,
        '/mots/4-lettres/commencant/u/terminant/l' => true, '/mots/4-lettres/commencant/u/terminant/z' => true,
        '/mots/4-lettres/commencant/v/terminant/f' => true, '/mots/4-lettres/commencant/v/terminant/g' => true,
        '/mots/4-lettres/commencant/v/terminant/k' => true, '/mots/4-lettres/commencant/v/terminant/l' => true,
        '/mots/4-lettres/commencant/w/terminant/f' => true, '/mots/4-lettres/commencant/w/terminant/g' => true,
        '/mots/4-lettres/commencant/w/terminant/m' => true, '/mots/4-lettres/commencant/w/terminant/o' => true,
        '/mots/4-lettres/commencant/w/terminant/u' => true, '/mots/4-lettres/commencant/w/terminant/z' => true,
        '/mots/4-lettres/commencant/x/terminant/a' => true, '/mots/4-lettres/commencant/y/terminant/l' => true,
        '/mots/4-lettres/commencant/y/terminant/u' => true, '/mots/4-lettres/commencant/y/terminant/x' => true,
        '/mots/4-lettres/commencant/z/terminant/h' => true, '/mots/4-lettres/commencant/z/terminant/l' => true,
        '/mots/4-lettres/commencant/z/terminant/r' => true, '/mots/5-lettres/commencant/a/terminant/g' => true,
        '/mots/5-lettres/commencant/a/terminant/v' => true, '/mots/5-lettres/commencant/a/terminant/y' => true,
        '/mots/5-lettres/commencant/b/terminant/p' => true, '/mots/5-lettres/commencant/c/terminant/q' => true,
        '/mots/5-lettres/commencant/d/terminant/b' => true, '/mots/5-lettres/commencant/e/terminant/p' => true,
        '/mots/5-lettres/commencant/e/terminant/v' => true, '/mots/5-lettres/commencant/e/terminant/y' => true,
        '/mots/5-lettres/commencant/h/terminant/b' => true, '/mots/5-lettres/commencant/h/terminant/g' => true,
        '/mots/5-lettres/commencant/i/terminant/h' => true, '/mots/5-lettres/commencant/k/terminant/f' => true,
        '/mots/5-lettres/commencant/k/terminant/g' => true, '/mots/5-lettres/commencant/k/terminant/m' => true,
        '/mots/5-lettres/commencant/k/terminant/p' => true, '/mots/5-lettres/commencant/k/terminant/x' => true,
        '/mots/5-lettres/commencant/l/terminant/m' => true, '/mots/5-lettres/commencant/m/terminant/b' => true,
        '/mots/5-lettres/commencant/n/terminant/g' => true, '/mots/5-lettres/commencant/o/terminant/h' => true,
        '/mots/5-lettres/commencant/o/terminant/k' => true, '/mots/5-lettres/commencant/o/terminant/p' => true,
        '/mots/5-lettres/commencant/o/terminant/x' => true, '/mots/5-lettres/commencant/p/terminant/w' => true,
        '/mots/5-lettres/commencant/q/terminant/u' => true, '/mots/5-lettres/commencant/s/terminant/w' => true,
        '/mots/5-lettres/commencant/u/terminant/z' => true, '/mots/5-lettres/commencant/v/terminant/g' => true,
        '/mots/5-lettres/commencant/v/terminant/y' => true, '/mots/5-lettres/commencant/w/terminant/x' => true,
        '/mots/5-lettres/commencant/w/terminant/y' => true, '/mots/5-lettres/commencant/y/terminant/m' => true,
        '/mots/5-lettres/commencant/y/terminant/u' => true, '/mots/5-lettres/commencant/y/terminant/y' => true,
        '/mots/6-lettres/commencant/a/terminant/g' => true, '/mots/6-lettres/commencant/a/terminant/h' => true,
        '/mots/6-lettres/commencant/d/terminant/b' => true, '/mots/6-lettres/commencant/e/terminant/h' => true,
        '/mots/6-lettres/commencant/f/terminant/b' => true, '/mots/6-lettres/commencant/g/terminant/f' => true,
        '/mots/6-lettres/commencant/g/terminant/h' => true, '/mots/6-lettres/commencant/g/terminant/p' => true,
        '/mots/6-lettres/commencant/h/terminant/j' => true, '/mots/6-lettres/commencant/h/terminant/p' => true,
        '/mots/6-lettres/commencant/i/terminant/g' => true, '/mots/6-lettres/commencant/j/terminant/g' => true,
        '/mots/6-lettres/commencant/j/terminant/h' => true, '/mots/6-lettres/commencant/k/terminant/c' => true,
        '/mots/6-lettres/commencant/k/terminant/g' => true, '/mots/6-lettres/commencant/k/terminant/v' => true,
        '/mots/6-lettres/commencant/k/terminant/y' => true, '/mots/6-lettres/commencant/l/terminant/b' => true,
        '/mots/6-lettres/commencant/l/terminant/k' => true, '/mots/6-lettres/commencant/l/terminant/p' => true,
        '/mots/6-lettres/commencant/m/terminant/b' => true, '/mots/6-lettres/commencant/n/terminant/b' => true,
        '/mots/6-lettres/commencant/n/terminant/h' => true, '/mots/6-lettres/commencant/o/terminant/h' => true,
        '/mots/6-lettres/commencant/o/terminant/y' => true, '/mots/6-lettres/commencant/p/terminant/y' => true,
        '/mots/6-lettres/commencant/q/terminant/g' => true, '/mots/6-lettres/commencant/r/terminant/v' => true,
        '/mots/6-lettres/commencant/s/terminant/p' => true, '/mots/6-lettres/commencant/s/terminant/w' => true,
        '/mots/6-lettres/commencant/t/terminant/p' => true, '/mots/6-lettres/commencant/w/terminant/c' => true,
        '/mots/6-lettres/commencant/w/terminant/f' => true, '/mots/6-lettres/commencant/w/terminant/y' => true,
        '/mots/6-lettres/commencant/z/terminant/c' => true, '/mots/6-lettres/commencant/z/terminant/f' => true,
        '/mots/6-lettres/commencant/z/terminant/m' => true, '/mots/7-lettres/commencant/a/terminant/y' => true,
        '/mots/7-lettres/commencant/b/terminant/b' => true, '/mots/7-lettres/commencant/b/terminant/y' => true,
        '/mots/7-lettres/commencant/d/terminant/b' => true, '/mots/7-lettres/commencant/h/terminant/y' => true,
        '/mots/7-lettres/commencant/i/terminant/k' => true, '/mots/7-lettres/commencant/i/terminant/q' => true,
        '/mots/7-lettres/commencant/j/terminant/c' => true, '/mots/7-lettres/commencant/k/terminant/f' => true,
        '/mots/7-lettres/commencant/k/terminant/j' => true, '/mots/7-lettres/commencant/k/terminant/p' => true,
        '/mots/7-lettres/commencant/k/terminant/y' => true, '/mots/7-lettres/commencant/n/terminant/y' => true,
        '/mots/7-lettres/commencant/o/terminant/d' => true, '/mots/7-lettres/commencant/o/terminant/y' => true,
        '/mots/7-lettres/commencant/q/terminant/y' => true, '/mots/7-lettres/commencant/v/terminant/y' => true,
        '/mots/7-lettres/commencant/w/terminant/f' => true, '/mots/7-lettres/commencant/y/terminant/d' => true,
        '/mots/7-lettres/commencant/y/terminant/k' => true, '/mots/7-lettres/commencant/z/terminant/m' => true,
        '/mots/8-lettres/commencant/a/terminant/y' => true, '/mots/8-lettres/commencant/b/terminant/w' => true,
        '/mots/8-lettres/commencant/f/terminant/b' => true, '/mots/8-lettres/commencant/g/terminant/k' => true,
        '/mots/8-lettres/commencant/h/terminant/c' => true, '/mots/8-lettres/commencant/h/terminant/o' => true,
        '/mots/8-lettres/commencant/h/terminant/y' => true, '/mots/8-lettres/commencant/i/terminant/k' => true,
        '/mots/8-lettres/commencant/j/terminant/c' => true, '/mots/8-lettres/commencant/k/terminant/j' => true,
        '/mots/8-lettres/commencant/m/terminant/b' => true, '/mots/8-lettres/commencant/q/terminant/m' => true,
        '/mots/8-lettres/commencant/r/terminant/b' => true, '/mots/8-lettres/commencant/u/terminant/c' => true,
        '/mots/8-lettres/commencant/v/terminant/k' => true, '/mots/8-lettres/commencant/v/terminant/y' => true,
        '/mots/8-lettres/commencant/y/terminant/y' => true, '/mots/8-lettres/commencant/z/terminant/g' => true,
        '/mots/8-lettres/commencant/z/terminant/y' => true, '/mots/9-lettres/commencant/d/terminant/k' => true,
        '/mots/9-lettres/commencant/g/terminant/k' => true, '/mots/9-lettres/commencant/h/terminant/f' => true,
        '/mots/9-lettres/commencant/i/terminant/y' => true, '/mots/9-lettres/commencant/k/terminant/h' => true,
        '/mots/9-lettres/commencant/l/terminant/v' => true, '/mots/9-lettres/commencant/n/terminant/h' => true,
        '/mots/9-lettres/commencant/s/terminant/y' => true, '/mots/9-lettres/commencant/w/terminant/u' => true,
        '/mots/9-lettres/commencant/w/terminant/x' => true, '/mots/9-lettres/commencant/y/terminant/h' => true,
        '/mots/commencant/b/terminant/j' => true, '/mots/commencant/c/terminant/j' => true,
        '/mots/commencant/d/terminant/q' => true, '/mots/commencant/f/terminant/j' => true,
        '/mots/commencant/f/terminant/q' => true, '/mots/commencant/g/terminant/w' => true,
        '/mots/commencant/i/terminant/w' => true, '/mots/commencant/m/terminant/j' => true,
        '/mots/commencant/m/terminant/v' => true, '/mots/commencant/n/terminant/w' => true,
        '/mots/commencant/o/terminant/j' => true, '/mots/commencant/o/terminant/q' => true,
        '/mots/commencant/o/terminant/w' => true, '/mots/commencant/p/terminant/v' => true,
        '/mots/commencant/q/terminant/c' => true, '/mots/commencant/q/terminant/q' => true,
        '/mots/commencant/r/terminant/q' => true, '/mots/commencant/r/terminant/w' => true,
        '/mots/commencant/s/terminant/v' => true, '/mots/commencant/t/terminant/j' => true,
        '/mots/commencant/t/terminant/q' => true, '/mots/commencant/u/terminant/b' => true,
        '/mots/commencant/u/terminant/v' => true, '/mots/commencant/v/terminant/q' => true,
        '/mots/commencant/v/terminant/v' => true, '/mots/commencant/w/terminant/l' => true,
        '/mots/commencant/x/terminant/o' => true, '/mots/commencant/x/terminant/u' => true,
        '/mots/commencant/y/terminant/p' => true, '/mots/commencant/y/terminant/q' => true,
        '/mots/commencant/y/terminant/v' => true, '/mots/commencant/z/terminant/j' => true,
        '/mots/commencant/z/terminant/q' => true,
    ],
    'word_list_combined_with_letter' => [
        '/mots/commencant/a/terminant/b/avec/i' => true, '/mots/commencant/a/terminant/c/avec/v' => true,
        '/mots/commencant/a/terminant/d/avec/k' => true, '/mots/commencant/a/terminant/h/avec/w' => true,
        '/mots/commencant/a/terminant/m/avec/j' => true, '/mots/commencant/a/terminant/m/avec/k' => true,
        '/mots/commencant/a/terminant/o/avec/w' => true, '/mots/commencant/a/terminant/o/avec/x' => true,
        '/mots/commencant/a/terminant/p/avec/k' => true, '/mots/commencant/a/terminant/r/avec/w' => true,
        '/mots/commencant/a/terminant/u/avec/k' => true, '/mots/commencant/a/terminant/y/avec/s' => true,
        '/mots/commencant/b/terminant/f/avec/d' => true, '/mots/commencant/b/terminant/f/avec/h' => true,
        '/mots/commencant/b/terminant/g/avec/z' => true, '/mots/commencant/b/terminant/h/avec/m' => true,
        '/mots/commencant/b/terminant/h/avec/z' => true, '/mots/commencant/b/terminant/i/avec/w' => true,
        '/mots/commencant/b/terminant/l/avec/w' => true, '/mots/commencant/b/terminant/m/avec/g' => true,
        '/mots/commencant/b/terminant/o/avec/y' => true, '/mots/commencant/b/terminant/o/avec/z' => true,
        '/mots/commencant/b/terminant/p/avec/k' => true, '/mots/commencant/b/terminant/p/avec/z' => true,
        '/mots/commencant/b/terminant/q/avec/e' => true, '/mots/commencant/b/terminant/q/avec/i' => true,
        '/mots/commencant/b/terminant/y/avec/h' => true, '/mots/commencant/b/terminant/y/avec/s' => true,
        '/mots/commencant/b/terminant/y/avec/t' => true, '/mots/commencant/b/terminant/y/avec/z' => true,
        '/mots/commencant/c/terminant/c/avec/z' => true, '/mots/commencant/c/terminant/h/avec/m' => true,
        '/mots/commencant/c/terminant/p/avec/g' => true, '/mots/commencant/c/terminant/v/avec/a' => true,
        '/mots/commencant/c/terminant/v/avec/o' => true, '/mots/commencant/c/terminant/w/avec/e' => true,
        '/mots/commencant/c/terminant/w/avec/r' => true, '/mots/commencant/c/terminant/x/avec/w' => true,
        '/mots/commencant/c/terminant/y/avec/k' => true, '/mots/commencant/c/terminant/y/avec/w' => true,
        '/mots/commencant/d/terminant/c/avec/j' => true, '/mots/commencant/d/terminant/d/avec/k' => true,
        '/mots/commencant/d/terminant/h/avec/g' => true, '/mots/commencant/d/terminant/m/avec/q' => true,
        '/mots/commencant/d/terminant/m/avec/z' => true, '/mots/commencant/d/terminant/o/avec/k' => true,
        '/mots/commencant/d/terminant/o/avec/x' => true, '/mots/commencant/d/terminant/p/avec/g' => true,
        '/mots/commencant/d/terminant/p/avec/h' => true, '/mots/commencant/d/terminant/p/avec/i' => true,
        '/mots/commencant/d/terminant/p/avec/m' => true, '/mots/commencant/d/terminant/p/avec/n' => true,
        '/mots/commencant/d/terminant/v/avec/e' => true, '/mots/commencant/d/terminant/v/avec/i' => true,
        '/mots/commencant/d/terminant/y/avec/g' => true, '/mots/commencant/d/terminant/y/avec/z' => true,
        '/mots/commencant/e/terminant/c/avec/b' => true, '/mots/commencant/e/terminant/c/avec/j' => true,
        '/mots/commencant/e/terminant/f/avec/b' => true, '/mots/commencant/e/terminant/g/avec/c' => true,
        '/mots/commencant/e/terminant/m/avec/k' => true, '/mots/commencant/e/terminant/m/avec/v' => true,
        '/mots/commencant/e/terminant/o/avec/f' => true, '/mots/commencant/e/terminant/o/avec/k' => true,
        '/mots/commencant/e/terminant/u/avec/y' => true, '/mots/commencant/e/terminant/y/avec/d' => true,
        '/mots/commencant/e/terminant/y/avec/i' => true, '/mots/commencant/f/terminant/c/avec/p' => true,
        '/mots/commencant/f/terminant/c/avec/v' => true, '/mots/commencant/f/terminant/h/avec/y' => true,
        '/mots/commencant/f/terminant/l/avec/j' => true, '/mots/commencant/f/terminant/m/avec/h' => true,
        '/mots/commencant/f/terminant/m/avec/x' => true, '/mots/commencant/f/terminant/y/avec/c' => true,
        '/mots/commencant/f/terminant/y/avec/d' => true, '/mots/commencant/f/terminant/y/avec/h' => true,
        '/mots/commencant/f/terminant/y/avec/m' => true, '/mots/commencant/f/terminant/y/avec/w' => true,
        '/mots/commencant/g/terminant/d/avec/k' => true, '/mots/commencant/g/terminant/g/avec/u' => true,
        '/mots/commencant/g/terminant/k/avec/b' => true, '/mots/commencant/g/terminant/k/avec/d' => true,
        '/mots/commencant/g/terminant/l/avec/z' => true, '/mots/commencant/g/terminant/o/avec/j' => true,
        '/mots/commencant/g/terminant/p/avec/s' => true, '/mots/commencant/g/terminant/y/avec/m' => true,
        '/mots/commencant/g/terminant/y/avec/s' => true, '/mots/commencant/g/terminant/y/avec/u' => true,
        '/mots/commencant/g/terminant/y/avec/w' => true, '/mots/commencant/g/terminant/y/avec/z' => true,
        '/mots/commencant/h/terminant/a/avec/w' => true, '/mots/commencant/h/terminant/b/avec/u' => true,
        '/mots/commencant/h/terminant/c/avec/k' => true, '/mots/commencant/h/terminant/h/avec/v' => true,
        '/mots/commencant/h/terminant/h/avec/w' => true, '/mots/commencant/h/terminant/k/avec/m' => true,
        '/mots/commencant/h/terminant/o/avec/b' => true, '/mots/commencant/h/terminant/o/avec/p' => true,
        '/mots/commencant/h/terminant/p/avec/e' => true, '/mots/commencant/h/terminant/p/avec/t' => true,
        '/mots/commencant/h/terminant/u/avec/k' => true, '/mots/commencant/h/terminant/y/avec/p' => true,
        '/mots/commencant/h/terminant/y/avec/s' => true, '/mots/commencant/i/terminant/c/avec/e' => true,
        '/mots/commencant/i/terminant/k/avec/a' => true, '/mots/commencant/i/terminant/u/avec/j' => true,
        '/mots/commencant/i/terminant/y/avec/x' => true, '/mots/commencant/j/terminant/d/avec/g' => true,
        '/mots/commencant/j/terminant/d/avec/w' => true, '/mots/commencant/j/terminant/i/avec/w' => true,
        '/mots/commencant/j/terminant/k/avec/n' => true, '/mots/commencant/j/terminant/m/avec/n' => true,
        '/mots/commencant/j/terminant/o/avec/k' => true, '/mots/commencant/j/terminant/p/avec/m' => true,
        '/mots/commencant/j/terminant/x/avec/k' => true, '/mots/commencant/j/terminant/y/avec/z' => true,
        '/mots/commencant/k/terminant/b/avec/n' => true, '/mots/commencant/k/terminant/c/avec/u' => true,
        '/mots/commencant/k/terminant/d/avec/b' => true, '/mots/commencant/k/terminant/f/avec/d' => true,
        '/mots/commencant/k/terminant/f/avec/w' => true, '/mots/commencant/k/terminant/h/avec/f' => true,
        '/mots/commencant/k/terminant/h/avec/z' => true, '/mots/commencant/k/terminant/i/avec/v' => true,
        '/mots/commencant/k/terminant/l/avec/w' => true, '/mots/commencant/k/terminant/n/avec/x' => true,
        '/mots/commencant/k/terminant/o/avec/p' => true, '/mots/commencant/k/terminant/o/avec/y' => true,
        '/mots/commencant/k/terminant/p/avec/o' => true, '/mots/commencant/k/terminant/u/avec/f' => true,
        '/mots/commencant/k/terminant/u/avec/v' => true, '/mots/commencant/k/terminant/z/avec/g' => true,
        '/mots/commencant/l/terminant/c/avec/f' => true, '/mots/commencant/l/terminant/c/avec/p' => true,
        '/mots/commencant/l/terminant/c/avec/y' => true, '/mots/commencant/l/terminant/g/avec/y' => true,
        '/mots/commencant/l/terminant/h/avec/d' => true, '/mots/commencant/l/terminant/h/avec/r' => true,
        '/mots/commencant/l/terminant/h/avec/s' => true, '/mots/commencant/l/terminant/i/avec/j' => true,
        '/mots/commencant/l/terminant/l/avec/q' => true, '/mots/commencant/l/terminant/l/avec/w' => true,
        '/mots/commencant/l/terminant/m/avec/j' => true, '/mots/commencant/l/terminant/m/avec/k' => true,
        '/mots/commencant/l/terminant/o/avec/x' => true, '/mots/commencant/l/terminant/o/avec/y' => true,
        '/mots/commencant/l/terminant/r/avec/w' => true, '/mots/commencant/l/terminant/u/avec/z' => true,
        '/mots/commencant/m/terminant/a/avec/w' => true, '/mots/commencant/m/terminant/b/avec/n' => true,
        '/mots/commencant/m/terminant/c/avec/j' => true, '/mots/commencant/m/terminant/f/avec/j' => true,
        '/mots/commencant/m/terminant/g/avec/j' => true, '/mots/commencant/m/terminant/h/avec/z' => true,
        '/mots/commencant/m/terminant/k/avec/d' => true, '/mots/commencant/m/terminant/k/avec/j' => true,
        '/mots/commencant/m/terminant/l/avec/w' => true, '/mots/commencant/m/terminant/m/avec/q' => true,
        '/mots/commencant/n/terminant/b/avec/q' => true, '/mots/commencant/n/terminant/c/avec/u' => true,
        '/mots/commencant/n/terminant/d/avec/j' => true, '/mots/commencant/n/terminant/k/avec/y' => true,
        '/mots/commencant/n/terminant/l/avec/x' => true, '/mots/commencant/n/terminant/l/avec/z' => true,
        '/mots/commencant/n/terminant/m/avec/z' => true, '/mots/commencant/n/terminant/o/avec/j' => true,
        '/mots/commencant/n/terminant/o/avec/k' => true, '/mots/commencant/n/terminant/o/avec/y' => true,
        '/mots/commencant/n/terminant/q/avec/d' => true, '/mots/commencant/n/terminant/q/avec/e' => true,
        '/mots/commencant/n/terminant/u/avec/j' => true, '/mots/commencant/n/terminant/w/avec/g' => true,
        '/mots/commencant/n/terminant/w/avec/k' => true, '/mots/commencant/o/terminant/a/avec/w' => true,
        '/mots/commencant/o/terminant/d/avec/z' => true, '/mots/commencant/o/terminant/f/avec/g' => true,
        '/mots/commencant/o/terminant/h/avec/a' => true, '/mots/commencant/o/terminant/k/avec/g' => true,
        '/mots/commencant/o/terminant/k/avec/m' => true, '/mots/commencant/o/terminant/k/avec/y' => true,
        '/mots/commencant/o/terminant/o/avec/b' => true, '/mots/commencant/o/terminant/o/avec/d' => true,
        '/mots/commencant/o/terminant/o/avec/p' => true, '/mots/commencant/o/terminant/u/avec/k' => true,
        '/mots/commencant/p/terminant/a/avec/w' => true, '/mots/commencant/p/terminant/b/avec/c' => true,
        '/mots/commencant/p/terminant/b/avec/i' => true, '/mots/commencant/p/terminant/f/avec/k' => true,
        '/mots/commencant/p/terminant/h/avec/r' => true, '/mots/commencant/p/terminant/k/avec/j' => true,
        '/mots/commencant/p/terminant/l/avec/z' => true, '/mots/commencant/p/terminant/o/avec/j' => true,
        '/mots/commencant/p/terminant/o/avec/x' => true, '/mots/commencant/p/terminant/p/avec/m' => true,
        '/mots/commencant/p/terminant/y/avec/x' => true, '/mots/commencant/q/terminant/a/avec/k' => true,
        '/mots/commencant/q/terminant/e/avec/w' => true, '/mots/commencant/q/terminant/f/avec/z' => true,
        '/mots/commencant/q/terminant/h/avec/o' => true, '/mots/commencant/q/terminant/k/avec/i' => true,
        '/mots/commencant/q/terminant/l/avec/z' => true, '/mots/commencant/q/terminant/n/avec/w' => true,
        '/mots/commencant/q/terminant/y/avec/c' => true, '/mots/commencant/r/terminant/b/avec/g' => true,
        '/mots/commencant/r/terminant/b/avec/h' => true, '/mots/commencant/r/terminant/c/avec/y' => true,
        '/mots/commencant/r/terminant/d/avec/y' => true, '/mots/commencant/r/terminant/g/avec/h' => true,
        '/mots/commencant/r/terminant/h/avec/k' => true, '/mots/commencant/r/terminant/h/avec/n' => true,
        '/mots/commencant/r/terminant/h/avec/p' => true, '/mots/commencant/r/terminant/k/avec/d' => true,
        '/mots/commencant/r/terminant/l/avec/k' => true, '/mots/commencant/r/terminant/l/avec/z' => true,
        '/mots/commencant/r/terminant/m/avec/q' => true, '/mots/commencant/r/terminant/n/avec/k' => true,
        '/mots/commencant/r/terminant/o/avec/f' => true, '/mots/commencant/r/terminant/p/avec/s' => true,
        '/mots/commencant/r/terminant/v/avec/d' => true, '/mots/commencant/r/terminant/v/avec/t' => true,
        '/mots/commencant/r/terminant/y/avec/s' => true, '/mots/commencant/r/terminant/y/avec/w' => true,
        '/mots/commencant/s/terminant/b/avec/d' => true, '/mots/commencant/s/terminant/b/avec/k' => true,
        '/mots/commencant/s/terminant/g/avec/j' => true, '/mots/commencant/s/terminant/h/avec/q' => true,
        '/mots/commencant/s/terminant/h/avec/y' => true, '/mots/commencant/s/terminant/k/avec/j' => true,
        '/mots/commencant/s/terminant/m/avec/f' => true, '/mots/commencant/s/terminant/m/avec/k' => true,
        '/mots/commencant/s/terminant/m/avec/v' => true, '/mots/commencant/s/terminant/o/avec/q' => true,
        '/mots/commencant/s/terminant/p/avec/g' => true, '/mots/commencant/s/terminant/p/avec/w' => true,
        '/mots/commencant/s/terminant/y/avec/c' => true, '/mots/commencant/s/terminant/y/avec/d' => true,
        '/mots/commencant/s/terminant/y/avec/v' => true, '/mots/commencant/s/terminant/y/avec/z' => true,
        '/mots/commencant/t/terminant/b/avec/i' => true, '/mots/commencant/t/terminant/d/avec/w' => true,
        '/mots/commencant/t/terminant/d/avec/x' => true, '/mots/commencant/t/terminant/h/avec/m' => true,
        '/mots/commencant/t/terminant/k/avec/d' => true, '/mots/commencant/t/terminant/k/avec/g' => true,
        '/mots/commencant/t/terminant/k/avec/s' => true, '/mots/commencant/t/terminant/o/avec/y' => true,
        '/mots/commencant/t/terminant/r/avec/z' => true, '/mots/commencant/t/terminant/u/avec/k' => true,
        '/mots/commencant/t/terminant/v/avec/a' => true, '/mots/commencant/t/terminant/v/avec/g' => true,
        '/mots/commencant/t/terminant/y/avec/g' => true, '/mots/commencant/t/terminant/y/avec/k' => true,
        '/mots/commencant/t/terminant/y/avec/x' => true, '/mots/commencant/u/terminant/g/avec/c' => true,
        '/mots/commencant/u/terminant/h/avec/g' => true, '/mots/commencant/u/terminant/h/avec/m' => true,
        '/mots/commencant/u/terminant/m/avec/h' => true, '/mots/commencant/u/terminant/o/avec/a' => true,
        '/mots/commencant/u/terminant/u/avec/b' => true, '/mots/commencant/u/terminant/u/avec/d' => true,
        '/mots/commencant/u/terminant/u/avec/n' => true, '/mots/commencant/u/terminant/u/avec/s' => true,
        '/mots/commencant/v/terminant/f/avec/b' => true, '/mots/commencant/v/terminant/l/avec/x' => true,
        '/mots/commencant/v/terminant/m/avec/b' => true, '/mots/commencant/v/terminant/o/avec/d' => true,
        '/mots/commencant/v/terminant/p/avec/a' => true, '/mots/commencant/v/terminant/u/avec/y' => true,
        '/mots/commencant/v/terminant/y/avec/t' => true, '/mots/commencant/v/terminant/y/avec/z' => true,
        '/mots/commencant/w/terminant/a/avec/u' => true, '/mots/commencant/w/terminant/d/avec/n' => true,
        '/mots/commencant/w/terminant/f/avec/h' => true, '/mots/commencant/w/terminant/h/avec/a' => true,
        '/mots/commencant/w/terminant/h/avec/l' => true, '/mots/commencant/w/terminant/m/avec/b' => true,
        '/mots/commencant/w/terminant/m/avec/d' => true, '/mots/commencant/w/terminant/m/avec/g' => true,
        '/mots/commencant/w/terminant/m/avec/k' => true, '/mots/commencant/w/terminant/m/avec/p' => true,
        '/mots/commencant/w/terminant/p/avec/i' => true, '/mots/commencant/w/terminant/u/avec/k' => true,
        '/mots/commencant/w/terminant/y/avec/b' => true, '/mots/commencant/w/terminant/y/avec/e' => true,
        '/mots/commencant/x/terminant/l/avec/m' => true, '/mots/commencant/y/terminant/a/avec/z' => true,
        '/mots/commencant/y/terminant/h/avec/a' => true, '/mots/commencant/y/terminant/i/avec/q' => true,
        '/mots/commencant/y/terminant/i/avec/v' => true, '/mots/commencant/y/terminant/i/avec/z' => true,
        '/mots/commencant/y/terminant/k/avec/l' => true, '/mots/commencant/y/terminant/m/avec/o' => true,
        '/mots/commencant/y/terminant/o/avec/e' => true, '/mots/commencant/y/terminant/r/avec/g' => true,
        '/mots/commencant/z/terminant/b/avec/a' => true, '/mots/commencant/z/terminant/b/avec/m' => true,
        '/mots/commencant/z/terminant/b/avec/u' => true, '/mots/commencant/z/terminant/c/avec/e' => true,
        '/mots/commencant/z/terminant/c/avec/u' => true, '/mots/commencant/z/terminant/g/avec/r' => true,
        '/mots/commencant/z/terminant/h/avec/d' => true, '/mots/commencant/z/terminant/h/avec/n' => true,
        '/mots/commencant/z/terminant/h/avec/w' => true, '/mots/commencant/z/terminant/k/avec/d' => true,
        '/mots/commencant/z/terminant/k/avec/p' => true, '/mots/commencant/z/terminant/l/avec/y' => true,
        '/mots/commencant/z/terminant/n/avec/f' => true, '/mots/commencant/z/terminant/o/avec/s' => true,
        '/mots/commencant/z/terminant/p/avec/i' => true, '/mots/commencant/z/terminant/p/avec/u' => true,
        '/mots/commencant/z/terminant/u/avec/k' => true, '/mots/commencant/z/terminant/u/avec/n' => true,
        '/mots/commencant/z/terminant/y/avec/e' => true, '/mots/commencant/z/terminant/y/avec/t' => true,
    ],
    'word_list_commencant_with_letter' => [
        '/mots/commencant/u/avec/j' => true, '/mots/commencant/w/avec/j' => true,
        '/mots/commencant/x/avec/z' => true, '/mots/commencant/y/avec/x' => true,
    ],
    'word_list_position' => [
        '/mots/13-lettres/position/10/w' => true, '/mots/15-lettres/position/10/w' => true,
    ],
    'word_list_terminant' => [
        '/mots/terminant/aada' => true, '/mots/terminant/aaku' => true, '/mots/terminant/aat' => true,
        '/mots/terminant/abem' => true, '/mots/terminant/abu' => true, '/mots/terminant/abza' => true,
        '/mots/terminant/aci' => true, '/mots/terminant/acq' => true, '/mots/terminant/adh' => true,
        '/mots/terminant/adsl' => true, '/mots/terminant/adza' => true, '/mots/terminant/aeis' => true,
        '/mots/terminant/afan' => true, '/mots/terminant/agor' => true, '/mots/terminant/aham' => true,
        '/mots/terminant/aido' => true, '/mots/terminant/ainu' => true, '/mots/terminant/aio' => true,
        '/mots/terminant/ajar' => true, '/mots/terminant/ajd' => true, '/mots/terminant/akal' => true,
        '/mots/terminant/akla' => true, '/mots/terminant/aly' => true, '/mots/terminant/amut' => true,
        '/mots/terminant/anki' => true, '/mots/terminant/anoa' => true, '/mots/terminant/aoi' => true,
        '/mots/terminant/aoo' => true, '/mots/terminant/apex' => true, '/mots/terminant/aph' => true,
        '/mots/terminant/areu' => true, '/mots/terminant/arrs' => true, '/mots/terminant/artt' => true,
        '/mots/terminant/asan' => true, '/mots/terminant/auer' => true, '/mots/terminant/aund' => true,
        '/mots/terminant/avus' => true, '/mots/terminant/awn' => true, '/mots/terminant/azoo' => true,
        '/mots/terminant/azs' => true, '/mots/terminant/bafs' => true, '/mots/terminant/bam' => true,
        '/mots/terminant/bbot' => true, '/mots/terminant/bca' => true, '/mots/terminant/bcp' => true,
        '/mots/terminant/bdes' => true, '/mots/terminant/beez' => true, '/mots/terminant/bex' => true,
        '/mots/terminant/bgen' => true, '/mots/terminant/binz' => true, '/mots/terminant/blob' => true,
        '/mots/terminant/bnc' => true, '/mots/terminant/bns' => true, '/mots/terminant/bocs' => true,
        '/mots/terminant/bogs' => true, '/mots/terminant/brns' => true, '/mots/terminant/brrr' => true,
        '/mots/terminant/bt' => true, '/mots/terminant/bube' => true, '/mots/terminant/bugs' => true,
        '/mots/terminant/bulk' => true, '/mots/terminant/bulu' => true, '/mots/terminant/bz' => true,
        '/mots/terminant/caru' => true, '/mots/terminant/cbg' => true, '/mots/terminant/cbm' => true,
        '/mots/terminant/ccp' => true, '/mots/terminant/cde' => true, '/mots/terminant/cdg' => true,
        '/mots/terminant/cdi' => true, '/mots/terminant/cds' => true, '/mots/terminant/cdt' => true,
        '/mots/terminant/ceft' => true, '/mots/terminant/cfa' => true, '/mots/terminant/cff' => true,
        '/mots/terminant/cgt' => true, '/mots/terminant/chae' => true, '/mots/terminant/cjob' => true,
        '/mots/terminant/cl' => true, '/mots/terminant/cm' => true, '/mots/terminant/cmsa' => true,
        '/mots/terminant/cnaf' => true, '/mots/terminant/cns' => true, '/mots/terminant/cobs' => true,
        '/mots/terminant/cofa' => true, '/mots/terminant/coix' => true, '/mots/terminant/cpas' => true,
        '/mots/terminant/cpe' => true, '/mots/terminant/cpu' => true, '/mots/terminant/crag' => true,
        '/mots/terminant/crds' => true, '/mots/terminant/css' => true, '/mots/terminant/ctoc' => true,
        '/mots/terminant/cv' => true, '/mots/terminant/cx' => true, '/mots/terminant/daws' => true,
        '/mots/terminant/dba' => true, '/mots/terminant/dc' => true, '/mots/terminant/ddax' => true,
        '/mots/terminant/dde' => true, '/mots/terminant/ddhi' => true, '/mots/terminant/ddt' => true,
        '/mots/terminant/dect' => true, '/mots/terminant/degs' => true, '/mots/terminant/deh' => true,
        '/mots/terminant/deps' => true, '/mots/terminant/dept' => true, '/mots/terminant/devs' => true,
        '/mots/terminant/deys' => true, '/mots/terminant/dhad' => true, '/mots/terminant/did' => true,
        '/mots/terminant/dixe' => true, '/mots/terminant/djoc' => true, '/mots/terminant/dl' => true,
        '/mots/terminant/dlr' => true, '/mots/terminant/dlt' => true, '/mots/terminant/dn' => true,
        '/mots/terminant/dns' => true, '/mots/terminant/donf' => true, '/mots/terminant/dops' => true,
        '/mots/terminant/dpi' => true, '/mots/terminant/dpin' => true, '/mots/terminant/dr' => true,
        '/mots/terminant/dsat' => true, '/mots/terminant/dsc' => true, '/mots/terminant/dsm' => true,
        '/mots/terminant/dtc' => true, '/mots/terminant/duos' => true, '/mots/terminant/dv' => true,
        '/mots/terminant/dzos' => true, '/mots/terminant/eah' => true, '/mots/terminant/eaie' => true,
        '/mots/terminant/eara' => true, '/mots/terminant/earl' => true, '/mots/terminant/ebac' => true,
        '/mots/terminant/ebox' => true, '/mots/terminant/edma' => true, '/mots/terminant/egt' => true,
        '/mots/terminant/eha' => true, '/mots/terminant/ehol' => true, '/mots/terminant/ehu' => true,
        '/mots/terminant/eix' => true, '/mots/terminant/eji' => true, '/mots/terminant/ejs' => true,
        '/mots/terminant/elof' => true, '/mots/terminant/embo' => true, '/mots/terminant/entz' => true,
        '/mots/terminant/epc' => true, '/mots/terminant/esem' => true, '/mots/terminant/esy' => true,
        '/mots/terminant/etva' => true, '/mots/terminant/eunt' => true, '/mots/terminant/eyou' => true,
        '/mots/terminant/fafs' => true, '/mots/terminant/faqs' => true, '/mots/terminant/fd' => true,
        '/mots/terminant/fdt' => true, '/mots/terminant/fems' => true, '/mots/terminant/feo' => true,
        '/mots/terminant/fft' => true, '/mots/terminant/fifa' => true, '/mots/terminant/fifs' => true,
        '/mots/terminant/fius' => true, '/mots/terminant/fka' => true, '/mots/terminant/fkif' => true,
        '/mots/terminant/fm' => true, '/mots/terminant/fnal' => true, '/mots/terminant/fob' => true,
        '/mots/terminant/fogs' => true, '/mots/terminant/fote' => true, '/mots/terminant/ftp' => true,
        '/mots/terminant/fumi' => true, '/mots/terminant/funs' => true, '/mots/terminant/gags' => true,
        '/mots/terminant/gaps' => true, '/mots/terminant/gayn' => true, '/mots/terminant/gban' => true,
        '/mots/terminant/gbd' => true, '/mots/terminant/gbra' => true, '/mots/terminant/gcp' => true,
        '/mots/terminant/geb' => true, '/mots/terminant/gede' => true, '/mots/terminant/gesh' => true,
        '/mots/terminant/gf' => true, '/mots/terminant/ggis' => true, '/mots/terminant/ggle' => true,
        '/mots/terminant/gheh' => true, '/mots/terminant/ghez' => true, '/mots/terminant/gini' => true,
        '/mots/terminant/giol' => true, '/mots/terminant/gkha' => true, '/mots/terminant/gm' => true,
        '/mots/terminant/gml' => true, '/mots/terminant/gnak' => true, '/mots/terminant/gnu' => true,
        '/mots/terminant/gnus' => true, '/mots/terminant/gogs' => true, '/mots/terminant/goim' => true,
        '/mots/terminant/gony' => true, '/mots/terminant/gosh' => true, '/mots/terminant/gows' => true,
        '/mots/terminant/goys' => true, '/mots/terminant/gpl' => true, '/mots/terminant/gps' => true,
        '/mots/terminant/grrr' => true, '/mots/terminant/gsm' => true, '/mots/terminant/gv' => true,
        '/mots/terminant/gyma' => true, '/mots/terminant/haan' => true, '/mots/terminant/hahe' => true,
        '/mots/terminant/haix' => true, '/mots/terminant/haja' => true, '/mots/terminant/hang' => true,
        '/mots/terminant/hato' => true, '/mots/terminant/hayn' => true, '/mots/terminant/hb' => true,
        '/mots/terminant/hcp' => true, '/mots/terminant/heci' => true, '/mots/terminant/hein' => true,
        '/mots/terminant/heit' => true, '/mots/terminant/hfa' => true, '/mots/terminant/hiya' => true,
        '/mots/terminant/hlab' => true, '/mots/terminant/hlm' => true, '/mots/terminant/hoax' => true,
        '/mots/terminant/homs' => true, '/mots/terminant/hrys' => true, '/mots/terminant/hse' => true,
        '/mots/terminant/htie' => true, '/mots/terminant/huau' => true, '/mots/terminant/hubs' => true,
        '/mots/terminant/huot' => true, '/mots/terminant/iao' => true, '/mots/terminant/iaq' => true,
        '/mots/terminant/iark' => true, '/mots/terminant/idh' => true, '/mots/terminant/idj' => true,
        '/mots/terminant/idt' => true, '/mots/terminant/idze' => true, '/mots/terminant/ieff' => true,
        '/mots/terminant/iepa' => true, '/mots/terminant/iglo' => true, '/mots/terminant/ildo' => true,
        '/mots/terminant/impi' => true, '/mots/terminant/inni' => true, '/mots/terminant/ipsy' => true,
        '/mots/terminant/irk' => true, '/mots/terminant/irm' => true, '/mots/terminant/itf' => true,
        '/mots/terminant/iud' => true, '/mots/terminant/iyar' => true, '/mots/terminant/izz' => true,
        '/mots/terminant/jamo' => true, '/mots/terminant/japs' => true, '/mots/terminant/jawi' => true,
        '/mots/terminant/jban' => true, '/mots/terminant/jedi' => true, '/mots/terminant/jobs' => true,
        '/mots/terminant/jra' => true, '/mots/terminant/jri' => true, '/mots/terminant/kaca' => true,
        '/mots/terminant/kah' => true, '/mots/terminant/kaid' => true, '/mots/terminant/kano' => true,
        '/mots/terminant/kapu' => true, '/mots/terminant/keas' => true, '/mots/terminant/kids' => true,
        '/mots/terminant/kki' => true, '/mots/terminant/kko' => true, '/mots/terminant/kmal' => true,
        '/mots/terminant/kmu' => true, '/mots/terminant/koas' => true, '/mots/terminant/kobs' => true,
        '/mots/terminant/koff' => true, '/mots/terminant/komi' => true, '/mots/terminant/koon' => true,
        '/mots/terminant/kops' => true, '/mots/terminant/kouf' => true, '/mots/terminant/kpa' => true,
        '/mots/terminant/krus' => true, '/mots/terminant/ksi' => true, '/mots/terminant/kton' => true,
        '/mots/terminant/kuia' => true, '/mots/terminant/kuko' => true, '/mots/terminant/kyr' => true,
        '/mots/terminant/kyus' => true, '/mots/terminant/lads' => true, '/mots/terminant/lbol' => true,
        '/mots/terminant/lcol' => true, '/mots/terminant/levs' => true, '/mots/terminant/lg' => true,
        '/mots/terminant/lgen' => true, '/mots/terminant/lnau' => true, '/mots/terminant/lnet' => true,
        '/mots/terminant/lnoi' => true, '/mots/terminant/loam' => true, '/mots/terminant/lods' => true,
        '/mots/terminant/loix' => true, '/mots/terminant/loke' => true, '/mots/terminant/lri' => true,
        '/mots/terminant/lrus' => true, '/mots/terminant/lsch' => true, '/mots/terminant/lsf' => true,
        '/mots/terminant/luen' => true, '/mots/terminant/lyk' => true, '/mots/terminant/lynx' => true,
        '/mots/terminant/lz' => true, '/mots/terminant/maos' => true, '/mots/terminant/mbul' => true,
        '/mots/terminant/mcdo' => true, '/mots/terminant/mdre' => true, '/mots/terminant/meuh' => true,
        '/mots/terminant/meum' => true, '/mots/terminant/mf' => true, '/mots/terminant/mgen' => true,
        '/mots/terminant/mh' => true, '/mots/terminant/mhun' => true, '/mots/terminant/miam' => true,
        '/mots/terminant/mips' => true, '/mots/terminant/mka' => true, '/mots/terminant/mli' => true,
        '/mots/terminant/mmm' => true, '/mots/terminant/mnt' => true, '/mots/terminant/moas' => true,
        '/mots/terminant/mofo' => true, '/mots/terminant/moh' => true, '/mots/terminant/mong' => true,
        '/mots/terminant/mrc' => true, '/mots/terminant/mre' => true, '/mots/terminant/mst' => true,
        '/mots/terminant/mtex' => true, '/mots/terminant/mtf' => true, '/mots/terminant/mtp' => true,
        '/mots/terminant/mts' => true, '/mots/terminant/mugs' => true, '/mots/terminant/mw' => true,
        '/mots/terminant/mwe' => true, '/mots/terminant/myre' => true, '/mots/terminant/nah' => true,
        '/mots/terminant/nary' => true, '/mots/terminant/nav' => true, '/mots/terminant/nb' => true,
        '/mots/terminant/nbou' => true, '/mots/terminant/ncb' => true, '/mots/terminant/ndt' => true,
        '/mots/terminant/nebe' => true, '/mots/terminant/nefe' => true, '/mots/terminant/negs' => true,
        '/mots/terminant/nems' => true, '/mots/terminant/nex' => true, '/mots/terminant/nids' => true,
        '/mots/terminant/nija' => true, '/mots/terminant/njac' => true, '/mots/terminant/nkan' => true,
        '/mots/terminant/nmer' => true, '/mots/terminant/nobi' => true, '/mots/terminant/noer' => true,
        '/mots/terminant/nok' => true, '/mots/terminant/np' => true, '/mots/terminant/nshi' => true,
        '/mots/terminant/nuds' => true, '/mots/terminant/nwin' => true, '/mots/terminant/nyos' => true,
        '/mots/terminant/oage' => true, '/mots/terminant/oatl' => true, '/mots/terminant/oci' => true,
        '/mots/terminant/oea' => true, '/mots/terminant/oeli' => true, '/mots/terminant/oeri' => true,
        '/mots/terminant/ohl' => true, '/mots/terminant/ohor' => true, '/mots/terminant/okei' => true,
        '/mots/terminant/oket' => true, '/mots/terminant/okio' => true, '/mots/terminant/oled' => true,
        '/mots/terminant/onek' => true, '/mots/terminant/opex' => true, '/mots/terminant/oph' => true,
        '/mots/terminant/oryx' => true, '/mots/terminant/osef' => true, '/mots/terminant/osik' => true,
        '/mots/terminant/osk' => true, '/mots/terminant/outt' => true, '/mots/terminant/oym' => true,
        '/mots/terminant/ozi' => true, '/mots/terminant/ozou' => true, '/mots/terminant/pafs' => true,
        '/mots/terminant/paix' => true, '/mots/terminant/pb' => true, '/mots/terminant/pcb' => true,
        '/mots/terminant/pdf' => true, '/mots/terminant/pdts' => true, '/mots/terminant/peg' => true,
        '/mots/terminant/peos' => true, '/mots/terminant/pfr' => true, '/mots/terminant/pfut' => true,
        '/mots/terminant/phu' => true, '/mots/terminant/phy' => true, '/mots/terminant/piu' => true,
        '/mots/terminant/pkoi' => true, '/mots/terminant/plc' => true, '/mots/terminant/plle' => true,
        '/mots/terminant/plop' => true, '/mots/terminant/pme' => true, '/mots/terminant/pn' => true,
        '/mots/terminant/pnyx' => true, '/mots/terminant/pouh' => true, '/mots/terminant/ppar' => true,
        '/mots/terminant/ppi' => true, '/mots/terminant/pqc' => true, '/mots/terminant/pset' => true,
        '/mots/terminant/pst' => true, '/mots/terminant/psys' => true, '/mots/terminant/pulk' => true,
        '/mots/terminant/pvd' => true, '/mots/terminant/pyx' => true, '/mots/terminant/qadi' => true,
        '/mots/terminant/qaf' => true, '/mots/terminant/qats' => true, '/mots/terminant/qch' => true,
        '/mots/terminant/qe' => true, '/mots/terminant/qins' => true, '/mots/terminant/qis' => true,
        '/mots/terminant/qof' => true, '/mots/terminant/qomi' => true, '/mots/terminant/qp' => true,
        '/mots/terminant/qqn' => true, '/mots/terminant/qta' => true, '/mots/terminant/qun' => true,
        '/mots/terminant/qus' => true, '/mots/terminant/rcq' => true, '/mots/terminant/rgh' => true,
        '/mots/terminant/rgn' => true, '/mots/terminant/rh' => true, '/mots/terminant/rid' => true,
        '/mots/terminant/rind' => true, '/mots/terminant/rkha' => true, '/mots/terminant/rndi' => true,
        '/mots/terminant/robs' => true, '/mots/terminant/rok' => true, '/mots/terminant/rpc' => true,
        '/mots/terminant/rrco' => true, '/mots/terminant/rrem' => true, '/mots/terminant/rsk' => true,
        '/mots/terminant/rtse' => true, '/mots/terminant/rtv' => true, '/mots/terminant/rump' => true,
        '/mots/terminant/ruz' => true, '/mots/terminant/ryak' => true, '/mots/terminant/ryin' => true,
        '/mots/terminant/sall' => true, '/mots/terminant/sb' => true, '/mots/terminant/sbn' => true,
        '/mots/terminant/sbos' => true, '/mots/terminant/sbot' => true, '/mots/terminant/sct' => true,
        '/mots/terminant/sd' => true, '/mots/terminant/sda' => true, '/mots/terminant/sdf' => true,
        '/mots/terminant/sdt' => true, '/mots/terminant/sea' => true, '/mots/terminant/seut' => true,
        '/mots/terminant/sfi' => true, '/mots/terminant/sg' => true, '/mots/terminant/sgt' => true,
        '/mots/terminant/shui' => true, '/mots/terminant/sisi' => true, '/mots/terminant/skea' => true,
        '/mots/terminant/slev' => true, '/mots/terminant/slt' => true, '/mots/terminant/sms' => true,
        '/mots/terminant/soas' => true, '/mots/terminant/soba' => true, '/mots/terminant/soef' => true,
        '/mots/terminant/sont' => true, '/mots/terminant/spdg' => true, '/mots/terminant/sqn' => true,
        '/mots/terminant/sri' => true, '/mots/terminant/srol' => true, '/mots/terminant/ssh' => true,
        '/mots/terminant/ssl' => true, '/mots/terminant/sst' => true, '/mots/terminant/stys' => true,
        '/mots/terminant/sucs' => true, '/mots/terminant/sumi' => true, '/mots/terminant/suve' => true,
        '/mots/terminant/svan' => true, '/mots/terminant/sw' => true, '/mots/terminant/syn' => true,
        '/mots/terminant/tach' => true, '/mots/terminant/taku' => true, '/mots/terminant/taps' => true,
        '/mots/terminant/tca' => true, '/mots/terminant/tcc' => true, '/mots/terminant/tcdd' => true,
        '/mots/terminant/tcp' => true, '/mots/terminant/tefs' => true, '/mots/terminant/teks' => true,
        '/mots/terminant/tgi' => true, '/mots/terminant/thex' => true, '/mots/terminant/tips' => true,
        '/mots/terminant/tjs' => true, '/mots/terminant/tkal' => true, '/mots/terminant/tlet' => true,
        '/mots/terminant/tlm' => true, '/mots/terminant/tm' => true, '/mots/terminant/tmar' => true,
        '/mots/terminant/tmp' => true, '/mots/terminant/tn' => true, '/mots/terminant/tnia' => true,
        '/mots/terminant/tnt' => true, '/mots/terminant/toe' => true, '/mots/terminant/toff' => true,
        '/mots/terminant/tofs' => true, '/mots/terminant/tpe' => true, '/mots/terminant/tpu' => true,
        '/mots/terminant/trax' => true, '/mots/terminant/tsan' => true, '/mots/terminant/ttc' => true,
        '/mots/terminant/ttp' => true, '/mots/terminant/tufs' => true, '/mots/terminant/tvi' => true,
        '/mots/terminant/tyx' => true, '/mots/terminant/uaca' => true, '/mots/terminant/uaf' => true,
        '/mots/terminant/uak' => true, '/mots/terminant/ugho' => true, '/mots/terminant/uhau' => true,
        '/mots/terminant/uiz' => true, '/mots/terminant/ujd' => true, '/mots/terminant/uji' => true,
        '/mots/terminant/ungu' => true, '/mots/terminant/unza' => true, '/mots/terminant/uoc' => true,
        '/mots/terminant/uoro' => true, '/mots/terminant/urao' => true, '/mots/terminant/urdo' => true,
        '/mots/terminant/urem' => true, '/mots/terminant/urio' => true, '/mots/terminant/url' => true,
        '/mots/terminant/urp' => true, '/mots/terminant/usar' => true, '/mots/terminant/usso' => true,
        '/mots/terminant/uyin' => true, '/mots/terminant/vams' => true, '/mots/terminant/vc' => true,
        '/mots/terminant/vda' => true, '/mots/terminant/vec' => true, '/mots/terminant/vg' => true,
        '/mots/terminant/vic' => true, '/mots/terminant/vipo' => true, '/mots/terminant/vitz' => true,
        '/mots/terminant/vlan' => true, '/mots/terminant/vlek' => true, '/mots/terminant/vmu' => true,
        '/mots/terminant/voe' => true, '/mots/terminant/vohe' => true, '/mots/terminant/void' => true,
        '/mots/terminant/voro' => true, '/mots/terminant/vp' => true, '/mots/terminant/vrd' => true,
        '/mots/terminant/vtc' => true, '/mots/terminant/vtt' => true, '/mots/terminant/vy' => true,
        '/mots/terminant/wacs' => true, '/mots/terminant/wada' => true, '/mots/terminant/wads' => true,
        '/mots/terminant/wano' => true, '/mots/terminant/wau' => true, '/mots/terminant/wax' => true,
        '/mots/terminant/wbie' => true, '/mots/terminant/wdah' => true, '/mots/terminant/wel' => true,
        '/mots/terminant/werk' => true, '/mots/terminant/werz' => true, '/mots/terminant/wfie' => true,
        '/mots/terminant/wid' => true, '/mots/terminant/wii' => true, '/mots/terminant/woke' => true,
        '/mots/terminant/woks' => true, '/mots/terminant/woro' => true, '/mots/terminant/writ' => true,
        '/mots/terminant/xeta' => true, '/mots/terminant/xl' => true, '/mots/terminant/xmex' => true,
        '/mots/terminant/xnay' => true, '/mots/terminant/xoid' => true, '/mots/terminant/xor' => true,
        '/mots/terminant/xs' => true, '/mots/terminant/xves' => true, '/mots/terminant/xxes' => true,
        '/mots/terminant/yamp' => true, '/mots/terminant/yaws' => true, '/mots/terminant/yaya' => true,
        '/mots/terminant/ybon' => true, '/mots/terminant/ydar' => true, '/mots/terminant/yday' => true,
        '/mots/terminant/yeds' => true, '/mots/terminant/yeul' => true, '/mots/terminant/yfa' => true,
        '/mots/terminant/ygge' => true, '/mots/terminant/yghe' => true, '/mots/terminant/ygos' => true,
        '/mots/terminant/yim' => true, '/mots/terminant/ylix' => true, '/mots/terminant/ynet' => true,
        '/mots/terminant/yodh' => true, '/mots/terminant/yods' => true, '/mots/terminant/yom' => true,
        '/mots/terminant/yoye' => true, '/mots/terminant/yuds' => true, '/mots/terminant/yues' => true,
        '/mots/terminant/yver' => true, '/mots/terminant/zako' => true, '/mots/terminant/zare' => true,
        '/mots/terminant/zays' => true, '/mots/terminant/zba' => true, '/mots/terminant/zebs' => true,
        '/mots/terminant/zecs' => true, '/mots/terminant/zefs' => true, '/mots/terminant/zeks' => true,
        '/mots/terminant/zeli' => true, '/mots/terminant/zese' => true, '/mots/terminant/zett' => true,
        '/mots/terminant/zf' => true, '/mots/terminant/zgo' => true, '/mots/terminant/zics' => true,
        '/mots/terminant/zigs' => true, '/mots/terminant/zio' => true, '/mots/terminant/zips' => true,
        '/mots/terminant/zire' => true, '/mots/terminant/ziva' => true, '/mots/terminant/zobs' => true,
        '/mots/terminant/zoes' => true, '/mots/terminant/zouz' => true, '/mots/terminant/zp' => true,
        '/mots/terminant/zubs' => true, '/mots/terminant/zugs' => true, '/mots/terminant/zups' => true,
    ],
];

$args = array_slice($argv, 1);

if ($args === []) {
    fwrite(STDERR, "usage : php scripts/propose_seo_batch.php <home|length|commencant|terminant|word_admitted> [--limit=N] [--offset=N]\n");
    exit(1);
}

$kind = $args[0];
$limit = 2_000;
$offset = 0;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = (int) substr($arg, strlen('--limit='));
    }

    if (str_starts_with($arg, '--offset=')) {
        $offset = (int) substr($arg, strlen('--offset='));
    }
}

$forbidden = array_merge(Family::FRENCH_NOT_ADMITTED, Family::NEVER_SITEMAP);

if (in_array($kind, $forbidden, true) || $kind === 'word_french_not_admitted') {
    fwrite(STDERR, "refuse : '{$kind}' n'est jamais propose automatiquement par cet outil (voir l'entete du fichier).\n");
    exit(1);
}

$root = dirname(__DIR__);
$dictPath = $root . '/storage/dictionary_fr.sqlite';

if (!is_file($dictPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dictPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dictPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA query_only = ON');

/** @var list<array<string, mixed>> $rows */
$rows = [];
$batchIdSuffix = $kind;

switch ($kind) {
    case 'home':
        $rows[] = [
            'route_path' => '/',
            'family' => Family::HOME,
            'robots' => 'index,follow',
            'canonical_path' => '/',
            'sitemap_fragment' => 'core-0001',
            'result_count' => null,
            'notes' => 'A COMPLETER : page d\'accueil, cible de tout lien "WORD CHECKR" du header sur chaque page du site (maillage total = 100% des pages).',
        ];
        break;

    case 'length':
        $statement = $pdo->query('SELECT length, COUNT(*) n FROM terms GROUP BY length ORDER BY length');

        foreach ($statement->fetchAll() as $row) {
            $n = (int) $row['n'];

            if ($n === 0) {
                continue;
            }

            $len = (int) $row['length'];
            $rows[] = [
                'route_path' => "/mots/{$len}-lettres",
                'family' => Family::WORD_LIST_LENGTH,
                'robots' => 'index,follow',
                'canonical_path' => "/mots/{$len}-lettres",
                'sitemap_fragment' => 'letters-0001',
                'result_count' => $n,
                'notes' => "A COMPLETER : liste de tous les mots de {$len} lettres ({$n} resultats), atteinte depuis la recherche liee \"longueur\" de chaque fiche mot de cette longueur.",
            ];
        }
        break;

    case 'commencant':
        $statement = $pdo->query("SELECT substr(normalized,1,1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c");

        foreach ($statement->fetchAll() as $row) {
            $n = (int) $row['n'];

            if ($n === 0) {
                continue;
            }

            $letter = strtolower($row['c']);
            $routePath = "/mots/commencant/{$letter}";

            // D-041 : aucune exclusion trouvee pour cette famille sur le balayage du 2026-08-21
            // (commencant est la forme la plus generale de son role, elle ne perd jamais un
            // depart a egalite de composants) -- le controle reste applique par coherence
            // structurelle, voir isD041Excluded().
            if (isD041Excluded(Family::WORD_LIST_COMMENCANT, $routePath)) {
                continue;
            }

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_COMMENCANT,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'starts-0001',
                'result_count' => $n,
                'notes' => "A COMPLETER : liste des mots commencant par {$row['c']} ({$n} resultats), atteinte depuis la recherche liee \"commencant par\" de chaque fiche mot correspondante.",
            ];
        }
        break;

    case 'terminant':
        $statement = $pdo->query("SELECT substr(reversed,1,1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c");

        foreach ($statement->fetchAll() as $row) {
            $n = (int) $row['n'];

            if ($n === 0) {
                continue;
            }

            $letter = strtolower($row['c']);
            $routePath = "/mots/terminant/{$letter}";

            // D-041 : 639 lettres exclues (perdent contre un commencant/terminant a egalite de
            // composants ou contre une autre famille plus generale sur le meme panier -- voir
            // scripts/lib/seo_duplicate_priority.php, isD041Excluded()).
            if (isD041Excluded(Family::WORD_LIST_TERMINANT, $routePath)) {
                continue;
            }

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_TERMINANT,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'ends-0001',
                'result_count' => $n,
                'notes' => "A COMPLETER : liste des mots terminant par {$row['c']} ({$n} resultats), atteinte depuis la recherche liee \"terminant par\" de chaque fiche mot correspondante. D-041 : les lettres dont le contenu duplique exactement une autre famille plus generale (ex. commencant+terminant a egalite de composants) sont exclues de ce lot, voir isD041Excluded().",
            ];
        }
        break;

    case 'combined':
        // Uniquement les combinaisons SANS longueur (voir l'entete du fichier) : requete
        // directe sur le dictionnaire, jamais list_counts (deja precalcule pour le runtime,
        // ce script reste independant -- meme convention que 'commencant'/'terminant' ci-dessus).
        $statement = $pdo->query(
            "SELECT substr(normalized,1,1) c, substr(reversed,1,1) e, COUNT(*) n FROM terms GROUP BY c, e ORDER BY c, e"
        );

        foreach ($statement->fetchAll() as $row) {
            $n = (int) $row['n'];

            if ($n === 0) {
                continue;
            }

            $start = strtolower($row['c']);
            $end = strtolower($row['e']);
            $routePath = "/mots/commencant/{$start}/terminant/{$end}";

            // D-041 : voir isD041Excluded() -- exclusions calculees pour Family::WORD_LIST_COMBINED
            // toutes formes confondues (avec et sans longueur, meme famille), partagees avec le cas
            // 'combined_with_length' plus bas.
            if (isD041Excluded(Family::WORD_LIST_COMBINED, $routePath)) {
                continue;
            }

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_COMBINED,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'combined-0001',
                'result_count' => $n,
                'notes' => "A COMPLETER : liste des mots commencant par {$row['c']} et terminant par {$row['e']} ({$n} resultats), atteinte depuis /mots/commencant/{$start} et /mots/terminant/{$end} (deja indexees, D-017) via App\\Search\\LetterCombinedLinksBuilder (D-024). D-041 : doublons de contenu croises avec d'autres familles combinatoires exclus, voir isD041Excluded().",
            ];
        }
        break;

    case 'position':
        // Regularisation d'un lot deja applique le 2026-08-11 (D-028) -- voir l'entete du
        // fichier. Source : list_counts (list_type 'length_with_position', D-023bis), jamais un
        // GROUP BY direct sur `terms` ici (deja precalcule hors ligne par
        // scripts/build_explore_hub_counts.php).
        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_position' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$lengthRaw, $letterRaw, $positionRaw] = explode(':', (string) $row['list_key'], 3);
            $length = (int) $lengthRaw;
            $position = (int) $positionRaw;

            // Positions degenerees (1re et derniere lettre) : collapsees vers commencant/
            // terminant par App\Search\WordListFilters::fromPath() (D-023) -- jamais une URL
            // "position/1/..." ni "position/{longueur}/...", donc jamais proposees ici.
            if ($position <= 1 || $position >= $length) {
                continue;
            }

            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            $parsed[] = [
                'length' => $length,
                'position' => $position,
                'letter' => strtolower($letterRaw),
                // result_count reprend le total REELLEMENT servi par la page
                // (App\Search\WordListSolver::ROW_EXAMINATION_CEILING = 10 000, D-019) -- jamais
                // le compte brut de list_counts au-dela de ce plafond. Verifie : 0 divergence sur
                // les 2 329 lignes reelles face au lot deja applique (reports/query-plans/
                // position-batch-reproducibility.md).
                'result_count' => min($n, 10_000),
            ];
        }

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['length'] <=> $b['length']
                ?: $a['position'] <=> $b['position']
                ?: $a['letter'] <=> $b['letter']
        );

        foreach ($parsed as $item) {
            $length = $item['length'];
            $position = $item['position'];
            $letter = $item['letter'];
            $routePath = "/mots/{$length}-lettres/position/{$position}/{$letter}";

            // D-041 : 2 exclusions (voir isD041Excluded()) -- doublon de contenu avec une autre
            // famille combinatoire sur le meme panier, jamais controle avant ce balayage.
            if (isD041Excluded(Family::WORD_LIST_POSITION, $routePath)) {
                continue;
            }

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_POSITION,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'position-0001',
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel depuis /mots/{$length}-lettres/avec/{$letter} (D-023bis, App\\Search\\PositionLinksBuilder), elle-meme atteinte depuis /mots/{$length}-lettres (deja indexee, D-017). Espace de la famille borne par construction (D-023 : une seule lettre a une seule position, 2 366 combinaisons reelles au total), balayage complet 0/2366 au-dessus du budget TTFB (reports/query-plans/position-full-sweep.md), lot D-028 applique en une seule vague (2 329 URL, 37 combinaisons a 0 resultat exclues).",
            ];
        }

        // Fixes, PAS la date du jour (contrairement aux autres cas) : ce lot a deja ete
        // applique manuellement le 2026-08-11 (D-028) sous cet identifiant exact -- ce cas
        // REND REPRODUCTIBLE un lot existant, il n'en cree pas un nouveau. Un futur lot
        // 'position' distinct devra utiliser son propre batch_id, jamais celui-ci.
        $batchId = 'position-full-2026-08-11';
        $addedAt = '2026-08-10';
        break;

    case 'avec_single_letter':
        // Palier 1 de l'ouverture progressive de "avec" a l'indexation (demande produit du
        // 2026-08-17, entonnoir : une lettre a la fois) -- App\Seo\Family::
        // WORD_LIST_AVEC_SINGLE_LETTER, voir son docblock pour la distinction complete avec
        // Family::WORD_LIST_AVEC (general, NEVER_SITEMAP permanent) et tout futur palier
        // (2 lettres, 3 lettres). Source : list_counts, list_type 'length_with' (D-022, deja
        // precalcule par scripts/build_explore_hub_counts.php) -- jamais un parcours direct de
        // `terms` ici, meme discipline que le cas 'position' ci-dessus. list_key = "{longueur}:
        // {lettre}" garantit PAR CONSTRUCTION que seule la forme longueur + UNE SEULE lettre est
        // generee (jamais 2 lettres, jamais sans longueur).
        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$lengthRaw, $letterRaw] = explode(':', (string) $row['list_key'], 2);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            $parsed[] = [
                'length' => (int) $lengthRaw,
                'letter' => strtolower($letterRaw),
                // result_count reprend le total REELLEMENT servi par la page
                // (App\Search\WordListSolver::ROW_EXAMINATION_CEILING = 10 000, D-019) -- jamais
                // le compte brut de list_counts au-dela de ce plafond, meme convention que le cas
                // 'position' ci-dessus.
                'result_count' => min($n, 10_000),
            ];
        }

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['length'] <=> $b['length']
                ?: $a['letter'] <=> $b['letter']
        );

        foreach ($parsed as $item) {
            $length = $item['length'];
            $letter = $item['letter'];
            $routePath = "/mots/{$length}-lettres/avec/{$letter}";

            // D-041 : 1 exclusion (voir isD041Excluded()) -- ce palier est la racine de la
            // hierarchie "avec" mais peut neanmoins perdre face a une famille combinatoire
            // encore plus generale sur le meme panier (ex. word_list_length seul).
            if (isD041Excluded(Family::WORD_LIST_AVEC_SINGLE_LETTER, $routePath)) {
                continue;
            }

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_AVEC_SINGLE_LETTER,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'avec-single-0001',
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel depuis /mots/{$length}-lettres (deja indexee, Family::WORD_LIST_LENGTH, D-017) via App\\Search\\LengthLinksBuilder::build()->byWith (list_counts, list_type 'length_with', D-022) -- couverture verifiee exhaustivement 364/364 (reports/query-plans/avec-length-1-letter-full-sweep.md). Palier 1 de Family::WORD_LIST_AVEC_SINGLE_LETTER (longueur + exactement une lettre avec, minCount=1) : balayage complet 0/364 au-dessus du budget TTFB p95 < 250 ms (max 168,0 ms), 0/364 a 0 resultat, 2/364 a exactement 1 resultat (2-lettres/avec/w, 2-lettres/avec/z -- conserves, consigne produit explicite).",
            ];
        }
        break;

    case 'avec_two_letters':
        // Palier 2 de l'ouverture progressive de "avec" a l'indexation (demande produit du
        // 2026-08-17, entonnoir : deux lettres a la fois) -- App\Seo\Family::
        // WORD_LIST_AVEC_TWO_LETTERS, voir son docblock (app/Seo/Family.php) pour la distinction
        // complete avec Family::WORD_LIST_AVEC_SINGLE_LETTER (palier 1) et Family::WORD_LIST_AVEC
        // (general, NEVER_SITEMAP permanent). Source : list_counts, list_type 'length_with_pair'
        // (deja precalcule par scripts/build_explore_hub_counts.php, agent data-engine) -- jamais
        // un parcours direct de `terms` ici, meme discipline que les cas ci-dessus. list_key =
        // "{longueur}:{lettre1}:{lettre2}" (lettre1 < lettre2) garantit PAR CONSTRUCTION que seule
        // la forme longueur + EXACTEMENT deux lettres distinctes est generee.
        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_pair' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$lengthRaw, $letter1Raw, $letter2Raw] = explode(':', (string) $row['list_key'], 3);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            $parsed[] = [
                'length' => (int) $lengthRaw,
                'letter1' => strtolower($letter1Raw),
                'letter2' => strtolower($letter2Raw),
                // result_count reprend le total REELLEMENT servi par la page
                // (App\Search\WordListSolver::ROW_EXAMINATION_CEILING = 10 000, D-019) -- jamais
                // le compte brut de list_counts au-dela de ce plafond, meme convention que les cas
                // 'position'/'avec_single_letter' ci-dessus.
                'result_count' => min($n, 10_000),
            ];
        }

        // CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3) : voir le
        // docblock de AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES en tete de ce fichier pour la
        // justification complete et les deux methodes de verification independantes -- exclut
        // les paires dont le contenu duplique EXACTEMENT celui d'un parent palier 1 (une seule
        // lettre, deja indexe, Family::WORD_LIST_AVEC_SINGLE_LETTER, D-029).
        $tierDuplicateCount = 0;
        $parsed = array_values(array_filter(
            $parsed,
            static function (array $item) use (&$tierDuplicateCount): bool {
                $key = "{$item['length']}:{$item['letter1']}:{$item['letter2']}";

                if (isset(AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES[$key])) {
                    $tierDuplicateCount++;

                    return false;
                }

                return true;
            }
        ));

        // D-041 : 138 exclusions supplementaires (voir isD041Excluded()) -- doublons de contenu
        // croises avec d'autres familles combinatoires (ex. word_list_combined, word_list_terminant
        // sur le meme panier), jamais controles avant le balayage generique du 2026-08-21.
        $d041ExcludedCount = 0;
        $parsed = array_values(array_filter(
            $parsed,
            static function (array $item) use (&$d041ExcludedCount): bool {
                $routePath = "/mots/{$item['length']}-lettres/avec/{$item['letter1']}/{$item['letter2']}";

                if (isD041Excluded(Family::WORD_LIST_AVEC_TWO_LETTERS, $routePath)) {
                    $d041ExcludedCount++;

                    return false;
                }

                return true;
            }
        ));

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['length'] <=> $b['length']
                ?: $a['letter1'] <=> $b['letter1']
                ?: $a['letter2'] <=> $b['letter2']
        );

        foreach ($parsed as $item) {
            $length = $item['length'];
            $letter1 = $item['letter1'];
            $letter2 = $item['letter2'];
            $routePath = "/mots/{$length}-lettres/avec/{$letter1}/{$letter2}";

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_AVEC_TWO_LETTERS,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'avec-pair-0001',
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel depuis /mots/{$length}-lettres/avec/{$letter1} ET /mots/{$length}-lettres/avec/{$letter2} (palier 1, deja indexe, Family::WORD_LIST_AVEC_SINGLE_LETTER, D-029) via App\\Search\\AvecTwoLettersLinksBuilder (list_counts, list_type 'length_with_pair') -- couverture verifiee exhaustivement dans les deux sens 4276/4276 (reports/query-plans/avec-length-2-letters-full-sweep.md). Palier 2 de Family::WORD_LIST_AVEC_TWO_LETTERS (longueur + exactement deux lettres avec distinctes, minCount=1 chacune) : balayage complet (agent data-engine, 3 runs) 0-75/4550 au-dessus du budget TTFB p95 < 250 ms selon le run, re-verifie independamment par l'agent seo-registry avant application (2 balayages complets du sous-ensemble longueur 12+13, jusqu'a 109 643,982 ms observe au pire). Reproduction en isolation dependante du MOMENT du test, pas de la requete : 1re verification isolee (10x, juste apres un balayage) a reproduit des depassements sur 10/13 cas cites par data-engine, une 2e verification des memes cas (apres un second balayage) n'en a reproduit aucun -- signature de contention systeme transitoire, pas un defaut de plan de requete (EXPLAIN QUERY PLAN stable, jamais de SCAN complet). Voir app/Seo/Family.php (docblock NEVER_SITEMAP) pour le detail. 274/4550 a 0 resultat (exclues), 132/4550 a exactement 1 resultat sur les 4276 candidates avant correctif (conservees, consigne produit explicite). CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, C-3) : {$tierDuplicateCount} paires exclues sur ce lot, contenu strictement identique a un parent palier 1 -- 4272/4276 pages reellement indexables apres ce correctif (voir AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES en tete de fichier, 2 methodes independantes, 0 divergence).",
            ];
        }
        break;

    case 'avec_three_letters':
        // Palier 3 de l'ouverture progressive de "avec" a l'indexation (demande produit du
        // 2026-08-18, entonnoir : trois lettres a la fois) -- App\Seo\Family::
        // WORD_LIST_AVEC_THREE_LETTERS, voir son docblock (app/Seo/Family.php) pour la
        // distinction complete avec les paliers 1/2 et Family::WORD_LIST_AVEC (general,
        // NEVER_SITEMAP permanent). Source : list_counts, list_type 'length_with_triple' (deja
        // precalcule par scripts/build_explore_hub_counts.php, agent data-engine) -- jamais un
        // parcours direct de `terms` ici, meme discipline que les cas ci-dessus. list_key =
        // "{longueur}:{lettre1}:{lettre2}:{lettre3}" (lettre1 < lettre2 < lettre3) garantit PAR
        // CONSTRUCTION que seule la forme longueur + EXACTEMENT trois lettres distinctes est
        // generee.
        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_triple' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$lengthRaw, $letter1Raw, $letter2Raw, $letter3Raw] = explode(':', (string) $row['list_key'], 4);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            $parsed[] = [
                'length' => (int) $lengthRaw,
                'letter1' => strtolower($letter1Raw),
                'letter2' => strtolower($letter2Raw),
                'letter3' => strtolower($letter3Raw),
                // result_count reprend le total REELLEMENT servi par la page
                // (App\Search\WordListSolver::ROW_EXAMINATION_CEILING = 10 000, D-019) -- jamais
                // le compte brut de list_counts au-dela de ce plafond, meme convention que les cas
                // 'position'/'avec_single_letter'/'avec_two_letters' ci-dessus.
                'result_count' => min($n, 10_000),
            ];
        }

        // CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, bloquant C-3) : voir le
        // docblock de AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES en tete de ce fichier pour la
        // justification complete et les deux methodes de verification independantes -- exclut
        // les triplets dont le contenu duplique EXACTEMENT celui d'un parent palier 1 (une seule
        // lettre) OU palier 2 (deux lettres, deja indexes D-029/D-030), ainsi que les doublons
        // SOEURS entre triplets distincts de meme longueur (deux ou trois lettres differentes,
        // meme ensemble de mots exact).
        $tierDuplicateCount = 0;
        $parsed = array_values(array_filter(
            $parsed,
            static function (array $item) use (&$tierDuplicateCount): bool {
                $key = "{$item['length']}:{$item['letter1']}:{$item['letter2']}:{$item['letter3']}";

                if (isset(AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES[$key])) {
                    $tierDuplicateCount++;

                    return false;
                }

                return true;
            }
        ));

        // D-041 : 666 exclusions supplementaires (voir isD041Excluded()) -- doublons de contenu
        // croises avec d'autres familles combinatoires (ex. word_list_combined,
        // word_list_combined_with_letter, word_list_commencant/terminant sur le meme panier),
        // jamais controles avant le balayage generique du 2026-08-21.
        $d041ExcludedCount = 0;
        $parsed = array_values(array_filter(
            $parsed,
            static function (array $item) use (&$d041ExcludedCount): bool {
                $routePath = "/mots/{$item['length']}-lettres/avec/{$item['letter1']}/{$item['letter2']}/{$item['letter3']}";

                if (isD041Excluded(Family::WORD_LIST_AVEC_THREE_LETTERS, $routePath)) {
                    $d041ExcludedCount++;

                    return false;
                }

                return true;
            }
        ));

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['length'] <=> $b['length']
                ?: $a['letter1'] <=> $b['letter1']
                ?: $a['letter2'] <=> $b['letter2']
                ?: $a['letter3'] <=> $b['letter3']
        );

        foreach ($parsed as $item) {
            $length = $item['length'];
            $letter1 = $item['letter1'];
            $letter2 = $item['letter2'];
            $letter3 = $item['letter3'];
            $routePath = "/mots/{$length}-lettres/avec/{$letter1}/{$letter2}/{$letter3}";

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_AVEC_THREE_LETTERS,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'avec-triple-0001',
                'result_count' => $item['result_count'],
                // Note volontairement plus concise que les paliers 1/2 (D-029/D-030) : a ~28 000
                // lignes (~6,6x le palier 2), repeter le paragraphe complet d'investigation sur
                // CHAQUE ligne epuisait le memory_limit CLI par defaut (128 Mo, voir ini_set()
                // en tete de ce fichier) -- le detail complet (mesures, investigation du pic de
                // latence isole, verification en trois sens, correctif C-3) reste dans
                // reports/query-plans/avec-length-3-letters-full-sweep.md et le docblock
                // WORD_LIST_AVEC_THREE_LETTERS de app/Seo/Family.php, jamais retire, seulement
                // pas duplique caractere pour caractere sur chaque ligne. R7 (note de maillage
                // non vide) reste respectee : chaque ligne cite ses TROIS pages source reelles.
                'notes' => "maillage interne reel depuis /mots/{$length}-lettres/avec/{$letter1}/{$letter2} ET /mots/{$length}-lettres/avec/{$letter1}/{$letter3} ET /mots/{$length}-lettres/avec/{$letter2}/{$letter3} (palier 2, deja indexe, Family::WORD_LIST_AVEC_TWO_LETTERS, D-030) via App\\Search\\AvecThreeLettersLinksBuilder -- couverture verifiee exhaustivement dans les TROIS sens (28827/28827 avant correctif). Palier 3 de Family::WORD_LIST_AVEC_THREE_LETTERS (longueur + exactement trois lettres avec distinctes, minCount=1 chacune) : balayage complet 36400 combinaisons (un seul passage, agent data-engine), 683/36400 au-dessus du budget TTFB p95 < 250 ms, investigue et juge non structurel (contention transitoire concentree sur la longueur 8, sans rapport avec le plan de requete ni la production -- detail complet dans reports/query-plans/avec-length-3-letters-full-sweep.md et app/Seo/Family.php). 7573/36400 a 0 resultat (exclues), 1682/28827 a exactement 1 resultat avant correctif (consigne produit explicite). CORRECTIF (4e audit seo-technical-auditor, 2026-08-20, C-3) : {$tierDuplicateCount} triplets exclus sur ce lot, contenu strictement identique a un parent palier 1/2 ou a un triplet soeur de meme longueur -- 28167/28827 pages reellement indexables apres ce correctif (voir AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES en tete de fichier, 2 methodes independantes, 0 divergence).",
            ];
        }
        break;

    case 'combined_with_length':
        // Axe 1 (D-027 byStartEnd) -- voir l'entete du fichier pour le detail complet. Source :
        // list_counts, list_type 'length_start_end' (D-027, deja precalcule) -- jamais un
        // parcours direct de `terms` ici.
        $startEndCounts = [];

        foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end'")->fetchAll() as $row) {
            $startEndCounts[(string) $row['list_key']] = (int) $row['count'];
        }

        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_start_end' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$lengthRaw, $startRaw, $endRaw] = explode(':', (string) $row['list_key'], 3);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            $pairKey = $startRaw . ':' . $endRaw;

            // Duplicat de contenu avec la variante sans longueur (D-025, I-1) : cette page
            // resterait noindex,follow en permanence (R3) -- jamais proposee ici, meme
            // exclusion que App\Search\LengthLinksBuilder::DUPLICATE_START_END_KEYS applique
            // deja au maillage.
            if (isset($startEndCounts[$pairKey]) && $startEndCounts[$pairKey] === $n) {
                continue;
            }

            $parsed[] = [
                'length' => (int) $lengthRaw,
                'start' => strtolower($startRaw),
                'end' => strtolower($endRaw),
                // Regime BORNE (WordListSolver::isExactMode() === false des que le suffixe est
                // present) : result_count reprend le total REELLEMENT servi par la page
                // (ROW_EXAMINATION_CEILING = 10 000, D-019), meme convention que les cas
                // 'position'/'avec_*' ci-dessus.
                'result_count' => min($n, 10_000),
            ];
        }

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['length'] <=> $b['length']
                ?: $a['start'] <=> $b['start']
                ?: $a['end'] <=> $b['end']
        );

        foreach ($parsed as $item) {
            $length = $item['length'];
            $start = $item['start'];
            $end = $item['end'];
            $routePath = "/mots/{$length}-lettres/commencant/{$start}/terminant/{$end}";

            // D-041 : voir isD041Excluded() -- meme constante D041_EXCLUDED_ROUTE_PATHS['word_list_combined']
            // que le cas 'combined' ci-dessus (meme famille, avec et sans longueur confondues).
            if (isD041Excluded(Family::WORD_LIST_COMBINED, $routePath)) {
                continue;
            }

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_COMBINED,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'combined-0002',
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel depuis /mots/{$length}-lettres (deja indexee, Family::WORD_LIST_LENGTH, D-017) via App\\Search\\LengthLinksBuilder::build()->byStartEnd (list_counts, list_type 'length_start_end', D-027) -- couverture verifiee exhaustivement dans les deux sens 5141/5141 (reports/query-plans/combined-length-maillage.md). Variante AVEC longueur de Family::WORD_LIST_COMBINED (deja hors NEVER_SITEMAP depuis D-025, meme famille que la variante sans longueur -- aucune nouvelle classification) : balayage complet 0/9464 au-dessus du budget TTFB (reports/query-plans/combined-with-length-full-sweep.md). 52 paires a contenu strictement duplique avec la variante sans longueur (D-025, deja gagnante canonique permanente) exclues de ce lot (R3).",
            ];
        }
        break;

    case 'combined_with_letter':
        // Axe 2 (D-033 StartEndWithLinks) -- voir l'entete du fichier pour le detail complet.
        // Source : list_counts, list_type 'start_end_with' (D-033, deja precalcule) -- jamais
        // un parcours direct de `terms` ici.
        //
        // CORRECTIF C-1 (2026-08-19) : parent 'start_end' (Family::WORD_LIST_COMBINED, deja
        // indexee, D-024/D-025) charge AVANT la boucle -- meme patron que le cas
        // 'combined_with_length' plus haut (ligne ~684) pour son parent 'start_end' egalement.
        $startEndCounts = [];

        foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_end'")->fetchAll() as $row) {
            $startEndCounts[(string) $row['list_key']] = (int) $row['count'];
        }

        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'start_end_with' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$startRaw, $endRaw, $letterRaw] = explode(':', (string) $row['list_key'], 3);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            // Degeneree (D-032) : l'URL candidate collapse silencieusement vers la page
            // parente /mots/commencant/{X}/terminant/{Y} elle-meme -- jamais un alias vers une
            // page deja indexee (R3), jamais proposee ici, meme exclusion que
            // App\Search\StartEndWithLinksBuilder applique deja au maillage.
            if ($letterRaw === $startRaw || $letterRaw === $endRaw) {
                continue;
            }

            // Duplicat de contenu C-1 (audit seo-technical-auditor, 2026-08-19) : cette ligne
            // ajoute une lettre "avec" DIFFERENTE du debut/fin (donc pas degeneree D-032
            // ci-dessus), mais si son compte egale EXACTEMENT celui de la page parente SANS
            // lettre "avec" (start_end), alors TOUS les mots de la page parente contiennent deja
            // cette lettre -- meme contenu, page parente deja gagnante canonique (R3). Meme regle
            // que le cas 'combined_with_length' (ligne ~704). Preuve concrete verifiee : F:Q/a
            // (FAQ, 1 mot) et X:O/{h,i,p} (XIPHO, 1 mot) sont exclus par ce controle.
            $pairKey = $startRaw . ':' . $endRaw;

            if (isset($startEndCounts[$pairKey]) && $startEndCounts[$pairKey] === $n) {
                continue;
            }

            $parsed[] = [
                'start' => strtolower($startRaw),
                'end' => strtolower($endRaw),
                'letter' => strtolower($letterRaw),
                // Regime BORNE (suffixe present) : meme convention de plafond que les cas
                // 'position'/'avec_*'/'combined_with_length' ci-dessus.
                'result_count' => min($n, 10_000),
            ];
        }

        // Doublons de contenu SOEURS (I-A, 2e audit seo-technical-auditor sur D-037,
        // 2026-08-19) : voir findSiblingContentDuplicates() en tete de fichier pour la
        // justification complete. UNE requete par paire distincte (panier complet, ancree sur
        // idx_terms_startletter_endletter_normalized, D-025bis), jamais par ligne.
        $lettersByPair = [];

        foreach ($parsed as $item) {
            $lettersByPair[$item['start'] . ':' . $item['end']][] = $item['letter'];
        }

        $pairBasketStatement = $pdo->prepare(
            "SELECT normalized FROM terms WHERE substr(normalized,1,1) = ? AND substr(reversed,1,1) = ? ORDER BY normalized"
        );

        $siblingResult = findSiblingContentDuplicates(
            $lettersByPair,
            function (string $groupKey) use ($pairBasketStatement): PDOStatement {
                [$pairStart, $pairEnd] = explode(':', $groupKey);
                $pairBasketStatement->execute([strtoupper($pairStart), strtoupper($pairEnd)]);

                return $pairBasketStatement;
            }
        );

        $siblingExcluded = $siblingResult['excluded'];
        $siblingDuplicateLines = count($siblingExcluded);

        $parsed = array_values(array_filter(
            $parsed,
            static fn (array $item): bool => !isset($siblingExcluded["{$item['start']}:{$item['end']}:{$item['letter']}"])
        ));

        // CORRECTIF C-2 (2026-08-19, 3e audit seo-technical-auditor consolide de la serie) : voir
        // findLengthAvecContentCollisions() en tete de fichier pour la justification complete.
        // Compare chaque tranche AXE 2 (lettre "avec", ce cas, deja filtree D-032+C-1+I-A
        // ci-dessus) a chaque tranche AXE 1 (longueur, cas 'combined_with_length' ci-dessus, deja
        // filtree des 52 doublons D-025) du MEME panier commencant+terminant -- ensemble de mots
        // EXACTEMENT identique, pas seulement le compte. En cas de collision, la tranche longueur
        // (axe 1) reste seule candidate, la tranche "avec" (axe 2) est exclue (regle de priorite
        // tranchee cote produit, coherente avec D-025).
        $lengthsByPairForCross = [];

        foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_start_end'")->fetchAll() as $row) {
            [$lengthRaw, $startRaw, $endRaw] = explode(':', (string) $row['list_key'], 3);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            $pairKeyForCross = $startRaw . ':' . $endRaw;

            // Meme exclusion que le cas 'combined_with_length' ci-dessus (D-025, I-1) : une
            // tranche axe 1 dont le compte egale celui du panier complet reste noindex,follow en
            // permanence, jamais une tranche candidate -- donc jamais une source de collision axe
            // 1 valide ici non plus.
            if (isset($startEndCounts[$pairKeyForCross]) && $startEndCounts[$pairKeyForCross] === $n) {
                continue;
            }

            // list_counts stocke les lettres en MAJUSCULE ("X:M"), $lettersByPairForCross
            // ci-dessous en MINUSCULE (issu de $parsed, deja strtolower()) -- cle normalisee en
            // minuscule ici pour que les deux tableaux partagent le meme espace de cles, sans
            // quoi findLengthAvecContentCollisions() ne trouverait jamais aucune correspondance
            // (bug trouve et corrige en ecrivant ce correctif, avant toute application au
            // registre reel -- balayage de verification 0 -> 333 apres ce correctif).
            $lengthsByPairForCross[strtolower($pairKeyForCross)][] = (int) $lengthRaw;
        }

        $lettersByPairForCross = [];

        foreach ($parsed as $item) {
            $lettersByPairForCross[$item['start'] . ':' . $item['end']][] = $item['letter'];
        }

        $crossBasketStatement = $pdo->prepare(
            "SELECT normalized, length FROM terms WHERE substr(normalized,1,1) = ? AND substr(reversed,1,1) = ? ORDER BY normalized"
        );

        $crossResult = findLengthAvecContentCollisions(
            $lengthsByPairForCross,
            $lettersByPairForCross,
            function (string $groupKey) use ($crossBasketStatement): PDOStatement {
                [$pairStart, $pairEnd] = explode(':', $groupKey);
                $crossBasketStatement->execute([strtoupper($pairStart), strtoupper($pairEnd)]);

                return $crossBasketStatement;
            }
        );

        $crossExcluded = $crossResult['excluded'];
        $crossDuplicateLines = count($crossExcluded);

        $parsed = array_values(array_filter(
            $parsed,
            static fn (array $item): bool => !isset($crossExcluded["{$item['start']}:{$item['end']}:{$item['letter']}"])
        ));

        // D-041 : 314 exclusions supplementaires (voir isD041Excluded()) -- doublons de contenu
        // croises avec d'autres familles combinatoires (ex. word_list_avec_three_letters,
        // word_list_terminant sur le meme panier), jamais controles avant le balayage generique du
        // 2026-08-21 (au-dela des controles C-1/I-A/C-2 deja appliques ci-dessus).
        $d041ExcludedCount = 0;
        $parsed = array_values(array_filter(
            $parsed,
            static function (array $item) use (&$d041ExcludedCount): bool {
                $routePath = "/mots/commencant/{$item['start']}/terminant/{$item['end']}/avec/{$item['letter']}";

                if (isD041Excluded(Family::WORD_LIST_COMBINED_WITH_LETTER, $routePath)) {
                    $d041ExcludedCount++;

                    return false;
                }

                return true;
            }
        ));

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['start'] <=> $b['start']
                ?: $a['end'] <=> $b['end']
                ?: $a['letter'] <=> $b['letter']
        );

        foreach ($parsed as $item) {
            $start = $item['start'];
            $end = $item['end'];
            $letter = $item['letter'];
            $routePath = "/mots/commencant/{$start}/terminant/{$end}/avec/{$letter}";

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_COMBINED_WITH_LETTER,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'combined-with-0001',
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel depuis /mots/commencant/{$start}/terminant/{$end} (deja indexee, Family::WORD_LIST_COMBINED, D-024/D-025) via App\\Search\\StartEndWithLinksBuilder (list_counts, list_type 'start_end_with', D-033) -- couverture verifiee exhaustivement dans les deux sens 10150/10150 (reports/query-plans/commencant-terminant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMBINED_WITH_LETTER (distincte de WORD_LIST_COMBINED, voir app/Seo/Family.php) : balayage complet 0/15886 au-dessus du budget TTFB (reports/query-plans/commencant-terminant-avec-full-sweep.md). 1198 lignes degenerees (lettre avec == debut ou fin, D-032) exclues de ce lot. CORRECTIF C-1 (2026-08-19) : 227 lignes a contenu strictement duplique avec la page parente sans lettre avec (meme compte que list_counts 'start_end') exclues de ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : {$siblingDuplicateLines} lignes supplementaires exclues, doublons de contenu ENTRE LETTRES AVEC SOEURS du meme parent commencant+terminant (ensemble de mots strictement identique entre plusieurs lettres avec distinctes, ex. paire X:M -- XALAM derriere avec/a ET avec/l, XENODOCHIUM derriere 8 lettres distinctes) -- la lettre alphabetiquement la plus petite de chaque groupe reste seule candidate (findSiblingContentDuplicates(), voir l'entete de ce fichier), verifie par 3 methodes independantes (0 divergence). CORRECTIF C-2 (2026-08-19, 3e audit consolide) : {$crossDuplicateLines} lignes supplementaires exclues, doublons de contenu CROISES avec une tranche AXE 1 (longueur, Family::WORD_LIST_COMBINED variante avec longueur, meme panier commencant+terminant) -- ex. paire X:M, avec/a et avec/c (gagnants I-A) identiques a /mots/5-lettres/commencant/x/terminant/m et /mots/11-lettres/commencant/x/terminant/m respectivement -- la tranche longueur reste seule candidate (regle de priorite produit, coherente D-025), verifie par 2 methodes independantes (0 divergence, findLengthAvecContentCollisions(), voir l'entete de ce fichier). " . (9_495 - $crossDuplicateLines) . " pages reellement indexables (9495 avant ce correctif - {$crossDuplicateLines}).",
            ];
        }
        break;

    case 'commencant_terminant_multilettres':
        // Axe 3 (dimensionnement multi-lettres) -- voir l'entete du fichier pour le detail
        // complet. Genere les DEUX directions (commencant, terminant) dans un seul lot --
        // families distinctes par ligne, sitemap_fragment distinct par direction.
        $directions = [
            'commencant' => ['listPrefix' => 'prefix', 'family' => Family::WORD_LIST_COMMENCANT, 'fragment' => 'starts-0002'],
            'terminant' => ['listPrefix' => 'suffix', 'family' => Family::WORD_LIST_TERMINANT, 'fragment' => 'ends-0002'],
        ];

        $allParsed = [];

        foreach ($directions as $direction => $cfg) {
            // Niveau 1 (mono-lettre, deja indexe D-017) : total REEL depuis `terms` -- meme
            // requete que les cas 'commencant'/'terminant' ci-dessus, necessaire uniquement
            // pour detecter un eventuel doublon de niveau 1 -> 2 (aucun trouve en pratique, mais
            // verifie plutot que suppose).
            $level = [1 => []];
            $mono = $direction === 'commencant'
                ? $pdo->query('SELECT substr(normalized,1,1) c, COUNT(*) n FROM terms GROUP BY c')
                : $pdo->query('SELECT substr(reversed,1,1) c, COUNT(*) n FROM terms GROUP BY c');

            foreach ($mono->fetchAll() as $row) {
                $level[1][(string) $row['c']] = (int) $row['n'];
            }

            foreach ([2, 3, 4] as $n) {
                $level[$n] = [];
                $stmt = $pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = '{$cfg['listPrefix']}{$n}'");

                foreach ($stmt->fetchAll() as $row) {
                    $level[$n][(string) $row['list_key']] = (int) $row['count'];
                }
            }

            // Detection des doublons parent/enfant (D-025, I-1, meme logique, un niveau a la
            // fois : 1->2, 2->3, 3->4). Un parent de longueur N est duplique par son enfant de
            // longueur N+1 ssi il n'a QU'UNE SEULE extension reelle ET que son compte EGALE
            // celui du parent -- dans ce cas, la page ENFANT (plus longue) reste noindex,follow
            // en permanence (R3), la page PARENTE (plus courte) reste le gagnant canonique.
            $duplicateChildren = [];

            foreach ([1, 2, 3] as $n) {
                $childrenByParent = [];

                foreach ($level[$n + 1] as $childKey => $childCount) {
                    $parentKey = $direction === 'commencant' ? substr($childKey, 0, $n) : substr($childKey, -$n);
                    $childrenByParent[$parentKey][] = [$childKey, $childCount];
                }

                foreach ($level[$n] as $parentKey => $parentCount) {
                    $children = $childrenByParent[$parentKey] ?? [];

                    if (count($children) === 1 && $children[0][1] === $parentCount) {
                        $duplicateChildren[$children[0][0]] = true;
                    }
                }
            }

            // Emission (niveaux 2 a 4 uniquement -- le niveau 1 est deja indexe depuis D-017,
            // jamais re-propose ici).
            foreach ([2, 3, 4] as $n) {
                foreach ($level[$n] as $key => $count) {
                    if ($count === 0) {
                        continue;
                    }

                    if (isset($duplicateChildren[$key])) {
                        continue;
                    }

                    $parentValue = $direction === 'commencant'
                        ? substr($key, 0, $n - 1)
                        : substr($key, 1);

                    $allParsed[] = [
                        'direction' => $direction,
                        'value' => strtolower($key),
                        'parentValue' => strtolower($parentValue),
                        'parentLength' => $n - 1,
                        'family' => $cfg['family'],
                        'fragment' => $cfg['fragment'],
                        // EXACT (commencant, WordListSolver::isExactMode() === true, jamais
                        // tronque) vs BORNE (terminant, ancre sur reversed, plafonne a
                        // ROW_EXAMINATION_CEILING = 10 000, D-019) -- verifie dans
                        // reports/query-plans/commencant-terminant-multi-lettres-
                        // dimensionnement.md section 2.
                        'result_count' => $direction === 'commencant' ? $count : min($count, 10_000),
                    ];
                }
            }
        }

        usort(
            $allParsed,
            static fn (array $a, array $b): int => $a['direction'] <=> $b['direction']
                ?: strlen($a['value']) <=> strlen($b['value'])
                ?: $a['value'] <=> $b['value']
        );

        // D-041 : voir isD041Excluded() -- la tres grande majorite des exclusions calculees pour
        // word_list_commencant (0) et surtout word_list_terminant (639) portent sur des prefixes/
        // suffixes MULTI-LETTRES (2 a 4 lettres), generes ICI et non par les cas 'commencant'/
        // 'terminant' mono-lettre ci-dessus (au plus 26 routes chacun, jamais assez etroit pour
        // dupliquer une autre famille combinatoire). Filtre applique aux DEUX directions par la
        // meme constante D041_EXCLUDED_ROUTE_PATHS, indexee par famille.
        $d041ExcludedCount = 0;
        $allParsed = array_values(array_filter(
            $allParsed,
            static function (array $item) use (&$d041ExcludedCount): bool {
                $routePath = "/mots/{$item['direction']}/{$item['value']}";

                if (isD041Excluded($item['family'], $routePath)) {
                    $d041ExcludedCount++;

                    return false;
                }

                return true;
            }
        ));

        foreach ($allParsed as $item) {
            $value = $item['value'];
            $direction = $item['direction'];
            $routePath = "/mots/{$direction}/{$value}";
            $parentLabel = $item['parentLength'] === 1
                ? "/mots/{$direction}/{$item['parentValue']} (1 lettre, deja indexee, D-017)"
                : "/mots/{$direction}/{$item['parentValue']} ({$item['parentLength']} lettres, ce meme lot)";

            $builderClass = $direction === 'commencant' ? 'PrefixExtensionLinksBuilder' : 'SuffixExtensionLinksBuilder';

            $rows[] = [
                'route_path' => $routePath,
                'family' => $item['family'],
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => $item['fragment'],
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel en entonnoir depuis {$parentLabel} via App\\Search\\{$builderClass} -- couverture verifiee exhaustivement 39539/39539 dans les deux sens (reports/query-plans/commencant-terminant-multi-lettres-dimensionnement.md). Extension multi-lettres (2 a 4) de Family::WORD_LIST_COMMENCANT/WORD_LIST_TERMINANT (deja hors NEVER_SITEMAP, aucune nouvelle classification) : balayage complet 0/39539 puis 2/39539 investigues et non reproduits en isolation (contention transitoire deja documentee, D-030/D-031). 1982 pages a contenu strictement duplique avec leur page parente immediate exclues de ce lot (page la plus longue de chaque paire, R3, arbitrage seo-registry).",
            ];
        }
        break;

    case 'commencant_avec':
        // Dernier des quatre axes commencant/terminant/avec travailles ce jour -- voir l'entete
        // du fichier pour le detail complet. Source : list_counts, list_type 'start_with' (deja
        // precalcule) -- jamais un parcours direct de `terms` ici. Contrairement au cas
        // 'combined_with_letter', les diagonales degenerees (D-032) sont deja exclues au
        // precalcul (verifie section 1 du rapport de maillage cite ci-dessus) : aucun filtre
        // supplementaire necessaire ici pour D-032.
        //
        // CORRECTIF C-1 (2026-08-19) : parent 'start' (Family::WORD_LIST_COMMENCANT, deja
        // indexee, D-017) charge AVANT la boucle -- meme patron que 'combined_with_letter' juste
        // au-dessus pour son parent 'start_end'.
        $startCounts = [];

        foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start'")->fetchAll() as $row) {
            $startCounts[(string) $row['list_key']] = (int) $row['count'];
        }

        $statement = $pdo->query(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'start_with' ORDER BY list_key"
        );

        $parsed = [];

        foreach ($statement->fetchAll() as $row) {
            [$prefixRaw, $letterRaw] = explode(':', (string) $row['list_key'], 2);
            $n = (int) $row['count'];

            if ($n === 0) {
                continue;
            }

            // Duplicat de contenu C-1 (audit seo-technical-auditor, 2026-08-19) : cette ligne
            // ajoute une lettre "avec" (deja garantie distincte du prefixe, exclue au precalcul
            // D-032) -- si son compte egale EXACTEMENT celui de la page parente SANS lettre
            // "avec" (start), alors TOUS les mots de la page parente contiennent deja cette
            // lettre -- meme contenu, page parente deja gagnante canonique (R3). Meme regle que
            // 'combined_with_letter' ci-dessus. Recalcule ici independamment : 0/650 lignes
            // concernees en pratique (voir docblock en tete de fichier) -- le controle reste
            // ecrit pour que ce cas ne depende plus implicitement d'une absence de collision.
            if (isset($startCounts[$prefixRaw]) && $startCounts[$prefixRaw] === $n) {
                continue;
            }

            $parsed[] = [
                'prefix' => strtolower($prefixRaw),
                'letter' => strtolower($letterRaw),
                // Regime BORNE (WordListSolver::needsUnindexedPredicates(), "avec" present) :
                // result_count reprend le total REELLEMENT servi par la page
                // (ROW_EXAMINATION_CEILING = 10 000, D-019), meme convention que les cas
                // 'position'/'avec_*'/'combined_with_letter' ci-dessus.
                'result_count' => min($n, 10_000),
            ];
        }

        // Doublons de contenu SOEURS (I-A, 2e audit seo-technical-auditor sur D-037,
        // 2026-08-19) : voir findSiblingContentDuplicates() en tete de fichier pour la
        // justification complete. UNE requete par prefixe distinct (panier complet, ancree sur
        // l'index de prefixe deja mesure), jamais par ligne -- 26 prefixes au plus.
        $lettersByPrefix = [];

        foreach ($parsed as $item) {
            $lettersByPrefix[$item['prefix']][] = $item['letter'];
        }

        $prefixBasketStatement = $pdo->prepare(
            "SELECT normalized FROM terms WHERE substr(normalized,1,1) = ? ORDER BY normalized"
        );

        $siblingResult = findSiblingContentDuplicates(
            $lettersByPrefix,
            function (string $groupKey) use ($prefixBasketStatement): PDOStatement {
                $prefixBasketStatement->execute([strtoupper($groupKey)]);

                return $prefixBasketStatement;
            }
        );

        $siblingExcluded = $siblingResult['excluded'];
        $siblingDuplicateLines = count($siblingExcluded);

        $parsed = array_values(array_filter(
            $parsed,
            static fn (array $item): bool => !isset($siblingExcluded["{$item['prefix']}:{$item['letter']}"])
        ));

        // D-041 : 4 exclusions supplementaires (voir isD041Excluded()) -- doublons de contenu
        // croises avec d'autres familles combinatoires, jamais controles avant le balayage
        // generique du 2026-08-21 (au-dela des controles C-1/I-A deja appliques ci-dessus).
        $d041ExcludedCount = 0;
        $parsed = array_values(array_filter(
            $parsed,
            static function (array $item) use (&$d041ExcludedCount): bool {
                $routePath = "/mots/commencant/{$item['prefix']}/avec/{$item['letter']}";

                if (isD041Excluded(Family::WORD_LIST_COMMENCANT_WITH_LETTER, $routePath)) {
                    $d041ExcludedCount++;

                    return false;
                }

                return true;
            }
        ));

        usort(
            $parsed,
            static fn (array $a, array $b): int => $a['prefix'] <=> $b['prefix']
                ?: $a['letter'] <=> $b['letter']
        );

        foreach ($parsed as $item) {
            $prefix = $item['prefix'];
            $letter = $item['letter'];
            $routePath = "/mots/commencant/{$prefix}/avec/{$letter}";

            $rows[] = [
                'route_path' => $routePath,
                'family' => Family::WORD_LIST_COMMENCANT_WITH_LETTER,
                'robots' => 'index,follow',
                'canonical_path' => $routePath,
                'sitemap_fragment' => 'commencant-avec-0001',
                'result_count' => $item['result_count'],
                'notes' => "maillage interne reel depuis /mots/commencant/{$prefix} (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) via App\\Search\\PrefixAvecLinksBuilder (list_counts, list_type 'start_with') -- couverture verifiee exhaustivement dans les deux sens 646/646 (reports/query-plans/commencant-avec-maillage.md). Nouvelle famille Family::WORD_LIST_COMMENCANT_WITH_LETTER (distincte de WORD_LIST_COMMENCANT et de WORD_LIST_COMBINED_WITH_LETTER, voir app/Seo/Family.php) : balayage complet 0/650 au-dessus du budget TTFB, ancrage toujours sur le prefixe (reports/query-plans/commencant-avec-no-length-full-sweep.md). 26 combinaisons degenerees (lettre avec == prefixe, D-032) exclues DIRECTEMENT AU PRECALCUL, jamais dans ce lot. CORRECTIF C-1 (2026-08-19) : controle de duplicat de contenu contre la page parente sans lettre avec (list_counts 'start') ajoute -- 0 ligne concernee sur ce lot. CORRECTIF I-A (2026-08-19, 2e audit sur D-037) : controle de duplicat de contenu ENTRE LETTRES AVEC SOEURS du meme prefixe ajoute (findSiblingContentDuplicates()) -- " . ($siblingDuplicateLines > 0 ? "{$siblingDuplicateLines} ligne(s) exclue(s)" : '0 ligne concernee, verifie exhaustivement sur les 26 prefixes') . ", compte final " . (646 - $siblingDuplicateLines) . "/646.",
            ];
        }
        break;

    case 'word_admitted':
        $statement = $pdo->prepare(
            'SELECT normalized FROM terms WHERE is_ods8 = 1 OR is_ods9 = 1 ORDER BY normalized LIMIT ? OFFSET ?'
        );
        $statement->execute([$limit, $offset]);
        $fragmentIndex = 1;
        $countInFragment = 0;

        foreach ($statement->fetchAll() as $i => $row) {
            if ($countInFragment >= 40_000) {
                $fragmentIndex++;
                $countInFragment = 0;
            }
            $countInFragment++;

            $slug = strtolower($row['normalized']);
            $rows[] = [
                'route_path' => "/mot/{$slug}",
                'family' => Family::WORD_ADMITTED,
                'robots' => 'index,follow',
                'canonical_path' => "/mot/{$slug}",
                'sitemap_fragment' => sprintf('words-%04d', $fragmentIndex),
                'result_count' => null,
                'notes' => "A COMPLETER : mot admis ODS8/ODS9, fiche atteinte depuis les listes /mots/... deja indexees et les relations d'autres fiches mot.",
            ];
        }
        $batchIdSuffix = sprintf('word_admitted-offset%d-limit%d', $offset, $limit);
        break;

    default:
        fwrite(STDERR, "famille inconnue ou non proposable automatiquement : {$kind}\n");
        exit(1);
}

// $batchId / $addedAt : deja fixes par le cas 'position' (regularisation d'un lot existant,
// voir l'entete du fichier) -- tous les autres cas gardent le comportement par defaut
// (identifiant + date du jour, une nouvelle proposition a chaque execution).
$batchId ??= $batchIdSuffix . '-proposed-' . gmdate('Y-m-d');
$addedAt ??= gmdate('Y-m-d');
$export = var_export(['batch_id' => $batchId, 'added_at' => $addedAt, 'rows' => $rows], true);

echo "<?php\n\ndeclare(strict_types=1);\n\n";
echo "// PROPOSITION generee par scripts/propose_seo_batch.php -- NON appliquee.\n";
echo '// ' . count($rows) . " ligne(s). Relire chaque 'notes', ajuster sitemap_fragment si necessaire\n";
echo "// (40 000 URL max par fragment, docs/05), PUIS lancer scripts/apply_seo_batch.php sur ce\n";
echo "// fichier -- jamais avant validation humaine explicite du lot (CLAUDE.md).\n\n";
echo "return {$export};\n";

fwrite(STDERR, sprintf("%d ligne(s) proposee(s) pour '%s' (redirige stdout vers un fichier pour les conserver)\n", count($rows), $kind));
