<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Relations de la fiche mot /mot/{mot} (Phase 4, docs/08), pour un mot ADMIS uniquement
 * (RelationsFinder::find() n'est appele que si TermPage::$status === STATUS_ADMITTED --
 * un mot inconnu ou francais non admis n'a pas de relations calculees, cout zero pour ces
 * deux statuts). Consomme par la couche de rendu (app/View/, hors perimetre de cet agent) --
 * structure prete a rendre, meme principe que TermPage/RackPage/WordListPage.
 *
 * Dix categories (docs/01_MASTER_BRIEF.md, definitions precisees par docs/08) :
 *
 *   1. anagrams              signature identique, meme longueur, != le mot lui-meme
 *   2. changeOneLetter       meme longueur, une seule position differente
 *   3. removeOneLetter       longueur - 1, sous-sequence (ordre preserve), pas un anagramme
 *   4. insertOneLetter       longueur + 1, le mot est obtenu en retirant une lettre du candidat
 *   5. substrings ("sous-mots") sous-chaine CONTIGUE, longueur 2 a N-1
 *   6. rightExtensions ("rallonges a droite")  admis, prefixe = le mot, plus long
 *   7. leftExtensions ("rallonges a gauche")   admis, suffixe = le mot, plus long
 *   8. containingWords ("dans un mot plus long") contient le mot, ni prefixe ni suffixe
 *   9. anagramsPlusOne       longueur + 1, memes lettres + une lettre quelconque
 *   10. anagramsMinusOne     longueur - 1, memes lettres moins une lettre quelconque
 *
 * Chaque entree de liste porte la forme commune {normalized, slug, score, length, isOds8,
 * isOds9}, plus un champ specifique pour changeOneLetter (position, newLetter),
 * anagramsPlusOne (addedLetter) et anagramsMinusOne (removedLetter) -- voir
 * RelationsFinder pour le detail exact de calcul.
 *
 * rightExtensions/leftExtensions/containingWords portent en plus un total et un indicateur
 * de troncature : au-dela de RelationsFinder::EXTENSION_ROW_CEILING correspondances, le
 * total cesse d'etre exact (meme convention que WordListPage::$exact/$truncated, Phase 3).
 * Ce sont les trois seules categories qui ont un lien "Voir les N mots ->" vers une page
 * canonique /mots/... existante (docs/08) -- 2/3/4/5/9/10 n'en ont pas.
 *
 * relatedSearches : jusqu'a 12 liens de recherche liee, purs liens sans donnee de mot
 * (ZERO requete SQLite supplementaire, RelationsFinder::relatedSearches() ne fait que de la
 * construction d'URL via WordListFilters::canonicalUrl() et Rack::fromInput()).
 */
final class TermRelations
{
    /**
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $anagrams
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, position: int, newLetter: string}> $changeOneLetter
     *        position : 1-based, position de la lettre qui differe de la forme d'origine
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $removeOneLetter
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $insertOneLetter
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $substrings
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $rightExtensions
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $leftExtensions
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool}> $containingWords
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, addedLetter: string}> $anagramsPlusOne
     * @param list<array{normalized: string, slug: string, score: int, length: int, isOds8: bool, isOds9: bool, removedLetter: string}> $anagramsMinusOne
     * @param list<array{type: string, url: string}> $relatedSearches jusqu'a 12 entrees
     */
    public function __construct(
        public readonly array $anagrams,
        public readonly array $changeOneLetter,
        public readonly array $removeOneLetter,
        public readonly array $insertOneLetter,
        public readonly array $substrings,
        public readonly array $rightExtensions,
        public readonly int $rightExtensionsTotal,
        public readonly bool $rightExtensionsTruncated,
        public readonly array $leftExtensions,
        public readonly int $leftExtensionsTotal,
        public readonly bool $leftExtensionsTruncated,
        public readonly array $containingWords,
        public readonly int $containingWordsTotal,
        public readonly bool $containingWordsTruncated,
        public readonly array $anagramsPlusOne,
        public readonly array $anagramsMinusOne,
        public readonly array $relatedSearches,
        public readonly int $queryCount,
    ) {
    }
}
