# Déploiement o2switch — état réel (2026-08-26)

Ce document décrit la topologie **réellement en place** pour wordcheckr.fr, pas un plan.
Réécrit après la migration vers une "lune" (sous-compte o2switch séparé) : la première version
de ce document décrivait un déploiement sur le compte cPanel principal (`huyd0972`), qui a été
abandonné en cours de route à cause d'un bug CageFS/PHP 8.4 non réparable en SSH — voir
`docs/DECISIONS.md` D-043 pour le détail de l'incident. Le compte `huyd0972` original héberge
encore des dizaines d'autres sites clients sans rapport avec ce projet ; ne jamais y toucher
sans confirmation explicite de l'utilisateur.

## Différence structurante avec le projet de référence

Le projet de référence est du Node.js (Passenger, `server.js`). Scrabble Light FR est du
**PHP 8.4 pur** (CLAUDE.md, D-001) : pas de build, pas de process Node à faire tourner, pas de
dépendance native. o2switch supporte PHP nativement via cPanel — le déploiement se résume à
poser des fichiers et configurer un document root.

## Topologie réelle

```text
compte / utilisateur   sc1huyd0972 -- une "lune" (sous-compte o2switch séparé, créé via
                        "Mon Univers Web" pour obtenir un environnement CageFS propre, distinct
                        du compte principal huyd0972)
hôte                    huyd0972.odns.fr -- MÊME serveur physique / MÊME IP partagée que le
                        compte principal (109.234.161.184) : la migration de domaine n'a
                        nécessité AUCUN changement DNS, juste une réassignation cPanel
accès SSH               ~/.ssh/o2switch_deploy (même paire de clés que le compte principal --
                        pour un nouveau compte/lune, importer/autoriser la MÊME clé publique
                        via cPanel "SSH Access" -> "Manage SSH Keys" -> "Import Key", jamais
                        besoin d'en régénérer une)
                        ssh -i ~/.ssh/o2switch_deploy sc1huyd0972@huyd0972.odns.fr
code applicatif         /home2/sc1huyd0972/wordcheckr-fr-app/  -- fichiers suivis par git, mais
                        PAS un dépôt git sur le serveur lui-même (voir "Transfert du code"
                        ci-dessous)
document root réel      /home2/sc1huyd0972/wordcheckr.fr/  -- PAS public_html/ (public_html
                        sert le domaine par défaut de la lune, sc1huyd0972.universe.wf --
                        confusion facile, chaque domaine ajouté sur un compte cPanel obtient
                        son propre dossier, nommé d'après le domaine)
PHP                     8.4.24, pdo_sqlite/mbstring/intl confirmés fonctionnels par une VRAIE
                        requête HTTP (pas seulement en CLI) -- voir "Bascule PHP" ci-dessous
SSL                     Let's Encrypt actif (activé via cPanel par l'utilisateur), handshake
                        TLS propre vérifié
```

## Prérequis cPanel

```text
PHP 8.4 sélectionné pour le domaine wordcheckr.fr (cPanel -> "Sélecteur PHP", PAS "MultiPHP
  Manager" -- déjà fait sur la lune, voir "Bascule PHP")
extensions actives : pdo_sqlite, mbstring, intl -- déjà incluses par défaut dans php8.4 sur
  cette lune, aucune configuration manuelle nécessaire (confirmé par requête HTTP réelle,
  pas seulement le panneau de config qui peut mentir -- voir l'incident CageFS ci-dessous)
mod_rewrite actif (Apache) -- requis par le .htaccess du document root, actif par défaut
domaine wordcheckr.fr ajouté au compte cPanel de la lune (fait manuellement par l'utilisateur
  via cPanel -> "Domaines" -- voir "Dépendance non résolue" plus bas, aucun outil CLI
  disponible pour cette étape sur ce serveur)
```

## Bascule PHP (si une future lune ou un futur domaine en a besoin)

```text
selectorctl -i php -u <compte> -b <version> -p
```

