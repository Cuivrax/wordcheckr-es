<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Solveur /mots/... (Phase 3, docs/08) : listes de mots par longueur, prefixe, suffixe,
 * sous-chaine contenue, lettres obligatoires (avec repetitions), lettres exclues, et motif de
 * cases connues -- seules ou combinees dans l'ordre canonique (docs/05).
 *
 * ## Decision d'architecture -- mesuree avant implementation (reports/query-plans/phase3.md
 * pour le detail complet)
 *
 * "Contenant" (sous-chaine a une position quelconque) et "avec" (multiensemble de lettres,
 * avec repetitions) n'ont NI l'un ni l'autre d'index dedie dans schema.sql : normalized et
 * reversed ne servent que prefixe/suffixe (D-012 avait deja ecarte un index de toutes les
 * sous-chaines, ~587 Mo). Mesure avant de trancher :
 *
 * - table de postings trigrammes + lettres (couverture complete, 838 180 lignes) : 355,6 Mo
 *   ajoutes (191,5 Mo trigrammes + 164,1 Mo lettres) -- plus que doubler storage/dictionary_fr.sqlite
 *   (154,5 Mo). Un trigramme frequent (ERA, 99 919 correspondances sur 838 180 lignes) reste
 *   LENT meme indexe : 283 ms pour la premiere page, jusqu'a 419 ms en pagination profonde
 *   (OFFSET 90 000) -- au-dela du budget TTFB p95 < 250 ms, pour une table qui ne resout meme
 *   pas le probleme qu'elle est censee resoudre.
 * - alternative retenue, cout de stockage nul : une sous-requete BORNEE sur les index deja en
 *   place (normalized, reversed, idx_terms_length_normalized), predicats "contenant"/"avec"/
 *   "sans"/motif exprimes en SQL pur (instr(), LENGTH()/REPLACE(), substr() -- toutes des
 *   fonctions scalaires SQLite standard). Mesure initiale (Phase 3) : <= 6 ms dans le pire cas
 *   pathologique ANCRE observe (longueur = 11, 127 772 lignes dans ce seul panier). Cette mesure
 *   ne couvrait PAS le cas sans aucun ancrage (contenant/avec/sans seuls) : aucune UI ne
 *   l'exposait encore a l'epoque -- voir la correction C1 ci-dessous, mesuree separement.
 *
 * Meme principe que RackSolver (Phase 2) : une borne bon marche AVANT toute requete couteuse,
 * jamais un calcul complet sur toute la base.
 *
 * ## Deux regimes d'execution, selon les contraintes presentes
 *
 * EXACT (isExactMode()) -- longueur et/ou prefixe seuls (ou combines), rien d'autre : l'index
 * idx_terms_length_normalized (ou l'index UNIQUE sur normalized si prefixe seul) donne un
 * ordre d'anchrage DEJA egal a l'ordre d'affichage (alphabetique). COUNT() exact + LIMIT/OFFSET,
 * 2 requetes indexees, pagination complete et fiable.
 *
 * BORNE (tout le reste : suffixe seul ou combine, contenant, avec, sans, motif avec une case
 * connue au-dela du prefixe initial) : l'ordre d'ancrage naturel n'est pas toujours l'ordre
 * d'affichage (le suffixe s'ancre sur `reversed`, pas `normalized`), et/ou des predicats non
 * indexes doivent filtrer les lignes ancrees. anchorClause() (index) et extraPredicates() (SQL
 * pur, non indexe) sont combines en UNE SEULE clause WHERE (correction C1, audit final,
 * code-reviewer) -- jamais un panier ANCRE seul borne PUIS filtre apres coup, qui produisait des
 * faux negatifs silencieux (voir docs/DECISIONS.md D-019). Deux formes selon $anchorOrder :
 *   - 'normalized' (ancrage sur prefixe/longueur, ou aucun ancrage) : 1 SEULE requete,
 *     `LIMIT ROW_EXAMINATION_CEILING + 1`, `truncated` deduit du nombre de lignes RENDUES (pas
 *     d'un COUNT() separe) -- fusion mesuree (audit final, code-optimizer, constat I-1) : les
 *     deux requetes precedentes executaient le meme parcours pour n'en extraire qu'un booleen.
 *   - 'reversed' (ancrage sur suffixe seul) : 2 requetes (plafond puis recuperation), l'ordre
 *     d'ancrage differant de l'ordre d'affichage rendrait la fusion incorrecte sans un tri PHP
 *     prealable -- chemin deja rapide (rapports mesures : 27 a 53 ms), non fusionne.
 * Le plafond porte sur le nombre de CORRESPONDANCES trouvees (lignes qui satisfont TOUTE la
 * clause WHERE combinee), jamais sur les lignes lues avant filtrage -- meme principe que
 * RelationsFinder::containingWords() (Phase 4). Sans ancrage indexe (contenant/avec/sans seuls),
 * la requete parcourt l'index normalized dans son integralite et s'arrete des que CEILING + 1
 * correspondances sont trouvees : ceci PEUT visiter jusqu'aux 838 180 lignes pour un motif rare
 * ou improbable (ex. "contenant/zzz", 0 resultat) -- exception bornee et documentee a l'interdit
 * "aucun scan complet de table au runtime" de CLAUDE.md, voir docs/DECISIONS.md D-019 pour
 * l'arbitrage complet (accepte par le propriétaire du produit apres mesure : plancher structurel
 * ~95-135 ms, aucune exposition automatique restante -- reports/query-plans/phase3-c1-fix.md).
 *
 * Au plus 2 requetes indexees, souvent 1 -- tres en-deca du budget de moins de 10 requetes par
 * fiche (CLAUDE.md, docs/01_MASTER_BRIEF.md).
 */
