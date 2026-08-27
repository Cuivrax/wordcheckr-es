<?php

declare(strict_types=1);

/**
 * Applique un lot de rollout au registre storage/seo_fr.sqlite (Phase 6, docs/08).
 *
 * Hors ligne uniquement (CLI). Aucun lot n'est fourni ni appliqué par ce dépôt à ce stade --
 * ce script est l'OUTIL générique qui appliquera un lot une fois qu'il aura été validé
 * explicitement (CLAUDE.md : "arrête-toi et attends validation explicite avant de figer un
 * premier lot de rollout concret"). Le construire maintenant, sans l'exécuter sur un lot réel,
 * permet de vérifier que les règles dures sont appliquées par l'OUTIL et pas seulement décrites
 * en prose -- un lot mal formé échoue à l'exécution, pas seulement à la relecture humaine.
 *
 * Usage :
 *     php scripts/apply_seo_batch.php path/to/batch.php [--force]
 *
 * Format d'un fichier de lot (PHP, jamais exécuté au runtime) : retourne un tableau
 *
 *   [
 *       'batch_id' => 'admitted-2026-08-10-pilot',
 *       'added_at' => '2026-08-10',           // optionnel, defaut = date du jour (UTC)
 *       'rows' => [
 *           [
 *               'route_path' => '/mot/poser',
 *               'family' => App\Seo\Family::WORD_ADMITTED,
 *               'robots' => 'index,follow',
 *               'canonical_path' => '/mot/poser',   // optionnel, defaut = route_path
 *               'sitemap_fragment' => 'words-0001', // optionnel, defaut = null
 *               'result_count' => null,             // optionnel, uniquement pour /mots/...
 *               'notes' => 'mot tres frequent, maillage riche (relations + recherches liees)',
 *           ],
 *           // ...
 *       ],
 *   ]
 *
 * Règles dures appliquées ICI, en plus de la contrainte CHECK du schéma (robots fermé à deux
 * valeurs) -- un lot qui viole l'une d'entre elles est refusé EN BLOC, aucune ligne n'est
 * écrite (transaction unique) :
 *
 *   R1  route_path doit commencer par '/', family doit être une valeur connue
 *       (App\Seo\Family::ALL), robots doit être 'index,follow' ou 'noindex,follow'.
 *   R2  aucun doublon de route_path DANS le lot.
 *   R3  une ligne 'index,follow' dont canonical_path != route_path est refusée -- ce registre
 *       ne sert jamais de mécanisme d'alias indexable (chaque permutation non canonique est
 *       déjà éliminée par une redirection 301 en amont, WordListFilters::canonicalPath() /
 *       TermPage::$slug / RackPage::$slug -- si canonical_path diverge ici, c'est que la route
 *       n'est PAS le gagnant canonique, donc jamais 'index,follow').
 *   R4  DEUX controles distincts, tous deux geres ici :
 *       R4a (original) une famille de App\Seo\Family::NEVER_SITEMAP (contenant, avec, sans,
 *           motif, listes combinees, jouer/{lettres} -- combinaisons infinies, docs/05 n'en
 *           documente d'ailleurs aucun fragment de sitemap) ne peut JAMAIS recevoir
 *           robots='index,follow' ni sitemap_fragment non nul, quel que soit le lot.
 *       R4b (durci, 2026-08-18, D-030 constat I-4 -- condition posee par l'audit
 *           seo-technical-auditor avant toute proposition du palier 3 de "avec") : jusqu'ici R4
 *           ne validait que le NOM de la famille contre NEVER_SITEMAP, jamais la FORME de
 *           route_path -- une ligne pouvait porter n'importe quel route_path malforme tant que
 *           sa famille declaree n'etait pas dans NEVER_SITEMAP. Desormais, pour chaque famille
 *           COUVERTE par familySeoBatchRouteShapeError() (voir plus bas), route_path doit
 *           correspondre EXACTEMENT a la grammaire de cette famille -- App\Search\
 *           WordListFilters::canonicalPath() est la source de verite de cette grammaire. Une
 *           ligne dont la forme ne correspond pas a sa famille declaree est refusee A
 *           L'ECRITURE, pas seulement documentee en prose. Familles couvertes : home,
 *           word_list_length, word_list_commencant, word_list_terminant, word_list_combined,
 *           word_list_position, word_list_avec_single_letter, word_list_avec_two_letters,
 *           word_list_avec_three_letters -- ces trois dernieres (les paliers de l'ouverture en
 *           entonnoir de "avec") exigent EN PLUS un ordre alphabetique STRICT des lettres "avec"
 *           dans route_path, meme convention que ksort() dans WordListFilters::fromPath() : un
 *           route_path dont les lettres ne sont pas triees ne serait JAMAIS la forme reellement
 *           servie en 200 (WordListFilters::fromPath()->canonicalPath() la redirigerait en 301
 *           ailleurs), donc jamais une ligne 'index,follow' valide. Familles explicitement NON
 *           couvertes par ce durcissement, documentees plutot qu'oubliees : word_admitted /
 *           word_french_not_admitted (plus de 838 000 lignes a elles deux, grammaire du slug
 *           derivee de App\Search\Normalizer plutot que de WordListFilters, hors perimetre de ce
 *           lot precis) et rack (deja bloquee a la racine par R4a, jamais index,follow possible,
 *           une regle de forme supplementaire n'y ajouterait rien) -- a instruire separement si
 *           un futur lot le justifie.
 *       R4c (durci, 2026-08-19, D-033 -- axe 2 de l'ouverture "commencant+terminant+avec") :
 *           couverture etendue a word_list_combined_with_letter (App\Seo\Family::
 *           WORD_LIST_COMBINED_WITH_LETTER, NOUVELLE classification, distincte de
 *           word_list_combined) -- prefixe ET suffixe chacun d'une seule lettre, SANS longueur,
 *           PLUS une lettre "avec" d'occurrence unique, meme discipline R4b que ci-dessus. Pas
 *           d'exigence de tri alphabetique ici (contrairement aux paliers "avec") : commencant,
 *           terminant et avec sont trois ROLES distincts dans cette route, jamais un ensemble de
 *           lettres interchangeables entre elles -- WordListFilters::fromPath() ne trie jamais
 *           ces trois segments les uns par rapport aux autres.
 *           CORRECTIF (2026-08-19, audit seo-technical-auditor consolide sur D-035/D-036,
 *           constat I-1, non bloquant) : la regex acceptait jusqu'ici une lettre "avec" EGALE au
 *           debut ou a la fin -- une telle forme collapse silencieusement en 301 vers la page
 *           parente elle-meme (App\Search\WordListFilters::fromPath(), D-032), elle n'est donc
 *           JAMAIS la forme reellement servie en 200 pour cette URL, exactement le meme
 *           raisonnement que l'ordre alphabetique strict deja impose pour les paliers "avec"
 *           purs (WORD_LIST_AVEC_TWO_LETTERS/THREE_LETTERS ci-dessus). Desormais refusee a
 *           l'ecriture : la lettre "avec" doit etre DIFFERENTE du debut ET de la fin.
 *       R4d (durci, 2026-08-18, dernier des quatre axes commencant/terminant/avec travailles ce
 *           jour) : couverture etendue a word_list_commencant_with_letter (App\Seo\Family::
 *           WORD_LIST_COMMENCANT_WITH_LETTER, NOUVELLE classification, distincte a la fois de
 *           word_list_commencant -- R4b rejette deja explicitement tout segment "/avec/..." apres
 *           le prefixe -- ET de word_list_combined_with_letter ci-dessus, forme de route
 *           syntaxiquement differente, DEUX segments de lettre au lieu de TROIS, aucun terminant)
 *           -- prefixe d'une seule lettre, SANS longueur, SANS terminant, PLUS une lettre "avec"
 *           d'occurrence unique. Pas d'exigence de tri alphabetique (memes raisons que R4c :
 *           commencant et avec sont deux roles distincts, pas un ensemble de lettres
 *           interchangeables).
 *           CORRECTIF (2026-08-19, meme passe et meme raison que R4c ci-dessus) : la lettre
 *           "avec" doit desormais etre DIFFERENTE du prefixe -- une forme "avec" = prefixe
 *           collapse elle aussi en 301 vers la page parente (D-032), jamais servie en 200.
 *       R4e (durci, 2026-08-22, 5e audit consolide, constat I-4) : word_list_position
 *           (App\Seo\Family::WORD_LIST_POSITION, D-023) acceptait jusqu'ici P=1 et P=N (N = la
 *           longueur du MEME route_path) pour toute longueur -- App\Search\WordListFilters::
 *           fromPath() collapse pourtant TOUJOURS silencieusement ces deux positions degenerees
 *           (premiere/derniere lettre) vers commencant/terminant, exactement le meme defaut deja
 *           corrige cote "avec" par R4c/R4d (I-1, D-037) : ces formes ne repondent donc JAMAIS
 *           200, seulement une redirection 301. Desormais rejetees a l'ecriture -- voir le cas
 *           Family::WORD_LIST_POSITION de familySeoBatchRouteShapeError() (backreference PCRE
 *           sur le groupe N deja capture, testee exhaustivement avant application, voir le
 *           rapport AFTER de cette tache).
 *   R5  result_count === 0 avec robots='index,follow' est refusé (page à résultat vide jamais
 *       indexable). result_count === 1 N'EST PAS refusé (docs/05 : jamais sur le seul compteur)
 *       -- seulement compté séparément dans le rapport imprimé par ce script.
 *   R6  une famille de App\Seo\Family::FRENCH_NOT_ADMITTED ne peut pas dépasser
 *       App\Seo\Family::MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED lignes 'index,follow' dans UN SEUL
 *       lot ("Never propose indexing these in bulk"), et chaque ligne de cette famille doit
 *       porter une note non vide (attestation de vérification manuelle -- genuinely French,
 *       absent d'ODS8/ODS9, utile, recherché ou fréquent).
 *   R7  toute ligne 'index,follow' doit porter une note non vide décrivant au moins son
 *       maillage interne prévu -- attestation humaine, PAS une vérification automatique du
 *       graphe de liens (hors de portée de ce script ; relève de l'audit seo-technical-auditor).
 *
 * Sans --force, une ligne dont route_path existe déjà en base avec un batch_id DIFFÉRENT est
 * refusée (protège l'historique d'un lot précédent contre un écrasement accidentel par un
 * lot mal ciblé). --force autorise explicitement le remplacement.
 *
 * --prune (ajouté 2026-08-21, D-040) : ce script ne fait que de l'INSERT OR REPLACE -- un lot
 * régénéré avec MOINS de lignes qu'avant (ex. retrait de doublons de contenu détectés après
 * coup, comme D-037/D-038/D-039/D-040) laisse sinon les anciennes lignes orphelines en base,
 * toujours 'index,follow', toujours dans le sitemap. --prune supprime, DANS LA MÊME
 * TRANSACTION que l'application du lot, toute ligne dont batch_id == $batchId mais dont
 * route_path n'apparaît plus dans les lignes du lot en cours d'application -- jamais une
 * ligne d'un AUTRE batch_id (une correction sur un lot ne doit jamais toucher un autre lot).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/apply_seo_batch.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

// Un gros lot genere par scripts/propose_seo_batch.php (ex. 'avec_three_letters', 28 827 lignes,
// palier 3) epuise le memory_limit CLI par defaut (128 Mo) des le `require $batchPath` ci-dessous
// (compilation d'un litteral PHP de centaines de milliers de lignes) -- meme raison et meme
// remede que scripts/propose_seo_batch.php (voir son ini_set() en tete de fichier pour le detail
// complet) : script CLI hors ligne, jamais expose a public/ ni au runtime (D-007), relever ce
// plafond ici est sans consequence sur le budget de performance runtime.
ini_set('memory_limit', '512M');

require_once dirname(__DIR__) . '/app/Seo/Family.php';

use App\Seo\Family;

/**
 * R4b (durci, D-030 constat I-4) : valide la FORME de route_path pour les familles couvertes,
 * en plus du controle R4a existant (nom de famille contre NEVER_SITEMAP). Renvoie un message
 * d'erreur si la forme ne correspond pas a la grammaire attendue de $family, null si conforme
 * OU si $family n'est pas couverte par ce controle (silencieusement acceptee -- une famille
 * non couverte n'est jamais bloquee faute de regle ecrite pour elle, voir le docblock de
 * fichier ci-dessus pour la liste exacte des familles couvertes et non couvertes).
 *
 * Les trois paliers "avec" (single/two/three letters) exigent en plus un ordre alphabetique
 * STRICT des lettres dans route_path -- meme convention que ksort() dans
 * App\Search\WordListFilters::fromPath() : des lettres non triees produiraient un route_path
 * qui ne serait jamais la forme reellement servie en 200 (redirection 301 vers la forme
 * canonique), donc jamais une ligne 'index,follow' valide.
 */
