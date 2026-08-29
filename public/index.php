<?php

declare(strict_types=1);

/**
 * Front controller unique, sans framework (CLAUDE.md).
 *
 * Routes livrees en Phase 1 (docs/08) :
 *   GET /                pas de requete SQLite
 *   GET /palabra/{mot}    jusqu'a 3 requetes indexees (App\Search\TermLookup::find())
 *   GET /verificar/{mot}  redirection pure vers /palabra/{slug}, 0 requete SQLite
 *   GET /verificar?mot=.. meme redirection, entree par formulaire GET sans JavaScript
 *
 * Route ajoutee en Phase 2 (docs/08, agent data-engine) :
 *   GET /buscador-de-palabras/{letras}  au plus 10 requetes indexees
 *                         (App\Search\RackSolver::solve()), 0 requete si le plafond de
 *                         securite est declenche (voir reports/query-plans/phase2.md)
 *   GET /buscador-de-palabras?lettres=.. redirection pure vers
 *                         /buscador-de-palabras/{slug}, 0 requete SQLite --
 *                         ajoute par l'agent frontend (rendu du solveur, Phase 2 UI)
 *                         pour que le formulaire chevalet de la home fonctionne sans
 *                         JavaScript, meme principe que /verificar?mot=.. ci-dessus.
 *                         Fichier partage (CLAUDE.md) : ajout signale pour validation.
 *
 * Route ajoutee en Phase 3 (docs/08, agent data-engine) :
 *   GET /palabras/...      listes de mots par longueur, empiezan-por, contenant,
 *                          terminan-en, avec, sans, motif, seules ou combinees dans
 *                          l'ordre canonique (docs/05) -- App\Search\WordListSolver::solve(),
 *                          au plus 2 requetes indexees (reports/query-plans/phase3.md).
 *                          Toute permutation non canonique redirige en 301 vers la forme
 *                          imposee (App\Search\WordListFilters::canonicalPath()).
 *
 * Route ajoutee en Phase 5 (docs/08, agent data-engine) :
 *   GET /api/suggest?q=..  autocompletion, backend seul (une combobox cote frontend consomme
 *                          cette route dans un lot ulterieur) -- App\Search\Suggester::suggest(),
 *                          exactement 1 requete indexee (reports/query-plans/phase5.md), jamais
 *                          plus de 8 entrees, jamais d'erreur HTTP (entree vide, trop courte ou
 *                          hors A-Z -> tableau JSON vide, code 200). Reponse JSON pure, jamais de
 *                          vue app/View/ -- endpoint volontairement non indexable : aucun lien
 *                          HTML ne pointe vers cette route ailleurs sur le site.
 *
 * Enrichissement D-018 (docs/DECISIONS.md, agent data-engine) : /palabra/{mot} passe desormais
 *   'conjugation' (App\Search\Conjugation) a la vue, en plus de 'page'/'relations' -- +1
 *   requete indexee (App\Search\ConjugationLookup::find()), pour tout mot TROUVE, admis ou
 *   non (independant du statut Scrabble). TermPage porte aussi pos/posSecondary/gender
 *   (colonnes ajoutees a `terms`, zero requete supplementaire, deja incluses dans le SELECT
 *   de TermLookup). Budget par fiche : 9 requetes pour un mot admis, 4 pour un mot francais
 *   non admis (reports/query-plans/d018-conjugation.md).
 *
 * Repli additif (Phase 2, refonte du champ unique de la home, agent frontend) :
 *   GET /verificar?q=..   accepte en plus de ?mot=.. (non supprime), meme redirection
 *   GET /buscador-de-palabras?q=..  accepte en plus de ?lettres=.. (non supprime), meme
 *                         redirection. Necessaire car un seul <input> HTML ne peut porter
 *                         qu'un nom : la home n'a plus qu'un champ commun aux deux boutons
 *                         submit (formaction differents vers /verificar et
 *                         /buscador-de-palabras). "mot" et "lettres" restent lus en
 *                         priorite, "q" est un repli, jamais un remplacement -- aucune
 *                         route existante n'est modifiee.
 *                         Fichier partage (CLAUDE.md) : ajout signale pour validation.
 *
 * Etat d'erreur visible (audit final, design-consistency-reviewer, bloquant F3) : toute
 *   saisie invalide sur /verificar, /buscador-de-palabras ou le formulaire GET de
 *   /palabras redirige desormais vers /?erreur=1 ou /palabras?erreur=1 plutot que vers
 *   l'URL nue -- "erreur" est un simple indicateur de presentation lu par
 *   app/View/home.php et app/View/explore-hub.php pour afficher un bandeau ARIA
 *   role="alert" (WCAG 3.3.1), jamais une URL indexable distincte (canonicalPath reste
 *   "/" ou "/palabras" dans les deux cas, voir $render).
 *
 * Contrat de rendu : chaque route prepare un nom de vue et un tableau de donnees, puis
 * inclut app/View/{vue}.php (livre par l'agent frontend, Phase 1b) en exposant ce
 * tableau comme variables locales. Tant que app/View/ n'existe pas, un message texte
 * explicite remplace la vue plutot qu'une erreur fatale -- permet de tester le routage
 * et les donnees des maintenant.
 *
 * Fichier partage (CLAUDE.md) : cree ici a la demande explicite de la Phase 1 (docs/08),
 * signale pour validation avant edition future par un autre agent.
 *
 * Localisation d'URL espagnole (docs/DECISIONS.md ES-004 puis ES-014) : /mot -> /palabra,
 * /mots -> /palabras, /jouer -> /buscador-de-palabras, /verifier -> /verificar,
 * "commencant" -> "empiezan-por", "terminant" -> "terminan-en" (ES-004), puis
 * "contenant" -> "contienen", "avec" -> "con-letras", "sans" -> "sin", "motif" -> "patron",
 * "position" -> "posicion", "statut" -> "estado", "tri" -> "orden" (ES-014) -- segments de
 * App\Search\WordListFilters, source unique. PLUS AUCUN segment d'URL francais n'est
 * reconnu : un ancien chemin ("/palabras/avec/e") est un 404 sec, jamais une redirection.
 * Les noms de champs GET internes (mot, lettres, longueur, commencant, terminant, contenant,
 * avec, sans, motif, q, erreur) ne sont PAS renommes : ce sont des attributs `name` de
 * formulaires HTML (app/View/, hors perimetre de cet agent), pas une URL -- meme convention
 * qu'ES-004. Seul le SEGMENT de chemin construit a partir de leur valeur change.
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Database\Connection;
use App\Search\AvecSansLengthLinksBuilder;
use App\Search\AvecThreeLettersLinksBuilder;
use App\Search\AvecTwoLettersLinksBuilder;
use App\Search\ConjugationLookup;
use App\Search\ExploreHubBuilder;
use App\Search\LengthCombinedLinksBuilder;
use App\Search\LengthLinksBuilder;
use App\Search\LetterCombinedLinksBuilder;
use App\Search\Normalizer;
use App\Search\PositionLinksBuilder;
use App\Search\PrefixAvecLinksBuilder;
use App\Search\PrefixExtensionLinksBuilder;
use App\Search\Rack;
use App\Search\RackSolver;
use App\Search\RelationsFinder;
use App\Search\SenseLookup;
use App\Search\StartEndWithLinksBuilder;
use App\Search\Suggester;
use App\Search\SuffixExtensionLinksBuilder;
use App\Search\TermLookup;
use App\Search\TermPage;
use App\Search\WordListFilters;
use App\Search\WordListSolver;
use App\Seo\Registry;
use App\Seo\SeoMeta;

const MAX_RAW_SEGMENT_LENGTH = 64;
// /palabras/... peut enchainer plusieurs contraintes (longueur, empiezan-por, contienen,
// terminan-en, con-letras, sin, patron -- voir docs/05), donc plusieurs segments : la meme borne
// de 64 par segment individuel serait trop stricte pour la combinaison complete, mais le
// chemin dans son ensemble reste borne pour ecarter tout abus (Phase 3, agent data-engine).
const MAX_RAW_WORDLIST_PATH_LENGTH = 512;

header('Content-Type: text/html; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = rawurldecode($path);

if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

// POST autorise UNIQUEMENT sur /contact (formulaire de contact, D-025ter) -- premiere et
// seule route du site qui accepte une soumission (toutes les autres restent GET, D-007 :
// aucune ecriture sur la base au runtime, ce formulaire n'y touche pas non plus, il envoie
// un email via mail() native PHP). Toute autre methode sur toute autre route reste refusee.
$allowedMethods = $path === '/contact' ? ['GET', 'HEAD', 'POST'] : ['GET', 'HEAD'];

if (!in_array($method, $allowedMethods, true)) {
    http_response_code(405);
    header('Allow: ' . implode(', ', $allowedMethods));
    echo '405 Methode non autorisee';

    return;
}

// Depot espagnol independant (config/sites/es.php) -- defaut change de 'fr' a 'es'
// (fichier partage, CLAUDE.md -- changement signale explicitement, voir le rapport de
// session de l'agent data-engine plutot qu'une modification silencieuse).
$config = Config::load(getenv('SCRABBLE_SITE') ?: 'es');
$seoRegistry = new Registry($config->seoPath, $config->canonicalBaseUrl);

/**
 * Rend une vue via app/View/{name}.php, ou un message d'attente si elle n'existe pas
 * encore. $data est expose comme variables locales dans le fichier de vue.
 *
 * $canonicalPath, quand fourni, doit deja etre la forme canonique exacte servie pour cette
 * requete (Phase 6, agent seo-registry) -- resolue via le registre SEO en une requete
 * indexee sur storage/seo_fr.sqlite, jamais une erreur si la base ou la ligne est absente
 * (App\Seo\Registry::resolve(), noindex,follow par defaut, D-005). null pour les reponses
 * qui n'ont pas d'URL canonique propre (404, JSON) -- la vue omet alors <link rel="canonical">.
 *
 * @param array<string, mixed> $data
 */
