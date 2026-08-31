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
réel du dépôt (`word_list_commencant`/`word_list_terminant`, 271 URL **à l'époque d'ES-016** --
voir la section "Familles Réellement Peuplées" plus bas pour l'état courant, bien plus large) --
documenté ici ET en détail (mesures, familles closes et pourquoi) dans `docs/DECISIONS.md` ES-016.

Resynchronisé une troisième fois le 2026-08-31 (blocage **C-3** du rapport `seo-technical-auditor`
+ blocage **C-5** du rapport `code-reviewer`, audits croisés NO GO du 2026-08-31 : ce document de
gouvernance de l'indexation décrivait un état largement périmé).

> **Collision d'étiquettes** : les codes de blocage/correctif `C-1`/`C-2`/`C-3` sont réutilisés
> d'un audit à l'autre. Dans ce fichier, tout `C-x` sans date renvoie à l'audit du **2026-08-31**
> (C-1 = grain `length_end` ; C-2 = vidage des `*DUPLICATE*` FR ; C-3 = `ExploreHubBuilder` /
> ce document). Les `C-x` de l'audit **2026-08-29** (ES-011) sont toujours cités « ES-011 C-x »
> ou datés explicitement -- ce sont des blocages différents.

Entre ES-016 et aujourd'hui, huit lots successifs ont ouvert des familles que ce fichier
décrivait encore comme vides ou pilotes :
ES-013/ES-015 (`word_admitted` complet, 14/14 longueurs), ES-018 (palier combiné longueur+axe),
ES-022 (`list_counts` complet + `terminan-en` 1 lettre), ES-023 (funnel `empiezan-por` 1+2+3 /
`terminan-en` 1+2+3+4), ES-024 (`word_spanish_not_admitted` complet), ES-025/ES-026 (paliers
`con-letras` 1 et 2 lettres). Toutes les sections ci-dessous ont été recomptées ligne à ligne le
2026-08-31 contre `storage/seo_es.sqlite` (772 629 lignes ; 772 507 `index,follow`, 122
`noindex,follow`) et `storage/dictionary_es.sqlite` (`list_counts` : 94 760 lignes, 20
`list_type`). Les correctifs de code appliqués le même jour par `data-engine` -- **C-1** (grain
`list_counts.length_end` ramené à 2 caractères, `reports/query-plans/es-c1-length-end-linking.md`),
**C-2** (vidage des 14 constantes `*DUPLICATE*` de `app/Search/*LinksBuilder.php`, héritées de
`storage/dictionary_fr.sqlite`), **C-3** (requête préparée + `LIMIT` dans
`App\Search\ExploreHubBuilder`, `reports/query-plans/es-c3-explore-hub-builder.md`) -- n'ont
produit **aucune nouvelle URL** ni **aucun changement de sitemap** : C-1 restaure un maillage
entrant réel (les 2 199 pages `word_list_combined` terminan-en + longueur étaient déjà
`index,follow` au sitemap), C-2 ne touche que du maillage vers des pages `noindex`, C-3 durcit une
requête runtime. Aucune régénération de registre ni de sitemap n'a été faite ni n'est requise.

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

NEUF familles ont des lignes `index,follow` réelles dans `storage/seo_es.sqlite` (recompté le
2026-08-31, `docs/DECISIONS.md` ES-013 à ES-026) -- toutes les autres constantes de `Family::ALL`
existent pour que le schéma soit prêt, mais restent VIDES tant qu'aucune décision de lot dédiée
ne les ouvre. Total : 772 507 `index,follow` + 122 `noindex,follow` = 772 629 lignes.

```text
home                         2 lignes -- '/' (index,follow, core-0001) + '/palabras' (hub,
                             noindex,follow, hors sitemap). La justification du noindex du hub
                             A CHANGÉ (voir section dédiée plus bas) : ce n'est plus "list_counts
                             vide", c'est une décision d'indexation du hub non prise.
word_admitted                661 221 / 661 221 -- COMPLÈTE, 14/14 longueurs (ES-013/ES-015).
                             Deux vagues : longueurs 7+9 (150 204, ES-011) puis les 12 autres
                             longueurs (511 017, ES-015). Fragments words-0001..words-0017 (17).
word_spanish_not_admitted    86 944 / 86 944 -- COMPLÈTE, lot UNIQUE (ES-024, lève le blocage
                             ES-010). Plafond dur Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED
                             relevé de 50 à 100 000 sur décision produit explicite (même
                             raisonnement que D-017 côté français : le site répond à deux
                             questions symétriques, exclure les formes non admises le rend
                             introuvable quand l'incertitude du visiteur est maximale).
                             Attestation ligne par ligne (notes non vide, R6/R7) restée
                             obligatoire ; seul le VOLUME maximal par lot a changé. Maillage
                             entrant réel préexistant : App\Search\TermLookup::neighbours()
                             (navigation mot précédent/suivant) parcourt déjà la chaîne
                             alphabétique complète, admises ET non admises confondues.
                             Fragments invalid-0001..invalid-0003 (3).
word_list_length             14 lignes (/palabras/{N}-letras, 2 à 15 lettres). Fragment
                             letters-0001.
word_list_commencant         2 871 index,follow (+ 12 noindex,follow, perdants de dédoublonnage
                             ES-023 vers le grain 2 lettres) : 25 à 1 lettre (ES-016 -- alphabet
                             A-Z+Ñ MOINS K et W, 0 mot admis ne commence par ces lettres, donc
                             0 lien réel), 396 à 2 lettres (ES-023), 2 450 à 3 lettres (ES-018,
                             84 doublons du grain 2 lettres exclus/corrigés). Grains 1+2+3.
                             Fragments starts-0001 (25) + starts-0002 (2 846).
word_list_terminant          14 192 index,follow : 23 à 1 lettre (ES-022 -- K/Q/W/Ñ exclus, 0
                             mot admis), 246 à 2 lettres (ES-016), 2 551 à 3 lettres (ES-023),
                             11 372 à 4 lettres (ES-023). Grains 1+2+3+4. Fragment ends-0001.
                             Le palier 1 lettre, fermé par ES-016 quand `list_counts` était
                             encore vide, a été ROUVERT par ES-022 sur un fait nouveau vérifié :
                             le hub /palabras émet désormais un lien réel et crawlable vers
                             chaque bucket terminan-en 1 lettre (App\Search\ExploreHubBuilder) --
                             source de maillage DISTINCTE de RelationsFinder, qui, lui, n'émet
                             jamais un "endsWith" de moins de 2 caractères (Normalizer::
                             MIN_LENGTH = 2 rend min(2, longueur) constant à 2).
word_list_combined           2 547 index,follow : 348 "longueur + empiezan-por" à 1 caractère +
                             2 199 "longueur + terminan-en" à 2 caractères (ES-018 + réinclusion
                             ES-023). N'ouvre PAS le troisième axe "empiezan-por + terminan-en
                             ensemble" (avec ou sans longueur). ATTENTION, la raison a changé :
                             `list_counts` 'start_end' (573 lignes) et 'length_start_end' (3 917
                             lignes) SONT désormais construits (ES-022) -- l'obstacle n'est plus
                             une donnée absente mais (a) une décision d'indexation non prise et
                             (b) un préalable technique : les listes de dédoublonnage héritées du
                             français (App\Search\LengthLinksBuilder::DUPLICATE_START_END_KEYS et
                             13 autres constantes *DUPLICATE* de app/Search/*LinksBuilder.php)
                             ont été VIDÉES le 2026-08-31 (correctif C-2 -- elles étaient
                             calculées sur storage/dictionary_fr.sqlite) et doivent être
                             recalculées pour l'espagnol AVANT toute ouverture d'une famille
                             combinée qui les consomme.
word_list_avec_single_letter 377 index,follow (ES-025) -- /palabras/{N}-letras/con-letras/{X},
                             longueur OBLIGATOIRE. Sous-famille BORNÉE, distincte de
                             Family::WORD_LIST_AVEC (générique, NEVER_SITEMAP). 0 doublon trouvé.
                             Fragment avec-single-0001.
word_list_avec_two_letters   4 340 index,follow (+ 109 noindex,follow -- doublons de contenu
                             exacts PARENT/SIBLING/EXTERNAL, canonical vers le gagnant réel) --
                             ES-026, /palabras/{N}-letras/con-letras/{X}/{Y}, deux lettres
                             distinctes triées alphabétiquement. Sous-famille BORNÉE. Fragment
                             avec-two-0001.
```

Familles réservées, mesurées ou outillées mais volontairement PAS ouvertes à ce stade (aucune
famille SEO ouverte dessus -- voir `docs/DECISIONS.md` ES-016/ES-018/ES-022 pour le détail),
jamais dans `Family::NEVER_SITEMAP` pour autant (bornées par construction) :
`word_list_position` (donnée `list_counts` 'length_with_position' construite -- 2 997 lignes --
mais aucune famille ouverte), l'axe `word_list_combined` "empiezan-por+terminan-en ensemble"
(avec ET sans longueur, distinct du palier longueur+UN SEUL axe ouvert ci-dessus),
`word_list_avec_three_letters` (constante déclarée par ES-025, 0 ligne).

Familles interdites de sitemap EN PERMANENCE (`Family::NEVER_SITEMAP`, combinaisons non bornées
en pratique) : `word_list_contenant` (contienen), `word_list_avec` (con-letras -- la réservation
GÉNÉRIQUE non bornée sur le nombre de lettres, DISTINCTE des sous-familles bornées
`word_list_avec_single_letter`/`word_list_avec_two_letters` ci-dessus, elles indexables),
`word_list_sans` (sin), `word_list_motif` (patron), `rack` (`/buscador-de-palabras/{lettres}`).
Confirmé par mesure (ES-016) : ces familles sans ancrage de longueur/préfixe/suffixe dégénèrent
en parcours quasi complet de la table dès qu'un motif est rare (ex. `contienen/qq` : 0 résultat,
~74 ms mesuré sur 748 165 lignes -- même signature que D-019 du dépôt français), et
`RelationsFinder::relatedSearches()` n'émet JAMAIS de lien vers elles (retiré dès la conception,
même raison que D-019).

