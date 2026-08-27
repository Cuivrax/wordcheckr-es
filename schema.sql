-- Scrabble Light — schéma de production français.
-- Fichier canonique, sous contrôle de la session principale.
-- Produit par scripts/import_fr.py dans storage/dictionary_fr.sqlite.
-- Ouvert en lecture seule au runtime. Aucune définition n'y est copiée (D-004).

CREATE TABLE terms (
    id           INTEGER PRIMARY KEY,
    display_term TEXT    NOT NULL,
    normalized   TEXT    NOT NULL UNIQUE,

    is_french    INTEGER NOT NULL DEFAULT 0 CHECK (is_french IN (0, 1)),
    is_ods8      INTEGER NOT NULL DEFAULT 0 CHECK (is_ods8   IN (0, 1)),
    is_ods9      INTEGER NOT NULL DEFAULT 0 CHECK (is_ods9   IN (0, 1)),

    -- Colonne derivee, jamais une source de verite independante : is_admitted =
    -- (is_ods8 = 1 OR is_ods9 = 1), precalculee au build (scripts/import_fr.py) pour que le
    -- filtre "admis seulement" des listes /mots/... reste indexable. Sans cette colonne,
    -- filtrer sur `is_ods8 = 1 OR is_ods9 = 1` empeche tout index couvrant sur le COMPTE EXACT
    -- du regime EXACT (longueur/prefixe seuls) : mesure 2026-08-08, ~750 ms (lookup ligne par
    -- ligne, non couvrant) contre 3 ms une fois indexe sur cette colonne -- voir
    -- reports/query-plans/status-filter-admitted.md et docs/DECISIONS.md D-022. Le regime
    -- BORNE (avec/contenant/sans/motif, deja borne par LIMIT) n'a pas besoin de cette colonne :
    -- le predicat s'ajoute au meme cout que n'importe quel autre (7 a 45 ms mesures).
    is_admitted  INTEGER NOT NULL DEFAULT 0 CHECK (is_admitted IN (0, 1)),

    score        INTEGER NOT NULL,
    length       INTEGER NOT NULL CHECK (length >= 2),
    signature    TEXT    NOT NULL,
    reversed     TEXT    NOT NULL,

    -- D-018 : nature grammaticale et genre, sur CHAQUE terme (admis ou non), independants
    -- du statut Scrabble. Source : data/raw/french_dict.db (Kartmaan), colonnes pos/gender,
    -- lignes NP et locutions exclues (meme regle que le filtre d'import francais). NULL si
    -- aucune ligne Kartmaan retenue ne couvre ce terme (~12,3 % des lignes, formes venues
    -- uniquement de hbenbel ou des additions ODS9) -- absence de donnee, pas une erreur.
    --
    -- pos/pos_secondary : jeu ferme de 9 codes, reduit depuis les valeurs brutes Kartmaan
    -- (flex-nom -> N, flex-verb -> V, flex-adj/adj-num/adj-pos/adj-int/adj-excl -> Adj,
    -- flex-adv -> Adv, flex-interj -> Interj, pronom-*/flex-pronom -> Pronom, prep -> Prep,
    -- conj-coord/flex-conj -> Conj, art-part -> Art). Deux colonnes seulement : mesure sur
    -- les 838 180 lignes, un seul pos ne suffit qu'a 87,9 % des termes couverts (homographes
    -- frequents nom/adjectif de gentile, nom/verbe type TABLE/tabler) -- le troisieme sens
    -- eventuel (0,37 % des termes) est perdu, juge negligeable plutot que sur-ingenierie.
    -- Premier et second pos distincts rencontres dans l'ordre "id" des lignes Kartmaan
    -- (meme convention de determinisme que ORDER BY forme, id de load_kartmaan()).
    --
    -- gender : calcule independamment de pos/pos_secondary -- premiere ligne (ordre id) ou
    -- pos = 'N' et gender non vide, meme si 'N' n'est pas retenu comme sens primaire (couvre
    -- TABLE : pos primaire V, gender du sens nom rapporte quand meme). Trois valeurs
    -- reelles constatees dans la source, pas deux : 'e' = epicene (meme forme aux deux
    -- genres, ex. ENFANT, ELEVE, ARTISTE), 1 861 lignes Kartmaan, jamais associee a un
    -- pos autre que 'N'.
    pos           TEXT DEFAULT NULL
        CHECK (pos IN ('N','V','Adj','Adv','Pronom','Prep','Conj','Interj','Art') OR pos IS NULL),
    pos_secondary TEXT DEFAULT NULL
        CHECK (pos_secondary IN ('N','V','Adj','Adv','Pronom','Prep','Conj','Interj','Art') OR pos_secondary IS NULL),
    gender        TEXT DEFAULT NULL
        CHECK (gender IN ('m','f','e') OR gender IS NULL)
);

