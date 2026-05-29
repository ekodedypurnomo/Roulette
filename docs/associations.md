---
title: Associations
nav_order: 5
---

# Associations

## Declaring Associations

Associations are declared in the prototype under the `associations` key.

```php
static::prototype([
    'table'        => 'posts',
    'associations' => [
        'author'   => ['type' => 'belongsTo',    'model' => User::class,    'field' => 'user_id'],
        'comments' => ['type' => 'hasMany',       'model' => Comment::class, 'field' => 'post_id'],
        'meta'     => ['type' => 'hasOne',        'model' => PostMeta::class, 'field' => 'post_id'],
        'tags'     => ['type' => 'belongsToMany', 'model' => Tag::class,     'pivotTable' => 'post_tags', 'foreignKey' => 'post_id', 'relatedKey' => 'tag_id'],
    ],
]);
```

---

## HasMany

One record has many related records. The related model holds the foreign key.

```php
// Post has many Comments (Comment.post_id = Post.id)
'comments' => ['type' => 'hasMany', 'model' => Comment::class, 'field' => 'post_id']
```

```php
$post     = Post::load('post-id');
$comments = $post->lookup('comments'); // → Store of Comment models
```

---

## HasOne

One record has one related record. The related model holds the foreign key.

```php
// User has one Profile (Profile.user_id = User.id)
'profile' => ['type' => 'hasOne', 'model' => Profile::class, 'field' => 'user_id']
```

```php
$user    = User::load('user-id');
$profile = $user->lookup('profile'); // → single Profile model (or null)
```

---

## BelongsTo

This model holds the foreign key; it "belongs to" the parent.

```php
// Post belongs to User (Post.user_id → User.id)
'author' => ['type' => 'belongsTo', 'model' => User::class, 'field' => 'user_id']
```

```php
$post   = Post::load('post-id');
$author = $post->lookup('author'); // → single User model
```

---

## BelongsToMany

Many-to-many relationship through a pivot (join) table. Neither model holds the foreign key directly — a pivot table connects them.

### Config keys

| Key | Required | Description |
| --- | -------- | ----------- |
| `type` | yes | `'belongsToMany'` |
| `model` | yes | Related model class |
| `pivotTable` | yes | Name of the pivot/join table |
| `foreignKey` | yes | Column in pivot that points to this model's PK |
| `relatedKey` | yes | Column in pivot that points to the related model's PK |

```php
// Post belongs to many Tags via post_tags pivot
'tags' => [
    'type'        => 'belongsToMany',
    'model'       => Tag::class,
    'pivotTable'  => 'post_tags',
    'foreignKey'  => 'post_id',
    'relatedKey'  => 'tag_id',
]
```

### Reading

```php
$post = Post::load('post-id');
$tags = $post->lookup('tags'); // → Store of Tag models
```

### Managing the pivot

```php
$post = Post::load('post-id');

// Attach one related record
$post->lookup('tags')->attach($tagId);

// Detach one related record
$post->lookup('tags')->detach($tagId);

// Replace all related records in one call
$post->lookup('tags')->sync([$tagId1, $tagId2, $tagId3]);
// → inserts rows for IDs not yet present, deletes rows no longer in the list
```

### Force reload

Pass `true` to bypass the cached relation and re-query:

```php
$post->lookup('tags', true); // fresh query
```

---

## Lazy Loading & N+1

All associations are lazy-loaded on the first `lookup()` call. Be careful inside loops:

```php
// N+1 problem — one query per post
foreach (Post::find() as $post) {
    $post->lookup('comments'); // fires SELECT for each post
}
```

Enable N+1 detection during development:

```php
use Roulette\N1Detector;

N1Detector::enable();
N1Detector::setThreshold(2); // warn after 2 loads of the same association key
N1Detector::onDetect(fn($key, $count) => error_log("N+1: $key ($count hits)"));
```

See [Advanced → N+1 Detection](advanced.md#n1-detection) for full reference.

---

## Force Reload

Pass `true` to any `lookup()` call to bypass the in-memory cache and re-query:

```php
$user->lookup('posts', true); // ignores cached result, runs fresh SELECT
```