final class WordListSolver
{
    /**
     * Plafond de CORRESPONDANCES (lignes qui satisfont TOUTE la clause WHERE combinee --
     * ancrage ET predicats non indexes), jamais un plafond de lignes lues avant filtrage
     * (corrige depuis C1, voir le docblock de classe et docs/DECISIONS.md D-019 : l'ancienne
     * lecture "lignes examinees dans le panier ancre, avant predicats" decrivait exactement le
     * defaut qui produisait des faux negatifs silencieux). Mesure initiale (Phase 3, ancrage
     * seul) : <= 6 ms dans le pire cas pathologique ancre (0 resultat, panier de longueur le
     * plus grand de la base, 127 772 lignes) -- voir reports/query-plans/phase3.md. Mesure
     * etendue au cas sans ancrage (correction C1) : reports/query-plans/phase3-c1-fix.md.
     * Verite terrain, pas une estimation combinatoire comme RackSolver::SIGNATURE_CEILING : le
     * plafond est directement applique via LIMIT CEILING + 1 sur la requete elle-meme.
     */
    public const ROW_EXAMINATION_CEILING = 10_000;

    /** Taille de page des listes /mots/.... Aucune convention documentee ailleurs : valeur
     * choisie pour rester tres en-dessous de ROW_EXAMINATION_CEILING (200 pages disponibles
     * au plafond) tout en restant une page web raisonnable. */
    public const PAGE_SIZE = 50;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * Normalise le chemin brut recu par le routeur et resout la liste correspondante.
     *
     * Renvoie null si le chemin n'est pas exploitable (voir WordListFilters::fromPath()) :
     * c'est une erreur de saisie/routage, pas un resultat de recherche, et elle n'engendre
     * aucune requete SQLite -- meme convention que TermLookup::find() et RackSolver::solve().
     */
    public function solve(string $rawPath): ?WordListPage
    {
        $filters = WordListFilters::fromPath($rawPath);

        if ($filters === null) {
            return null;
        }

        if ($filters->isEmpty()) {
            // /mots seul (aucune contrainte) : hors perimetre de cette phase. docs/05 ne
            // liste aucune route sans au moins une contrainte, et un COUNT()/parcours sans
            // aucune clause WHERE visiterait l'index sur la totalite des 838 180 lignes --
            // un parcours complet en tout point comparable a un SCAN TABLE, meme s'il passe
            // techniquement par un index couvrant. Refuse plutot que d'exposer une route non
            // gouvernee : erreur de routage, pas un resultat de recherche (meme convention
            // que WordListFilters::fromPath() renvoyant null).
            return null;
        }

        return $this->isExactMode($filters)
            ? $this->solveExact($filters)
            : $this->solveBounded($filters);
    }

    private function isExactMode(WordListFilters $filters): bool
    {
        return $filters->suffix === null && !$filters->needsUnindexedPredicates();
    }

