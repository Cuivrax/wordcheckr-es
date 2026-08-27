<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Relations de la fiche mot /mot/{mot} (Phase 4, docs/08) -- dix categories definies par
 * docs/01_MASTER_BRIEF.md et precisees par docs/08, calculees UNIQUEMENT pour un mot ADMIS
 * (le routeur n'appelle find() que si TermPage::$status === TermPage::STATUS_ADMITTED ;
 * cette classe elle-meme ne verifie pas le statut, elle fait confiance a l'appelant --
 * meme division des responsabilites que RackSolver/WordListSolver, qui ne verifient pas non
 * plus l'origine de leurs entrees deja normalisees).
 *
 * ## Budget -- chiffre avant implementation (reports/query-plans/phase4.md pour le detail)
 *
 * La fiche complete (TermLookup::find() + RelationsFinder::find()) tient a 8 requetes
 * SQLite indexees, sous le plafond de moins de 10 (CLAUDE.md) :
 *
 *   TermLookup::find()   3 requetes (lookup, precedent, suivant -- Phase 1, inchange)
 *   RelationsFinder      5 requetes pour un mot admis, 0 pour un mot non admis/inconnu
 *
 * Sans regroupement, les dix categories auraient coute 9 a 10 requetes a elles seules (une
 * par categorie), ce qui aurait fait depasser le budget total des 2026-08-03. Deux
 * regroupements ramenent ce cout a 5 requetes :
 *
 *   requete A (candidats explicites, categories 2+3+4+5)
 *     changer une lettre, retirer une lettre, inserer une lettre, sous-mots generent chacune
 *     un petit ensemble de candidats explicites (au plus quelques centaines meme pour un mot
 *     de 15 lettres -- voir candidateCounts() pour le calcul exact). Les quatre ensembles sont
 *     fusionnes en un seul `normalized IN (...)` -- la meme ligne peut legitimement appartenir
 *     a plusieurs categories a la fois (ex. OSER est a la fois "retirer une lettre" et
 *     "sous-mot" de POSER, exactement comme le prototype de reference les fait apparaitre
 *     dans les deux listes), donc l'appartenance est verifiee independamment par categorie
 *     apres recuperation, jamais supposee exclusive.
 *
 *   requete B (signatures, categories 1+9+10)
 *     anagrammes exactes (signature du mot lui-meme), anagrammes +1 lettre (26 signatures,
 *     une par lettre ajoutee) et anagrammes -1 lettre (au plus 15 signatures, une par lettre
 *     DISTINCTE retiree) sont trois longueurs differentes (N, N+1, N-1) : leurs chaines de
 *     signature ne peuvent jamais entrer en collision entre elles (la longueur de la chaine
 *     de signature egale la longueur du mot). Un seul `signature IN (...)` suffit, la
 *     categorie de chaque ligne renvoyee se deduit de la longueur associee a sa signature
 *     dans les tables de correspondance construites cote PHP, sans requete supplementaire.
 *
 *   requetes C/D/E (categories 6, 7, 8 -- une requete chacune, techniques distinctes)
 *     6 (rallonges a droite) et 7 (rallonges a gauche) reutilisent le patron deja etabli par
 *     WordListSolver (Phase 3) : plage indexee sur normalized (prefixe) ou reversed
 *     (suffixe), plus le predicat "length > N" et le filtre admis. Bornees a
 *     EXTENSION_ROW_CEILING lignes, comme RackSolver/WordListSolver le font deja pour leurs
 *     propres plafonds de securite -- jamais un calcul complet.
 *
 *     8 (contient le mot, ni prefixe ni suffixe) n'a NI l'un ni l'autre d'index dedie
 *     (D-012 a deja ecarte une table de postings pour cette meme raison en Phase 3) --
 *     MAIS, contrairement au "contenant" sans aucune autre contrainte de WordListSolver (qui
 *     n'a aucun ancrage disponible et degenere en un parcours des lignes alphabetiquement les
 *     plus petites de toute la table), cette categorie dispose TOUJOURS d'un ancrage reel :
 *     "length > N" est connu par construction. La requete s'appuie sur
 *     idx_terms_length_normalized (`EXPLAIN QUERY PLAN` : SEARCH, jamais SCAN TABLE) et
 *     n'examine que les lignes de longueur strictement superieure a N.
 *
 *     Cout mesure et arbitrage assume (reports/query-plans/phase4.md pour le detail complet) :
 *     un plafond d'EXAMEN des lignes (plutot qu'un plafond de CORRESPONDANCES trouvees) a ete
 *     essaye puis ECARTE apres mesure -- pour POSER, les 350 correspondances reelles sont
 *     TOUTES de longueur >= 8 (aucune en longueur 6 ou 7), alors que idx_terms_length_normalized
 *     ordonne precisement par longueur croissante d'abord : plafonner les lignes EXAMINEES
 *     aurait donc pu epuiser tout le budget sur les paliers 6/7 et ne renvoyer STRICTEMENT
 *     AUCUNE des 350 correspondances reelles pour un mot tres courant -- un faux negatif
 *     silencieux, pire qu'une lenteur mesuree. La requete retenue examine donc, dans le pire
 *     cas (peu ou pas de correspondances reelles), la totalite des lignes admises de longueur
 *     superieure a N -- mesure : jusqu'a 68 ms pour "EH" (le pire des mots de 2 lettres admis,
 *     403 000 lignes de longueur > 2 dans le panier ancre), 57-70 ms pour POSER/CHAT. Reste
 *     sous le budget TTFB p95 < 250 ms (CLAUDE.md) avec une marge confortable, meme dans ce
 *     pire cas mesure -- compromis delibere : exactitude (toute correspondance reelle est
 *     trouvee, jusqu'a EXTENSION_ROW_CEILING) plutot que vitesse minimale pour cette seule
 *     requete, cout absorbe par un budget de requetes qui reste a 8 au total (tres en-dessous
 *     de 10) et par une base qui ne sert qu'une fiche a la fois par requete HTTP.
 *
 *     $containingWordsTruncated ne se declenche que lorsque le nombre de CORRESPONDANCES
 *     reelles (pas de lignes examinees) atteint EXTENSION_ROW_CEILING -- rare en pratique
 *     (mesure : seuls des mots tres courts et tres frequents comme "AS" l'atteignent), jamais
 *     presente comme exhaustif au-dela -- meme convention que WordListPage::$truncated.
 *
 * Aucune de ces cinq requetes n'est un SCAN TABLE (voir reports/query-plans/phase4.md pour
 * EXPLAIN QUERY PLAN et chronometrage complet, y compris le pire cas nomme par la tache : POSER
 * lui-meme, et le pire cas mesure independamment pour la requete E : "EH").
 */
final class RelationsFinder
{
    private const ALPHABET = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    /** Nombre maximum de liens affiches par categorie -- 10 categories x 16 = 160 liens au
     * plus, conforme au plafond "environ 160 liens de mots" (docs/01, docs/08). Les listes
     * naturellement plus courtes (retirer une lettre, sous-mots -- bornees par N) n'atteignent
     * de toute facon jamais ce plafond pour la plupart des mots. */
    public const DISPLAY_LIMIT_PER_CATEGORY = 16;

    /**
     * Plafond de CORRESPONDANCES trouvees pour les categories 6/7/8 (rallonges, mot contenu)
     * -- beaucoup plus bas que WordListSolver::ROW_EXAMINATION_CEILING (10 000) : ici la
     * requete n'alimente qu'un affichage de DISPLAY_LIMIT_PER_CATEGORY liens plus un total,
     * jamais une pagination complete, donc un plafond plus bas suffit et reduit d'autant la
     * memoire par worker PHP (CLAUDE.md : "compte le cout memoire par worker, il se
     * multiplie") -- jamais plus de EXTENSION_ROW_CEILING + 1 lignes chargees en memoire PHP
     * a la fois, pour n'importe laquelle des trois categories.
     *
     * Pour les categories 6/7 (rallonges, indexees sur normalized/reversed), ce plafond borne
     * AUSSI le temps d'execution : l'index permet d'arreter la lecture des qu'assez de lignes
     * ont ete trouvees. Pour la categorie 8 (containingWords(), non indexee), ce n'est PAS le
     * cas -- voir le commentaire de containingWords() pour la mesure complete et l'arbitrage
     * assume (jusqu'a 68 ms dans le pire cas mesure, "EH", toujours sous le budget TTFB p95 de
     * 250 ms).
     */
    public const EXTENSION_ROW_CEILING = 1_000;

    /** Nombre maximum de recherches liees (docs/01, docs/08). */
    public const MAX_RELATED_SEARCHES = 12;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * Calcule les relations d'un mot ADMIS deja normalise (A-Z, 2 a 15 lettres). L'appelant
     * (routeur) garantit ce prealable en n'invoquant find() que pour TermPage::STATUS_ADMITTED
     * -- meme convention que RackSolver/WordListSolver, qui font confiance a une entree deja
     * validee par la couche appelante plutot que de revalider.
     */
    public function find(string $normalized): TermRelations
    {
        $length = strlen($normalized);
        $queryCount = 0;

        [$explicit, $explicitQueries] = $this->explicitCandidateRelations($normalized);
        $queryCount += $explicitQueries;

        [$signatureBased, $signatureQueries] = $this->signatureBasedRelations($normalized, $length);
        $queryCount += $signatureQueries;

        [$rightExtensions, $rightTotal, $rightTruncated] = $this->rightExtensions($normalized, $length);
        $queryCount++;

        [$leftExtensions, $leftTotal, $leftTruncated] = $this->leftExtensions($normalized, $length);
        $queryCount++;

        [$containingWords, $containingTotal, $containingTruncated] = $this->containingWords($normalized, $length);
        $queryCount++;

        return new TermRelations(
            anagrams: $signatureBased['anagrams'],
            changeOneLetter: $explicit['changeOneLetter'],
            removeOneLetter: $explicit['removeOneLetter'],
            insertOneLetter: $explicit['insertOneLetter'],
            substrings: $explicit['substrings'],
            rightExtensions: $rightExtensions,
            rightExtensionsTotal: $rightTotal,
            rightExtensionsTruncated: $rightTruncated,
            leftExtensions: $leftExtensions,
            leftExtensionsTotal: $leftTotal,
            leftExtensionsTruncated: $leftTruncated,
            containingWords: $containingWords,
            containingWordsTotal: $containingTotal,
            containingWordsTruncated: $containingTruncated,
            anagramsPlusOne: $signatureBased['anagramsPlusOne'],
            anagramsMinusOne: $signatureBased['anagramsMinusOne'],
            relatedSearches: self::relatedSearches($normalized, $length),
            queryCount: $queryCount,
        );
    }

    // ------------------------------------------------------------------
    // Requete A -- categories 2 (changer une lettre), 3 (retirer une lettre),
    // 4 (inserer une lettre), 5 (sous-mots), combinees en un seul `normalized IN (...)`.
    // ------------------------------------------------------------------

    /**
     * @return array{0: array{changeOneLetter: list<array<string, mixed>>, removeOneLetter: list<array<string, mixed>>, insertOneLetter: list<array<string, mixed>>, substrings: list<array<string, mixed>>}, 1: int}
     */
    private function explicitCandidateRelations(string $word): array
    {
        $changeMap = self::changeOneLetterCandidates($word);
        $removeSet = self::removeOneLetterCandidates($word);
        $insertSet = self::insertOneLetterCandidates($word);
        $substrSet = self::substringCandidates($word);

        // array_values() : array_unique() preserve les cles d'origine (des trous
        // apparaissent des qu'un doublon est retire) -- PDOStatement::execute() avec des
        // marqueurs positionnels "?" exige un tableau reindexe sequentiellement, sinon
        // SQLite leve "column index out of range" (verifie en developpement).
        $allKeys = array_values(array_unique(array_merge(
            array_keys($changeMap),
            array_keys($removeSet),
            array_keys($insertSet),
            array_keys($substrSet),
        )));

        $result = [
            'changeOneLetter' => [],
            'removeOneLetter' => [],
            'insertOneLetter' => [],
            'substrings' => [],
        ];

        if ($allKeys === []) {
            return [$result, 0];
        }

        $rows = $this->fetchByNormalizedIn($allKeys);

        foreach ($rows as $row) {
            $candidate = $row['normalized'];
            $item = self::toItem($row);

            if (isset($changeMap[$candidate])) {
                $result['changeOneLetter'][] = $item + $changeMap[$candidate];
            }

            if (isset($removeSet[$candidate])) {
                $result['removeOneLetter'][] = $item;
            }

            if (isset($insertSet[$candidate])) {
                $result['insertOneLetter'][] = $item;
            }

            if (isset($substrSet[$candidate])) {
                $result['substrings'][] = $item;
            }
        }

        foreach (array_keys($result) as $category) {
            $result[$category] = self::sortAndLimit($result[$category]);
        }

        return [$result, 1];
    }

    /**
     * Toutes les positions x 25 lettres alternatives (la lettre d'origine est exclue --
     * "exactement une position differente" implique une lettre reellement differente a cette
     * position). Au plus MAX_LENGTH x 25 = 375 candidats.
     *
     * @return array<string, array{position: int, newLetter: string}> candidat => metadonnee
     *         (position 1-based, nouvelle lettre) ; la premiere paire (position, lettre)
     *         rencontree pour un candidat donne est conservee -- une collision entre deux
     *         paires distinctes produisant la meme chaine candidate est possible en theorie
     *         sur un mot a lettres tres repetees, mais n'affecte que l'annotation informative
     *         (quelle position/lettre est montree), jamais l'appartenance a la categorie.
     */
    private static function changeOneLetterCandidates(string $word): array
    {
        $length = strlen($word);
        $candidates = [];

        for ($i = 0; $i < $length; $i++) {
            $original = $word[$i];

            foreach (self::ALPHABET as $letter) {
                if ($letter === $original) {
                    continue;
                }

                $candidate = substr($word, 0, $i) . $letter . substr($word, $i + 1);

                if (!isset($candidates[$candidate])) {
                    $candidates[$candidate] = ['position' => $i + 1, 'newLetter' => $letter];
                }
            }
        }

        return $candidates;
    }

    /**
     * Suppression d'exactement une lettre a une position quelconque, ordre des lettres
     * restantes preserve (sous-sequence, pas un anagramme). Au plus MAX_LENGTH candidats,
     * dedupliques (un mot a lettres repetees peut produire deux fois la meme sous-sequence).
     *
     * @return array<string, true>
     */
    private static function removeOneLetterCandidates(string $word): array
    {
        $length = strlen($word);
        $candidates = [];

        for ($i = 0; $i < $length; $i++) {
            $candidates[substr($word, 0, $i) . substr($word, $i + 1)] = true;
        }

        return $candidates;
    }

    /**
     * Le mot est obtenu en retirant une lettre du candidat -- donc le candidat = le mot avec
     * une lettre inseree a une position quelconque parmi (longueur + 1), pour chacune des 26
     * lettres. Vide si le mot est deja a MAX_LENGTH (D-010) : aucun candidat de longueur + 1
     * ne peut jamais exister en base, inutile de le generer.
     *
     * @return array<string, true>
     */
    private static function insertOneLetterCandidates(string $word): array
    {
        if (strlen($word) >= Normalizer::MAX_LENGTH) {
            return [];
        }

        $length = strlen($word);
        $candidates = [];

        for ($i = 0; $i <= $length; $i++) {
            foreach (self::ALPHABET as $letter) {
                $candidates[substr($word, 0, $i) . $letter . substr($word, $i)] = true;
            }
        }

        return $candidates;
    }

    /**
     * Toute sous-chaine CONTIGUE de longueur 2 a N-1. Au plus environ N(N-1)/2 candidats
     * (104 pour un mot de 15 lettres), dedupliques.
     *
     * @return array<string, true>
     */
    private static function substringCandidates(string $word): array
    {
        $length = strlen($word);
        $candidates = [];

        for ($start = 0; $start < $length; $start++) {
            for ($len = 2; $len <= $length - 1; $len++) {
                if ($start + $len > $length) {
                    break;
                }

                $candidates[substr($word, $start, $len)] = true;
            }
        }

        return $candidates;
    }

    /**
     * @param list<string> $candidates
     * @return list<array{normalized: string, length: string|int, score: string|int, is_ods8: string|int, is_ods9: string|int}>
     */
    private function fetchByNormalizedIn(array $candidates): array
    {
        $placeholders = implode(',', array_fill(0, count($candidates), '?'));
        $statement = $this->connection->pdo()->prepare(
            "SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE normalized IN ($placeholders) "
            . 'AND (is_ods8 = 1 OR is_ods9 = 1)'
        );
        $statement->execute($candidates);

        return $statement->fetchAll();
    }

    // ------------------------------------------------------------------
    // Requete B -- categories 1 (anagrammes exactes), 9 (+1 lettre), 10 (-1 lettre),
    // combinees en un seul `signature IN (...)`.
    // ------------------------------------------------------------------

    /**
     * @return array{0: array{anagrams: list<array<string, mixed>>, anagramsPlusOne: list<array<string, mixed>>, anagramsMinusOne: list<array<string, mixed>>}, 1: int}
     */
    private function signatureBasedRelations(string $word, int $length): array
    {
        $exactSignature = Normalizer::signature($word);
        $plusMap = self::plusOneSignatures($word);
        $minusMap = self::minusOneSignatures($word);

        // array_values() : meme raison que explicitCandidateRelations() ci-dessus.
        $signatures = array_values(array_unique(array_merge([$exactSignature], array_keys($plusMap), array_keys($minusMap))));

        $result = ['anagrams' => [], 'anagramsPlusOne' => [], 'anagramsMinusOne' => []];

        if ($signatures === []) {
            return [$result, 0];
        }

        $rows = $this->fetchBySignatureIn($signatures);

        foreach ($rows as $row) {
            $signature = $row['signature'];
            $item = self::toItem($row);

            if ($signature === $exactSignature) {
                // Longueur N par construction (signature de longueur N ne peut correspondre
                // qu'a des lignes de longueur N -- length est redondant ici, non revérifié).
                if ($row['normalized'] !== $word) {
                    $result['anagrams'][] = $item;
                }

                continue;
            }

            if (isset($plusMap[$signature])) {
                $result['anagramsPlusOne'][] = $item + ['addedLetter' => $plusMap[$signature]];

                continue;
            }

            if (isset($minusMap[$signature])) {
                $result['anagramsMinusOne'][] = $item + ['removedLetter' => $minusMap[$signature]];
            }
        }

        foreach (array_keys($result) as $category) {
            $result[$category] = self::sortAndLimit($result[$category]);
        }

        return [$result, 1];
    }

    /**
     * Une signature par lettre ajoutee (26). Vide si le mot est deja a MAX_LENGTH (D-010) --
     * aucune ligne de longueur + 1 ne peut jamais exister.
     *
     * @return array<string, string> signature => lettre ajoutee
     */
    private static function plusOneSignatures(string $word): array
    {
        if (strlen($word) >= Normalizer::MAX_LENGTH) {
            return [];
        }

        $base = Normalizer::signature($word);
        $map = [];

        foreach (self::ALPHABET as $letter) {
            $map[self::mergeSorted($base, $letter)] = $letter;
        }

        return $map;
    }

    /**
     * Une signature par lettre DISTINCTE presente dans le mot (au plus 15, souvent moins).
     *
     * @return array<string, string> signature => lettre retiree
     */
    private static function minusOneSignatures(string $word): array
    {
        $base = Normalizer::signature($word);
        $distinctLetters = array_unique(str_split($word));
        $map = [];

        foreach ($distinctLetters as $letter) {
            $position = strpos($base, $letter);

            if ($position === false) {
                // Ne devrait jamais arriver : $base contient toutes les lettres de $word par
                // construction (Normalizer::signature() est un tri, pas un filtre).
                continue;
            }

            $map[substr($base, 0, $position) . substr($base, $position + 1)] = $letter;
        }

        return $map;
    }

    /** Fusion de deux chaines triees en une seule chaine triee -- meme technique que
     * RackSolver::mergeSorted(), dupliquee ici plutot que partagee (les deux classes
     * restent independantes, meme convention que le reste du code de app/Search/). */
    private static function mergeSorted(string $a, string $b): string
    {
        $chars = str_split($a . $b);
        sort($chars, SORT_STRING);

        return implode('', $chars);
    }

    /**
     * @param list<string> $signatures
     * @return list<array{normalized: string, length: string|int, signature: string, score: string|int, is_ods8: string|int, is_ods9: string|int}>
     */
    private function fetchBySignatureIn(array $signatures): array
    {
        $placeholders = implode(',', array_fill(0, count($signatures), '?'));
        $statement = $this->connection->pdo()->prepare(
            "SELECT normalized, length, signature, score, is_ods8, is_ods9 FROM terms WHERE signature IN ($placeholders) "
            . 'AND (is_ods8 = 1 OR is_ods9 = 1)'
        );
        $statement->execute($signatures);

        return $statement->fetchAll();
    }

    // ------------------------------------------------------------------
    // Requetes C/D/E -- categories 6 (rallonges a droite), 7 (rallonges a gauche),
    // 8 (mot contenu, ni prefixe ni suffixe).
    // ------------------------------------------------------------------

    /**
     * Categorie 6 : admis, prefixe = le mot, plus long. Ancrage sur normalized (meme
     * technique que WordListSolver pour "commencant"), plus le predicat length > N.
     *
     * @return array{0: list<array<string, mixed>>, 1: int, 2: bool}
     */
    private function rightExtensions(string $word, int $length): array
    {
        [$lower, $upper] = self::rangeBounds($word);

        $conditions = ['normalized >= ?', 'length > ?', '(is_ods8 = 1 OR is_ods9 = 1)'];
        $params = [$lower, $length];

        if ($upper !== null) {
            $conditions[] = 'normalized < ?';
            $params[] = $upper;
        }

        $statement = $this->connection->pdo()->prepare(
            'SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE ' . implode(' AND ', $conditions)
            . ' ORDER BY normalized LIMIT ?'
        );
        $statement->execute([...$params, self::EXTENSION_ROW_CEILING + 1]);
        $rows = $statement->fetchAll();

        $truncated = count($rows) > self::EXTENSION_ROW_CEILING;
        $total = $truncated ? self::EXTENSION_ROW_CEILING : count($rows);

        $items = array_map(static fn (array $row): array => self::toItem($row), $rows);

        return [array_slice($items, 0, self::DISPLAY_LIMIT_PER_CATEGORY), $total, $truncated];
    }

    /**
     * Categorie 7 : admis, suffixe = le mot, plus long. Ancrage sur reversed (meme technique
     * que WordListSolver pour "terminant"), plus le predicat length > N. Re-trie par
     * normalized cote PHP -- l'ordre d'ancrage (reversed) n'est pas l'ordre d'affichage,
     * meme raison que WordListSolver::solveBounded().
     *
     * @return array{0: list<array<string, mixed>>, 1: int, 2: bool}
     */
    private function leftExtensions(string $word, int $length): array
    {
        [$lower, $upper] = self::rangeBounds(Normalizer::reverse($word));

        $conditions = ['reversed >= ?', 'length > ?', '(is_ods8 = 1 OR is_ods9 = 1)'];
        $params = [$lower, $length];

        if ($upper !== null) {
            $conditions[] = 'reversed < ?';
            $params[] = $upper;
        }

        $statement = $this->connection->pdo()->prepare(
            'SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE ' . implode(' AND ', $conditions)
            . ' ORDER BY reversed LIMIT ?'
        );
        $statement->execute([...$params, self::EXTENSION_ROW_CEILING + 1]);
        $rows = $statement->fetchAll();

        $truncated = count($rows) > self::EXTENSION_ROW_CEILING;
        $total = $truncated ? self::EXTENSION_ROW_CEILING : count($rows);

        usort($rows, static fn (array $a, array $b): int => $a['normalized'] <=> $b['normalized']);

        $items = array_map(static fn (array $row): array => self::toItem($row), $rows);

        return [array_slice($items, 0, self::DISPLAY_LIMIT_PER_CATEGORY), $total, $truncated];
    }

    /**
     * Categorie 8 : admis, contient le mot, plus long, MAIS ni prefixe ni suffixe (exclut
     * les deux categories precedentes). Aucun index dedie a une sous-chaine a une position
     * quelconque (D-012) -- mais "length > N" reste un ancrage reel et toujours disponible
     * ici (N = longueur du mot, connue par construction), contrairement au cas
     * WordListSolver::solveBounded() pour "contenant" employe seul, qui n'a AUCUN ancrage et
     * degenere en un parcours des lignes les plus petites alphabetiquement de toute la table
     * (documente dans reports/query-plans/phase3.md). Ici la requete s'appuie sur
     * idx_terms_length_normalized (SEARCH, jamais SCAN TABLE) et n'examine que les lignes de
     * longueur strictement superieure a N.
     *
     * Choix DELIBERE apres mesure (reports/query-plans/phase4.md) : le LIMIT porte sur le
     * nombre de CORRESPONDANCES trouvees (comme les autres categories), PAS sur le nombre de
     * lignes EXAMINEES. Une premiere version plafonnait les lignes examinees (meme esprit que
     * WordListSolver::ROW_EXAMINATION_CEILING) ; ecartee apres verification : pour POSER, les
     * 350 correspondances reelles sont TOUTES de longueur >= 8, alors que
     * idx_terms_length_normalized ordonne par longueur croissante d'abord -- un plafond de
     * lignes examinees aurait epuise son budget sur les paliers 6/7 sans jamais atteindre les
     * lignes pertinentes, renvoyant ZERO resultat pour un mot tres courant au lieu de 350 : un
     * faux negatif silencieux, inacceptable pour une fiche cense repondre correctement.
     *
     * Consequence acceptee : dans le pire cas (peu ou aucune correspondance reelle), SQLite
     * examine la totalite des lignes admises de longueur > N avant de conclure -- mesure :
     * jusqu'a 68 ms pour le pire mot de 2 lettres de la base ("EH", ~403 000 lignes dans le
     * panier ancre), 57-70 ms pour POSER/CHAT. Reste tres en-dessous du budget TTFB p95 < 250 ms
     * (CLAUDE.md). $containingWordsTruncated ne se declenche que si le nombre de
     * CORRESPONDANCES atteint EXTENSION_ROW_CEILING (rare : mesure uniquement sur des mots
     * tres courts et tres frequents comme "AS") -- jamais presente comme exhaustif au-dela,
     * meme convention que WordListPage::$truncated.
     *
     * @return array{0: list<array<string, mixed>>, 1: int, 2: bool}
     */
    private function containingWords(string $word, int $length): array
    {
        $statement = $this->connection->pdo()->prepare(
            'SELECT normalized, length, score, is_ods8, is_ods9 FROM terms WHERE length > ? '
            . 'AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND instr(normalized, ?) > 0 '
            . 'AND substr(normalized, 1, ?) != ? '
            . 'AND substr(normalized, -?) != ? '
            . 'ORDER BY length, normalized LIMIT ?'
        );
        $statement->execute([
            $length, $word,
            $length, $word,
            $length, $word,
            self::EXTENSION_ROW_CEILING + 1,
        ]);
        $rows = $statement->fetchAll();

        $truncated = count($rows) > self::EXTENSION_ROW_CEILING;
        $total = $truncated ? self::EXTENSION_ROW_CEILING : count($rows);

        usort($rows, static fn (array $a, array $b): int => $a['normalized'] <=> $b['normalized']);

        $items = array_map(static fn (array $row): array => self::toItem($row), $rows);

        return [array_slice($items, 0, self::DISPLAY_LIMIT_PER_CATEGORY), $total, $truncated];
    }

    /**
     * Bornes [inclusive, exclusive) d'une plage de prefixe sur une colonne triee en ordre
     * binaire (A-Z uniquement, D-009) -- meme technique que WordListSolver::rangeBounds(),
     * dupliquee ici plutot que partagee (meme convention que mergeSorted() ci-dessus).
     *
     * @return array{0: string, 1: string|null}
     */
    private static function rangeBounds(string $prefix): array
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

    // ------------------------------------------------------------------
    // Recherches liees -- ZERO requete SQLite, pure construction d'URL.
    // ------------------------------------------------------------------

    /**
     * Jusqu'a MAX_RELATED_SEARCHES liens purs (docs/08) : longueur, commencant (deux
     * granularites), terminant, avec (jusqu'a 3 lettres distinctes), /jouer/{signature}.
     * Reutilise WordListFilters::fromPath()->canonicalUrl() et Rack::fromInput()->slug --
     * jamais de concatenation manuelle d'URL, meme discipline que le reste du code (docs/08 :
     * "Reutilise... pour construire ces URL"). Plus de lien "contenant" ici (retire, audit
     * final 3e passe, bloquant -- voir le commentaire dans le corps de la methode).
     *
     * @return list<array{type: string, url: string}>
     */
    private static function relatedSearches(string $word, int $length): array
    {
        $links = [];
        $seen = [];

        $add = static function (string $type, string $rawPath) use (&$links, &$seen): void {
            $filters = WordListFilters::fromPath($rawPath);

            if ($filters === null) {
                return;
            }

            $url = $filters->canonicalUrl();

            if (isset($seen[$url])) {
                return;
            }

            $seen[$url] = true;
            $links[] = ['type' => $type, 'url' => $url];
        };

        $add('length', $length . '-lettres');

        $add('startsWith', 'commencant/' . strtolower(substr($word, 0, 1)));

        if ($length > 3) {
            $add('startsWith', 'commencant/' . strtolower(substr($word, 0, 3)));
        }

        $add('endsWith', 'terminant/' . strtolower(substr($word, -min(2, $length))));

        // Liens "contenant" SANS ancrage retires (audit final, 3e passe, code-reviewer/
        // code-optimizer, bloquant) : /mots/contenant/{sous-chaine} sans longueur/debut/fin en
        // complement force WordListSolver::solveBounded() a parcourir la table entiere
        // (App\Seo\Family::WORD_LIST_CONTENANT reste noindex,follow, mais un robot doit d'abord
        // FETCHER la page pour decouvrir ce noindex -- fetch qui coute le parcours complet).
        // Emis inconditionnellement sur CHAQUE fiche de mot admis (403 060 pages, toutes
        // index,follow, D-017), ce lien a ete mesure comme ~1 675 000 cibles de crawl distinctes,
        // chacune a 240-400 ms -- risque reel d'epuisement du pool de workers PHP sous simple
        // crawl, pas seulement un depassement de budget TTFB occasionnel. L'outil "Contenant" du
        // hub /mots (App\View\explore-hub.php, saisie humaine volontaire, jamais auto-genere en
        // masse) reste la seule porte d'entree vers cette recherche.

        $distinctLetters = array_unique(str_split($word));
        sort($distinctLetters, SORT_STRING);
        $lettersForAvec = array_slice($distinctLetters, 0, 3);

        if ($lettersForAvec !== []) {
            $segments = implode('/', array_map(static fn (string $l): string => strtolower($l), $lettersForAvec));
            $add('with', $length . '-lettres/avec/' . $segments);
        }

        $rack = Rack::fromInput($word);

        if ($rack !== null) {
            $links[] = ['type' => 'play', 'url' => '/jouer/' . $rack->slug];
        }

        // Page hub /mots (audit SEO final, C4/C5 : maillage interne insuffisant vers les
        // pages de listes). Toujours ajoutee en dernier, apres les liens specifiques au mot
        // -- generique, jamais redondante avec les URL deja ajoutees ci-dessus (aucune
        // collision possible : /mots seul n'est jamais construit par $add()).
        $links[] = ['type' => 'exploreAll', 'url' => '/mots'];

        return array_slice($links, 0, self::MAX_RELATED_SEARCHES);
    }

    // ------------------------------------------------------------------
    // Commun.
    // ------------------------------------------------------------------

    /**
     * @param array{normalized: string, length: string|int, score: string|int, is_ods8: string|int, is_ods9: string|int} $row
     * @return array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}
     */
    private static function toItem(array $row): array
    {
        return [
            'normalized' => $row['normalized'],
            'slug' => strtolower($row['normalized']),
            'score' => (int) $row['score'],
            'length' => (int) $row['length'],
            'isOds8' => (int) $row['is_ods8'] === 1,
            'isOds9' => (int) $row['is_ods9'] === 1,
        ];
    }

    /**
     * Tri alphabetique (normalized) puis plafond d'affichage par categorie -- meme ordre que
     * WordListSolver (predictible, pas de tri par score qui masquerait des mots courants).
     *
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private static function sortAndLimit(array $items): array
    {
        usort($items, static fn (array $a, array $b): int => $a['normalized'] <=> $b['normalized']);

        return array_slice($items, 0, self::DISPLAY_LIMIT_PER_CATEGORY);
    }
}
