<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Email;
use Roulette\Validator\Minlength;
use Roulette\Validator\Maxlength;

class ValidatorTest extends TestCase
{
    public function testEmailValid(): void
    {
        $v = new Email();
        $this->assertTrue($v->test('user@example.com'), 'valid email');
        $this->assertTrue($v->test('a@b.cc'), 'short domain valid');
    }

    public function testEmailInvalid(): void
    {
        $v = new Email();
        $this->assertFalse($v->test('not-an-email'), 'missing @ and domain');
        $this->assertFalse($v->test('noat.com'), 'missing @');
        $this->assertFalse($v->test(null), 'null');
    }

    public function testEmailMessage(): void
    {
        $v = new Email();
        $msg = $v->getMessage('bad');
        $this->assertIsString($msg);
        $this->assertNotEmpty($msg);
    }

    public function testMinlengthValid(): void
    {
        $v = new Minlength(3);
        $this->assertTrue($v->test('abc'), 'exactly min');
        $this->assertTrue($v->test('abcdef'), 'above min');
    }

    public function testMinlengthInvalid(): void
    {
        $v = new Minlength(5);
        $this->assertFalse($v->test('abc'), 'below min');
        $this->assertFalse($v->test(null), 'null fails');
    }

    public function testMinlengthMessage(): void
    {
        $v = new Minlength(5);
        $msg = $v->getMessage('ab');
        $this->assertStringContainsString('5', $msg, 'rule value in message');
    }

    public function testMaxlengthValid(): void
    {
        $v = new Maxlength(5);
        $this->assertTrue($v->test('abc'), 'below max');
        $this->assertTrue($v->test('abcde'), 'exactly max');
    }

    public function testMaxlengthInvalid(): void
    {
        $v = new Maxlength(3);
        $this->assertFalse($v->test('abcd'), 'above max');
    }

    public function testAddValidator(): void
    {
        $v = new Email();
        $this->assertInstanceOf(Email::class, $v);
        $this->assertTrue(method_exists($v, 'test'));
        $this->assertTrue(method_exists($v, 'getMessage'));
    }
}
