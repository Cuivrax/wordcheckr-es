<?php

declare(strict_types=1);

/**
 * Détecteur GÉNÉRIQUE de doublons de contenu entre familles combinatoires du registre SEO
 * (D-041 -- outillage structurel demandé par le constat C-4 du 4e audit consolidé, voir
 * docs/DECISIONS.md D-040 : "aucun garde-fou structurel empêchant une 5e récurrence du même
 * défaut").
 *
 * ## Pourquoi cet outil existe
 *
 * Quatre passes d'audit consécutives (D-037, D-038, D-039, D-040) ont chacune trouvé une
 * NOUVELLE dimension de doublon de contenu jamais contrôlée entre deux familles combinatoires
 * de /mots/... (parent/enfant entre deux familles, sœurs au sein d'une même famille, croisé
 * entre deux familles tranchant le même panier différemment, parent/enfant ET sœurs sur une
 * hiérarchie à trois paliers jamais contrôlée du tout). Chaque correction a été une liste
 * figée écrite à la main, propre à UNE paire de familles précise -- rien n'empêchait une 5e
 * famille de reproduire exactement le même défaut. Cet outil remplace la liste figée par un
 * mécanisme générique : il n'a besoin de connaître à l'avance AUCUNE paire de familles.
 *
 * ## Principe
 *
 * Pour chaque ligne de `registry` appartenant à une famille combinatoire (voir "Périmètre"
 * ci-dessous), reconstruit le VRAI filtre (App\Search\WordListFilters::fromPath(), source de
 * vérité unique de la grammaire /mots/...) à partir de route_path, puis reconstruit la VRAIE
 * clause WHERE que le runtime appliquerait pour cette page (App\Search\WordListSolver, mêmes
 * méthodes privées anchorClause()/extraPredicates()/exactWhereClause()/isExactMode(), invoquées
 * par réflexion -- même technique déjà utilisée et acceptée dans
 * scripts/bench_avec_length_2letters_full_sweep.php et
 * scripts/bench_avec_length_3letters_full_sweep.php pour ne JAMAIS dupliquer la logique de
 * construction SQL à côté du solveur réel). Exécute cette clause SANS LIMIT contre
 * storage/dictionary_fr.sqlite pour obtenir l'ENSEMBLE RÉEL ET COMPLET des mots que la page
 * représente (jamais un simple compte, jamais la vue tronquée à
 * App\Search\WordListSolver::ROW_EXAMINATION_CEILING que le runtime affiche parfois pour les
 * plus gros paniers -- voir "Limite connue" plus bas), calcule une empreinte stable de cet
 * ensemble (COUNT() + sha1 d'un GROUP_CONCAT ordonné par `normalized`, même technique
 * "empreinte SQL GROUP_CONCAT+sha1" déjà validée en D-038), puis regroupe TOUTES les lignes du
 * registre par empreinte identique. Tout groupe de 2+ lignes = doublon de contenu -- qu'il soit
 * parent/enfant, sœurs, ou croisé entre familles inconnues l'une de l'autre : le mécanisme n'a
 * besoin d'aucune connaissance a priori de la paire concernée, contrairement aux quatre
 * corrections précédentes.
 *
 * ## Périmètre
 *
 * Balaie TOUTES les familles de App\Seo\Family::ALL SAUF : HOME, WORD_ADMITTED,
 * WORD_FRENCH_NOT_ADMITTED (838 180 fiches mot individuelles, jamais dupliquées entre elles par
 * construction -- un mot = une fiche, hors du champ "doublon de LISTE" que cet outil couvre) et
 * les membres de App\Seo\Family::NEVER_SITEMAP (contenant/avec/sans/motif/rack -- espace non
 * borné, jamais en index,follow au registre par construction de scripts/apply_seo_batch.php R4a,
 * donc structurellement absents de tout groupe de doublons indexables). Cette exclusion est
 * calculée dynamiquement contre App\Seo\Family::ALL/NEVER_SITEMAP, JAMAIS une liste recopiée à
 * la main ici -- une future famille combinatoire ajoutée à App\Seo\Family (ex. un palier 4 de
 * "avec", un préfixe/suffixe combiné à une position...) entre AUTOMATIQUEMENT dans le périmètre
 * de ce balayage sans modifier ce fichier, exactement le point que le 4e audit a reproché à
 * l'absence de mécanisme global.
 *
 * À l'intérieur de ces familles, seules les lignes `robots = 'index,follow'` entrent dans le
 * balayage (WHERE SQL, CORRECTIF constat I-3, 5e audit consolidé) : une ligne `noindex,follow`
 * n'est, par définition, jamais servie à un robot comme candidate à l'indexation -- l'inclure dans
 * l'élection du "gagnant" d'un groupe de doublons pourrait désigner une page volontairement
 * `noindex,follow` comme survivante et faire retirer à tort une page réellement indexable du même
 * groupe. Avant ce correctif, la requête sélectionnait déjà la colonne `robots` mais ne s'en
 * servait jamais -- sans effet observable au moment du balayage du 2026-08-21 (aucune ligne
 * `noindex,follow` dans les familles combinatoires couvertes à cette date, total registre =
 * total sitemaps), mais un défaut latent dès qu'une ligne `noindex,follow` délibérée entrerait
 * dans une de ces familles. Une ligne `noindex,follow` non balayée ici n'est jamais un doublon de
 * contenu ACTIONNABLE au sens de cet outil : elle n'est déjà pas en lice pour l'indexation, donc
 * ne peut jamais faire perdre une page `index,follow` sœur -- si elle partage son contenu avec
 * N pages `index,follow`, ce sous-groupe reste détecté et tranché normalement entre elles.
 *
 * ## Limite connue
 *
 * Coût hors ligne uniquement (D-007) : un balayage complet peut prendre plusieurs minutes selon
 * le volume du registre (mesuré : voir le rapport AFTER de cette tâche) -- jamais exécuté au
 * runtime, jamais accessible depuis public/. L'empreinte reflète l'ENSEMBLE RÉEL ET COMPLET
 * (sans plafond), pas la vue potentiellement tronquée à ROW_EXAMINATION_CEILING que le runtime
 * sert pour les paniers les plus gros (D-019) -- un choix délibéré, pas un oubli : deux pages
 * dont l'ensemble RÉEL complet est identique produisent nécessairement la même vue tronquée
 * (même ordre `normalized`, même préfixe de 10 000 lignes) -- cette empreinte ne peut donc
 * JAMAIS manquer un doublon réellement affiché à l'utilisateur ou à un robot. La réciproque
 * (deux ensembles réels différents dont les 10 000 premiers mots triés coïncideraient par
 * hasard, alors que la queue diverge) est un cas résiduel théorique, jamais observé sur ce
 * projet -- il exigerait un recoupement massif (>= 10 000 mots communs) entre deux tranches déjà
 * étroitement bornées (longueur, préfixe/suffixe, lettres "avec"), implausible sur les familles
 * réellement ouvertes à ce jour.
 *
 * ## Usage
 *
 *     php scripts/check_combinatorial_duplicates.php [--quiet]
 *
 * --quiet   supprime la progression sur STDERR (le rapport final sur STDOUT est inchangé).
 *
 * Variables d'environnement (réservées aux tests, tests/Seo/ -- même convention que
 * scripts/apply_seo_batch.php/scripts/build_sitemaps.php) :
 *
 *     SCRABBLE_DICTIONARY_DB_PATH   defaut storage/dictionary_fr.sqlite
 *     SCRABBLE_SEO_DB_PATH          defaut storage/seo_fr.sqlite
 *
 * Sortie : rapport texte sur STDOUT (un bloc par groupe de doublons, puis un résumé chiffré).
 * Code de sortie : 0 si aucun groupe de doublons trouvé et aucune anomalie de traçabilité (lot
 * "propre" à ce jour) ; 1 si au moins un groupe de doublons ou une anomalie est trouvé ; 2 en
 * cas d'erreur d'exécution (base introuvable...). N'ÉCRIT JAMAIS dans storage/seo_fr.sqlite ni
 * storage/dictionary_fr.sqlite -- lecture seule sur les deux bases (D-001), aucune correction
 * n'est appliquée par cet outil : il RAPPORTE, un correctif ciblé reste une décision séparée.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/check_combinatorial_duplicates.php ne s'execute qu'en CLI.\n");
    exit(2);
}

require __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use App\Search\WordListFilters;
use App\Search\WordListSolver;
use App\Seo\Family;

$args = array_slice($argv, 1);
$quiet = in_array('--quiet', $args, true);

$root = dirname(__DIR__);
$dictionaryPath = getenv('SCRABBLE_DICTIONARY_DB_PATH') ?: $root . '/storage/dictionary_fr.sqlite';
$seoPath = getenv('SCRABBLE_SEO_DB_PATH') ?: $root . '/storage/seo_fr.sqlite';

if (!is_file($dictionaryPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dictionaryPath}\n");
    exit(2);
}

if (!is_file($seoPath)) {
    fwrite(STDERR, "registre introuvable : {$seoPath}\n");
    exit(2);
}

/**
 * Périmètre calculé dynamiquement (voir docblock ci-dessus) -- jamais une liste recopiée à la
 * main : App\Seo\Family::ALL moins les fiches mot individuelles et les familles à espace non
 * borné (structurellement absentes du registre en index,follow, R4a).
 *
 * @return list<string>
 */
