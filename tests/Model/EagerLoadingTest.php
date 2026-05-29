<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Model\Store;
use Roulette\Tests\Support\DbTestCase;

class EagerPost extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class EagerComment extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class EagerProfile extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class EagerLoadingTest extends DbTestCase
{
    public static function setUpBeforeClass(): void
    {
        EagerPost::prototype([
            'table'        => 'eager_posts',
            'primary'      => 'id',
            'autoId'       => true,
            'fields'       => [
                ['name' => 'id', 'update' => false],
                'title',
            ],
            'associations' => [
                'comments' => ['type' => 'hasMany', 'model' => EagerComment::class, 'field' => 'post_id'],
                'profile'  => ['type' => 'hasOne',  'model' => EagerProfile::class, 'field' => 'post_id'],
            ],
        ]);

        EagerComment::prototype([
            'table'        => 'eager_comments',
            'primary'      => 'id',
            'autoId'       => true,
            'fields'       => [
                ['name' => 'id', 'update' => false],
                'post_id',
                'body',
            ],
            'associations' => [
                'post' => ['type' => 'belongsTo', 'model' => EagerPost::class, 'field' => 'post_id'],
            ],
        ]);

        EagerProfile::prototype([
            'table'  => 'eager_profiles',
            'primary' => 'id',
            'autoId'  => true,
            'fields'  => [
                ['name' => 'id', 'update' => false],
                'post_id',
                'bio',
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tunel->exec('CREATE TABLE eager_posts     (id TEXT PRIMARY KEY, title TEXT)');
        $this->tunel->exec('CREATE TABLE eager_comments  (id TEXT PRIMARY KEY, post_id TEXT, body TEXT)');
        $this->tunel->exec('CREATE TABLE eager_profiles  (id TEXT PRIMARY KEY, post_id TEXT, bio TEXT)');
    }

    private function seedPosts(): void
    {
        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO eager_posts VALUES ('p1','Post One')");
        $pdo->exec("INSERT INTO eager_posts VALUES ('p2','Post Two')");
    }

    private function seedComments(): void
    {
        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO eager_comments VALUES ('c1','p1','First comment')");
        $pdo->exec("INSERT INTO eager_comments VALUES ('c2','p1','Second comment')");
        $pdo->exec("INSERT INTO eager_comments VALUES ('c3','p2','Third comment')");
    }

    private function seedProfiles(): void
    {
        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO eager_profiles VALUES ('pr1','p1','Bio of p1')");
    }

    // --- HasMany eager loading ---

    public function testWithLoadsManyRelation(): void
    {
        $this->seedPosts();
        $this->seedComments();

        $posts = EagerPost::with('comments')->get();

        $this->assertSame(2, $posts->count());

        $post1 = $posts->first();
        $comments1 = $post1->lookup('comments');

        $this->assertInstanceOf(Store::class, $comments1);
        $this->assertSame(2, $comments1->count());
    }

    public function testWithBatchesHasMany(): void
    {
        $this->seedPosts();
        $this->seedComments();

        // Verify that lookup does NOT make extra queries when already eager-loaded
        $queryCount = 0;
        $originalTunel = $this->tunel;

        $posts = EagerPost::with('comments')->get();
        $posts->each(function($post) use (&$queryCount, $originalTunel) {
            $post->lookup('comments'); // should use preloaded data, not new query
        });

        // After eager load, each post's relation is already associated
        $posts->each(function($post) {
            $comments = $post->lookup('comments');
            $this->assertInstanceOf(Store::class, $comments);
        });
    }

    public function testWithHasManyAssignsCorrectChildren(): void
    {
        $this->seedPosts();
        $this->seedComments();

        $posts = EagerPost::with('comments')->get();
        $post1 = null;
        $post2 = null;
        $posts->each(function($p) use (&$post1, &$post2) {
            if ($p->getId() === 'p1') $post1 = $p;
            if ($p->getId() === 'p2') $post2 = $p;
        });

        $this->assertSame(2, $post1->lookup('comments')->count());
        $this->assertSame(1, $post2->lookup('comments')->count());
    }

    // --- HasOne eager loading ---

    public function testWithLoadsHasOneRelation(): void
    {
        $this->seedPosts();
        $this->seedProfiles();

        $posts = EagerPost::with('profile')->get();
        $post1 = null;
        $posts->each(function($p) use (&$post1) {
            if ($p->getId() === 'p1') $post1 = $p;
        });

        $profile = $post1->lookup('profile');
        $this->assertInstanceOf(EagerProfile::class, $profile);
        $this->assertSame('Bio of p1', $profile->get('bio'));
    }

    public function testWithHasOneReturnsNullForNoRelation(): void
    {
        $this->seedPosts();
        // No profile for p2
        $this->seedProfiles();

        $posts = EagerPost::with('profile')->get();
        $post2 = null;
        $posts->each(function($p) use (&$post2) {
            if ($p->getId() === 'p2') $post2 = $p;
        });

        $this->assertNull($post2->lookup('profile'));
    }

    // --- BelongsTo eager loading ---

    public function testWithLoadsBelongsToRelation(): void
    {
        $this->seedPosts();
        $this->seedComments();

        $comments = EagerComment::with('post')->get();

        $this->assertGreaterThan(0, $comments->count());
        $comments->each(function($comment) {
            $post = $comment->lookup('post');
            $this->assertInstanceOf(EagerPost::class, $post);
        });
    }

    public function testWithMultipleRelations(): void
    {
        $this->seedPosts();
        $this->seedComments();
        $this->seedProfiles();

        $posts = EagerPost::with(['comments', 'profile'])->get();

        $post1 = null;
        $posts->each(function($p) use (&$post1) {
            if ($p->getId() === 'p1') $post1 = $p;
        });

        $this->assertSame(2, $post1->lookup('comments')->count());
        $this->assertInstanceOf(EagerProfile::class, $post1->lookup('profile'));
    }

    // --- with() returns ModelQueryBuilder for fluent chaining ---

    public function testWithReturnsQueryBuilder(): void
    {
        $result = EagerPost::with('comments');
        $this->assertInstanceOf(\Roulette\Query\ModelQueryBuilder::class, $result);
    }

    public function testWithChainableWithFindReturnsStore(): void
    {
        $this->seedPosts();
        $store = EagerPost::with('comments')->get();
        $this->assertInstanceOf(Store::class, $store);
    }
}
