<?php

declare(strict_types=1);

use App\Search\Conjugation;
use App\Search\TermPage;
use App\Search\TermRelations;
use App\Search\WordSenses;
use Tests\Support\Assert;

/**
 * Rend app/View/word.php avec des fiches synthetiques (les trois statuts, et les
 * bornes de longueur 2 et 15, D-010) sans serveur HTTP ni base de donnees --
 * verifie des invariants structurels sur le HTML produit :
 * - la somme des valeurs de tuiles egale toujours le score affiche (docs/04) ;
 * - une tuile par lettre, dans les deux extremes de longueur ;
 * - aucune mention de source de donnees (D-015 : ni Kartmaan, ni hbenbel, ni ODS
 *   publie par un tiers nomme, ni URL de depot) ;
 * - le formulaire de verification reste un GET natif vers /verifier (fonctionne
 *   sans JavaScript) ;
 * - Phase 4 (relations) : $relations = null (francais non admis / inconnu) ne rend
 *   AUCUNE section relations ; $relations non null rend uniquement les categories
 *   non vides, surligne <mark> la partie conservee/modifiee la ou attendu, groupe
 *   anagramsPlusOne par lettre ajoutee, et n'affiche "Voir les N mots ->" que
 *   lorsque le total depasse les elements deja affiches.
 * - D-018 (nature grammaticale, genre, conjugaison) : pas de ligne pos/genre si
 *   $page->pos est null, pas de section conjugaison si $conjugation->asLemma ET
 *   $conjugation->asForm sont vides. Les fixtures POSER/POSERA/TABLE/ETRE reprennent
 *   des valeurs reelement observees sur storage/dictionary_fr.sqlite (verifie
 *   manuellement pendant l'implementation) plutot que des valeurs inventees.
 *   $relations est deliberement laisse a null sur ces fixtures cibles D-018 meme
 *   quand status = admitted : seul le rendu pos/genre/conjugaison est sous test ici,
 *   pas l'invariant routeur "relations non nul ssi admis" (couvert ailleurs, hors
 *   perimetre de ce fichier de vue).
 */
