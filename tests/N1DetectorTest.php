<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\N1Detector;
use Roulette\Tests\Support\DbTestCase;

/**
 * Inline fixtures: parent model (Post) with a HasMany to Comment.
 */
class Post extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class Comment extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class N1DetectorTest extends DbTestCase
{
    public static function setUpBeforeClass(): void
    {
        Post::prototype([
            'table'        => 'posts',
            'primary'      => 'id',
            'autoId'       => true,
            'fields'       => [
                ['name' => 'id', 'update' => false],
                'title',
            ],
            'associations' => [
                'comments' => ['type' => 'hasMany', 'model' => Comment::class, 'field' => 'post_id'],
            ],
        ]);

        Comment::prototype([
            'table'   => 'comments',
            'primary' => 'id',
            'autoId'  => true,
            'fields'  => [
                ['name' => 'id', 'update' => false],
                'post_id',
                'body',
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tunel->exec(
            'CREATE TABLE posts (id TEXT PRIMARY KEY, title TEXT)'
        );
        $this->tunel->exec(
            'CREATE TABLE comments (id TEXT PRIMARY KEY, post_id TEXT, body TEXT)'
        );

        N1Detector::disable();
        N1Detector::reset();
        N1Detector::setThreshold(2);
        N1Detector::onDetect(null);
    }

    public function testDisabledByDefault(): void
    {
        $this->assertFalse(N1Detector::isEnabled());
    }

    public function testEnableAndDisable(): void
    {
        N1Detector::enable();
        $this->assertTrue(N1Detector::isEnabled());

        N1Detector::disable();
        $this->assertFalse(N1Detector::isEnabled());
    }

    public function testNoHitsWhenDisabled(): void
    {
        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p1', 'First')");

        $post = Post::load('p1');
        $post->lookup('comments');

        $this->assertEmpty(N1Detector::getHits());
    }

    public function testRecordsHitOnLazyLoad(): void
    {
        N1Detector::enable();

        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p1', 'First')");

        $post = Post::load('p1');
        $post->lookup('comments');  // first lazy-load — 1 hit

        $hits = N1Detector::getHits();
        $this->assertArrayHasKey(Post::class . '.comments', $hits);
        $this->assertSame(1, $hits[Post::class . '.comments']);
    }

    public function testDetectsN1OnRepeatLoads(): void
    {
        N1Detector::enable();
        N1Detector::setThreshold(2);

        $warnings = [];
        N1Detector::onDetect(function(string $key, int $count) use (&$warnings) {
            $warnings[] = compact('key', 'count');
        });

        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p1', 'Post 1')");
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p2', 'Post 2')");

        $posts = Post::find();
        foreach ($posts as $post) {
            $post->lookup('comments');  // fires once per post → N+1
        }

        $this->assertNotEmpty($warnings);
        $this->assertSame(Post::class . '.comments', $warnings[0]['key']);
    }

    public function testResetClearsHits(): void
    {
        N1Detector::enable();

        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p1', 'First')");

        $post = Post::load('p1');
        $post->lookup('comments');

        $this->assertNotEmpty(N1Detector::getHits());

        N1Detector::reset();
        $this->assertEmpty(N1Detector::getHits());
    }

    public function testThresholdIsRespected(): void
    {
        N1Detector::enable();
        N1Detector::setThreshold(3);

        $warnings = [];
        N1Detector::onDetect(function(string $key, int $count) use (&$warnings) {
            $warnings[] = $count;
        });

        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p1', 'A')");
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p2', 'B')");
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p3', 'C')");

        foreach (['p1', 'p2', 'p3'] as $id) {
            Post::load($id)->lookup('comments');
        }

        // Warning fires on hit #3 and stays fired for each subsequent hit
        $this->assertNotEmpty($warnings);
        $this->assertSame(3, $warnings[0]);
    }

    public function testCustomHandlerReceivesKeyAndCount(): void
    {
        N1Detector::enable();
        N1Detector::setThreshold(2);

        $captured = null;
        N1Detector::onDetect(function(string $key, int $count) use (&$captured) {
            $captured = ['key' => $key, 'count' => $count];
        });

        $pdo = $this->tunel->getPdo();
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p1', 'A')");
        $pdo->exec("INSERT INTO posts (id, title) VALUES ('p2', 'B')");

        Post::load('p1')->lookup('comments');
        Post::load('p2')->lookup('comments');

        $this->assertNotNull($captured);
        $this->assertSame(Post::class . '.comments', $captured['key']);
        $this->assertSame(2, $captured['count']);
    }
}
