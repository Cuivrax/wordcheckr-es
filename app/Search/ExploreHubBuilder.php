<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\ExploreHub depuis la table list_counts, precalculee hors ligne par
 * scripts/build_explore_hub_counts.php.
 *
 * Mesure qui a impose ce detour (pas de GROUP BY direct au runtime) : un GROUP BY sur
 * substr(normalized,1,1) / substr(reversed,1,1) n'a aucun index disponible sur l'expression
 * calculee -- 245 ms et 215 ms mesures sur les 838 180 lignes reelles (SCAN complet + TEMP
 * B-TREE), tres au-dessus du budget TTFB p95 < 250 ms pour une seule page (CLAUDE.md). La
 * table list_counts (66 lignes fixes) rend cette lecture triviale.
 *
 * Budget runtime : 1 requete SQLite (SELECT * FROM list_counts), aucun GROUP BY, aucun SCAN
 * de `terms` -- tres en-dessous du plafond de moins de 10 (CLAUDE.md).
 */
final class ExploreHubBuilder
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(): ExploreHub
    {
        $statement = $this->connection->pdo()->query('SELECT list_type, list_key, count FROM list_counts');

        $byLength = [];
        $byStart = [];
        $byEnd = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];
            $count = (int) $row['count'];

            switch ($row['list_type']) {
                case 'length':
                    $url = WordListFilters::fromPath($key . '-lettres')?->canonicalUrl();

                    if ($url !== null) {
                        $byLength[] = ['length' => (int) $key, 'url' => $url, 'count' => $count];
                    }
                    break;

                case 'start':
                    $url = WordListFilters::fromPath('commencant/' . strtolower($key))?->canonicalUrl();

                    if ($url !== null) {
                        $byStart[] = ['letter' => $key, 'url' => $url, 'count' => $count];
                    }
                    break;

                case 'end':
                    $url = WordListFilters::fromPath('terminant/' . strtolower($key))?->canonicalUrl();

                    if ($url !== null) {
                        $byEnd[] = ['letter' => $key, 'url' => $url, 'count' => $count];
                    }
                    break;
            }
        }

        usort($byLength, static fn (array $a, array $b): int => $a['length'] <=> $b['length']);
        usort($byStart, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);
        usort($byEnd, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new ExploreHub(byLength: $byLength, byStart: $byStart, byEnd: $byEnd, queryCount: 1);
    }
}
