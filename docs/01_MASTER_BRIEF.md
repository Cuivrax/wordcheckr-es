# 01 — Brief Maître Du Site

## Vision

Créer un site indépendant, extrêmement rapide et minimal, qui répond
immédiatement à deux questions :

```text
Quel mot puis-je jouer avec mes lettres et mes contraintes ?
Ce terme est-il admis au Scrabble ?
```

Le produit n’est ni un blog, ni un CMS, ni un dictionnaire éditorial.

## Hébergement Et Technologie

```text
hébergement mutualisé o2switch
PHP 8.4 sans framework
SQLite en lecture seule en production
HTML rendu côté serveur
CSS natif
JavaScript minimal et progressif
OPcache
cache HTTP LiteSpeed ou Varnish après mesure
```

Interdictions :

```text
React, Vue ou SPA
police externe
image décorative
base distante
processus applicatif permanent
scan de toute la base à chaque requête
cache produisant des millions de petits fichiers
texte SEO artificiellement rallongé
```

## Trois États D’Un Terme

Après nettoyage et import, chaque saisie peut être :

```text
1. mot admis au Scrabble
2. forme française réelle mais non admise
3. terme inconnu
```

Pour le site français :

```text
is_ods8 = 1 ou is_ods9 = 1
→ admis dans au moins une édition

is_french = 1 et is_ods8 = 0 et is_ods9 = 0
→ forme française non admise

absent de la base
→ terme inconnu
```

Toutes les formes françaises simples retenues après filtrage auront une fiche
publique et ont vocation à devenir indexables. L’ouverture dans les sitemaps
reste progressive pour protéger le crawl et l’hébergement.

Les termes réellement inconnus restent `noindex`.

## Fonctions De La Home

La home permet :

```text
vérifier un mot
chercher avec un chevalet
un ou deux jokers
choisir une longueur
commencer par une suite
terminer par une suite
contenir une suite exacte
imposer des lettres avec répétitions
exclure des lettres
utiliser un motif de cases connues
```

Le H1 est :

```text
Quel Mot Pouvez-Vous Jouer ?
```

Le formulaire reste immédiatement visible. Les deux paragraphes de contexte
sont placés en bas de page, juste avant le footer.

## Autocomplétion

À partir de deux caractères :

```text
huit suggestions maximum
préfixe exact uniquement
délai de 140 à 180 ms
navigation clavier
statut visible
```

Exemples :

```text
POSER          Admis ODS8 · ODS9
QUEULEULEU     Français · Non admis
```

Le formulaire reste fonctionnel sans JavaScript.

## Fiche Mot

La fiche affiche :

```text
mot
admis ou non admis
badges ODS8 et ODS9 actifs ou grisés
score brut
longueur
une tuile par lettre avec sa valeur
réponse directe
relations entre mots
recherches liées
mot précédent et suivant
champ de vérification
```

Phrase valide :

```text
POSER est valide dans le dictionnaire officiel du Scrabble. Son score brut est
de 7 points, hors bonus de plateau.
```

Phrase non admise :

```text
QUEULEULEU existe en français, mais n’est admis ni dans l’ODS8 ni dans l’ODS9.
Il ne peut pas être joué comme un mot unique.
```

## Relations De La Fiche

Afficher uniquement les catégories non vides :

```text
anagrammes exactes
changer une lettre
retirer une lettre
insérer une lettre
sous-mots
rallonges à gauche
rallonges à droite
mots plus longs contenant le mot
anagrammes avec une lettre en plus
anagrammes avec une lettre en moins
```

Présentation :

```text
simples liens en ligne
surlignage léger de la partie conservée ou modifiée
aucune carte par mot
aucune définition
aucune section vide
```

Plafond recommandé :

```text
environ 160 liens de mots
12 recherches liées
2 mots voisins
```

Une grande liste affiche un lien `Voir les N mots`.

## Performance

Objectifs :

```text
moins de 10 requêtes SQLite indexées par fiche
aucun scan complet au runtime
aucun appel réseau vers une base
résultat principal dans le HTML initial
TTFB chaud p95 visé sous 250 ms
```

Toute nouvelle requête doit fournir :

```text
EXPLAIN QUERY PLAN
temps d’exécution
nombre de lignes
benchmark avant/après
```
