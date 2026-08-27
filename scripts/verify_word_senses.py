#!/usr/bin/env python3
"""Audit qualite des definitions deja generees (verification, pas generation).

Trouve par revue manuelle d'un echantillon aleatoire de 25 mots du lot de 10 000 (2026-08-24) :
BOYERIE definie a tort comme "piece pour domestiques" au lieu d'"etable a boeufs" -- confirme
que le garde-fou anti-copie + le scan qualite par motif (deja en place, generate_word_senses.py)
NE SUFFISENT PAS a eux seuls : ils detectent la copie et les signaux de mauvaise correspondance
evidents, jamais une erreur de sens plausible-mais-fausse. Meme lecon que
DEFINITIONS_NATURE_PLAYBOOK.md section 6 -- "l'audit heuristique gratuit echoue, remplace par
une verification LLM a deux etages".

Etage 1 (ce script) : DeepSeek en lot juge chaque definition existante -- correct/incorrect/
incertain + correction proposee si incorrect. Bon marche, couvre tout le lot.

Etage 2 : PAS un second appel API -- la session Claude Code qui exploite ce script relit
elle-meme les entrees flaguees "incorrect" (et un echantillon des "incertain") avant
d'appliquer quoi que ce soit. Meme principe a deux niveaux que la methodologie (un modele bon
marche en volume, un jugement plus soigneux uniquement sur ce qui est flague), sans dependre
d'une deuxieme cle API.

Usage :
    python scripts/verify_word_senses.py --dry-run --limit 100
    DEEPSEEK_API_KEY=... python scripts/verify_word_senses.py
"""

from __future__ import annotations

import argparse
import json
import os
import subprocess
import sys
import threading
import time
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
CACHE_PATH = ROOT / "data" / "generated" / "word_senses_cache.jsonl"
OUTPUT_PATH = ROOT / "data" / "generated" / "word_senses_verification.jsonl"
API_URL = "https://api.deepseek.com/chat/completions"
MODEL = "deepseek-chat"
BATCH_SIZE = 35
MAX_RETRIES = 3

SYSTEM_PROMPT = """Tu es un lexicographe francais charge de VERIFIER des definitions deja
ecrites (pas d'en creer). Pour chaque mot et sa definition proposee, juge si elle est
FACTUELLEMENT CORRECTE pour ce mot en francais.

Regles :
1. "verdict" vaut EXACTEMENT "correct", "incorrect" ou "incertain".
   - "correct" : la definition decrit bien le sens reel du mot.
   - "incorrect" : la definition decrit un sens different ou faux (mot confondu avec un autre,
     sens errone, nature grammaticale fausse...).
   - "incertain" : mot rare/technique/regional dont tu n'es pas assez sur pour trancher --
     PREFERE "incertain" a une fausse certitude dans un sens ou dans l'autre.
2. Si "incorrect", fournis "correction" : une definition corrigee, meme format que l'original
   (une phrase, factuelle, moins de 20 mots). Sinon "correction" doit etre null.
3. Ne change JAMAIS le "pos" ou le "gender" fournis, juge uniquement le texte de la definition
   elle-meme (sauf incoherence flagrante a signaler dans "incorrect").
4. Reponds UNIQUEMENT avec un objet JSON de cette forme exacte, couvrant chaque entree donnee
   dans le meme ordre :
{"results": [{"word": "...", "verdict": "correct|incorrect|incertain", "correction": "..." ou null}]}
"""