Piège trouvé par essais répétés : le flag `-b`/`--set-user-current` doit recevoir sa valeur
comme **token suivant immédiat** (`-b 8.4`). Toute autre combinaison testée (`-v 8.4 ... -b`,
`--version=8.4 ... --set-user-current`, ordre des flags différent) échoue silencieusement ou
fait juste réafficher l'aide complète -- pas une erreur claire, donc facile à mal diagnostiquer
comme un problème de permissions plutôt que de syntaxe.

Vérifier le résultat par une **vraie requête HTTP**, jamais seulement `php -v` en CLI ni le
panneau cPanel : la version CLI par défaut d'un compte peut différer de celle réellement
servie à un domaine précis, et une extension peut apparaître "cochée" dans la config sans être
effectivement chargée pour de vraies requêtes web (voir l'incident CageFS ci-dessous).

```php
<?php echo phpversion() . " | pdo_sqlite=" . (extension_loaded("pdo_sqlite") ? "yes" : "no")
  . " | mbstring=" . (extension_loaded("mbstring") ? "yes" : "no")
  . " | intl=" . (extension_loaded("intl") ? "yes" : "no");
```

Déposer ce fichier dans le document root du domaine testé, y accéder par HTTP, puis le
supprimer immédiatement après vérification -- ne jamais le laisser en place.

## Incident CageFS (compte principal, résolu par migration)

Sur le compte cPanel principal (`huyd0972`), passer le domaine wordcheckr.fr en PHP 8.4 via le
Sélecteur PHP laissait `pdo_sqlite` coché dans la configuration sans le rendre réellement
disponible aux vraies requêtes -- une jail CageFS jamais "remount"-ée pour ce domaine. Le
support o2switch (ticket, 2026-08-24) a confirmé que ce remount doit se déclencher
automatiquement au changement de version PHP, sans que ça résolve le problème pour autant.

Investigation SSH poussée (tentée avant la migration) : les requêtes `selectorctl`/`uapi`
**par domaine** échouent avec "This command is not supported in CageFS" (lecture) ou échouent
silencieusement/avec des erreurs de syntaxe peu importe la combinaison de flags essayée
(écriture) -- cohérent avec un blocage CageFS délibéré empêchant une session SSH dans la jail
de reconfigurer sa propre jail (le support lui-même parle de forcer le remount avec les droits
root, pas quelque chose que le titulaire du compte peut faire depuis SSH).

**Solution retenue : contourner plutôt que réparer.** Créer une lune (nouveau sous-compte
o2switch propre) et y migrer entièrement le site règle le problème net -- PHP 8.4 a fonctionné
du premier coup sur la lune fraîche, sans staleness CageFS puisque c'est un environnement neuf.
À réutiliser comme solution de repli pour un futur incident CageFS similaire sur ce compte,
plutôt que de retenter une investigation SSH longue en premier réflexe.

## Transfert du code — sans dépôt GitHub

Aucun remote git (GitHub) n'est configuré pour ce dépôt local. Le code est transféré
**directement** de la machine de développement vers le serveur, sans intermédiaire :

```bash
git archive --format=tar HEAD | ssh -i ~/.ssh/o2switch_deploy sc1huyd0972@huyd0972.odns.fr \
  'tar xf - -C /home2/sc1huyd0972/wordcheckr-fr-app'
```

`git archive` empaquette exactement l'arbre du commit HEAD (fichiers suivis par git
uniquement, `.gitignore` déjà respecté) -- pas de `.git/` transféré, pas de fichier ignoré
transféré. Pour une mise à jour ciblée d'un petit nombre de fichiers (plus rapide qu'un
archive complet), `scp` direct fichier par fichier fonctionne aussi bien :

```bash
scp -i ~/.ssh/o2switch_deploy app/View/word.php \
  sc1huyd0972@huyd0972.odns.fr:/home2/sc1huyd0972/wordcheckr-fr-app/app/View/word.php
```

Le serveur n'a **pas** de dépôt git local (`git status` y échoue avec "not a git repository") --
chaque mise à jour doit repasser par ce mécanisme de transfert, jamais un `git pull` côté
serveur.

