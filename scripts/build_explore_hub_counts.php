<?php

declare(strict_types=1);

/**
 * Precalcule les 19 list_type de list_counts (storage/dictionary_es.sqlite) -- adaptation
 * espagnole COMPLETE de scripts/build_explore_hub_counts.php (depot francais cousin). ETEND la
 * premiere passe (ES-017, 5 des 19 list_type) aux 14 types restants -- demande produit
 * explicite (2026-08-30, session en cours) : rattraper le fosse de couverture SEO face au
 * depot francais.
 *
 * GRANULARITE 'end'/'length_end' : REVISEE ICI, 2 -> 1 CARACTERE (ES-022, discussion produit
 * en direct dans la conversation, pas suppose). ES-017 les avait deliberement construits a 2
 * caracteres pour matcher la famille indexee terminan-en (2 lettres, ES-016) -- raisonnement
 * solide A L'EPOQUE (list_counts etait le SEUL moyen de savoir si le hub avait un lien reel a
 * offrir, donc autant qu'il pointe directement vers ce qui etait indexable). MAIS discussion
 * produit (question : "pourquoi 1 lettre FR/DE et 2 ES ?") a etabli que FR et DE restent a 1
 * caractere -- c'est ES qui divergeait, pas l'inverse -- et que la vraie raison de fermer
 * "termine par 1 lettre" a l'indexation (App\Search\RelationsFinder emet un lien "se termine
 * par" toujours a 2 caracteres minimum, Normalizer::MIN_LENGTH=2) NE bloque PAS le hub, qui est
 * une source de lien reelle DISTINCTE et INDEPENDANTE de RelationsFinder. Decision produit :
 * revenir a 1 caractere pour 'end'/'length_end' (coherence avec FR/DE), garder la famille DEJA
 * INDEXEE a 2 lettres (ES-016, construite par un script one-off independant de list_counts,
 * non affectee), et ouvrir en plus un palier 1-lettre sur cette nouvelle base (lot SEO dedie
 * apres ce script, verification de doublons/TTFB incluse) -- symetrique a "empiezan-por" 1
 * lettre deja indexe.
 *
 * DECISION CRITIQUE 1 (ES-017, INCHANGEE) : granularite CARACTERE, jamais TUILE -- CH/LL/RR ne
 * forment jamais de bucket dedie, un mot comme CHOZA est compte dans le bucket "C" (1er
 * caractere litteral), coherent avec RelationsFinder::relatedSearches() et la famille
 * empiezan-por deja en production.
 *
 * Ñ : mbReverse() (mb_str_split + array_reverse + implode, deja definie plus bas) partout ou
 * un parcours PHP inverse une chaine -- JAMAIS strrev() (byte-oriente, corromprait Ñ, 2 octets
 * UTF-8, bug reel deja trouve et corrige par ES-017 pour 'end'/'length_end'). Meme discipline
 * pour tout parcours lettre par lettre : mb_str_split(), jamais str_split()/count_chars()
 * (byte-orientees).
 *
 * Idempotent : DROP + CREATE + INSERT en une transaction, ANALYZE (D-021 herite) a la fin.
 *
 * Usage : php scripts/build_explore_hub_counts.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/build_explore_hub_counts.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
$dbPath = getenv('SCRABBLE_DICTIONARY_DB_PATH') ?: $root . '/storage/dictionary_es.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dbPath}\n");
    exit(1);
}

/**
 * Inverse une chaine UTF-8 caractere par caractere (Ñ = 2 octets, jamais coupe en deux --
 * PHP strrev() opere sur des OCTETS et corromprait Ñ).
 */
