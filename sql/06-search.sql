CREATE INDEX faqoff_entry_title_idx ON faqoff_entry USING GIN (to_tsvector('english', title));
CREATE INDEX faqoff_entry_contents_idx ON faqoff_entry USING GIN (to_tsvector('english', contents));
CREATE INDEX faqoff_entry_combined_idx ON faqoff_entry USING GIN (to_tsvector('english', title || ' ' || contents));
