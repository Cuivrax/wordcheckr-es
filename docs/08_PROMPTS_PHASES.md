# 08 — Prompts De Lancement Par Phase

Prompts prêts à copier-coller, un par étape. À utiliser dans l'ordre.

## Mode D'Emploi

Les agents sont chargés au **démarrage de session** Claude Code. Après toute modification de
`.claude/agents/`, redémarrer avant de les invoquer.

Une étape = un prompt. Ne pas enchaîner deux phases dans le même message.

```text
agent build   → rapport BEFORE → implémentation → rapport AFTER + READY FOR AUDIT
agent audit   → GO ou NO GO
vous          → validation
              → commit
              → étape suivante
```

Si un audit rend NO GO, corriger et relancer le même audit. Ne pas passer outre.

## État Du Projet À La Sortie De La Phase 0

```text
storage/dictionary_fr.sqlite   838 180 termes, 154,5 Mo, integrity ok
schema.sql                     terms + build_metadata
scripts/lib/normalize.py       normalisation, score, signature, reversed,
                                plafond de 15 lettres (D-010 révisée)
scripts/import_fr.py           import déterministe, rejouable
scripts/bench_queries.py       plans de requêtes persistés
PHP 8.4.24 dans C:\php84       pdo_sqlite, sqlite3, mbstring, intl, OPcache
```

Base issue du second tour de la Phase 0, après un premier NO GO de code-reviewer. Le fichier
`data/raw/ods8.json` contient 411 430 formes brutes, dont 9 105 de plus de 15 lettres — non
jouables, écartées de la base (D-010 révisée). Les 402 325 formes retenues correspondent à la
valeur officielle de l'ODS8 publié par Larousse.

Colonnes de `terms` :

```text
id  display_term  normalized  is_french  is_ods8  is_ods9  score  length  signature  reversed
```

Index disponibles :

```text
sqlite_autoindex_terms_1        normalized, UNIQUE
idx_terms_length_normalized     length, normalized
idx_terms_signature             signature
idx_terms_reversed              reversed
idx_terms_ods8                  is_ods8, normalized
idx_terms_ods9                  is_ods9, normalized
```

Aucune table de postings : reportée en Phase 2/3 (D-012).

---

## Étape 0 bis — Second Audit De La Phase 0

Première chose à lancer après redémarrage. Le premier audit a rendu **NO GO** ; les corrections
sont appliquées, ce prompt lance le second tour.

```text
Utilise l'agent code-reviewer pour auditer la Phase 0, second tour après un
premier NO GO.

Périmètre : schema.sql, scripts/import_fr.py, scripts/lib/normalize.py,
scripts/download_hbenbel.py, scripts/bench_queries.py, reports/ et
reports/query-plans/phase0.md.

Le premier tour avait relevé cinq points, tous censés être corrigés — vérifie
chacun par toi-même plutôt que de prendre ce résumé pour acquis :
- I2 : 9 105 formes ODS8 de plus de 15 lettres étaient marquées admises à
  tort. La base ne doit plus contenir aucune ligne avec length > 15, et
  is_ods8=1 doit compter exactement 402 325 lignes.
- B1 : un rapport AFTER doit exister, distinct du rapport BEFORE.
- B2 : reports/query-plans/ doit contenir des plans persistés, pas seulement
  avoir été affiché en console.
- B3 : les comptes dans CLAUDE.md, docs/DECISIONS.md et docs/PHASE_STATUS.md
  doivent correspondre aux valeurs réelles de la base reconstruite.
- B4 : import-summary.json doit exposer french_source_rows et ne doit plus
  additionner des lignes Kartmaan avec des formes hbenbel dans un même total.

Vérifie par toi-même, avec Bash, les comptes contre storage/dictionary_fr.sqlite :
838 180 termes, ods8_only 64, ods9_only 735, ods8_and_ods9 402 261,
ods8 total 402 325, français non admis 435 120, is_french=0 attendu à 0,
length > 15 attendu à 0.

Contrôle aussi :
- PRAGMA integrity_check
- score = somme des tuiles — sur toutes les lignes si le temps le permet, sinon
  un échantillon large et dis lequel
- normalized conforme à ^[A-Z]{2,15}$ sur toutes les lignes
- déterminisme : relance scripts/import_fr.py --dry-run et compare au rapport
- absence de définition en base (D-004)
- écarts entre schema.sql et schema/terms_fr_proposal.sql, et leur justification
  dans docs/DECISIONS.md (D-009 à D-015)
- schema.sql : les requêtes commentées comme exemples utilisent-elles une
  plage (>=, <) et jamais un LIKE sur une colonne indexée ?

Rends ton verdict GO / NO GO.
```

