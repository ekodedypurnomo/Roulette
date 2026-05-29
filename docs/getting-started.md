---
title: Getting Started
nav_order: 2
---

# Getting Started

## Requirements

- PHP 8.1+
- PDO extension
- A supported database driver (SQLite, MySQL)

## Installation

```bash
composer require roulette/roulette
```

## Connecting to a Database

Roulette uses a **tunel** — an adapter that wraps your framework's DB connection.

### Auto-detected frameworks

For Laravel, CodeIgniter, and Phalcon, Roulette detects the running framework automatically:

```php
// Auto-detect framework and use its connection
Operation::useFrameworkTunel();
```

| Framework | Detection | Class |
| --- | --- | --- |
| Laravel 5–12, Lumen | `app()` helper + `Application` instance | `Roulette\Tunel\Laravel` |
| CodeIgniter 4 | `db_connect()` + `\CodeIgniter\CodeIgniter` | `Roulette\Tunel\CodeIgniter4` |
| CodeIgniter 3 | `get_instance()->db` | `Roulette\Tunel\Codeigniter3` |
| Phalcon 3/4/5 | DI container `db` service | `Roulette\Tunel\Phalcon` |

### Manual wiring (no auto-detect)

Symfony and standalone PDO require explicit registration:

```php
use Roulette\Query\Operation;
use Roulette\Tunel\Standalone;

// Standalone PDO (MySQL, PostgreSQL, SQLite, etc.)
$pdo   = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$tunel = Standalone::fromPdo($pdo);
Operation::setOperationTunel($tunel);
```

```php
use Roulette\Tunel\Symfony as SymfonyTunel;

// Symfony via Doctrine DBAL
$tunel = SymfonyTunel::fromConnection($doctrine->getConnection());
Operation::setOperationTunel($tunel);
```

For a complete reference of all adapters, driver internals, and how to write a custom tunel, see [docs/tunel.md](tunel.md).

## Defining Your First Model

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
            'autoId'  => true,   // auto-generate UUID on insert
            'fields'  => [
                ['name' => 'id',    'update' => false],
                ['name' => 'name',  'type' => 'string'],
                ['name' => 'email', 'type' => 'email'],
            ],
        ]);
    }
}

User::init();
```

## Creating the Table

```php
use Roulette\Schema;

// Inspect what would be generated
echo Schema::sql(User::class);
// → CREATE TABLE users (id TEXT PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL)

// Compare prototype vs live DB
$diff = Schema::diff(User::class);
// → ['table' => 'users', 'exists' => false, 'missing' => [...], 'extra' => []]

// Create/sync the table
Schema::migrate(User::class);
```

## Basic CRUD

```php
// Create
$user = new User(['name' => 'Alice', 'email' => 'alice@example.com']);
$user->save();

// Read one
$user = User::load('user-id');

// Read many
$users = User::find(['active' => 1]);

// Update
$user->set('name', 'Alicia');
$user->save();

// Delete
$user->destroy();
```
