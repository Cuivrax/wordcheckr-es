<?php

declare(strict_types=1);

namespace App\Seo;

/**
 * Liste fermée des familles de reporting/gouvernance du registre SEO (Phase 6, docs/08).
 *
 * Une famille correspond à un type de route, pas à une route individuelle — elle sert à :
 * - produire les métriques quantifiées exigées par lot (URL par famille) ;
 * - appliquer les règles dures par famille (ex. NEVER_SITEMAP ci-dessous), à la fois dans
 *   scripts/apply_seo_batch.php (refus à l'écriture) et dans les rapports de rollout.
 *
 * Correspondance avec les préfixes de fragments documentés (docs/05_URL_SEO_INDEXATION.md,
 * section Sitemaps) : words-*, invalid-french-*, starts-*, ends-*, contains-*, letters-*.
 * CORE n'est pas dans la liste documentée — extension minimale et délibérée pour couvrir la
 * home (aucune autre route statique connue à ce jour) ; signalée pour validation, pas ajoutée
 * silencieusement (voir rapport AFTER de l'agent seo-registry).
 */
final class Family
{
    public const HOME = 'home';
    public const WORD_ADMITTED = 'word_admitted';
    public const WORD_FRENCH_NOT_ADMITTED = 'word_french_not_admitted';
    public const WORD_LIST_LENGTH = 'word_list_length';
    public const WORD_LIST_COMMENCANT = 'word_list_commencant';
    public const WORD_LIST_TERMINANT = 'word_list_terminant';
    public const WORD_LIST_CONTENANT = 'word_list_contenant';
    public const WORD_LIST_AVEC = 'word_list_avec';
    public const WORD_LIST_SANS = 'word_list_sans';
    public const WORD_LIST_MOTIF = 'word_list_motif';
    public const WORD_LIST_COMBINED = 'word_list_combined';
    public const WORD_LIST_POSITION = 'word_list_position';
    public const WORD_LIST_AVEC_SINGLE_LETTER = 'word_list_avec_single_letter';
    public const WORD_LIST_AVEC_TWO_LETTERS = 'word_list_avec_two_letters';
    public const WORD_LIST_AVEC_THREE_LETTERS = 'word_list_avec_three_letters';
    public const WORD_LIST_COMBINED_WITH_LETTER = 'word_list_combined_with_letter';
    public const WORD_LIST_COMMENCANT_WITH_LETTER = 'word_list_commencant_with_letter';
    public const RACK = 'rack';

    /** @var list<string> */
    public const ALL = [
        self::HOME,
        self::WORD_ADMITTED,
        self::WORD_FRENCH_NOT_ADMITTED,
        self::WORD_LIST_LENGTH,
        self::WORD_LIST_COMMENCANT,
        self::WORD_LIST_TERMINANT,
        self::WORD_LIST_CONTENANT,
        self::WORD_LIST_AVEC,
        self::WORD_LIST_SANS,
        self::WORD_LIST_MOTIF,
        self::WORD_LIST_COMBINED,
        self::WORD_LIST_POSITION,
        self::WORD_LIST_AVEC_SINGLE_LETTER,
        self::WORD_LIST_AVEC_TWO_LETTERS,
        self::WORD_LIST_AVEC_THREE_LETTERS,
        self::WORD_LIST_COMBINED_WITH_LETTER,
        self::WORD_LIST_COMMENCANT_WITH_LETTER,
        self::RACK,
    ];

