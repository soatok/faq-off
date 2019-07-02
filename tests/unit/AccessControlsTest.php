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
    }
}
