# Advanced

## Schema Migration

Roulette can generate and apply DDL from model prototypes — no separate migration files needed.

### Generate SQL

```php
use Roulette\Schema;

echo Schema::sql(User::class);
// → CREATE TABLE users (id TEXT PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL)

// Specify dialect explicitly
echo Schema::sql(User::class, 'mysql');
// → CREATE TABLE users (id CHAR(36) PRIMARY KEY, name VARCHAR(255) NOT NULL, ...)
```

### Inspect Differences

```php
$diff = Schema::diff(User::class);

// $diff structure:
[
    'table'   => 'users',
    'exists'  => false,     // whether the table exists in the DB
    'missing' => [          // columns in model but not in DB → need CREATE/ADD
        ['name' => 'email', 'type' => 'email', ...],
    ],
    'extra'   => [          // columns in DB but not in model (info only, never dropped)
        ['name' => 'legacy_column'],
    ],
]
```

### Apply Changes

```php
Schema::migrate(User::class);
// → Creates table if absent, or ALTER TABLE ADD COLUMN for each missing column.
// → Never drops columns or changes existing column types.
```

### Type Mapping

| ORM type | SQLite | MySQL |
| -------- | ------ | ----- |
| `string` | TEXT | VARCHAR(255) |
| `integer` | INTEGER | INT |
| `float` | REAL | FLOAT |
| `boolean` | INTEGER | TINYINT(1) |
| `email` | TEXT | VARCHAR(255) |
| `uuid` | TEXT | CHAR(36) |
| `date` | TEXT | DATE |
| `datetime` | TEXT | DATETIME |

---

## Soft Deletes {#soft-deletes}

Apply the `SoftDeletable` trait to any model to replace hard deletes with a timestamp flag.

### Setup

```php
use Roulette\Mixin\SoftDeletable;

class Post extends Model
{
    use SoftDeletable;

    static function init(): void
    {
        static::prototype([
            'table'  => 'posts',
            'fields' => [
                ['name' => 'id',         'update' => false],
                ['name' => 'title',      'type' => 'string'],
                ['name' => 'deleted_at', 'nullable' => true, 'update' => true],
            ],
        ]);
    }
}
```

The `deleted_at` column must be declared in the prototype **and** exist in the database table (e.g. `TEXT` or `DATETIME`, nullable).

### Behavior

| Method | Description |
| ------ | ----------- |
| `$post->destroy()` | Sets `deleted_at` to now — row stays in DB |
| `$post->forceDelete()` | Permanently removes the row |
| `$post->restore()` | Clears `deleted_at`, un-deleting the record |
| `$post->isTrashed()` | Returns `true` when `deleted_at` is not null |
| `Post::withTrashed()::find()` | Includes soft-deleted rows in results |
| `Post::find()` | Automatically excludes soft-deleted rows |

### How the scope works

`SoftDeletable` injects a scope named `__softDelete` that appends `WHERE deleted_at IS NULL` to every `find()`, `load()`, `count()`, and `paginate()` call. Bypass it with:

```php
Post::withTrashed()::find();        // all rows including deleted
Post::withTrashed()::count();       // count including deleted
Post::withTrashed()::load($id);     // load a specific soft-deleted record
```

`withTrashed()` is consumed after one query — it does not persist.

### Callbacks

`destroy()` and `forceDelete()` accept the same optional callback as the base `destroy()`:

```php
$post->destroy(function(bool $success, $operation, $record) {
    if ($success) {
        // post-delete logic
    }
});
```

---

## Event Sourcing

Add an automatic audit trail to any model by applying the `EventSourceable` trait.

### Configuration

```php
use Roulette\Mixin\EventSourceable;

class User extends Model
{
    use EventSourceable;
}
```

The events table must exist before any save/destroy:

```sql
CREATE TABLE model_events (
    id          TEXT PRIMARY KEY,
    model_class TEXT NOT NULL,
    record_id   TEXT NOT NULL,
    operation   TEXT NOT NULL,   -- 'create' | 'update' | 'delete'
    payload     TEXT NOT NULL,   -- JSON: { field: { from, to } }
    created_at  TEXT NOT NULL
)
```

