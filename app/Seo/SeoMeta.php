<?php

declare(strict_types=1);

namespace App\Seo;

/**
 * Métadonnées SEO résolues pour une route, consommées directement par la couche de rendu
 * (app/View/, hors périmètre de l'agent seo-registry) — déjà branchées par public/index.php
 * ($render() de ce dépôt appelle App\Seo\Registry::resolve() et expose 'seo' aux vues).
 *
 * $canonicalUrl est déjà une URL ABSOLUE (domaine inclus), prête à imprimer telle quelle dans
 * <link rel="canonical" href="...">. Le calcul du domaine (Config::$canonicalBaseUrl,
 * config/sites/es.php : https://www.wordcheckr.es) reste dans Registry, jamais dans la vue.
 *
 * $canonicalUrl peut être null (ex. page 404 : aucune URL canonique n'a de sens pour une
 * route qui n'existe pas) — la vue omet alors la balise <link rel="canonical"> plutôt que
 * d'en émettre une vide ou trompeuse.
 */
final class SeoMeta
{
    public function __construct(
        public readonly string $robots,
        public readonly ?string $canonicalUrl,
        public readonly bool $inSitemap,
    ) {
    }

    /**
     * Comportement par défaut d'une route absente du registre (D-005) : noindex,follow,
     * jamais dans un sitemap. $canonicalUrl reste renseigné (self-canonical) même en
     * noindex — pratique standard, n'affaiblit rien et évite un aller-retour si la route
     * est ouverte plus tard sans que la vue ait à changer sa structure HTML.
     */
    public static function noindex(?string $canonicalUrl): self
    {
        return new self('noindex,follow', $canonicalUrl, false);
    }
}