function familySeoBatchRouteShapeError(string $family, string $routePath): ?string
{
    switch ($family) {
        case Family::HOME:
            return $routePath === '/' ? null : "forme attendue '/' exactement";

        case Family::WORD_LIST_LENGTH:
            return preg_match('#^/mots/\d{1,2}-lettres\z#', $routePath) === 1
                ? null
                : "forme attendue '/mots/{N}-lettres'";

        case Family::WORD_LIST_COMMENCANT:
            return preg_match('#^/mots/commencant/[a-z]{1,15}\z#', $routePath) === 1
                ? null
                : "forme attendue '/mots/commencant/{lettres}'";

        case Family::WORD_LIST_TERMINANT:
            return preg_match('#^/mots/terminant/[a-z]{1,15}\z#', $routePath) === 1
                ? null
                : "forme attendue '/mots/terminant/{lettres}'";

        case Family::WORD_LIST_COMBINED:
            return preg_match('#^/mots/(?:\d{1,2}-lettres/)?commencant/[a-z]{1,15}/terminant/[a-z]{1,15}\z#', $routePath) === 1
                ? null
                : "forme attendue '/mots/[{N}-lettres/]commencant/{X}/terminant/{Y}'";

        case Family::WORD_LIST_POSITION:
            // CORRECTIF (I-4, 5e audit consolide, 2026-08-22) : la regex acceptait jusqu'ici
            // P=1 et P=N (N = la longueur capturee dans le MEME route_path) pour TOUTE longueur
            // -- App\Search\WordListFilters::fromPath() (D-023) collapse pourtant TOUJOURS
            // silencieusement ces deux positions degenerees (premiere/derniere lettre) vers
            // commencant/terminant, donc "/mots/{N}-lettres/position/1/{X}" et
            // "/mots/{N}-lettres/position/{N}/{X}" ne sont JAMAIS la forme reellement servie en
            // 200 (redirection 301 vers la forme collapsee) -- meme defaut deja corrige cote
            // "avec" par R4c/R4d (I-1, D-037). (?!1/) rejette P=1 directement (aucune ambiguite
            // de longueur de chiffres possible : "1" est toujours un seul caractere). (?!\1/)
            // rejette P=N via une BACKREFERENCE PCRE sur le groupe 1 (N) deja capture plus haut
            // dans le meme motif -- teste exhaustivement pour les 124 combinaisons N=2..15 x
            // P=1..N (aucune divergence attendue/observee) ET contre les 2 327 lignes deja
            // appliquees en production (0 rejet) avant ce correctif, voir le rapport AFTER de
            // cette tache. Verifie separement en PHP que $m[2] est bien un entier valide reste
            // inutile ici : le motif lui-meme rejette deja toute forme non conforme.
            return preg_match('#^/mots/(\d{1,2})-lettres/position/(?!1/)(?!\1/)(\d{1,2})/[a-z]\z#', $routePath) === 1
                ? null
                : "forme attendue '/mots/{N}-lettres/position/{P}/{lettre}', P different de 1 et de N (positions degenerees collapsees en 301 vers commencant/terminant, D-023)";

        case Family::WORD_LIST_AVEC_SINGLE_LETTER:
            return preg_match('#^/mots/\d{1,2}-lettres/avec/[a-z]\z#', $routePath) === 1
                ? null
                : "forme attendue '/mots/{N}-lettres/avec/{X}' (une seule lettre)";

        case Family::WORD_LIST_AVEC_TWO_LETTERS:
            if (preg_match('#^/mots/\d{1,2}-lettres/avec/([a-z])/([a-z])\z#', $routePath, $m) !== 1) {
                return "forme attendue '/mots/{N}-lettres/avec/{X}/{Y}' (deux lettres distinctes)";
            }

            if (!($m[1] < $m[2])) {
                return "lettres avec doivent etre triees alphabetiquement (X < Y), recu '{$m[1]}' et '{$m[2]}'";
            }

            return null;

        case Family::WORD_LIST_AVEC_THREE_LETTERS:
            if (preg_match('#^/mots/\d{1,2}-lettres/avec/([a-z])/([a-z])/([a-z])\z#', $routePath, $m) !== 1) {
                return "forme attendue '/mots/{N}-lettres/avec/{X}/{Y}/{Z}' (trois lettres distinctes)";
            }

            if (!($m[1] < $m[2] && $m[2] < $m[3])) {
                return "lettres avec doivent etre triees alphabetiquement (X < Y < Z), recu '{$m[1]}', '{$m[2]}', '{$m[3]}'";
            }

            return null;

        case Family::WORD_LIST_COMBINED_WITH_LETTER:
            // R4c : prefixe ET suffixe chacun d'une seule lettre, SANS longueur, PLUS une
            // lettre "avec" -- pas de contrainte de tri (voir le docblock ci-dessus, trois
            // roles distincts, pas un ensemble de lettres interchangeables).
            if (preg_match('#^/mots/commencant/([a-z])/terminant/([a-z])/avec/([a-z])\z#', $routePath, $m) !== 1) {
                return "forme attendue '/mots/commencant/{X}/terminant/{Y}/avec/{Z}' (une seule lettre chacun, sans longueur)";
            }

            // CORRECTIF (2026-08-19, audit consolide, constat I-1) : une lettre "avec" egale au
            // debut ou a la fin collapse en 301 vers la page parente elle-meme (D-032) -- jamais
            // la forme reellement servie en 200, donc jamais une ligne 'index,follow' valide.
            if ($m[3] === $m[1] || $m[3] === $m[2]) {
                return "lettre avec '{$m[3]}' degeneree (egale au debut '{$m[1]}' ou a la fin '{$m[2]}') -- collapse en 301 vers la page parente (D-032), jamais servie en 200";
            }

            return null;

        case Family::WORD_LIST_COMMENCANT_WITH_LETTER:
            // R4d : prefixe d'une seule lettre, SANS longueur, SANS terminant, PLUS une lettre
            // "avec" -- pas de contrainte de tri (deux roles distincts, meme raisonnement que
            // R4c). Distinct de WORD_LIST_COMMENCANT (R4b rejette deja "/avec/...") et de
            // WORD_LIST_COMBINED_WITH_LETTER (trois segments de lettre, pas deux).
            if (preg_match('#^/mots/commencant/([a-z])/avec/([a-z])\z#', $routePath, $m) !== 1) {
                return "forme attendue '/mots/commencant/{X}/avec/{Y}' (une seule lettre chacune, sans longueur, sans terminant)";
            }

            // CORRECTIF (2026-08-19, meme passe et meme raison que R4c ci-dessus) : une lettre
            // "avec" egale au prefixe collapse elle aussi en 301 vers la page parente (D-032).
            if ($m[2] === $m[1]) {
                return "lettre avec '{$m[2]}' degeneree (egale le prefixe '{$m[1]}') -- collapse en 301 vers la page parente (D-032), jamais servie en 200";
            }

            return null;

        default:
            // Famille non couverte par ce durcissement (word_admitted, word_french_not_admitted,
            // rack, ou toute famille de Family::NEVER_SITEMAP -- deja bloquee par R4a) : aucune
            // regle de forme ecrite ici, jamais bloquee faute d'une regle absente.
            return null;
    }
}

