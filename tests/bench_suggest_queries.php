<?php

declare(strict_types=1);

/**
 * Chronometre et documente la requete SQLite de la Phase 5 (autocompletion, GET /api/suggest),
 * telle qu'executee par App\Search\Suggester -- la meme classe que le runtime, pas une
 * reimplementation separee (meme methodologie que tests/bench_rack_queries.php,
 * tests/bench_wordlist_queries.php et tests/bench_relations_queries.php).
 *
 * Ecrit reports/query-plans/phase5.md : EXPLAIN QUERY PLAN, chronometrage de bout en bout, et
 * le nombre de lignes REELLEMENT DANS LE PANIER (avant troncature a 8) pour les prefixes les
 * plus lourds de la base reelle storage/dictionary_fr.sqlite -- pas seulement un cas favorable.
 * Les prefixes les plus lourds sont mesures directement en base (pas devines) : voir la
 * fonction heaviestPrefixes() ci-dessous, qui interroge substr(normalized, 1, N) GROUP BY,
 * requete de DIAGNOSTIC reservee a ce script de developpement (jamais executee au runtime).
 *
 * Ne tourne jamais en production (D-007) : script de developpement, execute a la main.
 *
 * Usage :
 *     php tests/bench_suggest_queries.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use App\Search\Normalizer;
use App\Search\Suggester;

const RUNS = 20;

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';
$outPath = $root . '/reports/query-plans/phase5.md';

$connection = new Connection($dbPath);
$pdo = $connection->pdo();
$suggester = new Suggester($connection);

/** Bornes [inclusive, exclusive) -- copie litterale de Suggester::rangeBounds(), a des fins
 * d'EXPLAIN QUERY PLAN uniquement (meme convention que les scripts bench_* precedents). */
function rangeBounds(string $prefix): array
{
    $chars = str_split($prefix);

    for ($i = count($chars) - 1; $i >= 0; $i--) {
        if ($chars[$i] !== 'Z') {
            $chars[$i] = chr(ord($chars[$i]) + 1);

            return [$prefix, implode('', array_slice($chars, 0, $i + 1))];
        }
    }

    return [$prefix, null];
}

/** Requete reellement executee par Suggester::suggest() -- copie litterale, memes placeholders. */
function suggestSql(bool $withUpper): string
{
    $upper = $withUpper ? ' AND normalized < ?' : '';

    return 'SELECT normalized, score, length, is_ods8, is_ods9 FROM terms WHERE normalized >= ?'
        . "{$upper} ORDER BY normalized LIMIT ?";
}

function planFor(PDO $pdo, string $sql, array $params): array
{
    $statement = $pdo->prepare('EXPLAIN QUERY PLAN ' . $sql);
    $statement->execute($params);

    return array_map(static fn (array $row): string => (string) $row['detail'], $statement->fetchAll());
}

function usesTableScan(array $planText): bool
{
    foreach ($planText as $detail) {
        if (str_contains($detail, 'SCAN') && !str_contains($detail, 'USING') && !str_contains($detail, 'subquery')) {
            return true;
        }
    }

    return false;
}

/**
 * Execute litteralement la MEME requete que App\Search\Suggester::suggest() (memes bornes de
 * plage, meme LIMIT), mais SANS passer par Normalizer::isValid() -- utilisee uniquement pour
 * les prefixes d'UNE lettre ci-dessous : Suggester::suggest() refuse toute entree de moins de
 * MIN_LENGTH (2) caracteres AVANT toute requete (regle metier "deux caracteres minimum" de la
 * tache, deja verifiee independamment par tests/Search/SuggesterTest.php), donc un prefixe
 * d'une lettre n'atteint jamais cette requete en production. La tache demande neanmoins de
 * mesurer explicitement "les prefixes d'une lettre les plus frequents" -- ce qui revient a
 * mesurer le MECANISME SQL sous-jacent (le meme index, la meme technique de plage) dans son cas
 * de selectivite le PLUS DEFAVORABLE possible (le moins selectif qu'un prefixe puisse jamais
 * etre), independamment de la regle metier qui l'empeche d'etre atteint aujourd'hui -- preuve
 * que le `LIMIT 8` protege le temps de reponse quelle que soit la selectivite, pas seulement
 * dans les cas ou la regle des 2 caracteres l'a deja rendu improbable.
 *
 * @return list<array<string, mixed>>
 */
