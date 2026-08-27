#!/usr/bin/env python3
"""Construit storage/dictionary_fr.sqlite depuis les trois sources françaises.

Hors ligne uniquement (D-007). La base est recréée intégralement à chaque
exécution : elle n'est jamais mise à jour en place. Deux exécutions successives
produisent des rapports au sha256 identique — c'est un test de la porte Phase 0.

Ordre de fusion, conforme à docs/03_SOURCES_ET_IMPORT_DATA.md §4 :

    1. formes Kartmaan filtrées      is_french = 1
    1 bis. formes hbenbel filtrées   is_french = 1   (D-014)
    2. ODS8                          is_ods8 = 1, is_ods9 = 1, is_french = 1
    3. retraits ODS9                 is_ods9 = 0
    4. keep_overrides ODS9           is_ods9 = 1
    5. additions ODS9                is_ods9 = 1, is_ods8 = 0 si absent d'ODS8
    6. score, length, signature, reversed
    7. index, ANALYZE, VACUUM, integrity_check
    8. rapports

Usage :
    python scripts/import_fr.py [--dry-run]
"""

from __future__ import annotations

import argparse
import ast
import bisect
import csv
import hashlib
import json
import sqlite3
import sys
from collections import Counter, defaultdict
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib.normalize import (  # noqa: E402
    MAX_LENGTH,
    MIN_LENGTH,
    is_valid,
    normalize,
    reverse,
    score,
    signature,
)

ROOT = Path(__file__).resolve().parents[1]
ODS8_PATH = ROOT / "data" / "raw" / "ods8.json"
KARTMAAN_PATH = ROOT / "data" / "raw" / "french_dict.db"
HBENBEL_DIR = ROOT / "data" / "raw" / "hbenbel"
HBENBEL_FILES = ("dictionary.csv", "adj.csv", "noun.csv", "verb.csv", "adv.csv")
ODS9_PATH = ROOT / "data" / "ods9" / "ods9_patch.sqlite"
SCHEMA_PATH = ROOT / "schema.sql"
TARGET_PATH = ROOT / "storage" / "dictionary_fr.sqlite"
REPORTS = ROOT / "reports"

# pos Kartmaan écartés : noms propres et locutions.
# Le filtre s'applique LIGNE par ligne, pas forme par forme — une forme est
# retenue dès qu'au moins une de ses lignes passe. « école » porte une ligne NP
# et une ligne N : elle doit être conservée.
POS_PROPER_NOUN = "NP"

# D-018 — nature grammaticale et genre (Kartmaan) + liens de conjugaison (hbenbel verb.csv).
#
# Réduction des 38 valeurs brutes de pos Kartmaan vers un jeu fermé de 9 codes. Les lignes
# NP et loc* (POS_PROPER_NOUN et tout pos commençant par "loc", insensible à la casse) sont
# déjà écartées comme pour le filtre d'import français ci-dessus — même règle, appliquée ici
# à la sélection de la nature grammaticale plutôt qu'à la rétention d'une forme. flex-suf
# n'a pas besoin d'entrée : ses formes commencent toutes par "-" (suffixes, ex. "-ette"),
# déjà rejetées par rejection_rule() ("trait d'union"), donc jamais dans `terms`.
POS_MAP: dict[str, str] = {
    "N": "N", "flex-nom": "N",
    "V": "V", "Vaux": "V", "flex-verb": "V",
    "Adj": "Adj", "adj-num": "Adj", "adj-pos": "Adj", "adj-int": "Adj", "adj-excl": "Adj",
    "flex-adj": "Adj",
    "Adv": "Adv", "flex-adv": "Adv",
    "interj": "Interj", "flex-interj": "Interj",
    "pronom": "Pronom", "pronom-pers": "Pronom", "pronom-int": "Pronom",
    "pronom-pos": "Pronom", "pronom-rel": "Pronom", "flex-pronom": "Pronom",
    "prép": "Prep",
    "conj": "Conj", "conj-coord": "Conj", "flex-conj": "Conj",
    "art-part": "Art",
}
POS_VALUES = ("N", "V", "Adj", "Adv", "Pronom", "Prep", "Conj", "Interj", "Art")
GENDER_VALUES = ("m", "f", "e")

HBENBEL_VERB_PATH = HBENBEL_DIR / "verb.csv"

# Association tags hbenbel -> personne, restreinte aux six combinaisons personne/nombre
# simples. "reflexive" est explicitement exclu de toute classification (voir
# classify_conjugation()) : les quelques verbes purement pronominaux du fichier source
# produisent des formes à espace ("se laver"), déjà hors de `terms` par construction.
TENSE_PERSON_TAGS: dict[frozenset[str], str] = {
    frozenset({"first-person", "singular"}): "1s",
    frozenset({"second-person", "singular"}): "2s",
    frozenset({"third-person", "singular"}): "3s",
    frozenset({"first-person", "plural"}): "1p",
    frozenset({"second-person", "plural"}): "2p",
    frozenset({"third-person", "plural"}): "3p",
}

# Sélection représentative (pas le paradigme complet, ~50 formes/verbe dans la source) :
# présent, futur simple, imparfait (indicatif, 6 personnes chacun), participe présent,
# participe passé (forme de base seule, sans accord — hors périmètre, D-018).
TENSE_VALUES = ("present", "future", "imperfect", "participle_present", "participle_past")

