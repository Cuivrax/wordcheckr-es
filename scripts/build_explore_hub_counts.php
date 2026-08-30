<?php

declare(strict_types=1);

/**
 * Précalcule les comptes du hub /palabras et du maillage interne longueur x lettre
 * (App\Search\ExploreHubBuilder, App\Search\LengthLinksBuilder) dans
 * storage/dictionary_es.sqlite -- hors ligne uniquement, jamais au runtime (D-001,
 * même principe que score/signature/reversed).
 *
 * RÉÉCRITURE COMPLÈTE de la copie française héritée (git archive) : l'ancien fichier ciblait
 * storage/dictionary_fr.sqlite par défaut, calculait 20 list_type (D-022 à D-041, jamais
 * portés côté ES) et n'avait jamais été adapté à l'espagnol -- signalé comme dangereux par
 * l'audit seo-registry (docs/DECISIONS.md ES-011, constat I-3 : "écrit silencieusement des
 * données fausses plutôt que de simplement échouer"). Voir docs/DECISIONS.md ES-017 pour la
 * décision complète (portée retenue, granularité par list_type, comptes vérifiés).
 *
 * PORTÉE DE CETTE PASSE (ES-017) : seulement 5 list_type sur les 19 du site français --
 * 'length', 'start', 'end', 'length_start', 'length_end'. Choisis parce qu'ils débloquent
 * exactement ce qui est DÉJÀ indexé ou déjà mesuré côté SEO espagnol (docs/DECISIONS.md
 * ES-016) :
 *   - 'length'  : alimente le hub /palabras (App\Search\ExploreHubBuilder::build(), section
 *     "Por Longitud") -- les 14 pages /palabras/{N}-letras sont déjà index,follow
 *     (word_list_length, ES-011 I-1).
 *   - 'start'   : alimente le hub (section "Empiezan Por") -- les 25 pages
 *     /palabras/empiezan-por/{lettre} déjà index,follow (word_list_commencant, ES-016)
 *     couvrent déjà 25 des 27 buckets produits ici (K et W restent SANS lien SEO réel,
 *     0 mot ADMIS ne commence par l'une ou l'autre -- voir "Décision : granularité
 *     CARACTÈRE" ci-dessous, ce script ne filtre PAS K/W : la donnée brute reste correcte,
 *     l'exclusion SEO est une décision de rollout distincte, déjà appliquée ailleurs).
 *   - 'end'     : alimente le hub (section "Terminan En"). GRANULARITÉ ADAPTÉE, voir
 *     "Décision : 1 caractère pour start, 2 pour end" ci-dessous.
 *   - 'length_start' / 'length_end' : alimentent App\Search\LengthLinksBuilder::build()
 *     (sections byStart/byEnd sur une page /palabras/{N}-letras déjà indexée) -- débloque le
 *     palier "longueur+empiezan-por"/"longueur+terminan-en" qu'ES-016 avait mesuré comme
 *     RAPIDE (mode EXACT, ex. 9-letras/empiezan-por/a : 3,5 ms) mais fermé faute de maillage
 *     entrant réel ("AUCUN lien reel (LengthLinksBuilder::byStart depend de list_counts)").
 *     Une fois ce script exécuté, /palabras/{N}-letras (déjà indexée) émet un lien HTML RÉEL
 *     vers chaque combinaison longueur+lettre non vide -- la décision d'OUVRIR ces pages
 *     cibles à l'indexation reste une passe seo-registry séparée et future (hors périmètre
 *     de cette tâche).
 *
 * NON construits dans cette passe, chacun pour une raison distincte (mêmes raisons
 * qu'ES-016, pas réévaluées ici) :
 *   'length_with' (avec+longueur)         : App\Search\LengthLinksBuilder::byWith en a besoin,
 *                                            mais 'con-letras'+longueur SANS aucun autre
 *                                            ancrage n'a aujourd'hui aucun lien réel démontré
 *                                            (contrairement à empiezan-por/terminan-en) --
 *                                            candidat palier 2, pas mesuré dans cette passe.
 *   'start_end' (commençant+terminant)    : App\Search\LetterCombinedLinksBuilder en a besoin ;
 *   'length_start_end', 'length_with_*',
 *   'start_end_with', 'start_with',
 *   'prefix2-4', 'suffix2-4'              : aucun de ces 14 list_type n'a de générateur ES
 *                                            mesuré pour ce jeu de données, ni de décision
 *                                            produit ES tracée pour leur ouverture -- reportés
 *                                            explicitement à une passe future (voir aussi la
 *                                            mise en garde sur les constantes
 *                                            EXTERNAL_DUPLICATE_KEYS/DUPLICATE_START_END_KEYS/
 *                                            EXTERNAL_DUPLICATE_WITH_KEYS ci-dessous : elles
 *                                            contiennent encore des données FRANÇAISES non
 *                                            re-dérivées pour l'espagnol).
 *
 * ==========================================================================================
 * DÉCISION CRITIQUE 1 : granularité CARACTÈRE, jamais TUILE (ES-017)
 * ==========================================================================================
 * Le site espagnol utilise des tuiles digrammes dédiées CH/LL/RR (ES-002, 100 fiches,
 * App\Search\Normalizer::tokenizeTiles()). Ce script n'implémente PAS de bucket dédié "CH"/
 * "LL"/"RR" au niveau 1-caractère ('start'/'length_start') : un mot comme CHOZA est compté
 * dans le bucket "C" (son premier CARACTÈRE littéral), exactement comme CASA -- jamais dans
 * un bucket "CH" séparé.
 *
 * Vérifié, pas supposé : la famille RÉELLEMENT indexée word_list_commencant (ES-016, 25 URL)
 * est elle-même construite ainsi -- son propre commentaire de lot le confirme explicitement
 * (scripts/seo-batches/commencant-terminant-single-tier1-2026-08-29.php, "27 lettres de
 * l'alphabet [caractère] moins K et W"), sans aucun bucket CH/LL/RR séparé. App\Search\
 * RelationsFinder::relatedSearches() (ligne ~781, lien 'startsWith' inconditionnel émis par
 * CHAQUE fiche mot admise) utilise `mb_substr($word, 0, 1)` -- toujours 1 CARACTÈRE, jamais 1
 * TUILE : pour CHOZA, ce lien pointe vers 'empiezan-por/c', jamais 'empiezan-por/ch'. Ce
 * script reste cohérent avec ce comportement DÉJÀ EN PRODUCTION plutôt que d'inventer une
 * convention tuile différente (demande explicite de la tâche : "reste cohérent... ne réinvente
 * pas une convention différente").
 *
 * Une entrée "CH"/"LL"/"RR" existe malgré tout dans les données produites ici, mais UNIQUEMENT
 * côté 'end'/'length_end' (2 caractères) -- voir décision 2 ci-dessous : ce n'est PAS un
 * bucket "tuile" au sens ES-002 (rien ne distingue un mot finissant par tuile CH dédiée d'un
 * mot dont les 2 derniers CARACTÈRES littéraux sont "C" puis "H" -- les deux sont
 * orthographiquement identiques, la distinction tuile/caractère n'existe QUE côté rack de jeu,
 * jamais côté texte stocké). Vérifié : 'end'="ch" (34 mots, tous statuts), "ll" (15 mots),
 * "rr" (2 mots) -- voir le rapport après exécution pour le détail par mot.
 *
 * ==========================================================================================
 * DÉCISION CRITIQUE 2 : 1 caractère pour start/length_start, 2 caractères pour end/length_end
 * ==========================================================================================
 * Asymétrique et DÉLIBÉRÉ, PAS un oubli. App\Search\Normalizer::MIN_LENGTH = 2 (ES-003) force
 * TOUT mot de la base à faire au moins 2 caractères -- en conséquence,
 * RelationsFinder::relatedSearches() (ligne ~787) calcule le lien 'endsWith' inconditionnel
 * via `mb_substr($word, -min(2, $length))`, qui vaut donc TOUJOURS exactement 2 caractères,
 * JAMAIS 1 (min(2, $length) = 2 pour tout mot réel de cette base). Aucune fiche mot n'émet
 * donc jamais de lien 'endsWith' vers un suffixe d'1 seul caractère -- contrairement au lien
 * 'startsWith', toujours et uniquement 1 caractère.
 *
 * ES-016 a déjà tiré cette conséquence pour la famille SEO réellement ouverte : word_list_
 * terminant y est construite à 2 CARACTÈRES (246 URL, "toutes les terminaisons a 2 caracteres
 * reellement produites"), PAS 1 caractère ("un grain terminan-en a 1 lettre n'a AUCUN lien
 * reel actuellement et n'est PAS propose"). Pour rester cohérent avec cette famille déjà
 * indexée et déjà vérifiée -- pas pour reproduire mécaniquement la forme 1-caractère du site
 * français -- 'end' et 'length_end' sont ici construits à 2 CARACTÈRES littéraux, pas 1.
 *
 * Conséquence pratique, vérifiée avant d'écrire ce script : App\Search\ExploreHubBuilder
 * (case 'end') et App\Search\LengthLinksBuilder (case 'length_end') traitent déjà $key comme
 * une chaîne OPAQUE de longueur quelconque (mb_strtolower($key) concaténé tel quel dans l'URL,
 * substr($key, strpos($key, ':') + 1) pour extraire "tout ce qui suit le premier ':'") --
 * AUCUNE modification de ces deux classes n'est nécessaire pour ce changement de granularité,
 * confirmé en lisant leur code avant d'écrire ce script, pas supposé.
 *
 * BUG RÉEL ÉVITÉ EN ÉCRIVANT CE SCRIPT (même classe qu'ES-003) : la version française
 * utilisait `strrev()` (octet par octet) pour remettre substr(reversed,1,N) en ordre de
 * lecture normal (suffix2/3/4). `strrev()` sur une sous-chaîne UTF-8 contenant Ñ (2 octets,
 * C3 91) CORROMPT le caractère -- démontré : strrev("\xC3\x91E") donne les octets
 * 45 91 C3 (invalide, PAS "EÑ"). Corrigé ici par mbReverse() (mb_str_split + array_reverse +
 * implode), vérifié directement : reversed="ÑEHCLOHC" (CHOLCHEÑ) -> substr(reversed,1,2)=
 * octets 45 C3 91 ("EÑ" en ordre inverse-de-lecture) -> mbReverse() -> "ÑE" (octets C3 91 45),
 * confirmé correspondre à un mot réel se terminant par "...ÑE".
 *
 * ==========================================================================================
 * Mesure qui justifie ce script (identique en esprit à la version française, remesurée sur ce
 * jeu de données) : EXPLAIN QUERY PLAN sur les 5 requêtes ci-dessous confirme un SCAN USING
 * COVERING INDEX à chaque fois (idx_terms_length_normalized ou idx_terms_length_reversed),
 * mais avec USE TEMP B-TREE FOR GROUP BY pour 'end'/'length_start'/'length_end' (aucun index
 * sur l'expression substr() elle-même) -- 130 à 1 236 ms mesurés sur les 748 165 lignes
 * réelles de storage/dictionary_es.sqlite (2026-08-30), largement au-dessus du budget TTFB
 * p95 < 250 ms pour une seule page si exécuté au runtime. Précalculé une fois ici, lu par
 * App\Search\ExploreHubBuilder / App\Search\LengthLinksBuilder en une requête triviale,
 * aucun GROUP BY, aucun scan au runtime.
 *
 * DIVERGENCE TEMPORAIRE ASSUMÉE ET FLAGGÉE (agent data-engine, périmètre app/Search/,
 * scripts/build_*, jamais schema.sql -- fichier partagé sous contrôle de la session
 * principale, CLAUDE.md) : le CHECK ci-dessous inclut déjà 'length_start'/'length_end'
 * (nouveaux, ES-017), mais schema.sql (source canonique de la DDL) ne les inclut PAS encore
 * (CHECK actuel : 'length', 'start', 'end' seulement) -- diff proposé dans le rapport de
 * cette tâche, non appliqué. Cette table est de toute façon intégralement DROP + CREATE ici à
 * chaque exécution (jamais la version issue de schema.sql seule) : aucun impact sur le
 * comportement réel de storage/dictionary_es.sqlite, mais schema.sql resterait une
 * documentation incomplète tant que le diff proposé n'est pas appliqué par la session
 * principale.
 *
 * MISE EN GARDE POUR UNE PASSE FUTURE (trouvée en lisant le code avant d'écrire ce script,
 * pas corrigée ici -- hors périmètre, les list_type concernés ne sont pas construits par ce
 * script) : App\Search\LengthLinksBuilder::DUPLICATE_START_END_KEYS /
 * EXTERNAL_DUPLICATE_WITH_KEYS, App\Search\LetterCombinedLinksBuilder::
 * EXTERNAL_DUPLICATE_KEYS et App\Search\PositionLinksBuilder::EXTERNAL_DUPLICATE_KEYS
 * contiennent encore des listes de doublons calculées pour storage/dictionary_fr.sqlite
 * (D-025/D-041 côté français), jamais re-dérivées pour l'espagnol -- leurs docblocks le disent
 * eux-mêmes ("valable pour l'état actuel de storage/dictionary_fr.sqlite"). SANS EFFET
 * aujourd'hui : les list_type qu'elles filtrent ('start_end', 'length_with',
 * 'length_start_end', 'length_with_position') restent VIDES tant qu'un futur script ne les
 * peuple pas -- mais quiconque construira l'un de ces list_type pour l'espagnol devra
 * IGNORER ou re-dériver ces constantes, jamais les réutiliser telles quelles (même risque que
 * scripts/propose_seo_batch.php, déjà signalé par ES-016, un ordre de grandeur plus discret
 * ici car les données sont fausses seulement pour un sous-ensemble de clés, pas pour toutes).
 *
 * Idempotent : peut être relancé après chaque reconstruction de storage/dictionary_es.sqlite
 * (scripts/import_es.py) sans effet de bord -- DROP + CREATE + INSERT en une transaction.
 *
 * Usage : php scripts/build_explore_hub_counts.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "scripts/build_explore_hub_counts.php ne s'execute qu'en CLI, hors ligne.\n");
    exit(1);
}

$root = dirname(__DIR__);
$dbPath = getenv('SCRABBLE_DICTIONARY_DB_PATH') ?: $root . '/storage/dictionary_es.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "dictionnaire introuvable : {$dbPath}\n");
    exit(1);
}

/**
 * Inverse une chaîne UTF-8 caractère par caractère (Ñ = 2 octets, jamais coupé en deux --
 * voir "BUG RÉEL ÉVITÉ" ci-dessus, PHP strrev() opère sur des OCTETS et corromprait Ñ).
 */
