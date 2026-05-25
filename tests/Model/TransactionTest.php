<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class TransactionTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    public function testTransactionCommitsOnSuccess(): void
    {
        UserModel::transaction(function () {
            $user = new UserModel(['name' => 'Alice', 'email' => 'alice@example.com']);
            $user->save();
        });

        $found = UserModel::find();
        $this->assertSame(1, $found->count());
        $this->assertSame('Alice', $found->first()->get('name'));
    }

    public function testTransactionRollsBackOnException(): void
    {
        try {
            UserModel::transaction(function () {
                $user = new UserModel(['name' => 'Bob', 'email' => 'bob@example.com']);
                $user->save();
                throw new \RuntimeException('Deliberate rollback');
            });
        } catch (\RuntimeException $e) {
            // expected
        }

        $found = UserModel::find();
        $this->assertSame(0, $found->count(), 'Transaction rolled back — no records persisted');
    }

    public function testTransactionRethrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Deliberate rollback');

        UserModel::transaction(function () {
            throw new \RuntimeException('Deliberate rollback');
        });
    }

    public function testTransactionReturnsCallableResult(): void
    {
        $result = UserModel::transaction(fn() => 'done');
        $this->assertSame('done', $result);
    }

    public function testNestedSavesInTransaction(): void
    {
        UserModel::transaction(function () {
            (new UserModel(['name' => 'A', 'email' => 'a@x.com']))->save();
            (new UserModel(['name' => 'B', 'email' => 'b@x.com']))->save();
        });

        $this->assertSame(2, UserModel::find()->count());
    }
}
