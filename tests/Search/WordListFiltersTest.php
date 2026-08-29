<?php

declare(strict_types=1);

use App\Search\WordListFilters;
use Tests\Support\Assert;

/**
 * App\Search\WordListFilters : analyse et canonicalisation des contraintes de /palabras/...
 * (URL localisee, ES-004 -- equivalent /mots/... du site francais), independamment de toute
 * base de donnees -- meme esprit que RackTest.php pour App\Search\Rack.
 *
 * Mots-cles de segment, tous espagnols depuis ES-014 (ES-004 avait deja traite les deux
 * premiers, docs/DECISIONS.md) -- l'ORDRE canonique, lui, n'a jamais change :
 *
 *   commencant -> empiezan-por     contenant -> contienen     avec -> con-letras
 *   terminant  -> terminan-en      position  -> posicion      sans -> sin
 *   motif      -> patron           statut    -> estado        tri  -> orden
 *
 * Les VALEURS d'enumeration (admis/non-admis, points/points-desc) restent volontairement
 * inchangees par ES-014 -- voir WordListFilters::STATUS_VALUES pour la raison.
 *
 * Les anciens segments francais ne sont PLUS reconnus du tout (404, jamais une redirection) :
 * verifie explicitement plus bas, un par un, pas seulement implicitement par leur absence.
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
    $contains = WordListFilters::fromPath('contienen/che');
    Assert::notNull($contains);
    Assert::same('CHE', $contains->contains);

    // --- Avec : repetitions comptees, triees par lettre. ---
    $with = WordListFilters::fromPath('con-letras/a/a/r');
    Assert::notNull($with);
    Assert::same(['A' => 2, 'R' => 1], $with->withLetters);
    Assert::same('/palabras/con-letras/a/a/r', $with->canonicalUrl(), 'ordre canonique alphabetique, repetitions regroupees');

    // Ordre de saisie sans effet sur le resultat (meme principe que Rack : multiensemble).
    $withReordered = WordListFilters::fromPath('con-letras/r/a/a');
    Assert::notNull($withReordered);
    Assert::same($with->withLetters, $withReordered->withLetters);
    Assert::same($with->canonicalUrl(), $withReordered->canonicalUrl());

    // "con-letras" sans aucune lettre : entree malformee, pas un resultat vide.
    Assert::null(WordListFilters::fromPath('con-letras'));

    // --- Sans : lettres distinctes, sans notion de repetition, deduplique et triees. ---
    $without = WordListFilters::fromPath('sin/z/x/z');
    Assert::notNull($without);
    Assert::same(['X', 'Z'], $without->withoutLetters);

    // --- Motif : longueur derivee, prefixe initial detecte, cases connues preservees. ---
    $pattern = WordListFilters::fromPath('5-letras/patron/c--e-');
    Assert::notNull($pattern);
    Assert::same('C--E-', $pattern->pattern);
    Assert::same(5, $pattern->length, 'la longueur du motif prevaut');
    Assert::true($pattern->needsUnindexedPredicates(), 'ce motif a une case connue (E) au-dela du prefixe initial (C) -> predicat non indexe necessaire');

    // Motif entierement fait de '-' : refuse, n'apporte rien qu'une longueur ne dise deja.
    Assert::null(WordListFilters::fromPath('5-letras/patron/-----'));

    // Motif dont la longueur explicite ne correspond pas au segment "{N}-letras" fourni :
    // pas une 404, la longueur du motif prevaut -- l'URL canonique se corrige elle-meme,
    // et le routeur redirige en 301 (meme esprit que toute autre permutation, docs/05).
    $mismatched = WordListFilters::fromPath('6-letras/patron/c--e-');
    Assert::notNull($mismatched);
    Assert::same(5, $mismatched->length);
    Assert::same('/palabras/5-letras/patron/c--e-', $mismatched->canonicalUrl());

    // --- Canonicalisation : ordre impose quel que soit l'ordre recu (docs/05). ---
    $permuted = WordListFilters::fromPath('terminan-en/tion/empiezan-por/ch');
    Assert::notNull($permuted);
    Assert::same('/palabras/empiezan-por/ch/terminan-en/tion', $permuted->canonicalUrl());

    $fullOrder = WordListFilters::fromPath('sin/z/7-letras/patron/-------/con-letras/a/empiezan-por/a');
    // patron tout-tirets refuse plus haut dans la chaine -> attendu null ici aussi (verifie
    // que le refus d'un segment ne laisse pas les autres segments partiellement acceptes).
    Assert::null($fullOrder);

    // --- ORDRE CANONIQUE COMPLET (ES-014) : les NEUF mots-cles a la fois, saisis dans un ordre
    // --- volontairement aberrant, doivent ressortir dans l'ordre impose inchange depuis
    // --- l'origine -- longueur, empiezan-por, contienen, terminan-en, posicion, con-letras,
    // --- sin, patron*, estado, orden. (*patron est exclu de ce cas precis : il est
    // --- structurellement incompatible avec posicion, deja verifie plus bas ; il garde sa
    // --- place dans l'ordre, verifiee par les cas patron ci-dessus.)
    // --- Le token positionnel "{N}-letras" doit rester en tete, il n'est pas un mot-cle.
    $everyKeyword = WordListFilters::fromPath(
        '9-letras/orden/points-desc/estado/admis/sin/z/con-letras/b/posicion/3/a/terminan-en/s/contienen/rr/empiezan-por/c'
    );
    Assert::notNull($everyKeyword, 'huit contraintes simultanees restent une combinaison valide');
    Assert::same(
        '/palabras/9-letras/empiezan-por/c/contienen/rr/terminan-en/s/posicion/3/a/con-letras/b/sin/z/estado/admis/orden/points-desc',
        $everyKeyword->canonicalUrl(),
        'ordre canonique impose, identique a celui d\'avant ES-014 -- seuls les mots ont change'
    );

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
    $position = WordListFilters::fromPath('9-letras/posicion/3/a');
    Assert::notNull($position);
    Assert::same(3, $position->position);
    Assert::same('A', $position->positionLetter);
    Assert::same('/palabras/9-letras/posicion/3/a', $position->canonicalUrl());
    Assert::true(!$position->isEmpty());
    Assert::true($position->needsUnindexedPredicates(), 'substr() residuel, jamais indexe');

    Assert::null(WordListFilters::fromPath('posicion/3/a'), 'position sans longueur refusee');
    Assert::null(WordListFilters::fromPath('9-letras/posicion/10/a'), 'position au-dela de la longueur refusee');
    Assert::null(WordListFilters::fromPath('9-letras/posicion/0/a'), 'position 0 refusee');
    Assert::null(WordListFilters::fromPath('9-letras/posicion/3/ab'), 'position avec plus d\'une lettre refusee');
    Assert::null(WordListFilters::fromPath('9-letras/posicion/3'), 'position sans lettre refusee');
    Assert::null(WordListFilters::fromPath('9-letras/posicion/3/a/posicion/4/b'), 'mot-cle position duplique refuse');
    Assert::null(WordListFilters::fromPath('9-letras/patron/--a------/posicion/3/a'), 'position et motif incompatibles (meme concept, deux vocabulaires) refuses ensemble');

    // Collapse silencieux des positions degenerees (premiere/derniere lettre) vers
    // prefix/suffix -- evite le contenu duplique constate sur motif (voir docblock de classe
    // et reports/query-plans/position-family.md). canonicalPath() n'emet jamais
    // "posicion/1/..." ni "posicion/{longueur}/...".
    $firstLetter = WordListFilters::fromPath('5-letras/posicion/1/a');
    Assert::notNull($firstLetter);
    Assert::null($firstLetter->position, 'collapse vers prefix, position redevient null');
    Assert::same('A', $firstLetter->prefix);
    Assert::same('/palabras/5-letras/empiezan-por/a', $firstLetter->canonicalUrl());

    $lastLetter = WordListFilters::fromPath('5-letras/posicion/5/a');
    Assert::notNull($lastLetter);
    Assert::null($lastLetter->position, 'collapse vers suffix, position redevient null');
    Assert::same('A', $lastLetter->suffix);
    Assert::same('/palabras/5-letras/terminan-en/a', $lastLetter->canonicalUrl());

    // Conflits : une position degeneree qui contredit un empiezan-por/terminan-en explicite
    // deja present (lettre differente) est une contrainte contradictoire -> 404, jamais un
    // choix arbitraire entre les deux. Meme lettre : redondant mais coherent, accepte.
    Assert::null(WordListFilters::fromPath('5-letras/empiezan-por/b/posicion/1/a'), 'conflit empiezan-por=B vs position/1=A');
    Assert::null(WordListFilters::fromPath('5-letras/terminan-en/b/posicion/5/a'), 'conflit terminan-en=B vs position/5=A');
    $noConflict = WordListFilters::fromPath('5-letras/empiezan-por/a/posicion/1/a');
    Assert::notNull($noConflict, 'meme lettre : pas un conflit, collapse accepte');
    Assert::same('/palabras/5-letras/empiezan-por/a', $noConflict->canonicalUrl());

    // Position combinee a une autre contrainte (pas degeneree) : coexiste normalement.
    $combined = WordListFilters::fromPath('5-letras/empiezan-por/c/posicion/3/a');
    Assert::notNull($combined);
    Assert::same('C', $combined->prefix);
    Assert::same(3, $combined->position);
    Assert::same('/palabras/5-letras/empiezan-por/c/posicion/3/a', $combined->canonicalUrl());

    // --- Collapse "con-letras/X" redondant avec un empiezan-por/terminan-en d'une seule lettre X
    // --- (D-032) : "empiezan-por/X/con-letras/X" (minCount = 1) est toujours vrai des que le mot
    // --- commence deja par X -- garder cette entree withLetters ferait basculer a tort en
    // --- regime BORNE plafonne (voir reports/query-plans/commencant-avec-no-length-full-sweep.md
    // --- du site francais, section 5, 17/26 cas sous-affichant un total tronque a 10 000 au
    // --- lieu du vrai total, jusqu'a 224 205 pour R). Force brute sur les 26 lettres, cote
    // --- parsing uniquement (pas d'acces base ici, voir WordListSolverTest.php pour la
    // --- verification via le vrai solveur) : chaque combinaison degeneree doit voir son
    // --- entree withLetters retiree et son canonicalUrl() identique a celui de la forme
    // --- simplifiee.
    foreach (range('a', 'z') as $letter) {
        $degeneratePrefix = WordListFilters::fromPath("empiezan-por/$letter/con-letras/$letter");
        $simplePrefix = WordListFilters::fromPath("empiezan-por/$letter");
        Assert::notNull($degeneratePrefix, "empiezan-por/$letter/con-letras/$letter doit rester une entree valide");
        Assert::notNull($simplePrefix);
        Assert::same([], $degeneratePrefix->withLetters, "con-letras/$letter redondant avec empiezan-por/$letter doit etre retire");
        Assert::same($simplePrefix->canonicalUrl(), $degeneratePrefix->canonicalUrl(), "empiezan-por/$letter/con-letras/$letter doit collapser vers empiezan-por/$letter");
        Assert::true(!$degeneratePrefix->needsUnindexedPredicates(), "plus aucun predicat non indexe une fois le avec redondant retire ($letter)");

        $degenerateSuffix = WordListFilters::fromPath("terminan-en/$letter/con-letras/$letter");
        $simpleSuffix = WordListFilters::fromPath("terminan-en/$letter");
        Assert::notNull($degenerateSuffix, "terminan-en/$letter/con-letras/$letter doit rester une entree valide");
        Assert::notNull($simpleSuffix);
        Assert::same([], $degenerateSuffix->withLetters, "con-letras/$letter redondant avec terminan-en/$letter doit etre retire");
        Assert::same($simpleSuffix->canonicalUrl(), $degenerateSuffix->canonicalUrl(), "terminan-en/$letter/con-letras/$letter doit collapser vers terminan-en/$letter");
    }

    // Les DEUX cotes a la fois (empiezan-por/X ET terminan-en/X ET avec/X) : la meme lettre X
    // est redondante des deux points de vue simultanement, l'entree doit disparaitre une seule
    // fois (unset() est deja idempotent), les deux contraintes empiezan-por/terminan-en restent.
    $bothSidesDegenerate = WordListFilters::fromPath('5-letras/empiezan-por/a/terminan-en/a/con-letras/a');
    Assert::notNull($bothSidesDegenerate);
    Assert::same([], $bothSidesDegenerate->withLetters, 'con-letras/a redondant des deux cotes a la fois doit etre retire');
    Assert::same('/palabras/5-letras/empiezan-por/a/terminan-en/a', $bothSidesDegenerate->canonicalUrl());

    // --- Non-regression : cas NON degeneres, qui doivent rester parfaitement inchanges. ---

    // Lettre "con-letras" differente du prefixe/suffixe : jamais retiree.
    $differentLetter = WordListFilters::fromPath('empiezan-por/a/con-letras/b');
    Assert::notNull($differentLetter);
    Assert::same(['B' => 1], $differentLetter->withLetters, 'con-letras/b non redondant avec empiezan-por/a : jamais retire');
    Assert::same('/palabras/empiezan-por/a/con-letras/b', $differentLetter->canonicalUrl());

    // minCount >= 2 (deuxieme occurrence exigee, "con-letras/x/x") : PAS redondant avec un prefixe
    // d'une seule lettre -- le mot doit contenir un DEUXIEME X en plus de celui du prefixe,
    // un vrai predicat, jamais garanti par "commence par X" seul.
    $minCountTwo = WordListFilters::fromPath('empiezan-por/x/con-letras/x/x');
    Assert::notNull($minCountTwo);
    Assert::same(['X' => 2], $minCountTwo->withLetters, 'con-letras/x/x (minCount=2) n\'est jamais redondant avec empiezan-por/x seul');
    Assert::same('/palabras/empiezan-por/x/con-letras/x/x', $minCountTwo->canonicalUrl());

    $minCountTwoSuffix = WordListFilters::fromPath('terminan-en/x/con-letras/x/x');
    Assert::notNull($minCountTwoSuffix);
    Assert::same(['X' => 2], $minCountTwoSuffix->withLetters, 'con-letras/x/x (minCount=2) n\'est jamais redondant avec terminan-en/x seul');

    // minCount=1 pour X, mais UN AUTRE avec en plus a minCount=2 pour la meme lettre du
    // prefixe -- garde uniquement l'entree strictement redondante, jamais les autres lettres.
    $mixedWithOtherLetters = WordListFilters::fromPath('empiezan-por/a/con-letras/a/b/b');
    Assert::notNull($mixedWithOtherLetters);
    Assert::same(['B' => 2], $mixedWithOtherLetters->withLetters, 'seule l\'entree A (redondante) est retiree, B (minCount=2, non redondant) reste');

    // Prefixe/suffixe de PLUSIEURS lettres : hors perimetre de ce collapse (seule la forme
    // mono-lettre est traitee, voir docblock de classe) -- meme si la lettre "con-letras" fait partie
    // du prefixe multi-lettres, elle n'est PAS retiree.
    $multiLetterPrefixUntouched = WordListFilters::fromPath('empiezan-por/ab/con-letras/a');
    Assert::notNull($multiLetterPrefixUntouched);
    Assert::same(['A' => 1], $multiLetterPrefixUntouched->withLetters, 'prefixe multi-lettres : avec/a jamais retire (hors perimetre de ce collapse)');

    // --- Statut / tri (D-022) : raffinements d'affichage, en derniere position de l'ordre
    // --- canonique (statut avant tri), quel que soit l'ordre recu. ---
    $status = WordListFilters::fromPath('13-letras/estado/admis');
    Assert::notNull($status);
    Assert::same('admis', $status->status);
    Assert::same('/palabras/13-letras/estado/admis', $status->canonicalUrl());

    $sort = WordListFilters::fromPath('13-letras/orden/points-desc');
    Assert::notNull($sort);
    Assert::same('points-desc', $sort->sort);
    Assert::same('/palabras/13-letras/orden/points-desc', $sort->canonicalUrl());

    $statusSortReordered = WordListFilters::fromPath('13-letras/orden/points/estado/admis');
    Assert::notNull($statusSortReordered);
    Assert::same('admis', $statusSortReordered->status);
    Assert::same('points', $statusSortReordered->sort);
    Assert::same('/palabras/13-letras/estado/admis/orden/points', $statusSortReordered->canonicalUrl(), 'statut toujours avant tri, quel que soit l\'ordre recu');

    // "estado" seul, sans longueur : segment valide, vraie contrainte (isEmpty() = false).
    $statusOnly = WordListFilters::fromPath('estado/non-admis');
    Assert::notNull($statusOnly);
    Assert::true(!$statusOnly->isEmpty());
    Assert::same('/palabras/estado/non-admis', $statusOnly->canonicalUrl());

    // "orden" exige toujours une longueur explicite -- refuse sinon (404), y compris avec un
    // autre ancrage (empiezan-por seul n'est pas mesure pour ce tri, voir WordListSolver).
    Assert::null(WordListFilters::fromPath('orden/points'), 'tri sans longueur refuse');
    Assert::null(WordListFilters::fromPath('empiezan-por/a/orden/points'), 'tri sans longueur refuse meme avec un autre ancrage');

    // Valeurs fermees : toute valeur hors de la liste autorisee est refusee, jamais inventee.
    Assert::null(WordListFilters::fromPath('13-letras/estado/peut-etre'), 'valeur de statut hors liste fermee');
    Assert::null(WordListFilters::fromPath('13-letras/orden/alphabetique'), '"alphabetique" est le defaut implicite (absence de tri), pas une valeur acceptee');
    Assert::null(WordListFilters::fromPath('13-letras/estado'), 'statut sans valeur');
    Assert::null(WordListFilters::fromPath('13-letras/orden'), 'tri sans valeur');
    Assert::null(WordListFilters::fromPath('13-letras/estado/admis/estado/non-admis'), 'mot-cle statut duplique');

    // isEmpty() : statut seul est une vraie restriction, tri seul ne peut jamais exister sans
    // longueur (donc jamais un cas isEmpty() a lui seul, deja verifie ci-dessus indirectement).
    Assert::true(WordListFilters::fromPath('')->isEmpty());

    // --- Rejets : hors perimetre, malformes, ou hors bornes -- toujours null, jamais d'exception. ---
    Assert::null(WordListFilters::fromPath('posicion/3/r'), '"posicion" sans longueur explicite refusee (D-023)');
    Assert::null(WordListFilters::fromPath('empiezan-por/ch/empiezan-por/ta'), 'mot-cle "empiezan-por" duplique');
    Assert::null(WordListFilters::fromPath('20-letras'), 'longueur au-dessus de la borne D-010 (15)');
    Assert::null(WordListFilters::fromPath('1-letras'), 'longueur en dessous de la borne (2)');
    Assert::null(WordListFilters::fromPath('empiezan-por'), 'mot-cle sans valeur');
    Assert::null(WordListFilters::fromPath('inconnu/valeur'), 'mot-cle non reconnu');
    Assert::null(WordListFilters::fromPath('con-letras/ab'), 'segment "con-letras" de plus d\'une lettre');
    Assert::null(WordListFilters::fromPath("con-letras/\xFF\xFE"), 'octets UTF-8 invalides');
    Assert::null(WordListFilters::fromPath('empiezan-por/ch/7-letras'), 'longueur doit ouvrir le chemin, jamais apparaitre ailleurs');
    // Anciennes URL francaises : segments francais "commencant"/"terminant"/"-lettres"
    // ne sont PLUS reconnus (ES-004, "on ne garde pas les segments francais") -- doivent
    // etre rejetes comme un mot-cle inconnu quelconque, jamais silencieusement acceptes.
    Assert::null(WordListFilters::fromPath('commencant/ch'), 'ancien segment francais "commencant" jamais reconnu');
    Assert::null(WordListFilters::fromPath('terminant/tion'), 'ancien segment francais "terminant" jamais reconnu');
    Assert::null(WordListFilters::fromPath('7-lettres'), 'ancien token francais "{N}-lettres" jamais reconnu');

    // Idem pour les SEPT mots-cles traduits par ES-014 : chacun verifie individuellement, avec
    // une valeur par ailleurs parfaitement valide -- seul le mot-cle francais doit provoquer le
    // rejet. Aucune compatibilite ascendante, aucune redirection : 404 sec (meme regle qu'ES-004).
    Assert::null(WordListFilters::fromPath('contenant/che'), 'ancien segment francais "contenant" jamais reconnu (ES-014)');
    Assert::null(WordListFilters::fromPath('avec/a/a/r'), 'ancien segment francais "avec" jamais reconnu (ES-014)');
    Assert::null(WordListFilters::fromPath('sans/z'), 'ancien segment francais "sans" jamais reconnu (ES-014)');
    Assert::null(WordListFilters::fromPath('5-letras/motif/c--e-'), 'ancien segment francais "motif" jamais reconnu (ES-014)');
    Assert::null(WordListFilters::fromPath('9-letras/position/3/a'), 'ancien segment francais "position" jamais reconnu (ES-014)');
    Assert::null(WordListFilters::fromPath('13-letras/statut/admis'), 'ancien segment francais "statut" jamais reconnu (ES-014)');
    Assert::null(WordListFilters::fromPath('13-letras/tri/points'), 'ancien segment francais "tri" jamais reconnu (ES-014)');

    // Un ancien mot-cle GLISSE au milieu d'un chemin par ailleurs valide doit faire echouer le
    // chemin ENTIER, jamais etre ignore en silence en ne gardant que les segments compris.
    Assert::null(
        WordListFilters::fromPath('9-letras/empiezan-por/c/avec/a'),
        'un seul ancien mot-cle francais suffit a invalider tout le chemin (ES-014)'
    );

    // Symetrie : la meme requete ecrite avec les mots-cles espagnols reste evidemment valide --
    // prouve que le rejet ci-dessus vient bien du MOT-CLE, jamais de la valeur ni de la forme.
    $sameRequestInSpanish = WordListFilters::fromPath('9-letras/empiezan-por/c/con-letras/a');
    Assert::notNull($sameRequestInSpanish, 'meme requete en espagnol : valide');
    Assert::same('/palabras/9-letras/empiezan-por/c/con-letras/a', $sameRequestInSpanish->canonicalUrl());

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

    $containsEnye = WordListFilters::fromPath('contienen/ñoño');
    Assert::notNull($containsEnye, 'contienen/ñoño doit rester une contrainte valide');
    Assert::same('ÑOÑO', $containsEnye->contains);

    // "con-letras"/"sin" d'une lettre Ñ : AVANT le correctif, meme rejet.
    $withEnye = WordListFilters::fromPath('con-letras/ñ');
    Assert::notNull($withEnye, 'con-letras/ñ doit rester une contrainte valide');
    Assert::same(['Ñ' => 1], $withEnye->withLetters);

    $withoutEnye = WordListFilters::fromPath('sin/ñ');
    Assert::notNull($withoutEnye, 'sin/ñ doit rester une contrainte valide');
    Assert::same(['Ñ'], $withoutEnye->withoutLetters);

    // "posicion" avec une lettre Ñ.
    $positionEnye = WordListFilters::fromPath('5-letras/posicion/2/ñ');
    Assert::notNull($positionEnye, '5-letras/posicion/2/ñ doit rester une contrainte valide');
    Assert::same(2, $positionEnye->position);
    Assert::same('Ñ', $positionEnye->positionLetter);

    // "patron" avec Ñ comme case connue APRES la premiere case inconnue (regression du
    // bug le plus severe trouve : une boucle byte-par-byte y decalait TOUTES les
    // positions suivantes et corrompait la lettre elle-meme).
    $patternEnye = WordListFilters::fromPath('4-letras/patron/a-ñ-');
    Assert::notNull($patternEnye, '4-letras/patron/a-ñ- doit rester une contrainte valide');
    Assert::same('A-Ñ-', $patternEnye->pattern);
    Assert::same(4, $patternEnye->length, 'la longueur derivee du motif doit compter Ñ comme UN caractere, pas deux');
    Assert::true($patternEnye->needsUnindexedPredicates(), 'une case connue Ñ apres la premiere case inconnue reste un predicat non indexe');

    // Collapse D-032 ("con-letras/X" redondant avec "empiezan-por/X"/"terminan-en/X" d'une seule
    // lettre) doit continuer a fonctionner pour Ñ specifiquement -- AVANT le correctif,
    // strlen($prefix) === 1 valait faux pour "Ñ" (2 octets), desactivant le collapse.
    $collapseEnye = WordListFilters::fromPath('empiezan-por/ñ/con-letras/ñ');
    Assert::notNull($collapseEnye);
    Assert::same([], $collapseEnye->withLetters, 'con-letras/ñ redondant avec empiezan-por/ñ doit etre retire (D-032)');
    Assert::same('/palabras/empiezan-por/ñ', $collapseEnye->canonicalUrl());

    // Regression rangeBounds()/ALPHABET_ORDER (WordListSolver, verifiee indirectement
    // ici via un chemin qui la sollicite) : Ñ trie APRES Z sous la collation BINARY de
    // SQLite (verifie sur la base reelle) -- un prefixe finissant par Z ne doit plus
    // etre traite comme "dernier de l'alphabet", contrairement au comportement herite
    // du site francais. Couvert fonctionnellement par tests/Search/WordListSolverTest.php
    // (contre la vraie base) plutot qu'ici (WordListFilters ne fait aucun acces base).
};
