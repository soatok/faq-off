
CREATE TABLE faqoff_entry_abuse_reports (
    reportid BIGSERIAL PRIMARY KEY,
    entryid BIGINT REFERENCES faqoff_entry(entryid),
    reporter BIGINT REFERENCES faqoff_accounts(accountid),
    open BOOLEAN DEFAULT TRUE,
    details TEXT,
    lastchange BIGINT NULL REFERENCES faqoff_entry_changelog(changelogid),
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

CREATE TABLE faqoff_collection_abuse_reports (
    reportid BIGSERIAL PRIMARY KEY,
    collectionid BIGINT REFERENCES faqoff_collection(collectionid),
    reporter BIGINT REFERENCES faqoff_accounts(accountid),
    open BOOLEAN DEFAULT TRUE,
    details TEXT,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

CREATE TABLE faqoff_author_abuse_reports (
    reportid BIGSERIAL PRIMARY KEY,
    authorid BIGINT REFERENCES faqoff_author(authorid),
    reporter BIGINT REFERENCES faqoff_accounts(accountid),
    open BOOLEAN DEFAULT TRUE,
    details TEXT,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);
