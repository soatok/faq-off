<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Tests;

use PHPUnit\Framework\TestCase;
use Slim\Http\StatusCode;
use Soatok\FaqOff\TestHelper;

/**
 * Class AccessControlsTest
 * @package Soatok\FaqOff\Tests
 */
class AccessControlsTest extends TestCase
{
    public function testAuthenticated()
    {
        if (isset($_SESSION['account_id'])) {
            unset($_SESSION['account_id']);
        }

        TestHelper::fakeRequest('GET', '/');
        $response = TestHelper::getResponse();
        $this->assertSame(
            StatusCode::HTTP_OK,
            $response->getStatusCode(),
            'Error loading index page'
        );

        TestHelper::fakeRequest('GET', '/manage/authors');
        $response = TestHelper::getResponse();
        $this->assertSame(
            StatusCode::HTTP_FOUND,
            $response->getStatusCode(),
            'Not being redirected from control panel'
        );

        $_SESSION['account_id'] = random_int(1, PHP_INT_MAX - 1);
        $response = TestHelper::getResponse();
        $this->assertSame(
            StatusCode::HTTP_OK,
            $response->getStatusCode(),
            'Being redirected from control panel'
        );
        unset($_SESSION['account_id']);
    }
}
