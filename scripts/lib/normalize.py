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

# Tuiles digrammes dédiées : CH, LL, RR sont des tuiles PHYSIQUES à part entière du jeu
# Scrabble espagnol, pas une paire de tuiles simples adjacentes -- règle FISE explicite
# ("il est interdit de composer CH/LL/RR à partir de deux tuiles séparées"). Décision
# produit (revient sur la simplification "tuiles à lettre unique" envisagée plus tôt,
# annulée après vérification directe de la distribution officielle) : ce site
# implémente la tokenisation par tuile, pas une somme lettre par lettre. Ñ reste une
# lettre simple normale -- elle n'a besoin d'aucun traitement de tokenisation
# particulier au-delà de ce que normalize() fait déjà (voir ENYE_SENTINEL ci-dessus).
DIGRAPH_TILES = ("CH", "LL", "RR")

# Séparateur utilisé pour joindre les tuiles triées dans signature() (voir cette
# fonction). Choisi pour ne JAMAIS pouvoir apparaître dans une tuile elle-même (aucune
# tuile ne contient de point) -- élimine par construction toute collision entre un mot
# dont C et H apparaissent comme deux tuiles simples SÉPARÉES (non adjacentes dans le
# mot) et un mot qui contient la tuile CH dédiée : sans séparateur, les deux
# produiraient la même sous-chaîne concaténée "CH" une fois triées.
SIGNATURE_TILE_SEPARATOR = "."


def tokenize_tiles(normalized: str) -> list[str]:
    """Découpe une forme normalisée en tuiles Scrabble espagnoles.

    Correspondance gloutonne de gauche à droite : dès que deux caractères consécutifs
    forment CH, LL ou RR, ils comptent pour UNE seule tuile (la tuile dédiée) ; sinon,
    chaque caractère (dont Ñ) compte pour une tuile simple. C'est la même convention
    que la règle physique du jeu : toute occurrence adjacente de C+H, L+L ou R+R dans
    un mot valide DOIT être jouée avec la tuile dédiée, jamais deux tuiles séparées --
    donc aucune exception de "frontière de morphème" n'est faite ici non plus.
    """
    tiles: list[str] = []
    i = 0
    n = len(normalized)
    while i < n:
        pair = normalized[i:i + 2]
        if pair in DIGRAPH_TILES:
            tiles.append(pair)
            i += 2
        else:
            tiles.append(normalized[i])
            i += 1
    return tiles


# Valeurs des tuiles espagnoles -- édition internationale/européenne (100 fiches, AVEC
# les tuiles digrammes CH/LL/RR -- décision produit confirmée, voir DIGRAPH_TILES
# ci-dessus). Distribution vérifiée directement sur
# https://es.wikipedia.org/wiki/Distribuci%C3%B3n_de_las_letras_en_el_Scrabble
# (2026-08-27, section « Español », édition hors Amérique du Nord), confirmée par
# sommation manuelle indépendante (100 tuiles exactement, blancs compris) :
#   1 point   A(x12) E(x12) O(x9) I(x6) S(x6) N(x5) L(x4) R(x5) U(x5) T(x4)
#   2 points  D(x5) G(x2)
#   3 points  C(x4) B(x2) M(x2) P(x2)
#   4 points  H(x2) F(x1) V(x1) Y(x1)
#   5 points  CH(x1) Q(x1)
#   8 points  J(x1) LL(x1) Ñ(x1) RR(x1) X(x1)
#   10 points Z(x1)
#   2 blancs (0 point, non modélisés ici -- aucun terme du dictionnaire n'est un blanc)
# K et W sont ABSENTS de cette édition matérielle (aucune tuile K ni W dans le jeu
# physique) -- cohérent avec les deux sources Scrabble importées, qui ne contiennent NI
# K NI W (0 occurrence mesurée, voir data/raw/PROVENANCE.md). Les deux reçoivent
# néanmoins une valeur de secours (alignée sur l'édition nord-américaine Hasbro, qui
# inclut les deux) uniquement pour que score() ne lève jamais d'exception si un
# visiteur vérifie un mot inconnu contenant un K ou un W (TermLookup calcule un score
# même pour un terme absent de la base) -- aucun mot du dictionnaire construit ne porte
# cette valeur.
TILE_SCORES = {
    "A": 1, "B": 3, "C": 3, "D": 2, "E": 1, "F": 4, "G": 2, "H": 4, "I": 1,
    "J": 8, "K": 10, "L": 1, "M": 3, "N": 1, "Ñ": 8, "O": 1, "P": 3,
    "Q": 5, "R": 1, "S": 1, "T": 1, "U": 1, "V": 4, "W": 8, "X": 8, "Y": 4,
    "Z": 10,
    "CH": 5, "LL": 8, "RR": 8,
}


