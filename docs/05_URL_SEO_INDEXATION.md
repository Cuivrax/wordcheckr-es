# 05 — URL, SEO Et Indexation (Site Espagnol)

Ce document est SPÉCIFIQUE à ce dépôt (`wordcheckr.es`) depuis le 2026-08-29 (correctif C-3,
audit seo-technical-auditor NO GO). Avant cette date, ce fichier était une copie non modifiée du
document du site français, décrivant un schéma d'URL (`/mot/qi`, `/mots/7-lettres`,
`/mots/commencant/ch`...), des familles de sitemap (`invalid-french-*`, `avec-single-*`,
`combined-*`...) et un historique de décisions (D-017 à D-041) qui n'existent PAS ici et
contredisaient directement `app/Seo/Family.php` et `docs/DECISIONS.md` ES-004/ES-009/ES-011.

Resynchronisé le 2026-08-29 (`docs/DECISIONS.md` ES-016) : la version précédente de ce document
(écrite le même jour qu'ES-011/ES-014) était déjà périmée par rapport à ES-014 -- elle affirmait
encore que `contenant`/`avec`/`sans`/`motif`/`position`/`statut`/`tri` restaient français, alors
qu'ES-014 les avait déjà traduits (`contienen`/`con-letras`/`sin`/`patron`/`posicion`/`estado`/
`orden`) le même jour. Corrigé ci-dessous. ES-016 ouvre aussi le premier palier combinatoire
réel du dépôt (`word_list_commencant`/`word_list_terminant`, 271 URL) -- documenté dans ce
fichier ET en détail (mesures, familles closes et pourquoi) dans `docs/DECISIONS.md` ES-016.

## Registre Unique

Le registre SEO (`storage/seo_es.sqlite`, `app/Seo/Registry.php`) est l'unique source de vérité
pour :

```text
index ou noindex
canonical
sitemaps
maillage interne (rapporté, pas construit ici -- voir app/Search/RelationsFinder.php)
rollout
métadonnées
```

Une route absente du registre reste :

```text
noindex, follow
```

dans DEUX cas distincts, jamais une erreur (`App\Seo\Registry::resolve()`) : le fichier
`storage/seo_es.sqlite` n'existe pas encore, ou il existe mais `route_path` n'a aucune ligne
correspondante. Le rendu HTTP (`public/index.php`) est **indépendant** du registre : une fiche
mot ou une liste se rend toujours en 200 si le contenu existe, que la route soit indexable ou
non -- seule la balise `<meta name="robots">`/`<link rel="canonical">` change.

## Localisation D'URL (ES-004)

Segments espagnols, décision produit confirmée (`reports/es-serp-terminology-research.md`,
`docs/DECISIONS.md` ES-004) :

```text
/mot            -> /palabra
/mots           -> /palabras
/mots/commencant -> /palabras/empiezan-por
/mots/terminant  -> /palabras/terminan-en
/jouer           -> /buscador-de-palabras
/verifier        -> /verificar
```

`contenant`, `avec`, `sans`, `motif`, `position`, `statut`, `tri` ont été traduits par ES-014
(2026-08-29, même jour qu'ES-011) -- `estado`/`orden` sont des choix raisonnés (aucune source
concurrente trouvée), les cinq autres sont attestés par une source concurrente réelle (voir
`docs/DECISIONS.md` ES-014 pour le détail par terme) :

```text
contenant -> contienen        avec   -> con-letras     sans  -> sin
motif     -> patron           position -> posicion     statut -> estado    tri -> orden
```

Aucune compatibilité ascendante avec les anciens segments français : `/palabras/avec/e` est un
chemin INCONNU (404), jamais silencieusement accepté ni redirigé (`App\Search\
WordListFilters::fromPath()`). Valeurs d'énumération de `estado` (`admis`/`non-admis`) et
`orden` (`points`/`points-desc`) : **PAS** traduites par ES-014 (mandat portait sur les
mots-clés, pas les valeurs) -- incohérence assumée et signalée, sans effet SEO à ce jour (ces
raffinements restent `noindex,follow` en permanence, voir plus bas).

