<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Definitions de la fiche mot /mot/{mot} (D-043), pour tout terme TROUVE (admis ou non --
 * meme perimetre que ConjugationLookup, requirement produit : "un mot qui verifie un mot veut
 * souvent savoir ce que c'est", deja invoque pour D-018). L'appelant (routeur) n'invoque
 * find() que si TermPage::$found === true -- meme convention que ConjugationLookup/
 * RelationsFinder, qui font confiance a l'appelant plutot que de revalider.
 *
 * Budget : UNE requete SQLite indexee (idx_word_senses_term), un seul cote de lookup (pas de
 * OR sur deux colonnes comme ConjugationLookup -- word_senses n'a qu'une seule direction,
 * terme -> sens). Cette requete +1 aurait porte le budget total de la fiche a 10 pour un mot
 * admis, AU-DESSUS du plafond "moins de 10" de CLAUDE.md -- compense par la fusion des deux
 * requetes "mot precedent"/"mot suivant" de App\Search\TermLookup::neighbours() en une seule
 * (D-043, UNION ALL) : budget final 9 pour un mot admis (3 TermLookup + 5 RelationsFinder + 1
 * ConjugationLookup + 1 ici), 4 pour un mot francais non admis (3 + 1), voir
 * reports/query-plans/d043-neighbour-merge.md pour la mesure complete.
 */
final class SenseLookup
{
    /**
     * Plafond de lignes lues : au plus MAX_SENSES (2, scripts/lib/reference_definitions.py)
     * par construction du pipeline de generation -- genereux mais borne, meme discipline que
     * ConjugationLookup::ROW_LIMIT.
     */
    public const ROW_LIMIT = 5;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function find(string $normalized): WordSenses
    {
        $statement = $this->connection->pdo()->prepare(
            'SELECT pos, gender, definition, source FROM word_senses '
            . 'WHERE term_normalized = ? ORDER BY sense_rank LIMIT ?'
        );
        $statement->execute([$normalized, self::ROW_LIMIT]);
        $rows = $statement->fetchAll();

        $senses = array_map(
            static fn (array $row): array => [
                'pos' => $row['pos'],
                'gender' => $row['gender'],
                'definition' => $row['definition'],
                'source' => $row['source'],
            ],
            $rows,
        );

        return new WordSenses(senses: $senses, queryCount: 1);
    }
}
