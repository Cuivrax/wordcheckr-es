<?php

declare(strict_types=1);

/**
 * Precalcule les comptes de la page hub /mots (longueur x14, commencant x26, terminant x26,
 * soit 66 lignes), le maillage interne longueur x lettre (D-022, list_type 'length_start'/
 * 'length_end'/'length_with', au plus 14 x 26 x 3 = 1092 lignes supplementaires), le maillage
 * commencant x terminant sans longueur (D-024, list_type 'start_end', au plus 26 x 26 = 676
 * lignes supplementaires), le maillage longueur x lettre x position (D-023bis, list_type
 * 'length_with_position', au plus 14 x 26 x 15 = 5460 lignes supplementaires), le maillage
 * avec+sans x longueur (D-024bis, list_type 'length_avec_sans', au plus 26 x 25 x 14 = 9100
 * lignes supplementaires), le maillage commencant x terminant AVEC longueur (D-027, list_type
 * 'length_start_end', au plus 14 x 26 x 26 = 9464 lignes supplementaires), le maillage "avec
 * deux lettres" x longueur (palier 2 de l'ouverture en entonnoir de "avec", D-030, list_type
 * 'length_with_pair', au plus 14 x C(26,2) = 14 x 325 = 4550 lignes supplementaires), le
 * maillage "avec trois lettres" x longueur (palier 3, list_type 'length_with_triple', au plus
 * 14 x C(26,3) = 14 x 2600 = 36400 lignes supplementaires -- voir
 * reports/query-plans/avec-length-3-letters-full-sweep.md), le maillage commencant x terminant x
 * avec (tache 2026-08-18, list_type 'start_end_with', au plus 26 x 26 x 26 = 17576 lignes
 * supplementaires -- REELLEMENT 11348, voir
 * reports/query-plans/commencant-terminant-avec-maillage.md), le maillage commencant x avec SANS
 * terminant ni longueur (tache 2026-08-18, list_type 'start_with', au plus 26 x 26 = 676 lignes
 * supplementaires -- REELLEMENT 646 apres exclusion des 26 combinaisons degenerees X=Y au
 * precalcul lui-meme (D-032, voir son propre bloc plus bas), voir
 * reports/query-plans/commencant-avec-maillage.md) et l'entonnoir prefixe/suffixe
 * multi-lettres commencant/terminant (tache de dimensionnement 2026-08-18, list_type 'prefix2'/
 * 'prefix3'/'prefix4'/'suffix2'/'suffix3'/'suffix4', au plus 676+17576+456976 = 475228 prefixes
 * et autant de suffixes theoriques -- REELLEMENT 435+3775+17524 = 21734 prefixes et
 * 431+3293+14081 = 17805 suffixes, voir
 * reports/query-plans/commencant-terminant-multi-lettres-dimensionnement.md, PAS ENCORE une
 * decision produit tracee dans docs/DECISIONS.md, schema.sql non modifie par ce script pour ces
 * six list_type, voir la note de divergence plus bas) dans storage/dictionary_fr.sqlite -- hors
 * ligne uniquement, jamais au runtime (D-001, meme principe que score/signature/reversed).
 *
 * Mesure qui justifie ce script : SELECT substr(normalized,1,1), COUNT(*) FROM terms GROUP BY
 * ... force un SCAN complet de l'index (aucun index sur l'expression substr()) et un TEMP
 * B-TREE pour le GROUP BY -- 245 ms et 215 ms mesures pour commencant/terminant sur les
 * 838 180 lignes reelles, soit ~500 ms cumules pour une seule page. Largement au-dessus du
 * budget TTFB p95 < 250 ms (CLAUDE.md), pour un total qui ne change qu'a la reconstruction de
 * la base. Precalcule une fois ici, lu par App\Search\ExploreHubBuilder / LengthLinksBuilder en
 * une requete triviale, aucun GROUP BY, aucun scan.
 *
 * 'length_with' (avec + longueur, D-022) est calcule differemment des trois autres : 26
 * requetes SQL `LIKE '%X%'` forceraient chacune un SCAN complet des 838 180 lignes (aucun
 * index n'aide un motif sans ancre). Un unique parcours PHP sequentiel de `terms` (une seule
 * lecture, letters uniques par mot comptees en memoire) revient au meme cout qu'un seul des
 * GROUP BY ci-dessus plutot que 26x. 'length_with_pair' (palier 2) et 'length_with_triple'
 * (palier 3, voir leurs propres blocs plus bas) suivent le meme principe pour CHAQUE PAIRE puis
 * CHAQUE TRIPLET de lettres distinctes presentes dans le mot. 'start_end_with' (maillage
 * commencant+terminant+avec, voir son propre bloc plus bas) suit le meme principe une nouvelle
 * fois, MESURE contre l'alternative (26 requetes GROUP BY filtrees par instr()) plutot que
 * suppose -- scripts/bench_start_end_with_build.php, jetable, PHP retenu (3,945 s contre
 * 5,195 s, memes 11 348 lignes produites par les deux methodes, 0 divergence de compte).
 *
 * Idempotent : peut etre relance apres chaque reconstruction de storage/dictionary_fr.sqlite
 * (scripts/import_fr.py) sans effet de bord -- DROP + CREATE + INSERT en une transaction.
 *
 * DIVERGENCE TEMPORAIRE ASSUMEE ET FLAGGEE (agent data-engine, perimetre app/Search/,
 * scripts/build_*, jamais schema.sql -- fichier partage sous controle de la session
 * principale, CLAUDE.md) : le CHECK ci-dessous inclut deja 'start_with' (nouveau, tache
 * 2026-08-18, maillage commencant+avec sans longueur), mais schema.sql (source canonique de la
 * DDL) ne l'inclut PAS encore -- diff propose, non applique, voir reports/query-plans/
 * commencant-avec-maillage.md. Cette table est de toute facon integralement DROP + CREATE ici a
 * chaque execution (jamais la version issue de schema.sql seule) : aucun impact sur le
 * comportement reel de storage/dictionary_fr.sqlite, mais schema.sql resterait une documentation
 * incomplete tant que le diff propose n'est pas applique par la session principale -- ne pas
 * laisser cette entree trainer sans la resoudre (meme lecon que l'ecart I7 deja corrige une fois
 * sur ce meme fichier).
 * PRECEDENT RESOLU, verifie directement plutot que suppose : la meme note existait ici pour
 * 'length_start_end' (D-027), 'length_with_pair' (D-030), 'length_with_triple' (D-031) puis
 * 'start_end_with'/'prefix2'/'prefix3'/'prefix4'/'suffix2'/'suffix3'/'suffix4' (D-033 et tache de
 * dimensionnement du meme jour) -- schema.sql les a depuis tous rattrapes (la CHECK constraint de
 * schema.sql inclut deja les sept, confirme en le relisant au moment d'ecrire cette entree) --
 * seul 'start_with' ci-dessus reste divergent a ce jour, corrige des que la session principale
 * applique le diff propose.
 *
 * Usage : php scripts/build_explore_hub_counts.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/build_explore_hub_counts.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
$dbPath = getenv('SCRABBLE_DICTIONARY_DB_PATH') ?: $root . '/storage/dictionary_fr.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dbPath}\n");
    exit(1);
}

// Lecture-ecriture ASSUMEE ici (hors ligne uniquement) : le runtime PHP (app/Database/
// Connection.php) ouvre toujours ce meme fichier en SQLITE_OPEN_READONLY -- ce script ne
// s'execute jamais dans le flux d'une requete HTTP.
$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// DDL identique a schema.sql (source canonique) -- ecart trouve et corrige lors de l'audit
// final (code-reviewer, constat I7) : cette recreation omettait le CHECK sur list_type,
// present dans schema.sql mais jamais applique a la table reellement livree.
$pdo->exec('DROP TABLE IF EXISTS list_counts');
$pdo->exec(
    'CREATE TABLE list_counts ('
    . "list_type TEXT NOT NULL CHECK (list_type IN ('length', 'start', 'end', 'length_start', 'length_end', 'length_with', 'start_end', 'length_with_position', 'length_avec_sans', 'length_start_end', 'length_with_pair', 'length_with_triple', 'start_end_with', 'start_with', 'prefix2', 'prefix3', 'prefix4', 'suffix2', 'suffix3', 'suffix4')), "
    . 'list_key TEXT NOT NULL, '
    . 'count INTEGER NOT NULL, '
    . 'PRIMARY KEY (list_type, list_key)'
    . ')'
);

$insert = $pdo->prepare('INSERT INTO list_counts (list_type, list_key, count) VALUES (?, ?, ?)');

$pdo->beginTransaction();

$total = 0;

$lengthStatement = $pdo->query('SELECT length, COUNT(*) n FROM terms GROUP BY length ORDER BY length');
foreach ($lengthStatement as $row) {
    $insert->execute(['length', (string) $row['length'], (int) $row['n']]);
    $total++;
}

$startStatement = $pdo->query("SELECT substr(normalized, 1, 1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c");
foreach ($startStatement as $row) {
    $insert->execute(['start', $row['c'], (int) $row['n']]);
    $total++;
}

$endStatement = $pdo->query("SELECT substr(reversed, 1, 1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c");
foreach ($endStatement as $row) {
    $insert->execute(['end', $row['c'], (int) $row['n']]);
    $total++;
}

// length_start / length_end (D-022) : croise longueur et lettre de debut/fin -- alimente le
// maillage interne "mots de {N} lettres commencant/terminant par {X}". list_key =
// "{longueur}:{lettre}", ex. "13:A". Seules les combinaisons REELLEMENT non vides sont
// inserees (pas de ligne a 0 -- R5 du registre SEO, jamais de lien mort meme hors indexation).
$lengthStartStatement = $pdo->query(
    "SELECT length, substr(normalized, 1, 1) c, COUNT(*) n FROM terms GROUP BY length, c ORDER BY length, c"
);
foreach ($lengthStartStatement as $row) {
    $insert->execute(['length_start', $row['length'] . ':' . $row['c'], (int) $row['n']]);
    $total++;
}

$lengthEndStatement = $pdo->query(
    "SELECT length, substr(reversed, 1, 1) c, COUNT(*) n FROM terms GROUP BY length, c ORDER BY length, c"
);
foreach ($lengthEndStatement as $row) {
    $insert->execute(['length_end', $row['length'] . ':' . $row['c'], (int) $row['n']]);
    $total++;
}

// length_with (D-022) : lettre presente n'importe ou dans le mot (pas seulement debut/fin) --
// alimente "mots de {N} lettres avec {X}". Parcours PHP unique plutot que 26 requetes
// LIKE '%X%' (voir entete du fichier) : une seule lecture sequentielle de `terms`, lettres
// uniques par mot comptees en memoire (php array), jamais un SCAN par lettre.
$lengthWithCounts = [];

$allTermsStatement = $pdo->query('SELECT length, normalized FROM terms');
foreach ($allTermsStatement as $row) {
    $length = (int) $row['length'];
    $seenLetters = count_chars((string) $row['normalized'], 3);

    foreach (str_split($seenLetters) as $letter) {
        $lengthWithCounts[$length][$letter] = ($lengthWithCounts[$length][$letter] ?? 0) + 1;
    }
}

ksort($lengthWithCounts);
foreach ($lengthWithCounts as $length => $byLetter) {
    ksort($byLetter);
    foreach ($byLetter as $letter => $n) {
        $insert->execute(['length_with', $length . ':' . $letter, $n]);
        $total++;
    }
}

// start_end (D-024, maillage commencant+terminant) : croise lettre de debut ET de fin, SANS
// longueur -- alimente le maillage interne depuis /mots/commencant/{X} et /mots/terminant/{Y}
// (deja indexes, D-017) vers /mots/commencant/{X}/terminant/{Y} (Family::WORD_LIST_COMBINED).
// GROUP BY sur les deux expressions substr() a la fois (611 groupes non vides sur les
// 838 180 lignes reelles) : cout mesure une seule fois ici, jamais au runtime.
$startEndStatement = $pdo->query(
    "SELECT substr(normalized, 1, 1) s, substr(reversed, 1, 1) e, COUNT(*) n FROM terms GROUP BY s, e ORDER BY s, e"
);
foreach ($startEndStatement as $row) {
    $insert->execute(['start_end', $row['s'] . ':' . $row['e'], (int) $row['n']]);
    $total++;
}

// length_with_position (D-023bis) : longueur + lettre + position EXACTE de cette lettre dans
// le mot -- alimente "mots de {N} lettres avec {X}" -> liens vers chaque position ou cette
// lettre apparait reellement. Parcours PHP unique (une position par caractere), pas de GROUP
// BY SQL sur une expression composee (aucune fonction SQLite standard ne donne "position d'un
// caractere" directement pour toutes les occurrences).
$positionCounts = [];

$allTermsForPositionStatement = $pdo->query('SELECT length, normalized FROM terms');
foreach ($allTermsForPositionStatement as $row) {
    $length = (int) $row['length'];
    $normalized = (string) $row['normalized'];

    foreach (str_split($normalized) as $index => $letter) {
        $position = $index + 1;
        $key = $length . ':' . $letter . ':' . $position;
        $positionCounts[$key] = ($positionCounts[$key] ?? 0) + 1;
    }
}

ksort($positionCounts);
foreach ($positionCounts as $key => $n) {
    $insert->execute(['length_with_position', $key, $n]);
    $total++;
}

// length_avec_sans (D-024bis) : une lettre EXIGEE, une lettre EXCLUE, ET la longueur --
// alimente "mots avec {X} sans {Y}" (sans longueur) -> liens vers chaque longueur ou cette
// combinaison a des resultats. Parcours PHP unique : pour chaque mot, chaque lettre presente
// croisee avec chaque lettre absente (pas de GROUP BY SQL possible sur une paire de conditions
// instr()/NOT instr() -- aucun index n'aiderait, deja mesure ~91-170 ms par combinaison en
// requete live, voir schema.sql).
$avecSansCounts = [];
$alphabet = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

$allTermsForAvecSansStatement = $pdo->query('SELECT length, normalized FROM terms');
foreach ($allTermsForAvecSansStatement as $row) {
    $length = (int) $row['length'];
    $normalized = (string) $row['normalized'];
    $present = count_chars($normalized, 3);
    $presentArr = str_split($present);
    $presentFlip = array_flip($presentArr);

    foreach ($presentArr as $with) {
        foreach ($alphabet as $without) {
            if (isset($presentFlip[$without])) {
                continue;
            }

            $key = $with . ':' . $without . ':' . $length;
            $avecSansCounts[$key] = ($avecSansCounts[$key] ?? 0) + 1;
        }
    }
}

ksort($avecSansCounts);
foreach ($avecSansCounts as $key => $n) {
    $insert->execute(['length_avec_sans', $key, $n]);
    $total++;
}

// length_start_end (D-027, maillage commencant+terminant AVEC longueur -- deja synchronise avec
// schema.sql, voir la note de divergence en entete de fichier) : croise
// longueur, lettre de debut ET lettre de fin -- alimente le maillage interne depuis une page
// longueur+prefixe seul (ou longueur+suffixe seul) vers chaque variante combinee correspondante
// qui a au moins un resultat. GROUP BY sur les deux expressions substr() a la fois ET sur
// `length` (colonne indexee, mais substr(normalized,1,1)/substr(reversed,1,1) ne le sont pas,
// meme limite que 'start_end' ci-dessus, D-024) -- mesure separement, voir
// reports/query-plans/length-combined-links.md pour le temps reel.
$lengthStartEndStatement = $pdo->query(
    "SELECT length, substr(normalized, 1, 1) s, substr(reversed, 1, 1) e, COUNT(*) n FROM terms"
    . ' GROUP BY length, s, e ORDER BY length, s, e'
);
foreach ($lengthStartEndStatement as $row) {
    $insert->execute(['length_start_end', $row['length'] . ':' . $row['s'] . ':' . $row['e'], (int) $row['n']]);
    $total++;
}

// length_with_pair (palier 2 de l'ouverture en entonnoir de "avec") : longueur croisee avec
// CHAQUE PAIRE de lettres DISTINCTES presentes dans le mot (minCount=1 chacune, jamais de
// repetition comptee -- meme portee que le palier 1 'length_with' ci-dessus, mais pour deux
// lettres simultanement au lieu d'une seule) -- alimente le maillage "mots de {N} lettres avec
// {X}" (palier 1, deja indexe, D-029) -> liens vers chaque variante "avec {X} {Y}" (palier 2)
// qui a au moins un resultat. list_key = "{longueur}:{lettre1}:{lettre2}", lettre1 < lettre2
// ALPHABETIQUEMENT (une seule ligne par paire non ordonnee -- App\Search\
// AvecTwoLettersLinksBuilder interroge les deux sens via un OR sur list_key au runtime, voir sa
// propre entete). Parcours PHP unique : count_chars($normalized, 3) renvoie deja les lettres
// DISTINCTES d'un mot en ORDRE CROISSANT (verifie explicitement, pas suppose -- count_chars()
// mode 3 itere les valeurs d'octet 0-255 dans l'ordre), donc chaque paire (i, j) avec i < j
// dans le tableau resultant respecte deja lettre1 < lettre2 sans tri supplementaire. Aucun
// GROUP BY SQL sur une paire de conditions instr() (aucun index n'aiderait, meme raison que
// 'length_avec_sans' ci-dessus).
//
// Combinatoire maximale : 14 longueurs x C(26,2) = 14 x 325 = 4550 lignes. Mesure reelle
// (2026-08-17, storage/dictionary_fr.sqlite, 838 180 lignes) : 4 276 lignes non vides (274
// combinaisons a 0 resultat, jamais inserees -- R5), 19,05 s de calcul hors ligne (23 187 713
// increments cumules -- MOINS d'increments par mot que 'length_avec_sans' ci-dessus : C(k,2)
// pour k lettres distinctes presentes, contre k x (26-k) pour avec+sans -- donc plus rapide,
// pas plus lent, malgre la mise en garde initiale de la tache produit).
$pairCounts = [];

$allTermsForPairStatement = $pdo->query('SELECT length, normalized FROM terms');
foreach ($allTermsForPairStatement as $row) {
    $length = (int) $row['length'];
    $distinctLetters = str_split(count_chars((string) $row['normalized'], 3));
    $letterCount = count($distinctLetters);

    for ($i = 0; $i < $letterCount; $i++) {
        for ($j = $i + 1; $j < $letterCount; $j++) {
            $key = $length . ':' . $distinctLetters[$i] . ':' . $distinctLetters[$j];
            $pairCounts[$key] = ($pairCounts[$key] ?? 0) + 1;
        }
    }
}

ksort($pairCounts);
foreach ($pairCounts as $key => $n) {
    $insert->execute(['length_with_pair', $key, $n]);
    $total++;
}

// length_with_triple (palier 3 de l'ouverture en entonnoir de "avec") : longueur croisee avec
// CHAQUE TRIPLET de lettres DISTINCTES presentes dans le mot (minCount=1 chacune -- meme portee
// que le palier 2 'length_with_pair' ci-dessus, mais pour trois lettres simultanement au lieu de
// deux) -- alimente le maillage "mots de {N} lettres avec {X} {Y}" (palier 2, deja indexe, D-030)
// -> liens vers chaque variante "avec {X} {Y} {Z}" (palier 3) qui a au moins un resultat.
// list_key = "{longueur}:{lettre1}:{lettre2}:{lettre3}", lettre1 < lettre2 < lettre3
// ALPHABETIQUEMENT (une seule ligne par triplet non ordonne -- App\Search\
// AvecThreeLettersLinksBuilder interroge les trois positions possibles de la paire source dans
// ce triplet via un OR sur list_key au runtime, voir sa propre entete). Parcours PHP unique :
// count_chars($normalized, 3) renvoie deja les lettres DISTINCTES d'un mot en ORDRE CROISSANT
// (meme verification que 'length_with_pair' ci-dessus), donc chaque triplet (i, j, l) avec
// i < j < l dans le tableau resultant respecte deja lettre1 < lettre2 < lettre3 sans tri
// supplementaire. Aucun GROUP BY SQL sur un triplet de conditions instr() (aucun index
// n'aiderait, meme raison que 'length_with_pair' ci-dessus).
//
// Combinatoire maximale : 14 longueurs x C(26,3) = 14 x 2600 = 36400 lignes. Mesure reelle :
// voir reports/query-plans/avec-length-3-letters-full-sweep.md pour le nombre de lignes non
// vides et le temps de calcul isole mesures sur storage/dictionary_fr.sqlite (838 180 lignes).
$tripleCounts = [];

$allTermsForTripleStatement = $pdo->query('SELECT length, normalized FROM terms');
foreach ($allTermsForTripleStatement as $row) {
    $length = (int) $row['length'];
    $distinctLetters = str_split(count_chars((string) $row['normalized'], 3));
    $letterCount = count($distinctLetters);

    for ($i = 0; $i < $letterCount; $i++) {
        for ($j = $i + 1; $j < $letterCount; $j++) {
            for ($k = $j + 1; $k < $letterCount; $k++) {
                $key = $length . ':' . $distinctLetters[$i] . ':' . $distinctLetters[$j] . ':' . $distinctLetters[$k];
                $tripleCounts[$key] = ($tripleCounts[$key] ?? 0) + 1;
            }
        }
    }
}

ksort($tripleCounts);
foreach ($tripleCounts as $key => $n) {
    $insert->execute(['length_with_triple', $key, $n]);
    $total++;
}

// start_end_with (maillage commencant+terminant+avec, tache 2026-08-18 -- voir
// reports/query-plans/commencant-terminant-avec-maillage.md) : croise lettre de debut, lettre de
// fin ET une lettre presente n'importe ou dans le mot (minCount=1, jamais de repetition comptee --
// meme portee que 'length_with' ci-dessus, mais croisee avec (debut, fin) au lieu de longueur).
// list_key = "{debut}:{fin}:{lettre}", ex. "R:E:S" -- alimente le maillage depuis une page
// /mots/commencant/{X}/terminant/{Y} (deja indexee, Family::WORD_LIST_COMBINED, D-024/D-025) vers
// chaque variante /mots/commencant/{X}/terminant/{Y}/avec/{Z} qui a au moins un resultat.
//
// Parcours PHP unique, MESURE contre l'alternative (26 requetes GROUP BY SQL filtrees par
// instr(), une par lettre "avec") plutot que suppose -- scripts/bench_start_end_with_build.php
// (jetable) : methode PHP 3,945 s contre 5,195 s pour la methode SQL, LES DEUX methodes
// produisent EXACTEMENT le meme jeu de 11 348 lignes non vides (0 divergence de compte dans les
// deux sens) -- PHP retenu, plus rapide et deja le principe etabli pour 'length_with'/
// 'length_with_pair'/'length_with_triple' ci-dessus. count_chars($normalized, 3) donne les
// lettres DISTINCTES d'un mot (aucune repetition comptee, minCount=1 uniquement -- comme
// 'length_with', pas de pendant "minCount>=2" ici).
//
// Combinatoire maximale : 26 x 26 x 26 = 17 576 lignes. Mesure reelle (storage/dictionary_fr.sqlite,
// 838 180 lignes) : 11 348 lignes non vides -- exactement 611 (paires commencant+terminant
// reellement non vides, list_type 'start_end', D-024) x 26 lettres = 15 886 combinaisons
// reellement explorees par construction (les 65 x 26 = 1 690 combinaisons issues d'une paire
// commencant+terminant deja a 0 resultat sont necessairement a 0 elles aussi -- "avec" s'ajoute
// toujours en AND sur le meme panier, jamais en OR -- donc jamais inserees ici, meme raccourci
// logique deja applique par reports/query-plans/commencant-terminant-avec-full-sweep.md), sur les
// 17 576 combinaisons brutes au maximum.
$startEndWithCounts = [];

$allTermsForStartEndWithStatement = $pdo->query('SELECT normalized, reversed FROM terms');
foreach ($allTermsForStartEndWithStatement as $row) {
    $normalized = (string) $row['normalized'];
    $start = $normalized[0];
    $end = ((string) $row['reversed'])[0];
    $distinctLetters = str_split(count_chars($normalized, 3));

    foreach ($distinctLetters as $letter) {
        $key = $start . ':' . $end . ':' . $letter;
        $startEndWithCounts[$key] = ($startEndWithCounts[$key] ?? 0) + 1;
    }
}

ksort($startEndWithCounts);
foreach ($startEndWithCounts as $key => $n) {
    $insert->execute(['start_end_with', $key, $n]);
    $total++;
}

// start_with (maillage commencant+avec SANS terminant ni longueur, tache 2026-08-18 -- voir
// reports/query-plans/commencant-avec-maillage.md) : croise lettre de debut ET une lettre
// presente n'importe ou dans le mot (minCount=1, jamais de repetition comptee -- meme portee que
// 'length_with'/'start_end_with' ci-dessus, mais croisee avec le SEUL debut, sans fin ni
// longueur). list_key = "{debut}:{lettre}", ex. "R:S" -- alimente le maillage depuis une page
// /mots/commencant/{X} (deja indexee, Family::WORD_LIST_COMMENCANT, D-017) vers chaque variante
// /mots/commencant/{X}/avec/{Y} qui a au moins un resultat.
//
// EXCLUSION au precalcul lui-meme (pas au niveau du builder, contrairement a 'start_end_with'
// ci-dessus) des 26 combinaisons DEGENEREES ou la lettre "avec" egale la lettre de debut
// elle-meme (X:X) : WordListFilters::fromPath() collapse silencieusement "avec/X" vers la page
// parente /mots/commencant/{X} des que cette lettre "avec" egale le prefixe d'une seule lettre
// (D-032) -- une ligne list_counts "X:X" ne correspondrait JAMAIS a une URL canonique distincte,
// contrairement a 'start_end_with' ou une ligne "{debut}:{fin}:{lettre}" degeneree (lettre =
// debut OU lettre = fin) reste utile ailleurs (ce n'est le precalcul BRUT DE CETTE SEULE PAIRE
// qui est degenere, pas necessairement pertinent de le retirer a la source pour un usage futur
// different). Ici, il n'existe qu'UNE SEULE direction de lecture (toujours depuis
// /mots/commencant/{X}, jamais une page "avec {Y}" symetrique indexee -- Family::WORD_LIST_AVEC
// reste NEVER_SITEMAP en permanence) et une seule lettre "avec" par ligne : la condition de
// degenerescence (lettre = debut) est IDENTIQUE au precalcul et a l'usage reel, l'exclure ici
// directement est strictement equivalente et plus simple qu'une comparaison d'URL cote builder --
// verifie explicitement (reports/query-plans/commencant-avec-maillage.md, section 1) plutot que
// suppose.
//
// Combinatoire maximale : 26 x 26 = 676 lignes AVANT exclusion, 26 x 25 = 650 apres exclusion des
// 26 diagonales. Mesure reelle (storage/dictionary_fr.sqlite, 838 180 lignes) : 646 lignes non
// vides sur les 650 combinaisons non degenerees possibles (4 a 0 resultat : V+W, X+J, X+K, X+W,
// verifiees identiques a reports/query-plans/commencant-avec-no-length-full-sweep.md section 8,
// base inchangee depuis).
$startWithCounts = [];

$allTermsForStartWithStatement = $pdo->query('SELECT normalized FROM terms');
foreach ($allTermsForStartWithStatement as $row) {
    $normalized = (string) $row['normalized'];
    $start = $normalized[0];
    $distinctLetters = str_split(count_chars($normalized, 3));

    foreach ($distinctLetters as $letter) {
        if ($letter === $start) {
            // Degenere (D-032) : exclu ici, au precalcul, pas au niveau du builder -- voir le
            // commentaire ci-dessus pour la justification de ce choix distinct de 'start_end_with'.
            continue;
        }

        $key = $start . ':' . $letter;
        $startWithCounts[$key] = ($startWithCounts[$key] ?? 0) + 1;
    }
}

ksort($startWithCounts);
foreach ($startWithCounts as $key => $n) {
    $insert->execute(['start_with', $key, $n]);
    $total++;
}

// prefix2 / prefix3 / prefix4 (entonnoir commencant multi-lettres, tache de dimensionnement
// 2026-08-18) : GROUP BY direct sur substr(normalized, 1, N), N = 2, 3, 4 -- contrairement a
// 'length_with'/'length_with_pair'/'length_with_triple' ci-dessus (ou aucun index ne peut aider
// une expression composee avec `length`), substr(normalized, 1, N) SEUL est une expression a une
// seule colonne, deja couverte par idx_terms_length_normalized (SEARCH ... USING COVERING INDEX,
// verifie par EXPLAIN QUERY PLAN) -- mesure reelle sur les 838 180 lignes : 411-518 ms par
// requete (SCAN de l'index + TEMP B-TREE FOR GROUP BY, pas d'index sur l'expression substr()
// elle-meme, meme limite que 'start'/'end' -- voir l'entete de fichier), largement au-dessus du
// budget TTFB pour une page mais hors ligne uniquement ici, jamais au runtime. `WHERE length >= N`
// evite qu'un mot plus court que N lettres ne contribue un prefixe tronque (ex. un mot de 2
// lettres ne doit jamais apparaitre dans le GROUP BY a 3 ou 4 lettres).
foreach ([2, 3, 4] as $prefixLength) {
    $prefixStatement = $pdo->query(
        "SELECT substr(normalized, 1, {$prefixLength}) c, COUNT(*) n FROM terms"
        . " WHERE length >= {$prefixLength} GROUP BY c ORDER BY c"
    );
    foreach ($prefixStatement as $row) {
        $insert->execute(['prefix' . $prefixLength, $row['c'], (int) $row['n']]);
        $total++;
    }
}

// suffix2 / suffix3 / suffix4 (entonnoir terminant multi-lettres, meme tache) : meme principe via
// substr(reversed, 1, N), MAIS list_key stocke le SUFFIXE REEL (ordre de lecture normal), pas la
// sous-chaine de `reversed` telle quelle -- substr(reversed, 1, 3) donne les 3 dernieres lettres
// du mot EN ORDRE INVERSE (ex. "IER" pour CHANTIER devient "REI" dans reversed) : strrev() les
// remet dans l'ordre attendu par WordListFilters::fromPath()/l'URL "/mots/terminant/{suffixe}"
// avant insertion, pour que le builder de maillage n'ait jamais besoin de reversed() a l'usage.
foreach ([2, 3, 4] as $suffixLength) {
    $suffixStatement = $pdo->query(
        "SELECT substr(reversed, 1, {$suffixLength}) c, COUNT(*) n FROM terms"
        . " WHERE length >= {$suffixLength} GROUP BY c ORDER BY c"
    );
    foreach ($suffixStatement as $row) {
        $suffix = strrev((string) $row['c']);
        $insert->execute(['suffix' . $suffixLength, $suffix, (int) $row['n']]);
        $total++;
    }
}

$pdo->commit();

// D-021 : toute modification de table/index doit etre suivie d'ANALYZE dans la MEME
// operation, jamais une etape facultative ou differee -- ce script peuple list_counts a
// grande echelle (jusqu'a ~103 000 lignes) sans jamais l'avoir fait, laissant les
// statistiques du planificateur perimees et provoquant exactement la meme classe de
// regression que D-021 (plans de requete degrades, invisibles dans le code PHP, uniquement
// dans le plan choisi par SQLite) sur toute requete touchant list_counts apres un premier
// peuplement ou une reconstruction complete de storage/dictionary_fr.sqlite.
$pdo->exec('ANALYZE');

printf(
    "list_counts : %d lignes (14 longueur + 26 commencant + 26 terminant + length_start/length_end/length_with/start_end/length_with_position/length_avec_sans/length_start_end/length_with_pair/length_with_triple/start_end_with/start_with/prefix2/prefix3/prefix4/suffix2/suffix3/suffix4 attendues)\n",
    $total,
);
