# C-3 — `App\Search\ExploreHubBuilder::build()` : `SCAN list_counts` non préparé, sans `LIMIT`

Date : 2026-08-31
Correctif d'un bloquant `code-reviewer` (exécution réelle). Violation directe de CLAUDE.md :
« requêtes préparées uniquement, LIMIT strict systématique ».

## Constat

`app/Search/ExploreHubBuilder.php` (avant) :

```php
$statement = $this->connection->pdo()->query('SELECT list_type, list_key, count FROM list_counts');
```

- `query()` non préparé, aucun `WHERE`, aucun `LIMIT`.
- `list_counts` n'est plus la petite table décrite dans le docblock (« 66 lignes fixes ») :
  **92 755 lignes** au moment du constat (ES-022, 19 `list_type`) ; **94 760 lignes / 20
  `list_type`** après la régénération C-1 (`length_end` 2 caractères) — C-3 est insensible à ce
  volume, la requête après correctif ne lit que les 68 lignes des 3 `list_type` du hub.
- La méthode n'exploite que 3 `list_type` (`length`, `start`, `end`) = **68 lignes**.
- `/palabras` est l'unique point d'entrée de crawl du funnel (17 063 pages indexées).

## Correctif

```php
$statement = $this->connection->pdo()->prepare(
    'SELECT list_type, list_key, count FROM list_counts'
    . " WHERE list_type IN ('length', 'start', 'end')"
    . ' ORDER BY list_type, list_key'
    . ' LIMIT 100'
);
$statement->execute();
$rows = $statement->fetchAll();

if (count($rows) === 100) {          // garde HONNÊTE : jamais de troncature silencieuse du hub
    throw new \RuntimeException('ExploreHubBuilder : plafond LIMIT 100 atteint ...');
}
```

`LIMIT 100` : garde-fou dur (CLAUDE.md). Maximum **structurel** = 14 longueurs (mots de 2 à 15
lettres) + 27 buckets `start` (26 lettres + Ñ, `substr(x,1,1)`) + 27 buckets `end` = **68**,
jamais atteint. Durci (re-audit ES-027) : `ORDER BY list_type, list_key` rend l'échantillon
déterministe (coût nul, voir plan ci-dessous) et `build()` **lève une `RuntimeException`** si
les 100 lignes sont ramenées — si la donnée changeait un jour de nature, le hub échoue
bruyamment au lieu d'être tronqué en silence.

`queryCount` reste **1**. Sortie de `build()` **identique** : `byLength=14`, `byStart=27`,
`byEnd=27`, mêmes URLs/comptes (vérifié).

## `EXPLAIN QUERY PLAN` + timing (base de production, lecture seule)

| | requête | plan | timing (médiane ~300 exéc.) | lignes |
|---|---|---|---|---|
| avant | `SELECT list_type, list_key, count FROM list_counts` | `SCAN list_counts` | **64,3 ms** | 92 755 |
| après (C-3) | `… WHERE list_type IN (…) LIMIT 100` | `SEARCH list_counts USING INDEX sqlite_autoindex_list_counts_1 (list_type=?)` | **0,13 ms** | 68 |
| après (durci ES-027) | `… WHERE list_type IN (…) ORDER BY list_type, list_key LIMIT 100` | `SEARCH list_counts USING INDEX sqlite_autoindex_list_counts_1 (list_type=?)` — **pas de TEMP B-TREE** (l'index couvre l'ordre) | **0,10 ms** | 68 |

≈ **490–650× plus rapide**, `SCAN` → `SEARCH` sur clé primaire `(list_type, list_key)`. L'ajout
d'`ORDER BY` sur les colonnes de l'index n'introduit aucun tri temporaire.

## Tests

`php tests/run.php` : `Search\ExploreHubBuilderTest.php` **OK** (inchangé sur le fond :
14/27/27, `queryCount === 1`, granularité caractère, Ñ multi-octets — la garde
`RuntimeException` n'est jamais atteinte, 68 ≪ 100). Docblock du test rafraîchi (filtre +
`ORDER BY` + `LIMIT` + garde ; « 3 list_type sur les 19 » → 20 ; « 'end' = 2 caractères »
supprimé). Suite complète : 21/22 (seul échec pré-existant `Frontend\WordListViewTest.php`,
hérité, hors sujet).