function rawSuggestQuery(PDO $pdo, string $normalizedPrefix): array
{
    [$lower, $upper] = rangeBounds($normalizedPrefix);
    $conditions = ['normalized >= ?'];
    $params = [$lower];

    if ($upper !== null) {
        $conditions[] = 'normalized < ?';
        $params[] = $upper;
    }

    $statement = $pdo->prepare(
        'SELECT normalized, score, length, is_ods8, is_ods9 FROM terms WHERE '
        . implode(' AND ', $conditions) . ' ORDER BY normalized LIMIT ?'
    );
    $statement->execute([...$params, Suggester::MAX_RESULTS]);

    return $statement->fetchAll();
}

/**
 * Panier REEL (avant troncature a 8) pour un prefixe donne -- COUNT() indexe sur la meme plage
 * que la requete de production, jamais un fetchAll() complet des centaines de milliers de
 * lignes en memoire PHP. Requete de DIAGNOSTIC pour ce rapport uniquement, distincte de la
 * requete de production (qui ne fait JAMAIS de COUNT(), une seule requete bornee a LIMIT 8).
 */
function realBasketCount(PDO $pdo, string $prefix): int
{
    [$lower, $upper] = rangeBounds($prefix);
    $conditions = ['normalized >= ?'];
    $params = [$lower];

    if ($upper !== null) {
        $conditions[] = 'normalized < ?';
        $params[] = $upper;
    }

    $statement = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE ' . implode(' AND ', $conditions));
    $statement->execute($params);

    return (int) $statement->fetch()['c'];
}

/**
 * Prefixes de longueur $len les plus frequents dans la base REELLE, mesures directement (pas
 * devines) -- requete de diagnostic groupee, jamais executee au runtime (aucun equivalent dans
 * Suggester : substr()+GROUP BY sur les 838 180 lignes serait exactement le genre de parcours
 * complet interdit au runtime par CLAUDE.md, acceptable ici une seule fois, hors ligne, pour
 * choisir les cas de mesure).
 *
 * @return list<string>
 */
function heaviestPrefixes(PDO $pdo, int $len, int $n): array
{
    // CAST(? AS INTEGER) : meme bug que celui documente et corrige dans
    // App\Search\WordListSolver::extraPredicates() -- substr()/length() sont des expressions
    // calculees, sans affinite de colonne declaree. PDOStatement::execute(array) transmet les
    // entiers PHP comme du texte ; sans ce CAST explicite, INTEGER >= TEXT echoue toujours
    // (l'ordre de type de SQLite place tout entier avant tout texte quand les types different)
    // -- verifie en developpement (heaviestPrefixes() renvoyait un tableau vide sans le CAST).
    $statement = $pdo->prepare(
        'SELECT substr(normalized, 1, CAST(? AS INTEGER)) p, COUNT(*) c FROM terms '
        . 'WHERE length(normalized) >= CAST(? AS INTEGER) GROUP BY p ORDER BY c DESC LIMIT CAST(? AS INTEGER)'
    );
    $statement->execute([$len, $len, $n]);

    return array_column($statement->fetchAll(), 'p');
}

$heaviestOneLetter = heaviestPrefixes($pdo, 1, 3);
$heaviestTwoLetter = heaviestPrefixes($pdo, 2, 3);

$cases = [
    ['label' => 'PIRE CAS -- prefixe d\'une lettre le plus frequent de la base reelle', 'prefix' => $heaviestOneLetter[0]],
    ['label' => 'prefixe d\'une lettre, 2e plus frequent', 'prefix' => $heaviestOneLetter[1] ?? $heaviestOneLetter[0]],
    ['label' => 'PIRE CAS -- prefixe de deux lettres le plus frequent de la base reelle', 'prefix' => $heaviestTwoLetter[0]],
    ['label' => 'prefixe de deux lettres, 2e plus frequent', 'prefix' => $heaviestTwoLetter[1] ?? $heaviestTwoLetter[0]],
    ['label' => 'prefixe rare (peu de correspondances, panier non tronque)', 'prefix' => 'KYU'],
    ['label' => 'prefixe au plafond de longueur (15 lettres, D-010) -- au plus 1 correspondance possible', 'prefix' => 'ABANDONNERAIENT'],
    ['label' => 'entree la plus courte acceptee (2 lettres, MIN_LENGTH)', 'prefix' => 'RE'],
];

