<?php

declare(strict_types=1);

/**
 * D-041 -- Règle de priorité GLOBALE pour départager un groupe de pages /mots/... au contenu de
 * liste strictement identique (même ensemble EXACT de mots -- voir
 * scripts/check_combinatorial_duplicates.php pour la DÉTECTION du groupe lui-même : ce fichier ne
 * détecte rien, il TRANCHE un groupe déjà connu comme doublon).
 *
 * ## Pourquoi ce fichier existe
 *
 * Constat C-4 du 4e audit consolidé (docs/DECISIONS.md, D-040) : quatre corrections consécutives
 * (D-037, D-038, D-039, D-040) ont chacune ajouté une règle de priorité écrite à la main, propre à
 * UNE paire de familles précise (parent/enfant D-037, sœurs D-038, croisé longueur×avec D-039,
 * hiérarchie à trois paliers "avec" D-040) -- rien n'empêchait une 5e récidive. Confirmé le
 * 2026-08-21 : scripts/check_combinatorial_duplicates.php (détecteur générique, C-4) a trouvé
 * 1 656 nouveaux groupes de doublons (2 089 pages en excès) entre PAIRES DE FAMILLES jamais
 * comparées avant, notamment word_list_commencant × word_list_terminant (408 groupes).
 *
 * Ce fichier fournit la moitié manquante : une fonction UNIQUE et testée qui, une fois un groupe
 * de doublons DÉTECTÉ (par n'importe quel mécanisme, pas seulement check_combinatorial_
 * duplicates.php), décide laquelle de ses pages reste candidate à l'indexation -- sans avoir
 * besoin de connaître à l'avance la paire de familles concernée, exactement comme le détecteur
 * lui-même. scripts/propose_seo_batch.php l'utilise pour filtrer chaque cas concerné ; ce fichier
 * est partagé (pas seulement inline dans propose_seo_batch.php) pour rester directement
 * unit-testable (tests/Seo/ResolveDuplicateWinnerTest.php) sans passer par un sous-processus.
 *
 * ## Règle, dans l'ordre (demande produit explicite, D-041)
 *
 * 1. Le nombre de COMPOSANTS DE CONTRAINTE le plus BAS gagne -- extension du principe déjà établi
 *    en D-025/D-039 ("la forme la plus générale/simple gagne sur la plus spécifique") :
 *      longueur    = 1 composant si présente
 *      commençant  = 1
 *      contenant   = 1
 *      terminant   = 1
 *      position    = 2 (index + lettre)
 *      "avec"      = 1 par lettre distincte
 *      "sans"      = 1 par lettre distincte
 *      motif       = 1
 *    Calculé directement depuis App\Search\WordListFilters::fromPath() (source de vérité unique
 *    de la grammaire /mots/...), jamais un chiffre recopié à la main par famille -- une famille
 *    ne porte donc plus jamais sa propre "vérité" du nombre de composants, elle est dérivée du
 *    code réel à chaque appel.
 * 2. À égalité de composants ENTRE DEUX FAMILLES DIFFÉRENTES : comparaison LEXICOGRAPHIQUE des
 *    RÔLES présents, dans l'ordre canonique imposé partout (URL, clés de registre, canonicals --
 *    docs/05_URL_SEO_INDEXATION.md, docblock de App\Search\WordListFilters) : longueur ->
 *    commençant -> contenant -> terminant -> position -> avec -> sans -> motif. Le rôle le plus
 *    proche du DÉBUT de cet ordre gagne. Cas le plus fréquent observé (408/1656 groupes) :
 *    commençant (1 rôle) vs terminant (1 rôle) -- commençant gagne. Cette règle GÉNÉRALISE ce cas
 *    à des groupes impliquant 3+ familles/rôles différents (ex. combined_with_letter, 3 rôles,
 *    contre word_list_length seul, 1 rôle).
 * 3. À égalité de composants ET de rôles ENTRE PAGES DE LA MÊME famille à chaîne variable
 *    (commençant/contenant/terminant -- même componentCount() ET même roleSignature() PAR
 *    CONSTRUCTION quelle que soit la longueur réelle du préfixe/suffixe) : compare la PROFONDEUR
 *    du composant variable, seoDuplicatePriorityVariableComponentDepth() -- la forme la plus
 *    COURTE (la plus générale) gagne. Voir "Correctif I-1" ci-dessous pour le détail complet.
 * 4. À égalité de composants, de rôles ET de profondeur (vraies pages SŒURS, ex. deux triplets
 *    "avec" différents, ou deux suffixes "terminant" de même longueur mais de lettres
 *    différentes) : la forme alphabétiquement la plus petite (route_path complet) gagne -- même
 *    convention que D-029/D-030/D-031/D-038.
 *
 * Filet de sécurité déterministe (jamais exercé sur les familles réellement définies à ce jour --
 * vérifié empiriquement : les règles 1-4 ci-dessus résolvent la totalité des 1 656 groupes réels
 * trouvés le 2026-08-21, voir reports/query-plans/d040-d041-correctif.md) : nom de famille puis
 * route_path complet, pour garder un tri total déterministe même si une égalité totale imprévue
 * survenait.
 *
 * ## Correctif I-1 (5e audit consolidé, 2026-08-22) — départage par profondeur du composant variable
 *
 * Avant ce correctif, deux pages à égalité de composants ET de rôles (couple parent/enfant OU
 * sœurs au sein d'une MÊME famille à chaîne variable, ex. "commençant/wu" contre
 * "commençant/wub") tombaient directement sur la comparaison alphabétique de $family puis
 * $route_path (règle 4 ci-dessus) -- correct pour "commençant" (App\Search\
 * PrefixExtensionLinksBuilder ajoute la lettre d'extension EN QUEUE, ex. "wu" -> "wub" : le
 * parent est alors toujours un préfixe-au-sens-chaîne de l'enfant, donc alphabétiquement plus
 * petit, la comparaison fonctionnait par coïncidence) mais FAUX pour "terminant" : App\Search\
 * SuffixExtensionLinksBuilder ajoute la lettre d'extension EN TÊTE du suffixe ("zt" -> "azt",
 * jamais "zt" -> "zta"), donc le chemin de l'enfant n'est PLUS garanti alphabétiquement après
 * celui du parent -- ex. "/mots/terminant/azt" précède "/mots/terminant/zt" alphabétiquement bien
 * que "azt" soit l'ENFANT de "zt". Une comparaison alphabétique directe aurait donc désigné
 * l'enfant comme gagnant, à l'inverse du principe "la forme la plus générale/simple gagne"
 * (D-025/D-039), ET retiré le parent -- qui est pourtant, pour "terminant", le seul point
 * d'entrée réel (un suffixe de longueur N+1 n'est jamais lié que depuis son parent de longueur N,
 * jamais l'inverse).
 *
 * Même bug, même correctif qu'App\Search\DuplicatePageResolver (data-engine, vérifié en lecture
 * seule, jamais modifié ici) -- confirmé indépendamment par l'agent data-engine sur son propre
 * fichier au cours du même audit. Inatteignable sur le balayage réel du 2026-08-21 (0 groupe
 * SŒUR pur trouvé pour "terminant" ni pour "commençant" parmi les 1 656 groupes -- tous CROISÉS
 * entre familles différentes, départagés par la règle 2 avant même d'atteindre cette comparaison,
 * revérifié fraîchement le 2026-08-22, voir reports/query-plans/d040-d041-correctif.md), donc sans
 * effet sur D041_EXCLUDED_ROUTE_PATHS ni sur le lot déjà appliqué -- mais une divergence réelle du
 * garde-fou lui-même, corrigée ici par principe. Voir seoDuplicatePriorityVariableComponentDepth()
 * ci-dessous pour l'implémentation.
 *
 * ## Correctif I-2 (5e audit consolidé, 2026-08-22) — alignement avec App\Search\DuplicatePageResolver
 *
 * seoDuplicatePriorityProfile() (ci-dessous) émettait, avant ce correctif, UN SEUL jeton de rôle
 * par TYPE de rôle présent (ex. un seul jeton "avec" quel que soit le nombre de lettres "avec"
 * distinctes, un seul jeton "position" au lieu de deux) — alors qu'App\Search\DuplicatePageResolver::
 * roleSignature() (data-engine, vérifié en lecture seule, jamais modifié ici) émet TOUJOURS un jeton
 * PAR COMPOSANT (une boucle `for` par lettre "avec"/"sans", deux push pour "position"), si bien que
 * la longueur du tableau de rôles y égale toujours componentCount(). Les deux implémentations
 * pouvaient donc diverger dès que deux pages partageaient le même nombre TOTAL de composants et le
 * même ENSEMBLE de rôles distincts mais des MULTIPLICITÉS différentes entre rôles répétables (ex.
 * avec×2 + sans×1 contre avec×1 + sans×2, 3 composants chacune, mais [avec,sans] identique des deux
 * côtés dans l'ancienne version au lieu de [avec,avec,sans] contre [avec,sans,sans]) — inatteignable
 * aujourd'hui (`App\Seo\Family::WORD_LIST_SANS` reste en `NEVER_SITEMAP` permanent, docblock de
 * `App\Seo\Family`), donc sans effet sur `D041_EXCLUDED_ROUTE_PATHS` (scripts/propose_seo_batch.php),
 * figée au moment de son calcul hors ligne, ni sur le lot D-041 déjà appliqué en production — mais
 * une divergence réelle de la fonction elle-même, exactement la « seconde source de vérité » que
 * D-041 affirme avoir supprimée. Corrigé en poussant un jeton par composant pour "position" (deux
 * fois) et pour chaque lettre "avec"/"sans" distincte (boucle `for`, plus de push conditionnel
 * unique) — voir seoDuplicatePriorityProfile() ci-dessous et tests/Seo/ResolveDuplicateWinnerTest.php
 * pour un cas de test couvrant explicitement cette divergence de multiplicité.
 */

