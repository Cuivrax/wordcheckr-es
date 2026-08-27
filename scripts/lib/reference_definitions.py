"""Classification en paliers + extraction de reference pour la generation de definitions
(D-0XX, pipeline pilote 100 mots -- voir reports/definitions-nature-feasibility-audit.md).

Lecture SEULE, hors ligne (D-007) : data/raw/french_dict.db (Kartmaan), data/raw/kaikki_fr/
*.jsonl.gz (Wiktionnaire francais), storage/dictionary_fr.sqlite (deja construite, deja
auditee -- pos/pos_secondary/gender D-018, verb_forms). Ne modifie rien, ne fait aucun appel
reseau/API payant -- produit uniquement une structure de classification consommee par
scripts/generate_word_senses.py (qui, lui, appelle un LLM).

Quatre paliers, priorite dans cet ordre (methodologie-generation-definitions-llm.md section
4, adaptee) :

    0  template   terme = forme conjuguee dans verb_forms (deja construite, deja fiabilisee
                   D-018) -> gabarit "Forme conjuguee de {LEMME} ({temps}, {personne})."
                   ZERO appel LLM.
    1  kartmaan    data/raw/french_dict.db, colonne "definitions" (JSON de gloses), lignes
                   NP/loc* exclues -- meme filtre que scripts/import_fr.py.
    2  kaikki      data/raw/kaikki_fr/*.jsonl.gz (Wiktionnaire francais, lang_code == "fr"),
                   UNIQUEMENT pour les termes absents du palier 1.
    3  llm-only    aucune reference disponible -- trace explicitement, le LLM utilise ses
                   seules connaissances (prompt adapte, prudence demandee).

Aucune des sources de reference n'est jamais affichee telle quelle sur le site (D-015) :
elles ancrent uniquement la reformulation LLM de scripts/generate_word_senses.py, qui
applique en plus un garde-fou anti-copie programmatique.
"""

from __future__ import annotations

import gzip
import json
import re
import sqlite3
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from normalize import normalize  # noqa: E402

ROOT = Path(__file__).resolve().parents[2]
KARTMAAN_PATH = ROOT / "data" / "raw" / "french_dict.db"
KAIKKI_PATH = ROOT / "data" / "raw" / "kaikki_fr" / "kaikki-dictionary-francais.jsonl.gz"
DICTIONARY_PATH = ROOT / "storage" / "dictionary_fr.sqlite"

MAX_SENSES = 2  # meme plafond que pos/pos_secondary (D-018) -- voir schema.sql word_senses.

# Doit rester synchronise avec scripts/import_fr.py (POS_MAP, POS_PROPER_NOUN) -- meme jeu
# ferme de 9 codes, meme filtre NP/loc*. Duplique plutot que refactore : import_fr.py est un
# script deterministe deja audite (integrity_check, reconstruction byte-a-byte), on evite d'y
# toucher pour ce lot. Toute evolution de l'un doit etre reportee dans l'autre (meme
# discipline que D-009 pour normalize.py).
POS_PROPER_NOUN = "NP"
KARTMAAN_POS_MAP: dict[str, str] = {
    "N": "N", "flex-nom": "N",
    "V": "V", "Vaux": "V", "flex-verb": "V",
    "Adj": "Adj", "adj-num": "Adj", "adj-pos": "Adj", "adj-int": "Adj", "adj-excl": "Adj",
    "flex-adj": "Adj",
    "Adv": "Adv", "flex-adv": "Adv",
    "interj": "Interj", "flex-interj": "Interj",
    "pronom": "Pronom", "pronom-pers": "Pronom", "pronom-int": "Pronom",
    "pronom-pos": "Pronom", "pronom-rel": "Pronom", "flex-pronom": "Pronom",
    "prép": "Prep",
    "conj": "Conj", "conj-coord": "Conj", "flex-conj": "Conj",
    "art-part": "Art",
}

