# Roulette ORM

A sophisticated PHP ORM that bridges objects and relational databases with a clean, fluent API. Field values go through a full lifecycle pipeline — read → convert → validate → write → render — giving you fine-grained control over every column in every model.

**PHP 8.1+ · Framework agnostic · Zero migration files**

---

## Features

- **Model-driven schema** — prototype declaration is the single source of truth for table structure
- **Field lifecycle pipeline** — reader, converter, validator, writer, renderer per field
- **Global Query Scopes** — automatic WHERE constraints declared per model, bypassable per query
- **Associations** — HasOne, HasMany, BelongsTo with lazy loading
- **Authorization** — policy-based access control via `Actor`
- **Event Sourcing** — opt-in audit trail via `EventSourceable` trait
- **Schema migration** — `Schema::diff()` / `Schema::migrate()` without migration files
- **Computed fields** — virtual fields calculated at runtime, never persisted
- **N+1 Detection** — detects lazy-load loops during development

---

## Installation

```bash
composer require roulette/roulette
```

---

## Quick Start

```php
use Roulette\Model;

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
                ['name' => 'id', 'update' => false],
                ['name' => 'name',  'type' => 'string'],
                ['name' => 'email', 'type' => 'email'],
            ],
        ]);
    }
}
```

```php
// Create
$user = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$user->save();

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
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Installation, DB connection, first model |
| [Models](docs/model.md) | Prototype config, CRUD, caching |
| [Fields](docs/fields.md) | Types, lifecycle, validators, computed fields |
| [Associations](docs/associations.md) | HasOne, HasMany, BelongsTo |
| [Query Builder](docs/query.md) | Fluent queries, scopes |
| [Advanced](docs/advanced.md) | Schema migration, event sourcing, N+1 detection |

---

## License

© Eko Dedy Purnomo. See [LICENSE](LICENSE) for details.