if (!function_exists('seoDuplicatePriorityRoleRanks')) {
    /**
     * Rang canonique de chaque rôle, lu PAR RÉFLEXION sur la constante privée
     * App\Search\WordListFilters::KEYWORDS plutôt que recopié à la main -- si cet ordre change un
     * jour côté grammaire, ce classement change avec lui automatiquement, jamais une seconde
     * source de vérité qui pourrait diverger silencieusement (même discipline anti-C-4 que le
     * reste de ce mécanisme).
     *
     * @return array<string, int>
     */
    function seoDuplicatePriorityRoleRanks(): array
    {
        static $ranks = null;

        if ($ranks !== null) {
            return $ranks;
        }

        $reflection = new ReflectionClass(\App\Search\WordListFilters::class);
        /** @var list<string> $keywords */
        $keywords = $reflection->getConstant('KEYWORDS');

        // Sanity check : la grammaire commence bien par commencant/contenant/terminant/position/
        // avec/sans/motif, dans cet ordre ("statut"/"tri" sont des raffinements d'affichage,
        // D-022, hors de la portée "contrainte de recherche" -- jamais portés par une famille
        // combinatoire couverte par ce mécanisme). Si ce n'est plus vrai, la fonction refuse de
        // deviner un classement erroné plutôt que de produire un résultat silencieusement faux.
        $expectedPrefix = ['commencant', 'contenant', 'terminant', 'position', 'avec', 'sans', 'motif'];

        if (array_slice($keywords, 0, 7) !== $expectedPrefix) {
            throw new \RuntimeException(
                "App\\Search\\WordListFilters::KEYWORDS a change d'ordre (recu : " . implode(',', $keywords)
                . ') -- scripts/lib/seo_duplicate_priority.php doit etre revu (D-041)'
            );
        }

        $ranks = ['longueur' => 0];

        foreach ($keywords as $i => $keyword) {
            $ranks[$keyword] = $i + 1;
        }

        return $ranks;
    }
}

