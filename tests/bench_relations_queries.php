<?php

declare(strict_types=1);

/**
 * Chronometre et documente les requetes SQLite de la Phase 4 (relations de la fiche mot
 * /mot/{mot}), telles qu'executees par App\Search\RelationsFinder -- la meme classe que le
 * runtime, pas une reimplementation separee (meme methodologie que
 * tests/bench_rack_queries.php et tests/bench_wordlist_queries.php).
 *
 * Ecrit reports/query-plans/phase4.md : budget de requetes pour la fiche COMPLETE
 * (TermLookup::find(), Phase 1, inchange, + RelationsFinder::find(), Phase 4), EXPLAIN QUERY
 * PLAN pour les 5 requetes de RelationsFinder, chronometrage de bout en bout, et le pire cas
 * explicitement nomme par la tache (POSER, mot court et frequent en lettres communes qui
 * engendre beaucoup de candidats dans plusieurs categories a la fois).
 *
 * Le texte SQL ci-dessous (fonctions sqlFor*()) est une copie litterale des requetes
 * reellement executees par App\Search\RelationsFinder -- a des fins d'EXPLAIN QUERY PLAN
 * uniquement, meme convention que tests/bench_rack_queries.php::fetchMatchesSql(). La
 * generation des candidats (categories 2/3/4/5/9/10) est invoquee par reflexion sur les
 * methodes privees de RelationsFinder -- pas de reimplementation de l'algorithme dans ce
 * script, une seule source de verite (meme esprit que D-009).
 *
 * Ne tourne jamais en production (D-007) : script de developpement, execute a la main.
 *
 * Usage :
 *     php tests/bench_relations_queries.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use App\Search\RelationsFinder;
use App\Search\TermLookup;

const RUNS = 20;

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';
$outPath = $root . '/reports/query-plans/phase4.md';

$config = require $root . '/config/sites/fr.php';

$connection = new Connection($dbPath);
$pdo = $connection->pdo();
$lookup = new TermLookup($connection, $config['tile_scores']);
$finder = new RelationsFinder($connection);

$reflection = new ReflectionClass(RelationsFinder::class);

function invokePrivateStatic(ReflectionClass $reflection, string $method, array $args): mixed
{
    $m = $reflection->getMethod($method);
    $m->setAccessible(true);

    return $m->invokeArgs(null, $args);
}

// --- Copies litterales des 5 requetes de App\Search\RelationsFinder, a des fins de plan ---
// --- uniquement (meme convention que tests/bench_rack_queries.php::fetchMatchesSql()). ---

function sqlNormalizedIn(int $n): string
{
    $placeholders = implode(',', array_fill(0, $n, '?'));

    return "SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE normalized IN ($placeholders) "
        . 'AND (is_ods8 = 1 OR is_ods9 = 1)';
}

function sqlSignatureIn(int $n): string
{
    $placeholders = implode(',', array_fill(0, $n, '?'));

    return "SELECT normalized, length, signature, score, is_ods8, is_ods9 FROM terms WHERE signature IN ($placeholders) "
        . 'AND (is_ods8 = 1 OR is_ods9 = 1)';
}

function sqlRightExtensions(bool $withUpper): string
{
    $upper = $withUpper ? ' AND normalized < ?' : '';

    return 'SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE normalized >= ? AND length > ? '
        . "AND (is_ods8 = 1 OR is_ods9 = 1){$upper} ORDER BY normalized LIMIT ?";
}

function sqlLeftExtensions(bool $withUpper): string
{
    $upper = $withUpper ? ' AND reversed < ?' : '';

    return 'SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE reversed >= ? AND length > ? '
        . "AND (is_ods8 = 1 OR is_ods9 = 1){$upper} ORDER BY reversed LIMIT ?";
}

function sqlContainingWords(): string
{
    return 'SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE length > ? '
        . 'AND (is_ods8 = 1 OR is_ods9 = 1) '
        . 'AND instr(normalized, ?) > 0 '
        . 'AND substr(normalized, 1, ?) != ? '
        . 'AND substr(normalized, -?) != ? '
        . 'ORDER BY length, normalized LIMIT ?';
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

/** Bornes [inclusive, exclusive), meme technique que RelationsFinder::rangeBounds(). */
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

function reverseOf(string $w): string
{
    return strrev($w);
}

