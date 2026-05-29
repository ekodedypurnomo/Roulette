<?php

declare(strict_types=1);

namespace Roulette\Tests\Data;

use Roulette\Data\Option;
use Roulette\Tests\TestCase;

class OptionTest extends TestCase
{
    public function testProperty(): void
    {
        $this->assertTrue(property_exists(Option::class, 'fields'));
        $this->assertTrue(property_exists(Option::class, 'render'));
        $this->assertTrue(property_exists(Option::class, 'display'));
        $this->assertTrue(property_exists(Option::class, 'inline'));
        $this->assertTrue(property_exists(Option::class, 'merge'));
        $this->assertTrue(property_exists(Option::class, 'mergeMask'));
        $this->assertTrue(property_exists(Option::class, 'autoLoad'));
        $this->assertTrue(property_exists(Option::class, 'relations'));
    }

    public function testIsInline(): void
    {
        $this->assertTrue(method_exists(Option::class, 'isInline'));

        $obj = new Option();
        $this->assertFalse($obj->isInline(), 'default');

        $obj = new Option(['inline' => true]);
        $this->assertTrue($obj->isInline(), 'true');

        $obj = new Option(['inline' => false]);
        $this->assertFalse($obj->isInline(), 'false');
    }

    public function testSetInline(): void
    {
        $this->assertTrue(method_exists(Option::class, 'setInline'));

        $obj = new Option(['inline' => false]);
        $this->assertFalse($obj->isInline(), 'init');

        $obj->setInline(true);
        $this->assertTrue($obj->isInline(), 'true');

        $obj->setInline(false);
        $this->assertFalse($obj->isInline(), 'false');
    }

    public function testIsMerge(): void
    {
        $this->assertTrue(method_exists(Option::class, 'isMerge'));

        $obj = new Option();
        $this->assertFalse($obj->isMerge(), 'default');

        $obj = new Option(['merge' => true]);
        $this->assertTrue($obj->isMerge(), 'true');

        $obj = new Option(['merge' => false]);
        $this->assertFalse($obj->isMerge(), 'false');
    }

    public function testSetMerge(): void
    {
        $this->assertTrue(method_exists(Option::class, 'setMerge'));

        $obj = new Option(['merge' => false]);
        $this->assertFalse($obj->isMerge(), 'init');

        $obj->setMerge(true);
        $this->assertTrue($obj->isMerge(), 'true');

        $obj->setMerge(false);
        $this->assertFalse($obj->isMerge(), 'false');
    }

    public function testIsRender(): void
    {
        $this->assertTrue(method_exists(Option::class, 'isRender'));

        $obj = new Option();
        $this->assertTrue($obj->isRender(), 'default');

        $obj = new Option(['render' => true]);
        $this->assertTrue($obj->isRender(), 'true');

        $obj = new Option(['render' => false]);
        $this->assertFalse($obj->isRender(), 'false');
    }

    public function testSetRender(): void
    {
        $this->assertTrue(method_exists(Option::class, 'setRender'));

        $obj = new Option(['render' => false]);
        $this->assertFalse($obj->isRender(), 'init');

        $obj->setRender(true);
        $this->assertTrue($obj->isRender(), 'true');

        $obj->setRender(false);
        $this->assertFalse($obj->isRender(), 'false');
    }

    public function testIsAutoLoad(): void
    {
        $this->assertTrue(method_exists(Option::class, 'isAutoLoad'));

        $obj = new Option();
        $this->assertFalse($obj->isAutoLoad(), 'default');

        $obj = new Option(['autoLoad' => true]);
        $this->assertTrue($obj->isAutoLoad(), 'true');

        $obj = new Option(['autoLoad' => false]);
        $this->assertFalse($obj->isAutoLoad(), 'false');
    }

    public function testSetAutoLoad(): void
    {
        $this->assertTrue(method_exists(Option::class, 'setAutoLoad'));

        $obj = new Option(['autoLoad' => false]);
        $this->assertFalse($obj->isAutoLoad(), 'init');

        $obj->setAutoLoad(true);
        $this->assertTrue($obj->isAutoLoad(), 'true');

        $obj->setAutoLoad(false);
        $this->assertFalse($obj->isAutoLoad(), 'false');
    }
}
