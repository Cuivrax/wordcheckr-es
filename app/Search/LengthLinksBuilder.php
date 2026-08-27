<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\LengthLinks depuis la table list_counts (D-022), meme principe et meme
 * source que App\Search\ExploreHubBuilder -- une seule requete triviale, aucun GROUP BY sur
 * `terms` au runtime (voir scripts/build_explore_hub_counts.php pour la mesure qui impose ce
 * detour).
 *
 * list_key est toujours "{longueur}:{lettre}" pour 'length_start'/'length_end'/'length_with', et
 * "{longueur}:{lettre}:{position}" pour 'length_with_position' (D-023bis, ajoute au correctif
 * C1 de l'audit D-028, 2026-08-11), et "{longueur}:{debut}:{fin}" pour 'length_start_end' (D-027,
 * ajoute au correctif C1 applique cette fois a la variante commencant+terminant, 2026-08-18) --
 * le filtre `list_key LIKE '{longueur}:%'` reste sans ambiguite pour les cinq list_type a la fois
 * (le premier ':' delimite toujours la longueur).
 *
 * Budget runtime : 1 requete SQLite -- appelee uniquement pour une page "longueur seule"
 * (aucune autre contrainte, voir public/index.php), en plus des requetes deja comptees par
 * WordListSolver pour cette meme page (2 au plus), reste tres en-dessous du plafond de moins
 * de 10 (CLAUDE.md). L'ajout de 'length_with_position' puis de 'length_start_end' au IN(...)
 * n'ajoute AUCUNE requete supplementaire (meme requete elargie, meme LIKE '{longueur}:%').
 */
final class LengthLinksBuilder
{
    /**
     * Les 52 paires (longueur, debut, fin) a contenu strictement duplique identifiees par D-025
     * (I-1) : pour chacune, TOUS les mots commencant par {debut} et terminant par {fin} (toutes
     * longueurs confondues) partagent exactement la meme longueur {longueur} -- la variante SANS
     * longueur (deja indexee comme gagnante canonique permanente, storage/seo_fr.sqlite,
     * registry.notes) couvre alors 100% du contenu de la variante AVEC longueur correspondante.
     * Ces 52 pages restent et resteront noindex,follow (R3, jamais deux pages index,follow pour
     * un contenu identique) -- exclues ici pour ne jamais leur creer de lien depuis une page deja
     * indexee (/mots/{N}-lettres, Family::WORD_LIST_LENGTH).
     *
     * Source : storage/seo_fr.sqlite, `SELECT route_path, notes FROM registry WHERE
     * family = 'word_list_combined' AND notes LIKE '%ATTENTION doublon%'` -- exactement 52
     * lignes, chacune citant sa longueur partagee en toutes lettres ("tous les mots de cette
     * paire partagent la longueur N"). Verifie une seconde fois de facon independante (0
     * divergence dans les deux sens) en comparant list_counts : une entree 'length_start_end'
     * "{N}:{X}:{Y}" est un doublon si et seulement si son `count` est EGAL au `count` de l'entree
     * 'start_end' "{X}:{Y}" correspondante (meme total, donc aucun mot de cette paire n'existe a
     * une autre longueur) -- reproduit exactement les 52 memes triples, voir
     * tests/Search/LengthLinksBuilderTest.php et reports/query-plans/combined-length-maillage.md.
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022, integrity_check = ok). Une reconstruction future de la base devra
     * revalider cette liste (le test de coherence ci-dessus le detecterait, comparaison
     * list_counts, jamais un echantillon).
     *
     * @var list<string>
     */
    private const DUPLICATE_START_END_KEYS = [
        '3:A:J', '5:B:J', '4:C:J', '7:D:Q', '3:D:V', '2:E:J', '4:F:J', '3:F:Q', '4:F:W', '3:G:W',
        '9:I:B', '2:I:P', '2:I:V', '9:I:W', '3:J:B', '3:M:J', '6:M:V', '11:M:W', '7:N:P', '8:N:W',
        '4:O:J', '9:O:Q', '6:O:W', '2:P:V', '3:Q:C', '9:Q:P', '2:Q:Q', '9:R:Q', '8:R:W', '5:S:V',
        '5:T:J', '8:T:Q', '8:T:W', '3:U:B', '3:U:H', '5:U:K', '4:U:V', '9:V:B', '7:V:Q', '3:V:V',
        '10:W:L', '14:X:C', '5:X:G', '5:X:O', '7:X:U', '12:X:X', '4:Y:P', '5:Y:Q', '4:Y:V', '8:Z:J',
        '3:Z:P', '6:Z:Q',
    ];

