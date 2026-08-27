<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\AvecTwoLettersLinks depuis list_counts (list_type 'length_with_pair'),
 * meme principe que App\Search\PositionLinksBuilder / App\Search\AvecSansLengthLinksBuilder --
 * une seule requete triviale, aucun calcul sur `terms` au runtime (voir
 * scripts/build_explore_hub_counts.php pour la mesure qui impose ce detour).
 *
 * list_key est toujours "{longueur}:{lettre1}:{lettre2}" avec lettre1 < lettre2 ALPHABETIQUEMENT
 * (une seule ligne par paire non ordonnee -- jamais les deux sens stockes separement). Depuis une
 * page "avec {X}" (palier 1), $letter peut se trouver des DEUX cotes de la paire stockee selon
 * l'ordre alphabetique avec son partenaire ($letter < partenaire OU partenaire < $letter) : cette
 * classe interroge donc les deux cas avec un OR sur deux motifs LIKE ({longueur}:{$letter}:% et
 * {longueur}:%:{$letter}), une seule requete, jamais deux executions separees. La table est
 * minuscule au pire (4 550 lignes au maximum, 14 longueurs x C(26,2) paires -- voir le docblock
 * de build_explore_hub_counts.php) : le second motif LIKE (joker en tete) ne beneficie pas de
 * l'index de cle primaire de la meme facon que le premier, mais le cout reste negligeable sur une
 * table de cette taille (mesure : reports/query-plans/avec-length-2-letters-full-sweep.md).
 *
 * L'URL cible est TOUJOURS construite via WordListFilters::fromPath()->canonicalUrl(), jamais
 * assemblee a la main : ksort() y trie deja les lettres "avec" par cle alphabetique (D-022), donc
 * peu importe l'ordre dans lequel $letter et le partenaire sont passes a fromPath() ici, l'URL
 * rendue est toujours la forme canonique (lettre1 < lettre2), identique a la cle list_counts.
 *
 * Deux filtres anti-doublon, appliques dans build() ci-dessous (analyse independante data-engine,
 * 2026-08-20, demandee en parallele du meme calcul cote seo-registry avant toute application
 * registre/sitemap -- meme discipline que D-037/D-038/D-039) : DUPLICATE_PARENT_KEYS (doublon avec
 * la page PARENTE palier 1, /mots/{N}-lettres/avec/{X}) et SIBLING_DUPLICATE_KEYS (doublon entre
 * pages SOEURS du palier 2, meme longueur).
 */
final class AvecTwoLettersLinksBuilder
{
    /**
     * Les 4 triples (longueur, lettre1, lettre2) a contenu strictement DUPLIQUE avec l'une de leurs
     * deux pages parentes palier 1 (/mots/{N}-lettres/avec/{lettre1} OU .../avec/{lettre2}) --
     * meme patron que App\Search\StartEndWithLinksBuilder::DUPLICATE_CONTENT_KEYS (D-037) : une
     * ligne list_counts 'length_with_pair' "{N}:{X}:{Y}" est un doublon de contenu SI ET SEULEMENT
     * SI son `count` est EXACTEMENT EGAL au `count` de l'entree parente 'length_with' "{N}:{X}" OU
     * "{N}:{Y}" -- ca signifie que TOUS les mots contenant {X} (ou {Y}) a cette longueur contiennent
     * deja l'autre lettre, ajouter la seconde contrainte "avec" ne retire aucun mot.
     *
     * Exemples trouves, tous verifies contre des exemples cites explicitement par la demande
     * d'analyse (0 divergence) : 2:A:Z (ZA, seul mot de 2 lettres avec Z, contient deja A) ;
     * 2:U:W (WU, seul mot de 2 lettres avec W, contient deja U) ; 14:Q:U et 15:Q:U (regle
     * orthographique du francais -- aucun mot de 14 ou 15 lettres ne contient Q sans U, alors que
     * ce n'est pas vrai aux longueurs plus courtes, ex. COQ).
     *
     * Verifie par DEUX methodes independantes : 1. comparaison list_counts ('length_with_pair' vs
     * 'length_with', count enfant === count parent), sur les 4 276 lignes reelles du palier 2 ;
     * 2. recompute direct et independant depuis `terms` (scan longueur par longueur, comptage des
     * lettres uniques par mot, sans jamais lire list_counts) -- 4 trouvees, 0 divergence entre les
     * deux methodes. Sanity supplementaire : les comptes recalcules directement depuis `terms`
     * matchent EXACTEMENT list_counts sur les 364 + 4 276 + 28 827 lignes 'length_with'/
     * 'length_with_pair'/'length_with_triple' (0 mismatch), la source precalculee est fiable.
     *
     * La cle est exactement le `list_key` tel que stocke dans list_counts ("{N}:{X}:{Y}",
     * X < Y alphabetiquement, D-030) -- comparee directement a $row['list_key'] dans build()
     * ci-dessous, jamais reconstruite a la main.
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022, integrity_check = ok). Une reconstruction future de la base devra
     * revalider cette liste (meme avertissement que DUPLICATE_CONTENT_KEYS/DUPLICATE_START_END_KEYS
     * ailleurs dans ce projet).
     *
     * @var list<string>
     */
    private const DUPLICATE_PARENT_KEYS = [
        '14:Q:U', '15:Q:U', '2:A:Z', '2:U:W',
    ];