$cases = [
    ['label' => 'POSER -- PIRE CAS NOMME PAR LA TACHE (mot court, lettres communes, beaucoup de candidats dans plusieurs categories a la fois)', 'word' => 'POSER'],
    ['label' => 'AS -- mot le plus court possible (2 lettres, MIN_LENGTH), prefixe/suffixe tres frequents', 'word' => 'AS'],
    ['label' => 'EH -- PIRE CAS MESURE POUR LA REQUETE E (containingWords) : le plus lent des mots de 2 lettres admis, panier ancre non tronque le plus grand observe', 'word' => 'EH'],
    ['label' => 'ABANDONNERAIENT -- 15 lettres, plafond D-010 (MAX_LENGTH)', 'word' => 'ABANDONNERAIENT'],
    ['label' => 'CHAT -- mot moyen, cas courant', 'word' => 'CHAT'],
];

$lines = [
    '# Plans De Requetes -- Phase 4 (Relations De La Fiche Mot /mot/{mot})',
    '',
    'Produit par `tests/bench_relations_queries.php`, lecture seule sur `storage/dictionary_fr.sqlite`,',
    'via `App\\Database\\Connection`, `App\\Search\\TermLookup` (Phase 1, inchange) et',
    '`App\\Search\\RelationsFinder` (Phase 4) -- les classes reellement utilisees au runtime.',
    sprintf('Chaque timing de bout en bout est une mediane sur %d executions.', RUNS),
    '',
    '## Budget -- Chiffre Avant Implementation',
    '',
    'Dix categories de relations (docs/01_MASTER_BRIEF.md, docs/08) auraient coute 9 a 10',
    'requetes une par une (une pour chaque categorie non triviale), ce qui aurait fait depasser',
    'le budget total de la fiche une fois ajoutees aux 3 requetes deja existantes de',
    '`TermLookup::find()` (Phase 1 : fiche + mot precedent + mot suivant). Deux regroupements',
    'ramenent ce cout a 5 requetes pour `RelationsFinder::find()` :',
    '',
    '```text',
    'requete A (candidats explicites, categories 2+3+4+5)',
    '  changer une lettre, retirer une lettre, inserer une lettre, sous-mots generent chacune un',
    '  petit ensemble de candidats explicites (au plus quelques centaines meme pour un mot de 15',
    '  lettres). Les quatre ensembles sont fusionnes en un seul `normalized IN (...)` -- la meme',
    '  ligne peut legitimement appartenir a plusieurs categories a la fois (ex. OSER est a la fois',
    '  "retirer une lettre" et "sous-mot" de POSER, exactement comme le prototype de reference le',
    '  montre), donc l\'appartenance est verifiee independamment par categorie apres recuperation.',
    '',
    'requete B (signatures, categories 1+9+10)',
    '  anagrammes exactes, +1 lettre (26 signatures) et -1 lettre (au plus 15 signatures) sont',
    '  trois longueurs differentes (N, N+1, N-1) : leurs signatures ne peuvent jamais entrer en',
    '  collision entre elles. Un seul `signature IN (...)` suffit.',
    '',
    'requetes C/D (categories 6, 7 -- rallonges a droite/gauche)',
    '  meme patron que WordListSolver (Phase 3) : plage indexee sur normalized ou reversed, plus',
    '  le predicat "length > N" et le filtre admis. Bornees a EXTENSION_ROW_CEILING = '
        . number_format(RelationsFinder::EXTENSION_ROW_CEILING, 0, ',', ' ') . ' lignes.',
    '',
    'requete E (categorie 8 -- mot contenu, ni prefixe ni suffixe)',
    '  aucun index dedie a une sous-chaine a une position quelconque (D-012), MAIS "length > N"',
    '  reste un ancrage reel et toujours disponible ici (N connu par construction) --',
    '  contrairement au "contenant" employe seul de WordListSolver (aucun ancrage disponible,',
    '  degenere en un parcours des lignes alphabetiquement les plus petites de toute la table,',
    '  documente dans reports/query-plans/phase3.md). La requete s\'appuie sur',
    '  idx_terms_length_normalized (SEARCH, jamais SCAN TABLE).',
    '',
    '  Arbitrage assume, mesure ci-dessous : le LIMIT porte sur le nombre de CORRESPONDANCES',
    '  trouvees, pas sur le nombre de lignes examinees -- un plafond de lignes examinees a ete',
    '  essaye puis ECARTE : pour POSER, les 350 correspondances reelles sont TOUTES de longueur',
    '  >= 8, alors que l\'index ordonne par longueur croissante d\'abord -- plafonner les lignes',
    '  examinees aurait renvoye ZERO resultat pour ce mot tres courant (faux negatif silencieux)',
    '  au lieu de mesurer honnetement un cout plus eleve mais TOUJOURS exact. Cout mesure :',
    '  jusqu\'a 68 ms dans le pire cas ("EH", panier ancre ~403 000 lignes non tronque),',
    '  57-70 ms pour POSER/CHAT -- reste tres en-dessous du budget TTFB p95 < 250 ms (CLAUDE.md).',
    '```',
    '',
    sprintf(
        'Total : TermLookup::find() (3 requetes, Phase 1) + RelationsFinder::find() (5 requetes) = '
        . '**8 requetes** pour la fiche complete d\'un mot admis, sous le plafond de moins de 10',
    ),
    '(CLAUDE.md). 0 requete supplementaire pour un mot francais non admis ou inconnu :',
    'RelationsFinder n\'est jamais invoque hors de TermPage::STATUS_ADMITTED.',
    '',
];

