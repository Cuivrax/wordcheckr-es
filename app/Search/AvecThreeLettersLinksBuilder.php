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
     * ---
     * ES -- CORRECTIF C-2 (audits croises, 2026-08-31) : VIDEE. Liste calculee sur
     * storage/dictionary_fr.sqlite, jamais re-derivee pour l'espagnol (meme landmine que
     * App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES, videe ES-023). La
     * famille cible ('length_with_triple' -> /palabras/{N}-letras/con-letras/{X}/{Y}/{Z},
     * Family::WORD_LIST_AVEC_THREE_LETTERS) a 0 ligne dans storage/seo_es.sqlite a ce jour
     * (verifie) : sans effet sur l'indexation, n'affecte que le maillage entre pages non
     * indexees. A RECALCULER pour l'espagnol AVANT ouverture du palier "avec" 3 lettres.
     *
     * @var list<string>
     */
    private const DUPLICATE_PARENT_KEYS = [];

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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'length_with_triple'
    // (URL /palabras/{N}-letras/con-letras/{X}/{Y}/{Z},
    // famille WORD_LIST_AVEC_THREE_LETTERS) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    private const SIBLING_DUPLICATE_KEYS = [];

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
    // ES -- CORRECTIF C-2 (audits croises code-reviewer + seo-technical-auditor, 2026-08-31) :
    // VIDEE. Le contenu d'origine a ete calcule sur storage/dictionary_fr.sqlite /
    // storage/seo_fr.sqlite et n'a JAMAIS ete re-derive pour l'espagnol -- meme landmine que
    // App\Search\SuffixExtensionLinksBuilder::EXTERNAL_DUPLICATE_SUFFIXES (videe ES-023).
    // Cette liste ne filtre que le list_type 'length_with_triple'
    // (URL /palabras/{N}-letras/con-letras/{X}/{Y}/{Z},
    // famille WORD_LIST_AVEC_THREE_LETTERS) : cette famille n'a AUCUNE ligne dans storage/seo_es.sqlite a ce jour
    // (verifie exhaustivement) -- la liste n'affecte donc aujourd'hui que le maillage
    // interne entre pages non indexees. A RECALCULER pour l'espagnol (chantier separe,
    // cf. ES-021) AVANT toute ouverture de cette famille a l'indexation. Le docblock
    // ci-dessus decrit l'ancienne liste FR, conserve pour l'historique.
    private const EXTERNAL_DUPLICATE_KEYS = [];

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
            // 'con-letras' (ES-014), anciennement 'avec' -- revalide par fromPath() juste apres.
            $path = $length . '-letras/con-letras/' . mb_strtolower($x, 'UTF-8') . '/' . mb_strtolower($y, 'UTF-8') . '/' . mb_strtolower($partner, 'UTF-8');
            $url = WordListFilters::fromPath($path)?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['letter' => $partner, 'url' => $url, 'count' => $count];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['letter'] <=> $b['letter']);

        return new AvecThreeLettersLinks(links: $links, queryCount: 1);
    }
}
