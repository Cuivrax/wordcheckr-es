<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\Normalizer;
use App\Search\TermLookup;
use App\Search\TermPage;
use Tests\Support\Assert;

/**
 * Exerce App\Search\TermLookup sur la vraie base storage/dictionary_es.sqlite (lecture
 * seule) : cas connus des trois statuts, formes invalides, voisinage alphabetique, et
 * verification exhaustive de score/signature/reversed/length sur les 748 165 lignes
 * reelles -- pas un echantillon.
 *
 * Adapte de tests/Search/TermLookupTest.php (site francais) -- differences :
 *   - pos/pos_secondary/gender (D-018 du site francais) HORS PERIMETRE ici (voir
 *     docs/DECISIONS.md ES-001) : toujours NULL, colonnes conservees au schema
 *     uniquement pour compatibilite avec TermLookup::find() (voir schema.sql)
 *   - mots pivots espagnols reels, dont un mot AVEC tuile digramme (COCHE) pour
 *     verifier que "une tuile par lettre avec sa valeur" reste tuile-aware, pas
 *     caractere par caractere (bug reel corrige : str_split() plantait cette fiche
 *     pour tout mot contenant Ñ, et donnait 5 tuiles au lieu de 4 pour COCHE)
 *   - verification exhaustive mb-safe (mb_strlen, pas strlen) : Ñ occupe 2 octets
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_es.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $siteConfig = require __DIR__ . '/../../config/sites/es.php';
    $tileScores = $siteConfig['tile_scores'];

    $connection = new Connection($dbPath);
    $lookup = new TermLookup($connection, $tileScores);

    // Mot admis FILE 2017 et FISE-2, AVEC une tuile digramme (CH) -- verifie que la
    // decomposition en tuiles affichee (letters) reste coherente avec le score, tuile
    // par tuile, pas lettre par lettre.
    $coche = $lookup->find('coche');
    Assert::notNull($coche, 'COCHE devrait etre trouve');
    Assert::same('COCHE', $coche->normalized);
    Assert::same('coche', $coche->slug);
    Assert::true($coche->found);
    Assert::same(TermPage::STATUS_ADMITTED, $coche->status);
    Assert::same(10, $coche->score, 'C(3) + O(1) + CH(5) + E(1) = 10');
    Assert::same(5, $coche->length, 'longueur en CARACTERES (5), pas en tuiles (4)');
    Assert::true($coche->isOds8);
    Assert::true($coche->isOds9);
    Assert::same(4, count($coche->letters), 'COCHE = 4 TUILES (C, O, CH, E), pas 5 lettres');
    Assert::same(10, array_sum(array_column($coche->letters, 'value')), 'la somme des tuiles affichees doit egaler le score');
    Assert::same(['letter' => 'C', 'value' => 3], $coche->letters[0]);
    Assert::same(['letter' => 'O', 'value' => 1], $coche->letters[1]);
    Assert::same(['letter' => 'CH', 'value' => 5], $coche->letters[2], 'la tuile CH doit apparaitre comme UNE entree, valeur 5, pas C+H separes');
    Assert::same(['letter' => 'E', 'value' => 1], $coche->letters[3]);

    // pos/pos_secondary/gender hors perimetre pour ce site (ES-001) : toujours NULL,
    // meme pour un terme admis et bien connu.
    Assert::null($coche->pos);
    Assert::null($coche->posSecondary);
    Assert::null($coche->gender);

    // Mot admis simple, sans digramme, avec K et W (fallback de score, aucun mot du
    // dictionnaire construit ne les porte normalement -- voir config/sites/es.php) --
    // WHISKY est un emprunt reel, present comme forme espagnole (is_spanish = 1) mais
    // PAS admis au Scrabble (equivalent du role illustratif de GHOSTER cote francais).
    $whisky = $lookup->find('WHISKY');
    Assert::notNull($whisky, 'WHISKY devrait etre trouve');
    Assert::true($whisky->found);
    Assert::same(TermPage::STATUS_FRENCH_NOT_ADMITTED, $whisky->status, 'WHISKY est un mot espagnol reel, non admis au Scrabble');
    Assert::true(!$whisky->isOds8, 'WHISKY ne doit pas etre marque FILE 2017');
    Assert::true(!$whisky->isOds9, 'WHISKY ne doit pas etre marque FISE-2');
    Assert::same(28, $whisky->score, 'W(8) + H(4) + I(1) + S(1) + K(10) + Y(4) = 28 -- exerce les valeurs de secours K/W sur un mot reel');
    Assert::same(6, count($whisky->letters));
    Assert::null($whisky->pos);
    Assert::null($whisky->posSecondary);
    Assert::null($whisky->gender);

    // Terme absent, forme valide -> inconnu, pas une erreur (confirme absent de la base).
    $unknown = $lookup->find('ZZZQQQXXX');
    Assert::notNull($unknown, 'une forme valide, meme absente, doit produire une fiche');
    Assert::true(!$unknown->found);
    Assert::same(TermPage::STATUS_UNKNOWN, $unknown->status);
    Assert::same(9, $unknown->length);
    Assert::same(9, count($unknown->letters));
    Assert::true(!$unknown->isOds8 && !$unknown->isOds9);
    Assert::null($unknown->pos);
    Assert::null($unknown->posSecondary);
    Assert::null($unknown->gender);

    // Terme inconnu contenant Ñ : le score doit rester calculable (pas d'exception),
    // meme absent de la base -- reproduit le chemin "verifier un mot jamais vu" pour Ñ.
    $unknownEnye = $lookup->find('ÑQXW');
    Assert::notNull($unknownEnye);
    Assert::true(!$unknownEnye->found);
    Assert::same(TermPage::STATUS_UNKNOWN, $unknownEnye->status);
    Assert::same(4, $unknownEnye->length, 'longueur en CARACTERES : Ñ compte pour UN caractere (bug reel corrige, strlen() la comptait pour deux)');
    Assert::same(4, count($unknownEnye->letters), 'Ñ doit rester une seule tuile dans la decomposition, jamais deux fragments d\'octet');
    Assert::same('Ñ', $unknownEnye->letters[0]['letter']);
    Assert::same(8, $unknownEnye->letters[0]['value']);

    // Formes invalides -> aucune fiche, donc aucun quatrieme statut invente.
    Assert::null($lookup->find(''), 'entree vide');
    Assert::null($lookup->find('a'), 'une seule lettre, sous MIN_LENGTH');
    Assert::null($lookup->find('poser3'), 'chiffre dans l\'entree');
    Assert::null($lookup->find(str_repeat('a', Normalizer::MAX_LENGTH + 1)), 'au-dessus de MAX_LENGTH');

    // Voisinage alphabetique autour d'un mot present.
    $stmt = $connection->pdo()->prepare(
        'SELECT normalized FROM (SELECT normalized FROM terms WHERE normalized < ? ORDER BY normalized DESC LIMIT 1)'
        . ' UNION ALL '
        . 'SELECT normalized FROM (SELECT normalized FROM terms WHERE normalized > ? ORDER BY normalized ASC LIMIT 1)'
    );
    $stmt->execute(['COCHE', 'COCHE']);
    $neighbours = array_column($stmt->fetchAll(), 'normalized');
    Assert::same($neighbours[0], $coche->previousWord, 'precedent verifie directement sur la base reelle');
    Assert::same($neighbours[1], $coche->nextWord, 'suivant verifie directement sur la base reelle');

    // Bornes de la base : AA est le premier mot (verifie a la main). Ñ trie APRES Z sous la
    // collation BINARY de SQLite (verifie sur la base reelle, WordListSolver::
    // ALPHABET_ORDER) -- le DERNIER mot de la base est donc un mot en Ñ, pas un mot en Z
    // comme sur le site francais. Recalcule directement plutot que suppose.
    $first = $lookup->find('AA');
    Assert::notNull($first);
    Assert::true($first->found);
    Assert::null($first->previousWord, 'AA est le premier mot de la base, pas de precedent');
    Assert::notNull($first->nextWord);

    $lastWord = $connection->pdo()->query('SELECT normalized FROM terms ORDER BY normalized DESC LIMIT 1')->fetch()['normalized'];
    Assert::true(str_starts_with($lastWord, 'Ñ'), 'sanity check : le dernier mot de la base doit commencer par Ñ (Ñ trie apres Z)');
    $last = $lookup->find($lastWord);
    Assert::notNull($last);
    Assert::true($last->found);
    Assert::notNull($last->previousWord);
    Assert::null($last->nextWord, $lastWord . ' est le dernier mot de la base, pas de suivant');

    // Regression C1 (heritee du site francais, audit Phase 1) : entree UTF-8 invalide ->
    // aucune fiche, aucune exception qui remonterait au flux HTTP.
    Assert::null($lookup->find("\xFF\xFE"), 'octets UTF-8 invalides');

    // Regression C2 (heritee du site francais, audit Phase 1) : un saut de ligne final ne
    // doit jamais produire de fiche.
    Assert::null($lookup->find('coche' . "\n"), 'COCHE suivi d\'un saut de ligne');

    // Regression specifique espagnole : une entree DECOMPOSEE (n + tilde combinant U+0303,
    // au lieu du Ñ precompose) doit produire EXACTEMENT la meme fiche que la forme
    // precomposee -- bug reel trouve et corrige (NFC prealable, voir
    // Normalizer::normalize()) : avant le correctif, cette entree perdait silencieusement
    // le Ñ et se comportait comme si elle avait cherche "ANO".
    $decomposed = $lookup->find("an\u{0303}o");
    Assert::notNull($decomposed);
    Assert::same('AÑO', $decomposed->normalized, 'une entree decomposee (NFD) doit se recomposer en Ñ avant tout traitement');

    // Verification exhaustive : score/signature/reversed/length recalcules pour les
    // 748 165 lignes reelles, compares aux colonnes stockees par scripts/import_es.py.
    // mb_strlen(), pas strlen() : Ñ occupe 2 octets en UTF-8, un compte par octet donnerait
    // une longueur fausse pour toute forme contenant Ñ (bug reel trouve et corrige avant
    // tout import). Curseur PDO en streaming (pas de fetchAll) : ne charge pas la table en
    // memoire.
    $pdo = $connection->pdo();
    $statement = $pdo->query('SELECT normalized, score, length, signature, reversed, pos, pos_secondary, gender FROM terms');

    $rows = 0;
    foreach ($statement as $row) {
        $rows++;
        $normalized = $row['normalized'];

        Assert::true(Normalizer::isValid($normalized), 'forme invalide en base : ' . $normalized);
        Assert::same((int) $row['score'], Normalizer::score($normalized, $tileScores), 'score de ' . $normalized);
        Assert::same((int) $row['length'], mb_strlen($normalized, 'UTF-8'), 'length de ' . $normalized);
        Assert::same($row['signature'], Normalizer::signature($normalized), 'signature de ' . $normalized);
        Assert::same($row['reversed'], Normalizer::reverse($normalized), 'reversed de ' . $normalized);

        // Hors perimetre ES-001 : pos/pos_secondary/gender toujours NULL, sur les 748 165
        // lignes sans exception -- scripts/import_es.py ne les peuple jamais.
        Assert::null($row['pos'], 'pos doit rester NULL (hors perimetre) pour ' . $normalized);
        Assert::null($row['pos_secondary'], 'pos_secondary doit rester NULL (hors perimetre) pour ' . $normalized);
        Assert::null($row['gender'], 'gender doit rester NULL (hors perimetre) pour ' . $normalized);
    }

    Assert::same(748165, $rows, 'nombre total de lignes verifiees, doit correspondre a reports/import-summary-es.json');
};