return function (): void {
    require __DIR__ . '/../../app/bootstrap.php';

    $render = static function (TermPage $page, ?TermRelations $relations, Conjugation $conjugation, WordSenses $senses): string {
        $seo = \App\Seo\SeoMeta::noindex('https://exemple.fr/mot/' . $page->slug);

        ob_start();
        (static function (TermPage $page, ?TermRelations $relations, Conjugation $conjugation, WordSenses $senses, \App\Seo\SeoMeta $seo): void {
            require __DIR__ . '/../../app/View/word.php';
        })($page, $relations, $conjugation, $senses, $seo);

        return (string) ob_get_clean();
    };

    // Aucun sens genere (D-0XX, pilote partiel) : reutilise pour toutes les fixtures --
    // ce fichier couvre D-018 (pos/genre/conjugaison), pas le rendu des cartes de
    // definition (couvert par un test dedie a app/Search/SenseLookup).
    $noSenses = new WordSenses(senses: [], queryCount: 0);

    // Conjugaison vide (mot ni verbe ni forme conjuguee -- l'immense majorite des mots) :
    // reutilisee pour toutes les fixtures qui ne portent pas sur D-018 specifiquement.
    $noConjugation = new Conjugation(asLemma: [], asForm: [], queryCount: 0);

    $tiles = static function (string $normalized, array $tileScores): array {
        $letters = [];
        foreach (str_split($normalized) as $letter) {
            $letters[] = ['letter' => $letter, 'value' => $tileScores[$letter]];
        }

        return $letters;
    };

    /** Item de relation synthetique minimal (contrat TermRelations). */
    $item = static function (string $normalized, array $extra = []): array {
        return array_merge([
            'normalized' => $normalized,
            'slug' => strtolower($normalized),
            'score' => 1,
            'length' => strlen($normalized),
            'isOds8' => true,
            'isOds9' => true,
        ], $extra);
    };

    $tileScores = require __DIR__ . '/../../config/sites/fr.php';
    $tileScores = $tileScores['tile_scores'];

    // -------------------------------------------------------------------
    // AA -- deux lettres (borne basse, D-010). Plusieurs categories structurellement
    // vides (anagrams, removeOneLetter, substrings, anagramsMinusOne, containingWords)
    // -- aucune section correspondante ne doit apparaitre dans le HTML.
    // -------------------------------------------------------------------
    $aaPage = new TermPage(
        normalized: 'AA',
        slug: 'aa',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: 2,
        length: 2,
        isOds8: true,
        isOds9: true,
        letters: $tiles('AA', $tileScores),
        previousWord: null,
        nextWord: 'AABAM',
    );
    $aaRelations = new TermRelations(
        anagrams: [],
        changeOneLetter: [$item('AB', ['position' => 2, 'newLetter' => 'B'])],
        removeOneLetter: [],
        insertOneLetter: [$item('ACA')],
        substrings: [],
        rightExtensions: [$item('AAH'), $item('AAS')],
        rightExtensionsTotal: 5,
        rightExtensionsTruncated: false,
        leftExtensions: [$item('BAA')],
        leftExtensionsTotal: 1,
        leftExtensionsTruncated: false,
        containingWords: [],
        containingWordsTotal: 0,
        containingWordsTruncated: false,
        anagramsPlusOne: [
            $item('AAB', ['addedLetter' => 'B']),
            $item('ABA', ['addedLetter' => 'B']),
            $item('CAA', ['addedLetter' => 'C']),
        ],
        anagramsMinusOne: [],
        relatedSearches: [
            ['type' => 'length', 'url' => '/mots/2-lettres'],
            ['type' => 'startsWith', 'url' => '/mots/commencant/a'],
            ['type' => 'play', 'url' => '/jouer/aa'],
        ],
        queryCount: 5,
    );

    $htmlAa = $render($aaPage, $aaRelations, $noConjugation, $noSenses);

    Assert::true(str_contains($htmlAa, 'Jouer Autour De AA'), 'AA : titre de section relations attendu');

    // Categories structurellement vides : aucune trace de leur titre exact.
    Assert::true(!str_contains($htmlAa, '<span>Anagrammes</span>'), 'AA : anagrammes exactes structurellement vide, aucune section');
    Assert::true(!str_contains($htmlAa, '<span>Retirer Une Lettre</span>'), 'AA : retirer une lettre structurellement vide, aucune section');
    Assert::true(!str_contains($htmlAa, '<span>Sous-Mots</span>'), 'AA : sous-mots structurellement vide, aucune section');
    Assert::true(!str_contains($htmlAa, '<span>Anagrammes Avec Une Lettre En Moins</span>'), 'AA : anagrammes -1 lettre structurellement vide, aucune section');
    Assert::true(!str_contains($htmlAa, 'AA Dans Un Mot Plus Long'), 'AA : mot contenu structurellement vide, aucune section');

    // changeOneLetter : position/newLetter fournis par le backend, surlignage attendu.
    Assert::true(str_contains($htmlAa, 'A<mark>B</mark>'), 'AA : changeOneLetter doit surligner la lettre en position 2 (AB)');

    // insertOneLetter : surlignage calcule par comparaison de chaines (ACA = A + C inseree).
    Assert::true(str_contains($htmlAa, 'A<mark>C</mark>A'), 'AA : insertOneLetter doit surligner la lettre inseree (ACA)');

    // rightExtensions : mot pivot surligne en prefixe, lien "Voir les N mots" car total (5) > items affiches (2).
    Assert::true(str_contains($htmlAa, '<mark>AA</mark>H'), 'AA : rightExtensions doit surligner le prefixe (AAH)');
    Assert::true(str_contains($htmlAa, '<mark>AA</mark>S'), 'AA : rightExtensions doit surligner le prefixe (AAS)');
    Assert::true(str_contains($htmlAa, 'href="/mots/commencant/aa"'), 'AA : lien "Voir les N mots" attendu pour rightExtensions (total > affiches)');
    Assert::true(str_contains($htmlAa, 'Voir les 5 mots'), 'AA : libelle du lien rightExtensions avec le total exact');

    // leftExtensions : mot pivot surligne en suffixe, PAS de lien "Voir les N mots" (total == affiches).
    Assert::true(str_contains($htmlAa, 'B<mark>AA</mark>'), 'AA : leftExtensions doit surligner le suffixe (BAA)');
    Assert::true(!str_contains($htmlAa, 'href="/mots/terminant/aa"'), 'AA : aucun lien "Voir les N mots" pour leftExtensions (total == affiches)');
    Assert::true(str_contains($htmlAa, '1 mot<'), 'AA : compte au singulier pour leftExtensions (1 mot)');

    // anagramsPlusOne : regroupement par lettre ajoutee, pas de surlignage.
    Assert::true(str_contains($htmlAa, '<span class="plus">+B</span>'), 'AA : groupe +B attendu pour anagramsPlusOne');
    Assert::true(str_contains($htmlAa, '<span class="plus">+C</span>'), 'AA : groupe +C attendu pour anagramsPlusOne');
    Assert::true(str_contains($htmlAa, '>AAB<') && str_contains($htmlAa, '>ABA<') && str_contains($htmlAa, '>CAA<'), 'AA : les trois mots anagramsPlusOne doivent apparaitre en liens');

    // Recherches liees.
    Assert::true(str_contains($htmlAa, 'Recherches Liées'), 'AA : section recherches liees attendue');
    Assert::true(str_contains($htmlAa, '>Mots De 2 Lettres<'), 'AA : libelle "length" attendu');
    Assert::true(str_contains($htmlAa, '>Commençant Par A<'), 'AA : libelle "startsWith" attendu');
    Assert::true(str_contains($htmlAa, '>Jouer Avec AA<'), 'AA : libelle "play" attendu');

    // -------------------------------------------------------------------
    // ABANDONNATRICES -- quinze lettres (borne haute, D-010). insertOneLetter,
    // anagramsPlusOne, rightExtensions, leftExtensions, containingWords sont
    // structurellement vides (aucun mot de 16 lettres ne peut jamais exister en base).
    // -------------------------------------------------------------------
    $longPage = new TermPage(
        normalized: 'ABANDONNATRICES',
        slug: 'abandonnatrices',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: 20,
        length: 15,
        isOds8: true,
        isOds9: true,
        letters: $tiles('ABANDONNATRICES', $tileScores),
        previousWord: 'ABANDONNATRICE',
        nextWord: 'ABANDONNE',
    );
    $longRelations = new TermRelations(
        anagrams: [],
        changeOneLetter: [$item('ABANDONNATRICEX', ['position' => 15, 'newLetter' => 'X'])],
        removeOneLetter: [$item('ABANDONNATRICE')],
        insertOneLetter: [],
        substrings: [$item('BANDON')],
        rightExtensions: [],
        rightExtensionsTotal: 0,
        rightExtensionsTruncated: false,
        leftExtensions: [],
        leftExtensionsTotal: 0,
        leftExtensionsTruncated: false,
        containingWords: [],
        containingWordsTotal: 0,
        containingWordsTruncated: false,
        anagramsPlusOne: [],
        anagramsMinusOne: [$item('ABANDONNATRICE')],
        relatedSearches: [
            ['type' => 'length', 'url' => '/mots/15-lettres'],
        ],
        queryCount: 5,
    );

    $htmlLong = $render($longPage, $longRelations, $noConjugation, $noSenses);

    Assert::true(str_contains($htmlLong, 'Jouer Autour De ABANDONNATRICES'), 'ABANDONNATRICES : titre de section relations attendu');
    Assert::true(!str_contains($htmlLong, '<span>Insérer Une Lettre</span>'), 'ABANDONNATRICES : inserer une lettre structurellement vide (D-010)');
    Assert::true(!str_contains($htmlLong, '<span>Anagrammes Avec Une Lettre En Plus</span>'), 'ABANDONNATRICES : anagrammes +1 lettre structurellement vide (D-010)');
    Assert::true(!str_contains($htmlLong, '<span>Rallonges À Droite</span>'), 'ABANDONNATRICES : rallonges a droite structurellement vide (D-010)');
    Assert::true(!str_contains($htmlLong, '<span>Rallonges À Gauche</span>'), 'ABANDONNATRICES : rallonges a gauche structurellement vide (D-010)');
    Assert::true(!str_contains($htmlLong, 'ABANDONNATRICES Dans Un Mot Plus Long'), 'ABANDONNATRICES : mot contenu structurellement vide (D-010)');
    Assert::true(str_contains($htmlLong, 'ABANDONNATRICE<mark>X</mark>'), 'ABANDONNATRICES : changeOneLetter doit surligner la derniere position');

    // -------------------------------------------------------------------
    // Francais non admis / inconnu -- $relations = null : aucune section relations.
    // -------------------------------------------------------------------
    $notAdmittedPage = new TermPage(
        normalized: 'GHOSTER',
        slug: 'ghoster',
        found: true,
        status: TermPage::STATUS_FRENCH_NOT_ADMITTED,
        score: 11,
        length: 7,
        isOds8: false,
        isOds9: false,
        letters: $tiles('GHOSTER', $tileScores),
        previousWord: 'GHOSTENT',
        nextWord: 'GHOSTERA',
        // pos renseigne malgre le statut "francais non admis" : D-018 s'applique
        // independamment du statut Scrabble (voir docs/DECISIONS.md D-018) -- couvre
        // explicitement "un mot francais non admis avec pos renseigne".
        pos: 'V',
    );
    $htmlNotAdmitted = $render($notAdmittedPage, null, $noConjugation, $noSenses);
    Assert::true(!str_contains($htmlNotAdmitted, 'class="relations"'), 'GHOSTER : francais non admis, aucune section relations');
    Assert::true(!str_contains($htmlNotAdmitted, 'Recherches Liées'), 'GHOSTER : francais non admis, aucune recherche liee');
    Assert::true(str_contains($htmlNotAdmitted, '<p class="pos-line">Verbe</p>'), 'GHOSTER : pos renseigne malgre le statut non admis, ligne attendue');
    Assert::true(!str_contains($htmlNotAdmitted, 'class="conjugation"'), 'GHOSTER : aucune donnee de conjugaison, aucune section');

    $unknownPage = new TermPage(
        normalized: 'ZZZQQQXXX',
        slug: 'zzzqqqxxx',
        found: false,
        status: TermPage::STATUS_UNKNOWN,
        score: 84,
        length: 9,
        isOds8: false,
        isOds9: false,
        letters: $tiles('ZZZQQQXXX', $tileScores),
        previousWord: 'ZYZZYVAS',
        nextWord: null,
    );
    $htmlUnknown = $render($unknownPage, null, $noConjugation, $noSenses);
    Assert::true(!str_contains($htmlUnknown, 'class="relations"'), 'ZZZQQQXXX : inconnu, aucune section relations');
    Assert::true(!str_contains($htmlUnknown, 'Recherches Liées'), 'ZZZQQQXXX : inconnu, aucune recherche liee');
    Assert::true(!str_contains($htmlUnknown, 'class="pos-line"'), 'ZZZQQQXXX : pos absent (terme inconnu), aucune ligne nature grammaticale');
    Assert::true(!str_contains($htmlUnknown, 'class="conjugation"'), 'ZZZQQQXXX : pos absent, aucune section conjugaison');

    // -------------------------------------------------------------------
    // D-018 -- nature grammaticale, genre, conjugaison. Valeurs reprises telles
    // qu'observees sur storage/dictionary_fr.sqlite (verifie manuellement pendant
    // l'implementation, App\Search\TermLookup/ConjugationLookup), pas inventees.
    // -------------------------------------------------------------------
    $scoreFor = static function (string $word) use ($tileScores): int {
        return array_sum(array_map(static fn (string $letter): int => $tileScores[$letter], str_split($word)));
    };

    // POSER (lemme) : selection representative present/futur/imparfait/participes,
    // jusqu'a 20 formes -- ici un sous-ensemble suffisant pour couvrir les cinq temps
    // et le regroupement par forme homographe (POSE = 1s ET 3s au present).
    $poserPage = new TermPage(
        normalized: 'POSER',
        slug: 'poser',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: $scoreFor('POSER'),
        length: 5,
        isOds8: true,
        isOds9: true,
        letters: $tiles('POSER', $tileScores),
        previousWord: 'POSEUR',
        nextWord: 'POSERA',
        pos: 'V',
    );
    $poserConjugation = new Conjugation(
        asLemma: [
            ['form' => 'POSE', 'slug' => 'pose', 'tense' => 'present', 'person' => '1s'],
            ['form' => 'POSE', 'slug' => 'pose', 'tense' => 'present', 'person' => '3s'],
            ['form' => 'POSES', 'slug' => 'poses', 'tense' => 'present', 'person' => '2s'],
            ['form' => 'POSERA', 'slug' => 'posera', 'tense' => 'future', 'person' => '3s'],
            ['form' => 'POSAIT', 'slug' => 'posait', 'tense' => 'imperfect', 'person' => '3s'],
            ['form' => 'POSANT', 'slug' => 'posant', 'tense' => 'participle_present', 'person' => null],
            ['form' => 'POSE', 'slug' => 'pose', 'tense' => 'participle_past', 'person' => null],
        ],
        asForm: [],
        queryCount: 1,
    );
    $htmlPoser = $render($poserPage, null, $poserConjugation, $noSenses);
    Assert::true(str_contains($htmlPoser, '<p class="pos-line">Verbe</p>'), 'POSER : ligne "Verbe" attendue');
    Assert::true(str_contains($htmlPoser, '<h2>Se Conjugue</h2>'), 'POSER : titre "Se Conjugue" attendu (asLemma non vide)');
    Assert::true(!str_contains($htmlPoser, '<h2>Conjugaison</h2>'), 'POSER : pas le titre generique "Conjugaison" (asLemma non vide)');
    foreach (['Présent', 'Futur', 'Imparfait', 'Participe présent', 'Participe passé'] as $tenseLabel) {
        Assert::true(str_contains($htmlPoser, '<span class="plus">' . $tenseLabel . '</span>'), 'POSER : groupe de temps "' . $tenseLabel . '" attendu');
    }
    // POSE (1s et 3s au present) : une seule occurrence dans le flux du groupe Present,
    // les deux personnes fusionnees au survol plutot que le mot duplique visuellement.
    Assert::true(
        str_contains($htmlPoser, '<a href="/mot/pose" title="1re pers. sing. / 3e pers. sing.">POSE</a>'),
        'POSER : POSE (present 1s+3s) doit etre fusionne en un seul lien avec les deux personnes au survol',
    );
    Assert::true(
        str_contains($htmlPoser, '<a href="/mot/poses" title="2e pers. sing.">POSES</a>'),
        'POSER : POSES (present 2s) attendu avec sa personne au survol',
    );
    Assert::true(
        str_contains($htmlPoser, '<a href="/mot/posant">POSANT</a>'),
        'POSER : POSANT (participe present, aucune personne) ne doit porter aucun attribut title',
    );

    // POSERA (forme) : pointe vers POSER, futur 3s -- exemple nomme explicitement (CLAUDE.md).
    $poseraPage = new TermPage(
        normalized: 'POSERA',
        slug: 'posera',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: $scoreFor('POSERA'),
        length: 6,
        isOds8: true,
        isOds9: true,
        letters: $tiles('POSERA', $tileScores),
        previousWord: 'POSER',
        nextWord: 'POSERAI',
        pos: 'V',
    );
    $poseraConjugation = new Conjugation(
        asLemma: [],
        asForm: [['lemma' => 'POSER', 'slug' => 'poser', 'tense' => 'future', 'person' => '3s']],
        queryCount: 1,
    );
    $htmlPosera = $render($poseraPage, null, $poseraConjugation, $noSenses);
    Assert::true(str_contains($htmlPosera, '<h2>Conjugaison</h2>'), 'POSERA : titre generique "Conjugaison" attendu (asLemma vide)');
    Assert::true(!str_contains($htmlPosera, '<h2>Se Conjugue</h2>'), 'POSERA : pas "Se Conjugue", POSERA n\'est pas un infinitif');
    Assert::true(
        str_contains($htmlPosera, 'Forme conjuguée de <a href="/mot/poser">POSER</a> (futur, 3e pers. sing.).'),
        'POSERA : phrase courte attendue, temps/personne traduits en francais',
    );

    // TABLE : homographe nom/verbe reel (D-018). asLemma vide (TABLE n'est pas un
    // infinitif connu), asForm non vide (TABLE EST une forme conjuguee de TABLER).
    $tablePage = new TermPage(
        normalized: 'TABLE',
        slug: 'table',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: $scoreFor('TABLE'),
        length: 5,
        isOds8: true,
        isOds9: true,
        letters: $tiles('TABLE', $tileScores),
        previousWord: 'TABLAS',
        nextWord: 'TABLEAU',
        pos: 'N',
        posSecondary: 'V',
        gender: 'f',
    );
    $tableConjugation = new Conjugation(
        asLemma: [],
        asForm: [
            ['lemma' => 'TABLER', 'slug' => 'tabler', 'tense' => 'participle_past', 'person' => null],
            ['lemma' => 'TABLER', 'slug' => 'tabler', 'tense' => 'present', 'person' => '1s'],
            ['lemma' => 'TABLER', 'slug' => 'tabler', 'tense' => 'present', 'person' => '3s'],
        ],
        queryCount: 1,
    );
    $htmlTable = $render($tablePage, null, $tableConjugation, $noSenses);
    Assert::true(str_contains($htmlTable, '<p class="pos-line">Nom féminin, aussi verbe</p>'), 'TABLE : ligne "Nom féminin, aussi verbe" attendue');
    Assert::true(str_contains($htmlTable, 'Forme conjuguée de <a href="/mot/tabler">TABLER</a> (participe passé).'), 'TABLE : phrase participe passe attendue');
    Assert::true(str_contains($htmlTable, 'Forme conjuguée de <a href="/mot/tabler">TABLER</a> (présent, 1re pers. sing.).'), 'TABLE : phrase present 1s attendue');
    Assert::true(str_contains($htmlTable, 'Forme conjuguée de <a href="/mot/tabler">TABLER</a> (présent, 3e pers. sing.).'), 'TABLE : phrase present 3s attendue');

    // CHAT : nom simple, aucune conjugaison -- section entiere absente.
    $chatPage = new TermPage(
        normalized: 'CHAT',
        slug: 'chat',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: $scoreFor('CHAT'),
        length: 4,
        isOds8: true,
        isOds9: true,
        letters: $tiles('CHAT', $tileScores),
        previousWord: 'CHASSIS',
        nextWord: 'CHATOIE',
        pos: 'N',
        gender: 'm',
    );
    $htmlChat = $render($chatPage, null, $noConjugation, $noSenses);
    Assert::true(str_contains($htmlChat, '<p class="pos-line">Nom masculin</p>'), 'CHAT : ligne "Nom masculin" attendue');
    Assert::true(!str_contains($htmlChat, 'class="conjugation"'), 'CHAT : aucune donnee de conjugaison, aucune section');

    // ETRE : verbe suppletif exclu de verb_forms (non fiable, D-018) -- asLemma vide
    // malgre pos=V. Homographe reel avec le nom "un etre" (posSecondary=N, gender=m).
    $etrePage = new TermPage(
        normalized: 'ETRE',
        slug: 'etre',
        found: true,
        status: TermPage::STATUS_ADMITTED,
        score: $scoreFor('ETRE'),
        length: 4,
        isOds8: true,
        isOds9: true,
        letters: $tiles('ETRE', $tileScores),
        previousWord: 'ETRAVE',
        nextWord: 'ETREINDRE',
        pos: 'V',
        posSecondary: 'N',
        gender: 'm',
    );
    $htmlEtre = $render($etrePage, null, $noConjugation, $noSenses);
    Assert::true(str_contains($htmlEtre, '<p class="pos-line">Verbe, aussi nom masculin</p>'), 'ETRE : ligne "Verbe, aussi nom masculin" attendue');
    Assert::true(!str_contains($htmlEtre, 'class="conjugation"'), 'ETRE : verbe supplétif exclu, aucune section conjugaison malgre pos=V');

    // Francais non admis + conjugaison (ex. reel : ABADAIENT -> ABADER, pos absent) :
    // la section conjugaison ne depend pas du statut Scrabble ni de la presence de pos.
    $formOnlyNotAdmittedPage = new TermPage(
        normalized: 'ABADAIENT',
        slug: 'abadaient',
        found: true,
        status: TermPage::STATUS_FRENCH_NOT_ADMITTED,
        score: $scoreFor('ABADAIENT'),
        length: 9,
        isOds8: false,
        isOds9: false,
        letters: $tiles('ABADAIENT', $tileScores),
        previousWord: 'ABADAI',
        nextWord: 'ABADAIS',
    );
    $formOnlyConjugation = new Conjugation(
        asLemma: [],
        asForm: [['lemma' => 'ABADER', 'slug' => 'abader', 'tense' => 'imperfect', 'person' => '3p']],
        queryCount: 1,
    );
    $htmlFormOnlyNotAdmitted = $render($formOnlyNotAdmittedPage, null, $formOnlyConjugation, $noSenses);
    Assert::true(!str_contains($htmlFormOnlyNotAdmitted, 'class="pos-line"'), 'ABADAIENT : pos absent, aucune ligne nature grammaticale');
    Assert::true(
        str_contains($htmlFormOnlyNotAdmitted, 'Forme conjuguée de <a href="/mot/abader">ABADER</a> (imparfait, 3e pers. plur.).'),
        'ABADAIENT : section conjugaison attendue malgre le statut francais non admis',
    );

    // -------------------------------------------------------------------
    // Invariants communs a tous les cas (deja verifies en Phase 1, reconduits ici).
    // -------------------------------------------------------------------
    $cases = [
        $aaPage, $longPage, $notAdmittedPage, $unknownPage,
        $poserPage, $poseraPage, $tablePage, $chatPage, $etrePage, $formOnlyNotAdmittedPage,
    ];
    $htmlByCase = [
        $htmlAa, $htmlLong, $htmlNotAdmitted, $htmlUnknown,
        $htmlPoser, $htmlPosera, $htmlTable, $htmlChat, $htmlEtre, $htmlFormOnlyNotAdmitted,
    ];

    foreach ($cases as $i => $page) {
        $html = $htmlByCase[$i];

        $tileValueSum = array_sum(array_column($page->letters, 'value'));
        Assert::same($page->score, $tileValueSum, $page->normalized . ' : somme des tuiles doit egaler le score livre par TermPage');

        $letterTileCount = substr_count($html, 'class="letter-tile"');
        Assert::same($page->length, $letterTileCount, $page->normalized . ' : une tuile par lettre dans le HTML rendu');

        Assert::true(str_contains($html, $page->normalized), $page->normalized . ' : le mot doit apparaitre dans le HTML');
        Assert::true(str_contains($html, (string) $page->score), $page->normalized . ' : le score doit apparaitre dans le HTML');
        Assert::true(
            str_contains($html, '<form class="inline-check" action="/verifier" method="get">'),
            $page->normalized . ' : le formulaire de verification doit rester un GET natif sans JavaScript',
        );

        // D-015 : aucun credit de source publie, nulle part dans le HTML servi.
        $forbidden = ['kartmaan', 'hbenbel', 'github.com', 'larousse'];
        $lowerHtml = mb_strtolower($html);
        foreach ($forbidden as $needle) {
            Assert::true(!str_contains($lowerHtml, $needle), $page->normalized . ' : mention de source interdite (D-015) : ' . $needle);
        }
    }

    // Aucun mot precedent -> pas de lien mort, mais la structure reste symetrique.
    Assert::true(!str_contains($htmlAa, '← AA'), 'AA est le premier mot de la base : aucun lien precedent ne doit etre rendu');
    Assert::true(str_contains($htmlAa, 'AABAM'), 'le mot suivant doit rester present');

    Assert::true(!str_contains($htmlUnknown, 'ZZZQQQXXX →'), 'aucun mot suivant : aucun lien mort ne doit etre rendu');
};
