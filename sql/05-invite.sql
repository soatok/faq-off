CREATE TABLE faqoff_invites (
    inviteid BIGSERIAL PRIMARY KEY,
    invitefrom BIGINT REFERENCES faqoff_accounts (accountid),
    twitter TEXT,
    email TEXT,
    invite_code TEXT,
    claimed BOOLEAN DEFAULT FALSE,
    CREATED TIMESTAMP DEFAULT NOW(),
    newaccountid BIGINT NULL REFERENCES faqoff_accounts (accountid)
);
