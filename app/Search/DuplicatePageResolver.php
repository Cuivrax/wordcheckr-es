<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Règle de priorité GÉNÉRIQUE entre pages `/mots/...` au contenu strictement identique (D-041,
 * garde-fou structurel demandé par le constat C-4 du 4e audit consolidé, voir docs/DECISIONS.md
 * D-040). Quatre corrections consécutives (D-037, D-038, D-039, D-040) ont chacune codé, À LA
 * MAIN, une liste figée propre à UNE paire de familles précise, sans jamais formaliser la règle
 * de départage elle-même en code réutilisable — exactement ce que ce fichier corrige : UNE seule
 * fonction, testée, utilisée par tous les correctifs futurs plutôt que réécrite à chaque fois.
 *
 * ## Principe (extension de D-025/D-039 : « la forme la plus générale/simple gagne »)
 *
 * Pour chaque page candidate d'un groupe de doublons de contenu (même ensemble de mots exact,
 * détecté par scripts/check_combinatorial_duplicates.php), reconstruit ses filtres réels
 * (App\Search\WordListFilters::fromPath(), source unique de vérité de la grammaire /mots/...) et
 * compte ses COMPOSANTS DE CONTRAINTE :
 *
 *   longueur    1 composant si présente
 *   commençant  1 composant si présent (quel que soit le nombre de lettres du préfixe)
 *   contenant   1 composant si présent
 *   terminant   1 composant si présent (idem commençant)
 *   position    2 composants (index + lettre) — jamais 1, jamais 3
 *   avec        1 composant PAR LETTRE exigée (chaque lettre distincte de $withLetters)
 *   sans        1 composant PAR LETTRE exclue (chaque lettre distincte de $withoutLetters)
 *
 * Le nombre de composants le plus BAS gagne. C'est une propriété de la PAGE réelle (parsée depuis
 * route_path), jamais une constante par famille : Family::WORD_LIST_COMBINED à elle seule couvre
 * deux formes de comptes différents (commençant+terminant seuls = 2, avec longueur = 3) — compter
 * au niveau de la famille plutôt que de la page aurait été faux pour cette famille précise.
 *
 * ## Départage à égalité de composants
 *
 * 1. Entre deux familles DIFFÉRENTES (cas réel principal : commençant vs terminant, 1 composant
 *    chacun, 408 groupes sur le balayage du 2026-08-21) : compare la SIGNATURE DE RÔLES de chaque
 *    page — la liste, triée par l'ORDRE CANONIQUE déjà établi et déjà utilisé ailleurs sur ce
 *    projet (App\Search\WordListFilters, docblock de classe : longueur → commençant → contenant →
 *    terminant → position → avec → sans → motif), des mots-clés présents sur la page (un jeton par
 *    composant : "position" apparaît deux fois, "avec" une fois par lettre exigée). La page dont la
 *    signature est lexicographiquement la plus petite gagne. Reproduit exactement la règle déjà
 *    tranchée côté produit en D-039 (longueur vs avec, deux familles à 3 composants chacune : la
 *    variante longueur — signature [longueur, commençant, terminant] — gagne sur la variante avec
 *    — signature [commençant, terminant, avec] — parce que "longueur" précède "avec" dans l'ordre
 *    canonique) : ce n'était pas une exception câblée à la main, c'est une conséquence de cette
 *    même règle générale.
 * 2. Entre deux pages de la MÊME famille à chaîne variable (commençant/contenant/terminant : même
 *    componentCount() ET même roleSignature() PAR CONSTRUCTION quelle que soit la longueur réelle
 *    du préfixe/suffixe, voir componentCount()) : compare la PROFONDEUR du composant variable —
 *    variableComponentDepth(), la longueur en caractères du préfixe/contenant/suffixe de chaque
 *    page. La forme la plus COURTE gagne (extension de D-025/D-039 : « la forme la plus générale/
 *    simple gagne » — un préfixe/suffixe de 2 lettres est strictement plus général que son extension
 *    à 3 lettres, qui ne peut jamais désigner un ensemble de mots plus large). C'est un couple
 *    PARENT/ENFANT, jamais deux formes réellement indépendantes.
 *
 *    CORRECTIF (constat I-1, 5e audit consolidé, docs/DECISIONS.md) : une version antérieure de
 *    cette règle comparait directement le route_path complet, en s'appuyant sur la prémisse que
 *    « le seul segment qui varie entre deux pages sœurs d'une famille déjà canonicalisée est
 *    toujours en fin de chemin ». Cette prémisse est VRAIE pour "commençant" (App\Search\
 *    PrefixExtensionLinksBuilder ajoute la lettre d'extension EN QUEUE du préfixe, ex. "wu" →
 *    "wub" : le parent est alors toujours un préfixe-au-sens-chaîne de l'enfant, donc
 *    alphabétiquement plus petit) mais FAUSSE pour "terminant" : App\Search\
 *    SuffixExtensionLinksBuilder ajoute la lettre d'extension EN TÊTE du suffixe (`'_' . $suffix`
 *    dans le LIKE, "zt" → "azt", jamais "zt" → "zta"), donc le chemin de l'enfant n'est PLUS
 *    garanti alphabétiquement après celui du parent — ex. "/mots/terminant/azt" précède
 *    "/mots/terminant/zt" alphabétiquement bien que "azt" soit l'ENFANT de "zt". Comparer le route_
 *    path complet aurait donc désigné l'enfant comme gagnant sur un tel groupe, à l'inverse du
 *    principe établi, ET retiré le parent — qui est pourtant, pour "terminant", le seul mot-clé
 *    d'entrée : un suffixe de longueur N+1 n'est jamais lié que depuis son parent de longueur N
 *    (App\Search\SuffixExtensionLinksBuilder), jamais l'inverse, donc retirer le parent
 *    supprimerait le seul lien entrant du survivant. Comparer la profondeur du composant variable
 *    au lieu du chemin entier corrige les deux familles avec la même règle, sans coïncidence
 *    requise sur la direction dans laquelle l'extension s'ajoute.
 * 3. Si la profondeur est également égale (vraies pages SŒURS, ex. deux triplets "avec" différents
 *    de même longueur, ou deux suffixes "terminant" de même longueur mais de lettres différentes) :
 *    reprend la règle déjà utilisée en D-038/D-029-D-031 — la forme alphabétiquement la plus petite
 *    (route_path canonique complet) gagne. Les lettres/contraintes de ce projet sont toujours
 *    sérialisées dans canonicalPath() en ordre alphabétique croissant (ksort() sur $withLetters,
 *    D-022), donc comparer route_path revient à comparer la première lettre distinctive — même
 *    résultat que "la lettre la plus petite gagne" utilisé dans les corrections précédentes,
 *    généralisé à n'importe quelle forme de page plutôt qu'un seul segment "avec". "avec"/"sans"
 *    (chaque lettre = 1 composant distinct dans componentCount()) et "position" (toujours 2
 *    composants) n'ont pas besoin d'entrer dans variableComponentDepth() : deux pages à égalité de
 *    componentCount() ET de roleSignature() sur ces familles sont déjà de VRAIES sœurs (même
 *    nombre de lettres), jamais un couple parent/enfant — la profondeur y est donc toujours égale
 *    par construction, l'étape 2 ne les départage jamais, l'étape 3 s'en charge directement (déjà
 *    le comportement testé en D-038, inchangé par ce correctif).
 *
 * La règle 2 (profondeur) n'est jamais invoquée dans le balayage du 2026-08-21 (0 groupe SŒUR pur
 * trouvé pour "terminant" ni pour "commençant", tous les 1 656 groupes sont CROISÉS entre familles
 * différentes, départagés par la règle 1) — mais elle corrige un défaut latent du garde-fou lui-même
 * (constat I-1) plutôt qu'un défaut observé sur l'état actuel de storage/dictionary_fr.sqlite ; elle
 * reste nécessaire pour la robustesse générale de la fonction et pour tout futur balayage.
 *
 * ## Ce que cette classe NE fait PAS
 *
 * Ne lit AUCUNE base de données, ne connaît AUCUNE liste de doublons concrète : c'est un algorithme
 * pur, appliqué par l'appelant à un groupe de route_path déjà identifié comme dupliqué (par
 * scripts/check_combinatorial_duplicates.php ou toute détection future). Les listes figées
 * résultantes (ex. StartEndWithLinksBuilder::EXTERNAL_DUPLICATE_ROUTES) restent, comme pour tous
 * les correctifs précédents de cette série, des constantes gelées propres à l'état actuel de
 * storage/dictionary_fr.sqlite — cette classe ne les stocke jamais elle-même.
 */
