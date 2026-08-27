<?php

declare(strict_types=1);

namespace App\Search;

use App\Database\Connection;

/**
 * Construit App\Search\SuffixExtensionLinks depuis list_counts (`list_type` 'suffix2'/'suffix3'/
 * 'suffix4'), même principe que App\Search\PrefixExtensionLinksBuilder — une seule requête
 * triviale, aucun GROUP BY sur `terms` au runtime.
 *
 * `list_key` de 'suffixN' est TOUJOURS le suffixe RÉEL, en ordre de lecture normal (déjà "dé-
 * inversé" par `strrev()` à l'écriture, `scripts/build_explore_hub_counts.php` — jamais la
 * sous-chaîne brute de `reversed`), donc aucune conversion nécessaire ici non plus. L'extension
 * ajoute une lettre AU DÉBUT du suffixe (ex. depuis "NG", "ING"/"ANG"/"ONG"... se terminent tous
 * par "NG") : le joker `_` du LIKE se place donc en TÊTE du motif (`'_' . $suffix`), pas en
 * queue comme pour PrefixExtensionLinksBuilder — seul point de différence structurelle entre les
 * deux builders, documenté explicitement pour ne pas les confondre par simple copier-coller.
 * `list_counts` reste petit (91 681 lignes au total au 2026-08-18) — un LIKE à joker en tête
 * reste trivial ici, même raisonnement déjà accepté pour
 * App\Search\LetterCombinedLinksBuilder::buildForEnd() (D-024).
 */
final class SuffixExtensionLinksBuilder
{
    private const MIN_INPUT_LENGTH = 1;
    private const MAX_INPUT_LENGTH = 3;