    /**
     * Regime EXACT : longueur et/ou prefixe seuls (ou motif reductible a une longueur +
     * prefixe initial, sans case connue residuelle). 2 requetes : COUNT() puis page.
     */
    private function solveExact(WordListFilters $filters): WordListPage
    {
        $pdo = $this->connection->pdo();

        [$where, $params] = $this->exactWhereClause($filters);
        $whereSql = $where === '' ? '' : 'WHERE ' . $where;

        $countStatement = $pdo->prepare("SELECT COUNT(*) c FROM terms $whereSql");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetch()['c'];

        $offset = (($filters->page) - 1) * self::PAGE_SIZE;

        // Tri par points (D-022) : "normalized" reste toujours le departage secondaire (ordre
        // d'affichage stable entre deux mots de meme score), meme quand $filters->sort trie
        // par score en premier. fromPath() garantit deja $filters->length !== null des que
        // $filters->sort est fourni -- seul ce sous-ensemble beneficie d'un index couvrant
        // pour ce tri (idx_terms_length_score_normalized, voir schema.sql).
        $orderBy = match ($filters->sort) {
            'points' => 'score ASC, normalized',
            'points-desc' => 'score DESC, normalized',
            default => 'normalized',
        };

        $pageStatement = $pdo->prepare(
            'SELECT normalized, score, length, is_ods8, is_ods9 FROM terms '
            . "$whereSql ORDER BY $orderBy LIMIT ? OFFSET ?"
        );
        $pageStatement->execute([...$params, self::PAGE_SIZE, $offset]);
        $rows = $pageStatement->fetchAll();

        return new WordListPage(
            canonicalPath: $filters->canonicalPath(),
            page: $filters->page,
            pageSize: self::PAGE_SIZE,
            items: self::toItems($rows),
            total: $total,
            exact: true,
            truncated: false,
            hasNextPage: $offset + self::PAGE_SIZE < $total,
            hasPreviousPage: $filters->page > 1,
            queryCount: 2,
        );
    }

    /**
     * @return array{0: string, 1: list<int|string>}
     */
    private function exactWhereClause(WordListFilters $filters): array
    {
        $conditions = [];
        $params = [];

        if ($filters->prefix !== null) {
            [$lower, $upper] = self::rangeBounds($filters->prefix);
            $conditions[] = 'normalized >= ?';
            $params[] = $lower;

            if ($upper !== null) {
                $conditions[] = 'normalized < ?';
                $params[] = $upper;
            }
        } elseif ($filters->pattern !== null) {
            $leading = self::patternLeadingPrefix($filters->pattern);

            if ($leading !== '') {
                [$lower, $upper] = self::rangeBounds($leading);
                $conditions[] = 'normalized >= ?';
                $params[] = $lower;

                if ($upper !== null) {
                    $conditions[] = 'normalized < ?';
                    $params[] = $upper;
                }
            }
        }

        if ($filters->length !== null) {
            $conditions[] = 'length = ?';
            $params[] = $filters->length;
        }

        if ($filters->status !== null) {
            // is_admitted (D-022) : colonne precalculee, jamais (is_ods8 = 1 OR is_ods9 = 1)
            // en ligne -- un OR sur deux colonnes distinctes empeche tout index couvrant pour
            // le COUNT() exact ci-dessous (mesure : ~750 ms sans cette colonne, 3 ms avec,
            // voir schema.sql et reports/query-plans/status-filter-admitted.md).
            $conditions[] = 'is_admitted = ?';
            $params[] = $filters->status === 'admis' ? 1 : 0;
        }

        return [implode(' AND ', $conditions), $params];
    }

