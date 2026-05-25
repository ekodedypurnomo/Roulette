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
    'exists'  => false,     // whether table exists in DB
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
|----------|--------|-------|
| `string` | TEXT | VARCHAR(255) |
| `integer` | INTEGER | INT |
| `float` | REAL | FLOAT |
| `boolean` | INTEGER | TINYINT(1) |
| `email` | TEXT | VARCHAR(255) |
| `uuid` | TEXT | CHAR(36) |
| `date` | TEXT | DATE |
| `datetime` | TEXT | DATETIME |

---

## Event Sourcing

Add an automatic audit trail to any model by applying the `EventSourceable` trait.

### Setup

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
|-----------|---------|
| `create` | all fields with `from: null, to: <value>` |
| `update` | only changed fields with `from: <old>, to: <new>` |
| `delete` | empty payload `{}` |

### Reading History

```php
$user = User::load('user-id');
$user->set('name', 'New Name');
$user->save();

$history = $user->getHistory(); // → Collection, chronological

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

N1Detector::enable();           // off by default
N1Detector::setThreshold(2);   // warn after N loads of same association (default: 2)
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
        'edit'   => fn($actor, $record) => $actor->getId() === $record->get('user_id'),
        'delete' => fn($actor, $record) => $actor->get('role') === 'admin',
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