# kaikki.org (Wiktionnaire francais) utilise son propre jeu d'etiquettes pos (wiktextract),
# distinct de Kartmaan -- mapping separe vers le meme jeu ferme de 9 codes. Etiquette absente
# de ce dict -> sens ignore (pas d'erreur), le mot retombe sur un autre sens/palier.
KAIKKI_POS_MAP: dict[str, str] = {
    "noun": "N",
    "verb": "V",
    "adj": "Adj",
    "adj_form": "Adj",
    "adv": "Adv",
    "pron": "Pronom",
    "prep": "Prep",
    "conj": "Conj",
    "intj": "Interj",
    "article": "Art",
    "det": "Art",
}

# Detection de gabarits DEJA presents dans les gloses Kartmaan elles-memes (flex-nom/
# flex-adj/flex-verb) -- trouve empiriquement lors du --dry-run du pilote, pas suppose a
# l'avance (voir reports/definitions-nature-feasibility-audit.md section 5.2, "a verifier").
# Une glose Kartmaan comme "Pluriel de CEBUANO." ou "Troisieme personne du singulier de
# l'imparfait du subjonctif du verbe ACETYLER." est DEJA un gabarit, pas un contenu lexical --
# l'envoyer au LLM pour "reformulation" gaspille un appel ET risque de declencher le garde-fou
# anti-copie (la reformulation d'un gabarit ressemble structurellement beaucoup a l'original).
# On extrait les FAITS grammaticaux par regex et on rend NOTRE PROPRE phrase (jamais le texte
# source copie tel quel, meme discipline D-015 que le reste du pipeline) -- palier 0
# (gratuit), pas palier 1/2. Couverture bonus non anticipee : les temps couverts ici (passe
# simple, subjonctif, conditionnel...) depassent la selection representative de verb_forms
# (D-018, limitee a present/futur/imparfait indicatif + participes) sans toucher a cette table
# deja auditee.
#
# S'applique aux DEUX sources de reference (Kartmaan ET kaikki.org), pas seulement Kartmaan :
# trouve empiriquement sur le pilote -- kaikki.org (extrait du Wiktionnaire francais) emploie
# la MEME convention de phrasing ("Pluriel de X.") pour les formes flechies, kaikki.org et
# Kartmaan partageant la meme filiation Wiktionnaire (WiktionaryX est lui-meme derive du
# Wiktionnaire francais). Un gabarit non detecte au palier 1 (Kartmaan) peut donc tres bien
# etre detecte au palier 2 (kaikki) pour le meme terme -- les deux appellent cette meme
# fonction plutot que d'avoir chacune leur propre detection.
_GRAMMATICAL_TEMPLATE_PATTERNS: list[tuple[re.Pattern, str]] = [
    # "de (.+)" OU "d['’](.+)" -- elision devant un lemme a initiale vocalique/h muet
    # ("Pluriel d'etagere." et pas seulement "Pluriel de cebuano."), manquee par la version
    # initiale de ces 3 gabarits (trouve empiriquement : 396 mots bloques sur le lot de
    # rattrapage --only-admitted, tous des lemmes a initiale vocalique -- voir D-043).
    (re.compile(r"^Pluriel (?:de |d['’])(.+)\.$", re.IGNORECASE), "plural"),
    (
        re.compile(
            r"^Féminin(?: (singulier|pluriel))? (?:de |d['’])(.+)\.$", re.IGNORECASE
        ),
        "feminine",
    ),
    (
        re.compile(
            r"^Masculin(?: (singulier|pluriel))? (?:de |d['’])(.+)\.$", re.IGNORECASE
        ),
        "masculine",
    ),
    (
        # "...de l'imparfait du subjonctif DU VERBE acétyler." (phrasing la plus frequente)
        re.compile(
            r"^(Première|Deuxième|Troisième) personne du (singulier|pluriel) "
            r"(?:de l['’]|du )(.+?) du verbe (\S+)\.$",
            re.IGNORECASE,
        ),
        "conjugated_person",
    ),
    (
        # "...de l'indicatif présent DE déclamper." / "...du passé simple DE knockouter."
        # (meme information, "du verbe" omis -- variante observee empiriquement sur le
        # pilote, notamment via kaikki.org).
        re.compile(
            r"^(Première|Deuxième|Troisième) personne du (singulier|pluriel) "
            r"(?:de l['’]|du )(.+?) (?:de|d['’])\s?(\S+)\.$",
            re.IGNORECASE,
        ),
        "conjugated_person",
    ),
    (
        re.compile(
            r"^Participe (présent|passé)(?: (masculin|féminin))?(?: (singulier|pluriel))? "
            r"du verbe (.+)\.$",
            re.IGNORECASE,
        ),
        "participle",
    ),
    (re.compile(r"^Du verbe (.+)\.$", re.IGNORECASE), "verb_form_generic"),
]

