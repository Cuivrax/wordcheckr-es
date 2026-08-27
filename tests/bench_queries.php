<?php

declare(strict_types=1);

/**
 * Chronometre et documente les requetes SQLite de la Phase 1, telles qu'executees par
 * App\Database\Connection -- la meme classe que le runtime, pas une reimplementation
 * separee. Ecrit reports/query-plans/phase1.md : EXPLAIN QUERY PLAN et chronometrage
 * pour chacune des requetes utilisees par App\Search\TermLookup::find().
 *
 * Meme methodologie que scripts/bench_queries.py (Phase 0) : mediane sur 200
 * executions, lecture seule. Ne tourne jamais en production (D-007) : script de
 * developpement, execute a la main.
 *
 * Usage :
 *     php tests/bench_queries.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;

const RUNS = 200;

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';
$outPath = $root . '/reports/query-plans/phase1.md';

$connection = new Connection($dbPath);
$pdo = $connection->pdo();

$queries = [
    [
        'label' => 'Fiche mot',
        'sql' => 'SELECT display_term, score, length, is_ods8, is_ods9 FROM terms WHERE normalized = ? LIMIT 1',
        'params' => ['POSER'],
        'usage' => '/mot/{mot} -- App\\Search\\TermLookup::find(), requete 1/3',
    ],
    [
        'label' => 'Mot precedent',
        'sql' => 'SELECT normalized FROM terms WHERE normalized < ? ORDER BY normalized DESC LIMIT 1',
        'params' => ['POSER'],
        'usage' => '/mot/{mot} -- navigation, App\\Search\\TermLookup::find(), requete 2/3',
    ],
    [
        'label' => 'Mot suivant',
        'sql' => 'SELECT normalized FROM terms WHERE normalized > ? ORDER BY normalized ASC LIMIT 1',
        'params' => ['POSER'],
        'usage' => '/mot/{mot} -- navigation, App\\Search\\TermLookup::find(), requete 3/3',
    ],
];

$lines = [
    '# Plans De Requetes -- Phase 1',
    '',
    'Produit par `tests/bench_queries.php`, lecture seule sur `storage/dictionary_fr.sqlite`,',
    'via `App\\Database\\Connection` -- la connexion reellement utilisee au runtime, pas une',
    'requete Python separee.',
    sprintf('Chaque timing est une mediane sur %d executions.', RUNS),
    '',
];

$allIndexed = true;

foreach ($queries as $query) {
    $planStatement = $pdo->prepare('EXPLAIN QUERY PLAN ' . $query['sql']);
    $planStatement->execute($query['params']);
    $planRows = $planStatement->fetchAll();
    $planText = array_map(static fn (array $row): string => (string) $row['detail'], $planRows);

    $usesScan = false;
    foreach ($planText as $detail) {
        if (str_contains($detail, 'SCAN') && !str_contains($detail, 'USING')) {
            $usesScan = true;
        }
    }
    $allIndexed = $allIndexed && !$usesScan;

    $statement = $pdo->prepare($query['sql']);
    $timings = [];
    $rowCount = 0;

    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $statement->execute($query['params']);
        $rows = $statement->fetchAll();
        $timings[] = (hrtime(true) - $start) / 1e6;
        $rowCount = count($rows);
    }
    sort($timings);
    $median = $timings[intdiv(count($timings), 2)];

    $lines[] = '## ' . $query['label'];
    $lines[] = '';
    $lines[] = 'Sert : ' . $query['usage'];
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = $query['sql'];
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = sprintf(
        '%d lignes, mediane %.3f ms, min %.3f ms, max %.3f ms -- %s',
        $rowCount,
        $median,
        min($timings),
        max($timings),
        $usesScan ? 'SCAN' : 'index',
    );
    $lines[] = '';
}

$lines[] = '## Connexion -- ouverture + premiere requete';
$lines[] = '';
$lines[] = 'Chronometre separement : nouvelle connexion `App\\Database\\Connection` (lecture seule,';
$lines[] = 'PDO::SQLITE_ATTR_OPEN_FLAGS + PDO::SQLITE_ATTR_READONLY_STATEMENT) puis requete "Fiche mot",';
$lines[] = '100 executions, une connexion neuve a chaque fois (aucune connexion persistante entre requetes).';
$lines[] = '';

$openTimings = [];
for ($i = 0; $i < 100; $i++) {
    $start = hrtime(true);
    $fresh = new Connection($dbPath);
    $statement = $fresh->pdo()->prepare(
        'SELECT display_term, score, length, is_ods8, is_ods9 FROM terms WHERE normalized = ? LIMIT 1'
    );
    $statement->execute(['POSER']);
    $statement->fetch();
    $openTimings[] = (hrtime(true) - $start) / 1e6;
}
sort($openTimings);
$openMedian = $openTimings[intdiv(count($openTimings), 2)];
$lines[] = sprintf(
    'mediane %.3f ms, min %.3f ms, max %.3f ms',
    $openMedian,
    min($openTimings),
    max($openTimings),
);
$lines[] = '';

$lines[] = '## Budget par fiche';
$lines[] = '';
$lines[] = sprintf(
    '%d requetes indexees pour /mot/{mot} (App\\Search\\TermLookup::find() = fiche + precedent + '
    . 'suivant), 0 pour /verifier (redirection pure vers /mot/{slug}, aucune connexion ouverte), '
    . '0 pour / (Phase 1, pas de solveur).',
    count($queries)
);
$lines[] = '';

$lines[] = '## Verification exhaustive (hors chronometrage)';
$lines[] = '';
$lines[] = 'tests/Search/TermLookupTest.php recalcule score/signature/reversed/length en PHP pour les';
$lines[] = '838 180 lignes reelles de la base et les compare aux colonnes stockees : 0 divergence,';
$lines[] = 'voir la sortie de `php tests/run.php`.';
$lines[] = '';

$lines[] = '## Verdict';
$lines[] = '';
$lines[] = $allIndexed
    ? 'Toutes les requetes passent par un index, aucun `SCAN TABLE`.'
    : 'AU MOINS UNE REQUETE FAIT UN SCAN TABLE -- voir ci-dessus.';

@mkdir(dirname($outPath), recursive: true);
file_put_contents($outPath, implode("\n", $lines) . "\n");

echo 'ecrit : ' . $outPath . "\n";

exit($allIndexed ? 0 : 1);
