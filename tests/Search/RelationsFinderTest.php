<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\RelationsFinder;
use Tests\Support\Assert;

/**
 * Exerce App\Search\RelationsFinder sur la vraie base storage/dictionary_es.sqlite
 * (lecture seule) : correction croisee par force brute pour CARTAS (mot pivot sans
 * digramme) et COCHE (mot pivot AVEC tuile digramme CH), plus deux cas limites --
 * un mot tres court (AS, 2 lettres) et un mot au plafond de longueur
 * (ABALDONADAMENTE, 15 lettres).
 *
 * Adapte de tests/Search/RelationsFinderTest.php (site francais) -- chaque brute force
 * reimplemente la definition en langage naturel de la categorie de facon INDEPENDANTE
 * du mecanisme de RelationsFinder (candidats explicites, signatures), y compris sa
 * propre tokenisation en tuiles (dupliquee ici volontairement, PAS un appel a
 * Normalizer::tokenizeTiles()) : l'objectif est de detecter une erreur de logique dans
 * RelationsFinder, pas de la confirmer en circularite. Toutes les operations sur des
 * caracteres individuels sont mb-safe (Ñ occupe 2 octets en UTF-8).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $finder = new RelationsFinder($connection);

    // Tokenisation en tuiles REIMPLEMENTEE independamment (voir le commentaire de
    // fichier) : CH/LL/RR adjacents forment une tuile, tout le reste est une lettre.
    $tokenizeTiles = static function (string $word): array {
        $characters = mb_str_split($word, 1, 'UTF-8');
        $digraphs = ['CH', 'LL', 'RR'];
        $tiles = [];
        $i = 0;
        $n = count($characters);
        while ($i < $n) {
            $pair = $i + 1 < $n ? $characters[$i] . $characters[$i + 1] : '';
            if (in_array($pair, $digraphs, true)) {
                $tiles[] = $pair;
                $i += 2;
            } else {
                $tiles[] = $characters[$i];
                $i += 1;
            }
        }
        return $tiles;
    };

    $tileSignature = static function (string $word) use ($tokenizeTiles): string {
        $tiles = $tokenizeTiles($word);
        sort($tiles, SORT_STRING);
        return implode('.', $tiles);
    };

    /** @return list<string> admis, longueur exacte, trie */
    $admittedOfLength = static function (int $length) use ($pdo): array {
        $statement = $pdo->prepare('SELECT normalized FROM terms WHERE length = ? AND (is_ods8 = 1 OR is_ods9 = 1)');
        $statement->execute([$length]);
        $words = array_column($statement->fetchAll(), 'normalized');
        sort($words, SORT_STRING);

        return $words;
    };

    // Deliberement PAS de "SELECT ... WHERE length > ?" fetchAll() ici : ce panier peut
    // depasser des centaines de milliers de lignes pour un seuil bas -- le charger
    // entierement en memoire PHP pour le filtrer ensuite est le genre de motif que ce
    // projet interdit au runtime (CLAUDE.md) et qui, meme en test, epuise
    // memory_limit=128M par defaut. Les predicats prefixe/suffixe/contenant sont donc
    // exprimes directement en SQL (substr()/instr(), independants de RelationsFinder).

    $countRightExtensions = static function (string $word, int $length) use ($pdo): int {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) c FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND substr(normalized, 1, ?) = ?'
        );
        $statement->execute([$length, $length, $word]);

        return (int) $statement->fetch()['c'];
    };

    $fetchRightExtensions = static function (string $word, int $length) use ($pdo): array {
        $statement = $pdo->prepare(
            'SELECT normalized FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND substr(normalized, 1, ?) = ? ORDER BY normalized'
        );
        $statement->execute([$length, $length, $word]);

        return array_column($statement->fetchAll(), 'normalized');
    };

    $countLeftExtensions = static function (string $word, int $length) use ($pdo): int {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) c FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND substr(normalized, -?) = ?'
        );
        $statement->execute([$length, $length, $word]);

        return (int) $statement->fetch()['c'];
    };

    $fetchLeftExtensions = static function (string $word, int $length) use ($pdo): array {
        $statement = $pdo->prepare(
            'SELECT normalized FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND substr(normalized, -?) = ? ORDER BY normalized'
        );
        $statement->execute([$length, $length, $word]);

        return array_column($statement->fetchAll(), 'normalized');
    };

    $fetchContainingWords = static function (string $word, int $length) use ($pdo): array {
        $statement = $pdo->prepare(
            'SELECT normalized FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND instr(normalized, ?) > 0 AND substr(normalized, 1, ?) != ? AND substr(normalized, -?) != ? '
            . 'ORDER BY normalized'
        );
        $statement->execute([$length, $word, $length, $word, $length, $word]);

        return array_column($statement->fetchAll(), 'normalized');
    };

    /**
     * Assertion generique : $actual (deja plafonne a DISPLAY_LIMIT_PER_CATEGORY) doit
     * etre un sous-ensemble trie de $expectedFull, et si $expectedFull tient dans le
     * plafond, l'egalite doit etre exacte.
     *
     * @param list<string> $actual
     * @param list<string> $expectedFull deja trie
     */
    $assertCategory = static function (array $actual, array $expectedFull, string $label): void {
        sort($expectedFull, SORT_STRING);
        $limit = RelationsFinder::DISPLAY_LIMIT_PER_CATEGORY;

        if (count($expectedFull) <= $limit) {
            Assert::same($expectedFull, $actual, $label . ' : correspondance exacte attendue (sous le plafond d\'affichage)');

            return;
        }

        Assert::same($limit, count($actual), $label . ' : plafond d\'affichage attendu');
        $expectedDisplayed = array_slice($expectedFull, 0, $limit);
        Assert::same($expectedDisplayed, $actual, $label . ' : premiers elements tries attendus');
    };

    // =====================================================================
    // CARTAS -- mot pivot SANS digramme, chiffres verifies par force brute.
    // =====================================================================

    $word = 'CARTAS';
    $length = mb_strlen($word, 'UTF-8');
    $relations = $finder->find($word);

    Assert::same(5, $relations->queryCount, 'budget : 5 requetes pour un mot admis (RelationsFinder seul, hors TermLookup)');

    // --- 1. Anagrammes exactes (au sens des TUILES). ---
    $sameLength = $admittedOfLength($length);
    $sig = $tileSignature($word);
    $bruteAnagrams = array_values(array_filter($sameLength, static fn (string $w) => $w !== $word && $tileSignature($w) === $sig));
    $assertCategory(array_column($relations->anagrams, 'normalized'), $bruteAnagrams, 'anagrams');

    // --- 2. Changer une lettre : distance de Hamming CARACTERE exactement 1, meme longueur. ---
    $hamming1 = static function (string $a, string $b): bool {
        $charsA = mb_str_split($a, 1, 'UTF-8');
        $charsB = mb_str_split($b, 1, 'UTF-8');
        if (count($charsA) !== count($charsB)) {
            return false;
        }
        $diff = 0;
        foreach ($charsA as $i => $ch) {
            if ($ch !== $charsB[$i]) {
                $diff++;
            }
        }

        return $diff === 1;
    };
    $bruteChange = array_values(array_filter($sameLength, static fn (string $w) => $hamming1($w, $word)));
    $assertCategory(array_column($relations->changeOneLetter, 'normalized'), $bruteChange, 'changeOneLetter');

    // --- 3. Retirer une lettre : sous-sequence obtenue en supprimant exactement 1 CARACTERE. ---
    $shorterByOne = $admittedOfLength($length - 1);
    $isDeletionOf = static function (string $candidate, string $w): bool {
        $chars = mb_str_split($w, 1, 'UTF-8');
        $n = count($chars);
        for ($i = 0; $i < $n; $i++) {
            $variant = implode('', array_slice($chars, 0, $i)) . implode('', array_slice($chars, $i + 1));
            if ($variant === $candidate) {
                return true;
            }
        }

        return false;
    };
    $bruteRemove = array_values(array_filter($shorterByOne, static fn (string $w) => $isDeletionOf($w, $word)));
    $assertCategory(array_column($relations->removeOneLetter, 'normalized'), $bruteRemove, 'removeOneLetter');

    // --- 4. Inserer une lettre : le mot = candidat avec une lettre supprimee. ---
    $longerByOne = $admittedOfLength($length + 1);
    $bruteInsert = array_values(array_filter($longerByOne, static fn (string $w) => $isDeletionOf($word, $w)));
    $assertCategory(array_column($relations->insertOneLetter, 'normalized'), $bruteInsert, 'insertOneLetter');

    // --- 5. Sous-mots : sous-chaine CONTIGUE (caracteres), longueur 2 a N-1. ---
    $bruteSubstrings = [];
    for ($l = 2; $l <= $length - 1; $l++) {
        foreach ($admittedOfLength($l) as $candidate) {
            if (str_contains($word, $candidate)) {
                $bruteSubstrings[] = $candidate;
            }
        }
    }
    $assertCategory(array_column($relations->substrings, 'normalized'), $bruteSubstrings, 'substrings');

    // --- 6/7/8. Rallonges a droite/gauche, mot contenu. ---
    $bruteRight = $fetchRightExtensions($word, $length);
    $bruteLeft = $fetchLeftExtensions($word, $length);
    $bruteContaining = $fetchContainingWords($word, $length);

    Assert::same(count($bruteRight), $relations->rightExtensionsTotal, 'rightExtensions : total exact (sous le plafond)');
    $assertCategory(array_column($relations->rightExtensions, 'normalized'), $bruteRight, 'rightExtensions');
    Assert::same(0, count($bruteRight), 'rightExtensions CARTAS : structurellement vide, verifie a la main (aucun mot admis ne commence par CARTAS et est plus long)');

    Assert::same(count($bruteLeft), $relations->leftExtensionsTotal, 'leftExtensions : total exact (sous le plafond)');
    $assertCategory(array_column($relations->leftExtensions, 'normalized'), $bruteLeft, 'leftExtensions');

    Assert::same(count($bruteContaining), $relations->containingWordsTotal, 'containingWords : total exact (sous le plafond)');
    $assertCategory(array_column($relations->containingWords, 'normalized'), $bruteContaining, 'containingWords');

    // --- 9/10. Anagrammes +1/-1 TUILE (pas lettre -- voir Normalizer::ALL_TILES). ---
    $multisetDiffersByOneTile = static function (array $candidateTiles, array $baseTiles): bool {
        if (count($candidateTiles) !== count($baseTiles) + 1) {
            return false;
        }
        $remaining = $baseTiles;
        $extra = 0;
        foreach ($candidateTiles as $tile) {
            $pos = array_search($tile, $remaining, true);
            if ($pos === false) {
                $extra++;
                continue;
            }
            unset($remaining[$pos]);
            $remaining = array_values($remaining);
        }

        return $extra === 1 && $remaining === [];
    };
    $baseTiles = $tokenizeTiles($word);
    $bruteMinusOne = array_values(array_filter(
        $shorterByOne,
        static fn (string $w) => $multisetDiffersByOneTile($baseTiles, $tokenizeTiles($w))
    ));
    $assertCategory(array_column($relations->anagramsMinusOne, 'normalized'), $bruteMinusOne, 'anagramsMinusOne');

    $bruteFullPlusOne = array_values(array_filter(
        $longerByOne,
        static fn (string $w) => $multisetDiffersByOneTile($tokenizeTiles($w), $baseTiles)
    ));
    $assertCategory(array_column($relations->anagramsPlusOne, 'normalized'), $bruteFullPlusOne, 'anagramsPlusOne');

    // --- Recherches liees : bien formees. ---
    Assert::true(count($relations->relatedSearches) <= RelationsFinder::MAX_RELATED_SEARCHES, 'relatedSearches : plafond');
    foreach ($relations->relatedSearches as $link) {
        Assert::true(
            $link['url'] === '/palabras' || str_starts_with($link['url'], '/palabras/') || str_starts_with($link['url'], '/buscador-de-palabras/'),
            'relatedSearches : URL bien formee (ES-004, URL localisee) -- ' . $link['url'],
        );
        Assert::true(!str_starts_with($link['url'], '/palabras/contenant/'), 'relatedSearches ne doit jamais emettre de lien "contenant" sans ancrage : ' . $link['url']);
    }

    // =====================================================================
    // COCHE -- mot pivot AVEC tuile digramme (CH). Verifie que le mecanisme tuile
    // (categories 1/9/10) reste correct par force brute independante, en plus du
    // garde-fou deja pose dans NormalizerTest/RackSolverTest.
    // =====================================================================

    $digraphWord = 'COCHE';
    $digraphLength = mb_strlen($digraphWord, 'UTF-8');
    $digraphRelations = $finder->find($digraphWord);

    $digraphSameLength = $admittedOfLength($digraphLength);
    $digraphSig = $tileSignature($digraphWord);
    $bruteDigraphAnagrams = array_values(array_filter(
        $digraphSameLength,
        static fn (string $w) => $w !== $digraphWord && $tileSignature($w) === $digraphSig
    ));
    $assertCategory(array_column($digraphRelations->anagrams, 'normalized'), $bruteDigraphAnagrams, 'anagrams(COCHE)');
    Assert::true(in_array('CHECO', $bruteDigraphAnagrams, true), 'CHECO doit etre une anagramme (tuiles) confirmee par la verite terrain elle-meme');
    Assert::true(in_array('CHECO', array_column($digraphRelations->anagrams, 'normalized'), true), 'CHECO doit apparaitre dans les anagrammes renvoyees par RelationsFinder');

    // =====================================================================
    // AS -- mot le plus court possible (2 lettres). Categories structurellement
    // vides : retirer une lettre, sous-mots, anagrammes -1 tuile.
    // =====================================================================

    $shortRelations = $finder->find('AS');
    Assert::same(5, $shortRelations->queryCount, 'AS : meme budget de 5 requetes qu\'un mot plus long');
    Assert::same([], $shortRelations->removeOneLetter, 'AS : retirer une lettre structurellement vide (1 lettre non stockee)');
    Assert::same([], $shortRelations->substrings, 'AS : sous-mots structurellement vide (aucune longueur 2..N-1 possible)');
    Assert::same([], $shortRelations->anagramsMinusOne, 'AS : anagrammes -1 tuile structurellement vide, meme raison');

    $bruteRightAsCount = $countRightExtensions('AS', 2);
    $bruteLeftAsCount = $countLeftExtensions('AS', 2);
    Assert::true($bruteRightAsCount > RelationsFinder::EXTENSION_ROW_CEILING, 'AS : verite terrain, prefixe tres frequent, doit depasser le plafond (mesure : ' . $bruteRightAsCount . ')');
    Assert::true($bruteLeftAsCount > RelationsFinder::EXTENSION_ROW_CEILING, 'AS : verite terrain, suffixe tres frequent, doit depasser le plafond (mesure : ' . $bruteLeftAsCount . ')');
    Assert::true($shortRelations->rightExtensionsTruncated, 'AS : rightExtensions doit etre marque tronque');
    Assert::true($shortRelations->leftExtensionsTruncated, 'AS : leftExtensions doit etre marque tronque');
    Assert::same(RelationsFinder::EXTENSION_ROW_CEILING, $shortRelations->rightExtensionsTotal, 'AS : total plafonne, jamais presente comme exact au-dela du plafond');
    Assert::same(RelationsFinder::DISPLAY_LIMIT_PER_CATEGORY, count($shortRelations->rightExtensions), 'AS : liste affichee plafonnee malgre la troncature');

    // =====================================================================
    // ABALDONADAMENTE -- 15 lettres, plafond de longueur (Normalizer::MAX_LENGTH).
    // Categories structurellement vides : inserer une lettre et anagrammes +1 tuile
    // (aucun mot de 16 caracteres ne peut jamais exister en base), rallonges a
    // droite/gauche et mot contenu (aucun mot plus long que 15 caracteres non plus).
    // =====================================================================

    $longWord = 'ABALDONADAMENTE';
    Assert::same(15, mb_strlen($longWord, 'UTF-8'), 'mot de test au plafond exact de longueur');
    $longRelations = $finder->find($longWord);
    Assert::same(5, $longRelations->queryCount, 'ABALDONADAMENTE : meme budget de 5 requetes');
    Assert::same([], $longRelations->insertOneLetter, 'ABALDONADAMENTE : inserer une lettre structurellement vide (aucun mot de 16 caracteres en base)');
    Assert::same([], $longRelations->anagramsPlusOne, 'ABALDONADAMENTE : anagrammes +1 tuile structurellement vide, meme raison');
    Assert::same([], $longRelations->rightExtensions, 'ABALDONADAMENTE : rallonges a droite structurellement vide, meme raison');
    Assert::same(0, $longRelations->rightExtensionsTotal);
    Assert::true(!$longRelations->rightExtensionsTruncated, 'ABALDONADAMENTE : total 0 n\'est pas une troncature');
    Assert::same([], $longRelations->leftExtensions, 'ABALDONADAMENTE : rallonges a gauche structurellement vide, meme raison');
    Assert::same([], $longRelations->containingWords, 'ABALDONADAMENTE : mot contenu structurellement vide, meme raison');

    Assert::true(count($longRelations->relatedSearches) >= 1 && count($longRelations->relatedSearches) <= RelationsFinder::MAX_RELATED_SEARCHES, 'ABALDONADAMENTE : recherches liees dans les bornes');
};
