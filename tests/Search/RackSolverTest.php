<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\Rack;
use App\Search\RackSolver;
use Tests\Support\Assert;

/**
 * Exerce App\Search\RackSolver sur la vraie base storage/dictionary_fr.sqlite (lecture
 * seule) : correction croisee par force brute pour un chevalet connu, comportement du
 * plafond de securite, et le pire cas explicitement nomme par la tache (7 lettres +
 * 2 jokers, aucune contrainte).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $solver = new RackSolver($connection);

    // --- Entree invalide : aucun chevalet, meme convention que TermLookup::find(). ---
    Assert::null($solver->solve(''), 'entree vide');
    Assert::null($solver->solve('ae3t'), 'chiffre dans l\'entree');
    Assert::null($solver->solve('ae***'), 'trois jokers, au-dessus de Rack::MAX_JOKERS');
    Assert::null($solver->solve(str_repeat('a', 16)), '16 lettres, au-dessus de la borne D-010');

    // --- Correction, verifiee par force brute (pas un echantillon) : chevalet POSER, ---
    // --- sans joker. Tout mot admis (ODS8 ou ODS9) de longueur <= 5 dont chaque lettre ---
    // --- est disponible en quantite suffisante dans {P,O,S,E,R} doit apparaitre, et ---
    // --- aucun autre. ---
    $page = $solver->solve('poser');
    Assert::notNull($page);
    Assert::true(!$page->capped);
    Assert::same('eoprs', $page->slug, 'slug canonique = lettres triees, pas l\'ordre de saisie');
    Assert::same(0, $page->jokerCount);

    $pdo = $connection->pdo();
    $statement = $pdo->query('SELECT normalized FROM terms WHERE length <= 5 AND (is_ods8 = 1 OR is_ods9 = 1)');
    $rackCounts = ['P' => 1, 'O' => 1, 'S' => 1, 'E' => 1, 'R' => 1];
    $bruteForce = [];
    foreach ($statement as $row) {
        $word = $row['normalized'];
        $counts = array_count_values(str_split($word));
        $fits = true;
        foreach ($counts as $letter => $count) {
            if (!isset($rackCounts[$letter]) || $count > $rackCounts[$letter]) {
                $fits = false;
                break;
            }
        }
        if ($fits) {
            $bruteForce[] = $word;
        }
    }
    sort($bruteForce);

    $solverWords = array_column($page->matches, 'normalized');
    sort($solverWords);

    Assert::same(34, count($bruteForce), 'nombre de mots attendus par force brute pour le chevalet POSER (verifie a la main)');
    Assert::same($bruteForce, $solverWords, 'RackSolver doit trouver exactement les memes mots que la verification par force brute');
    Assert::true(in_array('POSER', $solverWords, true), 'le mot POSER lui-meme doit apparaitre (anagramme exacte du chevalet complet)');

    // Tri : score decroissant, puis longueur decroissante, puis alphabetique. Plusieurs
    // anagrammes de 5 lettres partagent le meme score (7) : PERSO precede POSER par
    // ordre alphabetique, c'est PERSO qui doit ouvrir la liste, pas POSER.
    $first = $page->matches[0];
    Assert::same('PERSO', $first['normalized']);
    Assert::same(7, $first['score']);
    Assert::same(5, $first['length']);
    for ($i = 1; $i < count($page->matches); $i++) {
        $previous = $page->matches[$i - 1];
        $current = $page->matches[$i];
        $orderOk = $previous['score'] > $current['score']
            || ($previous['score'] === $current['score'] && $previous['length'] > $current['length'])
            || ($previous['score'] === $current['score'] && $previous['length'] === $current['length']
                && $previous['normalized'] <= $current['normalized']);
        Assert::true($orderOk, 'ordre invalide entre ' . $previous['normalized'] . ' et ' . $current['normalized']);
    }

    // Chaque correspondance est necessairement admise -- jamais une forme francaise non
    // admise (modele a trois statuts ferme, CLAUDE.md : "quel mot puis-je jouer" ne
    // repond qu'avec des mots jouables).
    foreach ($page->matches as $match) {
        Assert::true($match['isOds8'] || $match['isOds9'], $match['normalized'] . ' devrait etre admis ODS8 ou ODS9');
    }

    Assert::true($page->queryCount <= 10, 'budget de requetes indexees depasse pour un chevalet de 5 lettres sans joker');

    // --- 1 joker : POSER doit rester atteignable (P,O,S,E + 1 joker valant R). ---
    $withJoker = $solver->solve('pose?');
    Assert::notNull($withJoker);
    Assert::true(!$withJoker->capped);
    Assert::same(1, $withJoker->jokerCount);
    Assert::true(
        in_array('POSER', array_column($withJoker->matches, 'normalized'), true),
        'POSER doit etre atteignable avec P,O,S,E + 1 joker'
    );

    // --- Redirection canonique : '?' et '*' doivent produire le meme slug. ---
    $withStar = $solver->solve('pose*');
    Assert::notNull($withStar);
    Assert::same($withJoker->slug, $withStar->slug, "? et * doivent produire le meme chevalet, donc le meme slug canonique ('*')");

    // --- Pire cas explicitement nomme par la tache : 7 lettres distinctes + 2 jokers, ---
    // --- aucune contrainte. Doit rester sous le plafond de securite et repondre dans ---
    // --- un temps raisonnable, toujours via l'index signature (voir aussi ---
    // --- reports/query-plans/phase2.md pour le detail EXPLAIN QUERY PLAN). ---
    $worstNamed = Rack::fromInput('abcdefg**');
    Assert::notNull($worstNamed);
    $upperBound = RackSolver::upperBoundSignatureCount($worstNamed);
    Assert::true($upperBound <= RackSolver::SIGNATURE_CEILING, 'le pire cas nomme par la tache doit rester sous le plafond de securite');

    $start = hrtime(true);
    $worstPage = $solver->solve('abcdefg**');
    $elapsedMs = (hrtime(true) - $start) / 1e6;

    Assert::notNull($worstPage);
    Assert::true(!$worstPage->capped, 'le pire cas nomme (7 lettres + 2 jokers) ne doit pas declencher le plafond');
    Assert::same(2, $worstPage->jokerCount);
    Assert::true($worstPage->queryCount <= 10, 'le pire cas nomme doit rester sous 10 requetes avec CHUNK_SIZE = 5000, obtenu : ' . $worstPage->queryCount);
    Assert::true($worstPage->candidateSignatureCount > 30000, 'sanity check : le pire cas doit bien engendrer des dizaines de milliers de signatures candidates');
    Assert::true(count($worstPage->matches) === $worstPage->displayLimit, 'le pire cas nomme doit produire plus de resultats que la limite d\'affichage');
    Assert::true($worstPage->truncated, 'le pire cas nomme doit etre marque tronque');
    Assert::true($elapsedMs < 1000.0, 'le pire cas nomme doit repondre en moins d\'une seconde, obtenu : ' . $elapsedMs . ' ms');

    // --- Plafond de securite : un chevalet de 13 lettres distinctes + 2 jokers (15 ---
    // --- caracteres, la borne D-010) doit etre refuse AVANT toute generation ou ---
    // --- requete -- pas une erreur, un resultat distinct (consigne du coordinateur). ---
    $tooLarge = Rack::fromInput('abcdefghijklm**');
    Assert::notNull($tooLarge, 'chevalet syntaxiquement valide : 13 lettres + 2 jokers = 15 caracteres, exactement la borne D-010');
    Assert::true(
        RackSolver::upperBoundSignatureCount($tooLarge) > RackSolver::SIGNATURE_CEILING,
        'ce chevalet doit depasser le plafond de securite (verification de coherence du test lui-meme)'
    );

    $cappedPage = $solver->solve('abcdefghijklm**');
    Assert::notNull($cappedPage, 'un chevalet trop grand est un resultat distinct, pas une entree invalide -> jamais null');
    Assert::true($cappedPage->capped, 'doit declencher le plafond de securite');
    Assert::same([], $cappedPage->matches, 'aucune correspondance calculee quand le plafond est declenche');
    Assert::null($cappedPage->totalMatches, 'totalMatches doit rester null (inconnu), jamais 0 (qui signifierait "aucun resultat trouve")');
    Assert::same(0, $cappedPage->queryCount, 'aucune requete SQLite ne doit etre executee quand le plafond est declenche');
    Assert::same(0, $cappedPage->candidateSignatureCount, 'aucune signature ne doit etre generee quand le plafond est declenche');
};