## Le Hub `/palabras` Reste Noindex -- Justification Révisée (ES-011 C-1 → ES-022)

`'/palabras'` (famille `home`, `route_path` unique) reste `noindex,follow` et absent de tout
sitemap. **La justification a changé le 2026-08-31 (blocage C-3 `seo-technical-auditor`) :**

- État ES-011/C-1 (périmé) : `list_counts` était **vide** (`docs/DECISIONS.md` ES-001), donc
  `App\Search\ExploreHubBuilder::build()` alimentait 3 grilles ("Por Longitud"/"Empiezan Por"/
  "Terminan En") avec 0 entrée -- `app/View/explore-hub.php` n'ayant aucun garde d'état vide,
  la page rendait 3 `<h2>` suivis de `<div class="related-links">` vides. Le hub était retiré du
  sitemap et repassé `noindex,follow` en attendant un contenu de liste réel.
- État réel 2026-08-31 : `list_counts` est **complète** (94 760 lignes, 20 `list_type`, ES-022).
  Le hub rend désormais un contenu de liste réel et crawlable sur ses 3 grilles : 14 liens
  "Por Longitud", 27 "Empiezan Por" (26 lettres + Ñ), 27 "Terminan En" (grain 1 caractère,
  `end`). Le correctif C-3 a durci la requête d'alimentation (préparée, filtrée
  `WHERE list_type IN ('length','start','end')`, `LIMIT 100`) -- 490× plus rapide, `SCAN` →
  `SEARCH` sur clé primaire, sortie identique (`reports/query-plans/es-c3-explore-hub-builder.md`).