# Seuil d'exclusion d'un lemme non fiable : nombre de formes s'appariant sur LUI-MÊME par
# plus-long-préfixe-commun (own_count), tous temps confondus, avant curation. Mesuré sur
# les 6 697 infinitifs distincts : médiane 50, moyenne 51,1. Les verbes suppletifs (ÊTRE,
# AVOIR, ALLER, DEVOIR, VALOIR, VOIR, ASSEOIR/RASSEOIR, SEOIR, GÉSIR, FÉRIR, la famille
# TENIR/VENIR/COURIR/CUIRE à radical alterné...) tombent nettement sous ce plancher (0 à 6
# formes propres) parce que leurs formes conjuguées ne partagent pas de préfixe long avec
# leur infinitif — mesure : 281 lemmes exclus sur 6 697 avec ce seuil. Décision D-018 :
# aucune donnée fausse plutôt qu'un lien de conjugaison erroné, même au prix de priver les
# verbes les plus courants de cette section dans cette passe.
VERB_LEMMA_MIN_RELIABLE_FORMS = 20


def sha256_of(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1 << 20), b""):
            digest.update(chunk)
    return digest.hexdigest()


def rejection_rule(form: str, pos: str | None, normalized: str) -> str | None:
    """Renvoie la règle de rejet, ou None si la forme est retenue.

    L'ordre des tests est significatif : il détermine sous quelle règle un rejet
    est comptabilisé quand plusieurs s'appliquent.
    """
    if pos == POS_PROPER_NOUN:
        return "nom propre (pos = NP)"
    if pos and pos.lower().startswith("loc"):
        return "locution (pos = Loc*/loc-*)"
    if " " in form:
        return "espace"
    if "-" in form:
        return "trait d'union"
    if "'" in form or "’" in form:
        return "apostrophe"
    if any(ch.isdigit() for ch in form):
        return "chiffre"
    if len(normalized) < MIN_LENGTH:
        return "moins de %d lettres" % MIN_LENGTH
    if len(normalized) > MAX_LENGTH:
        return "plus de %d lettres (injouable sur un plateau)" % MAX_LENGTH
    if not is_valid(normalized):
        return "caractere hors A-Z apres normalisation"
    return None


def load_ods8() -> tuple[list[str], dict[str, int]]:
    """Charge ODS8 et écarte ce qui ne peut pas être posé sur un plateau.

    Le fichier source n'est pas un ODS8 pur : il compte 411 430 entrées contre
    402 325 pour l'ODS8 publié. L'écart est exactement les formes de plus de 15
    lettres, des conjugaisons générées absentes de l'ODS. Les retenir
    reviendrait à répondre « admis au Scrabble » à tort (D-010).

    Le compte retenu doit donc valoir 402 325 : c'est un contrôle, pas une
    conséquence subie.
    """
    with ODS8_PATH.open(encoding="utf-8") as handle:
        words = json.load(handle)["words"]

    kept: list[str] = []
    stats = {"ods8_source_rows": len(words), "ods8_dropped_over_15_letters": 0}
    for word in words:
        if len(word) > MAX_LENGTH:
            stats["ods8_dropped_over_15_letters"] += 1
            continue
        if not is_valid(word):
            raise SystemExit("ODS8 : forme non conforme a ^[A-Z]{2,15}$ : %r" % word)
        kept.append(word)

    if len(kept) != 402_325:
        raise SystemExit(
            "ODS8 : %d formes retenues, 402 325 attendues — la source a change"
            % len(kept)
        )
    return kept, stats


def load_ods9() -> dict[str, list[str]]:
    connection = sqlite3.connect("file:%s?mode=ro" % ODS9_PATH.as_posix(), uri=True)
    try:
        integrity = connection.execute("PRAGMA integrity_check").fetchone()[0]
        if integrity != "ok":
            raise SystemExit("ods9_patch.sqlite : integrity_check = %s" % integrity)
        return {
            table: sorted(
                row[0] for row in connection.execute(
                    "SELECT normalized FROM %s" % table
                )
            )
            for table in ("additions", "removals", "keep_overrides")
        }
    finally:
        connection.close()


def load_kartmaan() -> tuple[
    dict[str, set[str]], Counter, list[tuple[str, str, str]], dict[str, int]
]:
    """Retourne (formes retenues, rejets, échantillon, volumétrie de la source).

    Le parcours est trié par (forme, id) pour rester déterministe quel que soit
    l'ordre physique des pages SQLite.

    Attention aux unités : Kartmaan se compte en LIGNES (une ligne par sens),
    une même forme pouvant en avoir plusieurs. Les rejets renvoyés sont donc des
    lignes, pas des formes — ils ne sont additionnables avec aucune autre source.
    """
    connection = sqlite3.connect("file:%s?mode=ro" % KARTMAAN_PATH.as_posix(), uri=True)
    kept: dict[str, set[str]] = defaultdict(set)
    rejected: Counter = Counter()
    samples: list[tuple[str, str, str]] = []
    seen_rejected: set[tuple[str, str]] = set()
    stats = {"source_rows": 0, "source_distinct_forms": 0}
    try:
        stats["source_rows"] = connection.execute(
            "SELECT COUNT(*) FROM mots"
        ).fetchone()[0]
        stats["source_distinct_forms"] = connection.execute(
            "SELECT COUNT(DISTINCT forme) FROM mots"
        ).fetchone()[0]
        query = "SELECT forme, pos FROM mots ORDER BY forme, id"
        for form, pos in connection.execute(query):
            normalized = normalize(form)
            rule = rejection_rule(form, pos, normalized)
            if rule is not None:
                rejected[rule] += 1
                key = (rule, form)
                if key not in seen_rejected:
                    seen_rejected.add(key)
                    samples.append((rule, form, pos or ""))
                continue
            kept[normalized].add(form)
    finally:
        connection.close()
    samples.sort()
    return kept, rejected, samples, stats