final class DuplicatePageResolver
{
    /**
     * Ordre canonique des mots-clés de /mots/... (App\Search\WordListFilters, docblock de classe,
     * docs/05_URL_SEO_INDEXATION.md) : longueur → commençant → contenant → terminant → position →
     * avec → sans → motif. "statut"/"tri" sont des raffinements d'affichage (D-022), jamais un
     * composant de CONTENU au sens de cette règle — absents ici par construction (aucune des
     * familles combinatoires concernées ne les porte).
     */
    private const KEYWORD_ORDER = [
        'longueur' => 0,
        'commencant' => 1,
        'contenant' => 2,
        'terminant' => 3,
        'position' => 4,
        'avec' => 5,
        'sans' => 6,
        'motif' => 7,
    ];

    private function __construct()
    {
        // Classe utilitaire statique uniquement, jamais instanciée.
    }

    /**
     * Nombre de composants de contrainte de $filters — voir le docblock de classe pour le détail
     * du barème. Une propriété de la PAGE réelle, jamais d'une famille au sens abstrait.
     */
    public static function componentCount(WordListFilters $filters): int
    {
        $count = 0;

        if ($filters->length !== null) {
            $count++;
        }
        if ($filters->prefix !== null) {
            $count++;
        }
        if ($filters->contains !== null) {
            $count++;
        }
        if ($filters->suffix !== null) {
            $count++;
        }
        if ($filters->position !== null) {
            $count += 2;
        }

        $count += count($filters->withLetters);
        $count += count($filters->withoutLetters);

        return $count;
    }

