# Query Builder

## find()

The primary way to retrieve multiple records.

```php
// All records
$users = User::find();

// With conditions
$users = User::find(['active' => 1]);
$users = User::find(['age' => ['>' => 18], 'active' => 1]);

// Ordered, paginated
$users = User::find(
    condition: ['active' => 1],
    order:     ['name' => 'ASC'],
    take:      10,
    skip:      0
);
```

## load()

Load a single record by primary key (or arbitrary condition).

```php
$user = User::load('user-id');             // by primary key
$user = User::load(['email' => 'a@b.com']); // by condition
```

Returns `null` if not found.

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

## Where Conditions

```php
// Simple equality
->where(['status' => 'active'])

// Operators
->where(['age' => ['>'  => 18]])
->where(['age' => ['>=' => 21]])
->where(['age' => ['<'  => 65]])
->where(['name' => ['LIKE' => '%alice%']])

// OR condition
->orWhere(['role' => 'admin'])

// NULL checks
->whereNull('deleted_at')
->whereNotNull('verified_at')

// IN / NOT IN
->whereIn('status', ['active', 'pending'])
->whereNotIn('role', ['banned'])

// Grouped conditions
->where(function($q) {
    $q->where(['a' => 1])->orWhere(['b' => 2]);
})
```

## Global Query Scopes

Scopes are automatic WHERE constraints declared in the prototype. They apply to every `find()` and `load()` call automatically.

```php
static::prototype([
    'scopes' => [
        'active'  => fn($qop) => $qop->where(['deleted' => 0]),
        'visible' => fn($qop) => $qop->where(['published' => 1]),
    ],
]);
```

```php
User::find();                               // → WHERE deleted = 0 AND published = 1
User::withoutScope('active')::find();       // → WHERE published = 1 only
User::withoutScope(['active', 'visible'])::find(); // → no scope
User::withoutScopes()::find();              // → bypass all scopes
```

Scopes are consumed after one query — `withoutScope()` never leaks to the next call.

## Result Collections

`find()` returns a `Store` (extends `Collection`):

```php
$users = User::find();

$users->getCount();              // number of records
$users->isEmpty();               // bool
$users->first();                 // first record
$users->last();                  // last record

$users->each(function($user) {
    echo $user->get('name');
});

$users->toArray();               // array of model instances
```
