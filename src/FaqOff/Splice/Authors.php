<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splice;

use Soatok\AnthroKit\Splice;

/**
 * Class Authors
 * @package Soatok\FaqOff\Splice
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
     * @param string $screenName
     * @param int $accountId
     * @param string $bio
     * @return bool
     */
    public function create(string $screenName, int $accountId, string $bio = ''): bool
    {
        if ($this->screenNameIsTaken($screenName)) {
            // TODO: Throw an exception instead.
            return false;
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
