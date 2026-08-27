<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Chevalet analyse a partir de l'entree brute de /jugar/{letras} (adapte du site
 * francais, Phase 2 -- /jouer/{lettres}).
 *
 * Reutilise Normalizer::normalize() sur la chaine COMPLETE, jokers inclus : '?' et '*'
 * ne sont ni des ligatures, ni des caracteres diacritiques, et ne sont pas affectes par
 * la mise en majuscule -- ils traversent normalize() sans modification.
 *
 * TUILES DIGRAMMES (CH/LL/RR) -- difference structurelle avec le site francais : le
 * chevalet espagnol se decoupe en TUILES (Normalizer::tokenizeTiles()), pas en
 * caracteres. Un rack tape "coche" represente donc 4 tuiles (C, O, CH, E), pas 5
 * lettres -- meme convention gloutonne de gauche a droite que le score et la
 * signature (Normalizer::score()/signature()), pour que "combien de tuiles dans ce
 * chevalet" et "quels mots ce chevalet peut-il former" restent coherents partout.
 *
 * DEUX MODES DE LECTURE, choisis selon la presence d'un tiret dans la saisie :
 *
 *   SAISIE LIBRE (aucun tiret, ex. "coche" tape au clavier) : tokenisation gloutonne
 *     de gauche a droite (Normalizer::tokenizeTiles()) -- pratique pour un humain, mais
 *     ne peut PAS distinguer un C et un H adjacents comme deux tuiles separees d'une
 *     tuile CH dediee (meme limitation que taper sur un vrai plateau, ou C et H
 *     adjacents DOIVENT venir de la tuile CH dediee -- regle FISE : "il est interdit de
 *     composer CH/LL/RR a partir de deux tuiles separees").
 *   SEGMENTS EXPLICITES (au moins un tiret present, ex. URL canonique "c-o-ch-e") :
 *     chaque segment entre tirets est UNE tuile, prise telle quelle, JAMAIS refusionnee
 *     avec sa voisine -- necessaire pour que la forme canonique soit STABLE au
 *     rechargement. Sans ce second mode, un chevalet a deux tuiles L SEPAREES (ex.
 *     tape "laela", jamais adjacentes dans la saisie -> chevalet {A:2, E:1, L:2})
 *     produirait le slug canonique "a-a-e-l-l" ; si ce tiret etait traite comme un
 *     simple separateur cosmetique retire avant retokenisation, "aaell" se
 *     retokeniserait en {A:2, E:1, LL:1} -- un chevalet DIFFERENT, silencieusement.
 *     Trouve et corrige avant tout deploiement (pas un correctif a posteriori).
 *
 * Bornes (meme plafond que Normalizer::MAX_LENGTH) : de 1 a 15 CASES au total (tuiles
 * connues + jokers, PAS caracteres) -- le sac de Scrabble espagnol (edition
 * internationale/europeenne) ne contient que deux jetons blancs (MAX_JOKERS).
 *
 * Representation canonique d'URL : jokers rendus par '*', jamais '?', tuiles jointes
 * par '-' (voir "SEGMENTS EXPLICITES" ci-dessus pour la raison structurelle, pas
 * seulement cosmetique, de ce separateur).
 */
final class Rack
{
    /** Le sac de Scrabble espagnol (edition internationale/europeenne) ne contient que
     * deux jetons blancs. */
    public const MAX_JOKERS = 2;

    public const MIN_TILES = 1;

    /**
     * @param array<string, int> $letterCounts tuiles (lettres simples A-Z/Ñ ou
     *        digrammes CH/LL/RR) connues avec leur multiplicite, triees par cle
     *        (ordre alphabetique, SORT_STRING) -- nom de propriete CONSERVE tel quel
     *        (pas renomme en tileCounts) : App\View\play.php (hors perimetre de cet
     *        agent) le consomme deja en dur par ce nom pour l'affichage du chevalet ;
     *        un rendu par TUILE (une entree par tuile, y compris CH/LL/RR) y reste
     *        correct sans aucune modification de ce fichier de vue.
     */
    private function __construct(
        public readonly array $letterCounts,
        public readonly int $jokerCount,
        public readonly string $slug,
    ) {
    }

    /**
     * Renvoie null pour toute entree qui n'est pas un chevalet exploitable : forme
     * normalisee vide, segment invalide, hors bornes de taille (en TUILES, pas en
     * caracteres), ou plus de MAX_JOKERS jokers. Aucune exception ne remonte -- meme
     * discipline que Normalizer::normalize() (audit Phase 1 du site francais, C1) :
     * une entree utilisateur ne doit jamais faire planter le flux HTTP normal.
     */
    public static function fromInput(string $rawInput): ?self
    {
        $normalized = Normalizer::normalize($rawInput);

        if ($normalized === '') {
            return null;
        }

        $jokerCount = substr_count($normalized, '?') + substr_count($normalized, '*');
        $withoutJokers = str_replace(['?', '*'], '', $normalized);

        $tiles = str_contains($withoutJokers, '-')
            ? self::tilesFromExplicitSegments($withoutJokers)
            : self::tilesFromFreeInput($withoutJokers);

        if ($tiles === null) {
            return null;
        }

        $totalTiles = count($tiles) + $jokerCount;

        if ($totalTiles < self::MIN_TILES || $totalTiles > Normalizer::MAX_LENGTH) {
            return null;
        }

        if ($jokerCount > self::MAX_JOKERS) {
            return null;
        }

        /** @var array<string, int> $letterCounts */
        $letterCounts = array_count_values($tiles);
        ksort($letterCounts, SORT_STRING);

        return new self($letterCounts, $jokerCount, self::buildSlug($letterCounts, $jokerCount));
    }

    /**
     * Saisie libre (aucun tiret) : tokenisation gloutonne, voir le commentaire de
     * classe. Renvoie null si un caractere hors A-Z/Ñ est present.
     *
     * @return list<string>|null
     */
    private static function tilesFromFreeInput(string $withoutJokers): ?array
    {
        if ($withoutJokers === '') {
            return [];
        }

        if (preg_match('/^[A-ZÑ]+\z/u', $withoutJokers) !== 1) {
            return null;
        }

        return Normalizer::tokenizeTiles($withoutJokers);
    }

    /**
     * Segments explicites (au moins un tiret) : chaque segment entre tirets doit etre
     * EXACTEMENT une tuile valide (une lettre simple A-Z/Ñ, ou un digramme CH/LL/RR).
     * Un segment vide (tirets consecutifs ou en bord de chaine -- ex. apres retrait des
     * jokers dans "...-*-*") est ignore, jamais une erreur : c'est un artefact de mise
     * en forme, pas une tuile manquante.
     *
     * @return list<string>|null
     */
    private static function tilesFromExplicitSegments(string $withoutJokers): ?array
    {
        $tiles = [];

        foreach (explode('-', $withoutJokers) as $segment) {
            if ($segment === '') {
                continue;
            }

            // Un segment est une tuile valide si et seulement si tokenizeTiles() le
            // decoupe en EXACTEMENT une tuile identique au segment lui-meme -- rejette
            // par construction tout segment qui n'est ni une lettre simple A-Z/Ñ ni un
            // digramme CH/LL/RR (ex. "ab" -> deux tuiles [A, B], donc invalide ici).
            $asTiles = Normalizer::tokenizeTiles($segment);

            if (count($asTiles) !== 1 || $asTiles[0] !== $segment) {
                return null;
            }

            $tiles[] = $segment;
        }

        return $tiles;
    }

    /**
     * @param array<string, int> $letterCounts deja triees par cle
     */
    private static function buildSlug(array $letterCounts, int $jokerCount): string
    {
        $tiles = [];

        foreach ($letterCounts as $tile => $count) {
            for ($i = 0; $i < $count; $i++) {
                $tiles[] = $tile;
            }
        }

        for ($i = 0; $i < $jokerCount; $i++) {
            $tiles[] = '*';
        }

        return mb_strtolower(implode('-', $tiles), 'UTF-8');
    }
}
