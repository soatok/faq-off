CREATE TABLE faqoff_frontpage_notices (
    noticeid BIGSERIAL PRIMARY KEY, -- OwO *notices*
    headline TEXT,
    body TEXT,
    account_id BIGINT REFERENCES faqoff_accounts (accountid),
    created TIMESTAMP DEFAULT NOW()
);
