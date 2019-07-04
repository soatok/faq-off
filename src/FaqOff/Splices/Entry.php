<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use SebastianBergmann\Diff\Differ;
use Soatok\AnthroKit\Splice;

/**
 * Class Entry
 * @package Soatok\FaqOff\Splices
 */
class Entry extends Splice
{
    /**
     * @param int $start
     * @param int $followUp
     * @param int $collection
     * @param int $author
     * @return bool
     */
    public function attachTo(int $start, int $followUp, int $collection, int $author): bool
    {
        if (!$this->belongsTo($collection, $start)) {
            // You cannot attach to one outside collection at creation time
            return false;
        }
        $this->db->beginTransaction();
        $options = $this->db->cell(
            "SELECT options FROM faqoff_entry WHERE entryid = ?",
            $start
        );
        if (!empty($options)) {
            $options = json_decode($options, true);
        } else {
            $options = [
                'follow-up' => []
            ];
        }
        $options['follow-up'] []= $followUp;
        $this->db->update(
            'faqoff_entry',
            [
                'options' => json_encode($options)
            ],
            [
                'entryid' => $start
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $collectionId
     * @param int $entryId
     * @return bool
     */
    public function belongsTo(int $collectionId, int $entryId): bool
    {
        return $this->db->exists(
            "SELECT count(*) FROM faqoff_entry 
            WHERE collectionid = ? AND entryid = ?",
            $collectionId,
            $entryId
        );
    }

    /**
     * @param int $collectionId
     * @param int $authorId
     * @param string $title
     * @param string $contents
     * @param array<int, int> $attachTo
     * @param bool $indexMe
     * @return int|null
     * @throws \Exception
     */
    public function create(
        int $collectionId,
        int $authorId,
        string $title,
        string $contents,
        array $attachTo,
        bool $indexMe = false
    ): ?int {
        $newEntryId = $this->db->insertGet(
            'faqoff_entry',
            [
                'collectionid' => $collectionId,
                'authorid' => $authorId,
                'title' => $title,
                'url' => $this->getDataUrl($title, $collectionId),
                'contents' => $contents,
            ],
            'entryid'
        );

        if ($indexMe) {
            $this->db->insert(
                'faqoff_collection_index',
                [
                    'collectionid' => $collectionId,
                    'entryid' => $newEntryId
                ]
            );
        }

        // Attach question as follow-up to previously-existing question
        foreach ($attachTo as $attach) {
            $this->attachTo((int) $attach, $newEntryId, $collectionId, $authorId);
        }
        return $newEntryId;
    }

    /**
     * @param string $query
     * @return array
     */
    public function entrySearch(string $query): array
    {
        $results = $this->db->run(
            "SELECT entryid AS id, title AS text FROM faqoff_entry 
            WHERE to_tsvector('english', title) @@ to_tsquery('english', ?)",
            $query
        );
        if (!$results) {
            return [];
        }
        return $results;
    }

    /**
     * @param array $followUps
     * @param bool $getURLs
     * @return array
     */
    public function getFollowUps(array $followUps = [], bool $getURLs = false): array
    {
        if (empty($followUps)) {
            return [];
        }
        $place = array_fill(0, count($followUps), '?');
        $statement = implode(', ', $place);
        if ($getURLs) {
            $followUps = $this->db->run(
                "SELECT
                    faqoff_entry.entryid,
                    faqoff_entry.url,
                    faqoff_entry.title,
                    fa.screenname AS author_screenname,
                    fc.url AS collection_url
                FROM faqoff_entry
                JOIN faqoff_collection fc on faqoff_entry.collectionid = fc.collectionid
                JOIN faqoff_author fa on faqoff_entry.authorid = fa.authorid
                WHERE faqoff_entry.entryid IN ({$statement})
                ORDER BY faqoff_entry.modified DESC, faqoff_entry.created DESC",
                ...$followUps
            );
        } else {
            $followUps = $this->db->run(
                'SELECT entryid, title FROM faqoff_entry
                WHERE entryid IN (' . $statement . ')',
                ...$followUps
            );
        }
        if (!$followUps) {
            return [];
        }
        return $followUps;
    }

    /**
     * @param int $entryId
     * @return array
     */
    public function getById(int $entryId): array
    {
        $entry = $this->db->row(
            "SELECT * FROM faqoff_entry WHERE entryid = ?",
            $entryId
        );
        if (!$entry) {
            return [];
        }
        $entry['options'] = json_decode($entry['options'] ?? '[]', true);
        $entry['index-me'] = $this->db->exists(
            "SELECT count(*) FROM faqoff_collection_index
            WHERE collectionid = ? AND entryid = ?",
            $entry['collectionid'],
            $entry['entryid']
        );
        return $entry;
    }

    /**
     * @param int $collectionId
     * @param string $url
     * @return array
     */
    public function getByCollectionAndUrl(int $collectionId, string $url): array
    {
        $entry = $this->db->row(
            "SELECT
                *
            FROM
                faqoff_entry
            WHERE 
                collectionid = ? AND url = ?",
            $collectionId,
            $url
        );
        if (!$entry) {
            return [];
        }
        $entry['index-me'] = $this->db->exists(
            "SELECT count(*) FROM faqoff_collection_index
            WHERE collectionid = ? AND entryid = ?",
            $collectionId,
            $entry['entryid']
        );
        $entry['options'] = json_decode($entry['options'] ?? '[]', true);
        if (!empty($entry['options']['follow-up'])) {
            $entry['follow-ups'] = $this->getFollowUps(
                $entry['options']['follow-up'],
                true
            );
        } else {
            $entry['follow-ups'] = [];
        }
        return $entry;
    }

    /**
     * @param int $collectionId
     * @return array
     */
    public function listIndexedByCollectionId(int $collectionId): array
    {
        $entries = $this->db->run(
            "SELECT
                faqoff_entry.entryid,
                faqoff_entry.authorid,
                faqoff_entry.collectionid,
                faqoff_entry.url,
                faqoff_entry.title,
                faqoff_entry.options,
                fa.screenname AS author_screenname,
                fc.url AS collection_url
            FROM faqoff_entry
            JOIN faqoff_collection_index 
                ON faqoff_entry.entryid = faqoff_collection_index.entryid
            JOIN faqoff_collection fc on faqoff_entry.collectionid = fc.collectionid
            JOIN faqoff_author fa on faqoff_entry.authorid = fa.authorid
            WHERE faqoff_entry.collectionid = ? AND faqoff_collection_index.collectionid = ?
            ORDER BY faqoff_entry.modified DESC, faqoff_entry.created DESC",
            $collectionId,
            $collectionId
        );
        if (!$entries) {
            return [];
        }
        foreach ($entries as $index => $entry) {
            $entries[$index]['index-me'] = true;
            $entries[$index]['options'] = json_decode($entry['options'] ?? '[]', true);
        }
        return $entries;
    }

    /**
     * @param int $collectionId
     * @return array
     */
    public function listByCollectionId(int $collectionId): array
    {
        $entries = $this->db->run(
            "SELECT * FROM faqoff_entry
            WHERE collectionid = ? 
            ORDER BY modified DESC, created DESC",
            $collectionId
        );
        if (!$entries) {
            return [];
        }
        foreach ($entries as $index => $entry) {
            $entries[$index]['index-me'] = $this->db->exists(
                "SELECT count(*) FROM faqoff_collection_index
                WHERE collectionid = ? AND entryid = ?",
                $collectionId,
                $entry['entryid']
            );
            $entries[$index]['options'] = json_decode($entry['options'] ?? '[]', true);
        }
        return $entries;
    }

    /**
     * @param int $entryId
     * @param array $post
     * @return bool
     * @throws \Exception
     */
    public function update(int $entryId, array $post): bool
    {
        $this->db->beginTransaction();
        $old = $this->getById($entryId);

        // Insert a change
        $this->db->insert(
            'faqoff_entry_changelog',
            [
                'entryid' => $entryId,
                'accountid' => $_SESSION['accountid'],
                'diff' => (new Differ())->diff(
                    $old['contents'],
                    $post['contents']
                ),
                'modified' => (new \DateTime())
                    ->format(\DateTime::ISO8601)
            ]
        );

        $indexed = $this->db->exists(
            "SELECT count(*) FROM faqoff_collection_index
                WHERE collectionid = ? AND entryid = ?",
            $old['collectionid'],
            $entryId
        );
        if (!$post['index-me'] && $indexed) {
            $this->db->delete(
                'faqoff_collection_index',
                [
                    'entryid' => $entryId
                ]
            );
        } elseif ($post['index-me'] && !$indexed) {
            $this->db->insert(
                'faqoff_collection_index',
                [
                    'collectionid' => $old['collectionid'],
                    'entryid' => $entryId
                ]
            );
        }

        // Coerce to integers
        $options = $old['options'];
        $options['follow-up'] = $post['follow-up'] ?? [];
        array_walk($options['follow-up'], 'intval');

        // Update the record
        $this->db->update(
            'faqoff_entry',
            [
                'title' => $post['title'],
                'contents' => $post['contents'],
                'options' => json_encode($options)
            ],
            ['entryid' => $entryId]
        );
        return $this->db->commit();
    }


    /**
     * Get a unique URL even if a collection/URL collision occurs.
     *
     * @param string $title
     * @param int|null $collectionId
     * @return string
     */
    public function getDataUrl(string $title, int $collectionId = null): string
    {
        $base = preg_replace('#[^a-z0-9\-]#', '-', strtolower($title));
        $base = trim($base, '-');
        $url = $base;
        $i = 1;
        while (
            $this->db->exists(
                "SELECT count(*) FROM faqoff_entry WHERE collectionid = ? AND url = ?",
                $collectionId,
                $url
            )
        ) {
            $url = $base . '-' . (++$i);
        }
        return $url;
    }
}