function combinatorialFamilies(): array
{
    $excluded = [Family::HOME, Family::WORD_ADMITTED, Family::WORD_FRENCH_NOT_ADMITTED];
    $excluded = [...$excluded, ...Family::NEVER_SITEMAP];

    return array_values(array_diff(Family::ALL, $excluded));
}

/**
 * Reconstruit EXACTEMENT la clause WHERE (sans LIMIT/OFFSET) que le runtime appliquerait pour
 * cette page -- mêmes méthodes privées que App\Search\WordListSolver::solveExact()/
 * solveBounded(), invoquées par réflexion pour ne jamais dupliquer la logique de construction
 * SQL à côté du solveur réel (même technique que scripts/bench_avec_length_2letters_full_sweep.php
 * et scripts/bench_avec_length_3letters_full_sweep.php).
 *
 * @return array{0: string, 1: list<int|string>}
 */
function fullSetWhereClause(
    ReflectionClass $solverReflection,
    WordListSolver $solver,
    WordListFilters $filters,
): array {
    $isExact = $solverReflection->getMethod('isExactMode')->invoke($solver, $filters);

    if ($isExact) {
        /** @var array{0: string, 1: list<int|string>} */
        return $solverReflection->getMethod('exactWhereClause')->invoke($solver, $filters);
    }

    /** @var array{0: string, 1: list<int|string>, 2: string, 3: string, 4: int} */
    $anchor = $solverReflection->getMethod('anchorClause')->invoke($solver, $filters);
    [$anchorWhere, $anchorParams, , $anchorType] = $anchor;

    /** @var array{0: string, 1: list<int|string>} */
    $extra = $solverReflection->getMethod('extraPredicates')->invoke($solver, $filters, $anchorType);
    [$extraWhere, $extraParams] = $extra;

    $conditions = array_values(array_filter(
        [$anchorWhere, $extraWhere],
        static fn (string $c): bool => $c !== '',
    ));

    return [implode(' AND ', $conditions), [...$anchorParams, ...$extraParams]];
}

