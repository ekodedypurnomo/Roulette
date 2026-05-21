<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Validation;
use Roulette\Validator\Above;
use Roulette\Validator\Below;
use Roulette\Validator\Custom;
use Roulette\Validator\Exclusion;
use Roulette\Validator\Format;
use Roulette\Validator\Inclusion;
use Roulette\Validator\Maxlength;
use Roulette\Validator\Maxvalue;
use Roulette\Validator\Minlength;
use Roulette\Validator\Minvalue;
use Roulette\Validator\Nullable;

class ValidationTest extends TestCase
{
    public function testGetMessageTemplates(): void
    {
        $this->assertTrue(method_exists(Validation::class, 'getMessageTemplates'));

        $obj = new Validation(['validators' => []]);
        $this->assertIsArray($obj->getMessageTemplates(), 'array');
        $this->assertNull($obj->getMessageTemplates('nullable'), 'string');
        $this->assertNull($obj->getMessageTemplates('undefined'), 'undefined');

        $obj = new Validation([
            'validators'       => [],
            'messageTemplates' => ['nullable' => 'dont null please'],
        ]);
        $this->assertEquals('dont null please', $obj->getMessageTemplates('nullable'), 'override');
    }

    public function testAddValidator(): void
    {
        $this->assertTrue(method_exists(Validation::class, 'addValidator'));
        $this->assertTrue(method_exists(Validation::class, 'getValidators'));

        $obj = new Validation(['validators' => []]);
        $this->assertCount(0, $obj->getValidators(), 'before add');

        $obj->addValidator('nullable', true);
        $this->assertGreaterThan(0, count($obj->getValidators()), 'after add');

        $obj->addValidator('minvalue', 5);
        $obj->addValidator('custom', function () {});
        $obj->addValidator(function () { return; }, 'should false');
        $this->assertCount(4, $obj->getValidators(), 'after add2');
    }

    public function testReset(): void
    {
        $this->assertTrue(method_exists(Validation::class, 'reset'));
        $this->assertTrue(method_exists(Validation::class, 'getValidators'));

        $obj = new Validation(['validators' => []]);
        $this->assertCount(0, $obj->getValidators(), 'before insert');

        $obj->addValidator('nullable', true);
        $this->assertGreaterThan(0, count($obj->getValidators()), 'after insert');

        $obj->reset();
        $this->assertCount(0, $obj->getValidators(), 'after reset, validators');
        $this->assertCount(0, $obj->getMessageTemplates(), 'after reset, messages');
    }

    public function testValidateAbove(): void
    {
        $validator = new Above(1, '{value}>{rule}');
        $this->assertFalse($validator->test(-1), 'below');
        $this->assertFalse($validator->test(1), 'equal');
        $this->assertTrue($validator->test(2), 'above');
        $this->assertFalse($validator->test('a'), 'invalid');
        $this->assertEquals('>1', $validator->getMessage(), 'message');
        $this->assertEquals('1>1', $validator->getMessage(1), 'message with params');
    }

    public function testValidateBelow(): void
    {
        $validator = new Below(1, '{value}<{rule}');
        $this->assertTrue($validator->test(-1), 'below');
        $this->assertFalse($validator->test(1), 'equal');
        $this->assertFalse($validator->test(2), 'above');
        $this->assertFalse($validator->test('a'), 'invalid');
        $this->assertEquals('<1', $validator->getMessage(), 'message');
        $this->assertEquals('1<1', $validator->getMessage(1), 'message with params');
    }

    public function testValidateCustom(): void
    {
        $validFn = fn($value = null) => !is_null($value);
        $validator = new Custom($validFn, '{value} != validation formula');
        $this->assertTrue($validator->test('dummy'), 'valid');
        $this->assertFalse($validator->test(null), 'invalid');
        $this->assertEquals('1 != validation formula', $validator->getMessage(1), 'message with value');
        $this->assertEquals(' != validation formula', $validator->getMessage(), 'message without value');
    }

