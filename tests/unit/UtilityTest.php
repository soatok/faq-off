<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Tests;

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
}
