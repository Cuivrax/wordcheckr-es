<?php

declare(strict_types=1);

namespace App\Search;

/**
 * Analyse et canonicalise les contraintes de /mots/... (Phase 3, docs/08).
 *
 * Ordre canonique impose partout -- URL, cles, canonicals (docs/05_URL_SEO_INDEXATION.md) :
 *
 *   longueur -> commencant -> contenant -> terminant -> position -> avec -> sans -> motif
 *   -> statut -> tri
 *
 * "position" (D-023, ajoutee a la place reservee dans l'ordre ci-dessus) : une lettre connue
 * a UNE position precise, ex. "9-lettres/position/3/a" = mots de 9 lettres avec A en 3e
 * position. Exige TOUJOURS une longueur explicite (comme "tri", meme raison : sans longueur,
 * "position 3" n'a pas de sens borne). Espace de combinaisons volontairement restreint par
 * rapport a "motif" general (une seule lettre connue, jamais plusieurs simultanement) --
 * ~2 366 combinaisons reelles au total (26 lettres x positions 2 a longueur-1 x 14 longueurs),
 * largement borne, contrairement a "motif" (2^15 combinaisons par longueur, jamais indexable
 * -- D-012/NEVER_SITEMAP). position/1/{lettre} et position/{longueur}/{lettre} (premiere et
 * derniere lettre) sont des cas degeneres deja couverts par "commencant"/"terminant" -- pour
 * eviter le contenu duplique constate sur "motif" (un "motif/a----" et un "commencant/a"
 * produisant la meme liste sous deux URL canoniques distinctes, jamais rapproche), fromPath()
 * les COLLAPSE silencieusement vers prefix/suffix (meme mecanisme que la correction de
 * longueur derivee du motif ci-dessous) -- $position/$positionLetter ne portent jamais les
 * positions 1 ou longueur, canonicalPath() n'emet donc jamais "position/1/..." ni
 * "position/{longueur}/...".
 *
 * "avec/X" redondant avec un "commencant/X"/"terminant/X" d'UNE SEULE LETTRE (D-032) : meme
 * mecanisme de collapse silencieux que "position" ci-dessus, applique cette fois a "avec".
 * "commencant/X/avec/X" (minCount = 1, l'occurrence unique par defaut) est logiquement toujours
 * vrai des que le mot commence deja par X -- garder cette entree withLetters ferait basculer a
 * tort needsUnindexedPredicates() en regime BORNE plafonne (ROW_EXAMINATION_CEILING) pour une
 * contrainte qui n'exclut jamais aucune ligne, produisant un total tronque et trompeur au lieu
 * du vrai total (regime EXACT, sans plafond) deja disponible via "commencant/X" seul. fromPath()
 * retire alors cette entree $withLetters plutot que de traiter le cas dans WordListSolver --
 * canonicalPath() n'emet donc plus jamais "avec/X" a cote de "commencant"/"terminant/X" pour la
 * meme lettre X, le routeur redirige en 301. Seule la forme mono-lettre est concernee (minCount
 * strictement egal a 1) : un "avec/X/X" (minCount = 2, un DEUXIEME X) reste un vrai predicat,
 * jamais garanti par le seul prefixe/suffixe. Mesure : reports/query-plans/
 * commencant-avec-no-length-full-sweep.md section 5 (17/26 cas affectes, jusqu'a 224 205 pour R).
 *
 * "statut" et "tri" (D-022) sont des RAFFINEMENTS d'affichage, pas des contraintes de
 * recherche a proprement parler -- places en derniere position de l'ordre canonique, apres
 * toutes les contraintes de contenu. "statut/admis" ou "statut/non-admis" filtre sur
 * is_admitted (colonne precalculee, voir schema.sql). "tri/points" ou "tri/points-desc"
 * trie par score plutot que par ordre alphabetique -- EXIGE une longueur explicite (readSort()
 * refuse sinon, 404) : seul ce sous-ensemble (longueur seule, longueur+prefixe, longueur+
 * suffixe) a ete mesure sur comme couvrant tout le budget TTFB (reports/query-plans/
 * status-filter-admitted.md) ; trier sans aucun ancrage de longueur retomberait dans le meme
 * cout qu'un parcours large non borne, jamais mesure, donc jamais propose.
 *
 * Cette classe ne fait AUCUN acces base : parsing et validation syntaxique pures, meme
 * discipline que Rack::fromInput(). WordListSolver traduit ensuite ces filtres en requetes.
 *
 * Canonicalisation : quel que soit l'ordre des mots-cles dans l'URL recue, fromPath()
 * reconstruit une representation interne normalisee, puis canonicalPath() la re-serialise
 * TOUJOURS dans l'ordre impose. Le routeur compare le chemin recu a canonicalPath() -- meme
 * convention que TermPage::$slug et RackPage::$slug -- et redirige en 301 si différent
 * ("toute autre permutation redirige en 301", docs/05).
 */