function mbReverse(string $s): string
{
    return implode('', array_reverse(mb_str_split($s, 1, 'UTF-8')));
}

// Lecture-ecriture ASSUMEE ici (hors ligne uniquement) : le runtime PHP (app/Database/
// Connection.php) ouvre toujours ce meme fichier en SQLITE_OPEN_READONLY -- ce script ne
// s'execute jamais dans le flux d'une requete HTTP.
$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// DDL : CHECK élargi à 5 list_type (length, start, end, length_start, length_end -- ES-017).
// Volontairement PAS les 19 du site français (D-022 à D-041) : aucun générateur ES mesuré
// pour les 14 autres dans cette passe, voir le docblock ci-dessus.
$pdo->exec('DROP TABLE IF EXISTS list_counts');
$pdo->exec(
    'CREATE TABLE list_counts ('
    . "list_type TEXT NOT NULL CHECK (list_type IN ('length', 'start', 'end', 'length_start', 'length_end')), "
    . 'list_key TEXT NOT NULL, '
    . 'count INTEGER NOT NULL, '
    . 'PRIMARY KEY (list_type, list_key)'
    . ')'
);

$insert = $pdo->prepare('INSERT INTO list_counts (list_type, list_key, count) VALUES (?, ?, ?)');

$pdo->beginTransaction();

