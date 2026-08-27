#!/usr/bin/env python3
"""Génère les sens (nature grammaticale + définition) pour word_senses via DeepSeek.

Étape de build hors ligne (D-007), jamais exécutée au runtime. Consomme la classification en
paliers de scripts/lib/reference_definitions.py :

    palier 0 (template)   gabarit "Forme conjuguée de LEMME (temps, personne)." -- ZÉRO appel
                           API, rendu directement depuis verb_forms.
    palier 1/2 (référence) appel LLM ancré sur une glose Kartmaan/kaikki.org -- reformulation
                           originale exigée, jamais une copie/paraphrase proche (garde-fou
                           anti-copie programmatique ci-dessous, pas seulement une consigne de
                           prompt).
    palier 3 (llm-only)    appel LLM sans référence, connaissances du modèle seules --
                           tracé explicitement, ton prudent imposé par le prompt système.

Écriture incrémentale et reprenable (JSONL, une ligne par terme) : un run interrompu (panne
réseau, crédit épuisé) reprend exactement où il s'est arrêté au prochain lancement, sans
retraiter ce qui est déjà dans le fichier de sortie.

Usage :
    python scripts/generate_word_senses.py --dry-run --pilot 100 --only-admitted
    DEEPSEEK_API_KEY=... python scripts/generate_word_senses.py --pilot 100 --only-admitted

La clé API n'est JAMAIS committée -- lue exclusivement via la variable d'environnement
DEEPSEEK_API_KEY, même discipline que SCRABBLE_CONTACT_EMAIL (public/index.php).
"""

from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
import threading
import time
from collections import Counter
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from lib.reference_definitions import (  # noqa: E402
    classify,
    load_target_terms,
)

ROOT = Path(__file__).resolve().parent.parent
# data/generated/, PAS storage/ : storage/ est entierement gitignore ("bases de production --
# generees par l'import, jamais versionnees", .gitignore) car dictionary_fr.sqlite est
# reconstructible a l'identique depuis les sources + le script. Ce fichier-ci est le produit
# d'un appel LLM PAYANT et NON deterministe -- le perdre ne signifie pas "relancer un script",
# mais "re-depenser de l'argent pour un resultat different". Meme categorie que data/ods9/
# (pack livre, versionne, checksums suivis), jamais que data/raw/ (source externe brute,
# gitignoree, reconstituee via PROVENANCE.md).
OUTPUT_PATH = ROOT / "data" / "generated" / "word_senses_cache.jsonl"
API_URL = "https://api.deepseek.com/chat/completions"
MODEL = "deepseek-chat"
BATCH_SIZE = 30
CONCURRENCY_UNUSED_NOTE = "séquentiel pour le pilote -- voir 'suite a donner' en bas de fichier"
MAX_RETRIES = 3
ANTI_COPY_MAX_SHARED_RUN = 7  # methodologie-generation-definitions-llm.md section 6
DUPLICATE_TEXT_ALERT_THRESHOLD = 5  # Axe B bis, rapport d'audit -- specifique a ce projet

# Mots-outils francais (liste NLTK francaise standard, formes elidees incluses car tokenize()
# separe l'apostrophe -- "d'un" -> ["d", "un"]) retires AVANT de mesurer le chevauchement
# anti-copie (D-043, lot de rattrapage --only-admitted) : trouve empiriquement que le seuil
# relatif (>=60% d'une reference COURTE) rejetait ~11 700 mots dont la glose de reference tient
# en 4-9 mots ("Faire de la bruine.") -- toute reformulation correcte d'une glose aussi courte
# partage forcement quelques mots-outils avec elle, ce n'est pas une copie de fond. Filtrer ces
# mots-outils avant de comparer concentre la mesure sur le contenu lexical reellement partage ;
# effet de bord positif sur le seuil absolu aussi (les copies reelles plus longues restent
# detectees, souvent mieux : supprimer les mots-outils rapproche les mots pleins copies qui
# etaient separes par eux).
FRENCH_STOPWORDS = frozenset({
    "au", "aux", "avec", "ce", "ces", "dans", "de", "des", "du", "elle", "en", "et", "eux",
    "il", "je", "la", "le", "leur", "lui", "ma", "mais", "me", "même", "mes", "moi", "mon",
    "ne", "nos", "notre", "nous", "on", "ou", "où", "par", "pas", "pour", "qu", "que", "qui",
    "sa", "se", "ses", "son", "sur", "ta", "te", "tes", "toi", "ton", "tu", "un", "une",
    "vos", "votre", "vous", "à", "c", "d", "j", "l", "m", "n", "s", "t", "y",
    "est", "sont", "être", "était", "étaient", "suis", "es", "sommes", "êtes",
    "sera", "seront", "serait", "seraient",
    "a", "ai", "as", "avons", "avez", "ont", "avoir", "avait", "avaient",
})

