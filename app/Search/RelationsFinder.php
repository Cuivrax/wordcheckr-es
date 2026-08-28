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
 *     anagrammes exactes (signature du mot lui-meme), anagrammes +1 tuile (30 signatures,
 *     une par tuile ajoutee, Normalizer::ALL_TILES) et anagrammes -1 tuile (au plus 15
 *     signatures, une par tuile DISTINCTE retiree) sont trois multiensembles de TUILES de
 *     tailles differentes (N, N+1, N-1 tuiles) : leurs chaines de signature ne peuvent
 *     jamais entrer en collision entre elles (Normalizer::signatureFromTiles() jointes par
 *     un separateur dedie -- un nombre different de tuiles produit un nombre different de
 *     separateurs dans la chaine, jamais la meme chaine). Un seul `signature IN (...)`
 *     suffit, la categorie de chaque ligne renvoyee se deduit d'une simple appartenance a
 *     l'une des trois tables de correspondance construites cote PHP (exactSignature/
 *     plusMap/minusMap), sans requete supplementaire ni comparaison de longueur.
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
    /**
     * Alphabet CARACTERE (pas tuile) pour les categories 2/3/4 (changer/inserer une
     * lettre) : ce sont des relations d'edition de texte sur le mot ECRIT (une lettre
     * a la fois, a une position), pas une recomposition de tuiles physiques -- Ñ y
     * figure comme une lettre normale au meme titre que les 26 autres (ex. "ANO" ->
     * "AÑO" est une relation "changer une lettre" valide). Delibermement DIFFERENT de
     * Normalizer::ALL_TILES (qui inclut CH/LL/RR pour les categories 1/9/10,
     * anagrammes -- voir plusOneSignatures()/minusOneSignatures() plus bas).
     */
    private const ALPHABET = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Ñ', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
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
        // mb_strlen(), pas strlen() : Ñ occupe 2 octets en UTF-8 -- $length alimente ensuite
        // TOUTES les comparaisons "length > ?"/"length = ?" en SQL (rightExtensions(),
        // leftExtensions(), containingWords(), signatureBasedRelations()) contre la colonne
        // `length` de `terms`, qui compte des CARACTERES (scripts/import_es.py :
        // len(normalized) en Python). Un compte en octets ici desynchronise ces comparaisons
        // pour tout mot contenant Ñ -- bug reel trouve par un audit independant, verifie sur
        // la base reelle : CAÑAS/AÑOS absents a tort des rallonges a droite (length surestime
        // de 1 -> "length > 5" au lieu de "length > 4" pour AÑOS, exclut des extensions
        // legitimes de longueur 5), ABAÑO/ABARAÑO dupliques a tort dans "mot contenu" sur
        // CAÑA/AÑO (le prefixe/suffixe exclu par containingWords() etait mesure sur la
        // mauvaise longueur). 16 022 fiches admises concernees (mots avec Ñ).
        $length = mb_strlen($normalized, 'UTF-8');
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
     * Toutes les positions x 26 lettres alternatives (la lettre d'origine est exclue --
     * "exactement une position differente" implique une lettre reellement differente a cette
     * position). Au plus MAX_LENGTH x 26 = 390 candidats.
     *
     * mb_str_split(), jamais strlen()/$word[$i]/substr() BYTE-par-BYTE : Ñ occupe 2 octets
     * en UTF-8 -- un decoupage par octet la couperait en deux "caracteres" invalides et
     * produirait des candidats corrompus pour tout mot contenant Ñ (bug reel trouve et
     * corrige avant tout import, absent du site francais car ses formes normalisees ne
     * contiennent jamais de caractere multi-octet).
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
        $characters = mb_str_split($word, 1, 'UTF-8');
        $length = count($characters);
        $candidates = [];

        for ($i = 0; $i < $length; $i++) {
            $original = $characters[$i];

            foreach (self::ALPHABET as $letter) {
                if ($letter === $original) {
                    continue;
                }

                $prefix = implode('', array_slice($characters, 0, $i));
                $suffix = implode('', array_slice($characters, $i + 1));
                $candidate = $prefix . $letter . $suffix;

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
     * mb_str_split(), pas substr() BYTE-par-BYTE -- meme raison que changeOneLetterCandidates()
     * ci-dessus.
     *
     * @return array<string, true>
     */
    private static function removeOneLetterCandidates(string $word): array
    {
        $characters = mb_str_split($word, 1, 'UTF-8');
        $length = count($characters);
        $candidates = [];

        for ($i = 0; $i < $length; $i++) {
            $prefix = implode('', array_slice($characters, 0, $i));
            $suffix = implode('', array_slice($characters, $i + 1));
            $candidates[$prefix . $suffix] = true;
        }

        return $candidates;
    }

    /**
     * Le mot est obtenu en retirant une lettre du candidat -- donc le candidat = le mot avec
     * une lettre inseree a une position quelconque parmi (longueur + 1), pour chacune des 27
     * lettres (self::ALPHABET, Ñ comprise). Vide si le mot est deja a MAX_LENGTH : aucun
     * candidat de longueur + 1 ne peut jamais exister en base, inutile de le generer.
     *
     * mb_strlen()/mb_str_split(), pas strlen()/substr() BYTE-par-BYTE -- meme raison que
     * changeOneLetterCandidates() ci-dessus. La borne MAX_LENGTH porte sur le nombre de
     * CARACTERES (colonne `length` de `terms`), pas de tuiles -- categorie de type
     * "edition de texte", pas "recomposition de tuiles" (voir le commentaire de
     * self::ALPHABET).
     *
     * @return array<string, true>
     */
    private static function insertOneLetterCandidates(string $word): array
    {
        if (mb_strlen($word, 'UTF-8') >= Normalizer::MAX_LENGTH) {
            return [];
        }

        $characters = mb_str_split($word, 1, 'UTF-8');
        $length = count($characters);
        $candidates = [];

        for ($i = 0; $i <= $length; $i++) {
            $prefix = implode('', array_slice($characters, 0, $i));
            $suffix = implode('', array_slice($characters, $i));

            foreach (self::ALPHABET as $letter) {
                $candidates[$prefix . $letter . $suffix] = true;
            }
        }

        return $candidates;
    }

    /**
     * Toute sous-chaine CONTIGUE de longueur 2 a N-1. Au plus environ N(N-1)/2 candidats
     * (104 pour un mot de 15 lettres), dedupliques.
     *
     * mb_str_split(), pas substr() BYTE-par-BYTE -- meme raison que
     * changeOneLetterCandidates() ci-dessus.
     *
     * @return array<string, true>
     */
    private static function substringCandidates(string $word): array
    {
        $characters = mb_str_split($word, 1, 'UTF-8');
        $length = count($characters);
        $candidates = [];

        for ($start = 0; $start < $length; $start++) {
            for ($len = 2; $len <= $length - 1; $len++) {
                if ($start + $len > $length) {
                    break;
                }

                $candidates[implode('', array_slice($characters, $start, $len))] = true;
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
     * Categories 9/10 (anagrammes +-1) : au sens des TUILES SCRABBLE (Normalizer::
     * tokenizeTiles()/signatureFromTiles()), pas des caracteres -- coherent avec la
     * categorie 1 (anagrammes exactes, deja tuile-aware) et avec App\Search\RackSolver,
     * qui resout "quel mot puis-je jouer" sur le meme modele de tuiles physiques.
     * "+1 tuile" utilise Normalizer::ALL_TILES (30 tuiles : 26 lettres + Ñ + CH/LL/RR),
     * PAS self::ALPHABET (27 lettres, categories 2/3/4 -- edition de texte, voir le
     * commentaire de cette constante) : ajouter "une lettre" au sens Scrabble, c'est
     * ajouter une TUILE a son chevalet, potentiellement une tuile digramme.
     *
     * Une signature par tuile ajoutee (30). Vide si le mot est deja a MAX_LENGTH
     * CARACTERES : aucune ligne de longueur + 1 caractere ne peut jamais exister en
     * base. Approximation acceptee, documentee : un mot DEJA a 15 tuiles mais dont le
     * nombre de CARACTERES est encore < 15 (parce qu'il contient un digramme) pourrait
     * en theorie encore accepter une tuile simple sans depasser 15 caracteres -- ce cas
     * limite n'est pas genere ici (verifie sur donnees reelles : 0 occurrence, aucun
     * mot du dictionnaire n'a 15 tuiles avec moins de 15 caracteres et une place libre
     * en dessous du plafond -- voir le rapport AFTER pour la mesure).
     *
     * @return array<string, string> signature => tuile ajoutee
     */
    private static function plusOneSignatures(string $word): array
    {
        if (mb_strlen($word, 'UTF-8') >= Normalizer::MAX_LENGTH) {
            return [];
        }

        $baseTiles = Normalizer::tokenizeTiles($word);
        $map = [];

        foreach (Normalizer::ALL_TILES as $tile) {
            $signature = Normalizer::signatureFromTiles(array_merge($baseTiles, [$tile]));
            $map[$signature] = $tile;
        }

        return $map;
    }

    /**
     * Une signature par TUILE DISTINCTE presente dans le mot (au plus 15, souvent
     * moins) -- voir plusOneSignatures() ci-dessus pour le choix "tuile, pas lettre".
     *
     * @return array<string, string> signature => tuile retiree
     */
    private static function minusOneSignatures(string $word): array
    {
        $baseTiles = Normalizer::tokenizeTiles($word);
        $distinctTiles = array_unique($baseTiles);
        $map = [];

        foreach ($distinctTiles as $tile) {
            $position = array_search($tile, $baseTiles, true);

            if ($position === false) {
                // Ne devrait jamais arriver : $baseTiles contient toutes les tuiles de $word
                // par construction (array_unique() filtre depuis $baseTiles lui-meme).
                continue;
            }

            $remainingTiles = $baseTiles;
            unset($remainingTiles[$position]);

            $map[Normalizer::signatureFromTiles(array_values($remainingTiles))] = $tile;
        }

        return $map;
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

    /** Voir App\Search\WordListSolver::ALPHABET_ORDER pour le detail complet (Ñ trie APRES Z
     * sous la collation BINARY de SQLite -- verifie sur la base reelle). */
    private const ALPHABET_ORDER = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ñ',
    ];

    private static function nextChar(string $char): ?string
    {
        $index = array_search($char, self::ALPHABET_ORDER, true);

        if ($index === false || $index === count(self::ALPHABET_ORDER) - 1) {
            return null;
        }

        return self::ALPHABET_ORDER[$index + 1];
    }

    /**
     * Bornes [inclusive, exclusive) d'une plage de prefixe sur une colonne triee en ordre
     * binaire (A-Z puis Ñ) -- meme technique que WordListSolver::rangeBounds(), dupliquee ici
     * plutot que partagee (meme convention que mergeSorted() ci-dessus). mb_str_split() +
     * self::nextChar(), PAS str_split()/chr(ord()+1) -- voir WordListSolver::rangeBounds()
     * pour le detail des deux bugs corriges.
     *
     * @return array{0: string, 1: string|null}
     */
    private static function rangeBounds(string $prefix): array
    {
        $chars = mb_str_split($prefix, 1, 'UTF-8');

        for ($i = count($chars) - 1; $i >= 0; $i--) {
            $next = self::nextChar($chars[$i]);

            if ($next !== null) {
                $chars[$i] = $next;

                return [$prefix, implode('', array_slice($chars, 0, $i + 1))];
            }
        }

        return [$prefix, null];
    }

    // ------------------------------------------------------------------
    // Recherches liees -- ZERO requete SQLite, pure construction d'URL.
    // ------------------------------------------------------------------

    /**
     * Jusqu'a MAX_RELATED_SEARCHES liens purs (docs/08) : longueur, empiezan-por (deux
     * granularites), terminan-en, avec (jusqu'a 3 lettres distinctes),
     * /buscador-de-palabras/{signature} (ES-004, URL localisee). Reutilise
     * WordListFilters::fromPath()->canonicalUrl() et Rack::fromInput()->slug -- jamais de
     * concatenation manuelle d'URL, meme discipline que le reste du code (docs/08 :
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

        // mb_substr(), pas substr() BYTE-par-BYTE : Ñ occupe 2 octets en UTF-8 -- un decoupage
        // par octet produirait un prefixe/suffixe corrompu (fragment invalide) des que Ñ tombe
        // dans les 1, 3 ou 2 premiers/derniers CARACTERES du mot (bug reel, absent du site
        // francais). mb_strtolower(), pas strtolower(), pour la meme raison (voir
        // TermLookup::find()).
        $add('length', $length . '-letras');

        $add('startsWith', 'empiezan-por/' . mb_strtolower(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8'));

        if ($length > 3) {
            $add('startsWith', 'empiezan-por/' . mb_strtolower(mb_substr($word, 0, 3, 'UTF-8'), 'UTF-8'));
        }

        $add('endsWith', 'terminan-en/' . mb_strtolower(mb_substr($word, -min(2, $length), null, 'UTF-8'), 'UTF-8'));

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

        // mb_str_split() ici aussi -- meme raison, "distinctLetters" doit rester des
        // CARACTERES (Ñ comprise), jamais des octets.
        $distinctLetters = array_unique(mb_str_split($word, 1, 'UTF-8'));
        sort($distinctLetters, SORT_STRING);
        $lettersForAvec = array_slice($distinctLetters, 0, 3);

        if ($lettersForAvec !== []) {
            $segments = implode('/', array_map(
                static fn (string $l): string => mb_strtolower($l, 'UTF-8'),
                $lettersForAvec
            ));
            $add('with', $length . '-letras/avec/' . $segments);
        }

        // "/buscador-de-palabras" (pas "/jouer" ni "/generador-de-anagramas") : localisation
        // d'URL espagnole, terme choisi pour coller au comportement REEL de l'outil --
        // App\Search\RackSolver::knownLetterSubsets() genere des SOUS-ENSEMBLES du chevalet
        // saisi (0 a n lettres connues), jamais seulement l'anagramme complet -- verifie dans
        // le code avant de choisir ce terme, pas suppose (voir docs/DECISIONS.md ES-004 et
        // reports/es-serp-terminology-research.md §2.6 : "generador de anagramas" designerait
        // a tort un outil qui exige TOUTES les lettres, un concept concurrent different et
        // non fidele au comportement de ce solveur).
        $rack = Rack::fromInput($word);

        if ($rack !== null) {
            $links[] = ['type' => 'play', 'url' => '/buscador-de-palabras/' . $rack->slug];
        }

        // Page hub /palabras (equivalent localise de /mots, ES-004). Toujours ajoutee en
        // dernier, apres les liens specifiques au mot -- generique, jamais redondante avec
        // les URL deja ajoutees ci-dessus (aucune collision possible : /palabras seul n'est
        // jamais construit par $add()).
        $links[] = ['type' => 'exploreAll', 'url' => '/palabras'];

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
            'slug' => mb_strtolower($row['normalized'], 'UTF-8'),
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
