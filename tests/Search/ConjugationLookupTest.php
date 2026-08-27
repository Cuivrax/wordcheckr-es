<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\ConjugationLookup;
use Tests\Support\Assert;

/**
 * Exerce App\Search\ConjugationLookup sur la vraie base storage/dictionary_fr.sqlite (lecture
 * seule) -- D-018. Couvre les cas nommes par le rapport BEFORE (POSER, TABLE, POSERA) et le
 * test d'acceptation anti-regression explicitement demande sur les cinq mots identifies
 * pendant l'analyse (SUIS, SOMMES, SONT, VAIS, VONT).
 *
 * Nuance verifiee et documentee (rapport AFTER, section "anti-regression") : sur les cinq
 * paires nommees au depart (SUIVRE/SOMMER/SONORISER/VAIRONNER/VOTER), quatre sont desormais
 * ABSENTES de verb_forms (SUIS, SONT, VAIS, VONT -- aucun appariement du tout, verifie
 * ci-dessous). La cinquieme, SOMMES -> SOMMER, RESTE presente mais s'est revelee etre une
 * conjugaison reguliere mecaniquement correcte et non une erreur : "sommer" (verbe -ER
 * regulier, "sommer quelqu'un de faire quelque chose") a bien pour 2e personne du singulier
 * du present "tu sommes" -- SOMM (radical) + ES (terminaison -ER reguliere) = SOMMES,
 * exactement comme POSE = POS + E ou CHANTES = CHANT + ES. Ce test verifie donc que, si
 * SOMMES pointe vers SOMMER, ce n'est QUE sous cette forme precise (present, 2s) -- jamais
 * sous la forme incorrecte (present, 1p, qui aurait ete l'attribution erronee de "nous
 * sommes" d'ETRE) que la validation morphologique de scripts/import_fr.py
 * (expected_regular_er_form()) a explicitement fermee.
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $connection = new Connection($dbPath);
    $lookup = new ConjugationLookup($connection);

    // POSER (lemme) : selection representative attendue -- present/futur/imparfait (6
    // personnes chacun) + participe present + participe passe, jamais le paradigme complet.
    $poser = $lookup->find('POSER');
    Assert::same(1, $poser->queryCount);
    Assert::same([], $poser->asForm, 'POSER est un infinitif, jamais lui-meme une forme conjuguee');
    Assert::true(count($poser->asLemma) > 0, 'POSER doit avoir des formes conjuguees');
    Assert::true(count($poser->asLemma) <= 20, 'au plus 20 formes/lemme (D-018, pas le paradigme complet)');

    $poserForms = array_column($poser->asLemma, 'form');
    foreach (['POSERA', 'POSERAI', 'POSE', 'POSANT', 'POSENT'] as $expectedForm) {
        Assert::true(in_array($expectedForm, $poserForms, true), $expectedForm . ' devrait etre une forme conjuguee de POSER');
    }
    // Aucun temps hors de la selection representative (present/futur/imparfait/participes).
    $allowedTenses = ['present', 'future', 'imperfect', 'participle_present', 'participle_past'];
    foreach ($poser->asLemma as $entry) {
        Assert::true(in_array($entry['tense'], $allowedTenses, true), 'temps hors selection D-018 : ' . $entry['tense']);
    }

    // POSERA (forme) : doit renvoyer vers POSER, futur, 3e personne du singulier -- exemple
    // nomme explicitement par la tache de depart (CLAUDE.md).
    $posera = $lookup->find('POSERA');
    Assert::same(1, $posera->queryCount);
    Assert::same(1, count($posera->asForm), 'POSERA doit pointer vers exactement un lemme');
    Assert::same('POSER', $posera->asForm[0]['lemma']);
    Assert::same('poser', $posera->asForm[0]['slug']);
    Assert::same('future', $posera->asForm[0]['tense']);
    Assert::same('3s', $posera->asForm[0]['person']);

    // TABLE : homographe nom/verbe reel (docs/DECISIONS.md D-018). TABLE n'est pas
    // elle-meme un infinitif connu (asLemma vide), mais EST une forme conjuguee de TABLER.
    $table = $lookup->find('TABLE');
    Assert::same([], $table->asLemma, 'TABLE n\'est pas un infinitif, aucune forme ne doit lui etre attachee comme lemme');
    Assert::true(count($table->asForm) > 0, 'TABLE devrait etre une forme conjuguee de TABLER');
    foreach ($table->asForm as $entry) {
        Assert::same('TABLER', $entry['lemma'], 'toute occurrence de TABLE comme forme doit pointer vers TABLER');
    }

    // ETRE : verbe suppletif detecte automatiquement comme non fiable a la construction
    // (own_count = 0, sous VERB_LEMMA_MIN_RELIABLE_FORMS = 20) -- exclu entierement plutot
    // que de risquer un lien de conjugaison faux (decision explicitement validee, D-018).
    $etre = $lookup->find('ETRE');
    Assert::same([], $etre->asLemma, 'ETRE doit etre exclu comme lemme non fiable (verbe suppletif)');

    // Anti-regression explicite (test d'acceptation demande) : SUIS, SONT, VAIS, VONT ne
    // doivent JAMAIS apparaitre comme forme conjuguee de SUIVRE, SONORISER, VAIRONNER,
    // VOTER/VOMIR -- verifie desormais completement absentes de verb_forms (pas seulement
    // absentes des mauvais lemmes precis, absentes tout court : aucun appariement fiable
    // n'a ete trouve du tout pour ces quatre formes).
    foreach (['SUIS', 'SONT', 'VAIS', 'VONT'] as $word) {
        $result = $lookup->find($word);
        Assert::same([], $result->asForm, $word . ' ne doit apparaitre comme forme conjuguee d\'aucun lemme');
    }

    // SOMMES : cas limite documente en tete de fichier -- la seule attribution restante
    // (SOMMER, present, 2s) est une conjugaison reguliere mecaniquement correcte, pas une
    // erreur. La regression concrete a fermer est la forme INCORRECTE (present, 1p, qui
    // aurait signifie a tort "nous sommes" comme forme de sommer).
    $sommes = $lookup->find('SOMMES');
    foreach ($sommes->asForm as $entry) {
        Assert::same('SOMMER', $entry['lemma']);
        Assert::same('present', $entry['tense']);
        Assert::same('2s', $entry['person'], 'SOMMES ne doit jamais etre attribue a SOMMER en 1re personne du pluriel (aurait ete "sommons")');
    }

    // Terme absent de la base : aucune requete supplementaire n'a de sens, mais find() doit
    // rester defensif si jamais invoque (le routeur ne devrait pas l'appeler pour un terme
    // non trouve -- verifie ici que rien ne casse si c'est fait quand meme).
    $unknown = $lookup->find('ZZZQQQXXX');
    Assert::same([], $unknown->asLemma);
    Assert::same([], $unknown->asForm);
};