## Routes Principales

```text
/
/palabras
/palabra/qi
/palabra/poser
/buscador-de-palabras/aeinrst
/palabras/7-letras
/palabras/empiezan-por/ch
/palabras/7-letras/empiezan-por/ch
/palabras/terminan-en/cion
/palabras/contienen/che
/palabras/con-letras/a/a/r
/palabras/5-letras/patron/c--e-
```

## Ordre Canonique

```text
longueur
empiezan-por (commençant)
contienen (contenant)
terminan-en (terminant)
posicion (position)
con-letras (avec)
sin (sans)
patron (motif)
estado (statut)
orden (tri)
```

Toute autre permutation redirige en 301 (`App\Search\WordListFilters::fromPath()` ->
`canonicalPath()`, comparé par le routeur).

## Familles Réellement Peuplées (`app/Seo/Family.php`)

Six familles ont des lignes réelles dans `storage/seo_es.sqlite` à ce jour (`docs/DECISIONS.md`
ES-016/ES-018) -- toutes les autres constantes de `Family::ALL` existent pour que le schéma soit
prêt, mais sont VIDES tant qu'aucune décision de lot dédiée ne les ouvre :

```text
home                  '/' et '/palabras' (hub) -- '/palabras' repassée noindex,follow le
                      2026-08-29 (ES-011, C-1), voir plus bas
word_admitted         /palabra/{mot}, mots admis Lexicon FILE 2017/FISE-2 -- OUVERTE PAR VAGUE,
                      jamais en une seule fois (voir "Rollout Par Vagues" plus bas)
word_list_length      /palabras/{N}-letras, 14 lignes (2 à 15 lettres)
word_list_commencant  /palabras/empiezan-por/{lettre}, 25 + 2 462 lignes -- 25 a 1 lettre
                      (alphabet de 27 lettres A-Z+Ñ MOINS K et W -- 0 mot admis ne commence par
                      ces deux lettres, donc aucun lien réel, exclues plutôt que supposées sûres,
                      ES-016) + 2 462 a 3 lettres (palier 2, ES-018 -- même exclusion K/W
                      reverifiée, 0 doublon avec le grain 1 lettre).
word_list_terminant   /palabras/terminan-en/{2 lettres}, 246 lignes -- grain à 2 caractères, PAS
                      1 : App\Search\RelationsFinder::relatedSearches() emet TOUJOURS un lien
                      "endsWith" de 2 caractères exactement (Normalizer::MIN_LENGTH = 2 rend
                      min(2, longueur) constant à 2), jamais 1. Un grain à 1 lettre n'a donc
                      AUCUN lien réel actuellement et n'est pas dans le registre. ES-016.
word_list_combined    /palabras/{N}-letras/empiezan-por/{lettre} et
                      /palabras/{N}-letras/terminan-en/{2 lettres}, 2 327 lignes (348 + 1 979) --
                      palier "longueur+empiezan-por"/"longueur+terminan-en", débloqué par
                      list_counts 'length_start'/'length_end' (ES-017). N'ouvre PAS le troisième
                      axe (empiezan-por+terminan-en ensemble, avec ou sans longueur, toujours
                      vide -- list_counts 'start_end'/'length_start_end' non construits). ES-018.
```

Familles réservées, mesurées mais volontairement PAS ouvertes à ce stade (aucun maillage interne
réel aujourd'hui -- voir `docs/DECISIONS.md` ES-016/ES-018 pour le détail des mesures et la
raison technique précise de chacune), jamais dans `Family::NEVER_SITEMAP` pour autant (bornées
par construction) : `word_list_position`, et l'axe `word_list_combined` "empiezan-por+terminan-en
ensemble" (avec ET sans longueur, distinct du palier longueur+UN SEUL axe ouvert ci-dessus).

