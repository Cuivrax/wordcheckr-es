<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Liens de conjugaison (D-018), pour un terme TROUVE quelconque (admis ou non -- independant
 * du statut Scrabble, meme perimetre que TermPage::$pos/$posSecondary/$gender). Construit par
 * ConjugationLookup::find(), consomme par la couche de rendu (app/View/, hors perimetre de cet
 * agent) -- meme principe que TermPage/TermRelations.
 *
 * $asLemma : le mot est un infinitif de verbe fiable (verb_forms.lemma_normalized le
 * reference) -- formes conjuguees representatives (present, futur, imparfait, participe
 * present, participe passe ; jusqu'a 20 par lemme -- voir docs/DECISIONS.md D-018). Vide si le
 * mot n'est pas un verbe, ou si c'est un verbe suppletif exclu comme non fiable a la
 * construction (ETRE, AVOIR, ALLER, DEVOIR, VALOIR, VOIR... -- voir scripts/import_fr.py,
 * VERB_LEMMA_MIN_RELIABLE_FORMS : 281 lemmes exclus sur 6 697, limite connue et documentee,
 * pas un bug).
 *
 * $asForm : le mot est lui-meme une forme conjuguee d'un ou plusieurs lemmes (rarement plus
 * d'un -- homographe entre deux verbes). Vide si le mot n'est pas une forme conjuguee connue.
 *
 * Les deux tableaux peuvent etre simultanement non vides pour un homographe nom/verbe (ex.
 * TABLE : pos secondaire V, $asLemma vide puisque TABLE n'est pas lui-meme un infinitif connu,
 * mais $asForm non vide puisque TABLE EST une forme conjuguee de TABLER).
 */
final class Conjugation
{
    /**
     * @param list<array{form: string, slug: string, tense: string, person: string|null}> $asLemma
     * @param list<array{lemma: string, slug: string, tense: string, person: string|null}> $asForm
     */
    public function __construct(
        public readonly array $asLemma,
        public readonly array $asForm,
        public readonly int $queryCount,
    ) {
    }
}
