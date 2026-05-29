<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Field;

use Roulette\Data\Permission;
use Roulette\Model\Field\Field;
use Roulette\Model\Field\Validation as FieldValidation;
use Roulette\Tests\TestCase;

class FieldTest extends TestCase
{
    public function testProperty(): void
    {
        $config = [
            'name'       => 'fieldName',
            'private'    => true,
            'default'    => true,
            'reader'     => true,
            'writer'     => true,
            'converter'  => true,
            'renderer'   => true,
            'insert'     => true,
            'update'     => true,
            'delete'     => true,
            'select'     => true,
            'readOnly'   => true,
            'validation' => true,
        ];
        $field = new Field($config);

        $this->assertTrue(property_exists(Field::class, 'name'));
        $this->assertTrue(property_exists(Field::class, 'source'));
        $this->assertTrue(property_exists(Field::class, 'display'));
        $this->assertTrue(property_exists(Field::class, 'private'));
        $this->assertTrue(property_exists(Field::class, 'default'));
        $this->assertTrue(property_exists(Field::class, 'reader'));
        $this->assertTrue(property_exists(Field::class, 'writer'));
        $this->assertTrue(property_exists(Field::class, 'converter'));
        $this->assertTrue(property_exists(Field::class, 'renderer'));
        $this->assertTrue(property_exists(Field::class, 'operation'));
        $this->assertTrue(property_exists(Field::class, 'readOnly'));
        $this->assertTrue(property_exists(Field::class, 'validation'));
    }

    public function testRead(): void
    {
        $this->assertTrue(method_exists(Field::class, 'read'));

        $var = (string) rand();
        $field = new Field(['name' => 'field', 'reader' => fn($value) => 'read:' . $value]);

        $this->assertTrue(is_callable($field->getConfig('reader')), 'callable');
        $this->assertEquals('read:' . $var, $field->read($var), 'callable result');

        $field->setConfig('reader', null);
        $this->assertFalse(is_callable($field->getConfig('reader')), 'not callable');
        $this->assertEquals($var, $field->read($var), 'not callable passthrough');
    }

    public function testWrite(): void
    {
        $this->assertTrue(method_exists(Field::class, 'write'));

        $var = (string) rand();
        $field = new Field(['name' => 'field', 'writer' => fn($value) => 'write:' . $value]);

        $this->assertTrue(is_callable($field->getConfig('writer')), 'callable');
        $this->assertEquals('write:' . $var, $field->write($var), 'callable result');

        $field->setConfig('writer', null);
        $this->assertFalse(is_callable($field->getConfig('writer')), 'not callable');
        $this->assertEquals($var, $field->write($var), 'not callable passthrough');
    }

    public function testConvert(): void
    {
        $this->assertTrue(method_exists(Field::class, 'convert'));

        $var = (string) rand();
        $field = new Field(['name' => 'field', 'converter' => fn($value) => 'convert:' . $value]);

        $this->assertTrue(is_callable($field->getConfig('converter')), 'callable');
        $this->assertEquals('convert:' . $var, $field->convert($var), 'callable result');

        $field->setConfig('converter', null);
        $this->assertFalse(is_callable($field->getConfig('converter')), 'not callable');
        $this->assertEquals($var, $field->convert($var), 'not callable passthrough');
    }

    public function testRender(): void
    {
        $this->assertTrue(method_exists(Field::class, 'render'));

        $var = (string) rand();
        $field = new Field(['name' => 'field', 'renderer' => fn($value) => 'render:' . $value]);

        $this->assertTrue(is_callable($field->getConfig('renderer')), 'callable');
        $this->assertEquals('render:' . $var, $field->render($var), 'callable result');

        $field->setConfig('renderer', null);
        $this->assertFalse(is_callable($field->getConfig('renderer')), 'not callable');
        $this->assertEquals($var, $field->render($var), 'not callable passthrough');
    }

    public function testGetValidation(): void
    {
        $this->assertTrue(method_exists(Field::class, 'getValidation'));

        $field = new Field(['name' => 'field', 'validation' => ['nullable' => true]]);
        $this->assertInstanceOf(FieldValidation::class, $field->getValidation());
    }

