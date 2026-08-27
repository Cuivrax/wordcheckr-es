<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Fiche mot consommee directement par la couche de rendu (app/View/, hors perimetre de
 * cet agent -- livree par l'agent frontend en Phase 1b).
 *
 * N'existe que pour une entree dont la forme est syntaxiquement valide : un terme de
 * forme invalide n'atteint jamais ce point (voir TermLookup::find(), qui renvoie null
 * avant toute construction de fiche).
 *
 * Le modele a trois statuts est ferme (CLAUDE.md) : $status ne prend jamais que l'une
 * des trois constantes STATUS_* ci-dessous.
 *
 * display_term == normalized sur toute ligne de la base (D-013) : un seul champ
 * "normalized" est expose, pas de doublon display/normalized.
 *
 * pos/posSecondary/gender (D-018) : nature grammaticale et genre, sur CHAQUE terme
 * TROUVE (admis ou non -- independant du statut Scrabble, voir docs/DECISIONS.md D-018),
 * jamais pour un terme inconnu ($found === false). Nullables : ~12,3 % des termes n'ont
 * aucune ligne Kartmaan exploitable (formes venues uniquement de hbenbel ou des
 * additions ODS9) -- absence de donnee, pas une erreur. pos/posSecondary : jeu ferme de 9
 * codes (N, V, Adj, Adv, Pronom, Prep, Conj, Interj, Art). gender : 'm', 'f' ou 'e'
 * (epicene -- meme forme aux deux genres, ex. ENFANT, ELEVE), jamais associe a un pos
 * autre que N.
 */
final class TermPage
{
    public const STATUS_ADMITTED = 'admitted';
    public const STATUS_FRENCH_NOT_ADMITTED = 'french_not_admitted';
    public const STATUS_UNKNOWN = 'unknown';

    /**
     * @param list<array{letter: string, value: int}> $letters lettre et valeur de
     *        tuile, dans l'ordre du mot ; la somme des valeurs egale toujours $score
     * @param string|null $previousWord forme normalisee (majuscules) du mot
     *        alphabetiquement precedent en base, ou null en debut de base
     * @param string|null $nextWord forme normalisee du mot alphabetiquement suivant,
     *        ou null en fin de base
     * @param string|null $pos nature grammaticale primaire (D-018), null si non trouve
     *        ou non couvert par Kartmaan
     * @param string|null $posSecondary second sens grammaticale distinct, si l'homographe
     *        en porte un (ex. TABLE : pos=N, posSecondary=V)
     * @param string|null $gender 'm', 'f' ou 'e', null si non applicable ou non couvert
     */
    public function __construct(
        public readonly string $normalized,
        public readonly string $slug,
        public readonly bool $found,
        public readonly string $status,
        public readonly int $score,
        public readonly int $length,
        public readonly bool $isOds8,
        public readonly bool $isOds9,
        public readonly array $letters,
        public readonly ?string $previousWord,
        public readonly ?string $nextWord,
        public readonly ?string $pos = null,
        public readonly ?string $posSecondary = null,
        public readonly ?string $gender = null,
    ) {
    }
}
