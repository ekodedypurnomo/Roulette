<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Boolean;
use Roulette\Validator\Double;
use Roulette\Validator\FloatType;
use Roulette\Validator\Integer;
use Roulette\Validator\Numeric;
use Roulette\Validator\StringType;

class TypeValidatorTest extends TestCase
{
    // --- Integer ---

    public function testIntegerValid(): void
    {
        $v = new Integer(null);
        $this->assertTrue($v->test(0));
        $this->assertTrue($v->test(42));
        $this->assertTrue($v->test(-10));
    }

    public function testIntegerInvalid(): void
    {
        $v = new Integer(null);
        $this->assertFalse($v->test(3.14), 'float is not integer');
        $this->assertFalse($v->test('42'), 'string is not integer');
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test(true));
    }

    public function testIntegerMessage(): void
    {
        $v = new Integer(null);
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- Boolean ---

    public function testBooleanValid(): void
    {
        $v = new Boolean(null);
        $this->assertTrue($v->test(true));
        $this->assertTrue($v->test(false));
    }

    public function testBooleanInvalid(): void
    {
        $v = new Boolean(null);
        $this->assertFalse($v->test(1), 'int 1 is not bool');
        $this->assertFalse($v->test(0), 'int 0 is not bool');
        $this->assertFalse($v->test('true'), 'string is not bool');
        $this->assertFalse($v->test(null));
    }

    public function testBooleanMessage(): void
    {
        $v = new Boolean(null);
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- FloatType ---

    public function testFloatValid(): void
    {
        $v = new FloatType(null);
        $this->assertTrue($v->test(3.14));
        $this->assertTrue($v->test(0.0));
        $this->assertTrue($v->test(-1.5));
    }

    public function testFloatInvalid(): void
    {
        $v = new FloatType(null);
        $this->assertFalse($v->test(42), 'integer is not float');
        $this->assertFalse($v->test('3.14'), 'string is not float');
        $this->assertFalse($v->test(null));
    }

    public function testFloatMessage(): void
    {
        $v = new FloatType(null);
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- Double ---

    public function testDoubleValid(): void
    {
        $v = new Double(null);
        $this->assertTrue($v->test(3.14));
        $this->assertTrue($v->test(0.0));
    }

    public function testDoubleInvalid(): void
    {
        $v = new Double(null);
        $this->assertFalse($v->test(42), 'integer is not double');
        $this->assertFalse($v->test('3.14'));
        $this->assertFalse($v->test(null));
    }

    public function testDoubleMessage(): void
    {
        $v = new Double(null);
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- Numeric ---

    public function testNumericValid(): void
    {
        $v = new Numeric(null);
        $this->assertTrue($v->test(42));
        $this->assertTrue($v->test(3.14));
        $this->assertTrue($v->test('42'), 'numeric string is valid');
        $this->assertTrue($v->test('3.14'), 'numeric string float is valid');
        $this->assertTrue($v->test(0));
    }

    public function testNumericInvalid(): void
    {
        $v = new Numeric(null);
        $this->assertFalse($v->test('abc'));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test(''));
    }

    public function testNumericMessage(): void
    {
        $v = new Numeric(null);
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- StringType ---

    public function testStringValid(): void
    {
        $v = new StringType(null);
        $this->assertTrue($v->test('hello'));
        $this->assertTrue($v->test(''));
        $this->assertTrue($v->test('0'));
    }

    public function testStringInvalid(): void
    {
        $v = new StringType(null);
        $this->assertFalse($v->test(42), 'int is not string');
        $this->assertFalse($v->test(true), 'bool is not string');
        $this->assertFalse($v->test(null));
    }

    public function testStringMessage(): void
    {
        $v = new StringType(null);
        $this->assertNotEmpty($v->getMessage('bad'));
    }
}