    /**
     * Regime BORNE : tout ce qui a besoin d'un predicat non indexe (contenant, avec, sans,
     * motif avec case connue residuelle) et/ou d'un ancrage sur `reversed` (suffixe). Exactement
     * 2 requetes indexees, quel que soit le nombre de contraintes combinees.
     *
     * Correction C1 (audit final, code-reviewer, bloquant) : anchorClause() et extraPredicates()
     * sont desormais combines dans UNE SEULE clause WHERE, appliquee ENSEMBLE a la fois pour le
     * plafond et pour la recuperation -- jamais anchorClause() seul borne a CEILING lignes PUIS
     * extraPredicates() filtre dans ce sous-ensemble deja tronque. L'ancienne version faisait
     * exactement ca : avec "contenant" seul (aucun ancrage indexe), les CEILING premieres lignes
     * dans l'ordre alphabetique de TOUTE la table etaient lues avant meme d'appliquer instr(),
     * donc un mot comme XYL (present dans 270 mots, aucun ne commencant par une lettre proche du
     * debut de l'alphabet en nombre suffisant) ressortait "0 mot trouve" -- faux negatif silencieux.
     * Meme principe desormais que RelationsFinder::containingWords() (Phase 4, deja valide) : le
     * LIMIT porte sur le nombre de CORRESPONDANCES trouvees, jamais sur les lignes lues avant
     * filtrage. Mesures avant/apres : reports/query-plans/phase3-c1-fix.md.
     *
     * Fusion en 1 requete (audit final, code-optimizer, constat I-1) quand $anchorOrder est deja
     * 'normalized' : la requete de plafond et la requete de recuperation executaient exactement
     * le meme parcours pour n'en extraire qu'un booleen -- gain mesure de 35 a 50% sur les cas
     * sans ancrage indexe (les plus couteux), resultat prouve identique (total ET truncated) sur
     * 10 cas representatifs. Non applique a $anchorOrder === 'reversed' (ancrage suffixe seul) :
     * l'ordre d'ancrage y differe de l'ordre d'affichage, retirer la bonne ligne demanderait un
     * tri PHP prealable pour un chemin deja rapide (27 a 53 ms mesures) -- reste a 2 requetes.
     */
    private function solveBounded(WordListFilters $filters): WordListPage
    {
        $pdo = $this->connection->pdo();

        [$anchorWhere, $anchorParams, $anchorOrder, $anchorType, $frequencyQueryCount] = $this->anchorClause($filters);
        [$extraWhere, $extraParams] = $this->extraPredicates($filters, $anchorType);

        $conditions = array_values(array_filter([$anchorWhere, $extraWhere], static fn (string $c): bool => $c !== ''));
        $whereSql = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $params = [...$anchorParams, ...$extraParams];

        if ($anchorOrder === 'normalized') {
            // L'ordre d'ancrage EST deja l'ordre d'affichage : une seule requete LIMIT
            // CEILING + 1 suffit. $truncated se deduit du nombre de lignes rendues ; la ligne
            // CEILING + 1 (au-dela de ce que $total doit annoncer) est simplement retiree.
            $statement = $pdo->prepare(
                "SELECT normalized, score, length, is_ods8, is_ods9 FROM terms $whereSql "
                . 'ORDER BY normalized LIMIT ?'
            );
            $statement->execute([...$params, self::ROW_EXAMINATION_CEILING + 1]);
            $rows = $statement->fetchAll();

            $truncated = count($rows) > self::ROW_EXAMINATION_CEILING;

            if ($truncated) {
                array_pop($rows);
            }

            $queryCount = 1 + $frequencyQueryCount;
        } else {
            // Ancrage 'reversed' (suffixe seul ou combine sans prefixe) : 2 requetes, voir
            // l'entete de la methode pour la raison de ne pas fusionner ce chemin.
            $boundaryStatement = $pdo->prepare(
                "SELECT COUNT(*) c FROM (SELECT id FROM terms $whereSql ORDER BY $anchorOrder LIMIT ?)"
            );
            $boundaryStatement->execute([...$params, self::ROW_EXAMINATION_CEILING + 1]);
            $boundaryCount = (int) $boundaryStatement->fetch()['c'];
            $truncated = $boundaryCount > self::ROW_EXAMINATION_CEILING;

            $fetchStatement = $pdo->prepare(
                'SELECT normalized, score, length, is_ods8, is_ods9 FROM ('
                . "SELECT normalized, score, length, is_ods8, is_ods9 FROM terms $whereSql "
                . "ORDER BY $anchorOrder LIMIT ?"
                . ') ORDER BY normalized'
            );
            $fetchStatement->execute([...$params, self::ROW_EXAMINATION_CEILING]);
            $rows = $fetchStatement->fetchAll();

            $queryCount = 2 + $frequencyQueryCount;
        }

        // Tri par points (D-022) : le panier est deja borne par ROW_EXAMINATION_CEILING a ce
        // stade (au plus 10 000 lignes) -- un tri PHP sur ce panier reste dans le budget TTFB
        // (mesure : 173 ms au pire cas rencontre, 10 000 lignes ; largement moins pour un
        // panier plus modeste). Stable : normalized reste le departage entre deux memes scores
        // (usort() de PHP est stable depuis PHP 8.0). fromPath() garantit que $filters->sort
        // n'est jamais fourni sans $filters->length -- pas de restriction supplementaire ici.
        if ($filters->sort !== null) {
            $direction = $filters->sort === 'points-desc' ? -1 : 1;
            usort($rows, static fn (array $a, array $b): int => $direction * ((int) $a['score'] <=> (int) $b['score']));
        }

        $total = count($rows);
        $offset = ($filters->page - 1) * self::PAGE_SIZE;
        $pageRows = array_slice($rows, $offset, self::PAGE_SIZE);

        return new WordListPage(
            canonicalPath: $filters->canonicalPath(),
            page: $filters->page,
            pageSize: self::PAGE_SIZE,
            items: self::toItems($pageRows),
            total: $total,
            exact: !$truncated,
            truncated: $truncated,
            hasNextPage: $offset + self::PAGE_SIZE < $total,
            hasPreviousPage: $filters->page > 1,
            queryCount: $queryCount,
        );
    }

