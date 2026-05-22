<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model\Field\Field;
use Roulette\Model\Fields;
use Roulette\Tests\TestCase;

class FieldsTest extends TestCase
{
    public function testConstruct(): void
    {
        $fields = new Fields();
        $this->assertInstanceOf(Fields::class, $fields);
        $this->assertSame(0, $fields->getCount());
    }

    public function testConstructWithConfig(): void
    {
        $fields = new Fields(['id', 'name', 'email']);
        $this->assertSame(3, $fields->getCount(), '3 fields from string array');
    }

    public function testAddConvertsToField(): void
    {
        $fields = new Fields();
        $fields->add('title');
        $field = $fields->get('title');
        $this->assertInstanceOf(Field::class, $field, 'string converted to Field');
        $this->assertEquals('title', $field->getName());
    }

    public function testAddFieldInstance(): void
    {
        $fields = new Fields();
        $f = new Field(['name' => 'price']);
        $fields->add($f);
        $this->assertSame($f, $fields->get('price'));
    }

    public function testGetName(): void
    {
        $fields = new Fields(['id', 'name', 'email']);
        $names = $fields->getName();
        $this->assertIsArray($names);
        $this->assertContains('id', $names);
        $this->assertContains('name', $names);
        $this->assertContains('email', $names);
    }

    public function testFilterInsertable(): void
    {
        $fields = new Fields([
            ['name' => 'id',    'insert' => false],
            ['name' => 'name',  'insert' => true],
            ['name' => 'email', 'insert' => true],
        ]);
        $insertable = $fields->filterInsertable();
        $this->assertSame(2, $insertable->getCount(), 'two insertable fields');
    }

    public function testFilterSelectable(): void
    {
        $fields = new Fields([
            ['name' => 'secret', 'select' => false],
            ['name' => 'name',   'select' => true],
        ]);
        $selectable = $fields->filterSelectable();
        $this->assertSame(1, $selectable->getCount(), 'one selectable field');
    }

    public function testFilterUpdatable(): void
    {
        $fields = new Fields([
            ['name' => 'id',   'update' => false],
            ['name' => 'name', 'update' => true],
        ]);
        $updatable = $fields->filterUpdatable();
        $this->assertSame(1, $updatable->getCount());
    }

    public function testGetAttribute(): void
    {
        $fields = new Fields(['name', 'email']);
        $names = $fields->getAttribute('name');
        $this->assertIsArray($names);
        $this->assertCount(2, $names);
    }
}