$render = static function (string $view, array $data, int $status = 200, ?string $canonicalPath = null) use ($seoRegistry): void {
    http_response_code($status);

    $file = __DIR__ . '/../app/View/' . $view . '.php';

    if (!is_file($file)) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Vue non implementee : {$view}\n";
        echo "Donnees pretes, en attente du gabarit (Phase 1b, agent frontend).\n";

        return;
    }

    $data['seo'] = $canonicalPath !== null ? $seoRegistry->resolve($canonicalPath) : SeoMeta::noindex(null);

    (static function (string $__file, array $__data): void {
        extract($__data, EXTR_SKIP);
        require $__file;
    })($file, $data);
};

$redirect = static function (string $location, int $status): void {
    http_response_code($status);
    header('Location: ' . $location);
};

if ($path === '/') {
    $render('home', [
        'minTermLength' => $config->minTermLength,
        'maxTermLength' => $config->maxTermLength,
        'error' => isset($_GET['erreur']),
    ], 200, '/');

    return;
}

if ($path === '/api/suggest') {
    // Phase 5 (docs/08, agent data-engine) : JSON pur, jamais de vue app/View/, jamais
    // d'erreur HTTP -- entree vide/trop courte/hors A-Z -> tableau vide, code 200. Remplace
    // le Content-Type text/html defini plus haut : aucune sortie n'a encore ete envoyee a ce
    // stade, header() peut donc encore le remplacer sans avertissement.
    header('Content-Type: application/json; charset=utf-8');

    $raw = $_GET['q'] ?? '';
    $raw = is_string($raw) ? $raw : '';

    $suggestions = strlen($raw) > MAX_RAW_SEGMENT_LENGTH
        ? []
        : (new Suggester(new Connection($config->dictionaryPath)))->suggest($raw);

    echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);

    return;
}

