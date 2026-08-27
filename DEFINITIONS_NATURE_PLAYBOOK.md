# Playbook — bulk-generating definitions + grammatical type for a word list

How we took Mot Valide's ~412K-word French Scrabble dictionary from 19,200 definitions
(and 0 grammatical types) to 403,060 words (the full 2-15 letter, Scrabble-playable
range) with 100% definition + 100% `nature` (grammatical type) coverage, for ~$54 total.
Written to be portable to another word list / another language.

## 0. Starting state

~412K words, 19,200 already had short definitions from an older, separate process.
Nothing had a grammatical type (`nature`). Two gaps to close: fill missing definitions,
add `nature`, and audit the definitions that already existed for errors.

## 1. Find a reference dataset (one-time research)

Compared a Wiktionary extract (kaikki.org, free, no auth) against a Kaggle French
dictionary dataset (word + POS + gloss + gender/register, 895K entries, needs a Kaggle
account/token). Picked Kaggle for better coverage of inflected/conjugated word forms.

**For English**: WordNet or kaikki.org's English extract are the direct equivalents —
no paid API needed for either.

Setup: `pip install kagglehub`, then `kagglehub.dataset_download(...)` — it caches the
file locally so credentials are only needed once.

## 2. Schema migration (`scripts/add-definition-columns.mjs`)

Added two columns, guarded by `PRAGMA table_info` so the script is safe to re-run:

- `nature` (TEXT) — comma-separated list of every grammatical type found, not just one
- `sens_complets` (TEXT, JSON) — the full raw reference data per word, so nothing is
  thrown away even though the displayed definition stays short

## 3. Match your word list against the reference (`scripts/prepare-kaggle-reference.py`)

Python (not Node) purely because pandas/parquet tooling is better there — the rest of
the pipeline is Node. One script, run per word-length range (`--min-length`/
`--max-length`, `--suffix` to avoid output files clobbering each other across runs),
that:

- Loads target words from the DB, loads the reference parquet
- Matches by exact word
- For matched words, splits by regex-detectable pattern into:
  - **Zero-cost**: reference gloss matches a template (e.g. "plural of X", "3rd person
    singular past tense of X") → render the definition with our own string templates,
    no AI call at all. This alone covered ~40% of the biggest gap for free.
  - **Needs generation**: everything else, tagged with whatever reference data exists
    (or empty if none)
- For words that *already* had a definition: outputs a separate nature-fill +
  verify-queue set (used later, in step 6)

Key subtlety: the regex templates render our **own** sentence structure from the
extracted grammatical facts (person, tense, gender, base word) — never copy the
reference text. That's what keeps this from being duplicate content against a
CC-BY-SA source.

**Watch out for**: templates that are ambiguous across grammatical categories. We had
one regex shared between noun and adjective glosses that produced "Forme masculine
plurielle de l'adjectif gonze" for a word that was actually a noun — the surface text
of the two categories' reference glosses was identical, only the `pos` field
disambiguated them. Fix: key your template dispatch by the reference's own POS tag,
never by regex alone.

## 4. Write the zero-cost entries (`scripts/write-free-updates.mjs`)

Pure DB writes, no API. `WHERE definition IS NULL` guard so it's idempotent.

## 5. Generate the rest via LLM (`scripts/generate-definitions.mjs` /
   `generate-definitions-deepseek.mjs`)

Batched (20-30 words/call), structured JSON output (Zod-validated schema), resumable
(re-queries "what's still missing from the DB" before each run, not a static list),
concurrent worker pool, per-batch try/catch so one bad batch never kills the run.

System prompt rules that mattered most:

- **One sentence, one sense** — explicitly ban cramming multiple meanings with
  semicolons; this was the single most common failure mode on both models
- Fixed vocabulary list for `nature` (e.g. `Nom, Verbe, Adjectif, Adverbe, Pronom
  démonstratif, ...`) so values are consistent and filterable later
- If a reference-derived nature hint exists, trust it and don't ask the model to
  re-derive it
- `max_tokens` must scale with batch size (~700-800 tokens/word was our safe margin) —
  too tight and you get truncated, invalid JSON mid-response

Used Claude Sonnet 5 for the smaller gap, DeepSeek V4-Flash (~20x cheaper) for the much
bigger one — reformulation doesn't need a frontier model.

## 6. Audit existing definitions (`heuristic-verify-definitions.py`,
   `verification-shared.mjs`, `deepseek-verify-pass.mjs`, `claude-confirm-pass.mjs`)

Tried a free lexical-overlap heuristic first — compare existing definition's words
against the reference gloss's words. **It failed**: correct paraphrased definitions
share almost no literal words with a terse reference gloss, so it flagged 58% of words
with a true error rate closer to 20%. Not precise enough to trust.

Replaced with a two-tier LLM check instead:

1. Cheap model (DeepSeek) does a bulk pass on every word: existing definition +
   reference → `correct`/`incorrect`/`uncertain` verdict + a proposed fix if wrong.
   Tell it explicitly that the reference may be incomplete, so it judges against
   general knowledge too, not just the reference.
2. Only the words flagged `incorrect` get re-checked by the stronger model (Claude)
   with the *same* prompt/schema (shared in one file so the two stages can't drift).
   A correction is only applied if **both** models agree it's wrong.

This kept a 12K-word audit under $25 instead of ~$70 running everything through the
expensive model.

## 7. Wire it into the UI

`nature` wasn't in the original word type or the `SELECT` query — added it to both
(the DB-access type, the `getWord()`-equivalent query), then a small badge next to the
definition heading in the word-page component. Trivial once the data exists, but easy
to forget — the data being "done" and the data being "visible" are two separate steps.

## 8. Deploy

Data (DB file) and code (build output) are separate deploy paths — data-only is a
simple file copy + process restart; code needs a full clean build, local verification,
then an atomic swap on the server (never delete-in-place on a live process). Commit and
push the scripts + UI diff to git so the server's source checkout stays in sync, even
though only the build output actually matters for what's running.

## Total cost across ~403K words

~$54 (six-letter gap on Claude ~$9, the audit ~$20, the large 7-15 letter gap on
DeepSeek ~$24, nature-only fill ~$0.30). The overwhelming majority of spend was
avoidable by using a cheap model for bulk work and reserving the expensive one for
confirmation gates.

## Biggest time-sinks, if you want to skip them

- Test every prompt change on ~20-50 words with a dry-run before the full batch —
  caught two real bugs this way (multi-sense cramming, and a regex template matching
  the wrong part-of-speech because the surface text was ambiguous across categories)
- Namespace your intermediate JSON files per partition — reusing filenames across
  different word-length runs clobbered our own data once
- Budget for API credits running out mid-run on anything over a few thousand words —
  build resumability in from the start rather than bolting it on after
