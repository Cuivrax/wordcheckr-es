<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots de {N} lettres commencant/terminant par {X}" (D-027) :
 * depuis une page longueur + UNE SEULE lettre "commencant" (SANS
 * "terminant"), liens vers chacune des variantes /mots/{N}-lettres/commencant/{X}/terminant/{Y}
 * qui a au moins un resultat -- et symetriquement depuis une page longueur + UNE SEULE lettre
 * "terminant" (SANS "commencant").
 *
 * Objectif : combler l'absence de lien entrant reel vers Family::WORD_LIST_COMBINED AVEC
 * longueur (5 141 pages eligibles sur 9 464 combinaisons possibles, aucune n'a de lien entrant
 * aujourd'hui) -- prealable identifie avant toute decision future d'ouverture a l'indexation de
 * ce sous-ensemble, meme role que App\Search\LetterCombinedLinks a joue pour le sous-ensemble
 * SANS longueur avant D-025. Cette classe ne decide d'aucune ouverture a l'indexation --
 * navigation seulement, meme si les pages ciblees restent noindex,follow par defaut (D-005)
 * tant qu'aucun lot ne les couvre.
 *
 * Precalcule (list_counts, list_type propose 'length_start_end',
 * scripts/build_explore_hub_counts.php), jamais un GROUP BY au runtime -- meme principe que
 * App\Search\LetterCombinedLinks (D-024) et App\Search\PositionLinks (D-023bis).
 */
final class LengthCombinedLinks
{
    /** @param list<array{letter: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
