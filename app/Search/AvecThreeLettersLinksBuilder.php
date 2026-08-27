<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\AvecThreeLettersLinks depuis list_counts (list_type 'length_with_triple'),
 * meme principe que App\Search\AvecTwoLettersLinksBuilder (palier 2) -- une seule requete
 * triviale, aucun calcul sur `terms` au runtime (voir scripts/build_explore_hub_counts.php pour
 * la mesure qui impose ce detour).
 *
 * list_key est toujours "{longueur}:{lettre1}:{lettre2}:{lettre3}" avec
 * lettre1 < lettre2 < lettre3 ALPHABETIQUEMENT (une seule ligne par triplet non ordonne -- jamais
 * les six permutations stockees separement). Depuis une page palier 2 "avec {X} {Y}" (deux
 * lettres source, deja triees alphabetiquement par WordListFilters::fromPath(), X < Y), la paire
 * source peut occuper TROIS positions differentes dans le triplet trie stocke, selon ou tombe la
 * troisieme lettre (partenaire) dans l'ordre alphabetique :
 *
 *   partenaire < X < Y   -> triplet stocke "{longueur}:{partenaire}:{X}:{Y}" (X,Y = lettre2,lettre3)
 *   X < partenaire < Y   -> triplet stocke "{longueur}:{X}:{partenaire}:{Y}" (X,Y = lettre1,lettre3)
 *   X < Y < partenaire   -> triplet stocke "{longueur}:{X}:{Y}:{partenaire}" (X,Y = lettre1,lettre2)
 *
 * Trois motifs LIKE distincts, combines par un seul OR dans une seule requete (jamais trois
 * executions separees) -- contrairement au palier 2 (deux motifs seulement, une paire n'a que
 * deux positions possibles dans une paire triee). Le second motif ("{longueur}:{X}:%:{Y}") est le
 * seul des trois a placer le joker entre deux lettres fixes plutot qu'en tete ou en queue -- reste
 * un LIKE valide (SQLite ne restreint pas '%' a une position), verifie par force brute dans
 * tests/Search/AvecThreeLettersLinksBuilderTest.php.
 *
 * L'URL cible est TOUJOURS construite via WordListFilters::fromPath()->canonicalUrl(), jamais
 * assemblee a la main : ksort() y trie deja les lettres "avec" par cle alphabetique (D-022), donc
 * peu importe l'ordre dans lequel $letter1/$letter2/le partenaire sont passes a fromPath() ici,
 * l'URL rendue est toujours la forme canonique (lettre1 < lettre2 < lettre3).
 *
 * Deux filtres anti-doublon, appliques dans build() ci-dessous (analyse independante data-engine,
 * 2026-08-20, demandee en parallele du meme calcul cote seo-registry avant toute application
 * registre/sitemap -- meme discipline que D-037/D-038/D-039) : DUPLICATE_PARENT_KEYS (doublon avec
 * l'une des trois pages parentes palier 2, ET transitivement avec une page parente palier 1 --
 * preuve mathematique : un triplet ne peut jamais dupliquer une lettre seule sans DEJA dupliquer
 * l'une de ses trois paires, MOTS(triplet) subset MOTS(paire) subset MOTS(lettre seule) --
 * verifie sur les 28 827 triplets reels, 0 cas de duplication "lettre seule" sans duplication de
 * paire correspondante, exactement comme la preuve le predit) et SIBLING_DUPLICATE_KEYS (doublon
 * entre pages SOEURS du palier 3, meme longueur).
 */