    /**
     * Familles dont l'espace d'URL est combinatoire, potentiellement non borné en pratique
     * (contenant/avec/sans/motif : toute sous-chaîne, tout multiensemble de lettres, toute
     * combinaison de cases connues — docs/05 n'en documente d'ailleurs aucun préfixe de
     * sitemap, contrairement à longueur/commençant/terminant). Contrainte dure du projet :
     * "Refuse infinite letter/sequence combinations as indexable by default." Ces familles ne
     * doivent JAMAIS recevoir de sitemap_fragment, quel que soit le lot — appliqué en dur par
     * scripts/apply_seo_batch.php, pas seulement documenté ici.
     *
     * RACK (/jouer/{lettres}) est logé à la même enseigne : un tirage est lui aussi une
     * combinaison quasi illimitée (15 caractères, jokers compris), et docs/05 ne documente
     * aucun fragment de sitemap pour cette route.
     *
     * WORD_LIST_COMBINED (commençant + terminant, avec ou sans longueur) n'a JAMAIS eu sa place
     * ici — retiré le 2026-08-09 (D-024 correctif, D-025) après analyse dédiée de l'agent
     * seo-registry.
     *
     * PRÉCISION (audit du lot D-025, seo-technical-auditor, constat C-2) : "26×26 = 676 sans
     * longueur, 14×26×26 = 9 464 au plus avec longueur" n'est PAS une borne de la famille au
     * sens strict — WordListFilters::readSingleLetterRun() accepte des préfixes/suffixes de 1 à
     * 15 lettres, l'espace complet de la famille n'est donc pas borné en général. C'est une
     * borne du LOT réellement appliqué (D-025 : uniquement préfixe et suffixe D'UNE SEULE
     * LETTRE CHACUN, SANS longueur, 611 lignes) — la seule forme mesurée sûre à ce jour, après
     * correctif de performance D-025bis (idx_terms_startletter_endletter_normalized, schema.sql
     * — sans cet index, cette même forme mesurait jusqu'à 6 675 ms sur certaines combinaisons,
     * voir reports/query-plans/prefix-suffix-anchor-fix.md). Tout lot futur touchant un préfixe
     * ou un suffixe de plus d'une lettre, ou la variante AVEC longueur, exige sa PROPRE mesure
     * avant proposition — ne jamais réutiliser cette justification de volume pour un périmètre
     * plus large que celui réellement mesuré. Voir scripts/propose_seo_batch.php (cas
     * 'combined') pour le périmètre réellement généré et son maillage interne réel préalable
     * (D-024, App\Search\LetterCombinedLinksBuilder), pas cette constante qui ne fait
     * qu'autoriser un sitemap, jamais décider seule qu'une ligne précise doit y figurer.
     *
     * WORD_LIST_POSITION (D-023, une seule lettre connue à une seule position, ex.
     * "/mots/9-lettres/position/3/a") n'a jamais figuré ici — ajoutée directement hors de
     * NEVER_SITEMAP le 2026-08-10 (analyse dédiée de l'agent seo-registry, reports/query-plans/
     * position-full-sweep.md), contrairement à MOTIF (juste au-dessus, plusieurs lettres
     * connues simultanément, 2^15 combinaisons par longueur, jamais borné). "position" exige
     * TOUJOURS une longueur explicite (WordListFilters::fromPath()), ce qui borne l'espace réel
     * à 2 366 combinaisons (26 lettres × positions 2 à longueur-1 × 14 longueurs) — borne de la
     * FAMILLE elle-même cette fois, pas seulement d'un lot particulier comme pour
     * WORD_LIST_COMBINED ci-dessus : WordListFilters::readPosition() n'accepte qu'un seul
     * couple (position, lettre), il n'existe pas de variante multi-position/multi-lettre sous
     * ce mot-clé (ça, c'est motif). Balayage complet des 2 366 combinaisons réelles (pas un
     * échantillon, même discipline que D-025bis) : 0/2 366 au-dessus du budget TTFB p95 < 250 ms
     * (p95 = 57,4 ms, max = 129,3 ms), voir reports/query-plans/position-full-sweep.md.
     *
     * WORD_LIST_AVEC_SINGLE_LETTER (demande produit du 2026-08-17 : ouverture progressive de
     * "avec" à l'indexation, en entonnoir, une lettre à la fois — PALIER 1) n'a jamais figuré
     * ici. Carve-out de l'espace général de WORD_LIST_AVEC (ci-dessus, multiensemble de
     * lettres, PERMANENT dans NEVER_SITEMAP), exactement comme WORD_LIST_POSITION a été extrait
     * de l'espace général de MOTIF — même logique, jamais la même constante que l'espace
     * d'origine. Portée du palier 1, STRICTEMENT : longueur explicite ET EXACTEMENT une lettre
     * "avec" (occurrence unique, minCount=1, WordListFilters::readLetterMultiset() avec un seul
     * segment), SANS aucune autre contrainte (pas de commençant/contenant/terminant/position/
     * sans/motif, pas de statut/tri — ces raffinements restent hors de ce lot, jamais mesurés
     * combinés à "avec", même arbitrage que celui déjà posé pour WORD_LIST_POSITION en D-023/
     * D-028) — 14 longueurs × 26 lettres = 364 combinaisons au plus, borne de la famille
     * elle-même sur ce périmètre précis. Balayage complet des 364 combinaisons réelles via le
     * vrai solveur (pas un échantillon) : 364/364 à ≥ 1 résultat, 0/364 au-dessus du budget TTFB
     * p95 < 250 ms (p50 = 36,6 ms, p95 = 90,2 ms, max = 168,0 ms), voir reports/query-plans/
     * avec-length-1-letter-full-sweep.md (agent data-engine). Maillage interne déjà 100% couvert
     * sans travail supplémentaire : App\Search\LengthLinksBuilder::build()->byWith, câblé depuis
     * /mots/{N}-lettres (déjà indexée, Family::WORD_LIST_LENGTH, D-017), vérifié exhaustivement
     * 364/364 dans le même rapport.
     *
     * AVERTISSEMENT explicite pour tout futur palier (3 lettres...) : ce nom désigne
     * SPÉCIFIQUEMENT le sous-ensemble à UNE SEULE lettre — le palier 2 ci-dessous
     * (WORD_LIST_AVEC_TWO_LETTERS) n'a PAS le même espace combinatoire ni la même mesure de
     * performance et a reçu SA PROPRE constante Family, sa propre mesure complète et sa propre
     * décision de lot — ne jamais réutiliser WORD_LIST_AVEC_SINGLE_LETTER pour un périmètre plus
     * large que "longueur + une seule lettre", exactement la même discipline que la mise en garde
     * déjà posée ci-dessus pour WORD_LIST_COMBINED (ne pas réutiliser sa justification de volume
     * pour un périmètre non mesuré).
     *
     * WORD_LIST_AVEC_TWO_LETTERS (demande produit du 2026-08-17, PALIER 2 de l'ouverture en
     * entonnoir de "avec", agent data-engine pour la mesure/maillage, agent seo-registry pour
     * cette classification et l'application du lot) — n'a jamais figuré ici. Sous-ensemble
     * DISTINCT de WORD_LIST_AVEC_SINGLE_LETTER (ci-dessus, palier 1) ET de WORD_LIST_AVEC
     * (multiensemble général, ci-dessous, PERMANENT dans NEVER_SITEMAP) : longueur explicite ET
     * EXACTEMENT DEUX lettres "avec" DISTINCTES (occurrence unique chacune, minCount=1 sur
     * chacune) — 14 longueurs × C(26,2) = 325 paires = 4 550 combinaisons au plus, borne de la
     * famille elle-même sur ce périmètre précis (list_key = "{longueur}:{lettre1}:{lettre2}",
     * lettre1 < lettre2 alphabétiquement, list_type 'length_with_pair', schema.sql). Balayage
     * complet des 4 550 combinaisons réelles via le vrai solveur (agent data-engine, pas un
     * échantillon) : 4 276/4 550 à ≥ 1 résultat (274 à 0 résultat, jamais indexées) — voir
     * reports/query-plans/avec-length-2-letters-full-sweep.md pour le détail complet, dont un
     * signal de bruit de mesure transitoire trouvé, investigué et documenté (trois balayages
     * complets : 75/4550, 0/4550, 3/4550 selon le run, jamais reproduit en isolation répétée 15×
     * PAR data-engine). RE-VÉRIFIÉ indépendamment par l'agent seo-registry avant application
     * (deux balayages complets supplémentaires du sous-ensemble longueur 12+13, celui qui avait
     * montré les pics : 52/650 puis 94/650 au-dessus du budget selon le run, jusqu'à
     * 109 643,982 ms observé au pire — plus sévère que ce que data-engine avait mesuré, mais dans
     * le même ordre de grandeur en proportion que leur run 1 une fois restreint à la longueur 12
     * (75/325 = 23,1 % chez data-engine, 83/325 = 25,5 % dans la pire passe seo-registry)).
     * NUANCE IMPORTANTE, à ne pas simplifier à tort : la première vérification isolée répétée
     * (10×) faite par seo-registry, immédiatement après le premier balayage complet, A REPRODUIT
     * des dépassements sur 10/13 des cas déjà cités par data-engine (ex. 12-lettres/avec/e/f :
     * moyenne 458,7 ms, max 1 331,8 ms sur 10 répétitions) — contrairement à une affirmation
     * initiale trop optimiste de cette même entrée, corrigée après relecture des données brutes.
     * Une SECONDE vérification isolée des 13 mêmes cas, faite après le second balayage complet
     * (donc à un moment différent), n'a RIEN reproduit (max 108,9 ms), tout comme un test dédié
     * (20×) sur les 3 pires cas du second balayage (max 122,7 ms, alors que le même cas avait
     * atteint 109 643,982 ms pendant le balayage). Lecture retenue : la reproduction en isolation
     * dépend du MOMENT du test, pas de la requête testée — signature d'une contention système
     * transitoire corrélée à une activité disque/CPU intense qui vient de se terminer (balayage
     * de plusieurs minutes), pas d'un défaut de plan de requête (EXPLAIN QUERY PLAN inchangé,
     * jamais un SCAN complet, structurellement identique au palier 1). Un trafic de production
     * réel (requêtes HTTP isolées, espacées dans le temps) ne reproduit pas ce déclencheur
     * précis — mais la sévérité observée cette fois renforce la recommandation déjà posée par
     * data-engine de re-vérifier en conditions réelles o2switch avant toute mise en ligne
     * effective (Phase 7), et d'éviter explicitement tout job de build/rollout concurrent au
     * trafic live sur cet hébergement mutualisé. Maillage interne déjà construit ET vérifié
     * exhaustivement dans les deux sens (App\Search\AvecTwoLettersLinksBuilder, depuis les 364
     * pages palier 1, déjà indexées) avant toute application du lot — couverture 4 276/4 276
     * (100 %), même discipline que D-029.
     *
     * WORD_LIST_AVEC_THREE_LETTERS (demande produit du 2026-08-18, PALIER 3 de l'ouverture en
     * entonnoir de "avec" à l'indexation, mesure/maillage/chiffrage de la surface de pagination
     * par l'agent data-engine — reports/query-plans/avec-length-3-letters-full-sweep.md —
     * classification et application du lot par l'agent seo-registry) — n'a jamais figuré ici.
     * Sous-ensemble DISTINCT de WORD_LIST_AVEC_TWO_LETTERS (palier 2, ci-dessus),
     * WORD_LIST_AVEC_SINGLE_LETTER (palier 1) ET de WORD_LIST_AVEC (multiensemble général,
     * ci-dessous, PERMANENT dans NEVER_SITEMAP) : longueur explicite ET EXACTEMENT TROIS lettres
     * "avec" DISTINCTES (occurrence unique chacune, minCount=1 sur chacune) — 14 longueurs ×
     * C(26,3) = 2 600 triplets = 36 400 combinaisons au plus, borne de la famille elle-même sur ce
     * périmètre précis (list_key = "{longueur}:{lettre1}:{lettre2}:{lettre3}", lettre1 < lettre2 <
     * lettre3 alphabétiquement, list_type 'length_with_triple', schema.sql). Balayage complet des
     * 36 400 combinaisons réelles via le vrai solveur (agent data-engine, UN SEUL passage complet,
     * demande produit explicite — le bruit de mesure de D-030/palier 2 avait été tranché comme une
     * contention entre agents concurrents, condition absente ici) : 28 827/36 400 à ≥ 1 résultat
     * (7 573 à 0 résultat, jamais indexées ; 1 682 à exactement 1 résultat, GARDÉES, même consigne
     * produit que les paliers 1/2). 683/36 400 (1,88 %) au-dessus du budget TTFB p95 < 250 ms dans
     * le balayage brut, concentrés à 18/20 sur la SEULE longueur 8 (partition PLUS PETITE que les
     * longueurs habituellement les plus coûteuses) — investigué en détail : 0/10 cas isolés
     * reproduisent (15× chacun), 0/2 600 sur le re-balayage complet de la longueur 8 entière,
     * `COUNT()` simple sain, `EXPLAIN QUERY PLAN` identique et stable partout (`SEARCH terms USING
     * INDEX idx_terms_length_normalized (length=?)`, jamais un `SCAN TABLE`) — cause retenue :
     * contention transitoire coïncidant avec un redémarrage de l'environnement de développement
     * SURVENU PENDANT ce balayage, sans rapport avec le plan de requête ni la production (runtime
     * lecture seule, D-001). Voir reports/query-plans/avec-length-3-letters-full-sweep.md pour le
     * détail complet de cette investigation — non re-bloquant, la vérification ciblée (10 cas ×15
     * + longueur 8 entière re-balayée, 0/2 610 mesures au-dessus du budget) a été jugée suffisante
     * par data-engine ET revérifiée indépendamment ici (voir le rapport AFTER de ce lot) avant
     * application. Maillage interne construit ET vérifié exhaustivement DANS LES TROIS SENS dès
     * cette même passe (App\Search\AvecThreeLettersLinksBuilder, depuis les 4 276 pages palier 2,
     * déjà indexées) — couverture 28 827/28 827 (100 %), chaîne complète à trois sauts vérifiée à
     * chaque maillon : /mots/{N}-lettres (indexée) → avec/{X} (palier 1) → avec/{X}/{Y} (palier 2)
     * → avec/{X}/{Y}/{Z} (palier 3, ce lot).
     *
     * AVERTISSEMENT explicite pour tout futur palier (4 lettres...) : ce nom désigne
     * SPÉCIFIQUEMENT le sous-ensemble à EXACTEMENT TROIS lettres — un futur palier 4 devra
     * recevoir SA PROPRE constante Family, sa propre mesure complète et sa propre décision de lot,
     * jamais réutiliser WORD_LIST_AVEC_THREE_LETTERS pour un périmètre plus large, même discipline
     * que les avertissements déjà posés ci-dessus pour WORD_LIST_COMBINED et
     * WORD_LIST_AVEC_SINGLE_LETTER.
     *
     * WORD_LIST_COMBINED_WITH_LETTER (demande produit du 2026-08-18, maillage/mesure de
     * performance déjà faits par l'agent data-engine —
     * reports/query-plans/commencant-terminant-avec-full-sweep.md et
     * reports/query-plans/commencant-terminant-avec-maillage.md — classification et application
     * du lot par l'agent seo-registry) — n'a jamais figuré ici. NOUVELLE classification,
     * DÉLIBÉRÉMENT DISTINCTE de WORD_LIST_COMBINED (commençant + terminant seuls, avec ou sans
     * longueur) : cette famille ajoute une TROISIÈME dimension libre à l'URL (une lettre "avec"
     * quelconque parmi 26, jusqu'à 26 × les 611 paires commençant+terminant réelles = 17 576
     * combinaisons théoriques par paire, contre 26×26 = 676 pour la paire seule) — exactement le
     * type d'extension que le docblock de WORD_LIST_COMBINED ci-dessus met déjà en garde de ne
     * jamais fondre dans sa propre justification de volume. Périmètre STRICT : préfixe ET suffixe
     * chacun d'UNE SEULE lettre, SANS longueur, PLUS une lettre "avec" d'occurrence unique
     * (minCount=1) — ni la variante avec longueur, ni un préfixe/suffixe multi-lettres, ni
     * plusieurs lettres "avec" simultanées ne sont couverts par ce nom, exactement la même
     * discipline « ne jamais réutiliser cette constante pour un périmètre plus large que celui
     * réellement mesuré » déjà appliquée aux paliers de WORD_LIST_AVEC_*.
     * Route : `/mots/commencant/{X}/terminant/{Y}/avec/{Z}` — 611 paires commençant+terminant
     * réelles (déjà indexées, WORD_LIST_COMBINED, D-024/D-025) × 26 lettres = 15 886 combinaisons
     * réellement candidates, 11 348 à ≥ 1 résultat (`list_counts`, `list_type = 'start_end_with'`,
     * calcul PHP mesuré contre l'alternative SQL avant choix : 3,945 s contre 5,195 s, mêmes
     * lignes produites). Balayage complet des 15 886 combinaisons réelles via le vrai solveur :
     * 0/15 886 au-dessus du budget TTFB p95 < 250 ms, toujours ancré sur
     * `idx_terms_startletter_endletter_normalized` (D-025bis), "avec" en résidu `instr()` peu
     * coûteux.
     * Interaction avec D-032 (collapse "avec/X" redondant avec un commençant/terminant d'une
     * seule lettre) : 1 198 des 11 348 lignes précalculées sont DÉGÉNÉRÉES (la lettre "avec" égale
     * la lettre de début ou de fin — leur URL collapse silencieusement vers la page parente
     * elle-même, `WordListFilters::fromPath()`) — exclues du maillage ET de tout lot
     * d'indexation, jamais un alias vers une page qui EST déjà elle-même la page indexée (R3).
     * CORRECTIF C-1 (2026-08-19, audit seo-technical-auditor consolidé sur D-035/D-036,
     * bloquant) : 227 lignes SUPPLÉMENTAIRES sont des doublons de CONTENU avec la page parente
     * SANS lettre "avec" (`Family::WORD_LIST_COMBINED`, `list_type = 'start_end'`) — la lettre
     * "avec" y est bien DIFFÉRENTE du début/de la fin (donc pas dégénérée D-032 ci-dessus), mais
     * son compte égale EXACTEMENT celui de la page parente : TOUS les mots de la page parente
     * contiennent déjà cette lettre, même contenu, page parente déjà gagnante canonique (R3) —
     * exactement le même contrôle que celui déjà appliqué au cas 'combined_with_length'
     * (D-027), qui manquait ici avant ce correctif. Preuve concrète (paires F:Q/XIPHO) et détail
     * complet dans `scripts/propose_seo_batch.php` (cas `'combined_with_letter'`). Périmètre
     * réellement indexable : 9 923 pages (11 348 − 1 198 − 227), dont 1 385 à exactement
     * 1 résultat (GARDÉES, même consigne produit que tous les paliers "avec" précédents — jamais
     * sur le seul compteur, docs/05). Maillage interne construit et vérifié exhaustivement dans
     * les deux sens sur les 611 pages sources réelles (`App\Search\StartEndWithLinksBuilder`,
     * depuis `/mots/commencant/{X}/terminant/{Y}`, déjà indexées) : couverture inchangée par ce
     * correctif — les 227 pages retirées n'étaient de toute façon jamais indexables (R3), 0 lien
     * mort, 0 doublon, 0 fuite dégénérée — voir
     * reports/query-plans/commencant-terminant-avec-maillage.md pour le détail complet.
     * CORRECTIF I-A (2026-08-19, 2e audit seo-technical-auditor sur D-037, non bloquant) : C-1
     * ci-dessus ne compare une ligne "avec" qu'À SA PROPRE page parente (vertical, parent/enfant)
     * — jamais aux AUTRES lettres "avec" DU MÊME parent ENTRE ELLES (horizontal, entre lignes
     * SŒURS). Pour un panier parent petit, plusieurs lettres "avec" DISTINCTES (chacune déjà
     * différente du début/de la fin, donc pas dégénérée D-032 ; chacune déjà différente du compte
     * du parent, donc pas doublon C-1) peuvent néanmoins isoler EXACTEMENT le même mot ou ensemble
     * de mots entre elles — même famille de défaut que C-1, mais horizontale. Exemple cité par
     * l'audit, confirmé sur pièces : paire X:M — XALAM (1 mot) derrière `avec/a` ET `avec/l` ;
     * XENODOCHIUM (1 mot) derrière 8 lettres distinctes `c`/`d`/`e`/`h`/`i`/`n`/`o`/`u`. Détecté
     * par `findSiblingContentDuplicates()` (`scripts/propose_seo_batch.php`) : nécessite le
     * contenu RÉEL du panier (un compte identique ne suffit pas à prouver un ensemble identique),
     * une requête par paire distincte (564 paires ayant ≥ 2 lettres avec en registre, 9 919
     * lettres vérifiées), jamais par ligne. Règle de canonicalisation : la lettre "avec"
     * alphabétiquement la PLUS PETITE de chaque groupe reste seule candidate, même convention que
     * lettre1 < lettre2 pour WORD_LIST_AVEC_TWO_LETTERS/THREE_LETTERS (D-029/D-030/D-031). 283
     * groupes de doublons sœurs trouvés, 428 lignes supplémentaires exclues — vérifié par 3
     * méthodes indépendantes (panier PHP en cache vs requête SQL directe par lettre, 0 divergence
     * sur 711 vérifications croisées ; empreinte SQL GROUP_CONCAT+sha1, 0 divergence sur les 283
     * groupes). Périmètre réellement indexable après I-A : 9 495 pages (9 923 − 428), dont 1 060
     * à exactement 1 résultat (1 385 avant I-A, moins 325 doublons sœurs à 1 résultat) — GARDÉES,
     * même consigne produit que tous les paliers "avec" précédents. Maillage interne inchangé par
     * ce correctif (les 428 pages retirées n'étaient de toute façon jamais indexables, R3), voir
     * le rapport AFTER de ce correctif (agent seo-registry) pour le détail complet, dont la liste
     * exhaustive des 283 groupes.
     *
     * CORRECTIF C-2 (2026-08-19, 3e audit seo-technical-auditor consolidé de la série, bloquant) :
     * ni C-1 (doublon VERTICAL avec la page parente SANS lettre avec) ni I-A (doublon HORIZONTAL
     * entre lettres avec SŒURS du même parent) ne comparaient jamais une tranche de CETTE famille
     * (une lettre "avec", axe 2) à une tranche de la famille SŒUR WORD_LIST_COMBINED (une
     * longueur, axe 1, D-027/D-035, ci-dessus) — les DEUX familles tranchent pourtant le MÊME
     * panier commençant+terminant, l'une par longueur, l'autre par lettre "avec". Preuve concrète
     * (exemple cité par l'audit, confirmé) : paire X:M (2 mots au total) — XALAM (5 lettres) est
     * à la fois le contenu EXACT de `/mots/5-lettres/commencant/x/terminant/m` (axe 1, déjà
     * indexée) ET de `/mots/commencant/x/terminant/m/avec/a` (axe 2, gagnant I-A du groupe {a,l})
     * ; XENODOCHIUM (11 lettres) est à la fois le contenu EXACT de
     * `/mots/11-lettres/commencant/x/terminant/m` ET de `.../avec/c` (gagnant I-A de l'autre
     * groupe, 8 lettres) — les DEUX gagnants I-A de cette paire se révèlent en réalité des
     * doublons croisés avec l'axe 1, aucun des deux ne survit à ce correctif.
     * Règle de priorité (tranchée côté produit, cohérente avec D-025 — la forme la plus
     * simple/générale gagne) : en cas de collision, la tranche LONGUEUR (axe 1,
     * WORD_LIST_COMBINED) reste seule candidate, la tranche "avec" (axe 2, CETTE famille) est
     * exclue — jamais l'inverse.
     * Détection EXHAUSTIVE sur les 611 paires réelles (pas un échantillon — l'audit lui-même
     * n'avait sondé que 9 paires à 5 lettres) : pour chaque paire ayant à la fois des tranches
     * axe 1 candidates (`list_counts`, `list_type = 'length_start_end'`, mêmes 52 exclusions
     * D-025 que `combined_with_length`) et des lettres axe 2 candidates à ce stade (déjà filtrées
     * D-032 + C-1 + I-A ci-dessus), compare l'ENSEMBLE EXACT des mots de chaque tranche longueur
     * à l'ENSEMBLE EXACT des mots de chaque lettre "avec" du même panier — jamais seulement le
     * compte. Vérifié par DEUX méthodes indépendantes (0 divergence sur les 611 paires réelles) :
     * (1) un seul panier complet ('normalized','length') récupéré par paire, filtré en PHP pour
     * les deux axes ; (2) une requête SQL directe par tranche (GROUP_CONCAT ordonné + sha1),
     * jamais de panier partagé. 333 collisions trouvées sur 191 paires distinctes (191/611) — voir
     * `findLengthAvecContentCollisions()` dans `scripts/propose_seo_batch.php` (cas
     * `'combined_with_letter'`) pour le détail complet, dont la liste exhaustive des 333 lignes
     * exclues. Périmètre réellement indexable après C-2 : 9 162 pages (9 495 − 333), dont 754 à
     * exactement 1 résultat (1 060 avant C-2, moins 306 collisions croisées à 1 résultat) —
     * GARDÉES, même consigne produit que tous les paliers "avec" précédents. Maillage interne
     * (`App\Search\StartEndWithLinksBuilder`, agent data-engine) : détection calculée en PARALLÈLE
     * et de façon INDÉPENDANTE côté data-engine (même méthode déjà rodée pour C-1/I-A, D-037/
     * D-038) — recoupement des deux listes de 333 clés encore à confirmer explicitement au moment
     * d'écrire cette entrée (voir le rapport AFTER de ce correctif, agent seo-registry, pour l'état
     * du recoupement) ; les 333 pages retirées côté registre n'étaient de toute façon jamais
     * indexables une fois exclues (R3), quel que soit l'état du maillage au moment de la lecture.
     *
     * WORD_LIST_COMMENCANT_WITH_LETTER (demande produit du 2026-08-18, dernier des quatre axes
     * commençant/terminant/avec travaillés ce jour, maillage/mesure de performance déjà faits par
     * l'agent data-engine — reports/query-plans/commencant-avec-no-length-full-sweep.md et
     * reports/query-plans/commencant-avec-maillage.md — classification et application du lot par
     * l'agent seo-registry) — n'a jamais figuré ici. NOUVELLE classification, DÉLIBÉRÉMENT
     * DISTINCTE à la fois de WORD_LIST_COMMENCANT (préfixe seul) ET de
     * WORD_LIST_COMBINED_WITH_LETTER (ci-dessus, préfixe + suffixe + une lettre "avec") : preuve
     * technique, pas seulement stylistique — `familySeoBatchRouteShapeError()` (R4b) impose déjà
     * une forme EXACTE `^/mots/commencant/[a-z]{1,15}\z` pour WORD_LIST_COMMENCANT, qui REJETTE
     * explicitement tout segment `/avec/...` après le préfixe ; réutiliser cette constante aurait
     * échoué à l'écriture même, pas seulement en incohérence documentaire. La forme de route est
     * elle-même syntaxiquement différente de WORD_LIST_COMBINED_WITH_LETTER
     * (`/mots/commencant/{X}/avec/{Y}`, DEUX segments de lettre, contre
     * `/mots/commencant/{X}/terminant/{Y}/avec/{Z}`, TROIS) : aucun terminant dans cette famille.
     * Périmètre STRICT : préfixe d'UNE SEULE lettre, SANS longueur, SANS terminant, PLUS une
     * lettre "avec" d'occurrence unique (`minCount=1`) — ni la variante avec longueur, ni un
     * préfixe multi-lettres, ni plusieurs lettres "avec" simultanées ne sont couverts par ce nom,
     * même discipline « ne jamais réutiliser cette constante pour un périmètre plus large que
     * celui réellement mesuré » déjà appliquée à toutes les familles ci-dessus.
     * Route : `/mots/commencant/{X}/avec/{Y}` — 26 préfixes réels (déjà indexés,
     * WORD_LIST_COMMENCANT, D-017) × 26 lettres = 676 combinaisons brutes au maximum
     * (`list_counts`, `list_type = 'start_with'`). Interaction avec D-032 (collapse "avec/X"
     * redondant avec un commençant d'une seule lettre) gérée DIRECTEMENT AU PRÉCALCUL cette fois
     * (choix distinct de `start_end_with`/WORD_LIST_COMBINED_WITH_LETTER, qui filtre au niveau du
     * builder) : les 26 combinaisons DÉGÉNÉRÉES (lettre "avec" égale le préfixe) sont exclues à la
     * source, jamais insérées dans `list_counts` — une seule direction de lecture et une seule
     * lettre "avec" par ligne rendent la condition de dégénérescence identique au précalcul et à
     * l'usage réel, voir reports/query-plans/commencant-avec-maillage.md section 2 pour la
     * justification complète. 650 combinaisons non dégénérées, dont 4 à 0 résultat (V+W, X+J,
     * X+K, X+W, jamais indexées) et 1 à exactement 1 résultat (W+J, GARDÉE, même consigne produit
     * que tous les axes "avec" précédents) — 646 pages réellement indexables.
     * CORRECTIF C-1 (2026-08-19, audit seo-technical-auditor consolidé sur D-035/D-036, même
     * passe que `WORD_LIST_COMBINED_WITH_LETTER` ci-dessus) : le même contrôle de doublon de
     * contenu contre la page parente SANS lettre "avec" (`Family::WORD_LIST_COMMENCANT`,
     * `list_type = 'start'`) a été ajouté à `scripts/propose_seo_batch.php` par discipline —
     * recalculé indépendamment, 0/650 lignes concernées (aucun préfixe réel de ce projet n'a la
     * totalité de ses mots contenant systématiquement une même lettre distincte), le compte reste
     * donc à 646, inchangé par ce correctif. Régime BORNE
     * (`App\Search\WordListSolver::needsUnindexedPredicates()`, "avec" présent) : `result_count`
     * plafonné à `ROW_EXAMINATION_CEILING` = 10 000 (D-019), 150/646 combinaisons réellement
     * au-dessus de ce plafond (ex. commençant/r/avec/e, 219 076 mots réels). Ancrage toujours sur
     * le préfixe (`sqlite_autoindex_terms_1`), jamais un parcours complet. Balayage complet des 26
     * pages sources réelles via le vrai code (`App\Search\PrefixAvecLinksBuilder`, DEUX passages
     * indépendants) : 0/26 au-dessus du budget TTFB p95 < 250 ms (max 1,836 ms puis 0,853 ms).
     * Maillage interne construit et vérifié exhaustivement dans les deux sens
     * (`App\Search\PrefixAvecLinksBuilder`, depuis les 26 pages `/mots/commencant/{X}`, déjà
     * indexées) — couverture 646/646 (100 %), 0 lien mort, 0 doublon, 0 fuite dégénérée. Voir
     * reports/query-plans/commencant-avec-no-length-full-sweep.md et
     * reports/query-plans/commencant-avec-maillage.md pour le détail complet.
     * CORRECTIF I-A (2026-08-19, 2e audit seo-technical-auditor sur D-037, non bloquant, même
     * fonction `findSiblingContentDuplicates()` que `WORD_LIST_COMBINED_WITH_LETTER` ci-dessus) :
     * recalculé sur les 26 préfixes (panier complet par préfixe, jusqu'à 219 076 mots pour R) — 0
     * groupe de doublons sœurs trouvé sur les 26 groupes vérifiés (646 lettres avec au total),
     * confirmé par 3 méthodes indépendantes. Compte inchangé à 646/646 — contrairement à
     * `WORD_LIST_COMBINED_WITH_LETTER` (paniers commençant+terminant, plus petits), les paniers
     * par PRÉFIXE SEUL sont en moyenne bien plus grands, ce qui rend statistiquement bien plus
     * rare que deux lettres distinctes induisent EXACTEMENT le même sous-ensemble — résultat
     * négatif vérifié, pas simplement supposé.
     *
     * @var list<string>
     */
    public const NEVER_SITEMAP = [
        self::WORD_LIST_CONTENANT,
        self::WORD_LIST_AVEC,
        self::WORD_LIST_SANS,
        self::WORD_LIST_MOTIF,
        self::RACK,
    ];

