<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Splice;

/**
 * Class Authors
 * @package Soatok\FaqOff\Splices
 */
class Authors extends Splice
{
    /**
     * Returns true if you're an owner OR contributor
     *
     * @param int $authorId
     * @param int $accountId
     * @return bool
     */
    public function accountHasAccess(int $authorId, int $accountId): bool
    {
        if ($this->accountIsOwner($authorId, $accountId)) {
            return true;
        }
        return $this->accountIsContributor($authorId, $accountId);
    }

    /**
     * Returns true only if $accountId is the owner of $authorId
     *
     * @param int $authorId
     * @param int $accountId
     * @return bool
     */
    public function accountIsOwner(int $authorId, int $accountId): bool
    {
        return $accountId === $this->db->cell(
            "SELECT ownerid FROM faqoff_author WHERE authorid = ?",
            $authorId
        );
    }

    /**
     * Return true if you're a contributor. Doesn't check ownership.
     *
     * @param int $authorId
     * @param int $accountId
     * @return bool
     */
    public function accountIsContributor(int $authorId, int $accountId): bool
    {
        return $this->db->exists(
            "SELECT 
                count(*)
            FROM
                faqoff_author_contributor
            WHERE
                authorid = ? AND accountid = ? ",
            $authorId,
            $accountId
        );
    }

    /**
     * @param int $authorId
     * @return int
     */
    public function countCollections(int $authorId): int
    {
        return (int) $this->db->cell(
            "SELECT count(*) FROM faqoff_collection WHERE authorid = ?",
            $authorId
        );
    }

    /**
     * @param int $authorId
     * @return int
     */
    public function countContributors(int $authorId): int
    {
        return (int) $this->db->cell(
            "SELECT count(*) FROM faqoff_author_contributor WHERE authorid = ?",
            $authorId
        );
    }