POS_VALUES = ("N", "V", "Adj", "Adv", "Pronom", "Prep", "Conj", "Interj", "Art")

# Motifs de mauvaise correspondance (methodologie-generation-definitions-llm.md section 8,
# adapte au francais) + ajout specifique a ce projet (Axe B bis du rapport d'audit) : un
# dictionnaire Scrabble exhaustif contient une proportion de mots rares/techniques bien plus
# elevee qu'une liste editorialisee -- terreau classique pour des phrases generiques.
QUALITY_RED_FLAGS = re.compile(
    r"(?i)nom propre|commune de|lieu-dit|espece de|genre de|sens incertain|non[- ]standard"
    r"|argot|terme rare|mot rare|orthographe (rare|variante)|region(al|ale) de"
)

SYSTEM_PROMPT = """Tu es un lexicographe qui rédige des entrées de dictionnaire extrêmement concises, en français.

Pour chaque mot donné, produis EXACTEMENT le nombre de sens indiqué pour ce mot (jamais plus,
jamais moins). Règles :

1. Chaque sens est EXACTEMENT une phrase, factuelle, moins de 20 mots, sans exemple, sans
   guillemets, se terminant par un point.
2. Ne JAMAIS combiner plusieurs sens en une phrase avec des points-virgules ou "ou" -- si
   plusieurs sens distincts sont demandés, donne à chacun son propre objet séparé. C'est la
   règle la plus importante.
3. Si une glose de référence est fournie pour un sens, sers-t'en UNIQUEMENT pour connaître le
   sens correct et confirmer la nature grammaticale -- ne JAMAIS copier ou paraphraser de trop
   près sa formulation. Écris toujours une phrase entièrement originale, avec ton propre choix
   de mots et de structure. Ceci s'applique ENCORE PLUS quand la référence est déjà courte (une
   poignée de mots) : renvoyer une référence courte quasiment telle quelle n'est PAS une
   définition originale, même si aucun mot individuel n'est "copié" au sens strict -- change la
   structure de la phrase, pas seulement un ou deux mots.
4. "pos" doit être exactement l'une de ces 9 valeurs : N, V, Adj, Adv, Pronom, Prep, Conj,
   Interj, Art.
5. "gender" ne s'applique qu'à pos == "N" : "m", "f" ou "e" (épicène) si connu, sinon null.
   Toujours null pour les autres pos.
6. Si aucune glose de référence n'est fournie pour un mot, utilise tes propres connaissances,
   mais reste prudent -- préfère une définition plus courte et sûre à une définition
   spéculative. Si tu n'es vraiment pas certain du sens, dis-le de façon neutre plutôt que
   d'inventer un sens précis.

Réponds UNIQUEMENT avec un objet JSON de cette forme exacte, couvrant chaque mot donné dans le
même ordre :
{"results": [{"word": "...", "senses": [{"pos": "...", "gender": null, "definition": "..."}]}]}
"""


def longest_shared_run(a: list[str], b: list[str]) -> int:
    """Plus longue suite de mots CONSÉCUTIFS identiques entre deux textes tokenisés --
    garde-fou anti-copie (methodologie-generation-definitions-llm.md section 6). Comparaison
    insensible à la casse, ponctuation déjà retirée par tokenize()."""
    best = 0
    for i in range(len(a)):
        # plus longue suite partagee entre a[i:] et un debut quelconque de b
        for start_b in range(len(b)):
            run = 0
            while (
                i + run < len(a)
                and start_b + run < len(b)
                and a[i + run] == b[start_b + run]
            ):
                run += 1
            best = max(best, run)
    return best


