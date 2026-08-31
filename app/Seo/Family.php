<?php

declare(strict_types=1);

namespace App\Seo;

/**
 * Liste fermee des familles de reporting/gouvernance du registre SEO espagnol
 * (storage/seo_es.sqlite). Meme patron que le depot francais cousin (app/Seo/Family.php,
 * FR), ADAPTE plutot que copie tel quel : ce fichier repart d'une base neuve, sans
 * l'historique de paliers "avec"/"position"/"combined" du site francais (D-023 a D-041,
 * FR/docs/DECISIONS.md) -- aucun de ces paliers n'a ete mesure ni ouvert ici.
 *
 * Une famille correspond a un TYPE de route, pas a une route individuelle -- elle sert a :
 * - produire les metriques quantifiees exigees par lot (URL par famille) ;
 * - appliquer les regles dures par famille (ex. NEVER_SITEMAP ci-dessous), a la fois dans
 *   scripts/apply_seo_batch.php (refus a l'ecriture) et dans les rapports de rollout.
 *
 * ETAT REEL DE CE DEPOT (docs/DECISIONS.md ES-009 a ES-026, recompte le 2026-08-31 :
 * storage/seo_es.sqlite = 772 629 lignes, 772 507 index,follow) : NEUF familles ont des lignes
 * index,follow reelles -- HOME (2 lignes : '/' index,follow + '/palabras' noindex,follow),
 * WORD_ADMITTED (661 221 / 661 221, COMPLETE, ES-013/ES-015), WORD_SPANISH_NOT_ADMITTED
 * (86 944 / 86 944, COMPLETE, lot unique, ES-024), WORD_LIST_LENGTH (14), WORD_LIST_COMMENCANT
 * (2 871 : grains 1+2+3 lettres, ES-016/ES-018/ES-023), WORD_LIST_TERMINANT (14 192 : grains
 * 1+2+3+4 lettres, ES-016/ES-022/ES-023), WORD_LIST_COMBINED (2 547 : longueur+empiezan-por 1
 * car. + longueur+terminan-en 2 car., ES-018), WORD_LIST_AVEC_SINGLE_LETTER (377, ES-025),
 * WORD_LIST_AVEC_TWO_LETTERS (4 340 + 109 noindex, ES-026). Toutes les autres constantes
 * existent pour que la FORME du registre (schema, classes, outils de build) soit complete et
 * prete a recevoir de futurs paliers sans migration de schema -- mais ne sont pas peuplees. Une
 * famille non peuplee n'indexe rien PAR CONSTRUCTION : une route absente de `registry` reste
 * noindex,follow (D-005 du depot francais, meme contrat ici, voir App\Seo\Registry::resolve()).
 *
 * Correspondance avec les prefixes de fragments de sitemap reellement generes a ce stade
 * (docs/05_URL_SEO_INDEXATION.md, section Sitemaps -- 28 fragments ; scripts/build_sitemaps.php
 * FAMILY_FRAGMENT_PREFIXES = source de verite) : core-* (home, '/' UNIQUEMENT -- '/palabras'
 * exclu, noindex), words-* (mots admis), invalid-* (formes espagnoles non admises, ES-024),
 * letters-* (listes par longueur), starts-* (empiezan-por, grains 1/2/3 lettres),
 * ends-* (terminan-en, grains 1/2/3/4 caracteres), combined-* (longueur+empiezan-por,
 * longueur+terminan-en), avec-single-* (con-letras 1 lettre, ES-025), avec-two-* (con-letras
 * 2 lettres, ES-026). Les autres prefixes documentes cote francais (contains-, position-,
 * avec-triple-...) restent des reservations de nommage, jamais generes par ce depot a ce jour.
 */
final class Family
{
    public const HOME = 'home';
    public const WORD_ADMITTED = 'word_admitted';

    /**
     * Forme espagnole retenue par kaikki.org/eswiktionary (colonne is_spanish), absente des
     * deux lexiques d'admissibilite (is_ods8 = Lexicon FILE 2017, is_ods9 = Lexicon FISE-2 --
     * voir config/sites/es.php et docs/DECISIONS.md ES-007 pour le renommage des etiquettes
     * visibles). Equivalent espagnol de Family::WORD_FRENCH_NOT_ADMITTED du depot francais.
     * COMPLETE depuis ES-024 (2026-08-30) : 86 944 / 86 944 lignes index,follow, lot UNIQUE
     * (batch_id word_spanish_not_admitted-full-2026-08-30), fragments invalid-0001..invalid-0003.
     * Leve le blocage ES-009/ES-010 : demande produit explicite d'ouvrir tout l'espagnol non
     * admis (meme raisonnement que D-017 cote francais -- le site repond a deux questions
     * symetriques). Le point qui manquait a ES-010 (maillage entrant) est constate deja present :
     * App\Search\TermLookup::neighbours() (navigation mot precedent/suivant) parcourt la chaine
     * alphabetique complete, admises ET non admises confondues. Attestation ligne par ligne
     * (notes non vide, R6/R7) restee obligatoire ; seul le VOLUME maximal par lot a change (voir
     * MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED plus bas).
     */
    public const WORD_SPANISH_NOT_ADMITTED = 'word_spanish_not_admitted';

