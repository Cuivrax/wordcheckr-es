<?php

declare(strict_types=1);

use App\Search\DuplicatePageResolver;
use Tests\Support\Assert;

/**
 * scripts/lib/seo_duplicate_priority.php (D-041, seo-registry) -- règle de priorité GLOBALE entre
 * pages `/mots/...` au contenu strictement identique, deuxième implémentation indépendante de la
 * même règle que App\Search\DuplicatePageResolver (data-engine, tests/Search/
 * DuplicatePageResolverTest.php). Ce fichier était cité par D-041 et par le docblock de
 * scripts/lib/seo_duplicate_priority.php comme preuve de couverture de test, mais n'existait pas
 * avant ce correctif (5e audit consolidé, seo-technical-auditor, point I-2, 2026-08-22).
 *
 * Ce fichier requiert directement scripts/lib/seo_duplicate_priority.php (fonctions globales, pas
 * une classe App\... -- pas d'autoload possible) et appelle ses fonctions EN PROCESSUS, jamais via
 * un sous-processus : aucune des fonctions testées ici n'accède à une base de données ni n'a
 * d'effet de bord au niveau fichier (voir le docblock de seo_duplicate_priority.php).
 *
 * Couvre aussi le correctif I-1 (même audit, trouvé indépendamment côté App\Search\
 * DuplicatePageResolver puis reproduit ici par principe d'alignement) :
 * seoDuplicatePriorityVariableComponentDepth() -- à égalité de composants ET de rôles au sein
 * d'une MÊME famille à chaîne variable (commençant/contenant/terminant), une comparaison
 * alphabétique directe du route_path est fausse pour "terminant" (App\Search\
 * SuffixExtensionLinksBuilder étend EN TÊTE du suffixe, "zt" -> "azt" précède "zt"
 * alphabétiquement bien qu'il en soit l'enfant) -- la profondeur du composant variable (longueur
 * du préfixe/contenant/suffixe) doit départager AVANT toute comparaison alphabétique.
 *
 * Point central I-2 de ce fichier : seoDuplicatePriorityProfile() émettait, avant ce correctif,
 * UN SEUL jeton de rôle par TYPE de rôle présent (ex. un seul jeton "avec" quel que soit le nombre
 * de lettres, un seul jeton "position" au lieu de deux) -- alors qu'App\Search\
 * DuplicatePageResolver::roleSignature() émet TOUJOURS un jeton PAR COMPOSANT. Les deux
 * implémentations pouvaient donc diverger dès que deux pages partageaient le même nombre total de
 * composants et le même ENSEMBLE de rôles mais des MULTIPLICITÉS différentes entre rôles
 * répétables ("avec"/"sans"), ex. avec×2+sans×1 contre avec×1+sans×2 -- inatteignable aujourd'hui
 * en production (App\Seo\Family::WORD_LIST_SANS reste dans NEVER_SITEMAP en permanence, aucune
 * famille combinatoire réelle du registre ne mélange "avec" et "sans"), mais une divergence réelle
 * de la fonction elle-même. La deuxième moitié de ce fichier construit ce cas explicitement et
 * prouve que la fonction corrigée se comporte correctement s'il devenait un jour atteignable.
 */
