---
title: Framework Adapters
nav_order: 11
---

# Tunel — Framework Adapters

A **tunel** is the layer that connects Roulette to a database. It wraps your framework's existing connection so the ORM can execute queries without you writing any SQL or touching PDO directly.

---

## Supported Adapters

| Adapter | Auto-detect | Manual factory |
| --- | --- | --- |
| Laravel 5–12, Lumen | `Operation::useFrameworkTunel()` | — |
| CodeIgniter 4 | `Operation::useFrameworkTunel()` | — |
| CodeIgniter 3 | `Operation::useFrameworkTunel()` | — |
| Phalcon 3/4/5 | `Operation::useFrameworkTunel()` | — |
| Symfony (DBAL) | — | `Symfony::fromConnection($conn)` |
| Standalone PDO | — | `Standalone::fromPdo($pdo)` |

---

## Setup per Framework

### Laravel / Lumen (auto-detect)

No configuration needed. Call once during bootstrap:

```php
use Roulette\Query\Operation;

Operation::useFrameworkTunel();
```

Supports Laravel 5 through 12 and Lumen. Detection is capability-based — no version-number probing.

---

### CodeIgniter 4 (auto-detect)

```php
use Roulette\Query\Operation;

Operation::useFrameworkTunel();
```

Requires `db_connect()` to be available and `\CodeIgniter\CodeIgniter` to be loaded (standard CI4 bootstrap).

---

### CodeIgniter 3 (auto-detect)

```php
use Roulette\Query\Operation;

Operation::useFrameworkTunel();
```

Requires the CI super-object (`get_instance()`) with `$ci->db` already loaded.

---

### Phalcon 3/4/5 (auto-detect)

```php
use Roulette\Query\Operation;

Operation::useFrameworkTunel();
```

Requires a Phalcon DI container with a registered `db` service (`$di->get('db')`). Compatible with Phalcon 3, 4, and 5.

---

### Symfony via Doctrine DBAL (manual)

Symfony has no global app helper, so auto-detection is not available. Wire the tunel manually during service initialization:

```php
use Roulette\Query\Operation;
use Roulette\Tunel\Symfony as SymfonyTunel;

// $doctrine is \Doctrine\Bundle\DoctrineBundle\Registry
$tunel = SymfonyTunel::fromConnection($doctrine->getConnection());
Operation::setOperationTunel($tunel);
```

Compatible with DBAL 3.x and 4.x.

---

### Standalone PDO (manual)

For plain PHP, Slim, Mezzio, or any framework not listed above:

```php
use Roulette\Query\Operation;
use Roulette\Tunel\Standalone;

$pdo   = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$tunel = Standalone::fromPdo($pdo);
Operation::setOperationTunel($tunel);
```

Works with any PDO-supported engine (MySQL, PostgreSQL, SQLite, etc.).

---

## Transactions

All tunels expose the same transaction API through `Assembly`:

```php
$tunel = Standalone::fromPdo($pdo);
Operation::setOperationTunel($tunel);

$tunel->beginTransaction();

try {
    $user->save();
    $log->save();
    $tunel->commit();
} catch (\Throwable $e) {
    $tunel->rollback();
    throw $e;
}
```

---

## Architecture

Every tunel is assembled from three independently-swappable driver components:

```
Assembly (abstract base)
├── Executor   — translates Operation objects into DB calls, writes results back
├── Logger     — captures the executed SQL string for debugging
└── Transaction — wraps begin / commit / rollback
```

The `Assembly` class holds all orchestration logic. Framework entry points (e.g. `Laravel.php`) only need to instantiate the three matching drivers.

### Driver directories

```
src/Tunel/Driver/
├── Illuminate/     Laravel 5–12, Lumen  (Executor, Logger, Transaction)
├── Pdo/            Standalone PDO        (Executor, Logger, Transaction)
├── CodeIgniter3/   CodeIgniter 3         (Executor, Logger, Transaction)
├── CodeIgniter4/   CodeIgniter 4         (Executor, Logger, Transaction)
├── Phalcon/        Phalcon 3/4/5         (Executor, Logger, Transaction)
└── Dbal/           Doctrine DBAL / Symfony (Executor, Logger, Transaction)
```

---

## Writing a Custom Adapter

1. Implement the three driver interfaces in `src/Tunel/Driver/{YourFramework}/`:

```php
use Roulette\Query\Operation;
use Roulette\Tunel\Driver\Executor;

class MyExecutor implements Executor
{
    public function __construct(private mixed $db) {}

    public function select(Operation $operation): void { /* ... */ }
    public function insert(Operation $operation): void { /* ... */ }
    public function update(Operation $operation): void { /* ... */ }
    public function delete(Operation $operation): void { /* ... */ }
    public function query(Operation $operation): void  { /* ... */ }
    public function exists(Operation $operation): void { /* ... */ }
    public function truncate(Operation $operation): void { /* ... */ }
}
```

Each method must write `result`, `success`, `affectedRows`, and (on failure) `error` back onto the `$operation` object.

2. Create the entry point in `src/Tunel/MyFramework.php`:

```php
use Roulette\Tunel\Assembly;

class MyFramework extends Assembly
{
    static mixed $frameworkInfo = null;

    static function check(): bool
    {
        if (!/* detection logic */) return false;

        $db    = /* get connection */;
        $tunel = new static(
            new MyExecutor($db),
            new MyLogger($db),
            new MyTransaction($db),
        );

        static::$frameworkInfo = [
            'framework' => 'MyFramework',
            'version'   => '1.0',
            'tunel'     => $tunel,
        ];

        return true;
    }
}
```

3. Register it in `src/Tunel/Tunels.php` for auto-detection:

```php
return [
    'MyFramework' => \Roulette\Tunel\MyFramework::class,
    // ... existing entries
];
```

The first entry whose `check()` returns `true` becomes the active tunel.
