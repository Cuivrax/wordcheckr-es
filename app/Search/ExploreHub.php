<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Page hub /mots (route jusque-la en 404, WordListSolver::solve('') renvoie null pour un
 * chemin vide -- voir public/index.php). Trois grilles completes vers les familles deja
 * indexees et deja finies (D-017) : longueur (14), commencant (26), terminant (26) -- 66
 * liens, chacun avec son compte reel, construits via WordListFilters::canonicalUrl() comme
 * partout ailleurs sur ce projet, jamais une URL assemblee a la main.
 *
 * "Contenant" n'a JAMAIS de grille ici : famille a combinaisons infinies
 * (App\Seo\Family::NEVER_SITEMAP), jamais indexable par defaut. La vue expose seulement un
 * champ de recherche borne (au plus 3 lettres, decision produit) qui soumet vers
 * /mots/contenant/{lettres} -- un outil, pas une liste de pages pre-generees.
 */
final class ExploreHub
{
    /**
     * @param list<array{length: int, url: string, count: int}> $byLength
     * @param list<array{letter: string, url: string, count: int}> $byStart
     * @param list<array{letter: string, url: string, count: int}> $byEnd
     */
    public function __construct(
        public readonly array $byLength,
        public readonly array $byStart,
        public readonly array $byEnd,
        public readonly int $queryCount,
    ) {
    }
}
