<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\SenseLookup;
use Tests\Support\Assert;

/**
 * Exerce App\Search\SenseLookup sur la vraie base storage/dictionary_fr.sqlite (lecture seule)
 * -- D-043, rollout complet des 403 060 mots admis (ODS8/ODS9). Ce test verifie le cas
 * couvert (un homographe a 2 sens, un mot a 1 sens, y compris un mot courant hors de l'ancien
 * lot pilote de 99 mots) et le cas non couvert (terme absent de la base, absence de donnee
 * attendue -- pas une erreur, meme convention que $pos/$gender D-018).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $lookup = new SenseLookup($connection);

    // AA : 1 sens, source Kartmaan (reformulation LLM ancree, jamais la glose source copiee
    // telle quelle -- garde-fou anti-copie applique a la generation).
    $aa = $lookup->find('AA');
    Assert::same(1, $aa->queryCount);
    Assert::same(1, count($aa->senses), 'AA doit avoir exactement 1 sens genere');
    Assert::same('N', $aa->senses[0]['pos']);
    Assert::same('m', $aa->senses[0]['gender']);
    Assert::same('kartmaan', $aa->senses[0]['source']);
    Assert::true($aa->senses[0]['definition'] !== '', 'AA doit avoir une definition non vide');

    // STEPPES : homographe reel nom/verbe (pluriel de STEPPE, ET forme conjuguee de STEPPER)
    // -- les deux sens viennent du palier gratuit (gabarit grammatical detecte dans une glose
    // de reference, jamais un appel LLM), source 'template' pour les deux, dans l'ordre
    // sense_rank croissant.
    $steppes = $lookup->find('STEPPES');
    Assert::same(2, count($steppes->senses), 'STEPPES doit avoir exactement 2 sens (homographe N/V)');
    Assert::same('N', $steppes->senses[0]['pos']);
    Assert::same('template', $steppes->senses[0]['source']);
    Assert::same('V', $steppes->senses[1]['pos']);
    Assert::same('template', $steppes->senses[1]['source']);
    Assert::true(
        $steppes->senses[0]['definition'] !== $steppes->senses[1]['definition'],
        'STEPPES : les deux sens doivent avoir des definitions distinctes',
    );

    // SHABIEN : 2 sens ancres sur kaikki.org (palier 2, mot absent de Kartmaan) -- gender
    // epicene ('e') autorise sur un nom au meme titre que m/f (D-018, meme jeu de valeurs).
    $shabien = $lookup->find('SHABIEN');
    Assert::same(2, count($shabien->senses), 'SHABIEN doit avoir exactement 2 sens');
    foreach ($shabien->senses as $sense) {
        Assert::same('kaikki', $sense['source']);
    }

    // POSER : mot courant, couvert par le rollout complet D-043 (autrefois hors du lot
    // pilote de 99 mots -- desormais dans les 403 060 mots admis, ancre sur Kartmaan).
    $poser = $lookup->find('POSER');
    Assert::same(1, $poser->queryCount);
    Assert::same(1, count($poser->senses), 'POSER doit avoir au moins 1 sens genere (rollout complet)');
    Assert::same('V', $poser->senses[0]['pos']);
    Assert::true($poser->senses[0]['definition'] !== '', 'POSER doit avoir une definition non vide');

    // Terme absent de la base : defensif, meme convention que ConjugationLookupTest --
    // le routeur ne devrait pas appeler find() pour un terme non trouve, mais find() ne doit
    // rien casser si c'est fait quand meme.
    $unknown = $lookup->find('ZZZQQQXXX');
    Assert::same([], $unknown->senses);
};