Le hub **reste malgré tout `noindex,follow`** : sa mise à l'index est une **décision explicite
non prise**, pas un défaut de contenu. Points à trancher avant toute proposition d'ouverture
(agent seo-registry, passe future) : maillage entrant du hub lui-même (aujourd'hui lié depuis
chaque en-tête via "Explorar Todas Las Palabras"), intégrité canonique face aux 14+27+27 pages
cibles déjà indexées qu'il duplique en partie, et volume/pacing. `/` (home) n'a jamais été
concernée : son contenu n'a jamais dépendu de `list_counts`.

## Maillage Interne Réel Vers `/palabras/{N}-letras` (ES-011, I-1)

Les 14 pages `/palabras/{N}-letras` sont indexables et publiées dans `letters-0001.xml` malgré
le `noindex` du hub ci-dessus : `App\Search\RelationsFinder::relatedSearches()` émet
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

## Maillage Interne Réel Vers `word_list_commencant`/`word_list_terminant` (ES-016 → ES-023)

Ces deux familles (funnel complet au 2026-08-31 : `empiezan-por` 1+2+3 lettres = 2 871
`index,follow`, `terminan-en` 1+2+3+4 lettres = 14 192 `index,follow`) s'appuient sur DEUX liens
inconditionnels de `RelationsFinder::relatedSearches()` PLUS, pour le grain 1 lettre de
`terminan-en`, le hub `/palabras` :

