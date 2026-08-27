<?php

declare(strict_types=1);

use App\Search\Rack;
use Tests\Support\Assert;

/**
 * App\Search\Rack : analyse de l'entree brute de /jugar/{letras} en chevalet (tuiles
 * connues + nombre de jokers), independamment de toute base de donnees.
 *
 * Adapte du site francais (tests/Search/RackTest.php) -- difference structurelle :
 * le chevalet espagnol se decoupe en TUILES (lettres simples A-Z/Ñ ou digrammes
 * CH/LL/RR), pas en caracteres, et la forme canonique d'URL separe les tuiles par
 * un tiret (necessaire, pas cosmetique -- voir Rack.php).
 */
return function (): void {
    // Chevalet simple, sans joker, sans digramme.
    $rack = Rack::fromInput('aeinrst');
    Assert::notNull($rack, 'chevalet valide attendu');
    Assert::same(['A' => 1, 'E' => 1, 'I' => 1, 'N' => 1, 'R' => 1, 'S' => 1, 'T' => 1], $rack->letterCounts);
    Assert::same(0, $rack->jokerCount);
    Assert::same('a-e-i-n-r-s-t', $rack->slug);

    // Lettres repetees comptees correctement.
    $repeated = Rack::fromInput('aabbc');
    Assert::notNull($repeated);
    Assert::same(['A' => 2, 'B' => 2, 'C' => 1], $repeated->letterCounts);
    Assert::same(0, $repeated->jokerCount);
    Assert::same('a-a-b-b-c', $repeated->slug);

    // Tuile digramme reconnue en saisie libre : "coche" = 4 tuiles (C, O, CH, E), pas
    // 5 lettres -- meme convention gloutonne que Normalizer::tokenizeTiles().
    $withDigraph = Rack::fromInput('coche');
    Assert::notNull($withDigraph);
    Assert::same(['C' => 1, 'CH' => 1, 'E' => 1, 'O' => 1], $withDigraph->letterCounts);
    Assert::same('c-ch-e-o', $withDigraph->slug);

    // Deux tuiles L SEPAREES (non adjacentes dans la saisie) restent deux tuiles L,
    // jamais une tuile LL -- "laela" n'a pas "ll" adjacent.
    $separateLs = Rack::fromInput('laela');
    Assert::notNull($separateLs);
    Assert::same(['A' => 2, 'E' => 1, 'L' => 2], $separateLs->letterCounts);
    Assert::same('a-a-e-l-l', $separateLs->slug);

    // La forme canonique DOIT rester stable au rechargement (round-trip) meme dans ce
    // cas limite -- le mode "segments explicites" (des qu'un tiret est present) evite
    // que le slug "a-a-e-l-l" ne se retokenise a tort en une seule tuile LL. Bug reel
    // trouve et corrige avant tout deploiement, voir le commentaire de classe de
    // Rack.php pour le detail complet.
    $roundTrip = Rack::fromInput($separateLs->slug);
    Assert::notNull($roundTrip);
    Assert::same($separateLs->letterCounts, $roundTrip->letterCounts, 'le rechargement du slug canonique ne doit jamais fusionner deux tuiles L en une tuile LL');
    Assert::same($separateLs->slug, $roundTrip->slug, 'le slug canonique doit etre un point fixe (round-trip stable)');

    // Meme garde-fou pour RR (perro a une tuile RR dediee) contre une saisie a deux R
    // separes (ex. "arreo" a un RR adjacent -- utiliser un mot different pour deux R
    // non adjacents : "raser" n'a pas de R adjacent).
    $tileRR = Rack::fromInput('carro');
    Assert::notNull($tileRR);
    Assert::same(['A' => 1, 'C' => 1, 'O' => 1, 'RR' => 1], $tileRR->letterCounts);
    Assert::same('a-c-o-rr', $tileRR->slug);

    // Segments explicites permettent, a l'inverse, de FORCER deux tuiles separees la
    // ou la saisie libre les aurait fusionnees -- "r-r" (avec tiret) reste deux tuiles
    // R distinctes, jamais une tuile RR (contrairement a "rr" sans tiret).
    $forcedSeparateRs = Rack::fromInput('r-r');
    Assert::notNull($forcedSeparateRs);
    Assert::same(['R' => 2], $forcedSeparateRs->letterCounts);
    $mergedRR = Rack::fromInput('rr');
    Assert::notNull($mergedRR);
    Assert::same(['RR' => 1], $mergedRR->letterCounts);
    Assert::true($forcedSeparateRs->slug !== $mergedRR->slug, '"r-r" et "rr" doivent produire des chevalets distincts');

    // Segment invalide (ni une lettre simple A-Z/Ñ, ni un digramme CH/LL/RR) -> aucun
    // chevalet, pas d'exception.
    Assert::null(Rack::fromInput('ab-cd'), 'segment "ab" n\'est pas une tuile valide');

    // '?' et '*' valent tous deux joker (docs/01_MASTER_BRIEF.md).
    $withQuestionMark = Rack::fromInput('ae?t');
    Assert::notNull($withQuestionMark);
    Assert::same(1, $withQuestionMark->jokerCount);
    Assert::same(['A' => 1, 'E' => 1, 'T' => 1], $withQuestionMark->letterCounts);
    // Slug canonique : toujours '*', jamais '?' (un '?' litteral casserait une URL non
    // encodee -- voir la note de classe).
    Assert::same('a-e-t-*', $withQuestionMark->slug);

    $withStar = Rack::fromInput('ae*t');
    Assert::notNull($withStar);
    Assert::same(1, $withStar->jokerCount);
    Assert::same('a-e-t-*', $withStar->slug, 'meme chevalet, meme slug canonique que la version avec ?');

    // Deux jokers, l'un et l'autre notation.
    $twoJokers = Rack::fromInput('a?e*t');
    Assert::notNull($twoJokers);
    Assert::same(2, $twoJokers->jokerCount);
    Assert::same('a-e-t-*-*', $twoJokers->slug);

    // Ñ traitee par Normalizer::normalize() comme une lettre a part entiere (pas une
    // regle dupliquee ici) -- verifie ici au niveau du chevalet, pas seulement de la
    // normalisation.
    $withEnye = Rack::fromInput('AÑO');
    Assert::notNull($withEnye);
    Assert::same(0, $withEnye->jokerCount);
    Assert::same(['A' => 1, 'O' => 1, 'Ñ' => 1], $withEnye->letterCounts);

    // Accents traites par Normalizer::normalize() (accents de voyelle retires, comme
    // dans les listes Scrabble sources).
    $accented = Rack::fromInput('cafétería');
    Assert::notNull($accented);
    Assert::same(0, $accented->jokerCount);
    Assert::same(['A' => 2, 'C' => 1, 'E' => 2, 'F' => 1, 'I' => 1, 'R' => 1, 'T' => 1], $accented->letterCounts);

    // Ordre de saisie sans effet sur le resultat (chevalet = multiensemble de tuiles).
    $reordered = Rack::fromInput('trisean');
    Assert::notNull($reordered);
    Assert::same($rack->letterCounts, $reordered->letterCounts, 'memes lettres, ordre different -> meme multiensemble');
    Assert::same($rack->slug, $reordered->slug);

    // Bornes de taille EN TUILES, pas en caracteres (meme plafond que
    // Normalizer::MAX_LENGTH = 15) -- "coche" = 4 tuiles pour 5 caracteres.
    Assert::null(Rack::fromInput(''), 'entree vide');
    Assert::notNull(Rack::fromInput('a'), 'une seule lettre : chevalet valide, meme si aucun mot ne peut en sortir');
    Assert::notNull(Rack::fromInput(str_repeat('a', 15)), '15 tuiles, exactement la borne');
    Assert::null(Rack::fromInput(str_repeat('a', 16)), '16 tuiles, au-dessus de la borne');
    Assert::notNull(Rack::fromInput(str_repeat('a', 13) . '**'), '13 tuiles + 2 jokers = 15 cases, exactement la borne');
    Assert::null(Rack::fromInput(str_repeat('a', 14) . '**'), '14 tuiles + 2 jokers = 16 cases, au-dessus de la borne');
    // 16 caracteres mais 8 tuiles digrammes (CH repete) : reste sous la borne de 15
    // TUILES malgre plus de 15 caracteres -- confirme que la borne porte bien sur les
    // tuiles, pas sur strlen().
    Assert::notNull(Rack::fromInput(str_repeat('ch', 8)), '16 caracteres mais 8 tuiles CH, sous la borne de 15 tuiles');

    // Au plus deux jokers (le sac de Scrabble espagnol n'en contient que deux).
    Assert::notNull(Rack::fromInput('ae**'), 'deux jokers, la limite exacte');
    Assert::null(Rack::fromInput('ae***'), 'trois jokers, refuse');

    // Formes invalides -> aucun chevalet, pas d'exception.
    Assert::null(Rack::fromInput('ae3t'), 'chiffre dans l\'entree');
    Assert::null(Rack::fromInput('ae t'), 'espace dans l\'entree');
    Assert::null(Rack::fromInput("\xFF\xFE"), 'octets UTF-8 invalides');
};
