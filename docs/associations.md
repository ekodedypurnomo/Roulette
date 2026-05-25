# Associations

## Declaring Associations

Associations are declared in the prototype under the `associations` key.

```php
static::prototype([
    'table'        => 'posts',
    'associations' => [
        'author'   => ['type' => 'belongsTo', 'model' => User::class,    'field' => 'user_id'],
        'comments' => ['type' => 'hasMany',   'model' => Comment::class, 'field' => 'post_id'],
        'meta'     => ['type' => 'hasOne',    'model' => PostMeta::class, 'field' => 'post_id'],
    ],
]);
```

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

## HasOne

One record has one related record. The related model holds the foreign key.

```php
// User has one Profile (Profile.user_id = User.id)
'profile' => ['type' => 'hasOne', 'model' => Profile::class, 'field' => 'user_id']
```

```php
$user    = User::load('user-id');
$profile = $user->lookup('profile'); // → single Profile model
```

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

## Lazy Loading & N+1

Associations are lazy-loaded on first `lookup()` call. Be careful inside loops:

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
N1Detector::setThreshold(2); // warn after 2 loads of same association
N1Detector::onDetect(fn($key, $count) => error_log("N+1: $key ($count hits)"));
```

See [Advanced → N+1 Detection](advanced.md#n1-detection) for details.

## Force Reload

Pass `true` to bypass the cached relation and re-query:

```php
$post->lookup('comments', true); // force fresh query
```
