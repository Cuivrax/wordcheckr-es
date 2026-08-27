<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page mono-lettre (D-024) : depuis /mots/commencant/{X} (deja indexee,
 * D-017), liens vers /mots/commencant/{X}/terminant/{Y} pour chaque Y avec au moins un
 * resultat -- et symetriquement depuis /mots/terminant/{Y} vers le meme jeu de pages, dans
 * l'autre sens. Comble le probleme de page orpheline releve par l'agent seo-registry
 * (2026-08-09) avant toute ouverture de Family::WORD_LIST_COMBINED a l'indexation : ces pages
 * combinees n'avaient jusque-la AUCUN lien entrant reel depuis le site.
 *
 * Precalcule (list_counts, list_type 'start_end', scripts/build_explore_hub_counts.php),
 * jamais un GROUP BY au runtime -- meme principe que App\Search\LengthLinks (D-022).
 */
final class LetterCombinedLinks
{
    /** @param list<array{letter: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
