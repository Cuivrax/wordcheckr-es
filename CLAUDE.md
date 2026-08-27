# Scrabble Light — Site Français

Moteur ultra léger pour le Scrabble et les jeux de lettres. Le site répond à deux questions :

```text
Quel mot puis-je jouer avec mes lettres et mes contraintes ?
Ce terme est-il admis au Scrabble ?
```

Ce n'est ni un blog, ni un CMS, ni un dictionnaire éditorial. Le site français est le premier
déploiement ; le code doit rester réutilisable pour un futur site anglais indépendant.

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

`docs/08_PROMPTS_PHASES.md` contient le prompt de lancement exact de chaque phase et de
chaque audit, prêt à copier-coller.

`docs/PHASE_STATUS.md` dit quelle phase est ouverte. Ne pas travailler sur une phase fermée.
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
scan complet de la table (~838 000 lignes) au runtime
cache produisant des millions de petits fichiers
texte SEO artificiellement rallongé
dépendance ajoutée sans entrée ## D-XXX dans docs/DECISIONS.md
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
is_ods8 = 1 ou is_ods9 = 1                       → admis au Scrabble
is_french = 1 et is_ods8 = 0 et is_ods9 = 0      → forme française non admise
absent de la base                                → terme inconnu
```

Aucun quatrième statut sémantique ne doit être inventé.

## Séparation Build / Runtime (D-007)

```text
scripts/*     hors ligne (Python pour l'import des sources externes, PHP pour les
              artefacts dérivés du runtime — registre SEO, sitemaps, comptes
              précalculés), jamais accessible depuis public/, jamais exécuté au
              runtime (voir D-007 pour le détail par script)
app/, public/  runtime, PHP 8.4 uniquement, lecture seule sur SQLite
```

Aucune écriture sur la base de production au runtime.

## Agents

Les 8 définitions vivent dans `.claude/agents/` — **source unique**. Ne pas en créer de copie
ailleurs dans le dépôt.

Build — droit d'écriture dans leur périmètre :

| Agent | Périmètre |
|---|---|
| `data-engine` | `app/Database/`, `app/Search/`, `scripts/import_*`, `scripts/build_*`, `tests/Search/`, `tests/Database/` |
| `frontend` | `app/View/`, `public/assets/`, `tests/Frontend/` |
| `seo-registry` | `app/Seo/`, `scripts/build_sitemaps*`, `tests/Seo/`, `public/robots.txt` |
| `microcopy` | `resources/copy/`, `resources/translations/` |

Audit — lecture seule, prononcent **GO / NO GO** :

```text
code-reviewer                 correction, contraintes dures, cohérence des comptes
code-optimizer                uniquement si un problème mesuré existe
design-consistency-reviewer   cohérence visuelle, accessibilité, sans-JS
seo-technical-auditor         registre SEO, canonicals, sitemaps, rollout
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

Une tâche correcte est bornée (« analyse l'import Kartmaan, ne modifie rien, retourne le schéma
détecté, les filtres proposés, les collisions possibles »). Une tâche trop large ne l'est pas
(« construis tout le moteur »).

## Commits

Un commit par unité validée, nommé par phase :

```text
phase-0-scaffold
phase-0-agents
phase-0-import-source
phase-0-normalization
phase-1-word-page
```

## État Des Données

```text
data/raw/ods8.json          411 430 mots bruts, dont 402 325 ≤ 15 lettres — présent
data/ods9/                  patch delta, integrity ok — présent
data/raw/french_dict.db     283 Mo — python scripts/download_french_dictionary.ps1
data/raw/hbenbel/           5 CSV — python scripts/download_hbenbel.py
storage/dictionary_fr.sqlite  838 180 termes, 236,5 Mo — construite (reconstruite D-022)
```

La base ne retient aucune forme de plus de 15 lettres : injouable sur un plateau (D-010).

Empreintes et provenance : `data/raw/PROVENANCE.md`. Vérification : `python scripts/verify_data_pack.py`.

La base est notre construction propre : formes normalisées, indicateurs et scores, aucune
définition. **Le site ne publie aucun crédit de source** (D-015) — ni page de licence, ni
mention en pied de page, ni commentaire dans le HTML servi.
