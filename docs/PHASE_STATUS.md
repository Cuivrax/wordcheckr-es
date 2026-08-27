# PHASE_STATUS

## Phase Courante

```text
Toutes les phases fonctionnelles (1 à 6) sont livrées, plus un enrichissement
  hors plan (D-018), la refonte de la home (rapprochement du prototype réel),
  le hub /mots et le correctif F3 (état d'erreur visible). L'audit consolidé
  final a tourné quatre passes au total (voir reports/phases2-6-after.md pour
  le détail complet de chacune) :
    1re passe  code-reviewer NO GO (4 bloquants) / code-optimizer GO
    2e passe   bloquants de la 1re passe corrigés, C1 (faux négatifs
               "contenant/avec/sans" sans ancrage) découvert et corrigé
    3e passe   NO GO sur les deux audits : la correction C1 réintroduisait
               un parcours quasi complet de la table (CLAUDE.md, Interdits),
               exposé à tout crawl via ~1,67M liens auto-générés
    4e passe   corrections appliquées (fusion de requêtes, retrait des liens
               sans ancrage, fuite de processus dans les tests) --
               code-reviewer : GO. code-optimizer : GO (gain de la fusion
               confirmé indépendamment, -20 à -55 %, 0 divergence sur 100+
               comparaisons total/truncated). Non-bloquants restants listés
               dans reports/phases2-6-after.md, dont un point Phase 7 réel :
               RelationsFinder::containingWords() sur /mot/{mot} approche le
               budget TTFB sous charge concurrente (p95 mesuré 252 ms à 8
               processus simultanés, hors périmètre de cette passe)
```

Mis à jour le 2026-08-21 (D-042, domaine wordcheckr.fr fixé ; D-041, correctif C-4
appliqué en production — 924 408 URL, prêt pour un 5e audit).

Décision explicite prise en cours de route (demande utilisateur) : construire
un site fonctionnel d'abord, un seul audit consolidé groupé avant mise en
ligne plutôt qu'un audit complet après chaque phase. Les points non bloquants
relevés par les audits Phase 1 (I2-I8, dédoublement de composants CSS, copy
provisoire...) n'ont jamais été refermés individuellement — à couvrir par
l'audit consolidé.

### Récapitulatif des phases livrées