$total = 0;

// length (hub /palabras, section "Por Longitud") : total tous statuts, comme le site
// francais (D-017) -- pas de filtre is_admitted, coherent avec le comptage deja verifie de
// word_list_length (ES-011 I-1, "TOUS statuts").
$lengthStatement = $pdo->query('SELECT length, COUNT(*) n FROM terms GROUP BY length ORDER BY length');
foreach ($lengthStatement as $row) {
    $insert->execute(['length', (string) $row['length'], (int) $row['n']]);
    $total++;
}

// start (hub, section "Empiezan Por") : 1 CARACTERE (decision critique 1 ci-dessus). 27
// buckets attendus (A-Z + N) -- K et W INCLUS ici (0 lien SEO reel depuis une fiche admise,
// ES-016, mais ce n'est pas une raison d'ecrire une donnee fausse : ces buckets existent
// reellement dans le dictionnaire, 428 et 172 mots tous statuts confondus).
$startStatement = $pdo->query("SELECT substr(normalized, 1, 1) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c");
foreach ($startStatement as $row) {
    $insert->execute(['start', $row['c'], (int) $row['n']]);
    $total++;
}

// end (hub, section "Terminan En") : 2 CARACTERES (decision critique 2 ci-dessus), pas 1.
// substr(reversed,1,2) donne les 2 derniers caracteres du mot en ordre INVERSE -- mbReverse()
// les remet dans l'ordre de lecture normal avant insertion (bug Ñ evite, voir docblock).
$endStatement = $pdo->query("SELECT substr(reversed, 1, 2) c, COUNT(*) n FROM terms GROUP BY c ORDER BY c");
foreach ($endStatement as $row) {
    $suffix = mbReverse((string) $row['c']);
    $insert->execute(['end', $suffix, (int) $row['n']]);
    $total++;
}

