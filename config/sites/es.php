<?php

declare(strict_types=1);

// Configuration du site espagnol. Structure conforme a docs/02_ARCHITECTURE_DATA_
// MULTILINGUE.md, adaptee de config/sites/fr.php.
// dictionary_path pointe vers storage/, hors du dossier web (public/) sur l'hebergement.
// seo_path : storage/seo_es.sqlite EST construite et lue au runtime par App\Seo\Registry
// (772 629 lignes au 2026-08-31, registre SEO dans le perimetre depuis ES-009 -- voir
// docs/DECISIONS.md ES-009 a ES-027 et docs/PHASE_STATUS.md section ES).
//
// Noms de colonnes 'is_ods8'/'is_ods9' CONSERVES tels quels (pas renommes en
// is_file2017/is_fise2) : ce sont des identifiants internes references en dur par SQL
// et par des cles de tableau PHP dans plusieurs fichiers de app/Search/ (TermLookup,
// RackSolver, RelationsFinder, Suggester, WordListSolver), eux-memes consommes par
// app/View/ (hors perimetre de cet agent -- jamais modifie). Renommer casserait ce
// chemin sans qu'aucun test ne puisse le couvrir depuis ce dossier. Voir schema.sql
// pour le detail complet de cette decision et docs/DECISIONS.md pour la decision
// tracee. Seule la SEMANTIQUE change pour ce site : is_ods8 = admis Lexicon FILE 2017,
// is_ods9 = admis Lexicon FISE-2 2009 -- les etiquettes visibles (badge) sont
// correctement espagnoles ci-dessous, seul l'identifiant interne garde son nom.
return [
    'language' => 'es',
    'dictionary_path' => __DIR__ . '/../../storage/dictionary_es.sqlite',
    'seo_path' => __DIR__ . '/../../storage/seo_es.sqlite',

    'lexicons' => [
        ['column' => 'is_ods8', 'badge' => 'FILE 2017'],
        ['column' => 'is_ods9', 'badge' => 'FISE-2'],
    ],
    // Colonne inerte a ce stade (aucun code de app/ ne la lit dynamiquement -- verifie
    // avant ce changement), mais gardee coherente avec le nom reel de la colonne dans
    // storage/dictionary_es.sqlite (is_spanish, PAS is_french : renommage sans risque,
    // cette colonne n'est jamais lue par une requete SQL en dur, contrairement a
    // is_ods8/is_ods9 ci-dessus).
    'general_language_column' => 'is_spanish',

    // Valeurs des tuiles espagnoles -- edition internationale/europeenne (100 fiches,
    // AVEC les tuiles digrammes dediees CH/LL/RR : decision produit confirmee -- regle
    // FISE explicite, "il est interdit de composer CH/LL/RR a partir de deux tuiles
    // separees". Une tuile CH/LL/RR compte pour SA valeur propre, jamais la somme de
    // ses deux lettres -- voir App\Search\Normalizer::tokenizeTiles()/score(), qui
    // implemente cette tokenisation, obligatoire pour /jugar/{letras} (App\Search\Rack,
    // RackSolver) et pour les anagrammes de fiche mot (RelationsFinder). Doit rester
    // identique a TILE_SCORES dans scripts/lib/normalize.py -- toute derive entre les
    // deux est detectee par tests/Search/NormalizerTest.php et
    // tests/Search/TermLookupTest.php.
    //
    // Distribution verifiee directement sur
    // https://es.wikipedia.org/wiki/Distribuci%C3%B3n_de_las_letras_en_el_Scrabble
    // (2026-08-27, section « Español », edition hors Amerique du Nord), confirmee par
    // sommation manuelle independante (100 tuiles exactement, 2 blancs compris) :
    //   1 point   A(x12) E(x12) O(x9) I(x6) S(x6) N(x5) L(x4) R(x5) U(x5) T(x4)
    //   2 points  D(x5) G(x2)
    //   3 points  C(x4) B(x2) M(x2) P(x2)
    //   4 points  H(x2) F(x1) V(x1) Y(x1)
    //   5 points  CH(x1) Q(x1)
    //   8 points  J(x1) LL(x1) Ñ(x1) RR(x1) X(x1)
    //   10 points Z(x1)
    //
    // K et W sont absents de cette edition materielle (aucune tuile K ni W dans le jeu
    // physique), et n'apparaissent dans AUCUN mot des deux sources Scrabble importees
    // (0 occurrence mesuree sur 639 292 + 636 598 entrees, data/raw/PROVENANCE.md) --
    // les valeurs ci-dessous sont des valeurs de secours (alignees sur l'edition
    // nord-americaine Hasbro, qui inclut les deux) uniquement pour que le calcul de
    // score ne leve jamais d'exception si un visiteur verifie un mot inconnu contenant
    // un K ou un W.
    'tile_scores' => [
        'A' => 1, 'B' => 3, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 4, 'G' => 2, 'H' => 4, 'I' => 1,
        'J' => 8, 'K' => 10, 'L' => 1, 'M' => 3, 'N' => 1, 'Ñ' => 8, 'O' => 1, 'P' => 3,
        'Q' => 5, 'R' => 1, 'S' => 1, 'T' => 1, 'U' => 1, 'V' => 4, 'W' => 8, 'X' => 8, 'Y' => 4,
        'Z' => 10,
        'CH' => 5, 'LL' => 8, 'RR' => 8,
    ],

    // Bornes identiques a MIN_LENGTH/MAX_LENGTH de scripts/lib/normalize.py. Le
    // Lexicon FISE 2 est documente comme couvrant explicitement "de deux a quinze
    // lettres" -- meme borne que le site francais, aucune adaptation necessaire.
    'min_term_length' => 2,
    'max_term_length' => 15,

    // Domaine prevu : wordcheckr.es (pas encore deploye -- ce build reste entierement
    // local, voir docs/PHASE_STATUS.md). Convention deja utilisee par le site
    // francais (wordcheckr.fr, D-042-equivalent) pour la famille de domaines du
    // projet.
    'canonical_base_url' => 'https://www.wordcheckr.es',
];
