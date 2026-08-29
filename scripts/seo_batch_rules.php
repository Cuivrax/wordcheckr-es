<?php

declare(strict_types=1);

/**
 * Règles dures R1-R7 partagées par TOUT script qui écrit dans `registry`
 * (storage/seo_es.sqlite) -- extraites de scripts/apply_seo_batch.php pour être exécutées par
 * LE MÊME CODE depuis n'importe quel point d'entrée, plutôt que redocumentées en commentaire à
 * chaque nouveau script.
 *
 * Correctif du blocage C-2 (audit seo-technical-auditor consolidé, docs/DECISIONS.md ES-011) :
 * avant ce fichier, scripts/apply_word_admitted_rollout.php codait en dur
 * robots = 'index,follow' pour les 661 221 lignes de la famille word_admitted sans jamais
 * repasser par R1-R7 -- ces règles n'étaient qu'AFFIRMÉES dans un commentaire, jamais vérifiées
 * par le code. scripts/apply_seo_batch.php reste la référence de ces règles (voir son docblock
 * de fichier pour le détail complet de chacune, R1 à R7) ; ce fichier-ci en est l'extraction
 * réutilisable, pas une redéfinition parallèle susceptible de diverger.
 *
 * Chaque fonction ici est PURE (aucun accès base, aucune E/S) -- mêmes garanties que
 * App\Search\WordListFilters::fromPath().
 */

require_once dirname(__DIR__) . '/app/Seo/Family.php';

use App\Seo\Family;

/**
 * R4b : valide la FORME de route_path pour les familles couvertes, en plus du contrôle R4a
 * (nom de famille contre NEVER_SITEMAP, appliqué séparément dans seoValidateBatchRow() ci-
 * dessous). Renvoie un message d'erreur si la forme ne correspond pas à la grammaire attendue
 * de $family, null si conforme OU si $family n'est pas couverte par ce contrôle (silencieusement
 * acceptée -- une famille non couverte n'est jamais bloquée faute de règle écrite pour elle).
 *
 * Familles couvertes à ce stade : home, word_list_length, word_list_commencant,
 * word_list_terminant (ces deux dernières ajoutées docs/DECISIONS.md ES-016, premier palier
 * combinatoire réellement ouvert sur ce dépôt). word_admitted / word_spanish_not_admitted (des
 * centaines de milliers de lignes potentielles, grammaire du slug dérivée de
 * App\Search\Normalizer plutôt que de WordListFilters) et toutes les autres familles
 * combinatoires non encore mesurées restent non couvertes -- à instruire séparément si un futur
 * lot le justifie.
 *
 * word_list_commencant/word_list_terminant : la forme accepte 1 À N lettres (pas seulement 1),
 * cohérent avec App\Search\WordListFilters::readSingleLetterRun() qui n'impose aucune longueur
 * fixe au segment -- seul le PALIER réellement appliqué par ES-016 restreint la profondeur (1
 * lettre pour empiezan-por, 2 pour terminan-en), une décision de LOT, pas une règle de FORME. Un
 * futur lot à une autre profondeur (ex. empiezan-por à 3 lettres, mesuré mais non appliqué par
 * ES-016) reste donc valide pour ce contrôle -- ce n'est pas son rôle de figer la profondeur.
 */
function seoBatchRouteShapeError(string $family, string $routePath): ?string
{
    switch ($family) {
        case Family::HOME:
            return in_array($routePath, ['/', '/palabras'], true)
                ? null
                : "forme attendue '/' ou '/palabras' exactement";

        case Family::WORD_LIST_LENGTH:
            return preg_match('#^/palabras/\d{1,2}-letras\z#', $routePath) === 1
                ? null
                : "forme attendue '/palabras/{N}-letras'";

        case Family::WORD_LIST_COMMENCANT:
            return preg_match('#^/palabras/empiezan-por/[a-zñ]+\z#u', $routePath) === 1
                ? null
                : "forme attendue '/palabras/empiezan-por/{lettres}'";

        case Family::WORD_LIST_TERMINANT:
            return preg_match('#^/palabras/terminan-en/[a-zñ]+\z#u', $routePath) === 1
                ? null
                : "forme attendue '/palabras/terminan-en/{lettres}'";

        default:
            return null;
    }
}

