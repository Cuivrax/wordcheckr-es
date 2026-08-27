<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Autocompletion (Phase 5, docs/08) : jusqu'a MAX_RESULTS suggestions dont la forme normalisee
 * commence exactement par le prefixe saisi -- prefixe EXACT uniquement, jamais de recherche
 * floue (pas de tolerance aux fautes, pas de recherche par sous-chaine).
 *
 * ## Budget -- une seule requete SQLite, tres en-dessous du plafond de moins de 10 par fiche
 * (CLAUDE.md). Cette route est independante de /mot/{mot} : elle n'ajoute rien au budget de
 * TermLookup::find() ni de RelationsFinder::find(), route HTTP distincte (GET /api/suggest).
 *
 * ## Meme technique de plage que WordListSolver/RelationsFinder pour "commencant"/rallonges
 *
 * `normalized >= prefixe AND normalized < incrementer(prefixe)`, jamais `LIKE 'prefixe%'`
 * (schema.sql : LIKE est insensible a la casse par defaut dans SQLite, l'optimiseur ne peut
 * pas l'adosser a l'index BINARY sur normalized -- la requete degenererait en SCAN complet des
 * 838 180 lignes). rangeBounds() est dupliquee ici plutot que partagee -- meme convention que
 * RelationsFinder::rangeBounds() et WordListSolver::rangeBounds(), chaque classe de
 * app/Search/ reste independante.
 *
 * ## Validation du prefixe : reutilise Normalizer::isValid() tel quel
 *
 * Un prefixe exploitable a EXACTEMENT la meme forme qu'un terme valide (2 a 15 lettres A-Z,
 * D-010) : en-dessous de 2 lettres, aucune suggestion n'a de sens (bruit) ; au-dela de 15
 * lettres, aucune ligne de la base ne peut jamais commencer par un prefixe plus long que la
 * plus longue forme retenue -- la requete serait garantie vide, inutile de l'executer.
 * Reutiliser Normalizer::isValid() evite une deuxieme regle de validation a maintenir en
 * parallele (meme esprit que D-009 : une seule source de verite).
 *
 * Une entree vide, trop courte, non-UTF-8, ou porteuse d'un caractere hors A-Z (apostrophe,
 * espace, chiffre...) renvoie simplement un tableau vide, jamais une erreur -- meme convention
 * que TermLookup::find()/RackSolver::solve()/WordListSolver::solve() renvoyant null avant toute
 * requete pour une entree non exploitable.
 */
final class Suggester
{
    /** Huit suggestions au plus (docs/08, Phase 5). */
    public const MAX_RESULTS = 8;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, status: string}>
     */
    public function suggest(string $rawInput): array
    {
        $normalized = Normalizer::normalize($rawInput);

        if (!Normalizer::isValid($normalized)) {
            return [];
        }

        [$lower, $upper] = self::rangeBounds($normalized);

        $conditions = ['normalized >= ?'];
        $params = [$lower];

        if ($upper !== null) {
            $conditions[] = 'normalized < ?';
            $params[] = $upper;
        }

        $statement = $this->connection->pdo()->prepare(
            'SELECT normalized, score, length, is_ods8, is_ods9 FROM terms WHERE '
            . implode(' AND ', $conditions) . ' ORDER BY normalized LIMIT ?'
        );
        $statement->execute([...$params, self::MAX_RESULTS]);

        return self::toItems($statement->fetchAll());
    }

    /**
     * Bornes [inclusive, exclusive) d'une plage de prefixe sur une colonne triee en ordre
     * binaire (A-Z uniquement, D-009) -- copie litterale de RelationsFinder::rangeBounds() /
     * WordListSolver::rangeBounds(), meme convention de duplication assumee dans app/Search/.
     *
     * @return array{0: string, 1: string|null}
     */
    private static function rangeBounds(string $prefix): array
    {
        $chars = str_split($prefix);

        for ($i = count($chars) - 1; $i >= 0; $i--) {
            if ($chars[$i] !== 'Z') {
                $chars[$i] = chr(ord($chars[$i]) + 1);

                return [$prefix, implode('', array_slice($chars, 0, $i + 1))];
            }
        }

        return [$prefix, null];
    }

    /**
     * Meme forme d'element que WordListSolver::toItems() : le modele a trois statuts reste
     * ferme (CLAUDE.md) -- $status ne prend jamais que TermPage::STATUS_ADMITTED ou
     * TermPage::STATUS_FRENCH_NOT_ADMITTED ici (toute ligne de `terms` est is_french = 1 par
     * construction, D-013 : jamais STATUS_UNKNOWN pour une ligne presente en base). Le detail
     * "ODS8 seul / ODS9 seul / ODS8+ODS9 / francais non admis" demande par la tache se lit en
     * combinant $status, $isOds8 et $isOds9 -- ces trois champs sont les seules donnees
     * exposees ici ; composer le libelle affiche est un choix de rendu (app/View/, hors
     * perimetre de cet agent), pas une donnee supplementaire a stocker ou a calculer cote
     * backend.
     *
     * @param list<array{normalized: string, score: string|int, length: string|int, is_ods8: string|int, is_ods9: string|int}> $rows
     * @return list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, status: string}>
     */
    private static function toItems(array $rows): array
    {
        return array_map(static function (array $row): array {
            $isOds8 = (int) $row['is_ods8'] === 1;
            $isOds9 = (int) $row['is_ods9'] === 1;

            return [
                'normalized' => $row['normalized'],
                'slug' => strtolower($row['normalized']),
                'score' => (int) $row['score'],
                'length' => (int) $row['length'],
                'isOds8' => $isOds8,
                'isOds9' => $isOds9,
                'status' => ($isOds8 || $isOds9) ? TermPage::STATUS_ADMITTED : TermPage::STATUS_FRENCH_NOT_ADMITTED,
            ];
        }, $rows);
    }
}
