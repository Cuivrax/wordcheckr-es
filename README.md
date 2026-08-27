# Scrabble Light — Site Espagnol

Moteur ultra léger pour le Scrabble et les jeux de lettres. PHP 8.4 sans framework,
SQLite en lecture seule, hébergement mutualisé o2switch. Domaine prévu : `wordcheckr.es`.

Dépôt indépendant, dupliqué depuis le site français (`git archive`, aucun historique
partagé) — même code applicatif (`app/`), configuration et données propres à l'espagnol.
Périmètre de ce dépôt (voir `docs/DECISIONS.md` ES-001) : uniquement le cœur du site,
vérification de mot et solveur de rack — build local, jamais déployé.

Point d'entrée pour toute session de travail : `CLAUDE.md`.

## Décision Multilingue

```text
une base de production par langue et par site
un registre SEO séparé par domaine (hors périmètre de ce dépôt à ce stade, ES-001)
un code partagé
```

Fichiers de production :

```text
storage/dictionary_es.sqlite    748 165 termes, 232,9 Mo — construite
storage/seo_es.sqlite           non construite dans cette passe (ES-001)
```

## Arborescence

```text
CLAUDE.md            constitution du projet (adaptee au site espagnol)
.claude/agents/       les agents, source unique
docs/                 cadrage 01 a 08, DECISIONS (section ES-*), PHASE_STATUS
data/raw/             sources brutes, hors Git (voir data/raw/PROVENANCE.md)
schema.sql            schema de production (adapte, voir DECISIONS ES-001)
scripts/               import (Python), scripts/import_es.py
tests/                 suite de tests (php tests/run.php)
reports/               rapports generes, hors Git
storage/               bases de production generees, hors Git
```

## Sources De Données

```text
Lexicón FILE 2017 (2017)     data/raw/file_2017.json, 639 292 mots -- fourni par le
                              proprietaire du produit (kamilmielnik/scrabble-
                              dictionaries, aucune licence propre declaree)
Lexicón FISE-2 (2009)        data/raw/an_array_of_spanish_words.json, 636 598 mots --
                              canal MIT (words/an-array-of-spanish-words)
kaikki.org eswiktionary      data/raw/kaikki_es/, extrait Wiktionnaire espagnol
                              (CC-BY-SA + GFDL), couche "mot espagnol reel"
```

Détail complet, empreintes sha256, licences et méthode de détection d'un défaut de
concaténation confirmé dans la source FILE 2017 : `data/raw/PROVENANCE.md`.

## Démarrage

```text
1. lire CLAUDE.md, puis docs/DECISIONS.md (section ES-*)
2. les sources sont deja presentes dans data/raw/ (voir data/raw/PROVENANCE.md) --
   sinon : python scripts/download_wordlists_es.py et
   python scripts/download_kaikki_spanish.py (le Lexicon FILE 2017 doit etre fourni
   manuellement, aucune licence propre ne permet de le re-heberger)
3. python scripts/import_es.py
4. php tests/run.php
```

## Tuiles Digrammes CH/LL/RR

Décision produit confirmée (`docs/DECISIONS.md` ES-002) : l'édition internationale du
Scrabble espagnol (100 fiches) utilise des tuiles dédiées CH/LL/RR, pas une simplification
à la lettre unique. `App\Search\Normalizer::tokenizeTiles()` implémente cette
tokenisation.

## Important

Le site ne publie aucun crédit de source (même discipline que D-015 du site français). Les
URL et empreintes des sources restent dans `data/raw/PROVENANCE.md`, à usage interne, pour
que l'import reste reproductible.

Aucune des deux listes Scrabble espagnoles n'a de licence publique claire adossée au
lexique FISE/FILE lui-même (voir `data/raw/PROVENANCE.md` §1-2) — risque accepté par le
propriétaire du produit, même régime que l'ODS8/ODS9 français.