def load_hbenbel() -> tuple[
    dict[str, set[str]], Counter, list[tuple[str, str, str]], dict[str, int]
]:
    """Charge le dictionnaire hbenbel (D-014).

    hbenbel n'a pas d'étiquette `NP` : ses noms propres et ses sigles sont noyés
    dans noun.csv. La CASSE de la forme d'origine est le seul marqueur
    disponible — `Aberdonien`, `Ewok`, `ADN`, `AVC` commencent par une
    majuscule, un nom commun français jamais. Cette règle implémente donc les
    exclusions « noms propres » et « sigles » exigées par docs/03 §5.

    Attention aux unités : hbenbel se compte en FORMES distinctes, Kartmaan en
    lignes. Les deux compteurs de rejet ne s'additionnent pas.
    """
    forms: dict[str, set[str]] = defaultdict(set)
    rejected: Counter = Counter()
    samples: list[tuple[str, str, str]] = []
    seen_rejected: set[tuple[str, str]] = set()
    origins: dict[str, set[str]] = defaultdict(set)

    for name in HBENBEL_FILES:
        path = HBENBEL_DIR / name
        category = path.stem
        with path.open(encoding="utf-8", newline="") as handle:
            if category == "dictionary":
                # Liste plate, une forme par ligne, sans en-tête.
                raw_forms = (line.strip() for line in handle)
            else:
                raw_forms = (
                    (row.get("form") or "").strip() for row in csv.DictReader(handle)
                )
            for form in raw_forms:
                if form:
                    origins[form].add(category)

    for form in sorted(origins):
        normalized = normalize(form)
        rule = rejection_rule(form, None, normalized)
        if rule is None and form[:1].isupper():
            rule = "majuscule initiale (nom propre ou sigle)"
        if rule is not None:
            rejected[rule] += 1
            key = (rule, form)
            if key not in seen_rejected:
                seen_rejected.add(key)
                samples.append((rule, form, "+".join(sorted(origins[form]))))
            continue
        forms[normalized].add(form)

    samples.sort()
    stats = {
        "source_distinct_forms": len(origins),
        "source_rows": len(origins),
    }
    return forms, rejected, samples, stats


def load_pos_gender(term_keys: set[str]) -> tuple[dict[str, dict], dict[str, int]]:
    """Nature grammaticale et genre (D-018), depuis Kartmaan, pour les formes déjà
    retenues dans `terms` (term_keys = clés normalisées de build_terms(), appelé avant
    cette fonction).

    Parcourt les lignes Kartmaan triées par id (ordre d'insertion source) : c'est la seule
    trace de "sens primaire probable" disponible dans cette source, exactement la même
    convention de déterminisme que load_kartmaan() (ORDER BY forme, id). Une ligne NP ou
    loc* est exclue de la sélection de pos exactement comme elle l'est de la rétention de
    forme (rejection_rule ci-dessus) — un sens proscrit ne doit pas polluer la nature
    grammaticale d'un terme retenu par une autre ligne.

    gender est calculé indépendamment de pos/pos_secondary : première ligne (ordre id) où
    pos == 'N' et gender est une valeur non vide, même si 'N' n'est pas le sens retenu comme
    primaire ou secondaire (couvre TABLE : pos primaire V venu de "flex-verb", genre f
    rapporté quand même depuis le sens N).
    """
    connection = sqlite3.connect("file:%s?mode=ro" % KARTMAAN_PATH.as_posix(), uri=True)
    pos_by_term: dict[str, list[str]] = defaultdict(list)
    gender_by_term: dict[str, str] = {}
    stats = {"kartmaan_pos_lines_considered": 0, "kartmaan_pos_lines_matched": 0}
    try:
        query = "SELECT forme, pos, gender FROM mots ORDER BY id"
        for form, pos, gender in connection.execute(query):
            if not pos or pos == POS_PROPER_NOUN or pos.lower().startswith("loc"):
                continue
            canonical = POS_MAP.get(pos)
            if canonical is None:
                continue
            stats["kartmaan_pos_lines_considered"] += 1
            normalized = normalize(form)
            if normalized not in term_keys:
                continue
            stats["kartmaan_pos_lines_matched"] += 1
            if canonical not in pos_by_term[normalized]:
                pos_by_term[normalized].append(canonical)
            if canonical == "N" and gender in GENDER_VALUES and normalized not in gender_by_term:
                gender_by_term[normalized] = gender
    finally:
        connection.close()

    result = {
        normalized: {
            "pos": pos_list[0],
            "pos_secondary": pos_list[1] if len(pos_list) > 1 else None,
            "gender": gender_by_term.get(normalized),
        }
        for normalized, pos_list in pos_by_term.items()
    }

    stats["terms_with_pos"] = len(result)
    stats["terms_with_pos_secondary"] = sum(1 for v in result.values() if v["pos_secondary"])
    stats["terms_with_gender"] = len(gender_by_term)
    stats["terms_with_gender_by_value"] = dict(sorted(Counter(gender_by_term.values()).items()))
    return result, stats