_PERSON_ORDINAL_LABEL = {
    "première": "1re",
    "deuxième": "2e",
    "troisième": "3e",
}

# Exceptions a h aspire les plus courantes (pas d'elision : "de hasard", pas "d'hasard") --
# liste non exhaustive mais couvre les cas usuels, en minuscules pour comparaison insensible
# a la casse. Le defaut (h absent de cette liste) est elide : la majorite des mots francais en
# h ont un h muet.
_ASPIRATE_H_WORDS = {
    "hasard", "haricot", "hangar", "hanche", "hachis", "haine", "hall", "halte", "hamac",
    "hanter", "harceler", "hardi", "harnais", "harpe", "hate", "hausse", "haut", "herisson",
    "heron", "heros", "hibou", "hockey", "hollandais", "homard", "honte", "hoquet", "hors",
    "houle", "housse", "huit", "hurler", "hutte", "hache", "honnir",
}


def _elided_de(lemma: str) -> str:
    """"de %s" ou "d'%s" selon l'initiale du lemme (elision francaise standard devant voyelle
    ou h muet) -- necessaire une fois les gabarits plural/feminine/masculine ouverts aux
    lemmes a initiale vocalique (D-043, "Pluriel d'etagere." et pas seulement "Pluriel de
    cebuano."), sans quoi le texte rendu serait grammaticalement incorrect."""
    first = lemma[:1].lower()
    first_word = lemma.split(" ", 1)[0].lower().strip("'’")
    if first in "aeiouyàâäéèêëïîôöùûüÿ" or (first == "h" and first_word not in _ASPIRATE_H_WORDS):
        return "d'%s" % lemma
    return "de %s" % lemma


def render_grammatical_template(gloss: str) -> str | None:
    """Si `gloss` correspond a un gabarit grammatical Kartmaan reconnu, retourne NOTRE propre
    phrase (jamais le texte source). None si la glose ne correspond a aucun gabarit connu --
    elle reste alors un sens lexical normal, achemine vers le LLM (palier 1) comme avant."""
    gloss = gloss.strip()
    for pattern, kind in _GRAMMATICAL_TEMPLATE_PATTERNS:
        match = pattern.match(gloss)
        if match is None:
            continue
        if kind == "plural":
            return "Forme plurielle %s." % _elided_de(match.group(1))
        if kind == "feminine":
            number, lemma = match.groups()
            return "Forme féminine%s %s." % (
                " plurielle" if number == "pluriel" else "", _elided_de(lemma)
            )
        if kind == "masculine":
            number, lemma = match.groups()
            return "Forme masculine%s %s." % (
                " plurielle" if number == "pluriel" else "", _elided_de(lemma)
            )
        if kind == "conjugated_person":
            ordinal_word, number, tense_phrase, lemma = match.groups()
            ordinal = _PERSON_ORDINAL_LABEL.get(ordinal_word.lower(), ordinal_word.lower())
            return "Forme conjuguée du verbe %s (%s, %s personne du %s)." % (
                lemma, tense_phrase.strip(), ordinal, number
            )
        if kind == "participle":
            tense, gender, number, lemma = match.groups()
            detail = tense
            if gender:
                detail += " " + gender
            if number:
                detail += " " + number
            return "Participe %s du verbe %s." % (detail, lemma)
        if kind == "verb_form_generic":
            return "Forme conjuguée du verbe %s." % match.group(1)
    return None


