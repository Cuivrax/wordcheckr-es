#!/usr/bin/env python3
from __future__ import annotations

import json
import sqlite3
import sys
from pathlib import Path

root = Path(__file__).resolve().parents[1]
raw = root / "data" / "raw"
ods9 = root / "data" / "ods9"

required = [
    ods9 / "ods9_patch.sqlite",
    ods9 / "ods9_additions_flat.json",
    ods9 / "ods9_removals_flat.json",
]

missing = [str(p) for p in required if not p.exists()]
if missing:
    print(json.dumps({"status": "error", "missing": missing}, indent=2))
    sys.exit(1)

report = {
    "status": "ok",
    "ods8_present": (raw / "ods8.json").exists(),
    "french_dict_present": (raw / "french_dict.db").exists(),
    "ods9_patch_integrity": None,
    "ods9_counts": {},
}

with sqlite3.connect(ods9 / "ods9_patch.sqlite") as conn:
    report["ods9_patch_integrity"] = conn.execute(
        "PRAGMA integrity_check"
    ).fetchone()[0]
    for table in ("additions", "removals", "keep_overrides", "modifications"):
        report["ods9_counts"][table] = conn.execute(
            f"SELECT COUNT(*) FROM {table}"
        ).fetchone()[0]

print(json.dumps(report, ensure_ascii=False, indent=2))
