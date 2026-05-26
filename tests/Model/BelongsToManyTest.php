<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Model\Store;
use Roulette\Tests\Support\DbTestCase;

// --- Fixtures ---

class Btm_UserModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class Btm_RoleModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

Btm_RoleModel::prototype([
    'table'   => 'btm_roles',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',    'update' => false],
        ['name' => 'label', 'type' => 'string'],
    ],
]);

Btm_UserModel::prototype([
    'table'   => 'btm_users',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',   'update' => false],
        ['name' => 'name', 'type' => 'string'],
    ],
    'associations' => [
        'roles' => [
            'type'       => 'belongsToMany',
            'model'      => Btm_RoleModel::class,
            'pivotTable' => 'btm_user_roles',
            'foreignKey' => 'user_id',
            'relatedKey' => 'role_id',
        ],
    ],
]);

// --- Test ---

class BelongsToManyTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec('CREATE TABLE btm_users (id TEXT PRIMARY KEY, name TEXT)');
        $this->tunel->exec('CREATE TABLE btm_roles (id TEXT PRIMARY KEY, label TEXT)');
        $this->tunel->exec('CREATE TABLE btm_user_roles (user_id TEXT, role_id TEXT)');
    }

    private function seedBtmUser(string $id, string $name): void
    {
        $this->tunel->getPdo()->prepare('INSERT INTO btm_users VALUES (?,?)')->execute([$id, $name]);
    }

    private function seedRole(string $id, string $label): void
    {
        $this->tunel->getPdo()->prepare('INSERT INTO btm_roles VALUES (?,?)')->execute([$id, $label]);
    }

    private function seedPivot(string $userId, string $roleId): void
    {
        $this->tunel->getPdo()->prepare('INSERT INTO btm_user_roles VALUES (?,?)')->execute([$userId, $roleId]);
    }

    private function pivotCount(): int
    {
        return (int) $this->tunel->getPdo()->query('SELECT COUNT(*) FROM btm_user_roles')->fetchColumn();
    }

    // --- lookup ---

    public function testLookupReturnsStore(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');
        $this->seedPivot('u1', 'r1');

        $user  = Btm_UserModel::load('u1');
        $roles = $user->lookup('roles');

        $this->assertInstanceOf(Store::class, $roles);
        $this->assertSame(1, $roles->count());
    }

    public function testLookupReturnsCorrectModels(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');
        $this->seedRole('r2', 'Editor');
        $this->seedPivot('u1', 'r1');
        $this->seedPivot('u1', 'r2');

        $roles = Btm_UserModel::load('u1')->lookup('roles');
        $labels = [];
        $roles->each(function($r) use (&$labels) { $labels[] = $r->get('label'); });
        sort($labels);

        $this->assertSame(['Admin', 'Editor'], $labels);
    }

    public function testLookupEmptyWhenNoPivotRows(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $roles = Btm_UserModel::load('u1')->lookup('roles');
        $this->assertSame(0, $roles->count());
    }

    // --- attach ---

    public function testAttachInsertsPivotRow(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');

        $user = Btm_UserModel::load('u1');
        $user->attach('roles', 'r1');

        $this->assertSame(1, $this->pivotCount());
    }

    public function testAttachReturnsTrueOnSuccess(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');

        $result = Btm_UserModel::load('u1')->attach('roles', 'r1');
        $this->assertTrue($result);
    }

    public function testLookupAfterAttach(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');

        $user = Btm_UserModel::load('u1');
        $user->attach('roles', 'r1');

        $roles = $user->lookup('roles', true); // reload
        $this->assertSame(1, $roles->count());
    }

    // --- detach ---

    public function testDetachSpecificRole(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');
        $this->seedRole('r2', 'Editor');
        $this->seedPivot('u1', 'r1');
        $this->seedPivot('u1', 'r2');

        $user = Btm_UserModel::load('u1');
        $removed = $user->detach('roles', 'r1');

        $this->assertSame(1, $removed);
        $this->assertSame(1, $this->pivotCount());
    }

    public function testDetachAllRoles(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');
        $this->seedRole('r2', 'Editor');
        $this->seedPivot('u1', 'r1');
        $this->seedPivot('u1', 'r2');

        Btm_UserModel::load('u1')->detach('roles');
        $this->assertSame(0, $this->pivotCount());
    }

    // --- sync ---

    public function testSyncReplacesAllPivotRows(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');
        $this->seedRole('r2', 'Editor');
        $this->seedRole('r3', 'Viewer');
        $this->seedPivot('u1', 'r1');
        $this->seedPivot('u1', 'r2');

        Btm_UserModel::load('u1')->sync('roles', ['r3']);

        $this->assertSame(1, $this->pivotCount());
        $roles = Btm_UserModel::load('u1')->lookup('roles', true);
        $this->assertSame('Viewer', $roles->first()->get('label'));
    }

    public function testSyncWithEmptyArrayDetachesAll(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedRole('r1', 'Admin');
        $this->seedPivot('u1', 'r1');

        Btm_UserModel::load('u1')->sync('roles', []);
        $this->assertSame(0, $this->pivotCount());
    }

    // --- eager loading ---

    public function testEagerLoadingBelongsToMany(): void
    {
        $this->seedBtmUser('u1', 'Alice');
        $this->seedBtmUser('u2', 'Bob');
        $this->seedRole('r1', 'Admin');
        $this->seedRole('r2', 'Editor');
        $this->seedPivot('u1', 'r1');
        $this->seedPivot('u1', 'r2');
        $this->seedPivot('u2', 'r1');

        $users = Btm_UserModel::with('roles')::find();

        $users->each(function($user) {
            $roles = $user->getRelation('roles');
            $this->assertNotNull($roles);
        });

        $aliceRoles = null;
        $bobRoles   = null;
        $users->each(function($user) use (&$aliceRoles, &$bobRoles) {
            if ($user->get('name') === 'Alice') {
                $aliceRoles = $user->lookup('roles');
            } else {
                $bobRoles = $user->lookup('roles');
            }
        });

        $this->assertSame(2, $aliceRoles->count());
        $this->assertSame(1, $bobRoles->count());
    }
}
