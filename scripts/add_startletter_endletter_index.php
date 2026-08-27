<?php

declare(strict_types=1);

/**
 * Applique idx_terms_startletter_endletter_normalized(substr(normalized,1,1),
 * substr(reversed,1,1), normalized) sur storage/dictionary_fr.sqlite -- ajout d'index seul,
 * aucune donnee touchee (D-001, D-007). Necessaire une seule fois tant que la base n'est pas
 * reconstruite depuis zero via scripts/import_fr.py (qui applique desormais schema.sql en
 * integralite, cet index y est inclus pour toute reconstruction future). Idempotent : DROP
 * INDEX IF EXISTS puis CREATE, jamais d'erreur si deja applique.
 *
 * Corrige une regression de performance reelle trouvee par l'audit du lot D-025 (611 pages
 * /mots/commencant/{X}/terminant/{Y} venant d'etre ouvertes a l'indexation) : une premiere
 * correction (choisir dynamiquement le cote le moins frequent comme ancrage) reduisait le pire
 * cas mesure a l'audit (1 211 ms) a 17 ms, mais un balayage complet des 611 combinaisons a
 * revele 53 cas encore au-dessus du budget TTFB p95 < 250 ms des que les DEUX lettres sont
 * frequentes (jusqu'a 6 675 ms, commencant/z/terminant/s). Voir schema.sql pour le detail
 * complet et reports/query-plans/prefix-suffix-anchor-fix.md pour les mesures avant/apres.
 *
 * ANALYZE en fin de script (meme discipline que add_length_reversed_index.php, D-021) : toute
 * modification d'index sur cette base DOIT relancer ANALYZE dans la meme operation, jamais
 * separement.
 *
 * Usage : php scripts/add_startletter_endletter_index.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/add_startletter_endletter_index.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
$dbPath = getenv('SCRABBLE_DICTIONARY_DB_PATH') ?: $root . '/storage/dictionary_fr.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "Base introuvable : {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('DROP INDEX IF EXISTS idx_terms_startletter_endletter_normalized');
$pdo->exec(
    'CREATE INDEX idx_terms_startletter_endletter_normalized'
    . ' ON terms(substr(normalized, 1, 1), substr(reversed, 1, 1), normalized)'
);
$pdo->exec('ANALYZE terms');

$count = (int) $pdo->query("SELECT COUNT(*) c FROM sqlite_master WHERE type = 'index' AND name = 'idx_terms_startletter_endletter_normalized'")->fetch()['c'];
$stat = (int) $pdo->query("SELECT COUNT(*) c FROM sqlite_stat1 WHERE tbl = 'terms' AND idx = 'idx_terms_startletter_endletter_normalized'")->fetch()['c'];

printf("idx_terms_startletter_endletter_normalized : %s\n", $count === 1 ? 'applique' : 'ECHEC');
printf("ANALYZE (sqlite_stat1)                      : %s\n", $stat === 1 ? 'a jour' : 'ECHEC');
