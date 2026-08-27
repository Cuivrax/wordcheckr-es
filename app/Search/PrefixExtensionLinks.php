<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage en entonnoir d'une page "mots commençant par {préfixe}" (tâche de dimensionnement
 * 2026-08-18, ouverture de `/mots/commencant/{préfixe de 2 à 4 lettres}` à l'indexation) : depuis
 * une page `/mots/commencant/{préfixe}` de longueur 1, 2 ou 3 lettres, liens vers chaque
 * extension d'UNE lettre supplémentaire (préfixe + 1 lettre) qui a au moins un résultat réel.
 *
 * Précalculé (`list_counts`, `list_type` 'prefix2'/'prefix3'/'prefix4',
 * `scripts/build_explore_hub_counts.php`), jamais un `GROUP BY` sur `terms` au runtime — même
 * principe que `App\Search\LetterCombinedLinks` (D-024) et `App\Search\AvecTwoLettersLinks`
 * (D-030).
 *
 * Grammaire : `App\Seo\Family::WORD_LIST_COMMENCANT` accepte déjà `/mots/commencant/{lettres
 * 1 à 15}` sans restriction de longueur (voir `scripts/apply_seo_batch.php`,
 * `familySeoBatchRouteShapeError()`, R4b) — aucune nouvelle classification de famille n'est
 * nécessaire pour ce maillage, seul un dimensionnement de lot (hors périmètre `data-engine`, à
 * instruire séparément par `seo-registry`).
 */
final class PrefixExtensionLinks
{
    /** @param list<array{prefix: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
