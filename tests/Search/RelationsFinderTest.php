<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\RelationsFinder;
use Tests\Support\Assert;

/**
 * Exerce App\Search\RelationsFinder sur la vraie base storage/dictionary_fr.sqlite (lecture
 * seule) : correction croisee par force brute pour POSER (mot pivot de docs/08, verifie
 * catégorie par categorie contre les chiffres du prototype de reference), et deux cas limites
 * explicitement demandes par la tache -- un mot tres court (AS, 2 lettres) et un mot au
 * plafond de longueur (ABANDONNERAIENT, 15 lettres, D-010).
 *
 * Chaque brute force ci-dessous reimplemente la definition en langage naturel de la categorie
 * (docs/08) de facon INDEPENDANTE du mecanisme de RelationsFinder (candidats explicites,
 * signatures) -- pas un appel a ses methodes privees : l'objectif est de detecter une erreur
 * de logique dans RelationsFinder, pas de la confirmer en circularite.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $finder = new RelationsFinder($connection);

    /** @return list<string> admis, longueur exacte, trie */
    $admittedOfLength = static function (int $length) use ($pdo): array {
        $statement = $pdo->prepare('SELECT normalized FROM terms WHERE length = ? AND (is_ods8 = 1 OR is_ods9 = 1)');
        $statement->execute([$length]);
        $words = array_column($statement->fetchAll(), 'normalized');
        sort($words, SORT_STRING);

        return $words;
    };

    // Deliberement PAS de "SELECT ... WHERE length > ? " fetchAll() ici : pour un seuil bas
    // (2 lettres), ce panier depasse 400 000 lignes (mesure : 402 979 pour > 2) -- le charger
    // entierement en memoire PHP pour le filtrer ensuite est le genre de motif que ce projet
    // interdit au runtime (CLAUDE.md) et qui, meme en test, epuise memory_limit=128M par
    // defaut. Les predicats prefixe/suffixe/contenant sont donc exprimes directement en SQL
    // (substr()/instr(), independants de RelationsFinder -- SQLite calcule, pas PHP) : verite
    // terrain streamee, jamais materialisee en un seul grand tableau.

    /** Nombre de lignes admises, longueur > $length, dont le prefixe des $length premiers
     * caracteres egale $word (rallonges a droite). */
    $countRightExtensions = static function (string $word, int $length) use ($pdo): int {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) c FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND substr(normalized, 1, ?) = ?'
        );
        $statement->execute([$length, $length, $word]);

        return (int) $statement->fetch()['c'];
    };

    /** @return list<string> trie, mots admis dont le prefixe egale $word (pas de LIMIT --
     * reserve aux cas ou le compte est deja connu comme raisonnable, ex. POSER). */
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

    /** @return list<string> trie */
    $fetchLeftExtensions = static function (string $word, int $length) use ($pdo): array {
        $statement = $pdo->prepare(
            'SELECT normalized FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND substr(normalized, -?) = ? ORDER BY normalized'
        );
        $statement->execute([$length, $length, $word]);

        return array_column($statement->fetchAll(), 'normalized');
    };

    /** @return list<string> trie, contient $word mais ni prefixe ni suffixe */
    $fetchContainingWords = static function (string $word, int $length) use ($pdo): array {
        $statement = $pdo->prepare(
            'SELECT normalized FROM terms WHERE length > ? AND (is_ods8 = 1 OR is_ods9 = 1) '
            . 'AND instr(normalized, ?) > 0 AND substr(normalized, 1, ?) != ? AND substr(normalized, -?) != ? '
            . 'ORDER BY normalized'
        );
        $statement->execute([$length, $word, $length, $word, $length, $word]);

        return array_column($statement->fetchAll(), 'normalized');
    };

    $signatureOf = static function (string $w): string {
        $chars = str_split($w);
        sort($chars, SORT_STRING);

        return implode('', $chars);
    };

    /**
     * Assertion generique : $actual (la liste renvoyee par RelationsFinder, deja plafonnee a
     * DISPLAY_LIMIT_PER_CATEGORY) doit etre un sous-ensemble trie de $expectedFull, et si
     * $expectedFull tient dans le plafond, l'egalite doit etre exacte.
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
    // POSER -- cas pivot de docs/08, chiffres verifies par force brute (pas un echantillon).
    // =====================================================================

    $word = 'POSER';
    $length = strlen($word);
    $relations = $finder->find($word);

    Assert::same(5, $relations->queryCount, 'budget : 5 requetes pour un mot admis (RelationsFinder seul, hors TermLookup)');

    // --- 1. Anagrammes exactes. ---
    $sameLength = $admittedOfLength($length);
    $sig = $signatureOf($word);
    $bruteAnagrams = array_values(array_filter($sameLength, static fn (string $w) => $w !== $word && $signatureOf($w) === $sig));
    $assertCategory(array_column($relations->anagrams, 'normalized'), $bruteAnagrams, 'anagrams');
    Assert::same(['PERSO', 'PORES', 'PROSE', 'PSORE', 'REPOS', 'SPORE'], array_column($relations->anagrams, 'normalized'), 'anagrams POSER : liste exacte connue');

    // --- 2. Changer une lettre : distance de Hamming exactement 1, meme longueur. ---
    $hamming1 = static function (string $a, string $b): bool {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        $diff = 0;
        for ($i = 0, $n = strlen($a); $i < $n; $i++) {
            if ($a[$i] !== $b[$i]) {
                $diff++;
            }
        }

        return $diff === 1;
    };
    $bruteChange = array_values(array_filter($sameLength, static fn (string $w) => $hamming1($w, $word)));
    $assertCategory(array_column($relations->changeOneLetter, 'normalized'), $bruteChange, 'changeOneLetter');
    Assert::same(['DOSER', 'LOSER', 'PESER', 'POKER', 'POSEE', 'POSES', 'POSEZ', 'POTER', 'ROSER'], array_column($relations->changeOneLetter, 'normalized'), 'changeOneLetter POSER : liste exacte connue');

    // --- 3. Retirer une lettre : sous-sequence obtenue en supprimant exactement 1 caractere. ---
    $shorterByOne = $admittedOfLength($length - 1);
    $isDeletionOf = static function (string $candidate, string $w): bool {
        for ($i = 0, $n = strlen($w); $i < $n; $i++) {
            if (substr($w, 0, $i) . substr($w, $i + 1) === $candidate) {
                return true;
            }
        }

        return false;
    };
    $bruteRemove = array_values(array_filter($shorterByOne, static fn (string $w) => $isDeletionOf($w, $word)));
    $assertCategory(array_column($relations->removeOneLetter, 'normalized'), $bruteRemove, 'removeOneLetter');
    Assert::same(['OSER', 'POSE'], array_column($relations->removeOneLetter, 'normalized'), 'removeOneLetter POSER : liste exacte connue');

    // --- 4. Inserer une lettre : le mot = candidat avec une lettre supprimee. ---
    $longerByOne = $admittedOfLength($length + 1);
    $bruteInsert = array_values(array_filter($longerByOne, static fn (string $w) => $isDeletionOf($word, $w)));
    $assertCategory(array_column($relations->insertOneLetter, 'normalized'), $bruteInsert, 'insertOneLetter');
    Assert::same(['POSERA', 'POSEUR', 'POSTER'], array_column($relations->insertOneLetter, 'normalized'), 'insertOneLetter POSER : POSERA est une insertion valide (retirer le A final redonne POSER), meme si absent du prototype illustratif');

    // --- 5. Sous-mots : sous-chaine CONTIGUE, longueur 2 a N-1. ---
    $bruteSubstrings = [];
    for ($l = 2; $l <= $length - 1; $l++) {
        foreach ($admittedOfLength($l) as $candidate) {
            if (str_contains($word, $candidate)) {
                $bruteSubstrings[] = $candidate;
            }
        }
    }
    $assertCategory(array_column($relations->substrings, 'normalized'), $bruteSubstrings, 'substrings');
    Assert::same(['OS', 'OSE', 'OSER', 'POSE', 'SE'], array_column($relations->substrings, 'normalized'), 'substrings POSER : liste exacte connue');

    // --- 6/7/8. Rallonges a droite/gauche, mot contenu. ---
    $bruteRight = $fetchRightExtensions($word, $length);
    $bruteLeft = $fetchLeftExtensions($word, $length);
    $bruteContaining = $fetchContainingWords($word, $length);

    Assert::same(count($bruteRight), $relations->rightExtensionsTotal, 'rightExtensions : total exact (sous le plafond)');
    Assert::true(!$relations->rightExtensionsTruncated, 'rightExtensions : pas de troncature attendue pour POSER');
    $assertCategory(array_column($relations->rightExtensions, 'normalized'), $bruteRight, 'rightExtensions');
    Assert::same(12, count($bruteRight), 'rightExtensions POSER : 12 mots, meme compte que le prototype');

    Assert::same(count($bruteLeft), $relations->leftExtensionsTotal, 'leftExtensions : total exact (sous le plafond)');
    Assert::true(!$relations->leftExtensionsTruncated, 'leftExtensions : pas de troncature attendue pour POSER');
    $assertCategory(array_column($relations->leftExtensions, 'normalized'), $bruteLeft, 'leftExtensions');
    Assert::same(30, count($bruteLeft), 'leftExtensions POSER : 30 mots, meme compte que le prototype');

    Assert::same(count($bruteContaining), $relations->containingWordsTotal, 'containingWords : total exact (sous le plafond)');
    Assert::true(!$relations->containingWordsTruncated, 'containingWords : pas de troncature attendue pour POSER (350 < 1000)');
    $assertCategory(array_column($relations->containingWords, 'normalized'), $bruteContaining, 'containingWords');

    // --- 9/10. Anagrammes +1/-1 lettre. ---
    $multisetDiffersByOneAdded = static function (string $candidate, string $w, string $sigW): bool {
        if (strlen($candidate) !== strlen($w) + 1) {
            return false;
        }
        // candidat = w + exactement une lettre : sa signature contient la signature de w
        // comme sous-multiensemble, avec un seul caractere en plus.
        $sigCandidate = str_split($candidate);
        sort($sigCandidate, SORT_STRING);
        $remaining = str_split($sigW);
        $extra = 0;
        foreach ($sigCandidate as $ch) {
            $pos = array_search($ch, $remaining, true);
            if ($pos === false) {
                $extra++;
                continue;
            }
            unset($remaining[$pos]);
            $remaining = array_values($remaining);
        }

        return $extra === 1 && $remaining === [];
    };
    $bruteMinusOne = array_values(array_filter($shorterByOne, static fn (string $w) => $multisetDiffersByOneAdded($word, $w, $signatureOf($w))));
    $assertCategory(array_column($relations->anagramsMinusOne, 'normalized'), $bruteMinusOne, 'anagramsMinusOne');
    Assert::same(['EROS', 'OPES', 'ORES', 'OSER', 'PERS', 'PESO', 'PORE', 'POSE', 'PRES', 'PROS', 'REPS', 'ROSE', 'SORE'], array_column($relations->anagramsMinusOne, 'normalized'), 'anagramsMinusOne POSER : liste exacte connue (13 mots, comme le prototype)');

    $bruteFullPlusOne = array_values(array_filter($longerByOne, static fn (string $w) => $multisetDiffersByOneAdded($w, $word, $sig)));
    Assert::same(35, count($bruteFullPlusOne), 'anagramsPlusOne POSER : 35 mots au total, meme compte que le prototype');
    $assertCategory(array_column($relations->anagramsPlusOne, 'normalized'), $bruteFullPlusOne, 'anagramsPlusOne');

    // --- Recherches liees : au plus 12, toutes des URL /mots/..., /jouer/... ou le hub /mots
    // (audit SEO final, C4/C5 -- App\Search\ExploreHub) bien formees. ---
    Assert::true(count($relations->relatedSearches) <= RelationsFinder::MAX_RELATED_SEARCHES, 'relatedSearches : plafond de 12');
    foreach ($relations->relatedSearches as $link) {
        Assert::true(
            $link['url'] === '/mots' || str_starts_with($link['url'], '/mots/') || str_starts_with($link['url'], '/jouer/'),
            'relatedSearches : URL bien formee -- ' . $link['url'],
        );
    }
    // Plus de liens "/mots/contenant/..." ici (retires, audit final 3e passe, bloquant -- voir
    // RelationsFinder::relatedSearches()) : sans ancrage, ce chemin force un parcours complet de
    // la table sur des liens emis depuis chaque fiche de mot admis.
    Assert::same(['/mots/5-lettres', '/mots/commencant/p', '/mots/commencant/pos', '/mots/terminant/er', '/mots/5-lettres/avec/e/o/p', '/jouer/eoprs', '/mots'], array_column($relations->relatedSearches, 'url'), 'relatedSearches POSER : selection exacte connue');
    foreach ($relations->relatedSearches as $link) {
        Assert::true(!str_starts_with($link['url'], '/mots/contenant/'), 'relatedSearches ne doit plus jamais emettre de lien "contenant" sans ancrage : ' . $link['url']);
    }

    // --- Plafond global "environ 160 liens de mots" (docs/01, docs/08) : verification que la ---
    // --- fiche POSER, mot volontairement choisi pour generer beaucoup de candidats dans ---
    // --- plusieurs categories a la fois, reste dans une enveloppe raisonnable. ---
    $totalLinks = count($relations->anagrams) + count($relations->changeOneLetter) + count($relations->removeOneLetter)
        + count($relations->insertOneLetter) + count($relations->substrings) + count($relations->rightExtensions)
        + count($relations->leftExtensions) + count($relations->containingWords) + count($relations->anagramsPlusOne)
        + count($relations->anagramsMinusOne);
    Assert::true($totalLinks <= 10 * RelationsFinder::DISPLAY_LIMIT_PER_CATEGORY, 'plafond de liens de mots : au plus 10 x DISPLAY_LIMIT_PER_CATEGORY');

    // =====================================================================
    // AS -- mot le plus court possible (2 lettres, Normalizer::MIN_LENGTH). Categories
    // structurellement vides : retirer une lettre (1 lettre restante, jamais en base),
    // sous-mots (aucune longueur possible entre 2 et N-1=1).
    // =====================================================================

    $shortRelations = $finder->find('AS');
    Assert::same(5, $shortRelations->queryCount, 'AS : meme budget de 5 requetes qu\'un mot plus long');
    Assert::same([], $shortRelations->removeOneLetter, 'AS : retirer une lettre structurellement vide (1 lettre non stockee)');
    Assert::same([], $shortRelations->substrings, 'AS : sous-mots structurellement vide (aucune longueur 2..N-1 possible)');
    Assert::same([], $shortRelations->anagramsMinusOne, 'AS : anagrammes -1 lettre structurellement vide, meme raison');

    // --- Categories 6/7/8 pour un prefixe/suffixe de 2 lettres tres frequent : verite ---
    // --- terrain mesuree directement par COUNT() SQL (pas un chargement de 400 000+ lignes ---
    // --- en memoire PHP), plafond attendu declenche. ---
    $bruteRightAsCount = $countRightExtensions('AS', 2);
    $bruteLeftAsCount = $countLeftExtensions('AS', 2);
    Assert::true($bruteRightAsCount > RelationsFinder::EXTENSION_ROW_CEILING, 'AS : verite terrain, prefixe tres frequent, doit depasser le plafond (mesure : ' . $bruteRightAsCount . ')');
    Assert::true($bruteLeftAsCount > RelationsFinder::EXTENSION_ROW_CEILING, 'AS : verite terrain, suffixe tres frequent, doit depasser le plafond (mesure : ' . $bruteLeftAsCount . ')');
    Assert::true($shortRelations->rightExtensionsTruncated, 'AS : rightExtensions doit etre marque tronque');
    Assert::true($shortRelations->leftExtensionsTruncated, 'AS : leftExtensions doit etre marque tronque');
    Assert::same(RelationsFinder::EXTENSION_ROW_CEILING, $shortRelations->rightExtensionsTotal, 'AS : total plafonne, jamais presente comme exact au-dela du plafond');
    Assert::same(RelationsFinder::DISPLAY_LIMIT_PER_CATEGORY, count($shortRelations->rightExtensions), 'AS : liste affichee plafonnee malgre la troncature');

    // =====================================================================
    // ABANDONNERAIENT -- 15 lettres, plafond D-010 (Normalizer::MAX_LENGTH). Categories
    // structurellement vides : inserer une lettre et anagrammes +1 (aucun mot de 16 lettres
    // ne peut jamais exister en base), rallonges a droite/gauche et mot contenu (aucun mot
    // plus long que 15 lettres ne peut jamais exister en base).
    // =====================================================================

    $longWord = 'ABANDONNERAIENT';
    Assert::same(15, strlen($longWord), 'mot de test au plafond exact de longueur');
    $longRelations = $finder->find($longWord);
    Assert::same(5, $longRelations->queryCount, 'ABANDONNERAIENT : meme budget de 5 requetes');
    Assert::same([], $longRelations->insertOneLetter, 'ABANDONNERAIENT : inserer une lettre structurellement vide (D-010, aucun mot de 16 lettres en base)');
    Assert::same([], $longRelations->anagramsPlusOne, 'ABANDONNERAIENT : anagrammes +1 lettre structurellement vide, meme raison');
    Assert::same([], $longRelations->rightExtensions, 'ABANDONNERAIENT : rallonges a droite structurellement vide, meme raison');
    Assert::same(0, $longRelations->rightExtensionsTotal);
    Assert::true(!$longRelations->rightExtensionsTruncated, 'ABANDONNERAIENT : total 0 n\'est pas une troncature');
    Assert::same([], $longRelations->leftExtensions, 'ABANDONNERAIENT : rallonges a gauche structurellement vide, meme raison');
    Assert::same([], $longRelations->containingWords, 'ABANDONNERAIENT : mot contenu structurellement vide, meme raison');

    // Recherches liees toujours bien formees, meme au plafond de longueur.
    Assert::true(count($longRelations->relatedSearches) >= 1 && count($longRelations->relatedSearches) <= RelationsFinder::MAX_RELATED_SEARCHES, 'ABANDONNERAIENT : recherches liees dans les bornes');
};
