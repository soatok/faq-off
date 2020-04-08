CREATE TABLE faqoff_question_box (
    questionid BIGSERIAL PRIMARY KEY,
    -- Account ID of the person that asked
    asked_by BIGINT NULL REFERENCES faqoff_accounts (accountid),
    -- Show the author who asked it?
    attribution BOOLEAN DEFAULT FALSE,
    -- Collection
    collectionid BIGINT NULL REFERENCES faqoff_collection (collectionid),
    -- Entry
    entryid BIGINT NULL REFERENCES faqoff_entry (entryid),
    -- Hidden from question box
    archived BOOLEAN DEFAULT FALSE,
    -- The actual question
    question TEXT,
    -- Creation time
    created TIMESTAMP DEFAULT NOW(),
    -- One or the other, but not both, must be set
    CHECK( (entryid IS NULL) != (collectionid IS NULL ))
);
