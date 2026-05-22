<?php

declare(strict_types=1);

namespace Roulette\Tests\Query\Option;

use Roulette\Query\Option\Delete;
use Roulette\Tests\TestCase;

class DeleteTest extends TestCase
{
    public function testGetAction(): void
    {
        $this->assertEquals('DELETE', Delete::getAction());
    }

    public function testTable(): void
    {
        $del = new Delete('orders');
        $this->assertEquals('orders', $del->getTable());
    }

    public function testWhere(): void
    {
        $del = new Delete('orders');
        $del->where('id', 'ord-1');
        $this->assertTrue($del->hasWhere());
        $this->assertCount(1, $del->getWhere());
    }

    public function testAndOrWhere(): void
    {
        $del = new Delete('orders');
        $del->where('status', 'cancelled')->orWhere('expired', 1);
        $this->assertCount(2, $del->getWhere());
    }

    public function testReset(): void
    {
        $del = new Delete('orders');
        $del->where('id', '1');
        $del->reset();
        $this->assertFalse($del->hasTable());
        $this->assertFalse($del->hasWhere());
    }
}