def normalize(form: str) -> str:
    """NFC préalable, puis protection de Ñ, puis NFD, puis retrait des diacritiques,
    puis majuscules.

    Ne valide pas : renvoie la forme normalisée telle quelle, éventuellement
    invalide. Utiliser is_valid() pour trancher. Ne tokenise PAS en tuiles -- le texte
    normalisé stocké (normalized/display_term/reversed) reste une suite de caractères
    ordinaires ; la tokenisation en tuiles (tokenize_tiles()) est une vue DÉRIVÉE,
    calculée à la demande par score() et signature() uniquement.

    NFC préalable : nécessaire car .replace("Ñ", ...) ne reconnaît que la forme
    PRÉCOMPOSÉE (Ñ = U+00D1, un seul point de code). Une entrée DÉCOMPOSÉE (N + tilde
    combinant U+0303, deux points de code -- rare mais réelle, ex. données déjà
    passées par un pipeline NFD ailleurs) contournerait sans cette étape la protection
    ENYE_SENTINEL : le retrait des marques Mn supprimerait ensuite le tilde combinant
    et perdrait silencieusement le Ñ (vérifié : "n" + U+0303 + "o" se normalisait à
    tort en "NO" au lieu de "ÑO" avant ce correctif -- même bug corrigé côté PHP,
    App\\Search\\Normalizer::normalize()).
    """
    form = unicodedata.normalize("NFC", form)
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
    toujours être égale à cette valeur -- une tuile CH/LL/RR compte pour SA valeur
    propre, pas la somme de ses deux lettres (ex. "COCHE" = C + O + CH + E = 3+1+5+1 =
    10, PAS C+O+C+H+E = 3+1+3+4+1 = 12)."""
    return sum(TILE_SCORES[tile] for tile in tokenize_tiles(normalized))


def signature(normalized: str) -> str:
    """Tuiles triées, jointes par SIGNATURE_TILE_SEPARATOR : deux mots sont des
    anagrammes AU SENS DES TUILES SCRABBLE s'ils partagent la même signature (même
    multiensemble de tuiles physiques, pas seulement de lettres -- "COCHE" et un mot
    hypothétique construit à partir des tuiles C, O, CH, E dans un autre ordre sont des
    anagrammes ; un mot qui contiendrait C et H comme deux tuiles simples SÉPARÉES
    (non adjacentes) n'est PAS un anagramme d'un mot qui contient la tuile CH dédiée,
    même si la séquence de lettres brutes semble proche -- voir SIGNATURE_TILE_SEPARATOR
    pour la raison du séparateur)."""
    return SIGNATURE_TILE_SEPARATOR.join(sorted(tokenize_tiles(normalized)))


def reverse(normalized: str) -> str:
    """Terme inversé : permet de traiter un suffixe comme un préfixe indexé.

    Reste au niveau du CARACTÈRE, pas de la tuile -- "terminer par -CION" est une
    recherche de suite de lettres dans le mot écrit, pas une recherche de tuile
    physique. Différent, délibérément, de signature() ci-dessus."""
    return normalized[::-1]