$args = array_slice($argv, 1);
$force = in_array('--force', $args, true);
$prune = in_array('--prune', $args, true);
$args = array_values(array_filter(
    $args,
    static fn (string $a): bool => $a !== '--force' && $a !== '--prune',
));

if (count($args) !== 1) {
    fwrite(STDERR, "usage : php scripts/apply_seo_batch.php path/to/batch.php [--force] [--prune]\n");
    exit(1);
}

$batchPath = $args[0];

if (!is_file($batchPath)) {
    fwrite(STDERR, "fichier de lot introuvable : {$batchPath}\n");
    exit(1);
}

$batch = require $batchPath;

if (!is_array($batch) || !isset($batch['batch_id'], $batch['rows']) || !is_array($batch['rows'])) {
    fwrite(STDERR, "format de lot invalide : attendu ['batch_id' => string, 'rows' => list<array>]\n");
    exit(1);
}

$batchId = (string) $batch['batch_id'];
$addedAt = isset($batch['added_at']) ? (string) $batch['added_at'] : gmdate('Y-m-d');
$rows = $batch['rows'];

if ($rows === []) {
    fwrite(STDERR, "lot vide : aucune ligne a appliquer\n");
    exit(1);
}

// SCRABBLE_SEO_MAX_FRENCH_NOT_ADMITTED : meme reserve aux tests que SCRABBLE_SEO_DB_PATH
// (tests/Seo/BuildScriptsTest.php) -- permet de verifier le mecanisme de refus R6 sans
// generer des centaines de milliers de lignes reelles pour depasser le plafond de
// production (Family::MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED, D-017).
$maxFrenchNotAdmitted = getenv('SCRABBLE_SEO_MAX_FRENCH_NOT_ADMITTED') !== false
    ? (int) getenv('SCRABBLE_SEO_MAX_FRENCH_NOT_ADMITTED')
    : Family::MAX_BATCH_SIZE_FRENCH_NOT_ADMITTED;

