<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class BulkOperationsTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    // --- insertMany ---

    public function testInsertManyInsertsAllRows(): void
    {
        $count = UserModel::insertMany([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob',   'email' => 'bob@example.com'],
            ['name' => 'Carol', 'email' => 'carol@example.com'],
        ]);

        $this->assertSame(3, $count);
        $this->assertSame(3, UserModel::find()->count());
    }

    public function testInsertManyReturnsInsertedCount(): void
    {
        $count = UserModel::insertMany([
            ['name' => 'A', 'email' => 'a@x.com'],
            ['name' => 'B', 'email' => 'b@x.com'],
        ]);

        $this->assertSame(2, $count);
    }

    public function testInsertManyEmptyArrayReturnsZero(): void
    {
        $count = UserModel::insertMany([]);
        $this->assertSame(0, $count);
    }

    // --- updateWhere ---

    public function testUpdateWhereUpdatesMatchingRows(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');
        $this->seedUser('u2', 'Bob', 'bob@example.com');
        $this->seedUser('u3', 'Alice', 'alice2@example.com');

        $affected = UserModel::updateWhere(['name' => 'Alice'], ['email' => 'updated@example.com']);
        $this->assertSame(2, $affected);
    }

    public function testUpdateWhereDoesNotAffectNonMatchingRows(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');
        $this->seedUser('u2', 'Bob', 'bob@example.com');

        UserModel::updateWhere(['name' => 'Alice'], ['email' => 'new@example.com']);

        $bob = UserModel::load('u2');
        $this->assertSame('bob@example.com', $bob->get('email'));
    }

    public function testUpdateWhereReturnsZeroWhenNoMatch(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');

        $affected = UserModel::updateWhere(['name' => 'Nobody'], ['email' => 'x@y.com']);
        $this->assertSame(0, $affected);
    }

    public function testUpdateWhereEmptyDataReturnsZero(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');
        $affected = UserModel::updateWhere(['name' => 'Alice'], []);
        $this->assertSame(0, $affected);
    }

    // --- destroyWhere ---

    public function testDestroyWhereDeletesMatchingRows(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');
        $this->seedUser('u2', 'Bob', 'bob@example.com');
        $this->seedUser('u3', 'Alice', 'alice2@example.com');

        $deleted = UserModel::destroyWhere(['name' => 'Alice']);
        $this->assertSame(2, $deleted);
        $this->assertSame(1, UserModel::find()->count());
    }

    public function testDestroyWhereReturnsZeroWhenNoMatch(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');

        $deleted = UserModel::destroyWhere(['name' => 'Nobody']);
        $this->assertSame(0, $deleted);
        $this->assertSame(1, UserModel::find()->count());
    }

    public function testDestroyWhereWithSpecificId(): void
    {
        $this->seedUser('u1', 'Alice', 'alice@example.com');
        $this->seedUser('u2', 'Bob', 'bob@example.com');

        $deleted = UserModel::destroyWhere(['id' => 'u1']);
        $this->assertSame(1, $deleted);

        $this->assertNull(UserModel::load('u1'));
        $this->assertNotNull(UserModel::load('u2'));
    }
}
