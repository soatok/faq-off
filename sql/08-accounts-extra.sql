ALTER TABLE faqoff_accounts ADD COLUMN public_id TEXT UNIQUE;
ALTER TABLE faqoff_accounts ADD COLUMN can_invite BOOLEAN DEFAULT TRUE;