```text
startsWith  1 lettre TOUJOURS émis ; 3 lettres si length > 3 (ES-018). Le grain 2 lettres
            (ES-023) est adossé à list_counts 'prefix2' (App\Search\PrefixExtensionLinksBuilder)
            + au hub, désormais peuplés.
endsWith    RelationsFinder n'émet JAMAIS moins de 2 caractères (Normalizer::MIN_LENGTH = 2
            rend min(2, longueur) constant). Le grain 1 lettre de terminan-en (ES-022, 23
            buckets) est donc lié UNIQUEMENT par le hub /palabras (section "Terminan En",
            App\Search\ExploreHubBuilder, list_counts 'end', 27 liens réels et crawlables) --
            source de maillage DISTINCTE de RelationsFinder. C'est le fait nouveau qui a permis
            de rouvrir ce palier, fermé par ES-016 à une époque où list_counts était vide.
            Grains 2/3/4 lettres : endsWith (2 lettres) + list_counts 'suffix3'/'suffix4'
            (App\Search\SuffixExtensionLinksBuilder) + hub.
```

`RelationsFinder::find()` n'est appelé que pour un mot ADMIS ; `word_admitted` étant complète
(661 221 / 661 221, ES-015), le maillage entrant `startsWith`/`endsWith` est réel dès
aujourd'hui pour chaque page ouverte. Exclusions d'indexation à chaque grain, même discipline
partout : `empiezan-por` exclut K et W (0 mot admis) ; `terminan-en` 1 lettre exclut K/Q/W/Ñ
(0 mot admis). Les perdants de dédoublonnage inter-grains (84 côté `empiezan-por`, dont 12
`word_list_commencant` repassées `noindex,follow`/canonical ; 63+639+26 côté `terminan-en`,
exclus avant application) ne sont jamais deux fois `index,follow`.

`App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES` (~630 suffixes calculés sur
`storage/dictionary_fr.sqlite`) avait été copié tel quel au portage : **vidé par ES-023** avant
l'ouverture des grains 3/4 lettres. Même classe de mine que les 14 constantes `*DUPLICATE*`
vidées par le correctif C-2 (2026-08-31) -- voir section suivante.

## Maillage Interne Réel Vers `word_list_combined` (ES-018)

Débloqué par `docs/DECISIONS.md` ES-017 (`list_counts` 'length_start'/'length_end' peuplés) :
`App\Search\LengthLinksBuilder::build()` lit ces deux `list_type` et émet un lien HTML RÉEL
(`byStart`/`byEnd`) depuis CHAQUE page `/palabras/{N}-letras` DÉJÀ indexée (`word_list_length`,
ES-011 I-1) vers chaque combinaison longueur+lettre/longueur+suffixe non vide -- indépendant de
`RelationsFinder`, vérifié directement (`app/View/word-list.php`, section `$lengthLinks`,
`public/index.php` -- déjà câblé avant ES-018, seule la DÉCISION D'OUVRIR ces pages cibles était
manquante).