Familles interdites de sitemap EN PERMANENCE (`Family::NEVER_SITEMAP`, combinaisons non bornées
en pratique) : `word_list_contenant` (contienen), `word_list_avec` (con-letras),
`word_list_sans` (sin), `word_list_motif` (patron), `rack` (`/buscador-de-palabras/{lettres}`).
Confirmé par mesure (ES-016) : ces familles sans ancrage de longueur/préfixe/suffixe dégénèrent
en parcours quasi complet de la table dès qu'un motif est rare (ex. `contienen/qq` : 0 résultat,
~74 ms mesuré sur 748 165 lignes -- même signature que D-019 du dépôt français), et
`RelationsFinder::relatedSearches()` n'émet JAMAIS de lien vers elles (retiré dès la conception,
même raison que D-019).

`word_spanish_not_admitted` (86 944 mots, `is_spanish = 1 AND is_ods8 = 0 AND is_ods9 = 0`) :
plafond dur `Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED = 50` par lot, attestation manuelle
(note non vide) obligatoire sur chaque ligne -- "never propose indexing these in bulk". Aucune
ligne appliquée à ce jour (`docs/DECISIONS.md` ES-010/ES-011).

## Le Hub `/palabras` Reste Noindex (ES-011, C-1)

`App\Search\ExploreHubBuilder::build()` lit la table `list_counts`, **vide** sur ce dépôt
(`docs/DECISIONS.md` ES-001 -- décision produit explicite, hors périmètre de l'agent
seo-registry). `app/View/explore-hub.php` n'a aucun garde d'état vide (contrairement à
`app/View/word-list.php`, qui garde chaque bloc derrière `if ($refine['byLength'] !== [])`) :
les 3 sections de grille ("Por Longitud"/"Empiezan Por"/"Terminan En") rendent un `<h2>` suivi
d'un `<div class="related-links">` totalement vide. La page reste RENDUE (2 formulaires
fonctionnels réels : recherche "contenant", vérification de mot -- ce n'est PAS une page à
résultat vide au sens de la règle R5), mais son contenu de liste est aujourd'hui plus mince que
prévu -- retirée du sitemap et repassée `noindex,follow` jusqu'à ce qu'une correction propre
(garde d'état vide + contenu de repli réel, ou peuplement de `list_counts`) soit apportée par les
agents frontend/data-engine (hors périmètre de l'agent seo-registry). `/` (home) n'est PAS
concernée : son contenu n'a jamais dépendu de `list_counts`.

## Maillage Interne Réel Vers `/palabras/{N}-letras` (ES-011, I-1)

Les 14 pages `/palabras/{N}-letras` sont indexables et publiées dans `letters-0001.xml` malgré
le hub vide ci-dessus : `App\Search\RelationsFinder::relatedSearches()` émet
INCONDITIONNELLEMENT un lien `length` vers `/palabras/{N}-letras` depuis CHAQUE fiche
`/palabra/{mot}` de longueur N, et `public/index.php` rend cette fiche (HTTP 200) pour tout mot
trouvé par `App\Search\TermLookup::find()`, INDÉPENDAMMENT de l'état du registre SEO -- le
registre ne pilote que `robots`/`canonical`, jamais le rendu lui-même. Les 14 longueurs ont donc
toutes un lien entrant réel et suivable, même les longueurs dont `word_admitted` n'est pas encore
ouvert (le lien existe sur la page RENDUE, pas seulement sur une page indexée) :

```text
2 -> 149      6 -> 29210    10 -> 123379   14 -> 36786
3 -> 822      7 -> 56565    11 -> 113734   15 -> 19322
4 -> 3627     8 -> 87622    12 -> 89320
5 -> 12470    9 -> 112998   13 -> 62161
```

(`result_count` = compte réel `storage/dictionary_es.sqlite`, TOUTES statuts confondus --
`App\Search\WordListSolver` n'applique le filtre `is_admitted` que si l'URL porte un segment
`/statut/...`, absent ici.)

## Maillage Interne Réel Vers `word_list_commencant`/`word_list_terminant` (ES-016)

Contrairement à `word_list_length` (lien inconditionnel `length`) et à toutes les familles
combinatoires qui dépendent de `list_counts` (VIDE sur ce dépôt, ES-001 -- `App\Search\
LengthLinksBuilder`, `LetterCombinedLinksBuilder`, `PositionLinksBuilder`...), les deux familles
ouvertes par ES-016 s'appuient sur DEUX autres liens inconditionnels de
`RelationsFinder::relatedSearches()`, indépendants de `list_counts` :

```text
startsWith  1 lettre, TOUJOURS émis, + 3 lettres si length > 3 (non ouvert, voir ES-016)
endsWith    TOUJOURS 2 caractères exactement (Normalizer::MIN_LENGTH = 2 rend
            min(2, longueur) constant), jamais 1 caractère
```

Ces deux liens sont émis depuis CHAQUE fiche `/palabra/{mot}` ADMISE rendue (`RelationsFinder::
find()` n'est appelé que pour un mot admis) -- `word_admitted` étant complète (661 221/661 221,
ES-015), les 25 pages `empiezan-por` et 246 pages `terminan-en` ouvertes ont donc un maillage
entrant réel dès aujourd'hui, sans dépendre d'aucun artefact `list_counts` non construit.
K et W (`empiezan-por`) sont exclues : 0 mot admis ne commence par ces lettres, donc 0 lien réel
-- resteraient orphelines si ouvertes. Aucun grain `terminan-en` à 1 lettre n'existe dans le
registre pour la même raison symétrique (aucun lien réel à cette profondeur).

Le lien `startsWith` à 3 lettres (`length > 3`) est désormais OUVERT (ES-018, palier 2 de
`word_list_commencant`, 2 462 pages) -- même mécanisme inconditionnel, même exclusion K/W
reverifiée pour ce grain.

## Maillage Interne Réel Vers `word_list_combined` (ES-018)

Débloqué par `docs/DECISIONS.md` ES-017 (`list_counts` 'length_start'/'length_end' peuplés) :
`App\Search\LengthLinksBuilder::build()` lit ces deux `list_type` et émet un lien HTML RÉEL
(`byStart`/`byEnd`) depuis CHAQUE page `/palabras/{N}-letras` DÉJÀ indexée (`word_list_length`,
ES-011 I-1) vers chaque combinaison longueur+lettre/longueur+suffixe non vide -- indépendant de
`RelationsFinder`, vérifié directement (`app/View/word-list.php`, section `$lengthLinks`,
`public/index.php` -- déjà câblé avant ES-018, seule la DÉCISION D'OUVRIR ces pages cibles était
manquante).

Trois exclusions mesurées avant application (docs/DECISIONS.md ES-018 pour le détail complet) :

```text
88 paires 'terminan-en'   doublon de contenu reel avec la variante SANS longueur (TOUS les mots
                          de ce suffixe partagent la même longueur) -- re-dérivé pour
                          storage/dictionary_es.sqlite, PAS la liste française
                          (LengthLinksBuilder::DUPLICATE_START_END_KEYS, jamais lue par ce
                          chemin : list_type 'length_start_end' reste vide, ES-017)
37 paires 'terminan-en'   risque de TTFB (mode BORNE, ancrage 'reversed') : 158-245 ms mesurés en
                          direct entre 8 439 et 9 903 résultats, tout près du plafond dur "TTFB
                          chaud p95 sous 250 ms" -- seuil de sécurité fixé à 5 000 (distinct du
                          plafond de troncature ROW_EXAMINATION_CEILING=10 000)
183 paires 'terminan-en'  <title> > 60 caractères (ES-012) sur les pages à 1 résultat -- trouvé
                          en vérification HTTP réelle : app/View/word-list.php préfixe le
                          <title> d'une page à 1 résultat par le mot lui-même (audit D-031,
                          déjà en production), et le gabarit "De N Letras" allonge le total
                          au-delà de 60 caractères dès que le mot correspondant est long
                          (ex. "DESENSOBERBECED", 15 lettres). app/View/ hors périmètre de
                          l'agent seo-registry -- SIGNALÉ, pas corrigé ; ces pages restent
                          noindex,follow par défaut
27 paires 'empiezan-por'  K et W, même raison que le grain 1 lettre (0 mot admis à AUCUNE
                          longueur)
```

## Rollout Par Vagues (ES-011, correctif C-2)

`word_admitted` (661 221 mots admis au total) n'est **jamais** appliquée en une seule fois. Une
version antérieure de `scripts/apply_word_admitted_rollout.php` (phase-es-14) codait en dur
`robots = 'index,follow'` pour les 661 221 lignes en un seul appel, sans jamais repasser par les
règles R1-R7 (juste affirmées en commentaire) -- corrigé le 2026-08-29.

Le script réécrit :

```text
exige --lengths=N,N,... explicitement (aucune valeur par defaut n'ouvre "tout")
valide chaque ligne via scripts/seo_batch_rules.php (seoValidateBatchRow()) -- LE MEME CODE
    que scripts/apply_seo_batch.php, en flux (curseur PDO), jamais un tableau charge en memoire
assigne un batch_id et une plage de fragments sitemap par invocation (continuite entre vagues)
```

Vague appliquée à ce jour (pilote, `docs/DECISIONS.md` ES-011) : longueurs **7 et 9**
(150 204 mots, 23 % de la famille) -- mêmes longueurs que le palier `word_list_length` déjà lié
depuis `app/View/home.php`. Les 12 autres longueurs restent `noindex,follow` par défaut
(absentes du registre), en attente d'une décision explicite de volume/pacing (jamais une
décision d'agent seule) -- voir `docs/DECISIONS.md` ES-011 pour le plan de vagues proposé.

## Pages À Un Résultat

Une page avec un résultat n'est pas automatiquement faible.

Décision basée sur :

```text
famille autorisée
intention claire
canonical correct
maillage réel
réponse utile
```

Jamais sur le seul compteur (`scripts/apply_seo_batch.php` R5 : seul `result_count = 0` avec
`index,follow` est refusé -- `result_count = 1` est compté séparément dans le rapport, jamais
bloqué automatiquement).

## Sitemaps

```text
sitemap-index.xml
core-*.xml     home ('/' uniquement -- '/palabras' exclue depuis ES-011/C-1)
letters-*.xml  word_list_length (/palabras/{N}-letras)
starts-*.xml   word_list_commencant (/palabras/empiezan-por/{lettre}, 1 et 3 lettres) -- ES-016,
               ES-018 (starts-0002)
ends-*.xml     word_list_terminant (/palabras/terminan-en/{2 lettres}) -- ES-016
combined-*.xml word_list_combined (/palabras/{N}-letras/empiezan-por/{lettre} ou
               .../terminan-en/{2 lettres}) -- ES-018
words-*.xml    word_admitted (/palabra/{mot})
```

Les préfixes `contains-*`/`position-*`/`avec-*`/`invalid-french-*`/`invalid-spanish-*`...
(hérités de la doc française) ne sont **PAS** générés par ce dépôt --
`scripts/build_sitemaps.php::FAMILY_FRAGMENT_PREFIXES` est la liste fermée réelle (`core`,
`words`, `letters`, `starts`, `ends`, `combined` à ce jour). Toute famille combinatoire future
devra ajouter sa propre entrée au moment où elle sera réellement ouverte, jamais avant.

Limite interne :

```text
40 000 URL par fragment (verifiee en sortie par scripts/build_sitemaps.php, pas seulement
    supposee respectee par la donnee en entree)
```

Chaque URL du sitemap doit répondre :

```text
200
index
canonical autonome (route_path === canonical_path, verifie par apply_seo_batch.php R3 ET par
    scripts/build_sitemaps.php avant publication)
contenu non vide
aucune redirection
```

`<loc>` est pourcent-encodé PAR SEGMENT (`rawurlencode()`, RFC 3986) avant publication --
`route_path`/`canonical_path` sont stockés en UTF-8 décodé partout ailleurs (HTML servi en UTF-8),
mais un caractère non-ASCII brut (Ñ) dans `<loc>` n'est pas une URI RFC 3986 conforme même s'il
reste un XML valide (correctif I-7, `scripts/build_sitemaps.php::seoSitemapEncodePath()`).

`scripts/build_sitemaps.php` purge intégralement `public/sitemaps/*.xml` avant régénération
(correctif I-8) : le script reconstruit l'état complet depuis `storage/seo_es.sqlite` à chaque
exécution, un fragment d'une exécution précédente devenu obsolète (ex. une vague réduite) ne doit
jamais survivre sur le disque. Les fragments et `sitemap-index.xml` ne sont **pas versionnés**
(`.gitignore`, `/public/sitemaps/` et `/public/sitemap-index.xml`) -- régénérés au déploiement,
même régime que `storage/`.

`tests/Seo/RegistrySitemapConsistencyTest.php` vérifie mécaniquement (base temporaire) qu'une
ligne `noindex,follow` n'apparaît jamais dans un sitemap et qu'aucune URL n'est dupliquée entre
fragments, PUIS (si `storage/seo_es.sqlite`/`storage/dictionary_es.sqlite` sont présents) que
chaque ligne réelle `word_admitted` correspond à un mot réellement admis, que chaque
`result_count` de `word_list_length`/`word_list_commencant`/`word_list_terminant` (ES-016) égale
le compte réel du dictionnaire, et que chaque URL publiée sur le disque correspond à une ligne
`index,follow` du registre réel.

## Pagination

```text
/page/2
/page/3
```

Canonical autonome et vrais liens précédent/suivant. Chaîne de pagination en `rel="nofollow"`
au-delà des 3 premières pages pour une liste ANCRÉE (longueur/début/fin), et sur toute liste NON
ancrée dès la page 1 (`app/View/word-list.php`, `$paginationRelFor` -- `WordListSolver::
solveBounded()` parcourt l'index en intégralité pour une liste non ancrée, suivre la chaîne
rejouerait ce parcours à chaque page).

Les tris (`/tri/points`, `/tri/points-desc`) et le filtre `/statut/...` sont des raffinements
d'affichage, jamais indexables par défaut (absents du registre -- `noindex,follow`).

## Écarts Connus, Non Corrigés Par L'Agent seo-registry (Hors Périmètre)

Constatés lors du correctif ES-011 (2026-08-29), signalés pour routage vers les agents
frontend/data-engine -- `app/View/`, `app/Search/`, `scripts/build_*` (hors `build_sitemaps*`)
restent hors périmètre de l'agent seo-registry :

```text
meta description des pages /palabras/{N}-letras : affirme "admitidas en los diccionarios
    oficiales del Scrabble" alors que result_count compte TOUS les statuts (app/View/
    word-list.php, $statusMeta['direct'], cas "default")
<title> des fiches mot (app/View/word.php) : depasse 60 caracteres sur un mot long (ex. 74
    caracteres mesures pour un mot de 14 lettres)
<title> des pages A 1 RESULTAT de word_list_combined (app/View/word-list.php, prefixe le mot
    devant "Palabras De N Letras Con Final En XX") : depasse 60 caracteres des que le mot est
    long (ex. 69 caracteres pour "DESENSOBERBECED - Palabras De 15 Letras Con Final En Ed |
    WORD CHECKR") -- TROUVE en verification HTTP reelle (ES-018), 183 pages concernees, exclues
    du lot plutot que corrigees (meme classe de defaut que la ligne precedente, mais gabarit
    "De N Letras" different -- app/View/word-list.php, pas app/View/word.php)
title/description des pages de pagination (app/View/word-list.php) : identiques a la page 1,
    aucun suffixe "Pagina N"
bascules statut/tri (app/View/word-list.php, $statusToggles/$sortToggles) : aucun rel="nofollow"
```