$errors = [];
$seenPaths = [];
$normalizedRows = [];
$frenchNotAdmittedIndexCount = 0;

foreach ($rows as $i => $row) {
    if (!is_array($row)) {
        $errors[] = "ligne {$i} : pas un tableau";

        continue;
    }

    $routePath = $row['route_path'] ?? null;
    $family = $row['family'] ?? null;
    $robots = $row['robots'] ?? null;
    $canonicalPath = $row['canonical_path'] ?? $routePath;
    $sitemapFragment = $row['sitemap_fragment'] ?? null;
    $resultCount = array_key_exists('result_count', $row) ? $row['result_count'] : null;
    $notes = $row['notes'] ?? '';

    $label = "ligne {$i} (" . (is_string($routePath) ? $routePath : '?') . ')';

    // R1
    if (!is_string($routePath) || !str_starts_with($routePath, '/')) {
        $errors[] = "{$label} : route_path doit commencer par '/'";

        continue;
    }

    if (!is_string($family) || !Family::isValid($family)) {
        $errors[] = "{$label} : family inconnue ou absente";

        continue;
    }

    // R4b (durci, D-030 constat I-4) : forme de route_path validee des que la famille est
    // connue, avant tout autre controle -- une famille valide avec une forme incoherente reste
    // un lot refuse, quel que soit robots/canonical_path/etc.
    $shapeError = familySeoBatchRouteShapeError($family, $routePath);

    if ($shapeError !== null) {
        $errors[] = "{$label} : {$shapeError} (R4)";

        continue;
    }

    if (!in_array($robots, ['index,follow', 'noindex,follow'], true)) {
        $errors[] = "{$label} : robots doit valoir 'index,follow' ou 'noindex,follow'";

        continue;
    }

    // R2
    if (isset($seenPaths[$routePath])) {
        $errors[] = "{$label} : route_path en double dans ce lot";

        continue;
    }
    $seenPaths[$routePath] = true;

    // R3
    if ($robots === 'index,follow' && $canonicalPath !== $routePath) {
        $errors[] = "{$label} : 'index,follow' avec un canonical_path different de route_path -- alias indexable refuse (R3)";

        continue;
    }

    // R4
    if (Family::forbidsSitemap($family)) {
        if ($robots === 'index,follow') {
            $errors[] = "{$label} : famille {$family} ne peut jamais etre 'index,follow' -- combinaison infinie (R4)";

            continue;
        }

        if ($sitemapFragment !== null) {
            $errors[] = "{$label} : famille {$family} ne peut jamais avoir de sitemap_fragment (R4)";

            continue;
        }
    }

    // R5
    if ($resultCount === 0 && $robots === 'index,follow') {
        $errors[] = "{$label} : result_count = 0 avec 'index,follow' -- page a resultat vide jamais indexable (R5)";

        continue;
    }

    // R7
    if ($robots === 'index,follow' && trim((string) $notes) === '') {
        $errors[] = "{$label} : 'index,follow' sans note de maillage interne -- attestation requise (R7)";

        continue;
    }

    // R6 (comptage, verifie apres la boucle une fois le total connu)
    if ($robots === 'index,follow' && Family::isFrenchNotAdmitted($family)) {
        $frenchNotAdmittedIndexCount++;

        if (trim((string) $notes) === '') {
            $errors[] = "{$label} : forme francaise non admise 'index,follow' sans attestation de verification manuelle (R6)";

            continue;
        }
    }

    $normalizedRows[] = [
        'route_path' => $routePath,
        'family' => $family,
        'robots' => $robots,
        'canonical_path' => (string) $canonicalPath,
        'sitemap_fragment' => $sitemapFragment !== null ? (string) $sitemapFragment : null,
        'result_count' => $resultCount !== null ? (int) $resultCount : null,
        'notes' => (string) $notes,
    ];
}

