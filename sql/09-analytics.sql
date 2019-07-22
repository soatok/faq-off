CREATE TABLE IF NOT EXISTS faqoff_entry_accesslog (
    logid BIGSERIAL PRIMARY KEY,
    entry BIGINT REFERENCES faqoff_entry (entryid),
    account BIGINT, -- Masked user account ID
    ipaddr TEXT, -- Masked IPv4 or IPv6 address
    uagent TEXT, -- Hash of the HTTP User Agent
    hittime TIMESTAMP DEFAULT NOW()
);

CREATE VIEW faqoff_view_entry_24h AS
    SELECT entry, COUNT(DISTINCT (ipaddr, entry)) AS count
    FROM faqoff_entry_accesslog
    WHERE hittime + '1 day' >= now()
    GROUP BY entry
    ORDER BY count DESC;

CREATE VIEW faqoff_view_collection_24h AS
    SELECT a.collectionid, SUM(b.count) AS hits
    FROM faqoff_entry a
    JOIN faqoff_view_entry_24h b
    ON a.entryid = b.entry
    GROUP BY a.collectionid
    ORDER BY hits DESC;
