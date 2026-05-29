---
title: Home
nav_order: 1
---

# Roulette ORM

A sophisticated PHP ORM framework that bridges objects and relational databases.
Field values go through a full lifecycle pipeline — read → convert → validate → write → render —
giving you fine-grained control over every column in every model.

**PHP 8.1+** · **Framework agnostic** · **Zero migration files**

```bash
composer require roulette/roulette
```

---

## Documentation

| Topic | Description |
| ----- | ----------- |
| [Getting Started](getting-started.html) | Installation, DB connection, first model |
| [Models](model.html) | Prototype config, CRUD, pagination, bulk ops, soft deletes |
| [Fields](fields.html) | Types, lifecycle, validators, computed fields, mass assignment |
| [Associations](associations.html) | HasOne, HasMany, BelongsTo, BelongsToMany |
| [Query Builder](query.html) | Fluent queries, scopes, chunk, cursor, upsert |
| [Collections](collections.html) | Collection, ManagedCollection, Store API reference |
| [Authorization](authorization.html) | Actor, Policy, can/able |
| [Validators](validators.html) | All 27+ built-in validators |
| [Advanced](advanced.html) | Schema migration, soft deletes, event sourcing, N+1 detection |
| [Framework Adapters](tunel.html) | Laravel, CodeIgniter, Phalcon, Symfony, Standalone PDO |

---

## Quick Example

```php
use Roulette\Model;

class User extends Model
{
    static function init(): void
    {
        static::prototype([
            'table'  => 'users',
            'primary' => 'id',
            'autoId' => true,
            'fields' => [
                ['name' => 'id',    'update' => false],
                ['name' => 'name',  'type' => 'string'],
                ['name' => 'email', 'type' => 'email'],
                ['name' => 'role',  'type' => 'string', 'fillable' => false],
            ],
        ]);
    }
}

// Create
$user = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$user->save();

// Query
$users = User::query()->where(['active' => true])->orderBy(['name' => 'ASC'])->get();

// Eager load
$users = User::with('posts')->where(['active' => true])->get();
```

---

## Features

- **Field lifecycle pipeline** — reader, converter, validator, writer, renderer per field
- **Mass assignment protection** — `fillable: false` guards sensitive fields
- **Associations** — HasOne, HasMany, BelongsTo, BelongsToMany with transactional sync()
- **Global Query Scopes** — automatic WHERE constraints, bypassable per query
- **Soft Deletes** — opt-in via `SoftDeletable` trait
- **Model Events** — class-level and instance-level lifecycle hooks
- **Authorization** — policy-based access control via `Actor`
- **Schema generator** — `Schema::diff()` / `Schema::migrate()` without migration files
- **N+1 Detection** — detects lazy-load loops during development
- **Framework adapters** — Laravel 5–12, CodeIgniter 4, Phalcon 3–5, Symfony 4–7, Standalone PDO
