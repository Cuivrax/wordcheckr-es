<?php

declare(strict_types=1);

/**
 * Applique idx_terms_length_reversed(length, reversed) sur storage/dictionary_fr.sqlite --
 * ajout d'index seul, aucune donnee touchee (D-001, D-007). Necessaire une seule fois tant
 * que la base n'est pas reconstruite depuis zero via scripts/import_fr.py (qui applique
 * desormais schema.sql en integralite, cet index y est inclus pour toute reconstruction
 * future). Idempotent : DROP INDEX IF EXISTS puis CREATE, jamais d'erreur si deja applique.
 *
 * Corrige un bug de performance reel trouve lors de l'analyse d'opportunite SEO longue
 * traine (2026-08-08) : /mots/{N}-lettres/terminant/{suffixe} sans cet index mesurait jusqu'a
 * 1 779 ms (idx_terms_reversed seul ne couvre pas `length`, SQLite doit lire chaque ligne
 * candidate sur toute la plage globale du suffixe). Voir schema.sql pour le detail complet
 * et reports/query-plans/terminant-length-index-fix.md pour les mesures avant/apres.
 *
 * ANALYZE en fin de script (regression trouvee le jour meme, D-021, docs/DECISIONS.md) :
 * un CREATE INDEX sans ANALYZE derriere laisse le nouvel index sans ligne dans sqlite_stat1,
 * ce qui a fait choisir a tort idx_terms_length_reversed a la place de
 * idx_terms_length_normalized pour de simples requetes "WHERE length = ? ORDER BY normalized"
 * (regime EXACT, /mots/{N}-lettres) -- un TEMP B-TREE FOR ORDER BY invisible dans le code,
 * mesure jusqu'a 1,1 s sur une famille deja indexee en production (word_list_length, D-017).
 * Toute future modification d'index sur cette base DOIT relancer ANALYZE dans la meme
 * operation, jamais separement.
 *
 * Usage : php scripts/add_length_reversed_index.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/add_length_reversed_index.php ne s'execute qu'en CLI, hors ligne.\n");
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

$pdo->exec('DROP INDEX IF EXISTS idx_terms_length_reversed');
$pdo->exec('CREATE INDEX idx_terms_length_reversed ON terms(length, reversed)');
$pdo->exec('ANALYZE terms');

$count = (int) $pdo->query("SELECT COUNT(*) c FROM sqlite_master WHERE type = 'index' AND name = 'idx_terms_length_reversed'")->fetch()['c'];
$stat = (int) $pdo->query("SELECT COUNT(*) c FROM sqlite_stat1 WHERE tbl = 'terms' AND idx = 'idx_terms_length_reversed'")->fetch()['c'];

printf("idx_terms_length_reversed : %s\n", $count === 1 ? 'applique' : 'ECHEC');
printf("ANALYZE (sqlite_stat1)     : %s\n", $stat === 1 ? 'a jour' : 'ECHEC');
