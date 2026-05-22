<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Collection;
use Roulette\Model\Store;
use Roulette\Tests\Support\UserModel;

class StoreTest extends TestCase
{
    public function testIsCollection(): void
    {
        $this->assertTrue(is_subclass_of(Store::class, Collection::class));
    }

    public function testInstantiate(): void
    {
        $store = new Store(null, UserModel::class);
        $this->assertInstanceOf(Store::class, $store);
        $this->assertSame(0, $store->getCount());
    }
}