-- La contrainte UNIQUE sur normalized crée déjà son propre index.
-- Un CREATE INDEX supplémentaire sur cette seule colonne serait redondant :
-- il est délibérément absent.

-- Longueur puis ordre alphabétique : /mots/7-lettres et ses paginations.
CREATE INDEX idx_terms_length_normalized ON terms(length, normalized);

-- Anagrammes exactes, et point de départ des anagrammes ±1 lettre.
CREATE INDEX idx_terms_signature ON terms(signature);

-- Suffixes : /mots/terminant/tion interroge reversed par PLAGE, jamais par LIKE.
--
--   correct   WHERE reversed >= 'NOIT' AND reversed < 'NOIU'   -> index, 0,20 ms
--   interdit  WHERE reversed LIKE 'NOIT%'                      -> SCAN complet
--
-- LIKE est insensible à la casse par défaut dans SQLite : l'optimiseur ne peut
-- pas l'adosser à un index BINARY, et la requête dégénère en balayage des
-- 850 000 lignes. La même règle vaut pour les préfixes sur normalized.
CREATE INDEX idx_terms_reversed ON terms(reversed);

-- Suffixe COMBINÉ à une longueur (ex. /mots/7-lettres/terminant/s) : bug de
-- performance réel trouvé lors de l'analyse d'opportunité SEO longue traîne
-- (2026-08-08), symétrique à idx_terms_length_normalized ci-dessus pour le
-- préfixe. Sans cet index composé, WordListSolver::anchorClause() ancre sur
-- reversed (plage globale, TOUTES longueurs confondues) et applique
-- `length = ?` comme prédicat résiduel non couvert par idx_terms_reversed :
-- SQLite doit alors faire un lookup de table par ligne candidate sur toute la
-- plage globale du suffixe avant de savoir si la longueur correspond. Mesuré
-- avant correctif : jusqu'à 1 779 ms (7-lettres/terminant/s, S = 338 308 mots
-- toutes longueurs) et 629 ms (6-lettres/terminant/ent) -- fonctionnellement
-- équivalent au parcours complet que ce fichier interdit par ailleurs. Après
-- ajout de cet index : 100,4 ms et 0,9 ms respectivement (mesures complètes :
-- reports/query-plans/terminant-length-index-fix.md). Le cas SANS longueur
-- (/mots/terminant/tion seul, déjà indexé en production depuis D-017) reste
-- sur idx_terms_reversed, inchangé -- ce nouvel index ne le concerne pas.
CREATE INDEX idx_terms_length_reversed ON terms(length, reversed);

-- Familles restreintes à une édition, en ordre alphabétique.
-- Index couvrants : ils servent aussi bien le filtre que le tri.
CREATE INDEX idx_terms_ods8 ON terms(is_ods8, normalized);
CREATE INDEX idx_terms_ods9 ON terms(is_ods9, normalized);

