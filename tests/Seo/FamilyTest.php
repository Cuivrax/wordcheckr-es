<?php

declare(strict_types=1);

use App\Seo\Family;
use Tests\Support\Assert;

/**
 * App\Seo\Family : liste fermee des familles de reporting/gouvernance et les deux regles
 * dures qui en decoulent (combinaisons infinies jamais dans un sitemap, espagnol non admis
 * jamais en masse) -- verifie ici independamment de toute base de donnees, meme esprit que
 * tests/Search/WordListFiltersTest.php pour App\Search\WordListFilters.
 */
return function (): void {
    Assert::true(Family::isValid(Family::HOME));
    Assert::true(Family::isValid(Family::WORD_ADMITTED));
    Assert::true(Family::isValid(Family::WORD_SPANISH_NOT_ADMITTED));
    Assert::true(Family::isValid(Family::WORD_LIST_LENGTH));
    Assert::true(!Family::isValid('mot_inconnu'));
    Assert::true(!Family::isValid(''));

    // Chaque valeur de ALL doit etre reconnue par isValid() -- coherence interne.
    foreach (Family::ALL as $family) {
        Assert::true(Family::isValid($family), "famille declaree mais non reconnue : {$family}");
    }

    // Combinaisons infinies : jamais de sitemap, quel que soit le lot (R4 de
    // scripts/apply_seo_batch.php).
    $expectedForbidden = [
        Family::WORD_LIST_CONTENANT,
        Family::WORD_LIST_AVEC,
        Family::WORD_LIST_SANS,
        Family::WORD_LIST_MOTIF,
        Family::RACK,
    ];

    foreach ($expectedForbidden as $family) {
        Assert::true(Family::forbidsSitemap($family), "attendu interdit de sitemap : {$family}");
    }

    // Familles bornees par construction (jamais interdites de sitemap en principe), meme si
    // aucune n'a de ligne reelle en base a ce stade (ES-009) -- seules HOME, WORD_ADMITTED et
    // WORD_LIST_LENGTH sont effectivement peuplees.
    $expectedAllowed = [
        Family::HOME,
        Family::WORD_ADMITTED,
        Family::WORD_SPANISH_NOT_ADMITTED,
        Family::WORD_LIST_LENGTH,
        Family::WORD_LIST_COMMENCANT,
        Family::WORD_LIST_TERMINANT,
        Family::WORD_LIST_POSITION,
        Family::WORD_LIST_COMBINED,
        // ES-025 : sous-familles BORNEES de "avec", distinctes de Family::WORD_LIST_AVEC
        // (generique, celle-ci reste dans $expectedForbidden ci-dessus).
        Family::WORD_LIST_AVEC_SINGLE_LETTER,
        Family::WORD_LIST_AVEC_TWO_LETTERS,
        Family::WORD_LIST_AVEC_THREE_LETTERS,
    ];

    foreach ($expectedAllowed as $family) {
        Assert::true(!Family::forbidsSitemap($family), "ne devrait pas etre interdit de sitemap : {$family}");
    }

    // Seule word_spanish_not_admitted porte la contrainte "jamais en masse".
    Assert::true(Family::isSpanishNotAdmitted(Family::WORD_SPANISH_NOT_ADMITTED));

    foreach (Family::ALL as $family) {
        if ($family === Family::WORD_SPANISH_NOT_ADMITTED) {
            continue;
        }

        Assert::true(!Family::isSpanishNotAdmitted($family), "ne devrait pas etre espagnol non admis : {$family}");
    }

    // Plafond RELEVE par ES-024 (decision explicite du proprietaire du produit, meme
    // raisonnement que D-017 cote francais) : couvre desormais le volume reel connu
    // (86 944 mots espagnols non admis) avec marge, comme le depot francais (D-017,
    // 500 000 >= 435 120).
    Assert::true(Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED > 0);
    Assert::true(
        Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED >= 86_944,
        'le plafond doit couvrir le volume reel de mots espagnols non admis (ES-024)',
    );
};