final class WordListFilters
{
    /** Mots-cles reconnus, dans l'ordre canonique (D-023 : "position" ajoutee). */
    private const KEYWORDS = ['commencant', 'contenant', 'terminant', 'position', 'avec', 'sans', 'motif', 'statut', 'tri'];

    /** Valeurs acceptees pour le segment "statut" (D-022). */
    private const STATUS_VALUES = ['admis', 'non-admis'];

    /** Valeurs acceptees pour le segment "tri" (D-022). */
    private const SORT_VALUES = ['points', 'points-desc'];

    /**
     * @param int|null $length longueur exacte demandee, 2 a 15
     * @param string|null $prefix forme normalisee (A-Z), commencant
     * @param string|null $suffix forme normalisee (A-Z), terminant
     * @param string|null $contains forme normalisee (A-Z), contenant
     * @param array<string, int> $withLetters lettre normalisee => nombre minimum d'occurrences
     *        (avec, repetitions comptees), triees par cle
     * @param list<string> $withoutLetters lettres normalisees a exclure completement (sans),
     *        triees, sans doublon
     * @param string|null $pattern motif de cases connues : A-Z pour une lettre connue, '-'
     *        pour une case inconnue ; longueur du motif = longueur du mot (2 a 15)
     * @param int|null $position position 1-based d'une lettre connue (D-023), jamais 1 ni
     *        $length (voir collapse vers prefix/suffix, docblock de classe) -- toujours
     *        accompagne d'une longueur explicite et de $positionLetter
     * @param string|null $positionLetter lettre normalisee (A-Z) a $position, null ssi
     *        $position est null
     * @param string|null $status 'admis'|'non-admis' (D-022), null = aucun filtre de statut
     * @param string|null $sort 'points'|'points-desc' (D-022), null = ordre alphabetique
     *        (par defaut). Toujours accompagne d'une longueur explicite -- voir readSort().
     * @param int $page page demandee, >= 1 (1 = premiere page, jamais reflete dans l'URL)
     */
    private function __construct(
        public readonly ?int $length,
        public readonly ?string $prefix,
        public readonly ?string $suffix,
        public readonly ?string $contains,
        public readonly array $withLetters,
        public readonly array $withoutLetters,
        public readonly ?string $pattern,
        public readonly ?int $position,
        public readonly ?string $positionLetter,
        public readonly ?string $status,
        public readonly ?string $sort,
        public readonly int $page,
    ) {
    }

