<?php

declare(strict_types=1);

namespace Roulette\Tests\Exception;

use Roulette\Exception\ModelNotFoundException;
use Roulette\Exception\ValidationException;
use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class ModelExceptionMethodsTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    // --- loadOrFail ---

    public function testLoadOrFailReturnsRecord(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');
        $user = UserModel::loadOrFail('u1');
        $this->assertInstanceOf(UserModel::class, $user);
        $this->assertSame('Alice', $user->get('name'));
    }

    public function testLoadOrFailThrowsWhenNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        UserModel::loadOrFail('nonexistent');
    }

    public function testLoadOrFailExceptionContainsModelClass(): void
    {
        try {
            UserModel::loadOrFail('missing');
        } catch (ModelNotFoundException $e) {
            $this->assertStringContainsString('UserModel', $e->getModelClass());
            $this->assertSame('missing', $e->getId());
            return;
        }
        $this->fail('Expected ModelNotFoundException was not thrown');
    }

    // --- saveOrFail ---

    public function testSaveOrFailSavesValidRecord(): void
    {
        $user = new UserModel(['name' => 'Bob', 'email' => 'bob@example.com']);
        $result = $user->saveOrFail();
        $this->assertTrue($result);
    }

    public function testSaveOrFailSkipsValidationWhenFlagFalse(): void
    {
        // validate=false skips validation and goes straight to save
        $user = new UserModel(['name' => 'NoValidation', 'email' => 'nv@example.com']);
        $result = $user->saveOrFail(false);
        $this->assertTrue($result);
    }
}
