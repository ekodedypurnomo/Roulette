<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Collection;
use Roulette\Model;
use Roulette\Model\Properties;
use Roulette\Model\Prototype;
use Roulette\Tests\TestCase;

class PropertiesTestModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

PropertiesTestModel::prototype([
    'table'      => 'props_test',
    'primary'    => 'id',
    'properties' => [
        'color' => 'red',
        'size'  => 'large',
    ],
]);

class PropertiesTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Properties::class));
    }

    public function testGetPropertiesReturnsCollection(): void
    {
        $this->assertInstanceOf(Collection::class, PropertiesTestModel::getProperties());
    }

    public function testGetPropertiesContainsConfiguredValues(): void
    {
        $props = PropertiesTestModel::getProperties();
        $this->assertSame('red', $props->get('color'));
        $this->assertSame('large', $props->get('size'));
    }

    public function testGetPropertiesEmptyWhenNotConfigured(): void
    {
        $this->assertInstanceOf(Collection::class, Model::getProperties());
    }
}
