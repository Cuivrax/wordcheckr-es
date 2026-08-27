-- Target production table, deliberately without definitions.
CREATE TABLE terms (
    id INTEGER PRIMARY KEY,
    display_term TEXT NOT NULL,
    normalized TEXT NOT NULL UNIQUE,
    is_french INTEGER NOT NULL DEFAULT 0 CHECK (is_french IN (0,1)),
    is_ods8 INTEGER NOT NULL DEFAULT 0 CHECK (is_ods8 IN (0,1)),
    is_ods9 INTEGER NOT NULL DEFAULT 0 CHECK (is_ods9 IN (0,1)),
    score INTEGER,
    length INTEGER NOT NULL,
    signature TEXT,
    reversed TEXT
);

CREATE INDEX idx_terms_normalized ON terms(normalized);
CREATE INDEX idx_terms_ods8 ON terms(is_ods8);
CREATE INDEX idx_terms_ods9 ON terms(is_ods9);
CREATE INDEX idx_terms_french ON terms(is_french);

-- Recommended merge order:
-- 1. Import every cleaned French form with is_french = 1.
-- 2. Import the user's ODS8 JSON:
--      set is_ods8 = 1 and initially is_ods9 = 1.
-- 3. Apply ods9_patch.sqlite.removals:
--      set is_ods9 = 0.
-- 4. Apply ods9_patch.sqlite.keep_overrides:
--      set is_ods9 = 1.
-- 5. Apply ods9_patch.sqlite.additions:
--      insert or update is_ods9 = 1 and preserve is_ods8 = 0 when absent from ODS8.
