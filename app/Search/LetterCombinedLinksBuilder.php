<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\LetterCombinedLinks depuis list_counts (D-024), meme principe que
 * App\Search\LengthLinksBuilder -- une seule requete triviale, aucun GROUP BY sur `terms` au
 * runtime (voir scripts/build_explore_hub_counts.php pour la mesure qui impose ce detour).
 *
 * list_key est toujours "{debut}:{fin}" pour 'start_end'. Le cote "fin" n'a pas de prefixe
 * exploitable par un index (`LIKE '%:Y'`, jokers en tete) -- accepte tel quel : list_counts ne
 * compte que 1 731 lignes au total, un SCAN complet reste trivial (aucun rapport avec un SCAN
 * sur `terms`, 838 180 lignes).
 *
 * Budget runtime : 1 requete SQLite par page (buildForStart() OU buildForEnd(), jamais les
 * deux sur la meme page).
 */
final class LetterCombinedLinksBuilder
{
    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à la variante commençant+terminant
     * SANS longueur (D-041, garde-fou structurel demandé par le constat C-4 du 4e audit consolidé,
     * docs/DECISIONS.md D-040) -- trouvés par le balayage GÉNÉRIQUE de tout le registre
     * (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 : 1 656 groupes,
     * 2 089 pages en excès), pas une comparaison ciblée à une seule paire de familles.
     *
     * Clé au format exact du `list_key` 'start_end' ("{début}:{fin}", D-024), comparée directement
     * à la clé reconstruite ci-dessous dans build(). Distinct des 52 doublons déjà exclus par
     * App\Search\LengthLinksBuilder::DUPLICATE_START_END_KEYS (D-025/I-1, sens inverse : une page
     * AVEC longueur dupliquant celle-ci -- la variante sans longueur y reste TOUJOURS gagnante,
     * jamais concernée par une exclusion) : ici, c'est cette page SANS longueur elle-même qui perd
     * face à une TROISIÈME famille (commençant/terminant multi-lettres, un seul mot suffit à
     * rendre "commençant/{X}/terminant/{Y}" et "terminant/{XY...}" identiques).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "commençant/{X}/terminant/{Y}" a TOUJOURS 2 composants. Perd systématiquement ici face à
     * l'adversaire à 1 seul composant de chaque groupe (commençant ou terminant multi-lettres, ex.
     * F:Q perd face à /mots/terminant/faq -- FAQ est le seul mot de la paire F:Q). Recalculé
     * indépendamment par échantillonnage direct contre `terms` (voir le rapport AFTER de cette
     * tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    public const EXTERNAL_DUPLICATE_KEYS = [
        'B:J', 'C:J', 'D:Q', 'F:J', 'F:Q', 'G:W', 'I:W', 'M:J',
        'M:V', 'N:W', 'O:J', 'O:Q', 'O:W', 'P:V', 'Q:C', 'Q:Q',
        'R:Q', 'R:W', 'S:V', 'T:J', 'T:Q', 'U:B', 'U:V', 'V:Q',
        'V:V', 'W:L', 'X:O', 'X:U', 'Y:P', 'Y:Q', 'Y:V', 'Z:J',
        'Z:Q',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /** Pour une page /mots/commencant/{X} : liens vers /mots/commencant/{X}/terminant/{Y}. */
    public function buildForStart(string $startLetter): LetterCombinedLinks
    {
        return $this->build($startLetter . ':%', fromStart: true);
    }

    /** Pour une page /mots/terminant/{Y} : liens vers /mots/commencant/{X}/terminant/{Y}. */
    public function buildForEnd(string $endLetter): LetterCombinedLinks
    {
        return $this->build('%:' . $endLetter, fromStart: false);
    }

    private function build(string $likePattern, bool $fromStart): LetterCombinedLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'start_end' AND list_key LIKE ?"
        );
        $statement->execute([$likePattern]);

        $links = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];

            if (in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)) {
                continue;
            }

            [$start, $end] = explode(':', $key, 2);
            $other = $fromStart ? $end : $start;

            $url = WordListFilters::fromPath('commencant/' . strtolower($start) . '/terminant/' . strtolower($end))?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['letter' => $other, 'url' => $url, 'count' => (int) $row['count']];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new LetterCombinedLinks(links: $links, queryCount: 1);
    }
}
