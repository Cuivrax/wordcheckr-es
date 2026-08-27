#!/usr/bin/env python3
"""Télécharge une source de formes françaises dans data/raw/hbenbel/.

Étape de build hors ligne. Les formes servent à alimenter la couche
`is_french` ; aucune définition ni structure d'origine n'entre en production,
et le site ne publie aucun crédit de source (D-015).

Usage :
    python scripts/download_hbenbel.py
"""

from __future__ import annotations

import hashlib
import urllib.request
from pathlib import Path

BASE_URL = "https://raw.githubusercontent.com/hbenbel/French-Dictionary/master/dictionary/"
FILES = ("dictionary.csv", "adj.csv", "noun.csv", "verb.csv", "adv.csv")
DEST = Path(__file__).resolve().parents[1] / "data" / "raw" / "hbenbel"


def main() -> int:
    DEST.mkdir(parents=True, exist_ok=True)
    for name in FILES:
        target = DEST / name
        partial = target.with_suffix(target.suffix + ".part")
        with urllib.request.urlopen(BASE_URL + name) as response:
            payload = response.read()
        partial.write_bytes(payload)
        partial.replace(target)
        digest = hashlib.sha256(payload).hexdigest()
        print("%-16s %10d o  %s" % (name, len(payload), digest))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