/**
 * Empreinte stable de l'ensemble réel et complet de mots que $where/$params désignent --
 * COUNT() + sha1 d'un GROUP_CONCAT ordonné par `normalized` (même technique "empreinte SQL
 * GROUP_CONCAT+sha1" déjà validée en D-038, docs/DECISIONS.md). Statement mis en cache par texte
 * SQL (les lignes d'une même famille partagent presque toujours la même forme de clause, seuls
 * les paramètres liés changent) -- réduit le nombre de PDO::prepare() de ~88 000 à une douzaine
 * de formes distinctes.
 *
 * @param array<string, PDOStatement> $statementCache
 * @param list<int|string> $params
 * @return array{fingerprint: string, wordCount: int}
 */
function fingerprintFullSet(PDO $pdo, array &$statementCache, string $where, array $params): array
{
    $whereSql = $where === '' ? '' : "WHERE {$where}";
    $sql = "SELECT COUNT(*) c, GROUP_CONCAT(normalized) g FROM (SELECT normalized FROM terms {$whereSql} ORDER BY normalized)";

    if (!isset($statementCache[$sql])) {
        $statementCache[$sql] = $pdo->prepare($sql);
    }

    $statement = $statementCache[$sql];
    $statement->execute($params);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    $count = (int) $row['c'];
    $concat = $row['g'] ?? '';

    return ['fingerprint' => $count . ':' . sha1((string) $concat), 'wordCount' => $count];
}

$dictionaryConnection = new Connection($dictionaryPath);
$registryConnection = new Connection($seoPath);

$solver = new WordListSolver($dictionaryConnection);
$solverReflection = new ReflectionClass($solver);

$dictionaryPdo = $dictionaryConnection->pdo();
$registryPdo = $registryConnection->pdo();

$families = combinatorialFamilies();