    public function testValidate(): void
    {
        $this->assertTrue(method_exists(Field::class, 'validate'));

        $field = new Field(['name' => 'field', 'validation' => ['nullable' => false]]);
        $this->assertNotEmpty($field->validate(null), 'nullable=false, null value fails');
    }

    public function testGetSetName(): void
    {
        $this->assertTrue(method_exists(Field::class, 'getName'));
        $this->assertTrue(method_exists(Field::class, 'setName'));

        $field = new Field(['name' => 'field']);
        $this->assertNotEmpty($field->getName(), 'getName default');

        $result = $field->setName('rak');
        $this->assertInstanceOf(Field::class, $result, 'setName returns fluent');
        $this->assertEquals('rak', $field->getName(), 'setName value applied');
    }

    public function testGetSetSource(): void
    {
        $this->assertTrue(method_exists(Field::class, 'getSource'));
        $this->assertTrue(method_exists(Field::class, 'setSource'));

        $field = new Field(['name' => 'field', 'source' => 'hobby']);
        $this->assertNotEmpty($field->getSource(), 'getSource default');

        $result = $field->setSource('source');
        $this->assertInstanceOf(Field::class, $result, 'setSource returns fluent');
        $this->assertSame('source', $field->getSource(), 'setSource value applied');
    }

    public function testGetSetDisplay(): void
    {
        $this->assertTrue(method_exists(Field::class, 'getDisplay'));
        $this->assertTrue(method_exists(Field::class, 'setDisplay'));

        $field = new Field(['name' => 'field', 'source' => 'hobby', 'display' => 'hobby']);
        $this->assertNotEmpty($field->getDisplay(), 'getDisplay default');

        $result = $field->setDisplay('display');
        $this->assertInstanceOf(Field::class, $result, 'setDisplay returns fluent');
    }

    public function testGetSetDefault(): void
    {
        $this->assertTrue(method_exists(Field::class, 'getDefault'));
        $this->assertTrue(method_exists(Field::class, 'setDefault'));

        $field = new Field(['name' => 'field', 'default' => 'default-h']);
        $this->assertNotEmpty($field->getDefault(), 'getDefault');

        $result = $field->setDefault('default');
        $this->assertInstanceOf(Field::class, $result, 'setDefault returns fluent');
    }

    public function testIsReadOnly(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isReadOnly'));

        $field = new Field(['name' => 'field', 'readOnly' => false]);
        $this->assertFalse($field->isReadOnly(), 'default false');
    }

    public function testSetToReadOnly(): void
    {
        $this->assertTrue(method_exists(Field::class, 'setToReadOnly'));

        $field = new Field(['name' => 'field', 'readOnly' => false]);
        $result = $field->setToReadOnly(true);
        $this->assertInstanceOf(Field::class, $result, 'returns fluent');
        $this->assertTrue($field->isReadOnly(), 'readOnly set to true');

        $field->setToReadOnly(false);
        $this->assertFalse($field->isReadOnly(), 'readOnly set back to false');
    }

    public function testIsPrivate(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isPrivate'));

        $field = new Field(['name' => 'field', 'private' => false]);
        $this->assertFalse($field->isPrivate(), 'default false');
    }

    public function testSetToPrivate(): void
    {
        $this->assertTrue(method_exists(Field::class, 'setToPrivate'));

        $field = new Field(['name' => 'field']);
        $result = $field->setToPrivate(true);
        $this->assertInstanceOf(Field::class, $result, 'returns fluent');
        $this->assertTrue($field->isPrivate(), 'private set to true');
    }

    public function testIsPublic(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isPublic'));

        $field = new Field(['name' => 'field', 'private' => false]);
        $this->assertTrue($field->isPublic(), 'default true');
    }

    public function testSetToPublic(): void
    {
        $this->assertTrue(method_exists(Field::class, 'setToPublic'));

        $field = new Field(['name' => 'field']);
        $result = $field->setToPublic(true);
        $this->assertInstanceOf(Field::class, $result, 'returns fluent');
        $this->assertTrue($field->isPublic(), 'public after setToPublic(true)');
        $this->assertFalse($field->isPrivate(), 'not private after setToPublic(true)');

        $field->setToPublic(false);
        $this->assertFalse($field->isPublic(), 'not public after setToPublic(false)');
        $this->assertTrue($field->isPrivate(), 'private after setToPublic(false)');
    }