    /**
     * Doublons de contenu CROISÉS avec une famille EXTÉRIEURE à "terminant" multi-lettres (D-041,
     * garde-fou structurel demandé par le constat C-4 du 4e audit consolidé, docs/DECISIONS.md
     * D-040) -- balayage GÉNÉRIQUE de tout le registre (scripts/check_combinatorial_duplicates.php,
     * balayage du 2026-08-21 : 1 656 groupes, 2 089 pages en excès).
     *
     * Règle de départage : App\Search\DuplicatePageResolver::resolveDuplicateWinner() -- une page
     * "terminant/{suffixe}" a TOUJOURS 1 seul composant, quelle que soit la longueur du suffixe.
     * Elle perd UNIQUEMENT face à "commençant/{préfixe}" (l'autre famille à 1 seul composant, qui
     * la départage systématiquement dans l'ordre canonique -- "commençant" précède "terminant",
     * WordListFilters, docblock de classe) quand les deux désignent le même mot unique et sont
     * TOUTES DEUX présentes dans le même groupe (ex. "terminant/wu" perd face à
     * "commencant/webj" -- non, cet exemple concerne une page distincte, voir le rapport AFTER
     * pour le détail complet des 639 clés). Jamais face à une famille à 2 composants ou plus
     * (avec, combiné, position...) : "terminant" gagne toujours contre celles-ci.
     *
     * Liste des suffixes perdants (639 valeurs, forme lisible -- ex. "WU" désigne
     * /mots/terminant/wu), recalculée indépendamment par échantillonnage direct contre `terms`
     * (voir le rapport AFTER de cette tâche) : 0 divergence.
     *
     * Liste figée : valable pour l'état actuel de storage/dictionary_fr.sqlite (838 180 termes,
     * inchangé depuis D-022). Une reconstruction future de la base devra revalider cette liste.
     *
     * @var list<string>
     */
    private const EXTERNAL_DUPLICATE_SUFFIXES = [
        'AADA', 'AAKU', 'AAT', 'ABEM', 'ABU', 'ABZA', 'ACI', 'ACQ',
        'ADH', 'ADSL', 'ADZA', 'AEIS', 'AFAN', 'AGOR', 'AHAM', 'AIDO',
        'AINU', 'AIO', 'AJAR', 'AJD', 'AKAL', 'AKLA', 'ALY', 'AMUT',
        'ANKI', 'ANOA', 'AOI', 'AOO', 'APEX', 'APH', 'AREU', 'ARRS',
        'ARTT', 'ASAN', 'AUER', 'AUND', 'AVUS', 'AWN', 'AZOO', 'AZS',
        'BAFS', 'BAM', 'BBOT', 'BCA', 'BCP', 'BDES', 'BEEZ', 'BEX',
        'BGEN', 'BINZ', 'BLOB', 'BNC', 'BNS', 'BOCS', 'BOGS', 'BRNS',
        'BRRR', 'BT', 'BUBE', 'BUGS', 'BULK', 'BULU', 'BZ', 'CARU',
        'CBG', 'CBM', 'CCP', 'CDE', 'CDG', 'CDI', 'CDS', 'CDT',
        'CEFT', 'CFA', 'CFF', 'CGT', 'CHAE', 'CJOB', 'CL', 'CM',
        'CMSA', 'CNAF', 'CNS', 'COBS', 'COFA', 'COIX', 'CPAS', 'CPE',
        'CPU', 'CRAG', 'CRDS', 'CSS', 'CTOC', 'CV', 'CX', 'DAWS',
        'DBA', 'DC', 'DDAX', 'DDE', 'DDHI', 'DDT', 'DECT', 'DEGS',
        'DEH', 'DEPS', 'DEPT', 'DEVS', 'DEYS', 'DHAD', 'DID', 'DIXE',
        'DJOC', 'DL', 'DLR', 'DLT', 'DN', 'DNS', 'DONF', 'DOPS',
        'DPI', 'DPIN', 'DR', 'DSAT', 'DSC', 'DSM', 'DTC', 'DUOS',
        'DV', 'DZOS', 'EAH', 'EAIE', 'EARA', 'EARL', 'EBAC', 'EBOX',
        'EDMA', 'EGT', 'EHA', 'EHOL', 'EHU', 'EIX', 'EJI', 'EJS',
        'ELOF', 'EMBO', 'ENTZ', 'EPC', 'ESEM', 'ESY', 'ETVA', 'EUNT',
        'EYOU', 'FAFS', 'FAQS', 'FD', 'FDT', 'FEMS', 'FEO', 'FFT',
        'FIFA', 'FIFS', 'FIUS', 'FKA', 'FKIF', 'FM', 'FNAL', 'FOB',
        'FOGS', 'FOTE', 'FTP', 'FUMI', 'FUNS', 'GAGS', 'GAPS', 'GAYN',
        'GBAN', 'GBD', 'GBRA', 'GCP', 'GEB', 'GEDE', 'GESH', 'GF',
        'GGIS', 'GGLE', 'GHEH', 'GHEZ', 'GINI', 'GIOL', 'GKHA', 'GM',
        'GML', 'GNAK', 'GNU', 'GNUS', 'GOGS', 'GOIM', 'GONY', 'GOSH',
        'GOWS', 'GOYS', 'GPL', 'GPS', 'GRRR', 'GSM', 'GV', 'GYMA',
        'HAAN', 'HAHE', 'HAIX', 'HAJA', 'HANG', 'HATO', 'HAYN', 'HB',
        'HCP', 'HECI', 'HEIN', 'HEIT', 'HFA', 'HIYA', 'HLAB', 'HLM',
        'HOAX', 'HOMS', 'HRYS', 'HSE', 'HTIE', 'HUAU', 'HUBS', 'HUOT',
        'IAO', 'IAQ', 'IARK', 'IDH', 'IDJ', 'IDT', 'IDZE', 'IEFF',
        'IEPA', 'IGLO', 'ILDO', 'IMPI', 'INNI', 'IPSY', 'IRK', 'IRM',
        'ITF', 'IUD', 'IYAR', 'IZZ', 'JAMO', 'JAPS', 'JAWI', 'JBAN',
        'JEDI', 'JOBS', 'JRA', 'JRI', 'KACA', 'KAH', 'KAID', 'KANO',
        'KAPU', 'KEAS', 'KIDS', 'KKI', 'KKO', 'KMAL', 'KMU', 'KOAS',
        'KOBS', 'KOFF', 'KOMI', 'KOON', 'KOPS', 'KOUF', 'KPA', 'KRUS',
        'KSI', 'KTON', 'KUIA', 'KUKO', 'KYR', 'KYUS', 'LADS', 'LBOL',
        'LCOL', 'LEVS', 'LG', 'LGEN', 'LNAU', 'LNET', 'LNOI', 'LOAM',
        'LODS', 'LOIX', 'LOKE', 'LRI', 'LRUS', 'LSCH', 'LSF', 'LUEN',
        'LYK', 'LYNX', 'LZ', 'MAOS', 'MBUL', 'MCDO', 'MDRE', 'MEUH',
        'MEUM', 'MF', 'MGEN', 'MH', 'MHUN', 'MIAM', 'MIPS', 'MKA',
        'MLI', 'MMM', 'MNT', 'MOAS', 'MOFO', 'MOH', 'MONG', 'MRC',
        'MRE', 'MST', 'MTEX', 'MTF', 'MTP', 'MTS', 'MUGS', 'MW',
        'MWE', 'MYRE', 'NAH', 'NARY', 'NAV', 'NB', 'NBOU', 'NCB',
        'NDT', 'NEBE', 'NEFE', 'NEGS', 'NEMS', 'NEX', 'NIDS', 'NIJA',
        'NJAC', 'NKAN', 'NMER', 'NOBI', 'NOER', 'NOK', 'NP', 'NSHI',
        'NUDS', 'NWIN', 'NYOS', 'OAGE', 'OATL', 'OCI', 'OEA', 'OELI',
        'OERI', 'OHL', 'OHOR', 'OKEI', 'OKET', 'OKIO', 'OLED', 'ONEK',
        'OPEX', 'OPH', 'ORYX', 'OSEF', 'OSIK', 'OSK', 'OUTT', 'OYM',
        'OZI', 'OZOU', 'PAFS', 'PAIX', 'PB', 'PCB', 'PDF', 'PDTS',
        'PEG', 'PEOS', 'PFR', 'PFUT', 'PHU', 'PHY', 'PIU', 'PKOI',
        'PLC', 'PLLE', 'PLOP', 'PME', 'PN', 'PNYX', 'POUH', 'PPAR',
        'PPI', 'PQC', 'PSET', 'PST', 'PSYS', 'PULK', 'PVD', 'PYX',
        'QADI', 'QAF', 'QATS', 'QCH', 'QE', 'QINS', 'QIS', 'QOF',
        'QOMI', 'QP', 'QQN', 'QTA', 'QUN', 'QUS', 'RCQ', 'RGH',
        'RGN', 'RH', 'RID', 'RIND', 'RKHA', 'RNDI', 'ROBS', 'ROK',
        'RPC', 'RRCO', 'RREM', 'RSK', 'RTSE', 'RTV', 'RUMP', 'RUZ',
        'RYAK', 'RYIN', 'SALL', 'SB', 'SBN', 'SBOS', 'SBOT', 'SCT',
        'SD', 'SDA', 'SDF', 'SDT', 'SEA', 'SEUT', 'SFI', 'SG',
        'SGT', 'SHUI', 'SISI', 'SKEA', 'SLEV', 'SLT', 'SMS', 'SOAS',
        'SOBA', 'SOEF', 'SONT', 'SPDG', 'SQN', 'SRI', 'SROL', 'SSH',
        'SSL', 'SST', 'STYS', 'SUCS', 'SUMI', 'SUVE', 'SVAN', 'SW',
        'SYN', 'TACH', 'TAKU', 'TAPS', 'TCA', 'TCC', 'TCDD', 'TCP',
        'TEFS', 'TEKS', 'TGI', 'THEX', 'TIPS', 'TJS', 'TKAL', 'TLET',
        'TLM', 'TM', 'TMAR', 'TMP', 'TN', 'TNIA', 'TNT', 'TOE',
        'TOFF', 'TOFS', 'TPE', 'TPU', 'TRAX', 'TSAN', 'TTC', 'TTP',
        'TUFS', 'TVI', 'TYX', 'UACA', 'UAF', 'UAK', 'UGHO', 'UHAU',
        'UIZ', 'UJD', 'UJI', 'UNGU', 'UNZA', 'UOC', 'UORO', 'URAO',
        'URDO', 'UREM', 'URIO', 'URL', 'URP', 'USAR', 'USSO', 'UYIN',
        'VAMS', 'VC', 'VDA', 'VEC', 'VG', 'VIC', 'VIPO', 'VITZ',
        'VLAN', 'VLEK', 'VMU', 'VOE', 'VOHE', 'VOID', 'VORO', 'VP',
        'VRD', 'VTC', 'VTT', 'VY', 'WACS', 'WADA', 'WADS', 'WANO',
        'WAU', 'WAX', 'WBIE', 'WDAH', 'WEL', 'WERK', 'WERZ', 'WFIE',
        'WID', 'WII', 'WOKE', 'WOKS', 'WORO', 'WRIT', 'XETA', 'XL',
        'XMEX', 'XNAY', 'XOID', 'XOR', 'XS', 'XVES', 'XXES', 'YAMP',
        'YAWS', 'YAYA', 'YBON', 'YDAR', 'YDAY', 'YEDS', 'YEUL', 'YFA',
        'YGGE', 'YGHE', 'YGOS', 'YIM', 'YLIX', 'YNET', 'YODH', 'YODS',
        'YOM', 'YOYE', 'YUDS', 'YUES', 'YVER', 'ZAKO', 'ZARE', 'ZAYS',
        'ZBA', 'ZEBS', 'ZECS', 'ZEFS', 'ZEKS', 'ZELI', 'ZESE', 'ZETT',
        'ZF', 'ZGO', 'ZICS', 'ZIGS', 'ZIO', 'ZIPS', 'ZIRE', 'ZIVA',
        'ZOBS', 'ZOES', 'ZOUZ', 'ZP', 'ZUBS', 'ZUGS', 'ZUPS',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * $suffix : suffixe normalisé (A-Z) de la page source, 1 à 3 lettres. Renvoie une liste vide
     * (queryCount = 0) pour toute longueur hors de cette plage.
     */
    public function build(string $suffix): SuffixExtensionLinks
    {
        $length = strlen($suffix);

        if ($length < self::MIN_INPUT_LENGTH || $length > self::MAX_INPUT_LENGTH) {
            return new SuffixExtensionLinks(links: [], queryCount: 0);
        }

        $listType = 'suffix' . ($length + 1);

        $statement = $this->connection->pdo()->prepare(
            "SELECT list_key, count FROM list_counts WHERE list_type = ? AND list_key LIKE ?"
        );
        $statement->execute([$listType, '_' . $suffix]);

        $links = [];

        foreach ($statement as $row) {
            $extendedSuffix = (string) $row['list_key'];

            if (in_array($extendedSuffix, self::EXTERNAL_DUPLICATE_SUFFIXES, true)) {
                continue;
            }

            $url = WordListFilters::fromPath('terminant/' . strtolower($extendedSuffix))?->canonicalUrl();

            if ($url !== null) {
                $links[] = ['suffix' => $extendedSuffix, 'url' => $url, 'count' => (int) $row['count']];
            }
        }

        usort($links, static fn (array $a, array $b): int => $a['suffix'] <=> $b['suffix']);

        return new SuffixExtensionLinks(links: $links, queryCount: 1);
    }
}
