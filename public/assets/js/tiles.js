/*
 * Amelioration progressive uniquement (docs/04_UI_PAGES.md) : affiche les tuiles
 * directement dans le champ de saisie, a la place du texte tape (voir la classe
 * "tiles-active" ci-dessous et les regles correspondantes dans site.css). L'input
 * HTML texte reste la source de verite et le formulaire fonctionne deja sans ce
 * script (soumission GET native vers /verifier). Ce fichier est charge en "defer"
 * et ne bloque jamais le rendu.
 */
(function () {
  "use strict";

  // Valeurs des tuiles francaises -- doit rester identique a tile_scores dans
  // config/sites/fr.php. Duplique ici volontairement : cet apercu s'affiche avant
  // tout aller-retour serveur, sans endpoint dedie a ce stade (Phase 1).
  var TILE_SCORES = {
    A: 1, B: 3, C: 3, D: 2, E: 1, F: 4, G: 2, H: 4, I: 1,
    J: 8, K: 10, L: 1, M: 2, N: 1, O: 1, P: 3, Q: 8, R: 1,
    S: 1, T: 1, U: 1, V: 4, W: 10, X: 10, Y: 10, Z: 10
  };

  function stripDiacritics(value) {
    if (typeof value.normalize !== "function") {
      return value;
    }
    return value.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }

  function renderTiles(input, tilesBox) {
    // [A-Z?*] : ? et * valent joker (docs/01_MASTER_BRIEF.md), acceptes par le
    // champ chevalet de la home en plus du champ mot -- ce script est generique
    // aux deux (data-tile-preview), donc les deux glyphes doivent rester visibles.
    var letters = stripDiacritics(input.value || "").toUpperCase().match(/[A-Z?*]/g) || [];

    if (letters.length === 0) {
      tilesBox.textContent = "";
      return;
    }

    var markup = "";
    for (var i = 0; i < letters.length; i++) {
      var letter = letters[i];
      var isJoker = letter === "?" || letter === "*";
      if (isJoker) {
        markup += '<span class="tile">?</span>';
        continue;
      }
      var value = TILE_SCORES[letter] || 0;
      markup += '<span class="tile">' + letter + "<small>" + value + "</small></span>";
    }
    tilesBox.innerHTML = markup;
  }

  var wraps = document.querySelectorAll("[data-tile-preview]");
  for (var w = 0; w < wraps.length; w++) {
    (function (wrap) {
      var input = wrap.querySelector("input");
      var tilesBox = wrap.querySelector(".tiles");

      if (!input || !tilesBox) {
        return;
      }

      // Pose la classe AVANT de brancher l'ecouteur : c'est elle qui active en CSS le
      // recouvrement du champ par les tuiles (site.css, .rack-wrap.tiles-active). Sans
      // cette classe (script absent ou desactive), .rack reste le champ texte normal,
      // visible et fonctionnel -- aucune regression sans JavaScript.
      wrap.classList.add("tiles-active");

      // Rendu immediat : une valeur deja presente au moment de l'init (restauration
      // d'historique/bfcache, autofill malgre autocomplete="off", ou simplement une
      // valeur non vide cote serveur) ne declenche aucun evenement "input" -- sans cet
      // appel, .rack serait deja transparent (classe posee ci-dessus) alors qu'aucune
      // tuile n'a ete dessinee pour ce texte, rendant le champ visuellement vide.
      renderTiles(input, tilesBox);

      input.addEventListener("input", function () {
        renderTiles(input, tilesBox);
      });
    })(wraps[w]);
  }
})();