---

## Phase 1 — Socle, Home Et Fiche Mot

Deux agents, l'un après l'autre. `data-engine` d'abord : `frontend` a besoin de ses
structures de données.

### 1a — Backend

```text
Utilise l'agent data-engine pour la Phase 1, couche données et routage.

Objectif : le socle PHP 8.4 sans framework et les requêtes des trois routes
/ , /mot/{mot} et /verifier/{mot}.

À livrer :
- public/index.php, routeur minimal en front controller
- app/Config.php et config/sites/fr.php, selon docs/02 — le code doit rester
  réutilisable pour un futur site anglais
- app/Database/ : connexion PDO SQLite en mode ro strict, requêtes préparées
- app/Search/ : recherche d'un terme, mots précédent et suivant
- une réimplémentation PHP de scripts/lib/normalize.py, strictement identique,
  avec un test qui compare les deux sur un échantillon

Contraintes : moins de 10 requêtes indexées par fiche, aucun scan complet,
LIMIT strict, sûr pour N workers PHP concurrents.

Ne touche pas à app/View/ : elle appartient à l'agent frontend. Livre-lui des
structures de données déjà prêtes à rendre.

Fournis EXPLAIN QUERY PLAN et un chronométrage pour chaque requête, dans
reports/query-plans/.

Commence par ton rapport BEFORE et attends ma validation avant d'implémenter.
```

### 1b — Frontend

```text
Utilise l'agent frontend pour la Phase 1, couche rendu.

Objectif : les templates de la home et de la fiche mot, à partir des structures
de données déjà livrées par data-engine.

Références de rendu : prototype/index.html et prototype/mot-poser.html.
Ce sont des références visuelles, pas l'architecture — en production le CSS est
externe et versionné, le HTML est rendu par PHP, le JavaScript est différé.

Ordre imposé de la home, sans paragraphe entre le H1 et le formulaire :
header → badge → H1 → formulaire → résultats → liens contextuels →
deux paragraphes courts → footer.

Le résultat principal doit être présent dans le HTML initial. Le formulaire doit
fonctionner JavaScript désactivé. Sur la fiche, la somme des tuiles doit être
strictement égale au score affiché.

Aucun crédit de source nulle part — ni footer, ni page de licence, ni
commentaire HTML (D-015).

Teste et rapporte : mobile, clavier seul, focus visible, collage,
JavaScript désactivé, mot de 2 lettres, mot de 15 lettres, mot de 21 lettres.

Commence par ton rapport BEFORE et attends ma validation.
```

### 1c — Audit

```text
Utilise code-reviewer puis design-consistency-reviewer pour auditer la Phase 1.
Chacun rend son verdict GO / NO GO.
```

Porte de la phase : les trois routes répondent, le résultat est visible sans JavaScript,
la somme des tuiles égale le score, moins de 10 requêtes par fiche, tous les plans passent
par un index.

Test local :

```powershell
php -S 127.0.0.1:8000 -t public
```

---

## Phase 2 — Solveur

