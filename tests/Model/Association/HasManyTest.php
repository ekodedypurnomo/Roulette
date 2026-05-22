<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Association;

use Roulette\Model\Association\HasMany;
use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class HasManyTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    public function testType(): void
    {
        $this->assertEquals('HASMANY', HasMany::TYPE);
    }

    public function testConstruct(): void
    {
        $assoc = new HasMany(['model' => UserModel::class, 'field' => 'user_id']);
        $this->assertInstanceOf(HasMany::class, $assoc);
    }

    public function testGetField(): void
    {
        $assoc = new HasMany(['model' => UserModel::class, 'field' => 'owner_id']);
        $this->assertEquals('owner_id', $assoc->getField());
    }

    public function testGetModel(): void
    {
        $assoc = new HasMany(['model' => UserModel::class, 'field' => 'user_id']);
        $this->assertEquals(UserModel::class, $assoc->getModel());
    }

    public function testGetName(): void
    {
        $assoc = new HasMany(['model' => UserModel::class, 'field' => 'user_id', 'name' => 'friends']);
        $this->assertEquals('friends', $assoc->getName());
    }
}
