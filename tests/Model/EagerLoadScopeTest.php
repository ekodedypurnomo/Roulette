<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Mixin\SoftDeletable;
use Roulette\Tests\Support\DbTestCase;

/**
 * Regression tests: eager-loaded relations must respect model scopes
 * (e.g. SoftDeletable must exclude soft-deleted records in relations).
 */

class ELAuthor extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class ELPost extends Model
{
    use SoftDeletable;

    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

ELAuthor::prototype([
    'table'        => 'el_authors',
    'primary'      => 'id',
    'autoId'       => false,
    'fields'       => [
        ['name' => 'id',   'update' => false],
        ['name' => 'name', 'type' => 'string'],
    ],
    'associations' => [
        'posts' => ['type' => 'hasMany', 'model' => ELPost::class, 'field' => 'author_id'],
    ],
]);

ELPost::prototype([
    'table'  => 'el_posts',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',         'update' => false],
        ['name' => 'author_id',  'type' => 'string'],
        ['name' => 'title',      'type' => 'string'],
        ['name' => 'deleted_at', 'nullable' => true, 'update' => true],
    ],
]);

class EagerLoadScopeTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec('CREATE TABLE el_authors (id TEXT PRIMARY KEY, name TEXT)');
        $this->tunel->exec('CREATE TABLE el_posts (id TEXT PRIMARY KEY, author_id TEXT, title TEXT, deleted_at TEXT)');
    }

    private function seedAuthor(string $id, string $name): void
    {
        $this->tunel->getPdo()->prepare('INSERT INTO el_authors (id, name) VALUES (?, ?)')
            ->execute([$id, $name]);
    }

    private function seedPost(string $id, string $authorId, string $title, ?string $deletedAt = null): void
    {
        $this->tunel->getPdo()->prepare('INSERT INTO el_posts (id, author_id, title, deleted_at) VALUES (?, ?, ?, ?)')
            ->execute([$id, $authorId, $title, $deletedAt]);
    }

    public function testEagerLoadExcludesSoftDeletedPosts(): void
    {
        $this->seedAuthor('a1', 'Alice');
        $this->seedPost('p1', 'a1', 'Live Post');
        $this->seedPost('p2', 'a1', 'Deleted Post', '2024-01-01 00:00:00');

        $authors = ELAuthor::with('posts')->where('id', 'a1')->get();
        $author  = $authors->first();
        $posts   = $author->lookup('posts');

        $this->assertSame(1, $posts->count(), 'soft-deleted post must not appear in eager-loaded relation');
        $this->assertSame('Live Post', $posts->first()->get('title'));
    }

    public function testEagerLoadIncludesAllPostsWhenNoneSoftDeleted(): void
    {
        $this->seedAuthor('a1', 'Alice');
        $this->seedPost('p1', 'a1', 'Post A');
        $this->seedPost('p2', 'a1', 'Post B');

        $authors = ELAuthor::with('posts')->where('id', 'a1')->get();
        $posts   = $authors->first()->lookup('posts');

        $this->assertSame(2, $posts->count());
    }

    public function testDirectFindAlsoExcludesSoftDeleted(): void
    {
        $this->seedPost('p1', 'a1', 'Live');
        $this->seedPost('p2', 'a1', 'Dead', '2024-01-01 00:00:00');

        $store = ELPost::find(['author_id' => 'a1']);
        $this->assertSame(1, $store->count(), 'find() must exclude soft-deleted via __softDelete scope');
    }
}
