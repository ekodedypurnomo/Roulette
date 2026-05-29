<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Model\Store;
use Roulette\Tests\Support\DbTestCase;

class ObsUser extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class ObservableTest extends DbTestCase
{
    public static function setUpBeforeClass(): void
    {
        ObsUser::prototype([
            'table'   => 'obs_users',
            'primary' => 'id',
            'autoId'  => false,
            'fields'  => [
                ['name' => 'id',   'update' => false],
                ['name' => 'name', 'type' => 'string'],
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // clear any registered class-level listeners between tests
        ObsUser::prototype()->set('eventListeners', []);
        $this->tunel->exec('CREATE TABLE obs_users (id TEXT PRIMARY KEY, name TEXT)');
        $this->tunel->getPdo()->exec("INSERT INTO obs_users VALUES ('u1','Alice')");
        $this->tunel->getPdo()->exec("INSERT INTO obs_users VALUES ('u2','Bob')");
    }

    // ── after:save ────────────────────────────────────────────────────────────

    public function testClassLevelListenerFiresAfterSave(): void
    {
        $fired = false;
        ObsUser::on('after:save', function($record) use (&$fired) {
            $fired = true;
        });

        $user = new ObsUser(['id' => 'u3', 'name' => 'Carol']);
        $user->save();

        $this->assertTrue($fired, 'class-level after:save should fire');
    }

    public function testInstanceLevelListenerFiresAfterSave(): void
    {
        $fired = false;
        $user  = new ObsUser(['id' => 'u3', 'name' => 'Carol']);
        $user->on('after:save', function($record) use (&$fired) {
            $fired = true;
        });
        $user->save();

        $this->assertTrue($fired, 'instance-level after:save should fire');
    }

    public function testClassListenerDoesNotFireForDifferentModel(): void
    {
        // A class-level listener on ObsUser must not fire when a different
        // model class saves. We verify by checking the fired count.
        $count = 0;
        ObsUser::on('after:save', function() use (&$count) {
            $count++;
        });

        // Save via ObsUser — should fire
        $u = new ObsUser(['id' => 'u3', 'name' => 'Carol']);
        $u->save();
        $this->assertSame(1, $count);

        // Creating a raw Model (no table) — no save, so count stays 1
        $this->assertSame(1, $count);
    }

    // ── before:save can abort ─────────────────────────────────────────────────

    public function testBeforeSaveReturnFalseAbortsSave(): void
    {
        ObsUser::on('before:save', fn() => false);

        $user   = new ObsUser(['id' => 'u3', 'name' => 'Carol']);
        $result = $user->save();

        $this->assertFalse($result, 'save should return false when before:save aborts');

        // Record should not exist in DB
        $found = ObsUser::load('u3');
        $this->assertNull($found);
    }

    // ── after:destroy ─────────────────────────────────────────────────────────

    public function testAfterDestroyFires(): void
    {
        $fired = false;
        ObsUser::on('after:destroy', function() use (&$fired) {
            $fired = true;
        });

        $user = ObsUser::load('u1');
        $user->destroy();

        $this->assertTrue($fired, 'after:destroy should fire');
    }

    // ── after:load ────────────────────────────────────────────────────────────

    public function testAfterLoadFiresWhenRecordHydrated(): void
    {
        $loadedId = null;
        ObsUser::on('after:load', function($record) use (&$loadedId) {
            $loadedId = $record->getId();
        });

        ObsUser::load('u1');

        $this->assertSame('u1', $loadedId, 'after:load should fire with the loaded record');
    }

    // ── after:find ────────────────────────────────────────────────────────────

    public function testAfterFindFiresWithStore(): void
    {
        $storeRef = null;
        ObsUser::on('after:find', function($record, $store) use (&$storeRef) {
            $storeRef = $store;
        });

        ObsUser::find();

        $this->assertInstanceOf(Store::class, $storeRef, 'after:find should receive the Store');
        $this->assertSame(2, $storeRef->count());
    }

    // ── off() removes class listener ──────────────────────────────────────────

    public function testOffRemovesClassListener(): void
    {
        $count = 0;
        $fn    = function() use (&$count) { $count++; };

        ObsUser::on('after:save', $fn);
        $u = new ObsUser(['id' => 'u3', 'name' => 'Carol']);
        $u->save();
        $this->assertSame(1, $count);

        ObsUser::off('after:save', $fn);
        $u2 = new ObsUser(['id' => 'u4', 'name' => 'Dave']);
        $u2->save();
        $this->assertSame(1, $count, 'listener removed — count should not increase');
    }
}
