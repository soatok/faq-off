CREATE TABLE faqoff_themes (
    themeid BIGSERIAL PRIMARY KEY,
    name TEXT,
    description TEXT, -- Rendered Markdown + HTMLPurifier
    url TEXT UNIQUE,
    twig_vars TEXT, -- serialized JSON
    css_files TEXT, -- serialized JSON array, used with CSP-Builder
    js_files TEXT, -- serialized JSON array, used with CSP-Builder
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

ALTER TABLE faqoff_collection ADD COLUMN
    theme BIGINT NULL REFERENCES faqoff_themes(themeid);