/**
 * Valide UNE ligne de lot contre R1, R3, R4 (R4a + R4b), R5, R7, et la partie PAR LIGNE de R6
 * (attestation non vide) -- R2 (doublon DANS le lot) est vérifié ici via $seenPaths, fourni par
 * référence par l'appelant afin de fonctionner aussi bien sur un tableau chargé en mémoire
 * (scripts/apply_seo_batch.php) que sur un flux ligne par ligne d'un curseur PDO
 * (scripts/apply_word_admitted_rollout.php, où charger 661 221 lignes en tableau épuiserait la
 * mémoire CLI par défaut). Le plafond GLOBAL de R6 (nombre total de lignes 'index,follow' en
 * espagnol non admis sur l'ENSEMBLE du lot) reste à la charge de l'appelant, une fois toutes les
 * lignes traitées -- $spanishNotAdmittedIndexCount est incrémenté ici par référence pour cet
 * usage, jamais comparé au plafond ici (l'appelant connaît seul le plafond applicable : la
 * production utilise Family::MAX_BATCH_SIZE_SPANISH_NOT_ADMITTED, les tests le réduisent via
 * SCRABBLE_SEO_MAX_SPANISH_NOT_ADMITTED, voir tests/Seo/BuildScriptsTest.php).
 *
 * Renvoie soit un message d'erreur (chaîne), soit la ligne normalisée (tableau) prête à être
 * insérée -- jamais les deux à la fois.
 *
 * @param array<string, mixed> $row
 * @param array<string, true> $seenPaths modifié par effet de bord (route_path déjà vus)
 * @return array{0: string|null, 1: array<string, mixed>|null}
 */
function seoValidateBatchRow(
    array $row,
    string $label,
    array &$seenPaths,
    int &$spanishNotAdmittedIndexCount,
): array {
    $routePath = $row['route_path'] ?? null;
    $family = $row['family'] ?? null;
    $robots = $row['robots'] ?? null;
    $canonicalPath = $row['canonical_path'] ?? $routePath;
    $sitemapFragment = $row['sitemap_fragment'] ?? null;
    $resultCount = array_key_exists('result_count', $row) ? $row['result_count'] : null;
    $notes = $row['notes'] ?? '';

    // R1
    if (!is_string($routePath) || !str_starts_with($routePath, '/')) {
        return ["{$label} : route_path doit commencer par '/'", null];
    }

    if (!is_string($family) || !Family::isValid($family)) {
        return ["{$label} : family inconnue ou absente", null];
    }

    // R4b : forme de route_path validée dès que la famille est connue, avant tout autre
    // contrôle -- une famille valide avec une forme incohérente reste un lot refusé, quel que
    // soit robots/canonical_path/etc.
    $shapeError = seoBatchRouteShapeError($family, $routePath);

    if ($shapeError !== null) {
        return ["{$label} : {$shapeError} (R4)", null];
    }

    if (!in_array($robots, ['index,follow', 'noindex,follow'], true)) {
        return ["{$label} : robots doit valoir 'index,follow' ou 'noindex,follow'", null];
    }

    // R2
    if (isset($seenPaths[$routePath])) {
        return ["{$label} : route_path en double dans ce lot", null];
    }
    $seenPaths[$routePath] = true;

    // R3
    if ($robots === 'index,follow' && $canonicalPath !== $routePath) {
        return [
            "{$label} : 'index,follow' avec un canonical_path different de route_path -- alias indexable refuse (R3)",
            null,
        ];
    }

    // R4a
    if (Family::forbidsSitemap($family)) {
        if ($robots === 'index,follow') {
            return [
                "{$label} : famille {$family} ne peut jamais etre 'index,follow' -- combinaison infinie (R4)",
                null,
            ];
        }

        if ($sitemapFragment !== null) {
            return ["{$label} : famille {$family} ne peut jamais avoir de sitemap_fragment (R4)", null];
        }
    }

    // R5
    if ($resultCount === 0 && $robots === 'index,follow') {
        return [
            "{$label} : result_count = 0 avec 'index,follow' -- page a resultat vide jamais indexable (R5)",
            null,
        ];
    }

    // R7
    if ($robots === 'index,follow' && trim((string) $notes) === '') {
        return ["{$label} : 'index,follow' sans note de maillage interne -- attestation requise (R7)", null];
    }

    // R6 (partie par ligne -- attestation non vide ; le plafond global reste a la charge de
    // l'appelant, voir docblock de fonction).
    if ($robots === 'index,follow' && Family::isSpanishNotAdmitted($family)) {
        $spanishNotAdmittedIndexCount++;

        if (trim((string) $notes) === '') {
            return [
                "{$label} : forme espagnole non admise 'index,follow' sans attestation de verification manuelle (R6)",
                null,
            ];
        }
    }

    return [null, [
        'route_path' => $routePath,
        'family' => $family,
        'robots' => $robots,
        'canonical_path' => (string) $canonicalPath,
        'sitemap_fragment' => $sitemapFragment !== null ? (string) $sitemapFragment : null,
        'result_count' => $resultCount !== null ? (int) $resultCount : null,
        'notes' => (string) $notes,
    ]];
}
