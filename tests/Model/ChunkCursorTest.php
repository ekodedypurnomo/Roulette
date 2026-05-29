<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Model\Store;
use Roulette\Tests\Support\DbTestCase;

class ChunkModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

ChunkModel::prototype([
    'table'   => 'items',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',    'update' => false],
        ['name' => 'value', 'type' => 'integer'],
    ],
]);

class ChunkCursorTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec('CREATE TABLE items (id TEXT PRIMARY KEY, value INTEGER)');
    }

    private function seedItems(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->tunel->getPdo()->prepare('INSERT INTO items VALUES (?, ?)')->execute(["i$i", $i]);
        }
    }

    // --- chunk ---

    public function testChunkProcessesAllRecordsInBatches(): void
    {
        $this->seedItems(25);

        $ids = [];
        ChunkModel::chunk(10, function(Store $batch) use (&$ids) {
            $batch->each(function($r) use (&$ids) { $ids[] = $r->get('id'); });
        });

        $this->assertCount(25, $ids);
    }

    public function testChunkBatchSizeIsRespected(): void
    {
        $this->seedItems(25);

        $batchSizes = [];
        ChunkModel::chunk(10, function(Store $batch) use (&$batchSizes) {
            $batchSizes[] = $batch->count();
        });

        $this->assertSame([10, 10, 5], $batchSizes);
    }

    public function testChunkReturnsTotalProcessed(): void
    {
        $this->seedItems(15);
        $total = ChunkModel::chunk(10, function(Store $batch) {});
        $this->assertSame(15, $total);
    }

    public function testChunkStopsWhenCallbackReturnsFalse(): void
    {
        $this->seedItems(30);

        $processed = 0;
        ChunkModel::chunk(10, function(Store $batch) use (&$processed) {
            $processed++;
            return false; // stop after first batch
        });

        $this->assertSame(1, $processed);
    }

    public function testChunkWithEmptyTable(): void
    {
        $total = ChunkModel::chunk(10, function(Store $batch) {});
        $this->assertSame(0, $total);
    }

    public function testChunkReturnsCorrectCountWhenStopped(): void
    {
        $this->seedItems(25);

        $total = ChunkModel::chunk(10, function(Store $batch) use (&$total) {
            return false;
        });

        $this->assertSame(10, $total); // stopped after first batch
    }

    // --- cursor ---

    public function testCursorYieldsAllRecords(): void
    {
        $this->seedItems(25);

        $ids = [];
        foreach (ChunkModel::cursor() as $record) {
            $ids[] = $record->get('id');
        }

        $this->assertCount(25, $ids);
    }

    public function testCursorYieldsModelInstances(): void
    {
        $this->seedItems(5);

        foreach (ChunkModel::cursor() as $record) {
            $this->assertInstanceOf(ChunkModel::class, $record);
            break;
        }
    }

    public function testCursorWithSmallBatchSize(): void
    {
        $this->seedItems(7);

        $count = 0;
        foreach (ChunkModel::cursor(batchSize: 3) as $record) {
            $count++;
        }

        $this->assertSame(7, $count);
    }

    public function testCursorWithEmptyTable(): void
    {
        $count = 0;
        foreach (ChunkModel::cursor() as $record) {
            $count++;
        }
        $this->assertSame(0, $count);
    }

    public function testCursorCanBeStoppedEarly(): void
    {
        $this->seedItems(50);

        $count = 0;
        foreach (ChunkModel::cursor(batchSize: 10) as $record) {
            $count++;
            if ($count === 5) break;
        }

        $this->assertSame(5, $count);
    }
}