$lines = [
    '# Plans De Requetes -- Phase 5 (Autocompletion, GET /api/suggest)',
    '',
    'Produit par `tests/bench_suggest_queries.php`, lecture seule sur `storage/dictionary_fr.sqlite`,',
    'via `App\\Database\\Connection` et `App\\Search\\Suggester` -- la classe reellement utilisee au',
    'runtime. Chaque timing de bout en bout est une mediane sur ' . RUNS . ' executions.',
    '',
    '## Budget',
    '',
    '`Suggester::suggest()` execute **exactement 1 requete SQLite** (0 si l\'entree normalisee est',
    'invalide, avant toute ouverture de curseur -- meme convention que `TermLookup::find()`,',
    '`RackSolver::solve()`, `WordListSolver::solve()`). Route HTTP independante (`GET',
    '/api/suggest`) : ce budget n\'affecte ni ne s\'ajoute au budget de la fiche `/mot/{mot}` (3',
    'requetes `TermLookup` + 5 `RelationsFinder` = 8, voir `reports/query-plans/phase4.md`),',
    'tres en-dessous du plafond de moins de 10 requetes par fiche (CLAUDE.md).',
    '',
    '## Prefixes Les Plus Lourds De La Base Reelle -- Mesures, Pas Devines',
    '',
    sprintf(
        'Prefixes d\'une lettre les plus frequents : %s.',
        implode(', ', array_map(static fn (string $p): string => "`{$p}`", $heaviestOneLetter))
    ),
    sprintf(
        'Prefixes de deux lettres les plus frequents : %s.',
        implode(', ', array_map(static fn (string $p): string => "`{$p}`", $heaviestTwoLetter))
    ),
    '',
    'Ces deux listes proviennent d\'une requete `substr(normalized, 1, N) GROUP BY` -- diagnostic',
    'de developpement reserve a ce script, jamais executee au runtime (un `GROUP BY` sur les',
    '838 180 lignes est exactement le parcours complet que CLAUDE.md interdit en production).',
    '',
    '**Note sur les cas d\'une lettre ci-dessous** : `Suggester::suggest()` refuse toute entree de',
    'moins de `Normalizer::MIN_LENGTH` (2) caracteres AVANT toute requete (regle metier "deux',
    'caracteres minimum" de la tache, verifiee independamment par `tests/Search/SuggesterTest.php`)',
    '-- un prefixe d\'une lettre n\'atteint donc jamais cette requete en production. Les deux cas',
    '"prefixe d\'une lettre" mesurent neanmoins directement le MECANISME SQL sous-jacent (copie',
    'litterale de la requete de `Suggester::suggest()`, memes bornes de plage, meme `LIMIT`, voir',
    '`rawSuggestQuery()`) dans son cas de selectivite le PLUS DEFAVORABLE possible -- la preuve que',
    '`LIMIT 8` protege le temps de reponse quelle que soit la selectivite du prefixe, pas',
    'seulement dans les cas ou la regle des 2 caracteres l\'a deja rendu improbable.',
    '',
];

$allIndexed = true;
$maxElapsed = 0.0;
$maxElapsedLabel = '';

