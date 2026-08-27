<?php

declare(strict_types=1);

namespace App\Seo;

use App\Database\Connection;

/**
 * Registre SEO (Phase 6, docs/08) — unique source de vérité pour index/noindex et canonical
 * (docs/05_URL_SEO_INDEXATION.md). Lit storage/seo_fr.sqlite, base distincte du dictionnaire
 * (D-003), en lecture seule stricte au runtime (D-001), via la même classe App\Database\
 * Connection déjà utilisée pour storage/dictionary_fr.sqlite — aucune écriture au runtime,
 * quelle que soit la base ouverte.
 *
 * Contrat central (D-005, docs/05) : une route absente du registre reste noindex,follow.
 * resolve() applique ce comportement par défaut dans DEUX cas distincts, jamais une erreur :
 * - storage/seo_fr.sqlite n'existe pas encore (déploiement avant le premier build du
 *   registre, ou environnement de développement qui n'a pas encore lancé
 *   scripts/build_seo_registry.php) ;
 * - le fichier existe mais route_path n'a aucune ligne correspondante.
 *
 * Ne dépend PAS de App\Config : le chemin de la base et le domaine canonique sont passés
 * explicitement au constructeur. Ce découplage garde app/Seo/ testable sans configuration de
 * site, et évite d'imposer à cet agent une modification de app/Config.php (fichier partagé,
 * CLAUDE.md) pour ajouter un champ dont la forme finale appartient à la session principale.
 * Contrat d'appel proposé pour public/index.php (rapport AFTER) :
 *
 *   new App\Seo\Registry($config->seoPath, $config->canonicalBaseUrl)
 *
 * $config->canonicalBaseUrl n'existe pas encore sur App\Config — diff proposé, pas appliqué
 * ici (fichier partagé).
 */
final class Registry
{
    private ?Connection $connection = null;

    private readonly bool $available;

    public function __construct(
        private readonly string $seoDatabasePath,
        private readonly string $canonicalBaseUrl,
    ) {
        $this->available = is_file($this->seoDatabasePath);
    }

    /**
     * $canonicalPath doit déjà être la forme canonique exacte servie pour cette requête (ex.
     * '/mot/' . $page->slug, ou $canonical déjà calculé pour /mots/...) — jamais le chemin brut
     * reçu avant redirection 301 : une route non canonique n'est de toute façon jamais rendue
     * (le routeur redirige avant d'appeler render()), donc resolve() ne voit jamais cette
     * forme-là en pratique.
     */
    public function resolve(string $canonicalPath): SeoMeta
    {
        if (!$this->available) {
            return SeoMeta::noindex($this->absoluteUrl($canonicalPath));
        }

        $statement = $this->connection()->pdo()->prepare(
            'SELECT robots, canonical_path, sitemap_fragment FROM registry WHERE route_path = ? LIMIT 1'
        );
        $statement->execute([$canonicalPath]);
        $row = $statement->fetch();

        if ($row === false) {
            return SeoMeta::noindex($this->absoluteUrl($canonicalPath));
        }

        return new SeoMeta(
            robots: $row['robots'],
            canonicalUrl: $this->absoluteUrl($row['canonical_path']),
            inSitemap: $row['sitemap_fragment'] !== null,
        );
    }

    /**
     * Résout un lot de chemins en une seule requête indexée (route_path est la clé primaire),
     * utile pour un futur outil de rapport/audit qui doit vérifier N routes d'un coup plutôt
     * qu'une par une — non utilisé par le rendu d'une seule page (resolve() suffit), fourni
     * pour scripts/build_sitemaps.php et les tests.
     *
     * @param list<string> $canonicalPaths
     * @return array<string, SeoMeta> indexé par route_path ; toute clé absente du tableau
     *         retourné doit être traitée comme noindex,follow par l'appelant (même contrat que
     *         resolve() pour une clé individuelle).
     */
    public function resolveMany(array $canonicalPaths): array
    {
        if ($canonicalPaths === [] || !$this->available) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($canonicalPaths), '?'));
        $statement = $this->connection()->pdo()->prepare(
            "SELECT route_path, robots, canonical_path, sitemap_fragment FROM registry WHERE route_path IN ($placeholders)"
        );
        $statement->execute($canonicalPaths);

        $result = [];

        foreach ($statement->fetchAll() as $row) {
            $result[$row['route_path']] = new SeoMeta(
                robots: $row['robots'],
                canonicalUrl: $this->absoluteUrl($row['canonical_path']),
                inSitemap: $row['sitemap_fragment'] !== null,
            );
        }

        return $result;
    }

    private function connection(): Connection
    {
        return $this->connection ??= new Connection($this->seoDatabasePath);
    }

    private function absoluteUrl(string $path): string
    {
        return rtrim($this->canonicalBaseUrl, '/') . $path;
    }
}
