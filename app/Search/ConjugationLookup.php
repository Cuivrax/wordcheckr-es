<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Liens de conjugaison de la fiche mot /mot/{mot} (D-018), pour tout terme TROUVE (admis ou
 * non -- independant du statut Scrabble, requirement 1 de D-018 : "nature grammaticale +
 * genre sur CHAQUE terme"). L'appelant (routeur) n'invoque find() que si
 * TermPage::$found === true -- meme convention que RelationsFinder, qui fait confiance a
 * l'appelant plutot que de revalider. verb_forms ne peut par construction contenir aucune
 * ligne referencant une forme absente de `terms` (garanti au build, scripts/import_fr.py,
 * D-010 applique automatiquement) : pour un terme inconnu, la requete serait donc toujours
 * vide -- le routeur economise l'appel plutot que de l'executer pour rien.
 *
 * Budget : UNE requete SQLite, grace a un seul `OR` combinant les deux directions (lemme ->
 * formes, forme -> lemme) sur les deux colonnes indexees independamment
 * (idx_verbforms_lemma, idx_verbforms_form) -- SQLite satisfait un OR entre deux conditions
 * sur deux colonnes indexees differentes sans SCAN TABLE (verifie par EXPLAIN QUERY PLAN,
 * voir reports/query-plans/d018-conjugation.md). Porte le budget total de la fiche a 9
 * requetes pour un mot admis (8 existantes + 1), 4 pour un mot francais non admis (3 + 1),
 * toujours sous le plafond de moins de 10 (CLAUDE.md).
 */
final class ConjugationLookup
{
    /**
     * Plafond de lignes lues : jusqu'a 20 formes/lemme (D-018) + une poignee de lignes de
     * retour (une forme appartient rarement a plus d'un lemme -- rare homographe entre deux
     * verbes, jamais observe sur la base construite). Genereux mais borne -- meme discipline
     * que RelationsFinder::DISPLAY_LIMIT_PER_CATEGORY.
     */
    public const ROW_LIMIT = 40;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function find(string $normalized): Conjugation
    {
        $statement = $this->connection->pdo()->prepare(
            'SELECT lemma_normalized, form_normalized, tense, person FROM verb_forms '
            . 'WHERE lemma_normalized = ? OR form_normalized = ? LIMIT ?'
        );
        $statement->execute([$normalized, $normalized, self::ROW_LIMIT]);
        $rows = $statement->fetchAll();

        $asLemma = [];
        $asForm = [];

        foreach ($rows as $row) {
            if ($row['lemma_normalized'] === $normalized) {
                $asLemma[] = [
                    'form' => $row['form_normalized'],
                    'slug' => strtolower($row['form_normalized']),
                    'tense' => $row['tense'],
                    'person' => $row['person'],
                ];
            }

            // Pas de "elseif" : une ligne dont le lemme ET la forme egaleraient
            // $normalized (jamais produite par scripts/import_fr.py -- la morphologie
            // francaise ne fait jamais coincider un infinitif avec l'une de ses propres
            // formes conjuguees) apparaitrait alors dans les deux tableaux plutot que
            // silencieusement dans un seul -- defense en profondeur, jamais observee en
            // pratique.
            if ($row['form_normalized'] === $normalized) {
                $asForm[] = [
                    'lemma' => $row['lemma_normalized'],
                    'slug' => strtolower($row['lemma_normalized']),
                    'tense' => $row['tense'],
                    'person' => $row['person'],
                ];
            }
        }

        usort($asLemma, static fn (array $a, array $b): int => $a['form'] <=> $b['form']);
        usort($asForm, static fn (array $a, array $b): int => $a['lemma'] <=> $b['lemma']);

        return new Conjugation(asLemma: $asLemma, asForm: $asForm, queryCount: 1);
    }
}
