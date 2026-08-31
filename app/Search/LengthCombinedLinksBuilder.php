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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'length_start_end'
    // (URL /palabras/{N}-letras/empiezan-por/{X}/terminan-en/{Y},
    // famille WORD_LIST_COMBINED (variante 3 contraintes)) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    public const EXTERNAL_DUPLICATE_KEYS = [];

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
                $length . '-letras/empiezan-por/' . mb_strtolower($start, 'UTF-8') . '/terminan-en/' . mb_strtolower($end, 'UTF-8')
            )?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['letter' => $other, 'url' => $url, 'count' => (int) $row['count']];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new LengthCombinedLinks(links: $links, queryCount: 1);
    }
}
