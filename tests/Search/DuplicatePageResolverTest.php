<?php

declare(strict_types=1);

use App\Search\DuplicatePageResolver;
use App\Search\WordListFilters;
use Tests\Support\Assert;

/**
 * App\Search\DuplicatePageResolver (D-041, garde-fou structurel demandé par le constat C-4 du 4e
 * audit consolidé, docs/DECISIONS.md D-040) : règle de priorité GÉNÉRIQUE entre pages `/mots/...`
 * au contenu strictement identique, appliquée aux 1 656 groupes trouvés par le balayage du
 * 2026-08-21 (scripts/check_combinatorial_duplicates.php).
 *
 * Ce fichier teste l'ALGORITHME lui-même (comptage de composants, signature de rôles, résolution
 * de groupe) sur des cas construits et sur les précédents déjà tranchés côté produit (D-025, D-038,
 * D-039) -- la vérification de la LISTE FIGÉE de chaque builder (que ce résolveur produit hors
 * ligne) vit dans le fichier de test de chaque builder concerné (ex.
 * StartEndWithLinksBuilderTest.php, AvecThreeLettersLinksBuilderTest.php...).
 */
return function (): void {
    // ============================================================================================
    // componentCount() -- barème exact (docblock de classe) : longueur/commençant/terminant/
    // contenant = 1 chacun, position = 2, chaque lettre "avec"/"sans" = 1.
    // ============================================================================================
    $length1 = WordListFilters::fromPath('7-lettres');
    Assert::same(1, DuplicatePageResolver::componentCount($length1), 'longueur seule = 1 composant');

    $commencant1 = WordListFilters::fromPath('commencant/khr');
    Assert::same(1, DuplicatePageResolver::componentCount($commencant1), 'commencant seul = 1 composant, quelle que soit la longueur du prefixe');

    $terminant1 = WordListFilters::fromPath('terminant/xxes');
    Assert::same(1, DuplicatePageResolver::componentCount($terminant1), 'terminant seul = 1 composant, quelle que soit la longueur du suffixe');

    $combinedNoLength = WordListFilters::fromPath('commencant/x/terminant/m');
    Assert::same(2, DuplicatePageResolver::componentCount($combinedNoLength), 'commencant+terminant sans longueur = 2 composants');

    $combinedWithLength = WordListFilters::fromPath('5-lettres/commencant/x/terminant/m');
    Assert::same(3, DuplicatePageResolver::componentCount($combinedWithLength), 'commencant+terminant avec longueur = 3 composants');

    $avecSingle = WordListFilters::fromPath('2-lettres/avec/w');
    Assert::same(2, DuplicatePageResolver::componentCount($avecSingle), 'longueur + 1 lettre avec = 2 composants');

    $avecTwo = WordListFilters::fromPath('10-lettres/avec/j/w');
    Assert::same(3, DuplicatePageResolver::componentCount($avecTwo), 'longueur + 2 lettres avec = 3 composants');

    $avecThree = WordListFilters::fromPath('5-lettres/avec/b/q/r');
    Assert::same(4, DuplicatePageResolver::componentCount($avecThree), 'longueur + 3 lettres avec = 4 composants');

    $position = WordListFilters::fromPath('9-lettres/position/3/a');
    Assert::same(3, DuplicatePageResolver::componentCount($position), 'longueur + position (2 a elle seule) = 3 composants');

    $combinedWithLetter = WordListFilters::fromPath('commencant/f/terminant/q/avec/a');
    Assert::same(3, DuplicatePageResolver::componentCount($combinedWithLetter), 'commencant+terminant+avec (1 lettre) = 3 composants');

    $commencantWithLetter = WordListFilters::fromPath('commencant/w/avec/j');
    Assert::same(2, DuplicatePageResolver::componentCount($commencantWithLetter), 'commencant+avec (1 lettre) = 2 composants');

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 1 : le plus petit nombre de composants gagne, sans
    // ambiguïté possible.
    // ============================================================================================
    Assert::same(
        '/mots/commencant/x/terminant/m',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/commencant/x/terminant/m', '/mots/5-lettres/avec/n/q/s']),
        '2 composants doit battre 4 composants'
    );

    Assert::same(
        '/mots/terminant/faq',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/commencant/f/terminant/q', '/mots/terminant/faq']),
        '1 composant doit battre 2 composants'
    );

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 1 : à égalité de composants entre familles différentes,
    // l'ordre canonique des mots-clés départage (WordListFilters, docblock de classe : longueur ->
    // commençant -> contenant -> terminant -> position -> avec -> sans -> motif).
    // ============================================================================================

    // Cas réel principal du balayage du 2026-08-21 (408 groupes) : commençant vs terminant, 1
    // composant chacun -- commençant gagne (rôle 1 < rôle 3 dans l'ordre canonique).
    Assert::same(
        '/mots/commencant/webj',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/commencant/webj', '/mots/terminant/wu']),
        'a egalite de composants, commencant precede terminant dans l\'ordre canonique'
    );
    // Ordre de saisie inversé : même résultat, la fonction ne doit jamais dépendre de l'ordre du
    // tableau passé en entrée.
    Assert::same(
        '/mots/commencant/webj',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/terminant/wu', '/mots/commencant/webj']),
        'resultat independant de l\'ordre du tableau passe en entree'
    );

    // Précédent déjà tranché côté produit, D-039 (longueur vs "avec", deux familles à 3 composants
    // chacune) : "5-lettres/commencant/x/terminant/m" (signature [longueur, commençant, terminant])
    // doit battre "commencant/x/terminant/m/avec/a" (signature [commençant, terminant, avec]) --
    // "longueur" (0) précède "commençant" (1) dans l'ordre canonique. Cette règle n'est pas câblée
    // à la main : c'est une conséquence directe de la règle 2 générale, vérifiée ici comme un cas
    // concret plutôt que supposée.
    Assert::same(
        '/mots/5-lettres/commencant/x/terminant/m',
        DuplicatePageResolver::resolveDuplicateWinner([
            '/mots/5-lettres/commencant/x/terminant/m',
            '/mots/commencant/x/terminant/m/avec/a',
        ]),
        'D-039 : la variante longueur gagne sur la variante avec, meme regle generale que D-025'
    );

    // Position (signature [longueur, position, position]) bat "avec" a 2 lettres (signature
    // [longueur, avec, avec]) -- "position" (4) precede "avec" (5) dans l'ordre canonique.
    Assert::same(
        '/mots/5-lettres/position/4/q',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/5-lettres/position/4/q', '/mots/5-lettres/avec/b/q/r']),
        'position bat avec a nombre de composants different (3 vs 4) -- cas simple, pas de tie-break necessaire ici'
    );

    // Cas ou position et "avec" ont le MEME nombre de composants (3) : position doit gagner
    // (role 4 < role 5).
    Assert::same(
        '/mots/9-lettres/position/3/a',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/9-lettres/position/3/a', '/mots/9-lettres/avec/a/b']),
        'a 3 composants egaux, position (role 4) precede avec (role 5) dans l\'ordre canonique'
    );

    // ============================================================================================
    // variableComponentDepth() -- longueur du composant à chaîne variable (commençant/contenant/
    // terminant), 0 si absent. Fonction pure, vérifiée directement avant d'exercer
    // resolveDuplicateWinner() dessus.
    // ============================================================================================
    Assert::same(2, DuplicatePageResolver::variableComponentDepth(WordListFilters::fromPath('terminant/zt')), 'profondeur = longueur du suffixe (2)');
    Assert::same(3, DuplicatePageResolver::variableComponentDepth(WordListFilters::fromPath('terminant/azt')), 'profondeur = longueur du suffixe etendu (3)');
    Assert::same(0, DuplicatePageResolver::variableComponentDepth(WordListFilters::fromPath('7-lettres')), 'aucun composant a chaine variable -> profondeur 0');
    Assert::same(5, DuplicatePageResolver::variableComponentDepth(WordListFilters::fromPath('commencant/wu/terminant/abc')), 'commencant+terminant combines : somme des deux longueurs (2 + 3)');

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 2 (constat I-1, 5e audit consolidé) : à égalité de
    // composants ET de signature de rôles au sein de la MÊME famille à chaîne variable
    // (commençant/contenant/terminant), la forme dont le composant variable est le plus COURT
    // gagne -- jamais une comparaison alphabétique naïve du chemin complet.
    //
    // Cas couvert par le bug corrigé : App\Search\SuffixExtensionLinksBuilder ajoute la lettre
    // d'extension EN TÊTE du suffixe ('_' . $suffix dans le LIKE), donc "/mots/terminant/azt"
    // (enfant, suffixe "azt") précède ALPHABÉTIQUEMENT "/mots/terminant/zt" (parent, suffixe "zt")
    // -- une comparaison naïve du route_path complet désignerait à tort l'enfant gagnant. Le
    // parent doit rester gagnant : il est plus général (D-025/D-039) et c'est le seul mot-clé qui
    // reçoit un lien entrant depuis le maillage (SuffixExtensionLinksBuilder ne lie que du parent
    // vers l'enfant, jamais l'inverse) -- retirer le parent supprimerait le seul lien entrant du
    // survivant.
    // ============================================================================================
    Assert::same(
        '/mots/terminant/zt',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/terminant/zt', '/mots/terminant/azt']),
        'I-1 : le parent "terminant/zt" (2 lettres) doit battre son enfant "terminant/azt" (3 lettres), '
        . 'bien que "azt" precede "zt" alphabetiquement'
    );
    // Ordre de saisie inversé : même résultat, la fonction ne doit jamais dépendre de l'ordre du
    // tableau passé en entrée (même garde-fou que le cas commençant/terminant croisé plus haut).
    Assert::same(
        '/mots/terminant/zt',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/terminant/azt', '/mots/terminant/zt']),
        'I-1 : resultat independant de l\'ordre du tableau passe en entree'
    );

    // Cas symétrique côté "commençant" (App\Search\PrefixExtensionLinksBuilder ajoute la lettre
    // d'extension EN QUEUE du préfixe) : le parent gagnait déjà avant ce correctif -- reste vrai
    // après, désormais via la profondeur plutôt que par coïncidence alphabétique.
    Assert::same(
        '/mots/commencant/wu',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/commencant/wu', '/mots/commencant/wub']),
        'le parent "commencant/wu" (2 lettres) doit battre son enfant "commencant/wub" (3 lettres)'
    );

    // Profondeur également égale (vraies pages sœurs, même longueur de suffixe, lettres
    // différentes) : la règle 3 (alphabétique) reste le départage final, inchangée par ce
    // correctif.
    Assert::same(
        '/mots/terminant/at',
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/terminant/zt', '/mots/terminant/at']),
        'a profondeur egale (2 lettres chacun), "at" precede "zt" alphabetiquement -- vraies soeurs'
    );

    // ============================================================================================
    // resolveDuplicateWinner() -- règle 3 : à égalité de composants ET de signature de rôles
    // (même famille, cas "sœurs"), la forme alphabétiquement la plus petite gagne (D-038).
    // canonicalPath() sérialise toujours les lettres "avec" en ordre alphabétique croissant
    // (ksort(), D-022), donc comparer route_path complet revient au même résultat que "la lettre
    // la plus petite gagne".
    // ============================================================================================
    Assert::same(
        '/mots/commencant/x/terminant/m/avec/a',
        DuplicatePageResolver::resolveDuplicateWinner([
            '/mots/commencant/x/terminant/m/avec/l',
            '/mots/commencant/x/terminant/m/avec/a',
        ]),
        'D-038 : entre deux pages soeurs de la meme famille, la lettre alphabetiquement la plus petite (A < L) gagne'
    );

    Assert::same(
        '/mots/10-lettres/avec/a/w/x',
        DuplicatePageResolver::resolveDuplicateWinner([
            '/mots/10-lettres/avec/e/w/x',
            '/mots/10-lettres/avec/a/w/x',
            '/mots/10-lettres/avec/n/w/x',
        ]),
        'meme regle sur un groupe de 3 pages soeurs (palier 3) : la plus petite (A) gagne parmi A/E/N'
    );

    // ============================================================================================
    // Groupe a N pages (> 2) melangeant plusieurs familles -- verifie que le gagnant est bien
    // l'unique minimum global, pas seulement le meilleur d'une comparaison par paires.
    // ============================================================================================
    Assert::same(
        '/mots/commencant/webj',
        DuplicatePageResolver::resolveDuplicateWinner([
            '/mots/10-lettres/avec/j/w',
            '/mots/commencant/w/avec/j',
            '/mots/commencant/w/terminant/l',
            '/mots/commencant/webj',
        ]),
        'commencant/webj (1 composant) doit gagner sur les trois autres (2 ou 3 composants)'
    );

    // ============================================================================================
    // Garde-fous : groupe trop petit, route_path invalide.
    // ============================================================================================
    $threw = false;
    try {
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/commencant/a']);
    } catch (\InvalidArgumentException) {
        $threw = true;
    }
    Assert::true($threw, 'un groupe de moins de 2 pages doit lever une exception, jamais un resultat silencieux');

    $threw = false;
    try {
        DuplicatePageResolver::resolveDuplicateWinner(['/mots/commencant/a', '/jouer/abc']);
    } catch (\InvalidArgumentException) {
        $threw = true;
    }
    Assert::true($threw, 'une route hors /mots doit lever une exception, jamais un resultat silencieux');

    // ============================================================================================
    // Cohérence : componentCount()/roleSignature() sont des fonctions PURES de $filters -- deux
    // appels sur le même route_path doivent toujours produire le même résultat (déterminisme).
    // ============================================================================================
    $filtersA = WordListFilters::fromPath('9-lettres/avec/a/b/c');
    $filtersB = WordListFilters::fromPath('9-lettres/avec/a/b/c');
    Assert::same(DuplicatePageResolver::componentCount($filtersA), DuplicatePageResolver::componentCount($filtersB), 'componentCount() deterministe');
    Assert::same(DuplicatePageResolver::roleSignature($filtersA), DuplicatePageResolver::roleSignature($filtersB), 'roleSignature() deterministe');

    // compareRoleSignatures() : anti-symetrie et reflexivite de base.
    Assert::same(0, DuplicatePageResolver::compareRoleSignatures([0, 1, 3], [0, 1, 3]), 'signatures identiques -> 0');
    Assert::true(DuplicatePageResolver::compareRoleSignatures([0, 1, 3], [0, 4, 4]) < 0, '[0,1,3] precede [0,4,4] (1 < 4 au 2e jeton)');
    Assert::true(DuplicatePageResolver::compareRoleSignatures([0, 4, 4], [0, 1, 3]) > 0, 'comparaison inverse, signe oppose');
};
