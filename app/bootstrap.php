<?php

declare(strict_types=1);

/**
 * Autoload minimal, sans Composer (CLAUDE.md : aucune dependance sans justification).
 *
 * App\Foo\Bar       -> app/Foo/Bar.php
 * Tests\Foo\BarTest -> tests/Foo/BarTest.php (enregistre separement par tests/run.php)
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

/**
 * Gestion globale des erreurs (audit Phase 1, C3).
 *
 * En dehors du CLI -- donc pour toute requete HTTP servie par public/index.php, que ce
 * soit via le serveur integre de PHP (PHP_SAPI === 'cli-server') ou un vrai
 * hebergement -- toute exception non rattrapee ou erreur PHP (base absente, config
 * absente, erreur PDO, avertissement de type) est journalisee puis convertie en
 * reponse 500 neutre : jamais de trace, jamais de chemin absolu du serveur dans le
 * corps envoye au client. display_errors est traite comme desactive en production.
 *
 * Sous CLI pur (tests/run.php, tests/bench_queries.php, scripts/*.py n'existent pas
 * en PHP), rien n'est installe : les tests attrapent eux-memes les Throwable via
 * tests/support/Assert.php et tests/run.php, et gardent besoin de la visibilite
 * complete des erreurs pour le diagnostic.
 */
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);

    $renderSafeError = static function (): void {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "Une erreur est survenue. Merci de reessayer.\n";
    };

    set_exception_handler(static function (\Throwable $exception) use ($renderSafeError): void {
        error_log(sprintf(
            '[scrabble-light] exception non rattrapee : %s dans %s:%d -- %s',
            $exception::class,
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage(),
        ));
        $renderSafeError();
    });

    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        // Respecte @ (suppression explicite) et le niveau courant de error_reporting().
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Convertit en exception : remonte au gestionnaire ci-dessus, qui journalise
        // et rend la page neutre. Empeche tout avertissement PHP (ex. "Undefined
        // array key") de s'imprimer tel quel dans une reponse HTTP partielle.
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    register_shutdown_function(static function () use ($renderSafeError): void {
        $error = error_get_last();
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

        if ($error !== null && in_array($error['type'], $fatalTypes, true)) {
            error_log(sprintf(
                '[scrabble-light] erreur fatale : %d dans %s:%d -- %s',
                $error['type'],
                $error['file'],
                $error['line'],
                $error['message'],
            ));
            $renderSafeError();
        }
    });
}
