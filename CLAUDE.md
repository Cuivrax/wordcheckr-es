# Scrabble Light — Site Espagnol

Moteur ultra léger pour le Scrabble et les jeux de lettres. Le site répond à deux questions :

```text
Quel mot puis-je jouer avec mes lettres et mes contraintes ?
Ce terme est-il admis au Scrabble ?
```

Ce n'est ni un blog, ni un CMS, ni un dictionnaire éditorial. Dépôt indépendant, dupliqué
depuis le site français (`git archive`, aucun historique partagé) : même architecture,
même code applicatif partagé (`app/`), déploiement propre en espagnol (domaine prévu
`wordcheckr.es`).

**Périmètre de ce dépôt, décision produit explicite (voir docs/DECISIONS.md ES-001) :**
uniquement le cœur du site — vérification de mot et solveur de rack/liste contrainte.
Nature grammaticale, conjugaison et définitions en prose (équivalent D-018/D-043 du site
français) sont explicitement **hors périmètre** de cette passe. Le registre SEO
(`storage/seo_es.sqlite`, sitemaps, rollout) est également hors périmètre — ce dépôt reste
un travail de build local, jamais déployé.

## Ordre De Lecture Obligatoire

Avant toute modification, dans cet ordre :

```text
docs/01_MASTER_BRIEF.md
docs/02_ARCHITECTURE_DATA_MULTILINGUE.md
docs/03_SOURCES_ET_IMPORT_DATA.md
docs/04_UI_PAGES.md
docs/05_URL_SEO_INDEXATION.md
docs/06_PHASES_IMPLEMENTATION.md
docs/07_CLAUDE_CODE_WORKFLOW.md
docs/08_PROMPTS_PHASES.md
docs/DECISIONS.md
docs/PHASE_STATUS.md
```

Les documents `docs/0X_*.md` sont hérités tels quels du site français (grammaire d'URL,
architecture multilingue, workflow) — leur contenu STRUCTUREL reste valable ; les exemples
concrets qu'ils citent (mots, comptes) restent français et n'ont pas été retraduits mot à
mot dans cette passe (hors périmètre, voir docs/DECISIONS.md ES-001). `docs/DECISIONS.md`
et `docs/PHASE_STATUS.md` sont en revanche spécifiques à ce dépôt à partir de leurs entrées
`ES-*`/section espagnole — consulter en priorité.

`docs/PHASE_STATUS.md` dit quel travail est fait et ce qui reste ouvert dans CE dépôt.
`docs/DECISIONS.md` est à consulter avant tout choix d'architecture, et à compléter après.

## Contraintes Dures

```text
PHP 8.4 sans framework
SQLite local, ouvert en lecture seule au runtime
HTML rendu côté serveur
CSS natif, JavaScript minimal et progressif
hébergement mutualisé o2switch, plusieurs workers PHP concurrents
```

Interdits :

```text
React, Vue, SPA, framework frontend
police externe, image décorative, animation lourde
base distante, processus applicatif permanent
scan complet de la table (~748 000 lignes) au runtime
cache produisant des millions de petits fichiers
texte SEO artificiellement rallongé
dépendance ajoutée sans entrée ## D-XXX/ES-XXX dans docs/DECISIONS.md
```

Cibles de performance :

```text
moins de 10 requêtes SQLite indexées par fiche mot
requêtes préparées uniquement, LIMIT strict systématique
résultat principal présent dans le HTML initial, sans JavaScript
TTFB chaud p95 sous 250 ms
```

Toute requête nouvelle ou modifiée fournit son `EXPLAIN QUERY PLAN`, son temps d'exécution,
son nombre de lignes, et un benchmark avant/après.

## Modèle À Trois Statuts — Fermé

```text
is_ods8 = 1 ou is_ods9 = 1                        → admis au Scrabble
is_spanish = 1 et is_ods8 = 0 et is_ods9 = 0       → forme espagnole non admise
absent de la base                                 → terme inconnu
```

Aucun quatrième statut sémantique ne doit être inventé.

**Noms de colonnes `is_ods8`/`is_ods9` conservés tels quels** (pas renommés en
`is_file2017`/`is_fise2`) bien que la sémantique change pour ce site : `is_ods8` = admis au
**Lexicón FILE 2017**, `is_ods9` = admis au **Lexicón FISE-2 2009**. Raison : ces
identifiants sont référencés en dur par plusieurs requêtes SQL et clés de tableau PHP dans
`app/Search/`, elles-mêmes consommées par `app/View/` (hors périmètre de l'agent
data-engine, jamais modifié dans cette passe) — renommer casserait ce chemin sans qu'aucun
test de ce dépôt ne puisse le couvrir. Les étiquettes VISIBLES (badge) sont correctement
espagnoles : voir `config/sites/es.php` (`'FILE 2017'`, `'FISE-2'`). Détail complet dans
`schema.sql` et `docs/DECISIONS.md` ES-001.