**Grains asymétriques `end` (1 car.) vs `length_end` (2 car.), correctif C-1 (2026-08-31).**
ES-022 avait ramené `end` ET `length_end` de 2 à 1 caractère d'un seul geste -- amalgame : la
justification produit (le hub `/palabras` est une source de lien distincte de `RelationsFinder`)
ne vaut que pour `end` (consommé par `ExploreHubBuilder`). `length_end` a pour UNIQUE
consommateur `LengthLinksBuilder::byEnd`, qui alimente le maillage entrant des 2 199 pages
`word_list_combined` "terminan-en + longueur", indexées à 2 caractères (ES-018 -- seule
granularité `terminant` réellement indexée sur ce dépôt, conséquence de `MIN_LENGTH = 2`). À 1
caractère, `byEnd` laissait ces 2 199 pages `index,follow` **sans aucun lien entrant**. C-1
restaure `length_end` à 2 caractères (`scripts/build_explore_hub_counts.php`, régénération de
production faite ; 0 / 2 199 pages orphelines après, était 2 199 / 2 199). `end` (hub) et
`length_start` (`empiezan-por` + longueur, 1 lettre) restent à 1 caractère. Détail et
`EXPLAIN QUERY PLAN` : `reports/query-plans/es-c1-length-end-linking.md`.

Trois exclusions mesurées avant application (docs/DECISIONS.md ES-018 pour le détail complet) :

```text
88 paires 'terminan-en'   doublon de contenu reel avec la variante SANS longueur (TOUS les mots
                          de ce suffixe partagent la même longueur) -- re-dérivé pour
                          storage/dictionary_es.sqlite, PAS la liste française. NB : le
                          troisième axe (empiezan-por + terminan-en, avec longueur) n'est PAS
                          ouvert, mais son list_type 'length_start_end' N'EST PLUS vide (3 917
                          lignes, ES-022) -- LengthLinksBuilder::DUPLICATE_START_END_KEYS, qui
                          filtrerait ce chemin, a été VIDÉE par le correctif C-2 (était la liste
                          française) et devra être recalculée pour l'espagnol AVANT toute
                          ouverture de cet axe
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

## Rollout Par Vagues (ES-011 correctif "rollout", ES-013/ES-015)

> Note de nommage : le "correctif C-2" cité par l'historique ES-011 (durcissement de
> `apply_word_admitted_rollout.php`) est SANS RAPPORT avec le correctif **C-2 du 2026-08-31**
> (vidage des constantes `*DUPLICATE*` héritées du français). Deux blocages différents, même
> étiquette dans deux audits différents.

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

Vagues appliquées (état 2026-08-31, `docs/DECISIONS.md` ES-011/ES-013/ES-015) :

```text
Vague 1 (pilote, ES-011)   longueurs 7 et 9            150 204 mots   batch_id
                           word_admitted-lengths-7-9-2026-08-28
Vague 2 (ES-015)           12 autres longueurs         511 017 mots   batch_id word_admitted-
                           (2,3,4,5,6,8,10..15)                       lengths-2-3-4-5-6-8-10-11-
                                                                     12-13-14-15-2026-08-29
```

`word_admitted` est donc **COMPLÈTE** : 661 221 / 661 221 lignes `index,follow`, 14/14
longueurs, décision de volume explicite du propriétaire du produit (ES-013 a vérifié les 12
longueurs restantes avant application ; ES-015 les a appliquées). Aucune longueur ne reste
`noindex` par omission. Les deux vagues ont chacune leur `batch_id` et leur plage de fragments
`words-*`, reproductibles.

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

Décompte réel des pages à `result_count = 1` (recompté le 2026-08-31 -- rapporté pour revue,
PAS marqué candidat `noindex`) :

```text
index,follow   4 801 pages     word_list_terminant 3 994 | word_list_combined 552 |
                               word_list_avec_two_letters 147 | word_list_commencant 108
toutes lignes  4 879 pages     (+ 78 lignes noindex,follow -- perdants de dédoublonnage
                               ES-023/ES-026, canonical vers un gagnant réel)
