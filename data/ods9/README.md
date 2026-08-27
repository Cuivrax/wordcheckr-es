# Pack de données ODS9 disponible

## Fichiers

- `ods9_additions_flat.json`
  - tableau JSON simple de 1091 termes ajoutés ou variantes ODS9
  - format le plus proche d'un JSON de mots

- `ods9_removals_flat.json`
  - tableau JSON simple des 64 formes ODS8 retirées en ODS9

- `ods9_status_records.json`
  - objets avec `word`, `normalized`, `is_ods8`, `is_ods9` et le type de changement

- `ods9_patch_complete.json`
  - paquet détaillé avec ajouts, retraits, homographes à conserver et modifications éditoriales

- `ods9_patch.sqlite`
  - même delta dans un SQLite directement exploitable

- `target_terms_schema.sql`
  - schéma minimal conseillé pour la base finale

## Ordre de fusion

1. Importer le dictionnaire français nettoyé avec `is_french = 1`.
2. Importer le JSON ODS8 :
   - `is_ods8 = 1`
   - `is_ods9 = 1` par défaut
3. Appliquer les 64 retraits :
   - `is_ods9 = 0`
4. Appliquer les 10 homographes à conserver :
   - `is_ods9 = 1`
5. Insérer les 1091 ajouts ou variantes :
   - `is_ods8 = 0` s'ils n'existent pas dans ODS8
   - `is_ods9 = 1`

## Limite importante

Ce pack contient **tout ce que nous avons actuellement dans les listes de la
conversation**. Il s'agit d'un delta de 1091 ajouts/variantes et
64 retraits.

Ce n'est pas encore un fichier ODS9 complet de toutes les formes jouables :
pour produire `ods9_full.json`, il faut fusionner ce patch avec le JSON ODS8
exact de l'utilisateur. Il ne faut pas inventer automatiquement des pluriels,
féminins ou conjugaisons qui ne figurent pas explicitement dans les sources.
