<?php

declare(strict_types=1);

use App\Database\Connection;
use Tests\Support\Assert;

/**
 * Verifie l'application effective de D-001/D-007 (lecture seule stricte au runtime) :
 * lecture possible, ecriture impossible par construction, aucun fichier cree en cas de
 * chemin absent.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $pdo = $connection->pdo();

    $row = $pdo->query("SELECT normalized FROM terms WHERE normalized = 'POSER' LIMIT 1")->fetch();
    Assert::notNull($row, 'lecture attendue sur une connexion en lecture seule');
    Assert::same('POSER', $row['normalized']);

    // La connexion est memorisee : deux appels a pdo() renvoient la meme instance,
    // pas une reouverture a chaque appel.
    Assert::true($pdo === $connection->pdo(), 'la connexion aurait du etre reutilisee, pas rouverte');

    // Toute tentative d'ecriture doit echouer, quel que soit le mecanisme employe
    // (SQLITE_OPEN_READONLY, PDO::SQLITE_ATTR_READONLY_STATEMENT, PRAGMA query_only).
    $wrote = true;
    try {
        $pdo->exec(
            "INSERT INTO terms (display_term, normalized, is_french, is_ods8, is_ods9, "
            . "score, length, signature, reversed) "
            . "VALUES ('X', 'ZZZTESTONLY', 1, 0, 0, 1, 11, 'X', 'X')"
        );
    } catch (\Throwable) {
        $wrote = false;
    }
    Assert::true(!$wrote, 'une ecriture a reussi sur une connexion censee etre lecture seule');

    $stillAbsent = $pdo->query("SELECT COUNT(*) AS n FROM terms WHERE normalized = 'ZZZTESTONLY'")->fetch();
    Assert::same(0, (int) $stillAbsent['n'], 'la tentative d\'ecriture precedente a laisse une trace');

    // Un chemin absent ne doit jamais provoquer la creation d'un nouveau fichier SQLite
    // (comportement par defaut de SQLite en ecriture, evite ici par SQLITE_OPEN_READONLY).
    $missingPath = sys_get_temp_dir() . '/scrabble_light_test_missing_' . bin2hex(random_bytes(4)) . '.sqlite';
    $missingConnection = new Connection($missingPath);

    $opened = true;
    try {
        $missingConnection->pdo();
    } catch (\Throwable) {
        $opened = false;
    }
    Assert::true(!$opened, 'une connexion vers un fichier absent aurait du echouer');
    Assert::true(!is_file($missingPath), 'un fichier a ete cree alors que la connexion devait etre en lecture seule stricte');
};
