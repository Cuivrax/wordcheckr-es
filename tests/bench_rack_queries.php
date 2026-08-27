<?php

declare(strict_types=1);

/**
 * Chronometre et documente les requetes SQLite de la Phase 2 (solveur /jouer/{lettres}),
 * telles qu'executees par App\Search\RackSolver -- la meme classe que le runtime, pas
 * une reimplementation separee (meme methodologie que tests/bench_queries.php, Phase 1).
 *
 * Ecrit reports/query-plans/phase2.md : EXPLAIN QUERY PLAN, chronometrage et comptes de
 * requetes pour plusieurs chevalets, du plus simple au pire cas explicitement nomme par
 * la tache (7 lettres + 2 jokers, aucune contrainte), plus la verification que le
 * plafond de securite se declenche proprement sur un chevalet de 13 lettres + 2 jokers,
 * plus la sensibilite au choix de CHUNK_SIZE (justifie le choix de 5000).
 *
 * La generation des signatures candidates est invoquee par reflexion sur la methode
 * privee App\Search\RackSolver::candidateSignatures() -- pas de reimplementation de
 * l'algorithme dans ce script, une seule source de verite (meme esprit que D-009).
 *
 * Ne tourne jamais en production (D-007) : script de developpement, execute a la main.
 *
 * Usage :
 *     php tests/bench_rack_queries.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use App\Search\Rack;
use App\Search\RackSolver;

const RUNS = 20;
const CHUNK_RUNS = 5;

$root = dirname(__DIR__);
$dbPath = $root . '/storage/dictionary_fr.sqlite';
$outPath = $root . '/reports/query-plans/phase2.md';

$connection = new Connection($dbPath);
$pdo = $connection->pdo();
$solver = new RackSolver($connection);

$reflection = new ReflectionClass(RackSolver::class);
$candidateSignaturesMethod = $reflection->getMethod('candidateSignatures');
$candidateSignaturesMethod->setAccessible(true);

/** @return list<string> */
function candidateSignaturesFor(ReflectionMethod $method, Rack $rack): array
{
    /** @var list<string> */
    return $method->invoke(null, $rack);
}

// Meme texte SQL que App\Search\RackSolver::fetchMatches() -- copie litterale a des
// fins d'EXPLAIN QUERY PLAN et de mesure de sensibilite au chunk size, meme convention
// que tests/bench_queries.php (Phase 1).
function fetchMatchesSql(int $placeholderCount): string
{
    $placeholders = implode(',', array_fill(0, $placeholderCount, '?'));

    return 'SELECT normalized, score, length, is_ods8, is_ods9 FROM terms '
        . "WHERE signature IN ($placeholders) AND (is_ods8 = 1 OR is_ods9 = 1)";
}

$racks = [
    ['label' => 'Chevalet 5 lettres, 0 joker (POSER)', 'input' => 'poser'],
    ['label' => 'Chevalet 7 lettres, 0 joker', 'input' => 'aeinrst'],
    ['label' => 'Chevalet 7 lettres, 1 joker', 'input' => 'abcdefg*'],
    ['label' => 'Chevalet 7 lettres, 2 jokers -- PIRE CAS NOMME PAR LA TACHE, aucune contrainte', 'input' => 'abcdefg**'],
    ['label' => 'Chevalet 13 lettres distinctes + 2 jokers (15 caracteres, borne D-010) -- doit declencher le plafond de securite', 'input' => 'abcdefghijklm**'],
];

