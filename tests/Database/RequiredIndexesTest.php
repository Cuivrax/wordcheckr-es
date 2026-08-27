<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * Garde-fou herite du site francais (seo-technical-auditor, audit 2e passe du lot D-025) :
 * plusieurs correctifs de performance de ce projet dependent d'un index precis existant
 * reellement dans storage/dictionary_es.sqlite, avec ses statistiques ANALYZE a jour --
 * sans quoi la regression corrigee peut revenir en silence (base copiee d'avant le
 * correctif, reconstruction partielle) sans qu'aucun test applicatif ne le detecte :
 * WordListSolverTest.php verifie le resultat et $queryCount (independants du plan choisi
 * par SQLite), jamais le plan lui-meme.
 *
 * Verifie directement sqlite_master (l'index existe) ET sqlite_stat1 (ANALYZE a tourne
 * dessus -- un index sans stats peut faire choisir un MAUVAIS plan, pas seulement un plan
 * sous-optimal) pour chaque index de schema.sql -- meme les cinq portes ici, herites tels
 * quels du site francais (memes classes de bug de performance possibles sur cette base,
 * pas encore individuellement re-mesurees sur les donnees espagnoles, voir le rapport
 * AFTER).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Chaque entree correspond a un index de schema.sql cree pour une regression de
    // performance mesuree sur le site francais -- construit ici preventivement (meme
    // structure de donnees, memes classes de requete) plutot que d'attendre une regression
    // equivalente sur cette base avant d'agir.
    $requiredIndexes = [
        'idx_terms_length_reversed' => 'longueur+terminant sans cet index : jusqu\'a 1 779 ms mesure sur le site francais',
        'idx_terms_length_admitted_normalized' => 'filtre statut EXACT sans cet index : jusqu\'a 1 286 ms mesure sur le site francais',
        'idx_terms_admitted_normalized' => 'filtre statut BORNE sans ancrage de longueur',
        'idx_terms_length_score_normalized' => 'tri par points EXACT sans cet index : jusqu\'a 870 ms mesure sur le site francais',
        'idx_terms_startletter_endletter_normalized' => 'commencant+terminant mono-lettre sans cet index : jusqu\'a 6 675 ms mesure sur le site francais',
    ];

    foreach ($requiredIndexes as $indexName => $reason) {
        $existsRow = $pdo->query(
            "SELECT COUNT(*) c FROM sqlite_master WHERE type = 'index' AND name = '{$indexName}'"
        )->fetch();
        Assert::same(1, (int) $existsRow['c'], "index manquant : {$indexName} ({$reason})");

        $statRow = $pdo->query(
            "SELECT COUNT(*) c FROM sqlite_stat1 WHERE tbl = 'terms' AND idx = '{$indexName}'"
        )->fetch();
        Assert::same(1, (int) $statRow['c'], "ANALYZE jamais execute sur {$indexName} (sqlite_stat1 vide) -- risque D-021 : SQLite peut choisir un mauvais plan sans statistiques ({$reason})");
    }
};