```text
Phase 1  socle, home, fiche mot            /, /mot/{mot}, /verifier/{mot}
         GO après correction de 3 bloquants critiques (C1-C3, entrées
         malformées) et 2 bloquants de contraste (design-consistency-reviewer)
Phase 2  solveur                            /jouer/{lettres}
         plafond de sécurité mesuré (signatures candidates), jamais de scan
Phase 3  contraintes de recherche           /mots/...
         postings écartés après mesure (355 Mo, toujours trop lent) au profit
         d'une approche bornée sur les index existants
Phase 4  fiches riches (relations)          10 catégories sur /mot/{mot}
         budget mesuré et tenu : 9 requêtes dictionnaire + 1 registre SEO
         pour un mot admis, 4 + 1 pour un français non admis (< 10 par base,
         voir D-003 pour la convention de comptage entre les deux bases)
Phase 5  autocomplétion                     GET /api/suggest, combobox ARIA
Phase 6  registre SEO                       storage/seo_fr.sqlite, D-017
         838 248 URL en index,follow (403 060 admis + 435 120 français non
         admis + 68 pages de structure, dont le hub /mots ajouté après D-017)
         — décision explicite du propriétaire du produit, contre l'avis
         initial de l'agent seo-registry (garde-fou de rôle légitime, voir
         D-017). Rien n'est visible par le vrai Google avant la Phase 7.
         Total actualisé à 838 859 après D-025 (+611, commençant+terminant
         sans longueur), puis à 841 188 après D-028 (+2 329, famille
         position) — voir D-025 et D-028 ci-dessous.
Home     refonte champ unifié (rapprochement du prototype, deux <form>
         distincts depuis le correctif F1) + liens contextuels vers
         /mots/... (maillage interne, signalé par seo-registry)
Hub      /mots (App\Search\ExploreHub) : 66 liens (longueur/commençant/
         terminant) + outil "Contenant" borné à 3 lettres, comptes
         précalculés hors ligne (list_counts) après mesure d'un problème de
         performance réel (~500-1000 ms en GROUP BY live, corrigé)
D-018    nature grammaticale, genre, liens de conjugaison sur /mot/{mot}
         (hors plan initial, demande utilisateur) — aucune définition (D-004
         toujours en vigueur)
F3       état d'erreur visible (WCAG 3.3.1) sur /verifier, /jouer, /mots :
         bandeau role="alert" au lieu d'une redirection silencieuse
C1       correction (audit final, bloquant) : recherches "contenant/avec/
         sans" sans ancrage renvoyaient des faux négatifs silencieux au-delà
         des 10 000 premiers mots alphabétiques — voir D-019
D-022    filtre statut (Admis/Non Admis), tri par points, et maillage
         interne longueur × lettre sur /mots/... (hors plan initial,
         demande utilisateur) — is_admitted précalculé (jusqu'à 318x plus
         rapide en conditions réelles), voir reports/query-plans/
         status-filter-admitted.md
D-023    famille "position" (une lettre à une position précise,
         /mots/{N}-lettres/position/{P}/{lettre}) — réutilise le prédicat
         motif déjà mesuré, collapse des positions dégénérées (1re/dernière
         lettre) vers commençant/terminant pour éviter le contenu dupliqué
         constaté sur motif, voir reports/query-plans/position-family.md
D-024/   maillage interne commençant+terminant (611 combos sans longueur,
D-025    App\Search\LetterCombinedLinksBuilder) puis ouverture de
         Family::WORD_LIST_COMBINED à l'indexation pour ce même sous-
         ensemble — registre porté à 838 859 URL (838 248 + 611), 27
         fragments sitemap (+combined-0001), voir reports/query-plans/
         start-end-links.md
D-025bis 1er audit seo-technical-auditor sur D-025 : NO GO (régression de
         performance jamais mesurée sur la forme réellement publiée, jusqu'à
         1 211 ms mesuré ; documentation de famille incohérente) — corrigé
         (nouvel index idx_terms_startletter_endletter_normalized, 0/611
         combos au-dessus du budget après correctif, était 53/611 après une
         1re tentative insuffisante). 2e audit (2026-08-10) : GO, plus trois
         correctifs mineurs (garde-fou d'index manquant, portée annoncée
         inexacte, contradiction de documentation famille D-022/D-024) —
         voir reports/query-plans/prefix-suffix-anchor-fix.md
D-023bis maillage "avec {X}" → position exacte sur /mots/{N}-lettres/avec/{X}
         (App\Search\PositionLinksBuilder), voir reports/query-plans/
         position-links.md
D-024bis maillage "avec {X} sans {Y}" → longueur (App\Search\
         AvecSansLengthLinksBuilder), navigation uniquement (WORD_LIST_AVEC
         et WORD_LIST_SANS restent NEVER_SITEMAP en permanence, aucun gain
         SEO), voir reports/query-plans/avec-sans-length-links.md
D-025ter pages /mentions-legales, /confidentialite, /contact (formulaire
         mail() natif PHP, piège à bots, email jamais versé au dépôt --
         lu via la variable d'environnement SCRABBLE_CONTACT_EMAIL),
         identité éditeur/hébergeur vérifiée auprès de sources publiques,
         nom personnel/adresse complète/email volontairement absents
D-026    /mentions-legales, /confidentialite, /contact volontairement non
         indexées (noindex,follow par défaut, D-005) -- décision produit
         explicite, pas un oubli laissé au prochain audit
D-027    maillage interne commençant+terminant AVEC longueur (App\Search\
         LengthCombinedLinksBuilder, list_type length_start_end) --
         préalable exigé avant toute décision future d'ouverture de
         cette variante à l'indexation (0 lien entrant avant ce lot sur
         ~5 141 pages éligibles), aucune décision d'indexation prise,
         voir reports/query-plans/length-combined-links.md
D-028    Family::WORD_LIST_POSITION créée et ouverte à l'indexation :
         2 329 URL appliquées à storage/seo_fr.sqlite le 2026-08-11
         (0 orpheline au sens strict, 0/2366 au-dessus du budget TTFB,
         canonicals vérifiés 0 divergence) — registre 838 859 → 841 188,
         sitemaps 27 → 28 fragments (position-0001.xml, 2 329 URL),
         toutes les autres familles inchangées, voir reports/query-plans/
         position-full-sweep.md
D-028bis 1er audit seo-technical-auditor sur D-028 : NO GO (C1 maillage
         insuffisant — 1/2329 seulement lié depuis une page déjà
         indexée, le reste via /mots/{N}-lettres/avec/{X} qui n'est et
         ne sera jamais indexable ; C2 métriques manquantes ; C3 lot non
         reproductible) — performance et bornage jugés bons, non remis
         en cause. Corrigé : App\Search\LengthLinks/LengthLinksBuilder
         étendus (byPosition, section groupée <details> sur
         /mots/{N}-lettres, déjà indexée D-017 — couverture 1/2329 →
         2329/2329), cas 'position' ajouté à
         scripts/propose_seo_batch.php + lot versionné
         (scripts/seo-batches/), métriques publiées (17/2329 pages à
         1 résultat, liens moyens 1,00043 → 2,00043), prémisse fausse
         I3 corrigée (schema.sql, D-027, length-combined-links.md).
         Aucune nouvelle URL indexée par cette passe (registre inchangé
         à 841 188), voir reports/query-plans/position-length-maillage.md
         et reports/query-plans/position-batch-reproducibility.md
         2e audit (2026-08-17) : GO, C1/C2/C3/I3 tous revérifiés
         indépendamment (identité d'ensemble list_counts/lot versionné/
         sitemap, pas repris du rapport précédent). Volume de liens
         (+320 sur 15 lettres) accepté non bloquant, explicitement pas
         un précédent pour d'autres familles. Non bloquants restants :
         notes registre encore fondées sur l'ancien maillage /avec/,
         aucun test de cohérence lot<->registre<->maillage rendu,
         pagination de la famille non chiffrée (budget de crawl),
         compte affiché > compte servi sur 166 pages plafonnées à
         10 000, pas de lien retour vers la page longueur parente
D-029    ouverture en entonnoir de "avec" — palier 1 (longueur + 1
         lettre, /mots/{N}-lettres/avec/{X}) : nouvelle famille
         Family::WORD_LIST_AVEC_SINGLE_LETTER, 364 URL appliquées
         (14 longueurs × 26 lettres, 0 exclusion, 2 pages à 1 résultat
         gardées) — maillage déjà 100% couvert avant même l'application
         (App\Search\LengthLinksBuilder::byWith, D-022, depuis
         /mots/{N}-lettres déjà indexée), leçon de D-028bis appliquée
         dès le départ cette fois. 0/364 au-dessus du budget TTFB
         (toujours ancré sur length = ?, jamais un scan complet — voir
         WordListSolver::anchorClause() pour la distinction avec le cas
         général "avec" sans longueur, resté et restant bloqué en
         permanence). Registre 841 188 → 841 552, sitemaps 28 → 29
         fragments (avec-single-0001.xml). Audit (2026-08-17) : GO, sans
         bloquant.
D-030    palier 2 (longueur + 2 lettres, /mots/{N}-lettres/avec/{X}/{Y}) :
         nouvelle famille Family::WORD_LIST_AVEC_TWO_LETTERS, 4 276 URL
         appliquées (274 à 0 résultat exclues, 132 à 1 résultat gardées)
         — maillage construit ET vérifié dans les deux sens dans la même
         passe (leçon D-028 appliquée dès le départ). Investigation de
         performance : pics isolés jusqu'à 109s observés lors du travail
         (plusieurs agents lisant/écrivant storage/dictionary_fr.sqlite en
         parallèle), tranchée par une vérification finale propre (aucun
         autre agent actif) : 1/650 au-dessus du budget, contention
         SQLite entre agents confirmée comme cause, sans rapport avec la
         production (runtime lecture seule, D-001). Registre 841 552 →
         845 828, sitemaps 29 → 30 fragments (avec-pair-0001.xml).
         Palier 3 (~36 400 combinaisons brutes) non commencé, voir
         reports/query-plans/avec-length-1-letter-full-sweep.md et
         avec-length-2-letters-full-sweep.md
D-031    palier 3 (longueur + 3 lettres, /mots/{N}-lettres/avec/{X}/{Y}/{Z}) :
         nouvelle famille Family::WORD_LIST_AVEC_THREE_LETTERS, 28 827 URL
         appliquées (7 573 à 0 résultat exclues, 1 682 à 1 résultat
         gardées) — maillage vérifié dans les 3 sens (86 481 vérifications).
         Deux conditions posées par l'audit du palier 2 fermées avant
         l'ouverture : pagination des listes ancrées plafonnée à 3 pages
         suivies (I-2, aucun changement d'indexation, juste le suivi du
         lien), garde-fou R4 durci pour valider la forme de route_path
         par famille (I-4, testé en direct). Registre 845 828 → 874 655,
         sitemaps 30 → 31 fragments (avec-triple-0001.xml). Audit
         (2026-08-18) : GO, sans bloquant. Deux conditions posées avant
         la suite : séquencer la soumission des sitemaps par vagues en
         Phase 7 (874 655 URL, jamais d'un bloc), et fournir avant tout
         palier 4 un balayage propre complet sur les grandes longueurs
         (9-15) + le nombre de mots distincts derrière les pages à 1
         résultat. Palier 4 (~209 300 combinaisons brutes) non commencé,
         voir reports/query-plans/avec-length-3-letters-full-sweep.md
D-032    correctif WordListFilters::fromPath() : collapse silencieux de
         "avec/X" quand X est deja garanti par un commencant/terminant
         d'une seule lettre (meme mecanisme que le collapse position,
         D-023) -- corrige un bug reel (17/26 combinaisons
         commencant/X/avec/X affichaient un total tronque a 10 000 au
         lieu du vrai total, jusqu'a 224 205 pour R)
D-033    maillage commençant+terminant+avec (une lettre chacun),
         App\Search\StartEndWithLinksBuilder, depuis les 611 pages
         commençant+terminant déjà indexées -- 10 150 liens nets
         (11 348 bruts - 1 198 dégénérées exclues, D-032)
D-034    maillage commençant+avec (une lettre, sans terminant ni
         longueur), App\Search\PrefixAvecLinksBuilder, depuis les 26
         pages commençant déjà indexées -- 646 liens nets
D-035    ouverture à l'indexation de trois axes en une passe : D-027
         (commençant+terminant AVEC longueur, 5 141 URL,
         Family::WORD_LIST_COMBINED), D-033 (commençant+terminant+avec,
         10 150 URL, nouvelle Family::WORD_LIST_COMBINED_WITH_LETTER),
         multi-lettres commençant/terminant (37 557 URL, familles
         existantes). Deux arbitrages tranchés : volume de liens
         accepté (n'affecte jamais le sitemap, atténué par <details>),
         1 982 doublons de contenu exclus (même règle que D-025, page
         la plus courte reste canonique). Registre 874 655 → 927 503,
         sitemaps 31 → 35 fragments. Axe D-034 (avec+commençant, 646
         URL) NON couvert par ce lot -- classification distincte à
         instruire séparément
D-036    ouverture à l'indexation du 4e et dernier axe : commençant+avec
         (D-034, 646 URL), nouvelle Family::WORD_LIST_COMMENCANT_WITH_LETTER.
         Registre 927 503 → 928 149, sitemaps 35 → 36 fragments. LES
         QUATRE AXES COMMENÇANT/TERMINANT/AVEC SONT DÉSORMAIS TOUS
         APPLIQUÉS -- total +53 494 URL depuis le début de cette série
         (874 655 → 928 149).
         Audit consolidé (2026-08-18) : NO GO -- un seul bloquant (C-1) :
         axes 2 et 4 appliqués sans le contrôle de doublon de contenu
         parent/enfant déjà utilisé pour les axes 1 et 3 (preuve sur
         pièces : FAQ, XIPHO). Corrigé en D-037.
D-037    correctif du NO GO : 227 doublons de contenu retirés (axe 2
         uniquement, axe 4 confirmé à 0), vérifié par deux calculs
         indépendants (0 divergence). Registre 928 149 → 927 922.
         Garde-fou R4c/R4d durci en défense supplémentaire (I-1). 2e
         audit (2026-08-18) : GO, aucun bloquant. Point non bloquant
         trouvé (I-A) : doublons entre pages SŒURS (pas seulement
         parent/enfant), jamais mesurés — corrigé en D-038.
D-038    correctif du point I-A : 428 doublons de contenu entre pages
         sœurs retirés (axe 2 uniquement, axe 4 confirmé à 0), calculé
         par trois méthodes indépendantes côté maillage ET
         indépendamment côté registre — match exact (283 groupes,
         169 paires). Preuve sur pièces : paire X:M, 10 pages
         candidates réduites à 2 gagnantes (XALAM, XENODOCHIUM).
         Registre 927 922 → 927 494. 3e audit (2026-08-18) : NO GO —
         I-A confirmé fermé (10 paires recalculées indépendamment, 0
         faux positif/négatif), mais NOUVEAU bloquant (C-2) : aucun
         contrôle entre Family::WORD_LIST_COMBINED (axe 1, longueur)
         et WORD_LIST_COMBINED_WITH_LETTER (axe 2, avec), qui tranchent
         le même panier commençant+terminant sous deux angles
         différents. Preuve : XALAM sur les deux URL. Corrigé en D-039.
D-039    correctif du bloquant C-2 : 333 collisions croisées
         longueur×avec retirées (191 paires), calculées indépendamment
         des deux côtés (maillage + registre) — match exact. Règle de
         priorité : la variante longueur gagne (cohérent D-025).
         Registre 927 494 → 927 161.
         4e audit (2026-08-20) : NO GO — C-2 confirmé fermé (26
         nouvelles paires vérifiées, 0 réfutation source ODS8
         indépendante), mais NOUVEAU bloquant (C-3) : les trois paliers
         "avec" ancrés longueur (D-029/D-030/D-031, 33 467 URL)
         n'avaient jamais reçu AUCUN contrôle de doublon (ni
         parent/enfant, ni sœurs). Preuve : /mots/10-lettres/avec/w/x
         identique à ses 6 variantes palier 3, /mots/15-lettres/avec/w/x
         à ses 8 variantes, plus deux collisions palier1↔palier2.
         Second bloquant (C-4) : aucun garde-fou structurel — 4
         découvertes successives (C-1, I-A, C-2, C-3), toujours une
         liste figée par paire de familles, rien n'empêche une 5e
         récidive. Corrigé en D-040 (C-3) + outil générique en cours
         (C-4, voir ci-dessous).
D-040    correctif du bloquant C-3 : 664 doublons retirés (4
         parent1↔2 + 426 parent2↔3 transitif absorbé + 234 sœurs
         palier 3), calculés indépendamment des deux côtés (maillage +
         registre), chacun par deux méthodes internes distinctes — match
         exact. Vérification secondaire position×avec_single_letter :
         0 doublon, négatif et concluant. Registre 927 161 → 926 497.
         Outil scripts/apply_seo_batch.php --prune ajouté (le script ne
         faisait jusqu'ici que de l'upsert, jamais de suppression — un
         lot corrigé plus petit laissait les anciennes lignes
         orphelines). C-4 : construction d'un détecteur de doublons
         générique et rejouable (scripts/check_combinatorial_duplicates.php)
         balayant l'ensemble des familles combinatoires du registre en
         une seule passe plutôt qu'une liste figée par paire.
D-041    correctif du bloquant C-4 : le détecteur générique, exécuté sur
         l'état post-D-040 (10 familles, 88 315 lignes, 433,2s), a trouvé
         1 656 groupes de doublons résiduels jamais contrôlés (2 089
         lignes en excès), tous croisés entre familles — la plus grosse
         paire jamais comparée avant cet outil : commençant × terminant
         multi-lettres (axe 3, D-035), 408 groupes à elle seule. Règle
         de priorité généralisée (App\Search\DuplicatePageResolver::
         resolveDuplicateWinner()) : nombre de composants de contrainte,
         puis signature de rôle, puis canonicalPath() le plus petit.
         Calculée indépendamment des deux côtés (maillage + registre),
         échantillon croisé de 64 groupes chacun, 37/37 combinaisons de
         familles couvertes, 0 désaccord. Ferme aussi M-A au passage
         (3 lots D-035 axes 1/3 jamais versionnés, générés pour la
         première fois). Registre 926 497 → 924 408 (-2 089 exact,
         vérifié famille par famille). php tests/run.php : 40/40.
D-042    domaine de production fixé : wordcheckr.fr (décision
         utilisateur). config/sites/fr.php, robots.txt (directive
         Sitemap activée) et les 36 fragments de sitemap mis à jour —
         mêmes comptes, seul le domaine change. Ferme le point non
         bloquant I-C. Prêt pour un 5e audit.
D-043    définitions lexicales (révise D-004) : nouvelle table word_senses
         (schema.sql), pipeline complet scripts/lib/reference_definitions.py
         + scripts/generate_word_senses.py + scripts/verify_word_senses.py
         + scripts/apply_verification_fixes.py — gabarits gratuits (palier
         0), reformulation LLM ancrée sur Kartmaan/kaikki.org (paliers 1-2,
         jamais la source copiée telle quelle), garde-fou anti-copie
         (seuil absolu + relatif), scan qualité, vérification à deux
         étages. TERMINÉ : 403 060/403 060 mots admis couverts (100%),
         418 774 sens (template 331 646 / kartmaan 56 519 / kaikki 17 296 /
         llm-only 13 313), verification systematique complete (398 483
         correct, 20 029 incorrect, 262 incertain), corrections appliquees
         (4 652 reelles) apres decouverte d'un biais systemique du
         verificateur : 13 284 des ~13 785 "corrections" proposees
         etaient des hallucinations sur des phrases-gabarit deja
         correctes par construction (source=template) — desormais
         bloquees par une regle generale (apply_verification_fixes.py,
         jamais applique de correction a une entree source=template).
         Temperature DeepSeek 0.3 -> 0.9 (a 0.3, copie quasi-verbatim de
         la reference des qu'elle est fournie ; 1.3 teste et ecarte,
         introduit des erreurs factuelles). Residu de ~9 mots au total
         verifies et corriges/ecrits a la main (Larousse/Wiktionnaire/
         WebSearch) apres plusieurs passes de convergence automatique et
         un dernier lot "sans reference" (paliers 1-2 forces en palier 3
         pour les cas ou DeepSeek recopiait systematiquement une
         reference trop courte). Incidents reels trouves et corriges
         pendant la mise en oeuvre (faux positifs/negatifs du
         verificateur automatise, bug de suppression par terme au lieu
         de par sens, verification externe Larousse, hallucination
         systemique du verificateur sur les gabarits) : voir D-043 pour
         le detail complet. Correctif de budget requetes associe (fusion
         TermLookup::neighbours(), reports/query-plans/
         d043-neighbour-merge.md) : fiche mot admise reste a 9 requetes
         dictionnaire, pas 10. app/View/word.php + public/assets/css/
         site.css : rendu carte par sens, style creme/bois du site.
         storage/dictionary_fr.sqlite reconstruite avec le jeu complet,
         php tests/run.php : 42/42. Non audité par code-reviewer/
         seo-technical-auditor à ce stade.
```

