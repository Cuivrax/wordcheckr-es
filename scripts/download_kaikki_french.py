#!/usr/bin/env python3
"""Télécharge l'extrait Wiktionnaire français de kaikki.org dans data/raw/kaikki_fr/.

Étape de build hors ligne (D-007). Source : kaikki.org/frwiktionary/Français/ -- extraction
du Wiktionnaire FRANÇAIS (fr.wiktionary.org), pas l'édition anglaise "kaikki.org/dictionary/
French/" qui documente le vocabulaire français avec des gloses en ANGLAIS (vérifié par
échantillonnage avant d'écrire ce script -- les deux existent, seule celle-ci convient pour
ancrer des définitions rédigées en français). Sert de palier 2 (référence de secours) pour
les ~12,3% de termes non couverts par data/raw/french_dict.db (Kartmaan) -- jamais affiché
tel quel, uniquement du grounding pour la génération LLM (voir scripts/lib/
reference_definitions.py), même discipline D-015 que Kartmaan.

Usage :
    python scripts/download_kaikki_french.py

Le fichier .gz (~366 Mo au moment de l'écriture de ce script) est conservé tel quel --
scripts/lib/reference_definitions.py le décompresse et le filtre à la volée (lang_code == "fr"
uniquement), jamais chargé entièrement en mémoire.
"""

from __future__ import annotations

import hashlib
import subprocess
from pathlib import Path

URL = "https://kaikki.org/frwiktionary/Fran%C3%A7ais/kaikki.org-dictionary-Fran%C3%A7ais.jsonl.gz"
DEST_DIR = Path(__file__).resolve().parents[1] / "data" / "raw" / "kaikki_fr"
DEST_FILE = DEST_DIR / "kaikki-dictionary-francais.jsonl.gz"
CHUNK_SIZE = 1024 * 1024  # 1 Mo


def main() -> int:
    DEST_DIR.mkdir(parents=True, exist_ok=True)
    partial = DEST_FILE.with_suffix(DEST_FILE.suffix + ".part")

    # curl plutôt que urllib.request : la chaîne de certificats système Windows de cet
    # environnement échoue la vérification stricte OpenSSL 3.x de Python 3.13+ ("Basic
    # Constraints of CA cert not marked critical") contre kaikki.org précisément -- curl,
    # déjà vérifié fonctionnel contre ce même hôte, n'a pas ce problème.
    subprocess.run(
        ["curl", "-fSL", "--retry", "3", "-o", str(partial), URL],
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