def load_done() -> set[tuple[str, int]]:
    if not OUTPUT_PATH.exists():
        return set()
    done = set()
    with OUTPUT_PATH.open(encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            row = json.loads(line)
            done.add((row["term"], row["sense_rank"]))
    return done


def build_prompt(batch: list[dict]) -> str:
    lines = []
    for entry in batch:
        pos_label = entry["sense"]["pos"]
        lines.append('- "%s" (%s) : "%s"' % (entry["term"].lower(), pos_label, entry["sense"]["definition"]))
    return "Definitions a verifier :\n" + "\n".join(lines)


def call_deepseek(api_key: str, batch: list[dict], tmp_dir: Path, batch_id: int) -> dict:
    payload = {
        "model": MODEL,
        "messages": [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": build_prompt(batch)},
        ],
        "response_format": {"type": "json_object"},
        "max_tokens": 200 * len(batch),
        "temperature": 0.1,
    }
    # Fichiers nommes par batch_id -- plusieurs lots en vol simultanement (concurrence)
    # partageraient sinon le meme fichier et se corromperaient mutuellement.
    payload_path = tmp_dir / ("verify_request_%d.json" % batch_id)
    payload_path.write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")

    response_path = tmp_dir / ("verify_response_%d.json" % batch_id)
    result = subprocess.run(
        [
            "curl", "-fsS", "-X", "POST", API_URL,
            "-H", "Authorization: Bearer %s" % api_key,
            "-H", "Content-Type: application/json",
            "--data-binary", "@%s" % payload_path,
            "-o", str(response_path),
        ],
        capture_output=True, text=True,
    )
    if result.returncode != 0:
        raise RuntimeError("appel DeepSeek echoue (curl exit %d): %s" % (result.returncode, result.stderr))

    raw = json.loads(response_path.read_text(encoding="utf-8"))
    content = raw["choices"][0]["message"]["content"]
    return json.loads(content)


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--limit", type=int, default=None)
    args = parser.parse_args()

    env_file = ROOT / ".env"
    if "DEEPSEEK_API_KEY" not in os.environ and env_file.exists():
        for line in env_file.read_text(encoding="utf-8").splitlines():
            if line.startswith("DEEPSEEK_API_KEY="):
                os.environ["DEEPSEEK_API_KEY"] = line.split("=", 1)[1].strip()
                break

    entries = []
    with CACHE_PATH.open(encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            row = json.loads(line)
            for rank, sense in enumerate(row["senses"], start=1):
                entries.append({"term": row["term"], "sense_rank": rank, "sense": sense})

    done = load_done()
    pending = [e for e in entries if (e["term"], e["sense_rank"]) not in done]
    if args.limit:
        pending = pending[:args.limit]

    print("Total sens : %d, deja verifies : %d, restants : %d" % (len(entries), len(done), len(pending)))

    if args.dry_run:
        print("--dry-run : aucun appel API")
        return 0

    if not pending:
        return 0

    api_key = os.environ.get("DEEPSEEK_API_KEY")
    if not api_key:
        print("DEEPSEEK_API_KEY absent.", file=sys.stderr)
        return 1

    tmp_dir = Path(os.environ.get("TEMP", "/tmp")) / "word_senses_verify"
    tmp_dir.mkdir(parents=True, exist_ok=True)

    OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    out_f = OUTPUT_PATH.open("a", encoding="utf-8")
    write_lock = threading.Lock()
    counts_lock = threading.Lock()

    counts = {"correct": 0, "incorrect": 0, "incertain": 0}
    batches = [(i, pending[i:i + BATCH_SIZE]) for i in range(0, len(pending), BATCH_SIZE)]

    def process_batch(item: tuple[int, list[dict]]) -> None:
        i, batch = item
        parsed = None
        for attempt in range(1, MAX_RETRIES + 1):
            try:
                parsed = call_deepseek(api_key, batch, tmp_dir, i)
                break
            except Exception as e:  # noqa: BLE001
                print("lot %d, tentative %d/%d echouee : %s" % (i, attempt, MAX_RETRIES, e), file=sys.stderr)
                if attempt < MAX_RETRIES:
                    time.sleep(2 ** attempt)

        if parsed is None:
            return

        results_by_word = {r["word"].upper(): r for r in parsed.get("results", [])}
        rows_to_write = []

        for entry in batch:
            result = results_by_word.get(entry["term"])
            if result is None:
                continue
            verdict = result.get("verdict")
            if verdict not in ("correct", "incorrect", "incertain"):
                continue
            rows_to_write.append((verdict, json.dumps({
                "term": entry["term"],
                "sense_rank": entry["sense_rank"],
                "pos": entry["sense"]["pos"],
                "original_definition": entry["sense"]["definition"],
                "verdict": verdict,
                "correction": result.get("correction"),
            }, ensure_ascii=False)))

        with counts_lock:
            for verdict, _ in rows_to_write:
                counts[verdict] += 1

        with write_lock:
            for _, line in rows_to_write:
                out_f.write(line + "\n")
            out_f.flush()

        print("lot %d-%d verifie (%d mots)" % (i, i + len(batch), len(batch)))

    try:
        # Concurrence limitee (6 lots en vol simultanement max, methodologie-generation-
        # definitions-llm.md section 7 : "5-6 appels en parallele maximum, sous peine de
        # rate-limiting") -- I/O-bound (subprocess curl + fichiers), le GIL n'est pas un
        # goulot ici.
        with ThreadPoolExecutor(max_workers=12) as executor:
            list(executor.map(process_batch, batches))
    finally:
        out_f.close()

    print("--- resume ---")
    print("correct=%d incorrect=%d incertain=%d" % (counts["correct"], counts["incorrect"], counts["incertain"]))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