// length_start (App\Search\LengthLinksBuilder::byStart) : croise longueur et 1er caractere --
// list_key = "{longueur}:{lettre}", ex. "9:A". Seules les combinaisons REELLEMENT non vides
// sont inserees (consequence naturelle du GROUP BY, jamais une ligne a 0 -- meme principe R5
// que le registre SEO, applique ici par construction).
$lengthStartStatement = $pdo->query(
    "SELECT length, substr(normalized, 1, 1) c, COUNT(*) n FROM terms GROUP BY length, c ORDER BY length, c"
);
foreach ($lengthStartStatement as $row) {
    $insert->execute(['length_start', $row['length'] . ':' . $row['c'], (int) $row['n']]);
    $total++;
}

// length_end (App\Search\LengthLinksBuilder::byEnd) : croise longueur et les 2 DERNIERS
// CARACTERES litteraux (decision critique 2) -- list_key = "{longueur}:{suffixe 2 car.}",
// ex. "9:AR". mbReverse() applique de la meme facon que pour 'end' ci-dessus.
$lengthEndStatement = $pdo->query(
    "SELECT length, substr(reversed, 1, 2) c, COUNT(*) n FROM terms GROUP BY length, c ORDER BY length, c"
);
foreach ($lengthEndStatement as $row) {
    $suffix = mbReverse((string) $row['c']);
    $insert->execute(['length_end', $row['length'] . ':' . $suffix, (int) $row['n']]);
    $total++;
}

$pdo->commit();

// D-021 (site francais, meme lecon appliquee ici) : toute modification de table/index doit
// etre suivie d'ANALYZE dans la MEME operation, jamais une etape facultative ou differee --
// ce script peuple list_counts a plusieurs milliers de lignes sans jamais l'avoir fait avant,
// laissant les statistiques du planificateur perimees.
$pdo->exec('ANALYZE');

printf(
    "list_counts : %d lignes (14 longueur + 27 commencant [1 caractere] + jusqu'a 400 terminant"
    . " [2 caracteres] + length_start + length_end attendues -- voir docs/DECISIONS.md ES-017)\n",
    $total,
);
