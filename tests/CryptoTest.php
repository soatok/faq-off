<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Tests;

use ParagonIE\HiddenString\HiddenString;
use PHPUnit\Framework\TestCase;
use Soatok\FaqOff\Crypto;

/**
 * Class CryptoTest
 * @package Soatok\FaqOff\Tests
 */
class CryptoTest extends TestCase
{
    public function testIdempotent()
    {
        $password = new HiddenString('correct horse battery staple');
        $key = new HiddenString(random_bytes(32));

        $cipher = Crypto::pwHash($password, $key);
        $this->assertTrue(Crypto::pwVerify($password, $key, $cipher));
    }
}