if (preg_match('#^/palabra/([^/]+)$#u', $path, $matches) === 1) {
    $segment = $matches[1];

    if ($segment === '' || strlen($segment) > MAX_RAW_SEGMENT_LENGTH) {
        $render('not-found', ['requestPath' => $path], 404);

        return;
    }

    $connection = new Connection($config->dictionaryPath);
    $lookup = new TermLookup($connection, $config->tileScores);
    $page = $lookup->find($segment);

    if ($page === null) {
        $render('not-found', ['requestPath' => $path], 404);

        return;
    }

    if ($segment !== $page->slug) {
        $redirect('/palabra/' . $page->slug, 301);

        return;
    }

    // Relations (Phase 4, docs/08, agent data-engine) : 5 requetes indexees
    // supplementaires (App\Search\RelationsFinder), uniquement pour un mot
    // effectivement admis -- jamais calculees pour "francais non admis" ou
    // "inconnu", ou elles n'auraient aucun sens produit. null sinon, la vue
    // ne rend alors aucune section de relations (voir reports/query-plans/phase4.md).
    $relations = $page->status === TermPage::STATUS_ADMITTED
        ? (new RelationsFinder($connection))->find($page->normalized)
        : null;

    // Conjugaison (D-018) : 1 requete indexee supplementaire (App\Search\ConjugationLookup),
    // pour tout mot TROUVE, admis ou non -- independant du statut Scrabble (docs/DECISIONS.md,
    // D-018). Jamais pour "inconnu" ($page est toujours trouve a ce point du routeur).
    $conjugation = (new ConjugationLookup($connection))->find($page->normalized);

    // Definitions (D-043) : 1 requete indexee supplementaire (App\Search\SenseLookup), meme
    // perimetre que ConjugationLookup ci-dessus -- tout mot TROUVE, admis ou non. Compensee
    // par la fusion des requetes "mot precedent"/"mot suivant" de TermLookup (D-043) : budget
    // dictionnaire reste a 9 requetes pour un mot admis, sous le plafond "moins de 10"
    // (CLAUDE.md) -- voir App\Search\SenseLookup, docblock de budget.
    $senses = (new SenseLookup($connection))->find($page->normalized);

    $render(
        'word',
        ['page' => $page, 'relations' => $relations, 'conjugation' => $conjugation, 'senses' => $senses, 'lexicons' => $config->lexicons],
        200,
        '/palabra/' . $page->slug,
    );

    return;
}

