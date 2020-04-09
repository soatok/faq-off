<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Splice;

/**
 * Class Questions
 * @package Soatok\FaqOff\Splices
 */
class Questions extends Splice
{
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
     * Load a specific question.
     *
     * @param int $questionId
     * @return array
     */
    public function getQuestion(int $questionId): array
    {
        $row = $this->db->row(
            "SELECT * FROM faqoff_question_box WHERE questionid = ?",
            $questionId
        );
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
        $queryString = "SELECT q.*, c.collectionid, NULL
            FROM faqoff_question_box q
            LEFT JOIN faqoff_collection c
                ON q.collectionid = c.collectionid
            WHERE c.authorid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT q.archived";
        }

        $queryString .= ' UNION ';

        $queryString .= "SELECT q.*, e.collectionid, e.entryid
            FROM faqoff_question_box q
            LEFT JOIN faqoff_entry e
                ON q.collectionid = e.entryid
            WHERE e.authorid = ?";
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
        $queryString = "SELECT * FROM faqoff_question_box WHERE collectionid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT archived";
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
        $queryString = "SELECT * FROM faqoff_question_box WHERE entryid = ?";
        if (!$includeHidden) {
            $queryString .= " AND NOT archived";
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
}
