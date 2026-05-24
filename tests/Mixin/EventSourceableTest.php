<?php

declare(strict_types=1);

namespace Roulette\Tests\Mixin;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Mixin\EventSourceable;
use Roulette\Tests\Support\DbTestCase;
use Roulette\Tests\Support\UserModel;

/**
 * Inline fixture model that opts into event sourcing.
 */
class EventableUser extends Model
{
    use EventSourceable;

    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class EventSourceableTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();
        $this->tunel->exec('
            CREATE TABLE model_events (
                id          TEXT PRIMARY KEY,
                model_class TEXT NOT NULL,
                record_id   TEXT NOT NULL,
                operation   TEXT NOT NULL,
                payload     TEXT NOT NULL,
                created_at  TEXT NOT NULL
            )
        ');
    }

    public static function setUpBeforeClass(): void
    {
        EventableUser::prototype([
            'table'         => 'users',
            'primary'       => 'id',
            'autoId'        => true,
            'eventSourcing' => ['table' => 'model_events'],
            'fields'        => [
                ['name' => 'id', 'update' => false],
                'name',
                'email',
            ],
        ]);
    }

    public function testCreateCapturesEvent(): void
    {
        $user = new EventableUser(['name' => 'Alice', 'email' => 'alice@example.com']);
        $user->save();

        $history = $user->getHistory();
        $this->assertCount(1, $history);
        $this->assertSame('create', $history->first()['operation']);
    }

    public function testCreateEventPayloadHasFields(): void
    {
        $user = new EventableUser(['name' => 'Bob', 'email' => 'bob@example.com']);
        $user->save();

        $payload = $user->getHistory()->first()['payload'];
        $this->assertArrayHasKey('name', $payload);
        $this->assertNull($payload['name']['from']);
        $this->assertSame('Bob', $payload['name']['to']);
    }

    public function testUpdateCapturesDiff(): void
    {
        $user = new EventableUser(['name' => 'Carol', 'email' => 'carol@example.com']);
        $user->save();

        $loaded = EventableUser::load($user->getId());
        $loaded->set('name', 'Caroline');
        $loaded->save();

        $history = $loaded->getHistory();
        $this->assertCount(2, $history);

        $updateEvent = $history->last();
        $this->assertSame('update', $updateEvent['operation']);
        $this->assertArrayHasKey('name', $updateEvent['payload']);
        $this->assertSame('Carol', $updateEvent['payload']['name']['from']);
        $this->assertSame('Caroline', $updateEvent['payload']['name']['to']);
        $this->assertArrayNotHasKey('email', $updateEvent['payload']);
    }

    public function testDeleteCapturesEvent(): void
    {
        $user = new EventableUser(['name' => 'Dave', 'email' => 'dave@example.com']);
        $user->save();
        $id = $user->getId();

        $user->destroy();

        $history = $user->getHistory();
        $last    = $history->last();
        $this->assertSame('delete', $last['operation']);
        $this->assertSame($id, $last['record_id']);
    }

    public function testGetHistoryIsChronological(): void
    {
        $user = new EventableUser(['name' => 'Eve', 'email' => 'eve@example.com']);
        $user->save();

        $loaded = EventableUser::load($user->getId());
        $loaded->set('name', 'Eva');
        $loaded->save();

        $ops = array_column($user->getHistory()->toArray(), 'operation');
        $this->assertSame(['create', 'update'], $ops);
    }

    public function testNonEventSourcedModelLeavesNoEvents(): void
    {
        $user = new UserModel(['name' => 'Frank', 'email' => 'frank@example.com']);
        $user->save();

        $count = $this->tunel->getPdo()
            ->query('SELECT COUNT(*) FROM model_events')
            ->fetchColumn();

        $this->assertSame(0, (int) $count);
    }
}
