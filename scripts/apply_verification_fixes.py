#!/usr/bin/env python3
"""Applique les corrections issues de scripts/verify_word_senses.py -- PAS un bulk-apply
aveugle. Revue manuelle d'un echantillon complet (tous les N/Adj "incorrect", ~139 entrees)
a trouve trois categories melangees dans les 580 "incorrect" :

1. vraies corrections (majorite) -- ex. BOYERIE "piece pour domestiques" -> "batiment
   d'elevage des bovins" (confirme par une revue manuelle independante plus tot).
2. le verificateur se trompe LUI-MEME sur une definition deja correcte -- ex. CONFITURE
   (correct : "confiture de fruits") "corrige" a tort en une forme de verbe inexistante
   "confiturer" ; ORPHELINAT, EFFICACE meme probleme. Appliquer ces "corrections"
   INTRODUIRAIT une erreur la ou il n'y en avait pas.
3. texte de "correction" malforme -- le champ contient le RAISONNEMENT du verificateur
   ("Non, 'notre' est un adjectif possessif... Correction: ...") plutot qu'une definition
   utilisable telle quelle (NOTRE, PERSONE).

Regle appliquee ici, apres revue manuelle :
- correction identique a l'original -> ignoree (le verdict "incorrect" du verificateur
  etait lui-meme incoherent avec sa propre correction, rien a changer)
- correction qui ressemble a du raisonnement plutot qu'a une definition (contient "?",
  commence par "Non", contient "Correction:", ou depasse une longueur raisonnable pour
  une definition -- voir MALFORMED_PATTERNS) -> IGNOREE, l'entree GARDE sa definition
  originale plutot que d'afficher du texte casse
- override manuel explicite pour les cas ou le verificateur s'est trompe sur une
  definition deja correcte (OVERRIDE_KEEP_ORIGINAL, trouves par revue manuelle, pas
  un mecanisme automatique -- toute extension de cette liste doit passer par une
  revue humaine du cas precis, jamais une heuristique generique)
- sinon -> la correction est appliquee

Usage :
    python scripts/apply_verification_fixes.py --dry-run
    python scripts/apply_verification_fixes.py
"""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
CACHE_PATH = ROOT / "data" / "generated" / "word_senses_cache.jsonl"
VERIFICATION_PATH = ROOT / "data" / "generated" / "word_senses_verification.jsonl"

MALFORMED_PATTERNS = re.compile(
    r"\?|^Non,|Correction\s*:|n'est pas un|n'est pas une|"
    r", pas un | pas une |n'a pas ce sens",
    re.IGNORECASE,
)

# Trouves par revue manuelle (voir docblock) -- le verificateur a juge "incorrect" une
# definition qui etait en realite correcte, et sa "correction" proposee introduirait une
# vraie erreur. Explicitement nommes, jamais une regle generique.
OVERRIDE_KEEP_ORIGINAL = {
    ("CONFITURE", 1),
    ("ORPHELINAT", 1),
    ("EFFICACE", 1),
    # 2e lot (regeneration des 133 mots retires) : la "correction" change la nature de la
    # phrase (adjectif -> nom ou l'inverse) sans que le "pos" stocke ne change -- appliquer
    # aurait cree une incoherence pos/texte pire que l'original, qui restait correct et
    # grammaticalement coherent avec son propre pos.
    ("ASSERTORIQUE", 1),  # N, original deja phrase en nom ("Enonce de..."), correction en adjectif
    ("OIDIE", 1),         # Adj, original deja phrase en adjectif ("Qualifie..."), correction en nom
    # COLAUREATE : la "correction" retire l'information de genre que la morphologie du mot
    # porte deja (colaureat-E = forme feminine) -- une regression, pas une amelioration.
    ("COLAUREATE", 1),
    # CAFETE-1 (V, "cafeter" = denoncer/rapporter, verbe argotique reel) et PARISIANISTES-1
    # (Adj) : le verificateur a confondu deux sens DIFFERENTS du meme terme entre eux (sa
    # "correction" pour le sens 1 est en realite le texte du sens 2, verifie par lecture
    # directe des deux verdicts cote a cote) -- pas une amelioration, une contamination
    # croisee. Trouve en restaurant EFFICACE (meme lot, meme bug de suppression au niveau du
    # terme) : restaurer le texte original SANS ce garde-fou l'aurait a nouveau expose a la
    # meme correction erronee au prochain passage de ce script.
    ("CAFETE", 1),
    ("PARISIANISTES", 1),
}

# Trouves par revue manuelle : ni l'original ni la correction proposee ne sont assez fiables
# pour etre affiches (terme trop obscur pour verification independante, les deux versions se
# contredisent frontalement sans element pour trancher) -- retire plutot que d'afficher une
# supposition non verifiee. Explicitement nommes, jamais une regle generique.
OVERRIDE_REMOVE = {
    ("SURALES", 1),  # "plaines de l'Orenoque" vs "plateaux andins" -- aucune base pour trancher
}


def is_usable_correction(correction: str | None) -> bool:
    if not correction:
        return False
    if MALFORMED_PATTERNS.search(correction):
        return False
    if len(correction) > 160:  # une definition reste "moins de 20 mots" (system prompt)
        return False
    return True


