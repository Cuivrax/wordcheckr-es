<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\StartEndWithLinks depuis list_counts (list_type 'start_end_with'), meme
 * principe que App\Search\PositionLinksBuilder / App\Search\AvecSansLengthLinksBuilder -- une
 * seule requete triviale, aucun calcul sur `terms` au runtime (voir
 * scripts/build_explore_hub_counts.php pour la mesure qui impose ce detour, et
 * scripts/bench_start_end_with_build.php pour la comparaison chiffree avec l'alternative SQL).
 *
 * list_key est toujours "{debut}:{fin}:{lettre}" pour 'start_end_with' -- une seule direction de
 * lecture necessaire (contrairement a App\Search\LetterCombinedLinksBuilder/
 * App\Search\LengthCombinedLinksBuilder, qui doivent lire "start_end"/"length_start_end" dans les
 * DEUX sens depuis deux pages source distinctes) : la page source de ce maillage est toujours
 * /mots/commencant/{X}/terminant/{Y}, debut ET fin sont donc TOUJOURS connus simultanement --
 * `list_key LIKE '{debut}:{fin}:%'` reste un prefixe exact, servi par l'index de cle primaire.
 *
 * Budget runtime : 1 requete SQLite par page.
 *
 * Trois filtres anti-doublon successifs, appliques dans build() ci-dessous (voir chaque
 * constante pour le detail) : DUPLICATE_CONTENT_KEYS (D-037, doublon avec la page PARENTE
 * commencant+terminant), SIBLING_DUPLICATE_KEYS (D-038, doublon entre pages SOEURS "avec" du
 * meme panier), CROSS_DUPLICATE_LENGTH_KEYS (3e audit consolide, 2026-08-19, doublon CROISE avec
 * la page LONGUEUR de la MEME paire, App\Search\LengthLinksBuilder::byStartEnd -- une famille
 * SEO differente, Family::WORD_LIST_COMBINED contre Family::WORD_LIST_COMBINED_WITH_LETTER).
 */
final class StartEndWithLinksBuilder
{
    /**
     * Les 227 triples (debut, fin, lettre) a contenu strictement DUPLIQUE avec leur page parente
     * /mots/commencant/{debut}/terminant/{fin} (sans "avec") -- distinct du collapse D-032
     * (lettre "avec" == debut ou fin, deja gere par la comparaison $url !== $parentUrl ci-dessous,
     * jamais reintroduit ici) : ici l'URL enfant EST DIFFERENTE de l'URL parente (lettre "avec"
     * ni debut ni fin), mais TOUS les mots de la paire commencant+terminant contiennent deja
     * cette lettre -- ajouter la contrainte "avec" ne retire aucun mot, le contenu reste
     * identique. Exemple trouve par l'audit consolide (2026-08-18, NO GO) : la paire F:Q (longueur
     * 3) ne contient que FAQ -- /mots/commencant/f/terminant/q/avec/a liste EXACTEMENT le meme
     * contenu que /mots/commencant/f/terminant/q (deja indexee). Autre exemple : la paire X:O ne
     * contient que XIPHO -- /mots/commencant/x/terminant/o/avec/h, .../avec/i et .../avec/p sont
     * TOUTES identiques entre elles ET a leur parent /mots/commencant/x/terminant/o.
     *
     * Regle de detection (meme patron que App\Search\LengthLinksBuilder::
     * DUPLICATE_START_END_KEYS, et que le cas 'combined_with_length' de scripts/
     * propose_seo_batch.php, ligne ~704) : une ligne list_counts 'start_end_with'
     * "{debut}:{fin}:{lettre}" est un doublon de contenu SI ET SEULEMENT SI son `count` est
     * EXACTEMENT EGAL au `count` de l'entree parente correspondante 'start_end' "{debut}:{fin}"
     * (sans la lettre "avec") -- ca signifie que TOUS les mots de la paire contiennent deja cette
     * lettre.
     *
     * Verifie par DEUX methodes independantes (meme discipline que les 52 paires D-025/I-1) :
     * 1. comparaison list_counts ('start_end_with' vs 'start_end', count enfant === count parent),
     *    sur les 11 348 lignes 'start_end_with' non degenerees D-032 (1 198 exclues avant
     *    comparaison, meme raison que ci-dessus) -- 227 trouvees
     * 2. balayage complet et INDEPENDANT (sans partir de list_counts), requete directe sur `terms`
     *    pour les 26 lettres x 611 paires commencant+terminant reelles (15 886 combinaisons, hors
     *    degenerees) : COUNT(*) WHERE debut ET fin ET instr(normalized, lettre) > 0, compare a
     *    COUNT(*) WHERE debut ET fin seuls -- 227 trouvees, 0 divergence dans les deux sens avec
     *    la methode 1
     * Les deux exemples cites par l'audit (F:Q:A, X:O:H/I/P) confirmes presents dans la liste.
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022, integrity_check = ok). Une reconstruction future de la base devra
     * revalider cette liste (meme avertissement que DUPLICATE_START_END_KEYS).
     *
     * @var list<string>
     */
    private const DUPLICATE_CONTENT_KEYS = [
        'A:J:D', 'B:J:D', 'B:J:O', 'B:J:R', 'B:Q:C', 'C:J:D', 'C:J:I', 'C:W:O',
        'D:Q:A', 'D:Q:C', 'D:Q:R', 'D:W:A', 'F:J:E', 'F:J:U', 'F:Q:A', 'F:W:L',
        'F:W:O', 'G:W:O', 'H:J:A', 'H:Z:E', 'I:B:C', 'I:B:E', 'I:B:L', 'I:B:N',
        'I:B:R', 'I:B:T', 'I:B:U', 'I:Q:A', 'I:Q:U', 'I:W:E', 'I:W:N', 'I:W:R',
        'I:W:T', 'I:W:V', 'I:Z:E', 'J:F:I', 'J:H:O', 'J:X:U', 'K:C:A', 'K:J:A',
        'K:J:M', 'K:J:N', 'K:J:R', 'K:J:U', 'K:V:I', 'K:V:L', 'M:J:A', 'M:V:A',
        'M:V:H', 'M:V:O', 'M:V:S', 'M:W:A', 'M:W:H', 'M:W:L', 'M:W:O', 'M:W:R',
        'M:W:S', 'M:Z:E', 'N:H:A', 'N:H:L', 'N:P:A', 'N:P:B', 'N:P:I', 'N:P:O',
        'N:P:R', 'N:W:A', 'N:W:D', 'N:W:T', 'N:Z:E', 'O:J:I', 'O:J:N', 'O:Q:C',
        'O:Q:E', 'O:Q:I', 'O:Q:R', 'O:Q:S', 'O:Q:U', 'O:W:A', 'O:W:L', 'O:W:T',
        'O:W:U', 'O:Z:E', 'P:Z:E', 'Q:D:U', 'Q:K:U', 'Q:L:U', 'Q:O:U', 'Q:P:C',
        'Q:P:E', 'Q:P:I', 'Q:P:K', 'Q:P:S', 'Q:P:T', 'Q:P:U', 'Q:X:U', 'R:Q:C',
        'R:Q:E', 'R:Q:M', 'R:Q:O', 'R:Q:S', 'R:Q:T', 'R:W:A', 'R:W:C', 'R:W:H',
        'R:W:I', 'R:W:K', 'R:W:S', 'S:V:A', 'S:V:C', 'S:V:I', 'T:J:A', 'T:J:K',
        'T:J:O', 'T:Q:A', 'T:Q:C', 'T:Q:E', 'T:Q:H', 'T:Q:M', 'T:W:A', 'T:W:H',
        'T:W:K', 'T:W:L', 'T:W:O', 'T:W:S', 'U:B:S', 'U:G:I', 'U:G:N', 'U:K:B',
        'U:K:E', 'U:K:Z', 'U:V:I', 'U:V:N', 'U:Z:E', 'V:B:C', 'V:B:D', 'V:B:E',
        'V:B:I', 'V:B:L', 'V:B:O', 'V:B:U', 'V:K:L', 'V:Q:C', 'V:Q:D', 'V:Q:E',
        'V:Q:I', 'V:Q:O', 'V:V:A', 'V:Z:E', 'W:C:O', 'W:L:A', 'W:L:B', 'W:L:E',
        'W:L:J', 'W:L:N', 'W:L:O', 'W:L:R', 'W:L:U', 'W:O:R', 'W:X:A', 'X:C:A',
        'X:C:D', 'X:C:E', 'X:C:G', 'X:C:I', 'X:C:N', 'X:C:O', 'X:C:S', 'X:C:T',
        'X:G:A', 'X:G:I', 'X:G:N', 'X:O:H', 'X:O:I', 'X:O:P', 'X:R:E', 'X:R:O',
        'X:T:A', 'X:T:G', 'X:T:H', 'X:T:I', 'X:T:O', 'X:T:P', 'X:T:R', 'X:U:G',
        'X:U:I', 'X:U:N', 'X:U:O', 'X:X:A', 'X:X:C', 'X:X:E', 'X:X:I', 'X:X:N',
        'X:X:O', 'X:X:R', 'X:X:S', 'X:X:T', 'X:X:U', 'X:Z:A', 'X:Z:E', 'X:Z:G',
        'X:Z:H', 'X:Z:I', 'X:Z:O', 'X:Z:P', 'X:Z:R', 'Y:G:A', 'Y:G:N', 'Y:P:O',
        'Y:P:U', 'Y:Q:E', 'Y:Q:L', 'Y:V:O', 'Y:V:U', 'Y:X:U', 'Y:Y:A', 'Y:Y:O',
        'Y:Z:E', 'Z:J:B', 'Z:J:D', 'Z:J:E', 'Z:J:O', 'Z:J:U', 'Z:Q:D', 'Z:Q:E',
        'Z:Q:I', 'Z:Q:N', 'Z:X:U',
    ];

    /**
     * Doublons de contenu entre pages SOEURS "avec" (I-A, 2e audit consolide de la serie,
     * 2026-08-18, GO avec ce point non bloquant) : distinct de DUPLICATE_CONTENT_KEYS ci-dessus
     * (comparaison a la page PARENTE, sans "avec") -- ici, DEUX lettres "avec" DIFFERENTES du
     * MEME panier commencant+terminant (ni l'une ni l'autre egale au panier complet, donc aucune
     * des deux n'est detectee par DUPLICATE_CONTENT_KEYS) isolent neanmoins EXACTEMENT le meme
     * SOUS-ENSEMBLE de mots -- ex. la paire A:B (6 mots : AB, ACHEB, AEROCLUB, ANTIPUB, APLOMB,
     * AUTOLUB) : "avec/c" et "avec/e" listent toutes deux EXACTEMENT {ACHEB, AEROCLUB}, ni plus
     * ni moins -- /mots/commencant/a/terminant/b/avec/c et .../avec/e afficheraient un contenu
     * strictement identique sous deux URL distinctes, aucune des deux n'etant la page parente.
     *
     * Regle de detection : pour une paire {debut}:{fin} donnee, deux lettres "avec" Z1 et Z2
     * (toutes deux SURVIVANTES du filtre D-032 + DUPLICATE_CONTENT_KEYS ci-dessus, c-a-d
     * actuellement produites par build()) sont un doublon de contenu SOEUR SSI, pour TOUS les
     * mots du panier {debut}:{fin}, la presence de Z1 et la presence de Z2 coincident toujours
     * (Z1 et Z2 apparaissent et disparaissent toujours ENSEMBLE d'un mot a l'autre) -- ca
     * signifie que le sous-ensemble de mots contenant Z1 est EXACTEMENT le meme ensemble que
     * celui contenant Z2, pas seulement un ensemble de meme taille. Regroupe les lettres liees
     * par cette relation (classes d'equivalence) ; canonicalisation deterministe : la lettre
     * alphabetiquement la plus petite de chaque classe reste candidate a l'indexation/au
     * maillage, les autres membres de la classe sont exclus ici.
     *
     * Verifie par DEUX methodes independantes sur le MEME panier de mots recupere une seule fois
     * par paire (611 paires reelles, 9 923 lettres "avec" survivantes au total) :
     * 1. regroupement DIRECT par egalite d'ensemble exacte : cle = liste triee des mots
     *    concernes, jointe par un separateur non ambigu -- comparaison de chaines completes,
     *    aucune notion de hash approximatif, aucune collision possible
     * 2. regroupement par PROPRIETE DE COINCIDENCE, algorithme different (suggere par l'audit) :
     *    pour chaque paire de lettres candidates (Z1, Z2) du meme panier, teste si presence(Z1)
     *    == presence(Z2) pour TOUS les mots du panier (union-find sur cette relation binaire)
     * 0 divergence entre les deux methodes sur l'integralite des 611 paires reelles (pas un
     * echantillon) -- 283 groupes trouves (169 paires affectees), 428 lettres a exclure au
     * total (unicite verifiee : aucune lettre n'appartient a deux groupes differents). Un
     * troisieme sondage manuel direct contre `terms` (A:B, W:Z, X:I) confirme les listes de mots
     * exactes. Aucun cas trouve sur l'axe 4 (voir PrefixAvecLinksBuilder::SIBLING_DUPLICATE_KEYS,
     * liste vide) -- les paniers mono-lettre restent trop grands pour qu'une paire de lettres y
     * soit toujours liee.
     *
     * Rapport complet (liste des 283 groupes, paires/prefixes concernes, mots impliques) :
     * reports/query-plans/avec-doublons-soeurs-correctif.md
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022). Une reconstruction future devra revalider cette liste (meme
     * avertissement que DUPLICATE_CONTENT_KEYS ci-dessus).
     *
     * @var list<string>
     */
    private const SIBLING_DUPLICATE_KEYS = [
        'A:B:E', 'A:B:N', 'A:B:O', 'A:K:S', 'A:K:U', 'A:M:Z', 'A:V:N', 'A:Y:O',
        'B:B:R', 'B:B:T', 'B:Q:N', 'B:Q:P', 'B:Q:T', 'B:W:G', 'B:W:L', 'B:W:N',
        'B:W:O', 'B:W:U', 'B:Y:L', 'C:B:U', 'C:H:W', 'C:V:N', 'C:V:U', 'C:W:L',
        'D:B:I', 'D:B:O', 'D:B:W', 'D:C:P', 'D:H:I', 'D:H:O', 'D:H:T', 'D:K:T',
        'D:P:T', 'E:H:C', 'E:H:S', 'E:K:F', 'E:K:L', 'E:K:N', 'E:K:O', 'E:K:R',
        'E:K:T', 'E:K:U', 'E:P:C', 'E:P:S', 'E:V:R', 'E:V:U', 'E:Y:U', 'F:B:L',
        'F:B:M', 'F:B:N', 'F:B:S', 'F:B:U', 'F:C:Q', 'F:H:P', 'F:K:S', 'F:P:D',
        'F:P:H', 'F:P:M', 'F:P:R', 'F:P:S', 'F:Y:O', 'G:B:U', 'G:C:S', 'G:C:U',
        'G:C:Z', 'G:G:T', 'G:H:N', 'G:H:R', 'G:K:S', 'H:B:J', 'H:C:R', 'H:C:V',
        'H:D:V', 'H:F:T', 'H:F:Y', 'H:H:Z', 'H:K:L', 'H:Y:U', 'I:C:M', 'I:C:N',
        'I:C:P', 'I:D:J', 'I:G:R', 'I:H:N', 'I:K:L', 'I:K:O', 'I:K:T', 'I:K:V',
        'I:Q:M', 'I:Q:P', 'I:Y:G', 'I:Y:N', 'I:Y:O', 'I:Y:R', 'I:Y:U', 'J:C:F',
        'J:C:I', 'J:F:C', 'J:G:H', 'J:G:N', 'J:G:S', 'J:H:P', 'J:H:S', 'J:K:L',
        'J:K:R', 'J:K:S', 'J:K:T', 'J:K:U', 'J:K:Y', 'J:L:Y', 'J:M:L', 'J:M:T',
        'J:O:L', 'J:O:R', 'J:O:V', 'J:P:K', 'J:P:O', 'J:Y:T', 'K:C:P', 'K:C:S',
        'K:D:L', 'K:F:L', 'K:F:S', 'K:F:T', 'K:G:P', 'K:G:T', 'K:G:X', 'K:H:N',
        'K:M:R', 'K:M:V', 'K:M:Z', 'K:P:B', 'K:P:H', 'K:P:T', 'K:V:H', 'K:V:N',
        'K:V:O', 'K:X:R', 'K:X:V', 'K:Y:P', 'L:B:I', 'L:B:M', 'L:B:P', 'L:B:U',
        'L:H:M', 'L:K:M', 'L:K:U', 'L:P:E', 'L:P:H', 'L:P:R', 'L:P:S', 'L:V:O',
        'L:V:T', 'M:B:R', 'M:B:T', 'M:P:G', 'M:P:L', 'M:P:S', 'M:P:T', 'N:B:H',
        'N:B:L', 'N:B:T', 'N:C:G', 'N:G:C', 'N:G:H', 'N:G:L', 'N:G:T', 'N:H:G',
        'N:H:I', 'N:H:R', 'N:H:U', 'N:Q:H', 'N:Q:M', 'N:Q:S', 'N:Q:T', 'N:Q:U',
        'N:Q:V', 'N:U:K', 'N:Y:O', 'N:Y:U', 'O:C:F', 'O:C:I', 'O:C:M', 'O:C:T',
        'O:F:Y', 'O:G:V', 'O:H:E', 'O:H:K', 'O:H:P', 'O:H:R', 'O:H:S', 'O:H:Y',
        'O:K:N', 'O:K:Z', 'O:P:I', 'O:P:N', 'O:P:R', 'O:P:S', 'O:P:T', 'O:P:U',
        'O:Y:I', 'O:Y:J', 'O:Y:N', 'O:Y:R', 'O:Y:W', 'P:B:M', 'P:H:V', 'P:W:H',
        'P:W:I', 'P:W:L', 'P:W:O', 'P:W:S', 'P:Y:H', 'Q:G:H', 'Q:G:N', 'Q:G:O',
        'Q:G:S', 'Q:H:D', 'Q:H:I', 'Q:H:P', 'Q:K:E', 'Q:K:R', 'Q:K:T', 'Q:M:I',
        'Q:O:H', 'Q:U:M', 'Q:U:R', 'Q:Y:I', 'Q:Y:N', 'Q:Y:T', 'Q:Y:W', 'R:B:T',
        'R:G:D', 'R:K:Z', 'R:P:M', 'R:P:O', 'R:P:V', 'R:V:O', 'R:V:S', 'R:V:U',
        'S:W:U', 'S:Y:W', 'T:B:M', 'T:B:P', 'T:B:R', 'T:B:Y', 'T:H:P', 'T:K:J',
        'T:P:L', 'T:P:W', 'T:Y:H', 'T:Y:V', 'U:C:I', 'U:C:L', 'U:C:S', 'U:C:T',
        'U:D:G', 'U:D:I', 'U:D:L', 'U:D:P', 'U:D:R', 'U:D:S', 'U:D:T', 'U:F:C',
        'U:F:E', 'U:F:L', 'U:G:O', 'U:G:P', 'U:G:W', 'U:G:X', 'U:G:Y', 'U:M:X',
        'U:O:N', 'U:P:E', 'U:P:F', 'U:P:I', 'U:P:L', 'U:P:N', 'U:P:R', 'V:C:M',
        'V:C:U', 'V:C:Y', 'V:F:N', 'V:F:S', 'V:G:O', 'V:G:T', 'V:H:C', 'V:H:L',
        'V:H:S', 'V:K:I', 'V:K:P', 'V:K:U', 'V:P:D', 'V:P:L', 'V:P:M', 'V:P:U',
        'V:Y:U', 'W:A:M', 'W:B:N', 'W:B:S', 'W:B:T', 'W:B:U', 'W:C:D', 'W:C:E',
        'W:C:I', 'W:C:K', 'W:C:L', 'W:C:R', 'W:D:H', 'W:D:P', 'W:F:N', 'W:F:P',
        'W:F:S', 'W:G:S', 'W:G:U', 'W:H:T', 'W:H:U', 'W:K:C', 'W:K:E', 'W:K:M',
        'W:K:R', 'W:K:T', 'W:K:U', 'W:M:C', 'W:M:E', 'W:M:N', 'W:O:D', 'W:O:E',
        'W:O:I', 'W:O:P', 'W:O:T', 'W:P:O', 'W:T:K', 'W:U:N', 'W:U:P', 'W:U:S',
        'W:U:Z', 'W:X:E', 'W:X:H', 'W:X:R', 'W:Y:I', 'W:Y:K', 'W:Y:L', 'W:Y:S',
        'W:Z:K', 'W:Z:M', 'W:Z:O', 'X:A:P', 'X:I:G', 'X:I:H', 'X:I:O', 'X:I:P',
        'X:I:R', 'X:I:Y', 'X:L:N', 'X:L:R', 'X:L:S', 'X:M:D', 'X:M:E', 'X:M:H',
        'X:M:I', 'X:M:L', 'X:M:N', 'X:M:O', 'X:M:U', 'X:R:G', 'X:R:H', 'X:R:I',
        'X:R:P', 'X:R:Y', 'X:T:Y', 'X:Z:Y', 'Y:D:L', 'Y:G:H', 'Y:G:L', 'Y:G:R',
        'Y:G:T', 'Y:H:E', 'Y:H:R', 'Y:H:T', 'Y:H:U', 'Y:K:M', 'Y:L:E', 'Y:L:T',
        'Y:M:E', 'Y:M:R', 'Y:M:T', 'Y:M:U', 'Y:O:I', 'Y:O:J', 'Y:O:T', 'Y:R:H',
        'Y:R:K', 'Y:T:Q', 'Y:U:G', 'Y:U:M', 'Y:U:N', 'Y:U:P', 'Y:U:S', 'Y:X:K',
        'Y:X:N', 'Y:X:R', 'Y:X:T', 'Y:Y:M', 'Y:Y:N', 'Y:Y:R', 'Z:B:R', 'Z:C:G',
        'Z:C:R', 'Z:C:S', 'Z:C:T', 'Z:C:Y', 'Z:D:I', 'Z:D:M', 'Z:D:R', 'Z:F:N',
        'Z:F:R', 'Z:F:U', 'Z:H:O', 'Z:H:Y', 'Z:K:C', 'Z:K:N', 'Z:K:W', 'Z:M:L',
        'Z:M:P', 'Z:M:R', 'Z:M:Y', 'Z:O:V',
    ];

    /**
     * Doublons de contenu CROISES entre DEUX FAMILLES DIFFERENTES qui partagent le meme panier de
     * base commencant+terminant {debut}:{fin} (3e audit consolide de la serie, 2026-08-19, NO GO) :
     * - axe 1 (App\Search\LengthLinksBuilder::byStartEnd, "/mots/{N}-lettres/commencant/{X}/
     *   terminant/{Y}", D-027/D-035, apres exclusion des 52 doublons D-025/I-1) -- tranche le
     *   panier {debut}:{fin} PAR LONGUEUR
     * - axe 2 (ce builder, "/mots/commencant/{X}/terminant/{Y}/avec/{Z}", D-033/D-035/D-037/D-038,
     *   apres exclusion des 1198 lignes degenerees D-032, des 227 doublons-parent DUPLICATE_
     *   CONTENT_KEYS et des 428 doublons-soeurs SIBLING_DUPLICATE_KEYS) -- tranche le MEME panier
     *   PAR LETTRE "avec"
     * DUPLICATE_CONTENT_KEYS et SIBLING_DUPLICATE_KEYS ci-dessus comparent chacun une page "avec"
     * a une autre page "avec" (parente ou soeur) DE LA MEME FAMILLE -- aucun des deux ne compare
     * jamais a l'AUTRE famille (byStartEnd). Preuve sur pieces (audit) : la paire X:M (2 mots au
     * total, XALAM et XENODOCHIUM) -- "/mots/5-lettres/commencant/x/terminant/m" (axe 1) et
     * "/mots/commencant/x/terminant/m/avec/a" (axe 2, A = lettre canonique survivante du groupe
     * soeur {A,L} apres D-038) contiennent tous deux EXACTEMENT le meme mot unique, XALAM -- deux
     * URL distinctes, deux familles distinctes (Family::WORD_LIST_COMBINED contre Family::
     * WORD_LIST_COMBINED_WITH_LETTER), un seul et meme contenu.
     *
     * Regle de detection : pour une paire {debut}:{fin} donnee, une tranche LONGUEUR L (survivante
     * axe 1) et une tranche LETTRE "avec" Z (survivante axe 2) sont un doublon croise SSI
     * l'ensemble EXACT des mots de longueur L est EGAL a l'ensemble EXACT des mots contenant Z --
     * pas seulement un meme compte (les deux tranches ne sont PAS l'une un sous-ensemble naturel de
     * l'autre, contrairement aux comparaisons parent/enfant ou soeur/soeur ci-dessus, ou le
     * sous-ensemble est garanti par construction -- ici une simple egalite de COMPTE ne suffirait
     * pas a demontrer une egalite d'ENSEMBLE).
     *
     * Regle de priorite (deja tranchee cote produit, meme principe que D-025 -- la forme la plus
     * simple/generale gagne sur la plus specifique) : en cas de collision, la variante LONGUEUR
     * (axe 1, LengthLinksBuilder) reste candidate a l'indexation ; la variante "avec" (axe 2, CE
     * builder) est retiree ici. LengthLinksBuilder n'est PAS modifie par ce correctif.
     *
     * Verifie par DEUX methodes independantes sur les 611 paires reelles :
     * 1. appel des VRAIS builders (LengthLinksBuilder::build($length) pour les 14 longueurs 2-15,
     *    puis StartEndWithLinksBuilder::build($start,$end) pour les 611 paires), regroupement par
     *    paire, puis pour chaque couple (longueur survivante, lettre survivante) de la MEME paire :
     *    panier recupere une seule fois par paire (ORDER BY normalized), tranche par longueur
     *    (strlen()) et par lettre (str_contains()), comparaison de tableau complete (===) --
     *    559 paires ont des survivants sur les deux axes a la fois, 101 383 couples (longueur,
     *    lettre) compares, 333 egalites trouvees
     * 2. pour chacun des 333 couples trouves par la methode 1, requete SQL DIRECTE et INDEPENDANTE
     *    (COUNT(length=L), COUNT(instr(normalized,Z)>0), COUNT(length=L AND instr(normalized,Z)>0)
     *    sur le panier {debut}:{fin}) : les trois comptes DOIVENT etre strictement egaux entre eux
     *    ET egaux au compte de mots trouve par la methode 1 -- une egalite triple prouve une
     *    egalite d'ensemble (sous-ensemble dans les deux sens) sans jamais comparer de tableaux --
     *    333/333 confirmes, 0 divergence
     * Les 9 exemples cites par l'audit (X:M x2, B:W x2, U:P x2, E:K x1, W:K x2) confirmes presents
     * dans la liste. Rapport complet (methodologie detaillee, liste des 333 couples, mots
     * impliques) : reports/query-plans/avec-doublons-croises-longueur-correctif.md.
     *
     * Disjoint par construction de DUPLICATE_CONTENT_KEYS et SIBLING_DUPLICATE_KEYS ci-dessus
     * (verifie explicitement, 0 intersection dans les deux sens) : cette liste ne compare jamais
     * une page "avec" a une autre page "avec", uniquement a une page LONGUEUR de l'autre famille.
     *
     * Liste figee : valable pour l'etat actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchange depuis D-022). Une reconstruction future devra revalider cette liste (meme
     * avertissement que DUPLICATE_CONTENT_KEYS/SIBLING_DUPLICATE_KEYS ci-dessus).
     *
     * @var list<string>
     */
    private const CROSS_DUPLICATE_LENGTH_KEYS = [
        'A:B:H', 'A:B:M', 'A:B:R', 'A:B:T', 'A:F:Y', 'A:K:F', 'A:K:H', 'A:K:L',
        'A:P:M', 'A:P:O', 'A:V:D', 'A:V:I', 'A:Y:U', 'B:B:C', 'B:B:E', 'B:K:D',
        'B:K:Z', 'B:Q:Y', 'B:W:A', 'B:W:M', 'C:B:K', 'C:B:N', 'C:B:P', 'C:H:D',
        'C:K:Y', 'C:P:K', 'C:P:S', 'C:Q:A', 'C:Q:I', 'C:W:M', 'D:B:C', 'D:B:K',
        'D:B:M', 'D:C:G', 'D:C:H', 'D:H:B', 'D:J:E', 'D:K:M', 'D:O:V', 'D:P:B',
        'D:P:K', 'D:W:U', 'D:Y:X', 'E:C:F', 'E:G:A', 'E:G:I', 'E:H:M', 'E:H:R',
        'E:K:C', 'E:P:A', 'E:P:Q', 'E:V:O', 'E:Y:L', 'F:B:C', 'F:B:H', 'F:C:H',
        'F:F:Q', 'F:H:D', 'F:M:S', 'F:P:A', 'F:P:E', 'F:P:L', 'F:P:T', 'F:Y:P',
        'G:B:H', 'G:B:I', 'G:C:N', 'G:G:K', 'G:H:D', 'G:K:P', 'G:K:V', 'G:P:F',
        'H:B:D', 'H:C:M', 'H:C:S', 'H:F:D', 'H:G:P', 'H:K:B', 'H:K:F', 'H:P:L',
        'H:Y:T', 'I:C:R', 'I:D:G', 'I:D:H', 'I:D:P', 'I:D:S', 'I:D:U', 'I:G:A',
        'I:G:L', 'I:G:T', 'I:G:V', 'I:H:D', 'I:H:E', 'I:K:H', 'I:K:R', 'I:Q:L',
        'I:Q:N', 'I:Y:A', 'I:Y:E', 'J:C:D', 'J:C:M', 'J:C:T', 'J:D:H', 'J:F:A',
        'J:F:N', 'J:G:B', 'J:G:T', 'J:H:D', 'J:H:E', 'J:K:B', 'J:K:O', 'J:L:B',
        'J:M:B', 'J:M:I', 'J:O:I', 'J:O:Y', 'J:P:H', 'J:X:D', 'J:X:P', 'J:Y:I',
        'K:B:E', 'K:C:E', 'K:C:J', 'K:D:A', 'K:D:O', 'K:F:N', 'K:G:R', 'K:H:M',
        'K:J:D', 'K:M:B', 'K:M:C', 'K:P:A', 'K:P:C', 'K:P:L', 'K:P:R', 'K:V:A',
        'K:V:E', 'K:Y:B', 'K:Y:T', 'K:Y:U', 'L:B:A', 'L:B:D', 'L:B:O', 'L:D:J',
        'L:K:T', 'L:P:T', 'L:V:M', 'L:Y:D', 'L:Y:I', 'L:Y:U', 'L:Y:V', 'M:B:D',
        'M:B:G', 'M:B:J', 'M:B:Y', 'M:C:H', 'M:F:P', 'M:G:Z', 'M:K:S', 'M:P:D',
        'N:B:G', 'N:B:M', 'N:C:E', 'N:C:F', 'N:F:P', 'N:F:V', 'N:G:B', 'N:G:E',
        'N:G:M', 'N:G:O', 'N:G:S', 'N:H:C', 'N:H:E', 'N:M:S', 'N:Q:I', 'N:Y:H',
        'N:Y:S', 'O:C:A', 'O:C:P', 'O:C:S', 'O:D:S', 'O:F:M', 'O:G:A', 'O:G:E',
        'O:G:H', 'O:H:B', 'O:H:G', 'O:H:L', 'O:H:M', 'O:M:C', 'O:O:V', 'O:O:X',
        'O:P:E', 'O:P:G', 'O:Y:B', 'O:Y:E', 'O:Y:L', 'P:B:L', 'P:G:M', 'P:H:J',
        'P:K:Q', 'P:P:D', 'P:P:S', 'P:W:A', 'P:W:E', 'P:Y:K', 'P:Y:M', 'P:Y:Q',
        'P:Y:V', 'Q:F:C', 'Q:F:N', 'Q:G:A', 'Q:G:L', 'Q:H:A', 'Q:K:B', 'Q:M:C',
        'Q:M:T', 'Q:M:V', 'Q:U:D', 'Q:Y:S', 'R:B:L', 'R:B:S', 'R:K:B', 'R:P:C',
        'R:P:D', 'R:V:K', 'R:X:Q', 'S:B:P', 'S:H:B', 'S:H:F', 'S:W:C', 'S:W:D',
        'S:W:Q', 'T:B:H', 'T:C:G', 'T:C:Q', 'T:F:P', 'T:G:Z', 'T:H:V', 'T:K:Q',
        'T:P:U', 'T:U:Q', 'T:Y:S', 'U:C:H', 'U:C:Q', 'U:D:E', 'U:D:F', 'U:D:H',
        'U:F:A', 'U:G:B', 'U:G:L', 'U:O:D', 'U:O:L', 'U:P:D', 'U:P:M', 'U:U:T',
        'V:C:L', 'V:C:S', 'V:F:G', 'V:G:A', 'V:G:D', 'V:G:H', 'V:G:K', 'V:H:B',
        'V:H:T', 'V:K:A', 'V:K:G', 'V:M:F', 'V:M:P', 'V:P:E', 'V:Y:H', 'V:Y:R',
        'W:B:E', 'W:B:I', 'W:C:B', 'W:C:H', 'W:D:B', 'W:D:C', 'W:F:D', 'W:F:E',
        'W:F:Q', 'W:G:C', 'W:H:G', 'W:K:B', 'W:K:L', 'W:M:F', 'W:O:B', 'W:O:L',
        'W:P:K', 'W:X:C', 'W:X:M', 'W:Y:R', 'X:L:P', 'X:M:A', 'X:M:C', 'X:N:M',
        'X:R:A', 'X:R:F', 'Y:D:E', 'Y:D:S', 'Y:G:I', 'Y:H:G', 'Y:I:J', 'Y:K:T',
        'Y:L:D', 'Y:L:K', 'Y:L:W', 'Y:O:A', 'Y:O:G', 'Y:U:B', 'Y:U:Z', 'Y:X:C',
        'Y:Y:B', 'Y:Y:E', 'Z:C:B', 'Z:C:D', 'Z:C:P', 'Z:D:E', 'Z:D:G', 'Z:F:A',
        'Z:F:I', 'Z:F:O', 'Z:G:P', 'Z:G:W', 'Z:K:B', 'Z:K:M', 'Z:K:U', 'Z:M:C',
        'Z:M:D', 'Z:M:G', 'Z:M:H', 'Z:R:C', 'Z:Y:C',
    ];

    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à l'axe commençant+terminant+avec
     * (D-041, garde-fou structurel demandé par le constat C-4 du 4e audit consolidé,
     * docs/DECISIONS.md D-040) -- distinct de DUPLICATE_CONTENT_KEYS/SIBLING_DUPLICATE_KEYS/
     * CROSS_DUPLICATE_LENGTH_KEYS ci-dessus (qui comparent uniquement au sein du même panier
     * commençant+terminant, ou avec la variante longueur du MÊME panier) : ici, une page
     * "commençant/{X}/terminant/{Y}/avec/{Z}" partage un contenu strictement identique avec une
     * page d'une famille SANS RAPPORT au panier commençant+terminant d'origine (terminant ou
     * commençant multi-lettres portant un préfixe/suffixe totalement différent, avec à deux
     * lettres...), trouvée par le balayage GÉNÉRIQUE de tout le registre
     * (scripts/check_combinatorial_duplicates.php, balayage du 2026-08-21 : 1 656 groupes,
     * 2 089 pages en excès).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "commençant/{X}/terminant/{Y}/avec/{Z}" a TOUJOURS 3 composants. Les 314 clés se répartissent
     * en pertes face à terminant multi-lettres (248), commençant multi-lettres (65) et avec à deux
     * lettres, palier 2 (1 -- cas non structurel, comparable à celui déjà trouvé sur le palier 3 de
     * "avec", voir AvecThreeLettersLinksBuilder::EXTERNAL_DUPLICATE_KEYS).
     *
     * 314 clés (format "{début}:{fin}:{lettre}"), recalculées indépendamment par échantillonnage
     * direct contre `terms` (voir le rapport AFTER de cette tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_KEYS = [
        'A:B:I', 'A:C:V', 'A:D:K', 'A:H:W', 'A:M:J', 'A:M:K', 'A:O:W', 'A:O:X',
        'A:P:K', 'A:R:W', 'A:U:K', 'A:Y:S', 'B:F:D', 'B:F:H', 'B:G:Z', 'B:H:M',
        'B:H:Z', 'B:I:W', 'B:L:W', 'B:M:G', 'B:O:Y', 'B:O:Z', 'B:P:K', 'B:P:Z',
        'B:Q:E', 'B:Q:I', 'B:Y:H', 'B:Y:S', 'B:Y:T', 'B:Y:Z', 'C:C:Z', 'C:H:M',
        'C:P:G', 'C:V:A', 'C:V:O', 'C:W:E', 'C:W:R', 'C:X:W', 'C:Y:K', 'C:Y:W',
        'D:C:J', 'D:D:K', 'D:H:G', 'D:M:Q', 'D:M:Z', 'D:O:K', 'D:O:X', 'D:P:G',
        'D:P:H', 'D:P:I', 'D:P:M', 'D:P:N', 'D:V:E', 'D:V:I', 'D:Y:G', 'D:Y:Z',
        'E:C:B', 'E:C:J', 'E:F:B', 'E:G:C', 'E:M:K', 'E:M:V', 'E:O:F', 'E:O:K',
        'E:U:Y', 'E:Y:D', 'E:Y:I', 'F:C:P', 'F:C:V', 'F:H:Y', 'F:L:J', 'F:M:H',
        'F:M:X', 'F:Y:C', 'F:Y:D', 'F:Y:H', 'F:Y:M', 'F:Y:W', 'G:D:K', 'G:G:U',
        'G:K:B', 'G:K:D', 'G:L:Z', 'G:O:J', 'G:P:S', 'G:Y:M', 'G:Y:S', 'G:Y:U',
        'G:Y:W', 'G:Y:Z', 'H:A:W', 'H:B:U', 'H:C:K', 'H:H:V', 'H:H:W', 'H:K:M',
        'H:O:B', 'H:O:P', 'H:P:E', 'H:P:T', 'H:U:K', 'H:Y:P', 'H:Y:S', 'I:C:E',
        'I:K:A', 'I:U:J', 'I:Y:X', 'J:D:G', 'J:D:W', 'J:I:W', 'J:K:N', 'J:M:N',
        'J:O:K', 'J:P:M', 'J:X:K', 'J:Y:Z', 'K:B:N', 'K:C:U', 'K:D:B', 'K:F:D',
        'K:F:W', 'K:H:F', 'K:H:Z', 'K:I:V', 'K:L:W', 'K:N:X', 'K:O:P', 'K:O:Y',
        'K:P:O', 'K:U:F', 'K:U:V', 'K:Z:G', 'L:C:F', 'L:C:P', 'L:C:Y', 'L:G:Y',
        'L:H:D', 'L:H:R', 'L:H:S', 'L:I:J', 'L:L:Q', 'L:L:W', 'L:M:J', 'L:M:K',
        'L:O:X', 'L:O:Y', 'L:R:W', 'L:U:Z', 'M:A:W', 'M:B:N', 'M:C:J', 'M:F:J',
        'M:G:J', 'M:H:Z', 'M:K:D', 'M:K:J', 'M:L:W', 'M:M:Q', 'N:B:Q', 'N:C:U',
        'N:D:J', 'N:K:Y', 'N:L:X', 'N:L:Z', 'N:M:Z', 'N:O:J', 'N:O:K', 'N:O:Y',
        'N:Q:D', 'N:Q:E', 'N:U:J', 'N:W:G', 'N:W:K', 'O:A:W', 'O:D:Z', 'O:F:G',
        'O:H:A', 'O:K:G', 'O:K:M', 'O:K:Y', 'O:O:B', 'O:O:D', 'O:O:P', 'O:U:K',
        'P:A:W', 'P:B:C', 'P:B:I', 'P:F:K', 'P:H:R', 'P:K:J', 'P:L:Z', 'P:O:J',
        'P:O:X', 'P:P:M', 'P:Y:X', 'Q:A:K', 'Q:E:W', 'Q:F:Z', 'Q:H:O', 'Q:K:I',
        'Q:L:Z', 'Q:N:W', 'Q:Y:C', 'R:B:G', 'R:B:H', 'R:C:Y', 'R:D:Y', 'R:G:H',
        'R:H:K', 'R:H:N', 'R:H:P', 'R:K:D', 'R:L:K', 'R:L:Z', 'R:M:Q', 'R:N:K',
        'R:O:F', 'R:P:S', 'R:V:D', 'R:V:T', 'R:Y:S', 'R:Y:W', 'S:B:D', 'S:B:K',
        'S:G:J', 'S:H:Q', 'S:H:Y', 'S:K:J', 'S:M:F', 'S:M:K', 'S:M:V', 'S:O:Q',
        'S:P:G', 'S:P:W', 'S:Y:C', 'S:Y:D', 'S:Y:V', 'S:Y:Z', 'T:B:I', 'T:D:W',
        'T:D:X', 'T:H:M', 'T:K:D', 'T:K:G', 'T:K:S', 'T:O:Y', 'T:R:Z', 'T:U:K',
        'T:V:A', 'T:V:G', 'T:Y:G', 'T:Y:K', 'T:Y:X', 'U:G:C', 'U:H:G', 'U:H:M',
        'U:M:H', 'U:O:A', 'U:U:B', 'U:U:D', 'U:U:N', 'U:U:S', 'V:F:B', 'V:L:X',
        'V:M:B', 'V:O:D', 'V:P:A', 'V:U:Y', 'V:Y:T', 'V:Y:Z', 'W:A:U', 'W:D:N',
        'W:F:H', 'W:H:A', 'W:H:L', 'W:M:B', 'W:M:D', 'W:M:G', 'W:M:K', 'W:M:P',
        'W:P:I', 'W:U:K', 'W:Y:B', 'W:Y:E', 'X:L:M', 'Y:A:Z', 'Y:H:A', 'Y:I:Q',
        'Y:I:V', 'Y:I:Z', 'Y:K:L', 'Y:M:O', 'Y:O:E', 'Y:R:G', 'Z:B:A', 'Z:B:M',
        'Z:B:U', 'Z:C:E', 'Z:C:U', 'Z:G:R', 'Z:H:D', 'Z:H:N', 'Z:H:W', 'Z:K:D',
        'Z:K:P', 'Z:L:Y', 'Z:N:F', 'Z:O:S', 'Z:P:I', 'Z:P:U', 'Z:U:K', 'Z:U:N',
        'Z:Y:E', 'Z:Y:T',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function build(string $startLetter, string $endLetter): StartEndWithLinks
    {
        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = 'start_end_with' AND list_key LIKE ?"
        );
        $statement->execute([$startLetter . ':' . $endLetter . ':%']);

        // URL de la page parente (commencant+terminant, sans "avec") : sert a detecter les
        // lettres "avec" degenerees (D-032, WordListFilters::fromPath() collapse silencieusement
        // "avec/X" quand X est deja garanti par un commencant/terminant d'une seule lettre --
        // meme mecanisme que le collapse "position" deja etabli, D-023). Sans cette detection,
        // ces lettres degenerees (toujours PRESENTES dans list_counts : count_chars() du script
        // de precalcul liste la lettre de debut et la lettre de fin comme des lettres
        // "distinctes" du mot au meme titre que les autres, aucune exclusion cote precalcul)
        // produiraient un lien dont l'URL est IDENTIQUE a celle de la page source elle-meme --
        // un doublon trompeur (deux lettres "avec" differentes menant chacune vers la MEME URL
        // que la page qui les propose), pas seulement une page en moins.
        $parentUrl = WordListFilters::fromPath(
            'commencant/' . strtolower($startLetter) . '/terminant/' . strtolower($endLetter)
        )?->canonicalUrl();

        $links = [];

        foreach ($statement as $row) {
            $parts = explode(':', (string) $row['list_key'], 3);
            $letter = $parts[2];
            $count = (int) $row['count'];

            $path = 'commencant/' . strtolower($startLetter) . '/terminant/' . strtolower($endLetter)
                . '/avec/' . strtolower($letter);
            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url === null || $url === $parentUrl) {
                continue;
            }

            // Doublon de CONTENU (audit consolide, NO GO) : URL distincte de la page parente,
            // mais tous les mots de la paire contiennent deja cette lettre -- voir
            // DUPLICATE_CONTENT_KEYS ci-dessus, jamais un lien vers une page dont le contenu est
            // identique a une page deja indexee.
            $key = strtoupper($startLetter) . ':' . strtoupper($endLetter) . ':' . strtoupper($letter);

            if (in_array($key, self::DUPLICATE_CONTENT_KEYS, true)) {
                continue;
            }

            // Doublon de CONTENU entre pages SOEURS (I-A, 2e audit consolide) : une AUTRE lettre
            // "avec" du MEME panier produit exactement le meme sous-ensemble de mots -- voir
            // SIBLING_DUPLICATE_KEYS ci-dessus. La lettre alphabetiquement la plus petite du
            // groupe reste candidate (jamais exclue par ce filtre) ; les autres sont retirees ici.
            if (in_array($key, self::SIBLING_DUPLICATE_KEYS, true)) {
                continue;
            }

            // Doublon de contenu CROISE avec l'AUTRE famille (3e audit consolide) : la tranche
            // LONGUEUR de ce meme panier {debut}:{fin} (App\Search\LengthLinksBuilder::byStartEnd)
            // contient EXACTEMENT le meme ensemble de mots -- voir CROSS_DUPLICATE_LENGTH_KEYS
            // ci-dessus. La variante LONGUEUR reste candidate, celle-ci (avec) est retiree.
            if (in_array($key, self::CROSS_DUPLICATE_LENGTH_KEYS, true)) {
                continue;
            }

            // Doublon de contenu CROISE avec une famille EXTERIEURE au panier commencant+
            // terminant d'origine (D-041) : voir EXTERNAL_DUPLICATE_KEYS ci-dessus.
            if (in_array($key, self::EXTERNAL_DUPLICATE_KEYS, true)) {
                continue;
            }

            $links[] = ['letter' => $letter, 'url' => $url, 'count' => $count];
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new StartEndWithLinks(links: $links, queryCount: 1);
    }
}
