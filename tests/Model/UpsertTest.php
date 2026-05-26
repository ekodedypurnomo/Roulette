<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Tests\Support\DbTestCase;

class ProductModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

ProductModel::prototype([
    'table'   => 'products',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',    'update' => false],
        ['name' => 'sku',   'type' => 'string'],
        ['name' => 'name',  'type' => 'string'],
        ['name' => 'price', 'type' => 'float'],
        ['name' => 'stock', 'type' => 'integer'],
    ],
]);

class UpsertTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec(
            'CREATE TABLE products (
                id TEXT PRIMARY KEY,
                sku TEXT UNIQUE,
                name TEXT,
                price REAL,
                stock INTEGER DEFAULT 0
            )'
        );
    }

    private function seed(string $id, string $sku, string $name, float $price, int $stock = 0): void
    {
        $this->tunel->getPdo()->prepare(
            'INSERT INTO products (id, sku, name, price, stock) VALUES (?, ?, ?, ?, ?)'
        )->execute([$id, $sku, $name, $price, $stock]);
    }

    private function fetchRow(string $id): array
    {
        return $this->tunel->getPdo()
            ->query("SELECT * FROM products WHERE id = '$id'")
            ->fetch(\PDO::FETCH_ASSOC);
    }

    private function countProducts(): int
    {
        return (int) $this->tunel->getPdo()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    // --- insertOrIgnore ---

    public function testInsertOrIgnoreInsertsNewRows(): void
    {
        $inserted = ProductModel::insertOrIgnore([
            ['id' => 'p1', 'sku' => 'SKU-A', 'name' => 'Widget', 'price' => 9.99],
            ['id' => 'p2', 'sku' => 'SKU-B', 'name' => 'Gadget', 'price' => 19.99],
        ]);

        $this->assertSame(2, $inserted);
        $this->assertSame(2, $this->countProducts());
    }

    public function testInsertOrIgnoreSkipsDuplicates(): void
    {
        $this->seed('p1', 'SKU-A', 'Widget', 9.99);

        $inserted = ProductModel::insertOrIgnore([
            ['id' => 'p2', 'sku' => 'SKU-B', 'name' => 'Gadget', 'price' => 19.99],
            ['id' => 'p3', 'sku' => 'SKU-A', 'name' => 'Duplicate', 'price' => 1.00], // sku conflict
        ]);

        $this->assertSame(1, $inserted);
        $this->assertSame(2, $this->countProducts());
    }

    public function testInsertOrIgnoreDoesNotOverwriteExistingData(): void
    {
        $this->seed('p1', 'SKU-A', 'Original', 9.99);

        ProductModel::insertOrIgnore([
            ['id' => 'p1', 'sku' => 'SKU-A', 'name' => 'Changed', 'price' => 99.99],
        ]);

        $row = $this->fetchRow('p1');
        $this->assertSame('Original', $row['name']);
    }

    // --- upsert ---

    public function testUpsertInsertsNewRows(): void
    {
        ProductModel::upsert([
            ['id' => 'p1', 'sku' => 'SKU-A', 'name' => 'Widget', 'price' => 9.99],
        ], ['sku']);

        $this->assertSame(1, $this->countProducts());
        $row = $this->fetchRow('p1');
        $this->assertSame('Widget', $row['name']);
    }

    public function testUpsertUpdatesOnConflict(): void
    {
        $this->seed('p1', 'SKU-A', 'Widget', 9.99, 10);

        ProductModel::upsert([
            ['id' => 'p1', 'sku' => 'SKU-A', 'name' => 'Widget Pro', 'price' => 14.99, 'stock' => 10],
        ], ['sku']);

        $row = $this->fetchRow('p1');
        $this->assertSame('Widget Pro', $row['name']);
        $this->assertSame(14.99, (float) $row['price']);
    }

    public function testUpsertWithSpecificUpdateFields(): void
    {
        $this->seed('p1', 'SKU-A', 'Widget', 9.99, 50);

        ProductModel::upsert([
            ['id' => 'p1', 'sku' => 'SKU-A', 'name' => 'Should Not Change', 'price' => 99.99, 'stock' => 50],
        ], ['sku'], ['price']);

        $row = $this->fetchRow('p1');
        $this->assertSame('Widget', $row['name']); // name unchanged
        $this->assertSame(99.99, (float) $row['price']); // price updated
    }

    public function testUpsertReturnsAffectedCount(): void
    {
        $this->seed('p1', 'SKU-A', 'Widget', 9.99);

        $affected = ProductModel::upsert([
            ['id' => 'p2', 'sku' => 'SKU-B', 'name' => 'New',     'price' => 5.00],
            ['id' => 'p1', 'sku' => 'SKU-A', 'name' => 'Updated', 'price' => 1.00],
        ], ['sku']);

        $this->assertGreaterThan(0, $affected);
    }
}