def tokenize(text: str) -> list[str]:
    return re.findall(r"[a-zàâäéèêëïîôöùûüç]+", text.lower())


def _content_tokens(text: str) -> list[str]:
    """tokenize() prive des mots-outils francais (FRENCH_STOPWORDS) -- la comparaison
    anti-copie porte sur le contenu lexical partage, pas sur des mots-outils qui
    co-occurrent forcement dans toute reformulation correcte (D-043, voir FRENCH_STOPWORDS)."""
    return [t for t in tokenize(text) if t not in FRENCH_STOPWORDS]


def is_too_similar(definition: str, references: list[str]) -> bool:
    """Rejette si la definition partage soit plus de ANTI_COPY_MAX_SHARED_RUN mots-pleins
    CONSECUTIFS avec une reference (seuil absolu, methodologie-generation-definitions-llm.md
    section 6), SOIT une part dominante d'une reference COURTE (seuil relatif, ajoute apres le
    pilote 100 mots -- trouve empiriquement : DeepSeek renvoie souvent une glose courte
    (<=6 mots) QUASIMENT TELLE QUELLE, ex. "Action de combiner plusieurs elements." pour
    ALLIAGE, "Habitant de Munich." pour MUNICHOIS -- jamais assez de mots consecutifs pour
    depasser le seuil absolu de 7, alors que c'est une copie de fait. Le seuil relatif (>=60%
    des mots de la reference, minimum absolu 3) attrape ces cas sans faire regresser le seuil
    absolu sur les references plus longues, ou un chevauchement partiel de 3-4 mots reste
    legitime.

    Les deux seuils comparent des tokens PRIVES DE MOTS-OUTILS (_content_tokens, pas tokenize()
    brut) -- sinon le seuil relatif rejette en masse les reformulations legitimes de gloses
    courtes (D-043, lot de rattrapage --only-admitted : ~11 700 mots bloques, glose de
    reference du type "Faire de la bruine." ou tout mot plein partage est un faux positif de
    mots-outils co-occurrents, jamais une copie de fond)."""
    def_tokens = _content_tokens(definition)
    for ref in references:
        ref_tokens = _content_tokens(ref)
        run = longest_shared_run(def_tokens, ref_tokens)
        if run > ANTI_COPY_MAX_SHARED_RUN:
            return True
        # plancher 3 -> 2 : recalibre pour des tokens PRIVES DE MOTS-OUTILS (mecaniquement
        # moins nombreux que les tokens bruts d'origine, D-043) -- verifie sur les cas ayant
        # motive le seuil relatif (MUNICHOIS "Habitant de Munich." -> 2 mots pleins, une copie
        # integrale doit rester rejetee : max(2, round(0.6*2)) = 2 = run integral, toujours
        # rejete). len(ref_tokens) < 2 : aucun seuil relatif significatif (un seul mot plein
        # partage est presque toujours le terme lui-meme/sa racine, pas une copie de structure)
        # -- seul le seuil absolu s'applique alors.
        if len(ref_tokens) >= 2:
            relative_threshold = max(2, round(0.6 * len(ref_tokens)))
            if run >= relative_threshold:
                return True
    return False


def render_template(term: str, classification_entry: dict) -> dict:
    """Palier 0 -- gabarit fixe, aucun appel LLM. Deux origines possibles, meme cout nul :

    "template" (verb_forms, D-018)         -> phrase construite ici, meme structure que la
                                               section conjugaison de app/View/word.php.
    "template_senses" (gabarit deja detecte
     dans une glose Kartmaan, voir
     render_kartmaan_template)              -> deja rendu par scripts/lib/
                                               reference_definitions.py, on ne fait que
                                               reformater ici.
    """
    if "template_senses" in classification_entry:
        senses = [
            {"pos": s["pos"], "gender": s["gender"], "definition": s["definition"], "source": "template"}
            for s in classification_entry["template_senses"]
        ]
        return {"term": term, "senses": senses}

    template = classification_entry["template"]
    detail = template["tense_label"]
    if template.get("person_label"):
        detail += ", " + template["person_label"]
    definition = "Forme conjuguée de %s (%s)." % (template["lemma"], detail)
    return {
        "term": term,
        "senses": [{"pos": "V", "gender": None, "definition": definition, "source": "template"}],
    }


