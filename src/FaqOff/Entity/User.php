<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Entity;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\HiddenString\HiddenString;
use Soatok\FaqOff\Crypto;
use Soatok\FaqOff\Entity;

/**
 * Class User
 * @package Soatok\FaqOff\Entity
 */
class User extends Entity
{
    /** @var HiddenString $key */
    private $key;

    /**
     * User constructor.
     * @param EasyDB $db
     * @param HiddenString $key
     */
    public function __construct(EasyDB $db, HiddenString $key)
    {
        parent::__construct($db);
        $this->key = $key;
    }

    /**
     * @param array $post
     * @return int|null
     * @throws \SodiumException
     */
    public function login(array $post): ?int
    {
        $user = $this->db->row(
            "SELECT * FROM faqoff_user WHERE login = ?",
            $post['username']
        );
        if (empty($user)) {
            // User not found!
            return null;
        }

        if (!Crypto::pwVerify(
            new HiddenString($post['passphrase']),
            $this->key,
            $user['pwhash'],
            \pack('P', (int) $user['userid'])
        )) {
            // Invalid password!
            return null;
        }

        return (int) $user['userid'];
    }

    /**
     * @param array $post
     * @return bool
     * @throws \SodiumException
     */
    public function register(array $post): bool
    {
        $this->db->beginTransaction();
        if ($this->db->exists(
            "SELECT count(userid) FROM faqoff_user WHERE login = ?",
            $post['username']
        )) {
            $this->db->rollBack();
            return false;
        }
        $this->db->insert('faqoff_user', ['login' => $post['username']]);
        $user_id = $this->db->cell(
            "SELECT userid FROM faqoff_user WHERE login = ?",
            $post['username']
        );
        if (!$user_id) {
            $this->db->rollBack();
            return false;
        }

        $pwhash = Crypto::pwHash(
            new HiddenString($post['passphrase']),
            $this->key,
            \pack('P', (int) $user_id)
        );
        $this->db->update(
            'faqoff_user',
            ['pwhash' => $pwhash],
            ['userid' => $user_id]
        );

        return $this->db->commit();
    }
}
