CREATE TABLE faqoff_entry_changelog (
    changelogid BIGSERIAL PRIMARY KEY,
    entryid BIGINT REFERENCES faqoff_entry(entryid),
    accountid BIGINT REFERENCES faqoff_accounts(accountid),
    diff TEXT,
    created TIMESTAMP DEFAULT NOW()
);
