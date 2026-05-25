<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Above;
use Roulette\Validator\Below;
use Roulette\Validator\Minvalue;
use Roulette\Validator\Maxvalue;

class RangeValidatorTest extends TestCase
{
    // --- Above (strict greater than) ---

    public function testAboveValid(): void
    {
        $v = new Above(10);
        $this->assertTrue($v->test(11));
        $this->assertTrue($v->test(100));
        $this->assertTrue($v->test(10.1));
    }

    public function testAboveInvalid(): void
    {
        $v = new Above(10);
        $this->assertFalse($v->test(10), 'equal is not above');
        $this->assertFalse($v->test(9));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test('abc'));
    }

    public function testAboveMessage(): void
    {
        $v = new Above(10);
        $this->assertStringContainsString('10', $v->getMessage(5));
    }

    // --- Below (strict less than) ---

    public function testBelowValid(): void
    {
        $v = new Below(10);
        $this->assertTrue($v->test(9));
        $this->assertTrue($v->test(0));
        $this->assertTrue($v->test(-100));
        $this->assertTrue($v->test(9.9));
    }

    public function testBelowInvalid(): void
    {
        $v = new Below(10);
        $this->assertFalse($v->test(10), 'equal is not below');
        $this->assertFalse($v->test(11));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test('abc'));
    }

    public function testBelowMessage(): void
    {
        $v = new Below(10);
        $this->assertStringContainsString('10', $v->getMessage(15));
    }

    // --- Minvalue (>=) ---

    public function testMinvalueValid(): void
    {
        $v = new Minvalue(5);
        $this->assertTrue($v->test(5), 'exactly min');
        $this->assertTrue($v->test(6));
        $this->assertTrue($v->test(100));
    }

    public function testMinvalueInvalid(): void
    {
        $v = new Minvalue(5);
        $this->assertFalse($v->test(4));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test('abc'));
    }

    public function testMinvalueMessage(): void
    {
        $v = new Minvalue(5);
        $this->assertStringContainsString('5', $v->getMessage(2));
    }

    // --- Maxvalue (<=) ---

    public function testMaxvalueValid(): void
    {
        $v = new Maxvalue(10);
        $this->assertTrue($v->test(10), 'exactly max');
        $this->assertTrue($v->test(0));
        $this->assertTrue($v->test(-5));
    }

    public function testMaxvalueInvalid(): void
    {
        $v = new Maxvalue(10);
        $this->assertFalse($v->test(11));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test('abc'));
    }

    public function testMaxvalueMessage(): void
    {
        $v = new Maxvalue(10);
        $this->assertStringContainsString('10', $v->getMessage(15));
    }
}