if ($path === '/buscador-de-palabras') {
    // Repli formulaire GET sans JavaScript (home, agent frontend, Phase 2) --
    // meme principe que /verificar?mot=.. juste au-dessus : 0 requete SQLite,
    // redirection pure vers la forme canonique /buscador-de-palabras/{slug}
    // (App\Search\Rack, deja construite par le solveur, reutilisee ici sans le lancer).
    //
    // Fichier partage (CLAUDE.md) : repli supplementaire ajoute par l'agent frontend
    // pour la refonte du champ unique de la home (un seul <input name="q">, deux
    // boutons submit avec des formaction differents vers /verificar et
    // /buscador-de-palabras -- deux noms de champ sur un seul input HTML sont
    // impossibles). "lettres" reste lu en premier et continue de fonctionner seul
    // (repli existant, non supprime) ; "q" n'est qu'un second nom accepte, additif
    // uniquement.
    $raw = $_GET['lettres'] ?? ($_GET['q'] ?? '');
    $raw = is_string($raw) ? trim($raw) : '';

    if ($raw === '' || strlen($raw) > MAX_RAW_SEGMENT_LENGTH) {
        // F3 (audit final, design-consistency-reviewer) : silencieusement revenir sur "/"
        // sans indication laissait l'utilisateur face a un formulaire vide, sans savoir
        // que sa saisie avait ete rejetee (WCAG 3.3.1). "erreur" est un simple indicateur
        // de presentation, consomme uniquement par app/View/home.php -- jamais une URL
        // indexable distincte (canonicalPath de "/" reste "/", voir $render ci-dessus).
        $redirect('/?erreur=1', 302);

        return;
    }

    $rack = Rack::fromInput($raw);

    if ($rack === null) {
        $redirect('/?erreur=1', 302);

        return;
    }

    $redirect('/buscador-de-palabras/' . $rack->slug, 302);

    return;
}

if (preg_match('#^/buscador-de-palabras/([^/]+)$#u', $path, $matches) === 1) {
    $segment = $matches[1];

    if ($segment === '' || strlen($segment) > MAX_RAW_SEGMENT_LENGTH) {
        $render('not-found', ['requestPath' => $path], 404);

        return;
    }

    $connection = new Connection($config->dictionaryPath);
    $solver = new RackSolver($connection);
    $page = $solver->solve($segment);

    if ($page === null) {
        $render('not-found', ['requestPath' => $path], 404);

        return;
    }

    if ($segment !== $page->slug) {
        $redirect('/buscador-de-palabras/' . $page->slug, 301);

        return;
    }

    // tileScores : les tuiles du chevalet n'affichaient pas leur valeur en points,
    // contrairement a la fiche mot (audit final, C3) -- meme table que partout ailleurs
    // (config/sites/fr.php), jamais recalculee.
    $render('play', ['page' => $page, 'tileScores' => $config->tileScores, 'lexicons' => $config->lexicons], 200, '/buscador-de-palabras/' . $page->slug);

    return;
}