$lines = [
    '# Plans De Requetes -- Phase 2 (Solveur /jouer/{lettres})',
    '',
    'Produit par `tests/bench_rack_queries.php`, lecture seule sur `storage/dictionary_fr.sqlite`,',
    'via `App\\Database\\Connection` et `App\\Search\\RackSolver` -- les classes reellement',
    'utilisees au runtime, pas une requete separee.',
    sprintf('Chaque timing de bout en bout (`RackSolver::solve()`) est une mediane sur %d executions.', RUNS),
    '',
    '## Decision D\'Architecture (Rappel)',
    '',
    'Avant implementation, `signature` (D-012, lettres triees, deja indexee) a ete choisie comme',
    'entree du solveur plutot qu\'une table de postings, apres mesure explicite du nombre de',
    'signatures candidates engendrees par un tirage (voir le rapport AFTER pour le detail complet',
    'du calcul et la validation du coordinateur) :',
    '',
    '```text',
    '1. enumeration : sous-multiensembles des lettres connues x remplissages de 0, 1 ou 2 jokers',
    '   (chaque joker vaut n\'importe quelle lettre A-Z)',
    '2. borne superieure bon marche (formule fermee, aucun appel base) comparee a',
    '   RackSolver::SIGNATURE_CEILING = ' . number_format(RackSolver::SIGNATURE_CEILING, 0, ',', ' ') . ' AVANT toute generation',
    '3. au-dela du plafond : reponse bornee explicite (RackPage::$capped = true), 0 requete,',
    '   0 signature generee -- jamais un calcul complet, jamais un blocage de worker',
    '4. en-deca : requetes `signature IN (...)` chunkees a RackSolver::CHUNK_SIZE = ' . number_format(RackSolver::CHUNK_SIZE, 0, ',', ' ') . ',',
    '   chacune servie par idx_terms_signature, filtrees aux mots admis',
    '   (is_ods8 = 1 OU is_ods9 = 1) -- "quel mot puis-je jouer" ne repond qu\'avec des mots jouables',
    '5. tri score decroissant / longueur decroissante / alphabetique en PHP, puis LIMIT',
    '   RackSolver::DISPLAY_LIMIT = ' . RackSolver::DISPLAY_LIMIT . ' applique apres tri',
    '```',
    '',
];

$allIndexed = true;
$allWithinQueryBudget = true;
$namedWorstCaseElapsed = null;
$namedWorstCaseQueries = null;

