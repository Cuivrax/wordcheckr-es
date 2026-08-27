#!/usr/bin/env python3
"""Télécharge l'extrait Wiktionnaire espagnol de kaikki.org dans data/raw/kaikki_es/.

Étape de build hors ligne (D-007). Source : kaikki.org/eswiktionary/Español/ --
extraction du Wiktionnaire ESPAGNOL (es.wiktionary.org), pas l'édition anglaise
"kaikki.org/dictionary/Spanish/" qui documente le vocabulaire espagnol avec des gloses
en ANGLAIS (même risque de confusion d'édition que pour le français -- les deux
existent, seule celle-ci convient). Sert de source pour le statut "mot espagnol réel,
pas nécessairement admis au Scrabble" (is_spanish, équivalent is_french du site
français) -- voir scripts/import_es.py.

Adapté de scripts/download_kaikki_french.py (site français) -- même pattern.

Usage :
    python scripts/download_kaikki_spanish.py

Le fichier .gz (~100 Mo au moment de l'écriture de ce script) est conservé tel quel --
scripts/import_es.py le décompresse et le filtre à la volée, jamais chargé entièrement
en mémoire.
"""

from __future__ import annotations

import hashlib
import subprocess
from pathlib import Path

URL = "https://kaikki.org/eswiktionary/Espa%C3%B1ol/kaikki.org-dictionary-Espa%C3%B1ol.jsonl.gz"
DEST_DIR = Path(__file__).resolve().parents[1] / "data" / "raw" / "kaikki_es"
DEST_FILE = DEST_DIR / "kaikki-dictionary-espanol.jsonl.gz"
CHUNK_SIZE = 1024 * 1024  # 1 Mo


def main() -> int:
    DEST_DIR.mkdir(parents=True, exist_ok=True)
    partial = DEST_FILE.with_suffix(DEST_FILE.suffix + ".part")

    # curl plutôt que urllib.request : même raison que download_kaikki_french.py
    # (chaîne de certificats système Windows en échec contre kaikki.org précisément
    # avec la vérification stricte OpenSSL 3.x de Python 3.13+).
    subprocess.run(
        ["curl", "-fSL", "--retry", "3", "--max-time", "300", "-o", str(partial), URL],
        check=True,
    )

    digest = hashlib.sha256()
    written = 0
    with partial.open("rb") as f:
        while True:
            chunk = f.read(CHUNK_SIZE)
            if not chunk:
                break
            digest.update(chunk)
            written += len(chunk)

    partial.replace(DEST_FILE)
    sha256_path = DEST_FILE.with_suffix(DEST_FILE.suffix + ".sha256")
    sha256_path.write_text("%s  %s\n" % (digest.hexdigest(), DEST_FILE.name), encoding="utf-8")
    print("%-40s %10d o  %s" % (DEST_FILE.name, written, digest.hexdigest()))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
