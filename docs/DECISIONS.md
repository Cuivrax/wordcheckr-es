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
