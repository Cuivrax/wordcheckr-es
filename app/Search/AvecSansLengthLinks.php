<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots avec {X} sans {Y}" (D-024bis, sans longueur) : liens vers
 * chaque longueur ou cette combinaison a reellement des resultats -- repond a "je veux les
 * mots avec Q sans U, en 7 lettres" depuis la page generique "avec Q sans U".
 *
 * Cible Family::WORD_LIST_AVEC / WORD_LIST_SANS (combinaisons de lettres), toutes deux
 * PERMANENTES dans NEVER_SITEMAP -- ces liens et leur page cible restent noindex,follow par
 * defaut (D-005), navigation/decouverte uniquement, jamais l'indexation.
 *
 * Precalcule (list_counts, list_type 'length_avec_sans', scripts/build_explore_hub_counts.php),
 * jamais un calcul au runtime (mesure : 91-170 ms par combinaison en requete live, non sur).
 */
final class AvecSansLengthLinks
{
    /** @param list<array{length: int, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