TENSE_LABELS = {
    "present": "présent",
    "future": "futur",
    "imperfect": "imparfait",
    "participle_present": "participe présent",
    "participle_past": "participe passé",
}
PERSON_LABELS = {
    "1s": "1re personne du singulier",
    "2s": "2e personne du singulier",
    "3s": "3e personne du singulier",
    "1p": "1re personne du pluriel",
    "2p": "2e personne du pluriel",
    "3p": "3e personne du pluriel",
}


def load_verb_form_templates(term_keys: set[str]) -> dict[str, dict]:
    """Palier 0 -- lit verb_forms DEJA CONSTRUITE dans storage/dictionary_fr.sqlite (pas le
    CSV hbenbel brut : la table a deja ete filtree/fiabilisee par D-018, c'est la source la
    plus sure disponible). Une ligne par forme conjuguee retenue dans term_keys ; si une forme
    a plusieurs lignes (homographe entre deux temps/personnes, rare), la premiere par ordre
    "id" est gardee -- meme discipline de determinisme que le reste du projet.

    Ne retourne QUE des formes distinctes de leur lemme (form_normalized != lemma_normalized)
    -- verb_forms ne contient de toute facon jamais l'infinitif comme forme (tense n'inclut
    pas 'infinitive', voir schema.sql), donc cette garde est structurelle plutot que
    necessaire en pratique, gardee par clarte.
    """
    if not term_keys:
        return {}

    connection = sqlite3.connect(f"file:{DICTIONARY_PATH.as_posix()}?mode=ro", uri=True)
    result: dict[str, dict] = {}
    try:
        rows = connection.execute(
            "SELECT form_normalized, lemma_normalized, tense, person FROM verb_forms ORDER BY id"
        )
        for form_normalized, lemma_normalized, tense, person in rows:
            if form_normalized not in term_keys or form_normalized == lemma_normalized:
                continue
            if form_normalized in result:
                continue
            result[form_normalized] = {
                "lemma": lemma_normalized,
                "tense": tense,
                "tense_label": TENSE_LABELS.get(tense, tense),
                "person": person,
                "person_label": PERSON_LABELS.get(person) if person else None,
            }
    finally:
        connection.close()

    return result