## Transfert des bases SQLite -- séparé, jamais via git

```text
storage/ est gitignore (build artifact, D-007) -- git archive n'apporte NI dictionary_fr.sqlite
  NI seo_fr.sqlite -- transfert scp obligatoire et distinct
storage/dictionary_fr.sqlite   ~330 Mo (403 060 termes + word_senses, D-043)
storage/seo_fr.sqlite          ~633 Mo
destination : /home2/sc1huyd0972/wordcheckr-fr-app/storage/ -- MÊME emplacement relatif qu'en
  local, hors document root
```

Procédure sûre pour remplacer la base en production sans interrompre le site pendant le
transfert (utilisée pour le rollout D-043) : transférer vers un nom temporaire, vérifier
l'intégrité, puis renommer atomiquement.

```bash
scp -i ~/.ssh/o2switch_deploy -o ServerAliveInterval=15 -o ServerAliveCountMax=6 \
  storage/dictionary_fr.sqlite \
  sc1huyd0972@huyd0972.odns.fr:/home2/sc1huyd0972/wordcheckr-fr-app/storage/dictionary_fr.sqlite.new

# comparer la taille locale/distante, puis verifier l'integrite avant de basculer :
ssh -i ~/.ssh/o2switch_deploy sc1huyd0972@huyd0972.odns.fr \
  'cd /home2/sc1huyd0972/wordcheckr-fr-app && php -r "
    \$db = new PDO(\"sqlite:storage/dictionary_fr.sqlite.new\");
    echo \$db->query(\"PRAGMA integrity_check\")->fetchColumn();
  "'

ssh -i ~/.ssh/o2switch_deploy sc1huyd0972@huyd0972.odns.fr \
  'cd /home2/sc1huyd0972/wordcheckr-fr-app/storage && \
   mv dictionary_fr.sqlite dictionary_fr.sqlite.bak && \
   mv dictionary_fr.sqlite.new dictionary_fr.sqlite'
```

Les transferts SCP de gros fichiers (~300 Mo+) peuvent se couper en cours de route
("Connection reset by peer") sans que ce soit un vrai blocage réseau -- observé une fois,
résolu par une simple reprise avec `-o ServerAliveInterval=15 -o ServerAliveCountMax=6`.

## Structure du document root

Le document root `/home2/sc1huyd0972/wordcheckr.fr/` ne contient AUCUN fichier applicatif
directement -- uniquement des liens symboliques vers `wordcheckr-fr-app/public/*`, plus son
propre `.htaccess`. Ça garde `app/`, `config/`, `storage/`, `scripts/`, `tests/`, `data/` hors
d'accès web par construction (ils ne sont même pas sous le document root), sans dépendre
uniquement des règles `.htaccess` de `public/.htaccess` (qui restent en place aussi, en
défense en profondeur).

```bash
cd /home2/sc1huyd0972/wordcheckr.fr && \
ln -sf ../wordcheckr-fr-app/public/index.php index.php && \
ln -sf ../wordcheckr-fr-app/public/assets assets && \
ln -sf ../wordcheckr-fr-app/public/robots.txt robots.txt && \
ln -sf ../wordcheckr-fr-app/public/sitemap-index.xml sitemap-index.xml && \
ln -sf ../wordcheckr-fr-app/public/sitemaps sitemaps && \
ln -sf ../wordcheckr-fr-app/public/apple-touch-icon.png apple-touch-icon.png && \
ln -sf ../wordcheckr-fr-app/public/favicon-96x96.png favicon-96x96.png && \
ln -sf ../wordcheckr-fr-app/public/favicon.ico favicon.ico && \
ln -sf ../wordcheckr-fr-app/public/favicon.svg favicon.svg && \
ln -sf ../wordcheckr-fr-app/public/site.webmanifest site.webmanifest && \
ln -sf ../wordcheckr-fr-app/public/web-app-manifest-192x192.png web-app-manifest-192x192.png && \
ln -sf ../wordcheckr-fr-app/public/web-app-manifest-512x512.png web-app-manifest-512x512.png
```

