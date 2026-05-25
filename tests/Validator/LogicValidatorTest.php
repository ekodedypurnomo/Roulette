<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Exclusion;
use Roulette\Validator\Inclusion;
use Roulette\Validator\IsFalse;
use Roulette\Validator\IsTrue;
use Roulette\Validator\Nullable;

class LogicValidatorTest extends TestCase
{
    // --- IsTrue ---

    public function testIsTrueValid(): void
    {
        $v = new IsTrue(null);
        $this->assertTrue($v->test(true));
    }

    public function testIsTrueInvalid(): void
    {
        $v = new IsTrue(null);
        $this->assertFalse($v->test(false));
        $this->assertFalse($v->test(1), 'int 1 is not strict true');
        $this->assertFalse($v->test('true'));
        $this->assertFalse($v->test(null));
    }

    public function testIsTrueMessage(): void
    {
        $v = new IsTrue(null);
        $this->assertNotEmpty($v->getMessage(false));
    }

    // --- IsFalse ---

    public function testIsFalseValid(): void
    {
        $v = new IsFalse(null);
        $this->assertTrue($v->test(false));
    }

    public function testIsFalseInvalid(): void
    {
        $v = new IsFalse(null);
        $this->assertFalse($v->test(true));
        $this->assertFalse($v->test(0), 'int 0 is not strict false');
        $this->assertFalse($v->test('false'));
        $this->assertFalse($v->test(null));
    }

    public function testIsFalseMessage(): void
    {
        $v = new IsFalse(null);
        $this->assertNotEmpty($v->getMessage(true));
    }

    // --- Nullable (rule=true means nullable/allow null) ---

    public function testNullableAllowsNull(): void
    {
        $v = new Nullable(true);
        $this->assertTrue($v->test(null), 'null allowed when nullable=true');
        $this->assertTrue($v->test('value'), 'non-null also passes');
    }

    public function testNullableRejectsNull(): void
    {
        $v = new Nullable(false);
        $this->assertFalse($v->test(null), 'null rejected when nullable=false');
    }

    public function testNullableNonNullPassesWhenStrict(): void
    {
        $v = new Nullable(false);
        $this->assertTrue($v->test('value'));
        $this->assertTrue($v->test(0));
        $this->assertTrue($v->test(false));
    }

    public function testNullableDefaultIsAllowNull(): void
    {
        $v = new Nullable(true);
        $this->assertTrue($v->test(null), 'default rule is true (nullable)');
    }

    public function testNullableMessage(): void
    {
        $v = new Nullable(false);
        $this->assertNotEmpty($v->getMessage(null));
    }

    // --- Inclusion ---

    public function testInclusionValid(): void
    {
        $v = new Inclusion(['a', 'b', 'c']);
        $this->assertTrue($v->test('a'));
        $this->assertTrue($v->test('b'));
        $this->assertTrue($v->test('c'));
    }

    public function testInclusionInvalid(): void
    {
        $v = new Inclusion(['a', 'b', 'c']);
        $this->assertFalse($v->test('d'));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test(''));
    }

    public function testInclusionEmptyList(): void
    {
        $v = new Inclusion([]);
        $this->assertFalse($v->test('anything'), 'empty list rejects all');
    }

    public function testInclusionNonArrayRule(): void
    {
        $v = new Inclusion(null);
        $this->assertFalse($v->test('a'), 'null rule treated as empty list');
    }

    public function testInclusionMessage(): void
    {
        $v = new Inclusion(['x', 'y']);
        $msg = $v->getMessage('z');
        $this->assertNotEmpty($msg);
    }

    // --- Exclusion ---

    public function testExclusionValid(): void
    {
        $v = new Exclusion(['a', 'b', 'c']);
        $this->assertTrue($v->test('d'));
        $this->assertTrue($v->test('z'));
        $this->assertTrue($v->test(null), 'null not in list passes');
    }

    public function testExclusionInvalid(): void
    {
        $v = new Exclusion(['a', 'b', 'c']);
        $this->assertFalse($v->test('a'));
        $this->assertFalse($v->test('b'));
    }

    public function testExclusionEmptyList(): void
    {
        $v = new Exclusion([]);
        $this->assertTrue($v->test('anything'), 'empty list allows all');
    }

    public function testExclusionMessage(): void
    {
        $v = new Exclusion(['x', 'y']);
        $msg = $v->getMessage('x');
        $this->assertNotEmpty($msg);
    }
}
