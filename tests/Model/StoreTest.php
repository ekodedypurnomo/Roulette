<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Collection;
use Roulette\Model\Store;
use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class StoreTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    public function testIsCollection(): void
    {
        $this->assertTrue(is_subclass_of(Store::class, Collection::class));
    }

    public function testGetCount(): void
    {
        $store = new Store(null, UserModel::class);
        $this->assertSame(0, $store->getCount(), 'empty store');
    }

    public function testAdd(): void
    {
        $store = new Store(null, UserModel::class);
        $store->add(['name' => 'Alice', 'email' => 'a@b.com']);
        $this->assertSame(1, $store->getCount(), 'count after add');
    }

    public function testFirst(): void
    {
        $store = new Store(null, UserModel::class);
        $store->add(['name' => 'Bob', 'email' => 'b@c.com']);
        $first = $store->first();
        $this->assertInstanceOf(UserModel::class, $first);
        $this->assertEquals('Bob', $first->get('name'));
    }

    public function testConstructWithData(): void
    {
        $store = new Store(
            [['name' => 'Carol', 'email' => 'c@d.com'], ['name' => 'Dave', 'email' => 'd@e.com']],
            UserModel::class
        );
        $this->assertSame(2, $store->getCount(), 'constructed with 2 items');
    }

    public function testGetByKey(): void
    {
        $store = new Store(null, UserModel::class);
        $store->add(['name' => 'Eve', 'email' => 'e@f.com']);
        $first = $store->first();
        $id = $first->getId();
        $this->assertInstanceOf(UserModel::class, $store->get($id), 'get by id');
    }
}