    /**
     * Signature de rôles triée (ordre canonique, voir KEYWORD_ORDER) : un jeton entier par
     * composant présent sur la page. Deux pages de MÊME longueur de signature (donc déjà à égalité
     * de composants, voir componentCount()) sont comparées lexicographiquement par
     * compareRoleSignatures() ci-dessous — la signature la plus petite indique la famille dont le
     * rôle le plus "en amont" dans l'ordre canonique apparaît en premier.
     *
     * @return list<int>
     */
    public static function roleSignature(WordListFilters $filters): array
    {
        $roles = [];

        if ($filters->length !== null) {
            $roles[] = self::KEYWORD_ORDER['longueur'];
        }
        if ($filters->prefix !== null) {
            $roles[] = self::KEYWORD_ORDER['commencant'];
        }
        if ($filters->contains !== null) {
            $roles[] = self::KEYWORD_ORDER['contenant'];
        }
        if ($filters->suffix !== null) {
            $roles[] = self::KEYWORD_ORDER['terminant'];
        }
        if ($filters->position !== null) {
            $roles[] = self::KEYWORD_ORDER['position'];
            $roles[] = self::KEYWORD_ORDER['position'];
        }

        for ($i = 0, $n = count($filters->withLetters); $i < $n; $i++) {
            $roles[] = self::KEYWORD_ORDER['avec'];
        }
        for ($i = 0, $n = count($filters->withoutLetters); $i < $n; $i++) {
            $roles[] = self::KEYWORD_ORDER['sans'];
        }

        sort($roles);

        return $roles;
    }

    /**
     * Compare deux signatures de rôles DÉJÀ DE MÊME LONGUEUR (comparaison lexicographique, premier
     * jeton différent décide) : < 0 si $a précède $b dans l'ordre canonique, > 0 si $b précède $a,
     * 0 si identiques (même multiset de rôles — n'arrive jamais entre deux familles distinctes de ce
     * projet, chaque famille ayant une grammaire de route_path syntaxiquement unique, mais géré
     * proprement plutôt que supposé impossible).
     *
     * @param list<int> $a
     * @param list<int> $b
     */
    public static function compareRoleSignatures(array $a, array $b): int
    {
        $length = min(count($a), count($b));

        for ($i = 0; $i < $length; $i++) {
            if ($a[$i] !== $b[$i]) {
                return $a[$i] <=> $b[$i];
            }
        }

        return count($a) <=> count($b);
    }

    /**
     * Résout le gagnant d'un groupe de pages `/mots/...` au contenu strictement identique.
     * $group : au moins deux route_path canoniques (ex. "/mots/10-lettres/avec/c/f/w"), format
     * identique à celui produit par WordListFilters::canonicalUrl() et lu depuis
     * storage/seo_fr.sqlite (registry.route_path) — exactement le format déjà utilisé par
     * scripts/check_combinatorial_duplicates.php.
     *
     * Renvoie le route_path qui reste candidat à l'indexation ; tous les autres membres du groupe
     * sont des perdants (à exclure du maillage, puis du registre — cette dernière étape reste une
     * décision d'application séparée, jamais faite par cette fonction).
     *
     * @param list<string> $group
     */
    public static function resolveDuplicateWinner(array $group): string
    {
        if (count($group) < 2) {
            throw new \InvalidArgumentException(
                'resolveDuplicateWinner() attend un groupe de doublons de 2 pages au moins, '
                . count($group) . ' recu(es).'
            );
        }

        $best = null;
        $bestCount = null;
        $bestRoles = null;
        $bestFilters = null;

        foreach ($group as $routePath) {
            $filters = self::filtersFor($routePath);
            $count = self::componentCount($filters);
            $roles = self::roleSignature($filters);

            if ($best === null) {
                $best = $routePath;
                $bestCount = $count;
                $bestRoles = $roles;
                $bestFilters = $filters;
                continue;
            }

            if (self::isBetter($count, $roles, $filters, $routePath, $bestCount, $bestRoles, $bestFilters, $best)) {
                $best = $routePath;
                $bestCount = $count;
                $bestRoles = $roles;
                $bestFilters = $filters;
            }
        }

        /** @var string $best */
        return $best;
    }

