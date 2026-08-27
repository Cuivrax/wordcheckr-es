# 06 — Phases D’Implémentation Et Portes De Validation

## Phase 0 — Données

Build :

```text
data-engine
```

Travail :

```text
audit des sources
import Kartmaan
audit ODS8
application du patch ODS9
normalisation
scores
signatures
reversed
rapports
```

Audit :

```text
code-reviewer
```

Porte :

```text
import reproductible
comptes cohérents
aucune collision silencieuse
PRAGMA integrity_check = ok
```

## Phase 1 — Socle, Home Et Fiche

Build :

```text
data-engine + frontend
```

Audit :

```text
code-reviewer + design-consistency-reviewer
```

Routes :

```text
/
/mot/{mot}
/verifier/{mot}
```

## Phase 2 — Solveur

Build :

```text
data-engine
```

Audit :

```text
code-reviewer
code-optimizer uniquement si un problème mesuré existe
```

## Phase 3 — Contraintes

```text
longueur
début
fin
suite
lettres obligatoires
répétitions
exclusions
motif
```

Audit :

```text
code-reviewer + code-optimizer après benchmark
```

## Phase 4 — Fiches Riches

Build :

```text
data-engine + frontend
```

Audit :

```text
code-reviewer + design-consistency-reviewer
```

## Phase 5 — Autocomplétion

Build :

```text
data-engine + frontend
```

Audit :

```text
code-reviewer
code-optimizer
design-consistency-reviewer
```

## Phase 6 — Registre SEO

Build :

```text
seo-registry
```

Audit :

```text
code-reviewer + seo-technical-auditor
```

## Phase 7 — Production

Audit complet :

```text
code-reviewer
code-optimizer
design-consistency-reviewer
seo-technical-auditor
```

## Boucle Standard

```text
1 agent build
→ rapport READY FOR AUDIT

1 agent audit adapté
→ GO ou NO GO

validation humaine
→ commit

phase suivante
```

Ne pas lancer les quatre audits sur chaque micro-tâche.
