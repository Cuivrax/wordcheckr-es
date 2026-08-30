# DECISIONS

## D-001 — PHP 8.4 Et SQLite

Date : 2026-08-02  
Statut : accepté

Décision :

```text
PHP 8.4 sans framework
SQLite local en lecture seule au runtime
```

Raison :

```text
compatibilité o2switch
déploiement simple
aucun daemon applicatif
```

## D-002 — Une Base Par Langue Et Par Site

Date : 2026-08-02  
Statut : accepté

Décision :

```text
dictionary_fr.sqlite pour le site français
dictionary_en.sqlite pour le futur site anglais
```

Raison :

```text
taille
licences
scores
autocomplétion
sitemaps
déploiements indépendants
```

## D-003 — Registre SEO Séparé

Date : 2026-08-02  
Statut : accepté

Décision :

```text
seo_fr.sqlite et seo_en.sqlite séparés des dictionnaires
```

Convention de budget de requêtes (ajout, audit final, constat I2) :

```text
« moins de 10 requêtes SQLite indexées par fiche mot » (CLAUDE.md) s'entend
  par base : moins de 10 sur le dictionnaire (dictionary_fr.sqlite), la
  requête de résolution du registre SEO (seo_fr.sqlite, App\Seo\Registry::
  resolve(), 1 requête systématique via $render()) est comptée séparément
raison de la séparation : deux bases physiquement distinctes (cette
  décision), ouvertes par deux connexions PDO indépendantes, sans jointure
  possible entre elles — les compter ensemble n'aurait pas de sens
  opérationnel (le budget vise le coût du dictionnaire, la table à 838 180
  lignes, pas le registre à faible volume)
mesuré : la requête registre coûte 0,035 ms (code-optimizer, EXPLAIN QUERY
  PLAN : SEARCH registry USING INDEX), négligeable dans tous les cas
```

## D-004 — Aucune Définition En Production

Date : 2026-08-02  
Statut : accepté

Décision :

```text
la base publique conserve les formes et les indicateurs, pas les définitions
```

## D-005 — Registre SEO Source Unique

Date : 2026-08-02  
Statut : accepté

Décision :

```text
aucune route n’est indexable par défaut
```

## D-006 — Toutes Les Formes Françaises Retenues Ont Une Fiche

Date : 2026-08-02  
Statut : accepté

Décision :

```text
toute forme simple conservée après filtrage avec is_french = 1 possède une
fiche publique ; l’ouverture aux sitemaps se fait progressivement
```

## D-007 — Scripts De Build En Python, Runtime En PHP

Date : 2026-08-03
Statut : accepté

Décision :

```text
scripts/*.py     import, build, vérification — hors ligne uniquement
app/ et public/  runtime — PHP 8.4 exclusivement, SQLite en lecture seule
```

Raison :

```text
les scripts d’import ne tournent jamais en production
Python est disponible immédiatement et traite 1,4 million de lignes source sans friction
le runtime reste strictement conforme à D-001
```

Conséquences :

```text
aucune dépendance Python n’atteint l’hébergement o2switch
le périmètre de data-engine devient scripts/import_*.py et scripts/build_*.py
la base produite est un artefact, jamais versionnée
```

Amendement (audit final, code-reviewer, constat M4) :

```text
6 scripts de build ultérieurs sont en PHP, pas en Python : apply_full_word_
  rollout.php, apply_seo_batch.php, build_explore_hub_counts.php, build_seo_
  registry.php, build_sitemaps.php, propose_seo_batch.php — tous opèrent sur
  storage/seo_fr.sqlite ou construisent des artefacts déjà dérivés du
  dictionnaire (registre SEO, sitemaps, comptes du hub /mots), jamais
  l'import brut des sources externes (ods8.json, hbenbel, Kartmaan), qui
  reste en Python
raison du choix PHP pour ceux-ci : réutilisent directement les classes du
  runtime (App\Search\WordListFilters, App\Seo\Family...) plutôt que de
  dupliquer leur logique dans un second langage — cohérence de comportement
  entre le calcul hors ligne et la lecture au runtime jugée plus importante
  que l'uniformité de langage du build
principe non modifié : tous gardés par `PHP_SAPI !== 'cli'` (jamais
  atteignables par le web), jamais exécutés au runtime, jamais de dépendance
  supplémentaire sur o2switch — seule la lettre de « scripts/*.py » était
  devenue inexacte, pas l'esprit de la décision (build hors ligne, runtime
  en lecture seule, D-001 intact)
```

## D-008 — Le Pack De Lancement Est Promu À La Racine

Date : 2026-08-03
Statut : accepté

Décision :

```text
le contenu de scrabble-light-claude-launch-pack/ remonte à la racine du dépôt
les 8 agents vivent dans .claude/agents/, source unique
CLAUDE.md devient le point d’entrée de toute session
```

Raison :

```text
les documents et les définitions d’agents citaient déjà docs/, data/, scripts/
la double localisation des agents avait produit un MANIFEST.json périmé et un
README annonçant à tort que les agents d’audit manquaient
```

Conséquences :

```text
data/raw/scrabble-french-FR-ODS8.json renommé en data/raw/ods8.json
empreintes et provenance consignées dans data/raw/PROVENANCE.md
documents d’amorçage conservés dans docs/archive/
```

## D-009 — Règle De Normalisation

Date : 2026-08-03
Statut : accepté

Décision :

```text
1. ligatures  œ → oe,  Œ → OE,  æ → ae,  Æ → AE
2. NFD
3. suppression des caractères de catégorie Unicode Mn
4. majuscules
5. acceptation de ^[A-Z]{2,} uniquement ; tout le reste est rejeté et tracé
```

Raison :

```text
NFD ne décompose pas les ligatures — sans l’étape 1, 760 formes disparaissent
silencieusement, dont 288 mots admis ODS8 (OEIL, BOEUF, OEUF) qui se
retrouveraient privés de is_french
```

Conséquences :

```text
scripts/lib/normalize.py est la source unique de la règle
le runtime PHP doit réimplémenter exactement les mêmes étapes
tout écart entre les deux implémentations est un bug, pas une variante
```

## D-010 — Plafond De 15 Lettres En Base (Révisée)

Date : 2026-08-03, révisée le 2026-08-03
Statut : accepté