    public const WORD_LIST_LENGTH = 'word_list_length';

    /**
     * Constantes pour les paliers combinatoires. WORD_LIST_COMMENCANT / WORD_LIST_TERMINANT
     * sont peuplees : commencant grains 1+2+3 lettres (ES-016 / ES-023 / ES-018), terminant
     * grains 1+2+3+4 lettres (ES-016 grain 2 ; ES-022 grain 1, lie par le hub /palabras et non
     * RelationsFinder ; ES-023 grains 3+4). WORD_LIST_COMBINED est peuplee depuis ES-018
     * (longueur+empiezan-por a 1 caractere, longueur+terminan-en a 2 caracteres) -- N'OUVRE PAS
     * le troisieme axe "empiezan-por+terminan-en ensemble" (avec ou sans longueur). ATTENTION,
     * la raison a evolue : list_counts 'start_end' (573 lignes) et 'length_start_end' (3 917
     * lignes) SONT desormais construits (ES-022) ; l'obstacle est desormais (a) une decision
     * d'indexation non prise et (b) le fait que les listes de dedoublonnage
     * *LinksBuilder::*DUPLICATE*_KEYS (14 constantes) ont ete VIDEES le 2026-08-31 (correctif
     * C-2 -- elles etaient calculees sur storage/dictionary_fr.sqlite) et doivent etre
     * recalculees pour l'espagnol AVANT toute ouverture d'un axe combine qui les consomme.
     * WORD_LIST_CONTENANT / WORD_LIST_SANS / WORD_LIST_MOTIF / WORD_LIST_POSITION restent non
     * peuplees (WORD_LIST_AVEC = reservation generique non bornee, non peuplee ; voir les
     * sous-familles bornees WORD_LIST_AVEC_SINGLE_LETTER / _TWO_LETTERS plus bas, elles
     * peuplees). Presentes ici pour que Family::ALL / NEVER_SITEMAP restent la liste fermee
     * complete attendue par le reste de app/Seo/ (et pour que app/Search/*LinksBuilder.php, deja
     * cable dans public/index.php pour le rendu de /palabras/..., ait un nom de famille
     * disponible le jour ou un palier reel est mesure et propose). Toute ouverture future
     * exige, comme sur le depot francais : balayage complet des combinaisons reelles,
     * mesure TTFB, maillage interne construit ET verifie AVANT application, et sa propre
     * entree docs/DECISIONS.md -- jamais une simple reutilisation de cette liste.
     */
    public const WORD_LIST_COMMENCANT = 'word_list_commencant';
    public const WORD_LIST_TERMINANT = 'word_list_terminant';
    public const WORD_LIST_CONTENANT = 'word_list_contenant';
    public const WORD_LIST_AVEC = 'word_list_avec';
    public const WORD_LIST_SANS = 'word_list_sans';
    public const WORD_LIST_MOTIF = 'word_list_motif';
    public const WORD_LIST_POSITION = 'word_list_position';
    public const WORD_LIST_COMBINED = 'word_list_combined';

    /**
     * ES-025 / ES-026 : sous-familles BORNEES de "avec" (con-letras), distinctes de
     * WORD_LIST_AVEC ci-dessus (qui reste la reservation GENERIQUE/NON BORNEE, toujours dans
     * NEVER_SITEMAP). Meme distinction que les depots francais/allemand cousins (D-DE-026).
     * WORD_LIST_AVEC_SINGLE_LETTER (377 lignes, ES-025) et WORD_LIST_AVEC_TWO_LETTERS (4 340
     * index,follow + 109 noindex, ES-026) sont peuplees ; WORD_LIST_AVEC_THREE_LETTERS reste
     * reservee pour un prochain palier (0 ligne).
     */
    public const WORD_LIST_AVEC_SINGLE_LETTER = 'word_list_avec_single_letter';
    public const WORD_LIST_AVEC_TWO_LETTERS = 'word_list_avec_two_letters';
    public const WORD_LIST_AVEC_THREE_LETTERS = 'word_list_avec_three_letters';

    /** Route /buscador-de-palabras/{letras} -- tirage de chevalet, combinatoire, jamais indexable. */
    public const RACK = 'rack';