    /**
     * Doublons de contenu entre pages SOEURS du palier 2 (deux paires DIFFERENTES a la MEME
     * longueur produisant exactement le meme ensemble de mots, ni l'une ni l'autre deja exclue par
     * DUPLICATE_PARENT_KEYS ci-dessus) -- meme classe de defaut que
     * App\Search\StartEndWithLinksBuilder::SIBLING_DUPLICATE_KEYS (D-038), recherchee ici de la
     * meme facon : regroupement par (longueur, count) parmi les 4 272 paires survivantes du filtre
     * parent (necessaire mais pas suffisant, deux ensembles distincts peuvent partager un compte),
     * PUIS verification par empreinte SQL GROUP_CONCAT (liste triee des mots concernes, comparaison
     * de chaines completes, aucun hash, aucune collision possible) sur les 286 groupes candidats
     * (1 064 paires) trouves par ce premier tri.
     *
     * Resultat : LISTE VOLONTAIREMENT VIDE -- 0 collision reelle trouvee sur les 1 064 candidates
     * verifiees par empreinte (contrairement au palier 3, voir
     * App\Search\AvecThreeLettersLinksBuilder::SIBLING_DUPLICATE_KEYS, ou 234 collisions reelles
     * existent). Mecanisme garde en place pour la coherence de forme avec les autres builders de
     * cette serie et en garde-fou si une reconstruction future de la base faisait apparaitre un cas
     * -- le test associe revalide ce chiffre a chaque execution, jamais suppose silencieusement.
     *
     * @var list<string>
     */
    private const SIBLING_DUPLICATE_KEYS = [];

    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE au palier 2 de "avec" (D-041,
     * garde-fou structurel demandé par le constat C-4 du 4e audit consolidé, docs/DECISIONS.md
     * D-040) -- distinct de DUPLICATE_PARENT_KEYS/SIBLING_DUPLICATE_KEYS ci-dessus (qui comparent
     * uniquement entre pages "avec" de la hiérarchie palier 1/2/3) : ici, une page palier 2 partage
     * un contenu strictement identique avec une page d'une AUTRE famille combinatoire (commençant/
     * terminant multi-lettres, combinée, avec+longueur d'une seule lettre...), trouvée par le
     * balayage GÉNÉRIQUE de tout le registre (scripts/check_combinatorial_duplicates.php,
     * balayage du 2026-08-21 : 1 656 groupes, 2 089 pages en excès).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "{N}-lettres/avec/{X}/{Y}" a TOUJOURS 3 composants (longueur + 2 lettres "avec"). Perd
     * systématiquement face à un adversaire à 1 ou 2 composants (commençant/terminant seuls,
     * combiné sans longueur, avec à une seule lettre, commençant+avec...), ET perd aussi, à 3
     * composants égaux, face à "combiné avec longueur" (signature de rôles [longueur, commençant,
     * terminant] précède [longueur, avec, avec] -- "commençant" avant "avec" dans l'ordre
     * canonique) : les 138 clés de cette liste se répartissent en pertes face à commençant (21),
     * combiné avec longueur (84) et terminant (33). Aucun cas face à "position" dans ce balayage
     * (0 groupe partagé entre les deux familles à ce jour) -- si un tel cas apparaissait un jour,
     * "position" gagnerait (signature [longueur, position, position] précède [longueur, avec,
     * avec], "position" avant "avec" dans l'ordre canonique).
     *
     * 138 clés (format "{longueur}:{lettre1}:{lettre2}", lettre1 < lettre2), recalculées
     * indépendamment par échantillonnage direct contre `terms` (voir le rapport AFTER de cette
     * tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_KEYS = [
        '10:J:W', '2:A:D', '2:A:F', '2:A:J', '2:A:L', '2:A:P', '2:A:T', '2:A:Y',
        '2:B:D', '2:B:E', '2:B:G', '2:B:I', '2:B:M', '2:B:N', '2:B:P', '2:B:R',
        '2:B:U', '2:B:Y', '2:C:D', '2:C:E', '2:C:H', '2:C:I', '2:C:M', '2:C:O',
        '2:C:P', '2:C:U', '2:C:V', '2:D:G', '2:D:J', '2:D:O', '2:D:P', '2:E:G',
        '2:E:K', '2:E:L', '2:E:M', '2:E:P', '2:E:R', '2:E:U', '2:E:Y', '2:F:M',
        '2:F:S', '2:G:O', '2:G:P', '2:H:I', '2:H:P', '2:H:S', '2:H:T', '2:I:J',
        '2:I:M', '2:I:O', '2:I:Q', '2:I:R', '2:I:T', '2:I:V', '2:I:X', '2:I:Y',
        '2:K:P', '2:K:U', '2:L:T', '2:L:U', '2:M:O', '2:M:P', '2:M:R', '2:M:S',
        '2:M:U', '2:O:R', '2:O:S', '2:O:T', '2:O:U', '2:O:Y', '2:P:S', '2:P:U',
        '2:P:V', '2:Q:U', '2:R:S', '2:R:U', '2:R:Y', '2:S:V', '2:T:V', '2:U:V',
        '3:B:J', '3:B:K', '3:C:J', '3:C:K', '3:C:Y', '3:C:Z', '3:D:F', '3:D:K',
        '3:D:X', '3:F:V', '3:F:X', '3:F:Y', '3:G:W', '3:G:X', '3:H:L', '3:H:N',
        '3:H:V', '3:H:W', '3:I:W', '3:J:N', '3:J:P', '3:J:Z', '3:K:Z', '3:M:Q',
        '3:M:V', '3:M:W', '3:M:X', '3:N:R', '3:N:V', '3:N:W', '3:P:Q', '3:P:W',
        '3:P:X', '3:P:Y', '3:Q:T', '3:S:V', '3:S:Z', '3:T:W', '3:T:X', '3:T:Y',
        '3:V:X', '3:W:X', '4:G:Q', '4:G:X', '4:J:W', '4:J:X', '4:J:Y', '4:L:Q',
        '4:M:W', '4:Q:W', '4:Q:Y', '4:W:Z', '5:K:Q', '5:K:X', '5:Q:X', '5:W:X',
        '6:J:W', '6:W:X',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(int $length, string $letter): AvecTwoLettersLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_pair'"
            . ' AND (list_key LIKE ? OR list_key LIKE ?)'
        );
        $statement->execute([$length . ':' . $letter . ':%', $length . ':%:' . $letter]);

        $links = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];

            if (
                in_array($key, self::DUPLICATE_PARENT_KEYS, true)
                || in_array($key, self::SIBLING_DUPLICATE_KEYS, true)
                || in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)
            ) {
                continue;
            }

            $parts = explode(':', $key, 3);
            $partner = $parts[1] === $letter ? $parts[2] : $parts[1];
            $count = (int) $row['count'];

            $path = $length . '-lettres/avec/' . strtolower($letter) . '/' . strtolower($partner);
            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['letter' => $partner, 'url' => $url, 'count' => $count];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new AvecTwoLettersLinks(links: $links, queryCount: 1);
    }
}
