<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Recherche d'un terme exact, mot precedent, mot suivant (Phase 1, docs/08).
 *
 * Budget : au plus 2 requetes SQLite par appel a find() (lookupRow() + neighbours()
 * fusionnee, D-043 -- mot precedent ET suivant en UNE requete UNION ALL, plutot que
 * deux requetes separees comme avant D-043), toutes avec LIMIT 1 par cote, toutes
 * servies par l'index UNIQUE sur normalized (sqlite_autoindex_terms_1) -- aucun scan,
 * voir reports/query-plans/phase1.md pour les plans d'origine et
 * reports/query-plans/d043-neighbour-merge.md pour la fusion. Correctif necessaire
 * par App\Search\SenseLookup (D-043, +1 requete) pour rester sous le plafond "moins
 * de 10" (CLAUDE.md) : 3+1(neighbours fusionnees en 1)+5(relations)+1(conjugaison)+
 * 1(sens) = 9 pour un mot admis, pas 10. Une forme d'entree invalide n'engendre
 * aucune requete : find() renvoie null avant toute ouverture de curseur.
 *
 * Les relations (anagrammes, sous-mots, rallonges...) sont hors perimetre de la
 * Phase 1 (Phase 4) ; les recherches liees statiques (longueur, prefixe...) egalement
 * (Phase 3).
 */
final class TermLookup
{
    /**
     * @param array<string, int> $tileScores
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly array $tileScores,
    ) {
    }

    /**
     * Normalise l'entree brute et construit la fiche mot correspondante.
     *
     * Renvoie null si la forme normalisee n'est pas un terme valide (2 a 15 lettres
     * A-Z) : c'est une erreur de saisie, pas un statut de terme (le modele a trois
     * statuts reste ferme), et elle n'engendre aucune requete SQLite. Au routeur de
     * traduire ce null en reponse 404.
     */
    public function find(string $rawInput): ?TermPage
    {
        $normalized = Normalizer::normalize($rawInput);

        if (!Normalizer::isValid($normalized)) {
            return null;
        }

        $row = $this->lookupRow($normalized);
        $found = $row !== null;

        $isOds8 = $found && (int) $row['is_ods8'] === 1;
        $isOds9 = $found && (int) $row['is_ods9'] === 1;

        $status = match (true) {
            $isOds8 || $isOds9 => TermPage::STATUS_ADMITTED,
            $found => TermPage::STATUS_FRENCH_NOT_ADMITTED,
            default => TermPage::STATUS_UNKNOWN,
        };

        $letters = $this->tiles($normalized);
        $score = $found ? (int) $row['score'] : Normalizer::score($normalized, $this->tileScores);
        $length = $found ? (int) $row['length'] : strlen($normalized);
        [$previousWord, $nextWord] = $this->neighbours($normalized);

        return new TermPage(
            normalized: $normalized,
            slug: strtolower($normalized),
            found: $found,
            status: $status,
            score: $score,
            length: $length,
            isOds8: $isOds8,
            isOds9: $isOds9,
            letters: $letters,
            previousWord: $previousWord,
            nextWord: $nextWord,
            // D-018 : uniquement pour un terme trouve -- $row est null sinon, ces trois
            // champs restent a leur defaut TermPage (null), jamais une fiche "inconnu"
            // avec une nature grammaticale.
            pos: $found ? $row['pos'] : null,
            posSecondary: $found ? $row['pos_secondary'] : null,
            gender: $found ? $row['gender'] : null,
        );
    }

    /**
     * @return array{display_term: string, score: string|int, length: string|int, is_ods8: string|int, is_ods9: string|int, pos: string|null, pos_secondary: string|null, gender: string|null}|null
     */
    private function lookupRow(string $normalized): ?array
    {
        // D-018 : pos/pos_secondary/gender ajoutes a ce SELECT existant -- ZERO requete
        // SQLite supplementaire (meme requete, meme plan que Phase 1 : recherche sur
        // l'index unique sqlite_autoindex_terms_1, puis lecture de la ligne complete,
        // deja necessaire pour display_term/score qui ne sont pas non plus couverts par
        // cet index).
        $statement = $this->connection->pdo()->prepare(
            'SELECT display_term, score, length, is_ods8, is_ods9, pos, pos_secondary, gender '
            . 'FROM terms WHERE normalized = ? LIMIT 1'
        );
        $statement->execute([$normalized]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Mot precedent et suivant en UNE SEULE requete (D-043, fusion -- deux SELECT bornes
     * chacun par leur propre ORDER BY/LIMIT, combines par UNION ALL). Chaque cote reste
     * servi par l'index unique sur normalized, exactement comme les deux requetes
     * separees qu'elle remplace (verifie par EXPLAIN QUERY PLAN, reports/query-plans/
     * d043-neighbour-merge.md) -- aucune ligne ne peut jamais satisfaire les deux
     * conditions a la fois (< et > au meme normalized), donc aucune ambiguite a
     * departager entre les 0, 1 ou 2 lignes renvoyees : normalized < $normalized est
     * toujours le precedent, normalized > $normalized est toujours le suivant.
     *
     * @return array{0: ?string, 1: ?string} [previousWord, nextWord]
     */
    private function neighbours(string $normalized): array
    {
        $statement = $this->connection->pdo()->prepare(
            'SELECT normalized FROM (SELECT normalized FROM terms WHERE normalized < ? ORDER BY normalized DESC LIMIT 1)'
            . ' UNION ALL '
            . 'SELECT normalized FROM (SELECT normalized FROM terms WHERE normalized > ? ORDER BY normalized ASC LIMIT 1)'
        );
        $statement->execute([$normalized, $normalized]);

        $previousWord = null;
        $nextWord = null;

        foreach ($statement->fetchAll() as $row) {
            if ($row['normalized'] < $normalized) {
                $previousWord = $row['normalized'];
            } else {
                $nextWord = $row['normalized'];
            }
        }

        return [$previousWord, $nextWord];
    }

    /**
     * Defense en profondeur (audit Phase 1, C2) : une lettre absente de
     * $this->tileScores leve plutot que de produire une valeur nulle silencieuse --
     * voir la meme regle dans Normalizer::score().
     *
     * @return list<array{letter: string, value: int}>
     */
    private function tiles(string $normalized): array
    {
        $tiles = [];

        foreach (str_split($normalized) as $letter) {
            if (!array_key_exists($letter, $this->tileScores)) {
                throw new \InvalidArgumentException(sprintf('Lettre sans valeur de tuile : %s', $letter));
            }

            $tiles[] = ['letter' => $letter, 'value' => $this->tileScores[$letter]];
        }

        return $tiles;
    }
}
