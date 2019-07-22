<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\HiddenString\HiddenString;
use Soatok\AnthroKit\Auth\Splices\Accounts as BaseClass;
use Soatok\DholeCrypto\Password;

/**
 * Class Accounts
 * @package Soatok\FaqOff\Splices
 */
class Accounts extends BaseClass
{
    /**
     * @param string $login
     * @param HiddenString $password
     * @param string $email
     * @param string|null $inviteCode
     *
     * @return int
     * @throws \Exception
     * @throws \SodiumException
     */
    public function createAccount(
        string $login,
        HiddenString $password,
        string $email,
        ?string $inviteCode = null
    ): int {
        $id = parent::createAccount($login, $password, $email, $inviteCode);
        $this->generatePublicId($id);
        return $id;
    }

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
     * @return array
     */
    public function getInviteTree(): array
    {
        $uninvited = $this->db->run(
            "SELECT a.accountid, a.public_id, a.active
             FROM faqoff_accounts a
             WHERE NOT EXISTS (
                 SELECT 1
                 FROM faqoff_invites b
                 WHERE b.newaccountid = a.accountid
             )
             ORDER BY accountid ASC"
        );
        foreach ($uninvited as $i => $row) {
            $children = $this->getInviteSubTree((int) $row['accountid']);
            if ($children) {
                $uninvited[$i]['children'] = $children;
            }
        }
        return $uninvited;
    }

    /**
     * @param int $accountId
     * @return array
     */
    protected function getInviteSubTree(int $accountId): array
    {
        $current = $this->db->run(
            "SELECT acc.accountid, acc.public_id, acc.active
             FROM faqoff_accounts acc
             JOIN faqoff_invites fi on acc.accountid = fi.newaccountid
             WHERE fi.invitefrom = ?",
            $accountId
        );
        foreach ($current as $i => $row) {
            $children = $this->getInviteSubTree((int) $row['accountid']);
            if ($children) {
                $current[$i]['children'] = $children;
            }
        }
        return $current;
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

    /**
     * @return array
     */
    public function listAllWithPublicId(): array
    {
        $accounts = $this->db->run(
            "SELECT * FROM faqoff_accounts ORDER BY created ASC"
        );
        if (empty($accounts)) {
            return [];
        }
        foreach ($accounts as $i => $acc) {
            if (!empty($acc['external_auth'])) {
                $accounts[$i]['external_auth'] = json_decode($acc['external_auth'], true);
            }
        }
        return $accounts;
    }

    /**
     * @param int $accountId
     * @return array
     */
    public function getInfoByAccountId(int $accountId): array
    {
        $acc = $this->db->row(
            "SELECT * FROM faqoff_accounts WHERE accountid = ?",
            $accountId
        );
        if (!empty($acc['external_auth'])) {
            $acc['external_auth'] = json_decode($acc['external_auth'], true);
        }
        return $acc;
    }

    /**
     * @param int $accountId
     * @param array $post
     * @return bool
     * @throws \Exception
     */
    public function updateAccountByAdmin(int $accountId, array $post): bool
    {
        $updates = [
            'login' => $post['login'],
            'active' => $post['active'] ?? false
        ];
        if (empty($post['public_id'])) {
            $this->generatePublicId($accountId);
        } else {
            $updates['public_id'] = $post['public_id'];
        }
        if (!empty($post['disable-two-factor'])) {
            $updates['twofactor'] = null;
        }
        if (!empty($post['disable-external-auth'])) {
            $updates['external_auth'] = null;
        }
        if (!empty($post['password']) && !empty($post['password2'])) {
            if (hash_equals($post['password'], $post['password2'])) {
                $updates['pwhash'] = (new Password($this->passwordKey))->hash(
                    new HiddenString($post['password']),
                    (string) $accountId
                );
            }
        }

        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_accounts',
            $updates,
            ['accountid' => $accountId]
        );
        return $this->db->commit();
    }
}