    /**
     * Profondeur du/des composant(s) de contrainte à CHAÎNE VARIABLE (commençant/contenant/
     * terminant) -- somme des longueurs de $prefix, $contains et $suffix (0 pour ceux absents).
     * Voir le docblock de classe (règle 2) pour le raisonnement complet et le bug que cette
     * fonction corrige (constat I-1, 5e audit consolidé) : deux pages "commençant/{préfixe}" ou
     * "terminant/{suffixe}" de longueurs DIFFÉRENTES ont TOUJOURS le même componentCount() (1) et
     * la même roleSignature(), quelle que soit la longueur réelle du préfixe/suffixe -- comparer
     * cette profondeur, plutôt que le route_path complet, est donc nécessaire pour départager
     * correctement un couple parent/enfant, la forme la plus courte (la plus générale) gagnant.
     *
     * "avec"/"sans"/"position" n'ont pas besoin d'entrer dans cette somme : voir le docblock de
     * classe, règle 3.
     */
    public static function variableComponentDepth(WordListFilters $filters): int
    {
        return strlen($filters->prefix ?? '')
            + strlen($filters->contains ?? '')
            + strlen($filters->suffix ?? '');
    }

    /**
     * true si ($count, $roles, $filters, $path) doit remplacer ($bestCount, $bestRoles,
     * $bestFilters, $bestPath) comme gagnant courant -- règle 1 (composants), règle 1b (signature
     * de rôles, départage inter-familles), règle 2 (profondeur du composant variable, départage
     * parent/enfant intra-famille), règle 3 (route_path alphabétique, départage sœurs).
     *
     * @param list<int> $roles
     * @param list<int> $bestRoles
     */
    private static function isBetter(
        int $count,
        array $roles,
        WordListFilters $filters,
        string $path,
        int $bestCount,
        array $bestRoles,
        WordListFilters $bestFilters,
        string $bestPath,
    ): bool {
        if ($count !== $bestCount) {
            return $count < $bestCount;
        }

        $roleComparison = self::compareRoleSignatures($roles, $bestRoles);

        if ($roleComparison !== 0) {
            return $roleComparison < 0;
        }

        // Même compte, même signature de rôles : soit deux familles distinctes partageant
        // exactement le même multiset de rôles (jamais rencontré sur ce projet à ce jour), soit --
        // le cas réel -- un couple parent/enfant OU sœurs au sein de la MÊME famille à chaîne
        // variable (commençant/contenant/terminant, D-038/D-041). Départage par profondeur du
        // composant variable AVANT toute comparaison alphabétique de chemin : le route_path
        // complet n'est un proxy fiable de "parent avant enfant" que lorsque l'extension s'ajoute
        // en fin de chaîne (commençant/avec), jamais garanti en tête de chaîne (terminant, voir le
        // docblock de classe, règle 2, constat I-1).
        $depth = self::variableComponentDepth($filters);
        $bestDepth = self::variableComponentDepth($bestFilters);

        if ($depth !== $bestDepth) {
            return $depth < $bestDepth;
        }

        // Profondeur également égale : vraies pages sœurs (D-038) -- départage alphabétique par
        // route_path complet, déterministe (canonicalPath() sérialise toujours les lettres "avec"
        // en ordre alphabétique croissant, donc comparer route_path revient au même résultat que
        // "la lettre alphabétiquement la plus petite gagne").
        return $path < $bestPath;
    }

    private static function filtersFor(string $routePath): WordListFilters
    {
        if (!str_starts_with($routePath, '/mots')) {
            throw new \InvalidArgumentException("route_path hors de /mots : {$routePath}");
        }

        $filters = WordListFilters::fromPath(substr($routePath, strlen('/mots')));

        if ($filters === null) {
            throw new \InvalidArgumentException("route_path inexploitable par WordListFilters::fromPath() : {$routePath}");
        }

        return $filters;
    }
}
