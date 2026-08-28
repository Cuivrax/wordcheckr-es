#!/usr/bin/env python3
"""Construit storage/dictionary_es.sqlite depuis les trois sources espagnoles.

Hors ligne uniquement (D-007). La base est recréée intégralement à chaque exécution :
elle n'est jamais mise à jour en place. Adapté de scripts/import_fr.py (site français) --
même discipline (ANALYZE + VACUUM systématiques après tout changement de schéma/données,
voir la régression documentée D-021 du site français : ne JAMAIS l'omettre).

Portée de cette passe (voir docs/DECISIONS.md, décision ES-001) : uniquement le coeur du
site -- vérification de mot et solveur de rack/liste. Nature grammaticale, conjugaison et
définitions lexicales sont HORS PÉRIMÈTRE, explicitement -- ce script laisse pos/
pos_secondary/gender à NULL et n'écrit aucune ligne dans verb_forms/word_senses/
list_counts (tables conservées au schéma pour compatibilité avec app/Search/, voir
schema.sql).

Ordre de fusion :

    1. extrait Wiktionnaire espagnol (kaikki_es), filtré par pos          -> is_spanish = 1
    2. Lexicón FILE 2017 (data/raw/file_2017.json), défaut de concaténation retiré
                                                                            -> is_ods8 = 1
    3. Lexicón FISE-2 2009, canal MIT (data/raw/an_array_of_spanish_words.json)
                                                                            -> is_ods9 = 1
    4. score, length, signature, reversed (Ñ-safe, scripts/lib/normalize.py)
    5. is_admitted précalculé (is_ods8 OR is_ods9)
    6. index (schema.sql), ANALYZE, VACUUM, integrity_check
    7. rapports

Usage :
    python scripts/import_es.py [--dry-run]
"""

from __future__ import annotations

import argparse
import csv
import gzip
import hashlib
import json
import sqlite3
import sys
from collections import Counter, defaultdict
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib import detect_concat_defects as dcd  # noqa: E402
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
FILE_2017_PATH = ROOT / "data" / "raw" / "file_2017.json"
AN_ARRAY_PATH = ROOT / "data" / "raw" / "an_array_of_spanish_words.json"
KAIKKI_ES_PATH = ROOT / "data" / "raw" / "kaikki_es" / "kaikki-dictionary-espanol.jsonl.gz"
SCHEMA_PATH = ROOT / "schema.sql"
TARGET_PATH = ROOT / "storage" / "dictionary_es.sqlite"
REPORTS = ROOT / "reports"

# pos kaikki.org écartés pour la couche "mot espagnol réel" (is_spanish) : noms propres,
# locutions (déjà écartées par le filtre espace, exclusion redondante mais explicite),
# caractères isolés (kana, symboles), abréviations/formes non lexicales, pos non résolu.
# Voir data/raw/PROVENANCE.md §3 pour la distribution complète mesurée.
KAIKKI_POS_EXCLUDED = frozenset({"name", "phrase", "character", "symbol", "unknown", "suffix", "prefix", "infix"})


def sha256_of(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1 << 20), b""):
            digest.update(chunk)
    return digest.hexdigest()


def rejection_rule(form: str, normalized: str) -> str | None:
    """Renvoie la règle de rejet, ou None si la forme est retenue.

    Les deux sources Scrabble sont déjà propres (alphabet strict a-z + ñ, aucune
    ponctuation -- vérifié à l'inspection, voir data/raw/PROVENANCE.md) ; ce filtre agit
    surtout comme un garde-fou défensif, et comme le filtre RÉEL pour la couche kaikki_es
    (formes multi-mots, apostrophes...).
    """
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
        return "caractere hors A-Z/Ñ apres normalisation"
    return None


