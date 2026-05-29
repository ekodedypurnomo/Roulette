<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Exception\ModelNotFoundException;
use Roulette\Exception\ValidationException;
use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Tests\Support\DbTestCase;

class StrictModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

StrictModel::prototype([
    'table'   => 'strict_items',
    'primary' => 'id',
    'autoId'  => true,
    'fields'  => [
        ['name' => 'id',    'update' => false],
        ['name' => 'name',  'type' => 'string', 'validation' => ['notblank' => true]],
        ['name' => 'count', 'type' => 'integer'],
    ],
]);

class ExceptionFlowTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec(
            'CREATE TABLE strict_items (id TEXT PRIMARY KEY, name TEXT, count INTEGER DEFAULT 0)'
        );
    }

    private function seed(string $id, string $name, int $count = 0): void
    {
        $this->tunel->getPdo()->prepare(
            'INSERT INTO strict_items (id, name, count) VALUES (?, ?, ?)'
        )->execute([$id, $name, $count]);
    }

    // --- loadOrFail ---

    public function testLoadOrFailThrowsWhenNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        StrictModel::loadOrFail('nonexistent-id');
    }

    public function testLoadOrFailReturnsRecordWhenFound(): void
    {
        $this->seed('s1', 'exists', 5);
        $this->assertSame('exists', StrictModel::loadOrFail('s1')->get('name'));
    }

    public function testLoadReturnsNullWhenNotFound(): void
    {
        $this->assertNull(StrictModel::load('ghost'));
    }

    // --- saveOrFail ---

    public function testSaveOrFailThrowsValidationException(): void
    {
        $record = new StrictModel(['name' => '', 'count' => 0]);
        $this->expectException(ValidationException::class);
        $record->saveOrFail();
    }

    public function testSaveReturnsFalseOnValidationFailure(): void
    {
        $record = new StrictModel(['name' => '', 'count' => 0]);
        $this->assertFalse($record->save());
    }

    public function testSaveReturnsTrueOnSuccess(): void
    {
        $record = new StrictModel(['name' => 'valid', 'count' => 1]);
        $this->assertTrue($record->save());
    }

    // --- increment invalid field ---

    public function testIncrementWhereThrowsOnUnknownField(): void
    {
        $this->seed('i1', 'test', 5);
        $this->expectException(\InvalidArgumentException::class);
        StrictModel::incrementWhere(['name' => 'test'], 'nonexistent_field');
    }

    public function testInstanceIncrementThrowsOnUnknownField(): void
    {
        $this->seed('i1', 'test', 5);
        $record = StrictModel::load('i1');
        $this->expectException(\InvalidArgumentException::class);
        $record->increment('nonexistent_field');
    }
}
