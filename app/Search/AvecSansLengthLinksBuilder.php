<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\AvecSansLengthLinks depuis list_counts (D-024bis), meme principe que
 * App\Search\LengthLinksBuilder -- une seule requete triviale, aucun calcul sur `terms` au
 * runtime (voir scripts/build_explore_hub_counts.php pour la mesure qui impose ce detour).
 *
 * list_key est toujours "{avec}:{sans}:{longueur}" pour 'length_avec_sans' -- le filtre
 * `list_key LIKE '{avec}:{sans}:%'` reste un prefixe exact, servi par l'index de cle primaire.
 */
final class AvecSansLengthLinksBuilder
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(string $avecLetter, string $sansLetter): AvecSansLengthLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_avec_sans' AND list_key LIKE ?"
        );
        $statement->execute([$avecLetter . ':' . $sansLetter . ':%']);

        $links = [];

        foreach ($statement as $row) {
            $parts = explode(':', (string) $row['list_key']);
            $length = (int) $parts[2];
            $count = (int) $row['count'];

            // 'con-letras'/'sin' (ES-014) : anciennement 'avec'/'sans'. Ce chemin est
            // immediatement revalide par WordListFilters::fromPath() ci-dessous -- un mot-cle
            // perime y produirait un url null, donc un lien silencieusement absent.
            $path = $length . '-letras/con-letras/' . mb_strtolower($avecLetter, 'UTF-8') . '/sin/' . mb_strtolower($sansLetter, 'UTF-8');
            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['length' => $length, 'url' => $url, 'count' => $count];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['length'] <=> $b['length']);

        return new AvecSansLengthLinks(links: $links, queryCount: 1);
    }
}
