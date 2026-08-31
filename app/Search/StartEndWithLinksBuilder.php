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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'start_end_with'
    // (URL /palabras/empiezan-por/{X}/terminan-en/{Y}/con-letras/{Z},
    // famille commencant+terminant+avec) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    private const DUPLICATE_CONTENT_KEYS = [];

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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'start_end_with'
    // (URL /palabras/empiezan-por/{X}/terminan-en/{Y}/con-letras/{Z},
    // famille commencant+terminant+avec) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    private const SIBLING_DUPLICATE_KEYS = [];

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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'start_end_with'
    // (URL /palabras/empiezan-por/{X}/terminan-en/{Y}/con-letras/{Z},
    // famille commencant+terminant+avec) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    private const CROSS_DUPLICATE_LENGTH_KEYS = [];

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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'start_end_with'
    // (URL /palabras/empiezan-por/{X}/terminan-en/{Y}/con-letras/{Z},
    // famille commencant+terminant+avec) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    private const EXTERNAL_DUPLICATE_KEYS = [];

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
            'empiezan-por/' . mb_strtolower($startLetter, 'UTF-8') . '/terminan-en/' . mb_strtolower($endLetter, 'UTF-8')
        )?->canonicalUrl();

        $links = [];

        foreach ($statement as $row) {
            $parts = explode(':', (string) $row['list_key'], 3);
            $letter = $parts[2];
            $count = (int) $row['count'];

            // 'con-letras' (ES-014), anciennement 'avec'.
            $path = 'empiezan-por/' . mb_strtolower($startLetter, 'UTF-8') . '/terminan-en/' . mb_strtolower($endLetter, 'UTF-8')
                . '/con-letras/' . mb_strtolower($letter, 'UTF-8');
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
