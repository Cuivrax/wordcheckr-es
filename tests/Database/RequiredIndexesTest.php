<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * Garde-fou trouvé manquant par l'audit 2e passe du lot D-025 (seo-technical-auditor) :
 * plusieurs correctifs de performance de ce projet (D-020, D-021, D-025bis) dépendent d'un
 * index précis existant réellement dans storage/dictionary_fr.sqlite, avec ses statistiques
 * ANALYZE à jour -- sans quoi la régression corrigée peut revenir en silence (base copiée
 * d'avant le correctif, reconstruction partielle) sans qu'aucun test applicatif ne le
 * détecte : WordListSolverTest.php vérifie le résultat et $queryCount (indépendants du plan
 * choisi par SQLite), jamais le plan lui-même.
 *
 * Vérifie directement sqlite_master (l'index existe) ET sqlite_stat1 (ANALYZE a tourné dessus,
 * leçon D-021 : un index sans stats peut faire choisir un MAUVAIS plan, pas seulement un plan
 * sous-optimal) pour chaque index dont une régression mesurée et documentée dépend.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Chaque entree correspond a une regression mesuree et documentee (reports/query-plans/).
    $requiredIndexes = [
        'idx_terms_length_reversed' => 'D-020 : longueur+terminant sans cet index, jusqu\'a 1 779 ms mesure',
        'idx_terms_length_admitted_normalized' => 'D-022 : filtre statut EXACT sans cet index, jusqu\'a 1 286 ms mesure',
        'idx_terms_admitted_normalized' => 'D-022 : filtre statut BORNE sans ancrage de longueur',
        'idx_terms_length_score_normalized' => 'D-022 : tri par points EXACT sans cet index, jusqu\'a 870 ms mesure',
        'idx_terms_startletter_endletter_normalized' => 'D-025bis : commencant+terminant mono-lettre sans cet index, jusqu\'a 6 675 ms mesure',
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
