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
 * ETAT REEL DE CE DEPOT (docs/DECISIONS.md ES-009, ES-016, ES-018) : HOME, WORD_ADMITTED,
 * WORD_LIST_LENGTH, WORD_LIST_COMMENCANT, WORD_LIST_TERMINANT et WORD_LIST_COMBINED ont des
 * lignes dans storage/seo_es.sqlite a ce stade. Toutes les autres constantes existent pour que
 * la FORME du registre (schema, classes, outils de build) soit complete et prete a recevoir de
 * futurs paliers sans migration de schema -- mais aucune d'entre elles n'est peuplee. Une
 * famille non peuplee n'indexe rien PAR CONSTRUCTION : une route absente de `registry` reste
 * noindex,follow (D-005 du depot francais, meme contrat ici, voir App\Seo\Registry::resolve()).
 *
 * Correspondance avec les prefixes de fragments de sitemap reellement generes a ce stade
 * (docs/05_URL_SEO_INDEXATION.md, section Sitemaps) : core-* (home + hub /palabras),
 * words-* (mots admis), letters-* (listes par longueur), starts-* (empiezan-por, 1 et 3
 * lettres), ends-* (terminan-en, 2 caracteres), combined-* (longueur+empiezan-por,
 * longueur+terminan-en). Les autres prefixes documentes (contains-, avec-*, position-...)
 * restent des reservations de nommage pour de futurs paliers, jamais generes par ce depot a ce
 * jour.
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
     * NON PEUPLEE dans ce premier palier -- voir docs/DECISIONS.md ES-009/ES-010 : volume
     * (86 944 mots) et utilite reelle de chaque page pas encore discutes/verifies au sens de
     * la contrainte de role "never propose indexing these in bulk", tenue separee du palier
     * "mots admis" par prudence, pas par manque de temps.
     */
    public const WORD_SPANISH_NOT_ADMITTED = 'word_spanish_not_admitted';

    public const WORD_LIST_LENGTH = 'word_list_length';

    /**
     * Constantes reservees pour de futurs paliers combinatoires (empiezan-por, terminan-en,
     * contenant, avec, sans, motif, position, combinaisons). WORD_LIST_COMMENCANT/
     * WORD_LIST_TERMINANT sont peuplees depuis ES-016 (1 lettre / 2 caracteres) et ES-018
     * (WORD_LIST_COMMENCANT etendue a 3 lettres). WORD_LIST_COMBINED est peuplee depuis ES-018
     * (longueur+empiezan-por a 1 caractere, longueur+terminan-en a 2 caracteres -- PAS le
     * troisieme axe "empiezan-por+terminan-en sans longueur", toujours vide, list_counts
     * 'start_end'/'length_start_end' non construits, voir ES-017). WORD_LIST_CONTENANT/
     * WORD_LIST_AVEC/WORD_LIST_SANS/WORD_LIST_MOTIF/WORD_LIST_POSITION restent non peuplees --
     * presentes ici pour que Family::ALL/NEVER_SITEMAP restent la liste fermee complete
     * attendue par le reste de app/Seo/ (et pour que app/Search/*LinksBuilder.php, deja
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
     * WORD_LIST_COMMENCANT/WORD_LIST_TERMINANT/WORD_LIST_POSITION/WORD_LIST_COMBINED ne sont
     * PAS dans cette liste (espace borne par construction, comme sur le depot francais une
     * fois mesure -- 26 lettres, positions bornees par longueur, etc.) mais ne sont pas non
     * plus peuplees a ce stade : une famille peut etre "autorisee en principe" sans avoir
     * encore de lignes reelles. RACK reste ici (tirage jusqu'a 15 tuiles, jokers compris,
     * espace quasi illimite, comme /jouer/{lettres} sur le depot francais).
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
     * seulement a la relecture humaine. Vide de lignes reelles a ce stade (ES-009/ES-010).
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
