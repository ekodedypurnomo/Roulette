# Models

## Prototype Configuration

Every model declares its schema via `prototype()`. This is the single source of truth — no separate migration files needed.

```php
static::prototype([
    'table'   => 'users',          // DB table name
    'primary' => 'id',             // primary key field name
    'autoId'  => true,             // auto-generate UUID on insert
    'fields'  => [...],            // field declarations
    'associations' => [...],       // relationships
    'policies' => [...],           // authorization rules
    'scopes'  => [...],            // global query constraints
]);
```

## CRUD Operations

### Create

```php
$user = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$user->save();

echo $user->getId(); // auto-generated UUID
```

With a callback to detect success:

```php
$user->save(function(bool $success, $operation, $record) {
    if ($success) {
        // do something after save
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
$user->destroy();
```

## Tracking Changes

```php
$user = User::load('user-id');
$user->set('name', 'New Name');

$user->getModified();   // ['name'] — fields changed since load
$user->isAlive();       // true if record exists in DB
$user->isModified();    // true if any field has changed
```

Revert uncommitted changes:

```php
$user->revert();        // undo all unsaved changes
```

## Caching

Model instances are cached by default (keyed by `ClassName-id`). Control via:

```php
// Disable for a model class
static protected bool $useCache = false;

// Manual cache operations
User::storeToCache($record);
User::fetchFromCache('user-id');
```

## Serialization

```php
$user->getData();                              // all fields as array
$user->getData(['fields' => ['name', 'email']]); // specific fields
$user->toJson();                               // JSON string
```
