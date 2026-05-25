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

Roulette uses a **tunel** — an adapter that wraps your framework's DB connection. For standalone use, register a PDO-based tunel directly:

```php
use Roulette\Query\Operation;
use Roulette\Tunel\SqliteTunel; // or your own adapter

$pdo   = new PDO('sqlite:database.sqlite');
$tunel = new SqliteTunel($pdo);

Operation::setOperationTunel($tunel);
```

For framework integrations, use the built-in adapters in `src/Tunel/`:

| Adapter | Class |
|---------|-------|
| Laravel 5 | `Roulette\Tunel\Laravel5` |
| CodeIgniter 3 | `Roulette\Tunel\Codeigniter3` |
| Phalcon 3 | `Roulette\Tunel\Phalcon3` |

```php
// Auto-detect framework and use its connection
Operation::useFrameworkTunel();
```

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
