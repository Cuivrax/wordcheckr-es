# Provenance Des Données Brutes — Site Espagnol

**Document interne.** Il existe pour une seule raison : rendre l'import reproductible à
l'identique. Rien de son contenu n'est publié sur le site — aucun crédit de source n'y figure
(même discipline que le site français, D-015).

Ce dossier est exclu de Git. Les fichiers y sont reconstitués grâce aux empreintes ci-dessous.

**Ce document ne reprend pas la recherche complète.** Voir le rapport de recherche du site
français, `reports/es-site-feasibility-audit.md` (licences, historique FISE/FILE, comparaisons
de candidats, tuiles) pour le raisonnement détaillé — ce fichier documente uniquement l'état
final des sources effectivement retenues et leurs empreintes.

## 1. file_2017.json — Lexicón FILE 2017

```text
nom d'origine   scrabble-spanish-ES-FILE-2017.json (fourni par le propriétaire du produit,
                dossier local "01. Data Scrabble/JSON/", hors de ce dépôt)
copié le        2026-08-27
taille          9 664 052 octets
sha256          8a7af425d820386faf82aa0550a8243b7299259f46e3a77be702993caead9bea
```

Structure vérifiée :

```json
{"words": ["aba", "abaa", "abaas", "..."]}
```

```text
639 292 entrées, toutes en minuscules
639 292 distinctes (aucun doublon)
charset strictement a-z + ñ, aucun accent de voyelle, aucun ü, aucun espace, aucune ponctuation,
  aucun chiffre
0 occurrence de K, 0 occurrence de W dans tout le fichier
14 768 entrées contenant ñ
longueurs de 2 à 22 caractères (1 530 entrées de plus de 15 caractères, écartées par le
  plafond du site, voir schema.sql / scripts/import_es.py)
```

**Identité confirmée avec la copie communautaire publique.** Comparaison octet à octet
(sha256) avec `spanish/file-2017.txt` du dépôt GitHub `kamilmielnik/scrabble-dictionaries`
(branche `master`, `https://raw.githubusercontent.com/kamilmielnik/scrabble-dictionaries/master/spanish/file-2017.txt`,
téléchargé le 2026-08-27) : **identique**. C'est donc très exactement cette même source
communautaire, pas une extraction indépendante.