-- Filtre "admis seulement"/"non admis seulement" sur les listes /mots/... (D-022).
-- idx_terms_length_admitted_normalized sert le regime EXACT ancre sur une longueur (seule ou
-- combinee a un prefixe) ; idx_terms_admitted_normalized sert le meme filtre SANS longueur
-- (prefixe seul, ex. /mots/commencant/a/statut/admis). Le regime BORNE (suffixe, avec,
-- contenant, sans, motif) n'utilise aucun des deux -- deja protege par LIMIT, le predicat
-- is_admitted s'y ajoute au meme cout qu'un predicat quelconque (mesure : 7 a 45 ms).
CREATE INDEX idx_terms_length_admitted_normalized ON terms(length, is_admitted, normalized);
CREATE INDEX idx_terms_admitted_normalized ON terms(is_admitted, normalized);

-- Tri "par points" sur les listes /mots/... (D-022). Necessaire uniquement pour le regime
-- EXACT ancre sur une SEULE longueur (le cas le plus large, jusqu'a 65 000+ mots par longueur) :
-- sans cet index, ORDER BY score force un TEMP B-TREE sur tout le panier avant LIMIT, mesure a
-- ~760-870 ms. Avec : 0,2 a 35 ms (pagination profonde comprise). Le cas longueur+prefixe
-- reste servi par idx_terms_length_normalized (panier deja petit une fois le prefixe applique,
-- tri residuel en memoire mesure a 9 ms, aucun index dedie necessaire). Le regime BORNE
-- (suffixe, avec, contenant, sans, motif) trie en PHP le panier deja borne par LIMIT
-- (WordListSolver::ROW_EXAMINATION_CEILING) -- mesure au pire cas (10 000 lignes) : 173 ms,
-- toujours sous le budget, aucun index dedie necessaire non plus.
CREATE INDEX idx_terms_length_score_normalized ON terms(length, score, normalized);

-- Regression D-025bis (audit du lot D-025, 2026-08-09) : /mots/commencant/{X}/terminant/{Y}
-- (une seule lettre de chaque cote, 611 pages ouvertes a l'indexation, D-025) ancrait
-- toujours sur le prefixe et appliquait "termine par Y" comme predicat residuel sur tout le
-- panier ancre -- catastrophique quand le prefixe est frequent (mesure jusqu'a 1 211 ms sur
-- commencant/p/terminant/h, 224 205 mots pour le seul prefixe R). Une premiere version du
-- correctif (choisir dynamiquement le cote le moins frequent comme ancrage, comptes
-- list_counts) reduisait la pire mesure a 17 ms sur ce cas precis, mais un balayage complet
-- des 611 combinaisons a revele 53 cas encore au-dessus du budget TTFB p95 < 250 ms (jusqu'a
-- 6 675 ms, commencant/z/terminant/s) : des que les DEUX lettres sont frequentes, aucun choix
-- d'ancrage n'evite un grand panier residuel. Corrige en profondeur par un index sur les deux
-- expressions substr() a la fois -- WordListSolver n'a alors plus besoin de choisir un
-- ancrage ni un residuel pour ce cas, une seule egalite couvre les deux conditions
-- simultanement. Mesure apres cet index : 5 a 40 ms sur les 611 combinaisons reelles, 0 cas
-- au-dessus de 250 ms (reports/query-plans/prefix-suffix-anchor-fix.md). Sert le cas prefixe
-- ET suffixe D'UNE SEULE LETTRE CHACUN, AVEC OU SANS longueur -- WordListSolver bascule sur ce
-- chemin des que les deux lettres sont presentes ; une longueur, si fournie, s'ajoute comme
-- predicat supplementaire sur le petit panier deja isole par l'egalite (pas dans cet index,
-- mais bon marche a ce stade -- mesure : 57,9 ms sur 9-lettres/commencant/r/terminant/e).
-- CORRECTIF (audit 2e passe, D-025) : une premiere version de ce commentaire annoncait a tort
-- "sans longueur" comme portee exacte, jamais verifiee contre le code reel qui n'a aucune
-- condition excluant la longueur. Seule la variante SANS longueur (611 lignes) est
-- effectivement ouverte a l'indexation a ce jour (D-025) -- la variante AVEC longueur
-- (jusqu'a 5 193 combinaisons) beneficie du meme index sans effort supplementaire, mais reste
-- noindex par omission, aucun lot ne l'ayant proposee.
CREATE INDEX idx_terms_startletter_endletter_normalized
    ON terms(substr(normalized, 1, 1), substr(reversed, 1, 1), normalized);

-- Pas d'index sur is_french : la colonne vaut 1 sur toutes les lignes de la base
-- française — un terme présent est français par construction, la distinction
-- utile est portée par is_ods8 et is_ods9. La colonne reste au schéma car le
-- code est partagé avec le futur site anglais, où l'équivalent is_english
-- n'aura pas la même constance.

-- D-018 : liens de conjugaison verbale, construits depuis data/raw/hbenbel/verb.csv.
--
-- La source est un tri alphabetique global sur "form", PAS des blocs contigus par verbe
-- (une pre-verification affirmant le contraire s'est revelee fausse a la verification
-- directe -- voir reports/ pour le detail). L'association forme -> lemme est reconstruite
-- au build par plus-long-prefixe-commun contre la liste des infinitifs connus ; les lemmes
-- dont trop peu de formes s'apparient sur eux-memes (verbes suppletifs -- ETRE, AVOIR,
-- ALLER, DEVOIR, VALOIR, VOIR... -- own_count < 20 mesure, 281 lemmes sur 6 697) sont
-- exclus plutot que de risquer un lien de conjugaison faux -- voir scripts/import_fr.py.
--
-- Selection representative, pas le paradigme complet (~50 formes/verbe dans la source) :
-- present, futur simple, imparfait (indicatif, 6 personnes chacun), participe present,
-- participe passe (forme de base seule, sans accord de genre/nombre -- hors perimetre,
-- comme flex-adj/flex-nom). Jusqu'a 20 formes par lemme retenu.
--
-- lemma_normalized et form_normalized referencent tous deux terms.normalized ; garanti par
-- construction du script de build (D-010 applique automatiquement : toute forme ou lemme de
-- plus de 15 lettres est deja absent de terms, donc jamais insere ici). FOREIGN KEY
-- documentaire -- non applique par SQLite sans PRAGMA foreign_keys, absent au runtime
-- (lecture seule, D-001) ; la coherence est garantie par le script de build, pas par le
-- moteur.
CREATE TABLE verb_forms (
    id               INTEGER PRIMARY KEY,
    lemma_normalized TEXT NOT NULL REFERENCES terms(normalized),
    form_normalized  TEXT NOT NULL REFERENCES terms(normalized),
    tense            TEXT NOT NULL
        CHECK (tense IN ('present', 'future', 'imperfect', 'participle_present', 'participle_past')),
    person           TEXT
        CHECK (person IN ('1s', '2s', '3s', '1p', '2p', '3p') OR person IS NULL),

    UNIQUE (lemma_normalized, form_normalized, tense, person)
);

-- Formes conjuguees d'un lemme ("POSER a pour formes POSERA, POSE, ...").
CREATE INDEX idx_verbforms_lemma ON verb_forms(lemma_normalized);
-- Lemme d'une forme conjuguee ("POSERA est une forme de POSER") -- lookup inverse.
CREATE INDEX idx_verbforms_form ON verb_forms(form_normalized);

-- D-0XX (a rediger apres validation du pilote 100 mots, revise D-004) : definitions
-- lexicales, une ligne par sens. Grounding : data/raw/french_dict.db (Kartmaan, colonne
-- "definitions", CC BY-SA 4.0, WiktionaryX/CNRS) et kaikki.org (extraction Wiktionnaire FR)
-- en complement pour les termes non couverts par Kartmaan -- AUCUNE des deux sources n'est
-- jamais copiee telle quelle : reformulation LLM systematique + garde-fou anti-copie
-- programmatique (plus longue suite de mots consecutifs partagee avec la reference > 7 mots
-- -> rejet automatique), voir scripts/generate_word_senses.py. D-015 reste en vigueur --
-- aucun credit de source publie, seule la reformulation originale est stockee.
--
-- FK par normalized (texte), pas par terms.id -- meme convention que verb_forms ci-dessus.
-- source trace la fiabilite de chaque ligne (meme principe que reports/verb-lemmas-
-- excluded.csv pour D-018) : 'template' = gabarit gratuit derive de verb_forms (aucun appel
-- LLM), 'kartmaan'/'kaikki' = LLM ancre sur une glose de reference, 'llm-only' = aucune
-- reference disponible, connaissance du modele seule -- traçe explicitement pour un futur
-- audit cible, jamais affichee au visiteur.
CREATE TABLE word_senses (
    id              INTEGER PRIMARY KEY,
    term_normalized TEXT    NOT NULL REFERENCES terms(normalized),
    sense_rank      INTEGER NOT NULL,
    pos             TEXT    NOT NULL
        CHECK (pos IN ('N','V','Adj','Adv','Pronom','Prep','Conj','Interj','Art')),
    gender          TEXT
        CHECK (gender IN ('m','f','e') OR gender IS NULL),
    definition      TEXT    NOT NULL,
    source          TEXT    NOT NULL
        CHECK (source IN ('template','kartmaan','kaikki','llm-only')),

    UNIQUE (term_normalized, sense_rank)
);

-- Sens d'un terme, dans l'ordre d'affichage (sense_rank : 1 = sens principal).
CREATE INDEX idx_word_senses_term ON word_senses(term_normalized);

-- Comptes precalcules de la page hub /mots (longueur, commencant, terminant -- 66 lignes de
-- base), produite hors ligne par scripts/build_explore_hub_counts.php, jamais au runtime.
-- Un GROUP BY sur substr(normalized,1,1)/substr(reversed,1,1) n'a aucun index disponible sur
-- l'expression calculee : 245 ms et 215 ms mesures sur les 838 180 lignes reelles (SCAN
-- complet + TEMP B-TREE), tres au-dessus du budget TTFB p95 < 250 ms pour une seule page
-- (CLAUDE.md). Lue par App\Search\ExploreHubBuilder en une requete triviale (1 ms, 66
-- lignes), jamais un GROUP BY sur `terms` au runtime.
--
-- 'length_start'/'length_end' (D-022) : meme principe, mais croise longueur ET lettre de
-- debut/fin (list_key = "{longueur}:{lettre}", ex. "13:A") -- alimente les liens de maillage
-- interne "mots de {N} lettres commencant/terminant par {X}" sur /mots/{N}-lettres et
-- "filtrer par longueur" sur /mots/commencant|terminant/{X}. 14 longueurs x 26 lettres x
-- 2 directions = 728 lignes ajoutees au maximum (moins en pratique, R5 : jamais de ligne a 0).
--
-- 'length_with' (D-022) : longueur croisee avec une lettre presente n'importe ou dans le mot
-- (pas seulement debut/fin), meme cle "{longueur}:{lettre}" -- alimente "mots de {N} lettres
-- avec {X}" sur /mots/{N}-lettres. Calcule par un parcours PHP unique (une seule lecture
-- sequentielle de `terms`, pas 26 requetes LIKE '%X%' qui forceraient 26 scans complets
-- distincts) dans scripts/build_explore_hub_counts.php.
-- CORRECTIF (D-029, palier 1 de l'ouverture en entonnoir de "avec", 2026-08-17) : ces pages
-- cible ne sont PLUS toutes hors sitemap -- App\Seo\Family::WORD_LIST_AVEC (multiensemble
-- general, non ancre, non borne) reste PERMANENT dans NEVER_SITEMAP, mais le sous-ensemble
-- longueur + EXACTEMENT une lettre (minCount=1, exactement ce que 'length_with' precalcule)
-- est desormais Family::WORD_LIST_AVEC_SINGLE_LETTER, INDEXE (364 lignes, ancre sur
-- length = ? donc sans risque de scan complet, voir reports/query-plans/
-- avec-length-1-letter-full-sweep.md). Un futur palier (2, 3 lettres...) exige sa PROPRE
-- classification et sa PROPRE mesure -- ne jamais reutiliser WORD_LIST_AVEC_SINGLE_LETTER
-- pour un perimetre plus large que celui-ci.
--
-- 'start_end' (D-024, maillage commencant+terminant) : croise lettre de debut ET de fin, SANS
-- longueur (list_key = "{debut}:{fin}", ex. "C:E") -- alimente le maillage interne depuis les
-- hubs deja indexes /mots/commencant/{X} et /mots/terminant/{Y} (D-017) vers
-- /mots/commencant/{X}/terminant/{Y} (Family::WORD_LIST_COMBINED). GROUP BY sur les deux
-- expressions substr() a la fois : 1,6 s mesures sur les 838 180 lignes reelles (SCAN complet +
-- TEMP B-TREE, 611 groupes non vides) -- hors ligne uniquement, jamais au runtime.
--
-- 'length_with_position' (D-023bis, maillage avec -> position) : croise longueur, lettre ET
-- position exacte de cette lettre dans le mot (list_key = "{longueur}:{lettre}:{position}",
-- ex. "9:W:3") -- alimente "mots de {N} lettres avec {X}" -> liens vers chaque position ou
-- cette lettre apparait reellement (position 1 et derniere position pointent vers
-- commencant/terminant deja existants, D-023, jamais une URL "position/1/..." dupliquee).
-- Calcule par un parcours PHP unique (une position par caractere, pas de GROUP BY SQL sur une
-- expression composee) dans scripts/build_explore_hub_counts.php.
--
-- 'length_avec_sans' (D-024bis, maillage avec+sans -> longueur) : croise une lettre exigee, une
-- lettre exclue ET la longueur (list_key = "{avec}:{sans}:{longueur}", ex. "Q:U:9") -- alimente
-- "mots avec {X} sans {Y}" (sans longueur, /mots/avec/{X}/sans/{Y}) -> liens vers chaque
-- longueur ou cette combinaison a des resultats. Calcule par un parcours PHP unique (pour
-- chaque mot, chaque lettre presente x chaque lettre absente), ~45 s mesures sur les 838 180
-- lignes reelles -- hors ligne uniquement, aucune requete live equivalente n'est sure (91-170 ms
-- mesures par combinaison, risque reel de depasser le budget TTFB combine au reste de la page).
-- Ces pages cible restent hors sitemap (App\Seo\Family::WORD_LIST_AVEC et WORD_LIST_SANS dans
-- NEVER_SITEMAP, combinaisons de lettres) : ces liens servent uniquement la
-- decouverte/navigation, jamais l'indexation directe de la page visee.
--
-- 'length_start_end' (D-027, maillage commencant+terminant AVEC longueur) : croise longueur,
-- lettre de debut ET lettre de fin (list_key = "{longueur}:{debut}:{fin}", ex. "9:R:E") --
-- alimente le maillage depuis une page longueur+prefixe seul (ou longueur+suffixe seul --
-- CORRECTIF (audit du lot D-028, seo-technical-auditor, constat I3, 2026-08-11) : ces pages
-- source ne sont PAS indexees, contrairement a ce qu'affirmait a tort une premiere version de
-- ce commentaire -- verifie directement contre storage/seo_fr.sqlite, 0 ligne de registre pour
-- /mots/{N}-lettres/commencant/{X} ou /mots/{N}-lettres/terminant/{Y}, noindex,follow par
-- omission (D-005), tout comme les pages qu'elles maillent) vers chaque variante
-- /mots/{N}-lettres/commencant/{X}/terminant/{Y}
-- (Family::WORD_LIST_COMBINED) qui a au moins un resultat -- 5 193 lignes non vides sur
-- 14 x 26 x 26 = 9 464 combinaisons possibles. GROUP BY sur length + les deux expressions
-- substr() a la fois, 8,0 s mesures sur les 838 180 lignes reelles (SCAN terms USING INDEX
-- idx_terms_length_reversed + TEMP B-TREE FOR GROUP BY) -- hors ligne uniquement. Ces pages
-- cible restent noindex,follow par defaut (D-005) tant qu'aucun lot ne les couvre
-- explicitement : ce list_type sert uniquement a construire le maillage prealable (regle dure
-- "jamais de page orpheline indexee"), aucune decision d'ouverture a l'indexation n'est prise
-- par sa seule presence.
--
-- 'length_with_pair' (palier 2 de l'ouverture en entonnoir de "avec") : croise longueur et CHAQUE
-- PAIRE de lettres DISTINCTES presentes dans le mot (list_key = "{longueur}:{lettre1}:{lettre2}",
-- lettre1 < lettre2 alphabetiquement) -- alimente le maillage "mots de {N} lettres avec {X}"
-- (palier 1, D-029, deja indexe) -> liens vers chaque variante "avec {X} {Y}" (palier 2) qui a au
-- moins un resultat. Parcours PHP unique (count_chars() renvoie deja les lettres distinctes en
-- ordre croissant, aucun tri supplementaire necessaire) : 4 276 lignes non vides sur 14 x C(26,2)
-- = 4 550 combinaisons maximum, 19,05 s mesures sur les 838 180 lignes reelles -- hors ligne
-- uniquement, voir reports/query-plans/avec-length-2-letters-full-sweep.md.
--
-- 'length_with_triple' (palier 3 de l'ouverture en entonnoir de "avec") : croise longueur et
-- CHAQUE TRIPLET de lettres DISTINCTES presentes dans le mot (list_key =
-- "{longueur}:{lettre1}:{lettre2}:{lettre3}", lettre1 < lettre2 < lettre3 alphabetiquement) --
-- alimente le maillage "mots de {N} lettres avec {X} {Y}" (palier 2, D-030, deja indexe) -> liens
-- vers chaque variante "avec {X} {Y} {Z}" (palier 3) qui a au moins un resultat. Parcours PHP
-- unique (count_chars() renvoie deja les lettres distinctes en ordre croissant, aucun tri
-- supplementaire necessaire) : 28 827 lignes non vides sur 14 x C(26,3) = 36 400 combinaisons
-- maximum -- hors ligne uniquement, voir
-- reports/query-plans/avec-length-3-letters-full-sweep.md.
--
-- 'start_end_with' (maillage commencant+terminant+avec, 2026-08-18, voir reports/query-plans/
-- commencant-terminant-avec-maillage.md) : croise lettre de debut, lettre de fin ET une lettre
-- presente n'importe ou dans le mot (list_key = "{debut}:{fin}:{lettre}", ex. "R:E:S") --
-- alimente le maillage depuis /mots/commencant/{X}/terminant/{Y} (deja indexee, Family::
-- WORD_LIST_COMBINED, D-024/D-025) vers chaque variante
-- /mots/commencant/{X}/terminant/{Y}/avec/{Z} qui a au moins un resultat. Parcours PHP unique
-- (meme principe que 'length_with'/'length_with_pair'/'length_with_triple' ci-dessus, mesure
-- contre l'alternative 26 requetes GROUP BY SQL avant de choisir : 3,945 s contre 5,195 s, memes
-- 11 348 lignes produites par les deux methodes). 11 348 lignes non vides sur 26 x 26 x 26 =
-- 17 576 combinaisons maximum. NOTE : App\Search\StartEndWithLinksBuilder exclut au runtime les
-- 1 198 lignes DEGENEREES ou la lettre "avec" egale la lettre de debut ou de fin
-- (WordListFilters::fromPath() les collapse silencieusement vers la page parente, D-032) --
-- list_counts les conserve TELLES QUELLES (precalcul brut, aucune exclusion cote precalcul),
-- l'exclusion est une decision du builder, pas du precalcul.
--
-- 'start_with' (maillage commencant+avec SANS terminant ni longueur, 2026-08-18, voir
-- reports/query-plans/commencant-avec-maillage.md) : croise lettre de debut ET une lettre
-- presente n'importe ou dans le mot (list_key = "{debut}:{lettre}", ex. "R:S") -- alimente le
-- maillage depuis /mots/commencant/{X} (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) vers
-- chaque variante /mots/commencant/{X}/avec/{Y} qui a au moins un resultat. Parcours PHP unique
-- (meme principe que 'length_with'/'start_end_with' ci-dessus). CHOIX DISTINCT de
-- 'start_end_with' : les 26 combinaisons DEGENEREES (lettre "avec" egale la lettre de debut,
-- D-032 -- WordListFilters::fromPath() les collapse silencieusement vers la page parente) sont
-- exclues DIRECTEMENT AU PRECALCUL ici (pas au niveau du builder) -- une seule direction de
-- lecture et une seule lettre "avec" par ligne rendent la condition de degenerescence identique
-- au precalcul et a l'usage reel, l'exclure a la source est equivalent et plus simple, voir le
-- rapport cite pour la justification complete. 646 lignes non vides sur 26 x 26 = 676
-- combinaisons brutes au maximum (650 apres exclusion des 26 diagonales, 4 a 0 resultat parmi
-- celles-ci).
--
-- 'prefix2'/'prefix3'/'prefix4' (entonnoir commencant multi-lettres, 2026-08-18) : GROUP BY
-- direct sur substr(normalized, 1, N), N = 2, 3, 4 -- alimente le maillage "mots commencant par
-- {X}" (longueur 1, deja indexe D-017) -> extensions reelles a 2, 3, 4 lettres. list_key = le
-- prefixe reel exact (longueur = N). 21 734 lignes non vides sur 26^2+26^3+26^4 = 475 228
-- combinaisons theoriques maximum (435+3775+17524 reelles), voir reports/query-plans/
-- commencant-terminant-multi-lettres-dimensionnement.md.
--
-- 'suffix2'/'suffix3'/'suffix4' (meme lot, symetrique cote terminant) : GROUP BY direct sur
-- substr(reversed, 1, N), MAIS list_key stocke le SUFFIXE REEL (strrev() applique a
-- l'insertion), jamais la sous-chaine brute de `reversed` -- alimente "mots terminant par {X}"
-- -> extensions reelles, l'extension ajoutant une lettre EN TETE du suffixe (pas en queue,
-- contrairement au prefixe). 17 805 lignes non vides sur le meme theorique maximum
-- (431+3293+14081 reelles).
CREATE TABLE list_counts (
    list_type TEXT    NOT NULL CHECK (list_type IN ('length', 'start', 'end', 'length_start', 'length_end', 'length_with', 'start_end', 'length_with_position', 'length_avec_sans', 'length_start_end', 'length_with_pair', 'length_with_triple', 'start_end_with', 'start_with', 'prefix2', 'prefix3', 'prefix4', 'suffix2', 'suffix3', 'suffix4')),
    list_key  TEXT    NOT NULL,
    count     INTEGER NOT NULL,

    PRIMARY KEY (list_type, list_key)
);

-- Empreintes des sources et paramètres du build. Aucune date d'exécution :
-- l'import doit rester déterministe et rejouable à l'identique.
-- "key" et "value" sont entre guillemets : ils ne sont pas réservés en SQLite,
-- mais le sont dans d'autres dialectes, ce qui fait crier les linters SQL sur
-- un fichier que plusieurs agents vont relire.
CREATE TABLE build_metadata (
    "key"   TEXT PRIMARY KEY,
    "value" TEXT NOT NULL
);
