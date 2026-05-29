# Collections

Three related classes form the collection hierarchy:

```
Collection             — general-purpose typed array wrapper
  └── ManagedCollection — Collection + validation gates before insert/set
  └── Store             — Collection of Model records (returned by find())
```

---

## Collection

`Roulette\Collection` — array wrapper with functional iteration methods.

Implements `IteratorAggregate`, `JsonSerializable`, `Countable`, `Jsonable`, `Arrayable`.

### Construction

```php
$col = new Collection([1, 2, 3]);
$col = Collection::create(['a' => 1, 'b' => 2]); // factory (returns existing if already Collection)
```

### Reading

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `get($key, $default)` | mixed | Get by key, array of keys, or return default |
| `getAt(int $at)` | mixed | Get by numeric position |
| `first()` / `getFirst()` | mixed | First item |
| `last()` / `getLast()` | mixed | Last item |
| `getKeys()` | array | All keys |
| `getValues()` | array | All values |
| `getAll($config)` | array | All items; `$config = ['only' => [...]]` or `['except' => [...]]` |
| `toArray()` | array | Plain PHP array |
| `toJson(int $options)` | string | JSON string |
| `count()` / `getCount()` | int | Number of items |
| `isEmpty()` | bool | True when empty |

### Writing

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `set($key, $value)` | static | Set by key, or bulk-set from array/object |
| `setIf($key, $value)` | static | Set only when key already exists |
| `setIfNot($key, $value)` | static | Set only when key does not exist |
| `add(...$values)` | static | Append one or more items (variadic) |
| `addAll($items)` | static | Append all items from array or iterable |
| `fill($value, $keys)` | static | Fill specified keys with a value |
| `fillIf($value, $keys)` | static | Fill only existing keys |
| `fillIfNot($value, $keys)` | static | Fill only missing or null keys |
| `reset()` | static | Clear all items |
| `clear()` | static | Set all values to null |
| `clean()` | static | Remove all null items |

### Checking

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `has($key, $strict)` | bool | Key exists and (if strict) is not null |
| `hasKey($key)` | bool | Key exists (null values allowed) |
| `hasItem($item)` | bool | Value exists |
| `contain($value)` | bool | Value exists in collection |
| `containIn($values)` | bool | Any of the given values exist |
| `every(callable $fn)` | bool | All items pass callback |
| `some(callable $fn)` | bool | At least one item passes callback |

### Aggregates

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `max()` | mixed | Maximum value |
| `min()` | mixed | Minimum value |
| `sum()` | int\|float | Sum of all values |
| `average()` | int\|float\|null | Average of all values |

### Functional / Iteration

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `each(callable $fn, $reverse)` | bool | Iterate; callback returns false to stop |
| `invoke(callable $fn)` | static | Apply callback to every item, replace with return value |
| `filter($filter)` | static | Return new collection matching callback or array condition |
| `reject($condition)` | array | Remove matching items, return removed items |
| `map(callable $fn)` | static | Alias for `invoke` |
| `pipe(callable $fn)` | static | Pass collection to callback, return self |
| `pluck(string $value, $key)` | static | Extract a property from each item |
| `chunk($size)` | static | Split into chunks |
| `sort()` | static | Sort ascending |
| `shuffle()` | static | Randomise order |
| `reverse()` | static | Reverse order |
| `diff($items)` | static | Items not in given set |
| `diffKeys($items)` | static | Items whose keys are not in given set |
| `intersect($items)` | static | Items present in both collections |
| `intersectKey($items)` | static | Items whose keys are present in both |
| `flip($apply)` | static | Swap keys and values |
| `implode(string $glue)` | string | Join values with separator |
| `remap(array $map)` | static | Rename keys using a key map |

### Search / Random

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `search($value)` | mixed | Return key of value |
| `random($amount)` | mixed | One or more random items |

### Removing

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `remove($item)` | mixed | Remove first matching item; returns its key |
| `removeIf($item)` | array | Remove all matching items |
| `removeOn($key)` | mixed | Remove by key; returns removed value |
| `removeKey(...$keys)` | mixed | Remove by key (variadic alias for `removeOn`) |
| `removeIn($condition)` | array | Remove items in condition set |
| `removeEx($condition)` | array | Remove items NOT in condition set |
| `removeBy($condition)` | array | Remove items matching condition |

### Static Utilities

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `Collection::isAssoc($array)` | bool | True if array has non-sequential keys |
| `Collection::iterable($iterable)` | array | Convert Collection/Arrayable/JsonSerializable to array |
| `Collection::enum($var, $list, $default)` | mixed | Return value if in list, else default |
| `Collection::with($iterable, $callback)` | array | Create, pipe callback, return items |

---

## ManagedCollection

`Roulette\ManagedCollection` — extends Collection with validation gates.

Any attempt to `set()` or `add()` an item that fails validation is **silently ignored** — no exception is thrown.

### Validation config methods

```php
$col = new ManagedCollection();

// Accept only specific keys
$col->setAcceptableKeys(['name', 'email', 'age']);

// Accept only string values
$col->setAcceptableValues(fn($v) => is_string($v));

// Accept only keys matching a pattern
$col->setAcceptableKeys(new \Roulette\Regexp('/^field_/'));
```

`setAcceptableKeys()` and `setAcceptableValues()` each accept:
- **array** — whitelist of exact matches
- **callable** — `fn(mixed $keyOrValue): bool`
- **`Regexp` instance** — tested via `$regexp->test($keyOrValue)`

### Validation check methods

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `acceptableKey($key)` | bool | Whether key passes the configured rule |
| `acceptableValue($value)` | bool | Whether value passes the configured rule |
| `acceptable($key, $value)` | bool | Whether both key AND value pass |

### Hooks (for subclassing)

Override `protected $beforeSet` and `protected $beforeAdd` with a callable to intercept mutations:

```php
class StrictCollection extends ManagedCollection
{
    public function __construct()
    {
        $this->beforeSet = function(mixed &$value, array &$items): bool {
            // Return false to cancel the set
            return !empty($value);
        };
    }
}
```

---

## Store

`Roulette\Model\Store` — model-aware Collection. Returned by `Model::find()` and `Paginator::getStore()`.

Items are indexed by the record's primary key, not by numeric index.

### Construction

```php
$store = new Store(null, User::class); // empty store for User records
```

### Additional methods

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `add(...$records)` | static | Add Model instances; indexed by their ID |
| `commit()` | static | Call `commit()` on every record in the store |
| `revert()` | static | Call `revert()` on every record |
| `save()` | static | Call `save()` on every record |
| `destroy()` | static | Call `destroy()` on every record |
| `getData($fields)` | array | Extract `getData()` from every record |

```php
$users = User::find(['active' => 1]);

$users->count();      // number of records
$users->isEmpty();
$users->first();      // first User record
$users->each(fn($user) => echo $user->get('name'));
$users->getData();    // array of plain arrays
$users->save();       // save all records in one call
```

All `Collection` methods are also available on `Store`.
