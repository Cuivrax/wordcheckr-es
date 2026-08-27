<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\PrefixAvecLinks depuis list_counts (list_type 'start_with'), meme principe
 * que App\Search\PositionLinksBuilder / App\Search\PrefixExtensionLinksBuilder -- une seule
 * requete triviale, aucun calcul sur `terms` au runtime (voir
 * scripts/build_explore_hub_counts.php pour la mesure qui impose ce detour).
 *
 * list_key est toujours "{prefixe}:{lettre}" pour 'start_with' -- une seule direction de lecture
 * necessaire (la page source est toujours /mots/commencant/{X}) : `list_key LIKE '{prefixe}:%'`
 * reste un prefixe exact, servi par l'index de cle primaire de list_counts.
 *
 * Les combinaisons degenerees (lettre "avec" = prefixe, D-032) sont deja absentes de list_counts
 * -- exclues AU PRECALCUL lui-meme (voir schema.sql / scripts/build_explore_hub_counts.php), pas
 * ici. Ce builder n'a donc pas besoin de comparer chaque URL candidate a l'URL de la page parente
 * comme le fait App\Search\StartEndWithLinksBuilder (D-033) pour se proteger des lignes
 * degenerees restees dans son propre precalcul -- mais la comparaison est appliquee malgre tout,
 * meme discipline "toujours via WordListFilters::fromPath()->canonicalUrl(), jamais une URL
 * reconstruite a la main, jamais une lettre comparee a la main" que tous les builders de ce
 * projet : garantie supplementaire a cout nul si le precalcul devait un jour diverger (ex. une
 * future reconstruction de la base qui oublierait l'exclusion cote precalcul serait quand meme
 * rattrapee ici).
 *
 * Budget runtime : 1 requete SQLite par page.
 */
final class PrefixAvecLinksBuilder
{
    /**
     * Doublons de CONTENU (audit consolide, NO GO) : meme regle de detection que
     * App\Search\StartEndWithLinksBuilder::DUPLICATE_CONTENT_KEYS et que le cas
     * 'combined_with_length' de scripts/propose_seo_batch.php (ligne ~704) -- une ligne
     * list_counts 'start_with' "{prefixe}:{lettre}" serait un doublon de contenu SI ET SEULEMENT
     * SI son `count` est EXACTEMENT EGAL au `count` de l'entree parente 'start' (mono-lettre,
     * D-017) correspondante, c-a-d si TOUS les mots commencant par {prefixe} contiennent deja
     * {lettre}.
     *
     * Verifie par DEUX methodes independantes (meme discipline que StartEndWithLinksBuilder) :
     * 1. comparaison list_counts ('start_with' vs total mono-lettre recalcule par
     *    substr(normalized,1,1), sur les 646 lignes 'start_with' non degenerees D-032) -- 0
     *    trouvee
     * 2. balayage complet et INDEPENDANT (sans partir de list_counts), requete directe sur `terms`
     *    pour les 26 lettres x 26 prefixes (650 combinaisons brutes, hors 26 degenerees) : 0
     *    trouvee, 0 divergence avec la methode 1
     * Resultat attendu : le plus petit panier "commencant" reel est X (427 mots, storage/
     * dictionary_fr.sqlite) -- aucun panier mono-lettre n'est assez petit pour que TOUTE une
     * lettre distincte y soit garantie a 100%, contrairement aux paires commencant+terminant de
     * App\Search\StartEndWithLinksBuilder (paniers pouvant descendre a 1 mot, ex. FAQ, XIPHO).
     * Liste VOLONTAIREMENT VIDE : mecanisme garde en place (meme forme que les deux autres
     * builders de cette serie) pour la coherence du code et en garde-fou si une reconstruction
     * future de storage/dictionary_fr.sqlite faisait apparaitre un cas -- le test associe
     * revalidera ce chiffre a chaque execution, jamais suppose silencieusement.
     *
     * @var list<string>
     */
    private const DUPLICATE_CONTENT_KEYS = [];

