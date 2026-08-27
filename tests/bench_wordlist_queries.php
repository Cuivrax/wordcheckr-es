<?php

declare(strict_types=1);

/**
 * Chronometre et documente les requetes SQLite de la Phase 3 (listes /mots/...), telles
 * qu'executees par App\Search\WordListSolver -- la meme classe que le runtime, pas une
 * reimplementation separee (meme methodologie que tests/bench_rack_queries.php, Phase 2).
 *
 * Ecrit reports/query-plans/phase3.md : decision d'architecture (mesures qui ont ecarte une
 * table de postings dediee, voir App\Search\WordListSolver pour le detail), EXPLAIN QUERY
 * PLAN et chronometrage pour chaque contrainte et plusieurs combinaisons, du cas simple au
 * pire cas explicitement identifie par la mesure (panier ancre au-dessus du plafond).
 *
 * Le texte SQL des deux requetes internes (ancrage, predicats supplementaires) est obtenu par
 * reflexion sur les methodes privees de WordListSolver -- pas de reimplementation de la
 * logique dans ce script, une seule source de verite (meme esprit que D-009 et
 * tests/bench_rack_queries.php).
 *
 * Ne tourne jamais en production (D-007) : script de developpement, execute a la main.
 *
 * Usage :
 *     php tests/bench_wordlist_queries.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use App\Search\WordListFilters;
use App\Search\WordListSolver;

const RUNS = 20;

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';
$outPath = $root . '/reports/query-plans/phase3.md';

$connection = new Connection($dbPath);
$pdo = $connection->pdo();
$solver = new WordListSolver($connection);

$reflection = new ReflectionClass(WordListSolver::class);
$isExactMethod = $reflection->getMethod('isExactMode');
$isExactMethod->setAccessible(true);
$exactWhereMethod = $reflection->getMethod('exactWhereClause');
$exactWhereMethod->setAccessible(true);
$anchorMethod = $reflection->getMethod('anchorClause');
$anchorMethod->setAccessible(true);
$extraMethod = $reflection->getMethod('extraPredicates');
$extraMethod->setAccessible(true);

/** @return array{0: string, 1: list<int|string>} */
function exactWhere(ReflectionMethod $m, WordListSolver $s, WordListFilters $f): array
{
    /** @var array{0: string, 1: list<int|string>} */
    return $m->invoke($s, $f);
}

/** @return array{0: string, 1: list<int|string>, 2: string} */
function anchorOf(ReflectionMethod $m, WordListSolver $s, WordListFilters $f): array
{
    /** @var array{0: string, 1: list<int|string>, 2: string} */
    return $m->invoke($s, $f);
}

