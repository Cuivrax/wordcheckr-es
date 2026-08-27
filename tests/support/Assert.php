<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Assertions minimales pour tests/run.php. Pas de framework de test ajoute
 * (CLAUDE.md : aucune dependance sans justification dans docs/DECISIONS.md) : chaque
 * echec leve une AssertionError avec un message explicite, attrapee par le runner.
 */
final class Assert
{
    public static function same(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new \AssertionError(sprintf(
                '%sattendu %s, obtenu %s',
                $message === '' ? '' : $message . ' : ',
                var_export($expected, true),
                var_export($actual, true),
            ));
        }
    }

    public static function true(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            throw new \AssertionError($message === '' ? 'condition fausse' : $message);
        }
    }

    public static function null(mixed $value, string $message = ''): void
    {
        self::same(null, $value, $message);
    }

    public static function notNull(mixed $value, string $message = ''): void
    {
        if ($value === null) {
            throw new \AssertionError($message === '' ? 'valeur inattendue null' : $message);
        }
    }
}
