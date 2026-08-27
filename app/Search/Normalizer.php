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
     * NFC prealable (recomposition), puis protection de Ñ, puis NFD, puis retrait des
     * diacritiques (categorie Unicode Mn), puis majuscules.
     *
     * Ne valide pas : renvoie la forme normalisee telle quelle, eventuellement
     * invalide. Utiliser isValid() pour trancher -- une entree qui n'est pas de
     * l'UTF-8 valide, ou que \Normalizer::normalize() refuse de decomposer, renvoie
     * une chaine vide, qui echoue toujours isValid() (audit Phase 1 du site francais,
     * C1). Ne leve jamais d'exception : find() doit pouvoir traiter toute entree
     * utilisateur sans jamais laisser remonter une erreur au flux HTTP normal.
     *
     * NFC prealable : necessaire car str_replace(['Ñ','ñ'], ...) ne reconnait que la
     * forme PRECOMPOSEE (Ñ = U+00D1, un seul point de code). Une entree deja DECOMPOSEE
     * (N + tilde combinant U+0303, deux points de code distincts -- possible si un
     * client HTTP envoie du NFD, rare mais reel) contournerait sans cette etape la
     * protection ENYE_SENTINEL : la sentinelle ne matcherait jamais, puis le retrait
     * des marques Mn supprimerait le tilde combinant et perdrait silencieusement le Ñ
     * (bug reel trouve et corrige avant tout import : verifie qu'une entree "n" +
     * U+0303 + "o" se normalisait a tort en "NO" au lieu de "ÑO"). La recomposition NFC
     * fusionne systematiquement N + tilde combinant en Ñ avant que la sentinelle ne
     * s'applique, quelle que soit la forme Unicode de l'entree.
     */
    public static function normalize(string $form): string
    {
        if (!mb_check_encoding($form, 'UTF-8')) {
            return '';
        }

        $composed = \Normalizer::normalize($form, \Normalizer::FORM_C);
        $form = $composed === false ? $form : $composed;

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
     * Tuiles digrammes dediees : CH, LL, RR sont des tuiles PHYSIQUES a part entiere du
     * jeu Scrabble espagnol (edition internationale/europeenne, 100 fiches, decision
     * produit confirmee -- regle FISE explicite : "il est interdit de composer CH/LL/RR
     * a partir de deux tuiles separees"). Ñ reste une lettre simple normale, aucun
     * traitement de tokenisation supplementaire au-dela de normalize() ci-dessus.
     *
     * Reimplementation stricte de DIGRAPH_TILES dans scripts/lib/normalize.py.
     */
    private const DIGRAPH_TILES = ['CH', 'LL', 'RR'];

    /**
     * Alphabet complet des tuiles espagnoles (26 lettres simples + Ñ + les 3 digrammes
     * dedies) -- utilise par App\Search\RackSolver pour enumerer les remplissages
     * possibles d'un joker (un blanc peut representer n'importe quelle tuile du jeu, y
     * compris une tuile digramme -- regle generale du blanc Scrabble, aucune exception
     * documentee pour les digrammes espagnols specifiquement).
     *
     * @var list<string>
     */
    public const ALL_TILES = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Ñ', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'CH', 'LL', 'RR',
    ];

    /**
     * Separateur utilise par signature()/signatureFromTiles() pour joindre les tuiles
     * triees. Aucune tuile ne contient de point : elimine par construction toute
     * collision entre un mot ou C et H apparaissent comme deux tuiles simples SEPAREES
     * (non adjacentes) et un mot qui contient la tuile CH dediee -- sans separateur, les
     * deux produiraient la meme sous-chaine concatenee "CH" une fois triees.
     */
    private const SIGNATURE_TILE_SEPARATOR = '.';

    /**
     * Decoupe une forme normalisee en tuiles Scrabble espagnoles (reimplementation
     * stricte de tokenize_tiles() dans scripts/lib/normalize.py).
     *
     * Correspondance gloutonne de gauche a droite, mb-safe (Ñ occupe 2 octets en UTF-8,
     * mb_str_split() la traite comme UN seul caractere -- voir score()/reverse()
     * ci-dessous pour la meme necessite).
     *
     * @return list<string>
     */
    public static function tokenizeTiles(string $normalized): array
    {
        $characters = mb_str_split($normalized, 1, 'UTF-8');
        $tiles = [];
        $count = count($characters);
        $i = 0;

        while ($i < $count) {
            $pair = $i + 1 < $count ? $characters[$i] . $characters[$i + 1] : '';

            if (in_array($pair, self::DIGRAPH_TILES, true)) {
                $tiles[] = $pair;
                $i += 2;
            } else {
                $tiles[] = $characters[$i];
                $i += 1;
            }
        }

        return $tiles;
    }

    /**
     * Score brut, hors bonus de plateau. La somme des tuiles affichees doit toujours
     * etre egale a cette valeur -- une tuile CH/LL/RR compte pour SA valeur propre, pas
     * la somme de ses deux lettres (ex. "COCHE" = C + O + CH + E = 3+1+5+1 = 10, PAS
     * C+O+C+H+E = 3+1+3+4+1 = 12).
     *
     * Defense en profondeur (audit Phase 1 du site francais, C2) : une tuile absente de
     * $tileScores ne doit jamais produire un total silencieusement faux (avertissement
     * PHP + addition avec null) -- leve une exception explicite, rattrapee en amont par
     * le gestionnaire global (app/bootstrap.php) plutot que de fuiter dans la reponse.
     *
     * @param array<string, int> $tileScores
     */
    public static function score(string $normalized, array $tileScores): int
    {
        $total = 0;

        foreach (self::tokenizeTiles($normalized) as $tile) {
            if (!array_key_exists($tile, $tileScores)) {
                throw new \InvalidArgumentException(sprintf('Tuile sans valeur : %s', $tile));
            }

            $total += $tileScores[$tile];
        }

        return $total;
    }

    /**
     * Tuiles triees, jointes par SIGNATURE_TILE_SEPARATOR : deux mots sont des
     * anagrammes AU SENS DES TUILES SCRABBLE s'ils partagent la meme signature (meme
     * multiensemble de tuiles physiques, pas seulement de lettres -- voir
     * SIGNATURE_TILE_SEPARATOR ci-dessus pour la raison du separateur, et
     * scripts/lib/normalize.py::signature() pour la meme regle cote build).
     */
    public static function signature(string $normalized): string
    {
        return self::signatureFromTiles(self::tokenizeTiles($normalized));
    }

    /**
     * Meme regle que signature() ci-dessus, mais a partir d'une liste de tuiles DEJA
     * CONNUE (pas retokenisee depuis un texte) -- utilise par App\Search\RackSolver
     * pour construire des signatures candidates a partir de combinaisons de tuiles de
     * chevalet, sans repasser par normalize()/tokenizeTiles(). Source UNIQUE du
     * separateur : RackSolver ne doit jamais coder ce caractere lui-meme.
     *
     * @param list<string> $tiles
     */
    public static function signatureFromTiles(array $tiles): string
    {
        sort($tiles, SORT_STRING);

        return implode(self::SIGNATURE_TILE_SEPARATOR, $tiles);
    }

    /**
     * Terme inverse : permet de traiter un suffixe comme un prefixe indexe.
     *
     * Reste au niveau du CARACTERE, pas de la tuile -- "terminer par -CION" est une
     * recherche de suite de lettres dans le mot ecrit, pas une recherche de tuile
     * physique (meme choix que reverse() dans scripts/lib/normalize.py).
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
