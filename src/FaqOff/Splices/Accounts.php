<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Soatok\AnthroKit\Auth\Splices\Accounts as BaseClass;

/**
 * Class Accounts
 * @package Soatok\FaqOff\Splices
 */
class Accounts extends BaseClass
{
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
