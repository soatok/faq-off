DROP VIEW faqoff_view_entry_24h;
CREATE VIEW faqoff_view_entry_24h AS
    SELECT entry, COUNT(DISTINCT (ipaddr, entry)) AS count
    FROM faqoff_entry_accesslog
    WHERE hittime + '1 day' >= now()
    GROUP BY entry
    ORDER BY count DESC;
