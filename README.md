# Scrabble Light — Site Français

Moteur ultra léger pour le Scrabble et les jeux de lettres. PHP 8.4 sans framework,
SQLite en lecture seule, hébergement mutualisé o2switch.

Point d'entrée pour toute session de travail : `CLAUDE.md`, puis `00_START_HERE.md`.

## Décision Multilingue

```text
une base de production par langue et par site
un registre SEO séparé par domaine
un code partagé
```

Fichiers de production :

```text
storage/dictionary_fr.sqlite
storage/seo_fr.sqlite
storage/dictionary_en.sqlite      site anglais, plus tard
storage/seo_en.sqlite
```

## Arborescence

```text
CLAUDE.md            constitution du projet
00_START_HERE.md     ordre de lecture et état des données
.claude/agents/      les 8 agents, source unique
docs/                cadrage 01 à 07, DECISIONS, PHASE_STATUS
docs/archive/        documents d'amorçage du pack de lancement
data/raw/            sources brutes, hors Git
data/ods9/           patch ODS9
schema/              proposition de schéma
scripts/             téléchargement, vérification, import (Python)
prototype/           références de rendu HTML
reports/             rapports générés, hors Git
storage/             bases de production générées, hors Git
```

## Démarrage

```text
1. lire CLAUDE.md
2. télécharger french_dict.db
3. exécuter python scripts/verify_data_pack.py
4. lancer l'audit Phase 0 avec data-engine
```

## Télécharger Kartmaan

Windows PowerShell :

```powershell
.\scripts\download_french_dictionary.ps1
```

Linux/macOS :

```bash
./scripts/download_french_dictionary.sh
```

## Important

Le pack ODS9 est un delta. Il ne constitue pas une certification officielle d'exhaustivité ODS9.

Le site ne publie aucun crédit de source pour le dictionnaire français (D-015). Les URL et
empreintes des sources restent dans `data/raw/PROVENANCE.md`, à usage interne, pour que
l'import reste reproductible.
