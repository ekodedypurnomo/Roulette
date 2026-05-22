<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model\Cache;
use Roulette\Tests\TestCase;

class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
        Cache::enable();
    }

    public function testEnableDisable(): void
    {
        $this->assertTrue(Cache::isEnabled(), 'enabled by default');
        Cache::disable();
        $this->assertFalse(Cache::isEnabled(), 'disabled after disable');
        $this->assertTrue(Cache::isDisabled(), 'isDisabled true');
        Cache::enable();
        $this->assertTrue(Cache::isEnabled(), 'enabled after enable');
    }

    public function testStore(): void
    {
        Cache::store('key1', 'value1');
        $this->assertTrue(Cache::exist('key1'), 'key exists after store');
        $this->assertEquals('value1', Cache::fetch('key1'), 'value matches');
    }

    public function testGet(): void
    {
        Cache::store('key2', 'value2');
        $this->assertEquals('value2', Cache::get('key2'), 'get is alias for fetch');
    }

    public function testHas(): void
    {
        Cache::store('key3', 'hello');
        $this->assertTrue(Cache::has('hello'), 'has finds by value');
        $this->assertFalse(Cache::has('nonexistent'), 'has false for missing value');
    }

    public function testAdd(): void
    {
        $obj = new \stdClass();
        $id = Cache::add($obj);
        $this->assertNotEmpty($id, 'add returns hash id');
        $this->assertTrue(Cache::exist($id), 'object found by hash');
    }

    public function testRemove(): void
    {
        Cache::store('rem', 'val');
        $removed = Cache::remove('rem');
        $this->assertEquals('val', $removed, 'remove returns value');
        $this->assertFalse(Cache::exist('rem'), 'key gone after remove');
    }

    public function testClear(): void
    {
        Cache::store('a', 1);
        Cache::store('b', 2);
        Cache::clear();
        $this->assertFalse(Cache::exist('a'), 'cleared key a');
        $this->assertFalse(Cache::exist('b'), 'cleared key b');
    }

    public function testDisabledReturnsNull(): void
    {
        Cache::disable();
        $this->assertNull(Cache::add(new \stdClass()), 'add null when disabled');
        $this->assertNull(Cache::store('k', 'v'), 'store null when disabled');
        $this->assertNull(Cache::exist('k'), 'exist null when disabled');
        $this->assertNull(Cache::has('v'), 'has null when disabled');
        $this->assertNull(Cache::fetch('k'), 'fetch null when disabled');
    }
}
