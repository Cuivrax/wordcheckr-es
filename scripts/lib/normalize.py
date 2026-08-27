"""Normalisation, score et dérivés — source unique de vérité.

Toute règle de transformation d'un terme vit ici et nulle part ailleurs.
Le runtime PHP réimplémente strictement les mêmes règles (D-007) ; tout écart
entre les deux implémentations est un bug de correspondance, pas une variante.
"""

from __future__ import annotations

import re
import unicodedata

# Les ligatures ne sont PAS décomposées par NFD. Sans ce mapping explicite,
# œil, bœuf et nœud sont rejetés comme « caractère hors A-Z » alors que OEIL,
# BOEUF et OEUF sont des mots admis à l'ODS8.
LIGATURES = str.maketrans({"œ": "oe", "Œ": "OE", "æ": "ae", "Æ": "AE"})

# Le plateau fait 15 cases : un mot de plus de 15 lettres ne peut jamais être
# posé. Le plafond s'applique donc aux DONNÉES, pas seulement à la saisie
# (D-010, révisée).
#
# Ce plafond est aussi un contrôle d'intégrité de la source. Le fichier
# data/raw/ods8.json compte 411 430 entrées, alors que l'ODS8 publié par
# Larousse en compte 402 325. L'écart est exactement les 9 105 formes de 16 à
# 21 lettres — des conjugaisons générées (CINEMATOGRAPHIASSIONS,
# REAPPROVISIONNERAIENT) qui ne figurent pas dans l'ODS. Les afficher comme
# « admises au Scrabble » serait faux. Le patch ODS9 confirme la borne : il ne
# contient aucune forme de plus de 15 lettres.
MIN_LENGTH = 2
MAX_LENGTH = 15

VALID_TERM = re.compile(r"^[A-Z]{%d,%d}$" % (MIN_LENGTH, MAX_LENGTH))

# Valeurs des tuiles françaises, identiques au prototype.
TILE_SCORES = {
    "A": 1, "B": 3, "C": 3, "D": 2, "E": 1, "F": 4, "G": 2, "H": 4, "I": 1,
    "J": 8, "K": 10, "L": 1, "M": 2, "N": 1, "O": 1, "P": 3, "Q": 8, "R": 1,
    "S": 1, "T": 1, "U": 1, "V": 4, "W": 10, "X": 10, "Y": 10, "Z": 10,
}


def normalize(form: str) -> str:
    """Ligatures, puis NFD, puis retrait des diacritiques, puis majuscules.

    Ne valide pas : renvoie la forme normalisée telle quelle, éventuellement
    invalide. Utiliser is_valid() pour trancher.
    """
    form = form.translate(LIGATURES)
    form = unicodedata.normalize("NFD", form)
    form = "".join(ch for ch in form if unicodedata.category(ch) != "Mn")
    return form.upper()


def is_valid(normalized: str) -> bool:
    """Un terme retenu ne contient que des A-Z et fait de 2 à 15 lettres."""
    return VALID_TERM.match(normalized) is not None


def score(normalized: str) -> int:
    """Score brut, hors bonus de plateau. La somme des tuiles affichées doit
    toujours être égale à cette valeur."""
    return sum(TILE_SCORES[letter] for letter in normalized)


def signature(normalized: str) -> str:
    """Lettres triées : deux anagrammes partagent la même signature."""
    return "".join(sorted(normalized))


def reverse(normalized: str) -> str:
    """Terme inversé : permet de traiter un suffixe comme un préfixe indexé."""
    return normalized[::-1]
