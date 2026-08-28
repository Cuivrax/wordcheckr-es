<?php

declare(strict_types=1);

use App\Search\WordListFilters;
use Tests\Support\Assert;

/**
 * App\Search\WordListFilters : analyse et canonicalisation des contraintes de /palabras/...
 * (URL localisee, ES-004 -- equivalent /mots/... du site francais), independamment de toute
 * base de donnees -- meme esprit que RackTest.php pour App\Search\Rack.
 *
 * "empiezan-por"/"terminan-en" (ES-004, reports/es-serp-terminology-research.md §2.4/2.5)
 * remplacent "commencant"/"terminant". "contenant", "avec", "sans", "motif", "position",
 * "statut", "tri" restent FRANCAIS -- hors perimetre de cette passe (aucune recherche
 * terminologique dediee, voir docs/DECISIONS.md ES-004 -- ne pas deviner une traduction
 * non recherchee).
 */
return function (): void {
    // --- Longueur seule. ---
    $length = WordListFilters::fromPath('7-letras');
    Assert::notNull($length);
    Assert::same(7, $length->length);
    Assert::true($length->isEmpty() === false);
    Assert::same('/palabras/7-letras', $length->canonicalUrl());

    // --- Prefixe seul, insensible a la casse et aux accents (Normalizer::normalize, D-009). ---
    $prefix = WordListFilters::fromPath('empiezan-por/CH');
    Assert::notNull($prefix);
    Assert::same('CH', $prefix->prefix);
    Assert::same('/palabras/empiezan-por/ch', $prefix->canonicalUrl());

    $accentedPrefix = WordListFilters::fromPath('empiezan-por/éÉ');
    Assert::notNull($accentedPrefix);
    Assert::same('EE', $accentedPrefix->prefix, 'accents retires par Normalizer::normalize()');

    // --- Longueur + prefixe combines, dans l'ordre canonique recu (deja correct ici). ---
    $combo = WordListFilters::fromPath('7-letras/empiezan-por/ch');
    Assert::notNull($combo);
    Assert::same(7, $combo->length);
    Assert::same('CH', $combo->prefix);
    Assert::same('/palabras/7-letras/empiezan-por/ch', $combo->canonicalUrl());

    // --- Terminan-en. ---
    $suffix = WordListFilters::fromPath('terminan-en/tion');
    Assert::notNull($suffix);
    Assert::same('TION', $suffix->suffix);

    // --- Prefixe/terminan-en multi-lettres (tache de dimensionnement "commencant/terminant
    // --- multi-lettres" du site francais, 2026-08-18) : readSingleLetterRun() accepte deja 1
    // --- a 15 lettres -- verifie explicitement ici la longueur 4 (borne haute retenue par
    // --- cette tache, 2 a 4 lettres), jamais mesuree avant ce jour. ---
    $prefix4 = WordListFilters::fromPath('empiezan-por/anti');
    Assert::notNull($prefix4);
    Assert::same('ANTI', $prefix4->prefix);
    Assert::same('/palabras/empiezan-por/anti', $prefix4->canonicalUrl());

    $suffix4 = WordListFilters::fromPath('terminan-en/zing');
    Assert::notNull($suffix4);
    Assert::same('ZING', $suffix4->suffix);
    Assert::same('/palabras/terminan-en/zing', $suffix4->canonicalUrl());

    // --- Contenant. ---
    $contains = WordListFilters::fromPath('contenant/che');
    Assert::notNull($contains);
    Assert::same('CHE', $contains->contains);

    // --- Avec : repetitions comptees, triees par lettre. ---
    $with = WordListFilters::fromPath('avec/a/a/r');
    Assert::notNull($with);
    Assert::same(['A' => 2, 'R' => 1], $with->withLetters);
    Assert::same('/palabras/avec/a/a/r', $with->canonicalUrl(), 'ordre canonique alphabetique, repetitions regroupees');

    // Ordre de saisie sans effet sur le resultat (meme principe que Rack : multiensemble).
    $withReordered = WordListFilters::fromPath('avec/r/a/a');
    Assert::notNull($withReordered);
    Assert::same($with->withLetters, $withReordered->withLetters);
    Assert::same($with->canonicalUrl(), $withReordered->canonicalUrl());

    // "avec" sans aucune lettre : entree malformee, pas un resultat vide.
    Assert::null(WordListFilters::fromPath('avec'));

    // --- Sans : lettres distinctes, sans notion de repetition, deduplique et triees. ---
    $without = WordListFilters::fromPath('sans/z/x/z');
    Assert::notNull($without);
    Assert::same(['X', 'Z'], $without->withoutLetters);

    // --- Motif : longueur derivee, prefixe initial detecte, cases connues preservees. ---
    $pattern = WordListFilters::fromPath('5-letras/motif/c--e-');
    Assert::notNull($pattern);
    Assert::same('C--E-', $pattern->pattern);
    Assert::same(5, $pattern->length, 'la longueur du motif prevaut');
    Assert::true($pattern->needsUnindexedPredicates(), 'ce motif a une case connue (E) au-dela du prefixe initial (C) -> predicat non indexe necessaire');

    // Motif entierement fait de '-' : refuse, n'apporte rien qu'une longueur ne dise deja.
    Assert::null(WordListFilters::fromPath('5-letras/motif/-----'));

    // Motif dont la longueur explicite ne correspond pas au segment "{N}-letras" fourni :
    // pas une 404, la longueur du motif prevaut -- l'URL canonique se corrige elle-meme,
    // et le routeur redirige en 301 (meme esprit que toute autre permutation, docs/05).
    $mismatched = WordListFilters::fromPath('6-letras/motif/c--e-');
    Assert::notNull($mismatched);
    Assert::same(5, $mismatched->length);
    Assert::same('/palabras/5-letras/motif/c--e-', $mismatched->canonicalUrl());

    // --- Canonicalisation : ordre impose quel que soit l'ordre recu (docs/05). ---
    $permuted = WordListFilters::fromPath('terminan-en/tion/empiezan-por/ch');
    Assert::notNull($permuted);
    Assert::same('/palabras/empiezan-por/ch/terminan-en/tion', $permuted->canonicalUrl());

    $fullOrder = WordListFilters::fromPath('sans/z/7-letras/motif/-------/avec/a/empiezan-por/a');
    // motif tout-tirets refuse plus haut dans la chaine -> attendu null ici aussi (verifie
    // que le refus d'un segment ne laisse pas les autres segments partiellement acceptes).
    Assert::null($fullOrder);

    // --- Pagination : page 1 jamais dans l'URL, page 1 explicite redirige (pas 404). ---
    $noPage = WordListFilters::fromPath('7-letras');
    Assert::notNull($noPage);
    Assert::same(1, $noPage->page);
    Assert::same('/palabras/7-letras', $noPage->canonicalUrl(), 'page 1 jamais refletee dans l\'URL');

    $explicitPageOne = WordListFilters::fromPath('7-letras/page/1');
    Assert::notNull($explicitPageOne, 'page/1 est syntaxiquement valide, pas une entree malformee');
    Assert::same(1, $explicitPageOne->page);
    Assert::same('/palabras/7-letras', $explicitPageOne->canonicalUrl(), 'redirige vers la forme sans /page/1, jamais 404');

    $pageTwo = WordListFilters::fromPath('7-letras/page/2');
    Assert::notNull($pageTwo);
    Assert::same(2, $pageTwo->page);
    Assert::same('/palabras/7-letras/page/2', $pageTwo->canonicalUrl());

    Assert::null(WordListFilters::fromPath('7-letras/page/0'), 'page 0 invalide');
    Assert::null(WordListFilters::fromPath('7-letras/page/-1'), 'page negative invalide');
    Assert::null(WordListFilters::fromPath('7-letras/page/deux'), 'page non numerique invalide');

    // --- Position (D-023) : une lettre connue a une position precise, exige une longueur. ---
    $position = WordListFilters::fromPath('9-letras/position/3/a');
    Assert::notNull($position);
    Assert::same(3, $position->position);
    Assert::same('A', $position->positionLetter);
    Assert::same('/palabras/9-letras/position/3/a', $position->canonicalUrl());
    Assert::true(!$position->isEmpty());
    Assert::true($position->needsUnindexedPredicates(), 'substr() residuel, jamais indexe');

    Assert::null(WordListFilters::fromPath('position/3/a'), 'position sans longueur refusee');
    Assert::null(WordListFilters::fromPath('9-letras/position/10/a'), 'position au-dela de la longueur refusee');
    Assert::null(WordListFilters::fromPath('9-letras/position/0/a'), 'position 0 refusee');
    Assert::null(WordListFilters::fromPath('9-letras/position/3/ab'), 'position avec plus d\'une lettre refusee');
    Assert::null(WordListFilters::fromPath('9-letras/position/3'), 'position sans lettre refusee');
    Assert::null(WordListFilters::fromPath('9-letras/position/3/a/position/4/b'), 'mot-cle position duplique refuse');
    Assert::null(WordListFilters::fromPath('9-letras/motif/--a------/position/3/a'), 'position et motif incompatibles (meme concept, deux vocabulaires) refuses ensemble');

    // Collapse silencieux des positions degenerees (premiere/derniere lettre) vers
    // prefix/suffix -- evite le contenu duplique constate sur motif (voir docblock de classe
    // et reports/query-plans/position-family.md). canonicalPath() n'emet jamais
    // "position/1/..." ni "position/{longueur}/...".
    $firstLetter = WordListFilters::fromPath('5-letras/position/1/a');
    Assert::notNull($firstLetter);
    Assert::null($firstLetter->position, 'collapse vers prefix, position redevient null');
    Assert::same('A', $firstLetter->prefix);
    Assert::same('/palabras/5-letras/empiezan-por/a', $firstLetter->canonicalUrl());

    $lastLetter = WordListFilters::fromPath('5-letras/position/5/a');
    Assert::notNull($lastLetter);
    Assert::null($lastLetter->position, 'collapse vers suffix, position redevient null');
    Assert::same('A', $lastLetter->suffix);
    Assert::same('/palabras/5-letras/terminan-en/a', $lastLetter->canonicalUrl());

    // Conflits : une position degeneree qui contredit un empiezan-por/terminan-en explicite
    // deja present (lettre differente) est une contrainte contradictoire -> 404, jamais un
    // choix arbitraire entre les deux. Meme lettre : redondant mais coherent, accepte.
    Assert::null(WordListFilters::fromPath('5-letras/empiezan-por/b/position/1/a'), 'conflit empiezan-por=B vs position/1=A');
    Assert::null(WordListFilters::fromPath('5-letras/terminan-en/b/position/5/a'), 'conflit terminan-en=B vs position/5=A');
    $noConflict = WordListFilters::fromPath('5-letras/empiezan-por/a/position/1/a');
    Assert::notNull($noConflict, 'meme lettre : pas un conflit, collapse accepte');
    Assert::same('/palabras/5-letras/empiezan-por/a', $noConflict->canonicalUrl());

    // Position combinee a une autre contrainte (pas degeneree) : coexiste normalement.
    $combined = WordListFilters::fromPath('5-letras/empiezan-por/c/position/3/a');
    Assert::notNull($combined);
    Assert::same('C', $combined->prefix);
    Assert::same(3, $combined->position);
    Assert::same('/palabras/5-letras/empiezan-por/c/position/3/a', $combined->canonicalUrl());

    // --- Collapse "avec/X" redondant avec un empiezan-por/terminan-en d'une seule lettre X
    // --- (D-032) : "empiezan-por/X/avec/X" (minCount = 1) est toujours vrai des que le mot
    // --- commence deja par X -- garder cette entree withLetters ferait basculer a tort en
    // --- regime BORNE plafonne (voir reports/query-plans/commencant-avec-no-length-full-sweep.md
    // --- du site francais, section 5, 17/26 cas sous-affichant un total tronque a 10 000 au
    // --- lieu du vrai total, jusqu'a 224 205 pour R). Force brute sur les 26 lettres, cote
    // --- parsing uniquement (pas d'acces base ici, voir WordListSolverTest.php pour la
    // --- verification via le vrai solveur) : chaque combinaison degeneree doit voir son
    // --- entree withLetters retiree et son canonicalUrl() identique a celui de la forme
    // --- simplifiee.
    foreach (range('a', 'z') as $letter) {
        $degeneratePrefix = WordListFilters::fromPath("empiezan-por/$letter/avec/$letter");
        $simplePrefix = WordListFilters::fromPath("empiezan-por/$letter");
        Assert::notNull($degeneratePrefix, "empiezan-por/$letter/avec/$letter doit rester une entree valide");
        Assert::notNull($simplePrefix);
        Assert::same([], $degeneratePrefix->withLetters, "avec/$letter redondant avec empiezan-por/$letter doit etre retire");
        Assert::same($simplePrefix->canonicalUrl(), $degeneratePrefix->canonicalUrl(), "empiezan-por/$letter/avec/$letter doit collapser vers empiezan-por/$letter");
        Assert::true(!$degeneratePrefix->needsUnindexedPredicates(), "plus aucun predicat non indexe une fois le avec redondant retire ($letter)");

        $degenerateSuffix = WordListFilters::fromPath("terminan-en/$letter/avec/$letter");
        $simpleSuffix = WordListFilters::fromPath("terminan-en/$letter");
        Assert::notNull($degenerateSuffix, "terminan-en/$letter/avec/$letter doit rester une entree valide");
        Assert::notNull($simpleSuffix);
        Assert::same([], $degenerateSuffix->withLetters, "avec/$letter redondant avec terminan-en/$letter doit etre retire");
        Assert::same($simpleSuffix->canonicalUrl(), $degenerateSuffix->canonicalUrl(), "terminan-en/$letter/avec/$letter doit collapser vers terminan-en/$letter");
    }

    // Les DEUX cotes a la fois (empiezan-por/X ET terminan-en/X ET avec/X) : la meme lettre X
    // est redondante des deux points de vue simultanement, l'entree doit disparaitre une seule
    // fois (unset() est deja idempotent), les deux contraintes empiezan-por/terminan-en restent.
    $bothSidesDegenerate = WordListFilters::fromPath('5-letras/empiezan-por/a/terminan-en/a/avec/a');
    Assert::notNull($bothSidesDegenerate);
    Assert::same([], $bothSidesDegenerate->withLetters, 'avec/a redondant des deux cotes a la fois doit etre retire');
    Assert::same('/palabras/5-letras/empiezan-por/a/terminan-en/a', $bothSidesDegenerate->canonicalUrl());

    // --- Non-regression : cas NON degeneres, qui doivent rester parfaitement inchanges. ---

    // Lettre "avec" differente du prefixe/suffixe : jamais retiree.
    $differentLetter = WordListFilters::fromPath('empiezan-por/a/avec/b');
    Assert::notNull($differentLetter);
    Assert::same(['B' => 1], $differentLetter->withLetters, 'avec/b non redondant avec empiezan-por/a : jamais retire');
    Assert::same('/palabras/empiezan-por/a/avec/b', $differentLetter->canonicalUrl());

    // minCount >= 2 (deuxieme occurrence exigee, "avec/x/x") : PAS redondant avec un prefixe
    // d'une seule lettre -- le mot doit contenir un DEUXIEME X en plus de celui du prefixe,
    // un vrai predicat, jamais garanti par "commence par X" seul.
    $minCountTwo = WordListFilters::fromPath('empiezan-por/x/avec/x/x');
    Assert::notNull($minCountTwo);
    Assert::same(['X' => 2], $minCountTwo->withLetters, 'avec/x/x (minCount=2) n\'est jamais redondant avec empiezan-por/x seul');
    Assert::same('/palabras/empiezan-por/x/avec/x/x', $minCountTwo->canonicalUrl());

    $minCountTwoSuffix = WordListFilters::fromPath('terminan-en/x/avec/x/x');
    Assert::notNull($minCountTwoSuffix);
    Assert::same(['X' => 2], $minCountTwoSuffix->withLetters, 'avec/x/x (minCount=2) n\'est jamais redondant avec terminan-en/x seul');

    // minCount=1 pour X, mais UN AUTRE avec en plus a minCount=2 pour la meme lettre du
    // prefixe -- garde uniquement l'entree strictement redondante, jamais les autres lettres.
    $mixedWithOtherLetters = WordListFilters::fromPath('empiezan-por/a/avec/a/b/b');
    Assert::notNull($mixedWithOtherLetters);
    Assert::same(['B' => 2], $mixedWithOtherLetters->withLetters, 'seule l\'entree A (redondante) est retiree, B (minCount=2, non redondant) reste');

    // Prefixe/suffixe de PLUSIEURS lettres : hors perimetre de ce collapse (seule la forme
    // mono-lettre est traitee, voir docblock de classe) -- meme si la lettre "avec" fait partie
    // du prefixe multi-lettres, elle n'est PAS retiree.
    $multiLetterPrefixUntouched = WordListFilters::fromPath('empiezan-por/ab/avec/a');
    Assert::notNull($multiLetterPrefixUntouched);
    Assert::same(['A' => 1], $multiLetterPrefixUntouched->withLetters, 'prefixe multi-lettres : avec/a jamais retire (hors perimetre de ce collapse)');

    // --- Statut / tri (D-022) : raffinements d'affichage, en derniere position de l'ordre
    // --- canonique (statut avant tri), quel que soit l'ordre recu. ---
    $status = WordListFilters::fromPath('13-letras/statut/admis');
    Assert::notNull($status);
    Assert::same('admis', $status->status);
    Assert::same('/palabras/13-letras/statut/admis', $status->canonicalUrl());

    $sort = WordListFilters::fromPath('13-letras/tri/points-desc');
    Assert::notNull($sort);
    Assert::same('points-desc', $sort->sort);
    Assert::same('/palabras/13-letras/tri/points-desc', $sort->canonicalUrl());

    $statusSortReordered = WordListFilters::fromPath('13-letras/tri/points/statut/admis');
    Assert::notNull($statusSortReordered);
    Assert::same('admis', $statusSortReordered->status);
    Assert::same('points', $statusSortReordered->sort);
    Assert::same('/palabras/13-letras/statut/admis/tri/points', $statusSortReordered->canonicalUrl(), 'statut toujours avant tri, quel que soit l\'ordre recu');

    // "statut" seul, sans longueur : segment valide, vraie contrainte (isEmpty() = false).
    $statusOnly = WordListFilters::fromPath('statut/non-admis');
    Assert::notNull($statusOnly);
    Assert::true(!$statusOnly->isEmpty());
    Assert::same('/palabras/statut/non-admis', $statusOnly->canonicalUrl());

    // "tri" exige toujours une longueur explicite -- refuse sinon (404), y compris avec un
    // autre ancrage (empiezan-por seul n'est pas mesure pour ce tri, voir WordListSolver).
    Assert::null(WordListFilters::fromPath('tri/points'), 'tri sans longueur refuse');
    Assert::null(WordListFilters::fromPath('empiezan-por/a/tri/points'), 'tri sans longueur refuse meme avec un autre ancrage');

    // Valeurs fermees : toute valeur hors de la liste autorisee est refusee, jamais inventee.
    Assert::null(WordListFilters::fromPath('13-letras/statut/peut-etre'), 'valeur de statut hors liste fermee');
    Assert::null(WordListFilters::fromPath('13-letras/tri/alphabetique'), '"alphabetique" est le defaut implicite (absence de tri), pas une valeur acceptee');
    Assert::null(WordListFilters::fromPath('13-letras/statut'), 'statut sans valeur');
    Assert::null(WordListFilters::fromPath('13-letras/tri'), 'tri sans valeur');
    Assert::null(WordListFilters::fromPath('13-letras/statut/admis/statut/non-admis'), 'mot-cle statut duplique');

    // isEmpty() : statut seul est une vraie restriction, tri seul ne peut jamais exister sans
    // longueur (donc jamais un cas isEmpty() a lui seul, deja verifie ci-dessus indirectement).
    Assert::true(WordListFilters::fromPath('')->isEmpty());

    // --- Rejets : hors perimetre, malformes, ou hors bornes -- toujours null, jamais d'exception. ---
    Assert::null(WordListFilters::fromPath('position/3/r'), '"position" hors perimetre de cette phase (absent de docs/08)');
    Assert::null(WordListFilters::fromPath('empiezan-por/ch/empiezan-por/ta'), 'mot-cle "empiezan-por" duplique');
    Assert::null(WordListFilters::fromPath('20-letras'), 'longueur au-dessus de la borne D-010 (15)');
    Assert::null(WordListFilters::fromPath('1-letras'), 'longueur en dessous de la borne (2)');
    Assert::null(WordListFilters::fromPath('empiezan-por'), 'mot-cle sans valeur');
    Assert::null(WordListFilters::fromPath('inconnu/valeur'), 'mot-cle non reconnu');
    Assert::null(WordListFilters::fromPath('avec/ab'), 'segment "avec" de plus d\'une lettre');
    Assert::null(WordListFilters::fromPath("avec/\xFF\xFE"), 'octets UTF-8 invalides');
    Assert::null(WordListFilters::fromPath('empiezan-por/ch/7-letras'), 'longueur doit ouvrir le chemin, jamais apparaitre ailleurs');
    // Anciennes URL francaises : segments francais "commencant"/"terminant"/"-lettres"
    // ne sont PLUS reconnus (ES-004, "on ne garde pas les segments francais") -- doivent
    // etre rejetes comme un mot-cle inconnu quelconque, jamais silencieusement acceptes.
    Assert::null(WordListFilters::fromPath('commencant/ch'), 'ancien segment francais "commencant" jamais reconnu');
    Assert::null(WordListFilters::fromPath('terminant/tion'), 'ancien segment francais "terminant" jamais reconnu');
    Assert::null(WordListFilters::fromPath('7-lettres'), 'ancien token francais "{N}-lettres" jamais reconnu');

    // --- Chemin vide : etat interne valide (isEmpty), mais WordListSolver le refuse
    // --- explicitement (hors perimetre de docs/05, jamais expose comme route). ---
    $empty = WordListFilters::fromPath('');
    Assert::notNull($empty);
    Assert::true($empty->isEmpty());

    // =====================================================================
    // Ñ -- regressions specifiques espagnoles (bugs reels trouves et corriges avant
    // tout import : Ñ occupe 2 octets en UTF-8, plusieurs fonctions byte-par-byte du
    // site francais herite rejetaient ou corrompaient toute contrainte impliquant Ñ).
    // =====================================================================

    // Prefixe/suffixe/contenant d'une seule lettre Ñ : AVANT le correctif, le regex
    // [A-Z] (sans Ñ) rejetait purement et simplement ces segments comme invalides.
    $prefixEnye = WordListFilters::fromPath('empiezan-por/ñ');
    Assert::notNull($prefixEnye, 'empiezan-por/ñ doit rester une contrainte valide');
    Assert::same('Ñ', $prefixEnye->prefix);
    Assert::same('/palabras/empiezan-por/ñ', $prefixEnye->canonicalUrl());

    $suffixEnye = WordListFilters::fromPath('terminan-en/ñ');
    Assert::notNull($suffixEnye, 'terminan-en/ñ doit rester une contrainte valide');
    Assert::same('Ñ', $suffixEnye->suffix);

    $containsEnye = WordListFilters::fromPath('contenant/ñoño');
    Assert::notNull($containsEnye, 'contenant/ñoño doit rester une contrainte valide');
    Assert::same('ÑOÑO', $containsEnye->contains);

    // "avec"/"sans" d'une lettre Ñ : AVANT le correctif, meme rejet.
    $withEnye = WordListFilters::fromPath('avec/ñ');
    Assert::notNull($withEnye, 'avec/ñ doit rester une contrainte valide');
    Assert::same(['Ñ' => 1], $withEnye->withLetters);

    $withoutEnye = WordListFilters::fromPath('sans/ñ');
    Assert::notNull($withoutEnye, 'sans/ñ doit rester une contrainte valide');
    Assert::same(['Ñ'], $withoutEnye->withoutLetters);

    // "position" avec une lettre Ñ.
    $positionEnye = WordListFilters::fromPath('5-letras/position/2/ñ');
    Assert::notNull($positionEnye, '5-letras/position/2/ñ doit rester une contrainte valide');
    Assert::same(2, $positionEnye->position);
    Assert::same('Ñ', $positionEnye->positionLetter);

    // "motif" avec Ñ comme case connue APRES la premiere case inconnue (regression du
    // bug le plus severe trouve : une boucle byte-par-byte y decalait TOUTES les
    // positions suivantes et corrompait la lettre elle-meme).
    $patternEnye = WordListFilters::fromPath('4-letras/motif/a-ñ-');
    Assert::notNull($patternEnye, '4-letras/motif/a-ñ- doit rester une contrainte valide');
    Assert::same('A-Ñ-', $patternEnye->pattern);
    Assert::same(4, $patternEnye->length, 'la longueur derivee du motif doit compter Ñ comme UN caractere, pas deux');
    Assert::true($patternEnye->needsUnindexedPredicates(), 'une case connue Ñ apres la premiere case inconnue reste un predicat non indexe');

    // Collapse D-032 ("avec/X" redondant avec "empiezan-por/X"/"terminan-en/X" d'une seule
    // lettre) doit continuer a fonctionner pour Ñ specifiquement -- AVANT le correctif,
    // strlen($prefix) === 1 valait faux pour "Ñ" (2 octets), desactivant le collapse.
    $collapseEnye = WordListFilters::fromPath('empiezan-por/ñ/avec/ñ');
    Assert::notNull($collapseEnye);
    Assert::same([], $collapseEnye->withLetters, 'avec/ñ redondant avec empiezan-por/ñ doit etre retire (D-032)');
    Assert::same('/palabras/empiezan-por/ñ', $collapseEnye->canonicalUrl());

    // Regression rangeBounds()/ALPHABET_ORDER (WordListSolver, verifiee indirectement
    // ici via un chemin qui la sollicite) : Ñ trie APRES Z sous la collation BINARY de
    // SQLite (verifie sur la base reelle) -- un prefixe finissant par Z ne doit plus
    // etre traite comme "dernier de l'alphabet", contrairement au comportement herite
    // du site francais. Couvert fonctionnellement par tests/Search/WordListSolverTest.php
    // (contre la vraie base) plutot qu'ici (WordListFilters ne fait aucun acces base).
};
