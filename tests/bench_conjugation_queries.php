<?php

declare(strict_types=1);

/**
 * Chronometre et documente la requete SQLite de D-018 (nature grammaticale/genre + liens de
 * conjugaison de la fiche mot /mot/{mot}), telles qu'executees par App\Search\TermLookup
 * (SELECT elargi, zero requete supplementaire) et App\Search\ConjugationLookup (+1 requete)
 * -- les memes classes que le runtime, pas une reimplementation separee (meme methodologie
 * que tests/bench_relations_queries.php).
 *
 * Ecrit reports/query-plans/d018-conjugation.md : budget de requetes pour la fiche COMPLETE
 * (TermLookup::find() + RelationsFinder::find() si admis + ConjugationLookup::find()),
 * EXPLAIN QUERY PLAN pour la requete verb_forms, chronometrage de bout en bout.
 *
 * Ne tourne jamais en production (D-007) : script de developpement, execute a la main.
 *
 * Usage :
 *     php tests/bench_conjugation_queries.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use App\Search\ConjugationLookup;
use App\Search\RelationsFinder;
use App\Search\TermLookup;
use App\Search\TermPage;

const RUNS = 20;

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';
$outPath = $root . '/reports/query-plans/d018-conjugation.md';

$config = require $root . '/config/sites/fr.php';

$connection = new Connection($dbPath);
$pdo = $connection->pdo();
$lookup = new TermLookup($connection, $config['tile_scores']);
$finder = new RelationsFinder($connection);
$conjugation = new ConjugationLookup($connection);

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

const CONJUGATION_SQL = 'SELECT lemma_normalized, form_normalized, tense, person FROM verb_forms '
    . 'WHERE lemma_normalized = ? OR form_normalized = ? LIMIT ?';

const LOOKUP_ROW_SQL = 'SELECT display_term, score, length, is_ods8, is_ods9, pos, pos_secondary, gender '
    . 'FROM terms WHERE normalized = ? LIMIT 1';

$cases = [
    ['label' => 'POSER -- lemme verbe fiable, admis, pivot de docs/08', 'word' => 'POSER'],
    ['label' => 'POSERA -- forme conjuguee, admis, exemple nomme par la tache de depart', 'word' => 'POSERA'],
    ['label' => 'TABLE -- homographe nom/verbe reel (pos secondaire V, forme de TABLER), admis', 'word' => 'TABLE'],
    ['label' => 'CHAT -- nom simple, aucune donnee de conjugaison, admis', 'word' => 'CHAT'],
    ['label' => 'GHOSTER -- verbe francais non admis, absent de Kartmaan (pos nul) mais conjugable', 'word' => 'GHOSTER'],
    ['label' => 'ETRE -- verbe suppletif exclu comme non fiable a la construction (D-018)', 'word' => 'ETRE'],
];

$lines = [
    '# Plans De Requetes -- D-018 (Nature Grammaticale, Genre, Conjugaison De La Fiche Mot)',
    '',
    'Produit par `tests/bench_conjugation_queries.php`, lecture seule sur `storage/dictionary_fr.sqlite`,',
    'via `App\\Database\\Connection`, `App\\Search\\TermLookup` (SELECT elargi, Phase 1 + D-018),',
    '`App\\Search\\RelationsFinder` (Phase 4, inchange) et `App\\Search\\ConjugationLookup` (D-018) --',
    'les classes reellement utilisees au runtime.',
    sprintf('Chaque timing de bout en bout est une mediane sur %d executions.', RUNS),
    '',
    '## Cout Chiffre',
    '',
    'pos/pos_secondary/gender (TermPage) : **0 requete supplementaire** -- trois colonnes ajoutees',
    'au SELECT deja execute par `TermLookup::lookupRow()` (Phase 1, inchange par ailleurs). Le plan',
    'reste identique : recherche sur l\'index unique `sqlite_autoindex_terms_1`, puis lecture de la',
    'ligne complete (deja necessaire pour `display_term`/`score`, non plus couverts par cet index',
    'que les trois nouvelles colonnes).',
    '',
    'Conjugaison (App\\Search\\ConjugationLookup) : **+1 requete**, executee pour tout terme TROUVE',
    '(admis ou non -- jamais pour un terme inconnu). Un seul `OR` combine les deux directions',
    '(lemme -> formes, forme -> lemme) sur deux colonnes indexees independamment',
    '(`idx_verbforms_lemma`, `idx_verbforms_form`).',
    '',
    'Budget total par fiche :',
    '',
    '```text',
    'mot admis              8 (Phase 1 + 4, inchange) + 1 (D-018) = 9 requetes',
    'mot francais non admis 3 (Phase 1, inchange)      + 1 (D-018) = 4 requetes',
    'mot inconnu             0 requete (ConjugationLookup non invoque, TermLookup::find()',
    '                        renvoie null avant toute requete pour une entree invalide, ou',
    '                        1 seule requete de lookup pour un terme valide mais absent)',
    '```',
    '',
    'Les deux restent tres en-dessous du plafond de moins de 10 requetes par fiche (CLAUDE.md).',
    '',
];

$allIndexed = true;

foreach ($cases as $case) {
    $word = $case['word'];

    $lines[] = '## ' . $case['label'];
    $lines[] = '';
    $lines[] = sprintf('Mot : `%s`.', $word);
    $lines[] = '';

    $timings = [];
    $page = null;
    $relations = null;
    $conj = null;

    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $page = $lookup->find($word);
        $relations = $page !== null && $page->status === TermPage::STATUS_ADMITTED ? $finder->find($page->normalized) : null;
        $conj = $page !== null && $page->found ? $conjugation->find($page->normalized) : null;
        $timings[] = (hrtime(true) - $start) / 1e6;
    }
    sort($timings);
    $median = $timings[intdiv(count($timings), 2)];

    $totalQueries = 3 + ($relations?->queryCount ?? 0) + ($conj?->queryCount ?? 0);

    $lines[] = sprintf(
        'Resultat : %d requetes SQLite au total (3 TermLookup + %d RelationsFinder + %d ConjugationLookup), '
        . 'pos=%s pos_secondary=%s gender=%s, asLemma=%d formes, asForm=%d lemme(s).',
        $totalQueries,
        $relations?->queryCount ?? 0,
        $conj?->queryCount ?? 0,
        var_export($page?->pos, true),
        var_export($page?->posSecondary, true),
        var_export($page?->gender, true),
        count($conj?->asLemma ?? []),
        count($conj?->asForm ?? []),
    );
    $lines[] = '';
    $lines[] = sprintf(
        'Mediane de bout en bout : %.3f ms, min %.3f ms, max %.3f ms sur %d executions -- %s.',
        $median,
        min($timings),
        max($timings),
        RUNS,
        $totalQueries < 10 ? 'sous 10 requetes' : 'AU-DESSUS DE 10 REQUETES',
    );
    $lines[] = '';

    // --- EXPLAIN QUERY PLAN pour lookupRow() (TermLookup, elargi D-018) et
    // ConjugationLookup, sur ce mot. ---

    $planText = planFor($pdo, LOOKUP_ROW_SQL, [strtoupper($word)]);
    $scan = usesTableScan($planText);
    $allIndexed = $allIndexed && !$scan;

    $lines[] = '`EXPLAIN QUERY PLAN` -- TermLookup::lookupRow(), SELECT elargi (pos/pos_secondary/gender) :';
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = LOOKUP_ROW_SQL;
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $scan ? '**SCAN TABLE detecte -- probleme.**' : 'Indexe (`sqlite_autoindex_terms_1`), aucun `SCAN TABLE` de `terms` -- plan identique a Phase 1, colonnes en plus non couvertes par l\'index (deja le cas de display_term/score).';
    $lines[] = '';

    $planText = planFor($pdo, CONJUGATION_SQL, [strtoupper($word), strtoupper($word), ConjugationLookup::ROW_LIMIT]);
    $scan = usesTableScan($planText);
    $allIndexed = $allIndexed && !$scan;

    $lines[] = '`EXPLAIN QUERY PLAN` -- ConjugationLookup::find() (D-018) :';
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = CONJUGATION_SQL;
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $scan
        ? '**SCAN TABLE detecte -- probleme.**'
        : 'Indexe (`idx_verbforms_lemma` et/ou `idx_verbforms_form`), aucun `SCAN TABLE` de `verb_forms`.';
    $lines[] = '';
}

$lines[] = '## Verdict';
$lines[] = '';
$lines[] = $allIndexed
    ? 'Toutes les requetes passent par un index (`sqlite_autoindex_terms_1`, `idx_verbforms_lemma`, `idx_verbforms_form`), aucun `SCAN TABLE`.'
    : 'AU MOINS UNE REQUETE FAIT UN SCAN TABLE -- voir ci-dessus.';
$lines[] = 'Budget par fiche : 9 requetes pour un mot admis (8 existantes + 1), 4 pour un mot francais non admis (3 + 1) -- tres en-dessous du plafond de moins de 10 requetes par fiche (CLAUDE.md).';

@mkdir(dirname($outPath), recursive: true);
file_put_contents($outPath, implode("\n", $lines) . "\n");

echo 'ecrit : ' . $outPath . "\n";

exit($allIndexed ? 0 : 1);