if (!function_exists('seoDuplicatePriorityProfile')) {
    /**
     * Profil de contrainte D-041 d'une page /mots/... : nombre de COMPOSANTS et liste ORDONNÉE
     * (rang canonique croissant, sans doublon) des RÔLES présents. Dérivé directement de
     * App\Search\WordListFilters::fromPath() -- jamais un chiffre ou une liste recopiés à la main
     * par famille.
     *
     * @return array{components: int, roles: list<int>}
     */
    function seoDuplicatePriorityProfile(string $routePath): array
    {
        if (!str_starts_with($routePath, '/mots')) {
            throw new \InvalidArgumentException("route_path hors de /mots/... : {$routePath}");
        }

        $filters = \App\Search\WordListFilters::fromPath(substr($routePath, strlen('/mots')));

        if ($filters === null) {
            throw new \InvalidArgumentException("route_path inexploitable par WordListFilters::fromPath() : {$routePath}");
        }

        $ranks = seoDuplicatePriorityRoleRanks();
        $components = 0;
        $roles = [];

        if ($filters->length !== null) {
            $components += 1;
            $roles[] = $ranks['longueur'];
        }

        if ($filters->prefix !== null) {
            $components += 1;
            $roles[] = $ranks['commencant'];
        }

        if ($filters->contains !== null) {
            $components += 1;
            $roles[] = $ranks['contenant'];
        }

        if ($filters->suffix !== null) {
            $components += 1;
            $roles[] = $ranks['terminant'];
        }

        if ($filters->position !== null) {
            // index + lettre -- regle explicite du produit (D-041), pas 1 seul composant : DEUX
            // jetons de role, un par composant, exactement comme App\Search\DuplicatePageResolver::
            // roleSignature() (verifie en lecture seule, meme double push) -- pas un seul jeton
            // comme avant ce correctif (I-2, audit 2026-08-22, cf. docblock de fichier).
            $components += 2;
            $roles[] = $ranks['position'];
            $roles[] = $ranks['position'];
        }

        if ($filters->withLetters !== []) {
            // Un jeton PAR LETTRE "avec" distincte exigee -- pas un seul jeton par TYPE de role
            // comme avant ce correctif (I-2). withLetters est deja indexe par lettre normalisee
            // (une cle par lettre distincte, WordListFilters::readLetterMultiset()), donc
            // count() donne directement le nombre de jetons a pousser, meme si une lettre porte
            // un minCount >= 2 (repetition) -- exactement comme App\Search\DuplicatePageResolver::
            // roleSignature() (boucle for, un push par composant), verifie en lecture seule.
            // AVANT ce correctif, deux pages a nombre de composants identique mais a
            // MULTIPLICITES avec/sans differentes (ex. avec x2 + sans x1 contre avec x1 +
            // sans x2, 3 composants chacune) produisaient la MEME liste de roles [avec, sans]
            // (un seul jeton par type) au lieu de diverger comme le fait le resolveur de
            // reference -- inatteignable aujourd'hui (word_list_sans reste en NEVER_SITEMAP
            // permanent, App\Seo\Family::NEVER_SITEMAP) donc sans effet sur le lot D-041 deja
            // applique (voir D041_EXCLUDED_ROUTE_PATHS, figee au moment de son calcul), mais une
            // divergence reelle de la fonction elle-meme, corrigee ici par principe (seconde
            // source de verite que D-041 affirme avoir supprimee).
            $components += count($filters->withLetters);
            for ($i = 0, $n = count($filters->withLetters); $i < $n; $i++) {
                $roles[] = $ranks['avec'];
            }
        }

        if ($filters->withoutLetters !== []) {
            $components += count($filters->withoutLetters);
            for ($i = 0, $n = count($filters->withoutLetters); $i < $n; $i++) {
                $roles[] = $ranks['sans'];
            }
        }

        if ($filters->pattern !== null) {
            $components += 1;
            $roles[] = $ranks['motif'];
        }

        sort($roles);

        return ['components' => $components, 'roles' => $roles];
    }
}