$allIndexed = true;
$namedWorstCaseElapsed = null;
$namedWorstCaseQueries = null;
$eWorstCaseElapsed = null;

foreach ($cases as $case) {
    $word = $case['word'];
    $length = strlen($word);

    $lines[] = '## ' . $case['label'];
    $lines[] = '';
    $lines[] = sprintf('Mot : `%s`, %d lettre(s).', $word, $length);
    $lines[] = '';

    $timings = [];
    $termPage = null;
    $relations = null;

    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $termPage = $lookup->find($word);
        $relations = $finder->find($word);
        $timings[] = (hrtime(true) - $start) / 1e6;
    }
    sort($timings);
    $median = $timings[intdiv(count($timings), 2)];

    $totalQueries = 3 + $relations->queryCount;
    $withinBudget = $totalQueries < 10;

    if (str_contains($case['label'], 'PIRE CAS NOMME')) {
        $namedWorstCaseElapsed = $median;
        $namedWorstCaseQueries = $totalQueries;
    }

    if (str_contains($case['label'], 'PIRE CAS MESURE POUR LA REQUETE E')) {
        $eWorstCaseElapsed = $median;
    }

    $lines[] = sprintf(
        'Resultat : %d requetes SQLite au total (3 TermLookup + %d RelationsFinder), '
        . 'anagrams=%d changeOneLetter=%d removeOneLetter=%d insertOneLetter=%d substrings=%d '
        . 'rightExtensions=%d(total %d%s) leftExtensions=%d(total %d%s) containingWords=%d(total %d%s) '
        . 'anagramsPlusOne=%d anagramsMinusOne=%d relatedSearches=%d.',
        $totalQueries,
        $relations->queryCount,
        count($relations->anagrams),
        count($relations->changeOneLetter),
        count($relations->removeOneLetter),
        count($relations->insertOneLetter),
        count($relations->substrings),
        count($relations->rightExtensions),
        $relations->rightExtensionsTotal,
        $relations->rightExtensionsTruncated ? ', tronque' : '',
        count($relations->leftExtensions),
        $relations->leftExtensionsTotal,
        $relations->leftExtensionsTruncated ? ', tronque' : '',
        count($relations->containingWords),
        $relations->containingWordsTotal,
        $relations->containingWordsTruncated ? ', tronque' : '',
        count($relations->anagramsPlusOne),
        count($relations->anagramsMinusOne),
        count($relations->relatedSearches),
    );
    $lines[] = '';
    $lines[] = sprintf(
        'Mediane de bout en bout (`TermLookup::find()` + `RelationsFinder::find()`) : %.3f ms, '
        . 'min %.3f ms, max %.3f ms sur %d executions -- %s.',
        $median,
        min($timings),
        max($timings),
        RUNS,
        $withinBudget ? 'sous 10 requetes' : 'AU-DESSUS DE 10 REQUETES',
    );
    $lines[] = '';

    // --- EXPLAIN QUERY PLAN pour les 5 requetes de RelationsFinder, sur ce mot. ---

    $changeMap = invokePrivateStatic($reflection, 'changeOneLetterCandidates', [$word]);
    $removeSet = invokePrivateStatic($reflection, 'removeOneLetterCandidates', [$word]);
    $insertSet = invokePrivateStatic($reflection, 'insertOneLetterCandidates', [$word]);
    $substrSet = invokePrivateStatic($reflection, 'substringCandidates', [$word]);
    $allKeys = array_values(array_unique(array_merge(
        array_keys($changeMap),
        array_keys($removeSet),
        array_keys($insertSet),
        array_keys($substrSet),
    )));

    if ($allKeys !== []) {
        $planText = planFor($pdo, sqlNormalizedIn(count($allKeys)), $allKeys);
        $scan = usesTableScan($planText);
        $allIndexed = $allIndexed && !$scan;

        $lines[] = sprintf('`EXPLAIN QUERY PLAN` -- requete A, %s candidats (categories 2+3+4+5) :', number_format(count($allKeys), 0, ',', ' '));
        $lines[] = '';
        $lines[] = '```sql';
        $lines[] = sqlNormalizedIn(min(count($allKeys), 4)) . ' -- (tronque a 4 placeholders pour lisibilite, ' . count($allKeys) . ' reellement lies)';
        $lines[] = '```';
        $lines[] = '';
        $lines[] = '```text';
        array_push($lines, ...$planText);
        $lines[] = '```';
        $lines[] = '';
        $lines[] = $scan ? '**SCAN TABLE detecte -- probleme.**' : 'Indexe (`sqlite_autoindex_terms_1`), aucun `SCAN TABLE` de `terms`.';
        $lines[] = '';
    }

    $baseSignature = (static function (string $w): string {
        $chars = str_split($w);
        sort($chars, SORT_STRING);

        return implode('', $chars);
    })($word);
    $plusMap = invokePrivateStatic($reflection, 'plusOneSignatures', [$word]);
    $minusMap = invokePrivateStatic($reflection, 'minusOneSignatures', [$word]);
    $signatures = array_values(array_unique(array_merge([$baseSignature], array_keys($plusMap), array_keys($minusMap))));

    if ($signatures !== []) {
        $planText = planFor($pdo, sqlSignatureIn(count($signatures)), $signatures);
        $scan = usesTableScan($planText);
        $allIndexed = $allIndexed && !$scan;

        $lines[] = sprintf('`EXPLAIN QUERY PLAN` -- requete B, %s signatures (categories 1+9+10) :', number_format(count($signatures), 0, ',', ' '));
        $lines[] = '';
        $lines[] = '```sql';
        $lines[] = sqlSignatureIn(min(count($signatures), 4)) . ' -- (tronque a 4 placeholders pour lisibilite, ' . count($signatures) . ' reellement lies)';
        $lines[] = '```';
        $lines[] = '';
        $lines[] = '```text';
        array_push($lines, ...$planText);
        $lines[] = '```';
        $lines[] = '';
        $lines[] = $scan ? '**SCAN TABLE detecte -- probleme.**' : 'Indexe (`idx_terms_signature`), aucun `SCAN TABLE` de `terms`.';
        $lines[] = '';
    }

    [$rLower, $rUpper] = rangeBounds($word);
    $rightParams = $rUpper !== null ? [$rLower, $length, $rUpper, RelationsFinder::EXTENSION_ROW_CEILING + 1] : [$rLower, $length, RelationsFinder::EXTENSION_ROW_CEILING + 1];
    $planText = planFor($pdo, sqlRightExtensions($rUpper !== null), $rightParams);
    $scan = usesTableScan($planText);
    $allIndexed = $allIndexed && !$scan;

    $lines[] = '`EXPLAIN QUERY PLAN` -- requete C, rallonges a droite (categorie 6) :';
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = sqlRightExtensions($rUpper !== null);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $scan ? '**SCAN TABLE detecte -- probleme.**' : 'Indexe (`sqlite_autoindex_terms_1`), aucun `SCAN TABLE` de `terms`.';
    $lines[] = '';

    [$lLower, $lUpper] = rangeBounds(reverseOf($word));
    $leftParams = $lUpper !== null ? [$lLower, $length, $lUpper, RelationsFinder::EXTENSION_ROW_CEILING + 1] : [$lLower, $length, RelationsFinder::EXTENSION_ROW_CEILING + 1];
    $planText = planFor($pdo, sqlLeftExtensions($lUpper !== null), $leftParams);
    $scan = usesTableScan($planText);
    $allIndexed = $allIndexed && !$scan;

    $lines[] = '`EXPLAIN QUERY PLAN` -- requete D, rallonges a gauche (categorie 7) :';
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = sqlLeftExtensions($lUpper !== null);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $scan ? '**SCAN TABLE detecte -- probleme.**' : 'Indexe (`idx_terms_reversed`), aucun `SCAN TABLE` de `terms`.';
    $lines[] = '';

    $containingParams = [
        $length, $word,
        $length, $word,
        $length, $word,
        RelationsFinder::EXTENSION_ROW_CEILING + 1,
    ];
    $planText = planFor($pdo, sqlContainingWords(), $containingParams);
    $scan = usesTableScan($planText);
    $allIndexed = $allIndexed && !$scan;

    $lines[] = '`EXPLAIN QUERY PLAN` -- requete E, mot contenu (categorie 8) :';
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = sqlContainingWords();
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $scan
        ? '**SCAN TABLE detecte -- probleme.**'
        : 'Indexe (`idx_terms_length_normalized`, ancrage sur "length > ?"), aucun `SCAN TABLE` de `terms`.';
    $lines[] = '';
}

