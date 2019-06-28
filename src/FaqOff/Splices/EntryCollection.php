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
     * @param int $authorId
     * @param array $data
     * @return int|null
     * @throws \Exception
     */
    public function create(int $authorId, array $data): ?int
    {
        if (empty($data['title'])) {
            throw new \Exception('You must provide a collection name');
        }
        $url = $data['url'] ?? null;
        if (empty($url)) {
            $url = $this->getDataUrl($data['title'], $authorId);
            if (empty($url)) {
                throw new \Exception('URL is empty');
            }
        }
        if ($this->db->exists(
            "SELECT count(*) FROM faqoff_collection WHERE authorid = ? AND url = ?",
            $authorId,
            $url
        )) {
            throw new \Exception('The URL you manually provided is already taken.');
        }

        $collectionId = $this->db->insertGet(
            'faqoff_collection',
            [
                'authorid' => $authorId,
                'title' => $data['title'],
                'url' => $url
            ],
            'collectionid'
        );
        if (!$collectionId) {
            return null;
        }
        return (int) $collectionId;
    }

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
     * @return array
     */
    public function getAllByAuthor(int $authorId): array
    {
        $collections = $this->db->run(
            "SELECT
                faqoff_collection.*,
                faqoff_author.screenname AS author_screenname
            FROM faqoff_collection
            JOIN faqoff_author ON faqoff_collection.authorid = faqoff_author.authorid
            WHERE faqoff_collection.authorid = ?",
            $authorId
        );
        if (!$collections) {
            return [];
        }
        return $collections;
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

    /**
     * @param string $title
     * @param int|null $authorId
     * @return string
     */
    public function getDataUrl(string $title, int $authorId = null): string
    {
        $base = preg_replace('#[^a-z0-9\-]#', '-', strtolower($title));
        $base = trim($base, '-');
        $url = $base;
        $i = 1;
        while (
            $this->db->exists(
                "SELECT count(*) FROM faqoff_collection WHERE authorid = ? AND url = ?",
                $authorId,
                $url
            )
        ) {
            $url = $base . '-' . (++$i);
        }
        return $url;
    }
}