if ($families === []) {
    fwrite(STDERR, "aucune famille combinatoire a balayer (App\\Seo\\Family::ALL vide ?)\n");
    exit(2);
}

$placeholders = implode(',', array_fill(0, count($families), '?'));
$countStatement = $registryPdo->prepare(
    "SELECT COUNT(*) c FROM registry WHERE family IN ({$placeholders}) AND robots = 'index,follow'"
);
$countStatement->execute($families);
$totalRows = (int) $countStatement->fetch(PDO::FETCH_ASSOC)['c'];

if (!$quiet) {
    fwrite(STDERR, sprintf(
        "familles balayees (%d) : %s\n",
        count($families),
        implode(', ', $families),
    ));
    fwrite(STDERR, sprintf("lignes a balayer : %d\n", $totalRows));
}

// AND robots = 'index,follow' (correctif I-3, 5e audit consolidé) : voir le docblock de fichier,
// section "Périmètre" -- une ligne noindex,follow n'est jamais candidate à l'élection du gagnant.
$rowsStatement = $registryPdo->prepare(
    "SELECT route_path, family, robots, result_count FROM registry"
    . " WHERE family IN ({$placeholders}) AND robots = 'index,follow' ORDER BY family, route_path"
);
$rowsStatement->execute($families);

/** @var array<string, list<array{route_path: string, family: string}>> $groups */
$groups = [];

/** @var array<string, int> $scannedByFamily */
$scannedByFamily = [];

/** @var list<array{route_path: string, family: string, reason: string}> $anomalies */
$anomalies = [];

/** @var array<string, PDOStatement> $statementCache */
$statementCache = [];

$processed = 0;
$start = microtime(true);
$progressEvery = 5000;

foreach ($rowsStatement as $row) {
    $routePath = (string) $row['route_path'];
    $family = (string) $row['family'];
    $robots = (string) $row['robots'];

    $scannedByFamily[$family] = ($scannedByFamily[$family] ?? 0) + 1;

    // Garde-fou (correctif I-3) : la clause WHERE ci-dessus filtre déjà robots = 'index,follow' --
    // ceci ne devrait jamais se produire. Vérifié explicitement plutôt que supposé, pour ne jamais
    // laisser une ligne noindex entrer silencieusement dans l'élection du gagnant si la requête
    // venait à être modifiée un jour sans toucher à cette boucle.
    if ($robots !== 'index,follow') {
        $anomalies[] = [
            'route_path' => $routePath,
            'family' => $family,
            'reason' => "robots = '{$robots}' inclus dans le balayage malgre le filtre SQL (WHERE robots = 'index,follow') -- ne devrait jamais arriver",
        ];
        $processed++;
        continue;
    }

    if (!str_starts_with($routePath, '/mots')) {
        $anomalies[] = ['route_path' => $routePath, 'family' => $family, 'reason' => 'route_path ne commence pas par /mots'];
        $processed++;
        continue;
    }

    $rawPath = substr($routePath, strlen('/mots'));
    $filters = WordListFilters::fromPath($rawPath);

    if ($filters === null) {
        $anomalies[] = ['route_path' => $routePath, 'family' => $family, 'reason' => 'WordListFilters::fromPath() renvoie null (route_path inexploitable)'];
        $processed++;
        continue;
    }

    if ($filters->canonicalUrl() !== $routePath) {
        $anomalies[] = [
            'route_path' => $routePath,
            'family' => $family,
            'reason' => 'canonicalUrl() diverge : ' . $filters->canonicalUrl() . ' (route jamais servie en 200 sous cette forme)',
        ];
        $processed++;
        continue;
    }

    try {
        [$where, $params] = fullSetWhereClause($solverReflection, $solver, $filters);
        $result = fingerprintFullSet($dictionaryPdo, $statementCache, $where, $params);
    } catch (\Throwable $e) {
        $anomalies[] = [
            'route_path' => $routePath,
            'family' => $family,
            'reason' => 'exception pendant le calcul de l\'empreinte : ' . $e::class . ' : ' . $e->getMessage(),
        ];
        $processed++;
        continue;
    }

    if ($result['wordCount'] === 0) {
        $anomalies[] = ['route_path' => $routePath, 'family' => $family, 'reason' => 'ensemble reel vide (0 mot) alors que la ligne est en registre'];
    }

    $groups[$result['fingerprint']][] = ['route_path' => $routePath, 'family' => $family];

    $processed++;

    if (!$quiet && $processed % $progressEvery === 0) {
        $elapsed = microtime(true) - $start;
        fwrite(STDERR, sprintf(
            "%d/%d (%.1f%%) en %.1fs (%.0f lignes/s)\n",
            $processed,
            $totalRows,
            100 * $processed / max(1, $totalRows),
            $elapsed,
            $processed / max(0.001, $elapsed),
        ));
    }
}