    /**
     * Construit les filtres a partir du chemin brut recu par le routeur (deja debarrasse du
     * prefixe "/mots", ex. "/7-lettres/commencant/ch" ou "" pour /mots seul).
     *
     * Renvoie null pour toute forme non exploitable : mot-cle inconnu, mot-cle duplique,
     * valeur manquante ou invalide, "avec"/"sans" sans lettre, longueur hors bornes, motif
     * hors bornes ou incoherent, "position" sans longueur ou hors bornes (D-023). Aucune
     * exception ne remonte --
     * meme discipline que Normalizer::normalize() et Rack::fromInput() : une entree
     * utilisateur ne doit jamais faire planter le flux HTTP normal. C'est une erreur de
     * saisie/routage, pas un resultat de recherche -- au routeur de traduire null en 404.
     */
    public static function fromPath(string $rawPath): ?self
    {
        $segments = array_values(array_filter(explode('/', trim($rawPath, '/')), static fn (string $s): bool => $s !== ''));

        [$page, $segments] = self::extractTrailingPage($segments);

        if ($page === null) {
            return null;
        }

        $length = null;
        $prefix = null;
        $suffix = null;
        $contains = null;
        $withLetters = [];
        $withoutLetters = [];
        $pattern = null;
        $position = null;
        $positionLetter = null;
        $status = null;
        $sort = null;
        $seenKeywords = [];

        $i = 0;
        $count = count($segments);

        // La longueur, si presente, doit ouvrir la liste -- c'est un token positionnel
        // ("{N}-lettres"), pas un mot-cle suivi d'une valeur comme les autres.
        if ($count > 0 && preg_match('/^(\d{1,2})-lettres\z/', $segments[0], $m) === 1) {
            $length = (int) $m[1];

            if ($length < Normalizer::MIN_LENGTH || $length > Normalizer::MAX_LENGTH) {
                return null;
            }

            $i = 1;
        }

        while ($i < $count) {
            $keyword = $segments[$i];

            if (!in_array($keyword, self::KEYWORDS, true)) {
                // Inclut le cas "{N}-lettres" hors premiere position, et tout mot-cle
                // inconnu : 404, jamais 301.
                return null;
            }

            if (isset($seenKeywords[$keyword])) {
                return null;
            }
            $seenKeywords[$keyword] = true;

            $i++;

            switch ($keyword) {
                case 'commencant':
                    [$prefix, $i] = self::readSingleLetterRun($segments, $i, $count);
                    if ($prefix === null) {
                        return null;
                    }
                    break;

                case 'contenant':
                    [$contains, $i] = self::readSingleLetterRun($segments, $i, $count);
                    if ($contains === null) {
                        return null;
                    }
                    break;

                case 'terminant':
                    [$suffix, $i] = self::readSingleLetterRun($segments, $i, $count);
                    if ($suffix === null) {
                        return null;
                    }
                    break;

                case 'position':
                    [$position, $positionLetter, $i] = self::readPosition($segments, $i, $count);
                    if ($position === null) {
                        return null;
                    }
                    break;

                case 'avec':
                    [$withLetters, $i] = self::readLetterMultiset($segments, $i, $count);
                    if ($withLetters === null) {
                        return null;
                    }
                    break;

                case 'sans':
                    [$withoutLetters, $i] = self::readLetterSet($segments, $i, $count);
                    if ($withoutLetters === null) {
                        return null;
                    }
                    break;

                case 'motif':
                    [$pattern, $i] = self::readPattern($segments, $i, $count);
                    if ($pattern === null) {
                        return null;
                    }
                    break;

                case 'statut':
                    [$status, $i] = self::readEnumValue($segments, $i, $count, self::STATUS_VALUES);
                    if ($status === null) {
                        return null;
                    }
                    break;

                case 'tri':
                    [$sort, $i] = self::readEnumValue($segments, $i, $count, self::SORT_VALUES);
                    if ($sort === null) {
                        return null;
                    }
                    break;
            }
        }

        // Le motif implique sa propre longueur (position de chaque case). Une longueur
        // explicite differente n'est pas une erreur 404 : canonicalPath() fait toujours
        // primer la longueur du motif, et le routeur redirige en 301 vers la forme corrigee
        // -- meme esprit que "toute autre permutation redirige en 301".
        if ($pattern !== null) {
            $length = strlen($pattern);
        }

        // "position" (D-023) exige une longueur explicite, quel que soit l'ordre de saisie
        // des segments -- meme raison que "tri" ci-dessous : sans longueur, "position 3" ne
        // borne rien. Incompatible avec "motif" : deux vocabulaires distincts pour le meme
        // concept (une lettre connue a une position) ne doivent jamais coexister dans la
        // meme URL.
        if ($position !== null) {
            if ($length === null || $pattern !== null || $position > $length) {
                return null;
            }

            // Positions degenerees (premiere/derniere lettre) : collapse silencieux vers
            // prefix/suffix plutot que de servir une seconde URL canonique pour la meme
            // liste de mots -- evite le contenu duplique deja constate sur "motif" (voir
            // docblock de classe). Un conflit avec un "commencant"/"terminant" explicite
            // portant une lettre DIFFERENTE reste une contrainte contradictoire -> 404.
            if ($position === 1) {
                if ($prefix !== null && $prefix !== $positionLetter) {
                    return null;
                }
                $prefix = $positionLetter;
                $position = null;
                $positionLetter = null;
            } elseif ($position === $length) {
                if ($suffix !== null && $suffix !== $positionLetter) {
                    return null;
                }
                $suffix = $positionLetter;
                $position = null;
                $positionLetter = null;
            }
        }

        // "avec" redondant avec un prefixe/suffixe D'UNE SEULE LETTRE (D-032) : "commencant/X/
        // avec/X" (minCount === 1) est TOUJOURS vrai des que "commence par X" l'est deja --
        // conserver cette entree withLetters ferait basculer a tort needsUnindexedPredicates()
        // en regime BORNE plafonne (ROW_EXAMINATION_CEILING) pour une contrainte qui n'exclut
        // jamais aucune ligne, produisant un total tronque et trompeur au lieu du vrai total
        // deja disponible sans plafond via le regime EXACT de "commencant/X" seul (mesure :
        // reports/query-plans/commencant-avec-no-length-full-sweep.md section 5 -- 17 des 26
        // combinaisons commencant/X/avec/X affichaient un total plafonne a 10 000 au lieu du
        // vrai total, jusqu'a 224 205 pour R). Retire silencieusement cette entree plutot que de
        // traiter le cas dans WordListSolver -- meme principe que le collapse "position"
        // degeneree ci-dessus (D-023) : canonicalPath() n'emet alors plus jamais "avec/X" a cote
        // de "commencant"/"terminant/X", le routeur redirige en 301 vers la forme simplifiee.
        // Ne retire QUE l'entree strictement redondante : minCount === 1 exactement -- un
        // minCount >= 2 (ex. avec/x/x, "commencant/x/avec/x/x") exige un DEUXIEME X, jamais
        // garanti par le seul prefixe/suffixe d'une lettre, donc jamais retire ici. Un prefixe/
        // suffixe de PLUSIEURS lettres n'est volontairement pas traite (hors perimetre mesure de
        // cette correction, voir le rapport cite) : seule la forme mono-lettre l'est.
        if ($prefix !== null && strlen($prefix) === 1 && isset($withLetters[$prefix]) && $withLetters[$prefix] === 1) {
            unset($withLetters[$prefix]);
        }

        if ($suffix !== null && strlen($suffix) === 1 && isset($withLetters[$suffix]) && $withLetters[$suffix] === 1) {
            unset($withLetters[$suffix]);
        }

        // "tri" (D-022) exige une longueur explicite, quel que soit l'ordre de saisie des
        // segments -- verifie ici, apres la longueur derivee du motif ci-dessus, plutot que
        // dans le case 'tri' du switch (une saisie non canonique pourrait sinon placer "tri"
        // avant "motif"/le token positionnel "{N}-lettres" dans les segments recus). Mesure
        // (schema.sql, idx_terms_length_score_normalized) : seul le sous-ensemble ancre sur une
        // longueur reste dans le budget TTFB pour un tri par points.
        if ($sort !== null && $length === null) {
            return null;
        }

        // Aucune contrainte du tout ($length === null && ... && $pattern === null) reste un
        // etat valide : /mots seul = parcours complet, pagine (voir isEmpty()). Ce n'est pas
        // une route annoncee par docs/05 -- le routeur decide s'il l'expose.

        ksort($withLetters, SORT_STRING);
        sort($withoutLetters, SORT_STRING);

        return new self($length, $prefix, $suffix, $contains, $withLetters, $withoutLetters, $pattern, $position, $positionLetter, $status, $sort, $page);
    }

