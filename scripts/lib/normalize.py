"""Normalisation, score et dérivés — source unique de vérité (site espagnol).

Toute règle de transformation d'un terme vit ici et nulle part ailleurs.
Le runtime PHP (app/Search/Normalizer.php) réimplémente strictement les mêmes règles
(D-007) ; tout écart entre les deux implémentations est un bug de correspondance,
pas une variante.

Adapté du site français (D-009) pour l'espagnol -- voir CLAUDE.md/DECISIONS.md de ce
dépôt pour la décision Ñ ci-dessous. Différence délibérée avec la version française :
aucun mapping de ligatures (œ/æ n'existent pas en orthographe espagnole standard, et
aucune des deux listes Scrabble sources n'en contient une seule occurrence).
"""

from __future__ import annotations

import re
import unicodedata

# Ñ n'est PAS un "N accentué" : c'est une lettre à part entière de l'alphabet
# espagnol, avec sa propre valeur de tuile Scrabble. La décomposition NFD casse
# pourtant cette distinction : Ñ (U+00D1) se décompose en N (U+006E) + tilde
# combinant (U+0303), catégorie Unicode Mn -- un retrait naïf des marques Mn
# fusionnerait donc "año" (année) et "ano" (anus) en une seule forme normalisée.
# Confirmé sur les données réelles (data/raw/PROVENANCE.md) : "ano", "anos", "año",
# "años" sont bien QUATRE entrées distinctes du lexique FILE-2017/FISE-2 source.
#
# Protection : Ñ/ñ sont substitués par une sentinelle de la zone d'usage privé
# Unicode AVANT la décomposition NFD (donc jamais vus par NFD), puis restitués
# après la mise en majuscules -- même principe que la protection des ligatures
# françaises (œ -> oe AVANT NFD) dans scripts/lib/normalize.py du site français,
# adapté ici à un besoin different (préserver une lettre, pas la développer).
ENYE_SENTINEL = ""

# Les accents de voyelle (á/é/í/ó/ú) et le tréma (ü) sont, eux, légitimement
# ABSENTS des listes Scrabble espagnoles en usage général -- confirmé par
# inspection directe des deux sources (0 occurrence sur 639 292 + 636 599
# entrées), alors que des mots qui les portent orthographiquement sont bien
# présents SANS eux ("pinguino", "linguistica", "bilingue"...). Ce n'est PAS un
# bug de données à corriger : la décomposition NFD + retrait des marques Mn
# les retire correctement, comme pour le français.
LIGATURES: dict[str, str] = {}

# Le plateau fait 15 cases : un mot de plus de 15 lettres ne peut jamais être
# posé. Le plafond s'applique donc aux DONNÉES, pas seulement à la saisie
# -- même borne que le site français (D-010), confirmée adaptée à l'espagnol :
# le Lexicón FISE 2 est explicitement documenté comme couvrant « de deux à
# quinze lettres » (voir reports/es-site-feasibility-audit.md du site français,
# §1.1 et §5.3).
MIN_LENGTH = 2
MAX_LENGTH = 15

# Alphabet espagnol : A-Z ASCII plus Ñ (PAS K ni W en pratique dans les deux
# sources Scrabble -- 0 occurrence mesurée -- mais l'un et l'autre restent
# acceptés ici si jamais rencontrés : le plafond de longueur et le filtre de
# source font le tri, cette regex ne doit pas être plus stricte que
# l'orthographe espagnole elle-même).
VALID_TERM = re.compile(r"^[A-ZÑ]{%d,%d}$" % (MIN_LENGTH, MAX_LENGTH))

# Valeurs des tuiles espagnoles -- édition Mattel 2021 (100 fiches, sans
# digrammes CH/LL/RR, décision produit du site : tuiles à lettre unique
# uniquement, voir CLAUDE.md/DECISIONS.md). Distribution vérifiée directement
# sur https://es.wikipedia.org/wiki/Distribuci%C3%B3n_de_las_letras_en_el_Scrabble
# (2026-08-27, page consultée en direct, PAS reprise sans vérification du rapport
# de faisabilité du site français qui la signalait comme "non trouvée avec
# confiance suffisante") :
#   1 point   A E I N O R S T U
#   2 points  D G L
#   3 points  B C M P
#   4 points  F H V Y
#   5 points  Q
#   8 points  J Ñ X
#   10 points K Z
# W est ABSENT de cette édition matérielle ("El grafema W tampoco se encuentra
# por su poco uso lingüístico") -- cohérent avec les deux sources Scrabble
# importées, qui ne contiennent NI K NI W (0 occurrence mesurée, voir
# data/raw/PROVENANCE.md). W reçoit néanmoins une valeur de secours (8 points,
# alignée sur l'édition nord-américaine Hasbro qui inclut W) uniquement pour
# que score() ne lève jamais d'exception si un visiteur vérifie un mot inconnu
# contenant un W (TermLookup calcule un score même pour un terme absent de la
# base) -- aucun mot du dictionnaire construit ne porte cette valeur.
TILE_SCORES = {
    "A": 1, "B": 3, "C": 3, "D": 2, "E": 1, "F": 4, "G": 2, "H": 4, "I": 1,
    "J": 8, "K": 10, "L": 2, "M": 3, "N": 1, "Ñ": 8, "O": 1, "P": 3,
    "Q": 5, "R": 1, "S": 1, "T": 1, "U": 1, "V": 4, "W": 8, "X": 8, "Y": 4,
    "Z": 10,
}


def normalize(form: str) -> str:
    """Protection de Ñ, puis NFD, puis retrait des diacritiques, puis majuscules.

    Ne valide pas : renvoie la forme normalisée telle quelle, éventuellement
    invalide. Utiliser is_valid() pour trancher.
    """
    form = form.replace("Ñ", ENYE_SENTINEL).replace("ñ", ENYE_SENTINEL)
    form = unicodedata.normalize("NFD", form)
    form = "".join(ch for ch in form if unicodedata.category(ch) != "Mn")
    form = form.upper()
    return form.replace(ENYE_SENTINEL, "Ñ")


def is_valid(normalized: str) -> bool:
    """Un terme retenu ne contient que des A-Z/Ñ et fait de 2 à 15 lettres."""
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
