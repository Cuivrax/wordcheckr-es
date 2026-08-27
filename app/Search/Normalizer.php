<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Reimplementation stricte de scripts/lib/normalize.py (site espagnol, adapte de D-009
 * du site francais).
 *
 * scripts/lib/normalize.py est la source unique de la regle de normalisation ; cette
 * classe doit produire EXACTEMENT les memes sorties. Tout ecart est un bug de
 * correspondance, pas une variante -- verifie par :
 * - tests/Search/NormalizerTest.php contre tests/fixtures/normalize_samples.json,
 *   genere depuis normalize.py par scripts/build_normalize_fixture.py ;
 * - tests/Search/TermLookupTest.php, qui recalcule score/signature/reversed/length
 *   pour les lignes reelles de storage/dictionary_es.sqlite.
 *
 * Seul ecart assume et documente : score() recoit sa table de points en parametre
 * plutot qu'en constante de classe. app/Search/ est du code partage entre sites
 * (docs/02_ARCHITECTURE_DATA_MULTILINGUE.md), alors que scripts/lib/normalize.py est un
 * script par langue -- un futur site pourrait avoir des valeurs de tuiles differentes
 * sans que cette classe change. Les VALEURS espagnoles restent identiques
 * (config/sites/es.php) ; seule l'organisation du code differe, pas la regle.
 */
final class Normalizer
{
    /**
     * Ñ n'est PAS un "N accentue" : lettre a part entiere de l'alphabet espagnol, avec
     * sa propre valeur de tuile Scrabble (8 points, config/sites/es.php). La forme
     * normale NFD la decompose pourtant en N (U+006E) + tilde combinant (U+0303,
     * categorie Unicode Mn) -- un retrait naif des marques Mn fusionnerait donc "año"
     * (annee) et "ano" (anus) en une seule forme normalisee, confirme sur les donnees
     * reelles du lexique source (data/raw/PROVENANCE.md) : "ano"/"anos"/"año"/"años"
     * sont bien QUATRE entrees distinctes.
     *
     * Protection : Ñ/ñ sont substitues par une sentinelle de la zone d'usage prive
     * Unicode AVANT la decomposition NFD (jamais vue par NFD), puis restitues apres
     * la mise en majuscules. Meme principe que la protection des ligatures francaises
     * (œ -> oe AVANT NFD) du site francais, adapte ici a un besoin different
     * (preserver une lettre telle quelle, pas la developper en deux lettres).
     */
    private const ENYE_SENTINEL = "\u{E000}";

    /**
     * Le plateau fait 15 cases : un mot de plus de 15 lettres ne peut jamais etre pose.
     * Plafond applique aux donnees, pas seulement a la saisie -- meme borne que le site
     * francais (D-010), confirmee adaptee a l'espagnol : le Lexicon FISE 2 est
     * explicitement documente comme couvrant "de deux a quinze lettres".
     */
    public const MIN_LENGTH = 2;
    public const MAX_LENGTH = 15;

    // \z (pas $) : $ accepte un \n final en PCRE, ce qui admettrait a tort
    // "POSER\n" comme terme valide (audit Phase 1 du site francais, C2). \z ancre
    // strictement la fin de la chaine, sans exception pour un saut de ligne terminal.
    // /u (unicode) : necessaire pour que Ñ (multi-octet en UTF-8) soit traite comme UN
    // seul caractere de la classe, pas comme deux octets independants.
    private const VALID_PATTERN = '/^[A-ZÑ]{' . self::MIN_LENGTH . ',' . self::MAX_LENGTH . '}\z/u';

    /**
     * Protection de Ñ, puis NFD, puis retrait des diacritiques (categorie Unicode Mn),
     * puis majuscules.
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

        $form = str_replace(['Ñ', 'ñ'], self::ENYE_SENTINEL, $form);
        $decomposed = \Normalizer::normalize($form, \Normalizer::FORM_D);

        if ($decomposed === false) {
            // \Normalizer::normalize() peut renvoyer false sur une sequence que
            // mb_check_encoding() n'aurait pas rejetee (ex. normalisation ICU
            // refusee) -- meme traitement : jamais un terme valide.
            return '';
        }

        $stripped = preg_replace('/\p{Mn}/u', '', $decomposed);
        $stripped ??= $decomposed;

        $upper = mb_strtoupper($stripped, 'UTF-8');

        return str_replace(self::ENYE_SENTINEL, 'Ñ', $upper);
    }

    /** Un terme retenu ne contient que des A-Z/Ñ et fait de 2 a 15 lettres. */
    public static function isValid(string $normalized): bool
    {
        return preg_match(self::VALID_PATTERN, $normalized) === 1;
    }

    /**
     * Score brut, hors bonus de plateau. La somme des tuiles affichees doit toujours
     * etre egale a cette valeur.
     *
     * Defense en profondeur (audit Phase 1 du site francais, C2) : une lettre absente
     * de $tileScores ne doit jamais produire un total silencieusement faux
     * (avertissement PHP + addition avec null) -- leve une exception explicite,
     * rattrapee en amont par le gestionnaire global (app/bootstrap.php) plutot que de
     * fuiter dans la reponse. Ne devrait jamais se produire pour un $normalized valide
     * (isValid() garantit des lettres A-Z/Ñ, toutes presentes dans config/sites/es.php)
     * : signale donc une incoherence interne, pas une erreur de saisie utilisateur.
     *
     * mb_str_split(), pas str_split() : Ñ occupe 2 octets en UTF-8 -- str_split()
     * (BYTE par BYTE) couperait Ñ en deux "lettres" invalides et casserait le score,
     * la signature et l'inversion de tout mot qui la contient (bug reel trouve et
     * corrige pendant l'adaptation espagnole de ce fichier, avant tout import).
     *
     * @param array<string, int> $tileScores
     */
    public static function score(string $normalized, array $tileScores): int
    {
        $total = 0;

        foreach (mb_str_split($normalized, 1, 'UTF-8') as $letter) {
            if (!array_key_exists($letter, $tileScores)) {
                throw new \InvalidArgumentException(sprintf('Lettre sans valeur de tuile : %s', $letter));
            }

            $total += $tileScores[$letter];
        }

        return $total;
    }

    /** Lettres triees : deux anagrammes partagent la meme signature. Mb-safe (Ñ). */
    public static function signature(string $normalized): string
    {
        $letters = mb_str_split($normalized, 1, 'UTF-8');
        sort($letters, SORT_STRING);

        return implode('', $letters);
    }

    /**
     * Terme inverse : permet de traiter un suffixe comme un prefixe indexe.
     *
     * strrev() est BYTE par BYTE : applique tel quel a une chaine contenant Ñ
     * (multi-octet UTF-8), il inverserait l'ORDRE DES OCTETS de Ñ lui-meme et
     * produirait une sequence UTF-8 invalide, pas seulement une lettre a la mauvaise
     * place. mb_str_split() + array_reverse() inverse les CARACTERES, jamais leurs
     * octets internes.
     */
    public static function reverse(string $normalized): string
    {
        return implode('', array_reverse(mb_str_split($normalized, 1, 'UTF-8')));
    }
}