    /**
     * Doublons de contenu entre pages SOEURS "avec" (I-A, 2e audit consolide de la serie,
     * 2026-08-18, GO avec ce point non bloquant) : meme regle de detection que
     * App\Search\StartEndWithLinksBuilder::SIBLING_DUPLICATE_KEYS -- deux lettres "avec"
     * DIFFERENTES du MEME panier "commencant" isolent le meme sous-ensemble EXACT de mots.
     *
     * Verifie par les DEUX memes methodes independantes (regroupement direct par egalite
     * d'ensemble exacte, et regroupement par propriete de coincidence/union-find), sur les
     * 26 prefixes reels (646 lettres "avec" survivantes au total) -- 0 divergence entre les
     * deux methodes, ET LISTE VIDE : aucun panier mono-lettre reel de storage/dictionary_fr.sqlite
     * n'est assez petit pour que deux lettres y soient TOUJOURS liees (meme raisonnement que
     * DUPLICATE_CONTENT_KEYS ci-dessus, aggrave ici puisque le panier residuel apres exclusion
     * d'une lettre est encore plus grand que celui compare a la page parente). Liste
     * VOLONTAIREMENT VIDE, mecanisme garde en place pour la coherence du code avec
     * StartEndWithLinksBuilder et en garde-fou si une reconstruction future de la base faisait
     * apparaitre un cas -- le test associe revalide ce chiffre a chaque execution.
     *
     * Rapport complet : reports/query-plans/avec-doublons-soeurs-correctif.md
     *
     * @var list<string>
     */
    private const SIBLING_DUPLICATE_KEYS = [];

    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à "avec" (D-041, garde-fou
     * structurel demandé par le constat C-4 du 4e audit consolidé, docs/DECISIONS.md D-040) --
     * distinct de DUPLICATE_CONTENT_KEYS/SIBLING_DUPLICATE_KEYS ci-dessus (qui comparent
     * uniquement entre pages "avec" du même panier "commençant") : ici, une page
     * "commençant/{X}/avec/{Y}" partage un contenu strictement identique avec une page d'une AUTRE
     * famille combinatoire (terminant multi-lettres, commençant seul, avec à deux lettres...),
     * trouvée par un balayage GÉNÉRIQUE de tout le registre
     * (scripts/check_combinatorial_duplicates.php, D-041, balayage du 2026-08-21 : 1 656 groupes,
     * 2 089 pages en excès) plutôt qu'une comparaison ciblée à une seule paire de familles comme
     * les corrections précédentes (D-037 à D-040).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- compte
     * les composants de contrainte de chaque page du groupe de doublons (longueur/commençant/
     * terminant/contenant = 1, position = 2, chaque lettre "avec"/"sans" = 1), le plus petit
     * nombre gagne ; à égalité de composants entre deux familles différentes, l'ordre canonique
     * des mots-clés (WordListFilters, docblock de classe) départage. "commençant/{X}/avec/{Y}" a
     * TOUJOURS 2 composants -- perd systématiquement ici face à l'adversaire à 1 seul composant de
     * chacun des 4 groupes concernés (commençant seul ou terminant multi-lettres).
     *
     * Les 4 clés (U:J, W:J, X:Z, Y:X) — un seul groupe par clé, jamais un doublon SOEUR entre deux
     * lettres "avec" du même préfixe (ce cas reste couvert par SIBLING_DUPLICATE_KEYS, toujours
     * vide) :
     *   U:J  /mots/commencant/u/avec/j   perd face à /mots/terminant/htie (1 composant)
     *   W:J  /mots/commencant/w/avec/j   perd face à /mots/commencant/webj (1 composant)
     *   X:Z  /mots/commencant/x/avec/z   perd face à /mots/terminant/xxes (1 composant)
     *   Y:X  /mots/commencant/y/avec/x   perd face à un adversaire à 1 composant du même groupe
     * Recalculé indépendamment par échantillonnage direct contre `terms` (voir le rapport AFTER
     * de cette tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste
     * (même avertissement que DUPLICATE_CONTENT_KEYS/SIBLING_DUPLICATE_KEYS ci-dessus).
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_KEYS = ['U:J', 'W:J', 'X:Z', 'Y:X'];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(string $prefix): PrefixAvecLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'start_with' AND list_key LIKE ?"
        );
        $statement->execute([$prefix . ':%']);

        $parentUrl = WordListFilters::fromPath('commencant/' . strtolower($prefix))?->canonicalUrl();

        $links = [];

        foreach ($statement as $row) {
            [, $letter] = explode(':', (string) $row['list_key'], 2);

            $path = 'commencant/' . strtolower($prefix) . '/avec/' . strtolower($letter);
            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url === null || $url === $parentUrl) {
                continue;
            }

            // Doublon de CONTENU (audit consolide, NO GO) : voir DUPLICATE_CONTENT_KEYS
            // ci-dessus -- liste vide sur l'etat actuel de la base, verifiee et non supposee.
            $key = strtoupper($prefix) . ':' . strtoupper($letter);

            if (in_array($key, self::DUPLICATE_CONTENT_KEYS, true)) {
                continue;
            }

            // Doublon de CONTENU entre pages SOEURS (I-A, 2e audit consolide) : voir
            // SIBLING_DUPLICATE_KEYS ci-dessus -- liste vide sur l'etat actuel de la base.
            if (in_array($key, self::SIBLING_DUPLICATE_KEYS, true)) {
                continue;
            }

            // Doublon de contenu CROISE avec une famille EXTERIEURE (D-041) : voir
            // EXTERNAL_DUPLICATE_KEYS ci-dessus.
            if (in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)) {
                continue;
            }

            $links[] = ['letter' => $letter, 'url' => $url, 'count' => (int) $row['count']];
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new PrefixAvecLinks(links: $links, queryCount: 1);
    }
}
