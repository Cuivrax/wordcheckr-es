<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Definitions (D-0XX, pilote 100 mots -- voir reports/definitions-nature-feasibility-audit.md),
 * pour un terme TROUVE quelconque (admis ou non -- meme perimetre que TermPage::$pos et
 * Conjugation, independant du statut Scrabble). Construit par SenseLookup::find(), consomme
 * par la couche de rendu (app/View/).
 *
 * $senses est vide pour la tres grande majorite des termes actuellement en base : le lot
 * genere ne couvre encore que le pilote (99 mots sur 838 180), pas un manque de donnee pour
 * les autres -- meme convention "absence de donnee, pas une erreur" que $pos/$gender (D-018).
 *
 * Chaque definition provient soit d'un gabarit grammatical (source 'template', zero cout,
 * jamais un appel LLM -- formes flechies), soit d'une reformulation LLM ancree sur une
 * reference (source 'kartmaan'/'kaikki', jamais copiee telle quelle -- garde-fou anti-copie
 * programmatique applique a la generation, scripts/generate_word_senses.py), soit sans aucune
 * reference disponible (source 'llm-only', prudence demandee au modele). `source` n'est
 * jamais affiche au visiteur -- traçabilite interne uniquement.
 */
final class WordSenses
{
    /**
     * @param list<array{pos: string, gender: string|null, definition: string, source: string}> $senses
     */
    public function __construct(
        public readonly array $senses,
        public readonly int $queryCount,
    ) {
    }
}