```text
Utilise l'agent data-engine pour la Phase 2, le solveur.

Objectif : la route /jouer/{lettres} — quels mots peut-on former avec un
chevalet, jokers compris.

Le chevalet fait 15 caractères maximum en saisie (D-010), ? et * valent joker.
Le tri par défaut est score décroissant, puis longueur, puis ordre alphabétique.

signature est l'entrée indexée naturelle : un tirage engendre un ensemble de
sous-multiensembles de lettres. Chiffre le nombre de signatures à interroger
pour un tirage de 7 lettres, puis pour 7 lettres et deux jokers, AVANT de
choisir ta stratégie.

D-012 a reporté les postings à cette phase. S'ils deviennent nécessaires,
mesure d'abord : taille de l'index, temps de construction, gain constaté.
Ne construis rien sans la mesure.

Fournis EXPLAIN QUERY PLAN et benchmark pour chaque requête, y compris le pire
cas — 7 lettres, deux jokers, aucune contrainte.

Commence par ton rapport BEFORE et attends ma validation.
```

Audit : `code-reviewer`, puis `code-optimizer` **uniquement si un problème est mesuré**.

---

## Phase 3 — Contraintes

```text
Utilise l'agent data-engine pour la Phase 3, les contraintes de recherche.

Contraintes à couvrir : longueur, commence par, termine par, contient la suite,
lettres obligatoires avec répétitions, lettres exclues, motif de cases connues.

Ordre canonique imposé partout — URL, clés, canonicals :
longueur → commençant → contenant → terminant → position → avec → sans → motif

Routes concernées :
/mots/7-lettres
/mots/commencant/ch
/mots/7-lettres/commencant/ch
/mots/terminant/tion
/mots/contenant/che
/mots/avec/a/a/r
/mots/5-lettres/motif/c--e-

Toute autre permutation redirige en 301.

Le préfixe passe par normalized, le suffixe par reversed. « Contient » et
« lettres obligatoires » n'ont pas d'index : c'est ici que se décide la question
des postings. Mesure la sélectivité réelle avant de trancher, et compare au
coût en taille de fichier.

Fournis les plans de requêtes et les benchmarks, avant et après.

Commence par ton rapport BEFORE et attends ma validation.
```

Audit : `code-reviewer`, puis `code-optimizer` après benchmark.

---

## Phase 4 — Fiches Riches

```text
Utilise data-engine puis frontend pour la Phase 4, les relations de la fiche mot.

Catégories, seules les non vides sont rendues :
anagrammes exactes, changer une lettre, retirer une lettre, insérer une lettre,
sous-mots, rallonges à gauche, rallonges à droite, mots plus longs contenant le
mot, anagrammes avec une lettre en plus, anagrammes avec une lettre en moins.

Plafonds : environ 160 liens de mots, 12 recherches liées, 2 mots voisins.
Au-delà, un lien « Voir les N mots ».

Présentation : simples liens en ligne, surlignage léger de la partie conservée
ou modifiée, aucune carte par mot, aucune définition, aucune section vide.

Le budget reste de moins de 10 requêtes indexées pour la fiche entière, toutes
catégories comprises. Chiffre-le explicitement.

Commence par ton rapport BEFORE et attends ma validation.
```

Audit : `code-reviewer` + `design-consistency-reviewer`.

---

## Phase 5 — Autocomplétion

```text
Utilise data-engine puis frontend pour la Phase 5, l'autocomplétion.

Deux caractères minimum, huit propositions maximum, préfixe exact uniquement,
délai de 140 à 180 ms.

Statut visible sur chaque suggestion :
Admis ODS8 · ODS9  /  Admis ODS8  /  Admis ODS9  /  Français · Non admis

Combobox ARIA complète : role=combobox, aria-expanded, aria-controls,
role=listbox, role=option, aria-activedescendant mis à jour et retiré.
Flèches haut et bas, Entrée, Échap. Le formulaire reste fonctionnel sans
JavaScript.

La requête de préfixe passe par l'index UNIQUE sur normalized. Mesure-la sur
les préfixes les plus lourds de la base, pas seulement sur un cas favorable.

Commence par ton rapport BEFORE et attends ma validation.
```

Audit : `code-reviewer` + `code-optimizer` + `design-consistency-reviewer`.

---

## Phase 6 — Registre SEO

