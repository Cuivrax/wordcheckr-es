<?php

declare(strict_types=1);

namespace App;

/**
 * Configuration d'un site (langue + dictionnaire), chargee depuis config/sites/{site}.php.
 *
 * Fichier partage (CLAUDE.md) : cree ici a la demande explicite de la Phase 1 (docs/08),
 * signale pour validation avant edition future par un autre agent.
 *
 * Le code applicatif (app/) reste commun a tous les sites ; seule cette configuration
 * change d'un deploiement a l'autre (docs/02_ARCHITECTURE_DATA_MULTILINGUE.md). Le site
 * actif se choisit via la variable d'environnement SCRABBLE_SITE (reglable au niveau de
 * l'hebergement), "fr" par defaut.
 */
final class Config
{
    /**
     * @param array<int, array{column: string, badge: string}> $lexicons
     * @param array<string, int> $tileScores
     */
    public function __construct(
        public readonly string $language,
        public readonly string $dictionaryPath,
        public readonly string $seoPath,
        public readonly array $lexicons,
        public readonly string $generalLanguageColumn,
        public readonly array $tileScores,
        public readonly int $minTermLength,
        public readonly int $maxTermLength,
        public readonly string $canonicalBaseUrl,
    ) {
    }

    public static function load(string $site): self
    {
        $file = __DIR__ . '/../config/sites/' . $site . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('Configuration de site introuvable : %s', $file));
        }

        /** @var array<string, mixed> $data */
        $data = require $file;

        return new self(
            language: $data['language'],
            dictionaryPath: $data['dictionary_path'],
            seoPath: $data['seo_path'],
            lexicons: $data['lexicons'],
            generalLanguageColumn: $data['general_language_column'],
            tileScores: $data['tile_scores'],
            minTermLength: $data['min_term_length'],
            maxTermLength: $data['max_term_length'],
            canonicalBaseUrl: $data['canonical_base_url'],
        );
    }
}
