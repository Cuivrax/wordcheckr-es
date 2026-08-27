<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\LengthCombinedLinks depuis list_counts (list_type 'length_start_end',
 * D-027, voir reports/query-plans/length-combined-links.md) -- meme principe que
 * App\Search\LetterCombinedLinksBuilder (D-024) et App\Search\PositionLinksBuilder (D-023bis) :
 * une seule requete triviale, aucun GROUP BY sur `terms` au runtime.
 *
 * list_key est toujours "{longueur}:{debut}:{fin}" pour 'length_start_end'. Les deux sens de
 * lecture restent efficaces malgre le joker en tete cote "buildForEnd()" (`LIKE '{N}:%:{Y}'`) :
 * le prefixe litteral "{N}:" borne deja la recherche a une seule longueur (au plus 676 lignes,
 * 26 debuts x 26 fins), memes conditions de cout que le joker en tete deja accepte pour
 * App\Search\LetterCombinedLinksBuilder::buildForEnd() sur la table list_counts entiere (13 846
 * lignes au total, tous list_type confondus -- sans rapport avec le risque de SCAN sur `terms`,
 * 838 180 lignes, que ce projet interdit par ailleurs).
 *
 * Budget runtime : 1 requete SQLite par page (buildForStart() OU buildForEnd(), jamais les deux
 * sur la meme page -- une page ne peut jamais avoir a la fois "commencant" seul ET "terminant"
 * seul).
 */