    /**
     * Familles couvrant des formes françaises non retenues à l'ODS8/ODS9. Contrainte dure :
     * "Never propose indexing these in bulk" — tout lot touchant cette famille doit rester
     * petit et justifié individuellement, jamais un simple pourcentage des 435 120 lignes.
     * Appliqué comme un plafond dur (voir MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED) plutôt qu'une
     * simple note, pour qu'un lot mal dimensionné échoue à l'application, pas seulement à la
     * relecture humaine.
     *
     * @var list<string>
     */
    public const FRENCH_NOT_ADMITTED = [
        self::WORD_FRENCH_NOT_ADMITTED,
    ];

    /**
     * Plafond appliqué par scripts/apply_seo_batch.php à tout lot touchant une famille de
     * FRENCH_NOT_ADMITTED. Révisé le 2026-08-04 (D-017, docs/DECISIONS.md) : la valeur initiale
     * de 50 avec attestation ligne par ligne empêchait structurellement de rendre les formes
     * françaises non admises trouvables via Google, alors que le site répond aux deux questions
     * "admis ?" et "non admis ?" et qu'un visiteur ne sait jamais laquelle s'applique avant de
     * chercher — décision explicite du propriétaire du produit, prise en connaissance du risque
     * SEO habituel de contenu peu différencié en volume, jugé acceptable ici car : (1) le site
     * n'est pas encore déployé (Phase 7 non commencée), donc rien de ce qui est écrit ici n'est
     * vu par le vrai Google avant une mise en ligne réelle et son propre séquençage par lots ;
     * (2) ces pages ne sont pas vides (badge, score, tuiles, réponse directe pour les trois
     * statuts, voir app/View/word.php) — seul le bloc de relations (Phase 4) leur manque.
     * L'attestation reste ligne par ligne dans le SCHEMA (R6, R7 : notes non vide obligatoire),
     * seul le plafond de VOLUME change. */
    public const MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED = 500_000;

    public static function isValid(string $family): bool
    {
        return in_array($family, self::ALL, true);
    }

    public static function forbidsSitemap(string $family): bool
    {
        return in_array($family, self::NEVER_SITEMAP, true);
    }

    public static function isFrenchNotAdmitted(string $family): bool
    {
        return in_array($family, self::FRENCH_NOT_ADMITTED, true);
    }
}