    /**
     * Un seul segment lettres-uniquement (commencant / contenant / terminant). Renvoie
     * [null, $i] si absent, vide ou invalide.
     *
     * @param list<string> $segments
     * @return array{0: string|null, 1: int}
     */
    private static function readSingleLetterRun(array $segments, int $i, int $count): array
    {
        if ($i >= $count) {
            return [null, $i];
        }

        $normalized = Normalizer::normalize($segments[$i]);

        if ($normalized === '' || preg_match('/^[A-Z]+\z/', $normalized) !== 1 || strlen($normalized) > Normalizer::MAX_LENGTH) {
            return [null, $i];
        }

        return [$normalized, $i + 1];
    }

    /**
     * Deux segments consecutifs : une position 1-based (entier decimal, jamais 0 ni negatif)
     * puis une lettre unique (D-023). Renvoie [null, null, $i] si l'un des deux est absent,
     * vide ou invalide -- la borne superieure (position <= longueur) est verifiee par
     * l'appelant, une fois la longueur connue (voir fromPath()).
     *
     * @param list<string> $segments
     * @return array{0: int|null, 1: string|null, 2: int}
     */
    private static function readPosition(array $segments, int $i, int $count): array
    {
        if ($i >= $count || preg_match('/^[1-9]\d?\z/', $segments[$i]) !== 1) {
            return [null, null, $i];
        }

        $position = (int) $segments[$i];

        [$letter, $next] = self::readSingleLetterRun($segments, $i + 1, $count);

        if ($letter === null || strlen($letter) !== 1) {
            return [null, null, $i];
        }

        return [$position, $letter, $next];
    }

