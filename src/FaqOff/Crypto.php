<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE_Sodium_Compat as NaCl;

/**
 * Class Crypto
 * @package Soatok\FaqOff
 */
abstract class Crypto
{
    /**
     * @param HiddenString $plaintext
     * @param HiddenString $key
     * @param string $aad
     * @return string
     * @throws \SodiumException
     */
    public static function encrypt(
        HiddenString $plaintext,
        HiddenString $key,
        string $aad = ''
    ): string {
        $nonce = \random_bytes(24);
        $cipher = NaCl::crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext->getString(),
            $nonce . $aad,
            $nonce,
            $key->getString()
        );
        if (!\is_string($cipher)) {
            throw new \SodiumException('Could not encrypt');
        }
        return Base64UrlSafe::encode($nonce . $cipher);
    }

    /**
     * @param string $cipher
     * @param HiddenString $key
     * @param string $aad
     * @return HiddenString
     * @throws \SodiumException
     */
    public static function decrypt(
        string $cipher,
        HiddenString $key,
        string $aad = ''
    ): HiddenString {
        $decoded = Base64UrlSafe::decode($cipher);
        $nonce = Binary::safeSubstr($decoded, 0, 24);
        $cipher = Binary::safeSubstr($decoded, 24);

        $plaintext = NaCl::crypto_aead_xchacha20poly1305_ietf_decrypt(
            $cipher,
            $nonce . $aad,
            $nonce,
            $key->getString()
        );
        if (!\is_string($plaintext)) {
            throw new \Error('Could not decrypt message');
        }
        $hidden = new HiddenString($plaintext);
        \sodium_memzero($plaintext);
        return $hidden;
    }

    /**
     * @param HiddenString $pw
     * @param HiddenString $sk
     * @param string $aad
     * @return string
     * @throws \SodiumException
     */
    public static function pwHash(
        HiddenString $pw,
        HiddenString $sk,
        string $aad = ''
    ): string {
        return static::encrypt(
            new HiddenString(\password_hash($pw->getString(), PASSWORD_ARGON2ID)),
            $sk,
            $aad
        );
    }

    /**
     * @param HiddenString $pw
     * @param HiddenString $sk
     * @param string $hash
     * @param string $aad
     * @return bool
     *
     * @throws \Error
     * @throws \SodiumException
     */
    public static function pwVerify(HiddenString $pw, HiddenString $sk, string $hash, string $aad = ''): bool
    {
        $valid = \password_verify(
            $pw->getString(),
            self::decrypt($hash, $sk, $aad)->getString()
        );
        return $valid;
    }
}
