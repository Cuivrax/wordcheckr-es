<?php

declare(strict_types=1);

use App\Search\Normalizer;
use Tests\Support\Assert;

/**
 * Compare App\Search\Normalizer a scripts/lib/normalize.py (site espagnol) sur un
 * echantillon de cas adversariaux, genere depuis le script Python par
 * scripts/build_normalize_fixture.py -- la fixture est committee, ce test n'invoque
 * jamais Python (D-007 : la couche runtime PHP reste independante).
 */
return function (): void {
    $fixturePath = __DIR__ . '/../fixtures/normalize_samples.json';

    Assert::true(
        is_file($fixturePath),
        'fixture manquante : ' . $fixturePath . ' -- lancer python scripts/build_normalize_fixture.py'
    );

    $cases = json_decode((string) file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
    Assert::true(count($cases) > 0, 'fixture vide');

    $siteConfig = require __DIR__ . '/../../config/sites/es.php';
    $tileScores = $siteConfig['tile_scores'];

    foreach ($cases as $case) {
        $raw = $case['raw'];
        $normalized = Normalizer::normalize($raw);

        Assert::same($case['normalized'], $normalized, 'normalize(' . json_encode($raw) . ')');

        $valid = Normalizer::isValid($normalized);
        Assert::same($case['valid'], $valid, 'isValid(' . json_encode($normalized) . ')');

        if ($valid) {
            Assert::same(
                $case['score'],
                Normalizer::score($normalized, $tileScores),
                'score(' . $normalized . ')'
            );
            Assert::same(
                $case['signature'],
                Normalizer::signature($normalized),
                'signature(' . $normalized . ')'
            );
            Assert::same(
                $case['reversed'],
                Normalizer::reverse($normalized),
                'reverse(' . $normalized . ')'
            );
        }
    }

    // Regression C1 (audit Phase 1) : des octets UTF-8 invalides (ex. 0xFF 0xFE, tels
    // que produits par /verifier?mot=%FF%FE) ne doivent jamais faire planter
    // normalize(). Avant le correctif, \Normalizer::normalize() renvoyait false,
    // transmis tel quel a preg_replace() sous strict_types -> TypeError non rattrapee.
    $invalidUtf8 = "\xFF\xFE";
    $normalizedInvalid = Normalizer::normalize($invalidUtf8);
    Assert::same('', $normalizedInvalid, 'normalize() sur des octets UTF-8 invalides doit rester une chaine vide');
    Assert::true(!Normalizer::isValid($normalizedInvalid), 'des octets UTF-8 invalides ne doivent jamais etre valides');

    // Regression C2 (audit Phase 1) : un saut de ligne ne doit jamais rendre un terme
    // valide. Avant le correctif, VALID_PATTERN ancrait avec $ (qui autorise un \n
    // final en PCRE), acceptant a tort "POSER\n" comme si c'etait "POSER".
    Assert::true(!Normalizer::isValid('POSER' . "\n"), 'POSER suivi d\'un saut de ligne doit rester invalide');
    Assert::true(!Normalizer::isValid("\n" . 'POSER'), 'un saut de ligne en tete doit rester invalide');
    Assert::true(Normalizer::isValid('POSER'), 'POSER seul doit rester valide (non-regression du correctif \\z)');

    // Regression specifique espagnole (adaptation de ce fichier) : Ñ n'est PAS un N
    // accentue. Une decomposition NFD naive fusionnerait "año" (annee) et "ano"
    // (anus) -- confirme comme un vrai defaut potentiel avant l'ajout de
    // ENYE_SENTINEL, voir Normalizer::normalize(). Ces deux mots doivent rester
    // strictement distincts a chaque etape (normalisation, score, signature, sens de
    // lecture inverse).
    Assert::same('AÑO', Normalizer::normalize('año'), 'año doit se normaliser en AÑO, pas ANO');
    Assert::same('ANO', Normalizer::normalize('ano'), 'ano doit rester ANO, distinct de AÑO');
    Assert::true(
        Normalizer::normalize('año') !== Normalizer::normalize('ano'),
        'año et ano ne doivent jamais se normaliser vers la meme forme'
    );

    // mb_str_split()/array_reverse(), pas str_split()/strrev() : Ñ occupe 2 octets en
    // UTF-8, une operation BYTE par BYTE la couperait en deux "lettres" invalides et
    // produirait une sequence UTF-8 corrompue en sens inverse (bug reel trouve et
    // corrige avant tout import, voir Normalizer::reverse()/score()/signature()).
    Assert::same('OÑA', Normalizer::reverse('AÑO'), 'reverse(AÑO) doit rester une chaine UTF-8 valide (OÑA)');
    Assert::same('AOÑ', Normalizer::signature('AÑO'), 'signature(AÑO) doit trier Ñ comme UNE lettre, pas deux octets');
    Assert::same(
        10,
        Normalizer::score('AÑO', $tileScores),
        'score(AÑO) = A(1) + Ñ(8) + O(1) = 10 -- Ñ doit compter comme une seule tuile'
    );
};
