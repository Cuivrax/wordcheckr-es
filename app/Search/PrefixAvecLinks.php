<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots commencant par {X}" (D-017, deja indexee,
 * Family::WORD_LIST_COMMENCANT) : liens vers chaque variante /mots/commencant/{X}/avec/{Y}
 * (prefixe d'une seule lettre + une lettre "avec", occurrence unique, SANS longueur, SANS
 * terminant) qui a au moins un resultat -- repond a "je veux les mots qui commencent par R et
 * contiennent un S" depuis la page generique "commencant R".
 *
 * UNE SEULE direction de lecture (contrairement a App\Search\LetterCombinedLinksBuilder, qui lit
 * 'start_end' dans les DEUX sens depuis deux pages source distinctes) : la page source de ce
 * maillage est toujours /mots/commencant/{X} -- il n'existe aucune page /mots/avec/{Y} indexee
 * symetrique vers laquelle mailler (Family::WORD_LIST_AVEC reste NEVER_SITEMAP en permanence,
 * multiensemble de lettres non ancre).
 *
 * Les 26 combinaisons DEGENEREES X=Y (D-032 : "avec/X" collapse silencieusement vers la page
 * parente /mots/commencant/{X} des que la lettre "avec" egale un prefixe/suffixe d'une seule
 * lettre, "avec X" etant toujours vrai des que "commence par X" l'est deja) sont exclues
 * directement AU PRECALCUL (list_counts, list_type 'start_with',
 * scripts/build_explore_hub_counts.php) -- choix DISTINCT de App\Search\StartEndWithLinksBuilder
 * (D-033, qui filtre au niveau du builder par comparaison d'URL, le precalcul restant brut) :
 * ici, une seule lettre "avec" par ligne et une seule direction de lecture rendent la condition de
 * degenerescence (lettre = prefixe) strictement identique au precalcul et a l'usage reel --
 * l'exclure a la source est equivalent et plus simple. Verifie explicitement, pas suppose : voir
 * reports/query-plans/commencant-avec-maillage.md, section 1.
 *
 * Precalcule (list_counts, list_type 'start_with'), jamais un calcul sur `terms` au runtime.
 *
 * Cible Family::WORD_LIST_COMMENCANT (page source) ; la page cible (avec "avec" en plus) n'a pas
 * encore de classification propre -- decision et classification hors perimetre data-engine, voir
 * le rapport pour la recommandation (avis donne, pas une decision).
 */
final class PrefixAvecLinks
{
    /** @param list<array{letter: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