final class AvecThreeLettersLinksBuilder
{
    /**
     * Les 426 quadruplets (longueur, lettre1, lettre2, lettre3) a contenu strictement DUPLIQUE
     * avec l'une de leurs trois pages parentes palier 2 (/mots/{N}-lettres/avec/{X}/{Y},
     * .../avec/{X}/{Z} ou .../avec/{Y}/{Z}) -- meme patron que
     * App\Search\AvecTwoLettersLinksBuilder::DUPLICATE_PARENT_KEYS : une ligne list_counts
     * 'length_with_triple' "{N}:{X}:{Y}:{Z}" est un doublon de contenu SI ET SEULEMENT SI son
     * `count` est EXACTEMENT EGAL au `count` de l'une des trois entrees parentes
     * 'length_with_pair' correspondantes -- ca signifie que TOUS les mots de cette paire
     * contiennent deja la troisieme lettre, l'ajouter comme contrainte "avec" supplementaire ne
     * retire aucun mot. Exemple cite par la demande d'analyse, confirme present : "10:A:W:X",
     * "10:E:W:X", "10:N:W:X", "10:O:W:X", "10:S:W:X", "10:T:W:X" (les 6 lettres A,E,N,O,S,T
     * partagent toutes le meme mot unique que la paire W:X a longueur 10) ; meme motif a
     * longueur 15 avec B,E,I,L,O,R,S,U autour de la paire W:X (8 variantes).
     *
     * Verification de la transitivite palier1/palier3 (mathematiquement demontree : si
     * MOTS(triplet) == MOTS(lettre seule), alors necessairement MOTS(paire) == MOTS(lettre seule)
     * aussi, pour l'une des deux paires contenant cette lettre -- la comparaison directe aux trois
     * lettres seules ne peut donc jamais trouver un cas que la comparaison aux trois paires ne
     * trouverait pas deja) : verifie sur les 28 827 triplets reels, 0 cas ou une lettre seule
     * matche sans que la paire correspondante ne matche aussi -- la preuve tient sur les donnees
     * reelles, pas seulement en theorie.
     *
     * Verifie par DEUX methodes independantes : 1. comparaison list_counts ('length_with_triple'
     * vs 'length_with_pair', count enfant === count d'une des 3 paires parentes), sur les 28 827
     * lignes reelles du palier 3 ; 2. recompute direct et independant depuis `terms` (scan
     * longueur par longueur, comptage des combinaisons de lettres uniques par mot, sans jamais
     * lire list_counts) -- 426 trouves, 0 divergence entre les deux methodes.
     *
     * La cle est exactement le `list_key` tel que stocke dans list_counts ("{N}:{X}:{Y}:{Z}",
     * X < Y < Z alphabetiquement, D-031) -- comparee directement a $row['list_key'] dans build()
     * ci-dessous, jamais reconstruite a la main.
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022, integrity_check = ok). Une reconstruction future de la base devra
     * revalider cette liste (meme avertissement que partout ailleurs dans ce projet).
     *
     * @var list<string>
     */
    private const DUPLICATE_PARENT_KEYS = [
        '10:A:J:W', '10:A:W:X', '10:B:J:W', '10:D:Q:U', '10:E:J:W', '10:E:Q:W', '10:E:V:W', '10:E:W:X',
        '10:E:W:Z', '10:E:X:Z', '10:F:Q:U', '10:G:Q:U', '10:I:V:W', '10:J:L:W', '10:J:N:W', '10:J:O:W',
        '10:J:Q:U', '10:J:R:W', '10:J:U:W', '10:K:Q:U', '10:L:Q:U', '10:N:W:X', '10:O:W:X', '10:P:Q:U',
        '10:Q:S:W', '10:Q:U:V', '10:Q:U:W', '10:Q:U:X', '10:Q:U:Y', '10:S:W:X', '10:T:W:X', '11:C:Q:W',
        '11:D:Q:U', '11:E:Q:W', '11:E:V:W', '11:E:W:X', '11:F:Q:U', '11:G:Q:U', '11:H:Q:U', '11:H:W:X',
        '11:I:V:W', '11:I:W:X', '11:J:Q:U', '11:K:Q:U', '11:L:Q:U', '11:L:W:X', '11:M:W:X', '11:O:Q:W',
        '11:O:W:X', '11:Q:S:W', '11:Q:U:V', '11:Q:U:W', '11:Q:U:X', '11:Q:U:Y', '11:S:W:X', '12:D:Q:U',
        '12:E:J:K', '12:E:K:X', '12:E:Q:W', '12:E:V:W', '12:E:W:X', '12:E:X:Z', '12:F:Q:U', '12:G:Q:U',
        '12:H:Q:U', '12:I:Q:W', '12:I:V:W', '12:I:W:X', '12:J:Q:U', '12:J:U:X', '12:K:O:X', '12:K:Q:U',
        '12:L:Q:U', '12:L:W:X', '12:O:W:X', '12:Q:U:V', '12:Q:U:W', '12:Q:U:X', '12:Q:U:Y', '12:S:W:X',
        '13:A:J:X', '13:A:K:X', '13:B:E:W', '13:B:Q:U', '13:C:Q:U', '13:D:Q:U', '13:E:G:J', '13:E:J:K',
        '13:E:J:Y', '13:E:J:Z', '13:E:K:X', '13:E:M:W', '13:E:P:W', '13:E:Q:W', '13:E:Q:X', '13:E:Q:Z',
        '13:E:V:W', '13:E:W:X', '13:E:W:Y', '13:E:W:Z', '13:E:X:Z', '13:F:Q:U', '13:G:Q:U', '13:H:Q:U',
        '13:I:Q:W', '13:I:V:W', '13:I:W:X', '13:I:W:Y', '13:J:Q:U', '13:J:S:Y', '13:K:L:X', '13:K:N:X',
        '13:K:O:X', '13:K:Q:U', '13:L:Q:U', '13:L:W:X', '13:M:Q:U', '13:O:W:X', '13:P:Q:U', '13:P:S:W',
        '13:Q:S:W', '13:Q:U:V', '13:Q:U:W', '13:Q:U:X', '13:Q:U:Y', '13:Q:U:Z', '13:S:W:X', '14:A:J:K',
        '14:A:J:X', '14:A:K:X', '14:A:Q:U', '14:B:Q:U', '14:C:J:K', '14:C:Q:U', '14:D:Q:U', '14:E:G:W',
        '14:E:H:J', '14:E:J:V', '14:E:K:Q', '14:E:K:W', '14:E:K:Z', '14:E:P:W', '14:E:Q:U', '14:E:Q:W',
        '14:E:Q:Z', '14:E:V:W', '14:E:V:X', '14:E:V:Z', '14:E:W:X', '14:E:W:Y', '14:E:W:Z', '14:E:X:Z',
        '14:F:Q:U', '14:G:Q:U', '14:H:Q:U', '14:I:J:Q', '14:I:J:Y', '14:I:K:W', '14:I:P:W', '14:I:Q:U',
        '14:I:V:W', '14:I:W:X', '14:J:Q:U', '14:K:L:X', '14:K:N:X', '14:K:O:X', '14:K:Q:U', '14:L:Q:U',
        '14:L:W:X', '14:M:Q:U', '14:N:Q:U', '14:O:Q:U', '14:O:W:X', '14:P:Q:U', '14:Q:R:U', '14:Q:S:U',
        '14:Q:T:U', '14:Q:U:V', '14:Q:U:W', '14:Q:U:X', '14:Q:U:Y', '14:Q:U:Z', '14:R:W:X', '14:S:W:X',
        '15:A:J:K', '15:A:J:X', '15:A:K:X', '15:A:Q:U', '15:B:E:J', '15:B:E:W', '15:B:I:W', '15:B:O:W',
        '15:B:Q:U', '15:B:W:X', '15:C:J:K', '15:C:Q:U', '15:D:E:K', '15:D:Q:U', '15:E:F:W', '15:E:F:X',
        '15:E:F:Z', '15:E:G:K', '15:E:G:W', '15:E:G:X', '15:E:H:J', '15:E:J:K', '15:E:J:L', '15:E:J:M',
        '15:E:J:Q', '15:E:J:V', '15:E:J:Y', '15:E:J:Z', '15:E:K:P', '15:E:K:Q', '15:E:K:W', '15:E:K:X',
        '15:E:K:Z', '15:E:M:W', '15:E:P:W', '15:E:Q:U', '15:E:Q:W', '15:E:Q:Y', '15:E:Q:Z', '15:E:R:W',
        '15:E:T:W', '15:E:U:W', '15:E:V:W', '15:E:V:X', '15:E:W:X', '15:E:W:Y', '15:E:W:Z', '15:E:X:Z',
        '15:F:J:S', '15:F:Q:U', '15:G:Q:U', '15:H:I:J', '15:H:Q:U', '15:I:J:Q', '15:I:J:V', '15:I:J:Y',
        '15:I:J:Z', '15:I:K:W', '15:I:M:W', '15:I:P:W', '15:I:Q:U', '15:I:V:W', '15:I:W:X', '15:I:W:Z',
        '15:J:Q:S', '15:J:Q:U', '15:J:R:Y', '15:J:S:Y', '15:J:U:X', '15:K:L:X', '15:K:N:X', '15:K:O:V',
        '15:K:O:X', '15:K:Q:U', '15:K:R:X', '15:K:S:X', '15:L:Q:U', '15:L:W:X', '15:M:Q:U', '15:N:Q:U',
        '15:O:Q:U', '15:O:W:X', '15:O:W:Y', '15:P:Q:U', '15:Q:R:U', '15:Q:S:U', '15:Q:T:U', '15:Q:U:V',
        '15:Q:U:W', '15:Q:U:X', '15:Q:U:Y', '15:Q:U:Z', '15:R:W:X', '15:S:W:X', '15:U:W:X', '3:A:C:K',
        '3:A:D:W', '3:A:F:Y', '3:A:J:M', '3:A:J:N', '3:A:J:P', '3:A:J:Z', '3:A:M:V', '3:A:P:W',
        '3:A:P:X', '3:A:Q:T', '3:A:S:Z', '3:A:T:W', '3:A:W:X', '3:A:Y:Z', '3:B:K:O', '3:B:M:W',
        '3:C:M:Q', '3:C:U:Y', '3:D:I:K', '3:D:I:X', '3:E:F:Z', '3:E:G:X', '3:E:K:Z', '3:E:M:Z',
        '3:E:N:Z', '3:E:P:Q', '3:E:V:X', '3:F:I:V', '3:G:O:W', '3:H:I:K', '3:H:I:V', '3:H:L:M',
        '3:H:O:W', '3:I:N:Y', '4:A:D:W', '4:A:G:X', '4:A:J:W', '4:A:Q:W', '4:A:V:Z', '4:A:W:Y',
        '4:E:J:X', '4:E:X:Z', '4:F:J:U', '4:F:L:Y', '4:F:Q:W', '4:G:I:Q', '4:G:N:Q', '4:G:U:X',
        '4:I:J:W', '4:I:N:Q', '4:I:W:Z', '4:J:R:Y', '4:J:U:X', '4:J:U:Y', '4:L:Q:U', '4:M:R:W',
        '4:M:U:W', '4:O:Q:Y', '4:Q:U:Y', '4:T:W:Z', '5:A:J:Q', '5:A:J:X', '5:A:Q:W', '5:A:W:X',
        '5:A:W:Y', '5:D:Q:U', '5:E:Q:X', '5:E:X:Z', '5:G:I:Q', '5:G:N:Q', '5:G:Q:S', '5:I:K:X',
        '5:I:W:X', '5:K:L:X', '5:K:Q:U', '5:K:X:Y', '5:M:W:X', '5:P:Q:U', '5:Q:S:W', '5:Q:U:V',
        '5:Q:U:X', '6:A:J:W', '6:A:K:X', '6:B:J:W', '6:C:W:X', '6:E:Q:X', '6:G:Q:U', '6:I:J:W',
        '6:J:O:W', '6:K:L:X', '6:K:N:X', '6:K:O:X', '6:K:Q:U', '6:O:W:X', '6:P:W:X', '6:Q:U:V',
        '6:Q:U:X', '7:A:J:W', '7:A:V:W', '7:A:W:X', '7:B:J:W', '7:B:Q:U', '7:E:Q:X', '7:E:V:W',
        '7:F:Q:U', '7:I:J:W', '7:I:V:W', '7:J:O:W', '7:J:Q:U', '7:K:Q:U', '7:N:V:W', '7:Q:U:X',
        '7:R:V:W', '8:A:J:W', '8:A:K:X', '8:A:V:W', '8:A:W:X', '8:B:Q:U', '8:E:V:W', '8:E:X:Z',
        '8:F:Q:U', '8:G:Q:U', '8:I:J:W', '8:J:Q:U', '8:K:Q:U', '8:P:Q:U', '8:Q:U:V', '8:Q:U:X',
        '8:Q:U:Y', '8:R:V:W', '8:S:W:X', '9:A:J:W', '9:A:K:X', '9:A:W:X', '9:D:Q:U', '9:F:Q:U',
        '9:G:Q:U', '9:I:Q:W', '9:J:O:W', '9:J:Q:U', '9:K:Q:U', '9:P:Q:U', '9:Q:U:V', '9:Q:U:W',
        '9:Q:U:X', '9:Q:U:Y',
    ];

