<?php

declare(strict_types=1);

namespace Roulette\Tests\Tunel;

use PDO;
use ReflectionProperty;
use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Query\Operation;
use Roulette\Tests\TestCase;
use Roulette\Tunel\Standalone;

/**
 * Integration tests for Pdo/Executor via the Standalone tunel.
 * Uses a real SQLite in-memory DB — exercises the production SQL code path.
 */

class PdoModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

PdoModel::prototype([
    'table'   => 'items',
    'primary' => 'id',
    'autoId'  => true,
    'fields'  => [
        ['name' => 'id',    'update' => false],
        ['name' => 'label', 'type' => 'string'],
        ['name' => 'score', 'type' => 'integer', 'nullable' => true],
        ['name' => 'tag',   'type' => 'string',  'nullable' => true],
    ],
]);

class PdoExecutorTest extends TestCase
{
    private PDO $pdo;
    private Standalone $tunel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $this->tunel = Standalone::fromPdo($this->pdo);
        Operation::setOperationTunel($this->tunel);

        $this->pdo->exec(
            'CREATE TABLE items (id TEXT PRIMARY KEY, label TEXT, score INTEGER, tag TEXT)'
        );
    }

    protected function tearDown(): void
    {
        $ref = new ReflectionProperty(Operation::class, 'operationTunel');
        $ref->setValue(null, null);
    }

    private function seed(string $id, string $label, ?int $score = null, ?string $tag = null): void
    {
        $this->pdo->prepare('INSERT INTO items (id, label, score, tag) VALUES (?, ?, ?, ?)')
            ->execute([$id, $label, $score, $tag]);
    }

    private function fetchScore(string $id): mixed
    {
        return $this->pdo->query("SELECT score FROM items WHERE id = '$id'")->fetchColumn();
    }

    private function orderedIds(\Roulette\Model\Store $store): array
    {
        $ids = [];
        $store->each(function($r) use (&$ids) { $ids[] = $r->getId(); });
        return $ids;
    }

    // --- INSERT ---

    public function testInsertCreatesRecord(): void
    {
        $item = new PdoModel(['label' => 'alpha', 'score' => 10]);
        $item->save();

        $this->assertNotNull($item->getId());
        $row = $this->pdo->query("SELECT label, score FROM items WHERE id = '{$item->getId()}'")->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('alpha', $row['label']);
        $this->assertSame(10, (int) $row['score']);
    }

    // --- SELECT ---

    public function testSelectReturnsAllRecords(): void
    {
        $this->seed('x1', 'foo', 5);
        $this->seed('x2', 'bar', 15);

        $this->assertSame(2, PdoModel::find()->count());
    }

    public function testSelectWhereFilters(): void
    {
        $this->seed('x1', 'foo', 5);
        $this->seed('x2', 'bar', 15);

        $this->assertSame(1, PdoModel::find(['label' => 'foo'])->count());
    }

    // --- UPDATE ---

    public function testUpdatePersistsChanges(): void
    {
        $this->seed('u1', 'original', 1);
        $item = PdoModel::load('u1');
        $item->set('label', 'updated');
        $item->save();

        $row = $this->pdo->query("SELECT label FROM items WHERE id = 'u1'")->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('updated', $row['label']);
    }

    // --- DELETE ---

    public function testDeleteRemovesRecord(): void
    {
        $this->seed('d1', 'gone', 0);
        PdoModel::load('d1')->destroy();

        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM items WHERE id = 'd1'")->fetchColumn();
        $this->assertSame(0, $count);
    }

    // --- IS NULL / IS NOT NULL ---

    public function testWhereNullFilters(): void
    {
        $this->seed('n1', 'with-score', 10);
        $this->seed('n2', 'no-score', null);

        $this->assertSame(1, PdoModel::query()->whereNull('score')->get()->count());
    }

    public function testWhereNotNullFilters(): void
    {
        $this->seed('n1', 'with-score', 10);
        $this->seed('n2', 'no-score', null);

        $this->assertSame(1, PdoModel::query()->whereNotNull('score')->get()->count());
    }

    // --- ORDER BY ---

    public function testOrderByApplied(): void
    {
        $this->seed('o1', 'charlie', 3);
        $this->seed('o2', 'alpha',   1);
        $this->seed('o3', 'bravo',   2);

        $store = PdoModel::query()->orderBy(['score' => 'ASC'])->get();
        $this->assertSame(['o2', 'o3', 'o1'], $this->orderedIds($store));
    }

    // --- INCREMENT (RawExpression must embed as SQL, not bind as string) ---

    public function testIncrementViaRawExpressionIsAtomic(): void
    {
        $this->seed('i1', 'counter', 5);
        PdoModel::incrementWhere(['label' => 'counter'], 'score', 3);
        $this->assertSame(8, (int) $this->fetchScore('i1'));
    }

    public function testDecrementViaRawExpression(): void
    {
        $this->seed('i1', 'counter', 10);
        PdoModel::decrementWhere(['label' => 'counter'], 'score', 4);
        $this->assertSame(6, (int) $this->fetchScore('i1'));
    }

    // --- LIMIT / OFFSET ---

    public function testLimitAndOffset(): void
    {
        $this->seed('p1', 'a', 1);
        $this->seed('p2', 'b', 2);
        $this->seed('p3', 'c', 3);

        $store = PdoModel::query()->orderBy(['score' => 'ASC'])->take(2)->skip(1)->get();
        $ids   = $this->orderedIds($store);
        $this->assertSame(2, count($ids));
        $this->assertSame('p2', $ids[0]);
    }
}
