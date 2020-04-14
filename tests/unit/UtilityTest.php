<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Tests;

use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Uri;
use Soatok\FaqOff\TestHelper;
use Soatok\FaqOff\Utility;
use PHPUnit\Framework\TestCase;

/**
 * Class UtilityTest
 * @package Soatok\FaqOff\Tests
 */
class UtilityTest extends TestCase
{
    public function testOrderBy()
    {
        $input = [
            ['a' => 'test-4', 'b' => 'rawr', 'c' => 5, 'd' => 'uwu'],
            ['a' => 'test', 'b' => 'rawr xD', 'c' => 1, 'd' => 'OwO'],
            ['a' => 'test-2', 'b' => 'blah', 'c' => 3, 'd' => '-_-'],
            ['a' => 'test-3', 'b' => 'wanna play a game?', 'c' => 7, 'd' => '=)'],
        ];
        $sorted = Utility::orderBy($input, 'c', [1,3,5,7]);
        $this->assertSame('test', $sorted[0]['a']);
        $this->assertSame('test-2', $sorted[1]['a']);
        $this->assertSame('test-4', $sorted[2]['a']);
        $this->assertSame('test-3', $sorted[3]['a']);

        $sorted = Utility::orderBy($input, 'c', [7,1,5,3]);
        $this->assertSame('test-3', $sorted[0]['a']);
        $this->assertSame('test', $sorted[1]['a']);
        $this->assertSame('test-4', $sorted[2]['a']);
        $this->assertSame('test-2', $sorted[3]['a']);
    }

    public function testGetGetVars()
    {
        $request = new Request(
            'GET',
            Uri::createFromString('/?a=b&c=12345&d[]=1&d[]=2&d[]=4'),
            new Headers([]),
            [],
            [],
            Utility::stringToStream('')
        );

        $get = Utility::getGetVars($request);
        $this->assertSame('b', $get['a']);
        $this->assertSame('12345', $get['c']);
        $this->assertSame('1', $get['d'][0]);
        $this->assertSame('2', $get['d'][1]);
        $this->assertSame('4', $get['d'][2]);
    }
}
