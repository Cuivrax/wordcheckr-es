/*
 * Amelioration progressive uniquement (docs/04_UI_PAGES.md, docs/08 Phase 5) : combobox ARIA
 * d'autocompletion sur le champ unique de la home (#q). L'input HTML texte reste la source de
 * verite -- sans ce script, aucun element de cette combobox n'existe (aucune liste, aucun
 * attribut ARIA de combobox) et le formulaire fonctionne exactement comme avant (soumission GET
 * native vers /verifier ou /jouer selon le bouton, docs/08). Fichier separe de tiles.js, charge
 * en "defer", role different (apercu de tuiles vs. suggestions serveur).
 *
 * Consomme GET /api/suggest?q=.. (App\Search\Suggester, deja cable par public/index.php) :
 * jusqu'a 8 objets {normalized, slug, score, length, isOds8, isOds9, status}, status valant
 * "admitted" ou "french_not_admitted" (jamais "unknown", jamais absent -- voir
 * app/Search/Suggester.php). Tableau vide pour moins de 2 caracteres ou entree invalide, jamais
 * d'erreur HTTP.
 */
(function () {
  "use strict";

  // Pas de polyfill ajoute (CLAUDE.md : aucune dependance sans decision) -- navigateur sans
  // fetch() ou sans Promise, aucune amelioration, le formulaire natif reste inchange.
  if (typeof window.fetch !== "function") {
    return;
  }

  var MIN_LENGTH = 2;
  var DEBOUNCE_MS = 160;

  /** Deux libelles exacts (ajustement UI post-Phase 5, demande directe du
   * coordinateur) : la distinction ODS8/ODS9 n'est plus affichee dans les
   * suggestions -- un mot admis (isOds8 ou isOds9 vrai) affiche simplement "Admis",
   * un mot non admis affiche "Non Admis" (meme casse, sans prefixe "Français ·").
   * isOds8/isOds9/status restent inchanges cote donnees (App\Search\Suggester) ; seul
   * ce rendu change. Ne pas confondre avec .edition-badge (app/View/word.php) ou
   * app/View/play.php, qui continuent de distinguer ODS8/ODS9. */
  function statusLabel(item) {
    if (item.isOds8 || item.isOds9) {
      return "Admis";
    }
    return "Non Admis";
  }

  /** Reutilise .status-badge tel quel (public/assets/css/site.css), meme modificateur que
   * app/View/word.php pour le meme statut -- aucun nouveau composant visuel. */
  function statusModifier(item) {
    return item.status === "admitted" ? "status-badge--admitted" : "status-badge--not-admitted";
  }

  function init(input) {
    var wrap = input.parentNode;

    var listbox = document.createElement("ul");
    listbox.className = "suggest-list";
    listbox.id = "q-suggest-listbox";
    listbox.setAttribute("role", "listbox");
    listbox.setAttribute("aria-label", "Suggestions");
    listbox.hidden = true;
    wrap.appendChild(listbox);

    input.setAttribute("role", "combobox");
    input.setAttribute("aria-autocomplete", "list");
    input.setAttribute("aria-haspopup", "listbox");
    input.setAttribute("aria-expanded", "false");
    input.setAttribute("aria-controls", listbox.id);

    var debounceTimer = null;
    var controller = null;
    var items = [];
    var activeIndex = -1;
    var suppressNextInput = false;

    function cancelPending() {
      if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
      }
      if (controller !== null) {
        controller.abort();
        controller = null;
      }
    }

    function closeList() {
      listbox.hidden = true;
      listbox.innerHTML = "";
      items = [];
      activeIndex = -1;
      input.setAttribute("aria-expanded", "false");
      input.removeAttribute("aria-activedescendant");
    }

    function setActive(index) {
      var options = listbox.children;

      if (activeIndex >= 0 && options[activeIndex]) {
        options[activeIndex].classList.remove("is-active");
        options[activeIndex].setAttribute("aria-selected", "false");
      }

      activeIndex = index;

      if (activeIndex >= 0 && options[activeIndex]) {
        var active = options[activeIndex];
        active.classList.add("is-active");
        active.setAttribute("aria-selected", "true");
        input.setAttribute("aria-activedescendant", active.id);
        if (typeof active.scrollIntoView === "function") {
          active.scrollIntoView({ block: "nearest" });
        }
      } else {
        input.removeAttribute("aria-activedescendant");
      }
    }

    function selectItem(index) {
      var item = items[index];

      if (!item) {
        return;
      }

      suppressNextInput = true;
      input.value = item.normalized;
      closeList();
      input.focus();
      // Laisse tiles.js (ecouteur "input" independant) redessiner l'apercu de tuiles pour
      // la valeur choisie -- suppressNextInput evite de relancer une recherche de suggestions
      // pour ce meme changement programmatique.
      input.dispatchEvent(new Event("input", { bubbles: true }));
    }

    function renderList(newItems) {
      items = newItems;
      activeIndex = -1;
      listbox.innerHTML = "";
      input.removeAttribute("aria-activedescendant");

      if (items.length === 0) {
        closeList();
        return;
      }

      for (var i = 0; i < items.length; i++) {
        var item = items[i];

        var option = document.createElement("li");
        option.className = "suggest-option";
        option.id = "q-suggest-option-" + i;
        option.setAttribute("role", "option");
        option.setAttribute("aria-selected", "false");

        var wordEl = document.createElement("span");
        wordEl.className = "suggest-word";
        wordEl.textContent = item.normalized;

        var badgeEl = document.createElement("span");
        badgeEl.className = "status-badge " + statusModifier(item);
        badgeEl.textContent = statusLabel(item);

        option.appendChild(wordEl);
        option.appendChild(badgeEl);

        // mousedown (pas click) + preventDefault : empeche l'input de perdre le focus au
        // moment ou l'utilisateur pointe une suggestion (souris ou tactile), donc aucun
        // "blur" ne se declenche avant que la selection ne s'execute -- patron standard
        // du modele ARIA combobox par aria-activedescendant.
        (function (index) {
          option.addEventListener("mousedown", function (event) {
            event.preventDefault();
            selectItem(index);
          });
        })(i);

        listbox.appendChild(option);
      }

      listbox.hidden = false;
      input.setAttribute("aria-expanded", "true");
    }

    function fetchSuggestions(value) {
      cancelPending();

      controller = typeof window.AbortController === "function" ? new AbortController() : null;

      var url = "/api/suggest?q=" + encodeURIComponent(value);
      var options = controller !== null ? { signal: controller.signal } : {};

      fetch(url, options)
        .then(function (response) {
          return response.ok ? response.json() : [];
        })
        .then(function (data) {
          renderList(Array.isArray(data) ? data : []);
        })
        .catch(function (error) {
          // Requete annulee (nouvelle frappe, effacement, ou fermeture) : jamais de resultat
          // perime affiche -- toute autre erreur reseau referme simplement la liste.
          if (error && error.name === "AbortError") {
            return;
          }
          closeList();
        });
    }

    input.addEventListener("input", function () {
      if (suppressNextInput) {
        suppressNextInput = false;
        return;
      }

      cancelPending();

      var value = input.value.trim();

      if (value.length < MIN_LENGTH) {
        closeList();
        return;
      }

      debounceTimer = setTimeout(function () {
        fetchSuggestions(value);
      }, DEBOUNCE_MS);
    });

    input.addEventListener("keydown", function (event) {
      if (listbox.hidden) {
        return;
      }

      switch (event.key) {
        case "ArrowDown":
          event.preventDefault();
          setActive(activeIndex < items.length - 1 ? activeIndex + 1 : 0);
          break;

        case "ArrowUp":
          event.preventDefault();
          setActive(activeIndex > 0 ? activeIndex - 1 : items.length - 1);
          break;

        case "Enter":
          if (activeIndex >= 0) {
            event.preventDefault();
            selectItem(activeIndex);
          }
          break;

        case "Escape":
          closeList();
          break;

        default:
          break;
      }
    });

    input.addEventListener("blur", function () {
      cancelPending();
      closeList();
    });
  }

  var field = document.getElementById("q");

  if (field) {
    init(field);
  }
})();