Base de production reconstruite (D-022) : 838 180 termes inchangés,
integrity_check = ok, déterminisme vérifié (reconstruction x2, comparaison
octet à octet). 38/38 fichiers de tests verts (`php tests/run.php`).

Note : le second passage `code-reviewer` sur la Phase 0 corrigée n'a jamais été
tracé formellement (historique conservé ci-dessous). Non bloquant pour la suite.

## Premier Audit — NO GO

Le premier audit `code-reviewer` de la Phase 0 a rendu **NO GO**. Les données étaient
correctement importées, mais quatre défauts de traçabilité et de cohérence bloquaient, plus un
défaut de fond (I2) qui exigeait une décision.

```text
I2  bloquant de fond   9 105 formes ODS8 de plus de 15 lettres marquées admises
                       à tort — corrigé, voir D-010 révisée ci-dessous
B1  traçabilité        rapport AFTER absent                     — corrigé
B2  traçabilité        reports/query-plans/ jamais écrit         — corrigé
B3  cohérence          comptes périmés dans DECISIONS/CLAUDE.md — corrigé
B4  cohérence          import-summary.json : champ manquant et
                       addition d'unités hétérogènes            — corrigé
I1  non bloquant       schema.sql:31 orientait vers un LIKE
                       qui force un SCAN                        — corrigé
```