    /**
     * @param string $screenName
     * @param int $accountId
     * @param string $bio
     * @return bool
     *
     * @throws \Exception
     */
    public function create(string $screenName, int $accountId, string $bio = ''): bool
    {
        if ($this->screenNameIsTaken($screenName)) {
            throw new \Exception('Screen name is already taken');
        }
        $this->db->beginTransaction();
        $this->db->insert(
            'faqoff_author',
            [
                'ownerid' => $accountId,
                'screenname' => $screenName,
                'biography' => $bio
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $accountId
     * @param bool $ownedOnly
     * @return array
     */
    public function getByAccount(int $accountId, bool $ownedOnly = false): array
    {
        if ($ownedOnly) {
            return $this->db->run(
                "SELECT *, TRUE as \"is_owner\" FROM faqoff_author WHERE ownerid = ? ORDER BY screenname ASC",
                $accountId
            );
        }
        $rows = $this->db->run(
            "SELECT * FROM faqoff_author
               WHERE ownerid = ? OR authorid IN (
                   SELECT authorid FROM faqoff_author_contributor WHERE accountid = ?
               )
               ORDER BY screenname ASC
            ",
            $accountId,
            $accountId
        );
        foreach ($rows as $i => $row) {
            $rows[$i]['is_owner'] = ($row['ownerid'] === $accountId);
        }
        return $rows;
    }

    /**
     * @param int $authorId
     * @return array
     */
    public function getById(int $authorId): array
    {
        $author = $this->db->row(
            "SELECT * FROM faqoff_author WHERE authorid = ?",
            $authorId
        );
        if (!$author) {
            return [];
        }
        return $author;
    }

    /**
     * Get an author by its screen name
     *
     * @param string $screenName
     * @return array
     */
    public function getByScreenName(string $screenName): array
    {
        $author = $this->db->row(
            "SELECT * FROM faqoff_author WHERE screenname = ?",
            $screenName
        );
        if (!$author) {
            return [];
        }
        return $author;
    }

    /**
     * @param bool $extraData
     * @return array
     */
    public function listAll(bool $extraData = false): array
    {
        if ($extraData) {
            $authors = $this->db->run(
                "SELECT a.*, fa.public_id AS owner_public_id
                 FROM faqoff_author a
                 LEFT JOIN faqoff_accounts fa ON a.ownerid = fa.accountid
                 ORDER BY a.screenname ASC"
            );
            if (empty($authors)) {
                return [];
            }
            foreach ($authors as $i => $auth) {
                $authors[$i]['collections'] = $this->countCollections(
                    (int) $auth['authorid']
                );
                $authors[$i]['contributors'] = $this->countContributors(
                    (int) $auth['authorid']
                );
            }
            return $authors;
        }
        return $this->db->run(
            "SELECT authorid, screenname FROM faqoff_author ORDER BY screenname ASC"
        );
    }

    /**
     * @return array
     */
    public function listForAdminQuestionIndex(): array
    {
        $authors = $this->db->run(
            "SELECT
                 authorid AS id, screenname AS label 
             FROM 
                 faqoff_author
             ORDER BY
                 created ASC"
        );
        if (empty($authors)) {
            return [];
        }
        return $authors;
    }

    /**
     * Allow another user account access to this author profile
     *
     * @param int $authorId
     * @param int $accountId
     * @return bool
     */
    public function grantAccess(int $authorId, int $accountId): bool
    {
        if ($this->accountHasAccess($authorId, $accountId)) {
            // Already has access
            return false;
        }
        $this->db->beginTransaction();
        $this->db->insert(
            'faqoff_author_contributor',
            [
                'authorid' => $authorId,
                'accountid' => $accountId
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param int $authorId
     * @return array
     */
    public function listContributors(int $authorId): array
    {
        $contributors = $this->db->col(
            "SELECT
                acc.public_id
            FROM faqoff_author_contributor fac
            JOIN faqoff_accounts acc ON fac.accountid = acc.accountid
            WHERE fac.authorid = ?
            ",
            0,
            $authorId
        );
        if (empty($contributors)) {
            return [];
        }
        return $contributors;
    }

    /**
     * @param int $authorId
     * @return array<int, int>
     */
    public function getContributorIds(int $authorId): array
    {
        return $this->db->col(
            "SELECT accountid FROM faqoff_author_contributor WHERE authorid = ?",
            0,
            $authorId
        );
    }

    /**
     * Revoke a user's access to this author profile
     *
     * @param int $authorId
     * @param int $accountId
     * @return bool
     */
    public function revokeAccess(int $authorId, int $accountId): bool
    {
        if (!$this->accountIsContributor($authorId, $accountId)) {
            // Is not a normal contributor
            return false;
        }
        $this->db->beginTransaction();
        $this->db->delete(
            'faqoff_author_contributor',
            [
                'authorid' => $authorId,
                'accountid' => $accountId
            ]
        );
        return $this->db->commit();
    }

    /**
     * @return array
     */
    public function listAllScreenNames(): array
    {
        return $this->db->col(
            "SELECT screenname FROM faqoff_author ORDER BY screenname ASC"
        );
    }

    /**
     * @param string $screenName
     * @return bool
     */
    public function screenNameIsTaken(string $screenName): bool
    {
        return $this->db->exists(
            "SELECT count(*) FROM faqoff_author WHERE screenname = ?",
            $screenName
        );
    }

    /**
     * @param int $authorId
     * @param array $post
     * @return bool
     */
    public function updateAuthorByAdmin(int $authorId, array $post): bool
    {
        $this->db->beginTransaction();
        $oldContributors = $this->getContributorIds($authorId);
        $newContributors = $post['contributors'];

        $deletes = array_diff($oldContributors, $newContributors);
        $inserts = array_diff($newContributors, $oldContributors);

        if ($deletes) {
            $placeholders = implode(', ', array_fill(0, count($deletes), '?'));
            $params = $deletes;
            array_unshift($params, $authorId);
            $this->db->safeQuery(
                "DELETE FROM faqoff_author_contributor 
                 WHERE authorid = ? AND accountid IN ({$placeholders})",
                array_values($params)
            );
        }
        foreach ($inserts as $newId) {
            $this->db->insert(
                'faqoff_author_contributor',
                [
                    'authorid' => $authorId,
                    'accountid' => $newId
                ]
            );
        }

        $this->db->update(
            'faqoff_author',
            [
                'screenname' => $post['screenname'],
                'biography' => $post['biography']
            ],
            [
                'authorid' => $authorId
            ]
        );

        return $this->db->commit();
    }

    /**
     * Update biography.
     *
     * @param int $authorId
     * @param string $bio
     * @return bool
     * @throws \Exception
     */
    public function updateBiography(int $authorId, string $bio): bool
    {
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_author',
            [
                'biography' => $bio,
                'modified' => (new \DateTime())
                    ->format(\DateTime::ISO8601)
            ],
            ['authorid' => $authorId]
        );
        return $this->db->commit();
    }
}
