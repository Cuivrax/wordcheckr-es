<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\PrefixAvecLinksBuilder;
use App\Search\WordListFilters;
use Tests\Support\Assert;

/**
 * App\Search\PrefixAvecLinksBuilder (maillage commencant+avec SANS terminant ni longueur, tache
 * 2026-08-18, voir reports/query-plans/commencant-avec-maillage.md) : depuis une page
 * /mots/commencant/{X} (deja indexee, Family::WORD_LIST_COMMENCANT, D-017), liens vers chaque
 * variante /mots/commencant/{X}/avec/{Y} qui a au moins un resultat -- lu depuis list_counts
 * (list_type 'start_with', precalcule par scripts/build_explore_hub_counts.php, les 26
 * combinaisons degenerees X=Y DEJA exclues au precalcul lui-meme, D-032), jamais un calcul sur
 * `terms` au runtime.
 *
 * Verifie par force brute sur la vraie base (lecture seule), PLUS une verification exhaustive du
 * maillage sur les 26 pages sources reelles (pas un echantillon) -- meme methodologie que
 * StartEndWithLinksBuilderTest.php.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new PrefixAvecLinksBuilder($connection);

    $alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

    // ============================================================================================
    // 0. Collapse D-032 verifie explicitement (pas suppose) sur les 26 cas diagonaux : condition
    // meme qui justifie l'exclusion au precalcul plutot qu'au builder (voir PrefixAvecLinks.php).
    // ============================================================================================
    foreach ($alphabet as $x) {
        $parentUrl = WordListFilters::fromPath('commencant/' . strtolower($x))?->canonicalUrl();
        $diagonalUrl = WordListFilters::fromPath('commencant/' . strtolower($x) . '/avec/' . strtolower($x))?->canonicalUrl();
        Assert::same($parentUrl, $diagonalUrl, "commencant/{$x}/avec/{$x} doit collapser vers la page parente (D-032)");
    }

    // ============================================================================================
    // 1. Cas representatif : prefixe R (224 205 mots, le plus grand panier "commencant" de la
    // base, D-025bis) -- chaque lien verifie par force brute (substr() + instr()), plus l'URL
    // canonique attendue.
    // ============================================================================================
    $linksR = $builder->build('R');
    Assert::same(1, $linksR->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($linksR->links !== [], 'sanity check : R (224 205 mots) doit avoir des lettres "avec" presentes');

    foreach ($linksR->links as $link) {
        Assert::true($link['letter'] !== 'R', 'R (lettre de debut) ne doit jamais apparaitre comme lettre "avec" -- degenere, D-032');

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) c FROM terms WHERE substr(normalized, 1, 1) = ? AND instr(normalized, ?) > 0'
        );
        $stmt->execute(['R', $link['letter']]);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], "R avec {$link['letter']} : verifie par force brute");
        Assert::same('/mots/commencant/r/avec/' . strtolower($link['letter']), $link['url']);
        Assert::true($link['count'] > 0, 'R5 : aucune entree a 0 attendue');
    }

    // Tri alphabetique par lettre "avec".
    for ($i = 1; $i < count($linksR->links); $i++) {
        Assert::true($linksR->links[$i - 1]['letter'] < $linksR->links[$i]['letter'], 'ordre alphabetique attendu');
    }

    // sanity check : list_counts ne contient jamais la ligne degeneree "R:R" (exclue au precalcul).
    $diagonalRow = $pdo->query("SELECT COUNT(*) c FROM list_counts WHERE list_type = 'start_with' AND list_key = 'R:R'")->fetch()['c'];
    Assert::same(0, (int) $diagonalRow, 'sanity check : R:R absent de list_counts (exclu au precalcul, pas au builder)');

    // ============================================================================================
    // 2. Cas a 0 resultat connus (reports/query-plans/commencant-avec-no-length-full-sweep.md
    // section 8, base inchangee depuis) : V+W, X+J, X+K, X+W -- aucun lien pour cette lettre
    // precise, jamais une erreur.
    // ============================================================================================
    $linksV = $builder->build('V');
    Assert::same(1, $linksV->queryCount);
    $letterW = array_values(array_filter($linksV->links, static fn (array $l): bool => $l['letter'] === 'W'));
    Assert::true($letterW === [], 'V+W est un cas connu a 0 resultat (full-sweep, section 8)');

    $linksX = $builder->build('X');
    Assert::same(1, $linksX->queryCount);
    foreach (['J', 'K', 'W'] as $zeroLetter) {
        $found = array_values(array_filter($linksX->links, static fn (array $l): bool => $l['letter'] === $zeroLetter));
        Assert::true($found === [], "X+{$zeroLetter} est un cas connu a 0 resultat (full-sweep, section 8)");
    }

    // ============================================================================================
    // 2bis. Doublons de contenu entre pages SOEURS "avec" (I-A, 2e audit consolide, 2026-08-18,
    // GO avec ce point non bloquant) : meme regle de detection que StartEndWithLinksBuilder --
    // deux lettres "avec" DIFFERENTES du MEME prefixe isolant exactement le meme sous-ensemble de
    // mots. Reflection sur la liste figee (vide sur cet axe), PUIS recalcul independant et
    // EXHAUSTIF (pas un echantillon) sur les 26 prefixes reels, DEUX methodes independantes sur le
    // MEME panier de mots recupere une seule fois par prefixe (meme discipline que
    // StartEndWithLinksBuilderTest.php) : A) egalite d'ensemble directe (cle = liste triee des
    // mots, comparaison de chaines completes) ; B) propriete de coincidence, algorithme different
    // (union-find sur "presence(Z1) == presence(Z2) pour tous les mots du panier"). Voir
    // reports/query-plans/avec-doublons-soeurs-correctif.md.
    // ============================================================================================
    $prefixReflection = new ReflectionClass(PrefixAvecLinksBuilder::class);
    $prefixSiblingDuplicateKeys = $prefixReflection->getConstant('SIBLING_DUPLICATE_KEYS');
    Assert::same(0, count($prefixSiblingDuplicateKeys), 'liste figee vide attendue sur cet axe (aucun panier mono-lettre reel n\'est assez petit pour qu\'une paire de lettres y soit toujours liee)');

    $computedPrefixSiblingExcluded = [];
    $prefixMethodDivergences = 0;

    foreach ($alphabet as $prefix) {
        $candidateStatement = $pdo->prepare(
            "SELECT list_key FROM list_counts WHERE list_type = 'start_with' AND list_key LIKE ?"
        );
        $candidateStatement->execute(["{$prefix}:%"]);

        $candidateLetters = [];
        foreach ($candidateStatement as $row) {
            [$rowPrefix, $letter] = explode(':', (string) $row['list_key'], 2);
            if ($letter === $rowPrefix) {
                continue; // degenere D-032, deja exclu au precalcul lui-meme
            }
            $candidateLetters[] = $letter;
        }

        if (count($candidateLetters) < 2) {
            continue; // aucun regroupement possible avec moins de 2 lettres candidates
        }

        $basketStmt = $pdo->prepare('SELECT normalized FROM terms WHERE substr(normalized, 1, 1) = ?');
        $basketStmt->execute([$prefix]);
        $basket = $basketStmt->fetchAll(PDO::FETCH_COLUMN);
        sort($basket);

        // Methode A : egalite d'ensemble directe.
        $bySetKey = [];
        foreach ($candidateLetters as $letter) {
            $subset = array_values(array_filter($basket, static fn (string $w): bool => str_contains($w, $letter)));
            $bySetKey[implode("\x1F", $subset)][] = $letter;
        }
        $groupsA = [];
        foreach ($bySetKey as $lettersInGroup) {
            if (count($lettersInGroup) >= 2) {
                sort($lettersInGroup);
                $groupsA[] = $lettersInGroup;
            }
        }

        // Methode B : propriete de coincidence, union-find (algorithme different).
        $parentOf = array_combine($candidateLetters, $candidateLetters);
        $find = static function (string $x) use (&$parentOf, &$find): string {
            return $parentOf[$x] === $x ? $x : ($parentOf[$x] = $find($parentOf[$x]));
        };
        $n = count($candidateLetters);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $l1 = $candidateLetters[$i];
                $l2 = $candidateLetters[$j];
                $tied = true;
                foreach ($basket as $w) {
                    if (str_contains($w, $l1) !== str_contains($w, $l2)) {
                        $tied = false;
                        break;
                    }
                }
                if ($tied) {
                    $r1 = $find($l1);
                    $r2 = $find($l2);
                    if ($r1 !== $r2) {
                        $parentOf[$r1] = $r2;
                    }
                }
            }
        }
        $classes = [];
        foreach ($candidateLetters as $letter) {
            $classes[$find($letter)][] = $letter;
        }
        $groupsB = [];
        foreach ($classes as $lettersInGroup) {
            if (count($lettersInGroup) >= 2) {
                sort($lettersInGroup);
                $groupsB[] = $lettersInGroup;
            }
        }

        $normA = $groupsA;
        sort($normA);
        $normB = $groupsB;
        sort($normB);
        if ($normA !== $normB) {
            $prefixMethodDivergences++;
        }

        foreach ($groupsA as $group) {
            foreach (array_slice($group, 1) as $excludedLetter) {
                $computedPrefixSiblingExcluded[] = "{$prefix}:{$excludedLetter}";
            }
        }
    }

    Assert::same(0, $prefixMethodDivergences, 'methode A (egalite directe) et methode B (union-find coincidence) doivent produire les memes groupes sur les 26 prefixes, 0 divergence');
    Assert::same([], $computedPrefixSiblingExcluded, 'aucun doublon soeur recalcule depuis `terms` sur les 26 prefixes reels -- confirme la liste figee vide, pas supposee');

    // ============================================================================================
    // 2ter. Doublons de contenu CROISES avec une famille EXTERIEURE (D-041, garde-fou structurel
    // demande par le constat C-4 du 4e audit consolide, docs/DECISIONS.md D-040) -- balayage
    // GENERIQUE de tout le registre (scripts/check_combinatorial_duplicates.php, balayage du
    // 2026-08-21 : 1 656 groupes, 2 089 pages en exces). Voir
    // PrefixAvecLinksBuilder::EXTERNAL_DUPLICATE_KEYS pour le detail complet des 4 cles.
    // ============================================================================================
    $externalDuplicateKeys = $prefixReflection->getConstant('EXTERNAL_DUPLICATE_KEYS');
    Assert::same(['U:J', 'W:J', 'X:Z', 'Y:X'], $externalDuplicateKeys, 'exactement 4 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');
    $externalDuplicateSet = array_fill_keys($externalDuplicateKeys, true);
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, array_fill_keys($prefixSiblingDuplicateKeys, true))), 'EXTERNAL_DUPLICATE_KEYS et SIBLING_DUPLICATE_KEYS doivent rester deux ensembles disjoints');

    foreach ($externalDuplicateKeys as $key) {
        [$prefix, $letter] = explode(':', $key, 2);
        $links = $builder->build($prefix);
        $found = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === $letter));
        Assert::true($found === [], "{$key} est un doublon de contenu croise (D-041) -- ne doit jamais etre produit");
    }

    // ============================================================================================
    // 3. Verification EXHAUSTIVE du maillage sur les 26 pages sources reelles (list_type
    // 'start_with') -- pas un echantillon, meme discipline que tous les paliers precedents.
    // Compare le vrai code (PrefixAvecLinksBuilder, via list_counts) au recalcul independant
    // depuis list_counts lui-meme, dans les DEUX sens.
    // ============================================================================================
    $expectedStatement = $pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'start_with'");
    $expected = [];
    foreach ($expectedStatement as $row) {
        $expected[(string) $row['list_key']] = (int) $row['count'];
    }
    Assert::same(646, count($expected), 'sanity check : 646 lignes start_with reelles (676 brutes - 26 degenerees - 4 a 0 resultat)');

    // Aucune ligne degeneree (X=Y) dans list_counts -- exclusion au precalcul verifiee directement.
    foreach (array_keys($expected) as $key) {
        [$start, $letter] = explode(':', $key, 2);
        Assert::true($start !== $letter, "ligne degeneree trouvee dans list_counts, aurait du etre exclue au precalcul : {$key}");
    }

    $producedKeys = [];
    $totalLinksProduced = 0;
    $exactlyOne = 0;

    foreach ($alphabet as $prefix) {
        $links = $builder->build($prefix);
        Assert::same(1, $links->queryCount, "queryCount doit rester 1 pour {$prefix}");

        $previousLetter = null;
        foreach ($links->links as $link) {
            $key = $prefix . ':' . $link['letter'];

            Assert::true(isset($expected[$key]), "cle produite par le builder absente de list_counts : {$key}");
            Assert::true($link['letter'] !== $prefix, "lettre degeneree produite par erreur, aurait du etre exclue (D-032) : {$key}");
            Assert::true(!isset($externalDuplicateSet[$key]), "cle doublon croise (D-041) produite par erreur, aurait du etre exclue : {$key}");
            Assert::same($expected[$key], $link['count'], "compte divergent pour {$key}");
            Assert::same(
                '/mots/commencant/' . strtolower($prefix) . '/avec/' . strtolower($link['letter']),
                $link['url'],
                "URL divergente pour {$key}"
            );
            Assert::true($link['count'] > 0, "R5 : aucune entree a 0 attendue ({$key})");

            if ($previousLetter !== null) {
                Assert::true($previousLetter < $link['letter'], "ordre alphabetique attendu pour {$prefix}");
            }
            $previousLetter = $link['letter'];

            Assert::true(!isset($producedKeys[$key]), "doublon de lien produit : {$key}");
            $producedKeys[$key] = true;
            $totalLinksProduced++;

            if ($link['count'] === 1) {
                $exactlyOne++;
            }
        }
    }

    // Sens 1 -> 2 : chaque lien produit par le vrai code correspond a une ligne list_counts reelle,
    // sans exception, sans doublon, sans lien mort. Volume total : les 646 lignes brutes moins les
    // 4 doublons croises D-041 = 642.
    $expectedEligibleCount = count($expected) - count($externalDuplicateSet);
    Assert::same(642, $expectedEligibleCount, 'sanity check : 642 lignes eligibles (646 brutes - 4 doublons croises famille exterieure D-041)');
    Assert::same($expectedEligibleCount, $totalLinksProduced, 'total des liens produits doit egaler les lignes list_counts start_with eligibles');
    Assert::same($expectedEligibleCount, count($producedKeys), 'aucun doublon, chaque cle eligible produite une seule fois');

    // Sens 2 -> 1 : chaque ligne list_counts start_with ELIGIBLE reelle est produite par le builder
    // depuis sa page source reelle (les 26 pages, pas un echantillon) ; chaque ligne EXCLUE (D-041),
    // a l'inverse, n'est jamais produite (deja verifie ci-dessus, reverifie ici cle par cle).
    foreach (array_keys($expected) as $key) {
        if (isset($externalDuplicateSet[$key])) {
            Assert::true(!isset($producedKeys[$key]), "ligne exclue (doublon croise D-041) produite par erreur : {$key}");
        } else {
            Assert::true(isset($producedKeys[$key]), "ligne list_counts eligible jamais produite par le builder depuis sa page source : {$key}");
        }
    }

    // Consigne produit deja connue (full-sweep original, ajustee au perimetre reellement
    // maillable) : W:J (commencant/w/avec/j) etait la seule combinaison a exactement 1 resultat --
    // DESORMAIS EXCLUE (D-041, doublon croise avec /mots/terminant/... ou /mots/commencant/...,
    // voir le rapport AFTER de cette tache pour l'adversaire exact) : 0 combinaison eligible reste
    // a exactement 1 resultat.
    Assert::same(0, $exactlyOne, 'sanity check : 0 combinaison eligible a exactement 1 resultat (W:J etait la seule, desormais exclue par D-041)');

    $exactlyOneKeys = [];
    foreach ($expected as $key => $count) {
        if ($count === 1 && !isset($externalDuplicateSet[$key])) {
            $exactlyOneKeys[] = $key;
        }
    }
    Assert::same([], $exactlyOneKeys, 'sanity check : aucune combinaison eligible a 1 resultat (W:J exclue par D-041)');
};
