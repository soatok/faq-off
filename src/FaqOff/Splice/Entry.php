<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splice;

use Soatok\AnthroKit\Splice;

/**
 * Class Entry
 * @package Soatok\FaqOff\Splice
 */
class Entry extends Splice
{
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
}
