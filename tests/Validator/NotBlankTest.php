<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\NotBlank;

class NotBlankTest extends TestCase
{
    public function testValidNonBlank(): void
    {
        $v = new NotBlank(null);
        $this->assertTrue($v->test('hello'));
        $this->assertTrue($v->test('0'));
        $this->assertTrue($v->test(' hello '));
    }

    public function testInvalidNull(): void
    {
        $v = new NotBlank(null);
        $this->assertFalse($v->test(null));
    }

    public function testInvalidEmptyString(): void
    {
        $v = new NotBlank(null);
        $this->assertFalse($v->test(''));
    }

    public function testInvalidWhitespaceOnly(): void
    {
        $v = new NotBlank(null);
        $this->assertFalse($v->test('   '));
        $this->assertFalse($v->test("\t\n"));
    }

    public function testMessage(): void
    {
        $v = new NotBlank(null);
        $msg = $v->getMessage('');
        $this->assertIsString($msg);
        $this->assertNotEmpty($msg);
    }
}