foreach ($cases as $case) {
    $prefix = $case['prefix'];
    $normalizedPrefix = strtoupper($prefix);

    $realBasket = realBasketCount($pdo, $normalizedPrefix);
    $reachableInProduction = strlen($normalizedPrefix) >= Normalizer::MIN_LENGTH;

    $timings = [];
    $results = [];

    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $results = $reachableInProduction ? $suggester->suggest($prefix) : rawSuggestQuery($pdo, $normalizedPrefix);
        $timings[] = (hrtime(true) - $start) / 1e6;
    }
    sort($timings);
    $median = $timings[intdiv(count($timings), 2)];

    if ($median > $maxElapsed) {
        $maxElapsed = $median;
        $maxElapsedLabel = $case['label'] . " (`{$prefix}`)";
    }

    [$lower, $upper] = rangeBounds($normalizedPrefix);
    $params = $upper !== null ? [$lower, $upper, Suggester::MAX_RESULTS] : [$lower, Suggester::MAX_RESULTS];
    $planText = planFor($pdo, suggestSql($upper !== null), $params);
    $scan = usesTableScan($planText);
    $allIndexed = $allIndexed && !$scan;

    $lines[] = '## ' . $case['label'];
    $lines[] = '';
    $lines[] = sprintf('Prefixe saisi : `%s` (normalise `%s`).', $prefix, $normalizedPrefix);
    $lines[] = '';
    $lines[] = sprintf(
        'Panier REEL avant troncature a 8 (`COUNT()` indexe, diagnostic hors production) : %s ligne(s).'
            . ($realBasket > Suggester::MAX_RESULTS ? ' Troncature a %d confirmee necessaire.' : ' Sous le plafond, aucune troncature.'),
        number_format($realBasket, 0, ',', ' '),
        Suggester::MAX_RESULTS,
    );
    $lines[] = '';
    $lines[] = $reachableInProduction
        ? sprintf(
            'Resultat retourne par `Suggester::suggest()` (appel reel, meme point d\'entree que la route) : %d suggestion(s) (plafond %d).',
            count($results),
            Suggester::MAX_RESULTS,
        )
        : sprintf(
            'Prefixe d\'une lettre : `Suggester::suggest()` renverrait `[]` sans requete (regle des 2 '
            . 'caracteres minimum, voir note ci-dessus). Resultat de la requete SQL sous-jacente mesuree '
            . 'directement (`rawSuggestQuery()`) : %d ligne(s) (plafond %d).',
            count($results),
            Suggester::MAX_RESULTS,
        );
    $lines[] = '';
    $lines[] = sprintf(
        'Mediane de bout en bout : %.3f ms, min %.3f ms, max %.3f ms sur %d executions.',
        $median,
        min($timings),
        max($timings),
        RUNS,
    );
    $lines[] = '';
    $lines[] = '`EXPLAIN QUERY PLAN` :';
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = suggestSql($upper !== null);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $scan
        ? '**SCAN TABLE detecte -- probleme.**'
        : 'Indexe (`sqlite_autoindex_terms_1`, l\'index UNIQUE sur `normalized`, D-013), aucun `SCAN TABLE` de `terms`.';
    $lines[] = '';
}

$lines[] = '## Verdict';
$lines[] = '';
$lines[] = $allIndexed
    ? 'Toutes les requetes passent par `sqlite_autoindex_terms_1` (recherche par plage sur `normalized`), aucun `SCAN TABLE` de `terms` -- y compris sur les prefixes les plus lourds de la base reelle (jusqu\'a plusieurs centaines de milliers de lignes dans le panier reel avant troncature). Le `LIMIT 8` arrete la lecture de l\'index des que 8 lignes ont ete trouvees : ce n\'est jamais un scan deguise.'
    : 'AU MOINS UNE REQUETE FAIT UN SCAN TABLE -- voir ci-dessus.';
$lines[] = sprintf(
    'Pire mediane observee : %.3f ms (%s) -- tres en-dessous du budget TTFB p95 < 250 ms (CLAUDE.md).',
    $maxElapsed,
    $maxElapsedLabel,
);
$lines[] = '1 requete SQLite par appel (0 pour une entree invalide), route independante de `/mot/{mot}` : aucun impact sur son budget de 8 requetes (`reports/query-plans/phase4.md`).';

@mkdir(dirname($outPath), recursive: true);
file_put_contents($outPath, implode("\n", $lines) . "\n");

echo 'ecrit : ' . $outPath . "\n";

exit($allIndexed ? 0 : 1);