def load_scrabble_source(path: Path) -> list[str]:
    """Charge une des deux listes Scrabble (formes brutes, ordre du fichier source
    conservé -- nécessaire à scripts/lib/detect_concat_defects.py, qui a besoin de
    l'ordre original pour son test d'adjacence alphabétique).

    Deux formats distincts constatés à l'inspection (docs/03 §2 : « ne pas supposer le
    format sans l'avoir lu », même discipline appliquée ici) : file_2017.json est un
    objet {"words": [...]}, an_array_of_spanish_words.json est un tableau JSON plat.
    """
    with path.open(encoding="utf-8") as handle:
        data = json.load(handle)
    if isinstance(data, dict):
        return data["words"]
    return data


def find_and_drop_concat_defects(
    label_a: str, words_a: list[str], label_b: str, words_b: list[str]
) -> tuple[list[str], list[dcd.ConcatDefect]]:
    """Balayage bidirectionnel (voir scripts/lib/detect_concat_defects.py). Renvoie
    (words_a SANS les entrées fautives confirmées, la liste des défauts confirmés)."""
    defects = dcd.detect(words_a, set(words_b))
    if not defects:
        return words_a, []
    dropped = {d.merged for d in defects}
    cleaned = [w for w in words_a if w not in dropped]
    return cleaned, defects


def load_kaikki_es() -> tuple[dict[str, set[str]], dict[str, int], Counter, list[tuple[str, str]]]:
    """Charge l'extrait Wiktionnaire espagnol de kaikki.org, filtré par pos puis par
    rejection_rule(). Renvoie (formes brutes groupées par normalisée, volumétrie, rejets,
    échantillon de formes rejetées).

    dict[normalized -> set(formes brutes)], pas un simple set(normalized) : necessaire pour
    detecter les COLLISIONS de normalisation (docs/03 §6, rapport obligatoire) -- kaikki_es
    contient des formes qui ne different que par un accent de voyelle (ex. "ababillara" et
    "ababillará", deux formes verbales distinctes qui convergent vers la meme normalisee
    ABABILLARA une fois l'accent retire, meme convention que le Scrabble espagnol). Une
    collision est une fusion de provenance attendue, pas une erreur -- mais elle doit rester
    TRACEE (meme discipline que scripts/import_fr.py, D-013 du site francais), jamais
    silencieuse.
    """
    kept: dict[str, set[str]] = defaultdict(set)
    rejected: Counter = Counter()
    seen_rejected: set[tuple[str, str]] = set()
    samples: list[tuple[str, str]] = []
    stats = {
        "kaikki_source_lines": 0,
        "kaikki_pos_excluded": 0,
        "kaikki_candidates_after_pos_filter": 0,
    }

    with gzip.open(KAIKKI_ES_PATH, "rt", encoding="utf-8") as handle:
        for line in handle:
            line = line.strip()
            if not line:
                continue
            stats["kaikki_source_lines"] += 1
            entry = json.loads(line)
            pos = entry.get("pos")
            if pos in KAIKKI_POS_EXCLUDED:
                stats["kaikki_pos_excluded"] += 1
                continue
            word = entry.get("word")
            if not word:
                continue
            stats["kaikki_candidates_after_pos_filter"] += 1
            normalized = normalize(word)
            rule = rejection_rule(word, normalized)
            if rule is not None:
                rejected[rule] += 1
                key = (rule, word)
                if key not in seen_rejected:
                    seen_rejected.add(key)
                    samples.append((rule, word))
                continue
            kept[normalized].add(word)

    stats["kaikki_distinct_normalized_retained"] = len(kept)
    samples.sort()
    return kept, stats, rejected, samples


def load_and_filter_scrabble_source(
    label: str, raw_words: list[str]
) -> tuple[dict[str, set[str]], dict[str, int], Counter, list[tuple[str, str]]]:
    """Meme contrat que load_kaikki_es() ci-dessus (formes brutes groupees par normalisee,
    pour detecter les collisions), applique aux deux sources Scrabble. Les deux sont deja
    tres propres (alphabet strict a-z + ñ, verifie a l'inspection) : les collisions
    attendues ici sont rares -- mais doivent rester mesurees, pas supposees nulles."""
    kept: dict[str, set[str]] = defaultdict(set)
    rejected: Counter = Counter()
    seen_rejected: set[tuple[str, str]] = set()
    samples: list[tuple[str, str]] = []
    for form in raw_words:
        normalized = normalize(form)
        rule = rejection_rule(form, normalized)
        if rule is not None:
            rejected[rule] += 1
            key = (rule, form)
            if key not in seen_rejected:
                seen_rejected.add(key)
                samples.append((rule, form))
            continue
        kept[normalized].add(form)
    stats = {
        "%s_source_rows" % label: len(raw_words),
        "%s_distinct_normalized_retained" % label: len(kept),
    }
    samples.sort()
    return kept, stats, rejected, samples