def classify_conjugation(tags: list[str]) -> tuple[str, str | None] | None:
    """Réduit une liste de tags hbenbel vers (temps, personne) parmi TENSE_VALUES, ou None
    si la combinaison ne fait pas partie de la sélection représentative D-018 (voir
    TENSE_VALUES) ou porte le tag "reflexive" (verbes pronominaux, hors périmètre)."""
    tag_set = set(tags)
    if "reflexive" in tag_set:
        return None

    person = None
    for combo, code in TENSE_PERSON_TAGS.items():
        if combo <= tag_set:
            person = code
            break

    if "present" in tag_set and "indicative" in tag_set and person is not None:
        return ("present", person)
    if "future" in tag_set and "indicative" in tag_set and person is not None:
        return ("future", person)
    if "imperfect" in tag_set and "indicative" in tag_set and person is not None:
        return ("imperfect", person)
    if "participle" in tag_set and "past" in tag_set and not ({"masculine", "feminine"} & tag_set):
        return ("participle_past", None)
    if "participle" in tag_set and "present" in tag_set:
        return ("participle_present", None)
    return None


# Terminaisons régulières du premier groupe (infinitif en -ER), seul groupe suffisamment
# mécanique pour être vérifié sans base de connaissance externe (radical + terminaison
# fixe, D-018). "GER" : les verbes en -ger insèrent un "e" de son avant une terminaison
# commençant par a/o (mangeons, mangeais, mangeant — jamais mangons/mangais/mangant).
PRESENT_ER_ENDINGS = {"1s": "E", "2s": "ES", "3s": "E", "1p": "ONS", "2p": "EZ", "3p": "ENT"}
FUTURE_ENDINGS = {"1s": "AI", "2s": "AS", "3s": "A", "1p": "ONS", "2p": "EZ", "3p": "ONT"}
IMPERFECT_ENDINGS = {"1s": "AIS", "2s": "AIS", "3s": "AIT", "1p": "IONS", "2p": "IEZ", "3p": "AIENT"}


def expected_regular_er_form(lemma: str, tense: str, person: str | None) -> str | None:
    """Forme régulière attendue pour un verbe du premier groupe (infinitif en -ER), pour
    les temps/personnes de la sélection D-018. None si le lemme n'est pas un verbe -ER, ou
    si (tense, person) sort du cadre couvert ici — dans ce cas, aucune validation
    morphologique supplémentaire n'est appliquée par l'appelant.

    Validation ajoutée AU-DELA du seuil own_count (VERB_LEMMA_MIN_RELIABLE_FORMS) : ce
    dernier détecte un LEMME entièrement peu fiable (ÊTRE, AVOIR...), mais ne protège pas
    un lemme par ailleurs fiable contre un appariement PONCTUEL erroné avec une forme d'un
    AUTRE verbe alphabétiquement proche. Mesuré sur la base réelle : SOMMES (forme de
    ÊTRE) s'appariait par plus-long-préfixe-commun à SOMMER (verbe -ER régulier, fiable
    par ailleurs, own_count élevé — jamais exclu par le seuil seul) ; SONT à SONORISER ;
    VAIS à VAIRONNER — trois cas concrets fermés par cette fonction, puisque "sommes"
    (attendu : SOMMONS), "sont" (attendu : SONORISENT) et "vais" (attendu : VAIRONNE) ne
    correspondent à AUCUNE conjugaison régulière possible de ces lemmes.

    Ne peut que RETIRER une ligne déjà candidate (un désaccord renvoie une forme attendue
    différente de la forme réelle, jamais l'inverse) — jamais ajouter une donnée : échec
    sûr par construction. Les groupes -IR/-RE/-OIR restent protégés uniquement par le
    seuil own_count (résidu documenté, voir rapport D-018) : construire un vérificateur
    couvrant ces groupes, nettement plus irréguliers, est hors périmètre de cette passe.
    """
    if not lemma.endswith("ER"):
        return None

    stem = lemma[:-2]
    soft_g_insert = "E" if lemma.endswith("GER") else ""

    if tense == "present" and person in PRESENT_ER_ENDINGS:
        ending = PRESENT_ER_ENDINGS[person]
        return stem + (soft_g_insert if ending[:1] in ("O", "A") else "") + ending
    if tense == "future" and person in FUTURE_ENDINGS:
        return lemma + FUTURE_ENDINGS[person]
    if tense == "imperfect" and person in IMPERFECT_ENDINGS:
        ending = IMPERFECT_ENDINGS[person]
        return stem + (soft_g_insert if ending[:1] in ("O", "A") else "") + ending
    if tense == "participle_present" and person is None:
        return stem + soft_g_insert + "ANT"
    if tense == "participle_past" and person is None:
        return stem + "E"
    return None


