<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Search\Normalizer;
use App\Search\TermLookup;
use App\Search\TermPage;
use Tests\Support\Assert;

/**
 * Exerce App\Search\TermLookup sur la vraie base storage/dictionary_fr.sqlite (lecture
 * seule) : cas connus des trois statuts, formes invalides, voisinage alphabetique, et
 * verification exhaustive de score/signature/reversed/length sur les 838 180 lignes
 * reelles -- pas un echantillon, meme discipline que l'audit Phase 0
 * (reports/phase0-after.md).
 */
return function (): void {
    $dbPath = __DIR__ . '/../../storage/dictionary_fr.sqlite';
    Assert::true(is_file($dbPath), 'base manquante : ' . $dbPath);

    $siteConfig = require __DIR__ . '/../../config/sites/fr.php';
    $tileScores = $siteConfig['tile_scores'];

    $connection = new Connection($dbPath);
    $lookup = new TermLookup($connection, $tileScores);

    // Mot admis ODS8 et ODS9 (meme cas que reports/query-plans/phase0.md).
    $poser = $lookup->find('poser');
    Assert::notNull($poser, 'POSER devrait etre trouve');
    Assert::same('POSER', $poser->normalized);
    Assert::same('poser', $poser->slug);
    Assert::true($poser->found);
    Assert::same(TermPage::STATUS_ADMITTED, $poser->status);
    Assert::same(7, $poser->score);
    Assert::same(5, $poser->length);
    Assert::true($poser->isOds8);
    Assert::true($poser->isOds9);
    Assert::same(5, count($poser->letters));
    Assert::same(7, array_sum(array_column($poser->letters, 'value')), 'la somme des tuiles doit egaler le score');
    Assert::same(['letter' => 'P', 'value' => 3], $poser->letters[0]);

    // D-018 : nature grammaticale, sur un mot admis. POSER n'a qu'un seul sens Kartmaan
    // retenu (verbe), pas de genre (jamais associe a un pos autre que N).
    Assert::same('V', $poser->pos);
    Assert::null($poser->posSecondary);
    Assert::null($poser->gender);

    // D-018 : homographe nom/verbe reel, verifie sur la base construite -- TABLE est a la
    // fois un nom (feminin) et une forme flechie du verbe "tabler" dans Kartmaan.
    $table = $lookup->find('table');
    Assert::notNull($table, 'TABLE devrait etre trouve');
    Assert::same('N', $table->pos);
    Assert::same('V', $table->posSecondary);
    Assert::same('f', $table->gender);

    // D-018 : nom simple, genre masculin, aucun second sens.
    $chat = $lookup->find('chat');
    Assert::notNull($chat, 'CHAT devrait etre trouve');
    Assert::same('N', $chat->pos);
    Assert::null($chat->posSecondary);
    Assert::same('m', $chat->gender);

    // Forme francaise non admise (docs/08, remplacant verifie de QUEULEULEU).
    $ghoster = $lookup->find('GHOSTER');
    Assert::notNull($ghoster, 'GHOSTER devrait etre trouve');
    Assert::true($ghoster->found);
    Assert::same(TermPage::STATUS_FRENCH_NOT_ADMITTED, $ghoster->status);
    Assert::true(!$ghoster->isOds8, 'GHOSTER ne doit pas etre marque ODS8');
    Assert::true(!$ghoster->isOds9, 'GHOSTER ne doit pas etre marque ODS9');
    // D-018 : GHOSTER est absent de Kartmaan (source unique de pos/gender) -- nature
    // grammaticale nulle, absence de donnee et non une erreur (~12,3 % des termes).
    Assert::null($ghoster->pos, 'GHOSTER absent de Kartmaan, pos doit rester nul');
    Assert::null($ghoster->posSecondary);
    Assert::null($ghoster->gender);

    // Terme absent, forme valide -> inconnu, pas une erreur (confirme absent de la base).
    $unknown = $lookup->find('ZZZQQQXXX');
    Assert::notNull($unknown, 'une forme valide, meme absente, doit produire une fiche');
    Assert::true(!$unknown->found);
    Assert::same(TermPage::STATUS_UNKNOWN, $unknown->status);
    Assert::same(9, $unknown->length);
    Assert::same(9, count($unknown->letters));
    Assert::true(!$unknown->isOds8 && !$unknown->isOds9);
    // D-018 : un terme inconnu n'a jamais de nature grammaticale (aucune ligne en base).
    Assert::null($unknown->pos);
    Assert::null($unknown->posSecondary);
    Assert::null($unknown->gender);

    // Formes invalides -> aucune fiche, donc aucun quatrieme statut invente.
    Assert::null($lookup->find(''), 'entree vide');
    Assert::null($lookup->find('a'), 'une seule lettre, sous MIN_LENGTH');
    Assert::null($lookup->find('poser3'), 'chiffre dans l\'entree');
    Assert::null($lookup->find(str_repeat('a', Normalizer::MAX_LENGTH + 1)), 'au-dessus de MAX_LENGTH');

    // Voisinage alphabetique autour d'un mot present -- ordre alphabetique strict sur
    // toute la base (838 180 formes, dont des conjugaisons), pas une selection eclairee :
    // verifie a la main, le precedent et le suivant reels de POSER sont POSENT et POSERA,
    // pas POSE/POSES (qui seraient les voisins dans un sous-ensemble plus restreint).
    Assert::same('POSENT', $poser->previousWord);
    Assert::same('POSERA', $poser->nextWord);

    // Bornes de la base : AA est le premier mot, ZYZZYVAS le dernier (verifie a la main).
    $first = $lookup->find('AA');
    Assert::notNull($first);
    Assert::true($first->found);
    Assert::null($first->previousWord, 'AA est le premier mot de la base, pas de precedent');
    Assert::notNull($first->nextWord);

    $last = $lookup->find('ZYZZYVAS');
    Assert::notNull($last);
    Assert::true($last->found);
    Assert::notNull($last->previousWord);
    Assert::null($last->nextWord, 'ZYZZYVAS est le dernier mot de la base, pas de suivant');

    // Regression C1 (audit Phase 1) : entree UTF-8 invalide -> aucune fiche, aucune
    // exception qui remonterait au flux HTTP (reproduit /verifier?mot=%FF%FE).
    Assert::null($lookup->find("\xFF\xFE"), 'octets UTF-8 invalides');

    // Regression C2 (audit Phase 1) : un saut de ligne final ne doit jamais produire
    // de fiche (reproduit /mot/poser%0A, qui decode en "poser\n" une fois le segment
    // d'URL passe dans rawurldecode() par public/index.php).
    Assert::null($lookup->find('poser' . "\n"), 'POSER suivi d\'un saut de ligne');

    // Verification exhaustive : score/signature/reversed/length recalcules pour les
    // 838 180 lignes reelles, compares aux colonnes stockees par scripts/import_fr.py.
    // D-018 : pos/pos_secondary/gender verifies contre leurs jeux fermes respectifs
    // (docs/DECISIONS.md D-018) sur les memes 838 180 lignes -- pas un echantillon.
    // Curseur PDO en streaming (pas de fetchAll) : ne charge pas la table en memoire.
    $validPos = ['N', 'V', 'Adj', 'Adv', 'Pronom', 'Prep', 'Conj', 'Interj', 'Art'];
    $validGender = ['m', 'f', 'e'];

    $pdo = $connection->pdo();
    $statement = $pdo->query('SELECT normalized, score, length, signature, reversed, pos, pos_secondary, gender FROM terms');

    $rows = 0;
    $rowsWithPos = 0;
    $rowsWithPosSecondary = 0;
    $rowsWithGender = 0;
    foreach ($statement as $row) {
        $rows++;
        $normalized = $row['normalized'];

        Assert::true(Normalizer::isValid($normalized), 'forme invalide en base : ' . $normalized);
        Assert::same((int) $row['score'], Normalizer::score($normalized, $tileScores), 'score de ' . $normalized);
        Assert::same((int) $row['length'], strlen($normalized), 'length de ' . $normalized);
        Assert::same($row['signature'], Normalizer::signature($normalized), 'signature de ' . $normalized);

        if ($row['pos'] !== null) {
            $rowsWithPos++;
            Assert::true(in_array($row['pos'], $validPos, true), 'pos hors du jeu ferme pour ' . $normalized . ' : ' . $row['pos']);
        }

        if ($row['pos_secondary'] !== null) {
            $rowsWithPosSecondary++;
            Assert::true(in_array($row['pos_secondary'], $validPos, true), 'pos_secondary hors du jeu ferme pour ' . $normalized);
            Assert::true($row['pos_secondary'] !== $row['pos'], 'pos_secondary identique a pos pour ' . $normalized);
            Assert::notNull($row['pos'], 'pos_secondary renseigne sans pos pour ' . $normalized);
        }

        if ($row['gender'] !== null) {
            $rowsWithGender++;
            Assert::true(in_array($row['gender'], $validGender, true), 'gender hors du jeu ferme pour ' . $normalized . ' : ' . $row['gender']);
        }
        Assert::same($row['reversed'], Normalizer::reverse($normalized), 'reversed de ' . $normalized);
    }

    Assert::same(838180, $rows, 'nombre total de lignes verifiees, doit correspondre a docs/PHASE_STATUS.md');

    // D-018 : comptes mesures au build (rapport BEFORE + reports/import-summary.json),
    // reverifies ici independamment sur la base reelle plutot que fait confiance au rapport.
    Assert::same(734622, $rowsWithPos, 'termes avec un pos Kartmaan');
    Assert::same(88649, $rowsWithPosSecondary, 'termes avec un second pos distinct');
    Assert::same(26261, $rowsWithGender, 'termes avec un genre');
};
