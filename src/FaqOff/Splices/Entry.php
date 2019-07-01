<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

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
     * @return int|null
     * @throws \Exception
     */
    public function create(
        int $collectionId,
        int $authorId,
        string $title,
        string $contents,
        array $attachTo
    ): ?int {
        $newEntryId = $this->db->insertGet(
            'faqoff_entry',
            [
                'collectionid' => $collectionId,
                'authorid' => $authorId,
                'title' => $title,
                'contents' => $contents,
            ],
            'entryid'
        );

        // Attach question as follow-up to previously-existing question
        foreach ($attachTo as $attach) {
            $this->attachTo($attach, $newEntryId, $collectionId, $authorId);
        }
        return $newEntryId;
    }

    /**
     * @param int $collectionId
     * @param string $url
     * @return array
     */
    public function getByCollectionAndUrl(int $collectionId, string $url): array
    {
        $collection = $this->db->row(
            "SELECT
                *
            FROM
                faqoff_entry
            WHERE 
                collectionid = ? AND url = ?",
            $collectionId,
            $url
        );
        if (!$collection) {
            return [];
        }
        return $collection;
    }

    /**
     * @param int $collectionId
     * @return array
     */
    public function listByCollectionId(int $collectionId): array
    {
        $collections = $this->db->run(
            "SELECT * FROM faqoff_entry
            WHERE collectionid = ? 
            ORDER BY modified DESC, created DESC",
            $collectionId
        );
        if (!$collections) {
            return [];
        }
        return $collections;
    }
}
