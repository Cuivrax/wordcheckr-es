#!/usr/bin/env python3
"""Récupère la source 2 (Lexicón FISE-2 2009, canal MIT) dans data/raw/.

Étape de build hors ligne (D-007). Voir data/raw/PROVENANCE.md pour le raisonnement
complet du choix de source.

La source 1 (Lexicón FILE 2017, data/raw/file_2017.json) N'EST PAS téléchargée par ce
script : elle est fournie par le propriétaire du produit (fichier local, hors dépôt,
aucune licence propre déclarée en amont) et doit être copiée manuellement vers
data/raw/file_2017.json avant d'exécuter scripts/import_es.py -- voir
data/raw/PROVENANCE.md §1 pour l'empreinte attendue (sha256) qui sert de contrôle
d'intégrité.

Usage :
    python scripts/download_wordlists_es.py
"""

from __future__ import annotations

import hashlib
import subprocess
from pathlib import Path

# words/an-array-of-spanish-words (MIT, Zeke Sikelianos 2016) est, à un mot près, le
# même contenu que kamilmielnik/scrabble-dictionaries/spanish/fise-2.txt (sans licence
# déclarée) -- voir data/raw/PROVENANCE.md §2 pour la comparaison octet à octet qui a
# établi cette équivalence. Retenu ici pour sa licence explicite.
URL = "https://raw.githubusercontent.com/words/an-array-of-spanish-words/master/index.json"
DEST = Path(__file__).resolve().parents[1] / "data" / "raw" / "an_array_of_spanish_words.json"

EXPECTED_SHA256 = "c43d6d90db76f9fa38f6885227895562bde7c4c70cd6cfe23b37f369c1f7b4a1"
EXPECTED_SIZE = 8328819


def sha256_of(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1 << 20), b""):
            digest.update(chunk)
    return digest.hexdigest()


def main() -> int:
    DEST.parent.mkdir(parents=True, exist_ok=True)
    partial = DEST.with_suffix(DEST.suffix + ".part")

    subprocess.run(
        ["curl", "-fSL", "--retry", "3", "--max-time", "60", "-o", str(partial), URL],
        check=True,
    )
    partial.replace(DEST)

    size = DEST.stat().st_size
    digest = sha256_of(DEST)
    print("%-40s %10d o  %s" % (DEST.name, size, digest))

    if size != EXPECTED_SIZE or digest != EXPECTED_SHA256:
        print(
            "AVERTISSEMENT : empreinte différente de celle documentée dans "
            "data/raw/PROVENANCE.md -- le contenu amont a peut-être changé. "
            "Revérifier avant de lancer scripts/import_es.py."
        )
        return 1

    print("empreinte conforme à data/raw/PROVENANCE.md")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
