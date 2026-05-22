<?php

declare(strict_types=1);

namespace Roulette\Tests\Query\Option;

use Roulette\Query\Option\Select;
use Roulette\Tests\TestCase;

class SelectTest extends TestCase
{
    public function testGetAction(): void
    {
        $this->assertEquals('SELECT', Select::getAction());
    }

    public function testTable(): void
    {
        $s = new Select('products');
        $this->assertEquals('products', $s->getTable());
        $this->assertTrue($s->hasTable());
    }

    public function testSelectDefault(): void
    {
        $s = new Select('users');
        $this->assertEquals('*', $s->getSelect(), 'default is *');
    }

    public function testAddSelect(): void
    {
        $s = new Select('users');
        $s->addSelect('id')->addSelect('name', 'username');
        $columns = $s->getSelect();
        $this->assertIsArray($columns);
        $this->assertEquals('id', $columns['id']);
        $this->assertEquals('name', $columns['username']);
    }

    public function testWhere(): void
    {
        $s = new Select('users');
        $s->where('active', 1);
        $this->assertTrue($s->hasWhere());
        $this->assertCount(1, $s->getWhere());
    }

    public function testWhereArray(): void
    {
        $s = new Select('users');
        $s->where(['name' => 'Alice', 'age' => 30]);
        $this->assertCount(2, $s->getWhere());
    }

    public function testOrWhere(): void
    {
        $s = new Select('users');
        $s->where('a', 1)->orWhere('b', 2);
        $this->assertCount(2, $s->getWhere());
    }

    public function testLimit(): void
    {
        $s = new Select('users');
        $s->take(20);
        $this->assertTrue($s->hasLimit());
        $this->assertEquals(20, $s->getLimit());
    }

    public function testOffset(): void
    {
        $s = new Select('users');
        $s->take(10)->skip(5);
        $this->assertTrue($s->hasOffset());
        $this->assertEquals(5, $s->getOffset());
    }

    public function testWhereNull(): void
    {
        $s = new Select('users');
        $s->whereNull('deleted_at');
        $this->assertCount(1, $s->getWhere());
    }

    public function testWhereIn(): void
    {
        $s = new Select('users');
        $s->whereIn('id', [1, 2, 3]);
        $this->assertCount(1, $s->getWhere());
    }

    public function testReset(): void
    {
        $s = new Select('users');
        $s->addSelect('id')->where('id', '1')->take(5);
        $s->reset();
        $this->assertFalse($s->hasTable());
        $this->assertFalse($s->hasWhere());
        $this->assertFalse($s->hasLimit());
    }
}
