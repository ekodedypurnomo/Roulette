<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\ManagedCollection;
use Roulette\Regexp;

class ManagedCollectionTest extends TestCase
{
    public function testAcceptableKeyDefault(): void
    {
        $obj = new ManagedCollection();
        $this->assertTrue($obj->acceptableKey('anything'), 'default accepts any key');
    }

    public function testAcceptableValueDefault(): void
    {
        $obj = new ManagedCollection();
        $this->assertTrue($obj->acceptableValue('anything'), 'default accepts any value');
    }

    public function testAcceptableDefault(): void
    {
        $obj = new ManagedCollection();
        $this->assertTrue($obj->acceptable('key', 'value'), 'default accepts all');
    }

    public function testSetAcceptableKeysArray(): void
    {
        $obj = new ManagedCollection();
        $obj->setAcceptableKeys(['name', 'email']);

        $this->assertTrue($obj->acceptableKey('name'), 'allowed key');
        $this->assertFalse($obj->acceptableKey('age'), 'rejected key');
    }

    public function testSetAcceptableValuesArray(): void
    {
        $obj = new ManagedCollection();
        $obj->setAcceptableValues([1, 2, 3]);

        $this->assertTrue($obj->acceptableValue(1), 'allowed value');
        $this->assertFalse($obj->acceptableValue(99), 'rejected value');
    }

    public function testSetFiltersItems(): void
    {
        $obj = new ManagedCollection();
        $obj->setAcceptableKeys(['name']);
        $obj->set('name', 'Alice');
        $obj->set('age', 30);

        $this->assertEquals('Alice', $obj->get('name'), 'allowed key stored');
        $this->assertNull($obj->get('age'), 'rejected key not stored');
    }

    public function testAddFiltersItems(): void
    {
        $obj = new ManagedCollection();
        $obj->setAcceptableValues([1, 2, 3]);
        $obj->add(1);
        $obj->add(99);

        $this->assertSame(1, $obj->getCount(), 'only allowed value added');
    }

    public function testCallableAcceptableKey(): void
    {
        $obj = new ManagedCollection();
        $obj->setAcceptableKeys(fn($key) => strlen($key) <= 3);

        $this->assertTrue($obj->acceptableKey('id'), 'short key ok');
        $this->assertFalse($obj->acceptableKey('email'), 'long key rejected');
    }

    public function testRegexpAcceptableKey(): void
    {
        $obj = new ManagedCollection();
        $obj->setAcceptableKeys(new Regexp('/^user_/'));

        $this->assertTrue($obj->acceptableKey('user_id'), 'regex matches');
        $this->assertFalse($obj->acceptableKey('name'), 'regex does not match');
    }
}