def load_verb_forms(
    term_keys: set[str],
) -> tuple[list[tuple[str, str, str, str | None]], dict[str, int], list[str]]:
    """Liens de conjugaison (D-018), depuis data/raw/hbenbel/verb.csv.

    ATTENTION — correction d'une pré-vérification fausse (voir rapport BEFORE, consigné
    dans D-018) : ce fichier n'est PAS organisé en blocs contigus par verbe. Vérifié
    directement : sur les 362 461 lignes, la colonne "form" ne décroît JAMAIS d'une ligne à
    la suivante — c'est un tri alphabétique global sur "form", toutes formes de tous les
    verbes mélangées. Un parcours séquentiel avec "lemme courant = dernier marqueur
    ['infinitive'] vu" aurait mal attribué une fraction significative des formes dès que
    deux verbes partagent un radical alphabétique proche (ex. POSER/POSITIONNER).

    Reconstruction retenue : pour chaque forme conjuguée, recherche par dichotomie du ou
    des infinitifs connus partageant le plus long préfixe avec elle (propriété standard :
    le meilleur candidat par préfixe commun dans une liste triée est toujours le
    prédécesseur ou le successeur d'insertion). Un lemme dont trop peu de formes s'apparient
    sur lui-même (own_count < VERB_LEMMA_MIN_RELIABLE_FORMS) est un verbe suppletif détecté
    automatiquement (ÊTRE, AVOIR, ALLER...) — exclu entièrement plutôt que de risquer un
    lien faux, voir la constante pour la mesure complète.

    D-010 (plafond 15 lettres) : appliqué automatiquement par le filtre "normalized in
    term_keys" sur le lemme ET sur la forme — aucune forme ou lemme trop long ne peut être
    dans `terms`, donc ne peut jamais atteindre ce point.
    """
    infinitives_raw: list[str] = []
    rows: list[tuple[str, list[str]]] = []

    with HBENBEL_VERB_PATH.open(encoding="utf-8", newline="") as handle:
        reader = csv.reader(handle)
        next(reader, None)  # en-tête "form,tags"
        for row in reader:
            if len(row) < 2:
                continue
            form, tags_raw = row[0], row[1]
            if not tags_raw:
                continue  # expressions idiomatiques a tags vides ("poser un lapin,")
            tags = ast.literal_eval(tags_raw)
            if tags == ["infinitive"]:
                infinitives_raw.append(form)
            else:
                rows.append((form, tags))

    normalized_infinitives = sorted({normalize(f) for f in infinitives_raw})

    def best_match(query: str) -> tuple[list[str], int]:
        """Infinitif(s) au plus long prefixe commun avec `query`, et la longueur de ce
        prefixe. best_len <= 0 : aucun prefixe partage. len(best) > 1 : egalite (ambigu,
        appelant doit ignorer la ligne plutot que deviner)."""
        i = bisect.bisect_left(normalized_infinitives, query)
        candidates = []
        if i < len(normalized_infinitives):
            candidates.append(normalized_infinitives[i])
        if i > 0:
            candidates.append(normalized_infinitives[i - 1])

        best_len = -1
        best: list[str] = []
        for candidate in candidates:
            shared = 0
            for a, b in zip(query, candidate):
                if a == b:
                    shared += 1
                else:
                    break
            if shared > best_len:
                best_len = shared
                best = [candidate]
            elif shared == best_len and candidate not in best:
                best.append(candidate)
        return best, best_len

    own_count: Counter = Counter()
    curated: set[tuple[str, str, str, str | None]] = set()

    for form, tags in rows:
        normalized_form = normalize(form)
        if normalized_form not in term_keys:
            continue
        best, shared_len = best_match(normalized_form)
        if shared_len <= 0 or len(best) != 1:
            continue  # aucun prefixe partage, ou egalite ambigue -- ignore, ne devine pas
        lemma = best[0]
        if lemma not in term_keys:
            continue
        own_count[lemma] += 1
        classified = classify_conjugation(tags)
        if classified is None:
            continue
        tense, person = classified

        # Validation morphologique supplementaire (verbes -ER uniquement, voir
        # expected_regular_er_form()) : ferme les cas concrets SOMMES/SONT/VAIS observes
        # sur la base reelle (appariement fortuit avec un voisin -ER alphabetiquement
        # proche mais grammaticalement sans rapport). None => hors cadre couvert, aucune
        # verification (comportement inchange) ; une valeur qui ne correspond pas a la
        # forme reelle => ligne rejetee (echec sur, ne peut que retirer une ligne).
        expected = expected_regular_er_form(lemma, tense, person)
        if expected is not None and expected != normalized_form:
            continue

        curated.add((lemma, normalized_form, tense, person))

    excluded_lemmas = sorted(
        lemma for lemma in normalized_infinitives
        if own_count.get(lemma, 0) < VERB_LEMMA_MIN_RELIABLE_FORMS
    )
    excluded_set = set(excluded_lemmas)
    kept = sorted(row for row in curated if row[0] not in excluded_set)

    stats = {
        "hbenbel_verb_infinitive_markers": len(infinitives_raw),
        "hbenbel_verb_distinct_normalized_infinitives": len(normalized_infinitives),
        "hbenbel_verb_conjugated_rows_considered": len(rows),
        "verb_lemma_min_reliable_forms_threshold": VERB_LEMMA_MIN_RELIABLE_FORMS,
        "verb_lemmas_excluded_unreliable": len(excluded_lemmas),
        "verb_lemmas_retained": len({row[0] for row in kept}),
        "verb_forms_rows": len(kept),
        "verb_forms_distinct_forms": len({row[1] for row in kept}),
    }
    return kept, stats, excluded_lemmas