    /**
     * Clause d'ancrage : la contrainte la plus selective disponible parmi prefixe, suffixe,
     * longueur, dans cet ordre de preference -- toujours servie par un index existant
     * (sqlite_autoindex_terms_1, idx_terms_reversed ou idx_terms_length_normalized). Prefixe
     * et longueur combines restent ancres sur normalized (ordre d'affichage direct, pas de tri
     * supplementaire necessaire) ; suffixe seul ou combine s'ancre sur reversed (necessite le
     * tri explicite par normalized applique dans solveBounded()).
     *
     * Regression corrigee en DEUX temps (D-025bis, 2026-08-09) : quand prefixe ET suffixe
     * sont TOUS DEUX explicites (ex. commencant/r/terminant/h), l'ancien code ancrait
     * TOUJOURS sur le prefixe, laissant le suffixe en predicat residuel non indexe --
     * catastrophique quand le prefixe est frequent (jusqu'a 1 211 ms mesure, commencant/p/
     * terminant/h, decouvert par l'audit du lot D-025, 611 pages commencant+terminant
     * venant d'etre ouvertes a l'indexation). Une premiere iteration (choisir dynamiquement
     * le cote le moins frequent comme ancrage, comptes list_counts) a ramene ce cas a 17 ms,
     * mais un balayage complet des 611 combinaisons du lot a revele 53 cas encore au-dessus
     * du budget des que les DEUX lettres sont frequentes (jusqu'a 6 675 ms). Corrige en
     * profondeur par idx_terms_startletter_endletter_normalized (schema.sql) pour le cas ou
     * prefixe et suffixe sont chacun d'une seule lettre -- voir le bloc ci-dessous, prioritaire
     * sur le choix par frequence qui ne sert plus que les prefixes/suffixes multi-lettres (hors
     * du lot D-025, jamais mesures problematiques). Mesures completes :
     * reports/query-plans/prefix-suffix-anchor-fix.md.
     *
     * CORRECTIF (audit 2e passe, D-025, constat sur la portee) : ce bloc s'applique QUE la
     * longueur soit presente ou non -- une premiere version de ce docblock annoncait a tort
     * "portee exacte du lot D-025" (sans longueur) comme si le code l'excluait explicitement,
     * jamais verifie contre le code reel (aucune condition sur $filters->length ci-dessous).
     * Seule la variante SANS longueur est effectivement ouverte a l'indexation a ce jour
     * (D-025) ; la variante AVEC longueur beneficie du meme index sans effort supplementaire
     * (mesure : 57,9 ms sur 9-lettres/commencant/r/terminant/e) mais reste noindex par omission.
     *
     * @return array{0: string, 1: list<int|string>, 2: string, 3: string, 4: int}
     */
    private function anchorClause(WordListFilters $filters): array
    {
        $conditions = [];
        $params = [];
        $order = 'normalized';
        $anchorType = 'none';
        $frequencyQueryCount = 0;

        // Prefixe ET suffixe D'UNE SEULE LETTRE CHACUN, avec ou sans longueur : egalite sur les
        // deux expressions substr() a la fois, servie par
        // idx_terms_startletter_endletter_normalized -- ni l'un ni l'autre ne devient un
        // predicat residuel, les deux sont couverts par l'index. Une longueur, si fournie,
        // s'ajoute en predicat supplementaire sur le petit panier deja isole par l'egalite (pas
        // dans cet index, mais bon marche a ce stade). Toujours prioritaire sur le choix par
        // frequence ci-dessous.
        if ($filters->prefix !== null && strlen($filters->prefix) === 1 && $filters->suffix !== null && strlen($filters->suffix) === 1) {
            $conditions[] = 'substr(normalized, 1, 1) = ?';
            $params[] = $filters->prefix;
            $conditions[] = 'substr(reversed, 1, 1) = ?';
            $params[] = $filters->suffix;

            if ($filters->length !== null) {
                $conditions[] = 'length = ?';
                $params[] = $filters->length;
            }

            return [implode(' AND ', $conditions), $params, 'normalized', 'prefix_suffix_pair', 0];
        }

        $prefix = $filters->prefix ?? ($filters->pattern !== null ? self::patternLeadingPrefix($filters->pattern) : null);

        $preferSuffixAnchor = false;

        if ($filters->prefix !== null && $filters->suffix !== null) {
            // Prefixe et suffixe explicites mais PAS tous deux d'une seule lettre (ex.
            // commencant/ch/terminant/tion) : hors de la portee mesuree problematique du lot
            // D-025 (jamais lie ni indexe aujourd'hui) -- choix par frequence en repli, une
            // amelioration reelle sur l'ancien "toujours le prefixe" sans necessiter un index
            // dedie a ce cas plus rare.
            $preferSuffixAnchor = $this->suffixLetterIsRarer($filters->prefix[0], $filters->suffix[strlen($filters->suffix) - 1]);
            $frequencyQueryCount = 1;
        }

        if ($prefix !== null && $prefix !== '' && !$preferSuffixAnchor) {
            [$lower, $upper] = self::rangeBounds($prefix);
            $conditions[] = 'normalized >= ?';
            $params[] = $lower;

            if ($upper !== null) {
                $conditions[] = 'normalized < ?';
                $params[] = $upper;
            }

            if ($filters->length !== null) {
                $conditions[] = 'length = ?';
                $params[] = $filters->length;
            }

            $anchorType = 'prefix';
        } elseif ($filters->suffix !== null) {
            $order = 'reversed';
            [$lower, $upper] = self::rangeBounds(Normalizer::reverse($filters->suffix));
            $conditions[] = 'reversed >= ?';
            $params[] = $lower;

            if ($upper !== null) {
                $conditions[] = 'reversed < ?';
                $params[] = $upper;
            }

            if ($filters->length !== null) {
                $conditions[] = 'length = ?';
                $params[] = $filters->length;
            }

            $anchorType = 'suffix';
        } elseif ($filters->length !== null) {
            $conditions[] = 'length = ?';
            $params[] = $filters->length;
            $anchorType = 'length';
        }
        // Aucune des trois : ancrage sur l'ordre normalized complet, SANS aucune clause WHERE
        // indexee (cas "avec"/"contenant"/"sans" seuls, sans longueur ni prefixe ni suffixe) --
        // le cas le plus large possible. Le plafond ROW_EXAMINATION_CEILING protege le nombre de
        // CORRESPONDANCES rendues, pas le nombre de lignes que la requete doit lire pour les
        // trouver : sans index sur les predicats non indexes, ce cas peut visiter la table
        // entiere (voir extraPredicates() ci-dessous et docs/DECISIONS.md D-019).

        return [implode(' AND ', $conditions), $params, $order, $anchorType, $frequencyQueryCount];
    }

