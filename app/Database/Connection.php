<?php

declare(strict_types=1);

namespace App\Database;

/**
 * Connexion PDO SQLite strictement en lecture seule.
 *
 * Deux verrous independants empechent toute ecriture au runtime (D-001, D-007) :
 * - SQLITE_OPEN_READONLY a l'ouverture du fichier : le moteur refuse l'ecriture au
 *   niveau du descripteur de fichier ;
 * - PDO::SQLITE_ATTR_READONLY_STATEMENT (PHP 8.4) : toute instruction d'ecriture
 *   echoue explicitement, meme si le premier verrou etait un jour contourne.
 * PRAGMA query_only=ON est ajoute en troisieme ceinture, portable si l'un des deux
 * attributs precedents venait a manquer sur une configuration differente.
 *
 * Mesure (voir reports/query-plans/phase1.md) : ouverture + premiere requete coute
 * environ 0,2 ms. Une instance par requete HTTP, jamais partagee ni persistee entre
 * requetes : inutile de mutualiser une connexion aussi bon marche, et cela evite les
 * ecueils d'un etat partage entre workers PHP concurrents.
 */
final class Connection
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly string $path)
    {
    }

    public function pdo(): \PDO
    {
        return $this->pdo ??= $this->open();
    }

    private function open(): \PDO
    {
        if (!is_file($this->path)) {
            throw new \RuntimeException(sprintf('Base introuvable en lecture seule : %s', $this->path));
        }

        $pdo = new \PDO('sqlite:' . $this->path, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::SQLITE_ATTR_OPEN_FLAGS => \PDO::SQLITE_OPEN_READONLY,
        ]);
        $pdo->setAttribute(\PDO::SQLITE_ATTR_READONLY_STATEMENT, true);
        $pdo->exec('PRAGMA query_only = ON');

        return $pdo;
    }
}
