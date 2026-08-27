# Méthodologie : génération de définitions + nature grammaticale à grande échelle via LLM

Procédure utilisée pour générer ~285 000 définitions (dictionnaire principal) puis ~37 000 définitions supplémentaires (mots "réels mais non valides au Scrabble") sur LexiFinder. Réutilisable pour n'importe quel site ayant besoin de définitions courtes et fiables pour un grand nombre de termes, sans recopier une source protégée.

---

## 1. Le problème

Obtenir nature grammaticale + définition pour des dizaines de milliers de mots, avec trois contraintes fortes :

1. **Droit d'auteur** : impossible de recopier Wiktionary (CC BY-SA, nécessite attribution + partage identique) ou un dictionnaire classique (copyright direct) tel quel.
2. **Coût** : à 50 000+ mots, un tarif par mot doit rester marginal (viser < 0,05 $ pour 1000 mots).
3. **Fiabilité à l'échelle** : le pipeline doit survivre à une coupure réseau, une panne de crédit API, ou un plantage à mi-parcours, sans perdre le travail déjà fait.

L'erreur classique à éviter : demander au LLM "donne-moi la définition de X" sans aucune donnée de référence → il invente, se trompe de sens (surtout sur les mots courts/ambigus), ou dans le pire cas paraphrase une source qu'il a mémorisée pendant son entraînement (risque de plagiat indirect).

---

## 2. Vue d'ensemble du pipeline

```
[Sources de référence gratuites]
        ↓
[Classement en 3 tiers : gratuit / avec référence / sans référence]
        ↓
[Génération LLM par lots, avec grounding + garde-fou anti-copie]
        ↓
[Scan qualité automatisé post-génération]
        ↓
[Intégration en base de données]
```

Chaque étape écrit son résultat sur disque avant de passer à la suivante → reprenable à tout moment.

---

## 3. Étape 1 — Sourcer des données de référence gratuites (coût : zéro, une seule fois)

### WordNet 3.1
Archive officielle Princeton téléchargeable directement (pas le package npm `wordnet-db`, obsolète). Les données sont figées depuis ~2011, donc pas de risque d'obsolescence — on peut la télécharger une fois et la garder indéfiniment.

### kaikki.org (extraction Wiktionary)
kaikki.org republie tout Wiktionary sous forme de JSONL streamable, un objet JSON par ligne :

```json
{"word": "steezy", "pos": "adj", "lang_code": "en",
 "senses": [{"glosses": ["Having effortless style and coolness."], "tags": ["slang", "informal"]}]}
```

