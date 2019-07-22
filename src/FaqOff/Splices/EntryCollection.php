<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Splice;
use Soatok\FaqOff\Exceptions\CollectionNotFoundException;

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
                'description' => $data['description'],
                'theme' => $data['theme'] < 1 ? null : $data['theme'],
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
     * @param int $collectionId
     * @return array
     * @throws CollectionNotFoundException
     */
    public function getById(int $collectionId): array
    {
        $collection = $this->db->row(
            "SELECT
                 faqoff_collection.*,
                 faqoff_author.screenname AS author_screenname
            FROM faqoff_collection
            JOIN faqoff_author ON faqoff_collection.authorid = faqoff_author.authorid
            WHERE faqoff_collection.collectionid = ?",
            $collectionId
        );
        if (!$collection) {
            throw new CollectionNotFoundException('Collection not found!');
        }
        return $collection;
    }

    /**
     * @param int $collectionId
     * @return int
     * @throws CollectionNotFoundException
     */
    public function getCollectionAuthorId(int $collectionId): int
    {
        $authorId = $this->db->cell(
            "SELECT authorid FROM faqoff_collection WHERE collectionid = ?",
            $collectionId
        );
        if (!$authorId) {
            throw new CollectionNotFoundException();
        }
        return $authorId;
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
     * @return array
     */
    public function getAll(): array
    {
        $collections = $this->db->run(
            "SELECT
                faqoff_collection.*,
                faqoff_author.screenname AS author_screenname
            FROM faqoff_collection
            JOIN faqoff_author ON faqoff_collection.authorid = faqoff_author.authorid",
        );
        if (!$collections) {
            return [];
        }
        return $collections;
    }
    /**
     * @param int $amount
     * @param int $offset
     * @return array
     */
    public function getMostPopular(int $amount = 10, int $offset = 0): array
    {
        $entries = $this->db->run(
            "SELECT
                collection.*,
                faqoff_author.screenname AS author_screenname,
                stats.hits
             FROM faqoff_collection collection
             JOIN faqoff_author ON collection.authorid = faqoff_author.authorid
             JOIN faqoff_view_collection_24h stats ON collection.collectionid = stats.collectionid 
             ORDER BY stats.hits DESC
             OFFSET {$offset} LIMIT {$amount}
            "
        );
        if (!$entries) {
            return [];
        }
        foreach ($entries as $i => $entry) {
            $entries[$i]['options'] = json_decode($entry['options'] ?? '[]', true);
        }
        return $entries;
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
     * Get a unique URL even if an author/URL collision occurs.
     *
     * @param string $title
     * @param int|null $authorId
     * @param int|null $existingId
     * @return string
     */
    public function getDataUrl(string $title, int $authorId = null, ?int $existingId = null): string
    {
        $base = preg_replace('#[^a-z0-9\-]#', '-', strtolower($title));
        $base = trim($base, '-');
        $url = $base;
        $i = 1;
        if ($existingId) {
            while (
                $this->db->exists(
                    "SELECT count(*) FROM faqoff_collection WHERE authorid = ? AND url = ? AND collectionid != ?",
                    $authorId,
                    $url,
                    $existingId
                )
            ) {
                $url = $base . '-' . (++$i);
            }
            return $url;
        }
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

    /**
     * @param int $collectionId
     * @param array $postData
     * @return bool
     */
    public function updateAsAdmin(int $collectionId, array $postData = []): bool
    {
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_collection',
            [
                'authorid' => $postData['author'],
                'description' => $postData['description'],
                'theme' => $postData['theme'] < 1 ? null : $postData['theme'],
                'title' => $postData['title']
            ],
            [
                'collectionid' => $collectionId
            ]
        );
        $this->db->update(
            'faqoff_entry',
            [
                'authorid' => $postData['author']
            ],
            [
                'collectionid' => $collectionId
            ]
        );
        if (empty($postData['url'])) {
            $postData['url'] = $this->getDataUrl(
                $postData['title'],
                (int) $postData['author'],
                $collectionId
            );
        }
        $this->db->update(
            'faqoff_collection',
            [
                'url' => $postData['url']
            ],
            [
                'collectionid' => $collectionId
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $collectionId
     * @param array $postData
     * @return bool
     */
    public function update(int $collectionId, array $postData = []): bool
    {
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_collection',
            [
                'theme' => $postData['theme'] < 1 ? null : $postData['theme'],
                'description' => $postData['description'],
                'title' => $postData['title']
            ],
            [
                'collectionid' => $collectionId
            ]
        );
        return $this->db->commit();
    }
}
