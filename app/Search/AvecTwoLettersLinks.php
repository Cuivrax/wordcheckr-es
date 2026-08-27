<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots de {N} lettres avec {X}" (palier 1, D-029, Family::
 * WORD_LIST_AVEC_SINGLE_LETTER, INDEXEE) vers chaque variante "mots de {N} lettres avec {X} {Y}"
 * (palier 2 -- ouverture en entonnoir de "avec", deuxieme palier, mesure dans
 * reports/query-plans/avec-length-2-letters-full-sweep.md) qui a au moins un resultat --
 * repond a "je veux les mots de 9 lettres avec un A ET un Y" depuis la page "avec A" generique.
 *
 * Precalcule (list_counts, list_type 'length_with_pair', scripts/build_explore_hub_counts.php),
 * jamais un calcul sur `terms` au runtime. list_key est toujours "{longueur}:{lettre1}:{lettre2}"
 * avec lettre1 < lettre2 ALPHABETIQUEMENT (une seule ligne par paire non ordonnee) -- voir
 * App\Search\AvecTwoLettersLinksBuilder pour la maniere dont une lettre source unique retrouve
 * ses partenaires quel que soit son cote dans la paire stockee.
 *
 * Cible Family::WORD_LIST_AVEC_TWO_LETTERS -- CORRECTIF (D-030, audit seo-technical-auditor,
 * constat I-1, 2026-08-18) : ce docblock affirmait a tort que la classification et l'ouverture
 * restaient a trancher. Faux depuis D-030 -- 4 276 pages INDEXEES (index,follow), lot applique
 * a storage/seo_fr.sqlite, fragment sitemap avec-pair-0001.xml. Le prochain palier (3 lettres)
 * exige sa PROPRE classification et sa PROPRE decision, jamais une reutilisation de
 * WORD_LIST_AVEC_TWO_LETTERS.
 */
final class AvecTwoLettersLinks
{
    /** @param list<array{letter: string, url: string, count: int}> $links */
    public function __construct(
        public readonly array $links,
        public readonly int $queryCount,
    ) {
    }
}
