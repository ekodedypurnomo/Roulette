<?php

declare(strict_types=1);

namespace Roulette\Tests\Query\Option;

use Roulette\Query\Option\Update;
use Roulette\Tests\TestCase;

class UpdateTest extends TestCase
{
    public function testGetAction(): void
    {
        $this->assertEquals('UPDATE', Update::getAction());
    }

    public function testTable(): void
    {
        $upd = new Update('products');
        $this->assertEquals('products', $upd->getTable());
    }

    public function testSet(): void
    {
        $upd = new Update('products');
        $upd->set('price', 100);
        $upd->set('stock', 50);
        $patch = $upd->getPatch();
        $this->assertEquals(100, $patch['price']);
        $this->assertEquals(50, $patch['stock']);
    }

    public function testWhere(): void
    {
        $upd = new Update('products');
        $upd->where('id', 'prod-1');
        $this->assertTrue($upd->hasWhere());
        $this->assertCount(1, $upd->getWhere());
    }

    public function testReset(): void
    {
        $upd = new Update('products');
        $upd->set('name', 'Widget')->where('id', '1');
        $upd->reset();
        $this->assertFalse($upd->hasTable());
        $this->assertEmpty($upd->getPatch());
        $this->assertFalse($upd->hasWhere());
    }
}
