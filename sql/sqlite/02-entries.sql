CREATE TABLE faqoff_entry (
    entryid INTEGER PRIMARY KEY,
    userid INTEGER REFERENCES faqoff_user(userid),
    title TEXT,
    contents TEXT,
    options TEXT, -- JSON blob
    created TEXT,
    modified TEXT
);

CREATE TABLE faqoff_collection (
    collectionid INTEGER PRIMARY KEY,
    title TEXT,
    custom_style TEXT, -- JSON containing CSS/JS files and relevant metadata (e.g. for CSP)
);