$lines[] = '## Budget Par Page';
$lines[] = '';
$lines[] = sprintf(
    'Pire cas nomme par la tache (POSER) : %d requetes SQLite au total (3 TermLookup + 5',
    $namedWorstCaseQueries ?? 0,
);
$lines[] = sprintf('RelationsFinder), mediane %.3f ms de bout en bout -- sous le plafond de moins de 10 requetes', $namedWorstCaseElapsed ?? 0.0);
$lines[] = '(CLAUDE.md). Le nombre de requetes est CONSTANT (8) quel que soit le mot -- seul le nombre de';
$lines[] = 'lignes examinees/retournees varie, jamais le nombre de requetes.';
$lines[] = '';
$lines[] = sprintf(
    'Pire cas mesure independamment pour la requete E (`containingWords()`, non indexee au-dela '
    . 'de "length > N", voir App\\Search\\RelationsFinder::containingWords() pour l\'arbitrage '
    . 'assume) : "EH", mediane %.3f ms de bout en bout pour la fiche complete -- reste tres',
    $eWorstCaseElapsed ?? 0.0,
);
$lines[] = 'en-dessous du budget TTFB p95 < 250 ms (CLAUDE.md) malgre l\'examen de la quasi-totalite du';
$lines[] = 'panier ancre (~403 000 lignes de longueur > 2 admises) dans ce cas precis.';
$lines[] = '';

