"""Détecteur de défaut de concaténation (saut de ligne perdu) entre deux sources.

Contexte : `data/raw/file_2017.json` porte un défaut de données confirmé par inspection
directe (`ababillaraababillarais` = `ababillara` + `ababillarais`, deux entrées valides
fusionnées sans séparateur) -- voir data/raw/PROVENANCE.md §1 pour le détail complet.
Ce module implémente un balayage SYSTÉMATIQUE (pas seulement ce cas connu) pour
scripts/import_es.py, avec une méthode plus précise que la recherche préalable
(reports/es-site-feasibility-audit.md du site français, §1.4, qui remontait 824
candidats bruts par simple splittabilité) :

1. candidat brut : un mot W, présent dans la source A mais ABSENT de la source B, se
   découpe en deux morceaux W1 + W2 tels que W1 ET W2 soient TOUS DEUX des entrées de
   la source B, ET qu'aucun des deux morceaux ne soit lui-même une entrée de la source
   A -- cette dernière condition est la clé qui réduit drastiquement le bruit : elle
   encode que le défaut REMPLACE deux lignes originales par une seule ligne fusionnée,
   plutôt que d'accepter n'importe quel mot par ailleurs réel qui se laisse
   accidentellement découper en deux sous-mots existants (ex. "dimanante" se découpe en
   "dima"+"nante", tous deux réels par coïncidence, mais "dimanante" est un participe
   présent authentique de "dimanar" -- pas un artefact) ;
2. confirmation par contexte local : le mot W doit occuper, dans l'ordre du fichier
   source A, la position où W1 seul se serait alphabétiquement trouvé (les voisins
   immédiats partagent le même radical que W1) -- un défaut de saut de ligne perdu ne
   peut se produire qu'entre deux lignes ADJACENTES dans un fichier originellement
   trié, jamais entre deux mots alphabétiquement éloignés.

Utilisé par scripts/import_es.py dans les deux sens (file_2017 vs an_array, et
an_array vs file_2017) avant la fusion.
"""

from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class ConcatDefect:
    merged: str
    part1: str
    part2: str


def raw_candidates(
    source_a_only: set[str], source_b: set[str], source_a: set[str]
) -> list[ConcatDefect]:
    """Étape 1 : candidats bruts par découpe en deux entrées de la source B.

    `source_a_only` = mots présents dans la source A mais absents de la source B
    (restreint la recherche aux mots qui n'ont pas déjà été corroborés ailleurs -- un
    mot présent des deux côtés ne peut pas être un artefact d'UNE source précise).
    """
    candidates: list[ConcatDefect] = []
    for word in sorted(source_a_only):
        length = len(word)
        for i in range(2, length - 1):
            part1, part2 = word[:i], word[i:]
            if (
                part1 in source_b
                and part2 in source_b
                and part1 not in source_a
                and part2 not in source_a
            ):
                candidates.append(ConcatDefect(word, part1, part2))
    return candidates


def confirm_by_local_context(
    candidate: ConcatDefect, source_a_ordered: list[str]
) -> bool:
    """Étape 2 : test mécanique d'adjacence alphabétique, pas une heuristique de
    similarité floue.

    Un défaut de saut de ligne perdu remplace exactement DEUX lignes ADJACENTES d'un
    fichier trié (..., X, part1, part2, Y, ...) par UNE seule ligne fusionnée
    (..., X, "part1part2", Y, ...). Ce test vérifie donc que le voisin PRÉCÉDENT du
    mot fusionné est alphabétiquement < part1, ET que le voisin SUIVANT est
    alphabétiquement > part2 -- exactement la condition qui aurait rendu le fichier
    original (avant le bug) correctement trié à cet endroit précis.
    Rejette par construction un mot par ailleurs réel qui se laisse accidentellement
    découper en deux sous-mots existants mais SANS RAPPORT avec son voisinage réel
    (ex. "dimanante", entouré de "dimanan"/"dimanantes" -- le préfixe "dima" n'est PAS
    plus grand que son prédécesseur réel "dimanando", donc rejeté ; alors que
    "ababillaraababillarais", entouré de "ababillar"/"ababillaramos", a bien
    "ababillar" < "ababillara" < "ababillaramos", donc confirmé).
    """
    try:
        index = source_a_ordered.index(candidate.merged)
    except ValueError:
        return False

    predecessor = source_a_ordered[index - 1] if index > 0 else None
    successor = source_a_ordered[index + 1] if index + 1 < len(source_a_ordered) else None

    if predecessor is not None and not (predecessor < candidate.part1):
        return False
    if successor is not None and not (candidate.part2 < successor):
        return False

    return True


def detect(
    source_a_ordered: list[str], source_b: set[str]
) -> list[ConcatDefect]:
    """Pipeline complet : renvoie les défauts CONFIRMÉS (étapes 1 et 2 combinées)."""
    source_a = set(source_a_ordered)
    source_a_only = source_a - source_b

    confirmed = []
    for candidate in raw_candidates(source_a_only, source_b, source_a):
        if confirm_by_local_context(candidate, source_a_ordered):
            confirmed.append(candidate)
    return confirmed
