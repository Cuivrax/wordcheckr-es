<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\PrefixExtensionLinks depuis list_counts (`list_type` 'prefix2'/'prefix3'/
 * 'prefix4'), même principe que App\Search\AvecTwoLettersLinksBuilder — une seule requête
 * triviale, aucun GROUP BY sur `terms` au runtime.
 *
 * `list_key` de 'prefixN' est TOUJOURS le préfixe réel de longueur exacte N (aucune conversion
 * nécessaire, contrairement à SuffixExtensionLinksBuilder) — LIKE avec un unique joker `_` en fin
 * de motif matche exactement les extensions d'une lettre (jamais un joker `%`, jamais de portée
 * plus large) : `list_key LIKE 'AN_'` matche "ANE", "ANT"... mais jamais "AN" lui-même (2
 * caractères) ni "ANTI" (4 caractères), la longueur de `list_key` étant fixe au sein d'un même
 * `list_type`. `list_counts` reste petit (91 681 lignes au total au 2026-08-18, largement moins
 * pour un seul `list_type`) — un LIKE sans ancrage d'index reste trivial ici, même raisonnement
 * que App\Search\LetterCombinedLinksBuilder::buildForEnd() (D-024, joker en tête accepté sur
 * cette même table).
 */
final class PrefixExtensionLinksBuilder
{
    /** Préfixes d'entrée acceptés : 1 à 3 lettres (extension possible jusqu'à 4). */
    private const MIN_INPUT_LENGTH = 1;
    private const MAX_INPUT_LENGTH = 3;

    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à "commençant" multi-lettres
     * (D-041, garde-fou structurel demandé par le constat C-4 du 4e audit consolidé,
     * docs/DECISIONS.md D-040) -- balayage GÉNÉRIQUE de tout le registre
     * (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 : 1 656 groupes,
     * 2 089 pages en excès).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "commençant/{préfixe}" a TOUJOURS 1 seul composant, quelle que soit la longueur du préfixe --
     * le plus petit compte possible parmi les 9 familles concernées par ce balayage (aucune
     * famille combinatoire n'a 0 composant). Elle ne peut donc JAMAIS perdre face à une autre
     * famille : soit elle est seule au minimum (elle gagne), soit elle est à égalité de composants
     * avec un "terminant" multi-lettres (l'autre famille à 1 seul composant) -- départagé par
     * l'ordre canonique des mots-clés (WordListFilters, docblock de classe : "commençant" précède
     * "terminant"), qui la fait TOUJOURS gagner aussi.
     *
     * Liste VOLONTAIREMENT VIDE, vérifiée par calcul exhaustif sur les 742 appartenances réelles
     * de word_list_commencant au balayage du 2026-08-21 : 0 perte. Mécanisme gardé en place pour
     * la cohérence de forme avec les autres builders de cette série et en garde-fou si un futur
     * balayage (nouvelle famille à 0 composant, base reconstruite) faisait apparaître un cas -- le
     * test associé revalide ce chiffre à chaque exécution, jamais supposé silencieusement.
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_PREFIXES = [];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * $prefix : préfixe normalisé (A-Z) de la page source, 1 à 3 lettres. Renvoie une liste vide
     * (queryCount = 0) pour toute longueur hors de cette plage — un préfixe de 4 lettres est déjà
     * la dernière extension ouverte par cette tâche (2 à 4 lettres), aucune extension à 5 lettres
     * n'a été mesurée ni proposée.
     */
    public function build(string $prefix): PrefixExtensionLinks
    {
        $length = strlen($prefix);

        if ($length < self::MIN_INPUT_LENGTH || $length > self::MAX_INPUT_LENGTH) {
            return new PrefixExtensionLinks(links: [], queryCount: 0);
        }

        $listType = 'prefix' . ($length + 1);

        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = ? AND list_key LIKE ?"
        );
        $statement->execute([$listType, $prefix . '_']);

        $links = [];

        foreach ($statement as $row) {
            $extendedPrefix = (string) $row['list_key'];

            if (in_array($extendedPrefix, self::EXTERNAL_DUPLICATE_PREFIXES, true)) {
                continue;
            }

            $url = WordListFilters::fromPath('commencant/' . strtolower($extendedPrefix))?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['prefix' => $extendedPrefix, 'url' => $url, 'count' => (int) $row['count']];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['prefix'] <=> $b['prefix']);

        return new PrefixExtensionLinks(links: $links, queryCount: 1);
    }
}