**Licence : aucune déclarée.** Le dépôt `kamilmielnik/scrabble-dictionaries` n'a pas de licence
publique (`license: null` via l'API GitHub, vérifié le 2026-08-27). Aucune autre source mieux
licenciée n'a été trouvée pour ce contenu précis (contrairement à la source 2 ci-dessous).
Risque accepté par le propriétaire du produit, même régime que l'ODS8/ODS9 du site français
(D-015 : « fourni par l'utilisateur, les droits d'usage relèvent de l'utilisateur »).
Ce document n'est pas un avis juridique.

**Défaut de données confirmé et corrigé à l'import.** La ligne `ababillaraababillarais`
(22 caractères) est la concaténation sans séparateur de deux entrées valides distinctes —
`ababillara` et `ababillarais` — perdues comme lignes séparées (saut de ligne perdu lors d'une
étape d'édition antérieure à l'obtention de ce fichier). Détecté par un balayage systématique
(pas seulement ce cas connu, voir méthode ci-dessous), et retiré par `scripts/import_es.py`
avant fusion — sans effet sur la base construite au-delà de ce retrait : `ababillara` et
`ababillarais` sont de toute façon couverts indépendamment par la source 2 (`fise-2`, voir
ci-dessous), et la forme fautive dépassait de toute façon le plafond de 15 lettres.

Méthode de détection (reproductible, `scripts/lib/detect_concat_defects.py` — voir aussi
`reports/`) : pour chaque mot W présent UNIQUEMENT dans `file_2017` (absent de la source 2),
recherche d'un point de coupe i tel que W[:i] ET W[i:] soient TOUS DEUX des entrées de la
source 2, ET qu'AUCUN des deux morceaux ne soit lui-même une entrée de `file_2017` (signature
attendue d'une fusion accidentelle de deux lignes originellement distinctes). 3 candidats bruts
trouvés ; 2 écartés après vérification du contexte alphabétique local dans le fichier source
(`dimanante` = `dima`+`nante` et `dimanantes` = `dima`+`nantes` s'insèrent naturellement dans le
paradigme de conjugaison de « dimanar » — `dimanais, dimanamos, dimanan, dimanando, dimanante,
dimanantes, dimanar, dimanara...` — donc ce sont des mots réels, pas un artefact ; seul
`ababillaraababillarais` s'insère à la place d'un unique infinitif manquant, cohérent avec un
saut de ligne perdu). Vérification symétrique côté source 2 (candidats splittables contre
`file_2017`) : 2 candidats bruts (`cachamarin`, `cachamarines`), tous deux écartés pour la même
raison (paradigme réel « cach- », voisinage alphabétique incompatible avec un artefact de
concaténation). Bilan : **1 défaut confirmé au total sur les deux sources combinées**, celui déjà
identifié par la recherche préalable — aucun défaut supplémentaire trouvé par le balayage complet.

## 2. an_array_of_spanish_words.json — Lexicón FISE-2 2009 (canal MIT)

```text
source          https://github.com/words/an-array-of-spanish-words (fichier index.json,
                branche master)
téléchargé le   2026-08-27
taille          8 328 819 octets
sha256          c43d6d90db76f9fa38f6885227895562bde7c4c70cd6cfe23b37f369c1f7b4a1
licence         MIT (Zeke Sikelianos, 2016 — fichier `license` du dépôt, `license.key: mit`
                confirmé via l'API GitHub le 2026-08-27)
```

```text
636 598 entrées, toutes en minuscules, dédupliquées
```

**Choix délibéré du canal MIT plutôt que la copie brute `fise-2.txt`.** Le rapport de
recherche du site français (`reports/es-site-feasibility-audit.md`, §1.4-1.7) documente que
`kamilmielnik/scrabble-dictionaries` (`spanish/fise-2.txt`, 636 599 entrées, même absence de
licence que la source 1) et `words/an-array-of-spanish-words` sont, à un mot près, LE MÊME
contenu (« Lexicón FISE-2 2009 » propagé via la liste de mots du jeu mobile *Letterpress*).
Vérifié directement le 2026-08-27 : `fise-2.txt` (téléchargé depuis
`https://raw.githubusercontent.com/kamilmielnik/scrabble-dictionaries/master/spanish/fise-2.txt`,
sha256 `3af8c1f519ca19d4f77986f19641a87df446cb2e8af655ffdffafa9c8d5f3a62`, 636 599 entrées) contre
`an_array_of_spanish_words.json` : 636 598/636 599 mots communs, le seul mot manquant côté MIT
(`zuñisteis`) est de toute façon présent dans la source 1 (`file_2017.json`) — la fusion finale
n'y perd donc rien. Le canal MIT offre une licence explicite et vérifiable pour un contenu
fonctionnellement identique : retenu comme source 2 effective de ce dépôt à la place de la copie
`kamilmielnik/spanish/fise-2.txt` (jamais importée, uniquement téléchargée transitoirement pour
cette vérification croisée — pas conservée dans `data/raw/`).

## 3. kaikki_es/ — extrait Wiktionnaire espagnol (couche « mot espagnol réel », équivalent
   `is_french` du site français)

```text
source          https://kaikki.org/eswiktionary/Español/ (extraction du WIKTIONNAIRE
                ESPAGNOL, eswiktionary -- PAS "kaikki.org/dictionary/Spanish/", qui documente
                le vocabulaire espagnol avec des gloses en ANGLAIS depuis le Wiktionnaire
                anglais -- même risque de confusion d'édition que pour le français, vérifié
                avant de choisir cette source)
fichier         kaikki-dictionary-espanol.jsonl.gz
téléchargé le   2026-08-27
taille          105 263 491 octets (~100 Mo)
sha256          d9617a90f488a4ae401f799ebaa9a4c317a12bac676d6087c84517f63b573706
licence         CC-BY-SA + GFDL (mêmes licences que le Wiktionnaire, même famille que
                Kartmaan/french-dictionary déjà acceptée pour le site français)
obtention       python scripts/download_kaikki_spanish.py (écrit aussi le .sha256)
```

Contenu vérifié par échantillonnage (854 082 lignes JSONL, `lang_code` = "es" sur 100 % des
lignes) :

```text
répartition par pos (extrait) : verb 647 802, noun 76 996, adj 50 989, participle 33 681,
  name 32 302 (noms propres, exclus à l'import), phrase 7 350 (locutions, exclues par le
  filtre espace), adv 2 035, intj 707, suffix 630 (exclu), abbrev 430, proverb 304, prefix 238
  (exclu), num 191, pron 147, unknown 68 (exclu), conj 62, character 57 (exclu), prep 57,
  contraction 15, article 14, symbol 4 (exclu), particle 1, syllable 1, infix 1 (exclu)
```

Sert de source pour `is_spanish` (statut « mot espagnol réel, pas nécessairement admis au
Scrabble ») — filtrage par `pos` (exclusion de `name`/`phrase`/`character`/`symbol`/`unknown`/
`suffix`/`prefix`/`infix`) puis même filtre normalize+longueur que les deux sources Scrabble.
Aucune définition, glose ni contenu éditorial n'est copié en base (même discipline que D-004 du
site français) — seule la forme du mot (`word`) est retenue.

## 4. Sources héritées du scaffold, non utilisées dans ce dépôt

Ce dépôt a été créé par copie du code du site français (`git archive`). Certains scripts/docs
hérités (`scripts/download_french_dictionary.*`, `scripts/download_hbenbel.py`,
`scripts/download_kaikki_french.py`, `data/ods9/`, `config/sites/fr.php`...) référencent des
sources FRANÇAISES qui ne sont PAS utilisées par ce dépôt espagnol et n'ont pas été
re-téléchargées ici. Ils restent dans l'arborescence pour mémoire/référence de pattern (ex.
`download_kaikki_spanish.py` est directement adapté de `download_kaikki_french.py`), mais
`data/raw/` de ce dépôt ne contient que les trois sources ci-dessus.
