<?php

declare(strict_types=1);

use Tests\Support\Assert;

/**
 * Verifie le gestionnaire global d'erreurs (audit Phase 1, C3) sur le vrai front
 * controller, via un serveur PHP integre reel (PHP_SAPI = 'cli-server', donc hors du
 * garde CLI de app/bootstrap.php) -- pas une reimplementation isolee du handler dans
 * le processus de test (PHP_SAPI = 'cli' pendant tests/run.php, garde volontairement
 * inactive, voir app/bootstrap.php).
 *
 * Panne simulee : SCRABBLE_SITE pointe vers un site sans config/sites/{site}.php,
 * cas "config absente" explicitement demande par l'audit -- App\Config::load() leve
 * une RuntimeException non rattrapee par le routeur. La reponse doit rester neutre :
 * 500, texte simple, jamais de trace ni de chemin absolu du serveur.
 *
 * Note d'implementation (verifie a la main, plusieurs dizaines de minutes de mise au
 * point) : sous cet environnement Windows/git-bash,
 * - proc_open() en forme tableau ('php', '-S', ...) n'aboutit jamais a un port a
 *   l'ecoute ; la forme chaine fonctionne de maniere fiable ;
 * - proc_terminate() suivi de proc_close() se bloque indefiniment ; taskkill par PID
 *   est la sortie propre ;
 * - une boucle d'attente de disponibilite sans timeout de contexte HTTP explicite peut
 *   bloquer plusieurs dizaines de secondes par tentative (default_socket_timeout).
 *   Un delai fixe apres le lancement, puis un nombre borne de tentatives dotees
 *   chacune d'un timeout de contexte court, s'est montre fiable sur des essais repetes.
 *
 * Correction (audit final, 3e passe, code-reviewer, constat I-2) : la forme CHAINE de
 * proc_open() lance `cmd.exe` sous Windows, qui lance a son tour `php -S` comme processus
 * ENFANT -- proc_get_status()['pid'] renvoie le PID de cmd.exe, jamais celui de php.exe.
 * `taskkill /F /PID` sans `/T` ne tuait donc que cmd.exe, laissant le serveur PHP integre
 * tourner indefiniment (verifie : plusieurs serveurs orphelins accumules au fil des
 * executions de cette suite). `/T` (tree) tue tout l'arbre de processus -- insuffisant seul
 * dans certains cas observes (le PID de php.exe se retrouve parfois hors de l'arbre suivi
 * par taskkill, selon la maniere dont cmd.exe le detache). Filet de securite ajoute : le
 * process qui ecoute reellement sur $port est retrouve via `netstat -ano` et tue directement
 * par son propre PID, independamment de toute relation parent/enfant -- verifie via `tasklist`
 * que ce PID est bien un php.exe avant de le tuer (constat M-2, 4e passe) : le port etant
 * tire au hasard, un programme tiers pourrait exceptionnellement l'occuper deja.
 */
return function (): void {
    $root = dirname(__DIR__, 2);
    $port = 8100 + random_int(0, 400);
    $host = '127.0.0.1:' . $port;

    $env = ['SCRABBLE_SITE' => 'ce_site_n_existe_pas'] + (getenv() ?: []);
    $cmd = 'php -S ' . $host . ' -t ' . escapeshellarg($root . '/public');

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = @proc_open($cmd, $descriptors, $pipes, $root, $env);

    Assert::true(is_resource($process), 'echec du lancement du serveur PHP integre pour le test C3');

    $status = proc_get_status($process);
    $pid = $status['pid'];

    try {
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 3]]);

        $body = false;
        $statusLine = '';
        for ($attempt = 0; $attempt < 3 && $body === false; $attempt++) {
            usleep(700_000);
            $body = @file_get_contents('http://' . $host . '/mot/poser', false, $context);
            $statusLine = $http_response_header[0] ?? '';
        }

        Assert::true($body !== false, 'aucune reponse recue du serveur PHP integre pour le test C3');
        Assert::true(str_contains($statusLine, '500'), 'attendu un statut 500, obtenu : ' . $statusLine);

        $bodyText = (string) $body;
        Assert::true(!str_contains($bodyText, $root), 'le chemin absolu du serveur a fuite dans la reponse');
        Assert::true(!str_contains($bodyText, 'RuntimeException'), 'le nom de l\'exception a fuite dans la reponse');
        Assert::true(!str_contains($bodyText, '.php:'), 'une reference de fichier:ligne a fuite dans la reponse');
        Assert::true(!str_contains($bodyText, '#0 '), 'une trace de pile a fuite dans la reponse');
        Assert::true($bodyText !== '', 'la reponse ne doit pas etre vide -- un message neutre est attendu');
    } finally {
        if (is_int($pid)) {
            exec('taskkill /F /T /PID ' . $pid . ' > NUL 2>&1');
        }

        // Filet de securite (voir docblock) : tue directement le process qui ecoute sur
        // $port, quelle que soit sa position dans l'arbre de processus issu de proc_open().
        // Le port est tire au hasard (8100-8500) : si un AUTRE programme local l'occupe deja,
        // php -S ne se lie jamais et ce filet ne doit tuer QUE php.exe, jamais ce programme
        // tiers (audit final, 4e passe, code-reviewer, constat M-2).
        exec('netstat -ano', $netstatLines);
        foreach ($netstatLines as $line) {
            if (preg_match('/127\.0\.0\.1:' . $port . '\s+\S+\s+LISTENING\s+(\d+)/', $line, $matches) === 1) {
                $listenerPid = $matches[1];
                exec('tasklist /FI "PID eq ' . $listenerPid . '" /FI "IMAGENAME eq php.exe" /NH', $tasklistLines);

                if (isset($tasklistLines[0]) && stripos($tasklistLines[0], 'php.exe') !== false) {
                    exec('taskkill /F /PID ' . $listenerPid . ' > NUL 2>&1');
                }
            }
        }

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        proc_close($process);
    }
};