    public function testGetOperation(): void
    {
        $this->assertTrue(method_exists(Field::class, 'getOperation'));

        $field = new Field(['name' => 'field']);
        $this->assertInstanceOf(Permission::class, $field->getOperation(), 'returns Permission');
    }

    public function testSetOperation(): void
    {
        $this->assertTrue(method_exists(Field::class, 'setOperation'));

        $field = new Field(['name' => 'field']);
        $result = $field->setOperation('si');
        $this->assertInstanceOf(Field::class, $result, 'returns fluent');
        $this->assertTrue($field->isSelectable(), 'select set');
        $this->assertTrue($field->isInsertable(), 'insert set');
        $this->assertFalse($field->isUpdatable(), 'update not set');
        $this->assertFalse($field->isDeletable(), 'delete not set');
    }

    public function testIsSetSelectable(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isSelectable'));
        $this->assertTrue(method_exists(Field::class, 'setSelectable'));

        $field = new Field(['name' => 'field']);
        $this->assertTrue($field->isSelectable(), 'default true (operation=f)');

        $result = $field->setSelectable(false);
        $this->assertInstanceOf(Field::class, $result, 'setSelectable returns fluent');
        $this->assertFalse($field->isSelectable(), 'after setSelectable false');

        $field->setSelectable(true);
        $this->assertTrue($field->isSelectable(), 'after setSelectable true');
    }

    public function testIsSetInsertable(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isInsertable'));
        $this->assertTrue(method_exists(Field::class, 'setInsertable'));

        $field = new Field(['name' => 'field']);
        $this->assertTrue($field->isInsertable(), 'default true (operation=f)');

        $result = $field->setInsertable(false);
        $this->assertInstanceOf(Field::class, $result, 'setInsertable returns fluent');
    }

    public function testIsSetUpdatable(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isUpdatable'));
        $this->assertTrue(method_exists(Field::class, 'setUpdatable'));

        $field = new Field(['name' => 'field']);
        $this->assertTrue($field->isUpdatable(), 'default true (operation=f)');

        $result = $field->setUpdatable(false);
        $this->assertInstanceOf(Field::class, $result, 'setUpdatable returns fluent');
    }

    public function testIsSetDeletable(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isDeletable'));
        $this->assertTrue(method_exists(Field::class, 'setDeletable'));

        $field = new Field(['name' => 'field']);
        $this->assertTrue($field->isDeletable(), 'default true (operation=f)');

        $result = $field->setDeletable(false);
        $this->assertInstanceOf(Field::class, $result, 'setDeletable returns fluent');
    }

    public function testIsUseRenderer(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isUseRenderer'));

        $field = new Field(['name' => 'field', 'renderer' => fn() => 'gender']);
        $this->assertTrue($field->isUseRenderer(), 'with renderer');

        $field = new Field(['name' => 'field']);
        $this->assertFalse($field->isUseRenderer(), 'without renderer');
    }

    public function testIsUseConverter(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isUseConverter'));

        $field = new Field(['name' => 'field', 'converter' => true]);
        $this->assertTrue($field->isUseConverter(), 'with converter');

        $field = new Field(['name' => 'field']);
        $this->assertFalse($field->isUseConverter(), 'without converter');
    }

    public function testIsUseReader(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isUseReader'));

        $field = new Field(['name' => 'field', 'reader' => true]);
        $this->assertTrue($field->isUseReader(), 'with reader');

        $field = new Field(['name' => 'field']);
        $this->assertFalse($field->isUseReader(), 'without reader');
    }

    public function testIsUseWriter(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isUseWriter'));

        $field = new Field(['name' => 'field', 'writer' => true]);
        $this->assertTrue($field->isUseWriter(), 'with writer');

        $field = new Field(['name' => 'field']);
        $this->assertFalse($field->isUseWriter(), 'without writer');
    }

    public function testIsUseValidation(): void
    {
        $this->assertTrue(method_exists(Field::class, 'isUseValidation'));

        $field = new Field(['name' => 'field', 'validation' => ['nullable' => true]]);
        $this->assertTrue($field->isUseValidation(), 'with validation');
    }
}
