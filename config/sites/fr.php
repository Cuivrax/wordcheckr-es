<?php

declare(strict_types=1);

// Configuration du site francais. Structure conforme a docs/02, exemple conceptuel.
// dictionary_path pointe vers storage/, hors du dossier web (public/) sur l'hebergement.
// seo_path reste inerte jusqu'a la Phase 6 (storage/seo_fr.sqlite n'existe pas encore).

return [
    'language' => 'fr',
    'dictionary_path' => __DIR__ . '/../../storage/dictionary_fr.sqlite',
    'seo_path' => __DIR__ . '/../../storage/seo_fr.sqlite',

    'lexicons' => [
        ['column' => 'is_ods8', 'badge' => 'ODS8'],
        ['column' => 'is_ods9', 'badge' => 'ODS9'],
    ],
    'general_language_column' => 'is_french',

    // Valeurs des tuiles francaises. Doit rester identique a TILE_SCORES dans
    // scripts/lib/normalize.py -- toute derive entre les deux est detectee par
    // tests/Search/TermLookupTest.php, qui recalcule score() pour les 838 180 lignes
    // reelles de storage/dictionary_fr.sqlite et compare a la colonne stockee.
    'tile_scores' => [
        'A' => 1, 'B' => 3, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 4, 'G' => 2, 'H' => 4, 'I' => 1,
        'J' => 8, 'K' => 10, 'L' => 1, 'M' => 2, 'N' => 1, 'O' => 1, 'P' => 3, 'Q' => 8, 'R' => 1,
        'S' => 1, 'T' => 1, 'U' => 1, 'V' => 4, 'W' => 10, 'X' => 10, 'Y' => 10, 'Z' => 10,
    ],

    // Bornes identiques a MIN_LENGTH/MAX_LENGTH de scripts/lib/normalize.py (D-010 revisee).
    'min_term_length' => 2,
    'max_term_length' => 15,

    // Domaine de production (decision utilisateur, 2026-08-21) : wordcheckr.fr, decline
    // ensuite en .com/.de/.es. Voir docs/DECISIONS.md.
    'canonical_base_url' => 'https://www.wordcheckr.fr',
];
