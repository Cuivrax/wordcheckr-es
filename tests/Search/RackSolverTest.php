<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\Normalizer;
use App\Search\Rack;
use App\Search\RackSolver;
use Tests\Support\Assert;

/**
 * Exerce App\Search\RackSolver sur la vraie base storage/dictionary_es.sqlite (lecture
 * seule) : correction croisee par force brute pour un chevalet connu (avec et sans
 * tuile digramme), comportement du plafond de securite.
 *
 * Adapte de tests/Search/RackSolverTest.php (site francais) -- difference
 * structurelle : la force brute compare des multiensembles de TUILES
 * (Normalizer::tokenizeTiles()), pas de caracteres (str_split()), pour rester
 * coherente avec le fonctionnement reel du solveur espagnol (CH/LL/RR).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $solver = new RackSolver($connection);
    $pdo = $connection->pdo();

    /** @return list<string> */
    $bruteForceMatches = static function (array $rackTileCounts, int $maxLength) use ($pdo): array {
        $statement = $pdo->query(sprintf('SELECT normalized FROM terms WHERE length <= %d AND (is_ods8 = 1 OR is_ods9 = 1)', $maxLength));
        $matches = [];
        foreach ($statement as $row) {
            $word = $row['normalized'];
            $counts = array_count_values(Normalizer::tokenizeTiles($word));
            $fits = true;
            foreach ($counts as $tile => $count) {
                if (!isset($rackTileCounts[$tile]) || $count > $rackTileCounts[$tile]) {
                    $fits = false;
                    break;
                }
            }
            if ($fits) {
                $matches[] = $word;
            }
        }
        sort($matches);
        return $matches;
    };

    // --- Entree invalide : aucun chevalet, meme convention que TermLookup::find(). ---
    Assert::null($solver->solve(''), 'entree vide');
    Assert::null($solver->solve('ae3t'), 'chiffre dans l\'entree');
    Assert::null($solver->solve('ae***'), 'trois jokers, au-dessus de Rack::MAX_JOKERS');
    Assert::null($solver->solve(str_repeat('a', 16)), '16 lettres, au-dessus de la borne');

    // --- Correction, verifiee par force brute (pas un echantillon) : chevalet CARTAS ---
    // --- (aucune tuile digramme -- cas de base), sans joker. Tout mot admis (FILE 2017 ---
    // --- ou FISE-2) de longueur <= 6 dont chaque TUILE est disponible en quantite ---
    // --- suffisante dans {C,A,A,R,T,S} doit apparaitre, et aucun autre. ---
    $page = $solver->solve('cartas');
    Assert::notNull($page);
    Assert::true(!$page->capped);
    Assert::same('a-a-c-r-s-t', $page->slug, 'slug canonique = tuiles triees jointes par un tiret, pas l\'ordre de saisie');
    Assert::same(0, $page->jokerCount);

    $bruteForce = $bruteForceMatches(['C' => 1, 'A' => 2, 'R' => 1, 'T' => 1, 'S' => 1], 6);
    $solverWords = array_column($page->matches, 'normalized');
    sort($solverWords);

    Assert::same(69, count($bruteForce), 'nombre de mots attendus par force brute pour le chevalet CARTAS (verifie a la main)');
    Assert::same($bruteForce, $solverWords, 'RackSolver doit trouver exactement les memes mots que la verification par force brute');
    Assert::true(in_array('CARTAS', $solverWords, true), 'le mot CARTAS lui-meme doit apparaitre (anagramme exacte du chevalet complet)');

    // Chaque correspondance est necessairement admise -- jamais une forme espagnole non
    // admise (modele a trois statuts ferme, CLAUDE.md : "quel mot puis-je jouer" ne
    // repond qu'avec des mots jouables).
    foreach ($page->matches as $match) {
        Assert::true($match['isOds8'] || $match['isOds9'], $match['normalized'] . ' devrait etre admis FILE 2017 ou FISE-2');
    }

    Assert::true($page->queryCount <= 10, 'budget de requetes indexees depasse pour ce chevalet');

    // --- Chevalet AVEC tuile digramme (COCHE = 4 tuiles C, O, CH, E, PAS 5 lettres) : ---
    // --- verifie que RackSolver traite bien CH comme une tuile unique cote signature, ---
    // --- pas comme C et H separes -- correction croisee par force brute tuile-aware. ---
    $digraphPage = $solver->solve('coche');
    Assert::notNull($digraphPage);
    Assert::true(!$digraphPage->capped);
    Assert::same('c-ch-e-o', $digraphPage->slug);

    $digraphBruteForce = $bruteForceMatches(['C' => 1, 'O' => 1, 'CH' => 1, 'E' => 1], 6);
    $digraphSolverWords = array_column($digraphPage->matches, 'normalized');
    sort($digraphSolverWords);

    Assert::same($digraphBruteForce, $digraphSolverWords, 'RackSolver doit rester correct tuile par tuile pour un chevalet avec digramme');
    Assert::true(in_array('COCHE', $digraphSolverWords, true), 'COCHE lui-meme doit apparaitre');
    Assert::true(in_array('CHECO', $digraphSolverWords, true), 'CHECO (anagramme de COCHE au sens des tuiles) doit apparaitre');

    // Garde-fou negatif : un chevalet avec DEUX tuiles C et H SEPAREES (jamais fusionnees
    // en CH) ne doit PAS pouvoir jouer COCHE ou CHECO -- le rack "caerho" n'a pas de "ch"
    // adjacent (c-a-e-r-h-o), donc reste 6 tuiles simples, jamais une tuile CH.
    $noDigraph = $solver->solve('caerho');
    Assert::notNull($noDigraph);
    Assert::same(['A' => 1, 'C' => 1, 'E' => 1, 'H' => 1, 'O' => 1, 'R' => 1], $noDigraph->letterCounts);
    Assert::true(
        !in_array('COCHE', array_column($noDigraph->matches, 'normalized'), true),
        'un chevalet a C et H separes ne doit jamais permettre de jouer un mot qui exige la tuile CH dediee'
    );

    // --- 1 joker : CARTAS doit rester atteignable (C,A,R,T + 1 joker valant A ou S, ---
    // --- selon la combinaison -- ici on retire le S et verifie que le joker le remplace). ---
    $withJoker = $solver->solve('carta?');
    Assert::notNull($withJoker);
    Assert::true(!$withJoker->capped);
    Assert::same(1, $withJoker->jokerCount);
    Assert::true(
        in_array('CARTAS', array_column($withJoker->matches, 'normalized'), true),
        'CARTAS doit etre atteignable avec C,A,R,T,A + 1 joker'
    );

    // --- Redirection canonique : '?' et '*' doivent produire le meme slug. ---
    $withStar = $solver->solve('carta*');
    Assert::notNull($withStar);
    Assert::same($withJoker->slug, $withStar->slug, "? et * doivent produire le meme chevalet, donc le meme slug canonique ('*')");

    // --- Cas large : 6 tuiles distinctes + 2 jokers, aucune contrainte. Doit rester sous ---
    // --- le plafond de securite et repondre dans un temps raisonnable, toujours via ---
    // --- l'index signature. Alphabet de remplissage elargi a 30 tuiles (26 lettres + Ñ ---
    // --- + CH/LL/RR, pas 26 comme le site francais) : le pire cas nomme a l'origine pour ---
    // --- le site francais (7 lettres + 2 jokers, 36 933 signatures avec 26 lettres) ---
    // --- depasse desormais SIGNATURE_CEILING avec 30 lettres (63 488, mesure) -- reduit ---
    // --- a 6 lettres + 2 jokers ici (31 744, sous le plafond) le temps qu'une decision ---
    // --- produit explicite tranche entre relever SIGNATURE_CEILING ou accepter un ---
    // --- plafond plus bas pour l'espagnol -- non tranche dans cette passe, voir le ---
    // --- rapport AFTER pour le detail.
    $worstNamed = Rack::fromInput('abcdef**');
    Assert::notNull($worstNamed);
    $upperBound = RackSolver::upperBoundSignatureCount($worstNamed);
    Assert::true($upperBound <= RackSolver::SIGNATURE_CEILING, 'ce cas large doit rester sous le plafond de securite');

    $start = hrtime(true);
    $worstPage = $solver->solve('abcdef**');
    $elapsedMs = (hrtime(true) - $start) / 1e6;

    Assert::notNull($worstPage);
    Assert::true(!$worstPage->capped, 'ce cas large (6 tuiles + 2 jokers) ne doit pas declencher le plafond');
    Assert::same(2, $worstPage->jokerCount);
    Assert::true($worstPage->queryCount <= 10, 'ce cas large doit rester sous 10 requetes avec CHUNK_SIZE = 5000, obtenu : ' . $worstPage->queryCount);
    Assert::true($worstPage->candidateSignatureCount > 10000, 'sanity check : ce cas large doit bien engendrer des dizaines de milliers de signatures candidates');
    Assert::true(count($worstPage->matches) === $worstPage->displayLimit, 'ce cas large doit produire plus de resultats que la limite d\'affichage');
    Assert::true($worstPage->truncated, 'ce cas large doit etre marque tronque');
    Assert::true($elapsedMs < 1000.0, 'ce cas large doit repondre en moins d\'une seconde, obtenu : ' . $elapsedMs . ' ms');

    // --- Plafond de securite : un chevalet de 13 lettres distinctes + 2 jokers (15 ---
    // --- tuiles, la borne) doit etre refuse AVANT toute generation ou requete -- pas ---
    // --- une erreur, un resultat distinct. ---
    $tooLarge = Rack::fromInput('abcdefghijklm**');
    Assert::notNull($tooLarge, 'chevalet syntaxiquement valide : 13 tuiles + 2 jokers = 15 cases, exactement la borne');
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
