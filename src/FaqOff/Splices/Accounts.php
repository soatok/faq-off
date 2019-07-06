<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use ParagonIE\ConstantTime\Base32;
use Soatok\AnthroKit\Auth\Splices\Accounts as BaseClass;

/**
 * Class Accounts
 * @package Soatok\FaqOff\Splices
 */
class Accounts extends BaseClass
{
    /**
     * @param int $accountId
     * @return string
     * @throws \Exception
     */
    public function getPublicId(int $accountId): string
    {
        $publicId = $this->db->cell(
            "SELECT public_id FROM faqoff_accounts WHERE accountid = ?",
            $accountId
        );
        if (empty($publicId)) {
            $this->generatePublicId($accountId);
            return $this->getPublicId($accountId);
        }
        return $publicId;
    }

    /**
     * @param int $accountId
     * @return bool
     * @throws \Exception
     */
    public function generatePublicId(int $accountId): bool
    {
        $this->db->beginTransaction();
        do {
            $random = Base32::encodeUnpadded(random_bytes(15));
        } while ($this->db->exists(
            "SELECT count(*) FROM faqoff_accounts WHERE public_id = ? AND accountid != ?",
            $random,
            $accountId
        ));
        $this->db->update(
            'faqoff_accounts',
            [
                'public_id' => $random
            ],
            [
                'accountid' => $accountId
            ]
        );
        return $this->db->commit();
    }

    /**
     * @param string $publicId
     * @return int|null
     */
    public function getAccountIdByPublicId(string $publicId): ?int
    {
        $accountId = $this->db->cell(
            "SELECT accountid FROM faqoff_accounts WHERE public_id = ?",
            $publicId
        );
        if (!$accountId) {
            return null;
        }
        return $accountId;
    }

    /**
     * @param int $accountId
     * @return array
     */
    public function getUnusedInviteCodes(int $accountId): array
    {
        $codes = $this->db->run(
            "SELECT 
                *
            FROM 
                faqoff_invites 
            WHERE
                invitefrom = ? AND NOT claimed 
            ORDER BY created DESC",
            $accountId
        );
        if (!$codes) {
            return [];
        }
        return $codes;
    }
}