    /**
     * Un seul segment dont la valeur doit appartenir a $allowed (statut, tri -- D-022).
     * Renvoie [null, $i] si absent ou hors de la liste fermee -- jamais de valeur inventee.
     *
     * @param list<string> $segments
     * @param list<string> $allowed
     * @return array{0: string|null, 1: int}
     */
    private static function readEnumValue(array $segments, int $i, int $count, array $allowed): array
    {
        if ($i >= $count || !in_array($segments[$i], $allowed, true)) {
            return [null, $i];
        }

        return [$segments[$i], $i + 1];
    }

    /**
     * Une ou plusieurs cases-segments d'une seule lettre chacune (avec), consommees
     * jusqu'au prochain mot-cle connu ou la fin du chemin. Compte les repetitions.
     *
     * @param list<string> $segments
     * @return array{0: array<string, int>|null, 1: int}
     */
    private static function readLetterMultiset(array $segments, int $i, int $count): array
    {
        $letters = [];
        $start = $i;

        while ($i < $count && !in_array($segments[$i], self::KEYWORDS, true)) {
            $normalized = Normalizer::normalize($segments[$i]);

            if (strlen($normalized) !== 1 || preg_match('/^[A-Z]\z/', $normalized) !== 1) {
                return [null, $i];
            }

            $letters[$normalized] = ($letters[$normalized] ?? 0) + 1;
            $i++;
        }

        if ($i === $start) {
            // "avec" sans aucune lettre : segment vide ou immediatement suivi d'un mot-cle.
            return [null, $i];
        }

        return [$letters, $i];
    }

