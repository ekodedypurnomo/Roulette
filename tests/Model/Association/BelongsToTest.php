<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Association;

use Roulette\Model\Association\BelongsTo;
use Roulette\Model\Association\Relation;
use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class BelongsToTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    public function testType(): void
    {
        $this->assertEquals('BELONGSTO', BelongsTo::TYPE);
    }

    public function testConstruct(): void
    {
        $assoc = new BelongsTo(['model' => UserModel::class, 'field' => 'author_id']);
        $this->assertInstanceOf(BelongsTo::class, $assoc);
    }

    public function testGetField(): void
    {
        $assoc = new BelongsTo(['model' => UserModel::class, 'field' => 'author_id']);
        $this->assertEquals('author_id', $assoc->getField());
    }

    public function testGetModel(): void
    {
        $assoc = new BelongsTo(['model' => UserModel::class, 'field' => 'author_id']);
        $this->assertEquals(UserModel::class, $assoc->getModel());
    }

    public function testGetName(): void
    {
        $assoc = new BelongsTo(['model' => UserModel::class, 'field' => 'author_id', 'name' => 'author']);
        $this->assertEquals('author', $assoc->getName());
    }

    public function testPatchRelation(): void
    {
        $assoc    = new BelongsTo(['model' => UserModel::class, 'field' => 'author_id']);
        $record   = new UserModel(['name' => 'Post Owner']);
        $relation = new Relation($assoc, $record);

        $assoc->patchRelation($relation, ['name' => 'Author Name']);

        $this->assertTrue($relation->isAssociated(), 'associated after patch');
        $this->assertInstanceOf(UserModel::class, $relation->getResource());
        $this->assertEquals('Author Name', $relation->getResource()->get('name'));
    }
}