## Livré

```text
schema.sql                        schéma canonique
scripts/lib/normalize.py          normalisation, score, signature, reversed,
                                   plafond de 15 lettres (D-010 révisée)
scripts/import_fr.py              import déterministe et rejouable
scripts/download_hbenbel.py       seconde source française
scripts/bench_queries.py          plans de requêtes persistés
storage/dictionary_fr.sqlite      838 180 termes, 154,5 Mo à la sortie de la Phase 0,
                                   integrity ok (base reconstruite depuis, D-018 :
                                   172,6 Mo au 2026-08-06 ; D-022 : 236,5 Mo au
                                   2026-08-09, mêmes 838 180 termes -- hausse
                                   attribuable aux 3 nouveaux index composés)
reports/                          rapports + audit BEFORE + rapport AFTER
reports/query-plans/phase0.md     7 requêtes témoins, plans et timings
8 agents installés dans .claude/agents/
PHP 8.4.24 local avec pdo_sqlite, sqlite3, mbstring, intl, OPcache
```

## Comptes De La Base — Vérifiés Exhaustivement, Pas Par Échantillon

```text
termes                   838 180
admis ODS8               402 325   (valeur officielle Larousse, édition 2020)
admis ODS9               402 996   (= ODS8 et ODS9 + ODS9 seulement)
ODS8 seulement                64
ODS9 seulement               735
ODS8 et ODS9             402 261
français non admis       435 120
collisions fusionnées     48 319
```

