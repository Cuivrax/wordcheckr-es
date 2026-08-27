<?php

declare(strict_types=1);

/**
 * Decouvre et execute tests/**\/*Test.php. Pas de framework (CLAUDE.md).
 *
 * Chaque fichier de test retourne une closure sans argument ; le runner l'appelle,
 * capture toute AssertionError ou Throwable, et rapporte succes/echec par fichier.
 *
 * Usage :
 *     php tests/run.php
 */

require __DIR__ . '/../app/bootstrap.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'Tests\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

$root = __DIR__;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

$testFiles = [];
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = $file->getPathname();

    if (str_ends_with($path, 'Test.php')) {
        $testFiles[] = $path;
    }
}
sort($testFiles);

if ($testFiles === []) {
    fwrite(STDERR, "aucun fichier *Test.php trouve sous tests/\n");
    exit(1);
}

$passed = 0;
$failed = 0;
$start = microtime(true);

foreach ($testFiles as $path) {
    $label = substr($path, strlen($root) + 1);

    try {
        /** @var callable $case */
        $case = require $path;

        if (!is_callable($case)) {
            throw new \RuntimeException('ce fichier de test ne retourne pas une closure appelable');
        }

        $case();
        $passed++;
        echo "OK    {$label}\n";
    } catch (\Throwable $e) {
        $failed++;
        echo "ECHEC {$label}\n";
        echo '      ' . $e::class . ' : ' . $e->getMessage() . "\n";
    }
}

$elapsed = microtime(true) - $start;
printf("\n%d reussis, %d echoues, en %.2fs\n", $passed, $failed, $elapsed);

exit($failed === 0 ? 0 : 1);
