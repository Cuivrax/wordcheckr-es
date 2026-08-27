<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots commencant par {X} et terminant par {Y}" (D-024/D-025,
 * Family::WORD_LIST_COMBINED, 611 pages deja indexees) : liens vers chaque variante
 * /mots/commencant/{X}/terminant/{Y}/avec/{Z} qui a au moins un resultat -- repond a "je veux les
 * mots qui commencent par R, terminent par E, et contiennent un S" depuis la page generique
 * "commencant R terminant E".
 *
 * Contrairement au maillage "position" (D-023), AUCUN collapse n'est necessaire ici : "avec" est
 * une contrainte de PRESENCE (instr(normalized, Z) > 0), jamais degeneree vers commencant/terminant
 * meme quand Z est egal a X ou Y -- "commencant/r/terminant/e/avec/r" reste une URL syntaxiquement
 * distincte et non redondante de "commencant/r/terminant/e" (elle re-affirme juste une contrainte
 * deja garantie par construction, jamais un motif de contenu duplique comme "motif"/"position").
 *
 * Precalcule (list_counts, list_type 'start_end_with', scripts/build_explore_hub_counts.php),
 * jamais un calcul sur `terms` au runtime -- voir reports/query-plans/
 * commencant-terminant-avec-maillage.md pour la mesure qui impose ce detour (comparee
 * explicitement a l'alternative 26 requetes GROUP BY SQL, plus lente).
 *
 * Cible Family::WORD_LIST_COMBINED (page source) ; la page cible (avec "avec" en plus) n'a pas
 * encore de classification propre -- decision et classification hors perimetre data-engine, voir
 * le rapport pour la recommandation.
 */
final class StartEndWithLinks
{
    /** @param list<array{letter: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
