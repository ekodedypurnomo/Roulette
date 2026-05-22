<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Association;

use Roulette\Model\Association\HasOne;
use Roulette\Model\Association\Relation;
use Roulette\Tests\Support\UserModel;
use Roulette\Tests\TestCase;

class RelationTest extends TestCase
{
    private HasOne $assoc;
    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assoc = new HasOne(['model' => UserModel::class, 'field' => 'id']);
        $this->user  = new UserModel(['name' => 'Alice']);
    }

    public function testConstruct(): void
    {
        $relation = new Relation($this->assoc, $this->user);
        $this->assertInstanceOf(Relation::class, $relation);
    }

    public function testInitialState(): void
    {
        $relation = new Relation($this->assoc, $this->user);
        $this->assertFalse($relation->isAssociated(), 'not associated initially');
        $this->assertNull($relation->getResource(), 'no resource initially');
    }

    public function testGetAssociation(): void
    {
        $relation = new Relation($this->assoc, $this->user);
        $this->assertSame($this->assoc, $relation->getAssociation());
    }

    public function testGetRecord(): void
    {
        $relation = new Relation($this->assoc, $this->user);
        $this->assertSame($this->user, $relation->getRecord());
    }

    public function testReset(): void
    {
        $relation = new Relation($this->assoc, $this->user);
        $relation->associated = true;
        $relation->resource   = new UserModel();
        $relation->reset();
        $this->assertFalse($relation->isAssociated(), 'associated reset to false');
        $this->assertNull($relation->getResource(), 'resource reset to null');
    }
}