final class LengthCombinedLinksBuilder
{
    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à la variante longueur+commençant+
     * terminant (D-041, garde-fou structurel demandé par le constat C-4 du 4e audit consolidé,
     * docs/DECISIONS.md D-040) -- trouvés par le balayage GÉNÉRIQUE de tout le registre
     * (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 : 1 656 groupes,
     * 2 089 pages en excès), pas une comparaison ciblée à une seule paire de familles.
     *
     * Clé au format exact du `list_key` 'length_start_end' ("{longueur}:{début}:{fin}", D-027),
     * comparée directement à la clé reconstruite ci-dessous dans build(). Ce même ensemble est
     * aussi utilisé par App\Search\LengthLinksBuilder (byStartEnd, qui cible la MÊME famille --
     * App\Seo\Family::WORD_LIST_COMBINED, variante avec longueur -- depuis une page source
     * différente, /mots/{N}-lettres) -- référencé depuis là-bas plutôt que dupliqué, une seule
     * source de vérité pour cette famille cible. Distinct de
     * App\Search\LengthLinksBuilder::DUPLICATE_START_END_KEYS (D-025/I-1, doublon avec la variante
     * SANS longueur du MÊME panier, 52 clés) : ici, le doublon est avec une AUTRE famille
     * (terminant/commençant multi-lettres, avec à N lettres, position...), jamais avec le panier
     * sans longueur lui-même.
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "{N}-lettres/commençant/{X}/terminant/{Y}" a TOUJOURS 3 composants. Perd face à tout
     * adversaire à 1 ou 2 composants (commençant/terminant multi-lettres seuls, avec à une lettre,
     * commençant+terminant sans longueur...), et perd aussi, à 3 composants égaux, face à
     * "position" (signature de rôles [longueur, commençant, terminant] précède [longueur,
     * position, position] -- "commençant" avant "position" dans l'ordre canonique, cette page
     * gagne dans CE cas précis) : cette liste ne contient donc AUCUN cas position-vs-combiné (voir
     * PositionLinksBuilder::EXTERNAL_DUPLICATE_KEYS, qui contient l'inverse : la variante position
     * qui perd face à CETTE famille). Recalculé indépendamment par échantillonnage direct contre
     * `terms` (voir le rapport AFTER de cette tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    public const EXTERNAL_DUPLICATE_KEYS = [
        '10:B:K', '10:F:Y', '10:I:K', '10:J:O', '10:J:P', '10:J:Y', '10:K:M', '10:K:Y',
        '10:M:Y', '10:O:P', '10:Y:O', '10:Y:U', '11:C:O', '11:J:C', '11:K:O', '11:K:V',
        '11:M:K', '11:M:U', '11:R:K', '11:T:M', '11:T:Y', '12:P:Y', '12:Y:I', '13:B:M',
        '13:I:O', '14:W:G', '15:K:N', '2:C:D', '2:C:V', '2:F:M', '2:G:D', '2:L:T',
        '2:M:C', '2:M:R', '2:N:B', '2:P:B', '2:P:D', '2:P:K', '2:P:M', '2:Q:I',
        '2:Q:U', '2:S:R', '2:U:A', '2:V:S', '2:W:U', '2:Y:I', '2:Z:A', '3:A:K',
        '3:A:Y', '3:B:W', '3:C:H', '3:C:O', '3:C:X', '3:C:Y', '3:D:G', '3:D:J',
        '3:E:C', '3:E:P', '3:F:B', '3:F:G', '3:F:K', '3:F:P', '3:F:Y', '3:F:Z',
        '3:G:B', '3:G:O', '3:G:P', '3:I:C', '3:I:D', '3:I:G', '3:I:H', '3:I:M',
        '3:J:D', '3:J:I', '3:J:P', '3:J:Z', '3:K:B', '3:K:D', '3:K:E', '3:K:L',
        '3:M:P', '3:N:B', '3:N:G', '3:N:K', '3:O:H', '3:P:D', '3:P:G', '3:Q:M',
        '3:Q:T', '3:S:B', '3:S:F', '3:S:W', '3:S:Z', '3:T:Z', '3:U:F', '3:U:L',
        '3:U:M', '3:V:D', '3:V:H', '3:V:M', '3:W:D', '3:W:H', '3:W:I', '3:W:K',
        '3:W:S', '3:W:U', '3:W:X', '3:Y:E', '3:Z:D', '3:Z:K', '3:Z:Y', '4:A:D',
        '4:A:P', '4:A:V', '4:C:Q', '4:D:W', '4:E:F', '4:E:X', '4:F:F', '4:G:B',
        '4:G:X', '4:H:K', '4:H:M', '4:I:K', '4:I:X', '4:J:H', '4:J:X', '4:J:Y',
        '4:K:D', '4:K:H', '4:K:M', '4:K:P', '4:K:R', '4:K:U', '4:K:Y', '4:L:B',
        '4:L:L', '4:M:F', '4:N:B', '4:N:M', '4:N:X', '4:P:B', '4:P:Z', '4:Q:E',
        '4:Q:G', '4:Q:Y', '4:Q:Z', '4:R:B', '4:S:Z', '4:U:F', '4:U:L', '4:U:Z',
        '4:V:F', '4:V:G', '4:V:K', '4:V:L', '4:W:F', '4:W:G', '4:W:M', '4:W:O',
        '4:W:U', '4:W:Z', '4:X:A', '4:Y:L', '4:Y:U', '4:Y:X', '4:Z:H', '4:Z:L',
        '4:Z:R', '5:A:G', '5:A:V', '5:A:Y', '5:B:P', '5:C:Q', '5:D:B', '5:E:P',
        '5:E:V', '5:E:Y', '5:H:B', '5:H:G', '5:I:H', '5:K:F', '5:K:G', '5:K:M',
        '5:K:P', '5:K:X', '5:L:M', '5:M:B', '5:N:G', '5:O:H', '5:O:K', '5:O:P',
        '5:O:X', '5:P:W', '5:Q:U', '5:S:W', '5:U:Z', '5:V:G', '5:V:Y', '5:W:X',
        '5:W:Y', '5:Y:M', '5:Y:U', '5:Y:Y', '6:A:G', '6:A:H', '6:D:B', '6:E:H',
        '6:F:B', '6:G:F', '6:G:H', '6:G:P', '6:H:J', '6:H:P', '6:I:G', '6:J:G',
        '6:J:H', '6:K:C', '6:K:G', '6:K:V', '6:K:Y', '6:L:B', '6:L:K', '6:L:P',
        '6:M:B', '6:N:B', '6:N:H', '6:O:H', '6:O:Y', '6:P:Y', '6:Q:G', '6:R:V',
        '6:S:P', '6:S:W', '6:T:P', '6:W:C', '6:W:F', '6:W:Y', '6:Z:C', '6:Z:F',
        '6:Z:M', '7:A:Y', '7:B:B', '7:B:Y', '7:D:B', '7:H:Y', '7:I:K', '7:I:Q',
        '7:J:C', '7:K:F', '7:K:J', '7:K:P', '7:K:Y', '7:N:Y', '7:O:D', '7:O:Y',
        '7:Q:Y', '7:V:Y', '7:W:F', '7:Y:D', '7:Y:K', '7:Z:M', '8:A:Y', '8:B:W',
        '8:F:B', '8:G:K', '8:H:C', '8:H:O', '8:H:Y', '8:I:K', '8:J:C', '8:K:J',
        '8:M:B', '8:Q:M', '8:R:B', '8:U:C', '8:V:K', '8:V:Y', '8:Y:Y', '8:Z:G',
        '8:Z:Y', '9:D:K', '9:G:K', '9:H:F', '9:I:Y', '9:K:H', '9:L:V', '9:N:H',
        '9:S:Y', '9:W:U', '9:W:X', '9:Y:H',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /** Pour une page /mots/{N}-lettres/commencant/{X} : liens vers .../commencant/{X}/terminant/{Y}. */
    public function buildForStart(int $length, string $startLetter): LengthCombinedLinks
    {
        return $this->build($length . ':' . $startLetter . ':%', $length, fromStart: true);
    }

    /** Pour une page /mots/{N}-lettres/terminant/{Y} : liens vers .../commencant/{X}/terminant/{Y}. */
    public function buildForEnd(int $length, string $endLetter): LengthCombinedLinks
    {
        return $this->build($length . ':%:' . $endLetter, $length, fromStart: false);
    }

    private function build(string $likePattern, int $length, bool $fromStart): LengthCombinedLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_start_end' AND list_key LIKE ?"
        );
        $statement->execute([$likePattern]);

        $links = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];

            if (in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)) {
                continue;
            }

            [, $start, $end] = explode(':', $key, 3);
            $other = $fromStart ? $end : $start;

            $url = WordListFilters::fromPath(
                $length . '-lettres/commencant/' . strtolower($start) . '/terminant/' . strtolower($end)
            )?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['letter' => $other, 'url' => $url, 'count' => (int) $row['count']];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new LengthCombinedLinks(links: $links, queryCount: 1);
    }
}