    public function testValidateExclusion(): void
    {
        $validator = new Exclusion(['a', 'b'], '{value}:ex:{rule}');
        $this->assertFalse($validator->test('a'), 'in');
        $this->assertTrue($validator->test('e'), 'ex');
        $this->assertTrue($validator->test(null), 'invalid');
        $this->assertEquals('1:ex:a,b', $validator->getMessage(1), 'message with value');
        $this->assertEquals(':ex:a,b', $validator->getMessage(), 'message without value');
    }

    public function testValidateFormat(): void
    {
        $validator = new Format('/dummy/', '{value}={rule}');
        $this->assertTrue($validator->test('dummy'), 'informat');
        $this->assertFalse($validator->test('dummies'), 'exformat');
        $this->assertFalse($validator->test(null), 'invalid');
        $this->assertEquals('=/dummy/', $validator->getMessage(), 'message');
        $this->assertEquals('1=/dummy/', $validator->getMessage(1), 'message with value');
    }

    public function testValidateInclusion(): void
    {
        $validator = new Inclusion(['a', 'b'], '{value}:in:{rule}');
        $this->assertTrue($validator->test('a'), 'in');
        $this->assertFalse($validator->test('e'), 'ex');
        $this->assertFalse($validator->test(null), 'invalid');
        $this->assertEquals('1:in:a,b', $validator->getMessage(1), 'message with value');
        $this->assertEquals(':in:a,b', $validator->getMessage(), 'message without value');
    }

    public function testValidateMaxlength(): void
    {
        $validator = new Maxlength(2, '{value}+{rule}');
        $this->assertTrue($validator->test('a'), 'below');
        $this->assertTrue($validator->test('ab'), 'equal');
        $this->assertFalse($validator->test('abc'), 'over');
        $this->assertFalse($validator->test(999), 'invalid');
        $this->assertEquals('+2', $validator->getMessage(), 'message');
        $this->assertEquals('1+2', $validator->getMessage(1), 'message with params');
    }

    public function testValidateMaxvalue(): void
    {
        $validator = new Maxvalue(1, '{value}<={rule}');
        $this->assertTrue($validator->test(-1), 'below');
        $this->assertTrue($validator->test(1), 'max');
        $this->assertFalse($validator->test(2), 'above');
        $this->assertFalse($validator->test('a'), 'invalid');
        $this->assertEquals('<=1', $validator->getMessage(), 'message');
        $this->assertEquals('1<=1', $validator->getMessage(1), 'message with params');
    }

    public function testValidateMinlength(): void
    {
        $validator = new Minlength(2, '{value}-{rule}');
        $this->assertTrue($validator->test('ab'), 'equal');
        $this->assertTrue($validator->test('abc'), 'above');
        $this->assertFalse($validator->test('a'), 'below');
        $this->assertFalse($validator->test(9), 'invalid');
        $this->assertEquals('-2', $validator->getMessage(), 'message');
        $this->assertEquals('1-2', $validator->getMessage(1), 'message with params');
    }

    public function testValidateMinvalue(): void
    {
        $validator = new Minvalue(1, '{value}>={rule}');
        $this->assertFalse($validator->test(-1), 'below');
        $this->assertTrue($validator->test(1), 'min');
        $this->assertTrue($validator->test(2), 'above');
        $this->assertFalse($validator->test('a'), 'invalid');
        $this->assertEquals('>=1', $validator->getMessage(), 'message');
        $this->assertEquals('1>=1', $validator->getMessage(1), 'message with params');
    }

    public function testValidateNullable(): void
    {
        $validator = new Nullable(false, 'value:{value}');
        $this->assertFalse($validator->test(), 'empty');
        $this->assertFalse($validator->test(null), 'null');
        $this->assertTrue($validator->test(1), 'notnull');
        $this->assertEquals('value:', $validator->getMessage(), 'message');
        $this->assertEquals('value:1', $validator->getMessage(1), 'message with params');

        $validator = new Nullable(true);
        $this->assertTrue($validator->test(), 'nullable, empty');
        $this->assertTrue($validator->test(null), 'nullable, null');
        $this->assertTrue($validator->test(1), 'nullable, notnull');
    }
}