function mbReverse(string $s): string
{
    return implode('', array_reverse(mb_str_split($s, 1, 'UTF-8')));
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

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

// ---- length/start/end/length_start/length_end (ES-017, 'end'/'length_end' revises a 1
// caractere ci-dessus, ES-022) ----

$lengthStatement = $pdo->query('SELECT length, COUNT(*) n FROM terms GROUP BY length ORDER BY length');
foreach ($lengthStatement as $row) {
    $insert->execute(['length', (string) $row['length'], (int) $row['n']]);
    $total++;
}

$startStatement = $pdo->query('SELECT substr(normalized, 1, 1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c');
foreach ($startStatement as $row) {
    $insert->execute(['start', $row['c'], (int) $row['n']]);
    $total++;
}

$endStatement = $pdo->query('SELECT substr(reversed, 1, 1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c');
foreach ($endStatement as $row) {
    $insert->execute(['end', $row['c'], (int) $row['n']]);
    $total++;
}

$lengthStartStatement = $pdo->query(
    'SELECT length, substr(normalized, 1, 1) c, COUNT(*) n FROM terms GROUP BY length, c ORDER BY length, c'
);
foreach ($lengthStartStatement as $row) {
    $insert->execute(['length_start', $row['length'] . ':' . $row['c'], (int) $row['n']]);
    $total++;
}

$lengthEndStatement = $pdo->query(
    'SELECT length, substr(reversed, 1, 1) c, COUNT(*) n FROM terms GROUP BY length, c ORDER BY length, c'
);
foreach ($lengthEndStatement as $row) {
    $insert->execute(['length_end', $row['length'] . ':' . $row['c'], (int) $row['n']]);
    $total++;
}

// ---- 14 types nouveaux (ce lot, ES-022) ----

// length_with : longueur + lettre presente n'importe ou (minCount=1).
$lengthWithCounts = [];
foreach ($pdo->query('SELECT length, normalized FROM terms') as $row) {
    $length = (int) $row['length'];
    $seen = array_unique(mb_str_split((string) $row['normalized'], 1, 'UTF-8'));
    foreach ($seen as $letter) {
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

// start_end : lettre de debut ET de fin (1 caractere chacune).
$startEndStatement = $pdo->query(
    'SELECT substr(normalized, 1, 1) s, substr(reversed, 1, 1) e, COUNT(*) n FROM terms GROUP BY s, e ORDER BY s, e'
);
foreach ($startEndStatement as $row) {
    $insert->execute(['start_end', $row['s'] . ':' . $row['e'], (int) $row['n']]);
    $total++;
}

// length_with_position : longueur + lettre + position exacte (1-based).
$positionCounts = [];
foreach ($pdo->query('SELECT length, normalized FROM terms') as $row) {
    $length = (int) $row['length'];
    foreach (mb_str_split((string) $row['normalized'], 1, 'UTF-8') as $index => $letter) {
        $key = $length . ':' . $letter . ':' . ($index + 1);
        $positionCounts[$key] = ($positionCounts[$key] ?? 0) + 1;
    }
}
ksort($positionCounts);
foreach ($positionCounts as $key => $n) {
    $insert->execute(['length_with_position', $key, $n]);
    $total++;
}

// length_avec_sans : lettre EXIGEE + lettre EXCLUE + longueur. Alphabet ES = A-Z + Ñ (27
// lettres) -- construit dynamiquement depuis les lettres reellement distinctes vues dans la
// base plutot qu'une liste A-Z figee (evite d'omettre silencieusement Ñ).
$distinctAlphabetStatement = $pdo->query(
    'SELECT DISTINCT substr(normalized, 1, 1) c FROM terms UNION SELECT DISTINCT substr(reversed, 1, 1) FROM terms'
);
$alphabet = [];
foreach ($distinctAlphabetStatement as $row) {
    $alphabet[] = $row['c'];
}
sort($alphabet);

$avecSansCounts = [];
foreach ($pdo->query('SELECT length, normalized FROM terms') as $row) {
    $length = (int) $row['length'];
    $normalized = (string) $row['normalized'];
    $presentArr = array_unique(mb_str_split($normalized, 1, 'UTF-8'));
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

// length_start_end : longueur + lettre de debut ET de fin (1 caractere chacune).
$lengthStartEndStatement = $pdo->query(
    'SELECT length, substr(normalized, 1, 1) s, substr(reversed, 1, 1) e, COUNT(*) n FROM terms'
    . ' GROUP BY length, s, e ORDER BY length, s, e'
);
foreach ($lengthStartEndStatement as $row) {
    $insert->execute(['length_start_end', $row['length'] . ':' . $row['s'] . ':' . $row['e'], (int) $row['n']]);
    $total++;
}

// length_with_pair : longueur + CHAQUE PAIRE de lettres distinctes presentes (lettre1 <
// lettre2, ordre PHP sort()).
$pairCounts = [];
foreach ($pdo->query('SELECT length, normalized FROM terms') as $row) {
    $length = (int) $row['length'];
    $distinct = array_values(array_unique(mb_str_split((string) $row['normalized'], 1, 'UTF-8')));
    sort($distinct);
    $n = count($distinct);
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $key = $length . ':' . $distinct[$i] . ':' . $distinct[$j];
            $pairCounts[$key] = ($pairCounts[$key] ?? 0) + 1;
        }
    }
}
ksort($pairCounts);
foreach ($pairCounts as $key => $n) {
    $insert->execute(['length_with_pair', $key, $n]);
    $total++;
}

// length_with_triple : longueur + CHAQUE TRIPLET de lettres distinctes presentes.
$tripleCounts = [];
foreach ($pdo->query('SELECT length, normalized FROM terms') as $row) {
    $length = (int) $row['length'];
    $distinct = array_values(array_unique(mb_str_split((string) $row['normalized'], 1, 'UTF-8')));
    sort($distinct);
    $n = count($distinct);
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            for ($k = $j + 1; $k < $n; $k++) {
                $key = $length . ':' . $distinct[$i] . ':' . $distinct[$j] . ':' . $distinct[$k];
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

// start_end_with : lettre de debut + lettre de fin (1 caractere chacune) + lettre presente
// n'importe ou (minCount=1).
$startEndWithCounts = [];
foreach ($pdo->query('SELECT normalized, reversed FROM terms') as $row) {
    $normalized = (string) $row['normalized'];
    $chars = mb_str_split($normalized, 1, 'UTF-8');
    $start = $chars[0];
    $end = mb_substr((string) $row['reversed'], 0, 1, 'UTF-8');
    $distinct = array_unique($chars);

    foreach ($distinct as $letter) {
        $key = $start . ':' . $end . ':' . $letter;
        $startEndWithCounts[$key] = ($startEndWithCounts[$key] ?? 0) + 1;
    }
}
ksort($startEndWithCounts);
foreach ($startEndWithCounts as $key => $n) {
    $insert->execute(['start_end_with', $key, $n]);
    $total++;
}

// start_with : lettre de debut + lettre presente n'importe ou (minCount=1), SANS longueur ni
// fin -- exclusion des diagonales (lettre = debut) au precalcul (meme raisonnement que D-032
// cote francais : WordListFilters::fromPath() collapse "con-letras/X" vers la page parente
// empiezan-por/X des que la lettre "con-letras" egale le prefixe d'une seule lettre).
$startWithCounts = [];
foreach ($pdo->query('SELECT normalized FROM terms') as $row) {
    $chars = mb_str_split((string) $row['normalized'], 1, 'UTF-8');
    $start = $chars[0];
    $distinct = array_unique($chars);

    foreach ($distinct as $letter) {
        if ($letter === $start) {
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

// prefix2/3/4 : GROUP BY direct sur substr(normalized, 1, N) -- character-safe en SQL.
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

// suffix2/3/4 : meme principe via substr(reversed, 1, N), remis en ordre de lecture normal via
// mbReverse() (definie en tete de fichier) -- PAS strrev() (corromprait Ñ).
foreach ([2, 3, 4] as $suffixLength) {
    $suffixStatement = $pdo->query(
        "SELECT substr(reversed, 1, {$suffixLength}) c, COUNT(*) n FROM terms"
        . " WHERE length >= {$suffixLength} GROUP BY c ORDER BY c"
    );
    foreach ($suffixStatement as $row) {
        $suffix = mbReverse((string) $row['c']);
        $insert->execute(['suffix' . $suffixLength, $suffix, (int) $row['n']]);
        $total++;
    }
}

$pdo->commit();

// D-021 (herite) : toute modification de table/index doit etre suivie d'ANALYZE dans la MEME
// operation.
$pdo->exec('ANALYZE');

printf("list_counts : %d lignes inserees (19/19 list_type)\n", $total);
