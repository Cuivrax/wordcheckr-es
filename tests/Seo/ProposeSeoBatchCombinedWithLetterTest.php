<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * scripts/propose_seo_batch.php, cas 'combined_with_letter' (axe 2 -- D-033 App\Search\
 * StartEndWithLinksBuilder, App\Seo\Family::WORD_LIST_COMBINED_WITH_LETTER, NOUVELLE
 * classification distincte de WORD_LIST_COMBINED, demande produit du 2026-08-18) -- boite
 * noire, un vrai sous-processus PHP, jamais contre le vrai storage/seo_fr.sqlite (ce script ne
 * l'ouvre de toute facon jamais, uniquement storage/dictionary_fr.sqlite en lecture seule --
 * voir l'entete du script).
 *
 * Meme discipline que tests/Seo/ProposeSeoBatchAvecThreeLettersTest.php : ce cas genere une
 * PROPOSITION nouvelle a chaque execution (batch_id/added_at dynamiques), ce test ne verifie
 * donc PAS un batch_id exact, seulement sa forme et la structure des 9 495 lignes.
 *
 * CORRECTIF C-1 (2026-08-19, audit seo-technical-auditor consolide sur D-035/D-036, bloquant) :
 * ce lot comptait a tort 10 150 lignes -- 227 d'entre elles etaient des doublons de contenu
 * avec leur page parente SANS lettre "avec" (meme compte exact dans list_counts, 'start_end_with'
 * vs 'start_end'), jamais detectes faute du meme controle deja applique au cas
 * 'combined_with_length' (D-027). Preuve concrete de l'audit, reproduite ici : F:Q (FAQ, 1 mot)
 * + avec/a, et X:O (XIPHO, 1 mot) + avec/{h,i,p}, listaient le meme contenu que leur page
 * parente /mots/commencant/{X}/terminant/{Y} deja indexee, sans canonical desingant un gagnant.
 * 9 923 = 10 150 - 227.
 *
 * CORRECTIF I-A (2026-08-19, 2e audit seo-technical-auditor sur D-037, non bloquant) : le
 * controle C-1 ci-dessus ne compare une ligne "avec" qu'A SA PROPRE page parente (verticale) --
 * jamais aux AUTRES lettres "avec" du meme parent ENTRE ELLES (horizontale, "soeurs"). Exemple
 * cite par l'audit, confirme : paire X:M -- XALAM derriere avec/a ET avec/l (meme 1 mot),
 * XENODOCHIUM derriere 8 lettres distinctes c/d/e/h/i/n/o/u (meme 1 mot). 283 groupes de
 * doublons soeurs trouves sur 564 paires ayant >= 2 lettres avec en registre, 428 lignes
 * exclues (la lettre alphabetiquement la plus petite de chaque groupe reste seule candidate) --
 * verifie par 3 methodes independantes (0 divergence). 9 495 = 9 923 - 428.
 *
 * CORRECTIF C-2 (2026-08-19, 3e audit seo-technical-auditor consolide de la serie, bloquant) :
 * ni C-1 ni I-A ne comparaient jamais une tranche AXE 2 (lettre "avec", ce cas) a une tranche
 * SOEUR de la famille AXE 1 (longueur, Family::WORD_LIST_COMBINED variante avec longueur, cas
 * 'combined_with_length', D-027/D-035) du MEME panier commencant+terminant. Exemple cite par
 * l'audit, confirme : paire X:M -- /mots/5-lettres/commencant/x/terminant/m (axe 1) ET
 * /mots/commencant/x/terminant/m/avec/a (axe 2, gagnant I-A du groupe {a,l}) listent EXACTEMENT
 * le meme contenu ({XALAM}) ; /mots/11-lettres/commencant/x/terminant/m ET .../avec/c (gagnant
 * I-A du groupe a 8 lettres) listent EXACTEMENT {XENODOCHIUM}. Regle de priorite (coherente
 * D-025, la forme la plus simple/generale gagne) : la tranche longueur reste candidate, la
 * tranche "avec" est exclue. 333 collisions trouvees sur 191 paires distinctes (191/611, pas un
 * echantillon -- l'audit n'avait lui-meme sonde que 9 paires a 5 lettres), verifiees par 2
 * methodes independantes (0 divergence). 9 162 = 9 495 - 333, verifie ci-dessous. Les deux
 * tranches X:M/avec/a et X:M/avec/c, precedemment GARDEES par I-A, sont donc desormais EXCLUES
 * par ce correctif -- assertions inversees ci-dessous par rapport a la version precedente de ce
 * test.
 *
 * CORRECTIF (D-041, 2026-08-21, isD041Excluded()) : 9 162 -> 8 848 lignes (314 exclusions
 * supplementaires) -- detecteur generique scripts/check_combinatorial_duplicates.php (balayage
 * TOUTES familles combinatoires, plus seulement des paires connues a l'avance), regle de priorite
 * resolveDuplicateWinner() (scripts/lib/seo_duplicate_priority.php). Ces lignes perdent desormais
 * contre une autre famille combinatoire (ex. word_list_avec_three_letters, word_list_terminant sur
 * le meme panier) avec MOINS de composants -- au-dela des controles C-1/I-A/C-2 deja appliques
 * ci-dessus, qui ne comparaient jamais que des paires de familles connues a l'avance.
 */
return function (): void {
    $root = __DIR__ . '/../..';
    $dictPath = $root . '/storage/dictionary_fr.sqlite';
    Assert::true(is_file($dictPath), 'base manquante : ' . $dictPath);

    $process = proc_open(
        [PHP_BINARY, $root . '/scripts/propose_seo_batch.php', 'combined_with_letter'],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        $root,
    );
    Assert::true($process !== false, 'impossible de lancer propose_seo_batch.php');

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    Assert::same(0, $exitCode, "propose_seo_batch.php combined_with_letter aurait du reussir : {$stderr}");
    Assert::true(str_contains($stderr, '8848 ligne(s) proposee(s)'), $stderr);

    $tmpFile = tempnam(sys_get_temp_dir(), 'combined_with_letter_batch_');
    file_put_contents($tmpFile, $stdout);

    try {
        $batch = require $tmpFile;

        Assert::true(str_starts_with($batch['batch_id'], 'combined_with_letter-proposed-'), 'batch_id dynamique attendu (nouvelle proposition)');
        Assert::same(8_848, count($batch['rows']), '11 348 combinaisons start_end_with a >= 1 resultat moins 1 198 (D-032) moins 227 (C-1) moins 428 (I-A) moins 333 (C-2) moins 314 (D-041, doublons croises avec d\'autres familles combinatoires) = 8 848');

        // --- Forme exacte de la route : commencant + terminant + avec, chacun d'une seule
        // --- lettre, SANS longueur -- et jamais la lettre "avec" degeneree (D-032). ---
        $seen = [];
        $singleResultCount = 0;

        foreach ($batch['rows'] as $row) {
            Assert::true(preg_match('#^/mots/commencant/([a-z])/terminant/([a-z])/avec/([a-z])\z#', $row['route_path'], $m) === 1, $row['route_path']);
            Assert::true($m[3] !== $m[1] && $m[3] !== $m[2], "lettre avec degeneree (D-032) ne devrait jamais apparaitre : {$row['route_path']}");

            // --- R1-R7 respectees par construction. ---
            Assert::same('word_list_combined_with_letter', $row['family']);
            Assert::same('index,follow', $row['robots']);
            Assert::same($row['route_path'], $row['canonical_path'], 'R3 : jamais d\'alias indexable');
            Assert::same('combined-with-0001', $row['sitemap_fragment']);
            Assert::true($row['result_count'] >= 1, 'R5 : jamais 0 resultat pour une ligne index,follow');
            Assert::true($row['result_count'] <= 10_000, 'plafond ROW_EXAMINATION_CEILING (D-019, regime BORNE des que le suffixe est present)');
            Assert::true(trim($row['notes']) !== '', 'R7 : note de maillage obligatoire');
            Assert::true(!isset($seen[$row['route_path']]), 'R2 : aucun doublon dans le lot');
            $seen[$row['route_path']] = true;

            if ($row['result_count'] === 1) {
                $singleResultCount++;
            }
        }

        Assert::same(448, $singleResultCount, 'pages a exactement 1 resultat parmi les 8 848 maillables (754 avant D-041, moins 306 exclues par D-041), GARDEES (docs/05, jamais sur le seul compteur)');

        // --- Cas connus, verifies contre list_counts (voir le rapport AFTER pour la
        // --- verification independante complete, agent seo-registry). ---
        $byPath = [];

        foreach ($batch['rows'] as $row) {
            $byPath[$row['route_path']] = $row;
        }

        Assert::true(isset($byPath['/mots/commencant/a/terminant/a/avec/b']));
        Assert::true(!isset($byPath['/mots/commencant/a/terminant/a/avec/a']), 'lettre avec = debut ET fin, degeneree, jamais proposee');
        Assert::true(isset($byPath['/mots/commencant/r/terminant/s/avec/e']));
        Assert::same(10_000, $byPath['/mots/commencant/r/terminant/s/avec/e']['result_count'], 'plafonne (ROW_EXAMINATION_CEILING, D-019 -- 82 517 mots reels)');

        // --- Regression C-1 (audit consolide, bloquant) : exemples exacts fournis par l'audit,
        // --- reproduits independamment -- une seule page (FAQ) derriere commencant/f/terminant/q,
        // --- qui contient deja un A ; trois lettres (h,i,p) toutes deja presentes dans XIPHO,
        // --- seul mot derriere commencant/x/terminant/o. Ces routes ne doivent JAMAIS etre
        // --- proposees : meme contenu que leur page parente deja indexee, sans canonical
        // --- designant un gagnant. ---
        Assert::true(!isset($byPath['/mots/commencant/f/terminant/q/avec/a']), 'C-1 : FAQ, doublon de contenu avec /mots/commencant/f/terminant/q');
        Assert::true(!isset($byPath['/mots/commencant/x/terminant/o/avec/h']), 'C-1 : XIPHO, doublon de contenu avec /mots/commencant/x/terminant/o');
        Assert::true(!isset($byPath['/mots/commencant/x/terminant/o/avec/i']), 'C-1 : XIPHO, doublon de contenu avec /mots/commencant/x/terminant/o');
        Assert::true(!isset($byPath['/mots/commencant/x/terminant/o/avec/p']), 'C-1 : XIPHO, doublon de contenu avec /mots/commencant/x/terminant/o');

        // --- Regression I-A (2e audit, non bloquant) : exemple exact cite par l'audit, reproduit
        // --- independamment -- paire X:M, deux groupes de lettres soeurs designant EXACTEMENT le
        // --- meme mot chacun (XALAM derriere avec/a et avec/l ; XENODOCHIUM derriere 8 lettres
        // --- distinctes). Seule la lettre alphabetiquement la plus petite de chaque groupe doit
        // --- rester candidate apres I-A -- toutes les autres sont des doublons de contenu
        // --- SOEURS, jamais proposees. avec/l et les 7 lettres perdantes de l'autre groupe
        // --- restent exclues quelle que soit la suite (C-2 ci-dessous ne les concerne pas, elles
        // --- sont deja hors course avant meme d'atteindre ce controle). ---
        Assert::true(!isset($byPath['/mots/commencant/x/terminant/m/avec/l']), 'I-A : XALAM, doublon de contenu avec avec/a (meme mot, meme paire X:M)');
        foreach (['d', 'e', 'h', 'i', 'n', 'o', 'u'] as $loserLetter) {
            Assert::true(!isset($byPath["/mots/commencant/x/terminant/m/avec/{$loserLetter}"]), "I-A : XENODOCHIUM, doublon de contenu avec avec/c (lettre {$loserLetter}, meme mot, meme paire X:M)");
        }

        // --- Regression C-2 (3e audit consolide, bloquant) : exemple exact cite par l'audit,
        // --- reproduit independamment -- les DEUX gagnants I-A ci-dessus (avec/a, avec/c) sont
        // --- CHACUN un doublon de contenu avec une tranche longueur SOEUR de la famille
        // --- Family::WORD_LIST_COMBINED (meme panier X:M) : avec/a == 5-lettres/commencant/x/
        // --- terminant/m (XALAM), avec/c == 11-lettres/commencant/x/terminant/m (XENODOCHIUM).
        // --- Les deux doivent desormais etre EXCLUS -- paire X:M n'a donc plus AUCUNE tranche
        // --- "avec" candidate apres ce correctif (0/2, contrairement a I-A ou 2/10 survivaient).
        // --- ---
        Assert::true(!isset($byPath['/mots/commencant/x/terminant/m/avec/a']), 'C-2 : XALAM, doublon de contenu avec la tranche longueur soeur /mots/5-lettres/commencant/x/terminant/m');
        Assert::true(!isset($byPath['/mots/commencant/x/terminant/m/avec/c']), 'C-2 : XENODOCHIUM, doublon de contenu avec la tranche longueur soeur /mots/11-lettres/commencant/x/terminant/m');

        // --- Invariant general I-A, recalcule INDEPENDAMMENT (methode par empreinte SQL
        // --- GROUP_CONCAT+sha1 -- structurellement differente du filtre PHP sur panier en cache
        // --- utilise par findSiblingContentDuplicates() dans le script sous test, meme
        // --- discipline de verification croisee que D-037) : pour TOUTES les paires du lot,
        // --- aucune lettre "avec" ne doit designer EXACTEMENT le meme ensemble de mots qu'une
        // --- autre lettre "avec" soeur du meme parent -- pas seulement X:M ci-dessus. ---
        $lettersByPairForCheck = [];
        foreach ($batch['rows'] as $row) {
            if (preg_match('#^/mots/commencant/([a-z])/terminant/([a-z])/avec/([a-z])\z#', $row['route_path'], $mm) === 1) {
                $lettersByPairForCheck[$mm[1] . ':' . $mm[2]][] = $mm[3];
            }
        }

        $dictPdo = new PDO('sqlite:' . $dictPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $dictPdo->exec('PRAGMA query_only = ON');

        $fingerprintStmt = $dictPdo->prepare(
            "SELECT group_concat(normalized, '|') AS fp
             FROM (SELECT normalized FROM terms WHERE substr(normalized,1,1) = ? AND substr(reversed,1,1) = ? AND instr(normalized, ?) > 0 ORDER BY normalized)"
        );

        $remainingSiblingDuplicates = 0;

        foreach ($lettersByPairForCheck as $pairKey => $letters) {
            if (count($letters) < 2) {
                continue;
            }

            [$pairStart, $pairEnd] = explode(':', $pairKey);
            $seenFingerprints = [];

            foreach ($letters as $letter) {
                $fingerprintStmt->execute([strtoupper($pairStart), strtoupper($pairEnd), strtoupper($letter)]);
                $hash = sha1((string) $fingerprintStmt->fetch()['fp']);

                if (isset($seenFingerprints[$hash])) {
                    $remainingSiblingDuplicates++;
                }

                $seenFingerprints[$hash] = true;
            }
        }

        Assert::same(0, $remainingSiblingDuplicates, 'I-A : aucune lettre avec ne devrait designer le meme ensemble de mots qu une lettre soeur du meme parent, recalcule independamment sur les ' . count($lettersByPairForCheck) . ' paires du lot corrige');

        // --- Invariant general C-2, recalcule INDEPENDAMMENT (methode par empreinte SQL
        // --- GROUP_CONCAT+sha1 sur la LONGUEUR plutot que sur une seconde lettre "avec" --
        // --- structurellement differente de findLengthAvecContentCollisions() dans le script sous
        // --- test, meme discipline de verification croisee que I-A ci-dessus) : pour TOUTES les
        // --- paires du lot final, aucune lettre "avec" restante ne doit designer EXACTEMENT le
        // --- meme ensemble de mots qu'une tranche longueur du MEME panier (Family::
        // --- WORD_LIST_COMBINED, variante avec longueur, deja indexee/appliquee, D-027/D-035) --
        // --- pas seulement X:M ci-dessus. Lit storage/seo_fr.sqlite pour les tranches longueur
        // --- deja appliquees (source independante du calcul list_counts fait par le script sous
        // --- test) -- si le registre reel n'est pas accessible (environnement de test isole),
        // --- ce bloc est ignore plutot que de faire echouer le test sur une dependance externe. ---
        $seoDbPath = getenv('SCRABBLE_SEO_DB_PATH') ?: ($root . '/storage/seo_fr.sqlite');

        if (is_file($seoDbPath)) {
            $seoPdo = new PDO('sqlite:' . $seoDbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $seoPdo->exec('PRAGMA query_only = ON');

            $lengthsByPairForCheck = [];

            foreach (
                $seoPdo->query(
                    "SELECT route_path FROM registry WHERE family = 'word_list_combined' "
                    . "AND route_path LIKE '/mots/%-lettres/commencant/%/terminant/%'"
                )->fetchAll() as $row
            ) {
                if (preg_match('#^/mots/(\d{1,2})-lettres/commencant/([a-z])/terminant/([a-z])\z#', (string) $row['route_path'], $lm) === 1) {
                    $lengthsByPairForCheck[$lm[2] . ':' . $lm[3]][] = (int) $lm[1];
                }
            }

            $lengthFingerprintStmt = $dictPdo->prepare(
                "SELECT group_concat(normalized, '|') AS fp
                 FROM (SELECT normalized FROM terms WHERE substr(normalized,1,1) = ? AND substr(reversed,1,1) = ? AND length = ? ORDER BY normalized)"
            );

            $remainingCrossDuplicates = 0;
            $pairsChecked = 0;

            foreach ($lettersByPairForCheck as $pairKey => $letters) {
                $lengths = $lengthsByPairForCheck[$pairKey] ?? [];

                if ($lengths === []) {
                    continue;
                }

                $pairsChecked++;
                [$pairStart, $pairEnd] = explode(':', $pairKey);

                $lengthHashes = [];

                foreach ($lengths as $length) {
                    $lengthFingerprintStmt->execute([strtoupper($pairStart), strtoupper($pairEnd), $length]);
                    $lengthHashes[sha1((string) $lengthFingerprintStmt->fetch()['fp'])] = true;
                }

                foreach ($letters as $letter) {
                    $fingerprintStmt->execute([strtoupper($pairStart), strtoupper($pairEnd), strtoupper($letter)]);
                    $hash = sha1((string) $fingerprintStmt->fetch()['fp']);

                    if (isset($lengthHashes[$hash])) {
                        $remainingCrossDuplicates++;
                    }
                }
            }

            Assert::same(0, $remainingCrossDuplicates, 'C-2 : aucune lettre avec restante ne devrait designer le meme ensemble de mots qu une tranche longueur soeur (Family::WORD_LIST_COMBINED), recalcule independamment (empreinte SQL) sur les ' . $pairsChecked . ' paires du lot corrige ayant a la fois des tranches longueur et des lettres avec');
        }
    } finally {
        unlink($tmpFile);
    }
};
