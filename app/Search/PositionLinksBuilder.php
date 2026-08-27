<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\PositionLinks depuis list_counts (D-023bis), meme principe que
 * App\Search\LengthLinksBuilder -- une seule requete triviale, aucun calcul sur `terms` au
 * runtime (voir scripts/build_explore_hub_counts.php pour la mesure qui impose ce detour).
 *
 * list_key est toujours "{longueur}:{lettre}:{position}" pour 'length_with_position' -- le
 * filtre `list_key LIKE '{longueur}:{lettre}:%'` reste un prefixe exact, servi par l'index de
 * cle primaire.
 */
final class PositionLinksBuilder
{
    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à "position" (D-041, garde-fou
     * structurel demandé par le constat C-4 du 4e audit consolidé, docs/DECISIONS.md D-040) --
     * trouvés par le balayage GÉNÉRIQUE de tout le registre
     * (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 : 1 656 groupes,
     * 2 089 pages en excès), pas une comparaison ciblée à une seule paire de familles.
     *
     * Clé au format exact du `list_key` 'length_with_position' ("{longueur}:{lettre}:{position}",
     * D-023bis), comparée directement à la clé reconstruite ci-dessous dans build(). Ce même
     * ensemble est aussi utilisé par App\Search\LengthLinksBuilder (byPosition, qui cible la MÊME
     * famille App\Seo\Family::WORD_LIST_POSITION depuis une page source différente,
     * /mots/{N}-lettres) -- référencé depuis là-bas plutôt que dupliqué, une seule source de
     * vérité pour cette famille cible.
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "position" a TOUJOURS 3 composants (longueur + position, qui vaut 2 à elle seule). Les 2
     * clés trouvées perdent toutes les deux :
     *   13:W:10  /mots/13-lettres/position/10/w  perd face à /mots/13-lettres/commencant/c/
     *            terminant/h (Family::WORD_LIST_COMBINED avec longueur, 3 composants -- signature
     *            de rôles [longueur, commençant, terminant] précède [longueur, position, position]
     *            dans l'ordre canonique, "commençant" avant "position")
     *   15:W:10  /mots/15-lettres/position/10/w  perd face à /mots/commencant/sask (1 composant)
     * Recalculé indépendamment par échantillonnage direct contre `terms` (voir le rapport AFTER
     * de cette tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    public const EXTERNAL_DUPLICATE_KEYS = ['13:W:10', '15:W:10'];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(int $length, string $letter): PositionLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_position' AND list_key LIKE ?"
        );
        $statement->execute([$length . ':' . $letter . ':%']);

        $links = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];

            if (in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)) {
                continue;
            }

            $parts = explode(':', $key);
            $position = (int) $parts[2];
            $count = (int) $row['count'];

            $path = match (true) {
                $position === 1 => $length . '-lettres/commencant/' . strtolower($letter),
                $position === $length => $length . '-lettres/terminant/' . strtolower($letter),
                default => $length . '-lettres/position/' . $position . '/' . strtolower($letter),
            };

            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['position' => $position, 'url' => $url, 'count' => $count];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['position'] <=> $b['position']);

        return new PositionLinks(links: $links, queryCount: 1);
    }
}
