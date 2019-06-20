CREATE TABLE faqoff_author (
    authorid BIGSERIAL PRIMARY KEY,
    ownerid BIGINT REFERENCES faqoff_accounts(accountid),
    screenname TEXT UNIQUE,
    biography TEXT,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

CREATE TABLE faqoff_author_contributor (
    contributorid BIGSERIAL PRIMARY KEY,
    authorid BIGINT REFERENCES faqoff_author(authorid),
    accountid BIGINT REFERENCES faqoff_accounts(accountid),
    created TIMESTAMP DEFAULT NOW()
);

CREATE TABLE faqoff_collection (
    collectionid BIGSERIAL PRIMARY KEY,
    title TEXT,
    url TEXT,
    authorid BIGINT REFERENCES faqoff_author(authorid),
    custom_style TEXT, -- JSON containing CSS/JS files and relevant metadata (e.g. for CSP)
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

CREATE TABLE faqoff_entry (
    entryid BIGSERIAL PRIMARY KEY,
    authorid BIGINT REFERENCES faqoff_author(authorid),
    url TEXT,
    title TEXT,
    contents TEXT,
    options TEXT, -- JSON blob for more entries
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

CREATE TABLE faqoff_collection_index (
    collectionid BIGINT REFERENCES faqoff_collection(collectionid),
    entryid BIGINT REFERENCES faqoff_entry(entryid)
);