if ($path === '/palabras' || preg_match('#^/palabras(/.*)$#u', $path, $matches) === 1) {
    // Route ajoutee en Phase 3 (docs/08, agent data-engine) : listes de mots par longueur,
    // empiezan-por, contienen, terminan-en, con-letras, sin, patron --
    // App\Search\WordListSolver::solve(), au plus 2 requetes indexees (voir
    // reports/query-plans/phase3.md). Fichier partage (CLAUDE.md) : ajout signale pour
    // validation, meme convention que les routes precedentes.
    $rest = $matches[1] ?? '';

    // Repli formulaire GET sans JavaScript pour le constructeur de contraintes de la home
    // (app/View/home.php, rapprochement de prototype/index.html) et l'outil "Contiene" de la
    // page hub /palabras (App\Search\ExploreHub ci-dessous) : 0 requete SQLite, redirection
    // pure vers la forme canonique -- meme principe que /verificar?mot=.. et
    // /buscador-de-palabras?lettres=... Chaque champ texte devient un segment de chemin ;
    // "con-letras"/"sin" eclatent leur valeur en un segment par lettre
    // (App\Search\WordListFilters::readLetterMultiset() n'accepte qu'une lettre par
    // segment). Un champ absent ou vide n'ajoute aucun segment. "contienen", "con-letras",
    // "sin", "patron" restent volontairement hors sitemap/index (combinaisons infinies,
    // App\Seo\Family::NEVER_SITEMAP) : ce formulaire est un outil, jamais une liste de pages
    // pre-generees.
    //
    // Noms de champs GET ($field('commencant')/$field('contenant')/$field('avec')...) NON
    // renommes : ce sont les attributs `name` des <input> de app/View/home.php et
    // explore-hub.php (hors perimetre de cet agent) -- seul le SEGMENT de chemin construit a
    // partir de leur valeur change ('empiezan-por/'/'terminan-en/' en ES-004, puis
    // 'contienen/'/'con-letras/'/'sin/'/'patron/' en ES-014), pour rester reconnu par
    // App\Search\WordListFilters::KEYWORDS -- source unique de ces mots-cles.
    if ($rest === '' && $_GET !== []) {
        $field = static function (string $name) use ($config): string {
            $raw = $_GET[$name] ?? '';
            $raw = is_string($raw) ? trim($raw) : '';

            return strlen($raw) > MAX_RAW_SEGMENT_LENGTH ? '' : $raw;
        };

        $segments = [];

        $length = $field('longueur');
        if ($length !== '' && ctype_digit($length)) {
            $segments[] = $length . '-letras';
        }

        $commencant = $field('commencant');
        if ($commencant !== '') {
            $segments[] = 'empiezan-por/' . $commencant;
        }

        $contenant = $field('contenant');
        if ($contenant !== '') {
            $segments[] = 'contienen/' . $contenant;
        }

        $terminant = $field('terminant');
        if ($terminant !== '') {
            $segments[] = 'terminan-en/' . $terminant;
        }

        $avec = $field('avec');
        if ($avec !== '') {
            // mb_str_split() (pas str_split()) : une lettre Ñ saisie dans ce champ doit
            // produire UN segment, jamais deux demi-octets -- inchange par ES-014, seul le
            // mot-cle qui precede change.
            $segments[] = 'con-letras/' . implode('/', mb_str_split($avec));
        }

        $sans = $field('sans');
        if ($sans !== '') {
            $segments[] = 'sin/' . implode('/', mb_str_split($sans));
        }

        $motif = $field('motif');
        if ($motif !== '') {
            $segments[] = 'patron/' . $motif;
        }

        if ($segments !== []) {
            $formFilters = WordListFilters::fromPath(implode('/', $segments));

            // F3 (audit final, design-consistency-reviewer) : une combinaison de contraintes
            // invalide (ex. longueur hors plage) revenait silencieusement sur /palabras, sans
            // que l'utilisateur sache que sa saisie avait ete rejetee. "erreur" reste un
            // indicateur de presentation pur, consomme uniquement par
            // app/View/explore-hub.php.
            $redirect($formFilters !== null ? '/palabras/' . $formFilters->canonicalPath() : '/palabras?erreur=1', 302);

            return;
        }
    }

    if (strlen($rest) > MAX_RAW_WORDLIST_PATH_LENGTH) {
        $render('not-found', ['requestPath' => $path], 404);

        return;
    }

    // Page hub /palabras (audit SEO final, seo-technical-auditor, C4 : les pages
    // /palabras/... sans aucun lien entrant depuis le site) : trois grilles completes vers
    // les familles deja indexees (longueur, empiezan-por, terminan-en -- 66 liens au total,
    // D-017), construites depuis App\Search\ExploreHub, jamais un GROUP BY au runtime (voir
    // sa propre entete pour la mesure qui l'impose). Remplace le 404 que
    // WordListSolver::solve('') aurait renvoye.
    $connection = new Connection($config->dictionaryPath);

    if ($rest === '') {
        $hub = (new ExploreHubBuilder($connection))->build();

        $render('explore-hub', ['hub' => $hub, 'error' => isset($_GET['erreur'])], 200, '/palabras');

        return;
    }

    $solver = new WordListSolver($connection);
    $page = $solver->solve($rest);

    if ($page === null) {
        $render('not-found', ['requestPath' => $path], 404);

        return;
    }

    $canonical = $page->canonicalPath === '' ? '/palabras' : '/palabras/' . $page->canonicalPath;
    $canonical .= $page->page > 1 ? '/page/' . $page->page : '';

    if ($path !== $canonical) {
        $redirect($canonical, 301);

        return;
    }

    // Maillage interne precalcule (D-022/D-023bis/D-024/D-024bis/D-027/D-029/D-030/D-031/
    // D-033/D-034) : chaque *LinksBuilder lit list_counts (1 requete triviale), jamais un
    // calcul sur `terms`. $filters est garanti non-null : $page->canonicalPath vient de
    // WordListFilters::canonicalPath(), sa reanalyse par fromPath() reussit toujours.
    $filters = WordListFilters::fromPath($page->canonicalPath);

    $hasNoOtherConstraint = static function (WordListFilters $f, array $ignore = []): bool {
        $active = [
            'length' => $f->length !== null,
            'prefix' => $f->prefix !== null,
            'suffix' => $f->suffix !== null,
            'contains' => $f->contains !== null,
            'withLetters' => $f->withLetters !== [],
            'withoutLetters' => $f->withoutLetters !== [],
            'pattern' => $f->pattern !== null,
            'position' => $f->position !== null || $f->positionLetter !== null,
            'status' => $f->status !== null,
            'sort' => $f->sort !== null,
        ];

        foreach ($ignore as $key) {
            unset($active[$key]);
        }

        return !in_array(true, $active, true);
    };

    // mb_strlen(), pas strlen() (audit round, meme classe de bug qu'ES-003/ES-006) : Ñ fait
    // 2 octets en UTF-8 -- strlen() === 1 etait toujours faux pour ce prefixe/suffixe, ce qui
    // desactivait silencieusement PrefixAvecLinksBuilder/LetterCombinedLinksBuilder/
    // StartEndWithLinksBuilder pour la seule lettre Ñ. Sans effet tant que list_counts est
    // vide (ces builders ne rendent rien), mais reel des que la table sera peuplee.
    $isBareSingleLetterPrefix = $filters->prefix !== null && mb_strlen($filters->prefix, 'UTF-8') === 1
        && $hasNoOtherConstraint($filters, ['prefix']);
    $isBareSingleLetterSuffix = $filters->suffix !== null && mb_strlen($filters->suffix, 'UTF-8') === 1
        && $hasNoOtherConstraint($filters, ['suffix']);
    $isBarePrefixOnly = $filters->prefix !== null && $hasNoOtherConstraint($filters, ['prefix']);
    $isBareSuffixOnly = $filters->suffix !== null && $hasNoOtherConstraint($filters, ['suffix']);
    $isBarePrefixSuffixPair = $filters->prefix !== null && mb_strlen($filters->prefix, 'UTF-8') === 1
        && $filters->suffix !== null && mb_strlen($filters->suffix, 'UTF-8') === 1
        && $hasNoOtherConstraint($filters, ['prefix', 'suffix']);
    $isLengthPlusSinglePrefixOnly = $filters->length !== null && $filters->prefix !== null
        && mb_strlen($filters->prefix, 'UTF-8') === 1 && $hasNoOtherConstraint($filters, ['length', 'prefix']);
    $isLengthPlusSingleSuffixOnly = $filters->length !== null && $filters->suffix !== null
        && mb_strlen($filters->suffix, 'UTF-8') === 1 && $hasNoOtherConstraint($filters, ['length', 'suffix']);

    $singleAvecLetter = null;
    if (count($filters->withLetters) === 1) {
        $onlyLetter = array_key_first($filters->withLetters);
        if ($filters->withLetters[$onlyLetter] === 1) {
            $singleAvecLetter = $onlyLetter;
        }
    }

    $twoAvecLetters = null;
    if (count($filters->withLetters) === 2) {
        $letters = array_keys($filters->withLetters);
        if ($filters->withLetters[$letters[0]] === 1 && $filters->withLetters[$letters[1]] === 1) {
            $twoAvecLetters = $letters;
        }
    }

    $isLengthPlusSingleAvecOnly = $filters->length !== null && $singleAvecLetter !== null
        && $hasNoOtherConstraint($filters, ['length', 'withLetters']);
    $isLengthPlusTwoAvecOnly = $filters->length !== null && $twoAvecLetters !== null
        && $hasNoOtherConstraint($filters, ['length', 'withLetters']);
    $isAvecSansOnlyNoLength = $filters->length === null && $singleAvecLetter !== null
        && count($filters->withoutLetters) === 1
        && $hasNoOtherConstraint($filters, ['withLetters', 'withoutLetters']);

    $lengthLinks = $filters->length !== null
        ? (new LengthLinksBuilder($connection))->build($filters->length)
        : null;

    $letterCombinedLinks = match (true) {
        $isBareSingleLetterPrefix => (new LetterCombinedLinksBuilder($connection))->buildForStart($filters->prefix),
        $isBareSingleLetterSuffix => (new LetterCombinedLinksBuilder($connection))->buildForEnd($filters->suffix),
        default => null,
    };

    $prefixAvecLinks = $isBareSingleLetterPrefix
        ? (new PrefixAvecLinksBuilder($connection))->build($filters->prefix)
        : null;

    $prefixExtensionLinks = $isBarePrefixOnly
        ? (new PrefixExtensionLinksBuilder($connection))->build($filters->prefix)
        : null;

    $suffixExtensionLinks = $isBareSuffixOnly
        ? (new SuffixExtensionLinksBuilder($connection))->build($filters->suffix)
        : null;

    $lengthCombinedLinks = match (true) {
        $isLengthPlusSinglePrefixOnly => (new LengthCombinedLinksBuilder($connection))->buildForStart($filters->length, $filters->prefix),
        $isLengthPlusSingleSuffixOnly => (new LengthCombinedLinksBuilder($connection))->buildForEnd($filters->length, $filters->suffix),
        default => null,
    };

    $startEndWithLinks = $isBarePrefixSuffixPair
        ? (new StartEndWithLinksBuilder($connection))->build($filters->prefix, $filters->suffix)
        : null;

    $positionLinks = $isLengthPlusSingleAvecOnly
        ? (new PositionLinksBuilder($connection))->build($filters->length, $singleAvecLetter)
        : null;

    $avecTwoLettersLinks = $isLengthPlusSingleAvecOnly
        ? (new AvecTwoLettersLinksBuilder($connection))->build($filters->length, $singleAvecLetter)
        : null;

    $avecThreeLettersLinks = $isLengthPlusTwoAvecOnly
        ? (new AvecThreeLettersLinksBuilder($connection))->build($filters->length, $twoAvecLetters[0], $twoAvecLetters[1])
        : null;

    $avecSansLengthLinks = $isAvecSansOnlyNoLength
        ? (new AvecSansLengthLinksBuilder($connection))->build($singleAvecLetter, $filters->withoutLetters[0])
        : null;

    $render('word-list', [
        'page' => $page,
        'lengthLinks' => $lengthLinks,
        'letterCombinedLinks' => $letterCombinedLinks,
        'prefixAvecLinks' => $prefixAvecLinks,
        'prefixExtensionLinks' => $prefixExtensionLinks,
        'suffixExtensionLinks' => $suffixExtensionLinks,
        'lengthCombinedLinks' => $lengthCombinedLinks,
        'startEndWithLinks' => $startEndWithLinks,
        'positionLinks' => $positionLinks,
        'avecTwoLettersLinks' => $avecTwoLettersLinks,
        'avecThreeLettersLinks' => $avecThreeLettersLinks,
        'avecSansLengthLinks' => $avecSansLengthLinks,
    ], 200, $canonical);

    return;
}

