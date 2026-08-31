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
 * B-TREE), tres au-dessus du budget TTFB p95 < 250 ms pour une seule page (CLAUDE.md).
 *
 * list_counts n'est PLUS une petite table (94 760 lignes reelles, 20 list_type -- ES-022 puis
 * regeneration du correctif C-1, 2026-08-31) -- un `SELECT ... FROM list_counts` sans filtre
 * etait un SCAN complet (~58-64 ms mesures alors que le hub n'exploite que 3 list_type).
 * Correctif C-3 (audits croises
 * 2026-08-31, voir reports/query-plans/es-c3-explore-hub-builder.md) : requete PREPAREE, filtree
 * `WHERE list_type IN ('length', 'start', 'end') ORDER BY list_type, list_key` -- servie par la
 * cle primaire (list_type, list_key), 68 lignes au plus (14 longueurs + 27 buckets 'start' +
 * 27 buckets 'end', bornes par construction), ~0,1 ms. `LIMIT 100` en garde-fou dur (CLAUDE.md :
 * "LIMIT strict systematique"), tres au-dessus du maximum structurel de 68 ; build() LEVE une
 * RuntimeException si les 100 lignes sont ramenees (troncature silencieuse du hub impossible).
 *
 * Budget runtime : 1 requete SQLite indexee, aucun GROUP BY, aucun SCAN de `terms` ni de
 * list_counts -- tres en-dessous du plafond de moins de 10 (CLAUDE.md).
 */
final class ExploreHubBuilder
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(): ExploreHub
    {
        $statement = $this->connection->pdo()->prepare(
            'SELECT list_type, list_key, count FROM list_counts'
            . " WHERE list_type IN ('length', 'start', 'end')"
            . ' ORDER BY list_type, list_key'
            . ' LIMIT 100'
        );
        $statement->execute();
        $rows = $statement->fetchAll();

        // Le maximum structurel est 68 (14 longueurs + 27 buckets 'start' + 27 buckets 'end').
        // LIMIT 100 est un garde-fou dur (CLAUDE.md) : s'il est atteint, la donnee a change de
        // nature et le hub serait tronque SILENCIEUSEMENT -- on echoue bruyamment a la place.
        if (count($rows) === 100) {
            throw new \RuntimeException(
                'ExploreHubBuilder : plafond LIMIT 100 atteint sur list_counts (length/start/end)'
                . ' -- le maximum structurel de 68 est depasse, la requete et cette garde doivent etre revues.'
            );
        }

        $byLength = [];
        $byStart = [];
        $byEnd = [];

        foreach ($rows as $row) {
            $key = (string) $row['list_key'];
            $count = (int) $row['count'];

            switch ($row['list_type']) {
                case 'length':
                    $url = WordListFilters::fromPath($key . '-letras')?->canonicalUrl();

                    if ($url !== null) {
                        $byLength[] = ['length' => (int) $key, 'url' => $url, 'count' => $count];
                    }
                    break;

                case 'start':
                    $url = WordListFilters::fromPath('empiezan-por/' . mb_strtolower($key, 'UTF-8'))?->canonicalUrl();

                    if ($url !== null) {
                        $byStart[] = ['letter' => $key, 'url' => $url, 'count' => $count];
                    }
                    break;

                case 'end':
                    $url = WordListFilters::fromPath('terminan-en/' . mb_strtolower($key, 'UTF-8'))?->canonicalUrl();

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
