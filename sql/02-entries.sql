CREATE TABLE faqoff_entry (
    entryid BIGSERIAL PRIMARY KEY,
    userid BIGINT REFERENCES faqoff_accounts(accountid),
    title TEXT,
    contents TEXT,
    options TEXT, -- JSON blob
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

CREATE TABLE faqoff_collection (
    collectionid BIGSERIAL PRIMARY KEY,
    title TEXT,
    custom_style TEXT, -- JSON containing CSS/JS files and relevant metadata (e.g. for CSP)
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);
