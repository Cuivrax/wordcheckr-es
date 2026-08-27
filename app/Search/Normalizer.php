<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Reimplementation stricte de scripts/lib/normalize.py (D-009).
 *
 * scripts/lib/normalize.py est la source unique de la regle de normalisation ; cette
 * classe doit produire EXACTEMENT les memes sorties. Tout ecart est un bug de
 * correspondance, pas une variante -- verifie par :
 * - tests/Search/NormalizerTest.php contre tests/fixtures/normalize_samples.json,
 *   genere depuis normalize.py par scripts/build_normalize_fixture.py ;
 * - tests/Search/TermLookupTest.php, qui recalcule score/signature/reversed/length
 *   pour les 838 180 lignes reelles de storage/dictionary_fr.sqlite.
 *
 * Seul ecart assume et documente : score() recoit sa table de points en parametre
 * plutot qu'en constante de classe. app/Search/ est du code partage entre sites
 * (docs/02_ARCHITECTURE_DATA_MULTILINGUE.md), alors que scripts/lib/normalize.py est un
 * script par langue -- un futur site anglais aurait des valeurs de tuiles differentes
 * sans que cette classe change. Les VALEURS francaises restent identiques
 * (config/sites/fr.php) ; seule l'organisation du code differe, pas la regle.
 */
final class Normalizer
{
    /**
     * Les ligatures ne sont PAS decomposees par la forme normale NFD. Sans ce mappage
     * explicite, "oeil", "boeuf" et "noeud" seraient rejetes comme hors A-Z alors
     * qu'OEIL, BOEUF et OEUF sont des mots admis a l'ODS8.
     */
    private const LIGATURES = [
        "\u{0153}" => 'oe', // œ
        "\u{0152}" => 'OE', // Œ
        "\u{00e6}" => 'ae', // æ
        "\u{00c6}" => 'AE', // Æ
    ];

    /**
     * Le plateau fait 15 cases : un mot de plus de 15 lettres ne peut jamais etre pose.
     * Plafond applique aux donnees, pas seulement a la saisie (D-010, revisee).
     */
    public const MIN_LENGTH = 2;
    public const MAX_LENGTH = 15;

    // \z (pas $) : $ accepte un \n final en PCRE, ce qui admettrait a tort
    // "POSER\n" comme terme valide (audit Phase 1, C2). \z ancre strictement la fin
    // de la chaine, sans exception pour un saut de ligne terminal.
    private const VALID_PATTERN = '/^[A-Z]{' . self::MIN_LENGTH . ',' . self::MAX_LENGTH . '}\z/';

    /**
     * Ligatures, puis NFD, puis retrait des diacritiques (categorie Unicode Mn), puis
     * majuscules.
     *
     * Ne valide pas : renvoie la forme normalisee telle quelle, eventuellement
     * invalide. Utiliser isValid() pour trancher -- une entree qui n'est pas de
     * l'UTF-8 valide, ou que \Normalizer::normalize() refuse de decomposer, renvoie
     * une chaine vide, qui echoue toujours isValid() (audit Phase 1, C1). Ne leve
     * jamais d'exception : find() doit pouvoir traiter toute entree utilisateur sans
     * jamais laisser remonter une erreur au flux HTTP normal.
     */
    public static function normalize(string $form): string
    {
        if (!mb_check_encoding($form, 'UTF-8')) {
            return '';
        }

        $form = strtr($form, self::LIGATURES);
        $decomposed = \Normalizer::normalize($form, \Normalizer::FORM_D);

        if ($decomposed === false) {
            // \Normalizer::normalize() peut renvoyer false sur une sequence que
            // mb_check_encoding() n'aurait pas rejetee (ex. normalisation ICU
            // refusee) -- meme traitement : jamais un terme valide.
            return '';
        }

        $stripped = preg_replace('/\p{Mn}/u', '', $decomposed);
        $stripped ??= $decomposed;

        return mb_strtoupper($stripped, 'UTF-8');
    }

    /** Un terme retenu ne contient que des A-Z et fait de 2 a 15 lettres. */
    public static function isValid(string $normalized): bool
    {
        return preg_match(self::VALID_PATTERN, $normalized) === 1;
    }

    /**
     * Score brut, hors bonus de plateau. La somme des tuiles affichees doit toujours
     * etre egale a cette valeur.
     *
     * Defense en profondeur (audit Phase 1, C2) : une lettre absente de $tileScores
     * ne doit jamais produire un total silencieusement faux (avertissement PHP +
     * addition avec null) -- leve une exception explicite, rattrapee en amont par le
     * gestionnaire global (app/bootstrap.php) plutot que de fuiter dans la reponse.
     * Ne devrait jamais se produire pour un $normalized valide (isValid() garantit
     * des lettres A-Z, toutes presentes dans config/sites/fr.php) : signale donc une
     * incoherence interne, pas une erreur de saisie utilisateur.
     *
     * @param array<string, int> $tileScores
     */
    public static function score(string $normalized, array $tileScores): int
    {
        $total = 0;

        foreach (str_split($normalized) as $letter) {
            if (!array_key_exists($letter, $tileScores)) {
                throw new \InvalidArgumentException(sprintf('Lettre sans valeur de tuile : %s', $letter));
            }

            $total += $tileScores[$letter];
        }

        return $total;
    }

    /** Lettres triees : deux anagrammes partagent la meme signature. */
    public static function signature(string $normalized): string
    {
        $letters = str_split($normalized);
        sort($letters, SORT_STRING);

        return implode('', $letters);
    }

    /** Terme inverse : permet de traiter un suffixe comme un prefixe indexe. */
    public static function reverse(string $normalized): string
    {
        return strrev($normalized);
    }
}
