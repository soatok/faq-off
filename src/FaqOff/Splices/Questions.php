<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Splice;
use Soatok\FaqOff\MessageOnceTrait;

/**
 * Class Questions
 * @package Soatok\FaqOff\Splices
 */
class Questions extends Splice
{
    use MessageOnceTrait;

    /**
     * Archive a question.
     *
     * @param int $questionId
     * @return bool
     */
    public function archive(int $questionId): bool
    {
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_question_box',
            ['archived' => true],
            ['questionid' => $questionId]
        );
        return $this->db->commit();
    }

    /**
     * Number of questions for an author.
     *
     * @param int $authorId
     * @param bool $includeHidden
     * @return int
     */
    public function countForAuthor(int $authorId, bool $includeHidden = false): int
    {
        $queryString = "SELECT count(q.questionid)
            FROM faqoff_question_box q
            LEFT JOIN faqoff_collection c
                ON q.collectionid = c.collectionid
            LEFT JOIN faqoff_entry e
                ON q.entryid = e.entryid
            WHERE (c.authorid = ? OR e.authorid = ?)";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        return (int) $this->db->cell($queryString, $authorId, $authorId);
    }

    /**
     * Number of questions for a collection.
     *
     * @param int $collectionId
     * @param bool $includeHidden
     * @return int
     */
    public function countForCollection(int $collectionId, bool $includeHidden = false): int
    {
        $queryString = "SELECT count(q.questionid)
            FROM faqoff_question_box q
            WHERE q.collectionid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        return (int) $this->db->cell($queryString, $collectionId);
    }

    /**
     * Number of questions for an entry.
     *
     * @param int $entryId
     * @param bool $includeHidden
     * @return int
     */
    public function countForEntry(int $entryId, bool $includeHidden = false): int
    {
        $queryString = "SELECT count(q.questionid)
            FROM faqoff_question_box q
            WHERE q.entryid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        return (int) $this->db->cell($queryString, $entryId);
    }

    /**
     * Load a specific question.
     *
     * @param int $questionId
     * @return array
     */
    public function getQuestion(int $questionId): array
    {
        $row = $this->db->row(
            "SELECT
                 q.*,
                 fa.public_id
             FROM
                 faqoff_question_box q
            LEFT JOIN faqoff_accounts fa
                 ON q.asked_by = fa.accountid
             WHERE q. questionid = ?",
            $questionId
        );
        if (empty($row)) {
            return [];
        }
        return $row;
    }

    /**
     * Load a specific question.
     * Checks that the given author matches the author on file.
     *
     * @param int $questionId
     * @param int $authorId
     * @param bool $includeHidden
     * @return array
     */
    public function getQuestionAuthorCheck(
        int $questionId,
        int $authorId,
        bool $includeHidden = false
    ): array
    {
        $queryString = "SELECT q.*, e.title AS entry_title, fa.public_id
            FROM faqoff_question_box q
            LEFT JOIN faqoff_accounts fa
                ON q.asked_by = fa.accountid
            LEFT JOIN faqoff_collection c
                ON q.collectionid = c.collectionid
            LEFT JOIN faqoff_entry e
                ON q.entryid = e.entryid
            WHERE q.questionid = ? AND (c.authorid = ? OR e.authorid = ?)";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        $row = $this->db->row($queryString, $questionId, $authorId, $authorId);
        if (empty($row)) {
            return [];
        }
        return $row;
    }

    /**
     * Get all questions asked to a collection or entry owned by a given author.
     *
     * @param int $authorId
     * @param bool $includeHidden
     * @return array
     */
    public function getForAuthor(int $authorId, bool $includeHidden = false): array
    {
        $queryString = "SELECT 
                q.*, 
                COALESCE(e.collectionid, c.collectionid) AS collectionid,
                q.entryid,
                e.title AS entry_title,
                fa.public_id
            FROM faqoff_question_box q
            LEFT JOIN faqoff_accounts fa
                ON q.asked_by = fa.accountid
            LEFT JOIN faqoff_collection c
                ON q.collectionid = c.collectionid
            LEFT JOIN faqoff_entry e
                ON q.entryid = e.entryid
            WHERE (c.authorid = ? OR e.authorid = ?)";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        $rows = $this->db->run($queryString, $authorId, $authorId);
        if (empty($rows)) {
            return [];
        }
        return $rows;
    }

    /**
     * Get all questions asked to a given collection.
     * (optionally include answered/hidden ones.)
     *
     * @param int $collectionId
     * @param bool $includeHidden
     * @return array
     */
    public function getForCollection(int $collectionId, bool $includeHidden = false): array
    {
        $queryString = "SELECT q.*, fa.public_id
            FROM faqoff_question_box q
            LEFT JOIN faqoff_accounts fa ON q.asked_by = fa.accountid
            WHERE q.collectionid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        $rows = $this->db->run($queryString, $collectionId);
        if (empty($rows)) {
            return [];
        }
        return $rows;
    }

    /**
     * Get all questions asked as a follow-up to a given entry.
     * (optionally include answered/hidden ones.)
     *
     * @param int $entryId
     * @param bool $includeHidden
     * @return array
     */
    public function getForEntry(int $entryId, bool $includeHidden = false): array
    {
        $queryString = "SELECT q.*, fa.public_id
            FROM faqoff_question_box q
            LEFT JOIN faqoff_accounts fa ON q.asked_by = fa.accountid
            WHERE q.entryid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }
        $rows = $this->db->run($queryString, $entryId);
        if (empty($rows)) {
            return [];
        }
        return $rows;
    }

    /**
     * Get all questions asked by a specific account.
     *
     * @param int $accountId
     * @param bool $includeHidden
     * @return array
     */
    public function getFromAccount(int $accountId, bool $includeHidden = false): array
    {
        $queryString = "SELECT * FROM faqoff_question_box WHERE asked_by = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT archived";
        }
        $rows = $this->db->run($queryString, $accountId);
        if (empty($rows)) {
            return [];
        }
        return $rows;
    }

    /**
     * @param int $collectionId
     * @param string $question
     * @param int $accountId
     * @param bool $attribution
     * @return bool
     */
    public function createForCollection(
        int $collectionId,
        string $question,
        int $accountId,
        bool $attribution
    ): bool {
        $this->db->beginTransaction();
        $this->db->insert('faqoff_question_box', [
            'asked_by' => $accountId,
            'attribution' => $attribution,
            'question' => $question,
            'collectionid' => $collectionId
        ]);
        return $this->db->commit();
    }

    /**
     * @param int $entryId
     * @param string $question
     * @param int $accountId
     * @param bool $attribution
     * @return bool
     */
    public function createForEntry(
        int $entryId,
        string $question,
        int $accountId,
        bool $attribution
    ): bool {
        $this->db->beginTransaction();
        $this->db->insert('faqoff_question_box', [
            'asked_by' => $accountId,
            'attribution' => $attribution,
            'question' => $question,
            'entryid' => $entryId
        ]);
        return $this->db->commit();
    }

    /**
     * @param int $questionId
     * @param array $post
     * @return bool
     */
    public function updateByAdmin(int $questionId, array $post): bool
    {
        $updates = [
            'archived' => $post['archived'],
            'attribution' => $post['attribution'],
            'question' => $post['question']
        ];
        if (!empty($post['collection'])) {
            if (!empty($post['entry'])) {
                $this->messageOnce(
                    'Questions cannot be assigned to both a collection and an entry.',
                    'error'
                );
                return false;
            }
            $updates['collectionid'] = $post['collection'];
            $updates['entryid'] = null;
        } elseif (!empty($post['entry'])) {
            $updates['collectionid'] = null;
            $updates['entryid'] = $post['entry'];
        } else {
            $this->messageOnce(
                'Questions must be assigned to a collection or an entry.',
                'error'
            );
            return false;
        }
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_question_box',
            $updates,
            ['questionid' => $questionId]
        );
        return $this->db->commit();
    }
}
