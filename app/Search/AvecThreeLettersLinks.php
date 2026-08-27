<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots de {N} lettres avec {X} {Y}" (palier 2, D-030, Family::
 * WORD_LIST_AVEC_TWO_LETTERS, INDEXEE) vers chaque variante "mots de {N} lettres avec {X} {Y} {Z}"
 * (palier 3 -- ouverture en entonnoir de "avec", troisieme palier, mesure dans
 * reports/query-plans/avec-length-3-letters-full-sweep.md) qui a au moins un resultat -- repond a
 * "je veux les mots de 9 lettres avec un A, un Y ET un Z" depuis la page "avec A Y" existante.
 *
 * Precalcule (list_counts, list_type 'length_with_triple', scripts/build_explore_hub_counts.php),
 * jamais un calcul sur `terms` au runtime. list_key est toujours
 * "{longueur}:{lettre1}:{lettre2}:{lettre3}" avec lettre1 < lettre2 < lettre3 ALPHABETIQUEMENT
 * (une seule ligne par triplet non ordonne) -- voir App\Search\AvecThreeLettersLinksBuilder pour
 * la maniere dont une paire source retrouve ses partenaires quelle que soit sa position dans le
 * triplet stocke.
 *
 * Cible Family::WORD_LIST_AVEC_THREE_LETTERS (D-031) -- distincte de WORD_LIST_AVEC_TWO_LETTERS
 * (palier 2) et de WORD_LIST_AVEC (general, permanent, NEVER_SITEMAP). 28 827 pages INDEXEES
 * (index,follow), lot applique a storage/seo_fr.sqlite, fragment sitemap avec-triple-0001.xml --
 * voir reports/query-plans/avec-length-3-letters-full-sweep.md.
 */
final class AvecThreeLettersLinks
{
    /** @param list<array{letter: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