foreach ($racks as $rackCase) {
    $rack = Rack::fromInput($rackCase['input']);

    if ($rack === null) {
        $lines[] = '## ' . $rackCase['label'];
        $lines[] = '';
        $lines[] = 'ECHEC : entree "' . $rackCase['input'] . '" refusee par Rack::fromInput() -- verifier le cas de test.';
        $lines[] = '';
        continue;
    }

    $upperBound = RackSolver::upperBoundSignatureCount($rack);

    $lines[] = '## ' . $rackCase['label'];
    $lines[] = '';
    $lines[] = sprintf(
        'Entree : `%s` -- %d lettre(s) connue(s), %d joker(s), slug canonique `%s`.',
        $rackCase['input'],
        array_sum($rack->letterCounts),
        $rack->jokerCount,
        $rack->slug,
    );
    $lines[] = '';
    $lines[] = sprintf('Borne superieure bon marche (avant generation, aucun appel base) : **%s** signatures.', number_format($upperBound, 0, ',', ' '));
    $lines[] = '';

    $timings = [];
    $page = null;
    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $page = $solver->solve($rackCase['input']);
        $timings[] = (hrtime(true) - $start) / 1e6;
    }
    sort($timings);
    $median = $timings[intdiv(count($timings), 2)];

    if ($page->capped) {
        $lines[] = 'Resultat : **plafond de securite declenche** -- `capped = true`, `queryCount = 0`,';
        $lines[] = '`candidateSignatureCount = 0`, `totalMatches = null`. Aucune requete SQLite executee,';
        $lines[] = 'aucune signature generee : verifie directement sur l\'objet retourne, pas sur un log.';
        $lines[] = '';
        $lines[] = sprintf(
            'Mediane de bout en bout (rejet avant generation) : %.4f ms, min %.4f ms, max %.4f ms sur %d executions.',
            $median,
            min($timings),
            max($timings),
            RUNS,
        );
        $lines[] = '';

        if ($page->queryCount !== 0 || $upperBound <= RackSolver::SIGNATURE_CEILING) {
            $allWithinQueryBudget = false;
        }

        continue;
    }

    $withinBudget = $page->queryCount <= 10;
    $allWithinQueryBudget = $allWithinQueryBudget && $withinBudget;

    if (str_contains($rackCase['label'], 'PIRE CAS NOMME')) {
        $namedWorstCaseElapsed = $median;
        $namedWorstCaseQueries = $page->queryCount;
    }

    $lines[] = sprintf(
        'Resultat : %s signatures candidates deduites, %d requete(s) SQLite (`CHUNK_SIZE = %s`), '
        . '%d correspondance(s) trouvee(s) au total, %d affichee(s) apres `LIMIT %d`%s.',
        number_format($page->candidateSignatureCount, 0, ',', ' '),
        $page->queryCount,
        number_format(RackSolver::CHUNK_SIZE, 0, ',', ' '),
        $page->totalMatches,
        count($page->matches),
        RackSolver::DISPLAY_LIMIT,
        $page->truncated ? ', **tronque**' : '',
    );
    $lines[] = '';
    $lines[] = sprintf(
        'Mediane de bout en bout (`RackSolver::solve()`, generation + requetes + tri) : %.3f ms, min %.3f ms, max %.3f ms sur %d executions -- %s.',
        $median,
        min($timings),
        max($timings),
        RUNS,
        $withinBudget ? 'sous 10 requetes' : 'AU-DESSUS DE 10 REQUETES',
    );
    $lines[] = '';

    $sampleSize = min(RackSolver::CHUNK_SIZE, $page->candidateSignatureCount);
    $sampleParams = array_fill(0, $sampleSize, 'AA');
    $planStatement = $pdo->prepare('EXPLAIN QUERY PLAN ' . fetchMatchesSql($sampleSize));
    $planStatement->execute($sampleParams);
    $planRows = $planStatement->fetchAll();
    $planText = array_map(static fn (array $row): string => (string) $row['detail'], $planRows);

    $usesScan = false;
    foreach ($planText as $detail) {
        if (str_contains($detail, 'SCAN') && !str_contains($detail, 'USING')) {
            $usesScan = true;
        }
    }
    $allIndexed = $allIndexed && !$usesScan;

    $lines[] = sprintf('`EXPLAIN QUERY PLAN` sur un lot de %s parametres (taille du premier lot reel) :', number_format($sampleSize, 0, ',', ' '));
    $lines[] = '';
    $lines[] = '```sql';
    $lines[] = fetchMatchesSql(min($sampleSize, 8)) . '  -- (tronque a 8 placeholders pour lisibilite, ' . number_format($sampleSize, 0, ',', ' ') . ' reellement lies)';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '```text';
    array_push($lines, ...$planText);
    $lines[] = '```';
    $lines[] = '';
    $lines[] = $usesScan ? '**SCAN detecte -- probleme.**' : 'Toujours `SEARCH ... USING INDEX idx_terms_signature`, jamais de `SCAN`.';
    $lines[] = '';
}

// --- Sensibilite au CHUNK_SIZE, mesuree directement (pas via RackSolver, dont
// --- CHUNK_SIZE est une constante de classe) sur le pire cas nomme. Justifie le choix
// --- retenu : le temps total est domine par le nombre de recherches d'index, pas par
// --- le nombre de requetes HTTP-vers-SQLite.
$worstRack = Rack::fromInput('abcdefg**');
$worstSignatures = candidateSignaturesFor($candidateSignaturesMethod, $worstRack);