    /**
     * true si la lettre de fin est strictement moins frequente que la lettre de debut --
     * decide quel cote ancrer quand prefixe et suffixe sont tous deux explicites mais PAS tous
     * deux d'une seule lettre (voir anchorClause() : ce cas mono-lettre est traite par
     * idx_terms_startletter_endletter_normalized, pas par cette heuristique). Comptes
     * precalcules (list_counts, list_type 'start'/'end', D-017), jamais un GROUP BY sur `terms`
     * : 1 requete triviale sur une table de 1 731 lignes. Absence improbable (les 26 lettres
     * ont toujours un compte, meme 0 exclu par R5 n'existe pas ici -- 'start'/'end' couvrent la
     * base entiere) traitee en faveur du comportement historique (prefixe ancre) plutot que de
     * risquer une erreur.
     *
     * HEURISTIQUE, pas un choix optimal (constat de l'audit 2e passe, D-025) : compare
     * uniquement la PREMIERE lettre du prefixe a la DERNIERE lettre du suffixe, jamais la
     * selectivite reelle de la plage multi-lettres complete (ex. "CH" contre "TION" -- cette
     * fonction ne regarde que C contre N). Peut donc choisir le cote le moins bon dans certains
     * cas multi-lettres. Cette famille (prefixe/suffixe multi-lettres) reste hors du lot D-025,
     * jamais mesuree problematique a ce jour -- si un futur lot l'expose, cette heuristique
     * devra etre revue avant, pas supposee suffisante par analogie avec le cas mono-lettre.
     */
    private function suffixLetterIsRarer(string $prefixLetter, string $suffixLetter): bool
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_type, count FROM list_counts"
            . " WHERE (list_type = 'start' AND list_key = ?) OR (list_type = 'end' AND list_key = ?)"
        );
        $statement->execute([$prefixLetter, $suffixLetter]);

        $prefixCount = PHP_INT_MAX;
        $suffixCount = PHP_INT_MAX;

        foreach ($statement as $row) {
            if ($row['list_type'] === 'start') {
                $prefixCount = (int) $row['count'];
            } else {
                $suffixCount = (int) $row['count'];
            }
        }

        return $suffixCount < $prefixCount;
    }

    /**
     * Predicats non indexes : contenant (instr), avec (compte de lettres via LENGTH/REPLACE,
     * pas de fonction dediee en SQLite standard), sans (absence via instr), suffixe quand le
     * prefixe est deja l'ancrage (reversed en predicat, pas en index dans ce cas precis), et
     * les cases connues du motif au-dela du prefixe initial (substr). Chaque predicat est une
     * expression SQL pure, combinee a anchorClause() dans la MEME clause WHERE par
     * solveBounded() (correction C1) -- evaluee par SQLite ligne par ligne au fil du parcours de
     * l'index d'ancrage (`normalized` ou `reversed`), potentiellement sur les 838 180 lignes
     * quand aucun ancrage indexe n'existe (voir le docblock de anchorClause() ci-dessus et le
     * docblock de classe).
     *
     * @param string $anchorType 'prefix'|'suffix'|'length'|'none', decide par anchorClause() --
     *        determine lequel de prefixe/suffixe doit redevenir un predicat residuel ici
     *        (celui qui N'A PAS ete choisi comme ancrage, D-025bis).
     * @return array{0: string, 1: list<int|string>}
     */
    private function extraPredicates(WordListFilters $filters, string $anchorType): array
    {
        $conditions = [];
        $params = [];

        if ($filters->status !== null) {
            // is_admitted (D-022) : le regime BORNE est deja protege par LIMIT
            // (ROW_EXAMINATION_CEILING), ce predicat s'ajoute au meme cout que n'importe quel
            // autre -- mesure : 7 a 45 ms selon le panier, jamais besoin d'index dedie ici
            // (contrairement au regime EXACT, voir exactWhereClause()).
            $conditions[] = 'is_admitted = ?';
            $params[] = $filters->status === 'admis' ? 1 : 0;
        }

        // Prefixe residuel (D-025bis) : uniquement quand un prefixe explicite existe mais n'a
        // PAS ete choisi comme ancrage -- soit choix par frequence (anchorType === 'suffix'),
        // soit deja couvert par l'egalite combinee (anchorType === 'prefix_suffix_pair', voir
        // anchorClause()) auquel cas AUCUN residuel n'est necessaire pour le prefixe.
        if ($filters->prefix !== null && $anchorType !== 'prefix' && $anchorType !== 'prefix_suffix_pair') {
            [$lower, $upper] = self::rangeBounds($filters->prefix);
            $conditions[] = 'normalized >= ?';
            $params[] = $lower;

            if ($upper !== null) {
                $conditions[] = 'normalized < ?';
                $params[] = $upper;
            }
        }

        // Le suffixe redevient un predicat explicite uniquement quand il n'a pas ete choisi
        // comme ancrage (prefixe choisi a sa place -- cas historique -- ou motif avec prefixe
        // initial non vide, deja integre a anchorClause() dans ce dernier cas) ET qu'il n'est
        // pas deja couvert par l'egalite combinee (D-025bis, anchorType === 'prefix_suffix_pair').
        if ($filters->suffix !== null && $anchorType !== 'suffix' && $anchorType !== 'prefix_suffix_pair') {
            [$lower, $upper] = self::rangeBounds(Normalizer::reverse($filters->suffix));
            $conditions[] = 'reversed >= ?';
            $params[] = $lower;

            if ($upper !== null) {
                $conditions[] = 'reversed < ?';
                $params[] = $upper;
            }
        }

        if ($filters->contains !== null) {
            $conditions[] = 'instr(normalized, ?) > 0';
            $params[] = $filters->contains;
        }

        if ($filters->position !== null) {
            // Position (D-023) : meme predicat substr() que les cases connues residuelles du
            // motif (patternResidualPredicates() ci-dessous) -- reutilise volontairement la
            // meme expression plutot qu'une nouvelle, deja mesuree sans surcout notable sur un
            // panier ancre par longueur (voir reports/query-plans/position-family.md). CAST
            // necessaire pour la meme raison que patternResidualPredicates() : substr()'s
            // deuxieme argument n'a pas d'affinite de colonne declaree.
            $conditions[] = 'substr(normalized, CAST(? AS INTEGER), 1) = ?';
            $params[] = $filters->position;
            $params[] = $filters->positionLetter;
        }

        foreach ($filters->withLetters as $letter => $minCount) {
            if ($minCount === 1) {
                // Cas ecrasement majoritaire (une seule occurrence exigee) : simple presence,
                // instr() > 0 -- mesure (correction C1, reports/query-plans/phase3-c1-fix.md) :
                // ~4x plus rapide que LENGTH()/REPLACE() ci-dessous (91,6 ms contre 351,7 ms sur
                // "avec X, Y, Z" sans aucun ancrage, panier de 838 180 lignes). REPLACE() alloue
                // une nouvelle chaine a chaque appel ; instr() s'arrete a la premiere occurrence.
                $conditions[] = 'instr(normalized, ?) > 0';
                $params[] = $letter;

                continue;
            }

            // CAST(? AS INTEGER) : LENGTH()-LENGTH(REPLACE(...)) est une expression calculee,
            // sans affinite de colonne declaree -- contrairement a `length = ?` (colonne
            // INTEGER, coercion automatique), SQLite compare alors un entier a un parametre
            // lie tel qu'il a ete transmis. PDOStatement::execute(array) transmet les entiers
            // PHP comme du texte : sans ce CAST explicite, INTEGER >= TEXT echoue toujours
            // (l'ordre de type de SQLite place tout entier avant tout texte quand les types
            // different), et la contrainte "avec" ne renvoyait jamais aucune ligne -- verifie
            // en developpement, corrige ici plutot que suppose. Reservee a minCount >= 2 (lettre
            // repetee) : instr() ne peut pas exprimer un COMPTE d'occurrences.
            $conditions[] = '(LENGTH(normalized) - LENGTH(REPLACE(normalized, ?, \'\'))) >= CAST(? AS INTEGER)';
            $params[] = $letter;
            $params[] = $minCount;
        }

        foreach ($filters->withoutLetters as $letter) {
            $conditions[] = 'instr(normalized, ?) = 0';
            $params[] = $letter;
        }

        if ($filters->pattern !== null) {
            foreach (self::patternResidualPredicates($filters->pattern) as [$position, $letter]) {
                // Meme raison que ci-dessus : substr()'s deuxieme argument (position) n'a pas
                // d'affinite de colonne, CAST necessaire pour la meme classe de bug.
                $conditions[] = 'substr(normalized, CAST(? AS INTEGER), 1) = ?';
                $params[] = $position;
                $params[] = $letter;
            }
        }

        return [implode(' AND ', $conditions), $params];
    }

    /** Portion de $pattern avant la premiere case inconnue ('-'), '' si $pattern commence par '-'. */
    private static function patternLeadingPrefix(string $pattern): string
    {
        $dash = strpos($pattern, '-');

        return $dash === false ? $pattern : substr($pattern, 0, $dash);
    }

    /**
     * Cases connues APRES la premiere case inconnue -- celles que anchorClause() ne peut pas
     * deja couvrir via un prefixe. Position 1-based (convention substr() SQLite).
     *
     * @return list<array{0: int, 1: string}>
     */
    private static function patternResidualPredicates(string $pattern): array
    {
        $dash = strpos($pattern, '-');

        if ($dash === false) {
            return [];
        }

        $residual = [];

        for ($i = $dash; $i < strlen($pattern); $i++) {
            if ($pattern[$i] !== '-') {
                $residual[] = [$i + 1, $pattern[$i]];
            }
        }

        return $residual;
    }

    /**
     * Bornes [inclusive, exclusive) d'une plage de prefixe sur une colonne triee en ordre
     * binaire (A-Z uniquement, D-009) : incremente le dernier caractere qui n'est pas 'Z' en
     * remontant depuis la fin, tronque apres lui. Si $prefix ne contient que des 'Z', aucune
     * borne superieure n'existe dans l'alphabet A-Z -- la plage reste ouverte (upper = null,
     * seule la borne inferieure s'applique). Meme principe que le commentaire de schema.sql
     * ("normalized >= 'NOIT' AND normalized < 'NOIU'"), generalise a un prefixe quelconque.
     *
     * @return array{0: string, 1: string|null}
     */
    private static function rangeBounds(string $prefix): array
    {
        $chars = str_split($prefix);

        for ($i = count($chars) - 1; $i >= 0; $i--) {
            if ($chars[$i] !== 'Z') {
                $chars[$i] = chr(ord($chars[$i]) + 1);

                return [$prefix, implode('', array_slice($chars, 0, $i + 1))];
            }
        }

        return [$prefix, null];
    }

    /**
     * @param list<array{normalized: string, score: string|int, length: string|int, is_ods8: string|int, is_ods9: string|int}> $rows
     * @return list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, status: string}>
     */
    private static function toItems(array $rows): array
    {
        return array_map(static function (array $row): array {
            $isOds8 = (int) $row['is_ods8'] === 1;
            $isOds9 = (int) $row['is_ods9'] === 1;

            return [
                'normalized' => $row['normalized'],
                'slug' => strtolower($row['normalized']),
                'score' => (int) $row['score'],
                'length' => (int) $row['length'],
                'isOds8' => $isOds8,
                'isOds9' => $isOds9,
                // Toute ligne de `terms` est is_french = 1 par construction (D-013) : le
                // statut ne peut jamais valoir TermPage::STATUS_UNKNOWN ici, seulement l'un
                // des deux autres membres du modele a trois statuts ferme (CLAUDE.md).
                'status' => ($isOds8 || $isOds9) ? TermPage::STATUS_ADMITTED : TermPage::STATUS_FRENCH_NOT_ADMITTED,
            ];
        }, $rows);
    }
}
