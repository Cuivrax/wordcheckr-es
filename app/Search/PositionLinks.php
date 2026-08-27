<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots de {N} lettres avec {X}" (D-023bis) : liens vers chaque
 * position ou cette lettre apparait reellement au sein des mots de cette longueur -- repond a
 * "je veux les mots de 9 lettres avec un W en 3e position" depuis la page "avec W" generique.
 *
 * Position 1 et derniere position pointent vers /mots/{N}-lettres/commencant|terminant/{X}
 * (deja existants, D-017/D-022), jamais vers une URL "position/1/..." ou "position/{N}/..."
 * qui n'existe pas (D-023 les collapse toujours en amont, voir WordListFilters). Les positions
 * intermediaires pointent vers /mots/{N}-lettres/position/{P}/{X} (D-023).
 *
 * Precalcule (list_counts, list_type 'length_with_position', scripts/build_explore_hub_counts.php),
 * jamais un calcul au runtime.
 */
final class PositionLinks
{
    /** @param list<array{position: int, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
