<?php

declare(strict_types=1);

/**
 * Aides d'echappement pour les vues (app/View/). Pas de moteur de gabarit ajoute
 * (CLAUDE.md : aucune dependance sans decision dans docs/DECISIONS.md) -- PHP simple,
 * avec un echappement systematique des valeurs issues des donnees.
 */

if (!function_exists('e')) {
    function e(string|int $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