def build_batch_prompt(batch: list[tuple[str, dict]]) -> str:
    lines = []
    for term, data in batch:
        senses = data.get("senses", [])
        max_senses = max(1, len(senses)) if senses else 1
        if senses:
            refs = "; ".join(
                "%s: \"%s\"" % (s["pos"], s["gloss"]) for s in senses
            )
            lines.append('- "%s" (%d sens, référence(s) : %s)' % (term.lower(), max_senses, refs))
        else:
            lines.append('- "%s" (1 sens, AUCUNE référence -- utilise tes connaissances, reste prudent)' % term.lower())
    return "Mots à traiter :\n" + "\n".join(lines)


def call_deepseek(api_key: str, batch: list[tuple[str, dict]], tmp_dir: Path, batch_id: int) -> tuple[dict, dict]:
    """Un appel API par lot. Écrit le payload dans un fichier temporaire et utilise curl (pas
    urllib) : le certificat système Windows de cet environnement échoue la vérification
    stricte OpenSSL 3.x de Python contre au moins un hôte externe déjà rencontré
    (kaikki.org) -- curl s'est révélé fiable partout, on garde la même approche ici plutôt que
    de risquer la même panne en plein run payant."""
    payload = {
        "model": MODEL,
        "messages": [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": build_batch_prompt(batch)},
        ],
        "response_format": {"type": "json_object"},
        "max_tokens": 800 * len(batch),
        # 0.3 -> 0.9 (D-043) : a 0.3, DeepSeek recopie la reference quasi mot pour mot des
        # qu'une glose de reference est fournie dans le prompt (mesure empirique sur le lot de
        # rattrapage --only-admitted : 42/43 sens generes = copie EXACTE de la reference, 100%
        # rejetes par le garde-fou anti-copie). 0.9 ramene ce taux a 35% sans introduire
        # d'erreur factuelle observee sur l'echantillon test (contrairement a 1.3, teste en
        # parallele : 9% de rejet mais confusion abayer/aboyer sur ABAYA, confusion genre
        # pin/sapin sur ABIES -- ecarte pour ce motif malgre son meilleur taux d'acceptation).
        "temperature": 0.9,
    }
    # Fichiers nommes par batch_id -- plusieurs lots en vol simultanement (concurrence)
    # partageraient sinon le meme fichier et se corromperaient mutuellement.
    payload_path = tmp_dir / ("request_%d.json" % batch_id)
    payload_path.write_text(json.dumps(payload, ensure_ascii=False), encoding="utf-8")

    response_path = tmp_dir / ("response_%d.json" % batch_id)
    result = subprocess.run(
        [
            "curl", "-fsS", "-X", "POST", API_URL,
            "-H", "Authorization: Bearer %s" % api_key,
            "-H", "Content-Type: application/json",
            "--data-binary", "@%s" % payload_path,
            "-o", str(response_path),
        ],
        capture_output=True,
        text=True,
    )
    if result.returncode != 0:
        raise RuntimeError("appel DeepSeek echoue (curl exit %d): %s" % (result.returncode, result.stderr))

    raw = json.loads(response_path.read_text(encoding="utf-8"))
    content = raw["choices"][0]["message"]["content"]
    parsed = json.loads(content)
    usage = raw.get("usage", {})
    return parsed, usage


def off_peak_advisory() -> str | None:
    """DeepSeek applique un tarif reduit en heures creuses (16h30-00h30 UTC, rappel
    utilisateur) -- avertissement seulement, jamais un blocage : un pilote a un cout
    negligeable a n'importe quelle heure."""
    hour_utc = datetime.now(timezone.utc).hour
    off_peak = hour_utc >= 16 or hour_utc < 1
    if off_peak:
        return None
    return (
        "ATTENTION : hors des heures creuses DeepSeek habituelles (~16h30-00h30 UTC) -- "
        "tarif plein applique. Sans consequence pour un pilote, mais a planifier pour tout "
        "run > quelques milliers de mots (rappel produit explicite)."
    )


