<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Resultat de /mots/... (Phase 3, docs/08), consomme par la couche de rendu (app/View/,
 * hors perimetre de cet agent -- structure prete a rendre, meme principe que TermPage pour
 * /mot/{mot} et RackPage pour /jouer/{lettres}).
 *
 * Contrairement a RackPage (qui ne renvoie que des mots admis, "quel mot puis-je jouer"),
 * cette liste couvre TOUTE la base is_french = 1 -- une fiche par forme francaise, admise ou
 * non (D-006, D-011). Chaque entree porte donc un statut explicite parmi les trois valeurs
 * fermees de TermPage::STATUS_* (jamais STATUS_UNKNOWN : une ligne presente dans `terms`
 * est par construction is_french = 1, jamais un terme inconnu).
 *
 * Deux regimes, determines par WordListSolver selon les contraintes presentes (voir sa
 * documentation pour la mesure qui justifie ce choix) :
 *
 * - exact = true  : $total est le compte EXACT de toutes les correspondances (pas seulement
 *   la page courante), obtenu par un COUNT() indexe. Pagination complete et fiable.
 * - exact = false : les contraintes demandent des predicats non indexes (contenant, avec,
 *   sans, motif partiel) -- $total est un compte trouve dans la fenetre examinee
 *   (WordListSolver::ROW_EXAMINATION_CEILING lignes au plus), jamais au-dela. $truncated
 *   signale que d'autres correspondances pourraient exister au-dela de cette fenetre : ne
 *   jamais presenter $total comme un compte exhaustif dans ce cas.
 */
final class WordListPage
{
    /**
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, status: string}> $items
     *        page courante uniquement, taille au plus WordListSolver::PAGE_SIZE
     */
    public function __construct(
        public readonly string $canonicalPath,
        public readonly int $page,
        public readonly int $pageSize,
        public readonly array $items,
        public readonly int $total,
        public readonly bool $exact,
        public readonly bool $truncated,
        public readonly bool $hasNextPage,
        public readonly bool $hasPreviousPage,
        public readonly int $queryCount,
    ) {
    }
}
