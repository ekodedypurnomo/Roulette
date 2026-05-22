<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

class ModelTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
    }

    public function testLoad(): void
    {
        $id = 'test-load-id-001';
        $this->seedUser($id, 'Alice', 'alice@example.com');

        $user = UserModel::load($id);

        $this->assertNotNull($user, 'load returns a record');
        $this->assertInstanceOf(UserModel::class, $user);
        $this->assertEquals($id, $user->getId(), 'id matches');
        $this->assertEquals('Alice', $user->get('name'), 'name matches');
        $this->assertEquals('alice@example.com', $user->get('email'), 'email matches');
    }

    public function testLoadMissing(): void
    {
        $result = UserModel::load('does-not-exist');
        $this->assertNull($result, 'load of unknown id returns null');
    }

    public function testFind(): void
    {
        $this->seedUser('find-001', 'Bob', 'bob@example.com');
        $this->seedUser('find-002', 'Carol', 'carol@example.com');

        $store = UserModel::find(['name' => 'Bob']);

        $this->assertNotNull($store);
        $this->assertEquals(1, $store->getCount(), 'find returns 1 match');
        $this->assertEquals('bob@example.com', $store->first()->get('email'), 'correct record');
    }

    public function testFindAll(): void
    {
        $this->seedUser('all-001', 'Dan', 'dan@example.com');
        $this->seedUser('all-002', 'Eve', 'eve@example.com');

        $store = UserModel::find();

        $this->assertGreaterThanOrEqual(2, $store->getCount(), 'find() returns all rows');
    }

    public function testSaveInsert(): void
    {
        $user = new UserModel(['name' => 'Frank', 'email' => 'frank@example.com']);

        $this->assertNotEmpty($user->getId(), 'autoId assigned before save');
        $this->assertFalse($user->isAlive(), 'not alive before save');

        $result = $user->save($callback = null, $validate = false);

        $this->assertTrue($result, 'save returns true on success');
        $this->assertTrue($user->isAlive(), 'alive after save');

        // Verify the row was actually persisted
        $loaded = UserModel::load($user->getId());
        $this->assertNotNull($loaded, 'record findable after save');
        $this->assertEquals('Frank', $loaded->get('name'));
    }

    public function testSaveUpdate(): void
    {
        $id = 'update-id-001';
        $this->seedUser($id, 'Grace', 'grace@example.com');

        $user = UserModel::load($id);
        $this->assertNotNull($user);
        $this->assertTrue($user->isAlive());

        $user->set('name', 'Grace Updated');
        $result = $user->save($callback = null, $validate = false);

        $this->assertTrue($result, 'save returns true on update');

        $reloaded = UserModel::load($id);
        $this->assertEquals('Grace Updated', $reloaded->get('name'), 'update persisted');
    }

    public function testDestroy(): void
    {
        $id = 'destroy-id-001';
        $this->seedUser($id, 'Heidi', 'heidi@example.com');

        $user = UserModel::load($id);
        $this->assertNotNull($user);

        $result = $user->destroy();

        $this->assertTrue($result, 'destroy returns true');
        $this->assertFalse($user->isAlive(), 'not alive after destroy');

        $gone = UserModel::load($id);
        $this->assertNull($gone, 'record gone from DB after destroy');
    }

    public function testIsAlive(): void
    {
        $fresh = new UserModel(['name' => 'Ivan']);
        $this->assertFalse($fresh->isAlive(), 'new unsaved record is not alive');

        $id = 'alive-id-001';
        $this->seedUser($id, 'Ivan', 'ivan@example.com');
        $loaded = UserModel::load($id);
        $this->assertTrue($loaded->isAlive(), 'loaded record is alive');
    }

    public function testGetData(): void
    {
        $id = 'data-id-001';
        $this->seedUser($id, 'Judy', 'judy@example.com');

        $user = UserModel::load($id);
        $data = $user->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals('Judy', $data['name']);
    }

    public function testGetModified(): void
    {
        $id = 'modified-id-001';
        $this->seedUser($id, 'Karl', 'karl@example.com');

        $user = UserModel::load($id);
        $this->assertEmpty($user->getModified(), 'no modified fields after load');
        $this->assertFalse($user->isModified());

        $user->set('name', 'Karl Changed');
        $this->assertContains('name', $user->getModified(), 'name is modified');
        $this->assertTrue($user->isModified());
    }
}
