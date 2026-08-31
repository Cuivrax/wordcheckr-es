# C-1 — `length_end` grain 2, restauration du maillage vers les 2 199 pages `word_list_combined` terminan-en + longueur

Date : 2026-08-31
Correctif d'un bloquant trouvé par deux audits indépendants (`code-reviewer` exécution réelle,
`seo-technical-auditor` statique).

## Constat

- ES-018 a ouvert 2 199 pages `index,follow` `/palabras/{N}-letras/terminan-en/{XX}` (suffixe
  **2 caractères**, famille `word_list_combined`). Leur seul lien entrant réel : `App\Search\
  LengthLinksBuilder::build()` cas `length_end` (section `byEnd`), émis depuis `/palabras/{N}-letras`
  (déjà indexée, `word_list_length`).
- ES-022 a ramené `end` **et** `length_end` de 2 à 1 caractère d'un seul geste. Amalgame : la
  justification produit (« le hub `/palabras` est une source de lien distincte de RelationsFinder »)
  concerne `end` (consommé par `ExploreHubBuilder`, hub), **pas** `length_end` (consommé
  uniquement par `LengthLinksBuilder::byEnd`). Vérifié : `grep -rn length_end app/ tests/` →
  1 seul consommateur, `app/Search/LengthLinksBuilder.php`.
- Conséquence : `byEnd` n'émettait plus que ~19 liens à 1 lettre par longueur (vers des pages non
  indexées) et **0 lien vers les 2 199 pages 2 lettres + longueur**.

## Correctif

