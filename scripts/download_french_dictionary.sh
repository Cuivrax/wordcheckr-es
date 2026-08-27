#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEST_DIR="${ROOT_DIR}/data/raw"
DEST_FILE="${DEST_DIR}/french_dict.db"
URL="https://huggingface.co/datasets/Kartmaan/french-dictionary/resolve/main/french_dict.db?download=true"

mkdir -p "${DEST_DIR}"

echo "Téléchargement de french_dict.db..."
curl --fail --location --retry 3 --output "${DEST_FILE}.part" "${URL}"
mv "${DEST_FILE}.part" "${DEST_FILE}"

echo "Fichier téléchargé : ${DEST_FILE}"
ls -lh "${DEST_FILE}"

if command -v sha256sum >/dev/null 2>&1; then
  sha256sum "${DEST_FILE}" > "${DEST_FILE}.sha256"
  echo "SHA-256 écrit dans ${DEST_FILE}.sha256"
fi