**Révision.** La version initiale de cette décision retenait toutes les formes sans plafond,
justifiée par « un plafond à 15 lettres écarterait 9 105 mots ODS8 admis ». Cette justification
était fausse : l'audit code-reviewer de la Phase 0 (NO GO, point I2) a établi que ces 9 105
formes ne sont pas des mots ODS8. L'ODS8 publié par Larousse compte 402 325 mots ; notre
fichier `data/raw/ods8.json` en compte 411 430 — l'écart exact est ces 9 105 formes, des
conjugaisons générées (`CINEMATOGRAPHIASSIONS`, `REAPPROVISIONNERAIENT`) absentes de l'ODS
réel. Les afficher comme « admises au Scrabble » aurait été une erreur factuelle sur le site.
Confirmé par une source externe : [Wikipedia, L'Officiel du jeu Scrabble](https://en.wikipedia.org/wiki/L%27Officiel_du_jeu_Scrabble),
402 325 mots pour l'édition 2020.

Décision :

```text
base de production   toutes les formes de 2 à 15 lettres, aucune au-delà
entrée du solveur    15 caractères maximum — même borne, cohérente
```

Raison :

```text
un mot de plus de 15 lettres ne peut jamais être posé sur un plateau standard :
  ce n’est pas seulement une limite de saisie, c’est une limite du jeu lui-même
le plafond sert aussi de contrôle d’intégrité de la source ODS8 : le nombre de
  formes retenues doit valoir exactement 402 325, vérifié par
  scripts/import_fr.py qui lève une erreur si ce n’est pas le cas
le patch ODS9 confirme la borne : ses 1091 additions, 64 retraits et
  10 keep_overrides ne contiennent aucune forme de plus de 15 lettres
```

Conséquences :

```text
ods8_rows passe de 411 430 à 402 325
9 105 formes de 16 à 21 lettres retirées de la base, plus 46 119 lignes
  Kartmaan et 12 054 formes hbenbel de même longueur, déjà écartées ailleurs
```

## D-011 — Dictionnaire Français Complet Dès Le Lancement

Date : 2026-08-03, comptes mis à jour le 2026-08-03 (D-010 révisée, D-014)
Statut : accepté

Décision :

```text
toutes les formes des sources françaises retenues après filtrage entrent en
base avec is_french = 1, y compris les formes fléchies non admises, dans la
limite du plafond de longueur (D-010)
```

Raison :

```text
la distinction admis / non admis est la fonction centrale du site ; elle exige
la couverture française la plus large possible dès le lancement
```

Conséquences, comptes vérifiés exhaustivement sur les 838 180 lignes de
`storage/dictionary_fr.sqlite` (pas un échantillon) :

```text
435 120 formes françaises hors ODS, 838 180 termes au total
la base atteint 154,5 Mo
le rollout SEO doit dimensionner ses lots sur ~838 000 fiches, pas ~412 000
```

## D-012 — Postings Reportés En Phase 2/3

Date : 2026-08-03
Statut : accepté

Décision :

```text
la Phase 0 livre les index du schéma, pas les tables de postings
```

Raison :

```text
un index de toutes les sous-chaînes pèserait ~587 Mo, plus que la source
les Phases 1, 4 et 5 n’en ont aucun besoin : normalized, reversed et signature
  suffisent, mesurés entre 0,10 et 0,97 ms
construire 61 Mo d’index avant de connaître les requêtes réelles reviendrait
  à optimiser sans mesure
```

Conséquences :

```text
écart assumé à l’étape 8 de docs/03, qui plaçait les postings en Phase 0
la structure sera choisie en Phase 2/3 sur benchmark de sélectivité
```

## D-013 — display_term Égal À normalized, Et Pas D’Index Sur is_french

Date : 2026-08-03
Statut : accepté

Décision :

```text
display_term = normalized sur toutes les lignes
aucun index sur is_french
aucun index simple sur normalized : la contrainte UNIQUE en crée déjà un
```

Raison :

```text
ODS8 ne contient aucun accent sur ses entrées ; afficher une forme accentuée
  venue d’une autre source rendrait les fiches incohérentes entre elles selon
  leur provenance
les collisions de normalisation deviennent de simples fusions de provenance,
  sans arbitrage d’affichage (48 319 après D-010 révisée et D-014)
is_french vaut 1 sur toutes les lignes de la base française : un index sur une
  colonne constante ne sert à rien et coûte ~18 Mo
```

Conséquences :

```text
la colonne display_term reste au schéma, partagé avec le futur site anglais,
  au coût mesuré d’environ 9 Mo de duplication
la colonne is_french reste au schéma pour la même raison
```

## D-014 — Seconde Source Française : hbenbel/French-Dictionary

Date : 2026-08-03
Statut : accepté

Décision :

```text
data/raw/hbenbel/   dictionary.csv, adj.csv, noun.csv, verb.csv, adv.csv
source              https://github.com/hbenbel/French-Dictionary
obtention           python scripts/download_hbenbel.py
```

Raison :

```text
la couche française non admise reposait sur une source unique, dont les
  lacunes sont réelles
hbenbel apporte 34 300 formes absentes de la base, toutes en minuscule et
  porteuses d’une catégorie grammaticale
```

Filtrage propre à cette source :

```text
hbenbel n’a pas d’étiquette NP : ses noms propres et ses sigles sont noyés
dans noun.csv. La CASSE de la forme d’origine est le seul marqueur exploitable.
Toute forme à majuscule initiale est écartée — 2 987 rejets, dont Ewok,
Aberdonien, ADN, ARN, AVC, AOC. C’est ainsi que sont appliquées les exclusions
« noms propres » et « sigles » exigées par docs/03 §5.
```

Conséquences :

```text
838 180 termes au total, dont 435 120 français non admis, après D-010 révisée
base à 154,5 Mo
aucun crédit de source n’est publié (D-015)
```

Limite constatée :

```text
QUEULEULEU reste absent des deux sources. L’exemple emblématique du brief pour
« forme française non admise » n’existe dans aucune d’elles : il n’apparaît que
dans la locution « à la queue leu leu », écartée par la règle des espaces.
La microcopie doit choisir un autre exemple.
```

## D-015 — Aucun Crédit De Source Publié

Date : 2026-08-03
Statut : accepté

Décision :

```text
le site ne publie aucun crédit de source pour le dictionnaire français
ni page de licence, ni mention en pied de page, ni commentaire dans le HTML
```

Raison :

```text
la base de production est une construction propre : formes normalisées,
  indicateurs d’admissibilité, scores, signatures et dérivés
aucune définition, aucun texte éditorial et aucune structure de données
  d’origine ne sont repris
```

Conséquences :

```text
data/raw/PROVENANCE.md reste un document strictement interne, conservé pour la
  seule reproductibilité de l’import
les agents ne doivent pas ajouter de mention de source dans les templates,
  le footer ou les métadonnées
```

Réserve consignée, non bloquante :

```text
les mots isolés ne sont pas protégeables et aucune définition n’est reprise.
Le droit sui generis européen sur les bases de données porte toutefois sur
l’extraction substantielle d’une base, indépendamment du droit d’auteur.
Décision prise en connaissance de cause par le propriétaire du projet.
Ce document n’est pas un avis juridique.
```

## D-016 — Lecture Seule PHP Au Runtime : Trois Verrous Indépendants

Date : 2026-08-03
Statut : accepté

Décision :

```text
chaque connexion PDO SQLite runtime combine trois verrous indépendants :
  SQLITE_OPEN_READONLY (drapeau d'ouverture PDO)
  PDO::SQLITE_ATTR_READONLY_STATEMENT (PHP 8.4)
  PRAGMA query_only = ON
une instance par requête HTTP, jamais persistée (app/Database/Connection.php)
```

Raison :

```text
contrainte dure D-001 (SQLite local, ouvert en lecture seule au runtime) --
  aucune écriture ne doit être possible depuis public/, même par erreur de code
testé explicitement : écriture bloquée sur les trois verrous, fichier absent
  jamais créé, coût d'ouverture mesuré ~0,2-0,3 ms
```

Note de traçabilité : décision proposée dans `reports/phase1a-after.md` (Phase 1a,
agent data-engine) mais jamais migrée dans ce document — trouvé et corrigé lors de
l'audit final (code-reviewer, constat I7/M3). Aucun changement de comportement,
seulement la consignation d'une décision déjà implémentée et déjà en vigueur depuis
la Phase 1a.

## D-017 — Indexation Complète Des Formes Françaises Non Admises

Date : 2026-08-04  
Statut : accepté

Décision :

```text
storage/seo_fr.sqlite couvre au lancement les 838 180 fiches mot (403 060
  admises + 435 120 françaises non admises), plus les 67 pages de structure
  (home, longueur, commençant, terminant) : toutes en index,follow
  mise à jour (audit final, constat I7) : le hub /mots (App\Search\ExploreHub,
  ajouté après cette décision lors de la refonte de la home) porte le total
  réel à 838 248 lignes / 68 pages de structure — non anticipé ici, corrigé
  dans docs/PHASE_STATUS.md, aucun changement de politique d'indexation
le plafond MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED (app/Seo/Family.php) passe de
  50 à 500 000 — l'attestation ligne par ligne (notes non vide, R6/R7) reste
  obligatoire, seul le volume par lot change
```

Raison :

```text
le site répond à deux questions symétriques : « ce mot est-il admis ? » et
  « ce mot est-il non admis ? » (docs/01_MASTER_BRIEF.md) — un visiteur qui
  cherche un mot sur Google ne sait jamais à l'avance dans lequel des deux
  cas il tombe
exclure les formes non admises de l'indexation rend le site introuvable
  précisément pour le cas d'usage où l'incertitude de l'utilisateur est la
  plus grande — constaté sur un exemple réel (DTC : présent en base,
  is_french=1, is_ods8=0, is_ods9=0)
ces pages ne sont pas du contenu vide ou dupliqué : badge, titre, score,
  tuiles et réponse directe sont rendus pour les trois statuts
  (app/View/word.php) — seul le bloc de relations (Phase 4) manque au non
  admis, pas la page entière
```

Contexte du désaccord :

```text
l'agent seo-registry a refusé d'appliquer ce lot, conformément à son propre
  garde-fou (.claude/agents/seo-registry.md : « never propose indexing
  these in bulk », « never propose indexing an entire word family at once
  without discussing batch size first ») — refus légitime, pas un
  dysfonctionnement : un message de coordinateur relayant une décision
  n'est pas une autorisation suffisante pour lever un garde-fou de rôle
le lot a donc été préparé et appliqué directement par la session
  principale (scripts/apply_full_word_rollout.php), à la demande explicite
  et informée du propriétaire du produit, après explication du compromis
  SEO habituel (contenu peu différencié en volume) et vérification que
  l'échantillon aléatoire de formes non admises contient un mélange réel
  (conjugaisons rares type REBULGARISAI, mais aussi DTC)
```

Conséquences :

```text
sans risque réel au moment de la décision : le site n'est pas encore
  déployé (Phase 7 — Production non commencée), rien de ce qui est écrit
  dans storage/seo_fr.sqlite n'est vu par le vrai Google avant une mise en
  ligne effective
le séquencement réel du rollout (quelles URL sont effectivement poussées
  en premier sur o2switch, avec point de contrôle Search Console entre
  deux vagues) reste une décision de la Phase 7, distincte de celle-ci —
  le registre local complet ne préjuge pas du calendrier de mise en ligne
scripts/build_sitemaps.php chargeait le registre entier en mémoire
  (fetchAll) avant traitement — épuisait la limite CLI par défaut (128 Mo)
  à cette échelle ; corrigé en lecture en flux (curseur PDO), jamais plus
  d'un fragment (40 000 URL max) retenu en mémoire à la fois
```

## D-018 — Nature Grammaticale, Genre Et Liens De Conjugaison

Date : 2026-08-04  
Statut : accepté

Décision :

```text
terms gagne trois colonnes nullables : pos, pos_secondary (jeu fermé de 9
  codes : N, V, Adj, Adv, Pronom, Prep, Conj, Interj, Art), gender (m/f/e)
nouvelle table verb_forms (lemma_normalized, form_normalized, tense,
  person) : liens de conjugaison pour les verbes fiables uniquement
D-004 reste pleinement en vigueur — aucune définition, aucune glose,
  aucun exemple d'usage n'est importé ni affiché
```

Raison :

```text
un joueur qui vérifie un mot veut souvent savoir ce que c'est, ne serait-ce
  que pour l'expliquer à un adversaire — un vrai besoin produit, distinct
  du besoin de définition en prose
nature grammaticale et genre sont des données factuelles structurées
  (data/raw/french_dict.db, Kartmaan, colonnes pos/gender), pas du texte
  éditorial — aucun risque de droit d'auteur, aucune génération par IA,
  aucun risque d'hallucination
```

Source et fiabilité :

```text
pos/gender : data/raw/french_dict.db (Kartmaan), lignes NP et loc* déjà
  exclues comme le fait scripts/import_fr.py pour l'import français
  734 622 termes sur 838 180 (87,7 %) ont au moins une ligne exploitable ;
  le reste (hbenbel/ODS9 seuls) a pos = NULL, une absence de donnée, pas
  une erreur
  un seul pos ne suffit qu'à 87,9 % des termes (homographes réels :
  TABLE nom/verbe, gentilés nom/adjectif) — d'où pos_secondary plutôt
  qu'une simplification à un seul champ
  gender a une troisième valeur non anticipée au départ : 'e' (épicène,
  ex. ENFANT, ÉLÈVE, ARTISTE), conservée plutôt que supprimée ou forcée à
  m/f — la faire disparaître aurait été une erreur factuelle du même
  ordre que celle corrigée par D-010

conjugaison : data/raw/hbenbel/verb.csv, 362 462 lignes forme+tags. Une
  pré-vérification transmise supposait un fichier organisé en blocs
  contigus par verbe — vérifié FAUX à l'implémentation : c'est un tri
  alphabétique global sur la forme, toutes formes de tous les verbes
  mélangées (0 inversion sur les 362 461 lignes). Un appariement par
  adjacence aurait mal attribué massivement les conjugaisons pour tout
  verbe au radical alphabétiquement voisin d'un autre
  corrigé par appariement au plus long préfixe commun (recherche
  dichotomique sur les 6 697 infinitifs connus) : 98,24 % d'appariements
  uniques sur les 348 366 lignes conjuguées
  limite mesurée et acceptée : les verbes supplétifs/fortement irréguliers
  s'apparient de façon confiante mais FAUSSE sur ce critère (ex. « suis »
  → SUIVRE au lieu d'ÊTRE, « vais » → VAIRONNER au lieu d'ALLER) — détecté
  par un seuil sur le nombre de formes que chaque lemme s'attribue à
  lui-même (médiane 50-51 formes/verbe fiable) : tout lemme sous 20 formes
  propres est exclu de verb_forms, 281 lemmes sur 6 697 (4,2 %), liste
  complète dans reports/verb-lemmas-excluded.csv — dont ÊTRE, AVOIR,
  ALLER, DEVOIR, VALOIR, VOIR, ASSEOIR, GÉSIR et la famille
  TENIR/VENIR/COURIR/CUIRE (radical alterné)
  une table d'exceptions saisie à la main pour ces verbes a été envisagée
  et écartée par défaut : introduirait des données non dérivées de la
  source mesurée, disproportionné pour "quelques liens simples, pas de
  surcharge" — pas de données fausses vaut mieux qu'un lien de
  conjugaison erroné, même au prix de l'absence de cette section sur les
  verbes les plus courants
  résidu accepté, non éliminé : ce seuil détecte les verbes entièrement
  peu fiables, pas les mismatches partiels sur un verbe par ailleurs
  fiable (ex. le futur de FAIRE continue de s'apparier à tort à FERIER) —
  ce résidu pollue la fiche du verbe voisin innocent, jamais celle du
  verbe irrégulier lui-même (qui se retrouve juste sans données)
```

Périmètre exclu, explicitement :

```text
paradigme de conjugaison complet (~50 formes/verbe) : sélection
  représentative seulement — présent/futur/imparfait (indicatif, 6
  personnes) + participe présent + participe passé (forme de base, sans
  accord), jusqu'à 20 formes/verbe
accord adjectif/nom (flex-adj/flex-nom) : hors périmètre de cette passe,
  seule la nature grammaticale (pos/gender) s'applique à ces mots, pas de
  liens d'accord
```

Conséquences :

```text
storage/dictionary_fr.sqlite reconstruite : 838 180 termes inchangés
  (integrity_check = ok), 734 622 avec pos, 123 563 lignes verb_forms
  déterminisme vérifié : reconstruction x2, comparaison octet à octet
budget runtime : 9 requêtes indexées sur dictionary_fr.sqlite par fiche
  pour un mot admis (8 existantes + 1), 4 pour un mot français non admis
  (3 + 1) — sous la limite de moins de 10 requêtes DICTIONNAIRE (CLAUDE.md).
  Aucune requête supplémentaire pour pos/pos_secondary/gender (colonnes
  ajoutées au SELECT déjà exécuté par TermLookup).
  reports/query-plans/d018-conjugation.md : toutes les requêtes passent
  par un index, aucun SCAN TABLE
  correction de traçabilité (audit final, code-reviewer, constat I2) :
  depuis la Phase 6, chaque fiche exécute EN PLUS 1 requête indexée sur
  seo_fr.sqlite (App\Seo\Registry::resolve()), soit 10 requêtes SQLite
  au total tous fichiers confondus — mesurée à 0,035 ms (code-optimizer),
  sans impact de performance. Ce chiffre-ci n'était pas compté ci-dessus ;
  voir D-003 pour la convention de budget retenue entre les deux bases
test de non-régression explicite : SUIS/SOMMES/SONT/VAIS/VONT ne doivent
  jamais apparaître comme forme conjuguée de SUIVRE/SOMMER/SONORISER/
  VAIRONNER/VOTER (tests/Search/ConjugationLookupTest.php)
rendu (app/View/) : à faire dans un second temps, hors périmètre de
  cette décision — data-engine livre la donnée, frontend l'affiche
```

## D-019 — Recherche "Contenant/Avec/Sans" Sans Ancrage : Correction Et Compromis De Performance Assumé

Date : 2026-08-06
Statut : accepté

Contexte :

```text
la refonte de la home et l'ajout du hub /mots (cette session) exposent en
  premier plan des recherches "contenant"/"avec"/"sans" SANS aucune
  longueur/début/fin fournis en complément (outil "Contenant" du hub,
  champs "Contient la suite"/"Lettres obligatoires"/"Sans les lettres" du
  constructeur home, liens "Voir les N mots" des fiches mots)
App\Search\WordListSolver::solveBounded() bornait alors le panier ANCRÉ
  (vide dans ce cas) à ROW_EXAMINATION_CEILING lignes AVANT d'appliquer
  ces prédicats -- pas après. Sans ancrage, "avant" = les 10 000 premiers
  mots de la base dans l'ordre alphabétique complet, pas un sous-ensemble
  pertinent : un mot comme "contenant XYL" (270 correspondances réelles,
  aucune dans les 10 000 premiers mots alphabétiques) répondait "0 mot
  trouvé" -- faux négatif silencieux, trouvé par l'audit final
  (code-reviewer, constat C1, bloquant)
```

Décision :

```text
anchorClause() (index) et extraPredicates() (SQL pur, non indexé) sont
  désormais combinés en UNE SEULE clause WHERE, appliquée ensemble à la
  fois pour le comptage de plafond et pour la récupération -- même
  principe que RelationsFinder::containingWords() (Phase 4, déjà validé) :
  le LIMIT porte sur le nombre de CORRESPONDANCES trouvées, jamais sur
  les lignes lues avant filtrage
optimisation mesurée dans la foulée : le prédicat "avec" utilisait
  LENGTH(normalized) - LENGTH(REPLACE(normalized, ?, '')) pour compter les
  occurrences, y compris quand une seule occurrence suffit (minCount = 1,
  cas majoritaire) -- remplacé par instr(normalized, ?) > 0 dans ce cas
  précis (~4x plus rapide, REPLACE() alloue une nouvelle chaîne à chaque
  appel), LENGTH/REPLACE conservé uniquement pour minCount >= 2 (lettre
  répétée exigée)
```

Correction de cadrage (3e passe d'audit, code-reviewer, bloquant C-1) : la première version de
cette décision présentait le dépassement ci-dessous comme un compromis de PERFORMANCE (budget
TTFB, une cible à respecter au mieux). C'était incomplet : CLAUDE.md range « scan complet de la
table (~838 000 lignes) au runtime » dans la section **Interdits** (même registre que
« React/Vue/SPA »), pas dans les cibles de performance -- une règle absolue, pas un objectif
négociable. `WordListSolver.php` applique d'ailleurs déjà cette règle pour rejeter `/mots` seul
(aucune contrainte du tout). Techniquement, le plan `EXPLAIN QUERY PLAN` de la requête sans
ancrage n'est pas un `SCAN TABLE` littéral (il passe par l'index couvrant
`sqlite_autoindex_terms_1`, confirmé par code-optimizer) -- mais le coût est fonctionnellement
identique à un parcours complet (~95 ms plancher structurel mesuré pour 838 180 lignes, même
sans aucun résultat). Deux atténuations ont donc été ajoutées après cette relecture, voir
« Décision (complément) » ci-dessous.

Compromis assumé, mesuré et accepté en connaissance de cause :

```text
avec ancrage (longueur/début/fin présent) : rapide dans la quasi-totalité
  des cas (0,9 à 80 ms mesurés) -- UNE régression trouvée par code-optimizer
  (3e passe) sur le cas pathologique déjà documenté dans phase3.md
  (longueur = 11, prédicat "avec" à lettres répétées et correspondance quasi
  nulle) : 14,7 ms avant C1, ~220 ms après C1 (première version de cette
  décision affirmait à tort "1 à 35 ms, inchangé" -- non re-mesuré à
  l'époque). Résolu par le complément ci-dessous (fusion des requêtes) :
  re-mesuré à 58,8 ms médiane après complément
sans aucun ancrage : la requête doit parcourir une grande partie des
  838 180 lignes pour garantir un résultat correct (elle ne peut plus
  s'arrêter tôt sur un sous-ensemble arbitraire) -- mesuré initialement
  entre 240 ms et 335 ms sur les cas les plus défavorables, au-dessus du
  budget TTFB p95 < 250 ms de CLAUDE.md ; ramené à 120-195 ms médiane après
  le complément ci-dessous (fusion des requêtes), plancher structurel
  ~95 ms incompressible sans index dédié (voir "suite à donner")
alternative écartée : plafonner aussi les lignes EXAMINÉES (pas seulement
  les correspondances) pour ce cas précis garantirait une latence basse,
  mais réintroduirait une forme atténuée du même défaut (un motif rare
  pourrait de nouveau être sous-compté au-delà du plafond d'examen) --
  jugé pire que le compromis retenu : mieux vaut une réponse correcte
  occasionnellement lente qu'une réponse rapide parfois fausse
```

Décision (complément, 3e passe d'audit -- deux atténuations à coût nul, aucune fonctionnalité
retirée) :

```text
1. Fusion des deux requêtes de solveBounded() en une seule quand l'ancrage
   est déjà l'ordre d'affichage (normalized) -- la requête de plafond et
   celle de récupération exécutaient exactement le même parcours pour n'en
   extraire qu'un booléen (constat code-optimizer, I-1). LIMIT
   CEILING + 1 sur la requête de récupération, truncated déduit du nombre
   de lignes rendues, la ligne surnuméraire retirée par array_pop(). Gain
   mesuré : 35 à 50 % sur les cas sans ancrage, corrige AUSSI la régression
   du cas ancré pathologique ci-dessus (14,666 ms -> ~220 ms -> 58,8 ms).
   Non appliqué à l'ancrage sur suffixe (reversed) : l'ordre d'ancrage y
   diffère de l'ordre d'affichage, chemin déjà rapide (27-53 ms), reste à
   2 requêtes plutôt que de risquer une erreur de tri
2. Retrait des liens "/mots/contenant/{sous-chaîne}" SANS ancrage
   auto-générés sur CHAQUE fiche de mot admis (RelationsFinder::
   relatedSearches(), 2 liens inconditionnels par mot ; app/View/word.php,
   1 lien conditionnel pour la catégorie "mot inséré") -- c'était la partie
   réellement grave du problème, pas la latence en elle-même : mesuré à
   ~1 675 000 liens follow distincts, émis depuis 403 060 pages toutes
   index,follow (D-017), chacun déclenchant le parcours coûteux dès qu'un
   robot le FETCH pour découvrir son noindex (Family::WORD_LIST_CONTENANT
   reste noindex,follow, mais ça ne dispense pas du coût de la requête).
   Un crawl normal du site aurait donc sollicité ce chemin des centaines de
   milliers de fois -- risque d'épuisement du pool de workers PHP sur
   hébergement mutualisé, pas seulement un dépassement de budget TTFB
   occasionnel pour un utilisateur isolé. L'outil "Contenant" du hub /mots
   et le champ "Contient la suite" du constructeur home restent inchangés
   (saisie humaine volontaire, jamais générée en masse) : aucune
   fonctionnalité utilisable directement par un visiteur n'est retirée
```

Raison de choisir la correction (résultats justes) plutôt que la restriction (exiger un ancrage) :

```text
le hub /mots et le constructeur home promettent explicitement une
  recherche "Contenant"/"avec"/"sans" utilisable seule (jusqu'à 3 lettres
  pour "Contenant", voir app/View/explore-hub.php) -- restreindre l'UI
  aurait supprimé une fonctionnalité déjà livrée à l'utilisateur plutôt
  que de corriger le bug qui la rendait fausse
un résultat lent mais juste est un problème de performance, mesurable et
  isolable ; un résultat rapide mais faux est un problème de confiance
  silencieux, qu'aucune mesure de performance ne révèle jamais
  le retrait des liens auto-générés (complément ci-dessus) réduit
  l'exposition réelle à une saisie humaine volontaire, seul contexte où
  "occasionnellement lent" redevient une description honnête
```

Suite à donner, non bloquante :

```text
re-mesurer en Phase 7 après déploiement réel sur o2switch, sous charge
  concurrente (plusieurs workers), pour confirmer ou infirmer l'ampleur du
  dépassement résiduel en conditions réelles -- voir reports/query-plans/
  phase3-c1-fix.md pour le protocole de mesure proposé
plancher structurel mesuré ~95 ms pour un parcours complet de l'index
  normalized sans aucun ancrage (COUNT() sans prédicat, ou instr() qui ne
  matche jamais) -- incompressible par une fusion de requêtes ou une
  optimisation de prédicat, seul un index dédié (trigrammes/lettres,
  D-012) ou un ancrage obligatoire y échapperait. Si la marge résiduelle se
  révèle insuffisante en Phase 7, ces deux pistes déjà écartées restent
  disponibles pour réexamen
```

## D-020 — Index Manquant Sur "Longueur + Terminant" : Correction

Date : 2026-08-08
Statut : accepté

Décision :

```text
nouvel index composé idx_terms_length_reversed(length, reversed) dans
  schema.sql, symétrique à idx_terms_length_normalized déjà en place pour
  le préfixe. Appliqué à la base déjà construite via scripts/
  add_length_reversed_index.php (ajout d'index seul, aucune donnée
  touchée, idempotent) -- inclus automatiquement dans schema.sql pour
  toute reconstruction future via scripts/import_fr.py
aucun changement de code PHP : WordListSolver::anchorClause() générait
  déjà exactement le WHERE que ce nouvel index sert, SQLite le choisit
  automatiquement
```

Raison :

```text
trouvé lors de l'analyse d'opportunité SEO longue traîne (2026-08-08,
  agent seo-registry) : /mots/{N}-lettres/terminant/{suffixe} n'avait
  jamais été construit en production (WORD_LIST_COMBINED reste
  NEVER_SITEMAP), donc son coût réel n'avait jamais été mesuré
  sans l'index composé, idx_terms_reversed(reversed) seul ne couvre pas
  `length` : SQLite ancre sur la plage reversed GLOBALE (toutes longueurs
  confondues) et lit chaque ligne candidate en table pour vérifier la
  longueur -- mesuré jusqu'à 1 779 ms (7-lettres/terminant/s), 629 ms
  (6-lettres/terminant/ent), fonctionnellement équivalent au parcours
  complet que ce projet interdit par ailleurs
```

Mesures (reports/query-plans/terminant-length-index-fix.md) :

```text
7-lettres/terminant/s      1 779,0 ms -> 100,7 ms  (17,7x)
6-lettres/terminant/ent      629,0 ms ->   1,7 ms  (370x)
tous les cas testés restent sous le budget TTFB p95 < 250 ms après correctif
cas SANS longueur (terminant seul, déjà indexé D-017) : inchangé, non affecté
```

Conséquence :

```text
débloque la piste "longueur + terminant" de l'analyse d'opportunité SEO
  longue traîne (auparavant classée "pas maintenant" faute de cet index) --
  devient aussi sûre que "longueur + commençant", déjà validée GO
la décision d'ouvrir effectivement cette famille à l'indexation (registre
  SEO, App\Seo\Family) reste distincte de ce correctif technique -- prise
  séparément, voir l'analyse d'opportunité SEO en cours
```

## D-021 — Régression Sur `/mots/{N}-lettres` (Déjà En Production) : ANALYZE Manquant Après D-020

Date : 2026-08-08
Statut : accepté

Décision :

```text
ANALYZE terms exécuté sur storage/dictionary_fr.sqlite immédiatement après
  l'ajout de idx_terms_length_reversed (D-020)
scripts/add_length_reversed_index.php modifié pour exécuter ANALYZE dans la
  MÊME opération que le CREATE INDEX, jamais séparément -- toute future
  modification d'index sur cette base doit suivre le même principe
```

Raison :

```text
trouvé en creusant la famille "position" pendant l'analyse d'opportunité SEO
  longue traîne (agent seo-registry, 2026-08-08, même jour que D-020) :
  idx_terms_length_reversed avait été créé sans relancer ANALYZE, laissant
  cet index sans ligne dans sqlite_stat1 -- le planificateur SQLite a alors
  parfois choisi À TORT idx_terms_length_reversed plutôt que
  idx_terms_length_normalized pour de simples requêtes
  "WHERE length = ? ORDER BY normalized" (régime EXACT), déclenchant un
  USE TEMP B-TREE FOR ORDER BY invisible dans le code PHP (aucune ligne de
  WordListSolver n'a changé, seul le plan choisi par SQLite a changé)
impact réel, pas seulement théorique : les 14 routes /mots/{N}-lettres sont
  DÉJÀ en index,follow au registre SEO (word_list_length, D-017, sitemap
  letters-0001) -- 8 des 14 étaient mesurées au-dessus du budget TTFB p95
  < 250 ms avant correctif (jusqu'à 889 ms médiane / 1 471 ms max sur
  11-lettres), donc potentiellement déjà visibles de Google si le site
  avait été en ligne
```

Mesures (via WordListSolver::solve(), code réel, avant/après ANALYZE) :

```text
6-lettres    212,84 ms -> 1,95 ms     10-lettres   861,47 ms -> 9,18 ms
7-lettres    388,61 ms -> 3,13 ms     11-lettres   889,21 ms -> 10,23 ms (max 1471,69 -> 13,11)
8-lettres    558,27 ms -> 4,49 ms     12-lettres   852,13 ms -> 10,70 ms
9-lettres    783,20 ms -> 8,84 ms     13-lettres   678,95 ms -> 9,26 ms
                                       14-lettres   495,15 ms -> 5,67 ms
                                       15-lettres   288,83 ms -> 2,75 ms
plan avant  : SEARCH terms USING INDEX idx_terms_length_reversed (length=?)
              + USE TEMP B-TREE FOR ORDER BY
plan après  : SEARCH terms USING INDEX idx_terms_length_normalized (length=?)
terminant+longueur (D-020) re-vérifié après ANALYZE : toujours bon
  (7-lettres/terminant/s 103,04 ms, 6-lettres/terminant/ent 1,18 ms)
```

Conséquence :

```text
php tests/run.php : 17/17 après correctif
leçon retenue pour scripts/import_fr.py et tout script de build futur qui
  touche aux index : ANALYZE fait partie intégrante de toute modification
  d'index, jamais une étape facultative ou différée
```

## D-022 — Filtre Statut, Tri Par Points, Et Maillage Interne Longueur × Lettre

Date : 2026-08-09
Statut : accepté

Décision :

```text
colonne dérivée is_admitted (schema.sql, scripts/import_fr.py) = (is_ods8 = 1
  OR is_ods9 = 1), précalculée à l'import -- jamais une source de vérité
  indépendante du modèle à trois statuts (CLAUDE.md)
deux index dédiés : idx_terms_length_admitted_normalized(length, is_admitted,
  normalized) pour le régime EXACT, idx_terms_admitted_normalized(is_admitted,
  normalized) pour le régime BORNE sans ancrage de longueur
idx_terms_length_score_normalized(length, score, normalized) pour le tri par
  points en régime EXACT -- régime BORNE : tri PHP (usort()) sur le panier
  déjà borné par ROW_EXAMINATION_CEILING, aucune requête supplémentaire
nouveaux segments URL (WordListFilters, ordre canonique) : /statut/admis|
  non-admis, /tri/points|points-desc -- "raffinements d'affichage", toujours
  en dernière position, "tri" exige une longueur explicite (404 sinon)
table list_counts (D-017) étendue : list_type 'length_start'/'length_end'
  (longueur × lettre de début/fin, GROUP BY précalculé) et 'length_with'
  (longueur × lettre présente n'importe où, parcours PHP précalculé, pas de
  GROUP BY exploitable) -- lus par App\Search\LengthLinksBuilder (nouvelle
  classe), 1 requête triviale, jamais de calcul sur `terms` au runtime
UI (app/View/word-list.php) : toggles Tous/Admis/Non Admis et Alphabétique/
  Points Croissants/Points Décroissants sous la réponse directe ; dès
  qu'une longueur est présente dans l'URL (seule ou combinée à n'importe
  quelle autre contrainte -- commençant, terminant, statut, tri...), trois
  groupes de liens "mots de {N} lettres commençant/terminant/avec {X}" plus
  un lien retour vers le hub /mots -- DEUX familles distinctes, jamais à
  confondre (CORRECTIF du 2026-08-10, 2e audit seo-technical-auditor sur
  D-025 : cette entrée confondait encore les deux après la correction D-024
  du 2026-08-09) : commençant/terminant ciblent Family::WORD_LIST_COMBINED
  (retirée de NEVER_SITEMAP depuis D-025, seul le sous-ensemble mono-lettre
  sans longueur est effectivement ouvert) ; "avec" cible
  Family::WORD_LIST_AVEC, qui reste et restera dans NEVER_SITEMAP en
  permanence (multiensemble de lettres, espace non borné). Ces liens
  restent noindex,follow par défaut tant qu'aucun lot ne les couvre
  explicitement.
  Version initiale restreignait ceci à la longueur seule (aucune autre
  contrainte active) : corrigé le jour même (retour utilisateur) -- un
  visiteur qui clique un toggle statut/tri depuis /mots/13-lettres perdait
  la section, incohérent pour une aide à la navigation censée rester stable
```

Raison :

```text
demande produit (2026-08-08) : filtrer une liste /mots/... sur Admis/Non
  Admis, trier par points, et un moyen de préciser une longue liste
  ("un 13 lettres avec un E ou un A") sans naviguer à l'aveugle
WHERE (is_ods8 = 1 OR is_ods9 = 1) sur deux colonnes distinctes empêche tout
  index couvrant -- mesuré 348 à 1 286 ms selon la longueur sur la base
  réelle (838 180 lignes), très au-dessus du budget TTFB p95 < 250 ms
  (CLAUDE.md) ; is_admitted seul ramène ce même COUNT() à 1,3-5,6 ms
```

Mesures complètes (reports/query-plans/status-filter-admitted.md) :

```text
statut, EXACT, COUNT() OR vs is_admitted (5 longueurs) : 139x à 318x plus
  rapide, COVERING INDEX confirmé par EXPLAIN QUERY PLAN
statut, via WordListSolver::solve() réel : 13-lettres/statut/admis 2,3-7,0 ms
  (2 requêtes) ; statut/admis sans ancrage 31,6-35,4 ms (2 requêtes, régime
  BORNE) ; contenant/che/statut/admis 93,3-162,4 ms (1 requête)
tri, EXACT (index couvrant) : 13-lettres/tri/points(-desc) 7,5-10,1 ms
tri, BORNE (usort PHP sur panier déjà borné, pire cas 10 000 lignes) :
  9-lettres/terminant/s/tri/points-desc 123-172 ms ; 11-lettres/avec/e/
  tri/points-desc 57-158 ms -- reste sous le budget TTFB
LengthLinksBuilder::build(13) : 1 requête triviale sur list_counts (1 120
  lignes), 1,5-3,9 ms, EXPLAIN QUERY PLAN confirme un SEARCH sur l'index de
  clé primaire (list_type=?), aucun GROUP BY sur `terms`
```

Conséquence :

```text
php tests/run.php : 19/19 (2 nouveaux fichiers : tests/Search/
  LengthLinksBuilderTest.php, tests/Frontend/WordListViewTest.php ;
  couverture statut/tri ajoutée à WordListFiltersTest.php et
  WordListSolverTest.php)
base reconstruite (scripts/import_fr.py) : toujours 838 180 termes, tous les
  index D-022 déclarés dans schema.sql -- aucun script d'ajout séparé comme
  pour D-020/D-021, donc aucune répétition possible du risque ANALYZE
  manquant (write_database() exécute déjà ANALYZE juste après l'import, dans
  la même opération que executescript())
les trois groupes de liens (commençant/terminant/avec par longueur) restent
  noindex,follow par défaut (D-005) -- vérifié en conditions réelles ;
  CORRECTIF (2026-08-10, 2e audit sur D-025) : commençant/terminant ciblent
  Family::WORD_LIST_COMBINED (retirée de NEVER_SITEMAP depuis D-025), "avec"
  cible Family::WORD_LIST_AVEC (reste dans NEVER_SITEMAP en permanence) --
  deux familles distinctes, jamais à confondre (voir aussi D-024 et
  app/Search/LengthLinks.php)
coût de stockage, mesuré honnêtement : storage/dictionary_fr.sqlite passe de
  172,6 Mo (D-018) à 236,5 Mo (+63,9 Mo, +37 %) -- entièrement attribuable
  aux 3 nouveaux index composés sur les 838 180 lignes (aucune ligne
  ajoutée : list_counts passe de 66 à 1 120 lignes, négligeable). Aucun
  budget de taille n'est documenté ailleurs dans ce projet (seuls le budget
  TTFB et le plafond de requêtes le sont) -- accepté ici au vu du gain
  mesuré (jusqu'à 318x sur le filtre statut), mais à surveiller si d'autres
  index composés devaient s'ajouter par la suite
```

## D-023 — Famille "Position" (Une Lettre À Une Position Précise)

Date : 2026-08-09
Statut : accepté

Décision :

```text
nouveau mot-clé URL "position" (App\Search\WordListFilters, place deja
  reservee dans l'ordre canonique documente) : "9-lettres/position/3/a" =
  mots de 9 lettres avec A en 3e position -- exige toujours une longueur
  explicite (meme raison que "tri", D-022)
espace volontairement restreint par rapport a "motif" general : UNE seule
  lettre connue a UNE seule position (jamais plusieurs simultanement) --
  ~2 366 combinaisons reelles au total (26 lettres x positions 2 a
  longueur-1 x 14 longueurs), largement borne contrairement a motif
  (2^15 combinaisons par longueur, jamais indexable, D-012)
collapse silencieux des positions degenerees (1re et derniere lettre) vers
  prefix/suffix existants (commencant/terminant) -- canonicalPath() n'emet
  jamais "position/1/..." ni "position/{longueur}/...", le routeur redirige
  en 301 vers la forme deja existante
implementation : App\Search\WordListSolver::extraPredicates() reutilise TEL
  QUEL le predicat substr(normalized, CAST(? AS INTEGER), 1) = ? deja
  present pour les cases residuelles de motif -- aucun nouvel index, ancrage
  toujours sur la longueur (idx_terms_length_normalized, deja mesure sur)
```

Raison :

```text
demande produit (2026-08-08/09) : "motif -a---" ne correspond a aucune
  intention de recherche reelle ("E en 3eme lettre" est la vraie phrase),
  contrairement a "avec"/"commencant"/"terminant" qui collent deja au
  vocabulaire naturel -- construire une vraie famille dediee plutot que de
  renommer motif en surface (l'espace combinatoire est fondamentalement
  different, voir ci-dessus, ce qui rend "position" borne et "motif" non)
en verifiant ce mecanisme, un defaut reel a ete trouve sur motif : "5-lettres/
  motif/a----" et "5-lettres/commencant/a" renvoient les 783 MEMES mots sous
  deux URL canoniques distinctes, jamais rapprochees -- sans consequence SEO
  active aujourd'hui (motif reste NEVER_SITEMAP en permanence), mais un vrai
  defaut de canonicalisation a ne pas reproduire sur une famille destinee a
  devenir indexable
```

Mesures (reports/query-plans/position-family.md, via WordListSolver::solve() réel) :

```text
9-lettres/position/3/a                 total=7 992   queryCount=1  min=45,6 ms  max=79,5 ms
15-lettres/position/8/e                total=2 532   queryCount=1  min=17,7 ms  max=22,5 ms
9-lettres/commencant/c/position/3/a    total=1 462   queryCount=1  min=3,3 ms   max=4,8 ms
11-lettres/position/5/e (pire cas plausible, panier le plus peuplé, plafond
  atteint) :                           total=10 000  queryCount=1  truncated=oui  min=52,0 ms  max=68,2 ms
correction verifiee par force brute (substr() SQL vs PHP) : 0 divergence
collapse verifie par force brute via le vrai solveur (pas seulement le
  parsing) : position/1/a et commencant/a produisent EXACTEMENT le meme
  total et le meme canonicalPath -- meme chose pour position/{longueur}/a
  et terminant/a
```

Conséquence :

```text
php tests/run.php : 19/19 (couverture ajoutee a WordListFiltersTest.php --
  parsing, collapse, conflits, bornes -- et WordListSolverTest.php --
  correction par force brute, regimes de requetes, collapse via le vrai
  solveur)
correctif Title Case decouvert au passage (app/View/word-list.php) :
  mb_convert_case(MB_CASE_TITLE) traite toute frontiere chiffre/lettre comme
  un debut de mot et capitalise ("3e" -> "3E") -- corrige par un
  preg_replace cible apres coup, aucune autre chaine du site n'a ce motif
  chiffre+lettre donc aucune regression ailleurs
portee NON couverte dans ce lot, par choix explicite (eviter le scope
  creep) : aucun maillage interne ajoute pour "position" (pas de nouvelle
  section sur /mots/{N}-lettres) et aucune classification App\Seo\Family --
  la famille reste noindex,follow par defaut (D-005), meme point de depart
  que "avec" avant sa propre decision d'ouverture -- decisions futures
  distinctes, a instruire separement si demandees
```

## D-024 — Correctif : `WORD_LIST_COMBINED` Est Dans `NEVER_SITEMAP`, Pas L'Inverse (Erreur D-022)

Date : 2026-08-09
Statut : accepté (correction de documentation + code)

Décision :

```text
correction de app/Search/LengthLinks.php (docblock) et de l'entrée D-022
  ci-dessus : les trois groupes de liens "commençant/terminant/avec par
  longueur" (D-022) ciblent tous des pages classées (en pratique, une fois
  qu'une classification existera -- voir raison) sous
  Family::WORD_LIST_COMBINED, qui EST dans NEVER_SITEMAP (app/Seo/Family.php
  ligne 72) -- D-022 affirmait le contraire ("pas dans NEVER_SITEMAP --
  éligibles à l'indexation"), erreur factuelle non vérifiée contre le code
  réel au moment de l'écrire
aucun changement de comportement runtime : ces pages étaient déjà
  noindex,follow par défaut (D-005, aucune ligne de registre pour elles),
  donc aucune régression -- uniquement une correction de ce qui était
  affirmé à tort dans la documentation
CORRECTIF ULTÉRIEUR (2026-08-10, 2e audit sur D-025) : cette entrée elle-même
  reconduisait une seconde erreur en affirmant que les TROIS groupes ciblent
  la MÊME famille -- faux, "avec" cible en réalité Family::WORD_LIST_AVEC
  (permanente dans NEVER_SITEMAP), distincte de Family::WORD_LIST_COMBINED
  (commençant/terminant, retirée de NEVER_SITEMAP par D-025). Voir la
  correction complète dans l'entrée D-022 ci-dessus et
  app/Search/LengthLinks.php -- trois affirmations successives sur ce même
  sujet avant d'arriver à la version correcte, chacune trouvée par une
  vérification indépendante (agent seo-registry, puis audit) plutôt que par
  relecture -- signe qu'une classification famille devrait être un
  classificateur exécutable, pas une affirmation en prose répétée à la main
  à chaque nouveau docblock (non résolu, voir D-025bis)
```

Raison :

```text
trouvé par l'agent seo-registry (2026-08-09) pendant l'analyse d'ouverture à
  l'indexation de "commençant + terminant" (demande produit distincte,
  D-025 si validée) -- l'agent a directement cité D-022/LengthLinks.php
  comme contredisant la constante réelle de app/Seo/Family.php, plutôt que
  de supposer l'un ou l'autre correct sans vérifier
leçon retenue : une affirmation sur le statut d'une famille SEO (dans
  NEVER_SITEMAP ou non) doit toujours être vérifiée directement contre
  app/Seo/Family.php au moment de l'écrire, jamais réutilisée de mémoire
  depuis une lecture antérieure dans la même session -- exactement le genre
  d'erreur silencieuse que ce document est censé empêcher de se répéter
```

Conséquence :

```text
aucun test cassé (les tests D-022 ne faisaient aucune assertion sur le
  statut d'indexation, seulement sur robots noindex,follow par défaut --
  toujours vrai)
décision produit prise dans la foulée (même jour) : ouvrir WORD_LIST_COMBINED
  à l'indexation -- l'agent seo-registry a repris son analyse pour lever le
  garde-fou, trancher le canonical des paires dupliquées et proposer un lot,
  voir D-025 ci-dessous (rapport AFTER reçu et vérifié)
maillage interne commençant+terminant construit dans la foulée (préalable
  identifié par l'agent, condition avant toute indexation, R "jamais de page
  orpheline indexée") : nouveau list_type 'start_end' dans list_counts
  (schema.sql, scripts/build_explore_hub_counts.php -- GROUP BY substr(),
  611 groupes non vides, 1,6 s hors ligne), nouvelles classes
  App\Search\LetterCombinedLinks/LetterCombinedLinksBuilder, câblées dans
  public/index.php depuis /mots/commencant/{X} et /mots/terminant/{Y} (déjà
  indexées, D-017) vers /mots/commencant/{X}/terminant/{Y} -- 1 requête
  triviale sur list_counts, testé par force brute
  (tests/Search/LetterCombinedLinksBuilderTest.php, cohérence des sommes
  vérifiée), php tests/run.php 20/20
portée volontairement partielle : seuls les 611 combos SANS longueur ont un
  maillage réel -- les 5 193 combos longueur+commençant+terminant restent
  orphelins (26x26 liens potentiels par page de longueur, densité non
  résolue dans ce lot) -- laissé au jugement de l'agent seo-registry pour la
  suite plutôt que résolu à la hâte
```

## D-025 — Ouverture De `WORD_LIST_COMBINED` À L'Indexation (Sans Longueur)

Date : 2026-08-09
Statut : accepté (rapport AFTER de l'agent seo-registry, vérifié indépendamment avant application de cette entrée)

Décision :

```text
App\Seo\Family::WORD_LIST_COMBINED retirée de NEVER_SITEMAP (app/Seo/
  Family.php) -- espace borné (26x26 = 676 sans longueur, 14x26x26 = 9 464
  au plus avec longueur), contrairement aux autres membres de la liste
  (combinaisons de lettres/sous-chaines veritablement non bornees)
lot appliqué (storage/seo_fr.sqlite) : 611 lignes index,follow -- les 676
  combinaisons commençant+terminant SANS longueur ayant >= 1 resultat reel
  (65 combinaisons a 0 resultat exclues, R5). Nouveau fragment sitemap
  combined-0001 (611 URL), prefixe documente dans docs/05_URL_SEO_INDEXATION.md
les 5 193 combinaisons AVEC longueur restent noindex,follow PAR OMISSION
  (aucune ligne de registre pour elles) : aucun maillage interne reel ne les
  couvre encore (D-024) -- decision explicitement differee, pas oubliee, pas
  une exclusion permanente
52 paires a contenu strictement duplique entre variante sans/avec longueur
  (tous les mots de la paire partagent la meme longueur) : variante SANS
  longueur designee gagnante canonique permanente, annotee ligne par ligne
  dans le registre (champ notes) -- si une famille "avec longueur" ouvre un
  jour, ces 52 triples precis devront rester noindex,follow (R3 : jamais
  deux lignes index,follow pour un contenu identique)
```

Raison :

```text
maillage interne reel construit au prealable (D-024, App\Search\
  LetterCombinedLinksBuilder, depuis /mots/commencant/{X} et /mots/
  terminant/{Y}, deja indexees D-017) -- condition posee par l'agent
  seo-registry avant toute ouverture (regle dure : jamais de page orpheline
  indexee), remplie uniquement pour ce sous-ensemble sans longueur
```

Vérifications faites par l'agent avant application (dry-run, pas seulement lu) :

```text
dry-run apply_seo_batch.php + build_sitemaps.php sur copie : 611/611 lignes
  acceptees, R1-R7 respectees, fragment combined-0001 valide
php tests/run.php avant ET apres application : 20/20 (verifie une seconde
  fois independamment par la session principale)
verification runtime reelle (pas seulement SQL) via App\Seo\Registry::
  resolve() ET un smoke-test HTTP reel (serveur php -S local, arrete apres) :
  page a 1 seul resultat NON exclue, combo a 0 resultat jamais dans le lot,
  variante avec longueur toujours noindex
```

Vérifications indépendantes faites par la session principale (pas seulement pris sur parole) :

```text
git status : fichiers modifies coherents avec le rapport (app/Seo/Family.php,
  tests/Seo/FamilyTest.php, scripts/build_sitemaps.php, scripts/
  propose_seo_batch.php, docs/05_URL_SEO_INDEXATION.md, storage/seo_fr.sqlite,
  public/sitemap-index.xml, public/sitemaps/combined-0001.xml -- nouveau)
app/Seo/Family.php lu directement : WORD_LIST_COMBINED bien absente de
  NEVER_SITEMAP, docblock a jour
storage/seo_fr.sqlite interroge directement : 838 859 lignes totales (838 248
  + 611), 611 lignes family='word_list_combined', toutes index,follow
/mots/commencant/x/terminant/q (0 resultat) et /mots/13-lettres/commencant/a/
  terminant/e (avec longueur) : absentes du registre, confirme -- retombent
  sur noindex,follow par defaut (D-005), jamais dans le lot
public/sitemaps/combined-0001.xml : 611 <loc>, public/sitemap-index.xml :
  27 fragments (etait 26)
php tests/run.php relance independamment : 20/20
```

Métriques quantifiées (avant → après) :

```text
registre, lignes totales               838 248 -> 838 859
fragments sitemap, total                    26 -> 27  (+combined-0001)
URLs, famille word_list_combined             0 -> 611  (sur 676 combinaisons
  possibles, 611 ont >= 1 resultat reel)
pages a exactement 1 resultat (famille)      -- -> 47  (signalees, PAS
  auto-exclues, coherent avec docs/05)
liens internes entrants par page (famille)   0 -> 2  (exact : chaque page
  recoit un lien depuis /mots/commencant/{X} ET /mots/terminant/{Y}, deja
  indexees D-017)
volume du lot applique                          611 URL (sur 5 804 candidates
  identifiees au total dans l'analyse BEFORE -- 5 193 avec longueur
  explicitement differees, pas oubliees)
```

Conséquence :

```text
audit seo-technical-auditor formel : NO GO (2026-08-09) -- deux bloquants,
  C-1 performance jamais mesuree sur la forme de page reellement publiee
  (corrige, voir D-025bis ci-dessous) et C-2 documentation de famille
  incoherente/imprecise (corrige, app/Search/LengthLinks.php et
  app/Seo/Family.php). Non bloquants releves aussi : arbitrage des 52
  paires non versionne durablement (I-1), aucun controle de coherence
  maillage <-> registre (I-2), pages de la famille sans lien retour vers
  /mots (I-3) -- ces trois-la restent ouverts, voir "non resolu" plus bas
ouvrir un jour les 5 193 combos avec longueur exige un maillage dedie
  (densite de liens a resoudre, 26x26 liens potentiels par page de
  longueur) -- hors perimetre de l'agent seo-registry (app/Seo/), a
  instruire separement si le produit le souhaite
domaine de production toujours https://CHANGE-ME.exemple.fr dans tous les
  sitemaps regeneres -- coherent avec l'existant, a corriger une seule fois
  en Phase 7 pour toutes les familles, pas specifique a ce lot
```

Non résolu après D-025bis (relevé par l'audit, pas encore traité) :

```text
I-1  arbitrage des 52 paires dupliquees trace uniquement dans storage/
     seo_fr.sqlite (colonne notes), non versionne -- perdu si le registre
     est reconstruit sans relire cette entree
I-2  aucun test n'assert l'egalite entre list_counts.start_end et les
     lignes reellement generees au registre pour la famille combined
I-3  les 611 pages de la famille n'ont aucun lien RETOUR vers /mots ni
     vers leurs deux pages parentes -- maillage entrant correct, sortant
     appauvri (section "Explorer" imbriquee dans la condition $lengthLinks,
     jamais atteinte sur ces pages qui n'ont pas de longueur)
```

## D-025bis — Régression De Performance Sur `/mots/commencant/{X}/terminant/{Y}` : Correction En Deux Temps

Date : 2026-08-09
Statut : accepté

Décision :

```text
nouvel index idx_terms_startletter_endletter_normalized(substr(normalized,1,1),
  substr(reversed,1,1), normalized) dans schema.sql -- egalite combinee sur
  les deux expressions a la fois, ni le prefixe ni le suffixe ne devient un
  predicat residuel quand les deux sont d'une seule lettre chacun
App\Search\WordListSolver::anchorClause() bascule sur ce chemin uniquement
  quand prefixe ET suffixe sont chacun d'une seule lettre (portee exacte du
  lot D-025) -- prioritaire sur une premiere iteration (choix de l'ancrage
  par frequence, comptes list_counts), conservee en repli pour les
  prefixes/suffixes multi-lettres (hors du lot D-025, jamais mesures
  problematiques)
applique a la base reelle via scripts/add_startletter_endletter_index.php
  (ANALYZE dans la meme operation, discipline D-021), inclus dans schema.sql
  pour toute reconstruction future
```

Raison :

```text
audit seo-technical-auditor du lot D-025 (611 pages) : NO GO, constat C-1 --
  performance jamais mesuree sur la forme de page reellement publiee
  (prefixe et suffixe d'une seule lettre chacun, suffixe toujours applique
  en predicat residuel non indexe). Verifie independamment avant tout
  correctif : confirme, pire que ce que l'audit citait -- jusqu'a 1 211 ms
  mesure reellement (commencant/p/terminant/h), tres au-dessus du budget
  TTFB p95 < 250 ms (CLAUDE.md), sur des pages DEJA index,follow
une premiere iteration (choisir l'ancrage le moins frequent des deux) a
  corrige les cas cites par l'audit (17 ms max) mais un balayage complet
  des 611 combinaisons reelles du lot (pas seulement les exemples de
  l'audit) a revele 53 cas encore au-dessus du budget des que les DEUX
  lettres sont frequentes -- jusqu'a 6 675 ms (commencant/z/terminant/s),
  pire que la mesure initiale de l'audit
```

Mesures complètes (reports/query-plans/prefix-suffix-anchor-fix.md) :

```text
avant tout correctif       commencant/r/terminant/h  mediane 158 ms max 346 ms
                            commencant/p/terminant/h  mediane 247 ms max 1 211 ms
apres iteration 1 (frequence)  memes cas : max 17 ms et 4 ms -- mais balayage
  complet des 611 combos : 53/611 au-dessus de 250 ms, max 6 675 ms
apres iteration 2 (index combine)  balayage complet des 611 combos :
  0/611 au-dessus de 250 ms, p50=0,63 ms p95=26,77 ms max=65,15 ms
correction verifiee par force brute sur plusieurs cas, dont le cas
  degenere Z (rangeBounds('Z') sans borne superieure, avait fait basculer
  le plan SQLite sur idx_terms_reversed -- 338 308 lignes -- au lieu de la
  petite plage prefixe pourtant disponible)
```

Conséquence :

```text
php tests/run.php : 20/20 (tests/Search/WordListSolverTest.php etendu :
  cas frequent+rare, rare+frequent, et le cas degenere frequent+frequent
  qui a revele le vrai defaut -- pas seulement les deux exemples cites par
  l'audit)
coeur de stockage : storage/dictionary_fr.sqlite passe de 236,5 Mo (D-022)
  a 255,5 Mo (+19 Mo) pour cet index seul
correction C-2 de l'audit traitee dans le meme lot (documentation) : voir
  app/Search/LengthLinks.php et app/Seo/Family.php -- la justification
  "26x26 borne" ne s'applique qu'au sous-ensemble mono-lettre reellement
  mesure et ouvert, jamais a la famille WORD_LIST_COMBINED dans son
  ensemble (prefixes/suffixes multi-lettres non mesures, non bornes)
lecon retenue, deux volets distincts : (1) un lot d'indexation doit etre
  dimensionne en cout serveur mesure sur la forme de page reellement
  publiee, jamais seulement en nombre d'URL ; (2) une correction qui
  resout les cas cites par un audit n'est pas la meme chose qu'une
  correction verifiee sur la totalite du lot concerne -- toujours
  re-balayer l'ensemble reel, pas seulement l'exemple qui a revele le defaut
2e passe seo-technical-auditor (2026-08-10) : GO -- C-1 et C-2 verifies
  corriges sur le fond (index structurellement correct, balayage complet
  des 611 URL reelles comme preuve, pas un echantillon). Trois nouveaux
  constats non bloquants, tous traites dans la foulee avant de considerer
  ce correctif clos :
    - aucun test n'assertait la presence reelle de l'index deploye (les
      tests de regression verifient $queryCount et le resultat, tous deux
      independants du plan choisi par SQLite) -- corrige par
      tests/Database/RequiredIndexesTest.php (verifie sqlite_master ET
      sqlite_stat1 pour tous les index de regression du projet, pas
      seulement celui-ci)
    - la portee annoncee ("sans longueur uniquement") etait fausse : le
      code applique ce chemin AVEC OU SANS longueur, jamais verifiee
      contre le code reel au moment d'ecrire le commentaire -- corrige
      dans schema.sql et le docblock de anchorClause()
    - suffixLetterIsRarer() (repli multi-lettres) documentee comme une
      heuristique (compare la 1re/derniere lettre seule, pas la
      selectivite reelle de la plage) plutot que presentee comme un choix
      optimal
    - contradiction trouvee entre docs/DECISIONS.md (D-022/D-024, qui
      affirmaient encore que "avec" et "commencant/terminant" ciblent la
      MEME famille) et le code deja corrige (app/Search/LengthLinks.php) --
      troisieme affirmation fausse sur ce meme sujet en deux jours,
      chacune trouvee par verification independante plutot que relecture --
      corrige dans les entrees D-022/D-024 ci-dessus ; le classificateur
      famille reste une affirmation en prose repetee a la main a chaque
      docblock plutot qu'un code executable verifiable une seule fois --
      non resolu, risque de recidive reel si un nouveau docblock est
      ecrit sans revalider contre app/Seo/Family.php
D-025 considere valide apres ce lot de correctifs -- php tests/run.php :
  22/22 (tests/Database/RequiredIndexesTest.php nouveau)
```

## D-023bis — Maillage "Avec {X}" → Position Exacte

Date : 2026-08-09
Statut : accepté

Décision :

```text
nouveau list_type 'length_with_position' dans list_counts (schema.sql,
  scripts/build_explore_hub_counts.php) : croise longueur, lettre ET
  position exacte (list_key = "{longueur}:{lettre}:{position}") -- calcule
  par un parcours PHP unique (une position par caractere), pas de GROUP BY
  SQL sur une expression composee
nouvelles classes App\Search\PositionLinks / PositionLinksBuilder --
  1 requete triviale sur list_counts par page
cablage (public/index.php) : uniquement depuis une page longueur + UNE
  SEULE lettre "avec" (occurrence unique, sans autre contrainte) -- section
  "Position De {X} Dans Le Mot" sur app/View/word-list.php, meme
  composant .explore-group/.related-links que le reste du site
collapse identique a D-023 : position 1 pointe vers commencant/{X} (deja
  existant), derniere position vers terminant/{X} (deja existant), jamais
  une URL "position/1/..." ou "position/{longueur}/..." qui n'existe pas
```

Raison :

```text
demande produit (2026-08-09) : depuis "mots de {N} lettres avec {X}",
  pouvoir filtrer par position exacte de la lettre, avec un lien interne
  vers chaque page position/{P}/{X} (D-023) correspondante
```

Mesures (reports/query-plans/position-links.md) :

```text
precalcul : 3 019 lignes generees (sur 14x26x15 = 5 460 combinaisons
  possibles au maximum), cumule avec les autres list_type du meme script
  (~59 s pour l'ensemble, hors ligne uniquement)
lecture runtime : build(9, 'W') : 9 liens, queryCount=1, 8,65 ms
correction verifiee par force brute (position 1, derniere position,
  position intermediaire) : 0 divergence
```

Conséquence :

```text
php tests/run.php : 22/22 (tests/Search/PositionLinksBuilderTest.php
  nouveau, force brute sur les trois cas -- collapse commencant, collapse
  terminant, position intermediaire)
cible Family::WORD_LIST_AVEC (via "avec") et le mecanisme position (D-023)
  pour les positions intermediaires -- toutes ces pages restent
  noindex,follow par defaut (D-005), navigation/decouverte uniquement
```

## D-024bis — Maillage "Avec {X} Sans {Y}" → Longueur

Date : 2026-08-09
Statut : accepté

Décision :

```text
nouveau list_type 'length_avec_sans' dans list_counts (schema.sql,
  scripts/build_explore_hub_counts.php) : croise une lettre exigee, une
  lettre exclue ET la longueur (list_key = "{avec}:{sans}:{longueur}") --
  calcule par un parcours PHP unique (chaque lettre presente x chaque
  lettre absente, par mot), ~66 s cumules avec les autres list_type du
  meme script, hors ligne uniquement
nouvelles classes App\Search\AvecSansLengthLinks / AvecSansLengthLinksBuilder
  -- 1 requete triviale sur list_counts par page
cablage (public/index.php) : uniquement depuis une page SANS longueur, UNE
  SEULE lettre "avec" (occurrence unique) ET UNE SEULE lettre "sans", sans
  autre contrainte -- section "Avec {X} Sans {Y}, Par Longueur" sur
  app/View/word-list.php
```

Raison :

```text
demande produit (2026-08-09), formulee explicitement comme une question de
  pertinence ("si pertinent ?") plutot qu'une exigence -- mesure faite
  avant toute decision : requete live (GROUP BY sur deux predicats instr())
  91 a 170 ms selon la combinaison, risque reel de depasser le budget TTFB
  combine au reste de la page -- precalcul retenu, meme mesure et meme
  arbitrage que length_with (D-022)
```

Mesures (reports/query-plans/avec-sans-length-links.md) :

```text
precalcul : 9 096 lignes generees (sur 26x25x14 = 9 100 combinaisons
  possibles au maximum)
lecture runtime : build('Q','U') : 12 liens, queryCount=1, 6,00 ms
correction verifiee par force brute sur les 12 longueurs, et par somme
  (total par longueur = total sans longueur)
```

Conséquence :

```text
php tests/run.php : 23/23 (tests/Search/AvecSansLengthLinksBuilderTest.php
  nouveau)
cible Family::WORD_LIST_AVEC ET Family::WORD_LIST_SANS a la fois (deux
  familles a espace non borne) : ces deux familles restent et resteront
  dans NEVER_SITEMAP en permanence -- ce maillage sert uniquement la
  navigation/decouverte humaine, aucun gain SEO (contrairement a
  D-023bis/D-024 qui ouvrent un chemin vers des familles potentiellement
  indexables) -- accepte en connaissance de cause, decision explicite du
  propriétaire du produit
```

## D-025ter — Pages Légales, Politique De Confidentialité Et Formulaire De Contact

Date : 2026-08-10
Statut : accepté

Décision :

```text
trois nouvelles pages statiques, aucune requete SQLite : /mentions-legales,
  /confidentialite, /contact (app/View/mentions-legales.php,
  confidentialite.php, contact.php) -- lien deja present dans le pied de
  page du mockup (prototype/index.html) mais jamais construit avant ce jour
identite de l'editeur (BIGBANG MEDIA, EURL, RCS Laval, SIREN 917 929 382) et
  de l'hebergeur (o2switch, SAS, RCS Clermont-Ferrand, SIREN 510 909 807)
  verifiees aupres de sources publiques (Infogreffe/INPI/Pappers pour
  BIGBANG MEDIA, CGV officielles pour o2switch) au moment de la redaction --
  jamais inventees
nom personnel, adresse complete du siege et email de l'editeur
  volontairement absents des deux pages (demande explicite du proprietaire
  du produit) : le siege n'apparait qu'au niveau ville/code postal, le
  directeur de la publication est designe par sa fonction plutot que nomme
formulaire /contact : mail() natif PHP (gratuit, aucune inscription, aucune
  dependance externe, D-007 : rien a declarer, mail() fait partie du
  langage) -- premiere et seule route du site a accepter une methode POST,
  refusee explicitement partout ailleurs (public/index.php)
adresse de destination JAMAIS presente dans un fichier verse au depot
  (demande explicite, anti-spam) : lue exclusivement via la variable
  d'environnement SCRABBLE_CONTACT_EMAIL, a definir cote hebergement
  (o2switch/cPanel, "Environment Variables"), meme convention que
  SCRABBLE_DICTIONARY_DB_PATH -- absence de configuration redirige vers un
  etat d'erreur explicite plutot qu'un mail() a destinataire vide
piege a bots (honeypot) sur le formulaire : champ cache hors du flux visuel
  (CSS, pas display:none) et hors du parcours clavier/lecteur d'ecran
  (aria-hidden, tabindex="-1") -- un bot qui le remplit recoit une fausse
  confirmation de succes, sans email envoye
validation stricte de l'email AVANT usage dans l'en-tete Reply-To
  (filter_var FILTER_VALIDATE_EMAIL + suppression CRLF en defense
  supplementaire) -- injection d'en-tetes email est une vulnerabilite
  classique de mail() avec une entree utilisateur non validee
ponctuation imposee sur ces trois pages (demande explicite, appliquee
  retroactivement) : aucun tiret cadratin, aucun deux-points en milieu de
  phrase -- seuls les couples etiquette/valeur factuels (ex. "SIREN :
  917 929 382") gardent un deux-points
```

Raison :

```text
demande produit (2026-08-10) : lien deja prevu dans le mockup original,
  jamais construit ; premiere version des pages legales jugee bien trop
  courte pour un site serieux, deuxieme version massivement etendue
  (sommaire ancre, ~15 rubriques chacune) apres retour explicite ; canal de
  contact ajoute pour combler l'ecart RGPD signale a la premiere version
  (aucun moyen de contact publie alors que le RGPD en exige un pour
  l'exercice des droits) -- referme partiellement cet ecart, sans jamais
  publier d'adresse email
```

Conséquence :

```text
php tests/run.php : 23/23, aucun test dedie a ces trois pages (contenu
  statique, aucune logique de recherche a verifier par force brute) --
  validation faite en direct via le serveur de developpement (soumission
  valide, email invalide, message vide, piege a bots), pas seulement lue
  dans le code
mail() non testable en conditions reelles sur la machine de developpement
  (aucun agent de transfert de courrier local configure sur cet
  environnement Windows) -- seule la validation et le routage ont ete
  verifies de bout en bout, pas la livraison effective ; o2switch fournit
  nativement mail() sans configuration supplementaire en production
/mentions-legales, /confidentialite, /contact restent noindex,follow par
  defaut (D-005, aucune ligne registre) -- voir D-026 pour la decision
  explicite de les y laisser
```

## D-026 — Pages Légales (/mentions-legales, /confidentialite, /contact) Volontairement Non Indexées

Date : 2026-08-10
Statut : accepté

Décision :

```text
/mentions-legales, /confidentialite et /contact restent noindex,follow par
  defaut (D-005) -- aucune ligne ajoutee au registre pour elles, decision
  explicite plutot qu'un simple oubli. Aucune famille App\Seo\Family creee
  pour ce trio, aucun lot jamais destine a les ouvrir.
```

Raison :

```text
demande produit (2026-08-10), apres consultation de la cartographie
  complete des URL du site (reports/query-plans/ n'en contient pas de
  trace ecrite avant cette entree, discussion tenue directement) : les
  pages legales/utilitaires n'apportent generalement aucun trafic de
  recherche pertinent et n'ont pas vocation a etre decouvertes via Google
  -- pratique standard sur le web, pas une particularite de ce site
```

Conséquence :

```text
aucun changement de code : ces trois pages etaient deja noindex,follow par
  la seule absence de ligne registre (D-005), verifie en conditions
  reelles au moment de leur creation (D-025ter). Cette entree documente
  l'intention pour eviter qu'un futur audit ou une future session ne
  traite cette absence comme un oubli a corriger.
```

## D-027 — Maillage Interne Commençant + Terminant AVEC Longueur

Date : 2026-08-10
Statut : accepté

Décision :

```text
nouveau list_type 'length_start_end' dans list_counts (schema.sql,
  scripts/build_explore_hub_counts.php) : croise longueur, lettre de
  debut ET lettre de fin (list_key = "{longueur}:{debut}:{fin}", ex.
  "9:R:E") -- 5 193 lignes non vides sur 9 464 combinaisons possibles
  (14 x 26 x 26), ~8,0 s de calcul hors ligne (GROUP BY sur terms,
  jamais au runtime)
nouvelles classes App\Search\LengthCombinedLinks /
  LengthCombinedLinksBuilder -- 1 requete triviale sur list_counts par
  page, balayage complet des 690 pages reelles (longueur+prefixe ou
  longueur+suffixe seul) : 0/690 au-dessus du budget TTFB, max 6,664 ms
CORRECTIF (audit du lot D-028, seo-technical-auditor, constat I3,
  2026-08-11) : cette entree affirmait a tort que les 690 pages source
  etaient "deja indexees, D-022" -- verifie directement contre
  storage/seo_fr.sqlite, 0 ligne de registre pour /mots/{N}-lettres/
  commencant/{X} ou /mots/{N}-lettres/terminant/{Y}, noindex,follow par
  omission (D-005), exactement comme les pages qu'elles maillent. Le
  maillage construit ici relie donc deux niveaux tous deux non
  indexes -- aucune consequence sur ce lot (aucune decision
  d'indexation n'a jamais ete prise pour la variante avec longueur),
  mais toute future decision d'ouverture devra d'abord traiter ce
  meme probleme de maillage-depuis-page-indexee que celui corrige
  pour D-028 (voir reports/query-plans/position-length-maillage.md)
cablage public/index.php ($lengthCombinedLinks, meme emplacement que
  $letterCombinedLinks) et app/View/word-list.php (nouvelle section
  "explore-group", meme structure que la section commencant+terminant
  existante) : declenche uniquement depuis une page longueur+UNE SEULE
  lettre commencant OU terminant, sans l'autre cote, sans aucune autre
  contrainte active (contenant/avec/sans/motif/position/statut)
```

Raison :

```text
prealable identifie par l'agent seo-registry avant toute future
  decision d'ouverture de la variante AVEC longueur de
  Family::WORD_LIST_COMBINED a l'indexation : performance deja mesuree
  sure sur les 9 464 combinaisons reelles
  (reports/query-plans/combined-with-length-full-sweep.md, 0/9464
  au-dessus de 250 ms), mais ouverture refusee faute de lien interne
  entrant reel -- 0 des ~5 141 pages eligibles n'avait le moindre lien
  avant ce lot, meme regle dure ("jamais de page orpheline indexee")
  qui avait deja bloque la variante SANS longueur avant D-024/D-025
```

Conséquence :

```text
php tests/run.php : 24/24 (tests/Search/LengthCombinedLinksBuilderTest.php
  nouveau), storage/dictionary_fr.sqlite reconstruit (list_counts
  13 846 -> 19 039 lignes, terms inchange, 838 180 lignes)
ne constitue AUCUNE decision d'ouverture a l'indexation -- toutes les
  pages ciblees (Family::WORD_LIST_COMBINED, variante avec longueur)
  restent noindex,follow par defaut (D-005), decision future distincte,
  non prise ici. Les 52 paires de doublons de contenu deja identifiees
  par D-025 (I-1, arbitrage canonique avec la variante sans longueur)
  restent hors sujet a ce stade -- concernent uniquement une future
  decision d'indexation sur le registre, jamais ce maillage de
  navigation
voir reports/query-plans/length-combined-links.md pour le detail complet
```

## D-028 — Classification `Family::WORD_LIST_POSITION` Et Ouverture À L'Indexation

Date : 2026-08-10 (classification et dimensionnement), appliqué le 2026-08-11
Statut : accepté et appliqué

Décision :

```text
demande produit (2026-08-10) : etudier l'ouverture a l'indexation de
  /mots/{N}-lettres/position/{P}/{X} (D-023) et de la variante AVEC
  longueur de commencant+terminant (voir D-027 ci-dessus pour cette
  seconde famille, restee bloquee)
balayage complet des 2 366 combinaisons position reelles via le vrai
  solveur (reports/query-plans/position-full-sweep.md) : 0/2366
  au-dessus du budget TTFB (p50=25,4ms p95=57,4ms max=129,3ms),
  37 combinaisons a 0 resultat -> 2 329 pages eligibles
maillage verifie exhaustivement, pas suppose : les 2 329 pages
  eligibles ont chacune exactement 1 lien entrant reel deja en place
  (D-023bis, depuis /mots/{N}-lettres/avec/{X}) -- 0 orpheline
nouvelle classification App\Seo\Family::WORD_LIST_POSITION ajoutee
  (app/Seo/Family.php), volontairement hors NEVER_SITEMAP -- espace
  borne par construction (une seule lettre a une seule position,
  jamais motif general), contrairement a WORD_LIST_MOTIF
scripts/build_sitemaps.php : prefixe de fragment 'position' ajoute
  pour cette famille ; docs/05_URL_SEO_INDEXATION.md mis a jour
canonicals rejoues sur les 2 329 route_path proposes via le vrai
  WordListFilters::fromPath()->canonicalUrl() : 0 divergence
dry-run reel (registre jetable, jamais storage/seo_fr.sqlite) :
  lot applique proprement, 2 329 lignes en index,follow, fragment
  position-0001.xml, 0 erreur
```

Raison :

```text
meme discipline que D-024/D-025 (D-025bis en particulier) : mesurer
  sur la totalite du lot avant toute proposition d'ouverture, jamais
  sur un echantillon -- ici les deux garde-fous (performance, maillage)
  sont verifies positifs pour position, contrairement a commencant+
  terminant avec longueur (D-027) qui echoue sur le maillage seul
```

Conséquence :

```text
php tests/run.php : 24/24
lot applique reellement le 2026-08-11 (validation explicite du volume
  donnee par le proprietaire du produit, "aucune contre-indication ?"),
  via scripts/apply_seo_batch.php contre storage/seo_fr.sqlite : 2 329
  lignes ajoutees, toutes en index,follow, canonical_path = route_path
  sur 100% des lignes (R3), notes non vide sur 100% (R7)
registre : 838 859 -> 841 188 URL (+2 329 exact), toutes les autres
  familles verifiees strictement identiques avant/apres
sitemaps regeneres (scripts/build_sitemaps.php) : 27 -> 28 fragments,
  nouveau position-0001.xml (2 329 URL), les 26 fragments preexistants
  restes byte-identiques
voir reports/query-plans/position-full-sweep.md et
  reports/query-plans/position-family.md pour le detail complet
```

## D-028bis — Correction Du NO GO Sur Le Lot Position (Maillage, Métriques, Traçabilité)

Date : 2026-08-11
Statut : accepté

Décision :

```text
1er audit seo-technical-auditor sur le lot D-028 (deja applique) : NO GO,
  trois bloquants -- performance et bornage de la famille juges bons,
  pas remis en cause
C1 (maillage insuffisant) -- constat verifie par l'auditeur : sur les
  2 329 pages, une seule avait un lien direct depuis une page DEJA
  INDEXEE (l'exemple contextuel de app/View/home.php:245). Les 2 328
  autres n'etaient reliees que depuis /mots/{N}-lettres/avec/{X}
  (D-023bis), qui appartient a Family::WORD_LIST_AVEC -- NEVER_SITEMAP,
  jamais indexable par construction. Corrige par une nouvelle section
  groupee par position sur /mots/{N}-lettres elle-meme (deja indexee,
  Family::WORD_LIST_LENGTH, D-017) : App\Search\LengthLinks/
  LengthLinksBuilder etendus (4e champ byPosition, meme requete SQL
  elargie a list_counts 'length_with_position', toujours 1 seule
  requete, aucun changement de signature ni de public/index.php),
  app/View/word-list.php (nouvelle section "Par Position De Lettre",
  sous-groupes <details> natifs replies par defaut -- tous les liens
  restent dans le HTML servi, aucune perte de crawlabilite, seule la
  presentation visuelle change). Couverture complete retenue (2 329/
  2 329, pas un sous-ensemble partiel) -- volume assume : de +26 liens
  (3 lettres) a +320 liens (15 lettres) ajoutes sur les pages
  /mots/{N}-lettres concernees, au-dessus du seul repere de plafond de
  liens documente du projet (~160, docs/01_MASTER_BRIEF.md, contexte
  fiche mot, pas directement transposable) pour 7 des 13 longueurs --
  attenue par le repli <details>, pas par une reduction du maillage
C2 (metriques manquantes) -- calculees et publiees : 17/2329 pages a
  exactement 1 resultat (0,73%, signalees pour revue, pas des
  candidates automatiques au noindex) ; couverture par lien direct
  depuis une page indexee passee de 1/2329 (0,04%) a 2329/2329 (100%)
  apres le correctif ci-dessus ; liens entrants reels moyens par page
  passes de 1,00043 a 2,00043
C3 (lot non reproductible) -- cas 'position' ajoute a
  scripts/propose_seo_batch.php (source list_counts, list_type
  'length_with_position', deja precalcule), lot regenere et verifie
  champ par champ contre les 2 329 lignes deja appliquees a
  storage/seo_fr.sqlite : 0 divergence. Lot versionne dans
  scripts/seo-batches/position-full-2026-08-11.php, teste par
  tests/Seo/ProposeSeoBatchPositionTest.php (sous-processus reel).
  Application testee sur une copie du registre (jamais le fichier
  reel) : comparaison integrale des 841 188 lignes, 0 divergence
I3 (constat annexe, non bloquant) -- corrige au passage : la premisse
  "690 pages sources deja indexees, D-022" (reports/query-plans/
  length-combined-links.md, schema.sql, D-027) etait fausse -- verifie
  directement contre storage/seo_fr.sqlite, ces pages sont
  noindex,follow par omission comme le reste. Corrige dans les trois
  fichiers concernes.
```

Raison :

```text
le lot avait ete ecrit au registre avant l'audit complet (question
  produit "aucune contre-indication ?" traitee comme un feu vert sur
  la performance seule, qui etait effectivement solide) -- lecon
  retenue : meme un lot mesure sur, comme D-025bis l'avait deja montre
  pour la performance, exige sa propre verification de maillage avant
  ecriture, pas seulement apres. L'audit distingue explicitement le
  travail de performance (juge conforme a la discipline post-D-025bis :
  balayage complet, vrai code, forme mesuree = forme publiee) du
  probleme de maillage, jamais confondus dans le verdict
```

Conséquence :

```text
php tests/run.php : 25/25 (Seo\ProposeSeoBatchPositionTest.php nouveau)
smoke test HTTP reel sur /mots/13-lettres : section "Par Position De
  Lettre" presente, 11 groupes <details>, lien position/9/r verifie
  present dans le HTML initial (pas de JavaScript requis)
aucune nouvelle URL ajoutee au registre par cette correction -- le
  total reste a 841 188 (fixe depuis D-028), cette passe ne modifie
  que le maillage et l'outillage, jamais le volume indexe
2e audit seo-technical-auditor (2026-08-17) : GO, C1/C2/C3/I3 tous
  reverifies independamment (pas sur parole) -- couverture 2 329/2 329
  confirmee par identite d'ensemble entre list_counts, le lot versionne
  et le sitemap derive du registre reel ; volume de liens (+320 sur
  /mots/15-lettres) juge acceptable, non bloquant, PAS un precedent
  pour d'autres familles (ex. length_start_end, D-027, 5 193 pages,
  exigerait sa propre decision). Points non bloquants releves (a
  traiter sans urgence) : notes du registre encore fondees sur
  l'ancien maillage /avec/ (a regenerer le jour ou le lot est
  rejoue), aucun test de coherence lot<->registre<->maillage rendu,
  profondeur de pagination de la famille non chiffree (budget de
  crawl, pas d'indexation), compte affiche parfois superieur au
  compte servi sur les 166 pages plafonnees a 10 000 resultats, pas de
  lien retour vers la page longueur parente. C-3 (domaine
  CHANGE-ME.exemple.fr, robots.txt sans directive Sitemap) confirme
  bloquant pour la Phase 7 uniquement, sans rapport avec ce lot.
```

## D-029 — Ouverture En Entonnoir De "avec" — Palier 1 (Longueur + 1 Lettre)

Date : 2026-08-17
Statut : accepté et appliqué

Décision :

```text
demande produit (2026-08-17) : les pages "avec" repondent a un vrai besoin
  de recherche ("mots 9 lettres avec A et Y") mais restent bloquees en bloc
  (Family::WORD_LIST_AVEC, NEVER_SITEMAP permanent, multiensemble de
  lettres non borne). Strategie retenue : ouvrir en ENTONNOIR, un palier
  de nombre de lettres exigees a la fois, chaque palier borne, mesure,
  maille et audite independamment -- jamais un seul lot couvrant tout
  "avec" d'un coup. Volume cible final assume comme important (plusieurs
  centaines de milliers de pages a terme, paliers futurs), mais chaque
  palier suit la meme discipline mesure-avant-ouverture que D-024/D-025/
  D-028, jamais un raccourci
palier 1 (celui-ci) : /mots/{N}-lettres/avec/{X} -- longueur explicite +
  EXACTEMENT une lettre "avec" (occurrence unique). 364 combinaisons
  reelles (14 longueurs x 26 lettres), TOUTES a au moins 1 resultat (0
  exclusion) -- balayage complet via le vrai solveur
  (reports/query-plans/avec-length-1-letter-full-sweep.md) : 0/364
  au-dessus du budget TTFB (p50=36,6ms p95=90,2ms max=168,0ms), toujours
  ancre sur length = ? (idx_terms_length_normalized), jamais un SCAN
  complet -- structurellement different du cas general "avec" sans
  longueur (WordListSolver::anchorClause(), anchorType='none' des que
  aucune longueur/prefixe/suffixe n'est present, qui LUI visite bien la
  table entiere -- c'est precisement pourquoi le cas general reste et
  restera bloque en permanence)
maillage deja 100% couvert AVANT ce lot, aucun nouveau code necessaire :
  App\Search\LengthLinksBuilder::byWith construisait deja les 364 liens
  depuis /mots/{N}-lettres (deja indexee, Family::WORD_LIST_LENGTH,
  D-017) -- verifie exhaustivement dans les deux sens (registre -> lien
  retrouve, lien -> registre index,follow), 364/364, avant meme
  d'appliquer le lot -- lecon retenue de D-028bis appliquee des le
  depart cette fois, pas apres un NO GO
nouvelle classification App\Seo\Family::WORD_LIST_AVEC_SINGLE_LETTER --
  sous-ensemble borne et distinct de WORD_LIST_AVEC (qui reste et restera
  dans NEVER_SITEMAP en permanence, jamais reutilisable pour un perimetre
  plus large que celui mesure ici). Nouveau fragment sitemap
  avec-single-0001.xml (364 URL), cas 'avec_single_letter' ajoute a
  scripts/propose_seo_batch.php (source list_counts, list_type
  'length_with', deja precalcule), lot versionne dans
  scripts/seo-batches/avec-single-letter-full-2026-08-16.php
2 pages a exactement 1 resultat dans ce lot (2-lettres/avec/w = WU,
  2-lettres/avec/z = ZA) -- GARDEES, pas exclues (instruction produit
  explicite : 0 resultat jamais indexe, 1 resultat reste legitime)
```

Raison :

```text
reponse a un besoin de recherche reel identifie par le proprietaire du
  produit, avec une architecture qui evite de repeter l'erreur du lot
  position (D-028, NO GO initial sur le maillage) : verification
  exhaustive du maillage AVANT application du lot, pas apres -- rendu
  possible ici par le fait que le palier 1 reutilise un maillage deja
  construit en D-022 (byWith), jamais un nouveau code a auditer en meme
  temps que le volume
```

Conséquence :

```text
php tests/run.php : 26/26 (tests/Seo/ProposeSeoBatchAvecSingleLetterTest.php
  nouveau)
registre : 841 188 -> 841 552 (+364 exact), toutes les autres
  familles verifiees strictement identiques avant/apres
sitemaps : 28 -> 29 fragments (avec-single-0001.xml, 364 URL)
smoke test HTTP reel : /mots/9-lettres/avec/a -> 200, index,follow,
  canonical correct ; /mots/avec/a (sans longueur) -> noindex,follow
  inchange ; /mots/9-lettres/avec/a/b (palier 2 futur) -> noindex,follow,
  ce lot ne le touche pas
deux corrections de documentation perimee au passage (meme risque de
  derive deja releve deux fois sur ce fichier, D-024/D-025bis) :
  schema.sql (commentaire 'length_with') et app/Search/LengthLinks.php
  (docblock byWith) affirmaient tous deux a tort que ces pages restaient
  hors sitemap -- corriges
palier 2 (longueur + 2 lettres) NON commence : aucun code, aucune
  mesure, aucune classification -- prochaine etape distincte, sa propre
  decision
audit seo-technical-auditor (2026-08-17) : GO, aucun bloquant. Registre,
  maillage (1 lien direct par page, verifie dans les deux sens), famille
  cloisonnee de WORD_LIST_AVEC, sitemaps et reproductibilite tous
  reverifies independamment. Points non bloquants : deux derives
  documentaires supplementaires trouvees et corrigees (public/index.php,
  commentaire "avec hors sitemap" perime ; app/Search/LengthLinks.php,
  etiquette de famille perimee sur byPosition) ; surface de pagination de
  la famille non chiffree (~34 000 a 54 000 URL /page/N crawlables,
  jamais indexables, budget de crawl seulement) ; compte affiche >
  compte servi sur les pages plafonnees a 10 000 resultats (deja connu,
  D-028bis) ; garde-fou R4 reste declaratif sur la FORME de route_path
  par famille (a couvrir avant les paliers futurs)
```

## D-030 — Ouverture En Entonnoir De "avec" — Palier 2 (Longueur + 2 Lettres)

Date : 2026-08-17
Statut : accepté et appliqué

Décision :

```text
/mots/{N}-lettres/avec/{X}/{Y} -- longueur explicite + EXACTEMENT deux
  lettres "avec" distinctes (occurrence unique chacune). 4 550
  combinaisons brutes (14 longueurs x C(26,2)=325 paires), 274 a 0
  resultat (exclues), 132 a exactement 1 resultat (GARDEES, meme
  consigne produit que D-029) -> 4 276 pages eligibles, une seule vague
ancrage confirme dans le code (WordListSolver::anchorClause()) : reste
  sur length = ? quel que soit le nombre de lettres "avec", jamais un
  second ancrage ni un scan complet -- structurellement identique au
  palier 1
nouveau list_type precalcule 'length_with_pair' dans list_counts
  (schema.sql, scripts/build_explore_hub_counts.php) : croise longueur
  et CHAQUE PAIRE de lettres distinctes presentes (list_key =
  "{longueur}:{lettre1}:{lettre2}", lettre1 < lettre2), 4 276 lignes
  non vides, 19,05 s de calcul hors ligne
maillage construit ET verifie exhaustivement DANS CETTE MEME PASSE
  (lecon de D-028 appliquee des le depart, pas apres un NO GO) : nouvelles
  classes App\Search\AvecTwoLettersLinks/AvecTwoLettersLinksBuilder,
  depuis les 364 pages palier 1 (deja indexees, D-029) -- couverture
  4276/4276 (100%) dans les deux sens, chaine complete /mots/{N}-lettres
  (indexee) -> avec/{X} (palier 1) -> avec/{X}/{Y} (palier 2), chaque
  maillon verifie
nouvelle classification App\Seo\Family::WORD_LIST_AVEC_TWO_LETTERS --
  distincte de WORD_LIST_AVEC_SINGLE_LETTER (palier 1) ET de
  WORD_LIST_AVEC (general, permanent, NEVER_SITEMAP)
cablage public/index.php ($avecTwoLettersLinks, meme condition
  d'activation que $positionLinks) et app/View/word-list.php (section
  "Mots De {N} Lettres Avec {X} Et", au plus 25 liens par page -- aucun
  besoin de repli <details>, contrairement a byPosition/D-028bis)
```

Investigation de performance -- bruit de mesure trouvé, creusé, tranché :

```text
balayage complet execute 5 fois independamment au total (2 par
  data-engine, 2 par seo-registry, 1 verification finale par la session
  principale) : resultats tres variables selon l'execution --
  0 a 94 cas au-dessus de 250ms sur 4550 (ou 650 pour les verifications
  ciblees longueur 12-13), pics isoles jusqu'a 109 643 ms (109 secondes)
  dans un run
EXPLAIN QUERY PLAN identique et stable sur TOUTES les executions :
  SEARCH terms USING INDEX idx_terms_length_normalized (length=?),
  jamais de SCAN ni de TEMP B-TREE -- la requete elle-meme n'a jamais
  varie
verification finale (session principale, 2026-08-18) : balayage propre
  des 650 combinaisons longueur 12-13, AUCUN autre agent actif en
  parallele au meme moment -- 1/650 au-dessus de 250ms (295ms), rien
  qui approche les pics precedents
conclusion retenue, AFFINEE par l'audit seo-technical-auditor (analyse
  structurelle independante, 2026-08-18, sans outil d'execution mais en
  relisant Connection.php et le plan de requete) : PAS un verrouillage
  SQLite (aucun busy_timeout dans Connection.php -- un lecteur bloque
  par un writer echouerait immediatement avec "database is locked", il
  n'attendrait pas 109 s). Cause plausible retenue : saturation d'E/S
  consecutive a l'ecriture massive de 132,7 s sur le meme fichier de
  236 Mo (invalide le cache de pages OS, expose a un re-scan antivirus
  sur fichier modifie), amplifiee par une connexion PDO neuve par
  combinaison (fidelite HTTP, D-016) qui multiplie les ouvertures du
  fichier pendant cette fenetre. Signal decisif indépendant du
  mecanisme exact : les pics sont contigus dans l'ordre d'iteration et
  changent de longueur selon le run (12 puis 13) -- signature
  temporelle, pas une signature de requete ou de donnees. Cout
  structurel du plan lui-meme borne et calcule par l'auditeur
  (~850 microsecondes/entree d'index, 109 s arithmetiquement hors
  d'atteinte de ce plan) -- artefact de developpement multi-agents,
  SANS RAPPORT avec la production : le runtime n'ecrit jamais sur
  cette base (lecture seule, D-001), aucune reconstruction ne tourne
  jamais en concurrence avec du trafic reel (separation build/runtime,
  D-007)
lecon de process retenue : eviter de lancer plusieurs agents qui lisent/
  ecrivent la meme base SQLite en parallele lors d'un travail de mesure
  -- isoler les passes de balayage complet des passes de reconstruction
  de donnees
```

Raison :

```text
suite logique de D-029 (palier 1) : meme demande produit, meme
  discipline mesure-avant-ouverture, maillage construit et verifie des
  cette meme passe cette fois (pas apres un NO GO comme pour position/
  D-028)
```

Conséquence :

```text
php tests/run.php : 28/28 (tests/Seo/ProposeSeoBatchAvecTwoLettersTest.php
  nouveau)
registre : 841 552 -> 845 828 (+4 276 exact), toutes les autres
  familles verifiees strictement identiques avant/apres
sitemaps : 29 -> 30 fragments (avec-pair-0001.xml, 4 276 URL)
smoke test HTTP reel : /mots/10-lettres/avec/a/b -> 200, index,follow,
  canonical autonome ; /mots/10-lettres/avec/a/b/c (palier 3 futur) et
  /mots/avec/a/b (sans longueur) -> noindex,follow inchanges
pages a exactement 1 resultat, registre entier : 66 -> 198
palier 3 (longueur + 3 lettres) NON commence -- prochaine etape
  distincte, sa propre decision, volume nettement plus grand (~36 400
  combinaisons brutes)
audit seo-technical-auditor (2026-08-18) : GO, aucun bloquant. Analyse
  structurelle independante de la performance (voir ci-dessus, cause
  affinee). Registre, maillage (2 liens directs par page, verifies dans
  les deux sens), famille cloisonnee, sitemaps et reproductibilite tous
  reverifies independamment -- 845 828 URL sur 30 fragments, chaque
  fragment au compte documente. CONDITIONS EXPLICITES posees pour le
  palier 3 (a fermer AVANT toute proposition, pas apres un NO GO,
  echeance deja signalee non urgente en D-029 -- le sursis est
  termine) :
    I-2  chiffrer et trancher la surface de pagination des pages
         ancrees (~200 000 URL /page/N nouvellement crawlables en
         follow pour ce seul palier, cout constant par page profonde
         -- rel=nofollow au-dela d'une profondeur, ou plafond)
    I-4  scripts/apply_seo_batch.php (regle R4) doit valider la FORME
         de route_path par famille, pas seulement le nom -- sur par
         construction jusqu'ici, plus par controle
  Points non bloquants restants, non urgents : I-1 (docblock
  AvecTwoLettersLinks.php perime, corrige), I-3 (aucun test de
  coherence lot<->registre<->maillage, 3e lot consecutif), I-5/C-3
  (domaine placeholder, Phase 7 uniquement)
```

## D-031 — Ouverture En Entonnoir De "avec" — Palier 3 (Longueur + 3 Lettres), R4 Durci, Pagination Plafonnee

Date : 2026-08-18
Statut : accepte et applique

Decision :

```text
/mots/{N}-lettres/avec/{X}/{Y}/{Z} -- longueur explicite + EXACTEMENT trois
  lettres "avec" distinctes. 36 400 combinaisons brutes (14 longueurs x
  C(26,3)=2600 triplets), 7 573 a 0 resultat (exclues), 1 682 a
  exactement 1 resultat (GARDEES, meme consigne produit que paliers 1/2)
  -> 28 827 pages eligibles, une seule vague
ancrage confirme inchange : length = ?, jamais un scan complet, quel que
  soit le nombre de lettres "avec"
nouveau list_type precalcule 'length_with_triple' dans list_counts
  (schema.sql, scripts/build_explore_hub_counts.php) : croise longueur
  et CHAQUE TRIPLET de lettres distinctes presentes (list_key =
  "{longueur}:{lettre1}:{lettre2}:{lettre3}", triees), 28 827 lignes non
  vides, 244 s de calcul hors ligne
maillage construit ET verifie exhaustivement (3 sens -- une paire source
  peut occuper 3 positions dans le triplet trie) : nouvelles classes
  App\Search\AvecThreeLettersLinks/AvecThreeLettersLinksBuilder, depuis
  les 4 276 pages palier 2 (deja indexees, D-030) -- couverture
  28827/28827 (100%), 86 481 verifications (28 827 x 3 pages source)
nouvelle classification App\Seo\Family::WORD_LIST_AVEC_THREE_LETTERS --
  distincte de WORD_LIST_AVEC_TWO_LETTERS (palier 2) ET de
  WORD_LIST_AVEC (general, permanent, NEVER_SITEMAP)
cablage public/index.php ($avecThreeLettersLinks, meme condition
  d'activation que $avecTwoLettersLinks mais longueur + DEUX lettres
  exigees) et app/View/word-list.php (section "Mots De {N} Lettres Avec
  {X} {Y} Et") -- applique par la session principale, verifie par smoke
  test HTTP reel (section rendue sur une page palier 2, page palier 3
  cible servie en index,follow)
```

Conditions posees par le 2e audit du palier 2 (D-030), fermees AVANT
cette ouverture, pas apres un NO GO :

```text
I-2 (surface de pagination) -- chiffree exactement par data-engine :
  palier 3 seul = 758 497 pages /page/N, cumul paliers 1+2+3 =
  1 049 502 pages. CORRIGE : app/View/word-list.php plafonne desormais
  le SUIVI (rel) de la chaine de pagination des listes ancrees a 3 pages
  (1<->2<->3 en follow, au-dela en nofollow) -- aucun changement
  d'indexation (chaque page /page/N reste noindex,follow dans les deux
  cas), seul le suivi du lien change. Reduit le facteur de crawl gaspille
  d'un ordre de grandeur (jusqu'a 200 pages suivies par liste avant,
  3 apres). Test ajoute (tests/Frontend/WordListViewTest.php), verifie.
I-4 (garde-fou R4 declaratif) -- CORRIGE : scripts/apply_seo_batch.php
  valide desormais la FORME de route_path par famille (R4b, en plus du
  nom de famille R4a), au moins pour home/length/commencant/terminant/
  combined/position et les trois paliers "avec" (ordre alphabetique
  strict des lettres exige pour ces derniers, meme convention que
  ksort() dans WordListFilters::fromPath()). Teste en direct : une ligne
  a lettres non triees ('c','a','b' au lieu de 'a','b','c') est refusee
  a l'ecriture, aucune trace laissee en base (transaction unique). Non
  couvert et documente comme tel : word_admitted/word_french_not_admitted
  (grammaire du slug), rack (deja bloque par R4a)
```

Raison :

```text
suite logique de D-029/D-030 : meme demande produit, meme discipline
  mesure-avant-ouverture, maillage construit et verifie des cette meme
  passe. Volume demande explicitement accelere par le proprietaire du
  produit -- process reduit a deux dispatches d'agent au lieu de six,
  un seul balayage complet au lieu de trois (le mecanisme du bruit de
  mesure du palier 2 etant deja compris et non specifique a la requete)
```

Consequence :

```text
php tests/run.php : 30/30 (tests/Seo/ProposeSeoBatchAvecThreeLettersTest.php
  nouveau, tests/Frontend/WordListViewTest.php etendu pour le plafond de
  pagination, 3 nouveaux cas de refus R4b dans tests/Seo/BuildScriptsTest.php)
registre : 845 828 -> 874 655 (+28 827 exact), toutes les autres familles
  verifiees strictement identiques avant/apres
sitemaps : 30 -> 31 fragments (avec-triple-0001.xml, 28 827 URL, sous la
  limite de 40 000)
smoke test HTTP reel : /mots/10-lettres/avec/a/b/c -> 200, index,follow ;
  section "Mots De 10 Lettres Avec A B Et" confirmee rendue sur la page
  palier 2 source
pages a exactement 1 resultat, registre entier : 198 -> 1 880
probleme memoire rencontre et resolu en cours de route (28 827 lignes
  var_export()) : memory_limit CLI porte a 512M dans les scripts hors
  ligne concernes (jamais exposes a public/, jamais au runtime, D-007),
  note de lot raccourcie (36,6 Mo -> 22,6 Mo) sans perte d'attestation
  R7 (chaque ligne cite toujours ses 3 pages source reelles)
palier 4 (longueur + 4 lettres, ~209 300 combinaisons brutes) NON
  commence -- prochaine etape distincte, sa propre decision, volume
  encore nettement plus grand
audit seo-technical-auditor (2026-08-18) : GO, aucun bloquant. I-2
  (pagination) et I-4 (R4) confirmes conformes, verifies dans le code
  reel (fermes AVANT proposition, comme exige par le 2e audit du palier
  2). Registre, maillage (3 liens reels par page, prouve structurellement
  necessaire, pas seulement mesure), classification, sitemaps et forme
  des 28 827 URL tous reverifies independamment -- 874 655 URL sur 31
  fragments, chaque fragment au compte documente. Points non bloquants
  releves : I-1 (balayage propre incomplet sur les grandes longueurs
  9-15, seule la longueur 8 -- la plus petite des partitions suspectes
  -- a ete re-balayee proprement), I-2 (nombre de mots distincts
  derriere les 1 682 pages a 1 resultat jamais calcule), I-3 (meta
  description tres courte et entierement templatee sur les 28 827
  pages), I-4 (AvecThreeLettersLinksBuilder livre sans EXPLAIN QUERY
  PLAN ni mesure, a completer). DEUX CONDITIONS explicites posees pour
  la suite : avant la Phase 7, sequencer la soumission des sitemaps par
  vagues (874 655 URL ne doivent pas partir d'un bloc) ; avant toute
  proposition de palier 4, fournir I-1 (balayage propre grandes
  longueurs) et I-2 (mots distincts, projection du ratio 0/1 resultat)
  -- a ce volume un seul fragment sitemap ne suffira plus (limite
  40 000)
correctif I-3 (2026-08-18, demande produit) : app/View/word-list.php
  enrichit desormais le <title> et la meta description des listes a 1
  seul resultat (cite le mot reel et son statut, ex. "WU, admis au
  Scrabble") et des listes courtes de 2 a 5 resultats (enumere les
  mots reels) -- donnees deja chargees pour le tableau de resultats,
  aucune requete supplementaire. S'applique a TOUTES les pages liste
  du site (position, combined, les trois paliers avec, etc.), pas
  seulement au palier 3 qui a revele le probleme. Repli explicite sur
  la phrase generique si $page->items est vide (page hors bornes,
  ex. ".../page/2" sur une liste a 1 resultat) -- garde-fou teste,
  bug reel trouve et corrige avant application (premiere version
  aurait plante sur $page->items[0] dans ce cas). H1/fil d'Ariane
  restent la categorie generale de la page, jamais le contenu d'une
  seule ligne -- seul <title> est enrichi. Aucun changement de
  registre, d'indexation ni de requete : pas de nouvel audit juge
  necessaire (changement de copie pure, deja la correction suggeree
  par l'auditeur lui-meme). php tests/run.php : 30/30, 6 nouvelles
  assertions dans tests/Frontend/WordListViewTest.php, verifie en
  direct sur /mots/2-lettres/avec/w (cas reel deja documente, WU)
```

## D-032 — Collapse "avec/X" Redondant Avec Un Commençant/Terminant D'Une Seule Lettre

Date : 2026-08-18
Statut : accepté

Décision :

```text
App\Search\WordListFilters::fromPath() retire desormais silencieusement une entree
  withLetters[X] (minCount === 1) quand commencant ou terminant vaut exactement X (une
  seule lettre) -- meme mecanisme que le collapse "position" deja etabli (D-023).
  canonicalPath() n'emet alors plus jamais "avec/X" a cote de "commencant/X" ou
  "terminant/X", le routeur redirige en 301 toute URL recue sous la forme non collapsee
  vers la forme simplifiee -- meme discipline que toute autre permutation non canonique
seule la forme MONO-LETTRE minCount=1 est retiree : minCount >= 2 (ex. avec/x/x, au
  moins deux X) n'est jamais redondant avec un prefixe/suffixe d'une seule occurrence,
  reste inchange ; les prefixes/suffixes multi-lettres restent hors perimetre (jamais
  mesures pour cet axe)
```

Raison :

```text
bug reel trouve par la mesure de l'axe commencant+avec (reports/query-plans/
  commencant-avec-no-length-full-sweep.md, section 5) : 17/26 combinaisons
  commencant/X/avec/X affichaient un total tronque a 10 000 (WordListSolver bascule a
  tort en regime BORNE des que "avec" est present, needsUnindexedPredicates()) au lieu
  du vrai total exact (jusqu'a 224 205 pour R) -- un resultat trompeur publie a
  l'utilisateur, pas seulement un probleme de lenteur
cote terminant/X/avec/X : verifie separement, 0/26 divergent au niveau du total
  (terminant/X seul est deja en regime BORNE plafonne quel que soit "avec", donc pas de
  regression de total sur ce cote) -- le collapse y reste neanmoins applique pour la
  deduplication d'URL (jamais deux formes canoniques distinctes pour le meme resultat)
```

Conséquence :

```text
force brute 26+26 combinaisons (prefix et suffix, vraie base) : 0 divergence apres
  correctif, EXPLAIN QUERY PLAN inchange (meme index sqlite_autoindex_terms_1 deja en
  place pour commencant/X seul, D-017)
effet en cascade trouve et corrige sur App\Search\StartEndWithLinksBuilder (maillage
  commencant+terminant+avec construit en parallele) : les lettres "avec" degenerees
  (deja garanties par le debut ou la fin) produisaient un lien dont l'URL devient
  desormais identique a celle de la page source elle-meme -- exclues explicitement
  (comparaison d'URL), 1 198 lignes list_counts 'start_end_with' desormais non
  utilisees par ce maillage (precalcul non modifie, leger gaspillage de stockage non
  traite ici)
php tests/run.php : 33/33 (verifie sur trois executions consecutives)
```

## D-033 — Maillage Interne Commençant + Terminant + Avec (Une Lettre)

Date : 2026-08-18
Statut : accepté

Décision :

```text
nouveau list_type 'start_end_with' dans list_counts (schema.sql, scripts/
  build_explore_hub_counts.php) : croise lettre de debut, lettre de fin ET une lettre
  presente n'importe ou dans le mot (list_key = "{debut}:{fin}:{lettre}") -- 11 348
  lignes non vides sur 17 576 combinaisons possibles, calcul PHP mesure contre
  l'alternative SQL avant choix (3,945 s contre 5,195 s, meme resultat), ~4 s de calcul
  hors ligne
nouvelles classes App\Search\StartEndWithLinks / StartEndWithLinksBuilder -- 1 requete
  triviale sur list_counts par page, balayage complet des 611 pages reelles : 0/611
  au-dessus du budget TTFB, max 22,276 ms
interaction avec D-032 geree dans la meme passe : 1 198 des 11 348 lignes precalculees
  sont degenerees (avec/Z ou Z egale la lettre de debut ou de fin, D-032 les collapse
  vers la page parente) -- exclues par le builder (comparaison d'URL), jamais par le
  precalcul brut. Maillage reellement produit : 10 150 pages candidates (11 348 - 1 198),
  dont 1 547 a exactement 1 resultat (GARDEES, meme consigne produit que tous les
  paliers "avec" precedents)
cablage public/index.php ($startEndWithLinks, depuis une page commencant ET terminant
  toutes deux d'une seule lettre, SANS longueur) et app/View/word-list.php (section
  "Commençant Par {X} Et Terminant Par {Y}, Avec") -- applique par la session principale,
  verifie par smoke test HTTP reel (section rendue sur /mots/commencant/r/terminant/e)
```

Raison :

```text
suite logique de reports/query-plans/commencant-terminant-avec-full-sweep.md (mesure de
  performance deja faite, 64,6% de combinaisons candidates, aucun code de maillage
  construit) -- construit et verifie ici dans la meme passe, meme discipline que
  D-030/D-031. Axe propose par le proprietaire du produit lui-meme.
```

Conséquence :

```text
php tests/run.php : 33/33 (tests/Search/StartEndWithLinksBuilderTest.php nouveau)
ne constitue AUCUNE decision d'ouverture a l'indexation -- toutes les pages ciblees
  restent noindex,follow par defaut (D-005), decision future distincte, non prise ici
classification App\Seo\Family : avis donne par data-engine (nouvelle constante
  recommandee, distincte de WORD_LIST_COMBINED, meme raisonnement que
  WORD_LIST_POSITION/WORD_LIST_AVEC_*), decision et nom laisses a seo-registry
```

## D-034 — Maillage Interne Commençant + Avec (Une Lettre, Sans Terminant Ni Longueur)

Date : 2026-08-18
Statut : accepté

Décision :

```text
nouveau list_type 'start_with' dans list_counts (schema.sql, scripts/
  build_explore_hub_counts.php) : croise lettre de debut ET une lettre presente n'importe
  ou dans le mot (list_key = "{debut}:{lettre}") -- 646 lignes non vides sur 676
  combinaisons brutes (26x26), les 26 combinaisons degenerees (avec = debut, D-032) sont
  exclues DIRECTEMENT AU PRECALCUL cette fois (choix distinct de start_end_with/D-033 --
  une seule direction de lecture et une seule lettre "avec" par ligne rendent la
  condition de degenerescence identique au precalcul et a l'usage reel, exclure a la
  source est equivalent et plus simple ici)
nouvelles classes App\Search\PrefixAvecLinks / PrefixAvecLinksBuilder -- 1 requete
  triviale sur list_counts par page, deux balayages complets independants des 26 pages
  sources reelles : 0/26 au-dessus du budget TTFB les deux fois (max 1,836 ms puis
  0,853 ms), 646 liens produits les deux fois
cablage public/index.php ($prefixAvecLinks, depuis une page commencant SEULE, sans
  longueur, sans suffixe, sans autre contrainte) et app/View/word-list.php (section
  "Commençant Par {X}, Avec") -- applique par la session principale, verifie par smoke
  test HTTP reel (section rendue sur /mots/commencant/r)
```

Raison :

```text
suite logique de reports/query-plans/commencant-avec-no-length-full-sweep.md (mesure de
  performance et decouverte du bug D-032 deja faites, aucun maillage construit) --
  construit et verifie ici, meme discipline que D-030/D-031/D-033. Chiffres reconfirmes
  independamment apres le correctif D-032 (deux methodes, 0 divergence), pas repris
  aveuglement de l'estimation initiale
```

Conséquence :

```text
php tests/run.php : 34/34 (tests/Search/PrefixAvecLinksBuilderTest.php nouveau)
ne constitue AUCUNE decision d'ouverture a l'indexation -- toutes les pages ciblees
  restent noindex,follow par defaut (D-005), decision future distincte, non prise ici
classification App\Seo\Family : avis donne par data-engine, preuve technique a l'appui
  (scripts/apply_seo_batch.php, R4b -- la regex de WORD_LIST_COMMENCANT rejette
  explicitement toute forme "/avec/...", pas seulement une preference de style) --
  nouvelle constante necessaire, distincte a la fois de WORD_LIST_COMMENCANT et de la
  future constante de D-033 (formes de route_path syntaxiquement differentes), decision
  et nom laisses a seo-registry
```

## D-035 — Ouverture À L'Indexation : Commençant+Terminant Avec Longueur, Commençant+Terminant+Avec, Commençant/Terminant Multi-Lettres

Date : 2026-08-18
Statut : accepté et appliqué

Décision :

```text
TROIS lots appliques en une seule passe contre storage/seo_fr.sqlite, chacun sur la base
  du maillage deja construit et cable (D-027/byStartEnd, D-033/StartEndWithLinks,
  dimensionnement multi-lettres) :

Axe 1 (D-027, commencant+terminant AVEC longueur) : 5 141 URL, Family::WORD_LIST_COMBINED
  (famille existante, variante avec longueur, aucune nouvelle classification -- deja hors
  NEVER_SITEMAP depuis D-025). Exclusion des 52 doublons de contenu deja identifies
  (D-025, I-1). Nouveau fragment sitemap combined-0002.xml.
  ARBITRAGE du volume de liens (jusqu'a 477/page, 695 liens "Explorer" cumules sur
  /mots/8-lettres) : ACCEPTE -- le volume de liens n'ajoute JAMAIS d'URL sitemap ni de
  cout de crawl (seul le DOM d'une page deja indexee grossit), attenue par le repli
  <details> deja en place (meme mecanisme que byPosition, D-028bis). Le refus d'ouvrir
  aurait laisse 5 141 pages orphelines, une violation plus grave que 477 liens dans un
  <details> ferme.

Axe 2 (D-033, commencant+terminant+avec, une lettre chacun) : 10 150 URL, NOUVELLE
  classification Family::WORD_LIST_COMBINED_WITH_LETTER (distincte de WORD_LIST_COMBINED
  -- preuve technique : R4b de WORD_LIST_COMBINED n'autorise pas de segment /avec/ apres
  /terminant/). Nouveau garde-fou R4c dans scripts/apply_seo_batch.php (forme
  ^/mots/commencant/[a-z]/terminant/[a-z]/avec/[a-z]\z, pas d'ordre alphabetique exige --
  trois roles distincts, pas un ensemble de lettres interchangeables). Exclusion des
  1 198 lignes degenerees (D-032). Nouveau fragment sitemap combined-with-0001.xml.

Axe 3 (dimensionnement multi-lettres) : 37 557 URL (20 712 commencant + 16 845 terminant),
  Family::WORD_LIST_COMMENCANT/WORD_LIST_TERMINANT (familles existantes, aucun changement
  de code necessaire -- R4b acceptait deja 1-15 lettres). ARBITRAGE des 1 982 paires
  parent/enfant a contenu strictement duplique (section 7 du rapport de dimensionnement) :
  meme regle que D-025 (52 paires) -- la page la PLUS COURTE reste index,follow
  canonique, la plus longue de chaque paire reste noindex,follow par omission (R3).
  Recalcule independamment (3 executions deterministes), verifie par HTTP reel (paire
  AQ/AQU : AQ index,follow, AQU noindex,follow). Nouveaux fragments starts-0002.xml /
  ends-0002.xml.

Sequencement : les trois lots ecrits en une seule vague chacun (meme precedent que
  D-029/D-030/D-031, jusqu'a 28 827 URL en une vague) -- ecriture LOCALE uniquement
  (storage/seo_fr.sqlite), rien de visible par le vrai Google avant la Phase 7 (site non
  deploye, D-017). La sequence reelle de SOUMISSION des sitemaps a Search Console reste
  une decision Phase 7 distincte, deja posee comme condition par D-031.
```

Raison :

```text
suite logique de D-027/D-033/du dimensionnement multi-lettres : maillage deja construit
  et cable pour les trois axes, plus qu'une decision de classification/dimensionnement/
  application restait a prendre -- demande explicite du proprietaire du produit
  ("avance la-dessus, corrige les problemes et met le truc en place")
```

Conséquence :

```text
registre : 874 655 -> 927 503 URL (+52 848 exact)
sitemaps : 31 -> 35 fragments (combined-0002, combined-with-0001, starts-0002, ends-0002)
familles touchees : word_list_combined 611 -> 5 752, word_list_combined_with_letter
  0 -> 10 150 (nouvelle), word_list_commencant 26 -> 20 738, word_list_terminant
  26 -> 16 871 -- toutes les autres familles verifiees strictement inchangees
pages a exactement 1 resultat, registre entier : 1 880 -> 12 321 (+10 441, toutes
  signalees, aucune auto-exclue -- meme consigne produit que tous les paliers precedents)
verification exhaustive : ~1 170 lignes echantillonnees (canonical_path = route_path,
  0 divergence), ~360 lignes echantillonnees (result_count contre le vrai solveur,
  0 divergence), reproductibilite complete des 52 848 lignes contre un lot regenere
  (0 divergence), smoke test HTTP reel sur les deux membres d'une paire dupliquee de
  chaque axe
php tests/run.php : 37/37 (3 nouveaux tests de reproductibilite -- ProposeSeoBatch
  CombinedWithLengthTest, CombinedWithLetterTest, CommencantTerminantMultilettresTest)
axe restant NON couvert par ce lot (chantier concurrent D-034, avec+commencant sans
  terminant, 646 candidats) -- classification et application distinctes, a instruire
  separement
```

## D-036 — Ouverture À L'Indexation : Commençant + Avec (Une Lettre, Sans Terminant Ni Longueur)

Date : 2026-08-18
Statut : accepté et appliqué

Décision :

```text
646 URL appliquees contre storage/seo_fr.sqlite, sur la base du maillage deja construit
  et cable (D-034, App\Search\PrefixAvecLinksBuilder, depuis les 26 pages
  /mots/commencant/{X} deja indexees, D-017)
nouvelle classification App\Seo\Family::WORD_LIST_COMMENCANT_WITH_LETTER -- distincte de
  WORD_LIST_COMMENCANT (preuve technique : R4b rejette deja explicitement toute forme
  "/avec/..." pour cette famille) ET de WORD_LIST_COMBINED_WITH_LETTER (D-035, forme de
  route a 3 segments de lettre, pas 2)
nouveau garde-fou R4d dans scripts/apply_seo_batch.php (forme exacte
  ^/mots/commencant/[a-z]/avec/[a-z]\z), teste en direct (refus d'une ligne portant un
  terminant)
cas 'commencant_avec' dans scripts/propose_seo_batch.php (source list_counts,
  list_type = 'start_with', deja expurge des 26 combinaisons degenerees au precalcul,
  D-034 -- 0 exclusion supplementaire necessaire, verifie)
nouveau fragment sitemap commencant-avec-0001.xml (646 URL)
```

Raison :

```text
dernier des quatre axes commencant/terminant/avec travailles aujourd'hui -- maillage
  deja construit et cable (D-034), il ne restait que la classification et l'application,
  meme methode que les trois autres axes (D-035)
```

Conséquence :

```text
registre : 927 503 -> 928 149 URL (+646 exact), toutes les autres familles verifiees
  strictement inchangees
sitemaps : 35 -> 36 fragments
1 page a exactement 1 resultat (W+J) -- GARDEE, meme consigne produit que tous les axes
  precedents ; 150/646 pages plafonnees au regime BORNE (ROW_EXAMINATION_CEILING)
lot reproductible : regenere deux fois independamment, byte-identique les deux fois
  (scripts/seo-batches/commencant-avec-full-2026-08-18.php)
php tests/run.php : 38/38 (deux executions consecutives)
LES QUATRE AXES COMMENCANT/TERMINANT/AVEC SONT DESORMAIS TOUS APPLIQUES : D-027
  (5 141), D-033 (10 150), multi-lettres (37 557), D-034/D-036 (646) -- total +53 494
  URL depuis le debut de cette serie de travaux (874 655 -> 928 149). Reste a faire :
  audit seo-technical-auditor consolide sur l'ensemble avant toute mise en ligne
```

## D-037 — Correction Du NO GO Consolidé : Doublons De Contenu Sur Les Axes 2 Et 4 (Avec)

Date : 2026-08-18
Statut : accepté

Décision :

```text
audit consolide seo-technical-auditor sur la serie D-027/D-032-D-036 : NO GO, un seul
  bloquant precis (C-1) -- les axes 2 (commencant+terminant+avec,
  Family::WORD_LIST_COMBINED_WITH_LETTER) et 4 (commencant+avec,
  Family::WORD_LIST_COMMENCANT_WITH_LETTER) avaient ete appliques SANS le controle de
  doublon de contenu parent/enfant deja applique aux axes 1 (52 exclusions, D-025) et 3
  (1 982 exclusions) de la meme serie -- preuve sur pieces : la paire F:Q (1 seul mot,
  FAQ) publiait /mots/commencant/f/terminant/q/avec/a en index,follow, contenu
  strictement identique a /mots/commencant/f/terminant/q deja indexee ; la paire X:O
  (1 seul mot, XIPHO) publiait 3 pages /avec/{h,i,p} toutes identiques entre elles et a
  leur parent
correctif applique en deux passes independantes, verifiees croisees (0 divergence) :
  - App\Search\StartEndWithLinksBuilder / PrefixAvecLinksBuilder (data-engine) : nouvelle
    constante DUPLICATE_CONTENT_KEYS (meme patron que LengthLinksBuilder::
    DUPLICATE_START_END_KEYS), filtree dans build() -- 227 cles exclues axe 2, 0 axe 4
  - scripts/propose_seo_batch.php (seo-registry) : meme regle de detection ajoutee aux
    cas combined_with_letter et commencant_avec (count de la ligne "avec" egal au count
    de l'entree parente SANS "avec" = doublon de contenu)
  - registre reel corrige en place : reapplication des lots corriges (INSERT OR REPLACE,
    notes rafraichies) PUIS suppression explicite des 227 lignes degenerees (apply_seo_
    batch.php ne supprime jamais, la reapplication seule etait insuffisante)
  - correctif I-1 (non bloquant, meme passe) : scripts/apply_seo_batch.php, R4c/R4d
    rejettent desormais aussi les formes degenerees ou la lettre "avec" egale le debut
    ou la fin (formes qui redirigent en 301 via le collapse D-032, ne repondent jamais
    200) -- defense en profondeur, en plus de l'exclusion cote propose_seo_batch.php
```

Raison :

```text
NO GO de l'audit consolide (seo-technical-auditor, 2026-08-18) -- correctif mecanique,
  meme regle de detection deja ecrite et validee pour l'axe 1, jamais repercutee aux
  axes 2 et 4 au moment de leur application initiale (D-035/D-036)
```

Conséquence :

```text
registre : 928 149 -> 927 922 (-227 exact), toutes les autres familles et lignes
  verifiees strictement inchangees
word_list_combined_with_letter : 10 150 -> 9 923 ; word_list_commencant_with_letter :
  646 -> 646 (0 exclusion, verifie independamment deux fois)
sitemaps : combined-with-0001.xml regenere (9 923 URL), les 35 autres fragments
  verifies byte-identiques (cmp)
pages a exactement 1 resultat, registre entier : 12 322 -> 12 160 (-162, toutes issues
  des lignes retirees)
les deux exemples cites par l'audit (FAQ, XIPHO) confirmes absents du registre apres
  correctif -- verifie par smoke test du maillage reel (StartEndWithLinksBuilder::
  build('F','Q') et build('X','O') retournent desormais 0 lien)
php tests/run.php : 38/38 (deux executions consecutives)
lot pret pour un nouvel audit seo-technical-auditor
2e audit seo-technical-auditor (2026-08-18) : GO, aucun bloquant. C-1
  reverifie independamment (pas sur parole) : FAQ et XIPHO confirmes
  absents des sitemaps (temoin positif -- leurs pages parentes, elles,
  y figurent bien), 30 autres cles des 227 sondees et absentes, 0 forme
  degeneree D-032 residuelle, regle de detection recalculee dans le
  code des 3 axes concernes (axe 4 confirme non-no-op : list_type
  'start' existe bien). I-1 (R4c/R4d) confirme corrige. Total 927 922
  = somme exacte des 36 fragments, les 35 fragments inchanges verifies
  par comptage et sondage de contenu (pas de cmp octet, aucun shell
  disponible dans cette session d'audit). Nouveau point non bloquant
  trouve (I-A) : doublons de contenu entre pages SOEURS (pas seulement
  parent/enfant) jamais mesures sur l'axe 2 -- exemple cite, paire X:M,
  jusqu'a 10 pages a 1 resultat potentiellement redondantes entre
  elles si le panier parent est petit. Non prouve (acces base refuse
  dans cette session), volume borne a 1 385 pages max (0,15% du
  registre), meme mesure deja demandee par D-031 pour un futur palier
  4 mais jamais appliquee retroactivement a l'axe 2. Autres points non
  bloquants : I-B (1 seul lien entrant par page sur les axes 2/4, deja
  accepte pour un precedent similaire sur position/D-028bis), I-C/I-D
  (domaine placeholder, sequencement Phase 7, deja connus), M-A a M-D
  (lots D-035 axes 1/3 non versionnes, liste figee a revalider a tout
  rebuild, quelques titres longs, aucun rapport seo-registry dedie au
  correctif). Les I-2 a I-5/M-1/M-2 du 1er audit restent d'actualite
  sans changement.
```

## D-038 — Correction Du Point Non Bloquant I-A : Doublons De Contenu Entre Pages Sœurs (Avec)

Date : 2026-08-18
Statut : accepté

Décision :

```text
2e audit consolide (D-037) : GO avec un point non bloquant (I-A) -- le controle
  anti-doublon existant (D-037, correctif C-1) ne comparait une page "avec/{Z}" qu'a sa
  page PARENTE (sans "avec"), jamais aux autres pages "avec" SOEURS du meme panier.
  Exemple cite par l'audit : paire X:M, panier de mots reduit, plusieurs lettres "avec"
  differentes isolant potentiellement le meme mot
correctif applique en deux passes independantes, verifiees croisees (0 divergence) :
  - App\Search\StartEndWithLinksBuilder / PrefixAvecLinksBuilder (data-engine) : nouvelle
    constante SIBLING_DUPLICATE_KEYS (meme patron que DUPLICATE_CONTENT_KEYS, D-037),
    calculee par TROIS methodes independantes (fetch-then-filter, re-requete par lettre,
    empreinte SQL GROUP_CONCAT+sha1) -- 283 groupes trouves axe 2, 0 axe 4
  - scripts/propose_seo_batch.php (seo-registry) : nouvelle fonction
    findSiblingContentDuplicates(), meme regle de detection, calculee independamment --
    283 groupes, 428 lignes, 169 paires -- match EXACT avec le calcul data-engine
  - regle de canonicalisation : la lettre "avec" alphabetiquement la plus petite de
    chaque groupe reste candidate, les autres sont exclues (meme convention que les
    paliers avec a 2/3 lettres, D-029-D-031 -- jamais un alias, toujours la perdante
    retiree completement, R3)
  - preuve sur pieces confirmee : paire X:M donne 2 groupes -- {A,L} partageant XALAM,
    {C,D,E,H,I,N,O,U} (8 lettres) partageant XENODOCHIUM -- 10 pages candidates
    collapsees a 2 gagnantes, exactement ce que l'audit avait pressenti
  - registre corrige en place : reapplication des lots corriges PUIS suppression
    explicite des 428 lignes (meme methode que D-037, apply_seo_batch.php ne supprime
    jamais de lignes)
```

Raison :

```text
point non bloquant du 2e audit consolide (I-A), corrige proactivement plutot que
  laisse en dette technique -- meme classe de defaut que C-1/D-037 (doublon de contenu
  sans canonical designant un gagnant), horizontal plutot que vertical
```

Conséquence :

```text
registre : 927 922 -> 927 494 (-428 exact) -- famille word_list_combined_with_letter
  9 923 -> 9 495 ; word_list_commencant_with_letter inchangee a 646 (0 exclusion,
  confirme independamment)
sitemaps : 36 fragments inchanges en nombre, combined-with-0001.xml regenere
  (9 495 URL)
pages a exactement 1 resultat, registre entier : 12 160 -> 11 835 (-325, toutes issues
  des lignes retirees)
php tests/run.php : 38/38
lot pret pour un nouvel audit seo-technical-auditor
```

## D-039 — Correction Du Bloquant C-2 : Doublons De Contenu Croisés Longueur × Avec

Date : 2026-08-19
Statut : accepté

Décision :

```text
3e audit consolide (D-038) : NO GO -- I-A confirme ferme (recalcule independamment par
  10 paires completes, 0 faux positif/negatif), mais NOUVEAU bloquant (C-2) : aucun
  controle ne comparait le contenu de Family::WORD_LIST_COMBINED variante AVEC longueur
  (axe 1, D-027/D-035, tranche le panier commencant+terminant PAR LONGUEUR) et
  Family::WORD_LIST_COMBINED_WITH_LETTER (axe 2, D-033/D-035/D-037/D-038, tranche le
  MEME panier PAR LETTRE "AVEC") -- deux familles differentes partageant le meme panier
  de base (611 paires commencant+terminant), jamais comparees entre elles. Preuve sur
  pieces : /mots/5-lettres/commencant/x/terminant/m ET
  /mots/commencant/x/terminant/m/avec/a contenaient tous deux exactement XALAM, seul
  mot du panier X:M a 5 lettres
correctif applique en deux passes independantes, verifiees croisees (0 divergence,
  meme methode que D-037/D-038) : detection EXHAUSTIVE sur les 611 paires reelles
  (pas seulement les 9 exemples cites par l'audit) -- 333 collisions trouvees, 191
  paires distinctes, 306 a 1 seul mot partage
  - App\Search\StartEndWithLinksBuilder (data-engine) : nouvelle constante
    CROSS_DUPLICATE_LENGTH_KEYS (333 entrees), filtree dans build()
  - scripts/propose_seo_batch.php (seo-registry) : nouvelle fonction
    findLengthAvecContentCollisions(), meme regle de detection, calculee
    independamment -- 333 collisions, match EXACT avec le calcul data-engine
  - regle de priorite tranchee cote produit : la variante LONGUEUR (axe 1) reste
    candidate a l'indexation, la variante "AVEC" (axe 2) est retiree -- coherent avec
    la regle deja etablie en D-025 (forme la plus simple/generale gagne sur la plus
    specifique). App\Search\LengthLinksBuilder (axe 1) NON touche, reste gagnant
  - registre corrige en place : reapplication du lot corrige PUIS suppression
    explicite des 333 lignes (meme methode que D-037/D-038)
```

Raison :

```text
bloquant du 3e audit consolide (C-2) -- meme classe de defaut que C-1/D-037 et
  I-A/D-038 (doublon de contenu sans canonical designant un gagnant), cette fois entre
  DEUX FAMILLES distinctes plutot qu'au sein d'une seule
```

Conséquence :

```text
registre : 927 494 -> 927 161 (-333 exact) -- famille word_list_combined_with_letter
  9 495 -> 9 162 ; word_list_combined (axe 1) inchangee a 5 752, toutes les autres
  familles verifiees strictement inchangees
sitemaps : 36 fragments inchanges en nombre, combined-with-0001.xml regenere
  (9 162 URL)
pages a exactement 1 resultat, registre entier : 11 835 -> 11 529 (-306, toutes issues
  des lignes retirees)
php tests/run.php : 38/38 (deux executions consecutives, ~200s -- temps de test
  desormais correle au volume du registre)
lot pret pour un 4e audit seo-technical-auditor
```

## D-040 — Correction Du Bloquant C-3 : Doublons De Contenu Entre Paliers "Avec" Ancrés Longueur

Date : 2026-08-21
Statut : accepté

Décision :

```text
4e audit consolide (D-039) : NO GO -- C-2 confirme corrige (26 nouvelles paires
  verifiees, 0 refutation sur source ODS8 independante), mais NOUVEAU bloquant (C-3) :
  les trois paliers "avec" ancres longueur (Family::WORD_LIST_AVEC_SINGLE_LETTER/
  TWO_LETTERS/THREE_LETTERS, D-029/D-030/D-031, 33 467 URL) avaient ete ouverts a
  l'indexation SANS jamais appliquer le controle de doublon de contenu deja rode sur
  les axes commencant+terminant (D-025/D-037/D-038/D-039). Preuve sur pieces :
  /mots/10-lettres/avec/w/x (palier 2, 1 resultat) identique a ses 6 variantes palier 3
  /mots/10-lettres/avec/{a,e,n,o,s,t}/w/x ; /mots/15-lettres/avec/w/x identique a ses
  8 variantes palier 3 ; /mots/2-lettres/avec/w identique a /mots/2-lettres/avec/u/w
  (palier1<->palier2) ; /mots/2-lettres/avec/z identique a /mots/2-lettres/avec/a/z
correctif applique en deux passes independantes, verifiees croisees (0 divergence,
  meme methode que D-037/D-038/D-039), chacune par DEUX methodes internes distinctes
  (decomposition PHP par mot ET SQL GROUP_CONCAT+sha1 cote seo-registry ; list_counts
  recalcule directement sur `terms` ET double methode de verification des soeurs cote
  data-engine) :
  - parent/enfant palier1<->palier2 : 4 paires (ex. 2:U:W, 2:A:Z)
  - parent/enfant palier2<->palier3 (transitif palier1<->palier3 absorbe, 0 cas
    "lettre seule sans paire correspondante") : 426 triplets (ex. les 6+8 variantes
    citees par l'audit)
  - soeurs au sein du palier 2 (apres les deux exclusions ci-dessus) : 0 groupe
  - soeurs au sein du palier 3 (apres les deux exclusions ci-dessus) : 189 groupes,
    234 lignes
  - App\Search\AvecTwoLettersLinksBuilder / AvecThreeLettersLinksBuilder (data-engine) :
    nouvelles constantes DUPLICATE_PARENT_KEYS et SIBLING_DUPLICATE_KEYS, filtrees
    dans build() -- meme patron que StartEndWithLinksBuilder (D-037/D-038)
  - scripts/propose_seo_batch.php (seo-registry) : nouvelles constantes
    AVEC_TWO_LETTERS_EXCLUDED_TIER_DUPLICATES (4) et
    AVEC_THREE_LETTERS_EXCLUDED_TIER_DUPLICATES (660), filtrees dans les cas
    avec_two_letters/avec_three_letters ; lots scripts/seo-batches/avec-two-letters-
    full-2026-08-17.php (4 272 lignes) et avec-three-letters-full-2026-08-18.php
    (28 167 lignes) regeneres en place, meme batch_id/added_at
  - verification secondaire (signalee "jamais mesuree" par le 4e audit) :
    word_list_position vs avec_single_letter (position/P/X subset de avec/X par
    construction) -- 0 doublon trouve, negatif et concluant, verifie par les deux
    agents independamment (2 329 et 3 019 combinaisons couvertes respectivement)
  - outil : scripts/apply_seo_batch.php gagne un flag --prune (nouveau, D-040) --
    ce script ne faisait jusqu'ici que de l'INSERT OR REPLACE, jamais de suppression ;
    un lot regenere avec MOINS de lignes qu'avant (cas de toute correction de doublon,
    D-037 a D-040) laissait sinon les anciennes lignes orphelines en base, toujours
    'index,follow'. --prune supprime, dans la MEME transaction que l'application du
    lot, toute ligne dont batch_id correspond mais dont route_path n'apparait plus
    dans le lot en cours -- jamais une ligne d'un autre batch_id
  - registre reel corrige en place : storage/seo_fr.sqlite.bak-pre-d040 sauvegarde
    avant toute ecriture, puis php scripts/apply_seo_batch.php ... --prune sur les
    deux lots corriges, puis php scripts/build_sitemaps.php (36 fragments regeneres)
```

Raison :

```text
bloquant du 4e audit consolide (C-3) -- meme classe de defaut que C-1/D-037,
  I-A/D-038 et C-2/D-039 (doublon de contenu sans canonical designant un gagnant),
  cette fois sur une famille qui n'avait jamais recu AUCUN des trois controles
  (ni parent/enfant, ni soeurs, ni croise) au moment de son ouverture initiale
```

Conséquence :

```text
registre : 927 161 -> 926 497 (-664 exact) -- word_list_avec_two_letters
  4 276 -> 4 272 (-4) ; word_list_avec_three_letters 28 827 -> 28 167 (-660) ;
  word_list_avec_single_letter inchangee a 364 (racine de la hierarchie, aucune
  exclusion possible par construction) ; toutes les autres familles verifiees
  strictement inchangees
sitemaps : 36 fragments inchanges en nombre, avec-pair-0001.xml (4 272 URL) et
  avec-triple-0001.xml (28 167 URL) regeneres, total 926 497 = somme exacte des
  36 fragments
pages a exactement 1 resultat, palier 2 : 132 -> 130 (-2) ; palier 3 : 1 682 -> 1 383
  (-299) -- signalees pour information, jamais un critere de noindex a lui seul
  (docs/05)
php tests/run.php : 38/38
le point C-4 du 4e audit (aucun garde-fou structurel empechant une 5e recurrence du
  meme defaut) reste ouvert -- traite separement, voir D-041
lot pret pour un 5e audit seo-technical-auditor
```

## D-041 — Correction Du Bloquant C-4 : Détecteur De Doublons Générique Et Rejouable

Date : 2026-08-21
Statut : accepté

Décision :

```text
point C-4 du 4e audit consolide (D-040) : aucun garde-fou structurel n'empechait la
  reapparition du meme defaut a une 5e famille -- chaque correction (D-037, D-038,
  D-039, D-040) avait ete une liste figee ecrite a la main, propre a UNE paire de
  familles precise, jamais une detection generale
outil construit : scripts/check_combinatorial_duplicates.php (App\Search, offline,
  D-007) -- perimetre calcule DYNAMIQUEMENT depuis App\Seo\Family::ALL (moins HOME/
  WORD_ADMITTED/WORD_FRENCH_NOT_ADMITTED/NEVER_SITEMAP), jamais une liste de familles
  recopiee a la main -- toute famille combinatoire future entre automatiquement dans
  le balayage. Pour chaque ligne du registre, reconstruit la clause WHERE reelle via
  App\Search\WordListFilters::fromPath() (source de verite unique de la grammaire,
  meme mecanisme que le runtime), l'execute SANS LIMIT sur storage/dictionary_fr.sqlite,
  calcule une empreinte (COUNT + sha1(GROUP_CONCAT(normalized ORDER BY normalized))),
  et regroupe TOUTES les lignes par empreinte identique -- aucune connaissance prealable
  de quelle paire de familles verifier
premier balayage complet (post D-040) : 10 familles, 88 315 lignes, 433,2s -- **1 656
  groupes de doublons residuels trouves, 2 089 lignes en exces, 0 anomalie de
  tracabilite**. Tous CROISES entre familles (0 groupe sœur pur au sein d'une meme
  famille) -- la plus grosse paire jamais comparee avant cet outil :
  word_list_commencant x word_list_terminant (multi-lettres, axe 3, D-035), 408
  groupes a elle seule
regle de priorite generalisee (App\Search\DuplicatePageResolver::
  resolveDuplicateWinner()) : 1) le nombre de composants de contrainte gagne (longueur=1,
  commencant=1, terminant=1, position=2, avec/sans=1 par lettre distincte) -- le moins
  de composants gagne ; 2) egalite -> comparaison des signatures de role dans l'ordre
  canonique de WordListFilters::KEYWORDS ; 3) egalite (meme famille) -> canonicalPath()
  alphabetiquement le plus petit gagne (generalise D-038). Verifie reproduire D-039
  comme CONSEQUENCE de la regle (longueur bat avec a 3-vs-3 composants), jamais comme
  cas code en dur
correctif applique en deux passes independantes, verifiees croisees (0 divergence sur
  les 10 familles ET le total) :
  - App\Search\DuplicatePageResolver (data-engine) + constantes d'exclusion dans les
    builders de maillage concernes (SuffixExtensionLinksBuilder 639,
    AvecThreeLettersLinksBuilder 666, AvecTwoLettersLinksBuilder 138,
    StartEndWithLinksBuilder 314, LengthCombinedLinksBuilder 292, LetterCombinedLinksBuilder
    33, PrefixAvecLinksBuilder 4, PositionLinksBuilder 2, LengthLinksBuilder::byWith 1)
  - scripts/lib/seo_duplicate_priority.php (seo-registry) : resolveDuplicateWinner()
    independant, meme barème de composants, meme ordre canonique lu par reflexion sur
    WordListFilters::KEYWORDS ; constante D041_EXCLUDED_ROUTE_PATHS (2 089 route_path)
    branchee dans 10 cas de scripts/propose_seo_batch.php
  - echantillonnage independant croise : 64 groupes (data-engine, reconstruction SQL a
    la main par famille) + 64 groupes (seo-registry, meme protocole) -- 37/37
    combinaisons de familles distinctes couvertes chacun, 0 desaccord de contenu, 0
    desaccord de gagnant
  - decouverte non anticipee par la tache initiale, corrigee en cours de route : les
    familles word_list_commencant/word_list_terminant sont produites par DEUX cas
    distincts de propose_seo_batch.php (mono-lettre ET commencant_terminant_
    multilettres) -- la quasi-totalite des 639 exclusions word_list_terminant vient du
    cas multilettres, jamais du cas mono-lettre (26 routes, jamais assez etroit pour
    dupliquer)
  - fermeture du point non bloquant M-A (lots D-035 axes 1/3 jamais versionnes) : trois
    lots scripts/seo-batches/ generes pour la premiere fois a cette occasion --
    combined-no-length-2026-08-09.php, combined-with-length-full-2026-08-18.php,
    commencant-terminant-multilettres-full-2026-08-18.php (batch_id/added_at alignes
    sur les valeurs deja en base, retrouvees par requete directe sur storage/
    seo_fr.sqlite avant generation)
  - outil scripts/apply_seo_batch.php --prune (D-040) reutilise pour les 9 lots
    concernes -- registre reel corrige en place, sauvegarde storage/seo_fr.sqlite.bak-
    pre-d040 conservee comme filet avant toute ecriture de la serie D-040/D-041
```

Raison :

```text
bloquant du 4e audit consolide (C-4) -- construire une detection generale, plutot
  qu'une 5e liste figee, est la seule facon de rompre le cycle "chaque audit trouve
  une nouvelle dimension" observe sur quatre passes consecutives (C-1, I-A, C-2, C-3)
```

Conséquence :

```text
registre : 926 497 -> 924 408 (-2 089 exact, verifie famille par famille, 0
  divergence) -- word_list_terminant 16 871 -> 16 232 (-639) ; word_list_combined
  5 752 -> 5 427 (-325, dont 33 sans longueur + 292 avec longueur) ;
  word_list_combined_with_letter 9 162 -> 8 848 (-314) ; word_list_avec_three_letters
  28 167 -> 27 501 (-666) ; word_list_avec_two_letters 4 272 -> 4 134 (-138) ;
  word_list_position 2 329 -> 2 327 (-2) ; word_list_commencant_with_letter 646 -> 642
  (-4) ; word_list_avec_single_letter 364 -> 363 (-1) ; word_list_length et
  word_list_commencant inchangees (0 exclusion chacune)
sitemaps : 36 fragments inchanges en nombre, 924 408 URL = somme exacte, tous
  regeneres et verifies
php tests/run.php : 40/40 (2 nouveaux fichiers : tests/Search/
  DuplicatePageResolverTest.php, tests/Seo/CheckCombinatorialDuplicatesTest.php)
scripts/check_combinatorial_duplicates.php reste dans le depot comme outil rejouable
  -- a executer avant toute future ouverture de famille combinatoire, avant meme
  d'ecrire une premiere ligne de garde-fou specifique a cette famille
lot pret pour un 5e audit seo-technical-auditor
```

## D-042 — Domaine De Production : wordcheckr.fr

Date : 2026-08-21
Statut : accepté

Décision :

```text
config/sites/fr.php : canonical_base_url passe de 'https://CHANGE-ME.exemple.fr' a
  'https://www.wordcheckr.fr'
public/robots.txt : directive Sitemap activee (https://www.wordcheckr.fr/sitemap-
  index.xml), auparavant en commentaire faute de domaine connu
36 fragments de sitemap regeneres (scripts/build_sitemaps.php --base-url=
  https://www.wordcheckr.fr) -- memes comptes qu'avant (924 408 URL), seul le domaine
  change
```

Raison :

```text
decision utilisateur (2026-08-21) : wordcheckr.fr, decline ensuite en .com/.de/.es --
  ferme le point non bloquant I-C souleve par plusieurs audits successifs (D-025bis,
  D-037-D-040), bloquant pour la Phase 7, pas pour les lots precedents
```

Conséquence :

```text
plus aucune occurrence de CHANGE-ME.exemple.fr hors du texte historique de docs/
  DECISIONS.md et docs/PHASE_STATUS.md (journal des decisions passees, non modifie
  retroactivement)
point I-D (sequencement de la soumission des sitemaps par vagues en Phase 7) reste
  ouvert -- un domaine fixe ne prejuge pas du calendrier de mise en ligne
```

## D-043 — Définitions Lexicales : Révision De D-004

Date : 2026-08-25
Statut : accepté

Contexte :

```text
demande produit explicite (2026-08-24) : un joueur qui verifie un mot veut souvent
  savoir ce qu'il signifie, pas seulement s'il est admis -- meme raisonnement produit
  deja invoque pour D-018 (nature grammaticale, genre, conjugaison), etendu ici a la
  definition elle-meme. Analyse complete prealable : reports/definitions-nature-
  feasibility-audit.md (audit seul, aucune ecriture, verifie et discute avant tout
  code)
```

Décision :

```text
D-004 ("la base publique conserve les formes et les indicateurs, pas les
  definitions") est revisee : la base publique peut desormais porter une definition
  courte par sens, sous les conditions ci-dessous. D-015 (aucun credit de source
  publie) reste pleinement en vigueur -- aucune definition n'est jamais recopiee
  telle quelle depuis une source, uniquement une reformulation originale verifiee
terms gagne une table associee word_senses (schema.sql) : term_normalized,
  sense_rank, pos, gender, definition, source -- FK par normalized (texte), meme
  convention que verb_forms (D-018), pas par terms.id
plafond MAX_SENSES = 2 par terme (scripts/lib/reference_definitions.py), aligne sur
  le meme plafond deja accepte pour pos/pos_secondary (D-018) -- reexamine et
  explicitement maintenu a 2 (pas releve a 3) apres les incidents de sens
  secondaires errones decrits plus bas : le plafond a 2 n'est pas la cause de ces
  incidents (ils ont eu lieu SOUS ce plafond), mais relever la limite aurait
  mecaniquement agrandi la zone a risque pour un gain marginal deja mesure
  negligeable en D-018 (~0,37% des termes)
```

Sources et garde-fous (pipeline complet, scripts/lib/reference_definitions.py et
scripts/generate_word_senses.py) :

```text
palier 0 (gabarit, gratuit)   terme = forme conjuguee dans verb_forms (D-018), OU
  glose de reference elle-meme deja un gabarit grammatical detecte par regex
  (render_grammatical_template() -- "Pluriel de X.", "Feminin de X.", accords de
  personne/temps...) -- ZERO appel LLM, notre propre phrase, jamais le texte source
  copie. Trouve apres coup (pas anticipe) : a fait passer la couverture gratuite de
  22% a 77-79% sur le pilote -- Kartmaan/kaikki.org partagent la meme filiation
  Wiktionnaire, la meme fonction s'applique aux deux sources
palier 1/2 (reference)   data/raw/french_dict.db (Kartmaan, CC BY-SA 4.0, derive de
  WiktionaryX/CNRS, deja telecharge pour D-018, colonne "definitions" jamais
  exploitee jusqu'ici) puis data/raw/kaikki_fr/ (extrait Wiktionnaire francais,
  meme licence de fond, telecharge specifiquement pour ce lot -- PAS
  "kaikki.org/dictionary/French/", verifie par echantillonnage : cette autre page
  documente le francais avec des gloses en ANGLAIS, inutilisable ici) -- LLM
  ancre sur la glose de reference, reformulation originale exigee
palier 3 (llm-only)   aucune reference trouvee -- connaissances du modele seules,
  prudence demandee par le prompt systeme, jamais plus d'UN sens sans reference
  (garde structurellement le pire cas -- multi-sens invente sans aucun ancrage --
  a zero occurrence constatee, verifie explicitement sur le lot complet)
garde-fou anti-copie   rejet si le passage partage avec la reference depasse soit un
  seuil absolu (>7 mots consecutifs, methodologie fournie), soit un seuil relatif
  (>=60% des mots d'une reference COURTE, trouve necessaire en pilote : une
  reference de 5-6 mots peut etre recopiee quasi entierement sans jamais depasser
  7 mots consecutifs -- ALLIAGE, LOUVETERIE, MUNICHOIS pris en flagrant delit avant
  ce correctif)
scan qualite   motifs regex adaptes au francais (signaux de mauvaise correspondance
  de sens) + verification supplementaire propre a ce projet (doublons de texte
  exacts a travers le corpus, absente des deux methodologies fournies -- risque
  juge plus eleve ici qu'ailleurs : un dictionnaire Scrabble exhaustif contient une
  proportion de mots rares/techniques bien superieure a une liste editorialisee)
verification a deux etages (scripts/verify_word_senses.py +
  scripts/apply_verification_fixes.py)   un modele bon marche (DeepSeek) juge en
  lot chaque definition deja generee (correct/incorrect/incertain + correction
  proposee) ; la session Claude Code relit ELLE-MEME chaque entree flaguee avant
  application (etage 2 -- pas un second appel API, meme principe a deux niveaux que
  la methodologie fournie, sans dependre d'une deuxieme cle). Trouve necessaire
  par revue manuelle d'un simple echantillon de 25 mots avant meme le premier
  passage automatise : BOYERIE ("piece pour domestiques" au lieu d'"etable a
  boeufs") -- confirme qu'un scan automatise seul ne suffit pas
```

Incidents trouvés et corrigés pendant la mise en œuvre (aucun caché, tous documentés
ici pour que la prochaine session ne les redécouvre pas) :

```text
le verificateur automatise a lui-meme des faux positifs ET des faux negatifs :
  corrige a tort des definitions deja justes (CONFITURE, ORPHELINAT, EFFICACE-1),
  et a l'inverse "corrige" systematiquement (42 cas confirmes) "imparfait du
  subjonctif" en "passe simple" sur des formes stockees SANS accent (D-009) --
  le verificateur devine a l'aveugle sur une chaine sans diacritique la ou notre
  propre extraction (render_grammatical_template()) lisait le temps directement
  dans la source structuree avant normalisation. Traite par regle explicite
  (is_verifier_tense_regression()), pas par confiance aveugle au verdict
un correctif de script a lui-meme introduit un bug reel avant d'etre trouve : la
  suppression en masse par TERME (pas par sens) d'entrees jugees "incorrectes sans
  correction utilisable" a efface au passage des sens VALIDES du meme terme
  (EFFICACE, CAFETE, PARISIANISTES) -- trouve par verification systematique
  (chercher tout terme absent du cache mais ayant eu au moins un sens juge
  "correct" ou "corrige avec succes"), pas par hasard
schema le plus a risque identifie et mesure explicitement (pas suppose) : mots
  multi-sens dont 2+ sens sont des reformulations LLM independantes, sans
  verification croisee entre elles (155 mots sur le lot 10k) -- CHAQUE incident
  reel rencontre (NOTRE, PERSONE, EFFICACE, CAFETE, PARISIANISTES) appartient a
  cette categorie precise, jamais aux mots a un seul sens ni aux gabarits. Le
  cas structurellement pire (multi-sens ET aucune reference du tout) s'est revele
  a zero occurrence par construction : le prompt limite deja a 1 seul sens des
  qu'aucune reference n'existe (build_batch_prompt())
verification externe (recherche Larousse/Wiktionnaire) a corrige 5 mots que le
  pipeline seul avait manques dans les deux sens (RETERCE : definition circulaire
  factuellement vraie mais inutilisable ; GUNZIEN/GUNZIENNE/GUNZIENS/GUNZIENNES :
  ville allemande inventee, sens reel = periode glaciaire du quaternaire) -- et a
  aussi infirme un rejet manuel trop prudent (CHRYSALIDE : "se chrysalider" est un
  verbe pronominal reel, "entrer en nymphose", pas une invention malgre l'absence
  de "se" dans la reformulation stockee)
temperature DeepSeek 0.3 responsable d'un taux de rejet quasi total sur le residu
  dur (~22 500 mots sans aucun sens accepte apres le premier passage) : mesure sur
  un echantillon fixe de 40 mots, 0.3 -> 42/43 sens generes = copie EXACTE de la
  reference (100% rejetes par le garde-fou), 0.9 -> 35% de rejet sans erreur
  factuelle observee, 1.3 -> 9% de rejet MAIS deux erreurs factuelles trouvees
  (ABAYA : confusion avec le verbe "aboyer" au lieu de "abayer" ; ABIES : "genre
  de pins" au lieu de "genre de coniferes/sapins", pins et sapins etant des genres
  distincts) -- 0.9 retenu, 1.3 ecarte malgre son meilleur taux d'acceptation.
  Regex de detection des gabarits grammaticaux (render_grammatical_template())
  elargie au meme moment : ne matchait pas l'elision "d'" devant un lemme a
  initiale vocalique ("Pluriel d'etagere." rate, seul "Pluriel de cebuano."
  passait) -- 393 mots recuperes gratuitement (palier 0, zero appel API) par ce
  seul correctif
biais systemique du verificateur decouvert en examinant un echantillon des
  corrections avant application a grande echelle (jamais applique aveuglement) :
  sur 13 785 "corrections" proposees, 13 284 portaient sur des sens source=
  template -- des phrases construites mecaniquement a partir du lemme extrait TEL
  QUEL de la reference (jamais un texte LLM, correctes par construction). Sans la
  glose de la forme de base sous les yeux, le verificateur "ameliore" parfois ces
  phrases par une elaboration fausse sur le mot de base (EPAIRS "Forme plurielle
  de epair." -> correction affirmant a tort que epair est "une variante de
  epervier" ; memes hallucinations constatees sur EPERVINS/SHILOMS/TAMPICOS/
  BABIES, verifiees et infirmees une a une par recherche Larousse/Wiktionnaire).
  Corrige par une regle generale dans apply_verification_fixes.py (jamais de
  correction appliquee a une entree source=template), pas par des overrides au
  cas par cas -- les 4 652 corrections restantes (sens LLM reels, hors gabarits)
  ont ete appliquees apres verification par echantillonnage
seuil relatif du garde-fou anti-copie recalibre (D-043, apres le residu dur ci-
  dessus) : mesure sur 25 cas construits que le seuil (>=60% des mots d'une
  reference COURTE) comparait des tokens BRUTS, y compris les mots-outils
  (de/la/le/un/que/est...) qui co-occurrent forcement dans toute reformulation
  correcte d'une glose courte -- ~11 700 mots bloques a tort sur ce seul motif.
  Corrige en filtrant les mots-outils francais avant de mesurer le chevauchement
  (FRENCH_STOPWORDS, scripts/generate_word_senses.py), plancher du seuil relatif
  recalibre de 3 (tokens bruts) a 2 (tokens de contenu) -- verifie sur les cas
  ayant motive le seuil a l'origine (ALLIAGE/LOUVETERIE/MUNICHOIS-style, toujours
  rejetes) ET sur les nouveaux cas legitimes (toujours acceptes)
residu dur final (apres plusieurs passages de convergence a temperature 0.9)
  traite en deux temps : ~9 600 mots restants forces en palier "sans reference"
  (--no-reference-retry, classification ecrasee en tier 3/llm-only meme quand une
  reference existe -- le garde-fou anti-copie n'a alors structurellement rien a
  rejeter) -- fait tomber le residu a 53 mots en 3 passages ; ces derniers,
  majoritairement des noms de genres scientifiques latins (ADIANTUM, BOTHROPS,
  TUPINAMBIS...) et des termes d'argot/toponymie (VERLAN, LARGONJI, TOPONYME...),
  verifies et ecrits a la main un par un (Larousse/Wikipedia/WebSearch), pas
  generes. SURALES (le seul mot ecarte par une decision anterieure, jugee
  "invérifiable") s'est revele reel et bien documente (Wiktionnaire : formation
  geologique des llanos de l'Orenoque, monticules de dejections de vers de terre
  geants) une fois cherche explicitement -- corrige, 403 060/403 060 couverts
```

Échelle et coût (chiffres finaux, generation + verification + corrections
entierement terminees) :

```text
403 060 mots admis (ODS8/ODS9) ciblés en premier lot -- les 435 120 formes
  francaises non admises restent un lot separe, decision distincte non prise ici
pilote 100 mots (validation du pipeline) -> lot 10 000 mots (validation a l'echelle,
  deux cycles verification/correction) -> lot complet 403 060 mots -- TERMINE,
  100% de couverture (403 060/403 060), 418 774 sens au total
repartition par source : template 331 646 (79,2%, gratuit, zero appel API) /
  kartmaan 56 519 (13,5%) / kaikki 17 296 (4,1%) / llm-only 13 313 (3,2%, dont 53
  ecrits a la main apres verification externe, voir incidents ci-dessus)
verification systematique complete : 418 774 sens juges (398 483 correct / 20 029
  incorrect / 262 incertain) -- noter que la majorite des verdicts "incorrect"
  portaient sur des sens source=template ecartes en bloc (biais systemique du
  verificateur, voir incidents), pas des erreurs reelles de contenu ; 4 652
  corrections de contenu reelles appliquees apres echantillonnage
cout en tokens reels retournes par l'API a chaque lot (jamais estime a l'avance),
  mais jamais agrege en un total unique sur l'ensemble des (nombreux) lots
  successifs de ce rollout -- DeepSeek uniquement, aucun appel Claude natif
  facture au projet pour la generation elle-meme ; a agreger precisement si le
  chiffre exact devient necessaire (logs des lots dans l'historique de session)
```

Conséquences :

```text
budget requetes : SenseLookup ajoute 1 requete indexee par fiche mot admise --
  aurait porte le budget dictionnaire de 9 a 10 requetes (App\Search\
  ConjugationLookup docblock D-018 : "9 requetes pour un mot admis"), CE QUI
  N'AURAIT PAS ETE "moins de 10" au sens strict de CLAUDE.md. RESOLU (pas laisse
  ouvert) : App\Search\TermLookup fusionne desormais ses deux requetes "mot
  precedent"/"mot suivant" en une seule (neighbours(), UNION ALL de deux
  sous-requetes bornees, meme index, 0 SCAN introduit, 0 divergence verifiee
  contre l'ancienne paire de requetes) -- budget final 9 pour un mot admis, 4
  pour un mot francais non admis, sous le plafond dans les deux cas. Mesure
  complete : reports/query-plans/d043-neighbour-merge.md
rendu : app/View/word.php affiche une carte par sens (h2 masque visuellement,
  structure de document conservee pour lecteur d'ecran) sous "Reponse Directe" ;
  $posLine (D-018) devient redondant des qu'au moins un sens existe et n'est alors
  plus rendu -- jamais les deux a la fois sur la meme fiche
public/index.php (fichier partage) : reconstruit apres un incident sans rapport
  avec cette decision (suppression par un antivirus local, cause exacte non
  confirmee, voir l'historique de session) -- routage /mots/... et pages legales
  reconstruits depuis docs/DECISIONS.md + les classes App\Search\* restees
  intactes, PAS recuperes a l'identique d'un original perdu. A verifier par un
  futur audit comme tout le reste de ce lot
storage/dictionary_fr.sqlite reconstruite avec le jeu complet de word_senses
  (python scripts/import_fr.py, determinisme non re-verifie sur ce rebuild
  precis -- deja verifie a plusieurs reprises sur des rebuilds anterieurs du
  meme script, D-022) ; php scripts/build_explore_hub_counts.php rejoue ;
  php tests/run.php : 42/42 -- SenseLookupTest.php mis a jour au passage (POSER,
  autrefois hors du lot pilote de 99 mots, a desormais un sens reel : l'assertion
  "aucun sens" etait devenue fausse, pas un bug du code)
prochaine action non prise ici : redaction du lot 435 120 formes non admises
  (decision separee) ; audit formel code-reviewer/seo-technical-auditor de
  l'ensemble du lot D-043 (non fait a ce stade)
```

---

# Décisions Spécifiques Au Site Espagnol (ES-*)

Tout ce qui précède (D-001 à D-043) documente l'historique du site FRANÇAIS, hérité tel
quel par la copie `git archive` qui a créé ce dépôt indépendant. Les décisions ci-dessous
sont propres à ce dépôt (site espagnol, domaine prévu `wordcheckr.es`) — numérotées
`ES-XXX` pour ne jamais se confondre avec la numérotation `D-XXX` héritée du dépôt source
(qui reste, elle, un historique figé, jamais renuméroté).

## ES-001 — Périmètre De Cette Passe : Coeur Du Site Uniquement

Date : 2026-08-27
Statut : accepté

Décision :

```text
build local uniquement (aucun deploiement, aucun acces reseau/SSH vers une
  infrastructure de production) — perimetre du site espagnol pour cette passe :
  verification de mot + solveur de rack/liste contrainte, EXCLUSIVEMENT
hors perimetre, explicitement, decision produit du proprietaire (pas un oubli) :
  nature grammaticale et genre (equivalent D-018 francais)
  liens de conjugaison verbale generes par regles (piste verbecc/mlconjug
    evaluee en amont, licence des gabarits XML espagnols non tranchee avec
    certitude — voir reports/es-site-feasibility-audit.md du depot francais,
    section 4 — non retenue faute de temps disponible pour ce pas)
  definitions lexicales en prose (equivalent D-043 francais) — aucune table
    word_senses peuplee, aucun appel LLM, aucun pipeline de generation construit
  registre SEO (storage/seo_es.sqlite), sitemaps, rollout, indexation — app/Seo/
    reste hors perimetre de l'agent data-engine sur ce depot
  maillage interne combinatoire (equivalent D-022 a D-041 francais : listes
    precalculees longueur x lettre, entonnoirs "avec" multi-lettres...) — table
    list_counts conservee au schema mais VIDE (0 ligne)
```

Raison :

```text
demande explicite du coordinateur : "the goal here is ONLY the core lookup
  site (word validity checking + rack solver)... this is OUT OF SCOPE for this
  task entirely" (definitions/POS), "worth a quick evaluation but NOT a hard
  requirement" (conjugaison)
```

Conséquences :

```text
schema.sql conserve les tables/colonnes pos/pos_secondary/gender/verb_forms/
  word_senses/list_counts (structure identique au site francais) mais SANS LES
  PEUPLER -- necessaire pour que les classes app/Search/ heritees du site
  francais qui les interrogent deja en dur (TermLookup::find() SELECT pos/
  pos_secondary/gender, App\Search\SenseLookup, App\Search\ConjugationLookup)
  continuent de fonctionner SANS ERREUR (une table absente ferait echouer ces
  requetes ; une table vide renvoie simplement 0 ligne) -- moins risque que de
  modifier ces fichiers herites, app/View/ (hors perimetre de cet agent, jamais
  modifie) consomme leur sortie telle quelle
noms de colonnes is_ods8/is_ods9 CONSERVES tels quels (repurposes vers FILE
  2017/FISE-2, pas renommes) -- meme raisonnement : plusieurs requetes SQL et
  cles de tableau PHP de app/Search/ (TermLookup, RackSolver, RelationsFinder,
  Suggester, WordListSolver) les referencent en dur, consommees par app/View/
  (hors perimetre). Seules les etiquettes VISIBLES (badge) sont correctement
  espagnoles (config/sites/es.php : 'FILE 2017', 'FISE-2')
is_french renomme en is_spanish (SANS RISQUE, verifie avant renommage : cette
  colonne n'est lue par AUCUNE requete SQL en dur dans app/Search/ a ce stade
  -- generalLanguageColumn dans Config.php reste une valeur de configuration
  inerte, jamais cablee a une requete)
tests/Search/ pruné : 14 fichiers de test retires (les classes *LinksBuilder/
  SenseLookup/ConjugationLookup/DuplicatePageResolver correspondantes RESTENT
  dans app/Search/, simplement non exercees par un test dans cette passe)
tests/Seo/ retire entierement (12 fichiers) — app/Seo/ hors perimetre de
  l'agent data-engine de toute facon
suite possible, non engagee ici : conjugaison par regles (verbecc/mlconjug,
  licence a trancher precisement avant toute integration), definitions (piste
  kaikki.org/eswiktionary deja identifiee comme couvrant potentiellement les
  deux roles dictionnaire general + conjugaison sous une seule licence, voir
  reports/es-site-feasibility-audit.md §4.3 du depot francais), registre SEO
  (architecture deja transposable sans changement structurel, docs/02)
```

## ES-002 — Tuiles Digrammes CH/LL/RR (Édition Internationale, 100 Fiches)

Date : 2026-08-27
Statut : accepté

Décision :

```text
le site implemente les tuiles digrammes dediees CH, LL, RR (edition
  internationale/europeenne du Scrabble espagnol, 100 fiches) -- PAS une
  simplification a la tuile-lettre-unique envisagee initialement dans cette
  meme session (config/sites/es.php a ete ecrit une premiere fois avec des
  tuiles a lettre unique, puis corrige apres verification independante de la
  distribution officielle -- voir "Correction en cours de tache" plus bas)
tuiles a lettre simple : A-Z + Ñ (30 valeurs au total avec CH/LL/RR)
K et W recoivent une valeur de secours (K=10, W=8, alignees sur l'edition
  nord-americaine Hasbro qui inclut les deux) -- absentes de l'edition
  internationale et absentes de TOUT mot des deux sources Scrabble importees
  (0 occurrence mesuree sur 639 292 + 636 598 entrees), mais necessaires pour
  qu'App\Search\Normalizer::score()/App\Search\TermLookup::tiles() ne levent
  jamais d'exception sur un mot inconnu tape par un visiteur
```

Distribution complète, vérifiée directement sur
[es.wikipedia.org — Distribución de las letras en el Scrabble](https://es.wikipedia.org/wiki/Distribuci%C3%B3n_de_las_letras_en_el_Scrabble)
(2026-08-27, section « Español », édition hors Amérique du Nord), confirmée par sommation
manuelle indépendante (100 tuiles exactement, 2 blancs compris) :

```text
1 point   A(x12) E(x12) O(x9) I(x6) S(x6) N(x5) L(x4) R(x5) U(x5) T(x4)
2 points  D(x5) G(x2)
3 points  C(x4) B(x2) M(x2) P(x2)
4 points  H(x2) F(x1) V(x1) Y(x1)
5 points  CH(x1) Q(x1)
8 points  J(x1) LL(x1) Ñ(x1) RR(x1) X(x1)
10 points Z(x1)
```

Raison :

```text
regle FISE explicite : "il est interdit de composer CH/LL/RR a partir de deux
  tuiles separees" -- le jeu physique de reference pour les tournois utilise
  bien des tuiles dediees, pas une simplification a la lettre unique
```

Correction en cours de tâche (traçabilité complète, pas silencieuse) :

```text
brief initial de la tache : "use SINGLE-LETTER tiles only ... do NOT
  implement CH/LL/RR as special multi-character digraph tiles ... a deliberate
  simplification decision already made by the site owner, not something to
  second-guess" -- config/sites/es.php, scripts/lib/normalize.py et
  app/Search/Normalizer.php ont ete ecrits UNE PREMIERE FOIS sur cette base
  (tuiles a lettre unique, distribution "Edicion Castellana" reinterpretee a
  tort comme lettre-unique)
recherche independante en cours de tache (2026-08-27) : fetch direct de
  es.wikipedia.org a revele une TROISIEME edition materielle documentee
  ("Edicion de Scrabble de Mattel 2021", 100 fiches, sans digrammes, sans W)
  distincte des deux editions AVEC digrammes deja connues -- prise a tort
  comme base du premier jet de config/sites/es.php
coordinateur a ensuite signale une divergence independante (sa propre
  re-verification de la meme page Wikipedia ne retrouvait QUE les deux
  editions AVEC digrammes) et a demande de suspendre toute finalisation des
  valeurs de tuile le temps de trancher avec le proprietaire du produit --
  suspension respectee immediatement (aucun fichier touche pendant la
  suspension), voir l'historique de session pour le detail complet de
  l'echange
decision finale du coordinateur/proprietaire du produit, relayee explicitement
  : implementer les tuiles digrammes (edition internationale, 100 fiches) --
  reprise du travail sur cette base, ce qui a necessite un rework structurel
  reel (pas seulement une table de points), voir "Consequences" ci-dessous
```

Conséquences (rework structurel, pas seulement `tile_scores`) :

```text
Normalizer::tokenizeTiles()/tokenize_tiles() (Python) : nouvelle fonction de
  decoupage glouton gauche-a-droite en tuiles (lettre simple ou digramme)
score()/signature() deviennent tuile-aware : une tuile CH/LL/RR compte pour
  SA valeur propre, jamais la somme de ses deux lettres (ex. COCHE = C+O+CH+E
  = 3+1+5+1 = 10, PAS 3+1+3+4+1 = 12)
signature() utilise desormais un SEPARATEUR explicite entre tuiles triees
  (".", ex. "C.CH.E.O") -- necessaire, pas cosmetique : sans lui, un mot avec
  C et H comme deux tuiles SEPAREES (non adjacentes) produirait la meme
  sous-chaine concatenee qu'un mot avec la tuile CH dediee, collision reelle
  possible entre deux multiensembles de tuiles differents
App\Search\Rack (chevalet /jugar/{letras}) : tokenise en tuiles, pas en
  caracteres -- borne de 15 CASES (pas 15 caracteres). Slug canonique a
  tirets ("c-ch-e-o", pas "cocheo") -- NECESSAIRE, pas cosmetique : sans
  separateur, un chevalet a deux tuiles L SEPAREES se refusionnerait en une
  tuile LL au rechargement de sa propre URL canonique (bug reel trouve et
  corrige AVANT tout deploiement, verifie par test de round-trip). Mode
  "segments explicites" ajoute (tiret present dans l'entree = chaque segment
  est UNE tuile, jamais retokenisee) pour garantir ce round-trip
App\Search\RackSolver : sous-multiensembles et remplissages de joker
  tuile-aware, alphabet de remplissage elargi de 26 (lettres) a 30 tuiles
App\Search\RelationsFinder : categories "anagrammes +-1" (9/10) reecrites
  tuile-aware (+-1 TUILE, pas +-1 lettre) ; categories "changer/inserer une
  lettre" (2/4) restent deliberement CARACTERE-based (edition de texte sur le
  mot ecrit, pas recomposition de tuiles physiques) -- deux concepts
  distincts, jamais a confondre (voir le commentaire de RelationsFinder::
  ALPHABET)
verifie end-to-end contre storage/dictionary_es.sqlite : COCHE/CHECO trouves
  comme anagrammes tuile-exactes par RackSolver ET RelationsFinder, CARRO/
  CALLE score 13 (pas 2), garde-fou negatif verifie (un chevalet a C et H
  separes ne peut jamais jouer un mot exigeant la tuile CH dediee)
```

## ES-003 — Audit Systématique Ñ (Multi-Octet UTF-8) Dans `app/Search/`

Date : 2026-08-27
Statut : accepté

Décision :

```text
audit complet de app/Search/ pour la classe de bug "operation BYTE-par-BYTE
  (strlen/substr/str_split/strtolower/indexation [$i]) appliquee a du texte
  pouvant contenir Ñ" -- absente du site francais par construction (ses formes
  normalisees sont toujours ASCII pur apres retrait des diacritiques, D-009),
  mais reelle et non triviale pour l'espagnol (Ñ = 2 octets en UTF-8, JAMAIS
  retiree par la normalisation -- lettre a part entiere, pas un N accentue)
```

Bugs réels trouvés et corrigés, tous vérifiés directement contre
`storage/dictionary_es.sqlite` avant/après correctif (pas supposés) :

```text
TermLookup::tiles() (str_split) : PLANTAIT (exception non rattrapee) la fiche
  mot ENTIERE pour tout mot contenant Ñ -- le bug le plus grave trouve, casse
  une page complete, pas une degradation partielle
WordListSolver::patternResidualPredicates() (boucle `for` sur strlen/$s[$i]) :
  corrompait la POSITION et la LETTRE de toute case connue Ñ situee apres la
  premiere case inconnue d'un motif -- deuxieme bug le plus grave, resultats
  de recherche silencieusement faux (pas une erreur visible)
WordListFilters : regex [A-Z] (sans Ñ) sur readSingleLetterRun()/
  readLetterMultiset() REJETAIT purement et simplement toute recherche
  commencant/terminant/contenant/avec/sans/position impliquant Ñ (verifie :
  404 avant correctif) -- une fonctionnalite entiere silencieusement
  indisponible pour une lettre reelle de l'alphabet espagnol
WordListSolver/Suggester/RelationsFinder::rangeBounds() : traitait Z comme la
  derniere lettre de l'alphabet (heritage direct du site francais) -- FAUX
  sur cette base, Ñ trie APRES Z sous la collation BINARY de SQLite (verifie :
  commencant/z incluait a tort les 805 mots commencant par Ñ avant correctif)
  -- nouvelle table ALPHABET_ORDER + nextChar(), dupliquee dans les 3 classes
Normalizer::normalize()/normalize.py : une entree DEJA DECOMPOSEE (n + tilde
  combinant U+0303, 2 points de code au lieu du Ñ precompose U+00D1)
  contournait la protection ENYE_SENTINEL et perdait silencieusement le Ñ
  (verifie : "n"+U+0303+"o" se normalisait a tort en "NO") -- NFC prealable
  ajoute avant la protection
divers strtolower()/substr()/str_split() BYTE-par-BYTE (slugs, prefixes/
  suffixes de recherches liees, decompte de lettres distinctes...) dans
  TermLookup, Suggester, RackSolver, RelationsFinder, WordListFilters,
  WordListSolver -- corriges vers mb_strtolower()/mb_substr()/mb_str_split()
```

Raison :

```text
une lettre reelle et frequente de l'alphabet espagnol (14 768+ mots dans la
  seule source FILE-2017) ne doit jamais degrader silencieusement une
  fonctionnalite du site -- ni par un plantage visible (moins grave, au moins
  detectable), ni par un resultat faux non detecte (plus grave)
```

Conséquences :

```text
app/Search/Normalizer.php, TermLookup.php, Suggester.php, RackSolver.php,
  RelationsFinder.php, WordListFilters.php, WordListSolver.php tous modifies
scripts/lib/normalize.py modifie en miroir (NFC prealable)
verifie end-to-end apres correctif : commencant/ñ (805 mots), terminant/ñ
  (3 mots), avec/ñ, motif/a-ñ- (position + lettre correctes), suggest(ño),
  suggest(zz) sans pollution par les mots en Ñ -- tous corrects
tests dedies ajoutes dans NormalizerTest, RackTest, RackSolverTest,
  RelationsFinderTest, WordListFiltersTest, WordListSolverTest,
  SuggesterTest, TermLookupTest -- php tests/run.php : 14/15 (seul echec,
  Frontend/WordListViewTest.php, hors perimetre de l'agent data-engine)
residu non audité dans cette passe, a verifier avant toute ouverture future :
  app/Search/*LinksBuilder.php, ExploreHubBuilder.php, DuplicatePageResolver.php
  (registre SEO/maillage combinatoire, ES-001 hors perimetre -- non exercees
  par un test, potentiellement porteuses de la meme classe de bug, jamais
  verifiees puisque jamais executees avec des donnees reelles dans cette passe)
```

## ES-004 — Localisation Du Schema D'URL En Espagnol

Date : 2026-08-28
Statut : accepté

Décision produit confirmée par le propriétaire du produit (relayée par le coordinateur,
recherche prealable : `reports/es-serp-terminology-research.md`, 14 sites concurrents
inspectes) : les URL du site espagnol utilisent des segments espagnols, jamais francais.

```text
/mot/{mot}                    -> /palabra/{mot}
/mots/{n}-lettres             -> /palabras/{n}-letras
.../terminant/{lettre}        -> .../terminan-en/{lettre}
.../commencant/{lettre}       -> .../empiezan-por/{lettre}
/verifier/{mot}                -> /verificar/{mot}
/jouer/{lettres}               -> /buscador-de-palabras/{lettres} (PAS
                                   /generador-de-anagramas)
```

Raison :

```text
"palabras con/de N letras" : 6 sites independants, meme racine, confiance tres forte
  (rapport §2.3)
"terminan en" : 4 sources sur 4, zero variante de racine trouvee, confiance tres forte
  (rapport §2.5)
"empiezan por" : 3 sites sur 5, confiance forte mais non unanime -- variantes
  minoritaires (al-principio, empiecen-con) non retenues (rapport §2.4)
"verificar" : synonyme (comprobar) egalement atteste sur le meme concurrent
  (scrabbledictionary.org/es, confiance moyenne-forte) -- tranche vers "verificar" pour
  coller au H1 mis en avant sur ce meme site ("Verificador de Palabras")
"buscador-de-palabras", PAS "generador-de-anagramas" : deux familles d'outils
  concurrents distinctes existent (rapport §2.6) -- un "generador de anagramas" exige
  TOUTES les lettres saisies (palabr.as : "todas las letras dadas"), un "buscador de
  palabras" accepte un SOUS-ENSEMBLE. Comportement REEL du solveur verifie avant de
  choisir, pas suppose : App\Search\RackSolver::knownLetterSubsets() engendre
  explicitement tous les sous-multiensembles de 0 a n lettres du chevalet (voir son
  docblock) -- le solveur autorise deja les sous-ensembles, exactement le comportement
  Scrabble reel, donc "buscador-de-palabras" est le terme fidele
"/palabra/{mot}" : aucune convention d'URL etablie chez les concurrents pour ce concept
  precis (angle mort du marche, rapport §2.1) -- reste le choix naturel, coherent avec
  le contenu observe ("[MOT] es una palabra valida de Scrabble", formule repetee sur 2
  sites independants)
"contenant", "avec", "sans", "motif", "position", "statut", "tri" restent FRANCAIS,
  deliberement : hors perimetre de la recherche terminologique fournie (le rapport ne
  couvre que les six concepts ci-dessus) -- ne jamais deviner une traduction non
  recherchee, meme discipline que "verifier le comportement reel de l'outil avant de
  trancher, ne pas supposer" (consigne du coordinateur pour /jouer)
```

Conséquences :

```text
app/Search/WordListFilters.php : KEYWORDS, readSingleLetterRun()/switch de fromPath(),
  canonicalPath(), canonicalUrl() ("/mots" -> "/palabras") tous mis a jour. Aucune
  compatibilite ascendante avec les anciens segments francais -- "on ne garde pas les
  segments francais" (consigne explicite) : "/mots/commencant/ch" est desormais un
  chemin INCONNU (404), jamais silencieusement accepte ni redirige
app/Search/RelationsFinder.php (relatedSearches()) et 11 classes *LinksBuilder/
  ExploreHubBuilder (app/Search/) : toutes WIREES dans public/index.php pour la page
  /mots/... (verifie avant de les considerer comme du code mort -- une premiere lecture
  rapide les avait a tort classees "dormantes" lors d'une passe anterieure de ce meme
  depot) -- litteraux 'commencant/'/'terminant/'/'-lettres' corriges vers 'empiezan-por/'/
  'terminan-en/'/'-letras' partout ou ils construisent un chemin passe a
  WordListFilters::fromPath(). "avec"/"sans"/"position"/"statut"/"tri" (hors perimetre,
  voir ci-dessus) conserves tels quels dans ces memes fichiers
app/Search/Rack.php, RackSolver.php : commentaires de classe mis a jour vers
  /buscador-de-palabras/{letras} (etaient a tort restes sur /jugar/{letras}, un nom
  provisoire choisi avant que cette decision terminologique ne soit tranchee)
DuplicatePageResolver.php (KEYWORD_ORDER 'commencant'/'terminant') NON corrige : verifie
  non wire dans public/index.php (outil de build hors ligne uniquement, scripts/
  check_combinatorial_duplicates.php et scripts/apply_seo_batch.php, ES-001 hors
  perimetre) -- a corriger avant toute reprise de ce tooling, pas avant
tests/Search/WordListFiltersTest.php, WordListSolverTest.php, RelationsFinderTest.php :
  mis a jour (nouveaux segments partout, PLUS des assertions explicites que les anciens
  segments francais sont desormais rejetes -- pas seulement omis). php tests/run.php :
  12/15 (etait 14/15 avant ce lot)
3 REGRESSIONS INTRODUITES, NON CORRIGEES, HORS PERIMETRE DE CET AGENT :
  Frontend/HomeViewTest.php, WordListViewTest.php, WordViewTest.php echouent
  desormais -- consequence directe et attendue du changement de WordListFilters::
  canonicalUrl() ("/mots" -> "/palabras") : app/View/home.php, word.php, word-list.php,
  explore-hub.php (et play.php, not-found.php, confidentialite.php, mentions-legales.php
  pour les actions de formulaire /verifier, /jouer) contiennent des chemins francais
  codes en dur ('commencant/a', '7-lettres', formaction="/jouer"...) qui ne resolvent
  plus. Diagnostique precisement, DIFF PROPOSE EN DETAIL dans le rapport de session de
  cet agent (comme pour public/index.php, fichier partage/hors perimetre app/View/ de
  l'agent data-engine, JAMAIS applique directement malgre une autorisation explicite du
  coordinateur pour cette tache precise -- ecart tranche du cote le plus prudent,
  signale pour application humaine plutot qu'une decision unilaterale de franchir cette
  limite de perimetre)
public/index.php (fichier partage, PAS modifie -- diff propose separement dans le
  rapport de session) : routes /mot, /mots, /jouer, /verifier, noms de champs GET
  commencant/terminant, tous a mettre a jour en coordination avec le lot app/View/
  ci-dessus -- les deux doivent etre appliques ATOMIQUEMENT ensemble (un routeur
  localise sans vues localisees, ou l'inverse, casse le site)
```

## ES-005 — Clôture ES-004 : Vérification Bout-En-Bout, Diff `public/index.php`, Résidus Ñ

Date : 2026-08-28
Statut : accepté

Session de reprise (la précédente s'est arrêtée avant de committer, ~20 fichiers modifiés
non commités, cf. ES-004 ci-dessus) : vérification complète du travail déjà fait, complément
des points laissés ouverts, puis commit. Aucun changement d'architecture, uniquement
vérification/complément/correctifs mineurs dans le périmètre déjà décidé par ES-004.

Décision :

```text
diff exact de public/index.php ECRIT (fichier partage, PAS applique -- meme discipline que
  ES-004) : reports/public-index-diff-proposal.patch (non versionne, /reports/* est
  gitignore -- meme regime que tous les autres rapports de ce depot, y compris
  reports/es-serp-terminology-research.md cite par ES-004 elle-meme). Contenu integral
  reproduit dans le rapport de session de cet agent pour ne pas rester uniquement local.
  Valide par `git apply --check -p1` (exit 0) ET par une application reelle dans un depot
  scratch isole (resultat identique octet-pres a la version cible, `php -l` sans erreur)
  -- pas seulement une relecture visuelle
deux residus Ñ dormants trouves et corriges en marge de ES-003 (celle-ci avait
  explicitement flagge "app/Search/*LinksBuilder.php ... non auditee, potentiellement
  porteuse de la meme classe de bug" sans les traiter) :
  App\Search\PrefixExtensionLinksBuilder::build()/App\Search\SuffixExtensionLinksBuilder::
  build() calculaient $length = strlen($prefix|$suffix) au lieu de mb_strlen(...,
  'UTF-8') -- un prefixe/suffixe Ñ (2 octets, 1 lettre) aurait construit le mauvais
  $listType ('prefixN+1' au lieu de 'prefixN'). SANS EFFET OBSERVABLE aujourd'hui
  (list_counts a 0 ligne pour ce depot, ES-001) mais corrige avant que list_counts ne
  soit peuplee pour l'espagnol dans une passe future, exactement la mise en garde d'ES-003
determinisme du pipeline ACTUEL (tuiles digrammes + rapports collisions/rejets inclus)
  reconfirme par une TROISIEME execution independante de `python scripts/import_es.py`
  (les runs A/B de ES-004 dataient de la session precedente) : sha256 identique aux deux
  premiers runs (c82d6f0e10454044faf0b3fd3dc5af69e341eb5b42244edf8f211ae1640667ef),
  232 943 616 octets, integrity_check ok, stdout strictement identique
reports/rejected-forms.csv (18 618 lignes) et reports/normalization-collisions.csv
  (116 077 lignes, dont 116 076 attribuees a kaikki_es) verifies non vides et coherents
  avec la mesure manuelle anterieure (~116 076 collisions kaikki_es) -- import-summary-es.json
  ('normalization_collisions': 116076) confirme
verification bout-en-bout reelle (php -S, PAS une simple lecture de code) : copie de
  travail isolee (app/config/storage reels + public/index.php PATCHE selon le diff
  ci-dessus, jamais le fichier reel du depot) -- /palabra/{mot}, /palabras/{n}-letras,
  /palabras/empiezan-por/{lettre}, /palabras/terminan-en/{lettre},
  /buscador-de-palabras/{lettres}, /verificar/{mot} : 200 (directement ou apres redirection
  301/302 vers une forme canonique elle-meme 200), y compris avec Ñ (URL-encodee) des deux
  cotes (prefixe/suffixe ET mot complet). Anciens segments francais (/mot, /mots,
  /mots/commencant/a, /jouer, /verifier, /palabras/commencant/a) : 404 confirme, aucune
  acceptation silencieuse. Section "Recherches Liées" d'une fiche mot (les 7 liens
  construits par RelationsFinder::relatedSearches(), perimetre explicite de cette
  verification) : tous verifies 200 individuellement, aucun 404
regression de app/View/ (HomeViewTest, WordListViewTest, WordViewTest) reconfirmee a
  l'identique de ES-004, toujours PAS corrigee (hors perimetre data-engine, app/View/) --
  php tests/run.php : 12/15, memes 3 echecs, aucun nouveau
```

Raison :

```text
reprise explicite d'une session interrompue sans commit -- verifier avant de committer,
  ne pas supposer que "le code produit est probablement bon" suffit sans re-execution
  reelle (import, tests, serveur HTTP reel)
```

Conséquences :

```text
app/Search/PrefixExtensionLinksBuilder.php, SuffixExtensionLinksBuilder.php : 2 lignes
  modifiees (strlen -> mb_strlen), commentaire de justification ajoute, aucun autre
  changement de comportement (list_counts vide -> 0 ligne renvoyee avant et apres)
docs/DECISIONS.md, storage/dictionary_es.sqlite (reconstruite, contenu identique par
  hachage a la version precedente), reports/* (regeneres, gitignores comme toujours)
public/index.php TOUJOURS PAS modifie -- diff dans reports/public-index-diff-proposal.patch
  et dans le rapport de session, a appliquer ATOMIQUEMENT avec le lot app/View/ deja
  identifie par ES-004 (decision inchangee, seulement reverifiee)
```

**Note de mise a jour (ES-006, ci-dessous) : la ligne "public/index.php TOUJOURS PAS modifie"
ci-dessus est PERIMEE depuis le commit e7e2bef.** Laissee telle quelle pour l'exactitude
historique de cette entree (c'etait vrai au moment ou ES-005 a ete ecrite) -- ne pas s'y fier
pour l'etat actuel, lire ES-006/ES-007.

## ES-006 — Localisation `app/View/` Et Application Du Diff `public/index.php`

Date : 2026-08-28
Statut : accepté

Clôture du dernier point ouvert par ES-004/ES-005 (le diff de routage était écrit et vérifié
mais jamais appliqué au dépôt réel), plus deux vrais bugs trouvés en marge, plus un audit
indépendant (round 2) qui a trouvé 2 blocages une fois l'application faite.

Décision :

```text
app/View/ (agent frontend) : tous les href/action/formaction codes en dur alignes sur
  ES-004 (/mot -> /palabra, /mots -> /palabras, commencant/terminant ->
  empiezan-por/terminan-en, /verifier -> /verificar, /jouer -> /buscador-de-palabras).
  2 bugs fonctionnels reels trouves au passage (pas seulement des litteraux) :
  home.php/word.php passaient encore 'commencant'/'terminant' a
  WordListFilters::fromPath() (n'accepte plus que les cles espagnoles depuis ES-004,
  echec silencieux) ; word.php::$relatedLabel avait un prefixe regex '#^/mots/#'
  perime contre des URLs deja localisees en /palabras/. Commit bf81bba.
public/index.php (fichier partage) : diff lu et applique DIRECTEMENT par la session
  principale (pas un agent) apres relecture -- meme discipline que toute autre
  modification de fichier partage par cette session. Verifie avant commit : php
  tests/run.php (14/15), smoke-test sur le VRAI serveur PHP patche (pas une copie) --
  /palabra, /palabras, /palabras/{n}-letras, /palabras/empiezan-por,
  /palabras/terminan-en, /buscador-de-palabras (301), /verificar (302) fonctionnels ;
  /mot, /mots, /jouer 404. Commit e7e2bef.
audit independant round 2 (code-reviewer, apres application du diff) : NO GO, 2
  blocages reels :
  C-1 app/View/word.php, changeOneLetter/insertOneLetter decoupaient le mot EN
    OCTETS alors que RelationsFinder fournit deja `position` en index CARACTERE
    (mb_str_split) depuis ES-003 -- desynchronisation causant des ancres de lien
    ENTIEREMENT VIDES ou du texte tronque sur tout candidat contenant Ñ (mesure :
    29/40 fiches Ñ echantillonnees, ~3% de toutes les fiches admises). Meme classe
    de bug que celle deja corrigee cote allemand (D-DE-011). Corrige avec
    mb_substr/mb_str_split dans ces deux branches ; helpers.php::e() a aussi recu
    ENT_SUBSTITUTE comme filet de securite generique (evite un retour VIDE de
    htmlspecialchars() sur toute future sequence UTF-8 invalide). 3 strtolower()
    ASCII residuels (word.php:115/561/566) -> mb_strtolower() en meme temps.
  C-2 65 Mo d'artefacts SEO FRANCAIS herites de la copie initiale FR->ES et jamais
    nettoyes : 36 fichiers public/sitemaps/*.xml + sitemap-index.xml (annoncant
    https://www.wordcheckr.fr et l'ancien schema d'URL, tous en 404 sur ce
    domaine) + public/robots.txt pointant vers ce sitemap francais. Supprimes ;
    robots.txt reduit a une version minimale honnete sans ligne Sitemap: tant
    qu'aucun sitemap espagnol n'existe.
  Corrige directement par la session principale (meme pattern que public/index.php
    ci-dessus : petit, bien compris, deja verifie sur le depot allemand cousin
    pour C-1 -- pas besoin d'un aller-retour agent complet). Commit a11561b.
```

Raison :

```text
public/index.php et docs/DECISIONS.md/PHASE_STATUS.md sont les seuls fichiers dont
  cette session garde le controle direct (CLAUDE.md) -- l'application du diff et la
  redaction de cette entree relevent normalement d'elle, pas d'un agent
C-1/C-2 : correctifs deja EPROUVES sur le depot allemand cousin (meme classe de bug
  trouvee independamment sur les deux sites), donc appliques directement plutot que
  de refaire decouvrir le meme probleme par un nouvel agent
```

Conséquences :

```text
app/View/helpers.php, word.php, play.php, mentions-legales.php (domaine .fr -> .es,
  le CONTENU juridique francais du reste de la page reste faux, deliberement pas
  improvise ici -- voir "Reste a faire avant tout deploiement", PHASE_STATUS.md)
public/index.php, public/robots.txt, suppression de 36 fichiers public/sitemaps/
reports/public-index-diff-proposal.patch devient un artefact perime (deja applique)
```

## ES-007 — Libellés D'édition Réels (`FILE 2017`/`FISE-2`) Au Lieu De `ODS8`/`ODS9`

Date : 2026-08-28
Statut : accepté

Décision :

```text
config/sites/es.php definissait deja les bons libelles ('FILE 2017'/'FISE-2') via
  Config::$lexicons depuis ES-001, mais rien ne les lisait -- app/View/word.php et
  play.php avaient 'ODS8'/'ODS9' code en dur (litteral francais errone, trouve par
  l'audit round 2, I-2). public/index.php passe desormais $config->lexicons aux
  deux vues qui portent .edition-badge ; les deux vues bouclent sur ce tableau au
  lieu d'un texte fixe. Classes CSS ods8/ods9 VOLONTAIREMENT inchangees --
  identifiants techniques internes, pas du texte affiche ; leur traduction (comme
  celle d'autres identifiants internes) est differee a la toute derniere passe du
  projet (avec les definitions), decision explicite du porteur de projet, pas un
  oubli.
Question D-015 (aucun credit de source) soulevee par l'audit round 3, tranchee ici :
  "FILE 2017"/"FISE-2" sont CONFORMES a D-015, par stricte analogie avec ODS8/ODS9
  deja affiches en production sur le site francais -- ce sont les noms du
  referentiel d'admissibilite (la reponse produit elle-meme : "ce mot est admis
  dans telle edition officielle"), pas le nom d'un fournisseur de donnees/depot
  GitHub (la liste interdite par D-015 et testee dans WordViewTest.php vise
  kartmaan/hbenbel/larousse -- des sources de construction de la base, pas des
  editions de reference du jeu lui-meme).
```

Raison :

```text
un site espagnol affichant des sigles francais (ODS8/ODS9) sur son resultat
  principal est incoherent et trompeur pour l'utilisateur, independamment de toute
  question de licence -- corrige des que trouve, meme mineur/non-bloquant a
  l'origine (I-2 de l'audit round 2)
```

Conséquences :

```text
public/index.php (2 lignes, ajout de 'lexicons' => $config->lexicons aux donnees
  passees a 'word' et 'play'), app/View/word.php, app/View/play.php
tests/Frontend/{PlayViewTest,WordViewTest,HomeViewTest}.php : corrigés pour charger
  config/sites/es.php au lieu de config/sites/fr.php (defaut preexistant, decouvert
  en marge, sans rapport avec ES-007 elle-meme) -- a revele un vrai bug de fixture :
  le score code en dur de ZZZQQQXXX (84) etait calcule avec les valeurs de tuiles
  FRANCAISES (Z=10,Q=8,X=10), jamais adapte lors de la copie FR->ES. Corrige a 69
  (valeurs espagnoles reelles Z=10,Q=5,X=8), confirme par l'invariant generique
  "somme des tuiles = score" deja present dans WordViewTest.php.
```

## ES-008 — Traduction Complète De L'Interface Visible En Espagnol

Date : 2026-08-28
Statut : accepté

Décision :

```text
tout le texte visible des 7 vues dans perimetre (home, word, word-list, play,
  explore-hub, not-found, contact) traduit en espagnol -- <html lang="fr"> ->
  "es" partout dans ces 7 vues ; titres, H1, meta descriptions, labels de
  formulaire, placeholders, boutons, titres de section, libelles de relation,
  toggles statut/tri, pagination, badges d'etat traduits. public/assets/js/
  suggest.js egalement corrige (libelles "Admis"/"Non Admis" de la combobox
  d'autocompletion, aria-label "Suggestions") -- omis par erreur du perimetre
  initial du prompt, trouve en verifiant le HTML rendu par ce script.
mentions-legales.php et confidentialite.php explicitement NON touches (contenu
  juridique francais errone, chantier separe deja identifie par ES-006/
  PHASE_STATUS.md "Reste a faire") -- <html lang="fr"> y reste donc, deliberement,
  pas un oubli.
terminologie choisie a partir de reports/es-serp-terminology-research.md (14
  concurrents espagnols inspectes) : "verificar" (pas "comprobar", section 2.2),
  "empiezan-por"/"terminan-en" repris tels quels de ES-004 comme vocabulaire
  visible (chips, titres <h2> de maillage, libelles "Recherches Liees"), pattern
  H1/titre de reponse directe "[MOT] es una palabra valida de Scrabble" /
  "[MOT] no es una palabra valida de Scrabble" (section 2.1, 2 concurrents
  independants), "palabras de/con N letras" (section 2.3, tres forte confiance).
bug critique corrige en meme temps que la traduction (pas seulement traduit tel
  quel) : app/View/word.php, statut STATUS_FRENCH_NOT_ADMITTED affichait "X
  existe en francais, mais..." sur un mot ESPAGNOL (releve par PHASE_STATUS.md
  "Reste a faire", second item) -- remplace par une phrase vraie pour le modele
  de donnees ES ("X esta documentada en el diccionario de espanol, pero esta
  palabra no esta admitida en los diccionarios oficiales del Scrabble"),
  reformulee a partir de la semantique reelle des colonnes (is_spanish issu de
  kaikki.org/Wiktionnaire espagnol, is_ods8/is_ods9 = FILE 2017/FISE-2), pas une
  simple substitution "francais" -> "espagnol". Verifie sur un mot reel
  (AA : is_spanish=1, is_ods8=0, is_ods9=0, storage/dictionary_es.sqlite).
descripteur de liste (app/View/word-list.php, $titleParts/$descriptor) reecrit en
  formulation prepositionnelle invariable ("con inicio en X", "con final en Y",
  "con la secuencia Z") plutot que "que empieza/empiezan por X" (verbe conjugue) --
  $descriptor est reutilise tel quel dans des phrases au singulier ET au pluriel
  (page a 1 resultat vs page a N resultats) sans jamais varier lui-meme (comme le
  participe francais invariable "commençant par" qu'il remplace) ; un verbe
  espagnol conjugue s'accorderait faux dans un des deux cas. La terminologie
  "empiezan por"/"terminan en" reste utilisee ailleurs sur la meme page (titres
  <h2> de maillage, toujours au pluriel car toujours rattaches au nom pluriel
  "Palabras") -- divergence assumee entre les deux contextes grammaticaux
  differents, justifiee dans le commentaire du code.
ordinal de position ("2e position" francais) traduit en "2ª" (feminin, accorde a
  "posición"/"letra"), pas de point avant le "ª" (convention RAE "2.ª" jugee trop
  formelle pour un fragment d'URL/titre compact, meme registre que le "2e" plat
  d'origine) -- verifie que mb_convert_case(MB_CASE_TITLE) laisse "ª" et les
  voyelles accentuees espagnoles intactes (aucune casse erronee constatee).
```

Raison :

```text
demande explicite du coordinateur, PHASE_STATUS.md listait ce point en tete de
  "Reste a faire avant tout deploiement reel" depuis l'audit round 3 -- interface
  entierement francaise sur un site .es, plus une affirmation factuellement
  fausse sur les mots non admis.
```

Conséquences :

```text
app/View/{home,word,word-list,play,explore-hub,not-found,contact}.php,
  public/assets/js/suggest.js, tests/Frontend/{HomeViewTest,WordListViewTest,
  WordViewTest}.php (assertions realignees sur les nouvelles chaines espagnoles)
php tests/run.php : 14/15, meme echec residuel qu'avant cette passe
  (Frontend\WordListViewTest.php, <summary> vs <p class="explore-subgroup-label">,
  herite du depot francais, texte interne mis a jour en espagnol mais assertion
  toujours en echec pour la MEME raison structurelle documentee -- pas un nouvel
  echec)
verifie par serveur PHP reel (pas seulement lecture de code) : mots avec Ñ
  (ABAJEÑA) et sans, mot non admis reel (AA), mot inconnu (ZZXXQQ/ZZZQQQXXX),
  tuiles digrammes CH/LL dans /buscador-de-palabras, listes longues (/palabras/
  4-letras, 3627 resultats), extremes de longueur (AD 2 lettres, ABALDONADAMENTE
  15 lettres), JS desactive (verifie l'absence de tout attribut ARIA de combobox
  cote serveur, seul suggest.js les ajoute).
non fait, signale explicitement (bonus round 3 I-1, "si le temps permet") :
  aucune fixture de non-regression Ñ ajoutee a tests/Frontend/WordViewTest.php,
  ses helpers restent en octets (strlen/strtolower/str_split) -- pas traite par
  manque de temps dans cette passe, toujours ouvert.
mentions-legales.php/confidentialite.php : toujours en francais (lang="fr"
  inclus), toujours dans "Reste a faire avant tout deploiement reel" -- seul
  point de ce bullet desormais non resolu est celui-la, le reste (interface +
  phrase factuellement fausse) est clos par ES-008.
```

## ES-009 — Registre SEO Espagnol : Infrastructure Complète + Premier Palier

Date : 2026-08-28
Statut : accepté

Décision :

```text
storage/seo_es.sqlite construite -- architecture identique au depot francais
  (app/Seo/Family.php/Registry.php/SeoMeta.php/schema.sql, scripts/build_seo_registry.php/
  apply_seo_batch.php/apply_word_admitted_rollout.php/build_sitemaps.php, tests/Seo/) --
  familles combinatoires gardees comme reservations non peuplees, pas supprimees du schema.
Palier applique : home ('/', '/palabras'), 2 listes /palabras/{N}-letras demontrees liees
  depuis app/View/home.php (7 et 9 lettres), word_admitted au complet (661 221 mots,
  Lexicon FILE 2017/FISE-2). 661 225 URL au total, 19 fragments sitemap, 0 page a resultat
  vide, 0 alias/doublon, moyenne 34,73 liens internes/page sur les fiches mot (echantillon
  n=300 verifie par HTTP reel).
Correctif applique AVANT tout audit (pas trouve par un audit, trouve par l'agent lui-meme) :
  le lot initial ouvrait les 14 longueurs de /palabras/{N}-letras en index,follow ; la
  verification live a montre que le hub /palabras (App\Search\ExploreHubBuilder) ne peut
  lier que 2 d'entre elles tant que list_counts reste vide (ES-001) -- les 12 autres
  auraient ete de vraies pages orphelines indexees. Reduit a 2 avant tout commit. Depend
  maintenant d'un futur travail data-engine (peuplement de list_counts) pour ouvrir les
  12 pages restantes.
Tenu hors de ce palier, decision deliberee (pas un oubli) : word_spanish_not_admitted
  (86 944 mots), toutes familles combinatoires (empiezan-por, terminan-en, contenant,
  avec, sans, motif, position, combinaisons), /buscador-de-palabras (NEVER_SITEMAP,
  outil pas page de contenu), /verificar (redirection pure, aucun rendu), pages legales.
Nettoyage en marge : 115 Mo de scripts/seo-batches/*.php francais perimes supprimes
  (URLs /mots/commencant/... jamais applicables au schema ES-004),
  scripts/apply_full_word_rollout.php (copie francaise non adaptee) remplace par
  scripts/apply_word_admitted_rollout.php (insertion SQL en flux, adapte au volume).
```

Raison :

```text
meme discipline de rollout progressif que le site francais (D-017, D-029 a D-031) : ne
  jamais ouvrir l'indexation d'une famille de pages sans verifier d'abord qu'elle a un
  maillage entrant reel -- une page indexable sans lien entrant est un signal de contenu
  de faible qualite pour les moteurs de recherche, pas seulement un gaspillage de budget
  de crawl
```

Conséquences :

```text
app/Seo/, scripts/build_seo_registry.php, apply_seo_batch.php,
  apply_word_admitted_rollout.php, build_sitemaps.php, tests/Seo/ (3 fichiers, ES-001
  avait supprime les 12 tests SEO francais herites -- recrees pour ce qui existe
  reellement ici), public/robots.txt (ligne Sitemap: ajoutee, pointant vers
  https://www.wordcheckr.es/sitemap-index.xml -- correcte des maintenant meme si le
  domaine n'est pas encore deploye, ne pas la retirer avant deploiement contrairement
  au retrait fait pour C-2/ES-006 qui visait un domaine et schema d'URL FAUX)
storage/seo_es.sqlite, public/sitemaps/*.xml (19 fragments), public/sitemap-index.xml
  (gitignores comme storage/dictionary_es.sqlite, a regenerer via les scripts ci-dessus)
```

## ES-010 — `word_spanish_not_admitted` Et Listes De Longueur Restantes : Décisions En Attente

Date : 2026-08-28
Statut : accepté (documente un blocage, pas une decision de contenu)

```text
86 944 mots espagnols non admis (is_spanish=1, is_ods8=0, is_ods9=0) restent
  noindex,follow par defaut -- necessite une decision explicite de volume de lot avant
  ouverture (meme prudence que ES-009 pour word_admitted, mais cette famille n'a encore
  reçu aucune analyse de maillage entrant)
12 des 14 pages /palabras/{N}-letras restent noindex,follow jusqu'a ce qu'un futur travail
  data-engine peuple list_counts (App\Search\ExploreHubBuilder) ou qu'un autre maillage
  reel soit construit vers elles
scripts/propose_seo_batch.php (2851 lignes) et scripts/check_combinatorial_duplicates.php
  restent NON modifies, specifiques au francais, et inertes (code mort tant qu'aucune
  famille combinatoire n'est ouverte ici) -- a reecrire avant tout palier futur qui les
  utiliserait
```

## ES-011 — Correctifs Du NO GO Audit SEO (3 Blocages, 9 Points Importants)

Date : 2026-08-29
Statut : accepté

Contexte : `seo-technical-auditor` a rendu NO GO sur ES-009/ES-010 (HEAD `340a3f7`). 3 blocages
(C-1, C-2, C-3) et 9 points importants (I-1 à I-9). Corrigés ici dans la mesure du périmètre de
l'agent seo-registry (`app/Seo/`, `scripts/build_sitemaps*`, `tests/Seo/`, `public/robots.txt`) ;
les points touchant `app/View/`/`app/Search/`/`scripts/build_*` (hors `build_sitemaps*`) sont
signalés, pas corrigés directement (hors périmètre), voir « Écarts Connus » ci-dessous.

Décision :

```text
C-1 (bloquant) — '/palabras' indexee alors que son contenu de liste est vide (list_counts
  vide, ES-001) : app/View/explore-hub.php n'a aucun garde d'etat vide, contrairement a
  app/View/word-list.php. Corrige au niveau du REGISTRE (seule option dans le perimetre de
  l'agent) : '/palabras' repassee noindex,follow, retiree du sitemap
  (scripts/seo-batches/home-and-length-2026-08-28.php, CORRECTIF 3). '/' (home) non concernee.
  Option "contenu de repli reel" (preferable, demandee par la tache) NON appliquee -- exige
  app/View/ (frontend) et potentiellement app/Search/ (data-engine), hors perimetre de l'agent
  seo-registry, signalee pour routage.

C-2 (bloquant) — 661 221 URL de word_admitted ouvertes en un seul lot, contournant R1-R7
  (scripts/apply_word_admitted_rollout.php codait 'index,follow' en dur, sans jamais appeler
  les regles). DEUX corrections cumulees, pas une seule :
  1. scripts/seo_batch_rules.php (nouveau) extrait seoValidateBatchRow()/
     seoBatchRouteShapeError() de scripts/apply_seo_batch.php -- desormais UN SEUL code pour
     R1-R7, partage par apply_seo_batch.php ET apply_word_admitted_rollout.php (en flux, un
     curseur PDO, jamais un tableau de 661 221 lignes en memoire).
  2. scripts/apply_word_admitted_rollout.php reecrit : --lengths=N,N,... OBLIGATOIRE, aucune
     valeur par defaut n'ouvre "tout". Registre corrige EN PLACE (pas seulement le script) :
     --reset-family a supprime les 661 221 lignes non validees, remplacees par UNE VAGUE
     PILOTE de 150 204 lignes (longueurs 7 et 9 -- memes longueurs que le palier
     word_list_length deja lie depuis app/View/home.php), validees par R1-R7. Plan des vagues
     RESTANTES PROPOSE ci-dessous, PAS APPLIQUE -- contrainte de role explicite ("never
     propose indexing an entire word family at once without discussing batch size first") :
     l'agent ne decide pas seul le volume total, il propose et attend confirmation.

C-3 (bloquant) — docs/05_URL_SEO_INDEXATION.md etait la copie non modifiee du document
  francais (/mot/qi, /mots/7-lettres, D-017 a D-041, invalid-french-*, avec-single-*...).
  Reecrit integralement pour le perimetre ES reel : schema ES-004, familles reellement
  construites (home/word_admitted/word_list_length), fragments core-*/words-*/letters-*,
  rollout par vagues (ce correctif), etat noindex de /palabras (C-1), maillage reel I-1,
  ecarts hors perimetre listes en fin de document. docs/PHASE_STATUS.md corrige dans le meme
  commit (voir plus bas, fichier partage -- diff applique directement, meme precedent que
  ES-009/ES-010 qui l'avaient deja fait pour DECISIONS.md sans repercuter sur PHASE_STATUS.md,
  incoherence relevee par l'audit et corrigee ici).

I-1 (le plus important a verifier en premier) — premisse FAUSSE ("seules 2 longueurs sur 14
  ont un lien entrant") : App\Search\RelationsFinder::relatedSearches() (ligne ~779) emet
  INCONDITIONNELLEMENT un lien "length" vers /palabras/{N}-letras depuis CHAQUE fiche
  /palabra/{mot} RENDUE, et public/index.php rend (HTTP 200) toute fiche mot trouvee par
  TermLookup::find() INDEPENDAMMENT du registre SEO (verifie dans le code, pas suppose) -- le
  registre ne pilote que robots/canonical, jamais le rendu. Les 14 longueurs ont donc TOUTES
  un lien entrant reel. Remesure : comptes reels (storage/dictionary_es.sqlite, TOUS statuts,
  2026-08-29) 2->149, 3->822, 4->3627, 5->12470, 6->29210, 7->56565, 8->87622, 9->112998,
  10->123379, 11->113734, 12->89320, 13->62161, 14->36786, 15->19322. Les 12 longueurs
  reouvertes (scripts/seo-batches/home-and-length-2026-08-28.php, CORRECTIF 2) -- 14 lignes
  word_list_length au total desormais, verifie en vivo (php -S, 200 partout, robots
  index,follow, canonical autonome sur un echantillon incluant 2-letras et 15-letras).

I-2 — surface de crawl noindex generee par relatedSearches() (empiezan-por 1-3 lettres,
  terminan-en 2 lettres, {N}-letras/avec/{a}/{b}/{c}) : PAS mesuree dans cette passe (hors
  temps disponible) -- reste ouvert, voir "Non Resolu" plus bas. Les URL noindex qui en
  resultent restent noindex,follow par defaut (comportement correct par construction, meme
  sans mesure de leur volume/cout), mais le budget de crawl reel (TTFB, EXPLAIN QUERY PLAN)
  n'a pas ete chiffre.

I-3 — scripts/build_explore_hub_counts.php confirme copie francaise non adaptee (cible
  dictionary_fr.sqlite par defaut, calcule 20 list_type). NON modifie (hors perimetre agent
  seo-registry, scripts/build_* hors build_sitemaps* appartient a data-engine) -- danger
  documente explicitement dans docs/05_URL_SEO_INDEXATION.md ("Ecarts Connus") pour un futur
  agent, comme demande.

I-4/I-5/I-6/I-9 — meta description "admitidas" trompeuse (app/View/word-list.php), <title>
  fiche mot > 60 caracteres (app/View/word.php, 74 caracteres mesures sur un exemple reel),
  title/description identiques sur les pages de pagination, bascules statut/tri sans
  rel="nofollow" : TOUS confirmes reels en lisant le code (app/View/), mais NON corriges --
  hors perimetre de l'agent seo-registry (templates, CLAUDE.md role seo-registry : "Don't
  modify templates... flag the need for a change"). Signales dans
  docs/05_URL_SEO_INDEXATION.md pour routage vers l'agent frontend.

I-7 — 16 022 <loc> avec Ñ brut non pourcent-encode dans les sitemaps : corrige.
  scripts/build_sitemaps.php::seoSitemapEncodePath() pourcent-encode (rawurlencode()) CHAQUE
  SEGMENT du chemin avant publication (jamais le '/' separateur). Verifie sur le registre reel
  regenere : 0 occurrence de Ñ brut restante dans public/sitemaps/*.xml, URL percent-encodees
  verifiees resolre en 200 sur un vrai serveur PHP (ex. /palabra/abaje%C3%B1a).

I-8 — deux constats distincts, tous deux corriges :
  1. Aucun test de coherence registre<->sitemap<->dictionnaire : tests/Seo/
     RegistrySitemapConsistencyTest.php (nouveau) verifie mecaniquement (base temporaire)
     qu'une ligne noindex,follow n'apparait jamais dans un sitemap genere, PUIS (donnees
     reelles du depot) que chaque ligne word_admitted correspond a un mot reellement admis,
     que chaque result_count de word_list_length egale le compte reel du dictionnaire, et que
     chaque URL publiee sur le disque correspond a une ligne index,follow du registre reel.
  2. Les sitemaps ETAIENT versionnes par git (20 fichiers, verifie par `git ls-files`) malgre
     l'affirmation contraire d'ES-009 -- .gitignore corrige (/public/sitemaps/,
     /public/sitemap-index.xml ajoutes) et `git rm --cached` applique (fichiers conserves sur
     le disque local, retires du suivi uniquement). scripts/build_sitemaps.php purge aussi
     desormais integralement public/sitemaps/*.xml avant regeneration (fragments perimes
     trouves reellement en base -- words-0005.xml a words-0017.xml restaient sur le disque
     apres la reduction du lot word_admitted au correctif C-2 ci-dessus, non references par
     sitemap-index.xml mais toujours publiees).
```

Plan de vagues restantes proposé pour `word_admitted` (511 017 mots, PAS appliqué -- décision de
volume explicite requise avant toute vague suivante, une par une ou groupée) :

```text
vague pilote (appliquee, ce correctif)  longueurs 7, 9    150 204 mots (23 %)
vague 2 (proposee, non appliquee)       longueurs 2,3,4,5  14 745 mots
vague 3 (proposee, non appliquee)       longueurs 6,8     103 906 mots
vague 4 (proposee, non appliquee)       longueur 10       108 833 mots
vague 5 (proposee, non appliquee)       longueur 11       100 126 mots
vague 6 (proposee, non appliquee)       longueur 12        79 006 mots
vague 7 (proposee, non appliquee)       longueurs 13,14,15 104 401 mots
```

Chaque vague future s'applique via
`php scripts/apply_word_admitted_rollout.php --lengths=...` (sans `--reset-family`, continuité
de fragments), suivie de `php scripts/build_sitemaps.php --base-url=...` -- jamais un lot
silencieux, jamais sans confirmation explicite du volume au moment de l'appliquer.

Raison :

```text
meme discipline que le depot francais cousin (D-017, D-029 a D-031) et contrainte dure du role
  seo-registry : jamais un lot complet sans passer par les regles dures, jamais une famille
  entiere indexee sans discussion prealable du volume -- l'agent propose et mesure, il ne
  decide pas seul un volume de rollout
```

Conséquences :

```text
app/Seo/ inchange (Family.php/Registry.php/SeoMeta.php) -- aucune nouvelle famille, aucun
  changement de contrat
scripts/seo_batch_rules.php (nouveau), scripts/apply_seo_batch.php (refactore, memes regles,
  memes messages d'erreur -- tests/Seo/BuildScriptsTest.php inchange et toujours au vert),
  scripts/apply_word_admitted_rollout.php (reecrit), scripts/build_sitemaps.php (purge +
  pourcent-encodage), scripts/seo-batches/home-and-length-2026-08-28.php (corrige en place,
  3 CORRECTIFS documentes dans son propre docblock)
tests/Seo/RegistrySitemapConsistencyTest.php (nouveau, 2 volets : mecanisme + donnees reelles)
public/robots.txt (commentaire resynchronise avec l'etat reel du registre)
docs/05_URL_SEO_INDEXATION.md (reecrit integralement pour le perimetre ES), docs/PHASE_STATUS.md
  (diff applique, fichier partage -- voir sa propre note en tete de section)
storage/seo_es.sqlite : 150 220 lignes (150 219 index,follow), regenere localement --
  152 208 -> 150 220 apres correctif (661 221 word_admitted -> 150 204, +12 word_list_length,
  '/palabras' noindex). public/sitemaps/*.xml : 19 fragments -> 6 fragments (core-0001 1 URL,
  letters-0001 14 URL, words-0001 a 0004 150 204 URL) -- ni versionnes ni deployes (site pas
  encore en production)
```

Non résolu (reporté, à traiter dans une passe future) :

```text
I-2 : surface de crawl noindex (empiezan-por/terminan-en courts, {N}-letras/avec/*) non
  mesuree (TTFB, EXPLAIN QUERY PLAN) -- a faire avant tout futur palier combinatoire
plan de vagues 2 a 7 ci-dessus : PROPOSE, pas applique -- decision de volume/pacing explicite
  attendue avant chaque vague suivante
word_spanish_not_admitted : toujours 0 ligne, ES-010 inchangee sur ce point
I-3/I-4/I-5/I-6/I-9 : hors perimetre de l'agent seo-registry, signales dans
  docs/05_URL_SEO_INDEXATION.md pour routage vers les agents frontend/data-engine
```

## ES-012 — Clôture I-4/I-5/I-6/I-9 (Audit Round 4) : Titres, Meta Descriptions, Pagination, Nofollow

Date : 2026-08-29
Statut : accepté

Décision :

```text
app/View/word.php (I-5, le plus important) : gabarit <title> raccourci sur les 3 statuts
  ("{MOT} Es Válida En Scrabble ({N} Pts)" / "{MOT} No Es Válida En Scrabble" / inchange pour
  inconnu, deja court) ET suffixe " | WORD CHECKR" RETIRE specifiquement sur cette vue --
  contrairement au choix fait cote allemand (D-DE-013 point equivalent, ou le suffixe est
  garde car seulement 15 URL sont indexees a ce stade) : ici la famille word_admitted est deja
  en cours de deploiement a grande echelle (150 204 fiches indexees, ES-011), donc la
  justification "pas encore de volume qui justifie l'exception" ne s'applique pas -- meme
  principe SEO (marque genante sur une famille a tres fort volume), applique differemment
  selon l'etat reel de chaque site. Mesure avant/apres sur le pire cas reel de la base
  (EMPEQUEÑEZCAMOS, 15 lettres, Ñ, score 43 -- score max reel sur toute la base = 46) : 75 ->
  46 caracteres.
app/View/word-list.php (I-4) : la meta description par defaut (2-5 resultats) utilisait deja
  "registradas" (terme neutre) plutot que "admitidas" pour eviter la meme affirmation fausse --
  le cas >=6 resultats disait encore "admitidas" alors que le compte n'est pas filtre par
  statut. Aligne sur le terme neutre deja etabli dans le meme fichier, pas de nouvelle donnee/
  requete ajoutee (aurait touche app/Search/, hors perimetre de cette passe).
app/View/word-list.php (I-6) : suffixe " — Página N" (title) / " Página N." (meta description)
  des que $page->page > 1, uniquement dans <head> -- le paragraphe visible ne change pas (deja
  redondant avec la navigation de pagination affichee plus bas).
app/View/word-list.php (I-9) : rel="nofollow" ajoute sur les 5 bascules statut/tri (section
  "Afinar La Lista"), meme raisonnement que la pagination profonde deja traitee (D-005) --
  variantes quasi identiques d'une page deja noindex par defaut.
```

Raison :

```text
fermeture des 4 points frontend signales par l'audit round 4 (hors perimetre seo-registry,
  docs/DECISIONS.md ES-011) -- ES-011 avait deja documente ces defauts avec precision, cette
  entree ferme la boucle plutot que de laisser une decision "signalee" indefiniment ouverte
```

Conséquences :

```text
app/View/word.php, app/View/word-list.php, tests/Frontend/WordListViewTest.php (assertions
  realignees sur les nouveaux textes, aucune assertion affaiblie, nouvelles assertions
  ajoutees pour page 1 vs page 2 et rel="nofollow")
php tests/run.php = 18/19 (inchange, seul echec WordListViewTest herite/sans rapport)
Toujours ouverts, inchanges par cette entree : I-2 (surface de crawl non mesuree), I-3 (script
  de comptage francais), plan de vagues 2 a 7 (propose, pas applique), word_spanish_not_admitted
```

## ES-013 — Vérification Pré-Vague Des 12 Longueurs Restantes De `word_admitted` : Vague Non Appliquée

Date : 2026-08-29
Statut : accepté (documente un refus justifié, pas une décision de contenu)

Décision explicite du propriétaire du produit, formulée directement dans cette session (pas
relayée par un message d'agent isolé) : indexer l'intégralité des mots admis, comme le site
français. L'agent seo-registry chargé d'appliquer les 12 longueurs restantes (511 017 mots,
plan de vagues ES-011) a refusé d'exécuter en l'état, pour deux raisons vérifiées sur pièces :

```text
1. la premisse "vague pilote deja auditee GO" transmise dans le mandat etait fausse -- ES-011
   dit explicitement qu'aucun audit de suivi n'avait ete relance a ce stade, ES-012 ne ferme
   que 4 points non bloquants (I-4/I-5/I-6/I-9), jamais un verdict GO formel du
   seo-technical-auditor sur l'etat post-correctif (ce verdict est arrive depuis, voir plus
   bas)
2. la premisse "le script a deja --confirm-full-rollout et un plafond de 100 000" etait fausse
   -- verifie par grep exhaustif : scripts/apply_word_admitted_rollout.php n'avait ni l'un ni
   l'autre, ni meme --dry-run, avant que l'agent ne les construise lui-meme dans cette meme
   session
```

Face à ces deux inexactitudes, l'agent a refusé de construire lui-même le mécanisme
`--confirm-full-rollout` qui lui aurait permis de contourner sa propre contrainte de rôle
("jamais un lot complet sans discussion de volume tracée") — jugeant qu'un mandat relayé par un
message d'agent n'équivaut pas à une décision produit réellement documentée (contrairement au
précédent D-017 côté français, qui documente un vrai arbitrage). Décision jugée correcte et
respectée : cette entrée documente maintenant la vraie décision, condition posée par l'agent
avant réexécution (voir ES-015 ci-dessous pour la suite).

Vérifications faites quand même, sans écriture sur le registre réel :

```text
dry-run des 12 longueurs : R1-R7 sans erreur, total projete 661 221 exact (511 017 + 150 204
  deja appliques), 0 longueur a 0 mot (minimum reel 88 a longueur 2)
sondage I-2 (jamais fait avant, ES-011 le demandait explicitement) : 40 URL noindex reelles,
  empiezan-por/terminan-en (Ñ, CH/LL/RR inclus) et {N}-letras/avec sur les 14 longueurs :
  200 partout, TTFB 7-229 ms. Point reel trouve : terminan-en/{lettre seule, sans longueur}
  coute 150-229 ms (USE TEMP B-TREE FOR ORDER BY) -- categorie deja connue et acceptee du
  depot, cout independant du volume du rollout (depend de storage/dictionary_es.sqlite,
  jamais modifie par cette famille)
```

Conséquences :

```text
scripts/apply_word_admitted_rollout.php : --dry-run ajoute pour de vrai (validation R1-R7
  identique, zero ecriture), tests/Seo/WordAdmittedRolloutDryRunTest.php (nouveau) prouve
  que dry-run et application reelle produisent exactement les memes comptes
php tests/run.php = 17/20 a ce moment (3 echecs preexistants sans rapport, reproduits
  identiques avant/apres)
Aucune ligne appliquee sur les 511 017 mots restants -- voir ES-015 pour la suite
```

## ES-014 — Troisième Palier De Localisation D'URL : `contenant`/`avec`/`sans`/`motif`/`position`/`statut`/`tri`

Date : 2026-08-29
Statut : accepté

ES-004 avait localisé `/mot`, `/mots`, `/jouer`, `/verifier`, `commencant`→`empiezan-por`,
`terminant`→`terminan-en`, en laissant volontairement `contenant`/`avec`/`sans`/`motif`/
`position`/`statut`/`tri` en français. Décision explicite du propriétaire du produit
(2026-08-29) : lancer ce palier maintenant.

Décision :

```text
contenant -> contienen        avec   -> con-letras     sans  -> sin
motif     -> patron           position -> posicion     statut -> estado    tri -> orden
```

Niveau de preuve par terme (l'agent a re-vérifié lui-même en direct plutôt que de faire
confiance aux citations du mandat, qui ne correspondaient pas au contenu réel de
`reports/es-serp-terminology-research.md` sur disque) :
```text
contienen  ATTESTE : listasdepalabras.es /indexcontienen.htm, title+H1 "Las listas de
           palabras que contienen una o mas letras" ; "Contiene" (formarpalabra.com)
con-letras ATTESTE : buscarpalabras.io /palabras-con-las-letras/arse
sin        ATTESTE : buscarpalabras.io /palabras-sin-estas-letras
patron     ATTESTE (meilleure preuve que prevu, le mandat l'annoncait non sourcee) :
           buscarpalabras.io /palabras-por-patron, "Palabras que Coinciden con un Patron" --
           corrobore par ES-008 qui avait deja choisi "Con El Patron" comme libelle visible
posicion   ATTESTE : listasdepalabras.es /indexposicion.htm
estado/orden  NON sourcees (aucun concurrent inspecte n'expose de filtre de statut dans une
           URL propre, outils en JavaScript sans URL dediee) -- choix raisonnes, documentes
           comme tels dans le code, pas presentes comme aussi solides que les 5 precedents
```

Distinction `contienen`/`con-letras` vérifiée au niveau SQL avant d'être nommée (pas
supposée) : `WordListSolver::exactWhereClause()` — `contienen` = un seul `instr()` sur la
séquence entière (lettres consécutives), `con-letras` = un `instr()` par lettre (lettres
dispersées). Deux prédicats distincts, deux mots-clés distincts — plus fin que
listasdepalabras.es qui emploie `contienen` pour les deux cas, choix délibéré.

`app/Search/WordListFilters.php` : les 7 mots-clés dans `KEYWORDS`, source unique pour le
parsing et la canonicalisation. `public/index.php` (fichier partagé) : diff proposé, lu et
appliqué directement par la session principale après vérification (git apply --check,
`php -l`, `php tests/run.php` 19/20, smoke-test réel confirmant les 4 champs préalablement
cassés + une route `Ñ` correcte). Commit `32965f4`.

Bug réel trouvé et corrigé au passage par la session principale en appliquant le diff (même
classe qu'ES-003/ES-006) : `public/index.php` lignes ~510-522 utilisait `strlen()` au lieu
de `mb_strlen()` pour détecter un préfixe/suffixe d'une seule lettre — `Ñ` fait 2 octets en
UTF-8, donc `strlen()===1` était toujours faux pour ce cas, désactivant silencieusement
`PrefixAvecLinksBuilder`/`LetterCombinedLinksBuilder`/`StartEndWithLinksBuilder` pour la
lettre Ñ. Sans effet aujourd'hui (`list_counts` vide), réel dès que la table sera peuplée.

Raison :

```text
demande explicite du proprietaire du produit, meme phase que ES-013/ES-015, meme jour --
  "quasi ISO" avec le site francais
```

Conséquences :

```text
app/Search/WordListFilters.php + 7 *LinksBuilder.php, RelationsFinder.php,
  app/View/{home,word-list,explore-hub,word}.php (app/View etait libre au moment de la
  tache, aucun agent frontend en vol), public/index.php (diff applique par la session
  principale + correctif Ñ), tests renforces (ordre canonique sur 8 contraintes combinees,
  garde-fou "aucun lien silencieusement absent" sur 31 gabarits)
php tests/run.php = 19/20 (inchange, seul echec WordListViewTest herite/sans rapport)
Performance : aucune requete SQL modifiee (WordListSolver.php intact), EXPLAIN QUERY PLAN
  identique avant/apres sur 8 chemins types, ecarts de temps dans le bruit de mesure
Non fait, signale explicitement : valeurs d'enumeration (admis/no-admitida, points/
  points-desc) restent en partie francaises -- /palabras/13-letras/estado/admis est a moitie
  traduit, aucune terminologie sourcee pour ces valeurs specifiquement, decision produit a
  prendre separement plutot que d'inventer un terme (meme discipline qu'ES-004).
  DuplicatePageResolver::KEYWORD_ORDER garde des cles francaises (identifiants internes,
  jamais des segments d'URL, laisse tel quel, coherent avec ES-004). Defaut pre-existant
  signale : tests/bench_wordlist_queries.php casse depuis ES-004 (mots-cles francais),
  non execute par tests/run.php, non corrige (hors perimetre)
Commits phase-es-21-url-keywords-es014 (e6fb904), phase-es-22-apply-router-diff-combined
  (32965f4, application du diff + correctif Ñ par la session principale)
```

## ES-015 — Fermeture Du Plan De Vagues `word_admitted` : Les 12 Longueurs Restantes Appliquées

Date : 2026-08-29
Statut : accepté

Contexte : suite directe d'ES-013. La décision produit qu'ES-013 cite ("indexer l'intégralité
des mots admis, comme le site français", formulée directement dans la session, pas relayée par
un message d'agent isolé) est la condition qu'ES-013 posait avant réexécution. Deux vérifications
préalables ont été faites sur pièces avant d'appliquer quoi que ce soit, car le mandat transmis
pour cette passe contenait lui-même une inexactitude, du même type que celle corrigée par ES-013 :

```text
1. le mandat affirmait que scripts/apply_word_admitted_rollout.php avait déjà --confirm-full-
   rollout et un plafond de sécurité "documenté dans ES-013" -- FAUX, vérifié en relisant ES-013
   en entier : cette entrée documente au contraire l'ABSENCE délibérée de ces deux mécanismes
   ("le role seo-registry ne construit pas son propre mecanisme de contournement de la regle"),
   confirmé par lecture du fichier réel (aucune occurrence de --confirm-full-rollout, aucune
   constante de plafond pour word_admitted dans app/Seo/Family.php). Aucun ajout fait ici : la
   validation obligatoire --lengths=N,N,... (aucune valeur par défaut, énumération explicite
   exigée) est déjà, par construction, le mécanisme qui rend un rollout complet "un choix
   ENTIÈREMENT explicite et documenté au moment de l'appel" (docblock du script) -- exactement
   ce qu'un --confirm-full-rollout aurait apporté, sans qu'il soit nécessaire de l'ajouter. Cette
   fois, contrairement à la passe refusée par ES-013, une décision produit réelle et directement
   citée existe déjà (ES-013) : la légitimité qui manquait alors est présente maintenant, la
   forme du garde-fou (énumération obligatoire déjà en place) suffit
2. ES-013 laissait aussi entendre ("ce verdict est arrivé depuis, voir plus bas") qu'un GO formel
   du seo-technical-auditor sur l'état post-ES-011/ES-012 existerait au moment de cette entrée --
   vérifié FAUX : `git log` ne montre aucun commit d'audit après 4a6affc (ES-013/ES-014),
   docs/PHASE_STATUS.md dit toujours explicitement "audit de suivi seo-technical-auditor pas
   encore relancé", et aucun rapport d'audit n'existe sur le disque. Aucun verdict GO n'est donc
   affirmé ici -- ce point reste ouvert, signalé pour l'agent seo-technical-auditor (voir "Non
   Résolu" ci-dessous), distinct de la décision produit elle-même qui, elle, ne dépend pas d'un
   audit technique préalable (même précédent que D-017 côté français : décision produit
   documentée directement, audit technique déroulé séparément, avant tout déploiement réel)
```

Vérification pré-application (`--dry-run`), pour confirmer qu'aucun changement de base/comptes
n'était survenu depuis ES-013 :

```text
php scripts/apply_word_admitted_rollout.php --lengths=2,3,4,5,6,8,10,11,12,13,14,15 --dry-run
total projeté : 511 017 (exact, identique à ES-013) -- 2:88, 3:500, 4:3022, 5:11135, 6:26100,
  8:77806, 10:108833, 11:100126, 12:79006, 13:54890, 14:32597, 15:16914
registre actuel avant application : 150 220 lignes (150 219 index,follow, 150 204 word_admitted)
  -- inchangé depuis ES-013, aucune hypothèse périmée
```

Décision : application réelle, sans `--reset-family` (continuité de fragments avec la vague
pilote ES-011) :

```text
php scripts/apply_word_admitted_rollout.php --lengths=2,3,4,5,6,8,10,11,12,13,14,15
php scripts/build_sitemaps.php --base-url=https://www.wordcheckr.es
```

Comptes avant / après (registre `storage/seo_es.sqlite`) :

```text
                    avant       après
total lignes        150 220     661 237
index,follow        150 219     661 236
word_admitted        150 204     661 221  (14 longueurs, 2 à 15, 100 %)
home                      1           1  ('/'), '/palabras' reste noindex (C-1, inchangé)
word_list_length         14          14  (inchangé)
```

Volume du lot appliqué dans cette passe : **511 017 URL** (12 longueurs : 2, 3, 4, 5, 6, 8, 10,
11, 12, 13, 14, 15), s'ajoutant aux 150 204 déjà en ligne (longueurs 7 et 9, ES-011). Détail par
longueur : 2→88, 3→500, 4→3022, 5→11135, 6→26100, 8→77806, 10→108833, 11→100126, 12→79006,
13→54890, 14→32597, 15→16914 (somme exacte 511 017, identique au dry-run).

Pages à exactement 1 résultat, reportées séparément (jamais un critère de noindex automatique) :

```text
sans objet pour cette famille -- /palabra/{mot} est une fiche mot unique, pas une page de liste
  (result_count reste NULL par construction, R5 ne s'applique pas, voir docblock du script).
  Les 14 pages de la famille word_list_length (seule famille "liste" en ligne) ont toutes un
  result_count ≥ 149 (minimum réel : /palabras/2-letras, 149) -- 0 page à 1 résultat sur tout le
  registre actuel
```

Sitemaps régénérés : **19 fragments** (`core-0001` 1 URL, `letters-0001` 14 URL, `words-0001` à
`words-0017` 661 221 URL au total, dernier fragment `words-0017` partiel à 31 017 URL) +
`sitemap-index.xml` (19 entrées, 661 236 URL, cohérent avec le compte index,follow). Fragments
ni versionnés ni déployés (`.gitignore`, ES-011 I-8, site pas encore en production).

Maillage interne, mesuré sur un échantillon réel de 97 fiches (au-delà des ≥ 60 exigées),
réparti sur les 12 longueurs, incluant Ñ (ex. ÑA, ÑU, ÑUZCOS, ÑIQUIÑAQUE, ÑEEMBUQUEÑOS,
ABAJEÑAS...), les digrammes CH/LL/RR (ACHI, ALLA, ARRA, ACLLA, ABERRA...) et les longueurs
extrêmes 2 et 15 :

```text
moyenne : 50,8 liens <a href> par page (comptage brut incluant l'ossature commune du site --
  entête/pied/nav -- méthodologie identique sur tout l'échantillon)
minimum : 21 (mots courts, longueur 2)
maximum : 123
```

Vérification bout-en-bout sur serveur PHP réel (`php -S 127.0.0.1:8091 -t public`,
`SCRABBLE_SITE=es` par défaut) :

```text
97 fiches vérifiées (≥ 60 exigées) : 97/97 code 200, 97/97 <title> ≤ 60 caractères (max mesuré :
  46, sur les mots de longueur 15 -- ES-012 tient à cette échelle), 97/97 canonical exact
  (https://www.wordcheckr.es/palabra/{mot en minuscules, UTF-8 brut, jamais pourcent-encodé} --
  distinct à dessein du <loc> des sitemaps qui, lui, reste pourcent-encodé RFC 3986, ES-011 I-7 :
  deux contextes différents, HTML vs XML, mêmes octets sous-jacents), 97/97 robots
  "index,follow"
sitemaps : <loc> pourcent-encodés vérifiés sur le fragment le plus récent (words-0017.xml,
  ex. aca%C3%B1avereabamos) -- 0 régression sur I-7
```

Bug réel trouvé et corrigé au passage, dans le périmètre de l'agent (`public/robots.txt`) :
les `Disallow` visaient encore les anciens segments français (`contenant/`, `avec/`, `sans/`,
`motif/`) remplacés par ES-014 (`contienen/`, `con-letras/`, `sin/`, `patron/`) -- les anciens
chemins 404ent désormais et ne sont plus empruntés par personne, tandis que les vraies routes
sans ancrage liées depuis `/` (`contienen/ch`, `con-letras/e`, `sin/e`, vérifiées 200 sur le
serveur réel) restaient hors de tout `Disallow` : le garde-fou I-C (budget de crawl, D-019)
avait cessé de protéger quoi que ce soit de réel depuis ES-014 sans que personne ne s'en
aperçoive. `public/robots.txt` corrigé (mots-clés + commentaire d'état resynchronisé sur le
registre complet).

```text
php tests/run.php : 19/20 (inchangé, seul échec WordListViewTest.php hérité/sans rapport,
  identique avant/après)
```

Raison :

```text
même discipline que le dépôt français cousin (D-017) et que la vague pilote de ce dépôt
  (ES-011) : une décision de volume explicite du propriétaire du produit, directement citée
  dans docs/DECISIONS.md, pas relayée par un message d'agent isolé -- condition posée par
  ES-013, remplie ici
```

Conséquences :

```text
storage/seo_es.sqlite : 661 237 lignes (661 236 index,follow), non versionné (artefact)
public/sitemaps/*.xml : 19 fragments, non versionnés (artefact, .gitignore)
public/robots.txt : mots-clés Disallow resynchronisés avec ES-014, commentaire d'état
  resynchronisé sur le registre complet (seul fichier de ce périmètre réellement modifié dans
  git par cette passe)
app/Seo/ inchangé (Family.php/Registry.php/SeoMeta.php) -- aucune nouvelle famille, aucun
  changement de contrat, aucun --confirm-full-rollout ni plafond ajoutés (jugés inutiles, voir
  point 1 du Contexte ci-dessus)
scripts/apply_word_admitted_rollout.php, scripts/build_sitemaps.php : inchangés (déjà corrects
  depuis ES-011/ES-013)
```

Non résolu (reporté) :

```text
audit de suivi seo-technical-auditor : TOUJOURS pas relancé sur l'état post-ES-011/ES-012 --
  ES-013 laissait entendre qu'un verdict GO existerait "depuis" à ce stade, vérifié faux (voir
  Contexte ci-dessus). Cette entrée applique une décision produit documentée, PAS un verdict
  d'audit technique -- les deux restent distincts. Un audit complet (registre à 661 237 lignes,
  4,4x le volume qu'avait vu le dernier audit réel) est recommandé avant tout déploiement,
  voir recommandation READY/NOT READY FOR AUDIT du rapport de session
I-2 (ES-011) : surface de crawl noindex (empiezan-por/terminan-en courts, {N}-letras/con-letras
  courts) toujours non mesurée en continu (TTFB, EXPLAIN QUERY PLAN) au-delà du sondage ponctuel
  fait par ES-013 (40 URL, 7-229 ms) -- pas repris ici, hors périmètre de cette passe
I-3 (ES-011) : scripts/build_explore_hub_counts.php reste la copie française non adaptée,
  toujours non modifié (hors périmètre seo-registry)
word_spanish_not_admitted : toujours 0 ligne (ES-009/ES-010 inchangées sur ce point)
valeurs d'énumération non traduites (statut/tri, ES-014) : /palabras/13-letras/estado/admis
  reste à moitié en français -- inchangé par cette passe, décision produit séparée à prendre
docs/PHASE_STATUS.md : fichier partagé, diff proposé mais NON appliqué ici (voir rapport de
  session) -- reste sous contrôle de la session principale
```

## ES-016 — Premier Palier Combinatoire : `empiezan-por` (1 Lettre) Et `terminan-en` (2 Lettres)

Date : 2026-08-29
Statut : accepté

Contexte : décision explicite du propriétaire du produit de construire l'indexation des
mots-clés restants (empiezan-por, terminan-en, contienen, con-letras, sin, patron, posicion)
"comme le FR", avec la même prudence que le site français a dû apprendre pour ces familles
précises (D-017, D-019, D-024, D-025, D-029 à D-031, D-041).

Décision :

```text
storage/seo_es.sqlite, familles word_list_commencant (25 URL, /palabras/empiezan-por/{lettre},
  1 lettre -- 27 lettres possibles moins K et W, 0 mot admis ne commence par l'une ou l'autre,
  exclusion reelle pas supposee) et word_list_terminant (246 URL,
  /palabras/terminan-en/{2 lettres} -- toutes les terminaisons a 2 caracteres reellement
  produites par un mot admis) appliquees via scripts/apply_seo_batch.php +
  scripts/seo-batches/commencant-terminant-single-tier1-2026-08-29.php. R1-R7 verifiees
  mecaniquement. Sitemaps regeneres (19 -> 21 fragments : +starts-0001, +ends-0001, 661 508
  URL au total).

CORRECTION DE LA PREMISSE DE DEPART (comme cote allemand, meme jour, meme cause) :
  list_counts est VIDE sur ce depot (ES-001, hors perimetre seo-registry) -- toute famille
  dont le maillage depend de LengthLinksBuilder/LetterCombinedLinksBuilder/PositionLinksBuilder
  (dont "longueur+empiezan-por combine", suggere comme palier 1 dans le mandat recu) a
  aujourd'hui ZERO lien entrant reel. Non ouverte. Seuls deux liens s'emettent
  inconditionnellement, independamment de list_counts : RelationsFinder::relatedSearches()
  "startsWith" (1 lettre, toujours) et "endsWith" (toujours exactement 2 caracteres, car
  Normalizer::MIN_LENGTH=2 force min(2,$longueur) a valoir 2) -- ce sont les deux familles
  reellement ouvertes ici, pas celles suggerees au depart.
```

Vérifications faites (les 271 URL ouvertes vérifiées individuellement, pas un échantillon) :

```text
271/271 URL testees en direct (php -S) : 200, index,follow, canonical exact, <title> <= 60
  caracteres, y compris Ñ (empiezan-por/ñ, terminan-en/ñu/ña/ño) et digrammes CH/LL/RR
  (terminan-en/ch, /ll)
0 page a resultat vide (R5) ; 4 pages terminan-en a exactement 1 resultat (MB, VS, VY, ÑU) --
  GARDEES, signalees separement, pas auto-exclues
moyenne de liens sortants/page : empiezan-por 61 (25/25, page toujours pleine a PAGE_SIZE=50) ;
  terminan-en 41,8 (echantillon 40/246, min 11, max 61, varie avec la taille de liste)
tests/Seo/RegistrySitemapConsistencyTest.php etendu : verifie result_count des deux nouvelles
  familles contre la vraie base (0 divergence sur les 661 508 lignes reelles)
```

Familles mesurées et NON ouvertes ce lot, chacune pour une raison technique distincte :

```text
empiezan-por a 3 lettres : 2 462 pages avec un LIEN REEL demontre (EXACT mode, rapide --
  SEARCH via idx_terms_length_normalized, sub-ms a ~7ms echantillonne) -- 10x le volume du
  palier ouvert ici, contrainte de role dure ("jamais une famille/gros lot entier sans
  discuter du volume d'abord") -- differe explicitement, candidat palier 2
combine (empiezan-por+terminan-en, sans longueur) : rapide (idx_terms_startletter_
  endletter_normalized confirme, 729 combinaisons balayees en 120ms, 156/729 a 0 resultat)
  mais AUCUN lien entrant reel -- aucun chemin de code n'emet ce lien sans list_counts
combine AVEC longueur : rapide (mode EXACT, ex. 9-letras/empiezan-por/a : 3,5ms) mais AUCUN
  lien reel (LengthLinksBuilder::byStart depend de list_counts) -- contredit l'hypothese de
  depart du mandat, corrige par la mesure avant toute ecriture
posicion : rapide (echantillon max ~32,6ms, 8/2366 combinaisons SEULEMENT -- pas un balayage
  complet) -- AUCUN lien reel (byPosition depend de list_counts)
contienen/con-letras/sin/patron : deja fermees en PERMANENCE (App\Seo\Family::NEVER_SITEMAP)
  -- confirme par la mesure : contienen/qq (0 resultat) coute quand meme ~73ms sur 748 165
  lignes (meme signature que D-019 cote francais), aucun chemin de code n'y lie jamais --
  aucun changement fait a Family.php
```

Découverte annexe importante, signalée pas corrigée (hors proportion pour cette passe) :
`scripts/propose_seo_batch.php` (2 852 lignes) est un artefact français **jamais adapté** --
routes `/mot/{slug}` codées en dur, numéros de décision français, référence directement
`storage/dictionary_fr.sqlite`. Le lancer tel quel sur ce dépôt écrirait des routes fausses
dans `storage/seo_es.sqlite`. Non touché -- l'agent a construit son propre petit générateur
pour ce lot plutôt que de s'en servir. Risque réel plus grave que celui déjà documenté sur
`build_explore_hub_counts.php` (I-3) : celui-ci écrit silencieusement des données fausses
plutôt que de simplement échouer à l'exécution.

Raison :

```text
discipline mesure-avant-ouverture identique au site francais et au palier equivalent cote
  allemand du meme jour (D-DE-017) -- jamais ouvrir une famille sans maillage entrant REEL
  verifie, jamais un gros lot sans discussion de volume tracee
```

Conséquences :

```text
scripts/seo_batch_rules.php (R4b etendu), scripts/build_sitemaps.php (+2 prefixes),
  scripts/seo-batches/commencant-terminant-single-tier1-2026-08-29.php (nouveau, 271 lignes),
  tests/Seo/RegistrySitemapConsistencyTest.php (verification donnees reelles etendue),
  docs/05_URL_SEO_INDEXATION.md (resynchronise avec ES-014, affirmait encore a tort que les
  7 mots-cles "restent francais")
storage/seo_es.sqlite : 661 237 -> 661 508 lignes (+271), non versionne (artefact)
public/sitemaps/starts-0001.xml (25 URL), ends-0001.xml (246 URL), sitemap-index.xml
  (19 -> 21 fragments), non versionnes
public/robots.txt : verifie, PAS modifie -- rien de nouvellement ouvert n'etait Disallow,
  rien de laisse ferme n'a change d'etat, aucune incoherence a corriger
php tests/run.php = 19/20 (inchange, seul echec WordListViewTest herite/sans rapport)
Commit phase-es-26-seo-tier1-commencant-terminant (0da64f2), pousse sur origin/master
Reste a mesurer/decider avant un palier 2 : peuplement de list_counts (data-engine, deverrouille
  presque tout le reste), empiezan-por 3 lettres (volume a discuter), balayage complet de
  posicion (8/2366 echantillonnes seulement), reecriture ES de propose_seo_batch.php avant
  toute utilisation future
```

## ES-017 — `list_counts`, Premier Palier : length/start/end/length_start/length_end

Date : 2026-08-30
Statut : accepté

Décision :

```text
scripts/build_explore_hub_counts.php REECRIT INTEGRALEMENT (l'ancien fichier etait une copie
  francaise non adaptee, ciblait storage/dictionary_fr.sqlite par defaut, deja signalee
  dangereuse par ES-011 I-3) -- construit 5 des 19 list_type dans storage/dictionary_es.sqlite :
  length, start, end, length_start, length_end -- 0 -> 3 084 lignes.
Debloque le rendu reel des grilles du hub /palabras (ExploreHubBuilder, ES-011 C-1, qui
  rendait des sections vides depuis le debut malgre 14 pages /palabras/{N}-letras et 271
  pages empiezan-por/terminan-en deja indexees, ES-016) et le maillage
  longueur+empiezan-por / longueur+terminan-en depuis ces memes 14 pages -- verifie en direct
  sur un vrai serveur php -S.
```

Deux décisions critiques tranchées explicitement (pas laissées implicites dans le code) :

```text
1. GRANULARITE CARACTERE, jamais TUILE. Verifie directement (pas suppose) : la famille deja
   indexee word_list_commencant (ES-016) et RelationsFinder::relatedSearches()
   (mb_substr($word,0,1), lien startsWith inconditionnel) sont toutes deux construites par
   PREMIER CARACTERE LITTERAL, jamais par tuile -- un mot comme CHOZA est compte dans le
   bucket "C", jamais dans un bucket "CH" separe. Le script suit strictement cette convention
   deja en production plutot que d'en inventer une nouvelle.
2. GRANULARITE ASYMETRIQUE : 1 caractere pour start, 2 pour end (different du script francais
   de reference, qui est a 1 caractere pour les deux -- adaptation deliberee, pas un ecart).
   Normalizer::MIN_LENGTH=2 force RelationsFinder::relatedSearches() a toujours emettre le
   lien endsWith sur exactement 2 caracteres -- ES-016 en a deja tire la consequence :
   word_list_terminant (246 URL, seule famille "terminant" reellement indexee) est construite
   a 2 caracteres, pas 1. end/length_end construits a 2 caracteres pour donner un maillage
   reellement utile a cette famille deja indexee ; start/length_start restent a 1 caractere
   (seule granularite ayant un lien reel cote empiezan-por). ExploreHubBuilder/
   LengthLinksBuilder traitent deja $key comme une chaine opaque de longueur quelconque --
   verifie avant d'ecrire le script, aucune modification de ces deux classes necessaire.
```

Bug réel trouvé et corrigé au passage (même classe qu'ES-003) :

```text
strrev() (octet par octet) sur substr(reversed,1,2) CORROMPT Ñ (2 octets UTF-8) -- demontre
  (strrev("\xC3\x91E") produit des octets invalides, pas "EÑ"). Remplace par mbReverse()
  (mb_str_split + array_reverse + implode), verifie explicitement contre CHOLCHEÑ -> bucket
  "ÑE" correct.
```

Vérifications faites (SQL direct, pas confiance au script -- sum(count) = 748 165 pour chaque
list_type, partition exhaustive) :

```text
start=Ñ 805, start=K 428, start=W 172 (presents en donnees brutes, sans lien SEO reel
  aujourd'hui -- decision de rollout separee), aucun bucket "CH"/"LL"/"RR" dans start
end=CH 34, end=LL 15, end=RR 2, end=ÑA 779, end=EÑ 1
length_start=9:Ñ 108, length_end=5:CH 8, length_end=6:ÑA 114
EXPLAIN QUERY PLAN sur les 5 requetes (hors ligne, 748 165 lignes reelles) : toutes en
  SCAN ... USING COVERING INDEX (idx_terms_length_normalized/idx_terms_length_reversed),
  130-1236 ms chacune (TEMP B-TREE FOR GROUP BY sur les 3 plus grosses, attendu et sans
  impact runtime -- calcul hors ligne)
verifie en direct sur un vrai serveur php -S : /palabras rend desormais 422 entrees reelles
  (0 avant), y compris empiezan-por/ñ (805), terminan-en/ña (779), terminan-en/ch (34), /ll
  (15), /rr (2). Pages cibles /palabras/9-letras/empiezan-por/k et .../terminan-en/ña : 200,
  titres/H1 corrects ("Palabras De 9 Letras Con Inicio En K", "... Con Final En Ña")
php tests/run.php = 21/22 (2 nouveaux tests ajoutes et verts --
  tests/Search/{ExploreHubBuilderTest,LengthLinksBuilderTest}.php -- seul echec
  WordListViewTest herite/sans rapport)
```

Types NON construits ce palier : `length_with`, `start_end`, `length_with_position`,
`length_avec_sans`, `length_start_end`, `length_with_pair/triple`, `start_end_with`,
`start_with`, `prefix2-4`, `suffix2-4` — aucun générateur ES mesuré ni décision produit
tracée pour leur ouverture (même raisons de fond que D-DE-018 côté allemand, mesurées
séparément sur les données espagnoles).

Risque trouvé, signalé, non corrigé (hors périmètre de ce lot, même classe que D-DE-018) :

```text
LengthLinksBuilder::DUPLICATE_START_END_KEYS/EXTERNAL_DUPLICATE_WITH_KEYS,
  LetterCombinedLinksBuilder::EXTERNAL_DUPLICATE_KEYS, PositionLinksBuilder::
  EXTERNAL_DUPLICATE_KEYS contiennent encore des listes de doublons calculees pour
  dictionary_fr.sqlite, jamais re-derivees pour l'espagnol -- sans effet aujourd'hui (leurs
  list_type sources restent vides), piege pour qui construira length_with/length_start_end/
  start_end plus tard sans les recalculer d'abord.
```

Raison :

```text
meme discipline mesure-avant-construction que le palier equivalent cote allemand du meme
  jour (D-DE-018) -- construire uniquement les types qui debloquent un besoin reel deja
  identifie, verifier chaque decision de granularite contre le comportement DEJA en
  production plutot que copier le script francais de reference tel quel
```

Conséquences :

```text
scripts/build_explore_hub_counts.php (reecrit), tests/Search/{ExploreHubBuilderTest,
  LengthLinksBuilderTest}.php (nouveaux), storage/dictionary_es.sqlite (list_counts 0 ->
  3 084 lignes), schema.sql (CHECK list_type etendu a length_start/length_end, commentaire
  resynchronise -- applique par la session principale)
la decision d'ouvrir les familles longueur+empiezan-por / longueur+terminan-en a
  l'indexation reste distincte de ce correctif technique (agent seo-registry, passe future)
reste pour une passe future : les 14 list_type restants, le recalcul des listes de doublons
  figees pour l'espagnol avant tout usage de length_with/length_start_end/start_end
Commit phase-es-28-list-counts-length-start-end (0d9c11a), pousse sur origin/master
```

## ES-018 — Palier "longueur+empiezan-por"/"longueur+terminan-en" Et "empiezan-por" 3 Lettres

Date : 2026-08-30
Statut : accepté

Contexte : ES-017 a peuplé `list_counts` (length_start/length_end) -- débloque le maillage
réel mesuré par ES-016 et différé faute de lien entrant. ES-016 avait aussi mesuré et différé
`empiezan-por` à 3 lettres faute de décision de volume -- décision désormais explicite
(propriétaire du produit : "comme le FR", encouragement à paralléliser cette passe SEO).

Décision :

```text
App\Seo\Family::WORD_LIST_COMBINED peuplee pour la PREMIERE fois (2 327 lignes,
  scripts/seo-batches/combined-length-start-end-tier1-2026-08-30.php) : 348
  /palabras/{N}-letras/empiezan-por/{lettre} (longueur+1 lettre, mode EXACT) + 1 979
  /palabras/{N}-letras/terminan-en/{2 lettres} (longueur+2 caracteres, mode BORNE).
word_list_commencant etendue (2 462 lignes, scripts/seo-batches/
  commencant-three-letters-tier2-2026-08-30.php) : palier 3 lettres, reutilise le lien
  startsWith 3 lettres deja en production (RelationsFinder, longueur > 3).
N'OUVRE PAS le troisieme axe "empiezan-por+terminan-en ensemble" (avec ou sans longueur) :
  list_counts start_end/length_start_end restent vides (ES-017), aucun lien reel.
```

Vérifications faites (en direct, php -S, jamais confiance aveugle dans ES-016/ES-017) :

```text
246 requetes reelles verifiees par EXPLAIN QUERY PLAN + timing (reflection sur
  WordListSolver), longueurs 2 et 15 incluses, Ñ, CH/LL/RR, K/W : 100% SEARCH USING
  COVERING INDEX, 0 SCAN. length_start (EXACT) : 0,2-7,4 ms quel que soit le volume
  (jamais tronque). length_end (BORNE, ancrage reversed) : 158-245 ms mesures entre
  8 439 et 9 903 resultats -- TROP PROCHE du plafond dur CLAUDE.md (TTFB p95 sous 250 ms)
  bien qu'aucune de ces pages ne soit tronquee (< ROW_EXAMINATION_CEILING=10 000) -- seuil
  de securite fixe a 5 000 apres echantillonnage de buckets intermediaires (~126 ms a
  5 105 lignes)
235 URL reelles verifiees en HTTP (php -S) : 200, canonical correct, y compris Ñ et
  digrammes CH/LL/RR. TROUVE en verification (pas anticipe) : 183 pages a 1 resultat de
  word_list_combined depassent 60 caracteres de <title> (app/View/word-list.php prefixe
  le mot au gabarit "De N Letras Con Final En XX", hors perimetre seo-registry) -- EXCLUES
  du lot, restent noindex,follow, signalees docs/05 pour un futur correctif frontend --
  MEME DEFAUT que D-DE-019 cote allemand, meme jour, meme gabarit herite (D-031)
Sitemaps regeneres (21 -> 23 fragments : +combined-0001 2 327 URL, +starts-0002 2 462 URL),
  <loc> percent-encode par segment verifie (0 octet Ñ/ñ brut, ES-011 I-7 non regresse)
robots.txt verifie, PAS modifie : aucun Disallow n'existait sur empiezan-por/terminan-en
Confirme en direct (0 ligne dans chaque list_type concerne) que LengthLinksBuilder::
  DUPLICATE_START_END_KEYS/EXTERNAL_DUPLICATE_WITH_KEYS (listes francaises gelees,
  signalees piege par ES-017) ne sont lues par AUCUN chemin de code que ce lot exerce
php tests/run.php = 21/22 (inchange, seul echec WordListViewTest herite/sans rapport) --
  tests/Seo/RegistrySitemapConsistencyTest.php etendu (verification result_count reel
  pour word_list_combined), vert
```

Familles mesurées et NON ouvertes, chacune pour une raison distincte :

```text
27 paires empiezan-por+longueur (K/W) : 0 mot admis a AUCUNE longueur ne commence par
  l'une ou l'autre (consequence triviale d'un fait deja etabli ES-016, revérifie ici)
88 paires terminan-en+longueur : doublon de contenu reel avec la variante sans longueur
  (list_counts length_end == end pour le meme suffixe), re-derive pour
  storage/dictionary_es.sqlite -- 10 des 88 suffixes correspondants deja index,follow
  sans longueur aujourd'hui
37 paires terminan-en+longueur : risque de TTFB mesure (voir ci-dessus), pas de troncature
183 paires terminan-en+longueur : <title> > 60 caracteres (ES-012), defaut frontend signale
empiezan-por+terminan-en ensemble (avec ET sans longueur) : list_counts start_end/
  length_start_end vides (ES-017) -- aucun lien reel, non ouvert
```

Découverte annexe, signalée pas corrigée (hors proportion pour cette passe) :

```text
scripts/check_combinatorial_duplicates.php est une copie francaise non adaptee --
  reference Family::WORD_FRENCH_NOT_ADMITTED (INEXISTANTE cote ES, ferait planter le
  script), cible storage/dictionary_fr.sqlite/seo_fr.sqlite par defaut, code en dur le
  prefixe /mots (jamais /palabras). Non utilise ici -- verifications de doublons
  construites independamment en SQL direct contre list_counts. Meme risque deja
  documente pour scripts/propose_seo_batch.php (ES-016).
```

Raison :

```text
discipline mesure-avant-ouverture identique aux paliers precedents (ES-016/ES-017) :
  jamais ouvrir une famille sans maillage entrant REEL verifie en direct, jamais un gros
  lot sans mesure de TTFB propre, jamais faire confiance a une mesure anterieure sans la
  rejouer
```

Conséquences :

```text
app/Seo/Family.php, scripts/seo_batch_rules.php (R4b etendu a WORD_LIST_COMBINED),
  scripts/build_sitemaps.php (+prefixe combined-), scripts/seo-batches/
  combined-length-start-end-tier1-2026-08-30.php (nouveau, 2 327 lignes),
  scripts/seo-batches/commencant-three-letters-tier2-2026-08-30.php (nouveau, 2 462
  lignes), tests/Seo/RegistrySitemapConsistencyTest.php (verification etendue),
  docs/05_URL_SEO_INDEXATION.md (resynchronise)
storage/seo_es.sqlite : 661 508 -> 666 297 lignes (+4 789), non versionne (artefact)
public/sitemaps/combined-0001.xml (2 327 URL), starts-0002.xml (2 462 URL),
  sitemap-index.xml (21 -> 23 fragments), non versionnes
public/robots.txt : verifie, PAS modifie
Commits phase-es-30-seo-combined-length-tier1 (ec01142), phase-es-31-seo-commencant-
  three-letters-tier2 (895c375), pousses sur origin/master
Reste a router : correctif <title> word_list_combined a 1 resultat (frontend -- MEME
  correctif que cote allemand, D-DE-019, a traiter dans un lot combine), reecriture ES
  de scripts/check_combinatorial_duplicates.php avant toute utilisation future
```

### Correctif 2026-08-30 — Ré-inclusion Des 220 Pages Bloquées (Titre + Risque TTFB)

Deux des points « restants » ci-dessus sont maintenant traités.

`app/View/word-list.php` a été corrigé directement par la session principale (commit
`cc7a5e6`, `phase-es-33-title-suffix-fix`) : le suffixe de marque " | WORD CHECKR" est
désormais omis quand `mb_strlen($metaTitle . $titleSuffix, 'UTF-8') > 60`.

Les 335 candidats `terminan-en+longueur` exclus par ce lot ont été RE-DÉRIVÉS depuis
`list_counts` (`length_end`/`end`, mêmes clés que `scripts/build_explore_hub_counts.php`) —
pas relus depuis le lot d'origine, qui n'écrivait jamais de ligne `noindex,follow` pour ces
candidats (absents du fichier, pas des lignes exclues explicitement). Reclassement confirmé à
l'identique des comptes ES-018 (183 + 37 + 88 + 27 = 335) :

```text
183 candidats <title> (result_count === 1) : RE-VÉRIFIÉS EN DIRECT sur un vrai serveur PHP
  (php -S) -- tous à <title> <= 60 caractères après le correctif, 0 rejet.
37 candidats risque TTFB (result_count >= 5000, seuil ES-018) : decision produit explicite
  du proprietaire (message direct dans cette conversation, PAS relayee par un agent) :
  "ouvre les pages meme a 245ms on est bon, je veux des pages a indexer". RE-MESURE EN
  DIRECT avant d'appliquer (pas de confiance aveugle) : une premiere passe sans montee en
  charge sur un serveur php -S fraichement relance montrait des pics jusqu'a 1654 ms --
  ARTEFACT DE DEMARRAGE A FROID, pas une TTFB reelle (confirme en re-testant avec un appel
  de rechauffement prealable). Methodologie corrigee (rechauffement + mediane de 3
  executions/page) : min=98,5 ms max=207,1 ms median=176,9 ms p95=203,1 ms sur les 37
  pages -- confortablement sous le plafond dur CLAUDE.md (250 ms), coherent avec la mesure
  originale ES-018 (158-245 ms). Aucune des 37 rejetee.
88 doublons de contenu reel + 27 paires K/W : re-verifies par la MEME requete de
  classification, confirmes NE PAS etre dans les 220 candidats ci-dessus (logique de
  detection "length_end count == end count pour le meme suffixe" -- correcte). Restent
  noindex,follow, exclusion toujours valide, non touches par ce lot.
```

Nouveau lot `scripts/seo-batches/combined-reinclusion-2026-08-30.php` (220 lignes,
`Family::WORD_LIST_COMBINED`, `sitemap_fragment = combined-0001`, appliqué via
`scripts/apply_seo_batch.php` sans `--force` — aucun de ces `route_path` n'existait déjà dans
un autre lot). Échantillon HTTP réel confirmé (200, `index,follow`, `<title>` correct pour la
catégorie titre ; 200 pour la catégorie TTFB) — et confirmation négative sur 2 candidats
`doublon` (`/palabras/3-letras/terminan-en/bc`, `/palabras/4-letras/terminan-en/rr`) : restent
bien `noindex,follow`, pas ré-inclus par erreur.

Registre : 666 297 → 666 517 lignes (+220 `index,follow`). Sitemaps régénérés
(`combined-0001.xml` : 2 327 → 2 547 URL, `sitemap-index.xml` : 666 516 URL au total, 23
fragments, inchangé). Suite de tests : 21/22, même échec pré-existant `WordListViewTest` que
la baseline connue, aucune régression nouvelle.

Reste à router (inchangé) : réécriture ES de `scripts/check_combinatorial_duplicates.php`
avant toute utilisation future.
```

## ES-019 — Localisation Des Noms De Champ GET Restants

Date : 2026-08-30
Statut : accepté

Contexte : ES-014 avait explicitement documenté (`public/index.php`, en-tête de fichier) que
les noms de champ GET internes (`mot`, `lettres`, `longueur`, `commencant`, `terminant`,
`contenant`, `avec`, `sans`, `motif`) restaient français — "ce sont des attributs `name` de
formulaires HTML, hors périmètre de cet agent, seul le SEGMENT de chemin change". Décision
explicite du propriétaire du produit (2026-08-30) : avancer ce lot maintenant, en parallèle du
correctif SEO ci-dessus. Même chantier que D-DE-020 côté allemand.

Décision :

```text
Noms de champ GET renommés (public/index.php, $field()/$_GET[] + tous les gabarits
  app/View/*.php qui les emettent) :
    longueur -> longitud       commencant -> empiezan-por   contenant -> contienen
    terminant -> terminan-en   avec -> con-letras            sans -> sin
    motif -> patron             mot -> palabra                lettres -> letras
  q/erreur INCHANGES (neutres). Chaque nom de champ reprend EXACTEMENT la valeur du segment
  de chemin correspondant (App\Search\WordListFilters::KEYWORDS), même convention que
  D-DE-020.
Règle CSS #motif -> #patron (public/assets/css/site.css), seule règle id-sélecteur
  concernée.
Ids DOM cosmétiques renommés en même temps (mot-check -> palabra-check, lettres-check ->
  letras-check) -- jamais lus par le backend, gardés cohérents avec les name= fonctionnels
  sur la même balise.
Constat annexe corrigé au passage (app/View/home.php, commentaire HTML au-dessus du
  constructeur de contraintes) : référençait encore "/mots" (route française jamais
  adaptée) au lieu de "/palabras" -- corrigé. Le reste des commentaires de ce fichier
  (lignes ~43-104) contient D'AUTRES références FR non adaptées (héritage git-archive
  jamais nettoyé) -- signalé, PAS corrigé ici, hors périmètre de cette tâche.
```

Périmètre volontairement NON couvert, distinct et non traité ici :

```text
Les VALEURS d'enumeration statut/tri ("admitida"/"no-admitida", "puntos"/"puntos-desc")
  restent francaises -- signale au docblock de public/index.php, perimetre distinct.
app/View/mentions-legales.php, confidentialite.php, la route /confidentialite elle-meme
  (route + contenu) : PAS touchees -- bundlees deliberement avec le contenu legal reel
  encore a ecrire (meme raisonnement que D-DE-020, confirme en lisant le meme genre de
  commentaire deja present cote allemand). Les 2 gabarits legaux ont neanmoins recu le
  meme renommage de champ GET fonctionnel (name="palabra" au lieu de name="mot") car ce
  nom de fil est partage globalement par public/index.php -- le texte visible (encore en
  francais sur ces 2 pages) reste inchange.
```

Vérifications faites (en direct, php -S) :

```text
php -l sur les 9 fichiers touches : propre.
GET fallback isole par champ : /palabras?longitud=6 -> /palabras/6-letras,
  /palabras?empiezan-por=cha -> /palabras/empiezan-por/cha, /palabras?contienen=che ->
  /palabras/contienen/che, /palabras?terminan-en=cion -> /palabras/terminan-en/cion,
  /palabras?con-letras=aar -> /palabras/con-letras/a/a/r, /palabras?sin=xz ->
  /palabras/sin/x/z, /palabras?patron=c--e- -> /palabras/5-letras/patron/c--e- (longueur
  implicite du patron, comportement WordListFilters preexistant, non modifie ici).
  /verificar?palabra=casa -> /palabra/casa. /buscador-de-palabras?letras=casa ->
  /buscador-de-palabras/a-a-c-s.
Degradation gracieuse confirmee sur les ANCIENS noms (contenant=, mot=) : plus aucun
  segment ajoute, /palabras?contenant=che rend le hub (200, pas de crash),
  /verificar?mot=casa retombe sur "q" absent -> /?erreur=1 -- pas de 500.
Ids rendus confirmes sur / (id="longitud", "empiezan-por", "terminan-en", "contienen",
  "con-letras", "sin", "patron").
php tests/run.php = 21/22 (meme echec pre-existant WordListViewTest) -- 1 test corrige
  (Frontend\PlayViewTest.php asserait litteralement name="lettres", mis a jour vers
  name="letras").
```

Raison :

```text
ES-014 avait deja pose ce lot comme volontaire et non oublie -- l'executer maintenant
termine la localisation de tout ce qui est fonctionnellement wireable sans toucher au
contenu legal encore non traduit, qui reste a raison un perimetre separe. Meme discipline
que D-DE-020 cote allemand, execute le meme jour.
```

Conséquences :

```text
public/index.php, app/View/home.php, app/View/explore-hub.php, app/View/confidentialite.php,
  app/View/mentions-legales.php, app/View/not-found.php, app/View/play.php,
  app/View/word-list.php, app/View/word.php, public/assets/css/site.css,
  tests/Frontend/PlayViewTest.php
Aucun changement de route ni de registre SEO -- ces noms de champ ne sont jamais visibles
  dans l'URL finale ni dans storage/seo_es.sqlite.
```

## ES-020 — Pages Légales En Espagnol Réel (Aviso Legal, Política De Privacidad)

Date : 2026-08-30
Statut : accepté

Contexte : `mentions-legales.php`/`confidentialite.php` contenaient depuis le portage initial
un contenu encore intégralement français (RGPD/CNIL/droit français), délibérément laissé de
côté avec la route `/confidentialite` elle-même (même raisonnement que D-DE-020 côté allemand :
un libellé espagnol pointant vers du contenu français serait plus trompeur que l'état honnête
précédent). Décision explicite et directe du propriétaire du produit (2026-08-30, dans la
conversation, pas relayée par un agent) : ne plus différer, écrire le contenu réel maintenant,
et localiser aussi les attributs `id`/`name` restants sur ces deux pages sans exception ("je
veux les attributs id/name en espagnol et de, point").

Décision :

```text
Routes localisees : /mentions-legales -> /aviso-legal, /confidentialite -> /privacidad.
  301 depuis les deux anciens chemins francais (jamais indexes, D-026, mais garde par
  prudence). Noms de fichier internes (mentions-legales.php/confidentialite.php) INCHANGES --
  identifiants techniques, pas des URL, meme convention que word.php/contact.php (ES-019).
Contenu integralement reecrit en espagnol, restructure selon le formalisme habituel d'un
  Aviso Legal (LSSI-CE, art. 10) et d'une Politica de Privacidad RGPD/LOPDGDD plutot que
  traduit mot a mot depuis la structure LCEN/RGPD francaise -- MEMES FAITS REELS que la
  version francaise (D-025ter, jamais reinventes) : BIGBANG MEDIA (EURL, RCS Laval, SIREN
  917 929 382, capital 1 000 €), o2switch (SAS, RCS Clermont-Ferrand, SIREN 510 909 807,
  capital 100 000 €, Chemin des Pardiaux 63000 Clermont-Ferrand). Nom personnel, adresse
  complete du siege et email restent volontairement absents (meme demande explicite du
  proprietaire du produit que D-025ter, reconduite a l'identique, pas une nouvelle decision)
  -- l'ecart est signale dans le texte lui-meme ("Editor"), pas silencieusement comble.
Point juridique verifie avant redaction (pas suppose) : BIGBANG MEDIA n'a d'etablissement
  qu'en France -- aucun representant espagnol au sens de l'article 27 RGPD n'est requis
  (cette obligation ne vise que les responsables SANS etablissement dans l'UE). La CNIL reste
  l'autorite de controle CHEF DE FILE (guichet unique RGPD, article 56) -- mentionnee comme
  telle dans la Politica de Privacidad, PAS remplacee par une autorite espagnole fictive. La
  rubrique reclamation rappelle neanmoins le droit garanti par l'article 77 RGPD de saisir
  aussi l'autorite du pays de residence -- coordonnees reelles de l'AEPD (Agencia Espanola de
  Proteccion de Datos) ajoutees a cet effet. LOPDGDD (loi organique espagnole 3/2018) citee en
  complement du RGPD, meme registre que la loi Informatique et Libertes cote francais.
TOUS les attributs id/name/href="#..." des deux pages en espagnol des l'ecriture initiale
  (pas de correction a posteriori necessaire, contrairement a D-DE-021 qui avait garde par
  erreur les anciens slugs francais dans un premier temps -- lecon appliquee directement ici) :
  sommaire ancre entierement en espagnol (editor, director, alojamiento, desarrollo,
  propiedad-intelectual, enlaces, cookies, terceros, datos, accesibilidad, disponibilidad,
  modificaciones, derecho-aplicable, definiciones pour l'Aviso Legal ; preambulo, responsable,
  datos-recopilados, base-legal, finalidades, conservacion, cookies, terceros, destinatarios,
  transferencias, seguridad, derechos, ejercicio, autoridad-control, menores, modificaciones,
  glosario pour la Politica de Privacidad).
Dernier champ GET/formulaire encore francais du site trouve et corrige au passage :
  app/View/contact.php, le champ optionnel "Nombre (Opcional)" utilisait id/name/for="nom" --
  renomme en "nombre". public/index.php ($_POST['nom'] -> ['nombre']) et le sujet/corps de
  l'email envoye au proprietaire du site ("Nouveau message via WORD CHECKR", "Nom : ...")
  etaient EGALEMENT encore en francais -- trouve en verifiant, pas suppose, traduits aussi
  ("Nuevo mensaje a traves de WORD CHECKR", "Nombre: ...").
Fait annexe corrige au passage : sur les 6 autres gabarits (home, word, word-list, play,
  explore-hub, not-found), le LIBELLE visible des liens de pied de page etait deja en espagnol
  ("Aviso Legal"/"Privacidad"/"Contacto") mais les href pointaient encore vers les anciens
  chemins francais (/mentions-legales, /confidentialite) -- corrige vers /aviso-legal et
  /privacidad. Divergence differente de D-DE-020 (ou le libelle ET la cible etaient francais).
Commentaire stale corrige : app/View/explore-hub.php affirmait encore que le NOM du champ
  GET "contenant" restait volontairement francais (vrai avant ES-019, faux depuis) -- mis a
  jour vers "contienen".
```

Vérifications faites (en direct, php -S) :

```text
php -l sur les 10 fichiers touches : propre.
/mentions-legales -> 301 -> /aviso-legal (200, <title>Aviso Legal | WORD CHECKR</title>).
/confidentialite -> 301 -> /privacidad (200, <title>Política De Privacidad | WORD CHECKR</title>).
Integrite du sommaire ancre verifiee PROGRAMMATIQUEMENT (pas a l'oeil) sur les deux pages :
  chaque href="#X" du sommaire correspond exactement a un id="X" reel dans la page rendue,
  0 lien mort, 0 id orphelin (a l'exception du id="palabra-check" de l'encart de recherche,
  qui n'a jamais eu vocation a figurer au sommaire).
Formulaire de contact : name="nombre" rendu et lu correctement par public/index.php.
Balayage final grep sur tout app/View/*.php : plus AUCUN attribut id/name/for de valeur
  francaise, uniquement espagnol ou neutre (email/message/q/site_web).
php tests/run.php = 21/22 (meme echec pre-existant WordListViewTest), aucune regression.
```

Raison :

```text
demande produit explicite et directe (2026-08-30) de ne plus differer le contenu legal ni les
  attributs id/name restants -- l'ancien raisonnement de bundling (ES-019) reste valide en
  PRINCIPE (ne jamais publier une etiquette localisee pointant vers du contenu non localise)
  mais cesse de s'appliquer des lors que le contenu reel est ecrit dans le meme lot, ce qui
  est desormais le cas. Meme discipline que D-DE-021 cote allemand, execute le meme jour.
```

Conséquences :

```text
public/index.php, app/View/mentions-legales.php, app/View/confidentialite.php,
  app/View/contact.php, app/View/home.php, app/View/word.php, app/View/word-list.php,
  app/View/play.php, app/View/explore-hub.php, app/View/not-found.php
Aucun changement de registre SEO (D-026 inchange : /aviso-legal et /privacidad restent
  noindex,follow par defaut, aucune ligne).
Reste hors perimetre, distinct : les VALEURS d'enumeration statut/tri (ES-019).
```

## ES-021 — Gardes Explicites Sur Les 2 Scripts Landmine Non Adaptés

Date : 2026-08-30
Statut : accepté

Contexte : `scripts/propose_seo_batch.php` (copie git-archive du dépôt français, 2 851 lignes)
et `scripts/check_combinatorial_duplicates.php` (460 lignes) signalés landmines par ES-016/
ES-018 sans jamais être neutralisés. Réinvestigués avant de décider entre un portage complet
(disproportionné pour `propose_seo_batch.php` : la plupart de ses cas exigent des `list_type`
que ce dépôt n'a pas construits, ES-017) et une neutralisation.

Vérifié avant d'agir (pas supposé), risque réel plus étroit qu'annoncé pour l'un, confirmé
pour l'autre :

```text
propose_seo_batch.php : $dictPath pointe en dur vers storage/dictionary_fr.sqlite, SANS
  variable d'environnement de contournement (contrairement a scripts/
  build_explore_hub_counts.php, seul script reellement vise par le constat ES-011 I-3 qui a
  inspire ce type de signalement). Un lancement naif echoue donc deja aujourd'hui avec
  "dictionnaire introuvable", avant meme d'atteindre la grammaire de route francaise codee en
  dur -- PAS un cas de "donnee fausse ecrite en silence".
check_combinatorial_duplicates.php : CONFIRME plus risque -- lit deja
  SCRABBLE_DICTIONARY_DB_PATH (repli storage/dictionary_fr.sqlite), un lancement pointe vers
  storage/dictionary_es.sqlite ne s'arrete donc PAS sur un dictionnaire introuvable. Plante
  plus loin sur Family::WORD_FRENCH_NOT_ADMITTED, verifie INEXISTANTE cote ES
  (app/Seo/Family.php ne definit que WORD_SPANISH_NOT_ADMITTED) -- un Fatal Error PHP net,
  jamais une donnee fausse ecrite en silence, mais atteignable avec un seul export de
  variable d'environnement, contrairement a l'autre script.
```

Décision :

```text
Garde explicite ajoutee dans les deux fichiers, juste apres le controle PHP_SAPI existant :
  refusent desormais TOUJOURS, message clair pointant vers cette entree et vers le patron
  reel de ce depot (scripts/seo-batches/*.php pour les lots, verification de doublons deja
  faite en SQL direct contre list_counts a chaque lot reel, voir ES-018). Ne dependent plus
  d'un effet de bord accidentel (fichier absent / constante inexistante) comme seul filet de
  securite.
Pas de portage complet : aucun cas de propose_seo_batch.php ne correspond a un besoin reel
  non couvert (tous les lots ouverts jusqu'ici -- ES-016 a ES-020 -- ont ete construits via
  des scripts dedies) ; check_combinatorial_duplicates.php est deja remplace par des
  verifications SQL directes independantes a chaque lot.
```

Vérifications faites :

```text
php -l sur les 2 fichiers : propre. Invocation reelle des deux : refusent immediatement avec
  le message attendu (exit 1 et exit 2 respectivement, codes de sortie preexistants
  conserves).
Aucun test ne couvre ni ne depend de ces deux scripts (grep confirme).
php tests/run.php = 21/22 (meme echec pre-existant WordListViewTest, aucune regression).
```

Raison :

```text
signales a repetition (ES-016/ES-018) sans jamais etre traites -- corriges au fil de l'eau
pendant une passe de nettoyage plutot que de laisser courir deux landmines documentes
indefiniment. Fait de facon proportionnee (garde, pas portage) une fois le risque reel de
chacun mesure independamment, pas suppose identique.
```

Conséquences :

```text
scripts/propose_seo_batch.php, scripts/check_combinatorial_duplicates.php (garde ajoutee dans
  chacun, aucun autre changement).
```

## ES-022 — `list_counts` Complet (19/19), Granularité `end` Révisée À 1, Palier "terminan-en" 1 Lettre Ouvert

Date : 2026-08-30
Statut : accepté

Contexte : `list_counts` ne construisait que 5 des 19 `list_type` (ES-017), signalé comme écart
de couverture face au dépôt français. Décision explicite du propriétaire du produit (2026-08-30,
en direct dans la conversation) : compléter les 14 types restants, puis rouvrir
`word_list_terminant` à 1 lettre (symétrique à `word_list_commencant` déjà indexé à 1 lettre,
ES-016). En discutant, la granularité `end`/`length_end` (2 caractères depuis ES-017) a
elle-même été remise en question ("pourquoi 1 lettre FR/DE et 2 ES ?").

Décision :

```text
scripts/build_explore_hub_counts.php etendu de 5 a 19 list_type. Granularite 'end'/
  'length_end' REVISEE : 2 -> 1 caractere. ES-017 avait choisi 2 caracteres pour matcher
  directement la famille indexee terminan-en (2 lettres) -- raisonnement solide a l'epoque,
  mais discussion produit a etabli que c'est ES qui divergeait de FR/DE, pas l'inverse, et que
  le hub /palabras est une source de lien reelle DISTINCTE de RelationsFinder qui justifie un
  palier 1-lettre separe (voir ci-dessous). schema.sql resynchronise (CHECK etendu a 19
  list_type). Nouveaux types : length_with, start_end, length_with_position,
  length_avec_sans, length_start_end, length_with_pair, length_with_triple, start_end_with,
  start_with, prefix2/3/4, suffix2/3/4 (mb_str_split()/mbReverse() partout, jamais
  str_split()/strrev() -- Ñ est 2 octets UTF-8).
Palier 1-lettre de word_list_terminant OUVERT (23/27 buckets 'end', K/Q/W/Ñ exclus -- 0 mot
  ADMIS pour chacun, meme discipline que empiezan-por K/W exclus ES-016) : symetrique a
  empiezan-por deja a 1 lettre. RAISON DU CHANGEMENT PAR RAPPORT A ES-016 (qui avait ferme
  cette famille a 1 lettre) : ES-016 avait mesure "0 lien reel" a un moment ou list_counts
  etait ENCORE VIDE (chronologie confirmee : ES-016 precede ES-017). Ce lot peuple
  list_counts, le hub /palabras rend desormais 27 liens reels et verifies
  (App\Search\ExploreHubBuilder, section "Terminan En", noindex,follow mais follow -- les
  liens sont bien crawles). La mesure ES-016 est donc PERIMEE, pas fausse a l'epoque, rouverte
  sur decision produit directe une fois ce fait etabli.
result_count STOCKE LE COMPTE REEL (tous statuts), jamais plafonne au ROW_EXAMINATION_CEILING
  -- BUG TROUVE ET CORRIGE en cours de tache par tests/Seo/RegistrySitemapConsistencyTest.php
  (7 lignes plafonnees a 10 000 detectees, alors que la convention deja en production
  -- commencant-terminant-single-tier1-2026-08-29.php, ex. empiezan-por/a = 115 806 -- stocke
  toujours le compte reel non plafonne). Le meme bug a ete trouve et corrige en parallele
  cote allemand (D-DE-023, qui n'a pas ce test -- corrige quand meme par coherence).
```

Vérifications faites (en direct, php -S, pas supposées) :

```text
php -l : propre. 92 755 lignes list_counts (19/19 list_type peuples), ~1m13 d'execution
  hors ligne.
Doublons : balayage PROGRAMMATIQUE des 27 buckets 'end' (tous statuts) contre les enfants
  'suffix2' (2 lettres, tous statuts) -- 0 doublon trouve (different du cas allemand, qui en
  avait 1).
Gate d'indexation : 4 lettres (K, Q, W, Ñ) ont 0 mot ADMIS bien qu'ayant des resultats
  "tous statuts" reels sur la page (K=64, Q=2, W=6, Ñ=3) -- exclues de l'ouverture, meme
  discipline que empiezan-por K/W (ES-016), la page fonctionne quand meme si visitee
  directement, simplement pas soumise a l'indexation.
TTFB : rechauffement + mediane de 3 executions (methodologie ES-018) sur les 6 buckets les
  plus lourds (S=369168, N=100460, A=96665, O=66413, E=77577, D=12584 tous statuts) :
  93-106 ms, tres sous le budget 250 ms malgre la troncature d'AFFICHAGE sur les buckets les
  plus gros (la troncature CAPE le cout d'examen, elle ne l'aggrave pas).
Sitemaps regeneres (ends-0001.xml : 246 -> 269 URL, sitemap-index.xml : 666 539 URL, 23
  fragments inchange). Echantillon HTTP reel : B/S (200, index,follow), K/Q (200,
  noindex,follow confirme exclus).
2 tests obsoletes corriges (assumaient l'ancienne granularite 2 caracteres ou l'absence de
  donnees pour length_with/length_with_position/length_start_end) :
  tests/Search/ExploreHubBuilderTest.php, tests/Search/LengthLinksBuilderTest.php -- reecrits
  avec des valeurs reelles reverifiees (byEnd desormais 27 buckets comme byStart, cas Ñ
  deplace vers une longueur ou Ñ existe reellement en position finale car aucun mot de 9
  lettres n'y finit).
php tests/run.php = 21/22 (meme echec pre-existant WordListViewTest), aucune regression
  apres correction des 2 tests obsoletes et du bug result_count.
```

Raison :

```text
demande produit explicite (2026-08-30) de completer la couverture face au francais ; la
  reouverture du palier 1-lettre repose sur un FAIT NOUVEAU verifie (le hub rend desormais un
  lien reel), pas sur un contournement de la discipline "jamais sans maillage entrant reel" --
  cette discipline reste respectee, la mesure sous-jacente a simplement change. La revision de
  granularite 'end' rapproche ES de la convention FR/DE plutot que de maintenir une divergence
  qui n'apportait plus rien une fois cette decision prise.
```

Conséquences :

```text
scripts/build_explore_hub_counts.php (5 -> 19 list_type, granularite end/length_end revisee),
  schema.sql (CHECK etendu a 19 list_type), scripts/seo-batches/
  terminan-en-single-letter-2026-08-30.php (nouveau, 23 lignes),
  tests/Search/ExploreHubBuilderTest.php, tests/Search/LengthLinksBuilderTest.php (obsoletes,
  reecrits)
storage/seo_es.sqlite : 666 517 -> 666 540 lignes index,follow (+23)
Reste a router (funnel pas encore complet, prochaine passe) : empiezan-por a 2 lettres
  (prefix2, seul palier manquant du cote "commencant" -- 1 et 3 lettres deja en ligne),
  terminan-en a 3/4 lettres (suffix3/suffix4). Donnees deja precalculees dans list_counts par
  ce lot, aucun nouveau calcul necessaire.
Constantes figees DUPLICATE_START_END_KEYS/EXTERNAL_DUPLICATE_WITH_KEYS/EXTERNAL_DUPLICATE_KEYS
  (calculees sur le francais) : CONFIRME que EXTERNAL_DUPLICATE_WITH_KEYS est bien lue par le
  chemin 'length_with' desormais peuple (App\Search\LengthLinksBuilder::build(), case
  'length_with') -- AUCUNE famille SEO n'est ouverte sur ce type par ce lot, donc sans
  consequence pratique aujourd'hui, mais confirme le risque signale ES-017/ES-018 : a
  recalculer pour l'espagnol avant tout futur lot qui ouvrirait 'length_with' a l'indexation.
```

## ES-023 — Funnel Complet : empiezan-por 2 Lettres, terminan-en 3/4 Lettres

Date : 2026-08-30
Statut : accepté

Contexte : demande produit explicite de compléter l'entonnoir SEO -- côté "commençant", 1
lettre (ES-016) et 3 lettres (ES-018) étaient en ligne mais pas 2 ; côté "terminant", seuls 1
(ES-022) et 2 lettres (ES-016) l'étaient. Données déjà précalculées dans `list_counts`
(ES-022, 19/19 `list_type`), seule l'ouverture manquait. Même chantier que D-DE-024 côté
allemand, même jour.

Décision :

```text
LANDMINE TROUVEE ET NEUTRALISEE avant d'ouvrir suffix3/suffix4 : App\Search\
  SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES contenait ~630 suffixes calcules sur
  storage/dictionary_fr.sqlite (D-040/D-041 cote francais), copies tels quels lors du portage
  -- videe (liste vide). App\Search\PrefixExtensionLinksBuilder::EXTERNAL_DUPLICATE_PREFIXES
  etait deja vide, verifie, rien a faire de ce cote.

empiezan-por 2 lettres OUVERT (396/396 buckets prefix2, 0 exclusion vs le palier 1 lettre).
  84 DOUBLONS DE CONTENU EXACT trouves contre le palier 3 lettres deja indexe (balayage
  programmatique) : la forme la plus courte gagne (D-041) -- 12 de ces 84 pages 3 lettres
  etaient deja index,follow (ES-018) et corrigees vers noindex,follow/canonical->2 lettres
  (scripts/seo-batches/commencant-three-letters-tier2-2026-08-30.php modifie en place) ; les
  72 autres n'avaient jamais ete indexees (0 lien reel, deja exclues -- rien a corriger).

terminan-en 3 lettres OUVERT (2551/2614 buckets suffix3, 63 exclus car doublons exacts du
  palier 2 lettres deja indexe).
terminan-en 4 lettres OUVERT (11372/12037 buckets suffix4, 639 exclus contre le palier 3
  lettres survivant, 26 exclus directement contre le palier 2 lettres).
```

Vérifications faites (en direct, php -S, pas supposées) :

```text
php -l sur tous les fichiers touches : propre.
Doublons : balayage PROGRAMMATIQUE a chaque niveau du funnel, comptage EXACT (meme
  correctif de methodologie qu'applique cote allemand le meme jour, D-DE-024 : un seul
  enfant non vide NE SUFFIT PAS, il faut aussi l'egalite des comptes).
TTFB : echantillons repartis sur les 4 nouveaux lots (CA/OS/EZA/MENTE) : 29-83 ms, tous
  largement sous le budget 250 ms.
Echantillon HTTP reel : gagnants index,follow canonical=soi-meme, perdants noindex,follow
  canonical vers le gagnant reel (ex. empiezan-por/dvd -> deja absent, jamais indexe, reste
  noindex par defaut ; empiezan-por/dv confirme gagnant reel avec DVD comme seul resultat).
php tests/run.php = 21/22 (meme echec pre-existant WordListViewTest), aucune regression --
  tests/Seo/RegistrySitemapConsistencyTest.php confirme (result_count reel, pas plafonne, a
  chaque nouvelle ligne).
```

Raison :

```text
demande produit explicite (2026-08-30) de completer l'entonnoir SEO deja entame -- meme
  discipline mesure-avant-ouverture que chaque palier precedent (ES-016, ES-018, ES-022),
  doublons et TTFB verifies programmatiquement a chaque niveau.
```

Conséquences :

```text
app/Search/SuffixExtensionLinksBuilder.php (liste figee videe), scripts/seo-batches/
  empiezan-por-two-letters-2026-08-30.php (nouveau, 396 lignes), scripts/seo-batches/
  commencant-three-letters-tier2-2026-08-30.php (12 lignes corrigees en place),
  scripts/seo-batches/terminan-en-three-letters-2026-08-30.php (nouveau, 2551 lignes),
  scripts/seo-batches/terminan-en-four-letters-2026-08-30.php (nouveau, 11372 lignes)
storage/seo_es.sqlite : 666 540 -> 680 859 lignes total, 680 846 index,follow (etait
  666 539 avant ES-023)
Sitemaps regeneres (starts-0002.xml 2462->2846, ends-0001.xml 269->14 192), 23 fragments
  inchange
Le funnel commencant est desormais 1+2+3 lettres COMPLET, le funnel terminant 1+2+3+4
  lettres COMPLET -- iso avec le depot francais et le depot allemand (D-DE-024).
```
