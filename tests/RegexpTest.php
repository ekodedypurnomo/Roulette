<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Regexp;

class RegexpTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertTrue(method_exists(Regexp::class, '__construct'));

        $obj = new Regexp();
        $this->assertNotNull($obj, 'default');
    }

    public function testCreate(): void
    {
        $this->assertTrue(method_exists(Regexp::class, 'create'));

        $obj = new Regexp();
        $this->assertEmpty($obj->getString(), 'default empty');
    }

    public function testSetGetString(): void
    {
        $this->assertTrue(method_exists(Regexp::class, 'setString'));
        $this->assertTrue(method_exists(Regexp::class, 'getString'));

        $obj = new Regexp();
        $this->assertEmpty($obj->getString(), 'default');

        $obj = new Regexp('/abc/');
        $this->assertEquals('/abc/', $obj->getString(), 'valued');
    }

    public function testSetGetReplaceString(): void
    {
        $this->assertTrue(method_exists(Regexp::class, 'setReplaceString'));
        $this->assertTrue(method_exists(Regexp::class, 'getReplaceString'));

        $obj = new Regexp();
        $this->assertEmpty($obj->getReplaceString(), 'default');

        $obj = new Regexp('/abc/', 'ABC');
        $this->assertEquals('ABC', $obj->getReplaceString(), 'valued');
    }

    public function testTest(): void
    {
        $this->assertTrue(method_exists(Regexp::class, 'test'));

        $obj = new Regexp('/abc/', 'ABC');
        $this->assertSame(0, $obj->test(), 'default');
        $this->assertSame(1, $obj->test('abc'), 'true');
        $this->assertSame(1, $obj->test('abcd'), 'true2');
        $this->assertSame(0, $obj->test('ab'), 'false');
        $this->assertSame(0, $obj->test(1), 'false2');
    }

    public function testReplace(): void
    {
        $this->assertTrue(method_exists(Regexp::class, 'replace'));

        $obj = new Regexp('/abc/', 'ABC');
        $this->assertEquals('', $obj->replace(), 'default');
        $this->assertEquals('ABC', $obj->replace('abc'), 'true');
        $this->assertEquals('ABCd', $obj->replace('abcd'), 'true2');
        $this->assertEquals('ab', $obj->replace('ab'), 'false');
        $this->assertEquals(1, $obj->replace(1), 'false2');
    }
}