if ($path === '/verificar' || preg_match('#^/verificar/([^/]*)$#u', $path, $matches) === 1) {
    // Fichier partage (CLAUDE.md) : "q" ajoute par l'agent frontend, meme raison et
    // meme convention que le repli "q" de /buscador-de-palabras juste au-dessus -- "mot"
    // reste lu en premier et continue de fonctionner seul (repli existant, non supprime).
    $raw = $matches[1] ?? ($_GET['mot'] ?? ($_GET['q'] ?? ''));
    $raw = is_string($raw) ? trim($raw) : '';

    if ($raw === '' || strlen($raw) > MAX_RAW_SEGMENT_LENGTH) {
        // F3 : voir le commentaire equivalent sur /buscador-de-palabras ci-dessus, meme raison.
        $redirect('/?erreur=1', 302);

        return;
    }

    $normalized = Normalizer::normalize($raw);

    if (!Normalizer::isValid($normalized)) {
        $redirect('/?erreur=1', 302);

        return;
    }

    $redirect('/palabra/' . mb_strtolower($normalized, 'UTF-8'), 302);

    return;
}

// Pages legales (D-025ter) : /mentions-legales, /confidentialite, /contact -- volontairement
// non indexees par defaut (D-026, aucune ligne au registre SEO pour elles), $render() avec
// leur propre chemin en canonicalPath suffit (App\Seo\Registry::resolve() renvoie
// noindex,follow par defaut en l'absence de ligne, exactement l'etat voulu ici).
if ($path === '/contact') {
    if ($method === 'POST') {
        // Piege a bots (D-025ter) : champ cache hors du flux visuel/tabulation cote vue --
        // un bot qui le remplit recoit une fausse confirmation, aucun mail envoye.
        $honeypot = is_string($_POST['site_web'] ?? null) ? trim($_POST['site_web']) : '';

        if ($honeypot !== '') {
            $redirect('/contact?envoye=1', 302);

            return;
        }

        $rawEmail = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        $rawEmail = str_replace(["\r", "\n"], '', $rawEmail);
        $email = filter_var($rawEmail, FILTER_VALIDATE_EMAIL);

        $name = is_string($_POST['nom'] ?? null) ? mb_substr(trim($_POST['nom']), 0, 100) : '';
        $message = is_string($_POST['message'] ?? null) ? trim($_POST['message']) : '';

        // Adresse jamais versee au depot (demande anti-spam explicite, D-025ter) -- lue
        // exclusivement via l'environnement, meme convention que SCRABBLE_SITE ci-dessus ; a
        // definir cote hebergement (cPanel / o2switch : "Environment Variables"), jamais dans
        // un fichier .php ni .env commite.
        $contactEmail = getenv('SCRABBLE_CONTACT_EMAIL');

        if ($email === false || $message === '' || mb_strlen($message) > 5000 || $contactEmail === false || $contactEmail === '') {
            $redirect('/contact?erreur=1', 302);

            return;
        }

        $subject = 'Nouveau message via WORD CHECKR';
        $body = ($name !== '' ? "Nom : {$name}\n" : '') . "Email : {$email}\n\n{$message}\n";
        $sent = @mail($contactEmail, $subject, $body, 'Reply-To: ' . $email);

        $redirect($sent ? '/contact?envoye=1' : '/contact?erreur=1', 302);

        return;
    }

    $render('contact', [
        'error' => isset($_GET['erreur']),
        'success' => isset($_GET['envoye']),
    ], 200, '/contact');

    return;
}

if ($path === '/mentions-legales') {
    $render('mentions-legales', [], 200, '/mentions-legales');

    return;
}

if ($path === '/confidentialite') {
    $render('confidentialite', [], 200, '/confidentialite');

    return;
}

$render('not-found', ['requestPath' => $path], 404);