WORD_SENSES_CACHE_PATH = ROOT / "data" / "generated" / "word_senses_cache.jsonl"


def load_word_senses(
    term_keys: set[str],
) -> tuple[list[tuple[str, int, str, str | None, str, str]], dict[str, int]]:
    """Definitions lexicales (D-0XX, pilote 100 mots -- voir reports/definitions-nature-
    feasibility-audit.md et scripts/generate_word_senses.py pour le pipeline de generation).

    Lit data/generated/word_senses_cache.jsonl -- un fichier VERSIONNE (pas storage/,
    entierement gitignore), produit hors ligne par un appel LLM payant et NON deterministe.
    import_fr.py lui-meme reste deterministe : il ne fait AUCUN appel reseau/API ici, il ne
    fait QUE lire ce fichier deja stable, exactement comme il lit ods8.json/hbenbel/Kartmaan.

    Optionnel, contrairement aux autres sources (pas dans `required` de main()) : ce cache est
    actuellement un pilote partiel (99 mots sur 838 180), pas encore un lot complet -- une
    reconstruction sans ce fichier doit rester possible (table word_senses simplement vide),
    le rollout complet est une decision separee (voir le rapport d'audit, section 12).

    Meme garde d'integrite que verb_forms : toute ligne dont le terme n'est PAS dans term_keys
    est ecartee plutot que d'etre inseree en base (aucune reference vers un terme absent de
    `terms`, D-010 applique automatiquement).
    """
    stats = {
        "word_senses_cache_present": 0,
        "word_senses_terms_in_cache": 0,
        "word_senses_terms_matched": 0,
        "word_senses_rows": 0,
    }
    if not WORD_SENSES_CACHE_PATH.exists():
        return [], stats

    stats["word_senses_cache_present"] = 1
    rows: list[tuple[str, int, str, str | None, str, str]] = []
    with WORD_SENSES_CACHE_PATH.open(encoding="utf-8") as handle:
        for line in handle:
            line = line.strip()
            if not line:
                continue
            entry = json.loads(line)
            stats["word_senses_terms_in_cache"] += 1
            term = entry["term"]
            if term not in term_keys:
                continue
            stats["word_senses_terms_matched"] += 1
            for rank, sense in enumerate(entry["senses"], start=1):
                rows.append((
                    term,
                    rank,
                    sense["pos"],
                    sense.get("gender"),
                    sense["definition"],
                    sense["source"],
                ))

    stats["word_senses_rows"] = len(rows)
    return sorted(rows), stats


def build_terms(
    ods8: list[str],
    ods9: dict[str, list[str]],
    kartmaan: dict[str, set[str]],
    hbenbel: dict[str, set[str]],
) -> tuple[dict[str, dict], dict[str, int]]:
    """Applique l'ordre de fusion et renvoie (termes, compteurs d'effet)."""
    terms: dict[str, dict] = {}
    effects: dict[str, int] = {}

    # 1. Kartmaan — couche française.
    for normalized in kartmaan:
        terms[normalized] = {"is_french": 1, "is_ods8": 0, "is_ods9": 0}

    # 1 bis. hbenbel — complément français (D-014).
    created_by_hbenbel = 0
    for normalized in hbenbel:
        if normalized not in terms:
            terms[normalized] = {"is_french": 1, "is_ods8": 0, "is_ods9": 0}
            created_by_hbenbel += 1
    effects["hbenbel_forms_absent_from_kartmaan"] = created_by_hbenbel

    # 2. ODS8 — admis dans les deux éditions par défaut.
    #    Un mot admis est français par construction : is_french = 1 sans
    #    consulter les sources françaises, qui ne couvrent pas tout ODS8.
    created_by_ods8 = 0
    for word in ods8:
        entry = terms.get(word)
        if entry is None:
            entry = terms[word] = {"is_french": 1, "is_ods8": 0, "is_ods9": 0}
            created_by_ods8 += 1
        entry["is_ods8"] = 1
        entry["is_ods9"] = 1
    effects["ods8_rows_absent_from_french_sources"] = created_by_ods8

    # 3. Retraits ODS9.
    removed = 0
    for normalized in ods9["removals"]:
        entry = terms.get(normalized)
        if entry is not None and entry["is_ods9"] == 1:
            entry["is_ods9"] = 0
            removed += 1
    effects["ods9_removals_applied"] = removed

    # 4. keep_overrides — garde-fou explicite.
    #    Effet attendu : 0. Les 10 homographes sont disjoints des 64 retraits ;
    #    ils ne sont donc jamais passés à 0 par l'étape précédente. Un compteur
    #    non nul signale que les sources ont changé.
    restored = 0
    for normalized in ods9["keep_overrides"]:
        entry = terms.get(normalized)
        if entry is not None and entry["is_ods9"] == 0:
            entry["is_ods9"] = 1
            restored += 1
    effects["ods9_keep_overrides_applied"] = restored

    # 5. Additions ODS9.
    added_new = 0
    for normalized in ods9["additions"]:
        entry = terms.get(normalized)
        if entry is None:
            entry = terms[normalized] = {"is_french": 1, "is_ods8": 0, "is_ods9": 0}
            added_new += 1
        entry["is_ods9"] = 1
    effects["ods9_additions_creating_a_term"] = added_new

    return terms, effects