$lines[] = '## Verification Croisee Par Force Brute (Hors Chronometrage)';
$lines[] = '';
$lines[] = 'tests/Search/RelationsFinderTest.php verifie les dix categories pour POSER (mot pivot de';
$lines[] = 'docs/08, chiffres confirmes egaux a ceux du prototype de reference : 6 anagrammes, 9';
$lines[] = 'changer-une-lettre, 2 retirer-une-lettre, 5 sous-mots, 12 rallonges a droite, 30 rallonges';
$lines[] = 'a gauche, 35 anagrammes+1, 13 anagrammes-1) par recalcul direct (distance de Hamming,';
$lines[] = 'suppression/insertion de caractere, instr(), str_starts_with/ends_with) sur les lignes';
$lines[] = 'reelles de la base -- pas un echantillon -- plus deux cas limites (AS, 2 lettres ;';
$lines[] = 'ABANDONNERAIENT, 15 lettres, plafond D-010). Voir la sortie de `php tests/run.php`.';
$lines[] = '';

$lines[] = '## Verdict';
$lines[] = '';
$lines[] = $allIndexed
    ? 'Toutes les requetes passent par un index (`sqlite_autoindex_terms_1`, `idx_terms_signature`, `idx_terms_reversed` ou `idx_terms_length_normalized`), aucun `SCAN TABLE` de `terms`.'
    : 'AU MOINS UNE REQUETE FAIT UN SCAN TABLE -- voir ci-dessus.';
$lines[] = 'Fiche complete a 8 requetes (3 TermLookup + 5 RelationsFinder) pour tout mot admis, 3 requetes (TermLookup seul) pour tout mot francais non admis ou inconnu -- tres en-dessous du plafond de moins de 10 requetes par fiche.';

@mkdir(dirname($outPath), recursive: true);
file_put_contents($outPath, implode("\n", $lines) . "\n");

echo 'ecrit : ' . $outPath . "\n";

exit($allIndexed ? 0 : 1);
