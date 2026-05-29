# Query Builder

## find()

The primary way to retrieve multiple records.

```php
// All records
$users = User::find();

// With conditions
$users = User::find(['active' => 1]);
$users = User::find(['age' => ['>' => 18], 'active' => 1]);

// Ordered and paginated
$users = User::find(
    condition: ['active' => 1],
    order:     ['name' => 'ASC'],
    take:      10,
    skip:      0
);
```

Returns a `Store` — see [Result Collections](#result-collections).

---

## load()

Load a single record by primary key or arbitrary condition.

```php
$user = User::load('user-id');              // by primary key
$user = User::load(['email' => 'a@b.com']); // by condition
```

Returns `null` if not found.

---

## count()

Count rows matching a condition without loading any records.

```php
$total = User::count();                      // all rows
$active = User::count(['active' => 1]);      // with condition
```

Respects global scopes. Use `withoutScope()` to bypass them.

---

## paginate()

Execute a `COUNT` + `LIMIT/OFFSET` query pair and return a `Paginator`.

```php
$result = User::paginate(perPage: 15, page: 2);
$result = User::paginate(15, 2, condition: ['active' => 1], order: ['name' => 'ASC']);
```

`Paginator` methods:

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `getStore()` | Store | Records for this page |
| `getTotal()` | int | Total matching rows |
| `getPerPage()` | int | Page size |
| `getCurrentPage()` | int | 1-based current page number |
| `getLastPage()` | int | Last page number |
| `hasMorePages()` | bool | Whether a next page exists |
| `isFirstPage()` | bool | Whether this is page 1 |
| `isLastPage()` | bool | Whether this is the last page |

```php
$result = User::paginate(15, 1);

foreach ($result->getStore() as $user) {
    echo $user->get('name');
}

if ($result->hasMorePages()) {
    // fetch page 2...
}
```

---

## chunk()

Process large result sets in fixed-size batches without loading everything into memory.

```php
$processed = User::chunk(
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

echo "Processed: $processed rows";
```

- `$callback` receives a `Store` of records.
- Return `false` from the callback to stop iteration early.
- Returns the total number of records processed.

---

## cursor()

Return a `Generator` that yields one record at a time. Fetches data in internal batches but exposes a single-record interface — ideal for very large datasets where even chunked stores are too large.

```php
foreach (User::cursor(condition: ['active' => 1]) as $user) {
    echo $user->get('name') . PHP_EOL;
}
```

Optional `$batchSize` controls the internal fetch size (default `100`):

```php
foreach (User::cursor(batchSize: 500) as $user) {
    // ...
}
```

---

## Fluent Query Builder

For complex queries, use `query()`:

```php
$results = User::query()
    ->select(['id', 'name', 'email'])
    ->where(['active' => 1])
    ->where(['age' => ['>' => 18]])
    ->orderBy(['name' => 'ASC'])
    ->groupBy(['department'])
    ->take(20)
    ->skip(40)
    ->execute();
```

---

## Where Conditions

```php
// Equality
->where(['status' => 'active'])

// Comparison operators
->where(['age' => ['>'  => 18]])
->where(['age' => ['>=' => 21]])
->where(['age' => ['<'  => 65]])
->where(['age' => ['<=' => 64]])
->where(['age' => ['<>' => 0]])

// LIKE / NOT LIKE
->where(['name' => ['LIKE'     => '%alice%']])
->where(['name' => ['NOT LIKE' => '%spam%']])

// IN / NOT IN
->whereIn('status',    ['active', 'pending'])
->whereNotIn('role',   ['banned'])

// NULL checks
->whereNull('deleted_at')
->whereNotNull('verified_at')

// BETWEEN / NOT BETWEEN
->where(['age' => ['BETWEEN'     => [18, 65]]])
->where(['age' => ['NOT BETWEEN' => [0,  17]]])

// IS / IS NOT (for NULL-safe checks)
->where(['deleted_at' => ['IS'     => null]])
->where(['deleted_at' => ['IS NOT' => null]])

// OR
->orWhere(['role' => 'admin'])

// Grouped conditions
->where(function($q) {
    $q->where(['a' => 1])->orWhere(['b' => 2]);
})
```

---

## increment() / decrement()

Atomically adjust a numeric column — emits `SET col = col + n` without a read-modify-write cycle.

### On a single record

```php
$post = Post::load('post-id');

$post->increment('views');          // views = views + 1
$post->increment('views', 5);       // views = views + 5
$post->decrement('stock');          // stock = stock - 1
$post->decrement('stock', 10);      // stock = stock - 10
```

Both methods return `$this` for chaining and refresh the in-memory value.

### Across multiple rows

```php
// Increment all matching rows
Post::incrementWhere(['featured' => 1], 'view_count');
Post::incrementWhere(['featured' => 1], 'view_count', 5);

// Decrement all matching rows
Product::decrementWhere(['on_sale' => 1], 'stock', 3);
```

Both `incrementWhere` / `decrementWhere` return the number of affected rows.

---

## insertOrIgnore()

Bulk-insert rows, silently skipping any that violate a unique constraint.

```php
$inserted = User::insertOrIgnore([
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob',   'email' => 'bob@example.com'],  // skipped if email already exists
]);

echo "$inserted rows inserted";
```

Returns the count of successfully inserted rows.

---

## upsert()

Insert rows or update them on conflict.

```php
$affected = User::upsert(
    rows: [
        ['email' => 'alice@example.com', 'name' => 'Alice Updated', 'score' => 10],
        ['email' => 'new@example.com',   'name' => 'New User',      'score' => 0],
    ],
    uniqueFields: ['email'],          // conflict detection key(s)
    updateFields: ['name', 'score'],  // fields to update on conflict (omit = update all non-key)
);

echo "$affected rows inserted or updated";
```

- `$uniqueFields` — model field names that form the unique/conflict key.
- `$updateFields` — fields to update when a conflict occurs. Pass an empty array to update all non-key fields.
- Returns the total number of rows inserted **plus** updated.

---

## Global Query Scopes

Scopes are automatic WHERE constraints declared in the prototype. They apply to every `find()`, `load()`, `count()`, and `paginate()` call automatically.

```php
static::prototype([
    'scopes' => [
        'active'  => fn($qop) => $qop->where(['deleted' => 0]),
        'visible' => fn($qop) => $qop->where(['published' => 1]),
    ],
]);
```

```php
User::find();                                      // → WHERE deleted = 0 AND published = 1
User::withoutScope('active')::find();              // → WHERE published = 1 only
User::withoutScope(['active', 'visible'])::find(); // → no scopes
User::withoutScopes()::find();                     // → bypass all scopes
```

`withoutScope()` is consumed after one query — it never leaks to the next call.

---

## Result Collections

`find()` and `paginate()->getStore()` return a `Store` (extends `Collection`):

```php
$users = User::find();

$users->count();   // number of records (also: getCount())
$users->isEmpty(); // bool
$users->first();   // first record
$users->last();    // last record

$users->each(function($user) {
    echo $user->get('name');
});

$users->map(fn($u) => $u->get('email'));  // Collection of transformed values
$users->filter(fn($u) => $u->get('age') > 18);
$users->toArray();                         // plain PHP array of model instances
```