def write_database(
    terms: dict[str, dict],
    pos_gender: dict[str, dict],
    verb_forms: list[tuple[str, str, str, str | None]],
    word_senses: list[tuple[str, int, str, str | None, str, str]],
    metadata: dict[str, str],
) -> None:
    TARGET_PATH.parent.mkdir(parents=True, exist_ok=True)
    if TARGET_PATH.exists():
        TARGET_PATH.unlink()

    connection = sqlite3.connect(TARGET_PATH)
    try:
        connection.executescript(SCHEMA_PATH.read_text(encoding="utf-8"))
        rows = (
            (
                index,
                normalized,          # display_term = normalized, sans exception
                normalized,
                entry["is_french"],
                entry["is_ods8"],
                entry["is_ods9"],
                # is_admitted (D-022) : colonne derivee, jamais une source de verite
                # independante -- precalculee ici pour que le filtre "admis seulement" des
                # listes /mots/... reste indexable (voir schema.sql pour la mesure complete).
                1 if (entry["is_ods8"] or entry["is_ods9"]) else 0,
                score(normalized),
                len(normalized),
                signature(normalized),
                reverse(normalized),
                pos_gender.get(normalized, {}).get("pos"),
                pos_gender.get(normalized, {}).get("pos_secondary"),
                pos_gender.get(normalized, {}).get("gender"),
            )
            for index, (normalized, entry) in enumerate(sorted(terms.items()), start=1)
        )
        connection.executemany(
            "INSERT INTO terms (id, display_term, normalized, is_french, is_ods8,"
            " is_ods9, is_admitted, score, length, signature, reversed, pos, pos_secondary, gender)"
            " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            rows,
        )
        connection.executemany(
            "INSERT INTO verb_forms (lemma_normalized, form_normalized, tense, person)"
            " VALUES (?, ?, ?, ?)",
            verb_forms,
        )
        connection.executemany(
            "INSERT INTO word_senses (term_normalized, sense_rank, pos, gender, definition, source)"
            " VALUES (?, ?, ?, ?, ?, ?)",
            word_senses,
        )
        connection.executemany(
            "INSERT INTO build_metadata (key, value) VALUES (?, ?)",
            sorted(metadata.items()),
        )
        connection.commit()
        connection.execute("ANALYZE")
        connection.commit()
        connection.execute("VACUUM")
    finally:
        connection.close()


def write_csv(path: Path, header: list[str], rows) -> None:
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.writer(handle, lineterminator="\n")
        writer.writerow(header)
        writer.writerows(rows)


