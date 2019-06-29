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
