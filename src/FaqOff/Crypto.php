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
     * @param HiddenString $pw
     * @param HiddenString $sk
     * @param string $aad
     * @return string
     * @throws \SodiumException
     */
    public static function pwHash(HiddenString $pw, HiddenString $sk, string $aad = ''): string
    {
        $nonce = \random_bytes(24);
        $cipher = NaCl::crypto_aead_xchacha20poly1305_ietf_encrypt(
            \password_hash($pw->getString(), PASSWORD_ARGON2ID),
            $nonce . $aad,
            $nonce,
            $sk->getString()
        );
        if (!\is_string($cipher)) {
            throw new \SodiumException('Could not encrypt');
        }
        return Base64UrlSafe::encode($nonce . $cipher);
    }

    /**
     * @param HiddenString $pw
     * @param HiddenString $sk
     * @param string $hash
     * @param string $aad
     * @return bool
     * @throws \SodiumException
     */
    public static function pwVerify(HiddenString $pw, HiddenString $sk, string $hash, string $aad = ''): bool
    {
        $decoded = Base64UrlSafe::decode($hash);
        $nonce = Binary::safeSubstr($decoded, 0, 24);
        $cipher = Binary::safeSubstr($decoded, 24);

        $plaintext = NaCl::crypto_aead_xchacha20poly1305_ietf_decrypt(
            $cipher,
            $nonce . $aad,
            $nonce,
            $sk->getString()
        );
        $valid = \password_verify($pw->getString(), $plaintext);
        \sodium_memzero($plaintext);
        return $valid;
    }
}
