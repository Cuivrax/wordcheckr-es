-- Scrabble Light — schéma de production espagnol.
-- Fichier canonique de ce dépôt (ES), adapté de schema.sql du site français.
-- Produit par scripts/import_es.py dans storage/dictionary_es.sqlite.
-- Ouvert en lecture seule au runtime. Aucune définition n'y est copiée.
--
-- Portée de cette passe (voir docs/DECISIONS.md, décision ES-001) : uniquement le
-- coeur du site -- vérification de mot et solveur de rack/liste contrainte. Nature
-- grammaticale, conjugaison et définitions en prose sont explicitement HORS PÉRIMÈTRE
-- (demande du propriétaire du produit). Les colonnes/tables correspondantes restent au
-- schéma UNIQUEMENT parce que plusieurs classes de app/Search/ héritées du site
-- français les lisent déjà en dur (TermLookup::find() sélectionne pos/pos_secondary/
-- gender, SenseLookup lit word_senses...) et que app/View/ (hors périmètre de cet
-- agent, jamais modifié) consomme leur sortie -- les retirer casserait ce chemin sans
-- qu'aucun test de ce dépôt ne puisse le couvrir. Elles restent donc PRÉSENTES mais
-- VIDES (0 ligne, ou NULL colonne par colonne) dans la base construite par
-- scripts/import_es.py. Voir docs/DECISIONS.md pour le détail de cet arbitrage.

