# 00 — Démarrer Ici

## But

Construire un moteur ultra léger pour le Scrabble et les jeux de lettres,
hébergé sur o2switch.

Le site français est le premier déploiement. Le code devra ensuite pouvoir être
réutilisé pour un site anglais indépendant.

## Point D'Entrée

`CLAUDE.md` à la racine est le point d'entrée de toute session. Il contient l'ordre de lecture,
les contraintes dures, la matrice des agents et la boucle de travail.

## Avant Toute Modification

Lire, dans cet ordre :

```text
1.  CLAUDE.md
2.  docs/01_MASTER_BRIEF.md
3.  docs/02_ARCHITECTURE_DATA_MULTILINGUE.md
4.  docs/03_SOURCES_ET_IMPORT_DATA.md
5.  docs/04_UI_PAGES.md
6.  docs/05_URL_SEO_INDEXATION.md
7.  docs/06_PHASES_IMPLEMENTATION.md
8.  docs/07_CLAUDE_CODE_WORKFLOW.md
9.  docs/08_PROMPTS_PHASES.md    prompts de lancement, phase par phase
10. docs/DECISIONS.md
11. docs/PHASE_STATUS.md
12. .claude/agents/*.md          les 8 agents, build et audit
```

## État Des Données

```text
data/raw/ods8.json        présent — 411 430 mots, A-Z, tous distincts
data/ods9/                présent — integrity ok, 1091 / 64 / 10 / 46
data/raw/french_dict.db   à télécharger — scripts/download_french_dictionary.ps1
```

Vérification : `python scripts/verify_data_pack.py`.
Provenance et empreintes : `data/raw/PROVENANCE.md`.

## Historique

Le pack de lancement d'origine a été promu à la racine du dépôt le 2026-08-03. Les documents
d'amorçage sont conservés dans `docs/archive/`. Leur `MANIFEST.json` est périmé : il ne liste
pas les quatre agents d'audit, ajoutés après sa génération.
