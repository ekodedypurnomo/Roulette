<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Actor;
use Roulette\Tests\Support\UserModel;

class ActorTest extends TestCase
{
    public function testIsModel(): void
    {
        $this->assertTrue(is_subclass_of(Actor::class, \Roulette\Model::class));
    }

    public function testCanReturnsTrueWhenNoPolicy(): void
    {
        $actor = new Actor();
        // null target has no getPolicy → returns true
        $this->assertTrue($actor->can('edit', null));
    }

    public function testCanReturnsTrueWhenPolicyNotFound(): void
    {
        $actor = new Actor();
        $user = new UserModel(['name' => 'Alice']);
        // UserModel has no policies defined → getPolicy returns null → can returns true
        $this->assertTrue($actor->can('edit', $user));
    }

    public function testCanReturnsTrueForClassWithNoMatchingPolicy(): void
    {
        $actor = new Actor();
        // UserModel class has no 'delete' policy
        $this->assertTrue($actor->can('delete', UserModel::class));
    }

    public function testAbleAliasesCan(): void
    {
        $actor = new Actor();
        $this->assertTrue($actor->able('view', null));
    }

    public function testCanWithDefinedPolicyAllow(): void
    {
        // Set a policy on UserModel that never denies
        UserModel::setPolicy('allow_all', fn() => false);
        $actor = new Actor();
        $user = new UserModel();
        $this->assertTrue($actor->can('allow_all', $user), 'policy fn returns false → allowed');

        // Clean up
        UserModel::getPolicies()->reset();
    }

    public function testCanWithDefinedPolicyDeny(): void
    {
        UserModel::setPolicy('deny_all', fn() => true);
        $actor = new Actor();
        $user = new UserModel();
        $this->assertFalse($actor->can('deny_all', $user), 'policy fn returns true → denied');

        UserModel::getPolicies()->reset();
    }
}