Quatre sources : ODS8 et patch ODS9 pour l'admissibilité, Kartmaan et hbenbel pour la couche
française non admise (D-014). Aucune forme de plus de 15 lettres en base (D-010 révisée) —
injouable sur un plateau, et source d'une erreur factuelle si elle avait été affichée comme
admise.

## Porte Phase 0

```text
integrity_check = ok                                              OK
comptes vérifiés exhaustivement (838 180 lignes, pas un échantillon) OK
score/signature/reversed : 0 divergence sur les 838 180 lignes     OK
déterminisme : fichier .sqlite reconstruit BYTE-IDENTIQUE          OK
7 requêtes témoins, toutes via index, persistées dans
  reports/query-plans/phase0.md, médiane 0,07 à 0,18 ms            OK
rapport AFTER écrit                                                OK
comptes des fichiers partagés resynchronisés                       OK
audit code-reviewer (nouveau tour)                                 EN ATTENTE
```

## Bloquants

```text
aucun bloquant technique restant après correction
```

## Points À Trancher Avant La Phase 1

```text
QUEULEULEU, exemple emblématique du brief pour « français non admis », est
  absent des deux sources françaises — il n'existe que dans la locution
  « à la queue leu leu ». Remplaçants vérifiés en base : GHOSTER (recommandé),
  MACRONISTE. Voir docs/08_PROMPTS_PHASES.md.
le rollout SEO doit être dimensionné sur ~838 000 fiches, pas ~412 000
```