Configure a custom table per model:

```php
static::prototype([
    'eventSourcing' => ['table' => 'user_audit_log'],
    // ...
]);
```

### What Gets Captured

| Operation | Payload |
| --------- | ------- |
| `create` | All fields with `from: null, to: <value>` |
| `update` | Only changed fields with `from: <old>, to: <new>` |
| `delete` | Empty payload `{}` |

### Reading History

```php
$user = User::load('user-id');
$user->set('name', 'New Name');
$user->save();

$history = $user->getHistory(); // → Collection, chronological order

foreach ($history as $event) {
    echo $event['operation'];          // 'create', 'update', 'delete'
    echo $event['created_at'];
    print_r($event['payload']);        // ['name' => ['from' => 'Old', 'to' => 'New']]
}
```

---

## N+1 Detection {#n1-detection}

Detect association lazy-load loops during development.

### Enable

```php
use Roulette\N1Detector;

N1Detector::enable();          // off by default
N1Detector::setThreshold(2);  // warn after N loads of the same association key (default: 2)
```

### Custom Handler

```php
N1Detector::onDetect(function(string $key, int $count) {
    // $key   = 'App\Post.comments'
    // $count = number of times loaded
    error_log("N+1 detected: $key loaded $count times");
});
```

Default behaviour (no handler): emits `E_USER_WARNING` via `trigger_error`.

### Inspect Hits

```php
$hits = N1Detector::getHits();
// → ['App\Post.comments' => 5, 'App\User.posts' => 3]

N1Detector::reset(); // clear counters
```

### Lifecycle

```php
N1Detector::enable();

foreach (Post::find() as $post) {
    $post->lookup('comments'); // ← detected on 2nd iteration
}

N1Detector::disable();
N1Detector::reset();
```

---

## Authorization

Control record access using policies and the `Actor` class.

```php
static::prototype([
    'policies' => [
        // Return true to DENY, false to ALLOW (inverted — callable is a "denial condition")
        'edit'   => fn($actor, $record) => $actor->getId() !== $record->get('user_id'),
        'delete' => fn($actor, $record) => $actor->get('role') !== 'admin',
    ],
]);
```

```php
use Roulette\Actor;

$actor = new Actor(['id' => 'user-123', 'role' => 'user']);
$post  = Post::load('post-id');

if ($actor->can('edit', $post)) {
    $post->set('title', 'Updated');
    $post->save();
}
```

If no policy is registered for an action, access is **open by default**.

---

## Large Dataset Processing

### chunk()

Process records in fixed-size batches. Useful when the full result set would exceed available memory.

```php
User::chunk(
    size:      100,
    callback:  function(Store $batch) {
        foreach ($batch as $user) {
            // process each record
        }
        // return false to stop early
    },
    condition: ['active' => 1],
    order:     ['id' => 'ASC']
);
```

Returns total number of records processed.

### cursor()

Yield records one at a time using a PHP `Generator`. Fetches data in internal pages but exposes a flat stream — best for very large tables.

```php
foreach (User::cursor(condition: ['active' => 1]) as $user) {
    echo $user->get('email') . PHP_EOL;
}
```

Optional third argument controls the internal batch size (default `100`):

```php
foreach (User::cursor(batchSize: 500) as $user) {
    // ...
}
```

---

## Exceptions

All Roulette exceptions extend `Roulette\Exception\RouletteException`.

| Exception | Thrown when |
| --------- | ----------- |
| `ModelNotFoundException` | `load()` finds no matching record |
| `AssociationException` | Association is misconfigured |
| `QueryException` | Query execution fails at the DB level |
| `ValidationException` | `save()` is called with invalid field values |

```php
use Roulette\Exception\ModelNotFoundException;
use Roulette\Exception\ValidationException;

try {
    $user = User::load('unknown-id');
} catch (ModelNotFoundException $e) {
    // handle not found
}

try {
    $user->save();
} catch (ValidationException $e) {
    $errors = $e->getErrors(); // field-keyed error messages
}
```
