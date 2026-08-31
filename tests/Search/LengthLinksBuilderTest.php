<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\LengthLinksBuilder;
use Tests\Support\Assert;

/**
 * App\Search\LengthLinksBuilder sur le dépôt ES : maillage interne d'une page
 * "/palabras/{N}-letras" (déjà indexée, word_list_length, ES-011 I-1) vers ses combinaisons
 * longueur+lettre -- lu depuis list_counts (scripts/build_explore_hub_counts.php).
 *
 * byStart ('length_start', 1 caractère) et byEnd ('length_end') alimentent la famille DÉJÀ
 * INDEXÉE App\Seo\Family::WORD_LIST_COMBINED (ES-016/ES-018) -- l'ancien commentaire de ce
 * fichier ("ces types alimentent des familles SEO pas encore ouvertes à l'indexation") est
 * FAUX depuis ES-018 : ce sont des maillages réellement en production, à vérifier comme tels.
 * byWith/byPosition/byStartEnd ('length_with'/'length_with_position'/'length_start_end') sont
 * peuplés (ES-022) mais n'ont, eux, aucune famille ouverte à ce jour -- sanity check seulement.
 *
 * CORRECTIF C-1 (audits croisés 2026-08-31, reports/query-plans/es-c1-length-end-linking.md) :
 * 'length_end' est repassé de 1 à 2 CARACTÈRES. ES-022 avait aligné 'end' ET 'length_end' sur
 * 1 caractère d'un seul geste -- amalgame : 'end' (hub) doit rester à 1, mais 'length_end' a
 * pour UNIQUE consommateur byEnd, qui alimente les 2 199 pages index,follow
 * /palabras/{N}-letras/terminan-en/{XX} (suffixe 2 caractères, seule granularité "terminant"
 * indexée sur ce dépôt). À 1 caractère, byEnd laissait ces 2 199 pages SANS lien entrant.
 *
 * La régénération de production a été faite (coordinateur, 2026-08-31) : le grain 2 de
 * 'length_end' est désormais un INVARIANT dur, pas un état transitoire. Ce test l'assère
 * (`Assert::same(2, $G, ...)`, plus de branche `else`) : si un revert du `substr` ou le rejeu
 * d'un vieux script ramenait 'length_end' au grain 1, le test ÉCHOUE -- il ne doit jamais
 * repasser au vert pendant que les 2 199 pages redeviennent orphelines (mode de défaillance
 * C-1). La garde de couverture 0/2 199 tourne alors inconditionnellement.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    $seoPath = __DIR__ . '/../../storage/seo_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);
    Assert::true(is_file($seoPath), 'registre manquant : ' . $seoPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();
    $builder = new LengthLinksBuilder($connection);

    // --- Granularité de 'length_end' : INVARIANT = 2 caractères (correctif C-1 / ES-027). ---
    // Si ce test échoue, `length_end` est redescendu au grain 1 (revert du substr, rejeu d'un
    // vieux script) et les 2 199 pages word_list_combined terminan-en + longueur sont
    // redevenues orphelines : c'est exactement le mode de défaillance C-1. Régénérer via
    // `php scripts/build_explore_hub_counts.php`.
    $endKeys = $pdo->query("SELECT list_key FROM list_counts WHERE list_type = 'length_end'")->fetchAll(PDO::FETCH_COLUMN);
    Assert::true($endKeys !== [], "list_counts doit contenir des lignes 'length_end'");
    $grains = [];
    foreach ($endKeys as $k) {
        $suffix = substr((string) $k, strpos((string) $k, ':') + 1);
        $grains[mb_strlen($suffix, 'UTF-8')] = true;
    }
    Assert::same(1, count($grains), "toutes les cles 'length_end' doivent avoir la meme longueur de suffixe -- trouve : " . implode(',', array_keys($grains)));
    $G = (int) array_key_first($grains);
    Assert::same(2, $G, 'grain length_end doit rester a 2 : invariant du correctif C-1, ES-027 (grain 1 => 2 199 pages word_list_combined terminan-en+longueur orphelines)');

    $links = $builder->build(9);

    Assert::same(1, $links->queryCount, 'une seule requete triviale sur list_counts, jamais de GROUP BY sur terms');
    Assert::true($links->byStart !== [], 'sanity check : des mots de 9 lettres existent pour au moins une lettre de debut');
    Assert::true($links->byEnd !== []);

    // list_type peuples (ES-022) mais sans famille ouverte : sanity check reel, pas exhaustif.
    Assert::true($links->byWith !== [], "'length_with' doit etre peuple depuis ES-022");
    $withA = array_values(array_filter($links->byWith, static fn (array $l): bool => $l['letter'] === 'A'));
    if ($withA !== []) {
        $expectedWithA = (int) $pdo->query("SELECT count FROM list_counts WHERE list_type='length_with' AND list_key='9:A'")->fetch()['count'];
        Assert::same($expectedWithA, $withA[0]['count'], "compte 'length_with' verifie independamment pour 9:A");
    }
    Assert::true($links->byPosition !== [], "'length_with_position' doit etre peuple depuis ES-022");
    Assert::true($links->byStartEnd !== [], "'length_start_end' doit etre peuple depuis ES-022");

    // --- byStart (toujours 1 caractere) : force brute. ---
    $expectedStartC = (int) $pdo->query("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 1, 1) = 'C'")->fetch()['c'];
    $startC = array_values(array_filter($links->byStart, static fn (array $l): bool => $l['letter'] === 'C'));
    Assert::true(count($startC) === 1);
    Assert::same($expectedStartC, $startC[0]['count']);
    Assert::same('/palabras/9-letras/empiezan-por/c', $startC[0]['url']);

    // K : donnee brute presente meme si /palabras/9-letras/empiezan-por/k n'est pas indexee.
    $stmtK = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(normalized, 1, 1) = 'K'");
    $stmtK->execute();
    $expectedStartK = (int) $stmtK->fetch()['c'];
    $startK = array_values(array_filter($links->byStart, static fn (array $l): bool => $l['letter'] === 'K'));
    if ($expectedStartK > 0) {
        Assert::true(count($startK) === 1, 'K attendu dans byStart, donnee brute (' . $expectedStartK . ' mots)');
        Assert::same($expectedStartK, $startK[0]['count']);
    }

    // --- byEnd : GRAIN-AWARE. Chaque lien doit refleter la granularite reelle de list_counts. ---
    $mbReverse = static fn (string $s): string => implode('', array_reverse(mb_str_split($s, 1, 'UTF-8')));

    Assert::true(
        array_all($links->byEnd, static fn (array $l): bool => mb_strlen($l['letter'], 'UTF-8') === $G),
        "byEnd doit refleter le grain de 'length_end' ({$G} caractere(s)) -- c'est le lien qui a casse en C-1 (le grain de la donnee a change, byEnd a suivi en silence)"
    );

    // Force brute : pour chaque entree byEnd de longueur 9, le compte doit matcher un recompte
    // direct sur `terms` par les G derniers caracteres (colonne `reversed` = MAJUSCULES, comme
    // la cle list_counts et $entry['letter']), et l'URL doit etre la forme canonique minuscule.
    $recount = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = 9 AND substr(reversed, 1, ?) = ?");
    foreach (array_slice($links->byEnd, 0, 40) as $entry) {
        $recount->execute([$G, $mbReverse($entry['letter'])]);
        Assert::same((int) $recount->fetch()['c'], $entry['count'], "compte byEnd verifie independamment pour 9:{$entry['letter']}");
        Assert::same('/palabras/9-letras/terminan-en/' . mb_strtolower($entry['letter'], 'UTF-8'), $entry['url']);
    }

    // Tri alphabetique BINARY (ES-003 : Ñ apres Z), stable quel que soit le grain.
    $letters = array_column($links->byEnd, 'letter');
    $sorted = $letters;
    usort($sorted, static fn (string $a, string $b): int => $a <=> $b);
    Assert::same($sorted, $letters, 'byEnd doit rester trie par ordre BINARY sur les lettres');

    // --- Ñ cote byEnd : verification multi-octets reelle, grain-aware. On cherche une longueur
    // ou byEnd produit une cle contenant Ñ (2 octets UTF-8, jamais coupee). ---
    $foundEnye = null;
    foreach (range(2, 15) as $n) {
        foreach ($builder->build($n)->byEnd as $entry) {
            if (str_contains($entry['letter'], 'Ñ')) {
                $foundEnye = [$n, $entry];
                break 2;
            }
        }
    }
    Assert::true($foundEnye !== null, 'au moins une cle byEnd doit contenir Ñ (mots finissant par Ñ, ou suffixe 2 lettres avec Ñ)');
    [$nEnye, $entryEnye] = $foundEnye;
    Assert::true(mb_check_encoding($entryEnye['letter'], 'UTF-8'), 'cle byEnd Ñ corrompue : ' . bin2hex($entryEnye['letter']));
    $recountEnye = $pdo->prepare("SELECT COUNT(*) c FROM terms WHERE length = ? AND substr(reversed, 1, ?) = ?");
    $recountEnye->execute([$nEnye, $G, $mbReverse($entryEnye['letter'])]);
    Assert::same((int) $recountEnye->fetch()['c'], $entryEnye['count'], 'compte byEnd Ñ verifie independamment');
    Assert::same('/palabras/' . $nEnye . '-letras/terminan-en/' . mb_strtolower($entryEnye['letter'], 'UTF-8'), $entryEnye['url']);

    // --- Non-régression C-1 : couverture des 2 199 pages index,follow terminan-en 2 lettres +
    // longueur (Family::WORD_LIST_COMBINED). byEnd en est l'UNIQUE source de maillage interne. ---
    $seo = new Connection($seoPath);
    $twoCharRoutes = [];
    $stmt = $seo->pdo()->query(
        "SELECT route_path FROM registry WHERE family = 'word_list_combined'"
        . " AND route_path LIKE '/palabras/%-letras/terminan-en/%' AND robots = 'index,follow'"
    );
    foreach ($stmt as $row) {
        if (preg_match('#^/palabras/(\d+)-letras/terminan-en/([^/]+)$#u', $row['route_path'], $m) && mb_strlen($m[2], 'UTF-8') === 2) {
            $twoCharRoutes[$row['route_path']] = (int) $m[1];
        }
    }
    Assert::true(count($twoCharRoutes) > 2000, 'sanity : la famille word_list_combined terminan-en 2 lettres + longueur doit exister (2 199 attendues)');

    // Invariant C-1 : byEnd DOIT couvrir chacune de ces pages (il en est l'unique source de
    // maillage interne). 0 orpheline tolérée -- pas de branche conditionnelle : si le grain
    // n'est pas 2 le test a déjà échoué plus haut, et à grain 2 la couverture doit être totale.
    $lengths = array_unique(array_values($twoCharRoutes));
    $emitted = [];
    foreach ($lengths as $n) {
        foreach ($builder->build($n)->byEnd as $entry) {
            $emitted['/palabras/' . $n . '-letras/terminan-en/' . mb_strtolower($entry['letter'], 'UTF-8')] = true;
        }
    }
    $missing = array_values(array_filter(array_keys($twoCharRoutes), static fn (string $r): bool => !isset($emitted[$r])));
    Assert::same([], $missing, 'C-1 : ' . count($missing) . ' page(s) terminan-en 2 lettres + longueur SANS lien entrant depuis byEnd -- ' . implode(', ', array_slice($missing, 0, 20)));
};
