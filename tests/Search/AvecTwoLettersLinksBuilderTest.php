<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\AvecTwoLettersLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\AvecTwoLettersLinksBuilder (palier 2 de l'ouverture en entonnoir de "avec") :
 * maillage "avec {X}" (palier 1) -> "avec {X} {Y}" (palier 2) -- lu depuis list_counts
 * (list_type 'length_with_pair', precalcule par scripts/build_explore_hub_counts.php), jamais un
 * calcul sur `terms` au runtime. Verifie par force brute sur la vraie base (lecture seule),
 * meme methodologie que PositionLinksBuilderTest.php / AvecSansLengthLinksBuilderTest.php.
 *
 * CORRECTIF (analyse independante data-engine, 2026-08-20, demandee en parallele du meme calcul
 * cote seo-registry avant toute application registre/sitemap -- meme discipline que
 * D-037/D-038/D-039) : les paliers "avec" (D-029/D-030/D-031) n'avaient jamais recu de controle de
 * doublon de contenu ENTRE PALIERS (contrairement aux axes commencant/terminant/avec, deja corriges
 * trois fois). Deux classes de doublon trouvees et exclues desormais par ce builder, voir
 * App\Search\AvecTwoLettersLinksBuilder::DUPLICATE_PARENT_KEYS / SIBLING_DUPLICATE_KEYS pour le
 * detail complet de chaque regle de detection et sa verification.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new AvecTwoLettersLinksBuilder($connection);

    // --- Lettre "au milieu de l'alphabet" : doit trouver des partenaires DES DEUX cotes
    // --- (ex. A < Q ET Q < Z), preuve directe que la requete OR sur les deux motifs LIKE
    // --- couvre bien les deux sens de la paire stockee (list_key = "{len}:{lettre1}:{lettre2}",
    // --- lettre1 < lettre2 alphabetiquement -- une seule ligne par paire non ordonnee). ---
    $links = $builder->build(9, 'Q');

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts');
    Assert::true($links->links !== [], 'sanity check : des mots de 9 lettres avec Q et une autre lettre existent');

    $partnerBeforeQ = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'A'));
    $partnerAfterQ = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'U'));
    Assert::true(count($partnerBeforeQ) === 1, 'partenaire A (avant Q alphabetiquement) doit etre trouve via list_key "9:A:Q"');
    Assert::true(count($partnerAfterQ) === 1, 'partenaire U (apres Q alphabetiquement) doit etre trouve via list_key "9:Q:U"');

    // --- Chaque lien verifie par force brute (instr() double), plus l'URL canonique attendue
    // --- (toujours lettre1 < lettre2 dans l'URL, quel que soit le cote ou Q se trouvait dans la
    // --- cle stockee). ---
    foreach ($links->links as $link) {
        $letters = ['Q', $link['letter']];
        sort($letters, SORT_STRING);

        $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0');
        $stmt->execute(['Q', $link['letter']]);
        $expected = (int) $stmt->fetch()['c'];

        Assert::same($expected, $link['count'], "9-lettres avec Q et {$link['letter']} : verifie par force brute");
        Assert::same('/mots/9-lettres/avec/' . strtolower($letters[0]) . '/' . strtolower($letters[1]), $link['url']);
    }

    // --- R5 (registre SEO, jamais de lien mort) : aucune entree a 0. ---
    foreach ($links->links as $link) {
        Assert::true($link['count'] > 0, 'aucune entree a 0 attendue');
    }

    // --- Tri alphabetique par lettre partenaire. ---
    for ($i = 1; $i < count($links->links); $i++) {
        Assert::true($links->links[$i - 1]['letter'] < $links->links[$i]['letter'], 'ordre alphabetique attendu');
    }

    // --- Reciprocite : si "avec Q" mene a "avec Q R", alors "avec R" doit aussi mener a
    // --- "avec Q R" (meme paire, meme compte, meme URL), quel que soit le cote de la paire
    // --- ou chaque lettre se trouve dans list_counts. ---
    $linksR = $builder->build(9, 'R');
    $qFromR = array_values(array_filter($linksR->links, static fn (array $l): bool => $l['letter'] === 'Q'));
    $rFromQ = array_values(array_filter($links->links, static fn (array $l): bool => $l['letter'] === 'R'));
    Assert::same(count($qFromR), count($rFromQ), 'reciprocite Q<->R : presence identique dans les deux sens');
    if ($qFromR !== [] && $rFromQ !== []) {
        Assert::same($qFromR[0]['count'], $rFromQ[0]['count'], 'reciprocite Q<->R : meme compte dans les deux sens');
        Assert::same($qFromR[0]['url'], $rFromQ[0]['url'], 'reciprocite Q<->R : meme URL canonique dans les deux sens');
    }

    // --- Cas limite mesure (palier 1) : "2-lettres/avec/w" n'a qu'un seul mot (WU). Son unique
    // --- partenaire palier 2 possible serait U (paire "2:U:W") -- mais TOUS les mots de 2 lettres
    // --- avec W contiennent deja U (WU est le seul), donc "avec/u/w" est un doublon de CONTENU
    // --- strict de la page parente "avec/w" (App\Search\AvecTwoLettersLinksBuilder::
    // --- DUPLICATE_PARENT_KEYS, cle "2:U:W") -- ce builder ne doit plus jamais le produire. ---
    $linksW2 = $builder->build(2, 'W');
    Assert::same([], $linksW2->links, '2:U:W est un doublon de contenu (WU est le seul mot, "avec/u" ne retire rien) -- ne doit jamais etre produit');

    $linksU2 = $builder->build(2, 'U');
    $wFromU2 = array_values(array_filter($linksU2->links, static fn (array $l): bool => $l['letter'] === 'W'));
    Assert::true($wFromU2 === [], 'reciprocite : "avec U" ne doit pas non plus produire "avec U W" (meme doublon de contenu, meme cle canonique)');

    $rawCountUW = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'length_with_pair' AND list_key = '2:U:W'")->fetch()['count'];
    $rawCountW = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type = 'length_with' AND list_key = '2:W'")->fetch()['count'];
    Assert::same(1, $rawCountW, 'sanity check : W (2 lettres) ne contient bien que WU (1 mot)');
    Assert::same($rawCountW, $rawCountUW, 'sanity check : 2:U:W existe dans list_counts (precalcul brut inchange) et vaut le meme total que 2:W -- exclusion cote builder, pas cote precalcul');

    // --- Meme cas symetrique verifie sur ZA (2:A:Z, "avec/z" ne contient que ZA, qui contient
    // --- deja A). ---
    $linksZ2 = $builder->build(2, 'Z');
    Assert::same([], $linksZ2->links, '2:A:Z est un doublon de contenu (ZA est le seul mot, "avec/a" ne retire rien) -- ne doit jamais etre produit');

    // --- Lettre Z (longueur 9, derniere de l'alphabet) : TOUS ses partenaires sont stockes avec Z
    // --- en position "lettre2" de la cle (ex. "9:M:Z", jamais "9:Z:...") -- exerce quasi
    // --- exclusivement le second motif LIKE ("{longueur}:%:{lettre}") de la requete OR,
    // --- jamais teste par les cas Q/R ci-dessus (Q et R ont des partenaires des deux cotes). ---
    $linksZ9 = $builder->build(9, 'Z');
    Assert::true($linksZ9->links !== [], 'sanity check : des mots de 9 lettres avec Z et une autre lettre existent');
    foreach ($linksZ9->links as $link) {
        Assert::true($link['letter'] !== 'Z', 'le partenaire ne doit jamais etre la lettre source elle-meme');
        $stmt = $pdo->prepare('SELECT COUNT(*) c FROM terms WHERE length = 9 AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0');
        $stmt->execute(['Z', $link['letter']]);
        Assert::same((int) $stmt->fetch()['c'], $link['count'], "9-lettres avec Z et {$link['letter']} : verifie par force brute");
        Assert::same('/mots/9-lettres/avec/' . strtolower($link['letter']) . '/z', $link['url'], 'Z est toujours alphabetiquement apres tout partenaire reel');
    }

    // ============================================================================================
    // Reflection sur la liste figee des 4 doublons de contenu PARENT (analyse independante
    // data-engine, 2026-08-20) -- meme pattern deja accepte sur ce projet pour verifier un detail
    // d'implementation prive (voir LengthLinksBuilderTest.php, DUPLICATE_START_END_KEYS).
    // ============================================================================================
    $reflection = new ReflectionClass(AvecTwoLettersLinksBuilder::class);
    $duplicateParentKeys = $reflection->getConstant('DUPLICATE_PARENT_KEYS');
    Assert::same(4, count($duplicateParentKeys), 'exactement 4 paires doublons de contenu PARENT attendues');
    Assert::same(count($duplicateParentKeys), count(array_unique($duplicateParentKeys)), 'aucun doublon dans la liste figee elle-meme');
    Assert::true(in_array('2:A:Z', $duplicateParentKeys, true), '2:A:Z doit figurer dans la liste figee (ZA)');
    Assert::true(in_array('2:U:W', $duplicateParentKeys, true), '2:U:W doit figurer dans la liste figee (WU)');
    Assert::true(in_array('14:Q:U', $duplicateParentKeys, true), '14:Q:U doit figurer dans la liste figee (regle orthographique Q toujours suivi de U a cette longueur)');
    Assert::true(in_array('15:Q:U', $duplicateParentKeys, true), '15:Q:U doit figurer dans la liste figee (meme regle, longueur 15)');

    // --- Verification independante de la liste figee, recalculee depuis list_counts (0
    // --- divergence attendue dans les deux sens) : une entree length_with_pair "{N}:{X}:{Y}" est
    // --- un doublon de contenu si et seulement si son compte est EGAL au compte length_with "{N}:{X}"
    // --- OU "{N}:{Y}" correspondant -- meme regle que DUPLICATE_START_END_KEYS/DUPLICATE_CONTENT_KEYS
    // --- ailleurs dans ce projet. ---
    $singleTotals = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with'") as $row) {
        $singleTotals[(string) $row['list_key']] = (int) $row['count'];
    }

    $computedParentDuplicates = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_pair'") as $row) {
        $key = (string) $row['list_key'];
        [$length, $x, $y] = explode(':', $key, 3);
        $count = (int) $row['count'];

        $sx = $singleTotals[$length . ':' . $x] ?? null;
        $sy = $singleTotals[$length . ':' . $y] ?? null;

        if ($sx === $count || $sy === $count) {
            $computedParentDuplicates[] = $key;
        }
    }

    sort($computedParentDuplicates);
    $sortedDeclaredParent = $duplicateParentKeys;
    sort($sortedDeclaredParent);
    Assert::same($sortedDeclaredParent, $computedParentDuplicates, 'DUPLICATE_PARENT_KEYS doit etre identique aux doublons recalcules depuis list_counts (0 divergence, 4 276 lignes, pas un echantillon)');

    // ============================================================================================
    // Doublons de contenu entre pages SOEURS du palier 2 (analyse independante data-engine,
    // 2026-08-20) -- liste figee volontairement vide, verifiee de facon EXHAUSTIVE (pas un
    // echantillon) sur les 14 longueurs reelles, meme discipline que StartEndWithLinksBuilderTest.
    // ============================================================================================
    $siblingDuplicateKeys = $reflection->getConstant('SIBLING_DUPLICATE_KEYS');
    Assert::same(0, count($siblingDuplicateKeys), 'liste figee vide attendue sur le palier 2 (0 collision reelle trouvee, contrairement au palier 3)');

    // Regroupement par (longueur, count) parmi les paires survivantes du filtre parent -- condition
    // NECESSAIRE (deux ensembles egaux ont necessairement le meme compte) mais pas suffisante,
    // verifiee ensuite par empreinte SQL GROUP_CONCAT (liste triee des mots, comparaison de chaines
    // completes, aucun hash, aucune collision possible).
    $parentDuplicateSet = array_fill_keys($duplicateParentKeys, true);
    $survivorsByLength = [];
    foreach ($pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_pair'") as $row) {
        $key = (string) $row['list_key'];
        if (isset($parentDuplicateSet[$key])) {
            continue;
        }
        [$length] = explode(':', $key, 2);
        $survivorsByLength[(int) $length][$key] = (int) $row['count'];
    }

    $computedSiblingExcludedPair = [];
    $candidateGroupsChecked = 0;
    $candidateMembersChecked = 0;

    foreach ($survivorsByLength as $length => $keys) {
        $byCount = [];
        foreach ($keys as $key => $count) {
            $byCount[$count][] = $key;
        }

        foreach ($byCount as $count => $candidateKeys) {
            if (count($candidateKeys) < 2) {
                continue;
            }
            $candidateGroupsChecked++;
            $candidateMembersChecked += count($candidateKeys);

            $byFingerprint = [];
            foreach ($candidateKeys as $key) {
                [, $x, $y] = explode(':', $key, 3);
                $stmt = $pdo->prepare(
                    'SELECT GROUP_CONCAT(normalized, "|") FROM (SELECT normalized FROM terms WHERE length = ?'
                    . ' AND instr(normalized, ?) > 0 AND instr(normalized, ?) > 0 ORDER BY normalized)'
                );
                $stmt->execute([$length, $x, $y]);
                $fingerprint = (string) $stmt->fetchColumn();
                $byFingerprint[$fingerprint][] = $key;
            }

            foreach ($byFingerprint as $fingerprint => $group) {
                if (count($group) < 2) {
                    continue;
                }
                sort($group, SORT_STRING);
                foreach (array_slice($group, 1) as $excludedKey) {
                    $computedSiblingExcludedPair[] = $excludedKey;
                }
            }
        }
    }

    // Diagnostic : confirme que le pipeline a reellement examine des candidats (pas une liste vide
    // par construction) -- 286 groupes / 1 064 membres attendus, tous verifies par empreinte, tous
    // reellement distincts (0 collision reelle).
    Assert::same(286, $candidateGroupsChecked, 'sanity check : 286 groupes candidats (meme longueur+compte, >= 2 membres) attendus sur le palier 2');
    Assert::same(1064, $candidateMembersChecked, 'sanity check : 1 064 paires candidates au total attendues');
    Assert::same([], $computedSiblingExcludedPair, 'aucune collision reelle attendue apres verification par empreinte (0 divergence avec la liste figee vide)');

    // ============================================================================================
    // Verification EXHAUSTIVE du maillage sur les 364 pages sources reelles (14 longueurs x 26
    // lettres, list_type 'length_with', D-029) -- pas un echantillon, meme discipline que tous les
    // paliers precedents. Compare le vrai code (AvecTwoLettersLinksBuilder, via list_counts) au
    // recalcul independant depuis list_counts lui-meme, dans les DEUX sens.
    // ============================================================================================
    $anchorsStatement = $pdo->query("SELECT list_key FROM list_counts WHERE list_type = 'length_with'");
    $anchors = [];
    foreach ($anchorsStatement as $row) {
        $anchors[] = explode(':', (string) $row['list_key'], 2);
    }
    Assert::same(364, count($anchors), 'sanity check : 364 pages palier 1 reelles (14 longueurs x 26 lettres, D-029)');

    $expectedStatement = $pdo->query("SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_pair'");
    $expected = [];
    foreach ($expectedStatement as $row) {
        $expected[(string) $row['list_key']] = (int) $row['count'];
    }
    Assert::same(4276, count($expected), 'sanity check : 4 276 lignes length_with_pair reelles (D-030)');

    // ========================================================================================
    // Doublons de contenu CROISES avec une famille EXTERIEURE (D-041, garde-fou structurel
    // demande par le constat C-4 du 4e audit consolide, docs/DECISIONS.md D-040) -- balayage
    // GENERIQUE de tout le registre (scripts/check_combinatorial_duplicates.php, balayage du
    // 2026-08-21 : 1 656 groupes, 2 089 pages en exces). Meme pattern de verification (reflection
    // + disjonction) que DUPLICATE_PARENT_KEYS/SIBLING_DUPLICATE_KEYS ci-dessus.
    // ========================================================================================
    $externalDuplicateKeys = $reflection->getConstant('EXTERNAL_DUPLICATE_KEYS');
    Assert::same(138, count($externalDuplicateKeys), 'exactement 138 doublons croises avec une famille exterieure attendus (D-041, balayage du 2026-08-21)');
    Assert::same(count($externalDuplicateKeys), count(array_unique($externalDuplicateKeys)), 'aucun doublon dans la liste figee elle-meme');
    $externalDuplicateSet = array_fill_keys($externalDuplicateKeys, true);
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, $parentDuplicateSet)), 'EXTERNAL_DUPLICATE_KEYS et DUPLICATE_PARENT_KEYS doivent rester deux ensembles disjoints');
    Assert::same(0, count(array_intersect_key($externalDuplicateSet, array_fill_keys($siblingDuplicateKeys, true))), 'EXTERNAL_DUPLICATE_KEYS et SIBLING_DUPLICATE_KEYS doivent rester deux ensembles disjoints');

    $excludedKeys = $parentDuplicateSet + $externalDuplicateSet; // SIBLING_DUPLICATE_KEYS vide, rien a fusionner de plus

    $producedKeys = [];
    $totalLinksProduced = 0;
    $exactlyOne = 0;

    foreach ($anchors as [$length, $letter]) {
        $anchorLinks = $builder->build((int) $length, $letter);
        Assert::same(1, $anchorLinks->queryCount, "queryCount doit rester 1 pour {$length}:{$letter}");

        $previousLetter = null;
        foreach ($anchorLinks->links as $link) {
            $pair = [$letter, $link['letter']];
            sort($pair, SORT_STRING);
            $key = $length . ':' . $pair[0] . ':' . $pair[1];

            Assert::true(isset($expected[$key]), "cle produite par le builder absente de list_counts : {$key}");
            Assert::true(!isset($excludedKeys[$key]), "cle doublon de contenu produite par erreur, aurait du etre exclue : {$key}");
            Assert::same($expected[$key], $link['count'], "compte divergent pour {$key}");
            Assert::same(
                '/mots/' . $length . '-lettres/avec/' . strtolower($pair[0]) . '/' . strtolower($pair[1]),
                $link['url'],
                "URL divergente pour {$key}"
            );
            Assert::true($link['count'] > 0, "R5 : aucune entree a 0 attendue ({$key})");

            if ($previousLetter !== null) {
                Assert::true($previousLetter < $link['letter'], "ordre alphabetique attendu pour {$length}:{$letter}");
            }
            $previousLetter = $link['letter'];

            if (!isset($producedKeys[$key])) {
                $producedKeys[$key] = true;
                $totalLinksProduced++;
                if ($link['count'] === 1) {
                    $exactlyOne++;
                }
            }
        }
    }

    // Sens 1 -> 2 : chaque lien produit par le vrai code correspond a une ligne list_counts
    // eligible reelle (ni doublon de contenu PARENT ni doublon de contenu SOEUR), sans exception,
    // sans lien mort -- deja verifie ligne par ligne ci-dessus. Volume total : exactement les
    // lignes eligibles, ni plus ni moins (chaque paire comptee UNE FOIS bien qu'accessible depuis
    // ses deux ancres palier 1).
    $expectedEligibleCount = count($expected) - count($excludedKeys);
    Assert::same(4134, $expectedEligibleCount, 'sanity check : 4 134 lignes eligibles (4 276 brutes - 4 doublons de contenu PARENT - 0 doublon de contenu SOEUR - 138 doublons croises famille exterieure D-041)');
    Assert::same($expectedEligibleCount, $totalLinksProduced, 'total des paires distinctes produites doit egaler les lignes list_counts length_with_pair eligibles');

    // Sens 2 -> 1 : chaque ligne list_counts length_with_pair eligible reelle est produite par le
    // builder depuis AU MOINS UNE de ses deux pages source reelles ; chaque ligne EXCLUE (doublon
    // de contenu PARENT), a l'inverse, n'est JAMAIS produite depuis AUCUNE de ses deux ancres.
    foreach (array_keys($expected) as $key) {
        if (isset($excludedKeys[$key])) {
            Assert::true(!isset($producedKeys[$key]), "ligne exclue (doublon de contenu parent) produite par erreur : {$key}");
        } else {
            Assert::true(isset($producedKeys[$key]), "ligne list_counts eligible jamais produite par le builder depuis aucune de ses pages source : {$key}");
        }
    }

    // Consigne produit deja connue (D-030 : 132 combinaisons a exactement 1 resultat parmi les
    // 4 276 lignes precalculees brutes), AJUSTEE apres exclusion des 4 doublons de contenu PARENT
    // (dont 2 -- 2:A:Z et 2:U:W -- valent exactement 1 : 132 - 2 = 130), PUIS des 138 doublons
    // croises famille exterieure (D-041, dont 122 valent exactement 1) : 130 - 122 = 8 restantes,
    // GARDEES (meme consigne produit que tous les paliers "avec" precedents).
    $expectedExactlyOneEligible = 0;
    foreach ($expected as $key => $count) {
        if ($count === 1 && !isset($excludedKeys[$key])) {
            $expectedExactlyOneEligible++;
        }
    }
    Assert::same(8, $expectedExactlyOneEligible, 'sanity check : 8 combinaisons eligibles a exactement 1 resultat (132 brutes - 2 doublons de contenu parent a 1 resultat - 122 doublons croises famille exterieure a 1 resultat)');
    Assert::same($expectedExactlyOneEligible, $exactlyOne, 'consigne produit deja connue (GARDEES, meme consigne que tous les paliers "avec" precedents)');
};
