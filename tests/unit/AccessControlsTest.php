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

    public function testAuthorAccess()
    {
        $db = TestHelper::getContainer()['db'];
        $db->beginTransaction();
        $first = $db->insertGet(
            'faqoff_accounts',
            ['login' => 'phpunit-' . bin2hex(random_bytes(16))],
            'accountid'
        );
        $second = $db->insertGet(
            'faqoff_accounts',
            ['login' => 'phpunit-' . bin2hex(random_bytes(16))],
            'accountid'
        );
        $third = $db->insertGet(
            'faqoff_accounts',
            ['login' => 'phpunit-' . bin2hex(random_bytes(16))],
            'accountid'
        );
        $authorId = $db->insertGet(
            'faqoff_author',
            [
                'ownerid' => $second,
                'screenname' => 'phpunit_' . bin2hex(random_bytes(16)),
                'biography' => 'phpunit'
            ],
            'authorid'
        );
        $db->insert(
            'faqoff_author_contributor',
            [
                'authorid' => $authorId,
                'accountid' => $third
            ]
        );

        // Non-owner, non-contributor
        $_SESSION['account_id'] = $first;
        TestHelper::fakeRequest('GET', '/manage/author/' . $authorId);
        $response = TestHelper::getResponse();
        $this->assertSame(
            StatusCode::HTTP_FOUND,
            $response->getStatusCode(),
            'Not being redirected from author profile (but should be)'
        );

        // Owner
        $_SESSION['account_id'] = $second;
        TestHelper::fakeRequest('GET', '/manage/author/' . $authorId);
        $response = TestHelper::getResponse();
        $this->assertSame(
            StatusCode::HTTP_OK,
            $response->getStatusCode(),
            'Redirected from author profile (owner)'
        );

        // Non-owner, but a contributor
        $_SESSION['account_id'] = $third;
        TestHelper::fakeRequest('GET', '/manage/author/' . $authorId);
        $response = TestHelper::getResponse();
        $this->assertSame(
            StatusCode::HTTP_OK,
            $response->getStatusCode(),
            'Redirected from author profile (contributor)'
        );
        $db->rollBack();
    }
}