// R6, plafond global du lot.
if ($frenchNotAdmittedIndexCount > $maxFrenchNotAdmitted) {
    $errors[] = sprintf(
        "lot refuse (R6) : %d lignes 'index,follow' en francais non admis, plafond %d par lot",
        $frenchNotAdmittedIndexCount,
        $maxFrenchNotAdmitted,
    );
}

if ($errors !== []) {
    fwrite(STDERR, "lot refuse, " . count($errors) . " erreur(s) :\n");

    foreach ($errors as $error) {
        fwrite(STDERR, "  - {$error}\n");
    }

    exit(1);
}

$root = dirname(__DIR__);
// SCRABBLE_SEO_DB_PATH : meme reserve aux tests que dans scripts/build_seo_registry.php.
$dbPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_fr.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "registre introuvable, lancer d'abord : php scripts/build_seo_registry.php\n{$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if (!$force) {
    $checkStatement = $pdo->prepare('SELECT batch_id FROM registry WHERE route_path = ?');

    foreach ($normalizedRows as $row) {
        $checkStatement->execute([$row['route_path']]);
        $existing = $checkStatement->fetch();

        if ($existing !== false && $existing['batch_id'] !== $batchId) {
            fwrite(STDERR, sprintf(
                "route_path '%s' existe deja sous le lot '%s' -- utiliser --force pour remplacer\n",
                $row['route_path'],
                $existing['batch_id'] ?? '(aucun)',
            ));
            exit(1);
        }
    }
}