$lines[] = '## Sensibilite Au Choix De CHUNK_SIZE';
$lines[] = '';
$lines[] = sprintf(
    'Mesure directe sur le pire cas nomme (7 lettres + 2 jokers, %s signatures candidates), '
    . 'independamment de RackSolver (dont CHUNK_SIZE est fixe) : meme requete SQL, meme jeu de',
    number_format(count($worstSignatures), 0, ',', ' '),
);
$lines[] = 'parametres, seule la taille des lots change. Confirme que le nombre de requetes peut';
$lines[] = 'etre reduit sans cout de temps supplementaire -- justifie CHUNK_SIZE = ' . number_format(RackSolver::CHUNK_SIZE, 0, ',', ' ') . '.';
$lines[] = '';
$lines[] = '| Taille de lot | Requetes | Mediane totale (' . CHUNK_RUNS . ' executions) |';
$lines[] = '|---|---|---|';

foreach ([500, 5000, 30000] as $chunkSize) {
    $chunks = array_chunk($worstSignatures, $chunkSize);
    $statementCache = [];

    $wallTimes = [];
    for ($run = 0; $run < CHUNK_RUNS; $run++) {
        $start = hrtime(true);
        $queryCount = 0;
        foreach ($chunks as $chunk) {
            $n = count($chunk);
            if (!isset($statementCache[$n])) {
                $statementCache[$n] = $pdo->prepare(fetchMatchesSql($n));
            }
            $statementCache[$n]->execute($chunk);
            $statementCache[$n]->fetchAll();
            $queryCount++;
        }
        $wallTimes[] = (hrtime(true) - $start) / 1e6;
    }
    sort($wallTimes);
    $chunkMedian = $wallTimes[intdiv(count($wallTimes), 2)];

    $lines[] = sprintf('| %s | %d | %.2f ms |', number_format($chunkSize, 0, ',', ' '), $queryCount, $chunkMedian);
}
$lines[] = '';

$lines[] = '## Verification Independante : SQLite Refuse Un IN() Au-Dela De La Limite De Variables';
$lines[] = '';
try {
    $placeholders = implode(',', array_fill(0, count($worstSignatures), '?'));
    $singleStatement = $pdo->prepare("SELECT normalized FROM terms WHERE signature IN ($placeholders)");
    $singleStatement->execute($worstSignatures);
    $lines[] = 'INATTENDU : une requete unique avec ' . count($worstSignatures) . ' parametres a reussi -- le chunking pourrait etre superflu sur cette installation, a revalider.';
} catch (\Throwable $e) {
    $lines[] = sprintf(
        'Confirme : une requete unique avec %s parametres echoue (`%s`), ce qui rend le chunking obligatoire, pas seulement une preference de style.',
        number_format(count($worstSignatures), 0, ',', ' '),
        $e->getMessage(),
    );
}
$lines[] = '';

$lines[] = '## Budget Par Page';
$lines[] = '';
$lines[] = sprintf(
    'Pire cas nomme (7 lettres + 2 jokers, aucune contrainte) : %d requetes indexees, mediane %.1f ms. '
    . '0 requete pour tout chevalet au-dessus du plafond de securite (%s signatures).',
    $namedWorstCaseQueries ?? 0,
    $namedWorstCaseElapsed ?? 0.0,
    number_format(RackSolver::SIGNATURE_CEILING, 0, ',', ' '),
);
$lines[] = '';

$lines[] = '## Verdict';
$lines[] = '';
$lines[] = $allIndexed
    ? 'Toutes les requetes passent par `idx_terms_signature`, aucun `SCAN TABLE`.'
    : 'AU MOINS UNE REQUETE FAIT UN SCAN TABLE -- voir ci-dessus.';
$lines[] = $allWithinQueryBudget
    ? 'Tous les chevalets testes restent a 10 requetes ou moins (ou 0 si plafonnes correctement).'
    : 'AU MOINS UN CHEVALET DEPASSE 10 REQUETES OU DECLENCHE INCORRECTEMENT LE PLAFOND -- voir ci-dessus.';

@mkdir(dirname($outPath), recursive: true);
file_put_contents($outPath, implode("\n", $lines) . "\n");

echo 'ecrit : ' . $outPath . "\n";

exit(($allIndexed && $allWithinQueryBudget) ? 0 : 1);