def is_verifier_tense_regression(original: str, correction: str) -> bool:
    """Trouve par revue manuelle (2e echantillon, 60 entrees "verbe") : le verificateur
    "corrige" systematiquement (42 cas confirmes sur l'ensemble du lot) "imparfait du
    subjonctif" en "passe simple" pour des formes en -AT (GRILLAT, HANDICAPAT, MARMONNAT...).
    Erreur du VERIFICATEUR, pas de la donnee d'origine : ces formes sont stockees SANS
    accent (D-009, normalisation), "GRILLAT" est en realite "grillat" -> circonflexe absent
    a l'affichage mais present dans la source structuree AVANT normalisation -- le
    verificateur, qui ne voit que la forme normalisee (sans accent), devine "passe simple"
    a tort. Le tag de temps d'origine vient de l'extraction reglee sur la reference
    structuree elle-meme (render_grammatical_template()), plus fiable ici qu'une supposition
    LLM sur une chaine sans diacritique -- on garde l'original pour cette classe precise de
    desaccord plutot que de faire confiance au verificateur."""
    return "imparfait du subjonctif" in original.lower() and "passé simple" in correction.lower()


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()

    verdicts: dict[tuple[str, int], dict] = {}
    with VERIFICATION_PATH.open(encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            row = json.loads(line)
            verdicts[(row["term"], row["sense_rank"])] = row

    entries = []
    with CACHE_PATH.open(encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if line:
                entries.append(json.loads(line))

    applied = 0
    skipped_noop = 0
    skipped_malformed = 0
    skipped_override = 0
    skipped_tense_regression = 0
    skipped_template = 0
    removed_no_replacement = 0
    removed_override = 0

    for entry in entries:
        new_senses = []
        for rank, sense in enumerate(entry["senses"], start=1):
            key = (entry["term"], rank)
            verdict_row = verdicts.get(key)

            if verdict_row is None or verdict_row["verdict"] != "incorrect":
                new_senses.append(sense)
                continue

            # Garde-fou verdict perime : si le texte actuellement en cache ne correspond
            # PLUS a "original_definition" du verdict, c'est qu'une regeneration a eu lieu
            # DEPUIS cette verification (meme (term, sense_rank), texte differe -- trouve en
            # verifiant POLLUPOSTEUSE : appliquer aurait REINTRODUIT l'ancienne definition
            # fausse "femme qui travaille a la poste" par-dessus la nouvelle, meilleure,
            # version). Un verdict perime n'a plus rien a dire sur le texte actuel -- ignore,
            # le texte actuel reste en l'etat jusqu'a sa PROPRE verification.
            if verdict_row["original_definition"] != sense["definition"]:
                new_senses.append(sense)
                continue

            # source == "template" : phrase grammaticale construite mecaniquement a partir
            # du lemme extrait TEL QUEL de la reference Kartmaan/kaikki (render_grammatical_
            # template(), scripts/lib/reference_definitions.py) -- correcte par construction,
            # jamais un texte LLM. Revue manuelle (D-043, lot de rattrapage --no-reference-
            # retry) : le verificateur, sans avoir la glose de la forme de base sous les yeux,
            # "corrige" parfois ces phrases par une elaboration fausse sur le mot de base
            # (ex. EPAIRS "Forme plurielle de epair." -> correction affirmant a tort que
            # "epair" est "une variante de epervier" ; memes constats sur EPERVINS/SHILOMS/
            # TAMPICOS/BABIES) -- une hallucination du verificateur, pas une erreur du
            # gabarit. On ne laisse jamais le verificateur "ameliorer" une phrase de gabarit.
            if sense.get("source") == "template":
                skipped_template += 1
                new_senses.append(sense)
                continue

            if key in OVERRIDE_KEEP_ORIGINAL:
                skipped_override += 1
                new_senses.append(sense)
                continue

            if key in OVERRIDE_REMOVE:
                removed_override += 1
                continue

            correction = verdict_row.get("correction")

            if correction == sense["definition"]:
                skipped_noop += 1
                new_senses.append(sense)
                continue

            if is_verifier_tense_regression(sense["definition"], correction):
                skipped_tense_regression += 1
                new_senses.append(sense)
                continue

            if not is_usable_correction(correction):
                skipped_malformed += 1
                # Aucune definition utilisable -- retiree plutot que de garder une
                # definition confirmee fausse par le verificateur (et jamais un texte
                # casse). Le terme repasse "sans definition", meme convention que
                # "absence de donnee, pas une erreur".
                removed_no_replacement += 1
                continue

            new_senses.append({**sense, "definition": correction})
            applied += 1

        # Assignation INCONDITIONNELLE -- y compris quand new_senses est vide (tous les
        # sens de ce terme retires comme malformes). Un "if new_senses:" ici serait un bug
        # reel : entry["senses"] garderait alors silencieusement son ANCIENNE valeur (les
        # sens fausses/malformes d'origine), l'entree ne serait jamais retiree a l'ecriture
        # -- trouve en verifiant NOTRE/PERSONE apres une premiere execution.
        entry["senses"] = new_senses

    print("corrections appliquees : %d" % applied)
    print("ignorees (correction identique) : %d" % skipped_noop)
    print("ignorees (override manuel, verificateur dans l'erreur) : %d" % skipped_override)
    print("ignorees (regression de temps, verificateur dans l'erreur) : %d" % skipped_tense_regression)
    print("ignorees (source=template, phrase grammaticale correcte par construction) : %d" % skipped_template)
    print("ignorees (texte malforme) -> definition retiree : %d" % skipped_malformed)
    print("override manuel -> definition retiree (ni original ni correction fiables) : %d" % removed_override)
    print("(retrait net de definitions) : %d" % (removed_no_replacement + removed_override))

    if args.dry_run:
        print("--dry-run : fichier non modifie")
        return 0

    with CACHE_PATH.open("w", encoding="utf-8") as f:
        for entry in entries:
            if entry["senses"]:
                f.write(json.dumps(entry, ensure_ascii=False) + "\n")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
