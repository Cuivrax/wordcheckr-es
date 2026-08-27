<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage en entonnoir d'une page "mots terminant par {suffixe}" (tâche de dimensionnement
 * 2026-08-18, ouverture de `/mots/terminant/{suffixe de 2 à 4 lettres}` à l'indexation) : depuis
 * une page `/mots/terminant/{suffixe}` de longueur 1, 2 ou 3 lettres, liens vers chaque extension
 * d'UNE lettre supplémentaire — ajoutée AU DÉBUT du suffixe (ex. "NG" → "ING", "ANG", "ONG"...),
 * jamais à la fin (contrairement au préfixe, voir App\Search\PrefixExtensionLinks) — qui a au
 * moins un résultat réel.
 *
 * Précalculé (`list_counts`, `list_type` 'suffix2'/'suffix3'/'suffix4',
 * `scripts/build_explore_hub_counts.php`), jamais un `GROUP BY` sur `terms` au runtime.
 *
 * Grammaire : `App\Seo\Family::WORD_LIST_TERMINANT` accepte déjà `/mots/terminant/{lettres
 * 1 à 15}` sans restriction de longueur (`scripts/apply_seo_batch.php`,
 * `familySeoBatchRouteShapeError()`, R4b) — aucune nouvelle classification de famille n'est
 * nécessaire, seul un dimensionnement de lot (hors périmètre `data-engine`).
 */
final class SuffixExtensionLinks
{
    /** @param list<array{suffix: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