```

Ces 4 801 pages `index,follow` sont dans des familles autorisées, avec un maillage entrant réel
vérifié et un canonical autonome (`route_path === canonical_path`, R3) -- elles restent
indexables : la règle de rôle est explicite, « never decide indexation from result count alone ».
`result_count = 0` avec `index,follow` : **0 ligne** dans tout le registre (R5).

## Sitemaps

**28 fragments** à ce jour (recomptés le 2026-08-31 sur le disque `public/sitemaps/` ET dans
`storage/seo_es.sqlite` -- identiques). `public/sitemap-index.xml` les liste tous les 28 sous
`https://www.wordcheckr.es/sitemaps/`.

```text
sitemap-index.xml
core-*.xml        home -- '/' UNIQUEMENT ('/palabras' exclu, noindex)                  1 frag.
words-*.xml       word_admitted (/palabra/{mot})                                      17 frag.
invalid-*.xml     word_spanish_not_admitted (/palabra/{mot}, formes ES non admises)    3 frag. -- ES-024
letters-*.xml     word_list_length (/palabras/{N}-letras)                              1 frag.
starts-*.xml      word_list_commencant (/palabras/empiezan-por/{1|2|3 lettres})        2 frag. -- starts-0001 = 1 lettre (25), starts-0002 = 2+3 lettres (2 846)
ends-*.xml        word_list_terminant (/palabras/terminan-en/{1|2|3|4 lettres})        1 frag. -- ES-016/ES-022/ES-023, 14 192 URL
combined-*.xml    word_list_combined (/palabras/{N}-letras/empiezan-por/{1 car.} ou    1 frag. -- ES-018
                 .../terminan-en/{2 car.})
avec-single-*.xml word_list_avec_single_letter (/palabras/{N}-letras/con-letras/{X})   1 frag. -- ES-025
avec-two-*.xml    word_list_avec_two_letters (/palabras/{N}-letras/con-letras/{X}/{Y}) 1 frag. -- ES-026
```

`scripts/build_sitemaps.php::FAMILY_FRAGMENT_PREFIXES` est la liste fermée réelle
famille → préfixe (source de vérité) : `home→core`, `word_admitted→words`,
`word_spanish_not_admitted→invalid`, `word_list_length→letters`, `word_list_commencant→starts`,
`word_list_terminant→ends`, `word_list_combined→combined`,
`word_list_avec_single_letter→avec-single`, `word_list_avec_two_letters→avec-two`. Les préfixes
hérités de la doc française qui n'ont PAS d'équivalent ici (`contains-*`, `position-*`,
`avec-triple-*`, `invalid-french-*`/`invalid-spanish-*`...) ne sont pas générés. Toute famille
combinatoire future devra ajouter sa propre entrée au moment où elle sera réellement ouverte,
jamais avant.

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
ligne `noindex,follow` n'apparaît jamais dans un sitemap, qu'une ligne dont `canonical_path`
diffère de `route_path` n'y apparaît jamais, et qu'aucune URL n'est dupliquée entre fragments,
PUIS (si `storage/seo_es.sqlite`/`storage/dictionary_es.sqlite` sont présents) que chaque ligne
réelle `word_admitted` correspond à un mot réellement admis, que chaque `result_count` de
`word_list_length`/`word_list_commencant`/`word_list_terminant` (ES-016) et de
`word_list_combined` (ES-018, sur la même longueur) égale le compte réel du dictionnaire, et que
chaque URL publiée sur le disque correspond à une ligne `index,follow` du registre réel. Le
`result_count` des familles `word_spanish_not_admitted`/`word_list_avec_*` n'est pas encore
couvert par ce test (à instruire si un futur lot le justifie).

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

Ajouté par la resynchro du 2026-08-31 (comptes list_type périmés, hors périmètre seo-registry) :

```text
schema.sql:179 (fichier partagé, session principale) : commentaire "19/19 list_type" -- le
    CHECK juste en dessous en énumère 20 (length_avec_sans inclus), et list_counts en compte
    20 réellement. À corriger en "20/20".
app/Search/ExploreHubBuilder.php (docblock, data-engine) : "list_counts ... 92 755 lignes
    reelles depuis ES-022, 19 list_type" -- réel 94 760 lignes / 20 list_type après la
    régénération C-1. Sans effet fonctionnel (la requête ne lit que 3 list_type), mais chiffre
    à rafraîchir.
```