    /**
     * Une ou plusieurs cases-segments d'une seule lettre chacune (sans), dedupliquees.
     *
     * @param list<string> $segments
     * @return array{0: list<string>|null, 1: int}
     */
    private static function readLetterSet(array $segments, int $i, int $count): array
    {
        [$multiset, $next] = self::readLetterMultiset($segments, $i, $count);

        if ($multiset === null) {
            return [null, $next];
        }

        return [array_keys($multiset), $next];
    }

    /**
     * Le motif : un seul segment, lettres A-Z ou '-' (case inconnue), longueur 2 a 15,
     * au moins une lettre connue (un motif entierement fait de '-' n'apporte aucune
     * information au-dela de la longueur -- refuse pour rester un mot-cle utile).
     *
     * @param list<string> $segments
     * @return array{0: string|null, 1: int}
     */
    private static function readPattern(array $segments, int $i, int $count): array
    {
        if ($i >= $count) {
            return [null, $i];
        }

        $raw = $segments[$i];
        $letters = str_replace('-', '', $raw);
        $normalizedLetters = Normalizer::normalize($letters);

        if ($letters !== '' && (preg_match('/^[A-Z]+\z/', $normalizedLetters) !== 1 || strlen($normalizedLetters) !== strlen($letters))) {
            return [null, $i];
        }

        // Reconstruit le motif normalise en respectant la position d'origine des '-' : on ne
        // peut pas juste normaliser $raw tel quel, Normalizer::normalize() ne connait pas '-'.
        $pattern = '';
        $letterPos = 0;
        $normalizedChars = str_split($normalizedLetters);

        foreach (str_split($raw) as $char) {
            if ($char === '-') {
                $pattern .= '-';
                continue;
            }

            $pattern .= $normalizedChars[$letterPos] ?? '';
            $letterPos++;
        }

        if (strlen($pattern) < Normalizer::MIN_LENGTH || strlen($pattern) > Normalizer::MAX_LENGTH) {
            return [null, $i];
        }

        if ($letters === '') {
            // Motif entierement inconnu ("---") : refuse, n'apporte rien qu'une longueur ne
            // dise deja.
            return [null, $i];
        }

        return [$pattern, $i + 1];
    }

    /**
     * Detecte et retire un segment terminal "page/{n}". Absent -> page 1, segments
     * inchanges. "page/1" est syntaxiquement VALIDE (n'est pas une entree malformee) mais
     * jamais canonique -- canonicalUrl() l'omet toujours pour la page 1, donc le routeur
     * compare et redirige naturellement en 301 vers la forme sans "/page/1", meme mecanisme
     * que toute autre permutation (docs/05 : "toute autre permutation redirige en 301"), pas
     * un 404. Seule une valeur non numerique, 0 ou negative reste une entree malformee ->
     * [null, ...], propage vers fromPath() qui renvoie null (404).
     *
     * @param list<string> $segments
     * @return array{0: int|null, 1: list<string>}
     */
    private static function extractTrailingPage(array $segments): array
    {
        $count = count($segments);

        if ($count < 2 || $segments[$count - 2] !== 'page') {
            return [1, $segments];
        }

        if (preg_match('/^\d+\z/', $segments[$count - 1]) !== 1) {
            return [null, $segments];
        }

        $page = (int) $segments[$count - 1];

        if ($page < 1) {
            return [null, $segments];
        }

        return [$page, array_slice($segments, 0, $count - 2)];
    }

