<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\AvecThreeLettersLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\AvecThreeLettersLinksBuilder (palier 3 de l'ouverture en entonnoir de "avec") :
 * maillage "avec {X} {Y}" (palier 2) -> "avec {X} {Y} {Z}" (palier 3) -- lu depuis list_counts
 * (list_type 'length_with_triple', precalcule par scripts/build_explore_hub_counts.php), jamais
 * un calcul sur `terms` au runtime. Verifie par force brute sur la vraie base (lecture seule),
 * meme methodologie que AvecTwoLettersLinksBuilderTest.php -- etendue aux TROIS positions
 * possibles de la paire source dans le triplet trie stocke (contre deux pour le palier 2).
 *
 * CORRECTIF (analyse independante data-engine, 2026-08-20, demandee en parallele du meme calcul
 * cote seo-registry avant toute application registre/sitemap -- meme discipline que
 * D-037/D-038/D-039) : les paliers "avec" n'avaient jamais recu de controle de doublon de contenu
 * ENTRE PALIERS. Deux classes de doublon trouvees et exclues desormais, voir
 * App\Search\AvecThreeLettersLinksBuilder::DUPLICATE_PARENT_KEYS / SIBLING_DUPLICATE_KEYS pour le
 * detail complet de chaque regle de detection et sa verification.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new AvecThreeLettersLinksBuilder($connection);

    // --- Paire "au milieu de l'alphabet" (I, N) : doit trouver des partenaires dans les TROIS
    // --- zones possibles -- avant I (ex. A), entre I et N (ex. K), apres N (ex. S) -- preuve
    // --- directe que les trois motifs LIKE de la requete OR couvrent bien les trois positions
    // --- possibles du triplet stocke (list_key = "{len}:{lettre1}:{lettre2}:{lettre3}",
    // --- lettre1 < lettre2 < lettre3 alphabetiquement -- une seule ligne par triplet non
    // --- ordonne). ---
    $links = $builder->build(9, 'I', 'N');

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($links->links !== [], 'sanity check : des mots de 9 lettres avec I, N et une autre lettre existent');

    $partnerBeforeI = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'A'));
    $partnerBetween = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'K'));
    $partnerAfterN = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'S'));
    Assert::true(count($partnerBeforeI) === 1, 'partenaire A (avant I alphabetiquement) doit etre trouve via list_key "9:A:I:N"');
    Assert::true(count($partnerBetween) === 1, 'partenaire K (entre I et N alphabetiquement) doit etre trouve via list_key "9:I:K:N"');
    Assert::true(count($partnerAfterN) === 1, 'partenaire S (apres N alphabetiquement) doit etre trouve via list_key "9:I:N:S"');

    // --- Chaque lien verifie par force brute (instr() triple), plus l'URL canonique attendue
    // --- (toujours lettre1 < lettre2 < lettre3 dans l'URL, quelle que soit la position ou I et N
    // --- se trouvaient dans le triplet stocke). ---
    foreach ($links->links as $link) {
        $letters = ['I', 'N', $link['letter']];
        sort($letters, SORT_STRING);

        $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0');
        $stmt->execute(['I', 'N', $link['letter']]);
        $expected = (int) $stmt->fetch()['c'];

        Assert::same($expected, $link['count'], "9-lettres avec I, N et {$link['letter']} : verifie par force brute");
        Assert::same('/mots/9-lettres/avec/' . strtolower($letters[0]) . '/' . strtolower($letters[1]) . '/' . strtolower($letters[2]), $link['url']);
        Assert::true($link['letter'] !== 'I' && $link['letter'] !== 'N', 'le partenaire ne doit jamais etre une des deux lettres source');
    }

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ($links->links as $link) {
        Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
    }

    // --- Tri alphabetique par lettre partenaire. ---
    for ($i = 1; $i < count($links->links); $i++) {
        Assert::true($links->links[$i - 1]['letter'] < $links->links[$i]['letter'], 'ordre alphabetique attendu');
    }

    // --- Reciprocite TROIS SENS : le triplet {I, N, S} (verifie ci-dessus a >= 1 resultat) doit
    // --- etre retrouve depuis SES TROIS pages source palier 2 -- (I,N)->S, (I,S)->N, (N,S)->I --
    // --- avec le MEME compte et la MEME URL canonique dans les trois cas. C'est precisement le
    // --- point qui distingue le palier 3 du palier 2 (deux sens seulement) : une paire source
    // --- peut se trouver dans TROIS positions differentes du triplet trie stocke. ---
    $bruteForceINS = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, 'I') > 0 AND instr(normalized, 'N') > 0 AND instr(normalized, 'S') > 0")->fetch()['c'];
    Assert::true($bruteForceINS > 0, 'sanity check : au moins un mot de 9 lettres avec I, N et S doit exister');

    $sFromIN = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'S'));
    Assert::true(count($sFromIN) === 1, '(I,N) doit mener a S');

    $linksIS = $builder->build(9, 'I', 'S');
    $nFromIS = array_values(array_filter($linksIS->links, static fn (array $l): bool => $l['letter'] === 'N'));
    Assert::true(count($nFromIS) === 1, '(I,S) doit mener a N');

    $linksNS = $builder->build(9, 'N', 'S');
    $iFromNS = array_values(array_filter($linksNS->links, static fn (array $l): bool => $l['letter'] === 'I'));
    Assert::true(count($iFromNS) === 1, '(N,S) doit mener a I');

    Assert::same($bruteForceINS, $sFromIN[0]['count']);
    Assert::same($bruteForceINS, $nFromIS[0]['count']);
    Assert::same($bruteForceINS, $iFromNS[0]['count']);
    Assert::same('/mots/9-lettres/avec/i/n/s', $sFromIN[0]['url']);
    Assert::same('/mots/9-lettres/avec/i/n/s', $nFromIS[0]['url']);
    Assert::same('/mots/9-lettres/avec/i/n/s', $iFromNS[0]['url']);

    // --- Ordre de saisie des deux lettres source sans effet (defense dans le builder, meme si
    // --- l'appelant reel -- WordListFilters::withLetters -- les passe deja triees via ksort()). ---
    $linksReversedOrder = $builder->build(9, 'N', 'I');
    Assert::same($links->links, $linksReversedOrder->links, 'ordre de saisie (I,N) vs (N,I) sans effet sur le resultat');

    // --- Cas limite mesure (le plus petit mot possible avec trois lettres distinctes, longueur
    // --- 3) : "3-lettres/avec/a/b/e" n'a qu'un seul mot (BEA) -- ses trois pages source palier 2
    // --- (avec/a/b, avec/a/e, avec/b/e) doivent chacune y mener, dans les trois sens. ---
    $bruteForceABE3 = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 3 AND instr(normalized, 'A') > 0 AND instr(normalized, 'B') > 0 AND instr(normalized, 'E') > 0")->fetch()['c'];
    Assert::same(1, $bruteForceABE3, 'sanity check : BEA doit etre l\'unique mot de 3 lettres avec A, B et E');

    $linksAB3 = $builder->build(3, 'A', 'B');
    $eFromAB = array_values(array_filter($linksAB3->links, static fn (array $l): bool => $l['letter'] === 'E'));
    Assert::true(count($eFromAB) === 1 && $eFromAB[0]['count'] === 1, '(A,B) doit mener a E, compte = 1');
    Assert::same('/mots/3-lettres/avec/a/b/e', $eFromAB[0]['url']);

    $linksAE3 = $builder->build(3, 'A', 'E');
    $bFromAE = array_values(array_filter($linksAE3->links, static fn (array $l): bool => $l['letter'] === 'B'));
    Assert::true(count($bFromAE) === 1 && $bFromAE[0]['count'] === 1, '(A,E) doit mener a B, compte = 1');
    Assert::same('/mots/3-lettres/avec/a/b/e', $bFromAE[0]['url']);

    $linksBE3 = $builder->build(3, 'B', 'E');
    $aFromBE = array_values(array_filter($linksBE3->links, static fn (array $l): bool => $l['letter'] === 'A'));
    Assert::true(count($aFromBE) === 1 && $aFromBE[0]['count'] === 1, '(B,E) doit mener a A, compte = 1');
    Assert::same('/mots/3-lettres/avec/a/b/e', $aFromBE[0]['url']);

    // --- Lettre Z (derniere de l'alphabet) : tout triplet contenant Z stocke necessairement Z en
    // --- derniere position ("...:Z") -- exerce quasi exclusivement le premier motif LIKE
    // --- ("{longueur}:{X}:{Y}:%") quand Z est la lettre PARTENAIRE recherchee depuis une paire
    // --- de lettres qui la precedent toutes deux alphabetiquement. ---
    $linksMQ = $builder->build(9, 'M', 'Q');
    $zFromMQ = array_values(array_filter($linksMQ->links, static fn (array $l): bool => $l['letter'] === 'Z'));
    if ($zFromMQ !== []) {
        $stmt = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, 'M') > 0 AND instr(normalized, 'Q') > 0 AND instr(normalized, 'Z') > 0");
        $stmt->execute();
        Assert::same((int) $stmt->fetch()['c'], $zFromMQ[0]['count'], '9-lettres avec M, Q, Z : verifie par force brute');
        Assert::same('/mots/9-lettres/avec/m/q/z', $zFromMQ[0]['url']);
    }

    // --- Exemples cites par la tache (proof-on-paper reproduits tels quels) : "10-lettres/avec/w/x"
    // --- (1 mot) -- ses 6 partenaires A, E, N, O, S, T sont tous des doublons de contenu PARENT
    // --- (le mot unique contient deja W et X, ajouter chacune de ces 6 lettres ne retire rien) --
    // --- aucun ne doit jamais etre produit par ce builder. ---
    $linksWX10 = $builder->build(10, 'W', 'X');
    foreach (['A', 'E', 'N', 'O', 'S', 'T'] as $degenerateLetter) {
        $found = array_values(array_filter($linksWX10->links, static function (array $l) use ($degenerateLetter): bool {
            return $l['letter'] === $degenerateLetter;
        }));
        Assert::true($found === [], "10:W:X:{$degenerateLetter} est un doublon de contenu PARENT (proof-on-paper de la tache) -- ne doit jamais etre produit");
    }

    // --- Meme exemple a longueur 15 : "15-lettres/avec/w/x" (1 mot), 8 partenaires B, E, I, L, O,
    // --- R, S, U tous doublons de contenu PARENT. ---
    $linksWX15 = $builder->build(15, 'W', 'X');
    foreach (['B', 'E', 'I', 'L', 'O', 'R', 'S', 'U'] as $degenerateLetter) {
        $found = array_values(array_filter($linksWX15->links, static function (array $l) use ($degenerateLetter): bool {
            return $l['letter'] === $degenerateLetter;
        }));
        Assert::true($found === [], "15:W:X:{$degenerateLetter} est un doublon de contenu PARENT (proof-on-paper de la tache) -- ne doit jamais etre produit");
    }

    // ============================================================================================
    // Reflection sur la liste figee des 426 doublons de contenu PARENT (analyse independante
    // data-engine, 2026-08-20) -- meme pattern deja accepte sur ce projet (voir
    // AvecTwoLettersLinksBuilderTest.php, StartEndWithLinksBuilderTest.php).
    // ============================================================================================
    $reflection = new ReflectionClass(AvecThreeLettersLinksBuilder::class);
    $duplicateParentKeys = $reflection->getConstant('DUPLICATE_PARENT_KEYS');
    Assert::same(426, count($duplicateParentKeys), 'exactement 426 triplets doublons de contenu PARENT attendus');
    Assert::same(count($duplicateParentKeys), count(array_unique($duplicateParentKeys)), 'aucun doublon dans la liste figee elle-meme');
    foreach (['10:A:W:X', '10:E:W:X', '10:N:W:X', '10:O:W:X', '10:S:W:X', '10:T:W:X'] as $expectedKey) {
        Assert::true(in_array($expectedKey, $duplicateParentKeys, true), "{$expectedKey} doit figurer dans la liste figee (exemple cite par la tache)");
    }
    foreach (['15:B:W:X', '15:I:W:X', '15:L:W:X', '15:O:W:X', '15:R:W:X', '15:S:W:X', '15:U:W:X'] as $expectedKey) {
        Assert::true(in_array($expectedKey, $duplicateParentKeys, true), "{$expectedKey} doit figurer dans la liste figee (exemple cite par la tache)");
    }

    // --- Verification independante de la liste figee, recalculee depuis list_counts (0
    // --- divergence attendue dans les deux sens) : une entree length_with_triple "{N}:{X}:{Y}:{Z}"
    // --- est un doublon de contenu si et seulement si son compte est EGAL au compte
    // --- length_with_pair de l'une de ses trois sous-paires -- meme regle que
    // --- App\Search\AvecTwoLettersLinksBuilder::DUPLICATE_PARENT_KEYS, appliquee un niveau plus
    // --- haut. Verifie AUSSI la preuve de transitivite (docblock du builder) : aucun triplet ne
    // --- doit matcher une lettre SEULE (length_with) sans matcher AUSSI l'une de ses trois
    // --- sous-paires -- 0 cas attendu, la transitivite palier1/palier3 est mathematiquement
    // --- subsumee par la comparaison palier2/palier3 (MOTS(triplet) subset MOTS(paire) subset
    // --- MOTS(lettre seule), egalite aux extremes force l'egalite au milieu).
    // ============================================================================================
    $pairTotals = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_pair'") as $row) {
        $pairTotals[(string) $row['list_key']] = (int) $row['count'];
    }
    $singleTotalsForTriple = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with'") as $row) {
        $singleTotalsForTriple[(string) $row['list_key']] = (int) $row['count'];
    }

    $computedParentDuplicatesTriple = [];
    $transitiveViolations = 0;
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_triple'") as $row) {
        $key = (string) $row['list_key'];
        [$length, $x, $y, $z] = explode(':', $key, 4);
        $count = (int) $row['count'];

        $subpairs = [$length . ':' . $x . ':' . $y, $length . ':' . $x . ':' . $z, $length . ':' . $y . ':' . $z];
        $matchesPair = false;
        foreach ($subpairs as $sp) {
            if (($pairTotals[$sp] ?? null) === $count) {
                $matchesPair = true;
                break;
            }
        }

        if ($matchesPair) {
            $computedParentDuplicatesTriple[] = $key;
        }

        $subsingles = [$length . ':' . $x, $length . ':' . $y, $length . ':' . $z];
        $matchesSingle = false;
        foreach ($subsingles as $ss) {
            if (($singleTotalsForTriple[$ss] ?? null) === $count) {
                $matchesSingle = true;
                break;
            }
        }
        if ($matchesSingle && !$matchesPair) {
            $transitiveViolations++;
        }
    }

    Assert::same(0, $transitiveViolations, 'preuve de transitivite : aucun triplet ne doit matcher une lettre seule sans matcher aussi l\'une de ses sous-paires');

    sort($computedParentDuplicatesTriple);
    $sortedDeclaredParentTriple = $duplicateParentKeys;
    sort($sortedDeclaredParentTriple);
    Assert::same($sortedDeclaredParentTriple, $computedParentDuplicatesTriple, 'DUPLICATE_PARENT_KEYS doit etre identique aux doublons recalcules depuis list_counts (0 divergence, 28 827 lignes, pas un echantillon)');

    // ============================================================================================
    // Doublons de contenu entre pages SOEURS du palier 3 (analyse independante data-engine,
    // 2026-08-20) -- 234 cles exclues (189 groupes), verifiees de facon EXHAUSTIVE (pas un
    // echantillon) sur les 14 longueurs reelles, meme discipline que StartEndWithLinksBuilderTest.
    // ============================================================================================
    $siblingDuplicateKeys = $reflection->getConstant('SIBLING_DUPLICATE_KEYS');
    Assert::same(234, count($siblingDuplicateKeys), 'exactement 234 cles doublons soeurs attendues (189 groupes)');
    Assert::same(count($siblingDuplicateKeys), count(array_unique($siblingDuplicateKeys)), 'aucun doublon dans la liste figee elle-meme (une cle n\'appartient qu\'a un seul groupe)');
    Assert::true(in_array('10:G:J:Y', $siblingDuplicateKeys, true), '10:G:J:Y doit figurer dans la liste figee (exemple cite dans le docblock du builder)');
    Assert::true(in_array('10:G:W:Y', $siblingDuplicateKeys, true), '10:G:W:Y doit figurer dans la liste figee (meme groupe que 10:G:J:Y)');

    $parentDuplicateSetTriple = array_fill_keys($duplicateParentKeys, true);
    Assert::same(0, count(array_intersect_key(array_fill_keys($siblingDuplicateKeys, true), $parentDuplicateSetTriple)), 'SIBLING_DUPLICATE_KEYS et DUPLICATE_PARENT_KEYS doivent rester deux ensembles disjoints par construction');

    $survivorsByLengthTriple = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_triple'") as $row) {
        $key = (string) $row['list_key'];
        if (isset($parentDuplicateSetTriple[$key])) {
            continue;
        }
        [$length] = explode(':', $key, 2);
        $survivorsByLengthTriple[(int) $length][$key] = (int) $row['count'];
    }

    $computedSiblingExcludedTriple = [];
    $candidateGroupsCheckedTriple = 0;
    $candidateMembersCheckedTriple = 0;
    $verifiedPairwiseByCounting = 0;

    foreach ($survivorsByLengthTriple as $length => $keys) {
        $byCount = [];
        foreach ($keys as $key => $count) {
            $byCount[$count][] = $key;
        }

        foreach ($byCount as $count => $candidateKeys) {
            if (count($candidateKeys) < 2) {
                continue;
            }
            $candidateGroupsCheckedTriple++;
            $candidateMembersCheckedTriple += count($candidateKeys);

            $byFingerprint = [];
            foreach ($candidateKeys as $key) {
                [, $x, $y, $z] = explode(':', $key, 4);
                $stmt = $pdo->prepare(
                    'SELECT GROUP_CONCAT(normalized, "|") FROM (SELECT normalized FROM terms WHERE length = ?'
                    . ' AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 ORDER BY normalized)'
                );
                $stmt->execute([$length, $x, $y, $z]);
                $fingerprint = (string) $stmt->fetchColumn();
                $byFingerprint[$fingerprint][] = $key;
            }

            foreach ($byFingerprint as $fingerprint => $group) {
                if (count($group) < 2) {
                    continue;
                }
                sort($group, SORT_STRING);

                // Methode 2, INDEPENDANTE du GROUP_CONCAT ci-dessus (meme principe que
                // App\Search\StartEndWithLinksBuilder::CROSS_DUPLICATE_LENGTH_KEYS methode 2,
                // D-039) : pour chaque paire de cles du groupe, countA === countB === countA-ET-B
                // === count du groupe prouve une egalite d'ensemble sans jamais comparer de
                // tableau.
                $m = count($group);
                for ($i = 0; $i < $m; $i++) {
                    for ($j = $i + 1; $j < $m; $j++) {
                        [, $ax, $ay, $az] = explode(':', $group[$i], 4);
                        [, $bx, $by, $bz] = explode(':', $group[$j], 4);

                        $stmtA = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = ? AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0');
                        $stmtA->execute([$length, $ax, $ay, $az]);
                        $cA = (int) $stmtA->fetch()['c'];

                        $stmtB = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = ? AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0');
                        $stmtB->execute([$length, $bx, $by, $bz]);
                        $cB = (int) $stmtB->fetch()['c'];

                        $stmtAB = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = ? AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0');
                        $stmtAB->execute([$length, $ax, $ay, $az, $bx, $by, $bz]);
                        $cAB = (int) $stmtAB->fetch()['c'];

                        Assert::true($cA === $count && $cB === $count && $cAB === $count, "verification par comptage independant divergente pour {$group[$i]} vs {$group[$j]}");
                        $verifiedPairwiseByCounting++;
                    }
                }

                foreach (array_slice($group, 1) as $excludedKey) {
                    $computedSiblingExcludedTriple[] = $excludedKey;
                }
            }
        }
    }

    // Diagnostic : confirme que le pipeline a reellement examine des candidats (pas une liste vide
    // par construction).
    Assert::same(3496, $candidateGroupsCheckedTriple, 'sanity check : 3 496 groupes candidats (meme longueur+compte, >= 2 membres) attendus sur le palier 3');
    Assert::same(19049, $candidateMembersCheckedTriple, 'sanity check : 19 049 triplets candidats au total attendus');
    Assert::same(290, $verifiedPairwiseByCounting, 'sanity check : 290 paires de cles verifiees par comptage independant (methode 2)');

    sort($computedSiblingExcludedTriple);
    $sortedDeclaredSiblingTriple = $siblingDuplicateKeys;
    sort($sortedDeclaredSiblingTriple);
    Assert::same($sortedDeclaredSiblingTriple, $computedSiblingExcludedTriple, 'SIBLING_DUPLICATE_KEYS doit etre identique aux doublons soeurs recalcules depuis `terms` (0 divergence, pas un echantillon)');

    // ============================================================================================
    // Verification EXHAUSTIVE du maillage sur les 4 276 pages sources reelles du palier 2
    // (list_type 'length_with_pair', D-030) -- pas un echantillon, meme discipline que tous les
    // paliers precedents. Compare le vrai code (AvecThreeLettersLinksBuilder, via list_counts) au
    // recalcul independant depuis list_counts lui-meme, dans les DEUX sens.
    // ============================================================================================
    $pairAnchorsStatement = $pdo->query("SELECT list_key FROM list_counts WHERE list_type = 'length_with_pair'");
    $pairAnchors = [];
    foreach ($pairAnchorsStatement as $row) {
        $pairAnchors[] = explode(':', (string) $row['list_key'], 3);
    }
    Assert::same(4276, count($pairAnchors), 'sanity check : 4 276 pages palier 2 reelles (D-030)');

    $expectedTriple = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_triple'") as $row) {
        $expectedTriple[(string) $row['list_key']] = (int) $row['count'];
    }
    Assert::same(28827, count($expectedTriple), 'sanity check : 28 827 lignes length_with_triple reelles (D-031)');

    // ========================================================================================
    // Doublons de contenu CROISES avec une famille EXTERIEURE (D-041, garde-fou structurel
    // demande par le constat C-4 du 4e audit consolide, docs/DECISIONS.md D-040) -- balayage
    // GENERIQUE de tout le registre (scripts/check_combinatorial_duplicates.php, balayage du
    // 2026-08-21 : 1 656 groupes, 2 089 pages en exces). Meme pattern de verification (reflection
    // + disjonction) que les deux blocs precedents.
    // ========================================================================================
    $externalDuplicateKeysTriple = $reflection->getConstant('EXTERNAL_DUPLICATE_KEYS');
    Assert::same(666, count($externalDuplicateKeysTriple), 'exactement 666 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');
    Assert::same(count($externalDuplicateKeysTriple), count(array_unique($externalDuplicateKeysTriple)), 'aucun doublon dans la liste figee elle-meme');
    $externalDuplicateSetTriple = array_fill_keys($externalDuplicateKeysTriple, true);
    Assert::same(0, count(array_intersect_key($externalDuplicateSetTriple, $parentDuplicateSetTriple)), 'EXTERNAL_DUPLICATE_KEYS et DUPLICATE_PARENT_KEYS doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($externalDuplicateSetTriple, array_fill_keys($siblingDuplicateKeys, true))), 'EXTERNAL_DUPLICATE_KEYS et SIBLING_DUPLICATE_KEYS doivent rester deux ensembles disjoints');

    $excludedKeysTriple = $parentDuplicateSetTriple + array_fill_keys($siblingDuplicateKeys, true) + $externalDuplicateSetTriple;

    $producedKeysTriple = [];
    $totalLinksProducedTriple = 0;
    $exactlyOneTriple = 0;

    foreach ($pairAnchors as [$length, $x, $y]) {
        $anchorLinks = $builder->build((int) $length, $x, $y);
        Assert::same(1, $anchorLinks->queryCount, "queryCount doit rester 1 pour {$length}:{$x}:{$y}");

        $previousLetter = null;
        foreach ($anchorLinks->links as $link) {
            $triple = [$x, $y, $link['letter']];
            sort($triple, SORT_STRING);
            $key = $length . ':' . $triple[0] . ':' . $triple[1] . ':' . $triple[2];

            Assert::true(isset($expectedTriple[$key]), "cle produite par le builder absente de list_counts : {$key}");
            Assert::true(!isset($excludedKeysTriple[$key]), "cle doublon de contenu produite par erreur, aurait du etre exclue : {$key}");
            Assert::same($expectedTriple[$key], $link['count'], "compte divergent pour {$key}");
            Assert::same(
                '/mots/' . $length . '-lettres/avec/' . strtolower($triple[0]) . '/' . strtolower($triple[1]) . '/' . strtolower($triple[2]),
                $link['url'],
                "URL divergente pour {$key}"
            );
            Assert::true($link['count'] > 0, "R5 : aucune entree a 0 attendue ({$key})");

            if ($previousLetter !== null) {
                Assert::true($previousLetter < $link['letter'], "ordre alphabetique attendu pour {$length}:{$x}:{$y}");
            }
            $previousLetter = $link['letter'];

            if (!isset($producedKeysTriple[$key])) {
                $producedKeysTriple[$key] = true;
                $totalLinksProducedTriple++;
                if ($link['count'] === 1) {
                    $exactlyOneTriple++;
                }
            }
        }
    }

    // Sens 1 -> 2 : chaque lien produit par le vrai code correspond a une ligne list_counts
    // eligible reelle (ni doublon de contenu PARENT ni doublon de contenu SOEUR), sans exception,
    // sans lien mort -- deja verifie ligne par ligne ci-dessus. Volume total : exactement les
    // lignes eligibles, ni plus ni moins (chaque triplet compte UNE FOIS bien qu'accessible depuis
    // jusqu'a 3 ancres palier 2 distinctes).
    $expectedEligibleCountTriple = count($expectedTriple) - count($excludedKeysTriple);
    Assert::same(27501, $expectedEligibleCountTriple, 'sanity check : 27 501 lignes eligibles (28 827 brutes - 426 doublons de contenu PARENT - 234 doublons de contenu SOEUR - 666 doublons croises famille exterieure D-041)');
    Assert::same($expectedEligibleCountTriple, $totalLinksProducedTriple, 'total des triplets distincts produits doit egaler les lignes list_counts length_with_triple eligibles');

    // Sens 2 -> 1 : chaque ligne list_counts length_with_triple eligible reelle est produite par le
    // builder depuis AU MOINS UNE de ses (jusqu'a 3) pages source reelles ; chaque ligne EXCLUE, a
    // l'inverse, n'est JAMAIS produite depuis AUCUNE de ses ancres.
    foreach (array_keys($expectedTriple) as $key) {
        if (isset($excludedKeysTriple[$key])) {
            Assert::true(!isset($producedKeysTriple[$key]), "ligne exclue (doublon de contenu parent/soeur) produite par erreur : {$key}");
        } else {
            Assert::true(isset($producedKeysTriple[$key]), "ligne list_counts eligible jamais produite par le builder depuis aucune de ses pages source : {$key}");
        }
    }

    // Consigne produit deja connue (D-031 : 1 682 combinaisons a exactement 1 resultat parmi les
    // 28 827 lignes precalculees brutes), AJUSTEE apres exclusion des doublons de contenu PARENT,
    // SOEUR, PUIS croises famille exterieure (D-041, 643 des 666 doublons croises ont exactement 1
    // resultat) : 740 restantes, GARDEES (meme consigne produit que tous les paliers "avec"
    // precedents).
    $expectedExactlyOneEligibleTriple = 0;
    foreach ($expectedTriple as $key => $count) {
        if ($count === 1 && !isset($excludedKeysTriple[$key])) {
            $expectedExactlyOneEligibleTriple++;
        }
    }
    Assert::same(740, $expectedExactlyOneEligibleTriple, 'sanity check : 740 combinaisons eligibles a exactement 1 resultat (1 682 brutes - 299 exclues par les deux filtres precedents - 643 doublons croises famille exterieure)');
    Assert::same($expectedExactlyOneEligibleTriple, $exactlyOneTriple, 'consigne produit deja connue (GARDEES, meme consigne que tous les paliers "avec" precedents)');
};
