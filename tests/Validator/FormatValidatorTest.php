<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Custom;
use Roulette\Validator\Format;
use Roulette\Validator\Url;
use Roulette\Validator\Uuid;

class FormatValidatorTest extends TestCase
{
    // --- Format (regex) ---

    public function testFormatValid(): void
    {
        $v = new Format('/^\d{4}$/');
        $this->assertTrue($v->test('1234'));
        $this->assertTrue($v->test('0000'));
    }

    public function testFormatInvalid(): void
    {
        $v = new Format('/^\d{4}$/');
        $this->assertFalse($v->test('123'), 'too short');
        $this->assertFalse($v->test('12345'), 'too long');
        $this->assertFalse($v->test('abcd'), 'non-digit');
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test(1234), 'integer fails (not string)');
    }

    public function testFormatMessage(): void
    {
        $v = new Format('/foo/');
        $this->assertNotEmpty($v->getMessage('bar'));
    }

    // --- Custom ---

    public function testCustomValid(): void
    {
        $v = new Custom(fn($val) => strlen($val) > 3);
        $this->assertTrue($v->test('hello'));
        $this->assertTrue($v->test('abcd'));
    }

    public function testCustomInvalid(): void
    {
        $v = new Custom(fn($val) => strlen($val) > 3);
        $this->assertFalse($v->test('abc'));
        $this->assertFalse($v->test(''));
    }

    public function testCustomWithNonCallableRule(): void
    {
        $v = new Custom(null);
        $this->assertFalse($v->test('anything'), 'null rule always fails');
    }

    public function testCustomMessage(): void
    {
        $v = new Custom(fn() => false);
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- Url ---

    public function testUrlValid(): void
    {
        $v = new Url();
        $this->assertTrue($v->test('https://example.com'));
        $this->assertTrue($v->test('http://foo.bar/path?q=1'));
        $this->assertTrue($v->test('ftp://files.example.com'));
    }

    public function testUrlInvalid(): void
    {
        $v = new Url();
        $this->assertFalse($v->test('not-a-url'));
        $this->assertFalse($v->test('example.com'), 'missing scheme');
        $this->assertFalse($v->test(null));
    }

    public function testUrlMessage(): void
    {
        $v = new Url();
        $this->assertNotEmpty($v->getMessage('bad'));
    }

    // --- Uuid ---

    public function testUuidValid(): void
    {
        $v = new Uuid();
        $this->assertTrue($v->test('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertTrue($v->test('00000000-0000-0000-0000-000000000000'));
    }

    public function testUuidInvalid(): void
    {
        $v = new Uuid();
        $this->assertFalse($v->test('not-a-uuid'));
        $this->assertFalse($v->test('550e8400-e29b-41d4-a716'));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test(''));
    }

    public function testUuidMessage(): void
    {
        $v = new Uuid();
        $this->assertNotEmpty($v->getMessage('bad'));
    }
}
