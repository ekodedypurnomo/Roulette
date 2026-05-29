# Roulette ORM

A sophisticated PHP ORM that bridges objects and relational databases with a clean, fluent API. Field values go through a full lifecycle pipeline — read → convert → validate → write → render — giving you fine-grained control over every column in every model.

PHP 8.1+ · Framework agnostic · Zero migration files

---

## Features

- **Model-driven schema** — prototype declaration is the single source of truth for table structure
- **Field lifecycle pipeline** — reader, converter, validator, writer, renderer per field
- **Mass assignment protection** — `fillable: false` on sensitive fields prevents user data from overwriting them
- **Associations** — HasOne, HasMany, BelongsTo, BelongsToMany (many-to-many via pivot; `sync()` is transactional)
- **Global Query Scopes** — automatic WHERE constraints declared per model, bypassable per query
- **Pagination** — `paginate()` returns a `Paginator` with total, page, and navigation metadata
- **Bulk operations** — `insertOrIgnore()`, `upsert()`, `insertMany()`, `incrementWhere()`, `decrementWhere()`
- **Large datasets** — `chunk()` for batch processing, `cursor()` for generator-based streaming
- **Soft Deletes** — opt-in `SoftDeletable` trait; `destroy()` sets `deleted_at`, `restore()` reverts it (no-op if not trashed)
- **Authorization** — policy-based access control via `Actor`
- **Model Events** — `before:save`, `after:save`, `before:destroy`, `after:destroy`, `after:load`, `after:find`, and more; class-level (`Model::on()`) and instance-level (`$record->on()`)
- **Event Sourcing** — opt-in audit trail via `EventSourceable` trait
- **Schema migration** — `Schema::diff()` / `Schema::migrate()` without migration files
- **Computed fields** — virtual fields calculated at runtime, never persisted
- **N+1 Detection** — detects lazy-load loops during development
- **Framework agnostic** — adapters for Laravel 5–12, CodeIgniter 3–4, Phalcon 3–5, Symfony 4–7, Standalone PDO
- **Long-running process safe** — `Operation::clearLog()`, `N1Detector::fullReset()` for Octane/Swoole/RoadRunner

---

## Installation

```bash
composer require roulette/roulette
```

---

## Quick Start

```php
use Roulette\Model;
use Roulette\Model\Prototype;

class User extends Model
{
    static protected ?Prototype $prototype = null;

    static function init(): void
    {
        static::prototype([
            'table'   => 'users',
            'primary' => 'id',
            'autoId'  => true,
            'fields'  => [
                ['name' => 'id',    'update' => false],
                ['name' => 'name',  'type' => 'string'],
                ['name' => 'email', 'type' => 'email'],
            ],
        ]);
    }
}
```

```php
// Create — non-fillable fields in the array are silently ignored
$user = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$user->save();               // returns bool
$user->save(reload: false);  // skip post-save SELECT (faster for write-heavy paths)
$user->saveOrFail();         // throws ValidationException or QueryException on any failure

// Read
$user  = User::load('some-id');
$users = User::find(['active' => 1]);

// Update
$user->set('name', 'Alicia');
$user->save();

// Delete
$user->destroy();
```

---

## Documentation

| Topic | Description |
| ----- | ----------- |
| [Getting Started](docs/getting-started.md) | Installation, DB connection, first model |
| [Models](docs/model.md) | Prototype config, CRUD, pagination, bulk ops, soft deletes |
| [Fields](docs/fields.md) | Types, lifecycle, validators, computed fields |
| [Associations](docs/associations.md) | HasOne, HasMany, BelongsTo, BelongsToMany |
| [Query Builder](docs/query.md) | Fluent queries, scopes, chunk, cursor, upsert |
| [Collections](docs/collections.md) | Collection, ManagedCollection, Store API reference |
| [Authorization](docs/authorization.md) | Actor, Policy, can/able — policy inverted-logic explained |
| [Validators](docs/validators.md) | All 27 built-in validators with parameters and error messages |
| [Advanced](docs/advanced.md) | Schema migration, soft deletes, event sourcing, N+1 detection |
| [Tunel](docs/tunel.md) | Framework adapters and writing a custom adapter |

---

## License

© Eko Dedy Purnomo. See [LICENSE](LICENSE) for details.
