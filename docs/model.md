---
title: Models
nav_order: 3
---

# Models

## Prototype Configuration

Every model declares its schema via `prototype()`. This is the single source of truth — no separate migration files needed.

```php
static::prototype([
    'table'        => 'users',          // DB table name
    'primary'      => 'id',             // primary key field name
    'autoId'       => true,             // auto-generate UUID on insert
    'fields'       => [...],            // field declarations
    'associations' => [...],            // relationships
    'policies'     => [...],            // authorization rules
    'scopes'       => [...],            // global query constraints
]);
```

---

## CRUD Operations

### Create

```php
$user = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$user->save();

echo $user->getId(); // auto-generated UUID
```

With a result callback:

```php
$user->save(function(bool $success, $operation, $record) {
    if ($success) {
        // post-save logic
    }
});
```

### Read

```php
// Load by primary key
$user = User::load('user-id');

// Find with conditions
$users = User::find(['active' => 1]);
$users = User::find(['age' => ['>' => 18]], ['name' => 'ASC'], $take = 10, $skip = 0);

// Fluent query builder
User::query()
    ->where(['active' => 1])
    ->orderBy(['name' => 'ASC'])
    ->take(10)
    ->execute();
```

### Update

```php
$user = User::load('user-id');
$user->set('name', 'Alicia');
$user->save();
```

Only modified fields are sent to the database.

### Delete

```php
$user->destroy(); // hard-delete by default
                  // soft-delete if SoftDeletable trait is applied
```

---

## Tracking Changes

```php
$user = User::load('user-id');
$user->set('name', 'New Name');

$user->getModified();   // ['name'] — fields changed since load
$user->isModified();    // true if any field has changed
$user->isAlive();       // true if record exists in DB
```

Revert uncommitted changes:

```php
$user->revert(); // undo all unsaved changes
```

---

## Counting

```php
$total  = User::count();                 // count all rows
$active = User::count(['active' => 1]); // count with condition
```

Respects global scopes. Use `withoutScope()` to bypass.

---

## Pagination

```php
$result = User::paginate(perPage: 15, page: 1);
$result = User::paginate(15, 2, condition: ['active' => 1], order: ['name' => 'ASC']);

$result->getStore();       // Store of records for this page
$result->getTotal();       // total matching rows
$result->getPerPage();
$result->getCurrentPage();
$result->getLastPage();
$result->hasMorePages();   // bool
$result->isFirstPage();    // bool
$result->isLastPage();     // bool
```

---

## Bulk Operations

### insertOrIgnore()

Insert multiple rows, silently skipping any that violate a unique constraint.

```php
$inserted = User::insertOrIgnore([
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob',   'email' => 'bob@example.com'], // skipped if email exists
]);
```

Returns the count of successfully inserted rows.

### upsert()

Insert or update rows on conflict.

```php
$affected = User::upsert(
    rows:         [['email' => 'alice@example.com', 'name' => 'Alice', 'score' => 10]],
    uniqueFields: ['email'],          // conflict detection key(s)
    updateFields: ['name', 'score'],  // fields to update on conflict (omit = all non-key)
);
```

Returns total rows inserted + updated.

### insertMany()

Bulk-insert an array of field-value maps (no conflict handling).

```php
User::insertMany([
    ['name' => 'Charlie', 'email' => 'c@example.com'],
    ['name' => 'Diana',   'email' => 'd@example.com'],
]);
```

---

## increment() / decrement()

Atomically adjust a numeric column — emits `SET col = col ± n` without a read-modify-write cycle.

```php
$post->increment('views');       // views = views + 1
$post->increment('views', 5);   // views = views + 5
$post->decrement('stock');       // stock = stock - 1

// Returns $this for chaining
$post->increment('views')->decrement('remaining');
```

Both methods refresh the in-memory value. For bulk adjustments across rows, see [Query Builder → increment/decrement](query.md#increment--decrement).

---

## Soft Deletes

Apply the `SoftDeletable` trait to any model. Requires a `deleted_at` column.

```php
use Roulette\Mixin\SoftDeletable;

class Post extends Model
{
    use SoftDeletable;

    static function init(): void
    {
        static::prototype([
            'fields' => [
                // ...
                ['name' => 'deleted_at', 'nullable' => true, 'update' => true],
            ],
        ]);
    }
}
```

```php
$post->destroy();          // sets deleted_at to now (soft delete)
$post->forceDelete();      // permanently removes the row
$post->restore();          // clears deleted_at
$post->isTrashed();        // true when deleted_at is not null

Post::find();                      // auto-excludes soft-deleted rows
Post::withTrashed()::find();       // includes soft-deleted rows
```

Full reference: [Advanced → Soft Deletes](advanced.md#soft-deletes).

---

## Caching

Model instances are cached by default (keyed by `ClassName-id`). Control via:

```php
// Disable caching for a model class
static protected bool $useCache = false;

// Manual cache operations
User::storeToCache($record);
User::fetchFromCache('user-id');
User::isUseCache(); // check whether caching is enabled
```

---

## Serialization

```php
$user->getData();                               // all fields as associative array
$user->getData(['fields' => ['name', 'email']]); // specific fields
$user->getData(['relations' => ['posts']]);      // include eager-loaded relations
$user->toJson();                                // JSON string
```
