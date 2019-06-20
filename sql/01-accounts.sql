/* Accounts -- what you login as */
CREATE TABLE IF NOT EXISTS faqoff_accounts (
    accountid BIGSERIAL PRIMARY KEY,
    login TEXT UNIQUE, -- username
    pwhash TEXT UNIQUE, -- encrypted argon2id hash
    twofactor TEXT, -- encrypted two factor auth shared secret
    active BOOLEAN DEFAULT FALSE,
    email TEXT,
    external_auth JSONB,
    email_activation TEXT,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);

/* Two Factor Auth -- 30 day remember feature */
CREATE TABLE IF NOT EXISTS faqoff_account_known_device (
    knowndeviceid BIGSERIAL PRIMARY KEY,
    accountid BIGINT REFERENCES faqoff_accounts(accountid),
    selector TEXT,
    validator TEXT,
    created TIMESTAMP DEFAULT NOW()
);
