-- Proposition de schéma français.
-- Le fichier canonique schema.sql reste sous le contrôle de la session principale.

PRAGMA foreign_keys = ON;

CREATE TABLE terms (
    id INTEGER PRIMARY KEY,
    display_term TEXT NOT NULL,
    normalized TEXT NOT NULL UNIQUE,

    is_french INTEGER NOT NULL DEFAULT 0 CHECK (is_french IN (0, 1)),
    is_ods8 INTEGER NOT NULL DEFAULT 0 CHECK (is_ods8 IN (0, 1)),
    is_ods9 INTEGER NOT NULL DEFAULT 0 CHECK (is_ods9 IN (0, 1)),

    score INTEGER NOT NULL,
    length INTEGER NOT NULL CHECK (length >= 1),
    signature TEXT NOT NULL,
    reversed TEXT NOT NULL
);

CREATE INDEX idx_terms_normalized
ON terms(normalized);

CREATE INDEX idx_terms_length_normalized
ON terms(length, normalized);

CREATE INDEX idx_terms_signature
ON terms(signature);

CREATE INDEX idx_terms_reversed
ON terms(reversed);

CREATE INDEX idx_terms_french
ON terms(is_french, normalized);

CREATE INDEX idx_terms_ods8
ON terms(is_ods8, normalized);

CREATE INDEX idx_terms_ods9
ON terms(is_ods9, normalized);

CREATE TABLE build_metadata (
    key TEXT PRIMARY KEY,
    value TEXT NOT NULL
);
