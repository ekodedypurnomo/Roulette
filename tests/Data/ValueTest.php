<?php

declare(strict_types=1);

namespace Roulette\Tests\Data;

use Roulette\Data\Value;
use Roulette\Model;
use Roulette\Model\Field\Field;
use Roulette\Tests\TestCase;

class ValueTest extends TestCase
{
    public function testProperty(): void
    {
        $this->assertTrue(property_exists(Value::class, 'field'));
        $this->assertTrue(property_exists(Value::class, 'record'));
        $this->assertTrue(property_exists(Value::class, 'original'));
        $this->assertTrue(property_exists(Value::class, 'raw'));
        $this->assertTrue(property_exists(Value::class, 'display'));
        $this->assertTrue(property_exists(Value::class, 'error'));
    }

    public function testGetField(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getField'));

        $field = new Field();
        $value = new Value(new Model(), $field);
        $this->assertSame($field, $value->getField(), 'after set');
    }

    public function testGetRecord(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getRecord'));

        $record = new Model();
        $value = new Value($record, new Field());
        $this->assertSame($record, $value->getRecord(), 'after set');
    }

    public function testSetGetOriginal(): void
    {
        $this->assertTrue(method_exists(Value::class, 'setOriginal'));
        $this->assertTrue(method_exists(Value::class, 'getOriginal'));

        $var = (string) rand();
        $field = new Field([
            'name'   => 'field',
            'reader' => fn($value) => 'g:' . $value,
            'writer' => fn($value) => 'w:' . $value,
        ]);

        $fieldValue = new Value(new Model(), $field);
        $this->assertFalse($fieldValue->isModified(), 'before, modified');
        $this->assertEquals('g:', $fieldValue->getValue(false), 'before, raw');
        $this->assertEquals('g:', $fieldValue->getValue(), 'before, display');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal();
        $this->assertFalse($fieldValue->isModified(), 'after, default, modified');
        $this->assertEquals('g:', $fieldValue->getOriginal(), 'after, default, original');
        $this->assertEquals('g:', $fieldValue->getValue(false), 'after, default, raw');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal($var);
        $this->assertTrue($fieldValue->isModified(), 'after, default valued, modified');
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, default valued, original');
        $this->assertEquals('g:', $fieldValue->getValue(false), 'after, default valued, raw');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal($var, true, true);
        $this->assertFalse($fieldValue->isModified(), 'after, norevert noread nodefault, modified');
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, norevert noread nodefault, original');
        $this->assertEquals('g:' . $var, $fieldValue->getValue(false), 'after, norevert noread nodefault, raw');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal($var, true, true);
        $this->assertFalse($fieldValue->isModified(), 'after, norevert read nodefault, modified');
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, norevert read nodefault, original');
        $this->assertEquals('g:' . $var, $fieldValue->getValue(false), 'after, norevert read nodefault, raw');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal($var, true, true);
        $this->assertFalse($fieldValue->isModified(), 'after, norevert noread default, modified');
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, norevert noread default, original');
        $this->assertEquals('g:' . $var, $fieldValue->getValue(false), 'after, norevert noread default, raw');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal($var, true, true);
        $this->assertFalse($fieldValue->isModified(), 'after, revert noread nodefault, modified');
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, revert noread nodefault, original');
        $this->assertEquals('g:' . $var, $fieldValue->getValue(false), 'after, revert noread nodefault, raw');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setOriginal($var, true, true);
        $this->assertFalse($fieldValue->isModified(), 'after, revert read default, modified');
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, revert read default, original');
        $this->assertEquals('g:' . $var, $fieldValue->getValue(false), 'after, revert read default, raw');

        $field->setDefault(0);
        $fieldValue->setOriginal(null, true);
        $this->assertEquals('g:' . $field->getDefault(), $fieldValue->getOriginal(), 'after, default value, null, original');
        $this->assertEquals('g:' . $field->getDefault(), $fieldValue->getValue(false), 'after, default value, null, raw');

        $fieldValue->setOriginal($var, true);
        $this->assertEquals('g:' . $var, $fieldValue->getOriginal(), 'after, default value, notnull, original');
        $this->assertEquals('g:' . $var, $fieldValue->getValue(false), 'after, default value, notnull, raw');
    }