    /**
     * Doublon de contenu CROISÉ avec une famille EXTÉRIEURE pour byWith (D-041, garde-fou
     * structurel demandé par le constat C-4 du 4e audit consolidé, docs/DECISIONS.md D-040) --
     * balayage GÉNÉRIQUE de tout le registre (scripts/check_combinatorial_duplicates.php, balayage
     * du 2026-08-21 : 1 656 groupes, 2 089 pages en excès).
     *
     * "2-lettres/avec/w" (Family::WORD_LIST_AVEC_SINGLE_LETTER, 2 composants -- WU, seul mot de 2
     * lettres avec W) fait partie d'un groupe de 3 pages au contenu identique : elle-même,
     * "2-lettres/commencant/w/terminant/u" (Family::WORD_LIST_COMBINED avec longueur, 3
     * composants) et "terminant/wu" (Family::WORD_LIST_TERMINANT sans longueur, 1 seul composant).
     * Le gagnant du groupe est "terminant/wu" (le plus petit compte des trois) --
     * App\Search\DuplicatePageResolver::resolveDuplicateWinner() retire donc "2:W" ICI, pas parce
     * qu'elle perd face à la variante avec longueur (2 < 3, elle la battrait seule à seule), mais
     * parce qu'une troisième page à 1 seul composant gagne le groupe entier.
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_WITH_KEYS = ['2:W'];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(int $length): LengthLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_type, list_key, count FROM list_counts"
            . " WHERE list_type IN ('length_start', 'length_end', 'length_with', 'length_with_position', 'length_start_end') AND list_key LIKE ?"
        );
        $statement->execute([$length . ':%']);

        $byStart = [];
        $byEnd = [];
        $byWith = [];
        $byPositionGrouped = [];
        $byStartEndGrouped = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];
            $count = (int) $row['count'];

            if ($row['list_type'] === 'length_with_position') {
                // key = "{longueur}:{lettre}:{position}" (D-023bis) -- structure a 3 segments,
                // distincte des trois autres list_type (2 segments). Positions degenerees (1re
                // et derniere lettre) exclues ici : deja couvertes par byStart/byEnd ci-dessous,
                // D-023 les collapse toujours vers commencant/terminant, jamais une URL
                // "position/1/..." ni "position/{longueur}/...".
                [, $letter, $positionRaw] = explode(':', $key, 3);
                $position = (int) $positionRaw;

                if ($position <= 1 || $position >= $length) {
                    continue;
                }

                // Doublon de contenu CROISE avec une famille EXTERIEURE (D-041) : voir
                // PositionLinksBuilder::EXTERNAL_DUPLICATE_KEYS -- meme famille cible
                // (Family::WORD_LIST_POSITION), source de verite unique referencee ici plutot que
                // dupliquee.
                if (in_array($key, PositionLinksBuilder::EXTERNAL_DUPLICATE_KEYS, true)) {
                    continue;
                }

                $url = WordListFilters::fromPath($length . '-lettres/position/' . $position . '/' . strtolower($letter))?->canonicalUrl();

                if ($url !== null) {
                    $byPositionGrouped[$position][] = ['letter' => $letter, 'url' => $url, 'count' => $count];
                }

                continue;
            }

            if ($row['list_type'] === 'length_start_end') {
                // key = "{longueur}:{debut}:{fin}" (D-027) -- structure a 3 segments elle aussi,
                // mais debut/fin plutot que lettre/position. Les 52 paires a contenu duplique
                // (D-025, I-1) sont exclues explicitement : ces pages resteront noindex,follow en
                // permanence (R3), inutile et trompeur de leur creer un lien depuis une page deja
                // indexee.
                if (in_array($key, self::DUPLICATE_START_END_KEYS, true)) {
                    continue;
                }

                // Doublon de contenu CROISE avec une famille EXTERIEURE (D-041) : voir
                // LengthCombinedLinksBuilder::EXTERNAL_DUPLICATE_KEYS -- meme famille cible
                // (Family::WORD_LIST_COMBINED avec longueur), source de verite unique referencee
                // ici plutot que dupliquee.
                if (in_array($key, LengthCombinedLinksBuilder::EXTERNAL_DUPLICATE_KEYS, true)) {
                    continue;
                }

                [, $start, $end] = explode(':', $key, 3);

                $url = WordListFilters::fromPath(
                    $length . '-lettres/commencant/' . strtolower($start) . '/terminant/' . strtolower($end)
                )?->canonicalUrl();

                if ($url !== null) {
                    $byStartEndGrouped[$start][] = ['letter' => $end, 'url' => $url, 'count' => $count];
                }

                continue;
            }

            $letter = substr($key, strpos($key, ':') + 1);

            switch ($row['list_type']) {
                case 'length_start':
                    $url = WordListFilters::fromPath($length . '-lettres/commencant/' . strtolower($letter))?->canonicalUrl();

                    if ($url !== null) {
                        $byStart[] = ['letter' => $letter, 'url' => $url, 'count' => $count];
                    }
                    break;

                case 'length_end':
                    $url = WordListFilters::fromPath($length . '-lettres/terminant/' . strtolower($letter))?->canonicalUrl();

                    if ($url !== null) {
                        $byEnd[] = ['letter' => $letter, 'url' => $url, 'count' => $count];
                    }
                    break;

                case 'length_with':
                    // Doublon de contenu CROISE avec une famille EXTERIEURE (D-041) : voir
                    // EXTERNAL_DUPLICATE_WITH_KEYS.
                    if (in_array($key, self::EXTERNAL_DUPLICATE_WITH_KEYS, true)) {
                        break;
                    }

                    $url = WordListFilters::fromPath($length . '-lettres/avec/' . strtolower($letter))?->canonicalUrl();

                    if ($url !== null) {
                        $byWith[] = ['letter' => $letter, 'url' => $url, 'count' => $count];
                    }
                    break;
            }
        }

        usort($byStart, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);
        usort($byEnd, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);
        usort($byWith, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        ksort($byPositionGrouped);
        $byPosition = [];

        foreach ($byPositionGrouped as $position => $letters) {
            usort($letters, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);
            $byPosition[] = ['position' => $position, 'letters' => $letters];
        }

        ksort($byStartEndGrouped);
        $byStartEnd = [];

        foreach ($byStartEndGrouped as $start => $letters) {
            usort($letters, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);
            $byStartEnd[] = ['start' => $start, 'letters' => $letters];
        }

        return new LengthLinks(
            byStart: $byStart,
            byEnd: $byEnd,
            byWith: $byWith,
            byPosition: $byPosition,
            byStartEnd: $byStartEnd,
            queryCount: 1,
        );
    }
}
