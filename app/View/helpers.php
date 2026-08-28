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
        // ENT_SUBSTITUTE (audit round 2, C-1/D-DE-011) : sans ce flag, htmlspecialchars()
        // renvoie une chaine VIDE (pas U+FFFD) sur une sequence UTF-8 invalide -- un filet
        // de securite generique en plus des vrais correctifs mb_* en amont.
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