## Prochaine Action

```text
lancer l'audit consolidé avant Phase 7 (Production) :
  code-reviewer, code-optimizer, design-consistency-reviewer,
  seo-technical-auditor — un seul NO GO bloque la mise en ligne
```

## GO / NO GO

```text
Phase 0 — GO implicite (données inchangées depuis, jamais retracé formellement)
Phase 1 — GO (audit formel, 2 tours)
Phases 2-6, home, D-018, D-022, D-023, D-023bis, D-024bis, D-025ter,
  D-026, D-027, D-032, D-033, D-034 — jamais audités formellement,
  code fonctionnel et testé (38/38), en attente de l'audit consolidé
  ci-dessus (ou d'un audit dédié pour D-035/D-036, vu le volume appliqué)
D-024/D-025/D-025bis — GO (2e audit, 2026-08-10) après correction du 1er
  NO GO (régression de performance + documentation). Non bloquants encore
  ouverts : I-1 (arbitrage des 52 paires non versionné durablement), I-2
  (aucun contrôle de cohérence maillage/registre), I-3 (pages de la famille
  sans lien retour vers /mots), C-3 (domaine CHANGE-ME.exemple.fr et
  robots.txt sans directive Sitemap — bloquant pour la Phase 7, pas pour ce
  lot)
D-028/D-028bis — 1er audit NO GO (2026-08-11, maillage/métriques/
  traçabilité) corrigé, 2e audit GO (2026-08-17), détail ci-dessus
D-029 — audit GO (2026-08-17), sans bloquant, détail ci-dessus
D-030 — audit GO (2026-08-18), sans bloquant. Deux conditions
  explicites posées avant toute proposition de palier 3 (pas après un
  NO GO) : chiffrer/trancher la surface de pagination des pages
  ancrées, et faire valider la forme de route_path par famille dans
  scripts/apply_seo_batch.php (R4) — détail ci-dessus
D-031 — audit GO (2026-08-18), sans bloquant. Deux conditions
  explicites posées avant la suite : séquencer la soumission des
  sitemaps par vagues en Phase 7 (874 655 URL), et fournir avant tout
  palier 4 un balayage propre grandes longueurs + le compte de mots
  distincts derrière les pages à 1 résultat — détail ci-dessus
D-035 — lot de 52 848 URL appliqué (3 axes), détail ci-dessus
D-036 — lot de 646 URL appliqué (4e axe, commençant+avec), détail
  ci-dessus. Clôt la série des quatre axes commençant/terminant/avec
D-027/D-035/D-036/D-037/D-038/D-039/D-040/D-041 — 1er audit consolidé
  NO GO (2026-08-18, C-1 : doublons de contenu non exclus sur les axes
  2 et 4) — corrigé en D-037 (227 lignes). 2e audit : GO, point non
  bloquant (I-A, doublons entre pages sœurs) — corrigé en D-038 (428
  lignes). 3e audit : NO GO (C-2, doublons croisés entre les familles
  longueur et avec du même panier commençant+terminant) — corrigé en
  D-039 (333 lignes). 4e audit : NO GO (C-2 confirmé fermé ; NOUVEAU
  C-3, paliers "avec" ancrés longueur jamais contrôlés du tout, +
  C-4, aucun garde-fou structurel empêchant une 5e récidive) — C-3
  corrigé en D-040 (664 lignes), C-4 corrigé en D-041 par un
  détecteur générique rejouable (1 656 groupes trouvés, 2 089 lignes
  retirées). Cinq corrections consécutives au total, chacune calculée
  indépendamment des deux côtés (maillage + registre) avec match exact
  à chaque fois. Domaine de production fixé en D-042 (wordcheckr.fr).
  Registre final : 924 408 URL. En attente du 5e audit.
```