`.htaccess` du document root (distinct de `public/.htaccess`, qui gère le routage interne
front-controller) :

```apache
SetEnv SCRABBLE_CONTACT_EMAIL contact@wordcheckr.fr
DirectoryIndex index.php
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{HTTPS} off [OR]
    RewriteCond %{HTTP_HOST} !^www\. [NC]
    RewriteRule ^ https://www.wordcheckr.fr%{REQUEST_URI} [L,R=301]

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    RewriteRule ^ index.php [L]
</IfModule>
```

Piège si SSL n'est pas encore actif au moment du déploiement : la redirection HTTPS forcée
casse l'accès tant que le certificat n'est pas émis. Déployer d'abord SANS la condition
`RewriteCond %{HTTPS} off [OR]` (juste la redirection www), demander le certificat Let's
Encrypt, vérifier HTTPS fonctionnel, PUIS ajouter la redirection HTTPS forcée.

## Dépendance non résolue : gestion de domaine via cPanel uniquement

Le module API cPanel `AddonDomain` n'est **pas installé** sur ce serveur
(`Can't locate Cpanel/API/AddonDomain.pm`) -- confirmé en cherchant directement dans
`/usr/local/cpanel/Cpanel/API/`. Ajouter ou retirer un domaine d'un compte n'est PAS possible
via `uapi`/SSH sur ce serveur, seulement via l'interface web cPanel ("Domaines"). Ne pas
perdre de temps à retenter une approche CLI pour cette étape précise -- demander à
l'utilisateur de le faire manuellement.

## Variables d'environnement

```text
SCRABBLE_CONTACT_EMAIL={adresse}   formulaire /contact (public/index.php), PHP mail() natif
                                    -- définie directement dans le .htaccess du document root
                                    via SetEnv (voir ci-dessus), pas besoin du panneau
                                    "Variables d'environnement" de cPanel pour celle-ci
```

`app/Config.php` ne lit `SCRABBLE_SITE` que via `getenv()` avec un défaut implicite (site
français unique pour l'instant) -- pas de variable à définir séparément tant qu'un seul site
tourne sur ce dépôt.

## Vérification post-déploiement

```text
routes témoins en 200 : /, /mot/{mot-connu}, /mots/7-lettres, /sitemap-index.xml
/verifier/{mot} en 302 (redirige vers /mot/{mot} canonique) -- pas 200 directement, normal
storage/*.sqlite non accessible depuis une URL publique (https://www.wordcheckr.fr/storage/...
  doit répondre 404, jamais servir le fichier) -- confirmé
non-www et HTTP redirigent vers https://www.wordcheckr.fr (301) -- confirmé
TTFB à chaud mesuré : 200-295ms depuis la machine de dev (inclut la latence réseau réelle,
  pas seulement le temps serveur) -- dans le budget CLAUDE.md (p95 < 250ms) ou tout proche,
  à surveiller sous charge réelle plutôt qu'un signal d'alerte isolé
définition réelle vérifiée dans le HTML servi (pas seulement le code HTTP) : chercher
  `sense-card` dans la réponse de /mot/{mot} pour confirmer que D-043 sert vraiment du contenu,
  pas juste une page qui charge
```

## Après mise en ligne — soumission des sitemaps

Le dimensionnement des vagues de soumission (combien de fragments par vague, critère de
passage à la suivante) est traité séparément — voir `docs/DECISIONS.md` (D-041/D-042 et
suivants) et `reports/query-plans/` pour le plan chiffré une fois produit. Ne pas soumettre
les fragments d'un coup sans ce séquencement.

## Housekeeping non résolu

```text
compte huyd0972 (l'original, avant migration) conserve encore wordcheckr-fr-app/ et
  wordcheckr.fr/ avec l'ancienne base -- gardé délibérément comme filet de sécurité, pas
  encore nettoyé, nécessite une confirmation explicite de l'utilisateur avant suppression
```
