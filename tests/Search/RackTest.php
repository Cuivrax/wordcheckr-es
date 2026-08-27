<?php

declare(strict_types=1);

use App\Search\Rack;
use Tests\Support\Assert;

/**
 * App\Search\Rack : analyse de l'entree brute de /jouer/{lettres} en chevalet
 * (lettres connues + nombre de jokers), independamment de toute base de donnees.
 */
return function (): void {
    // Chevalet simple, sans joker.
    $rack = Rack::fromInput('aeinrst');
    Assert::notNull($rack, 'chevalet valide attendu');
    Assert::same(['A' => 1, 'E' => 1, 'I' => 1, 'N' => 1, 'R' => 1, 'S' => 1, 'T' => 1], $rack->letterCounts);
    Assert::same(0, $rack->jokerCount);
    Assert::same('aeinrst', $rack->slug);

    // Lettres repetees comptees correctement.
    $repeated = Rack::fromInput('aabbc');
    Assert::notNull($repeated);
    Assert::same(['A' => 2, 'B' => 2, 'C' => 1], $repeated->letterCounts);
    Assert::same(0, $repeated->jokerCount);
    Assert::same('aabbc', $repeated->slug);

    // '?' et '*' valent tous deux joker (docs/01_MASTER_BRIEF.md).
    $withQuestionMark = Rack::fromInput('ae?t');
    Assert::notNull($withQuestionMark);
    Assert::same(1, $withQuestionMark->jokerCount);
    Assert::same(['A' => 1, 'E' => 1, 'T' => 1], $withQuestionMark->letterCounts);
    // Slug canonique : toujours '*', jamais '?' (un '?' litteral casserait une URL non
    // encodee -- voir la note de classe).
    Assert::same('aet*', $withQuestionMark->slug);

    $withStar = Rack::fromInput('ae*t');
    Assert::notNull($withStar);
    Assert::same(1, $withStar->jokerCount);
    Assert::same('aet*', $withStar->slug, 'meme chevalet, meme slug canonique que la version avec ?');

    // Deux jokers, l'un et l'autre notation.
    $twoJokers = Rack::fromInput('a?e*t');
    Assert::notNull($twoJokers);
    Assert::same(2, $twoJokers->jokerCount);
    Assert::same('aet**', $twoJokers->slug);

    // Accents et majuscules traites par Normalizer::normalize() (D-009), pas de regle
    // dupliquee ici.
    $accented = Rack::fromInput('éàÉCOLE');
    Assert::notNull($accented);
    Assert::same(0, $accented->jokerCount);
    Assert::same(['A' => 1, 'C' => 1, 'E' => 3, 'L' => 1, 'O' => 1], $accented->letterCounts);

    // Ordre de saisie sans effet sur le resultat (chevalet = multiensemble).
    $reordered = Rack::fromInput('trisean');
    Assert::notNull($reordered);
    Assert::same($rack->letterCounts, $reordered->letterCounts, 'meme lettres, ordre different -> meme multiensemble');
    Assert::same($rack->slug, $reordered->slug);

    // Bornes de taille (D-010, meme plafond que Normalizer::MAX_LENGTH = 15).
    Assert::null(Rack::fromInput(''), 'entree vide');
    Assert::notNull(Rack::fromInput('a'), 'une seule lettre : chevalet valide, meme si aucun mot ne peut en sortir');
    Assert::notNull(Rack::fromInput(str_repeat('a', 15)), '15 lettres, exactement la borne');
    Assert::null(Rack::fromInput(str_repeat('a', 16)), '16 lettres, au-dessus de la borne');
    Assert::notNull(Rack::fromInput(str_repeat('a', 13) . '**'), '13 lettres + 2 jokers = 15 cases, exactement la borne');
    Assert::null(Rack::fromInput(str_repeat('a', 14) . '**'), '14 lettres + 2 jokers = 16 cases, au-dessus de la borne');

    // Au plus deux jokers (le sac de Scrabble francais n'en contient que deux).
    Assert::notNull(Rack::fromInput('ae**'), 'deux jokers, la limite exacte');
    Assert::null(Rack::fromInput('ae***'), 'trois jokers, refuse');

    // Formes invalides -> aucun chevalet, pas d'exception.
    Assert::null(Rack::fromInput('ae3t'), 'chiffre dans l\'entree');
    Assert::null(Rack::fromInput('ae t'), 'espace dans l\'entree');
    Assert::null(Rack::fromInput("\xFF\xFE"), 'octets UTF-8 invalides');
};
