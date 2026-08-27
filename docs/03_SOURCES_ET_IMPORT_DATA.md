# 03 — Sources Et Import Des Données

## 1. Dictionnaire Français Général

Source retenue :

```text
Kartmaan/french-dictionary
```

Page :

```text
https://huggingface.co/datasets/Kartmaan/french-dictionary
```

Fichiers disponibles au moment de la préparation du pack :

```text
french_dict.db        environ 283 Mo
french_dict.parquet   environ 22,6 Mo
```

Téléchargement recommandé pour le pipeline SQLite :

```text
french_dict.db
```

Le fichier brut ne doit pas être publié dans le dossier web.

Le site ne publie aucun crédit de source (D-015).

## 2. ODS8

Fichier utilisateur attendu :

```text
data/raw/ods8.json
```

Son format exact doit être audité avant l’import.

Ne pas supposer que le JSON est un simple tableau de chaînes sans l’avoir lu.

## 3. Patch ODS9 Disponible

Le dossier `data/ods9/` contient :

```text
ods9_additions_flat.json
ods9_removals_flat.json
ods9_status_records.json
ods9_patch_complete.json
ods9_patch.sqlite
target_terms_schema.sql
manifest.json
README.md
```

Compteurs actuels :

```text
1 091 ajouts ou variantes
64 retraits
10 homographes à conserver
46 modifications éditoriales
```

Ce pack est le delta actuellement disponible. Il ne doit pas être présenté
comme un dictionnaire ODS9 exhaustif et certifié de toutes les formes fléchies.

## 4. Ordre De Fusion Français

```text
1. extraire et nettoyer les formes du SQLite Kartmaan
2. insérer les formes retenues avec is_french = 1
3. importer ODS8 avec is_ods8 = 1 et is_ods9 = 1 par défaut
4. appliquer les retraits ODS9 avec is_ods9 = 0
5. appliquer les keep_overrides avec is_ods9 = 1
6. appliquer les ajouts ODS9 avec is_ods9 = 1
7. calculer score, longueur, signature et reversed
8. construire les postings et index
9. produire les rapports
```

## 5. Filtrage Du Dictionnaire Général

Retenir les formes simples utiles au site.

Écarter ou isoler :

```text
expressions avec espaces
noms propres identifiés
sigles
abréviations
formes contenant chiffres ou ponctuation incompatible
entrées étrangères
formes purement métalinguistiques
doublons de normalisation
```

La règle finale doit être documentée et reproductible.

Toutes les formes conservées avec `is_french = 1` auront une fiche publique.

## 6. Rapports Obligatoires

```text
reports/import-summary.json
reports/duplicates.csv
reports/rejected-forms.csv
reports/normalization-collisions.csv
reports/ods8-ods9-status-counts.json
reports/sqlite-integrity.txt
reports/query-plans/
```

`import-summary.json` doit notamment contenir :

```json
{
  "french_source_rows": 0,
  "french_distinct_normalized": 0,
  "french_rejected": 0,
  "ods8_rows": 0,
  "ods9_additions": 1091,
  "ods9_removals": 64,
  "ods8_only": 0,
  "ods9_only": 0,
  "ods8_and_ods9": 0,
  "french_non_ods": 0,
  "normalization_collisions": 0
}
```

## 7. Crédits De Source

Aucun crédit de source n'est publié sur le site (D-015).

La base de production est une construction propre : formes normalisées,
indicateurs d'admissibilité, scores et dérivés. Aucune définition, aucun texte
éditorial et aucune structure de données d'origine n'y sont repris.

Les URL et empreintes des sources restent dans `data/raw/PROVENANCE.md`, à
usage interne, pour que l'import demeure reproductible.

Ce document n'est pas un avis juridique.
