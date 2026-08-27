-- Scrabble Light — registre SEO du site français.
-- Fichier canonique de ce schéma, périmètre de l'agent seo-registry (CLAUDE.md).
-- Produit par scripts/build_seo_registry.php dans storage/seo_fr.sqlite.
-- Base distincte du dictionnaire (D-003) : storage/seo_fr.sqlite, jamais
-- storage/dictionary_fr.sqlite. Ouverte en lecture seule au runtime, comme le
-- dictionnaire (D-001), mais réécrite bien plus souvent que lui (docs/02) : chaque
-- lot de rollout est une nouvelle passe d'écriture hors ligne, jamais au runtime.
--
-- Unique source de vérité pour index/noindex, canonical, sitemaps, maillage,
-- rollout et métadonnées (docs/05_URL_SEO_INDEXATION.md). Une route ABSENTE de la
-- table `registry` reste noindex, follow — c'est le comportement par défaut
-- appliqué par App\Seo\Registry::resolve() dès que storage/seo_fr.sqlite est
-- absent OU qu'aucune ligne ne correspond au chemin demandé (D-005 : "aucune route
-- n'est indexable par défaut"). Cette table ne contient donc QUE des décisions
-- explicites, jamais un miroir de toutes les routes possibles — un miroir complet
-- des ~838 180 fiches mot avant tout rollout validé serait lui-même une forme
-- d'indexation par omission déguisée.

CREATE TABLE registry (
    route_path       TEXT    PRIMARY KEY,
    -- Chemin absolu exact tel que servi par public/index.php, sans domaine :
    --   '/'
    --   '/mot/poser'
    --   '/mots/7-lettres'
    --   '/mots/commencant/ch/page/2'
    -- Jamais une forme non canonique : WordListFilters::canonicalUrl() (ou
    -- l'équivalent pour /mot, /jouer) est toujours la source de la valeur insérée
    -- (docs/05 : "toute autre permutation redirige en 301", jamais indexée).

    family           TEXT    NOT NULL,
    -- Regroupement de reporting et de gouvernance, voir app/Seo/Family.php pour la
    -- liste fermée des valeurs acceptées. Sert à produire les métriques quantifiées
    -- exigées par lot (URL par famille) et à appliquer les règles dures par famille
    -- (ex. les familles avec/sans/motif ne doivent jamais recevoir de
    -- sitemap_fragment — combinaisons infinies, jamais indexables par défaut).

    robots           TEXT    NOT NULL CHECK (robots IN ('index,follow', 'noindex,follow')),
    -- Fermé à deux valeurs : ni noindex,nofollow ni index,nofollow n'ont d'usage
    -- documenté ici (docs/05 ne mentionne que ces deux formes). Toute route qui a
    -- besoin d'une troisième valeur est un signal d'architecture à faire remonter,
    -- pas une valeur à ajouter silencieusement.

    canonical_path   TEXT    NOT NULL,
    -- Chemin absolu (sans domaine) du gagnant canonique. Égal à route_path quand
    -- cette ligne EST le gagnant (cas normal). Différent uniquement pour une
    -- variante explicitement alias-ée vers un autre gagnant — jamais deux routes
    -- avec robots='index,follow' qui pointent vers des canonicals différents pour
    -- un même contenu (refusé par scripts/apply_seo_batch.php).

    sitemap_fragment TEXT,
    -- NULL = absente de tout sitemap. Sinon, nom du fragment sans extension
    -- (ex. 'words-0001'), conforme aux préfixes documentés (docs/05 Sitemaps) :
    -- words-*, invalid-french-*, starts-*, ends-*, contains-*, letters-*, core-*,
    -- combined-* (D-025), position-* (D-028), avec-single-* (palier 1 de "avec",
    -- 2026-08-17) -- liste tenue à jour au fil des familles ouvertes, voir
    -- FAMILY_FRAGMENT_PREFIXES dans scripts/build_sitemaps.php pour la table
    -- exacte famille -> préfixe réellement appliquée (source de vérité, cette
    -- ligne de commentaire n'en est qu'un résumé).
    -- Jamais renseignée pour robots != 'index,follow' (appliqué par
    -- scripts/apply_seo_batch.php, pas seulement documenté ici).

    batch_id         TEXT,
    -- Identifiant du lot de rollout ayant introduit ou modifié cette ligne, NULL
    -- pour une route hors gouvernance de lot (aucune actuellement — toute ligne
    -- présente ici est par construction une décision de lot, y compris la home).

    result_count     INTEGER,
    -- Total de résultats au moment de l'entrée en registre, pour une liste
    -- /mots/... (WordListPage::$total en mode exact). NULL pour /, /mot/{mot} et
    -- /jouer/{lettres}, qui n'ont pas la notion de "nombre de résultats". Une
    -- valeur de 0 avec robots='index,follow' est refusée par
    -- scripts/apply_seo_batch.php (page à résultat vide jamais indexable). Une
    -- valeur de 1 N'EST PAS refusée : docs/05 est explicite, "jamais [décidé] sur
    -- le seul compteur" — ce champ sert à isoler ces pages pour un examen séparé
    -- dans le rapport, pas à les exclure automatiquement.

    notes            TEXT,
    -- Justification courte et humaine de la décision (pourquoi ce lot, pourquoi
    -- cette route). Optionnelle mais fortement recommandée pour tout ce qui
    -- s'écarte du gabarit par défaut d'une famille.

    added_at         TEXT    NOT NULL
    -- Date ISO 8601 (YYYY-MM-DD) d'entrée dans le registre. Trace d'audit du
    -- rollout, pas une contrainte de reproductibilité déterministe comme
    -- build_metadata du dictionnaire (D-002 : ce registre change souvent, par
    -- construction — contrairement à storage/dictionary_fr.sqlite).
);

CREATE INDEX idx_registry_family  ON registry(family);
CREATE INDEX idx_registry_sitemap ON registry(sitemap_fragment);
CREATE INDEX idx_registry_batch   ON registry(batch_id);

-- Empreintes et paramètres du dernier build, même convention que schema.sql du
-- dictionnaire (colonnes entre guillemets : "key"/"value" ne sont pas réservés en
-- SQLite mais le sont dans d'autres dialectes).
CREATE TABLE build_metadata (
    "key"   TEXT PRIMARY KEY,
    "value" TEXT NOT NULL
);
