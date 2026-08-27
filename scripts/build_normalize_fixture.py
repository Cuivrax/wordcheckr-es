#!/usr/bin/env python3
"""Genere tests/fixtures/normalize_samples.json depuis scripts/lib/normalize.py.

Fixture de reference pour tests/Search/NormalizerTest.php (PHP), qui compare sa
reimplementation a la sortie reelle du script Python -- normalize.py reste la source
unique de la regle (D-009). Script de developpement : ne tourne jamais en production
(D-007), a relancer a la main si normalize.py change.

Usage :
    python scripts/build_normalize_fixture.py
"""

from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts" / "lib"))

import normalize as n  # noqa: E402

OUT = ROOT / "tests" / "fixtures" / "normalize_samples.json"

# Cas adversariaux : ligatures francaises, NFD + marques combinantes, casse (dont la
# conversion speciale de ss allemand), espaces, chiffres, ponctuation, bornes de
# longueur (2, 15, 16), chaine vide, mots reels.
RAW_SAMPLES = [
    "œuf", "bœuf", "nœud", "Œdipe", "cœur", "sœur", "vœu",
    "æquo", "Æquo", "curriculum vitæ",
    "café", "Straße", "naïve", "Noël", "à", "ça", "goûter",
    "Ïle", "Île-de-France", "Ůicode", "AEIOU",
    "poser", "POSER", "PoSeR", "  poser  ", "poser3", "12poser",
    "élève", "hétérogénéité", "être", "ça-va",
    "çÇ", "ÿŸ", "ñÑ", "üÜ", "êÊ", "ëË",
    "", "a", "ab", "abcdefghijklmno", "abcdefghijklmnop",
    "aeinrst", "ghoster", "qi", "xi",
    "123", "mot avec espace", "mot-compose", "mot'apostrophe",
]


def main() -> int:
    cases = []
    for raw in RAW_SAMPLES:
        normalized = n.normalize(raw)
        valid = n.is_valid(normalized)
        cases.append(
            {
                "raw": raw,
                "normalized": normalized,
                "valid": valid,
                "score": n.score(normalized) if valid else None,
                "signature": n.signature(normalized) if valid else None,
                "reversed": n.reverse(normalized) if valid else None,
            }
        )

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(cases, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print("ecrit :", OUT, "(%d cas)" % len(cases))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
