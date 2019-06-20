<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splice;

use Soatok\AnthroKit\Splice;

/**
 * Class EntryCollection
 * @package Soatok\FaqOff\Splice
 */
class EntryCollection extends Splice
{
    /**
     * @param int $authorId
     * @param string $url
     * @return array
     */
    public function getByAuthorAndUrl(int $authorId, string $url): array
    {
        $collection = $this->db->row(
            "SELECT * FROM faqoff_collection WHERE authorid = ? AND url = ?",
            $authorId,
            $url
        );
        if (!$collection) {
            return [];
        }
        return $collection;
    }
}