    /**
     * Chemin canonique, sans le "/mots" initial ni le "/page/{n}" final (la pagination est
     * geree separement par le routeur/la vue, pas par cette representation de filtre --
     * meme raison que "page 1" n'apparait jamais dans l'URL). Toujours reconstruit dans
     * l'ordre impose, quel que soit l'ordre recu en entree.
     */
    public function canonicalPath(): string
    {
        $segments = [];

        if ($this->length !== null) {
            $segments[] = $this->length . '-lettres';
        }

        if ($this->prefix !== null) {
            $segments[] = 'commencant';
            $segments[] = strtolower($this->prefix);
        }

        if ($this->contains !== null) {
            $segments[] = 'contenant';
            $segments[] = strtolower($this->contains);
        }

        if ($this->suffix !== null) {
            $segments[] = 'terminant';
            $segments[] = strtolower($this->suffix);
        }

        if ($this->position !== null) {
            $segments[] = 'position';
            $segments[] = (string) $this->position;
            $segments[] = strtolower($this->positionLetter);
        }

        if ($this->withLetters !== []) {
            $segments[] = 'avec';
            foreach ($this->withLetters as $letter => $times) {
                for ($k = 0; $k < $times; $k++) {
                    $segments[] = strtolower($letter);
                }
            }
        }

        if ($this->withoutLetters !== []) {
            $segments[] = 'sans';
            foreach ($this->withoutLetters as $letter) {
                $segments[] = strtolower($letter);
            }
        }

        if ($this->pattern !== null) {
            $segments[] = 'motif';
            $segments[] = strtolower($this->pattern);
        }

        if ($this->status !== null) {
            $segments[] = 'statut';
            $segments[] = $this->status;
        }

        if ($this->sort !== null) {
            $segments[] = 'tri';
            $segments[] = $this->sort;
        }

        return implode('/', $segments);
    }

    /** Chemin canonique complet, "/page/{n}" inclus si $this->page > 1. */
    public function canonicalUrl(): string
    {
        $base = '/mots' . ($this->canonicalPath() !== '' ? '/' . $this->canonicalPath() : '');

        return $this->page > 1 ? $base . '/page/' . $this->page : $base;
    }

    /**
     * true si le filtre ne pose aucune contrainte (parcours complet de la base). "tri" seul
     * ne peut jamais rendre ce test faux a lui seul (il exige toujours une longueur, voir
     * fromPath()) ; "statut" le peut (ex. /mots/statut/admis, sans autre contrainte) -- une
     * vraie restriction du panier, pas un parcours complet.
     */
    public function isEmpty(): bool
    {
        return $this->length === null && $this->prefix === null && $this->suffix === null
            && $this->contains === null && $this->withLetters === [] && $this->withoutLetters === []
            && $this->pattern === null && $this->position === null && $this->status === null;
    }

    /**
     * true si des predicats non couverts par un index dedie sont necessaires (contenant,
     * avec, sans, position (D-023), ou motif avec une case connue au-dela du prefixe initial).
     * Determine si WordListSolver doit appliquer WordListSolver::ROW_EXAMINATION_CEILING (voir
     * sa documentation pour le detail des mesures qui justifient ce plafond).
     */
    public function needsUnindexedPredicates(): bool
    {
        if ($this->contains !== null || $this->withLetters !== [] || $this->withoutLetters !== [] || $this->position !== null) {
            return true;
        }

        if ($this->pattern !== null) {
            // Le prefixe initial (avant le premier '-') est deja couvert par l'index
            // normalized/length -- voir WordListSolver::patternLeadingPrefix(). Une case
            // connue (A-Z) APRES la premiere case inconnue reste un predicat non indexe
            // (substr(normalized, position, 1) = lettre, evalue en ligne, pas via un index).
            $firstUnknown = strpos($this->pattern, '-');

            if ($firstUnknown === false) {
                // Pas de '-' du tout : readPattern() l'autorise (seul un motif ENTIEREMENT
                // fait de '-' est refuse) -- un motif entierement connu equivaut a
                // normalized = ?, couvert par l'index UNIQUE, aucun predicat supplementaire.
                return false;
            }

            return preg_match('/[A-Z]/', substr($this->pattern, $firstUnknown)) === 1;
        }

        // Prefixe ET suffixe combines : le suffixe est applique en predicat supplementaire
        // sur les lignes deja bornees par le prefixe (pas son propre index dans ce cas).
        if ($this->prefix !== null && $this->suffix !== null) {
            return true;
        }

        return false;
    }
}