CREATE TABLE terms (
    id           INTEGER PRIMARY KEY,
    display_term TEXT    NOT NULL,
    normalized   TEXT    NOT NULL UNIQUE,

    -- Modèle à trois statuts (mêmes règles que le site français, sources différentes) :
    --   is_ods8 = 1 ou is_ods9 = 1   -> admis au Scrabble
    --   is_spanish = 1 et les deux a 0 -> forme espagnole réelle, non admise
    --   absent de la base            -> terme inconnu
    is_spanish   INTEGER NOT NULL DEFAULT 0 CHECK (is_spanish IN (0, 1)),

    -- Noms de colonnes CONSERVÉS tels quels (is_ods8/is_ods9, pas is_file2017/
    -- is_fise2) bien que la sémantique change pour ce site : is_ods8 = admis au
    -- Lexicón FILE 2017 (kamilmielnik/scrabble-dictionaries, spanish/file-2017.txt,
    -- copie octet-identique vérifiée du fichier fourni par l'utilisateur -- aucune
    -- licence propre déclarée sur ce dépôt, risque accepté par le propriétaire du
    -- produit, même régime que l'ODS8/ODS9 français, D-015-équivalent) ; is_ods9 =
    -- admis au Lexicón FISE-2 2009, obtenu via words/an-array-of-spanish-words
    -- (MIT, Zeke Sikelianos 2016 -- canal MIEUX licencié que kamilmielnik/spanish/
    -- fise-2.txt pour un contenu identique à 99,9998 % près, 636 598/636 599 mots
    -- communs, le seul mot manquant "zuñisteis" est de toute façon couvert par
    -- FILE 2017). Raison de la conservation du nom de colonne : plusieurs requêtes
    -- SQL de app/Search/ (RackSolver, RelationsFinder, Suggester, TermLookup,
    -- WordListSolver) et les clés de tableau PHP qu'elles produisent ('isOds8',
    -- 'isOds9') sont écrites EN DUR avec ce nom, elles-mêmes consommées par
    -- app/View/ (hors périmètre de cet agent) -- un renommage casserait ce chemin
    -- sans qu'aucun test de ce dépôt ne puisse le couvrir. Les étiquettes VISIBLES
    -- (badge) sont correctement espagnoles : voir config/sites/es.php ('FILE 2017',
    -- 'FISE-2'), seul l'identifiant interne garde son nom d'origine. Voir
    -- docs/DECISIONS.md pour la décision tracée en détail.
    is_ods8      INTEGER NOT NULL DEFAULT 0 CHECK (is_ods8   IN (0, 1)),
    is_ods9      INTEGER NOT NULL DEFAULT 0 CHECK (is_ods9   IN (0, 1)),

    -- Colonne dérivée, jamais une source de vérité indépendante : is_admitted =
    -- (is_ods8 = 1 OR is_ods9 = 1), précalculée au build (scripts/import_es.py) pour
    -- que le filtre "admis seulement" des listes /palabras/... reste indexable (même
    -- raison et même mesure que le site français, voir schema.sql de ce dépôt-source
    -- pour le détail chiffré -- non reproduit ici faute de volumétrie espagnole
    -- encore mesurée à ce stade).
    is_admitted  INTEGER NOT NULL DEFAULT 0 CHECK (is_admitted IN (0, 1)),

    score        INTEGER NOT NULL,
    length       INTEGER NOT NULL CHECK (length >= 2),
    signature    TEXT    NOT NULL,
    reversed     TEXT    NOT NULL,

    -- Nature grammaticale et genre : HORS PÉRIMÈTRE pour ce site (décision explicite
    -- du propriétaire du produit, voir docs/DECISIONS.md ES-001). Colonnes conservées
    -- au schéma UNIQUEMENT parce que app/Search/TermLookup.php les sélectionne déjà en
    -- dur (héritage du site français) -- scripts/import_es.py les laisse TOUJOURS
    -- NULL, aucune source espagnole n'est parcourue pour les peupler dans cette passe.
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

-- Longueur puis ordre alphabétique : /palabras/7-letras et ses paginations.
CREATE INDEX idx_terms_length_normalized ON terms(length, normalized);

-- Anagrammes exactes, et point de départ des anagrammes ±1 lettre.
CREATE INDEX idx_terms_signature ON terms(signature);

-- Suffixes : /palabras/terminan-en/cion interroge reversed par PLAGE, jamais par LIKE
-- (même raison que le site français : LIKE est insensible à la casse par défaut dans
-- SQLite, l'optimiseur ne peut pas l'adosser à un index BINARY, la requête dégénère en
-- balayage complet -- interdit par CLAUDE.md).
CREATE INDEX idx_terms_reversed ON terms(reversed);

-- Suffixe COMBINÉ à une longueur (ex. /palabras/7-letras/terminan-en/s) : même bug de
-- performance que le site français (voir schema.sql de ce dépôt-source, D-020) --
-- sans cet index composé, WordListSolver::anchorClause() ancre sur reversed (plage
-- globale, toutes longueurs confondues) et applique length = ? comme prédicat
-- résiduel non couvert par idx_terms_reversed. Ajouté préventivement ici (même classe
-- de bug déjà mesurée et corrigée côté français), pas encore re-mesuré sur ce jeu de
-- données espagnol -- à vérifier avant toute ouverture SEO future de cette famille.
CREATE INDEX idx_terms_length_reversed ON terms(length, reversed);

-- Familles restreintes à une édition (FILE 2017 / FISE-2), en ordre alphabétique.
-- Index couvrants : ils servent aussi bien le filtre que le tri.
CREATE INDEX idx_terms_ods8 ON terms(is_ods8, normalized);
CREATE INDEX idx_terms_ods9 ON terms(is_ods9, normalized);

-- Filtre "admis seulement"/"non admis seulement" sur les listes /palabras/... (même
-- raison que le site français, D-022-équivalent) : idx_terms_length_admitted_normalized
-- sert le régime EXACT ancré sur une longueur, idx_terms_admitted_normalized sert le
-- même filtre SANS longueur.
CREATE INDEX idx_terms_length_admitted_normalized ON terms(length, is_admitted, normalized);
CREATE INDEX idx_terms_admitted_normalized ON terms(is_admitted, normalized);

-- Tri "par points" sur les listes /palabras/... (même raison que le site français,
-- D-022-équivalent) : nécessaire uniquement pour le régime EXACT ancré sur une seule
-- longueur, sans cet index ORDER BY score forcerait un TEMP B-TREE sur tout le panier
-- avant LIMIT.
CREATE INDEX idx_terms_length_score_normalized ON terms(length, score, normalized);

-- Préfixe ET suffixe d'une seule lettre chacun, avec ou sans longueur (même bug de
-- performance et même correctif que le site français, voir schema.sql de ce
-- dépôt-source, D-025bis) : sans cet index, une requête ancrée sur le préfixe le plus
-- rare applique le suffixe comme prédicat résiduel sur tout le panier ancré --
-- catastrophique quand les deux lettres sont fréquentes. Ajouté préventivement ici,
-- pas encore re-mesuré sur ce jeu de données espagnol.
CREATE INDEX idx_terms_startletter_endletter_normalized
    ON terms(substr(normalized, 1, 1), substr(reversed, 1, 1), normalized);

