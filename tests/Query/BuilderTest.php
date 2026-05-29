<?php

declare(strict_types=1);

namespace Roulette\Tests\Query;

use Roulette\Query\Builder;
use Roulette\Query\Option\Delete;
use Roulette\Query\Option\Insert;
use Roulette\Query\Option\Option;
use Roulette\Query\Option\Select;
use Roulette\Query\Option\Update;
use Roulette\Tests\TestCase;

class BuilderTest extends TestCase
{
    public function testQuerySelect(): void
    {
        $b = Builder::query('SELECT', 'users');
        $this->assertInstanceOf(Builder::class, $b);
        $this->assertInstanceOf(Select::class, $b->getOption());
        $this->assertEquals('users', $b->getOption()->getTable());
    }

    public function testQueryInsert(): void
    {
        $b = Builder::query('INSERT', 'orders');
        $this->assertInstanceOf(Insert::class, $b->getOption());
    }

    public function testQueryUpdate(): void
    {
        $b = Builder::query('UPDATE', 'products');
        $this->assertInstanceOf(Update::class, $b->getOption());
    }

    public function testQueryDelete(): void
    {
        $b = Builder::query('DELETE', 'logs');
        $this->assertInstanceOf(Delete::class, $b->getOption());
    }

    public function testQueryDefault(): void
    {
        $b = Builder::query('query', 'misc');
        $this->assertInstanceOf(Option::class, $b->getOption());
    }

    public function testTable(): void
    {
        $b = Builder::table('products');
        $this->assertInstanceOf(Builder::class, $b);
        $this->assertEquals('products', $b->getOption()->getTable());
    }

    public function testGetOption(): void
    {
        $b = new Builder('items', 'SELECT');
        $this->assertInstanceOf(Select::class, $b->getOption());
    }

    public function testMethodDelegation(): void
    {
        $b = Builder::query('SELECT', 'users');
        $b->where('status', 'active');
        $where = $b->getOption()->getWhere();
        $this->assertCount(1, $where, 'where delegated to option');
    }
}