$elapsed = microtime(true) - $start;

/** @var list<array{fingerprint: string, wordCount: int, members: list<array{route_path: string, family: string}>}> $duplicateGroups */
$duplicateGroups = [];

foreach ($groups as $fingerprint => $members) {
    if (count($members) < 2) {
        continue;
    }

    $wordCount = (int) explode(':', $fingerprint, 2)[0];
    $duplicateGroups[] = ['fingerprint' => $fingerprint, 'wordCount' => $wordCount, 'members' => $members];
}

// Ordre deterministe et reproductible : par la plus petite route_path de chaque groupe.
usort($duplicateGroups, static function (array $a, array $b): int {
    $minA = min(array_column($a['members'], 'route_path'));
    $minB = min(array_column($b['members'], 'route_path'));

    return $minA <=> $minB;
});

echo "=== Doublons de contenu entre familles combinatoires (D-041) ===\n\n";

foreach ($duplicateGroups as $i => $group) {
    $members = $group['members'];
    usort($members, static fn (array $a, array $b): int => $a['route_path'] <=> $b['route_path']);

    $familiesInGroup = array_values(array_unique(array_column($members, 'family')));
    $kind = count($familiesInGroup) === 1 ? 'SOEURS (meme famille)' : 'CROISE (familles differentes)';

    printf(
        "GROUPE %d -- %d mots partages -- %d pages -- %s\n",
        $i + 1,
        $group['wordCount'],
        count($members),
        $kind,
    );

    foreach ($members as $member) {
        printf("  %s  [%s]\n", $member['route_path'], $member['family']);
    }

    echo "\n";
}

// --- Repartition par combinaison de familles impliquees (paires ou plus). ---
/** @var array<string, int> $byFamilyCombo */
$byFamilyCombo = [];

foreach ($duplicateGroups as $group) {
    $familiesInGroup = array_values(array_unique(array_column($group['members'], 'family')));
    sort($familiesInGroup, SORT_STRING);
    $key = implode(' + ', $familiesInGroup);
    $byFamilyCombo[$key] = ($byFamilyCombo[$key] ?? 0) + 1;
}
ksort($byFamilyCombo, SORT_STRING);

$excessRows = 0;
foreach ($duplicateGroups as $group) {
    $excessRows += count($group['members']) - 1;
}

echo "=== Anomalies de tracabilite (route_path inexploitable, hors calcul de doublon) ===\n\n";

if ($anomalies === []) {
    echo "aucune\n\n";
} else {
    foreach ($anomalies as $anomaly) {
        printf("  %s  [%s]  %s\n", $anomaly['route_path'], $anomaly['family'], $anomaly['reason']);
    }
    echo "\n";
}

echo "=== Resume ===\n\n";
printf("familles balayees          : %d (%s)\n", count($families), implode(', ', $families));
printf("lignes balayees             : %d\n", $processed);

foreach ($scannedByFamily as $family => $count) {
    printf("  %-38s %d\n", $family, $count);
}

printf("empreintes distinctes       : %d\n", count($groups));
printf("groupes de doublons trouves : %d\n", count($duplicateGroups));
printf("lignes en exces (doublons)  : %d\n", $excessRows);
printf("anomalies de tracabilite    : %d\n", count($anomalies));
printf("duree du balayage           : %.1fs (%.0f lignes/s)\n", $elapsed, $processed / max(0.001, $elapsed));

if ($byFamilyCombo !== []) {
    echo "\nrepartition par combinaison de familles :\n";
    foreach ($byFamilyCombo as $combo => $count) {
        printf("  %-70s %d groupe(s)\n", $combo, $count);
    }
}

echo "\n";

if (count($duplicateGroups) === 0 && count($anomalies) === 0) {
    echo "RESULTAT : propre -- aucun doublon de contenu, aucune anomalie.\n";
    exit(0);
}

echo "RESULTAT : doublons de contenu et/ou anomalies trouves -- voir le detail ci-dessus. Cet outil ne corrige rien, un correctif cible reste une decision separee.\n";
exit(1);