`scripts/build_explore_hub_counts.php` : le bloc `length_end` passe de
`substr(reversed, 1, 1)` à `substr(reversed, 1, 2)` (+ `WHERE length >= 2`, + `mbReverse()` pour
l'ordre de lecture, Ñ-safe — même traitement que `suffix2`). `end` (hub) **inchangé**, reste à
1 caractère. `length_start` **inchangé**, reste à 1 caractère (matche les 348 pages
`empiezan-por` + longueur, elles à 1 lettre). Aucun changement de code dans `LengthLinksBuilder`
(la partie après le premier `:` est traitée comme une chaîne opaque).

**Schéma : aucun changement.** `length_end` est déjà dans le `CHECK (list_type IN (...))` de
`schema.sql` — seule la granularité de la donnée change.

## `list_counts` — avant / après (mesuré sur copie, jamais sur la base de production)

`storage/dictionary_es.sqlite` **non modifiée** par cette tâche (D-001, directive explicite du
mandat). Mesures faites sur une copie régénérée via
`SCRABBLE_DICTIONARY_DB_PATH=<copie> php scripts/build_explore_hub_counts.php`.

| | avant (prod, grain 1) | après (copie, grain 2) |
|---|---|---|
| lignes `length_end` | 282 (toutes 1 car.) | 2 287 (toutes 2 car.) |
| lignes `list_counts` (total) | 92 755 | 94 760 |
| 18 autres `list_type` | — | **inchangés** (même compte ligne à ligne) |
| `terms` (sha256 contenu ordonné rowid) | `3829eb76…d13ea` | `3829eb76…d13ea` (**identique**) |
| `terms` agrégats (count/score/length/is_ods8/is_ods9/is_spanish) | inchangés | inchangés |
| `build_metadata` | inchangé | inchangé |
| `PRAGMA integrity_check` | `ok` | `ok` |
| déterminisme (build ×2 depuis `terms`) | — | `list_counts` sha256 identique sur les 2 exécutions |

## `EXPLAIN QUERY PLAN` — requête de `LengthLinksBuilder::build()`

Requête (inchangée par le correctif) :

```sql
SELECT list_type, list_key, count FROM list_counts
WHERE list_type IN ('length_start','length_end','length_with','length_with_position','length_start_end')
  AND list_key LIKE ?          -- '9:%'
```

| | plan | timing (`build(9)`, médiane ~300 exéc.) | lignes rendues | dont `length_end` |
|---|---|---|---|---|
| avant (grain 1) | `SEARCH list_counts USING INDEX sqlite_autoindex_list_counts_1 (list_type=?)` | 1,07 ms | 620 | 24 |
| après (grain 2) | `SEARCH list_counts USING INDEX sqlite_autoindex_list_counts_1 (list_type=?)` | 1,44 ms | 774 | 178 |

Toujours servi par la clé primaire `(list_type, list_key)` — jamais de `SCAN`. +0,37 ms sur
**une** des ≤ 10 requêtes de la page ; budget TTFB p95 < 250 ms très largement tenu.

## Vérification exhaustive du maillage (les deux sens, `App\Search\LengthLinksBuilder` réel)

`byEnd` collecté pour les longueurs 2→15 via la vraie classe, comparé aux `route_path` réels de
`storage/seo_es.sqlite`.

| | avant (prod, grain 1) | après (copie, grain 2) |
|---|---|---|
| URLs distinctes émises par `byEnd` | 282 (toutes 1 car.) | 2 287 (toutes 2 car.) |
| **DIR1** — pages `word_list_combined` terminan-en 2 car. + longueur SANS lien entrant | **2 199 / 2 199** | **0 / 2 199** |
| **DIR2** — liens `byEnd` 2 car. pointant hors registre | — | 88 |

**DIR2, décomposition exacte des 88** (re-mesurée contre les bases réelles, `remeasure_dir2`) —
les deux catégories partitionnent les 88, 0 « autre » :

| motif de non-inscription au registre | nb | détail |
|---|---|---|
| suffixe contenant une lettre marginale **K / Q / W / Ñ** | **17** | même exclusion de rollout qu'ES-016 (`empiezan-por` K/W) et ES-022 (`terminan-en` 1 lettre K/Q/W/Ñ) : ces lettres sont quasi absentes des lexiques d'admissibilité FILE 2017 / FISE-2, la famille n'est pas ouverte dessus. (16/17 ont 0 mot admis ; `2:ÑU` a 1 mot admis mais Ñ reste marginale.) |
| **doublon de contenu avec la variante SANS longueur** | **71** | `list_counts` : `suffix2[{XX}].count == length_end[{N}:{XX}].count` → **tous** les mots finissant en `{XX}` ont la longueur `{N}`, donc `/palabras/{N}-letras/terminan-en/{XX}` liste exactement le même contenu que `/palabras/terminan-en/{XX}` (famille `word_list_terminant`), qui gagne comme canonique (règle « la page la plus générale gagne », ES-018 / ES-025). Ces 71 restent `noindex,follow` / `canonical` → la page sans longueur. |

**« 0 mot admis » n'est PAS la porte d'exclusion** (le rapport précédent l'affirmait à tort) :
**415 des 2 199 pages RÉGISTRÉES `index,follow`** ont elles aussi 0 mot admis, et **10 des 88**
non-registrées ont ≥ 1 mot admis. La porte réelle est le **doublon de contenu** (0 des 2 199
pages registrées est un `suffix2 == length_end`, contre 71 des 88) plus l'**exclusion des
lettres marginales** (17). Sur l'axe admis : 78 des 88 ont 0 mot admis, 10 en ont ≥ 1.

Les 88 pointent toutes vers des pages réelles, résolvables, `noindex,follow` par défaut — même
classe que les liens `{N}:K` / `{N}:W` déjà émis par `byStart` (27 liens hors registre, acceptés
depuis ES-018). Aucun lien vers une URL 404 / non résolvable.

## Régénération de production — FAITE (coordinateur, 2026-08-31)

`php scripts/build_explore_hub_counts.php` exécuté sur le vrai `storage/dictionary_es.sqlite` par
le coordinateur après validation :

- `length_end` = **2 287 lignes / 2 caractères** ; `list_counts` total 94 760 lignes / 20 list_type.
- `PRAGMA integrity_check = ok` ; déterministe (sha256 identique sur 2 runs).
- Vérif exhaustive C-1 sur les **bases réelles** : **0 / 2 199** pages `word_list_combined`
  terminan-en `index,follow` orphelines (était 2 199 / 2 199).
- `php tests/run.php` = 21/22 (baseline inchangée). `tests/Search/LengthLinksBuilderTest.php`
  tourne désormais sa branche `grain === 2` (garde de non-régression active).

**C-1 clos.**
