<?php

declare(strict_types=1);

namespace Roulette\Tests\Mixin;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Mixin\SoftDeletable;
use Roulette\Tests\Support\DbTestCase;

class SoftPost extends Model
{
    use SoftDeletable;

    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

SoftPost::prototype([
    'table'   => 'soft_posts',
    'primary' => 'id',
    'autoId'  => true,
    'fields'  => [
        ['name' => 'id',         'update' => false],
        'title',
        ['name' => 'deleted_at', 'nullable' => true, 'update' => true],
    ],
]);

class SoftDeletableTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec(
            'CREATE TABLE soft_posts (id TEXT PRIMARY KEY, title TEXT, deleted_at TEXT)'
        );
    }

    private function seed(string $id, string $title, ?string $deletedAt = null): void
    {
        $val = $deletedAt ? "'$deletedAt'" : 'NULL';
        $this->tunel->exec("INSERT INTO soft_posts VALUES ('$id', '$title', $val)");
    }

    // --- destroy() soft-deletes ---

    public function testDestroySetsDeletedAt(): void
    {
        $this->seed('p1', 'Post One');
        $post = SoftPost::loadOrFail('p1');
        $result = $post->destroy();

        $this->assertTrue($result);
        $this->assertTrue($post->isTrashed());
    }

    public function testDestroyDoesNotRemoveRow(): void
    {
        $this->seed('p1', 'Post One');
        $post = SoftPost::loadOrFail('p1');
        $post->destroy();

        // withTrashed bypasses scope — row should still exist
        $found = SoftPost::withTrashed()->where(['id' => 'p1'])->get();
        $this->assertSame(1, $found->count());
    }

    // --- find() excludes soft-deleted by default ---

    public function testFindExcludesTrashedRecords(): void
    {
        $this->seed('p1', 'Post One');
        $this->seed('p2', 'Post Two', '2024-01-01 00:00:00');

        $posts = SoftPost::find();
        $this->assertSame(1, $posts->count());
        $this->assertSame('p1', $posts->first()->getId());
    }

    // --- withTrashed() includes soft-deleted ---

    public function testWithTrashedIncludesAllRecords(): void
    {
        $this->seed('p1', 'Post One');
        $this->seed('p2', 'Post Two', '2024-01-01 00:00:00');

        $posts = SoftPost::withTrashed()->get();
        $this->assertSame(2, $posts->count());
    }

    // --- load() excludes soft-deleted ---

    public function testLoadReturnsTrashedRecordWithWithTrashed(): void
    {
        $this->seed('p1', 'Post One', '2024-01-01 00:00:00');

        // Normal load respects soft-delete scope → null
        $notFound = SoftPost::load('p1');
        $this->assertNull($notFound, 'load() should not return soft-deleted record');

        // withTrashed bypasses scope
        $found = SoftPost::withTrashed()->where(['id' => 'p1'])->first();
        $this->assertNotNull($found);
        $this->assertSame('p1', $found->getId());
    }

    // --- isTrashed() ---

    public function testIsTrashedFalseForActiveRecord(): void
    {
        $this->seed('p1', 'Post One');
        $post = SoftPost::loadOrFail('p1');
        $this->assertFalse($post->isTrashed());
    }

    public function testIsTrashedTrueAfterDestroy(): void
    {
        $this->seed('p1', 'Post One');
        $post = SoftPost::loadOrFail('p1');
        $post->destroy();
        $this->assertTrue($post->isTrashed());
    }

    // --- restore() ---

    public function testRestoreUndeletes(): void
    {
        $this->seed('p1', 'Post One', '2024-01-01 00:00:00');
        $post = SoftPost::withTrashed()->where(['id' => 'p1'])->first();
        $this->assertNotNull($post, 'withTrashed()->first() should find soft-deleted record');
        $this->assertTrue($post->isTrashed());

        $result = $post->restore();
        $this->assertTrue($result);
        $this->assertFalse($post->isTrashed());

        // Now visible in normal find
        $found = SoftPost::find(['id' => 'p1']);
        $this->assertSame(1, $found->count());
    }

    // --- forceDelete() ---

    public function testForceDeleteRemovesRow(): void
    {
        $this->seed('p1', 'Post One');
        $post = SoftPost::loadOrFail('p1');
        $post->forceDelete();

        $notFound = SoftPost::withTrashed()->where(['id' => 'p1'])->get();
        $this->assertSame(0, $notFound->count());
    }
}
