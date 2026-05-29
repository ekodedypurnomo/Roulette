<?php

declare(strict_types=1);

namespace Roulette\Tests\Schema;

use Roulette\Model;
use Roulette\Schema;
use Roulette\Model\Prototype;
use Roulette\Tests\Support\DbTestCase;

/**
 * Inline fixture: model with explicit field types for schema introspection.
 */
class TypedModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class SchemaTest extends DbTestCase
{
    public static function setUpBeforeClass(): void
    {
        TypedModel::prototype([
            'table'   => 'typed_test',
            'primary' => 'id',
            'autoId'  => true,
            'fields'  => [
                ['name' => 'id', 'update' => false],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'count', 'type' => 'integer', 'nullable' => true],
            ],
        ]);
    }

    public function testSqlGeneratesCreateTable(): void
    {
        $sql = Schema::sql(TypedModel::class);

        $this->assertStringContainsString('CREATE TABLE typed_test', $sql);
        $this->assertStringContainsString('title', $sql);
        $this->assertStringContainsString('count', $sql);
        $this->assertStringContainsString('id', $sql);
    }

    public function testSqlIncludesPrimaryKey(): void
    {
        $sql = Schema::sql(TypedModel::class);

        $this->assertStringContainsString('PRIMARY KEY', $sql);
        // primary key must appear on the id column definition
        $this->assertMatchesRegularExpression('/id\s+TEXT\s+PRIMARY KEY/i', $sql);
    }

    public function testDiffAllMissingWhenTableAbsent(): void
    {
        $diff = Schema::diff(TypedModel::class);

        $this->assertSame('typed_test', $diff['table']);
        $this->assertFalse($diff['exists']);
        $this->assertCount(3, $diff['missing']);  // id, title, count
        $this->assertEmpty($diff['extra']);

        $missingNames = array_column($diff['missing'], 'name');
        $this->assertContains('id', $missingNames);
        $this->assertContains('title', $missingNames);
        $this->assertContains('count', $missingNames);
    }

    public function testDiffEmptyWhenSchemaMatches(): void
    {
        Schema::migrate(TypedModel::class);

        $diff = Schema::diff(TypedModel::class);

        $this->assertTrue($diff['exists']);
        $this->assertEmpty($diff['missing']);
    }

    public function testDiffDetectsMissingColumn(): void
    {
        // Create table with only the primary key — title and count are missing
        $this->tunel->exec('CREATE TABLE typed_test (id TEXT PRIMARY KEY)');

        $diff = Schema::diff(TypedModel::class);

        $this->assertTrue($diff['exists']);

        $missingNames = array_column($diff['missing'], 'name');
        $this->assertContains('title', $missingNames);
        $this->assertContains('count', $missingNames);
        $this->assertNotContains('id', $missingNames);
    }

    public function testMigrateCreatesWorkingTable(): void
    {
        Schema::migrate(TypedModel::class);

        $record = new TypedModel(['title' => 'hello', 'count' => 42]);
        $record->save();

        $found = TypedModel::find();
        $this->assertCount(1, $found);
        $this->assertSame('hello', $found->first()->get('title'));
        $this->assertEquals(42, $found->first()->get('count'));
    }

    public function testMigrateAddsColumnsToExistingTable(): void
    {
        // Create table with only primary key
        $this->tunel->exec('CREATE TABLE typed_test (id TEXT PRIMARY KEY)');

        // Confirm title and count are missing
        $diff = Schema::diff(TypedModel::class);
        $this->assertCount(2, $diff['missing']);

        // migrate() should ADD COLUMN for missing ones
        Schema::migrate(TypedModel::class);

        // After migrate, diff should be clean
        $diff = Schema::diff(TypedModel::class);
        $this->assertEmpty($diff['missing']);
    }

    public function testMigrateDoesNotDropExtraColumns(): void
    {
        // Create table with an extra column not in the model
        $this->tunel->exec(
            'CREATE TABLE typed_test (id TEXT PRIMARY KEY, title TEXT, count INTEGER, legacy_col TEXT)'
        );

        Schema::migrate(TypedModel::class);

        $diff = Schema::diff(TypedModel::class);
        $this->assertEmpty($diff['missing']);
        $this->assertCount(1, $diff['extra']);
        $this->assertSame('legacy_col', $diff['extra'][0]['name']);
    }

    public function testDiffDetectsExtraColumns(): void
    {
        $this->tunel->exec(
            'CREATE TABLE typed_test (id TEXT PRIMARY KEY, title TEXT, count INTEGER, orphan TEXT)'
        );

        $diff = Schema::diff(TypedModel::class);
        $this->assertEmpty($diff['missing']);
        $extraNames = array_column($diff['extra'], 'name');
        $this->assertContains('orphan', $extraNames);
    }
}