$pdo->beginTransaction();

$prunedCount = 0;

if ($prune) {
    $existingStatement = $pdo->prepare('SELECT route_path FROM registry WHERE batch_id = ?');
    $existingStatement->execute([$batchId]);
    $existingPaths = $existingStatement->fetchAll(PDO::FETCH_COLUMN);

    $keptPaths = array_flip(array_column($normalizedRows, 'route_path'));
    $stalePaths = array_filter($existingPaths, static fn (string $p): bool => !isset($keptPaths[$p]));

    if ($stalePaths !== []) {
        $delete = $pdo->prepare('DELETE FROM registry WHERE batch_id = ? AND route_path = ?');

        foreach ($stalePaths as $stalePath) {
            $delete->execute([$batchId, $stalePath]);
            $prunedCount++;
        }
    }
}

$insert = $pdo->prepare(
    'INSERT OR REPLACE INTO registry '
    . '(route_path, family, robots, canonical_path, sitemap_fragment, batch_id, result_count, notes, added_at) '
    . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

foreach ($normalizedRows as $row) {
    $insert->execute([
        $row['route_path'],
        $row['family'],
        $row['robots'],
        $row['canonical_path'],
        $row['sitemap_fragment'],
        $batchId,
        $row['result_count'],
        $row['notes'],
        $addedAt,
    ]);
}

$pdo->commit();

$totalCount = $pdo->query('SELECT COUNT(*) c FROM registry')->fetch()['c'];
$indexCount = $pdo->query("SELECT COUNT(*) c FROM registry WHERE robots = 'index,follow'")->fetch()['c'];
$singleResultCount = $pdo->query('SELECT COUNT(*) c FROM registry WHERE result_count = 1')->fetch()['c'];

echo "lot '{$batchId}' applique : " . count($normalizedRows) . " ligne(s)\n";

if ($prune) {
    echo "lignes obsoletes retirees (--prune) : {$prunedCount}\n";
}

echo "registre apres application : {$totalCount} lignes au total, {$indexCount} en 'index,follow'\n";
echo "pages a exactement 1 resultat dans le registre (toutes familles) : {$singleResultCount}\n";