return function (): void {
    $lib = __DIR__ . '/../../scripts/lib/seo_duplicate_priority.php';
    Assert::true(is_file($lib), 'scripts/lib/seo_duplicate_priority.php introuvable : ' . $lib);
    require_once $lib;

    // ============================================================================================
    // seoDuplicatePriorityProfile() -- barème des composants (docblock de fichier) : longueur/
    // commençant/terminant/contenant/motif = 1 chacun, position = 2, chaque lettre "avec"/"sans"
    // distincte = 1.
    // ============================================================================================
    Assert::same(['components' => 1, 'roles' => [0]], seoDuplicatePriorityProfile('/mots/7-lettres'), 'longueur seule = 1 composant, role [0]');
    Assert::same(['components' => 1, 'roles' => [1]], seoDuplicatePriorityProfile('/mots/commencant/khr'), 'commencant seul = 1 composant, quelle que soit la longueur du prefixe');
    Assert::same(['components' => 1, 'roles' => [3]], seoDuplicatePriorityProfile('/mots/terminant/xxes'), 'terminant seul = 1 composant');
    Assert::same(['components' => 2, 'roles' => [1, 3]], seoDuplicatePriorityProfile('/mots/commencant/x/terminant/m'), 'commencant+terminant sans longueur = 2 composants');
    Assert::same(['components' => 3, 'roles' => [0, 1, 3]], seoDuplicatePriorityProfile('/mots/5-lettres/commencant/x/terminant/m'), 'commencant+terminant avec longueur = 3 composants');

    // ============================================================================================
    // Correctif I-2 -- "position" doit émettre DEUX jetons de rôle (index + lettre), pas un seul.
    // ============================================================================================
    $positionProfile = seoDuplicatePriorityProfile('/mots/9-lettres/position/3/a');
    Assert::same(3, $positionProfile['components'], 'longueur + position (2 a elle seule) = 3 composants');
    Assert::same([0, 4, 4], $positionProfile['roles'], 'I-2 : position doit emettre DEUX jetons de role (4 pousse deux fois), pas un seul');

    // ============================================================================================
    // Correctif I-2 -- "avec"/"sans" doivent émettre UN jeton PAR LETTRE distincte, pas un seul
    // jeton par type de rôle. C'est la régression concrète trouvée par le 5e audit consolidé : la
    // longueur du tableau "roles" doit toujours égaler "components" (même invariant que
    // App\Search\DuplicatePageResolver::roleSignature()).
    // ============================================================================================
    $avecTwo = seoDuplicatePriorityProfile('/mots/10-lettres/avec/j/w');
    Assert::same(3, $avecTwo['components'], 'longueur + 2 lettres avec = 3 composants');
    Assert::same([0, 5, 5], $avecTwo['roles'], 'I-2 : avec doit emettre un jeton par lettre (2 lettres = 2 jetons "5"), pas un seul');
    Assert::same(count($avecTwo['roles']), $avecTwo['components'], 'I-2 : la longueur de "roles" doit toujours egaler "components"');

    $avecThree = seoDuplicatePriorityProfile('/mots/5-lettres/avec/b/q/r');
    Assert::same(4, $avecThree['components'], 'longueur + 3 lettres avec = 4 composants');
    Assert::same([0, 5, 5, 5], $avecThree['roles'], 'I-2 : 3 lettres avec = 3 jetons "5"');
    Assert::same(count($avecThree['roles']), $avecThree['components'], 'I-2 : invariant roles/components (3 lettres avec)');

    $sansTwo = seoDuplicatePriorityProfile('/mots/avec/a/sans/b/c');
    Assert::same(3, $sansTwo['components'], 'avec x1 + sans x2 = 3 composants');
    Assert::same([5, 6, 6], $sansTwo['roles'], 'I-2 : sans doit emettre un jeton par lettre (2 lettres = 2 jetons "6")');
    Assert::same(count($sansTwo['roles']), $sansTwo['components'], 'I-2 : invariant roles/components (avec x1 + sans x2)');

    // ============================================================================================
    // Cas central I-2 -- avec×2+sans×1 (roles [5,5,6]) contre avec×1+sans×2 (roles [5,6,6]) : même
    // nombre total de composants (3), même ENSEMBLE de rôles distincts {avec, sans}, mais des
    // MULTIPLICITÉS différentes. AVANT ce correctif, seoDuplicatePriorityProfile() produisait
    // [5, 6] pour les DEUX pages (un seul jeton par type de rôle) -- indiscernables au niveau
    // "roles", la fonction retombait alors sur le départage alphabétique de route_path, jamais sur
    // la vraie règle de priorité. Après correctif, les deux profils diffèrent bien dès le 2e
    // jeton, exactement comme App\Search\DuplicatePageResolver::roleSignature() le ferait pour le
    // même groupe.
    // ============================================================================================
    $avecTwoSansOne = '/mots/avec/a/b/sans/c';
    $avecOneSansTwo = '/mots/avec/a/sans/b/c';

    $profileA = seoDuplicatePriorityProfile($avecTwoSansOne);
    $profileB = seoDuplicatePriorityProfile($avecOneSansTwo);

    Assert::same(3, $profileA['components'], 'avec x2 + sans x1 = 3 composants');
    Assert::same(3, $profileB['components'], 'avec x1 + sans x2 = 3 composants');
    Assert::same([5, 5, 6], $profileA['roles'], 'avec x2 + sans x1 : roles [avec, avec, sans]');
    Assert::same([5, 6, 6], $profileB['roles'], 'avec x1 + sans x2 : roles [avec, sans, sans]');
    Assert::true($profileA['roles'] !== $profileB['roles'], 'I-2 : les deux profils doivent maintenant DIVERGER (avant correctif, [5,6] === [5,6])');

    Assert::same(
        $avecTwoSansOne,
        resolveDuplicateWinner([
            ['route_path' => $avecTwoSansOne, 'family' => 'word_list_avec_sans_hypothetique'],
            ['route_path' => $avecOneSansTwo, 'family' => 'word_list_avec_sans_hypothetique'],
        ]),
        'avec x2 + sans x1 doit gagner (role "avec" = 5 precede "sans" = 6, le 2e jeton differe : 5 < 6)'
    );

    // Ordre de saisie inversé : même résultat, indépendant de l'ordre du tableau passé en entrée.
    Assert::same(
        $avecTwoSansOne,
        resolveDuplicateWinner([
            ['route_path' => $avecOneSansTwo, 'family' => 'word_list_avec_sans_hypothetique'],
            ['route_path' => $avecTwoSansOne, 'family' => 'word_list_avec_sans_hypothetique'],
        ]),
        'resultat independant de l\'ordre du tableau passe en entree'
    );

    // Preuve croisée directe : App\Search\DuplicatePageResolver (data-engine, lu en lecture seule,
    // jamais modifié ici) tranche EXACTEMENT le même groupe de la même façon -- les deux "sources
    // de vérité" que D-041 affirme avoir unifiées sont désormais réellement d'accord sur ce cas,
    // pas seulement sur les cas déjà exercés en production.
    Assert::same(
        DuplicatePageResolver::resolveDuplicateWinner([$avecTwoSansOne, $avecOneSansTwo]),
        resolveDuplicateWinner([
            ['route_path' => $avecTwoSansOne, 'family' => 'word_list_avec_sans_hypothetique'],
            ['route_path' => $avecOneSansTwo, 'family' => 'word_list_avec_sans_hypothetique'],
        ]),
        'I-2 : App\Search\DuplicatePageResolver et resolveDuplicateWinner() doivent converger sur le cas de multiplicite avec/sans'
    );

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 1 : le plus petit nombre de composants gagne.
    // ============================================================================================
    Assert::same(
        '/mots/commencant/x/terminant/m',
        resolveDuplicateWinner([
            ['route_path' => '/mots/commencant/x/terminant/m', 'family' => 'word_list_combined'],
            ['route_path' => '/mots/5-lettres/avec/n/q/s', 'family' => 'word_list_avec_three_letters'],
        ]),
        '2 composants doit battre 4 composants'
    );

    Assert::same(
        '/mots/terminant/faq',
        resolveDuplicateWinner([
            ['route_path' => '/mots/commencant/f/terminant/q', 'family' => 'word_list_combined'],
            ['route_path' => '/mots/terminant/faq', 'family' => 'word_list_terminant'],
        ]),
        '1 composant doit battre 2 composants'
    );

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 2 : à égalité de composants entre familles différentes,
    // l'ordre canonique des mots-clés départage (longueur -> commençant -> contenant -> terminant
    // -> position -> avec -> sans -> motif). Cas réel principal du balayage D-041 (408 groupes) :
    // commençant vs terminant.
    // ============================================================================================
    Assert::same(
        '/mots/commencant/webj',
        resolveDuplicateWinner([
            ['route_path' => '/mots/commencant/webj', 'family' => 'word_list_commencant'],
            ['route_path' => '/mots/terminant/wu', 'family' => 'word_list_terminant'],
        ]),
        'a egalite de composants, commencant precede terminant dans l\'ordre canonique'
    );

    // D-039 (longueur vs "avec", deux familles à 3 composants chacune) : la variante longueur
    // gagne -- reproduit comme CONSEQUENCE de la règle générale, pas un cas câblé en dur.
    Assert::same(
        '/mots/5-lettres/commencant/x/terminant/m',
        resolveDuplicateWinner([
            ['route_path' => '/mots/5-lettres/commencant/x/terminant/m', 'family' => 'word_list_combined'],
            ['route_path' => '/mots/commencant/x/terminant/m/avec/a', 'family' => 'word_list_combined_with_letter'],
        ]),
        'D-039 : la variante longueur gagne sur la variante avec'
    );

    // Position (signature [longueur, position, position]) bat "avec" à 3 composants (signature
    // [longueur, avec, avec]) -- "position" (4) précède "avec" (5) dans l'ordre canonique.
    Assert::same(
        '/mots/9-lettres/position/3/a',
        resolveDuplicateWinner([
            ['route_path' => '/mots/9-lettres/position/3/a', 'family' => 'word_list_position'],
            ['route_path' => '/mots/9-lettres/avec/a/b', 'family' => 'word_list_avec_two_letters'],
        ]),
        'a 3 composants egaux, position (role 4) precede avec (role 5) dans l\'ordre canonique'
    );

    // ============================================================================================
    // Correctif I-1 (5e audit consolidé, 2026-08-22) -- seoDuplicatePriorityVariableComponentDepth()
    // : à égalité de composants ET de rôles au sein de la MÊME famille à chaîne variable
    // (commençant/contenant/terminant), la forme dont le composant variable est le plus COURT
    // gagne -- jamais une comparaison alphabétique naïve du chemin complet en premier.
    //
    // Même bug que celui trouvé et corrigé indépendamment côté App\Search\DuplicatePageResolver
    // (tests/Search/DuplicatePageResolverTest.php) : App\Search\SuffixExtensionLinksBuilder ajoute
    // la lettre d'extension EN TÊTE du suffixe, donc "/mots/terminant/azt" (enfant, 3 lettres)
    // précède ALPHABÉTIQUEMENT "/mots/terminant/zt" (parent, 2 lettres) -- une comparaison naïve du
    // route_path désignerait à tort l'enfant gagnant, retirant le parent qui est pourtant le SEUL
    // point d'entrée du maillage réel (SuffixExtensionLinksBuilder ne lie que du parent vers
    // l'enfant, jamais l'inverse).
    // ============================================================================================
    Assert::same(
        '/mots/terminant/zt',
        resolveDuplicateWinner([
            ['route_path' => '/mots/terminant/zt', 'family' => 'word_list_terminant'],
            ['route_path' => '/mots/terminant/azt', 'family' => 'word_list_terminant'],
        ]),
        'I-1 : le parent "terminant/zt" (2 lettres) doit battre son enfant "terminant/azt" (3 lettres), '
        . 'bien que "azt" precede "zt" alphabetiquement'
    );
    Assert::same(
        '/mots/terminant/zt',
        resolveDuplicateWinner([
            ['route_path' => '/mots/terminant/azt', 'family' => 'word_list_terminant'],
            ['route_path' => '/mots/terminant/zt', 'family' => 'word_list_terminant'],
        ]),
        'I-1 : resultat independant de l\'ordre du tableau passe en entree'
    );

    // Cas symétrique côté "commençant" (App\Search\PrefixExtensionLinksBuilder ajoute la lettre
    // d'extension EN QUEUE du préfixe) : le parent gagnait déjà avant ce correctif -- reste vrai
    // après, désormais via la profondeur plutôt que par coïncidence alphabétique.
    Assert::same(
        '/mots/commencant/wu',
        resolveDuplicateWinner([
            ['route_path' => '/mots/commencant/wu', 'family' => 'word_list_commencant'],
            ['route_path' => '/mots/commencant/wub', 'family' => 'word_list_commencant'],
        ]),
        'le parent "commencant/wu" (2 lettres) doit battre son enfant "commencant/wub" (3 lettres)'
    );

    // Profondeur également égale (vraies pages sœurs, même longueur de suffixe, lettres
    // différentes) : le départage alphabétique final reste inchangé par ce correctif.
    Assert::same(
        '/mots/terminant/at',
        resolveDuplicateWinner([
            ['route_path' => '/mots/terminant/zt', 'family' => 'word_list_terminant'],
            ['route_path' => '/mots/terminant/at', 'family' => 'word_list_terminant'],
        ]),
        'a profondeur egale (2 lettres chacun), "at" precede "zt" alphabetiquement -- vraies soeurs'
    );

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 3 : à égalité de composants ET de rôles (même famille, cas
    // "sœurs"), la forme alphabétiquement la plus petite gagne (D-038).
    // ============================================================================================
    Assert::same(
        '/mots/commencant/x/terminant/m/avec/a',
        resolveDuplicateWinner([
            ['route_path' => '/mots/commencant/x/terminant/m/avec/l', 'family' => 'word_list_combined_with_letter'],
            ['route_path' => '/mots/commencant/x/terminant/m/avec/a', 'family' => 'word_list_combined_with_letter'],
        ]),
        'D-038 : entre deux pages soeurs, la lettre alphabetiquement la plus petite (A < L) gagne'
    );

    // ============================================================================================
    // Groupe à N pages (> 2) mélangeant plusieurs familles -- vérifie que le gagnant est bien
    // l'unique minimum global.
    // ============================================================================================
    Assert::same(
        '/mots/commencant/webj',
        resolveDuplicateWinner([
            ['route_path' => '/mots/10-lettres/avec/j/w', 'family' => 'word_list_avec_two_letters'],
            ['route_path' => '/mots/commencant/w/avec/j', 'family' => 'word_list_commencant_with_letter'],
            ['route_path' => '/mots/commencant/w/terminant/l', 'family' => 'word_list_combined'],
            ['route_path' => '/mots/commencant/webj', 'family' => 'word_list_commencant'],
        ]),
        'commencant/webj (1 composant) doit gagner sur les trois autres (2 ou 3 composants)'
    );

    // ============================================================================================
    // Cohérence croisée -- pour tous les groupes déjà tranchés côté produit (D-025/D-037 à D-041),
    // App\Search\DuplicatePageResolver (data-engine) et resolveDuplicateWinner() (seo-registry)
    // doivent produire EXACTEMENT le même gagnant : c'est précisément ce que D-041 affirme, vérifié
    // ici directement plutôt que supposé.
    // ============================================================================================
    $crossCheckGroups = [
        ['/mots/commencant/x/terminant/m', '/mots/5-lettres/avec/n/q/s'],
        ['/mots/commencant/f/terminant/q', '/mots/terminant/faq'],
        ['/mots/commencant/webj', '/mots/terminant/wu'],
        ['/mots/5-lettres/commencant/x/terminant/m', '/mots/commencant/x/terminant/m/avec/a'],
        ['/mots/9-lettres/position/3/a', '/mots/9-lettres/avec/a/b'],
        ['/mots/commencant/x/terminant/m/avec/l', '/mots/commencant/x/terminant/m/avec/a'],
        // Correctif I-1 (profondeur du composant variable) : couvert aussi dans la comparaison
        // croisée, pas seulement en isolation ci-dessus.
        ['/mots/terminant/zt', '/mots/terminant/azt'],
        ['/mots/commencant/wu', '/mots/commencant/wub'],
        ['/mots/terminant/zt', '/mots/terminant/at'],
    ];

    foreach ($crossCheckGroups as $group) {
        $viaResolver = DuplicatePageResolver::resolveDuplicateWinner($group);
        $viaSeoLib = resolveDuplicateWinner(array_map(
            static fn (string $path): array => ['route_path' => $path, 'family' => 'test'],
            $group,
        ));

        Assert::same(
            $viaResolver,
            $viaSeoLib,
            'App\Search\DuplicatePageResolver et resolveDuplicateWinner() divergent sur le groupe : ' . implode(', ', $group)
        );
    }

    // ============================================================================================
    // Garde-fous : groupe trop petit, route_path invalide.
    // ============================================================================================
    $threw = false;
    try {
        resolveDuplicateWinner([['route_path' => '/mots/commencant/a', 'family' => 'word_list_commencant']]);
    } catch (\InvalidArgumentException) {
        $threw = true;
    }
    Assert::true($threw, 'un groupe de moins de 2 pages doit lever une exception, jamais un resultat silencieux');

    $threw = false;
    try {
        seoDuplicatePriorityProfile('/jouer/abc');
    } catch (\InvalidArgumentException) {
        $threw = true;
    }
    Assert::true($threw, 'une route hors /mots doit lever une exception, jamais un resultat silencieux');

    // ============================================================================================
    // seoDuplicatePriorityCompareRoles() -- anti-symétrie et réflexivité de base.
    // ============================================================================================
    Assert::same(0, seoDuplicatePriorityCompareRoles([0, 1, 3], [0, 1, 3]), 'signatures identiques -> 0');
    Assert::true(seoDuplicatePriorityCompareRoles([0, 1, 3], [0, 4, 4]) < 0, '[0,1,3] precede [0,4,4] (1 < 4 au 2e jeton)');
    Assert::true(seoDuplicatePriorityCompareRoles([0, 4, 4], [0, 1, 3]) > 0, 'comparaison inverse, signe oppose');
};