def load_kartmaan_glosses(term_keys: set[str]) -> dict[str, list[dict]]:
    """Palier 1 -- lit data/raw/french_dict.db (colonne "definitions", JSON de gloses par
    ligne forme+pos+gender). Meme filtre NP/loc* que scripts/import_fr.py::load_pos_gender().

    Au plus MAX_SENSES entrees par terme, une par code pos DISTINCT rencontre en premier
    (ordre id, meme convention de determinisme que pos/pos_secondary D-018) -- Kartmaan liste
    parfois plusieurs gloses A L'INTERIEUR d'une meme ligne pos (ex. "chat" a 3 lignes N
    distinctes : animal, messagerie, zodiaque) ; on ne garde que la PREMIERE gloss de la
    PREMIERE ligne de chaque pos retenu -- une selection representative, pas exhaustive
    (meme esprit que D-018 pour verb_forms : "selection representative, pas le paradigme
    complet").
    """
    if not term_keys:
        return {}

    connection = sqlite3.connect(f"file:{KARTMAAN_PATH.as_posix()}?mode=ro", uri=True)
    by_term: dict[str, list[dict]] = {}
    try:
        query = "SELECT forme, pos, gender, definitions FROM mots ORDER BY id"
        for form, pos, gender, definitions_json in connection.execute(query):
            if not pos or pos == POS_PROPER_NOUN or pos.lower().startswith("loc"):
                continue
            canonical = KARTMAAN_POS_MAP.get(pos)
            if canonical is None:
                continue
            normalized = normalize(form)
            if normalized not in term_keys:
                continue

            existing = by_term.setdefault(normalized, [])
            if len(existing) >= MAX_SENSES or any(e["pos"] == canonical for e in existing):
                continue

            try:
                senses = json.loads(definitions_json)
            except (TypeError, ValueError):
                continue
            if not senses:
                continue

            first = senses[0]
            gloss = (first.get("gloss") or "").strip()
            if not gloss:
                continue
            examples = first.get("exemples") or []

            existing.append({
                "pos": canonical,
                "gender": gender if gender in ("m", "f", "e") else None,
                "gloss": gloss,
                "rendered_template": render_grammatical_template(gloss),
                "example": examples[0] if examples else None,
            })
    finally:
        connection.close()

    # by_term.setdefault() ci-dessus cree une entree meme quand aucune gloss exploitable n'a
    # ete ajoutee (ligne rejetee : JSON invalide, gloss vide...) -- filtree ici plutot que de
    # laisser une liste [] atteindre classify() (all([]) vaudrait True par vacuite, routant a
    # tort un terme SANS aucune reference vers le palier 0 avec 0 sens).
    return {k: v for k, v in by_term.items() if v}


def load_kaikki_glosses(term_keys: set[str]) -> dict[str, list[dict]]:
    """Palier 2 -- UNE SEULE passe streaming sur data/raw/kaikki_fr/*.jsonl.gz (Wiktionnaire
    francais, lang_code == "fr" uniquement -- pas "kaikki.org/dictionary/French/", verifie
    par echantillonnage : cette autre page documente le francais avec des gloses en ANGLAIS,
    inutilisable ici). Le fichier n'est JAMAIS charge entierement en memoire (gzip.open en
    mode texte streamant, une ligne JSON a la fois).

    Appeler avec un term_keys DEJA REDUIT au residu non couvert par les paliers 0/1 (pas la
    base entiere) -- limite le cout de ce passage et la taille du resultat en memoire.
    """
    if not term_keys:
        return {}
    if not KAIKKI_PATH.exists():
        return {}

    remaining = set(term_keys)
    by_term: dict[str, list[dict]] = {}

    with gzip.open(KAIKKI_PATH, "rt", encoding="utf-8") as f:
        for line in f:
            if not remaining:
                break
            try:
                entry = json.loads(line)
            except ValueError:
                continue
            if entry.get("lang_code") != "fr":
                continue

            word = entry.get("word")
            if not word:
                continue
            normalized = normalize(word)
            if normalized not in remaining:
                continue

            canonical = KAIKKI_POS_MAP.get(entry.get("pos"))
            if canonical is None:
                continue

            existing = by_term.setdefault(normalized, [])
            if len(existing) >= MAX_SENSES or any(e["pos"] == canonical for e in existing):
                continue

            senses = entry.get("senses") or []
            if not senses:
                continue
            first_sense = senses[0]
            glosses = first_sense.get("glosses") or []
            if not glosses:
                continue

            gloss = glosses[0].strip()
            existing.append({
                "pos": canonical,
                "gender": None,  # kaikki.org (fr) ne porte pas de champ genre exploitable ici
                "gloss": gloss,
                "rendered_template": render_grammatical_template(gloss),
                "example": None,
            })

            if len(existing) >= MAX_SENSES:
                remaining.discard(normalized)

    # meme garde que load_kartmaan_glosses ci-dessus -- setdefault() peut laisser une entree
    # vide si une ligne matchee n'a in fine fourni aucune gloss exploitable.
    return {k: v for k, v in by_term.items() if v}


