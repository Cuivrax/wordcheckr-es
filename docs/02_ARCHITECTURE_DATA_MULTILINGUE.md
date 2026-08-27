# 02 — Architecture Data Et Multilingue

## Décision Principale

Créer **une base de production par langue et par site**, pas une base mondiale
unique.

Structure recommandée :

```text
site français
├── storage/dictionary_fr.sqlite
└── storage/seo_fr.sqlite

site anglais
├── storage/dictionary_en.sqlite
└── storage/seo_en.sqlite
```

Les sources brutes restent hors production :

```text
data/raw/french_dict.db
data/raw/ods8.json
data/ods9/ods9_patch.sqlite
data/raw/english_general.*
data/raw/nwl2023.*
data/raw/csw24.*
```

## Pourquoi Séparer Les Langues

```text
déploiements plus petits
backups plus rapides
moins d’I/O
autocomplétion limitée à la bonne langue
aucun risque de mélange français/anglais
licences séparables
scores de lettres différents
sitemaps et rollouts indépendants
évolution d’un site sans bloquer l’autre
```

Une base multilingue unique imposerait des index plus gros, des sauvegardes
inutiles et une séparation logique permanente sur chaque requête.

## Ce Qui Est Partagé

Un seul dépôt de code peut gérer plusieurs sites :

```text
app/
scripts/
templates/
tests/
```

Chaque déploiement possède une configuration :

```text
config/sites/fr.php
config/sites/en.php
```

Exemple conceptuel :

```php
return [
    'language' => 'fr',
    'dictionary_path' => __DIR__ . '/../../storage/dictionary_fr.sqlite',
    'seo_path' => __DIR__ . '/../../storage/seo_fr.sqlite',
    'lexicons' => [
        ['column' => 'is_ods8', 'badge' => 'ODS8'],
        ['column' => 'is_ods9', 'badge' => 'ODS9'],
    ],
    'general_language_column' => 'is_french',
    'tile_scores' => [/* valeurs françaises */],
];
```

Le futur site anglais pourra utiliser :

```php
'lexicons' => [
    ['column' => 'is_nwl2023', 'badge' => 'NWL23'],
    ['column' => 'is_csw24', 'badge' => 'CSW24'],
],
'general_language_column' => 'is_english',
```

## Cas Du Site Anglais

Pour un seul domaine anglais couvrant US et UK, utiliser une seule
`dictionary_en.sqlite` avec les deux indicateurs :

```text
is_nwl2023
is_csw24
```

Le chevauchement entre les listes est important, donc deux bases anglaises
séparées dupliqueraient inutilement beaucoup de données.

Si deux domaines distincts US et UK sont créés plus tard, ils peuvent déployer
deux copies ou deux builds filtrés produits depuis la même source anglaise.

## Deux Bases Par Site

`dictionary_XX.sqlite` change rarement et reste strictement en lecture seule.

`seo_XX.sqlite` change plus souvent :

```text
rollout
indexation
sitemaps
maillage autorisé
métadonnées
```

Les séparer permet de publier une nouvelle stratégie SEO sans reconstruire ou
réuploader le dictionnaire principal.

## Base Brute Et Base De Production

Ne pas utiliser directement le fichier Kartmaan de 283 Mo au runtime.

Pipeline :

```text
french_dict.db brut
→ extraction des formes utiles
→ normalisation
→ filtrage
→ fusion ODS8/ODS9
→ calcul des index
→ dictionary_fr.sqlite allégé
```

Aucune définition n’est copiée dans la base de production.