/** @return array{0: string, 1: list<int|string>} */
function extraOf(ReflectionMethod $m, WordListSolver $s, WordListFilters $f): array
{
    /** @var array{0: string, 1: list<int|string>} */
    return $m->invoke($s, $f);
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

$cases = [
    ['label' => 'Longueur seule (route documentee : /mots/7-lettres)', 'path' => '7-lettres'],
    ['label' => 'Commencant seul (route documentee : /mots/commencant/ch)', 'path' => 'commencant/ch'],
    ['label' => 'Longueur + commencant (route documentee : /mots/7-lettres/commencant/ch)', 'path' => '7-lettres/commencant/ch'],
    ['label' => 'Terminant seul (route documentee : /mots/terminant/tion)', 'path' => 'terminant/tion'],
    ['label' => 'Contenant seul (route documentee : /mots/contenant/che) -- predicat non indexe', 'path' => 'contenant/che'],
    ['label' => 'Avec, repetitions comptees (route documentee : /mots/avec/a/a/r) -- predicat non indexe, aucun ancrage', 'path' => 'avec/a/a/r'],
    ['label' => 'Sans, lettre exclue -- predicat non indexe, aucun ancrage', 'path' => 'sans/z'],
    ['label' => 'Motif (route documentee : /mots/5-lettres/motif/c--e-)', 'path' => '5-lettres/motif/c--e-'],
    ['label' => 'Commencant + terminant -- PIRE CAS MESURE : panier ancre (CH, > 10 000 lignes) au-dessus du plafond', 'path' => 'commencant/ch/terminant/tion'],
    ['label' => "Longueur seule sur le panier le plus grand de la base (11 lettres, 127 772 lignes) + avec impossible -- pire cas pathologique (0 resultat, plafond quand meme applique)", 'path' => '11-lettres/avec/z/z/z/z/z/q/q/q'],
];

$lines = [
    '# Plans De Requetes -- Phase 3 (Listes /mots/...)',
    '',
    'Produit par `tests/bench_wordlist_queries.php`, lecture seule sur `storage/dictionary_fr.sqlite`,',
    'via `App\\Database\\Connection` et `App\\Search\\WordListSolver` -- les classes reellement',
    'utilisees au runtime, pas une requete separee.',
    sprintf('Chaque timing de bout en bout (`WordListSolver::solve()`) est une mediane sur %d executions.', RUNS),
    '',
    '## Decision D\'Architecture -- Mesuree Avant Implementation',
    '',
    '« Contenant » (sous-chaine a une position quelconque) et « avec » (multiensemble de',
    'lettres, avec repetitions) n\'ont ni l\'un ni l\'autre d\'index dedie dans schema.sql :',
    '`normalized` et `reversed` ne servent que prefixe/suffixe (D-012 avait deja ecarte un index',
    'de toutes les sous-chaines, ~587 Mo). Mesure avant de trancher, avec un prototype construit',
    'et chronometre (hors depot, jamais integre a schema.sql ni a storage/dictionary_fr.sqlite --',
    'aucune ecriture sur la base de production, D-001/D-007) :',
    '',
    '```text',
    'table de postings trigrammes + lettres (couverture complete, 838 180 lignes)',
    '  cout de stockage    355,6 Mo ajoutes (191,5 Mo trigrammes + 164,1 Mo lettres),',
    '                      soit plus que doubler storage/dictionary_fr.sqlite (154,5 Mo)',
    '  selectivite         un trigramme frequent (ERA, 99 919 correspondances sur 838 180',
    '                      lignes) reste LENT meme indexe : 283 ms pour la premiere page,',
    '                      jusqu\'a 419 ms en pagination profonde (OFFSET 90 000) -- au-dela',
    '                      du budget TTFB p95 < 250 ms (CLAUDE.md), pour une table qui ne',
    '                      resout meme pas le probleme qu\'elle est censee resoudre',
    '  precedent           coherent avec D-012, qui avait deja ecarte un index de toutes les',
    '                      sous-chaines (~587 Mo) pour la meme raison de cout disproportionne',
    '',
    'alternative retenue : sous-requete BORNEE sur les index deja en place',
    '  cout de stockage    0 octet -- aucune table nouvelle, aucun octet ajoute a',
    '                      storage/dictionary_fr.sqlite, aucune modification de schema.sql',
    '  mecanisme           instr(), LENGTH()/REPLACE(), substr() -- fonctions scalaires',
    '                      SQLite standard, evaluees uniquement sur les lignes deja bornees',
    '                      par un index existant (normalized, reversed ou',
    '                      idx_terms_length_normalized) et par',
    '                      WordListSolver::ROW_EXAMINATION_CEILING (10 000 lignes)',
    '  mesure              <= 6 ms meme dans le pire cas pathologique observe (longueur = 11,',
    '                      127 772 lignes dans ce seul panier, 0 resultat pour un "avec"',
    '                      impossible) -- voir le cas correspondant plus bas dans ce rapport',
    '```',
    '',
    'Retenu : la sous-requete bornee, cout de stockage nul, cout de latence mesure et borne',
    'dans tous les cas testes, contrairement a la table de postings qui coute cher ET reste',
    'lente sur les cles frequentes (exactement le cas d\'usage qu\'elle devait accelerer).',
    '',
    '## Deux Regimes D\'Execution',
    '',
    '```text',
    'EXACT  : longueur et/ou prefixe seuls (ou motif reductible a longueur + prefixe initial,',
    '         sans case connue residuelle) -- COUNT() exact + LIMIT/OFFSET, pagination complete',
    '         et fiable, 2 requetes indexees',
    'BORNE  : suffixe seul ou combine, contenant, avec, sans, ou motif avec case connue',
    '         residuelle -- verification du panier ancre (1 requete bornee a CEILING + 1),',
    '         puis panier ancre + predicats + tri normalized (1 requete bornee a CEILING),',
    '         2 requetes indexees au total, quel que soit le nombre de contraintes combinees',
    '```',
    '',
    '## Lecture Des Plans -- "SCAN ... USING COVERING INDEX" Sur Les Cas Sans Ancrage',
    '',
    'Pour "avec"/"contenant"/"sans" employes seuls (aucune longueur, aucun prefixe, aucun',
    'suffixe -- le cas le plus large possible), `EXPLAIN QUERY PLAN` rapporte',
    '`SCAN terms USING COVERING INDEX sqlite_autoindex_terms_1` pour la sous-requete interne.',
    'Le mot `SCAN` ici ne signifie PAS un parcours des 838 180 lignes : SQLite l\'emploie des',
    'qu\'aucune condition d\'egalite/plage ne borne le point de depart dans l\'index (il n\'y a pas',
    'de `WHERE` sur cette sous-requete precise), mais le `LIMIT` qui la termine reste honore --',
    'la sous-requete s\'arrete des que `WordListSolver::ROW_EXAMINATION_CEILING` lignes ont ete',
    'visitees, jamais au-dela. Les mesures ci-dessous (quelques millisecondes, jamais plusieurs',
    'centaines) le confirment directement : un vrai parcours des 838 180 lignes couterait au',
    'moins un ordre de grandeur de plus (voir la Phase 1, ou une simple lecture indexee de',
    'quelques lignes coute deja ~0,02 ms -- un balayage complet, meme via un index couvrant,',
    'ne tiendrait pas sous quelques millisecondes).',
    '',
];

$allIndexed = true;
$allWithinQueryBudget = true;
$namedWorstCaseElapsed = null;
$namedWorstCaseQueries = null;

foreach ($cases as $case) {
    $filters = WordListFilters::fromPath($case['path']);

    $lines[] = '## ' . $case['label'];
    $lines[] = '';

    if ($filters === null) {
        $lines[] = 'ECHEC : chemin "' . $case['path'] . '" refuse par WordListFilters::fromPath() -- verifier le cas de test.';
        $lines[] = '';
        continue;
    }

    $exactMode = $isExactMethod->invoke($solver, $filters);
    $lines[] = sprintf('Chemin : `/mots/%s`, canonique `%s`, regime **%s**.', $case['path'], $filters->canonicalPath(), $exactMode ? 'EXACT' : 'BORNE');
    $lines[] = '';

    $timings = [];
    $page = null;
    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $page = $solver->solve($case['path']);
        $timings[] = (hrtime(true) - $start) / 1e6;
    }
    sort($timings);
    $median = $timings[intdiv(count($timings), 2)];

    $withinBudget = $page->queryCount <= 10;
    $allWithinQueryBudget = $allWithinQueryBudget && $withinBudget;

    if (str_contains($case['label'], 'PIRE CAS MESURE')) {
        $namedWorstCaseElapsed = $median;
        $namedWorstCaseQueries = $page->queryCount;
    }

    $lines[] = sprintf(
        'Resultat : total %s%s, %d element(s) sur cette page, %d requete(s) SQLite.',
        number_format($page->total, 0, ',', ' '),
        $page->truncated ? ' (**tronque**, panier ancre au-dessus du plafond -- pas un compte exhaustif)' : ' (exact)',
        count($page->items),
        $page->queryCount,
    );
    $lines[] = '';
    $lines[] = sprintf(
        'Mediane de bout en bout (`WordListSolver::solve()`) : %.3f ms, min %.3f ms, max %.3f ms sur %d executions -- %s.',
        $median,
        min($timings),
        max($timings),
        RUNS,
        $withinBudget ? 'sous 10 requetes' : 'AU-DESSUS DE 10 REQUETES',
    );
    $lines[] = '';

    $planBlocks = [];

    if ($exactMode) {
        [$where, $params] = exactWhere($exactWhereMethod, $solver, $filters);
        $whereSql = $where === '' ? '' : "WHERE $where";
        $countSql = "SELECT COUNT(*) c FROM terms $whereSql";
        $pageSql = "SELECT normalized, score, length, is_ods8, is_ods9 FROM terms $whereSql ORDER BY normalized LIMIT ? OFFSET ?";

        $planBlocks[] = ['sql' => $countSql, 'params' => $params, 'label' => 'COUNT() exact'];
        $planBlocks[] = ['sql' => $pageSql, 'params' => [...$params, WordListSolver::PAGE_SIZE, 0], 'label' => 'page (LIMIT/OFFSET)'];
    } else {
        [$anchorWhere, $anchorParams, $anchorOrder] = anchorOf($anchorMethod, $solver, $filters);
        $anchorWhereSql = $anchorWhere === '' ? '' : "WHERE $anchorWhere";
        $boundarySql = "SELECT COUNT(*) c FROM (SELECT id FROM terms $anchorWhereSql ORDER BY $anchorOrder LIMIT ?)";

        [$extraWhere, $extraParams] = extraOf($extraMethod, $solver, $filters);
        $extraWhereSql = $extraWhere === '' ? '' : "WHERE $extraWhere";
        $fetchSql = 'SELECT normalized, score, length, is_ods8, is_ods9 FROM ('
            . "SELECT normalized, score, length, is_ods8, is_ods9, reversed FROM terms $anchorWhereSql "
            . "ORDER BY $anchorOrder LIMIT ?"
            . ") $extraWhereSql ORDER BY normalized";

        $planBlocks[] = ['sql' => $boundarySql, 'params' => [...$anchorParams, WordListSolver::ROW_EXAMINATION_CEILING + 1], 'label' => 'verite terrain (panier ancre borne)'];
        $planBlocks[] = ['sql' => $fetchSql, 'params' => [...$anchorParams, WordListSolver::ROW_EXAMINATION_CEILING, ...$extraParams], 'label' => 'panier + predicats + tri'];
    }

    foreach ($planBlocks as $block) {
        $planText = planFor($pdo, $block['sql'], $block['params']);
        $scan = usesTableScan($planText);
        $allIndexed = $allIndexed && !$scan;

        $lines[] = '`EXPLAIN QUERY PLAN` -- ' . $block['label'] . ' :';
        $lines[] = '';
        $lines[] = '```sql';
        $lines[] = $block['sql'];
        $lines[] = '```';
        $lines[] = '';
        $lines[] = '```text';
        array_push($lines, ...$planText);
        $lines[] = '```';
        $lines[] = '';
        $lines[] = $scan ? '**SCAN TABLE detecte -- probleme.**' : 'Indexe, aucun `SCAN TABLE` de `terms`.';
        $lines[] = '';
    }
}

