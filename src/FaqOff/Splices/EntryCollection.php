<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Splice;

/**
 * Class EntryCollection
 * @package Soatok\FaqOff\Splice
 */
class EntryCollection extends Splice
{
    /**
     * @param int $accountId
     * @return array
     */
    public function getByAccount(int $accountId): array
    {
        return $this->db->run(
            "SELECT
                 faqoff_collection.*,
                 faqoff_author.screenname AS author_screenname
            FROM faqoff_collection
            JOIN faqoff_author ON faqoff_collection.authorid = faqoff_author.authorid
            WHERE faqoff_collection.authorid IN (
               (SELECT authorid FROM faqoff_author WHERE ownerid = ?)
                  UNION 
               (SELECT authorid FROM faqoff_author_contributor WHERE accountid = ?)
           )
           ORDER BY faqoff_author.screenname ASC, faqoff_collection.title ASC
           ",
            $accountId,
            $accountId
        );
    }

    /**
     * @param int $authorId
     * @param string $url
     * @return array
     */
    public function getByAuthorAndUrl(int $authorId, string $url): array
    {
        $collection = $this->db->row(
            "SELECT
                faqoff_collection.*,
                faqoff_author.screenname AS author_screenname
            FROM faqoff_collection
            JOIN faqoff_author ON faqoff_collection.authorid = faqoff_author.authorid
            WHERE faqoff_collection.authorid = ? AND faqoff_collection.url = ?",
            $authorId,
            $url
        );
        if (!$collection) {
            return [];
        }
        return $collection;
    }
}
