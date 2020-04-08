ALTER TABLE faqoff_collection
    ADD COLUMN allow_questions BOOLEAN DEFAULT FALSE;

ALTER TABLE faqoff_entry
    ADD COLUMN allow_questions BOOLEAN DEFAULT FALSE;