```text
Utilise l'agent seo-registry pour la Phase 6.

Objectif : storage/seo_fr.sqlite, base distincte du dictionnaire (D-003), et
la couche app/Seo/ qui l'interroge.

Le registre est l'unique source de vérité : index/noindex, canonical, sitemaps,
maillage, rollout, métadonnées. Toute route absente reste noindex, follow.
Aucune indexation par omission.

Dimensionnement réel, à ne pas sous-estimer : la base compte 838 180 fiches
mot, dont 435 120 formes françaises non admises. Le brief parlait de 412 000 —
c'est périmé.

Propose des lots de rollout chiffrés, en commençant petit, avec un point de
contrôle o2switch et Search Console entre deux lots.

Fournis les métriques quantifiées : URL par famille, pages à exactement un
résultat rapportées séparément, nombre de fragments de sitemap, liens internes
moyens par page, volume du lot proposé.

Ne propose jamais d'indexer en masse les formes françaises non admises.

Commence par ton rapport BEFORE et attends ma validation.
```

Audit : `code-reviewer` + `seo-technical-auditor`.

---

## Phase 7 — Production

```text
Lance l'audit complet avant mise en production : code-reviewer, code-optimizer,
design-consistency-reviewer et seo-technical-auditor.

Chacun rend son verdict GO / NO GO. Un seul NO GO bloque la mise en ligne.
```

Points de vérification propres au déploiement o2switch :

```text
storage/ hors du dossier web
data/raw/ hors du dossier web
OPcache actif
cache HTTP LiteSpeed ou Varnish, après mesure seulement
TTFB chaud p95 sous 250 ms
```

---

## Agent Microcopy — À La Demande

`microcopy` n'appartient à aucune phase. Il n'est appelé que lorsqu'un texte précis manque.

```text
Utilise l'agent microcopy pour écrire [le texte précis demandé].
N'écris rien au-delà de ce qui est demandé.
```

Texte connu comme nécessaire :

```text
les deux paragraphes de contexte de la home
la phrase de validation d'un mot admis
la phrase d'un mot français non admis
les mentions légales et la page confidentialité
les messages d'erreur : mot invalide, aucun résultat, saisie malformée
```

### Remplacer QUEULEULEU

Le brief utilise QUEULEULEU comme exemple de forme française non admise. Ce terme n'existe
dans aucune de nos sources : il n'apparaît que dans la locution « à la queue leu leu ». Son
statut réel sur le site serait *inconnu*, pas *français non admis* — l'exemple illustrerait
donc le mauvais cas.

Deux remplaçants vérifiés en base, `is_french = 1`, `is_ods8 = 0`, `is_ods9 = 0` :

```text
GHOSTER       recommandé — verbe courant, un joueur le tentera
MACRONISTE    correct, mais connoté politiquement
```

Phrase attendue, sur le modèle du brief :

```text
GHOSTER existe en français, mais n'est admis ni dans l'ODS8 ni dans l'ODS9.
Il ne peut pas être joué comme un mot unique.
```

### Ce Que Contient Réellement La Couche Non Admise

À savoir avant d'écrire les textes et avant de dimensionner le rollout SEO : les 435 120
formes françaises non admises sont massivement des gentilés et des flexions rares.
Échantillon réel tiré de la base :

```text
4 lettres   BIFS, ZOUF, MICO, RONE
6 lettres   MURETOIS, CHIBOU, CIROIR, RAVETS
8 lettres   ROIFFEEN, PEGUOISE, MIRODIEZ, THONNINE
```

ODS8 est extrêmement complet sur le français courant — y compris les néologismes récents
(SELFIE, PODCAST, TELETRAVAIL, COVOITURAGE, GOOGLISER sont tous admis). Une forme non admise
qu'un joueur chercherait spontanément est donc **rare**.

Conséquence directe pour `seo-registry` : ces fiches ont une demande de recherche quasi nulle.
Elles ne justifient pas un rollout prioritaire, et l'ouverture en masse serait un mauvais
calcul de budget de crawl.
