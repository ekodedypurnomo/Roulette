<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Unique;

class UniqueTest extends TestCase
{
    public function testPassesWhenCallableReturnsTrue(): void
    {
        $v = new Unique(fn($value) => true);
        $this->assertTrue($v->test('anything'));
    }

    public function testFailsWhenCallableReturnsFalse(): void
    {
        $v = new Unique(fn($value) => false);
        $this->assertFalse($v->test('duplicate@example.com'));
    }

    public function testCallableReceivesValue(): void
    {
        $received = null;
        $v = new Unique(function($value) use (&$received) {
            $received = $value;
            return true;
        });
        $v->test('test@example.com');
        $this->assertSame('test@example.com', $received);
    }

    public function testFailsWhenRuleIsNotCallable(): void
    {
        $v = new Unique(null);
        $this->assertFalse($v->test('value'));
    }

    public function testMessage(): void
    {
        $v = new Unique(fn($value) => false);
        $msg = $v->getMessage('dup');
        $this->assertIsString($msg);
        $this->assertNotEmpty($msg);
    }
}