    public function testSetGetValue(): void
    {
        $this->assertTrue(method_exists(Value::class, 'setValue'));
        $this->assertTrue(method_exists(Value::class, 'getValue'));

        $var = (string) rand();
        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
        ]);

        $fieldValue = new Value(new Model(), $field);
        $this->assertFalse($fieldValue->isModified(), 'before, modified');
        $this->assertNull($fieldValue->getValue(false), 'before, raw');
        $this->assertEquals('r:', $fieldValue->getValue(), 'before, display');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setValue();
        $this->assertTrue($fieldValue->isModified(), 'after, default, modified');
        $this->assertEquals('c:', $fieldValue->getValue(false), 'after, default, raw');
        $this->assertEquals('r:c:', $fieldValue->getValue(), 'after, default, display');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setValue($var);
        $this->assertTrue($fieldValue->isModified(), 'after, default valued, modified');
        $this->assertEquals('c:' . $var, $fieldValue->getValue(false), 'after, default valued, raw');
        $this->assertEquals('r:c:' . $var, $fieldValue->getValue(), 'after, default valued, display');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setValue($var, false, false);
        $this->assertTrue($fieldValue->isModified(), 'after, nocommit noconvert, modified');
        $this->assertEquals($var, $fieldValue->getValue(false), 'after, nocommit noconvert, raw');
        $this->assertEquals('r:' . $var, $fieldValue->getValue(), 'after, nocommit noconvert, display');

        $fieldValue->setValue($var, false, true);
        $this->assertTrue($fieldValue->isModified(), 'after, nocommit convert, modified');
        $this->assertEquals('c:' . $var, $fieldValue->getValue(false), 'after, nocommit convert, raw');
        $this->assertEquals('r:c:' . $var, $fieldValue->getValue(), 'after, nocommit convert, display');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setValue($var, true, false);
        $this->assertFalse($fieldValue->isModified(), 'after, commit noconvert, modified');
        $this->assertEquals($var, $fieldValue->getOriginal(), 'after, commit noconvert, original');
        $this->assertEquals($var, $fieldValue->getValue(false), 'after, commit noconvert, raw');
        $this->assertEquals('r:' . $var, $fieldValue->getValue(), 'after, commit noconvert, display');

        $fieldValue = new Value(new Model(), $field);
        $fieldValue->setValue($var, true, true);
        $this->assertFalse($fieldValue->isModified(), 'after, commit convert, modified');
        $this->assertEquals('c:' . $var, $fieldValue->getOriginal(), 'after, commit convert, original');
        $this->assertEquals('c:' . $var, $fieldValue->getValue(false), 'after, commit convert, raw');
        $this->assertEquals('r:c:' . $var, $fieldValue->getValue(), 'after, commit convert, display');
    }

    public function testIsModified(): void
    {
        $this->assertTrue(method_exists(Value::class, 'isModified'));

        $var = (string) rand();
        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);

        $this->assertFalse($fieldValue->isModified(), 'before');

        $fieldValue->setValue($var, false);
        $this->assertTrue($fieldValue->isModified(), 'after setValue');

        $fieldValue->commit();
        $this->assertFalse($fieldValue->isModified(), 'after commit');

        $fieldValue->setOriginal($var, false);
        $this->assertTrue($fieldValue->isModified(), 'after setOriginal');

        $fieldValue->revert();
        $this->assertFalse($fieldValue->isModified(), 'after revert');
    }

    public function testValidate(): void
    {
        $this->assertTrue(method_exists(Value::class, 'validate'));
        $this->assertTrue(method_exists(Value::class, 'isValid'));
        $this->assertTrue(method_exists(Value::class, 'getError'));

        $var = (string) rand(0, 99);
        $field = new Field([
            'name'       => 'field',
            'validation' => ['nullable' => false],
        ]);
        $fieldValue = new Value(new Model(), $field);

        $this->assertTrue($fieldValue->isValid(), 'before, isValid');
        $this->assertEmpty($fieldValue->getError(), 'before, message');

        $fieldValue->validate();
        $this->assertFalse($fieldValue->isValid(), 'after, isValid');
        $this->assertNotEmpty($fieldValue->getError(), 'after, message');

        $fieldValue->setValue($var)->validate();
        $this->assertTrue($fieldValue->isValid(), 'valid, isValid');
        $this->assertEmpty($fieldValue->getError(), 'valid, message');

        $var = (string) rand(10, 99);
        $field = new Field([
            'name'       => 'field',
            'validation' => ['maxvalue' => 9, 'above' => 99],
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertEmpty($fieldValue->getError(), 'errorMessages, before');
        $fieldValue->setValue($var)->validate();
        $this->assertGreaterThan(0, count($fieldValue->getError()), 'errorMessages, count');
    }

    public function testCommit(): void
    {
        $this->assertTrue(method_exists(Value::class, 'commit'));

        $var = (string) rand();
        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);

        $this->assertFalse($fieldValue->isModified(), 'before set, modified');
        $this->assertEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'before set, value');

        $fieldValue->setValue($var);
        $this->assertTrue($fieldValue->isModified(), 'after set, modified');
        $this->assertNotEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'after set, value');

        $fieldValue->commit();
        $this->assertFalse($fieldValue->isModified(), 'after commit, modified');
        $this->assertEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'after commit, value');
    }

    public function testRevert(): void
    {
        $this->assertTrue(method_exists(Value::class, 'revert'));

        $var = (string) rand();
        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);

        $this->assertFalse($fieldValue->isModified(), 'before set, modified');
        $this->assertEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'before set, value');

        $fieldValue->setOriginal($var);
        $this->assertTrue($fieldValue->isModified(), 'after set, modified');
        $this->assertNotEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'after set, value');

        $fieldValue->revert();
        $this->assertFalse($fieldValue->isModified(), 'after revert, modified');
        $this->assertEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'after revert, value');
    }

    public function testRender(): void
    {
        $this->assertTrue(method_exists(Value::class, 'render'));

        $var = (string) rand();
        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);

        $this->assertNotEquals($fieldValue->getDisplay(), $fieldValue->getRaw(), 'before set, validity');
        $this->assertEquals('r:g:', $fieldValue->getDisplay(), 'before set, value');

        $fieldValue->setValue($var);
        $this->assertNotEquals($fieldValue->getDisplay(), $fieldValue->getRaw(), 'after set, validity');
        $this->assertEquals('r:c:' . $var, $fieldValue->getDisplay(), 'after set, value');
    }

    public function testGetWriteValue(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getWriteValue'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotNull($fieldValue->getWriteValue(), 'get');
    }

    public function testSetOriginal(): void
    {
        $this->assertTrue(method_exists(Value::class, 'setOriginal'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotNull($fieldValue->setOriginal(), 'default');
        $this->assertNotNull($fieldValue->setOriginal('g:R'), 'set value');
    }

    public function testGetOriginal(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getOriginal'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotFalse($fieldValue->getOriginal(), 'default');
    }

    public function testSetRaw(): void
    {
        $this->assertTrue(method_exists(Value::class, 'setRaw'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotNull($fieldValue->setRaw('T:'), 'default');
    }

    public function testSetValue(): void
    {
        $this->assertTrue(method_exists(Value::class, 'setValue'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotNull($fieldValue->setValue('T:'), 'default');
    }

    public function testGetRaw(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getRaw'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotFalse($fieldValue->getRaw(), 'default');
    }

    public function testGetDisplay(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getDisplay'));

        $field = new Field([
            'name'    => 'field',
            'display' => 'Hobby',
        ]);
        $this->assertNotNull($field->getDisplay(), 'default');
    }

    public function testGetValue(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getValue'));

        $field = new Field([
            'name'      => 'field',
            'display'   => 'Hobby',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $this->assertNotFalse($fieldValue->getValue(), 'default');
    }

    public function testGetError(): void
    {
        $this->assertTrue(method_exists(Value::class, 'getError'));

        $field = new Field([
            'name'       => 'field',
            'display'    => 'Hobby',
            'validation' => ['nullable' => false],
            'error'      => ['Error Message'],
        ]);
        $fieldValue = new Value(new Model(), $field);
        $fieldValue->validate();
        $this->assertNotEmpty($fieldValue->getError(), 'message');
    }

    public function testIsValid(): void
    {
        $this->assertTrue(method_exists(Value::class, 'isValid'));

        $field = new Field([
            'name'       => 'field',
            'display'    => 'Hobby',
            'validation' => ['nullable' => false],
            'error'      => ['Error Message'],
        ]);
        $fieldValue = new Value(new Model(), $field);
        $fieldValue->validate();
        $this->assertFalse($fieldValue->isValid(), 'valid');
    }

    public function testRollback(): void
    {
        $this->assertTrue(method_exists(Value::class, 'rollback'));

        $field = new Field([
            'name'      => 'field',
            'converter' => fn($value) => 'c:' . $value,
            'renderer'  => fn($value) => 'r:' . $value,
            'reader'    => fn($value) => 'g:' . $value,
            'writer'    => fn($value) => 'w:' . $value,
        ]);
        $fieldValue = new Value(new Model(), $field);
        $fieldValue->rollback();
        $this->assertFalse($fieldValue->isModified(), 'after rollback, modified');
        $this->assertEquals($fieldValue->getOriginal(), $fieldValue->getRaw(), 'after rollback, value');
    }
}
