<?php

declare(strict_types=1);

namespace Roulette\Tests\Query;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Tests\Support\DbTestCase;

class WhereModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

WhereModel::prototype([
    'table'   => 'products',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',       'update' => false],
        ['name' => 'name',     'type' => 'string'],
        ['name' => 'price',    'type' => 'integer', 'nullable' => true],
        ['name' => 'category', 'type' => 'string',  'nullable' => true],
        ['name' => 'active',   'type' => 'integer', 'nullable' => true],
    ],
]);

class WhereClauseExecutionTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec(
            'CREATE TABLE products (id TEXT PRIMARY KEY, name TEXT, price INTEGER, category TEXT, active INTEGER)'
        );
    }

    private function seed(string $id, string $name, ?int $price, ?string $cat = null, ?int $active = 1): void
    {
        $this->tunel->getPdo()->prepare(
            'INSERT INTO products (id, name, price, category, active) VALUES (?, ?, ?, ?, ?)'
        )->execute([$id, $name, $price, $cat, $active]);
    }

    /** Collect IDs in DB-result order (no sort). */
    private function orderedIds(\Roulette\Model\Store $store): array
    {
        $ids = [];
        $store->each(function($r) use (&$ids) { $ids[] = $r->getId(); });
        return $ids;
    }

    /** Collect IDs sorted — for assertions where order doesn't matter. */
    private function sortedIds(\Roulette\Model\Store $store): array
    {
        $ids = $this->orderedIds($store);
        sort($ids);
        return $ids;
    }

    // --- IS NULL / IS NOT NULL ---

    public function testWhereNull(): void
    {
        $this->seed('p1', 'has-price', 100);
        $this->seed('p2', 'no-price', null);

        $store = WhereModel::query()->whereNull('price')->get();
        $this->assertSame(['p2'], $this->sortedIds($store));
    }

    public function testWhereNotNull(): void
    {
        $this->seed('p1', 'has-price', 100);
        $this->seed('p2', 'no-price', null);

        $store = WhereModel::query()->whereNotNull('price')->get();
        $this->assertSame(['p1'], $this->sortedIds($store));
    }

    // --- BETWEEN ---

    public function testWhereBetween(): void
    {
        $this->seed('p1', 'cheap',     10);
        $this->seed('p2', 'mid',       50);
        $this->seed('p3', 'expensive', 90);

        $store = WhereModel::query()->whereBetween('price', [20, 80])->get();
        $this->assertSame(['p2'], $this->sortedIds($store));
    }

    public function testWhereNotBetween(): void
    {
        $this->seed('p1', 'cheap',     10);
        $this->seed('p2', 'mid',       50);
        $this->seed('p3', 'expensive', 90);

        $store = WhereModel::query()->whereNotBetween('price', [20, 80])->get();
        $this->assertSame(['p1', 'p3'], $this->sortedIds($store));
    }

    // --- IN / NOT IN ---

    public function testWhereIn(): void
    {
        $this->seed('p1', 'a', 1, 'shoes');
        $this->seed('p2', 'b', 2, 'bags');
        $this->seed('p3', 'c', 3, 'hats');

        $store = WhereModel::query()->whereIn('category', ['shoes', 'hats'])->get();
        $this->assertSame(['p1', 'p3'], $this->sortedIds($store));
    }

    public function testWhereNotIn(): void
    {
        $this->seed('p1', 'a', 1, 'shoes');
        $this->seed('p2', 'b', 2, 'bags');
        $this->seed('p3', 'c', 3, 'hats');

        $store = WhereModel::query()->whereNotIn('category', ['shoes', 'hats'])->get();
        $this->assertSame(['p2'], $this->sortedIds($store));
    }

    // --- LIKE ---

    public function testWhereLike(): void
    {
        $this->seed('p1', 'blue shirt', 20);
        $this->seed('p2', 'red shirt',  25);
        $this->seed('p3', 'blue pants', 30);

        $store = WhereModel::query()->where('name', 'LIKE', 'blue%')->get();
        $this->assertSame(['p1', 'p3'], $this->sortedIds($store));
    }

    // --- Nested grouped conditions ---

    public function testNestedOrConditions(): void
    {
        $this->seed('p1', 'a', 10,  'shoes', 1);
        $this->seed('p2', 'b', 200, 'bags',  0);
        $this->seed('p3', 'c', 30,  'hats',  1);

        // (price < 20 OR category = 'bags') AND active = 1
        $store = WhereModel::query()
            ->where(function($q) {
                $q->where('price', '<', 20)->orWhere('category', 'bags');
            })
            ->where('active', 1)
            ->get();

        $this->assertSame(['p1'], $this->sortedIds($store));
    }

    // --- ORDER BY ---

    public function testOrderByDesc(): void
    {
        $this->seed('p1', 'c', 30);
        $this->seed('p2', 'a', 10);
        $this->seed('p3', 'b', 20);

        $store = WhereModel::query()->orderBy(['price' => 'DESC'])->get();
        $this->assertSame(['p1', 'p3', 'p2'], $this->orderedIds($store));
    }

    // --- LIMIT / OFFSET ---

    public function testLimitOffset(): void
    {
        $this->seed('p1', 'a', 1);
        $this->seed('p2', 'b', 2);
        $this->seed('p3', 'c', 3);
        $this->seed('p4', 'd', 4);

        $store = WhereModel::query()->orderBy(['price' => 'ASC'])->take(2)->skip(1)->get();
        $this->assertSame(['p2', 'p3'], $this->orderedIds($store));
    }
}
