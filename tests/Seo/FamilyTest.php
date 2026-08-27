<?php

declare(strict_types=1);

use App\Seo\Family;
use Tests\Support\Assert;

/**
 * App\Seo\Family : liste fermee des familles de reporting/gouvernance et les deux regles
 * dures qui en decoulent (combinaisons infinies jamais dans un sitemap, francais non admis
 * jamais en masse) -- verifie ici independamment de toute base de donnees, meme esprit que
 * tests/Search/WordListFiltersTest.php pour App\Search\WordListFilters.
 */
return function (): void {
    Assert::true(Family::isValid(Family::WORD_ADMITTED));
    Assert::true(Family::isValid(Family::HOME));
    Assert::true(Family::isValid(Family::WORD_LIST_POSITION));
    Assert::true(Family::isValid(Family::WORD_LIST_AVEC_SINGLE_LETTER));
    Assert::true(Family::isValid(Family::WORD_LIST_AVEC_TWO_LETTERS));
    Assert::true(Family::isValid(Family::WORD_LIST_AVEC_THREE_LETTERS));
    Assert::true(Family::isValid(Family::WORD_LIST_COMBINED_WITH_LETTER));
    Assert::true(Family::isValid(Family::WORD_LIST_COMMENCANT_WITH_LETTER));
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

    // WORD_LIST_COMBINED (commençant + terminant) retiré de NEVER_SITEMAP le 2026-08-09
    // (D-024 correctif, D-025) : espace borné (26×26 ou 14×26×26), pas une combinaison
    // infinie comme ses anciens voisins de la liste ci-dessus -- voir app/Seo/Family.php.
    // WORD_LIST_POSITION (D-023) ajoutée directement hors de NEVER_SITEMAP le 2026-08-10 :
    // une seule position/lettre connue à la fois, 2 366 combinaisons réelles au total, borne
    // de la famille elle-même (pas seulement d'un lot), voir app/Seo/Family.php.
    // WORD_LIST_AVEC_SINGLE_LETTER (demande produit du 2026-08-17, palier 1 de l'ouverture en
    // entonnoir de "avec") ajoutée directement hors de NEVER_SITEMAP : longueur + exactement
    // une lettre "avec", 364 combinaisons réelles au total sur ce périmètre précis -- distincte
    // en permanence de WORD_LIST_AVEC (multiensemble général, ci-dessous, reste interdit), voir
    // app/Seo/Family.php.
    // WORD_LIST_AVEC_TWO_LETTERS (demande produit du 2026-08-17, palier 2 de l'ouverture en
    // entonnoir de "avec") ajoutée directement hors de NEVER_SITEMAP : longueur + exactement
    // deux lettres "avec" distinctes, 4 550 combinaisons réelles au plus sur ce périmètre précis
    // (4 276 à ≥ 1 résultat) -- distincte en permanence de WORD_LIST_AVEC_SINGLE_LETTER (palier
    // 1, ci-dessus) et de WORD_LIST_AVEC (multiensemble général, ci-dessous, reste interdit),
    // voir app/Seo/Family.php.
    // WORD_LIST_AVEC_THREE_LETTERS (demande produit du 2026-08-18, palier 3 de l'ouverture en
    // entonnoir de "avec") ajoutée directement hors de NEVER_SITEMAP : longueur + exactement
    // trois lettres "avec" distinctes, 36 400 combinaisons réelles au plus sur ce périmètre précis
    // (28 827 à ≥ 1 résultat) -- distincte en permanence de WORD_LIST_AVEC_TWO_LETTERS (palier 2)
    // et WORD_LIST_AVEC_SINGLE_LETTER (palier 1, ci-dessus) et de WORD_LIST_AVEC (multiensemble
    // général, ci-dessous, reste interdit), voir app/Seo/Family.php.
    // WORD_LIST_COMBINED_WITH_LETTER (demande produit du 2026-08-18) ajoutée directement hors de
    // NEVER_SITEMAP : préfixe ET suffixe d'une seule lettre chacun, SANS longueur, PLUS une
    // lettre "avec" d'occurrence unique -- 15 886 combinaisons réellement candidates sur ce
    // périmètre précis (11 348 à ≥ 1 résultat, 9 923 réellement indexables une fois les 1 198
    // dégénérées D-032 ET les 227 doublons de contenu parent/enfant C-1 exclus -- correctif du
    // 2026-08-19, audit seo-technical-auditor consolidé -- puis 9 495 une fois 428 doublons de
    // contenu supplémentaires ENTRE LETTRES SOEURS exclus, I-A, 2e audit, même jour) --
    // distincte en permanence de WORD_LIST_COMBINED (commençant + terminant seuls, ci-dessus),
    // voir app/Seo/Family.php.
    // WORD_LIST_COMMENCANT_WITH_LETTER (demande produit du 2026-08-18, dernier des quatre axes
    // commençant/terminant/avec travaillés ce jour) ajoutée directement hors de NEVER_SITEMAP :
    // préfixe d'une seule lettre, SANS longueur, SANS terminant, PLUS une lettre "avec"
    // d'occurrence unique -- 676 combinaisons brutes au plus sur ce périmètre précis (650 non
    // dégénérées une fois les 26 diagonales D-032 exclues au précalcul, 646 réellement
    // indexables) -- distincte en permanence de WORD_LIST_COMMENCANT (préfixe seul, ci-dessus)
    // ET de WORD_LIST_COMBINED_WITH_LETTER (préfixe+terminant+avec, ci-dessus, forme de route
    // syntaxiquement différente), voir app/Seo/Family.php.
    $expectedAllowed = [
        Family::HOME,
        Family::WORD_ADMITTED,
        Family::WORD_FRENCH_NOT_ADMITTED,
        Family::WORD_LIST_LENGTH,
        Family::WORD_LIST_COMMENCANT,
        Family::WORD_LIST_TERMINANT,
        Family::WORD_LIST_COMBINED,
        Family::WORD_LIST_POSITION,
        Family::WORD_LIST_AVEC_SINGLE_LETTER,
        Family::WORD_LIST_AVEC_TWO_LETTERS,
        Family::WORD_LIST_AVEC_THREE_LETTERS,
        Family::WORD_LIST_COMBINED_WITH_LETTER,
        Family::WORD_LIST_COMMENCANT_WITH_LETTER,
    ];

    foreach ($expectedAllowed as $family) {
        Assert::true(!Family::forbidsSitemap($family), "ne devrait pas etre interdit de sitemap : {$family}");
    }

    // Seule word_french_not_admitted porte la contrainte "jamais en masse".
    Assert::true(Family::isFrenchNotAdmitted(Family::WORD_FRENCH_NOT_ADMITTED));

    foreach (Family::ALL as $family) {
        if ($family === Family::WORD_FRENCH_NOT_ADMITTED) {
            continue;
        }

        Assert::true(!Family::isFrenchNotAdmitted($family), "ne devrait pas etre francais non admis : {$family}");
    }

    // Plafond revise le 2026-08-04 (D-017, docs/DECISIONS.md) : decision explicite du
    // propriétaire du produit d'ouvrir l'indexation du francais non admis, le site
    // n'etant pas encore deploye (aucun risque Google reel avant la Phase 7). Le volume
    // change, l'attestation ligne par ligne (R6/R7 : notes non vide) reste obligatoire.
    Assert::true(Family::MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED > 0);
    Assert::true(
        Family::MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED >= 435_120,
        'le plafond doit couvrir la totalite des formes francaises non admises connues (D-017)',
    );
};