if (!function_exists('seoDuplicatePriorityCompareRoles')) {
    /**
     * Comparaison LEXICOGRAPHIQUE de deux listes de rôles (déjà triées par rang croissant) -- le
     * premier rang qui diffère tranche ; à préfixe commun, la liste la plus COURTE (moins de
     * rôles distincts) gagne.
     *
     * @param list<int> $a
     * @param list<int> $b
     */
    function seoDuplicatePriorityCompareRoles(array $a, array $b): int
    {
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            if ($a[$i] !== $b[$i]) {
                return $a[$i] <=> $b[$i];
            }
        }

        return count($a) <=> count($b);
    }
}

if (!function_exists('seoDuplicatePriorityVariableComponentDepth')) {
    /**
     * CORRECTIF I-1 (5e audit consolidé, 2026-08-22) -- alignement avec App\Search\
     * DuplicatePageResolver::variableComponentDepth() (data-engine, vérifié en lecture seule,
     * jamais modifié ici) : profondeur du/des composant(s) de contrainte à CHAÎNE VARIABLE
     * (commençant/contenant/terminant) -- somme des longueurs de $prefix, $contains et $suffix (0
     * pour ceux absents).
     *
     * Bug corrigé, IDENTIQUE à celui déjà trouvé et corrigé côté App\Search\DuplicatePageResolver
     * (même audit, même constat) : avant ce correctif, resolveDuplicateWinner() ci-dessous tombait
     * directement sur une comparaison alphabétique de $family puis $route_path dès que deux pages
     * partageaient le même nombre de composants ET la même signature de rôles -- correct pour un
     * couple parent/enfant "commençant" (App\Search\PrefixExtensionLinksBuilder ajoute la lettre
     * d'extension EN QUEUE, ex. "wu" -> "wub" : le parent est alors toujours un préfixe-au-sens-
     * chaîne de l'enfant, donc alphabétiquement plus petit, la comparaison fonctionnait par
     * coïncidence), mais FAUX pour "terminant" : App\Search\SuffixExtensionLinksBuilder ajoute la
     * lettre d'extension EN TÊTE du suffixe (`'_' . $suffix` dans le LIKE, "zt" -> "azt", jamais
     * "zt" -> "zta"), donc le chemin de l'enfant n'est PLUS garanti alphabétiquement après celui du
     * parent -- ex. "/mots/terminant/azt" précède "/mots/terminant/zt" alphabétiquement bien que
     * "azt" soit l'ENFANT de "zt". Une comparaison alphabétique aurait donc désigné l'enfant comme
     * gagnant sur un tel groupe, à l'inverse du principe "la forme la plus générale/simple gagne"
     * (D-025/D-039), ET retiré le parent -- qui est pourtant, pour "terminant", le seul point
     * d'entrée : un suffixe de longueur N+1 n'est jamais lié que depuis son parent de longueur N
     * (App\Search\SuffixExtensionLinksBuilder), jamais l'inverse, donc retirer le parent
     * supprimerait le seul lien entrant réel du survivant.
     *
     * Inatteignable sur le balayage réel du 2026-08-21 (0 groupe SŒUR pur trouvé pour "terminant"
     * ni pour "commençant" parmi les 1 656 groupes -- tous CROISÉS entre familles différentes,
     * départagés par la règle des composants/rôles avant même d'atteindre cette comparaison,
     * revérifié fraîchement le 2026-08-22, voir reports/query-plans/d040-d041-correctif.md), donc
     * sans effet sur D041_EXCLUDED_ROUTE_PATHS ni sur le lot déjà appliqué -- mais une divergence
     * réelle du garde-fou lui-même, corrigée ici par principe, même raisonnement que le correctif
     * de multiplicité avec/sans ci-dessus.
     *
     * "avec"/"sans"/"position" n'ont pas besoin d'entrer dans cette somme : deux pages à égalité de
     * "components" ET de "roles" sur ces familles sont déjà de VRAIES sœurs (même nombre de
     * lettres), jamais un couple parent/enfant -- la profondeur y est donc toujours égale par
     * construction, cette règle ne les départage jamais, le départage alphabétique final s'en
     * charge directement (comportement inchangé par ce correctif).
     */
    function seoDuplicatePriorityVariableComponentDepth(string $routePath): int
    {
        if (!str_starts_with($routePath, '/mots')) {
            throw new \InvalidArgumentException("route_path hors de /mots/... : {$routePath}");
        }

        $filters = \App\Search\WordListFilters::fromPath(substr($routePath, strlen('/mots')));

        if ($filters === null) {
            throw new \InvalidArgumentException("route_path inexploitable par WordListFilters::fromPath() : {$routePath}");
        }

        return strlen($filters->prefix ?? '')
            + strlen($filters->contains ?? '')
            + strlen($filters->suffix ?? '');
    }
}