def build_terms(
    kaikki_es: dict[str, set[str]], file_2017: dict[str, set[str]], an_array: dict[str, set[str]]
) -> tuple[dict[str, dict], dict[str, int]]:
    """Applique l'ordre de fusion et renvoie (termes, compteurs d'effet). Iterer un dict
    (kaikki_es, file_2017, an_array) parcourt ses cles (les formes normalisees) -- les valeurs
    (ensembles de formes brutes, utilisees pour les collisions ailleurs) ne sont pas necessaires
    ici."""
    terms: dict[str, dict] = {}
    effects: dict[str, int] = {}

    # 1. kaikki_es -- couche "mot espagnol réel" (équivalent Kartmaan du site français).
    for normalized in kaikki_es:
        terms[normalized] = {"is_spanish": 1, "is_ods8": 0, "is_ods9": 0}

    # 2. Lexicón FILE 2017 -- admis par construction : is_spanish = 1 sans consulter
    #    kaikki_es, qui ne couvre pas tout le lexique Scrabble (même raisonnement que
    #    ODS8 => is_french = 1 du site français).
    created_by_file2017 = 0
    for normalized in file_2017:
        entry = terms.get(normalized)
        if entry is None:
            entry = terms[normalized] = {"is_spanish": 1, "is_ods8": 0, "is_ods9": 0}
            created_by_file2017 += 1
        entry["is_ods8"] = 1
    effects["file2017_rows_absent_from_kaikki"] = created_by_file2017

    # 3. Lexicón FISE-2 2009 (canal MIT) -- même raisonnement.
    created_by_an_array = 0
    for normalized in an_array:
        entry = terms.get(normalized)
        if entry is None:
            entry = terms[normalized] = {"is_spanish": 1, "is_ods8": 0, "is_ods9": 0}
            created_by_an_array += 1
        entry["is_ods9"] = 1
    effects["fise2_rows_absent_from_kaikki_and_file2017"] = created_by_an_array

    return terms, effects


def write_database(terms: dict[str, dict], metadata: dict[str, str]) -> None:
    TARGET_PATH.parent.mkdir(parents=True, exist_ok=True)
    if TARGET_PATH.exists():
        TARGET_PATH.unlink()

    connection = sqlite3.connect(TARGET_PATH)
    try:
        connection.executescript(SCHEMA_PATH.read_text(encoding="utf-8"))
        rows = (
            (
                index,
                normalized,  # display_term = normalized, sans exception (D-013-equivalent)
                normalized,
                entry["is_spanish"],
                entry["is_ods8"],
                entry["is_ods9"],
                1 if (entry["is_ods8"] or entry["is_ods9"]) else 0,  # is_admitted
                score(normalized),
                len(normalized),
                signature(normalized),
                reverse(normalized),
                # pos, pos_secondary, gender : toujours NULL (hors périmètre, ES-001).
            )
            for index, (normalized, entry) in enumerate(sorted(terms.items()), start=1)
        )
        connection.executemany(
            "INSERT INTO terms (id, display_term, normalized, is_spanish, is_ods8,"
            " is_ods9, is_admitted, score, length, signature, reversed)"
            " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            rows,
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


def write_json(path: Path, payload: dict) -> None:
    path.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
    )


def write_csv_defects(path: Path, defects: list[tuple[str, dcd.ConcatDefect]]) -> None:
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.writer(handle, lineterminator="\n")
        writer.writerow(["source", "merged", "part1", "part2"])
        for source_label, defect in defects:
            writer.writerow([source_label, defect.merged, defect.part1, defect.part2])


