<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Solveur /jouer/{lettres} (Phase 2, docs/08) : quels mots admis au Scrabble peut-on
 * former avec un chevalet, jokers compris.
 *
 * Strategie retenue apres mesure, validee explicitement par le coordinateur avant
 * implementation (reports/query-plans/phase2.md pour le detail complet) :
 *
 * 1. Un chevalet engendre l'ensemble de ses sous-multiensembles de lettres connues (0 a
 *    n lettres), croise avec 0, 1 ou 2 "remplissages" de jokers (chaque joker vaut
 *    n'importe quelle lettre A-Z). Chaque combinaison produit une signature candidate
 *    (lettres triees) -- signature est deja indexee (idx_terms_signature, D-012).
 * 2. Avant de generer quoi que ce soit, une borne SUPERIEURE bon marche (formule
 *    combinatoire fermee, aucun appel base, aucune allocation) determine si le nombre
 *    de signatures candidates peut depasser SIGNATURE_CEILING. Au-dela, aucune
 *    generation ni requete n'est executee : RackPage::$capped = true, un resultat
 *    distinct plutot qu'un calcul complet ou un blocage de worker PHP partage.
 * 3. En-deca du plafond, les signatures deduites (dedupliquees) sont regroupees en lots
 *    de CHUNK_SIZE et interrogees via `signature IN (...)`, chacune servie par
 *    idx_terms_signature (EXPLAIN QUERY PLAN : SEARCH ... USING INDEX, jamais de SCAN,
 *    voir reports/query-plans/phase2.md). Pire cas explicitement nomme par la tache
 *    (7 lettres + 2 jokers, aucune contrainte) : 36 933 signatures candidates, 8
 *    requetes a CHUNK_SIZE = 5000, ~115 ms mesures -- sous le plafond de 50 000.
 * 4. Seuls les mots admis (is_ods8 = 1 OU is_ods9 = 1) sont retournes : voir RackPage.
 * 5. Tri score decroissant puis longueur decroissante puis alphabetique en PHP (les
 *    volumes mesures -- au plus quelques milliers de lignes pour le pire cas -- restent
 *    triviaux en memoire), puis LIMIT DISPLAY_LIMIT applique apres tri, jamais avant
 *    (le tri porte sur l'ensemble des correspondances, pas sur un sous-ensemble
 *    arbitraire de lots).
 */
final class RackSolver
{
    /**
     * Sous le defaut SQLITE_LIMIT_VARIABLE_NUMBER (32766 sur SQLite >= 3.32, confirme
     * en environnement local : 3.53.2) avec marge confortable. Mesure : un IN() de
     * 36 933 parametres echoue explicitement ("too many SQL variables"), un IN() de
     * CHUNK_SIZE = 5000 reussit toujours et le temps total est domine par le nombre de
     * recherches d'index effectuees, pas par le nombre de requetes HTTP-vers-SQLite
     * (reports/query-plans/phase2.md : 74 requetes a 500 ou 8 requetes a 5000 prennent
     * le meme temps total, ~110-120 ms, pour le meme pire cas).
     */
    public const CHUNK_SIZE = 5000;

    /**
     * Valide explicitement par le coordinateur : confortablement au-dessus du pire cas
     * nomme par la tache (36 933 signatures pour 7 lettres + 2 jokers), tres en dessous
     * des millions atteints par un chevalet de 13 lettres + 2 jokers (D-010 autorise
     * jusqu'a 15 caracteres en entree, jokers compris). Comparee a une borne SUPERIEURE
     * bon marche (avant deduplication, voir upperBoundSignatureCount()), jamais au
     * compte reel -- un chevalet refuse ne declenche donc jamais la generation qu'il
     * est cense eviter.
     */
    public const SIGNATURE_CEILING = 50_000;

    /** Limite d'affichage validee par le coordinateur. */
    public const DISPLAY_LIMIT = 300;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * Normalise l'entree brute et resout le chevalet correspondant.
     *
     * Renvoie null si l'entree n'est pas un chevalet exploitable (voir
     * Rack::fromInput()) : c'est une erreur de saisie, pas un resultat de recherche, et
     * elle n'engendre aucune requete SQLite. Au routeur de traduire ce null en reponse
     * 404, meme convention que TermLookup::find().
     */
    public function solve(string $rawInput): ?RackPage
    {
        $rack = Rack::fromInput($rawInput);

        if ($rack === null) {
            return null;
        }

        if (self::upperBoundSignatureCount($rack) > self::SIGNATURE_CEILING) {
            return new RackPage(
                slug: $rack->slug,
                letterCounts: $rack->letterCounts,
                jokerCount: $rack->jokerCount,
                capped: true,
                matches: [],
                totalMatches: null,
                truncated: false,
                displayLimit: self::DISPLAY_LIMIT,
                candidateSignatureCount: 0,
                queryCount: 0,
            );
        }

        $signatures = self::candidateSignatures($rack);
        [$rows, $queryCount] = $this->fetchMatches($signatures);

        usort($rows, static function (array $a, array $b): int {
            $cmp = (int) $b['score'] <=> (int) $a['score'];

            if ($cmp === 0) {
                $cmp = (int) $b['length'] <=> (int) $a['length'];
            }

            if ($cmp === 0) {
                $cmp = $a['normalized'] <=> $b['normalized'];
            }

            return $cmp;
        });

        $total = count($rows);
        $limited = array_slice($rows, 0, self::DISPLAY_LIMIT);

        $matches = array_map(static fn (array $row): array => [
            'normalized' => $row['normalized'],
            'slug' => strtolower($row['normalized']),
            'score' => (int) $row['score'],
            'length' => (int) $row['length'],
            'isOds8' => (int) $row['is_ods8'] === 1,
            'isOds9' => (int) $row['is_ods9'] === 1,
        ], $limited);

        return new RackPage(
            slug: $rack->slug,
            letterCounts: $rack->letterCounts,
            jokerCount: $rack->jokerCount,
            capped: false,
            matches: $matches,
            totalMatches: $total,
            truncated: $total > self::DISPLAY_LIMIT,
            displayLimit: self::DISPLAY_LIMIT,
            candidateSignatureCount: count($signatures),
            queryCount: $queryCount,
        );
    }

    /**
     * Borne superieure bon marche (aucun appel base, formule fermee, O(26)) : nombre de
     * sous-multiensembles connus (produit des multiplicites + 1, incluant le
     * sous-ensemble vide) multiplie par la somme, pour 0 a $rack->jokerCount jokers, des
     * remplissages possibles (combinaisons AVEC repetition parmi 26 lettres). Toujours
     * superieure ou egale au compte reel deduplique -- jamais l'inverse -- puisqu'elle
     * ignore les collisions entre combinaisons distinctes qui produisent la meme
     * signature triee.
     */
    public static function upperBoundSignatureCount(Rack $rack): int
    {
        $subsetCount = 1;

        foreach ($rack->letterCounts as $count) {
            $subsetCount *= ($count + 1);
        }

        $jokerFillingsSum = 0;

        for ($j = 0; $j <= $rack->jokerCount; $j++) {
            $jokerFillingsSum += self::multisetCount(26, $j);
        }

        return $subsetCount * $jokerFillingsSum;
    }

    /** Nombre de multiensembles de taille $k choisis parmi $n types : C(n + k - 1, k). */
    private static function multisetCount(int $n, int $k): int
    {
        if ($k === 0) {
            return 1;
        }

        return self::binomial($n + $k - 1, $k);
    }

    private static function binomial(int $n, int $k): int
    {
        if ($k < 0 || $k > $n) {
            return 0;
        }

        $k = min($k, $n - $k);
        $result = 1;

        for ($i = 0; $i < $k; $i++) {
            $result = intdiv($result * ($n - $i), $i + 1);
        }

        return $result;
    }

    /**
     * Genere puis deduplique les signatures candidates. N'est appelee que lorsque
     * upperBoundSignatureCount() est deja sous SIGNATURE_CEILING : la generation reelle
     * est donc elle-meme bornee par construction.
     *
     * @return list<string> signatures candidates, dedupliquees
     */
    private static function candidateSignatures(Rack $rack): array
    {
        $knownSubsets = self::knownLetterSubsets($rack->letterCounts);
        $jokerFillings = self::jokerFillingsUpTo($rack->jokerCount);

        $signatures = [];

        foreach ($knownSubsets as $subset) {
            $subsetLength = strlen($subset);

            for ($j = 0; $j <= $rack->jokerCount; $j++) {
                $length = $subsetLength + $j;

                if ($length < Normalizer::MIN_LENGTH || $length > Normalizer::MAX_LENGTH) {
                    continue;
                }

                foreach ($jokerFillings[$j] as $filling) {
                    $signatures[self::mergeSorted($subset, $filling)] = true;
                }
            }
        }

        return array_keys($signatures);
    }

    /**
     * Tous les sous-multiensembles connus, deja tries alphabetiquement : les lettres
     * sont traitees dans l'ordre croissant (Rack::fromInput trie $letterCounts par cle)
     * et chaque copie choisie est ajoutee en fin de chaine, donc jamais avant une lettre
     * deja placee -- le resultat est trie sans appel supplementaire a sort().
     *
     * @param array<string, int> $letterCounts deja triees par cle
     * @return list<string>
     */
    private static function knownLetterSubsets(array $letterCounts): array
    {
        $subsets = [''];

        foreach ($letterCounts as $letter => $count) {
            $next = [];

            foreach ($subsets as $prefix) {
                for ($k = 0; $k <= $count; $k++) {
                    $next[] = $prefix . str_repeat($letter, $k);
                }
            }

            $subsets = $next;
        }

        return $subsets;
    }

    /**
     * @return array<int, list<string>> remplissages tries alphabetiquement pour 0, 1 et
     *         (si demande) 2 jokers -- respectivement 1, 26 puis 351 chaines
     */
    private static function jokerFillingsUpTo(int $maxJokers): array
    {
        $result = [0 => ['']];

        if ($maxJokers < 1) {
            return $result;
        }

        $alphabet = range('A', 'Z');
        $result[1] = $alphabet;

        if ($maxJokers < 2) {
            return $result;
        }

        $pairs = [];

        foreach ($alphabet as $i => $a) {
            foreach ($alphabet as $j => $b) {
                if ($j < $i) {
                    continue;
                }

                $pairs[] = $a . $b;
            }
        }

        $result[2] = $pairs;

        return $result;
    }

    /**
     * Fusion de deux chaines deja triees en une seule chaine triee. $a et $b font
     * chacune au plus Normalizer::MAX_LENGTH caracteres : un tri PHP standard sur une
     * chaine aussi courte est trivial, pas la peine d'ecrire une fusion lineaire
     * manuelle pour ce volume (mesure : cout negligeable devant les requetes SQLite,
     * voir reports/query-plans/phase2.md).
     */
    private static function mergeSorted(string $a, string $b): string
    {
        if ($b === '') {
            return $a;
        }

        $chars = str_split($a . $b);
        sort($chars, SORT_STRING);

        return implode('', $chars);
    }

    /**
     * Requetes chunkees `signature IN (...)`, un statement prepare reutilise par taille
     * de lot rencontree (au plus deux tailles distinctes en pratique : CHUNK_SIZE pour
     * les lots pleins, le reste pour le dernier lot).
     *
     * @param list<string> $signatures
     * @return array{0: list<array{normalized: string, score: string|int, length: string|int, is_ods8: string|int, is_ods9: string|int}>, 1: int}
     */
    private function fetchMatches(array $signatures): array
    {
        if ($signatures === []) {
            return [[], 0];
        }

        $pdo = $this->connection->pdo();
        $statementCache = [];
        $rows = [];
        $queryCount = 0;

        foreach (array_chunk($signatures, self::CHUNK_SIZE) as $chunk) {
            $count = count($chunk);

            if (!isset($statementCache[$count])) {
                $placeholders = implode(',', array_fill(0, $count, '?'));
                $statementCache[$count] = $pdo->prepare(
                    'SELECT normalized, score, length, is_ods8, is_ods9 FROM terms '
                    . "WHERE signature IN ($placeholders) AND (is_ods8 = 1 OR is_ods9 = 1)"
                );
            }

            $statement = $statementCache[$count];
            $statement->execute($chunk);

            foreach ($statement->fetchAll() as $row) {
                $rows[] = $row;
            }

            $queryCount++;
        }

        return [$rows, $queryCount];
    }
}
