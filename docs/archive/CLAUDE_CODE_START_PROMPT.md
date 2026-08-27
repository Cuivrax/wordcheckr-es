# Prompt De Lancement Pour La Session Principale Claude Code

```text
Nous lançons la construction du site Scrabble Light.

Lis 00_START_HERE.md et tous les documents qu’il référence.

Le projet doit utiliser PHP 8.4 sans framework, SQLite en lecture seule,
HTML serveur, CSS natif et JavaScript progressif.

Ne modifie encore aucun fichier applicatif.

Utilise l’agent data-engine pour auditer la Phase 0 en lecture seule.
Il doit :
- inventorier les sources ;
- inspecter le vrai JSON ODS8 lorsqu’il est disponible ;
- inspecter le schéma de french_dict.db ;
- vérifier le patch ODS9 ;
- proposer le pipeline d’import ;
- identifier les collisions de normalisation ;
- définir les rapports, tests et benchmarks ;
- lister les fichiers qu’il modifierait après validation.

Retourne son rapport BEFORE STARTING.
Attends ma validation avant l’implémentation.
```
