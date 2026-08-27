<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Maillage interne d'une page "mots de {N} lettres" (D-022) : quatre listes de liens vers des
 * combinaisons longueur + une lettre -- commencant, terminant, avec, position -- toutes
 * precalculees (list_counts, scripts/build_explore_hub_counts.php), jamais un GROUP BY au
 * runtime.
 *
 * CORRECTIF (audit du lot D-025, seo-technical-auditor, constat C-2, 2026-08-09) : ce docblock
 * affirmait a tort que les TROIS groupes pointaient vers la MEME famille SEO. Ce n'est pas le
 * cas -- deux familles distinctes, jamais a confondre :
 * - byStart/byEnd (longueur+commencant, longueur+terminant) : App\Seo\Family::WORD_LIST_COMBINED.
 *   Cette famille n'est PLUS dans NEVER_SITEMAP depuis D-025 (levee du garde-fou), mais SEUL le
 *   sous-ensemble prefixe+suffixe D'UNE SEULE LETTRE CHACUN, SANS longueur, a ete mesure et
 *   ouvert (611 lignes, D-025 + correctif de performance D-025bis). Les combinaisons
 *   longueur+commencant/longueur+terminant liees ICI restent noindex,follow par defaut (D-005) :
 *   aucun lot ne les a jamais proposees. Ne JAMAIS supposer qu'elles sont sures a ouvrir sans
 *   leur propre mesure -- WordListFilters::readSingleLetterRun() accepte des prefixes/suffixes
 *   jusqu'a 15 lettres, l'espace n'est PAS borne en general, seul le cas mono-lettre l'est.
 * - byWith (longueur+avec) : CORRECTIF (D-029, palier 1 de l'ouverture en entonnoir de "avec",
 *   2026-08-17) : ce docblock affirmait a tort que ces liens ne servaient jamais l'indexation.
 *   Faux depuis D-029 -- ils ciblent exactement Family::WORD_LIST_AVEC_SINGLE_LETTER (longueur
 *   + UNE SEULE lettre "avec", 364 pages, INDEXEE), un sous-ensemble borne et mesure sur de
 *   Family::WORD_LIST_AVEC. La famille generale WORD_LIST_AVEC (plusieurs lettres, ou une seule
 *   lettre SANS longueur) reste et restera dans NEVER_SITEMAP en permanence (multiensemble de
 *   lettres, espace non borne, contrainte dure du projet) -- ne JAMAIS confondre les deux :
 *   c'est PRECISEMENT la longueur presente ici (byWith n'existe que sur une page /mots/
 *   {N}-lettres) qui rend ce cas borne et sur, pas une propriete de "avec" en general.
 * - byPosition (AJOUT, correctif audit seo-technical-auditor sur D-028, constat C1, 2026-08-11) :
 *   groupe par position (2 a longueur-1 -- jamais 1 ni longueur, deja couvertes par byStart/byEnd,
 *   D-023 les collapse toujours vers commencant/terminant) les lettres non vides a cette
 *   position, chacune pointant vers /mots/{N}-lettres/position/{P}/{X} -- Family::
 *   WORD_LIST_POSITION, DEJA ouverte a l'indexation (D-028). Comble le defaut releve par l'audit :
 *   les 2 329 pages de cette famille n'avaient jusque-la aucun lien direct depuis une page deja
 *   indexee (seul chemin reel a l'epoque : /mots/{N}-lettres/avec/{X}, alors Family::
 *   WORD_LIST_AVEC, NEVER_SITEMAP -- D-023bis). PRECISION (2026-08-17) : cette page source est
 *   depuis D-029 elle-meme indexee (Family::WORD_LIST_AVEC_SINGLE_LETTER), mais ca ne change
 *   rien au constat C1 de l'epoque ni a ce correctif -- byPosition ajoute ICI un second lien
 *   direct depuis /mots/{N}-lettres elle-meme (Family::WORD_LIST_LENGTH, indexee, D-017), qui
 *   reste la source de reference. Voir reports/query-plans/position-length-maillage.md pour la
 *   mesure complete et l'analyse.
 * - byStartEnd (AJOUT, meme patron que byPosition, applique cette fois a la variante AVEC
 *   longueur de Family::WORD_LIST_COMBINED, 2026-08-18) : groupe par lettre de debut les
 *   variantes /mots/{N}-lettres/commencant/{X}/terminant/{Y} qui ont au moins un resultat
 *   (list_counts, list_type 'length_start_end', D-027 -- deja precalcule, jamais un GROUP BY au
 *   runtime). Comble exactement le meme defaut que celui corrige pour byPosition (D-028bis,
 *   constat C1) : avant cet ajout, les ~5 141 pages eligibles de cette variante n'avaient AUCUN
 *   lien direct depuis une page deja indexee (D-027, App\Search\LengthCombinedLinksBuilder relie
 *   uniquement depuis /mots/{N}-lettres/commencant/{X} ou .../terminant/{Y}, qui restent
 *   noindex,follow par omission -- verifie directement, jamais indexees). byStartEnd ajoute ICI
 *   un lien direct depuis /mots/{N}-lettres elle-meme (Family::WORD_LIST_LENGTH, indexee, D-017).
 *   EXCLUSION explicite des 52 paires de doublons de contenu identifiees par D-025 (I-1,
 *   confirmees dans storage/seo_fr.sqlite, registry.notes LIKE '%ATTENTION doublon%', family
 *   'word_list_combined') : ces 52 (longueur, debut, fin) precis designent une page dont TOUS
 *   les mots partagent deja la meme longueur -- la variante SANS longueur (deja indexee comme
 *   gagnante canonique permanente par D-025) couvre deja 100% du contenu de la variante AVEC
 *   longueur correspondante. Ces 52 pages resteront noindex,follow en permanence quelle que soit
 *   une future decision d'ouverture (R3, jamais deux pages index,follow pour un contenu
 *   identique) -- ne jamais leur creer de lien depuis une page indexee. Voir
 *   App\Search\LengthLinksBuilder::DUPLICATE_START_END_KEYS pour la liste exacte et sa
 *   verification, et reports/query-plans/combined-length-maillage.md pour la mesure complete.
 */
final class LengthLinks
{
    /**
     * @param list<array{letter: string, url: string, count: int}> $byStart
     * @param list<array{letter: string, url: string, count: int}> $byEnd
     * @param list<array{letter: string, url: string, count: int}> $byWith
     * @param list<array{position: int, letters: list<array{letter: string, url: string, count: int}>}> $byPosition
     * @param list<array{start: string, letters: list<array{letter: string, url: string, count: int}>}> $byStartEnd
     */
    public function __construct(
        public readonly array $byStart,
        public readonly array $byEnd,
        public readonly array $byWith,
        public readonly array $byPosition,
        public readonly array $byStartEnd,
        public readonly int $queryCount,
    ) {
    }
}