if (!function_exists('resolveDuplicateWinner')) {
    /**
     * D-041 -- fonction UNIQUE et testée (tests/Seo/ResolveDuplicateWinnerTest.php) qui tranche,
     * pour un groupe de pages au contenu de liste strictement identique (même empreinte de mots
     * exacte -- voir scripts/check_combinatorial_duplicates.php pour la détection), laquelle
     * reste candidate à l'indexation. Voir le docblock de fichier ci-dessus pour le détail complet
     * des trois règles appliquées, dans l'ordre.
     *
     * @param list<array{route_path: string, family: string}> $group au moins 2 membres, DÉJÀ
     *     connus comme doublons de contenu par l'appelant -- cette fonction ne recalcule jamais le
     *     contenu lui-même, seulement la priorité entre des membres déjà identifiés
     * @return string route_path du membre gagnant -- tous les autres membres du groupe doivent
     *     être exclus par l'appelant (jamais deux lignes index,follow pour un contenu identique,
     *     R3)
     */
    function resolveDuplicateWinner(array $group): string
    {
        if (count($group) < 2) {
            throw new \InvalidArgumentException('resolveDuplicateWinner() attend un groupe d\'au moins 2 membres');
        }

        $scored = [];

        foreach ($group as $member) {
            $profile = seoDuplicatePriorityProfile($member['route_path']);
            $scored[] = [
                'route_path' => $member['route_path'],
                'family' => $member['family'],
                'components' => $profile['components'],
                'roles' => $profile['roles'],
                // CORRECTIF I-1 (5e audit consolidé) : profondeur du composant variable
                // (commençant/contenant/terminant), voir seoDuplicatePriorityVariableComponentDepth()
                // -- départage un couple parent/enfant AVANT toute comparaison alphabétique de
                // family/route_path, exactement comme App\Search\DuplicatePageResolver::isBetter().
                'depth' => seoDuplicatePriorityVariableComponentDepth($member['route_path']),
            ];
        }

        usort($scored, static function (array $a, array $b): int {
            return $a['components'] <=> $b['components']
                ?: seoDuplicatePriorityCompareRoles($a['roles'], $b['roles'])
                ?: $a['depth'] <=> $b['depth']
                ?: $a['family'] <=> $b['family']
                ?: $a['route_path'] <=> $b['route_path'];
        });

        return $scored[0]['route_path'];
    }
}
