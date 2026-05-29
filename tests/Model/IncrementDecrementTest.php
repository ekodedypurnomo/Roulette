<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Tests\Support\DbTestCase;

class CounterModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

CounterModel::prototype([
    'table'   => 'counters',
    'primary' => 'id',
    'autoId'  => true,
    'fields'  => [
        ['name' => 'id', 'update' => false],
        ['name' => 'label', 'type' => 'string'],
        ['name' => 'count', 'type' => 'integer'],
    ],
]);

class IncrementDecrementTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec(
            'CREATE TABLE counters (id TEXT PRIMARY KEY, label TEXT, count INTEGER DEFAULT 0)'
        );
    }

    private function seedCounter(string $id, string $label, int $count): void
    {
        $this->tunel->getPdo()->prepare(
            'INSERT INTO counters (id, label, count) VALUES (?, ?, ?)'
        )->execute([$id, $label, $count]);
    }

    private function fetchCount(string $id): int
    {
        return (int) $this->tunel->getPdo()
            ->query("SELECT count FROM counters WHERE id = '$id'")
            ->fetchColumn();
    }

    // --- incrementWhere ---

    public function testIncrementWhereAddsAmount(): void
    {
        $this->seedCounter('a', 'hits', 5);
        CounterModel::incrementWhere(['label' => 'hits'], 'count', 3);
        $this->assertSame(8, $this->fetchCount('a'));
    }

    public function testIncrementWhereDefaultAmountIsOne(): void
    {
        $this->seedCounter('a', 'hits', 10);
        CounterModel::incrementWhere(['label' => 'hits'], 'count');
        $this->assertSame(11, $this->fetchCount('a'));
    }

    public function testIncrementWhereReturnsAffectedRows(): void
    {
        $this->seedCounter('a', 'hits', 0);
        $this->seedCounter('b', 'hits', 0);
        $this->seedCounter('c', 'misses', 0);
        $affected = CounterModel::incrementWhere(['label' => 'hits'], 'count');
        $this->assertSame(2, $affected);
    }

    public function testIncrementWhereZeroRowsWhenNoMatch(): void
    {
        $this->seedCounter('a', 'hits', 5);
        $affected = CounterModel::incrementWhere(['label' => 'none'], 'count');
        $this->assertSame(0, $affected);
        $this->assertSame(5, $this->fetchCount('a'));
    }

    // --- decrementWhere ---

    public function testDecrementWhereSubtractsAmount(): void
    {
        $this->seedCounter('a', 'stock', 20);
        CounterModel::decrementWhere(['label' => 'stock'], 'count', 5);
        $this->assertSame(15, $this->fetchCount('a'));
    }

    public function testDecrementWhereDefaultAmountIsOne(): void
    {
        $this->seedCounter('a', 'stock', 10);
        CounterModel::decrementWhere(['label' => 'stock'], 'count');
        $this->assertSame(9, $this->fetchCount('a'));
    }

    // --- instance increment ---

    public function testInstanceIncrementUpdatesDbAndMemory(): void
    {
        $this->seedCounter('r1', 'views', 100);
        $record = CounterModel::load('r1');
        $record->increment('count', 10);

        $this->assertSame(110, $this->fetchCount('r1'));
        $this->assertSame(110, $record->get('count'));
    }

    public function testInstanceIncrementDefaultAmount(): void
    {
        $this->seedCounter('r1', 'views', 5);
        $record = CounterModel::load('r1');
        $record->increment('count');
        $this->assertSame(6, $this->fetchCount('r1'));
    }

    // --- instance decrement ---

    public function testInstanceDecrementUpdatesDbAndMemory(): void
    {
        $this->seedCounter('r1', 'stock', 50);
        $record = CounterModel::load('r1');
        $record->decrement('count', 5);

        $this->assertSame(45, $this->fetchCount('r1'));
        $this->assertSame(45, $record->get('count'));
    }

    public function testInstanceDecrementDefaultAmount(): void
    {
        $this->seedCounter('r1', 'stock', 10);
        $record = CounterModel::load('r1');
        $record->decrement('count');
        $this->assertSame(9, $this->fetchCount('r1'));
    }
}
