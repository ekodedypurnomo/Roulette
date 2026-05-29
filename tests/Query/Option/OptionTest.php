<?php

declare(strict_types=1);

namespace Roulette\Tests\Query\Option;

use Roulette\Query\Option\Option;
use Roulette\Query\Option\Select;
use Roulette\Query\Option\Insert;
use Roulette\Query\Option\Update;
use Roulette\Query\Option\Delete;
use Roulette\Tests\TestCase;

class OptionTest extends TestCase
{
    public function testGetAction(): void
    {
        $this->assertEquals('QUERY', Option::getAction());
    }

    public function testTable(): void
    {
        $opt = new Option('users');
        $this->assertEquals('users', $opt->getTable());
    }

    public function testHasTable(): void
    {
        $opt = new Option('orders');
        $this->assertTrue($opt->hasTable());
        $opt->resetTable();
        $this->assertFalse($opt->hasTable());
    }

    public function testSelect(): void
    {
        $opt = new Option('users');
        $opt->addSelect('id');
        $opt->addSelect('name');
        $select = $opt->getSelect();
        $this->assertIsArray($select);
        $this->assertArrayHasKey('id', $select);
        $this->assertArrayHasKey('name', $select);
    }

    public function testPatch(): void
    {
        $opt = new Option('users');
        $opt->set('name', 'Alice');
        $opt->set('email', 'alice@example.com');
        $patch = $opt->getPatch();
        $this->assertEquals('Alice', $patch['name']);
        $this->assertEquals('alice@example.com', $patch['email']);
    }

    public function testWhere(): void
    {
        $opt = new Option('users');
        $opt->where('status', 'active');
        $this->assertTrue($opt->hasWhere());
        $this->assertCount(1, $opt->getWhere());
    }

    public function testLimit(): void
    {
        $opt = new Option('users');
        $opt->take(10);
        $this->assertTrue($opt->hasLimit());
        $this->assertEquals(10, $opt->getLimit());
    }

    public function testOffset(): void
    {
        $opt = new Option('users');
        $opt->take(10)->skip(5);
        $this->assertTrue($opt->hasOffset());
        $this->assertEquals(5, $opt->getOffset());
    }

    public function testReset(): void
    {
        $opt = new Option('users');
        $opt->addSelect('id');
        $opt->where('id', '1');
        $opt->take(5);
        $opt->reset();
        $this->assertFalse($opt->hasTable());
        $this->assertFalse($opt->hasWhere());
    }

    public function testToSelect(): void
    {
        $opt = new Option('products');
        $result = $opt->toSelect();
        $this->assertInstanceOf(Select::class, $result);
        $this->assertEquals('products', $result->getTable());
    }

    public function testToInsert(): void
    {
        $opt = new Option('products');
        $result = $opt->toInsert();
        $this->assertInstanceOf(Insert::class, $result);
    }

    public function testToUpdate(): void
    {
        $opt = new Option('products');
        $result = $opt->toUpdate();
        $this->assertInstanceOf(Update::class, $result);
    }

    public function testToDelete(): void
    {
        $opt = new Option('products');
        $result = $opt->toDelete();
        $this->assertInstanceOf(Delete::class, $result);
    }
}