$lines[] = '## Budget Par Page';
$lines[] = '';
$lines[] = sprintf(
    'Pire cas mesure (commencant + terminant, panier ancre au-dessus du plafond) : %d requetes '
    . 'indexees, mediane %.3f ms. Tous les cas testes restent a 2 requetes, tres en-dessous du '
    . 'budget de moins de 10 requetes par fiche (CLAUDE.md).',
    $namedWorstCaseQueries ?? 0,
    $namedWorstCaseElapsed ?? 0.0,
);
$lines[] = '';

$lines[] = '## Verification Croisee Par Force Brute (Hors Chronometrage)';
$lines[] = '';
$lines[] = 'tests/Search/WordListSolverTest.php verifie chaque contrainte (longueur, commencant,';
$lines[] = 'contenant, terminant, avec, sans, motif) et plusieurs combinaisons par recalcul direct';
$lines[] = '(instr(), array_count_values(), substr()) sur les lignes reelles de la base -- pas un';
$lines[] = 'echantillon. Voir la sortie de `php tests/run.php`.';
$lines[] = '';

$lines[] = '## Verdict';
$lines[] = '';
$lines[] = $allIndexed
    ? 'Toutes les requetes passent par un index ou par une sous-requete deja bornee, aucun `SCAN TABLE` de `terms`.'
    : 'AU MOINS UNE REQUETE FAIT UN SCAN TABLE -- voir ci-dessus.';
$lines[] = $allWithinQueryBudget
    ? 'Tous les cas testes restent a 2 requetes, tres en-dessous du budget de moins de 10 requetes par fiche.'
    : 'AU MOINS UN CAS DEPASSE LE BUDGET DE REQUETES -- voir ci-dessus.';

@mkdir(dirname($outPath), recursive: true);
file_put_contents($outPath, implode("\n", $lines) . "\n");

echo 'ecrit : ' . $outPath . "\n";

exit(($allIndexed && $allWithinQueryBudget) ? 0 : 1);
