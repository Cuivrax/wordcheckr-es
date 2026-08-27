# 04 — Pages Et Interface

## Références Visuelles

```text
prototype/index.html
prototype/mot-qi.html
prototype/mot-poser.html
```

Ces fichiers sont des références de rendu, pas l’architecture finale.

En production :

```text
CSS externe versionné
HTML rendu par PHP
JavaScript séparé et différé
données calculées côté serveur
```

## Home

Ordre obligatoire :

```text
header
badge discret
H1
formulaire
résultats
liens contextuels
deux paragraphes courts
footer
```

Aucun paragraphe entre le H1 et le formulaire.

## Champ En Tuiles

L’input HTML reste la source de vérité.

Les tuiles :

```text
affichent chaque lettre
affichent la valeur
acceptent collage et clavier
restent une amélioration progressive
```

Le formulaire doit fonctionner sans JavaScript.

## Autocomplétion

```text
2 caractères minimum
8 propositions maximum
préfixe uniquement
ARIA combobox
flèches haut/bas
Entrée
Échap
```

Badges possibles :

```text
Admis ODS8 · ODS9
Admis ODS8
Admis ODS9
Français · Non admis
```

## Fiche Mot

Le bloc score contient :

```text
score total
longueur
tuiles de chaque lettre
```

La somme des tuiles doit toujours être égale au score affiché.

## Relations

Simple flux de liens en ligne.

Limites visibles définies par catégorie. Les sections vides ne sont pas
rendues.

Une cible non encore ouverte au rollout peut être affichée en texte simple
plutôt qu’en lien.

## Accessibilité

Tester :

```text
clavier seul
focus visible
collage
JavaScript désactivé
mobile
mot de 2 lettres
mot de 15 lettres
150 liens sur une fiche
autocomplétion tactile
```
