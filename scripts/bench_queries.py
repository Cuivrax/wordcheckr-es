#!/usr/bin/env python3
"""Chronomètre et documente les requêtes témoins de la Phase 0.

Écrit reports/query-plans/phase0.md : EXPLAIN QUERY PLAN et timing pour chaque
requête que les Phases 1 à 5 réutiliseront. Lecture seule sur la base produite.

Usage :
    python scripts/bench_queries.py
"""

from __future__ import annotations

import sqlite3
import statistics
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DB = ROOT / "storage" / "dictionary_fr.sqlite"
OUT = ROOT / "reports" / "query-plans" / "phase0.md"

RUNS = 200

# (libellé, SQL, paramètres, ce que la requête sert dans la Phase 1)
QUERIES = [
    (
        "Fiche mot",
        "SELECT display_term, score, length, is_ods8, is_ods9 "
        "FROM terms WHERE normalized = ? LIMIT 1",
        ("POSER",),
        "/mot/{mot}",
    ),
    (
        "Anagrammes exactes",
        "SELECT normalized FROM terms WHERE signature = ? AND normalized <> ? LIMIT 60",
        ("EOPRS", "POSER"),
        "relations de la fiche mot (Phase 4)",
    ),
    (
        "Préfixe — PLAGE, pas LIKE",
        "SELECT normalized FROM terms WHERE normalized >= ? AND normalized < ? "
        "ORDER BY normalized LIMIT 8",
        ("CH", "CI"),
        "autocomplétion (Phase 5)",
    ),
    (
        "Suffixe — PLAGE sur reversed, pas LIKE",
        "SELECT normalized FROM terms WHERE reversed >= ? AND reversed < ? LIMIT 50",
        ("NOIT", "NOIU"),
        "/mots/terminant/tion (Phase 3)",
    ),
    (
        "Longueur, ordre alphabétique",
        "SELECT normalized FROM terms WHERE length = ? ORDER BY normalized LIMIT 50",
        (7,),
        "/mots/7-lettres (Phase 3)",
    ),
    (
        "Longueur + préfixe, admis ODS9",
        "SELECT normalized FROM terms WHERE is_ods9 = 1 "
        "AND normalized >= ? AND normalized < ? LIMIT 50",
        ("CH", "CI"),
        "/mots/7-lettres/commencant/ch (Phase 3)",
    ),
    (
        "Mot précédent",
        "SELECT normalized FROM terms WHERE normalized < ? ORDER BY normalized DESC LIMIT 1",
        ("POSER",),
        "navigation mot précédent/suivant (Phase 1)",
    ),
]


def main() -> int:
    connection = sqlite3.connect("file:%s?mode=ro" % DB.as_posix(), uri=True)
    lines = [
        "# Plans De Requêtes — Phase 0",
        "",
        "Produit par `scripts/bench_queries.py`, lecture seule sur "
        "`storage/dictionary_fr.sqlite`.",
        "Chaque timing est une médiane sur %d exécutions." % RUNS,
        "",
    ]
    all_indexed = True

    for label, sql, params, usage in QUERIES:
        plan = connection.execute(
            "EXPLAIN QUERY PLAN " + sql, params
        ).fetchall()
        plan_text = [row[3] for row in plan]
        uses_scan = any("SCAN" in p and "USING" not in p for p in plan_text)
        all_indexed = all_indexed and not uses_scan

        timings = []
        row_count = 0
        for _ in range(RUNS):
            start = time.perf_counter()
            rows = connection.execute(sql, params).fetchall()
            timings.append((time.perf_counter() - start) * 1000)
            row_count = len(rows)
        median = statistics.median(timings)

        lines.append("## %s" % label)
        lines.append("")
        lines.append("Sert : %s" % usage)
        lines.append("")
        lines.append("```sql")
        lines.append(sql)
        lines.append("```")
        lines.append("")
        lines.append("```text")
        lines.extend(plan_text)
        lines.append("```")
        lines.append("")
        lines.append(
            "%d lignes, médiane %.3f ms, min %.3f ms, max %.3f ms — %s"
            % (
                row_count,
                median,
                min(timings),
                max(timings),
                "SCAN" if uses_scan else "index",
            )
        )
        lines.append("")

    lines.append("## Verdict")
    lines.append("")
    lines.append(
        "Toutes les requêtes passent par un index, aucun `SCAN TABLE`."
        if all_indexed
        else "AU MOINS UNE REQUÊTE FAIT UN SCAN TABLE — voir ci-dessus."
    )

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text("\n".join(lines) + "\n", encoding="utf-8")
    print("écrit :", OUT)
    return 0 if all_indexed else 1


if __name__ == "__main__":
    raise SystemExit(main())