    /** @var list<string> */
    public const ALL = [
        self::HOME,
        self::WORD_ADMITTED,
        self::WORD_SPANISH_NOT_ADMITTED,
        self::WORD_LIST_LENGTH,
        self::WORD_LIST_COMMENCANT,
        self::WORD_LIST_TERMINANT,
        self::WORD_LIST_CONTENANT,
        self::WORD_LIST_AVEC,
        self::WORD_LIST_AVEC_SINGLE_LETTER,
        self::WORD_LIST_AVEC_TWO_LETTERS,
        self::WORD_LIST_AVEC_THREE_LETTERS,
        self::WORD_LIST_SANS,
        self::WORD_LIST_MOTIF,
        self::WORD_LIST_POSITION,
        self::WORD_LIST_COMBINED,
        self::RACK,
    ];

    /**
     * Familles dont l'espace d'URL est combinatoire, potentiellement non borne en pratique
     * (contenant/avec/sans/motif : toute sous-chaine, tout multiensemble de lettres, toute
     * combinaison de cases connues). Contrainte dure du role seo-registry : "Refuse infinite
     * letter/sequence combinations as indexable by default." Ces familles ne doivent JAMAIS
     * recevoir de sitemap_fragment, quel que soit le lot -- applique en dur par
     * scripts/apply_seo_batch.php, pas seulement documente ici.
     *
     * WORD_LIST_COMMENCANT/WORD_LIST_TERMINANT/WORD_LIST_POSITION/WORD_LIST_COMBINED/
     * WORD_LIST_AVEC_SINGLE_LETTER/TWO_LETTERS/THREE_LETTERS ne sont PAS dans cette liste
     * (espace borne par construction, comme sur le depot francais une fois mesure -- 27
     * lettres A-Z+Ñ, positions bornees par longueur, 1-3 lettres "avec" au plus, etc.) --
     * WORD_LIST_AVEC (generique, sans borne sur le nombre de lettres) reste distinct et reste
     * ici, voir ES-025/ES-026. Parmi les familles bornees, WORD_LIST_POSITION et
     * WORD_LIST_AVEC_THREE_LETTERS ne sont pas encore peuplees (0 ligne) : une famille peut
     * etre "autorisee en principe" sans avoir encore de lignes reelles. RACK reste ici (tirage
     * jusqu'a 15 tuiles, jokers compris, espace quasi illimite, comme /jouer/{lettres} sur le
     * depot francais).
     *
     * @var list<string>
     */
    public const NEVER_SITEMAP = [
        self::WORD_LIST_CONTENANT,
        self::WORD_LIST_AVEC,
        self::WORD_LIST_SANS,
        self::WORD_LIST_MOTIF,
        self::RACK,
    ];

    /**
     * Familles couvrant des formes espagnoles non retenues aux lexiques d'admissibilite
     * (FILE 2017 / FISE-2). Contrainte dure du role : "Never propose indexing these in
     * bulk." Applique comme un plafond dur (MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED) plutot
     * qu'une simple note, pour qu'un lot mal dimensionne echoue a l'application, pas
     * seulement a la relecture humaine. Peuplee au complet depuis ES-024 (2026-08-30) :
     * 86 944 lignes index,follow, lot unique, sous le plafond releve a 100 000 -- attestation
     * ligne par ligne (R6/R7) restee obligatoire.
     *
     * @var list<string>
     */
    public const SPANISH_NOT_ADMITTED = [
        self::WORD_SPANISH_NOT_ADMITTED,
    ];

    /**
     * Plafond applique par scripts/apply_seo_batch.php a tout lot touchant
     * Family::WORD_SPANISH_NOT_ADMITTED. Releve de 50 a 100 000 par ES-024 : decision
     * explicite du proprietaire du produit d'ouvrir tout l'espagnol non admis en un seul lot
     * (86 944 mots, meme raisonnement que D-017 cote francais -- le site repond a deux
     * questions symetriques, "ce mot est-il admis ?"/"ce mot est-il non admis ?", exclure les
     * formes non admises rend le site introuvable precisement quand l'incertitude du
     * visiteur est la plus grande). Marge au-dela du volume reel (86 944), meme ratio que
     * D-017 (500 000 pour 435 120 lignes reelles). Attestation ligne par ligne (notes non
     * vide, R6/R7) reste obligatoire, seul le VOLUME maximal par lot change -- voir
     * docs/DECISIONS.md ES-010 (blocage d'origine) et ES-024 (levee).
     */
    public const MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED = 100_000;

    public static function isValid(string $family): bool
    {
        return in_array($family, self::ALL, true);
    }

    public static function forbidsSitemap(string $family): bool
    {
        return in_array($family, self::NEVER_SITEMAP, true);
    }

    public static function isSpanishNotAdmitted(string $family): bool
    {
        return in_array($family, self::SPANISH_NOT_ADMITTED, true);
    }
}
