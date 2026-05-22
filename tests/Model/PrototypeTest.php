<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Collection;
use Roulette\Model\Prototype;
use Roulette\Tests\TestCase;

class PrototypeTest extends TestCase
{
    public function testIsCollection(): void
    {
        $proto = new Prototype();
        $this->assertInstanceOf(Collection::class, $proto);
    }

    public function testStoresConfig(): void
    {
        $proto = new Prototype(['table' => 'users', 'primary' => 'id']);
        $this->assertEquals('users', $proto->get('table'));
        $this->assertEquals('id', $proto->get('primary'));
    }

    public function testCreate(): void
    {
        $proto = Prototype::create(['table' => 'orders']);
        $this->assertInstanceOf(Prototype::class, $proto);
        $this->assertEquals('orders', $proto->get('table'));
    }

    public function testSetGet(): void
    {
        $proto = new Prototype();
        $proto->set('autoId', true);
        $this->assertTrue($proto->get('autoId'));
    }
}