def write_json(path: Path, payload: dict) -> None:
    path.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
    )


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="analyse les sources et affiche le resume, sans rien ecrire",
    )
    args = parser.parse_args()

    required = [ODS8_PATH, KARTMAAN_PATH, ODS9_PATH, SCHEMA_PATH, HBENBEL_VERB_PATH]
    required += [HBENBEL_DIR / name for name in HBENBEL_FILES]
    for path in required:
        if not path.exists():
            raise SystemExit("source manquante : %s" % path)

    ods8, ods8_stats = load_ods8()
    ods9 = load_ods9()
    kartmaan, rejected, rejected_samples, km_stats = load_kartmaan()
    hbenbel, hb_rejected, hb_samples, hb_stats = load_hbenbel()
    terms, effects = build_terms(ods8, ods9, kartmaan, hbenbel)

    # D-018 — nature grammaticale, genre, liens de conjugaison. Appelé APRES build_terms() :
    # les deux fonctions filtrent leurs sources contre les clés normalisées déjà retenues
    # (term_keys), jamais l'inverse — ni pos/gender ni verb_forms ne peuvent créer un terme.
    term_keys = set(terms.keys())
    pos_gender, pos_stats = load_pos_gender(term_keys)
    verb_forms, verb_stats, verb_excluded_lemmas = load_verb_forms(term_keys)
    word_senses, word_senses_stats = load_word_senses(term_keys)

    # Les collisions se calculent sur l'union des formes sources : deux graphies
    # venues de sources différentes qui se rejoignent après normalisation sont
    # une fusion au même titre que deux graphies d'une même source.
    merged_forms: dict[str, set[str]] = defaultdict(set)
    for normalized, forms in kartmaan.items():
        merged_forms[normalized] |= forms
    for normalized, forms in hbenbel.items():
        merged_forms[normalized] |= forms
    collisions = {
        normalized: sorted(forms)
        for normalized, forms in merged_forms.items()
        if len(forms) > 1
    }

    status = Counter()
    for entry in terms.values():
        if entry["is_ods8"] and entry["is_ods9"]:
            status["ods8_and_ods9"] += 1
        elif entry["is_ods8"]:
            status["ods8_only"] += 1
        elif entry["is_ods9"]:
            status["ods9_only"] += 1
        else:
            status["french_non_ods"] += 1

    # Champs imposés par docs/03 §6. `french_source_rows` désigne la volumétrie
    # brute des sources françaises. Kartmaan se compte en lignes (un sens par
    # ligne), hbenbel en formes distinctes : les deux unités ne sont pas
    # additionnables, donc le total brut est exposé par source et jamais sommé.
    summary = {
        "french_source_rows": {
            "kartmaan_rows": km_stats["source_rows"],
            "kartmaan_distinct_forms": km_stats["source_distinct_forms"],
            "hbenbel_distinct_forms": hb_stats["source_distinct_forms"],
        },
        "french_rejected": {
            "kartmaan_rows": sum(rejected.values()),
            "hbenbel_forms": sum(hb_rejected.values()),
        },
        "kartmaan_distinct_normalized": len(kartmaan),
        "hbenbel_distinct_normalized": len(hbenbel),
        "french_distinct_normalized": len(merged_forms),
        "ods8_source_rows": ods8_stats["ods8_source_rows"],
        "ods8_dropped_over_15_letters": ods8_stats["ods8_dropped_over_15_letters"],
        "ods8_rows": len(ods8),
        "ods9_additions": len(ods9["additions"]),
        "ods9_removals": len(ods9["removals"]),
        "ods8_only": status["ods8_only"],
        "ods9_only": status["ods9_only"],
        "ods8_and_ods9": status["ods8_and_ods9"],
        "french_non_ods": status["french_non_ods"],
        "normalization_collisions": len(collisions),
        "terms_total": len(terms),
        "max_term_length": MAX_LENGTH,
        "merge_effects": effects,
        "rejections_by_rule_kartmaan": dict(sorted(rejected.items())),
        "rejections_by_rule_hbenbel": dict(sorted(hb_rejected.items())),
        # D-018 — nature grammaticale, genre, liens de conjugaison.
        "pos_gender": dict(sorted(pos_stats.items())),
        "verb_forms": dict(sorted(verb_stats.items())),
        "word_senses": dict(sorted(word_senses_stats.items())),
    }

    if args.dry_run:
        print(json.dumps(summary, ensure_ascii=False, indent=2, sort_keys=True))
        print("\n--dry-run : aucune ecriture", file=sys.stderr)
        return 0

    metadata = {
        "language": "fr",
        "schema": "terms v3 (D-018 : pos, pos_secondary, gender, verb_forms ; D-0XX : word_senses)",
        "source_ods8_sha256": sha256_of(ODS8_PATH),
        "source_ods9_sha256": sha256_of(ODS9_PATH),
        "source_kartmaan_sha256": sha256_of(KARTMAAN_PATH),
        "terms_total": str(len(terms)),
        "verb_forms_total": str(len(verb_forms)),
        "word_senses_rows_total": str(len(word_senses)),
    }
    for name in HBENBEL_FILES:
        metadata["source_hbenbel_%s_sha256" % Path(name).stem] = sha256_of(
            HBENBEL_DIR / name
        )
    write_database(terms, pos_gender, verb_forms, word_senses, metadata)

    REPORTS.mkdir(parents=True, exist_ok=True)
    write_json(REPORTS / "import-summary.json", summary)
    write_json(
        REPORTS / "ods8-ods9-status-counts.json",
        {
            "ods8_only": status["ods8_only"],
            "ods9_only": status["ods9_only"],
            "ods8_and_ods9": status["ods8_and_ods9"],
            "french_non_ods": status["french_non_ods"],
            "ods8_total": status["ods8_only"] + status["ods8_and_ods9"],
            "ods9_total": status["ods9_only"] + status["ods8_and_ods9"],
            "terms_total": len(terms),
        },
    )
    write_csv(
        REPORTS / "normalization-collisions.csv",
        ["normalized", "source_forms_count", "source_forms"],
        (
            (normalized, len(forms), " | ".join(forms))
            for normalized, forms in sorted(collisions.items())
        ),
    )
    write_csv(
        REPORTS / "rejected-forms.csv",
        ["source", "rule", "form", "pos_or_origin"],
        sorted(
            [("kartmaan",) + row for row in rejected_samples]
            + [("hbenbel",) + row for row in hb_samples]
        ),
    )
    write_csv(
        REPORTS / "duplicates.csv",
        ["normalized", "kept_display_term", "merged_source_forms"],
        (
            (normalized, normalized, " | ".join(forms))
            for normalized, forms in sorted(collisions.items())
        ),
    )
    write_csv(
        REPORTS / "verb-lemmas-excluded.csv",
        ["lemma_normalized"],
        ((lemma,) for lemma in verb_excluded_lemmas),
    )

    connection = sqlite3.connect("file:%s?mode=ro" % TARGET_PATH.as_posix(), uri=True)
    try:
        integrity = connection.execute("PRAGMA integrity_check").fetchone()[0]
        quick = connection.execute("PRAGMA quick_check").fetchone()[0]
    finally:
        connection.close()
    (REPORTS / "sqlite-integrity.txt").write_text(
        "integrity_check: %s\nquick_check: %s\nbytes: %d\n"
        % (integrity, quick, TARGET_PATH.stat().st_size),
        encoding="utf-8",
    )

    print(json.dumps(summary, ensure_ascii=False, indent=2, sort_keys=True))
    print(
        "\nbase : %s (%.1f Mo)\nintegrity_check : %s"
        % (TARGET_PATH, TARGET_PATH.stat().st_size / 1e6, integrity),
        file=sys.stderr,
    )
    return 0 if integrity == "ok" else 1


if __name__ == "__main__":
    raise SystemExit(main())