## Tuiles Digrammes CH/LL/RR

Décision produit confirmée (pas une simplification à la lettre unique) : l'édition
internationale/européenne du Scrabble espagnol (100 fiches) utilise des tuiles dédiées CH,
LL, RR — il est interdit de composer ces tuiles à partir de deux tuiles séparées (règle
FISE). `App\Search\Normalizer::tokenizeTiles()` implémente cette tokenisation, utilisée par
`score()`, `signature()`, `App\Search\Rack`, `App\Search\RackSolver` et les catégories
anagrammes de `App\Search\RelationsFinder`. Voir `docs/DECISIONS.md` ES-002 pour le détail
complet (distribution des tuiles, raisonnement, mesures).

## Séparation Build / Runtime (D-007, hérité)

```text
scripts/*     hors ligne (Python pour l'import des sources externes), jamais accessible
              depuis public/, jamais exécuté au runtime
app/, public/  runtime, PHP 8.4 uniquement, lecture seule sur SQLite
```

Aucune écriture sur la base de production au runtime.

## Agents

Les définitions vivent dans `.claude/agents/` — **source unique**. Ne pas en créer de copie
ailleurs dans le dépôt.

Build — droit d'écriture dans leur périmètre :

| Agent | Périmètre |
|---|---|
| `data-engine` | `app/Database/`, `app/Search/`, `scripts/import_*`, `scripts/build_*`, `tests/Search/`, `tests/Database/` |
| `frontend` | `app/View/`, `public/assets/`, `tests/Frontend/` |
| `seo-registry` | `app/Seo/`, `scripts/build_sitemaps*`, `tests/Seo/`, `public/robots.txt` (hors périmètre de la passe actuelle, voir docs/DECISIONS.md ES-001) |
| `microcopy` | `resources/copy/`, `resources/translations/` |

Audit — lecture seule, prononcent **GO / NO GO** :

```text
code-reviewer                 correction, contraintes dures, cohérence des comptes
code-optimizer                uniquement si un problème mesuré existe
design-consistency-reviewer   cohérence visuelle, accessibilité, sans-JS
seo-technical-auditor         registre SEO, canonicals, sitemaps, rollout (hors périmètre actuel)
```

Matrice d'audit par phase : `docs/06_PHASES_IMPLEMENTATION.md`. Ne pas lancer les quatre audits
après chaque micro-tâche.

## Fichiers Partagés

```text
schema.sql
app/Config.php
public/index.php
docs/DECISIONS.md
docs/PHASE_STATUS.md
```

Sous contrôle de la session principale. Un agent peut proposer un diff, jamais les modifier
silencieusement.

## Boucle De Travail

```text
1 agent build   → rapport BEFORE, implémentation, rapport AFTER + READY FOR AUDIT
1 agent audit   → GO ou NO GO
validation humaine
commit
phase suivante
```

Séquence pour tout changement d'architecture : analyser sans rien modifier → proposer →
**attendre validation explicite** → implémenter → tester → rapport diff + mesures.

## Commits

Un commit par unité validée, nommé par phase :

```text
phase-es-0-normalize-schema-config
phase-es-1-import-pipeline-digraph-tiles
phase-es-2-relationsfinder-digraph-and-mbsafe
phase-es-3-mbsafe-audit-and-scope-pruning
phase-es-4-tests-database-suggester-termlookup
```

## État Des Données

```text
data/raw/file_2017.json               Lexicón FILE 2017, 639 292 mots bruts — présent
data/raw/an_array_of_spanish_words.json  Lexicón FISE-2 2009 (canal MIT), 636 598 mots — présent
data/raw/kaikki_es/                   extrait Wiktionnaire espagnol, ~100 Mo compressé — présent
storage/dictionary_es.sqlite          748 165 termes, 232,9 Mo — construite, integrity ok
```

La base ne retient aucune forme de plus de 15 caractères : injouable sur un plateau standard.

Empreintes et provenance : `data/raw/PROVENANCE.md`.

La base est notre construction propre : formes normalisées, indicateurs et scores, aucune
définition. **Le site ne publie aucun crédit de source** (même discipline que D-015 du site
français) — ni page de licence, ni mention en pied de page, ni commentaire dans le HTML
servi.