def load_done_terms(output_path: Path) -> set[str]:
    if not output_path.exists():
        return set()
    done = set()
    with output_path.open("r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            try:
                row = json.loads(line)
            except ValueError:
                continue
            done.add(row["term"])
    return done


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--pilot", type=int, default=None)
    parser.add_argument("--only-admitted", action="store_true")
    parser.add_argument("--dry-run", action="store_true", help="classification + prompts, 0 appel API")
    parser.add_argument("--output", type=Path, default=OUTPUT_PATH)
    parser.add_argument(
        "--no-reference-retry", action="store_true",
        help="Nettoyage du residu dur (D-043) : ignore la reference Kartmaan/kaikki meme "
             "quand elle existe, traite tous les termes restants comme palier 3 (llm-only, "
             "trace comme tel dans 'source'). Reserve aux mots qui echouent encore apres "
             "plusieurs passes normales -- une reference tres courte/rigide pousse DeepSeek a "
             "la recopier presque telle quelle a chaque tentative (temperature=0.9 ou pas), "
             "et le garde-fou anti-copie n'a structurellement rien a rejeter s'il n'y a plus "
             "de reference dans le prompt.",
    )
    args = parser.parse_args()

    # DEEPSEEK_API_KEY : variable d'environnement en priorite (SCRABBLE_CONTACT_EMAIL,
    # public/index.php), fichier .env local en repli (jamais committe -- .gitignore) --
    # evite de jamais faire transiter la cle en clair dans une commande shell/historique.
    env_file = ROOT / ".env"
    if "DEEPSEEK_API_KEY" not in os.environ and env_file.exists():
        for line in env_file.read_text(encoding="utf-8").splitlines():
            if line.startswith("DEEPSEEK_API_KEY="):
                os.environ["DEEPSEEK_API_KEY"] = line.split("=", 1)[1].strip()
                break

    advisory = off_peak_advisory()
    if advisory:
        print(advisory, file=sys.stderr)

    targets = load_target_terms(only_admitted=args.only_admitted, sample=args.pilot)
    classification = classify(set(targets))

    if args.no_reference_retry:
        classification = {
            term: {"tier": 3, "source": "llm-only", "senses": []} for term in classification
        }

    done = load_done_terms(args.output)
    pending = [t for t in targets if t not in done]
    print("Cible : %d mots, deja en cache : %d, restants : %d" % (len(targets), len(done), len(pending)))

    if not pending:
        print("Rien a faire.")
        return 0

    api_key = os.environ.get("DEEPSEEK_API_KEY")
    if not args.dry_run and not api_key:
        print("DEEPSEEK_API_KEY absent de l'environnement -- utilisez --dry-run ou fournissez la cle.", file=sys.stderr)
        return 1

    definition_text_counter: Counter[str] = Counter()
    counter_lock = threading.Lock()
    rejected_anti_copy = 0
    rejected_quality = 0
    counts_lock = threading.Lock()
    total_usage = {"prompt_tokens": 0, "completion_tokens": 0}
    usage_lock = threading.Lock()
    write_lock = threading.Lock()

    tmp_dir = Path(os.environ.get("TEMP", "/tmp")) / "word_senses_gen"
    tmp_dir.mkdir(parents=True, exist_ok=True)

    args.output.parent.mkdir(parents=True, exist_ok=True)
    out_f = args.output.open("a", encoding="utf-8")

    try:
        template_terms = [t for t in pending if classification[t]["tier"] == 0]
        api_terms = [t for t in pending if classification[t]["tier"] != 0]

        for term in template_terms:
            row = render_template(term, classification[term])
            out_f.write(json.dumps(row, ensure_ascii=False) + "\n")
        out_f.flush()
        print("Palier 0 (gabarit, 0 appel API) : %d mots ecrits" % len(template_terms))

        if args.dry_run:
            print("--dry-run : %d mots auraient necessite un appel API (paliers 1-3), aucun appel effectue" % len(api_terms))
            if api_terms:
                sample_batch = [(t, classification[t]) for t in api_terms[:BATCH_SIZE]]
                print("--- apercu du premier lot ---")
                print(build_batch_prompt(sample_batch))
            return 0

        batches = [(i, api_terms[i:i + BATCH_SIZE]) for i in range(0, len(api_terms), BATCH_SIZE)]

        def process_batch(item: tuple[int, list[str]]) -> None:
            nonlocal rejected_anti_copy, rejected_quality

            i, batch_terms = item
            batch = [(t, classification[t]) for t in batch_terms]

            parsed = None
            usage: dict = {}
            for attempt in range(1, MAX_RETRIES + 1):
                try:
                    parsed, usage = call_deepseek(api_key, batch, tmp_dir, i)
                    break
                except Exception as e:  # noqa: BLE001
                    print("lot %d-%d, tentative %d/%d echouee : %s" % (i, i + len(batch_terms), attempt, MAX_RETRIES, e), file=sys.stderr)
                    if attempt < MAX_RETRIES:
                        time.sleep(2 ** attempt)

            if parsed is None:
                return

            with usage_lock:
                total_usage["prompt_tokens"] += usage.get("prompt_tokens", 0)
                total_usage["completion_tokens"] += usage.get("completion_tokens", 0)

            results_by_word = {r["word"].upper(): r for r in parsed.get("results", [])}
            rows_to_write = []
            local_anti_copy = 0
            local_quality = 0

            for term, data in batch:
                result = results_by_word.get(term)
                if result is None:
                    print("mot absent de la reponse : %s" % term, file=sys.stderr)
                    continue

                reference_glosses = [s["gloss"] for s in data.get("senses", [])]
                accepted_senses = []
                for sense in result.get("senses", []):
                    definition = (sense.get("definition") or "").strip()
                    pos = sense.get("pos")
                    if not definition or pos not in POS_VALUES:
                        continue
                    if is_too_similar(definition, reference_glosses):
                        local_anti_copy += 1
                        continue
                    if QUALITY_RED_FLAGS.search(definition):
                        local_quality += 1
                        continue

                    with counter_lock:
                        definition_text_counter[definition] += 1
                        dup_count = definition_text_counter[definition]
                    if dup_count > DUPLICATE_TEXT_ALERT_THRESHOLD:
                        print("ALERTE doublon texte (%dx) : %r" % (dup_count, definition), file=sys.stderr)

                    accepted_senses.append({
                        "pos": pos,
                        "gender": sense.get("gender") if pos == "N" else None,
                        "definition": definition,
                        "source": data["source"],
                    })

                if accepted_senses:
                    rows_to_write.append(json.dumps({"term": term, "senses": accepted_senses}, ensure_ascii=False))

            with counts_lock:
                rejected_anti_copy += local_anti_copy
                rejected_quality += local_quality

            with write_lock:
                for line in rows_to_write:
                    out_f.write(line + "\n")
                out_f.flush()

            print("lot %d-%d traite (%d mots)" % (i, i + len(batch_terms), len(batch_terms)))

        # Concurrence relevee a 9 (au-dessus du "5-6" prudent de methodologie-generation-
        # definitions-llm.md section 7) : demande explicite produit pour entrer dans la
        # fenetre heures creuses DeepSeek restante -- le lot test a 6 (2047 mots, ~3m30s,
        # aucune erreur de rate-limiting observee) laisse de la marge. A revenir a 6 si des
        # erreurs HTTP 429 apparaissent en cours de run complet. I/O-bound (subprocess curl +
        # fichiers), le GIL n'est pas un goulot ici.
        with ThreadPoolExecutor(max_workers=9) as executor:
            list(executor.map(process_batch, batches))
    finally:
        out_f.close()

    print("--- resume ---")
    print("tokens prompt=%d completion=%d" % (total_usage["prompt_tokens"], total_usage["completion_tokens"]))
    print("rejets garde-fou anti-copie : %d" % rejected_anti_copy)
    print("rejets scan qualite : %d" % rejected_quality)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

# Suite a donner (hors perimetre du pilote 100 mots) : concurrence limitee (5-6 lots en
# parallele, methodologie-generation-definitions-llm.md section 7) pour le passage a l'echelle
# -- le pilote reste sequentiel par simplicite, le volume ne le justifie pas.
