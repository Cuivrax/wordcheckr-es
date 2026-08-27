#!/usr/bin/env python3
"""Genere tests/fixtures/normalize_samples.json depuis scripts/lib/normalize.py (site
espagnol).

Fixture de reference pour tests/Search/NormalizerTest.php (PHP), qui compare sa
reimplementation a la sortie reelle du script Python -- normalize.py reste la source
unique de la regle. Script de developpement : ne tourne jamais en production (D-007), a
relancer a la main si normalize.py change.

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

# Cas adversariaux espagnols. Le plus important : "año" vs "ano" (annee / anus) ne
# doivent JAMAIS se normaliser vers la meme forme -- Ñ est une lettre a part entiere,
# pas un N accentue (voir ENYE_SENTINEL dans normalize.py). Le reste couvre les
# accents de voyelle (retires, absents des listes Scrabble sources), le trema, la
# casse, les bornes de longueur (2, 15, 16), les caracteres non A-Z/Ñ, la chaine vide.
RAW_SAMPLES = [
    "año", "Año", "AÑO", "años", "ano", "Ano", "ANO", "anos",
    "ñoño", "Ñoño", "ÑOÑO", "ñu", "Ñ",
    "pingüino", "vergüenza", "bilingüe", "lingüística",
    "café", "sótano", "corazón", "camión", "área", "óptica",
    "señor", "SeñOr", "  señor  ",
    "poser", "POSER", "PoSeR",
    "casa", "CASA", "casa3", "12casa",
    "casa grande", "años-luz", "no'se",
    "", "a", "ab", "ababillara", "ababillarais",
    "abcdefghijklmno", "abcdefghijklmnop",
    "aeinrst", "cachamarin", "dimanante",
    "123", "ñññ",
    # Tuiles digrammes CH/LL/RR (decision produit : edition avec tuiles dediees,
    # PAS une simplification a la lettre simple -- voir docs/DECISIONS.md).
    "coche", "carro", "calle", "chico", "llama", "perro", "rrr", "chchoal",
    "correcto", "achicharrar", "chachara", "cochecillo",
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
                "tiles": n.tokenize_tiles(normalized) if valid else None,
            }
        )

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(cases, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print("ecrit :", OUT, "(%d cas)" % len(cases))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