Champs utiles :
- `word` : le terme (casse d'origine conservée — utile pour distinguer noms propres)
- `pos` : nature grammaticale (`noun`, `verb`, `adj`, `name`, `contraction`, `intj`...)
- `senses[].glosses` : définition(s) de référence
- `senses[].tags` : métadonnées précieuses — `slang`, `informal`, `internet`, `misspelling`, `dialectal`, `Scotland`, `abbreviation`, `derogatory`, `vulgar`... Ces tags permettent de **filtrer et catégoriser sans lire chaque définition à la main**.

Le fichier anglais complet fait ~3 Go non compressé. On le télécharge en streaming (`fetch()` + `readline`), on filtre à la volée, on ne garde en mémoire que ce qui nous intéresse.

```ts
const res = await fetch('https://kaikki.org/dictionary/English/kaikki.org-dictionary-English.jsonl');
const rl = readline.createInterface({ input: Readable.fromWeb(res.body), crlfDelay: Infinity });
for await (const line of rl) {
  const entry = JSON.parse(line);
  if (entry.lang_code !== 'en') continue;
  // ... filtrer/stocker ce qui intéresse
}
```

**Important** : ces sources ne sont **jamais affichées telles quelles** sur le site. Elles servent uniquement à ancrer le LLM (bon sens, bonne nature grammaticale, désambiguïsation) qui réécrit ensuite une phrase originale.

---

## 4. Étape 2 — Classer les mots pour minimiser le coût (gratuit)

Avant tout appel LLM, chaque mot passe dans un classificateur à 3 tiers :

### Tier 1 — Gabarit gratuit (zéro appel LLM)
Détecte les formes fléchies régulières (pluriel, passé, gérondif, comparatif...) par **double vérification**, jamais par supposition :
1. Retirer le suffixe candidat (`-s`, `-ed`, `-ing`, `-er`...)
2. Vérifier que la base obtenue existe déjà dans notre propre dictionnaire
3. **Réappliquer** la règle orthographique standard sur la base pour confirmer qu'on retombe exactement sur le mot d'origine (gère les cas `cat→cats` mais aussi `try→tries`, `run→running` avec doublement de consonne, etc.)

Si le moindre doute subsiste → on ne force rien, le mot part dans le tier suivant. Le biais est volontairement en faveur de la sécurité plutôt que de l'économie.

```
Résultat : "Plural of CAT." / "Past tense of RUN." — gabarit fixe, aucun coût.
```

### Tier 2 — Avec référence
WordNet et/ou Wiktionary connaît le mot → mis en file d'attente avec le(s) gloss(es) de référence comme contexte pour le LLM. On estime aussi ici le **nombre de sens probables** : si les sources montrent ≥2 natures grammaticales avec des définitions nettement différentes, on autorise jusqu'à 2-3 sens en sortie (cas classique : un sigle américain qui est aussi un mot régional archaïque — les deux sens méritent d'apparaître).

### Tier 3 — Sans référence
Ni WordNet ni Wiktionary ne connaît le mot → génération à partir des seules connaissances du LLM, **tracé explicitement** (`source: 'llm-only'`) pour pouvoir distinguer plus tard les entrées les moins fiables.

Sur ~285 000 mots, cette classification a permis de traiter gratuitement une fraction significative (formes fléchies) et de grouper le reste efficacement.

---

## 5. Étape 3 — Génération LLM par lots

### Choix du modèle
Le moins cher viable pour une tâche de reformulation courte (pas besoin d'un modèle "raisonneur" — désactiver le chain-of-thought si l'API le permet, ça réduit drastiquement les tokens de sortie facturés).

### Lots, pas un mot à la fois
20 à 25 mots par appel API. Un appel par mot serait ~20x plus cher en overhead et beaucoup plus lent.

### Le prompt système (le cœur de la méthode)

```
Tu es un lexicographe qui rédige des entrées de dictionnaire extrêmement concises.

Pour chaque mot donné, produis 1 à son nombre maximum de sens indiqué. Règles :
1. Chaque sens est EXACTEMENT une phrase, factuelle, moins de ~15 mots, sans exemple,
   sans guillemets, se terminant par un point.
2. Ne JAMAIS combiner plusieurs sens en une phrase avec des points-virgules ou "ou" —
   si un mot a plusieurs sens distincts, donne à chacun son propre objet séparé.
   C'est la règle la plus importante.
3. Si des sens de référence sont fournis, sers-t'en UNIQUEMENT pour connaître le sens
   correct et la nature grammaticale — ne JAMAIS copier ou paraphraser de trop près
   leur formulation. Toujours écrire une phrase entièrement originale.
4. Si les sens de référence montrent des significations vraiment différentes (nature
   grammaticale différente, ou même nature mais sens clairement sans rapport), inclus
   CHACUNE comme sens séparé, jusqu'au maximum indiqué — ne pas en omettre un juste
   parce qu'il est plus rare. Ne fusionner que si les sources disent essentiellement
   la même chose.
5. Le PREMIER sens doit toujours être le sens le plus courant et le plus connu — celui
   auquel un lecteur moyen penserait en premier — jamais un sens rare ou technique,
   même si la source de référence le liste en premier.
6. "pos" doit être une étiquette courte en minuscules : noun, verb, adjective, adverb,
   interjection, acronym, pronoun, preposition, conjunction, proper noun, etc.
7. Si un mot n'a aucun sens de référence fourni, utilise tes propres connaissances,
   mais reste prudent — préfère une définition plus courte et sûre à une définition
   spéculative.

Réponds UNIQUEMENT avec un objet JSON de cette forme exacte, couvrant chaque mot
donné, dans le même ordre :
{"results": [{"word": "...", "senses": [{"pos": "...", "definition": "..."}]}]}
```

Le message utilisateur liste les mots du lot avec leur(s) gloss(es) de référence :

```
- "steezy" (max 1 sens, sens de référence : adj: "Having effortless style" [slang, informal])
- "phillilew" (max 1 sens, AUCUNE donnée de référence -- utilise tes propres connaissances)
```

### Validation stricte de la réponse
Réponse JSON forcée (`response_format: json_object` côté API) puis validée avec un schéma strict (zod ou équivalent) — si la réponse ne colle pas exactement au format attendu, on retente plutôt que d'accepter des données malformées.

---

## 6. Étape 4 — Le garde-fou anti-plagiat (LE point critique)

Après génération, **avant d'accepter** une définition, on la compare mot par mot à chaque gloss de référence utilisé :

```ts
function longestSharedRun(a: string[], b: string[]): number {
  // plus longue suite de mots consécutifs identiques entre deux textes tokenisés
}

function isTooSimilar(definition: string, references: RefSense[]): boolean {
  const defTokens = tokenize(definition);
  for (const ref of references) {
    if (longestSharedRun(defTokens, tokenize(ref.gloss)) > 7) return true;
  }
  return false;
}
```

Si la définition générée partage plus de ~7 mots consécutifs avec la source de référence, on la **rejette** et elle repart en file d'attente pour une nouvelle tentative (avec un prompt qui insiste encore plus sur la reformulation, ou simplement au hasard d'une nouvelle génération qui tombera différemment). C'est ce mécanisme, pas juste l'instruction dans le prompt, qui garantit réellement l'absence de copie — un LLM peut ignorer une instruction, un contrôle programmatique après coup ne peut pas être contourné aussi facilement.

**Effet observé** : sur les gros lots (~28 000 mots), 1-3 % des sens sont rejetés à ce stade et repassent en file — c'est normal et sain, pas un bug.

---

## 7. Étape 5 — Architecture reprenable

Trois principes non négociables à cette échelle :

1. **Écriture incrémentale** : on n'accumule pas tout en mémoire jusqu'à la fin. Toutes les ~40 lots, on réécrit le fichier de résultats sur disque. Une coupure de connexion, un plantage, une panne de crédit API (`HTTP 402 Insufficient Balance` vécu en pratique dans ce projet) → on relance le même script, il ignore ce qui est déjà fait et reprend exactement là où ça s'est arrêté.

```ts
const existing = fs.existsSync(OUTPUT_PATH) ? JSON.parse(fs.readFileSync(OUTPUT_PATH)) : {};
const queue = allPending.filter(job => !existing[job.word]); // reprise automatique
```

2. **Concurrence limitée avec retry/backoff** : 5-6 appels en parallèle maximum (pas plus, sous peine de rate-limiting), chaque lot retenté jusqu'à 3 fois avec un délai exponentiel avant d'abandonner (le mot reste alors en attente pour le prochain lancement du script).

3. **Clé API en variable d'environnement, jamais en dur** : `process.loadEnvFile('.env')` puis `process.env.MA_CLE_API`. Ce projet a eu un incident réel de clé committée en dur dans le code — leçon appliquée strictement depuis.

4. **Mode `--dry-run`** : toujours tester sur 20-50 mots (choisis pour couvrir les différents cas : mots courts ambigus, mots avec plusieurs sens attendus, mots sans aucune référence) avant de lancer le run complet et de dépenser du crédit pour de vrai.

---

## 8. Étape 6 — Scan qualité automatisé après génération

Leçon apprise à grande échelle (30 000+ mots) : même avec le grounding + le garde-fou anti-copie, certaines définitions se trompent complètement de sens. Cas typique : un mot court ou ambigu ("IM", "ROAS") correspond par hasard à une entrée Wiktionary totalement différente du sens réellement recherché (ex. "IM" généré comme classification astronomique de galaxie au lieu de la contraction internet "I'm"). Le LLM a bien utilisé la référence — mais la référence elle-même ne correspondait pas au bon sens.

**Solution** : après génération, scanner *toutes* les définitions produites avec des motifs regex ciblant les signaux de "mauvaise correspondance" :

```
/surname|given name|village in|genus of|county in|province in|commune in
 |no standard meaning|uncertain|not standard/i
```

Chaque mot qui matche est soit corrigé à la main (petits lots), soit retiré de la liste de candidats (gros lots — ne pas perdre de temps à sauver un mot marginal qui ne valait probablement pas la peine d'être ajouté de toute façon).

**Piège à éviter** : ces regex génèrent des faux positifs. Exemple vécu : un mot dont la définition légitime *contient* le mot "uncertain" comme concept ("the quality of being uncertain") se fait flaguer par erreur, alors que ce n'est pas le LLM qui hésite — c'est la vraie définition du mot. Toujours **vérifier un échantillon des entrées flaguées avant suppression en masse**, jamais supprimer aveuglément sur un simple match regex.

---

## 9. Coût réel observé

Avec un modèle d'entrée LLM à ~0,20-0,25 $/M tokens et sortie à ~0,60-0,70 $/M tokens (tarifs hors heures de pointe) :

- ~285 000 mots (dictionnaire complet) : **~5,50-6,50 $ au total**
- ~37 000 mots supplémentaires : **moins d'1 $**

Soit environ **0,02 $ pour 1000 mots**. Si le fournisseur a des heures de pointe/creuses (souvent le cas,×2 sur le prix), viser les heures creuses ou le week-end si le fournisseur applique un tarif uniforme réduit ces jours-là — ça ne coûte rien d'attendre quelques heures sur un run de plusieurs milliers de mots.

---

## 10. Adapter cette méthode à un autre site/domaine

Ce qui **ne change jamais** :
- Le principe grounding + reformulation + garde-fou anti-copie
- L'architecture reprenable (écriture incrémentale, retry, dry-run)
- Le scan qualité post-génération

Ce qui **change selon le domaine** :
- La ou les sources de référence gratuites (Wiktionary/WordNet ici, mais ce serait par exemple une API produit pour un catalogue e-commerce, une base de données publique pour des lieux/entreprises, etc.)
- Le prompt système (format de sortie, longueur, ton) adapté au besoin réel
- Les critères du tier "gratuit" (ici les formes fléchies ; pour un autre domaine ce serait peut-être des doublons évidents ou des variantes connues)
- Les motifs du scan qualité (ici "surname/genus/village" ; pour un autre domaine ce seraient d'autres signaux de mauvaise correspondance spécifiques au contenu)
