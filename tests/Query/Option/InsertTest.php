<?php

declare(strict_types=1);

namespace Roulette\Tests\Query\Option;

use Roulette\Query\Option\Insert;
use Roulette\Tests\TestCase;

class InsertTest extends TestCase
{
    public function testGetAction(): void
    {
        $this->assertEquals('INSERT', Insert::getAction());
    }

    public function testTable(): void
    {
        $ins = new Insert('users');
        $this->assertEquals('users', $ins->getTable());
    }

    public function testSet(): void
    {
        $ins = new Insert('users');
        $ins->set('name', 'Alice');
        $ins->set('email', 'alice@example.com');
        $patch = $ins->getPatch();
        $this->assertEquals('Alice', $patch['name']);
        $this->assertEquals('alice@example.com', $patch['email']);
    }

    public function testSetArray(): void
    {
        $ins = new Insert('users');
        $ins->set(['name' => 'Bob', 'role' => 'admin']);
        $patch = $ins->getPatch();
        $this->assertEquals('Bob', $patch['name']);
        $this->assertEquals('admin', $patch['role']);
    }

    public function testAddPatch(): void
    {
        $ins = new Insert('users');
        $ins->addPatch('city', 'Jakarta');
        $this->assertEquals('Jakarta', $ins->getPatch()['city']);
    }

    public function testReset(): void
    {
        $ins = new Insert('users');
        $ins->set('name', 'Carol');
        $ins->reset();
        $this->assertFalse($ins->hasTable());
        $this->assertEmpty($ins->getPatch());
    }
}