-- Pas d'index sur is_spanish : cette colonne vaut 1 sur la quasi-totalité des lignes de
-- la base espagnole (toute forme admise au Scrabble OU couverte par l'extrait
-- Wiktionnaire espagnol de kaikki.org) -- un index sur une colonne quasi constante
-- n'apporterait rien, même raisonnement que is_french sur le site français.

-- Liens de conjugaison verbale : HORS PÉRIMÈTRE pour ce site dans cette passe (piste
-- évaluée -- verbecc/mlconjug, licence des gabarits XML espagnols non tranchée avec
-- certitude -- notée comme suite possible, non retenue faute de temps disponible,
-- voir docs/DECISIONS.md ES-001). Table conservée VIDE (0 ligne) uniquement parce que
-- app/Search/ConjugationLookup.php l'interroge déjà (héritage du site français) --
-- une table absente ferait échouer cette requête plutôt que de renvoyer 0 résultat.
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

CREATE INDEX idx_verbforms_lemma ON verb_forms(lemma_normalized);
CREATE INDEX idx_verbforms_form ON verb_forms(form_normalized);

-- Définitions lexicales : HORS PÉRIMÈTRE pour ce site, explicitement (demande du
-- propriétaire du produit -- "ne pas construire de pipeline word_senses, ne pas
-- générer de contenu pour cette table"). Table conservée VIDE (0 ligne) uniquement
-- parce que app/Search/SenseLookup.php l'interroge déjà (héritage du site français) --
-- une table absente ferait échouer cette requête plutôt que de renvoyer 0 résultat.
-- scripts/import_es.py n'écrit JAMAIS dans cette table.
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

CREATE INDEX idx_word_senses_term ON word_senses(term_normalized);

-- Comptes précalculés pour un futur hub de navigation (/palabras) et le maillage
-- interne automatisé : HORS PÉRIMÈTRE pour ce site dans cette passe (registre SEO,
-- sitemaps et maillage combinatoire explicitement exclus, voir docs/DECISIONS.md
-- ES-001). Table conservée mais VIDE (0 ligne) -- champ list_type volontairement
-- réduit aux trois catégories de base (D-017-équivalent côté français), pas
-- l'ensemble étendu du site français (D-022 à D-041), puisqu'aucun des générateurs
-- de maillage combinatoire correspondants n'est porté dans cette passe.
CREATE TABLE list_counts (
    list_type TEXT    NOT NULL CHECK (list_type IN ('length', 'start', 'end')),
    list_key  TEXT    NOT NULL,
    count     INTEGER NOT NULL,

    PRIMARY KEY (list_type, list_key)
);

-- Empreintes des sources et paramètres du build. Aucune date d'exécution :
-- l'import doit rester déterministe et rejouable à l'identique.
CREATE TABLE build_metadata (
    "key"   TEXT PRIMARY KEY,
    "value" TEXT NOT NULL
);