def classify(term_keys: set[str]) -> dict[str, dict]:
    """Point d'entree principal. Retourne {normalized: {"tier": int, "source": str, ...}} --
    "..." varie par palier (voir load_verb_form_templates/load_kartmaan_glosses/
    load_kaikki_glosses ci-dessus pour la forme exacte). Priorite stricte 0 > 1 > 2 > 3,
    jamais de fusion entre paliers pour un meme terme.
    """
    result: dict[str, dict] = {}

    templates = load_verb_form_templates(term_keys)
    for normalized, data in templates.items():
        result[normalized] = {"tier": 0, "source": "template", "template": data}

    # Si TOUTES les gloses retenues pour un terme (Kartmaan OU kaikki) sont elles-memes des
    # gabarits grammaticaux deja reconnus (render_grammatical_template), aucun appel LLM n'est
    # necessaire -- palier 0, notre propre phrase, jamais le texte source copie. Cas mixte (au
    # moins un sens lexical reel a cote d'un gabarit) : reste au palier de reference (1 ou 2)
    # par simplicite pour ce pilote, TOUS les sens du terme passent alors par le LLM comme
    # avant -- pas de decoupage par sens individuel dans cette premiere version.
    def _as_template_or_reference(senses: list[dict]) -> dict:
        if all(s.get("rendered_template") for s in senses):
            return {
                "tier": 0,
                "source": "template",
                "template_senses": [
                    {"pos": s["pos"], "gender": s["gender"], "definition": s["rendered_template"]}
                    for s in senses
                ],
            }
        return None

    remaining = term_keys - result.keys()
    kartmaan = load_kartmaan_glosses(remaining)
    for normalized, senses in kartmaan.items():
        result[normalized] = _as_template_or_reference(senses) or {
            "tier": 1, "source": "kartmaan", "senses": senses,
        }

    remaining = remaining - kartmaan.keys()
    kaikki = load_kaikki_glosses(remaining)
    for normalized, senses in kaikki.items():
        result[normalized] = _as_template_or_reference(senses) or {
            "tier": 2, "source": "kaikki", "senses": senses,
        }

    remaining = remaining - kaikki.keys()
    for normalized in remaining:
        result[normalized] = {"tier": 3, "source": "llm-only", "senses": []}

    return result


def load_target_terms(only_admitted: bool = False, sample: int | None = None) -> list[str]:
    """Termes cibles depuis storage/dictionary_fr.sqlite, ordre alphabetique (normalized) --
    determinisme du pilote : deux runs avec le meme --pilot N tirent le meme echantillon tant
    que la base ne change pas, sans dependre d'un generateur aleatoire non trace."""
    connection = sqlite3.connect(f"file:{DICTIONARY_PATH.as_posix()}?mode=ro", uri=True)
    try:
        where = "WHERE is_admitted = 1" if only_admitted else ""
        query = f"SELECT normalized FROM terms {where} ORDER BY normalized"
        terms = [row[0] for row in connection.execute(query)]
    finally:
        connection.close()

    if sample is not None and sample < len(terms):
        step = len(terms) / sample
        terms = [terms[int(i * step)] for i in range(sample)]

    return terms


if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--pilot", type=int, default=None, help="Echantillon de N mots")
    parser.add_argument("--only-admitted", action="store_true")
    args = parser.parse_args()

    targets = load_target_terms(only_admitted=args.only_admitted, sample=args.pilot)
    classification = classify(set(targets))

    tier_counts = {0: 0, 1: 0, 2: 0, 3: 0}
    for data in classification.values():
        tier_counts[data["tier"]] += 1

    total = len(classification)
    print("Termes classes : %d" % total)
    for tier, label in ((0, "template (gratuit)"), (1, "kartmaan"), (2, "kaikki"), (3, "llm-only")):
        count = tier_counts[tier]
        pct = (count / total * 100) if total else 0.0
        print("  palier %d %-20s %6d  (%.1f%%)" % (tier, label, count, pct))