    /**
     * Doublons de contenu entre pages SOEURS du palier 3 (deux triplets DIFFERENTS a la MEME
     * longueur produisant exactement le meme ensemble de mots, ni l'un ni l'autre deja exclu par
     * DUPLICATE_PARENT_KEYS ci-dessus) -- meme classe de defaut que
     * App\Search\StartEndWithLinksBuilder::SIBLING_DUPLICATE_KEYS (D-038) et
     * App\Search\AvecTwoLettersLinksBuilder::SIBLING_DUPLICATE_KEYS (palier 2, liste vide), recherchee
     * ici de la meme facon : regroupement par (longueur, count) parmi les 28 401 triplets survivants
     * du filtre parent (necessaire mais pas suffisant, deux ensembles distincts peuvent partager un
     * compte), PUIS verification par empreinte SQL GROUP_CONCAT (liste triee des mots concernes,
     * comparaison de chaines completes, aucun hash, aucune collision possible) sur les 3 496 groupes
     * candidats (19 049 triplets) trouves par ce premier tri.
     *
     * Resultat : 189 groupes reels trouves (423 triplets impliques), la lettre alphabetiquement plus
     * petite du groupe (cle string la plus petite) reste candidate, les 234 autres membres sont
     * exclus ici -- CONTRAIREMENT au palier 2 (0 collision reelle) : les paniers du palier 3 sont
     * significativement plus petits (declenche plus souvent une coincidence exacte de contenu, ex.
     * "10:G:J:Y" et "10:G:W:Y" partagent exactement le meme mot unique).
     *
     * Verifie par DEUX methodes independantes sur les 189 groupes trouves (290 paires de cles
     * comparees au total) : 1. GROUP_CONCAT direct (chaine complete, decrit ci-dessus) ;
     * 2. verification par comptage triple (countA, countB, countA-ET-B), meme principe que
     * App\Search\StartEndWithLinksBuilder::CROSS_DUPLICATE_LENGTH_KEYS methode 2 (D-039) : pour
     * chaque paire de cles du meme groupe, countA === countB === countA-ET-B === count du groupe
     * prouve une egalite d'ensemble sans jamais comparer de tableau. 0 divergence entre les deux
     * methodes, 0 chevauchement entre groupes (aucune cle n'appartient a deux groupes differents).
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022, integrity_check = ok). Une reconstruction future de la base devra
     * revalider cette liste.
     *
     * @var list<string>
     */
    private const SIBLING_DUPLICATE_KEYS = [
        '10:G:J:Y', '10:G:W:Y', '10:J:L:Y', '10:M:W:Z', '11:G:J:Y', '11:G:K:W', '11:I:Q:W', '11:J:M:X',
        '11:J:X:Y', '11:N:Q:W', '11:Q:R:W', '11:Q:V:W', '12:C:K:X', '12:F:J:X', '12:G:J:Y', '12:K:L:X',
        '12:K:N:X', '12:K:P:W', '12:K:U:X', '12:M:W:X', '12:P:W:Y', '12:R:W:X', '13:F:K:V', '13:G:J:Y',
        '13:J:K:L', '13:J:K:U', '13:J:M:X', '13:J:P:Y', '13:J:Q:X', '13:M:W:X', '13:P:W:Z', '13:U:W:X',
        '13:W:Y:Z', '14:F:K:V', '14:H:J:P', '14:H:J:Y', '14:J:K:M', '14:K:P:V', '14:K:P:W', '14:K:R:X',
        '14:K:W:Y', '14:P:U:W', '14:P:W:Y', '14:P:W:Z', '14:U:W:X', '14:U:W:Y', '14:W:Y:Z', '15:C:J:Y',
        '15:D:J:Y', '15:F:K:V', '15:F:P:W', '15:G:J:P', '15:G:V:W', '15:H:J:P', '15:H:J:Y', '15:H:V:W',
        '15:J:K:M', '15:J:O:Y', '15:J:R:X', '15:J:S:X', '15:K:P:V', '15:K:W:Y', '15:O:P:W', '15:P:Q:W',
        '15:P:R:W', '15:P:U:W', '15:P:W:Y', '15:P:W:Z', '15:U:W:Y', '15:W:Y:Z', '4:B:I:W', '4:B:J:U',
        '4:B:S:W', '4:C:G:O', '4:C:G:P', '4:C:N:Q', '4:C:P:Q', '4:C:Q:S', '4:C:R:Z', '4:C:U:Z',
        '4:C:V:Y', '4:D:F:Q', '4:D:H:P', '4:D:K:U', '4:D:L:P', '4:D:M:T', '4:D:S:Z', '4:E:J:L',
        '4:E:P:W', '4:F:I:Q', '4:F:M:T', '4:F:O:W', '4:F:P:Q', '4:F:Q:U', '4:F:Q:Z', '4:F:S:V',
        '4:F:U:Z', '4:G:I:W', '4:G:L:T', '4:H:I:Q', '4:H:K:Y', '4:H:L:Z', '4:H:P:Q', '4:H:T:Y',
        '4:I:J:V', '4:I:T:Y', '4:I:U:Z', '4:J:K:U', '4:J:L:O', '4:J:M:U', '4:K:L:V', '4:K:M:U',
        '4:K:R:Z', '4:K:S:Z', '4:K:U:Z', '4:L:N:Y', '4:L:U:Z', '4:L:X:Y', '4:M:T:Y', '4:O:P:Q',
        '4:O:Q:S', '4:O:U:W', '4:P:R:W', '4:P:S:V', '4:Q:U:Z', '4:R:T:W', '4:T:X:Y', '4:V:Y:Z',
        '5:B:O:Q', '5:B:O:W', '5:B:S:W', '5:C:J:M', '5:D:U:W', '5:E:M:W', '5:F:I:Q', '5:F:J:R',
        '5:F:N:P', '5:F:O:W', '5:G:W:Z', '5:H:I:Q', '5:H:J:U', '5:H:Q:S', '5:H:Q:U', '5:H:R:W',
        '5:H:V:Y', '5:I:K:Q', '5:J:Q:U', '5:J:S:Y', '5:J:T:V', '5:J:U:X', '5:K:O:V', '5:K:O:W',
        '5:K:Q:R', '5:K:R:W', '5:L:Q:Y', '5:M:S:W', '5:M:U:W', '5:M:X:Y', '5:N:O:Q', '5:Q:U:Y',
        '5:R:U:W', '5:R:W:Z', '5:S:W:Y', '5:S:W:Z', '5:U:X:Z', '6:C:D:W', '6:C:W:Y', '6:F:M:Y',
        '6:F:N:W', '6:G:Q:Y', '6:I:K:Q', '6:J:P:Y', '6:J:Q:R', '6:K:M:P', '6:K:U:W', '6:K:W:Y',
        '6:N:U:W', '6:O:W:Y', '6:P:U:W', '6:Q:S:X', '6:Q:T:W', '6:Q:U:W', '6:Q:W:Y', '6:R:W:Y',
        '6:T:W:Y', '6:X:Y:Z', '7:B:K:W', '7:F:N:W', '7:J:K:X', '7:J:P:Y', '7:K:Q:T', '7:L:Q:W',
        '7:L:W:X', '7:M:W:X', '7:O:W:Z', '7:Q:T:W', '7:Q:U:W', '7:Q:W:Y', '7:T:W:X', '7:W:X:Y',
        '8:F:H:W', '8:H:J:Y', '8:H:K:X', '8:I:K:Q', '8:J:N:W', '8:J:O:W', '8:J:S:W', '8:J:W:Y',
        '8:K:R:X', '8:K:X:Y', '8:L:V:W', '8:M:W:X', '8:N:V:W', '8:O:V:W', '8:Q:R:W', '8:S:V:W',
        '8:W:X:Y', '9:F:H:W', '9:G:K:W', '9:H:W:X', '9:H:W:Y', '9:J:L:Y', '9:J:N:W', '9:J:P:V',
        '9:J:R:W', '9:J:T:W', '9:J:W:Z', '9:N:Q:W', '9:N:W:X', '9:O:W:X', '9:Q:R:W', '9:R:W:X',
        '9:S:W:X', '9:T:W:X',
    ];

    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE au palier 3 de "avec" (D-041,
     * garde-fou structurel demandé par le constat C-4 du 4e audit consolidé, docs/DECISIONS.md
     * D-040) -- distinct de DUPLICATE_PARENT_KEYS/SIBLING_DUPLICATE_KEYS ci-dessus (qui comparent
     * uniquement au sein de la hiérarchie palier 1/2/3), trouvés par le balayage GÉNÉRIQUE de tout
     * le registre (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 : 1 656
     * groupes, 2 089 pages en excès).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "{N}-lettres/avec/{X}/{Y}/{Z}" a TOUJOURS 4 composants (longueur + 3 lettres "avec"), le
     * compte le plus élevé parmi les 9 familles concernées par ce balayage : elle perd donc
     * TOUJOURS face à n'importe quel adversaire, sans exception, ni tie-break requis (aucune autre
     * famille de cette série n'a jamais 4 composants ou plus). Les 666 clés se répartissent en
     * pertes face à terminant (315), commençant (221), combiné avec longueur (78), combiné+avec
     * (49), position (2) et avec à deux lettres, palier 2 (1 -- cas non structurel, une paire et un
     * triplet SANS relation parent/enfant au sens strict isolant néanmoins le même mot, ex.
     * "5:G:Q" (palier 2) == "5:N:Q:S" (palier 3), trouvé uniquement par l'empreinte de contenu du
     * balayage générique, jamais par la règle structurelle de D-040).
     *
     * 666 clés (format "{longueur}:{lettre1}:{lettre2}:{lettre3}", triées), recalculées
     * indépendamment par échantillonnage direct contre `terms` (voir le rapport AFTER de cette
     * tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_KEYS = [
        '10:C:F:W', '10:F:G:W', '10:F:P:W', '10:F:Q:X', '10:G:J:K', '10:J:V:X', '10:K:P:X', '11:H:K:X',
        '11:J:K:P', '11:K:X:Y', '12:B:J:Y', '12:F:J:M', '12:F:V:W', '12:G:H:J', '12:H:W:X', '13:D:J:K',
        '13:G:J:X', '14:F:J:X', '14:M:Q:W', '15:F:K:Y', '15:G:K:Y', '3:A:B:F', '3:A:B:J', '3:A:C:H',
        '3:A:D:Z', '3:A:E:O', '3:A:F:P', '3:A:G:M', '3:A:G:T', '3:A:G:Z', '3:A:H:T', '3:A:I:Z',
        '3:A:J:R', '3:A:K:W', '3:A:K:Y', '3:A:S:X', '3:A:U:V', '3:A:U:W', '3:A:U:X', '3:A:U:Y',
        '3:B:C:N', '3:B:D:S', '3:B:D:U', '3:B:E:P', '3:B:E:W', '3:B:E:Z', '3:B:G:H', '3:B:G:O',
        '3:B:H:U', '3:B:I:M', '3:B:J:O', '3:B:L:X', '3:B:M:O', '3:B:O:R', '3:B:O:X', '3:B:O:Z',
        '3:B:P:Z', '3:C:D:G', '3:C:D:R', '3:C:E:Z', '3:C:G:S', '3:C:G:T', '3:C:I:Z', '3:C:J:U',
        '3:C:L:P', '3:C:M:R', '3:C:M:X', '3:C:N:S', '3:C:O:Q', '3:C:O:X', '3:C:P:R', '3:C:P:T',
        '3:C:P:U', '3:C:P:V', '3:C:T:V', '3:D:E:G', '3:D:E:J', '3:D:E:P', '3:D:E:U', '3:D:E:V',
        '3:D:E:Y', '3:D:F:P', '3:D:F:S', '3:D:G:N', '3:D:G:P', '3:D:H:I', '3:D:H:R', '3:D:I:V',
        '3:D:I:Z', '3:D:L:M', '3:D:M:R', '3:D:N:P', '3:D:N:S', '3:D:N:T', '3:D:N:U', '3:D:O:P',
        '3:D:O:Y', '3:D:O:Z', '3:D:P:T', '3:D:P:V', '3:D:R:S', '3:D:S:T', '3:D:U:Y', '3:E:F:M',
        '3:E:F:T', '3:E:J:T', '3:E:J:U', '3:E:K:L', '3:E:K:P', '3:E:L:Z', '3:E:M:P', '3:E:O:X',
        '3:E:O:Z', '3:E:Q:U', '3:E:S:X', '3:E:S:Y', '3:E:U:X', '3:E:U:Y', '3:F:G:I', '3:F:G:M',
        '3:F:G:O', '3:F:I:O', '3:F:L:S', '3:F:N:U', '3:F:O:Q', '3:F:O:R', '3:F:P:T', '3:G:H:U',
        '3:G:I:V', '3:G:I:Z', '3:G:L:O', '3:G:L:P', '3:G:M:O', '3:G:M:S', '3:G:M:U', '3:G:N:R',
        '3:G:O:Y', '3:G:P:S', '3:G:S:T', '3:G:T:V', '3:H:O:R', '3:H:P:U', '3:I:J:R', '3:I:J:S',
        '3:I:K:L', '3:I:K:R', '3:I:L:Z', '3:I:N:Q', '3:I:O:V', '3:I:P:U', '3:I:P:Z', '3:I:Q:S',
        '3:I:Q:U', '3:I:S:T', '3:J:S:T', '3:K:N:O', '3:K:O:P', '3:K:O:W', '3:K:R:U', '3:K:S:U',
        '3:K:S:Y', '3:K:U:Y', '3:L:M:T', '3:L:M:U', '3:L:N:U', '3:L:R:U', '3:L:U:X', '3:L:U:Y',
        '3:M:N:T', '3:M:O:P', '3:M:O:U', '3:M:O:X', '3:M:O:Y', '3:M:P:T', '3:M:P:U', '3:N:O:W',
        '3:N:P:V', '3:N:Q:S', '3:O:S:U', '3:O:U:Z', '3:P:S:T', '3:P:S:Y', '3:P:U:Y', '3:P:U:Z',
        '3:Q:S:U', '3:R:T:V', '3:R:U:Z', '3:S:U:V', '3:S:U:W', '3:T:U:Z', '4:A:B:F', '4:A:B:J',
        '4:A:B:W', '4:A:C:Z', '4:A:F:H', '4:A:F:W', '4:A:F:Y', '4:A:F:Z', '4:A:G:J', '4:A:G:K',
        '4:A:G:W', '4:A:H:Z', '4:A:J:V', '4:A:J:Z', '4:A:O:W', '4:A:R:W', '4:A:V:Y', '4:A:X:Z',
        '4:B:C:G', '4:B:C:M', '4:B:C:P', '4:B:D:G', '4:B:D:M', '4:B:E:W', '4:B:F:R', '4:B:G:T',
        '4:B:I:K', '4:B:I:V', '4:B:J:O', '4:B:K:L', '4:B:K:N', '4:B:K:S', '4:B:L:M', '4:B:L:N',
        '4:B:M:R', '4:B:M:Z', '4:B:N:Z', '4:B:R:Z', '4:B:S:X', '4:B:T:Z', '4:B:U:X', '4:C:D:G',
        '4:C:D:H', '4:C:D:L', '4:C:D:M', '4:C:D:P', '4:C:D:Q', '4:C:E:J', '4:C:E:W', '4:C:F:P',
        '4:C:F:T', '4:C:G:N', '4:C:H:Q', '4:C:H:V', '4:C:H:Z', '4:C:I:J', '4:C:I:Q', '4:C:I:X',
        '4:C:J:N', '4:C:K:Y', '4:C:L:Y', '4:C:M:R', '4:C:M:T', '4:C:M:Y', '4:C:N:U', '4:C:N:V',
        '4:C:N:Z', '4:C:O:Q', '4:C:O:W', '4:C:O:Z', '4:C:P:T', '4:C:R:V', '4:C:R:W', '4:C:R:Y',
        '4:C:S:V', '4:C:U:Y', '4:D:E:H', '4:D:F:M', '4:D:G:L', '4:D:H:O', '4:D:H:U', '4:D:H:Y',
        '4:D:I:X', '4:D:J:S', '4:D:K:N', '4:D:K:R', '4:D:K:Y', '4:D:L:V', '4:D:L:Y', '4:D:M:P',
        '4:D:N:Z', '4:D:O:V', '4:D:O:Z', '4:D:Q:U', '4:D:S:V', '4:D:U:W', '4:D:U:X', '4:E:F:J',
        '4:E:F:Y', '4:E:G:K', '4:E:H:J', '4:E:H:K', '4:E:H:W', '4:E:H:X', '4:E:I:Y', '4:E:J:M',
        '4:E:J:V', '4:E:J:Z', '4:E:K:M', '4:E:K:V', '4:E:K:Z', '4:E:M:V', '4:E:N:W', '4:E:O:W',
        '4:E:Q:Z', '4:F:G:R', '4:F:G:T', '4:F:G:U', '4:F:H:O', '4:F:H:Q', '4:F:H:U', '4:F:I:J',
        '4:F:I:M', '4:F:I:V', '4:F:I:W', '4:F:L:W', '4:F:M:S', '4:F:O:Q', '4:F:O:Z', '4:F:Q:S',
        '4:F:S:Z', '4:F:U:V', '4:G:H:N', '4:G:H:R', '4:G:H:U', '4:G:H:W', '4:G:H:Z', '4:G:I:J',
        '4:G:I:Y', '4:G:J:P', '4:G:K:O', '4:G:L:V', '4:G:O:P', '4:G:O:W', '4:G:O:Z', '4:G:P:R',
        '4:G:P:U', '4:G:R:T', '4:G:R:Z', '4:G:S:Z', '4:G:U:Z', '4:H:I:V', '4:H:I:Z', '4:H:J:O',
        '4:H:K:P', '4:H:K:R', '4:H:M:R', '4:H:M:S', '4:H:M:Y', '4:H:O:Q', '4:H:O:V', '4:H:O:X',
        '4:H:P:W', '4:H:P:Z', '4:H:U:X', '4:H:U:Z', '4:I:J:R', '4:I:J:S', '4:I:J:T', '4:I:K:U',
        '4:I:K:V', '4:I:K:Z', '4:I:M:P', '4:I:M:Z', '4:I:P:Z', '4:I:Q:Z', '4:I:R:W', '4:I:S:W',
        '4:I:V:Z', '4:I:X:Z', '4:J:K:N', '4:J:K:R', '4:J:M:P', '4:J:M:S', '4:J:O:R', '4:J:P:S',
        '4:K:L:T', '4:K:L:U', '4:K:L:W', '4:K:M:S', '4:K:N:R', '4:K:N:T', '4:K:O:Z', '4:K:S:V',
        '4:K:T:Y', '4:L:M:P', '4:L:M:R', '4:L:M:Y', '4:L:N:R', '4:L:N:V', '4:L:N:X', '4:L:S:W',
        '4:L:T:Y', '4:L:T:Z', '4:L:V:X', '4:L:W:Y', '4:M:N:X', '4:M:P:R', '4:M:P:T', '4:M:P:V',
        '4:M:R:Y', '4:M:S:V', '4:M:U:Y', '4:N:P:R', '4:N:P:X', '4:N:Q:S', '4:N:R:S', '4:N:R:Z',
        '4:N:S:Z', '4:N:U:Y', '4:O:R:Z', '4:O:T:X', '4:O:T:Z', '4:O:V:X', '4:O:Y:Z', '4:P:R:V',
        '4:P:R:X', '4:P:R:Y', '4:P:T:X', '4:Q:S:T', '4:Q:T:U', '4:R:S:Z', '4:R:T:X', '4:R:U:W',
        '4:R:U:X', '4:R:X:Y', '4:S:U:X', '4:S:V:X', '4:S:W:Y', '4:S:X:Y', '4:S:Y:Z', '4:T:U:Y',
        '4:U:X:Y', '4:U:Y:Z', '5:A:Q:Z', '5:A:W:Z', '5:B:C:J', '5:B:C:Q', '5:B:C:V', '5:B:D:K',
        '5:B:D:M', '5:B:F:Z', '5:B:G:H', '5:B:H:J', '5:B:H:M', '5:B:I:W', '5:B:L:W', '5:B:M:W',
        '5:B:N:P', '5:B:N:Q', '5:B:Q:R', '5:B:R:W', '5:C:D:J', '5:C:D:V', '5:C:F:M', '5:C:F:V',
        '5:C:F:Y', '5:C:G:K', '5:C:G:P', '5:C:G:Z', '5:C:J:Y', '5:C:K:M', '5:C:K:Q', '5:C:N:Q',
        '5:C:Q:S', '5:C:V:Y', '5:D:F:G', '5:D:F:J', '5:D:F:M', '5:D:F:P', '5:D:G:P', '5:D:H:K',
        '5:D:H:P', '5:D:H:Z', '5:D:J:M', '5:D:J:T', '5:D:K:V', '5:D:L:W', '5:D:N:Q', '5:D:O:Q',
        '5:D:R:X', '5:D:S:W', '5:D:T:W', '5:E:J:X', '5:E:Q:Y', '5:F:H:M', '5:F:H:W', '5:F:J:O',
        '5:F:K:W', '5:F:K:Y', '5:F:M:P', '5:F:Q:W', '5:F:T:W', '5:F:Y:Z', '5:G:H:W', '5:G:H:Z',
        '5:G:K:P', '5:G:K:W', '5:G:M:Z', '5:G:N:X', '5:G:R:W', '5:G:S:X', '5:G:V:Y', '5:H:K:Z',
        '5:H:L:W', '5:H:M:V', '5:H:N:W', '5:H:T:W', '5:H:T:X', '5:I:Q:Y', '5:J:L:M', '5:J:M:T',
        '5:J:N:Y', '5:J:O:V', '5:J:O:Y', '5:J:P:R', '5:J:Q:R', '5:J:Y:Z', '5:K:M:P', '5:K:M:Y',
        '5:K:T:W', '5:K:U:V', '5:L:M:V', '5:L:N:W', '5:L:P:W', '5:L:Q:T', '5:L:R:W', '5:L:T:W',
        '5:L:U:W', '5:M:N:V', '5:M:P:X', '5:N:Q:S', '5:N:R:W', '5:N:R:X', '5:O:V:X', '5:P:W:Z',
        '5:Q:R:Z', '5:Q:U:W', '5:R:W:Y', '6:B:D:W', '6:B:F:K', '6:B:F:Y', '6:B:G:W', '6:B:K:P',
        '6:B:N:W', '6:B:W:Y', '6:B:W:Z', '6:C:G:X', '6:C:M:W', '6:C:P:V', '6:C:Q:Y', '6:C:U:W',
        '6:D:F:K', '6:D:F:X', '6:D:G:K', '6:D:H:V', '6:D:H:W', '6:D:J:Q', '6:D:L:Q', '6:D:M:Q',
        '6:D:W:Y', '6:E:K:Q', '6:F:G:H', '6:F:G:K', '6:F:H:K', '6:F:I:W', '6:F:P:X', '6:F:T:W',
        '6:F:U:W', '6:G:H:Q', '6:G:K:L', '6:G:K:V', '6:G:K:Z', '6:G:M:W', '6:G:W:Y', '6:H:J:M',
        '6:H:J:P', '6:H:N:Q', '6:H:P:V', '6:H:R:W', '6:H:W:Y', '6:J:K:L', '6:J:K:M', '6:J:M:V',
        '6:J:M:Y', '6:J:R:Y', '6:J:X:Y', '6:J:Y:Z', '6:K:M:V', '6:K:O:Q', '6:K:T:W', '6:K:U:V',
        '6:L:Q:V', '6:L:V:X', '6:M:U:W', '6:N:Q:W', '6:P:W:Z', '6:Q:R:Y', '6:Q:S:W', '6:Q:W:Z',
        '6:U:W:Z', '6:W:Y:Z', '7:B:D:W', '7:B:G:P', '7:B:H:W', '7:B:J:K', '7:B:U:W', '7:C:F:J',
        '7:C:G:W', '7:C:U:W', '7:D:F:J', '7:D:H:Q', '7:D:J:P', '7:D:M:W', '7:D:Q:X', '7:E:W:X',
        '7:F:G:W', '7:F:H:W', '7:F:K:P', '7:F:K:X', '7:F:K:Y', '7:F:M:W', '7:F:T:W', '7:F:W:Y',
        '7:G:H:Q', '7:G:J:V', '7:G:J:X', '7:H:J:K', '7:H:P:W', '7:I:K:X', '7:I:Q:W', '7:I:W:X',
        '7:J:K:L', '7:J:S:W', '7:J:W:Y', '7:K:M:Q', '7:K:P:V', '7:K:U:W', '7:P:V:Y', '7:U:W:Z',
        '8:C:F:W', '8:C:K:X', '8:D:J:W', '8:D:W:Y', '8:G:J:X', '8:G:K:X', '8:J:K:Y', '8:K:M:V',
        '8:K:T:X', '8:K:U:X', '8:K:V:W', '8:W:Y:Z', '9:C:K:Q', '9:C:W:X', '9:D:G:K', '9:D:J:X',
        '9:F:J:P', '9:F:K:X', '9:G:J:K', '9:G:P:W', '9:H:J:V', '9:H:K:X', '9:I:J:W', '9:J:L:X',
        '9:J:M:W', '9:T:V:W',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * $letter1 et $letter2 : les deux lettres "avec" de la page palier 2 source, dans n'importe
     * quel ordre (triees ici par defense, meme si l'appelant les passe deja triees -- WordListFilters
     * ksort() garantit deja $letter1 < $letter2 quand elles viennent de $filters->withLetters).
     */
    public function build(int $length, string $letter1, string $letter2): AvecThreeLettersLinks
    {
        $pair = [$letter1, $letter2];
        sort($pair, SORT_STRING);
        [$x, $y] = $pair;

        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'length_with_triple'"
            . ' AND (list_key LIKE ? OR list_key LIKE ? OR list_key LIKE ?)'
        );
        $statement->execute([
            $length . ':' . $x . ':' . $y . ':%',
            $length . ':' . $x . ':%:' . $y,
            $length . ':%:' . $x . ':' . $y,
        ]);

        $links = [];

        foreach ($statement as $row) {
            $key = (string) $row['list_key'];

            if (
                in_array($key, self::DUPLICATE_PARENT_KEYS, true)
                || in_array($key, self::SIBLING_DUPLICATE_KEYS, true)
                || in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)
            ) {
                continue;
            }

            $parts = explode(':', $key, 4);
            $triple = [$parts[1], $parts[2], $parts[3]];

            $partner = null;
            foreach ($triple as $candidate) {
                if ($candidate !== $x && $candidate !== $y) {
                    $partner = $candidate;
                    break;
                }
            }

            if ($partner === null) {
                // Defensif, jamais attendu : $x et $y sont toujours distincts (page palier 2
                // source), donc exactement une des trois lettres du triplet stocke n'est ni $x
                // ni $y. Ignore silencieusement plutot que de produire un lien incorrect.
                continue;
            }

            $count = (int) $row['count'];
            $path = $length . '-lettres/avec/' . strtolower($x) . '/' . strtolower($y) . '/' . strtolower($partner);
            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['letter' => $partner, 'url' => $url, 'count' => $count];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new AvecThreeLettersLinks(links: $links, queryCount: 1);
    }
}
