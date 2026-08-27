# 07 — Workflow Claude Code Et Agents

## Coordination

Aucun agent orchestrateur séparé.

La session principale Claude Code et l’utilisateur coordonnent le projet avec :

```text
docs/DECISIONS.md
docs/PHASE_STATUS.md
commits Git par phase
```

## Agents Build

```text
data-engine
frontend
seo-registry
microcopy
```

Ils ont des droits d’écriture dans leur périmètre.

## Agents Audit

```text
code-reviewer
code-optimizer
design-consistency-reviewer
seo-technical-auditor
```

Ils restent en lecture seule et prononcent GO ou NO GO.

## Fichiers Partagés

```text
app/Config.php
public/index.php
schema.sql
docs/DECISIONS.md
docs/PHASE_STATUS.md
```

Un agent peut proposer un diff, mais ne doit pas les modifier silencieusement.

## Tâche Correcte

```text
Analyse l’import Kartmaan.
Ne modifie rien.
Retourne le schéma détecté, les filtres proposés, les collisions possibles,
les requêtes de contrôle et le plan de benchmark.
```

## Tâche Trop Large

```text
Construis tout le moteur.
```

## Séquence Architecture

```text
analyser
proposer
attendre validation
implémenter
tester
rapport diff + mesures
audit externe
validation humaine
commit
```

## Commit

Un commit par unité validée :

```text
phase-0-import-source
phase-0-normalization
phase-1-word-page
phase-1-home
```

Les worktrees sont réservés à un vrai parallélisme sans conflit.