def write_collisions_csv(path: Path, collisions_by_source: list[tuple[str, dict[str, list[str]]]]) -> None:
    """docs/03 §6 (rapport obligatoire) : une ligne par normalisee associee a plusieurs formes
    brutes distinctes DANS LA MEME source -- meme colonnes que reports/normalization-collisions.csv
    du site francais (scripts/import_fr.py), plus une colonne "source" (trois sources ici, pas
    une seule liste deja fusionnee)."""
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.writer(handle, lineterminator="\n")
        writer.writerow(["source", "normalized", "source_forms_count", "source_forms"])
        for source_label, collisions in collisions_by_source:
            for normalized, forms in sorted(collisions.items()):
                writer.writerow([source_label, normalized, len(forms), " | ".join(forms)])


def write_rejected_forms_csv(path: Path, samples_by_source: list[tuple[str, list[tuple[str, str]]]]) -> None:
    """docs/03 §6 (rapport obligatoire) : un echantillon (une ligne par (regle, forme) distincte
    rencontree) des formes rejetees a l'import, toutes sources confondues -- meme esprit que
    reports/rejected-forms.csv du site francais (scripts/import_fr.py)."""
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.writer(handle, lineterminator="\n")
        writer.writerow(["source", "rule", "form"])
        for source_label, samples in samples_by_source:
            for rule, form in samples:
                writer.writerow([source_label, rule, form])


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="analyse les sources et affiche le resume, sans rien ecrire",
    )
    args = parser.parse_args()

    required = [FILE_2017_PATH, AN_ARRAY_PATH, KAIKKI_ES_PATH, SCHEMA_PATH]
    for path in required:
        if not path.exists():
            raise SystemExit("source manquante : %s" % path)

    file_2017_raw = load_scrabble_source(FILE_2017_PATH)
    an_array_raw = load_scrabble_source(AN_ARRAY_PATH)

    file_2017_clean, defects_in_file2017 = find_and_drop_concat_defects(
        "file_2017", file_2017_raw, "an_array", an_array_raw
    )
    an_array_clean, defects_in_an_array = find_and_drop_concat_defects(
        "an_array", an_array_raw, "file_2017", file_2017_raw
    )

    file_2017, file2017_stats, file2017_rejected, file2017_samples = load_and_filter_scrabble_source(
        "file2017", file_2017_clean
    )
    an_array, an_array_stats, an_array_rejected, an_array_samples = load_and_filter_scrabble_source(
        "fise2", an_array_clean
    )
    kaikki_es, kaikki_stats, kaikki_rejected, kaikki_samples = load_kaikki_es()

    terms, effects = build_terms(kaikki_es, file_2017, an_array)

    # Collisions de normalisation (docs/03 §6, rapport obligatoire) : une normalisee associee a
    # PLUSIEURS formes brutes distinctes dans la MEME source -- fusion de provenance attendue
    # (ex. "ababillara"/"ababillará" -> ABABILLARA, deux formes verbales qui ne different que par
    # un accent retire a la normalisation), jamais une erreur, mais jamais silencieuse non plus.
    # Mesurees PAR SOURCE (pas fusionnees entre elles, contrairement a scripts/import_fr.py qui
    # fusionne Kartmaan+hbenbel avant de mesurer) : kaikki_es est ici la SEULE source jouant le
    # role de "dictionnaire general a formes multiples" (equivalent Kartmaan+hbenbel combines cote
    # francais) ; file_2017/an_array sont des listes Scrabble a une seule graphie par mot, mesurees
    # separement pour ne jamais SUPPOSER qu'elles sont a zero collision.
    def collisions_of(forms_by_normalized: dict[str, set[str]]) -> dict[str, list[str]]:
        return {
            normalized: sorted(forms)
            for normalized, forms in forms_by_normalized.items()
            if len(forms) > 1
        }

    kaikki_collisions = collisions_of(kaikki_es)
    file2017_collisions = collisions_of(file_2017)
    fise2_collisions = collisions_of(an_array)
    normalization_collisions_total = len(kaikki_collisions) + len(file2017_collisions) + len(fise2_collisions)

    status = Counter()
    for entry in terms.values():
        if entry["is_ods8"] and entry["is_ods9"]:
            status["file2017_and_fise2"] += 1
        elif entry["is_ods8"]:
            status["file2017_only"] += 1
        elif entry["is_ods9"]:
            status["fise2_only"] += 1
        else:
            status["spanish_non_admitted"] += 1

    summary = {
        "file_2017_source_rows": len(file_2017_raw),
        "an_array_source_rows": len(an_array_raw),
        "concat_defects_found_in_file_2017": len(defects_in_file2017),
        "concat_defects_found_in_an_array": len(defects_in_an_array),
        "concat_defects_detail": [
            {"merged": d.merged, "part1": d.part1, "part2": d.part2} for d in defects_in_file2017 + defects_in_an_array
        ],
        "file2017": dict(sorted(file2017_stats.items())),
        "fise2": dict(sorted(an_array_stats.items())),
        "kaikki": dict(sorted(kaikki_stats.items())),
        "scrabble_union_distinct_normalized": len(file_2017.keys() | an_array.keys()),
        "file2017_only": status["file2017_only"],
        "fise2_only": status["fise2_only"],
        "file2017_and_fise2": status["file2017_and_fise2"],
        "spanish_non_admitted": status["spanish_non_admitted"],
        "terms_total": len(terms),
        "max_term_length": MAX_LENGTH,
        "merge_effects": effects,
        "rejections_by_rule_file2017": dict(sorted(file2017_rejected.items())),
        "rejections_by_rule_fise2": dict(sorted(an_array_rejected.items())),
        "rejections_by_rule_kaikki": dict(sorted(kaikki_rejected.items())),
        "normalization_collisions": normalization_collisions_total,
        "normalization_collisions_kaikki": len(kaikki_collisions),
        "normalization_collisions_file2017": len(file2017_collisions),
        "normalization_collisions_fise2": len(fise2_collisions),
    }

    if args.dry_run:
        print(json.dumps(summary, ensure_ascii=False, indent=2, sort_keys=True))
        print("\n--dry-run : aucune ecriture", file=sys.stderr)
        return 0

    metadata = {
        "language": "es",
        "schema": "terms v1 (site espagnol -- coeur uniquement, ES-001 : pos/gender/verb_forms/"
        "word_senses/list_counts au schema mais vides)",
        "source_file_2017_sha256": sha256_of(FILE_2017_PATH),
        "source_an_array_sha256": sha256_of(AN_ARRAY_PATH),
        "source_kaikki_es_sha256": sha256_of(KAIKKI_ES_PATH),
        "terms_total": str(len(terms)),
    }
    write_database(terms, metadata)

    REPORTS.mkdir(parents=True, exist_ok=True)
    write_json(REPORTS / "import-summary-es.json", summary)
    write_csv_defects(
        REPORTS / "concat-defects-es.csv",
        [("file_2017", d) for d in defects_in_file2017] + [("an_array", d) for d in defects_in_an_array],
    )
    write_collisions_csv(
        REPORTS / "normalization-collisions.csv",
        [("kaikki", kaikki_collisions), ("file2017", file2017_collisions), ("fise2", fise2_collisions)],
    )
    write_rejected_forms_csv(
        REPORTS / "rejected-forms.csv",
        [("file2017", file2017_samples), ("fise2", an_array_samples), ("kaikki", kaikki_samples)],
    )

    connection = sqlite3.connect("file:%s?mode=ro" % TARGET_PATH.as_posix(), uri=True)
    try:
        integrity = connection.execute("PRAGMA integrity_check").fetchone()[0]
        quick = connection.execute("PRAGMA quick_check").fetchone()[0]
    finally:
        connection.close()
    (REPORTS / "sqlite-integrity-es.txt").write_text(
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
