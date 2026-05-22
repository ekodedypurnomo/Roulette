<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Association;

use Roulette\Model\Association\HasOne;
use Roulette\Model\Association\Relation;
use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class HasOneTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    public function testType(): void
    {
        $this->assertEquals('HASONE', HasOne::TYPE);
    }

    public function testConstruct(): void
    {
        $assoc = new HasOne(['model' => UserModel::class, 'field' => 'user_id']);
        $this->assertInstanceOf(HasOne::class, $assoc);
    }

    public function testGetField(): void
    {
        $assoc = new HasOne(['model' => UserModel::class, 'field' => 'manager_id']);
        $this->assertEquals('manager_id', $assoc->getField());
    }

    public function testGetModel(): void
    {
        $assoc = new HasOne(['model' => UserModel::class, 'field' => 'user_id']);
        $this->assertEquals(UserModel::class, $assoc->getModel());
    }

    public function testPatchRelation(): void
    {
        $assoc  = new HasOne(['model' => UserModel::class, 'field' => 'user_id']);
        $record = new UserModel(['name' => 'Parent']);
        $relation = new Relation($assoc, $record);

        $assoc->patchRelation($relation, ['name' => 'Child']);

        $this->assertTrue($relation->isAssociated(), 'associated after patch');
        $this->assertInstanceOf(UserModel::class, $relation->getResource());
        $this->assertEquals('Child', $relation->getResource()->get('name'));
    }
}
