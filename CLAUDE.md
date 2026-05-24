# Roulette — PHP ORM Framework

**Version:** 2.0.0  
**Author:** Eko Dedy Purnomo  
**Type:** Object-Relational Mapping (ORM) Library

## Overview

Roulette is a sophisticated PHP ORM framework that bridges objects and relational databases. It provides a clean, fluent API for database operations while maintaining strict control over field-level transformations, validation, and authorization.

### Core Philosophy

- **Field Lifecycle**: Every field value goes through reader→converter→validator→writer→renderer stages
- **Modified Tracking**: Automatic detection of field changes vs. database state
- **Authorization First**: Built-in policy system for access control at the model level
- **Framework Agnostic**: Adapters for Laravel 5, CodeIgniter 3, Phalcon 3

---

## Architecture

### Key Components

| Component | Purpose |
|-----------|---------|
| **Model** | Represents a database record as an object with CRUD operations |
| **Field** | Defines field schema, validation rules, and value transformations |
| **Value** | Manages field state (raw, original, display) with lifecycle hooks |
| **Actor** | Authorization agent; checks policies before operations |
| **QueryBuilder** | Constructs SELECT/INSERT/UPDATE/DELETE queries fluently |
| **Validator** | ~20+ validator types (Email, UUID, Unique, Custom, etc.) |
| **Association** | Manages HasOne/HasMany relationships between models |
| **Policy** | Authorization rules; tied to models and callable by Actor |
| **Collection** | Array-like object with functional methods (map, filter, each) |

### Directory Structure

```
Roulette/
├── Model/               # Core model sub-classes
│   ├── Field/          # Field definition & validation
│   ├── Association/    # HasOne, HasMany, BelongsTo relationships
│   ├── Cache.php       # Model instance caching
│   ├── Prototype.php   # Static model configuration
│   ├── Policy.php      # Authorization policies
│   ├── Store.php       # Collection of records
│   └── ViewOption.php  # Data output views
├── Query/              # Query building & execution
│   ├── Builder.php     # Query construction API
│   ├── Operation.php   # Query execution
│   └── Option/         # SELECT, INSERT, UPDATE, DELETE
├── Data/               # Field value management
│   ├── Value.php       # Single field state lifecycle
│   ├── Option.php      # Output formatting options
│   ├── Join.php        # Join configurations
│   └── Permission.php  # Field-level permissions
├── Validator/          # Validation rules
├── Tunel/              # Framework adapters
│   ├── FrameworkCheckers.php  # Framework detection utility
│   ├── Tunels.php             # Registered adapter list
│   ├── TunelAbstract.php      # Base adapter interface
│   ├── Laravel5.php           # Laravel 5 integration
│   ├── Codeigniter3.php       # CodeIgniter 3 integration
│   └── Phalcon3.php           # Phalcon 3 integration
├── Contract/           # Interfaces (Jsonable, Arrayable)
├── Mixin/              # Traits (Observable, Configurable)
├── Model.php           # Main Model base class (CRUD, fields, associations, policies)
├── Actor.php           # Authorization agent
├── Collection.php      # Array wrapper
├── Base.php            # Top-level parent class
└── Template.php        # String parsing utilities
```

---

## Usage Patterns

### Model Definition

```php
use Roulette\Model;

class User extends Model
{
    static function init()
    {
        static::prototype([
            'table' => 'users',
            'primary' => 'id',
            'autoId' => true,
            'fields' => [
                'id',
                'name' => ['type' => 'string'],
                'email' => ['type' => 'email'],
                'age' => ['type' => 'integer', 'nullable' => true]
            ],
            'associations' => [
                'posts' => ['type' => 'hasMany', 'model' => 'App\Post']
            ],
            'policies' => [
                'edit' => function($actor, $record) {
                    return $actor->getId() === $record->getId();
                }
            ]
        ]);
    }
}
```

### CRUD Operations

```php
// Create
$user = new User(['name' => 'John', 'email' => 'john@example.com']);
$user->save();

// Read
$user = User::load('user-id');
$users = User::find(['age' => ['>' => 18]]);

// Update
$user->set('name', 'Jane');
$user->save();

// Delete
$user->destroy();
```

### Authorization

```php
$actor = new Actor(['id' => 'admin-id']);
$user = User::load('user-id');

if ($actor->can('edit', $user)) {
    // Allowed
}
```

### Field Lifecycle

Value flow for a field:

1. **Read from DB** → `reader()` → apply `default` if null
2. **Store** → `raw` (user changes) vs `original` (DB state)
3. **Validate** → `converter()` → `validator()`
4. **Display** → `renderer()` → `display`
5. **Persist** → `writer()` → DB

Check `Roulette/Data/Value.php` for detailed lifecycle diagram.

---

## Field Value States

A field maintains multiple internal states:

- **`original`**: Value from database (baseline)
- **`raw`**: Current user-modified value
- **`display`**: Rendered value for output
- **`modified`**: `raw !== original` (change detection)
- **`valid`**: Validation status + error messages

Methods:
- `setOriginal()` - Set value from database
- `setValue()` - User sets value (triggers convert/validate)
- `commit()` - Mark `raw` as `original` (syncs to DB)
- `revert()` - Revert `raw` back to `original`
- `validate()` - Run validators on current `raw`

---

## Validators

Built-in validators in `Roulette/Validator/`:

**Type Validators:** String, Integer, Float, Double, Boolean, DateTime, Date, Time, Numeric

**Format Validators:** Email, URL, UUID, Format (regex), Custom

**Range Validators:** MinValue, MaxValue, Below, Above, MinLength, MaxLength

**Logic Validators:** NotBlank, IsTrue, IsFalse, Unique, Inclusion, Exclusion, Nullable

---

## Associations

### HasOne
```php
// User has one Profile (Profile.user_id = User.id)
'profile' => ['type' => 'hasOne', 'model' => 'App\Profile', 'foreignKey' => 'user_id']
```

### HasMany
```php
// User has many Posts (Post.user_id = User.id)
'posts' => ['type' => 'hasMany', 'model' => 'App\Post', 'foreignKey' => 'user_id']
```

### BelongsTo

```php
// Post belongs to User (Post.user_id → User.id)
'author' => ['type' => 'belongsTo', 'model' => 'App\User', 'foreignKey' => 'user_id']
```

Load associated data:
```php
$user = User::load('id');
$posts  = $user->lookup('posts');   // Store of Post models
$author = $post->lookup('author');  // single User model
```

---

## Query Builder

```php
User::query()
    ->select(['id', 'name'])
    ->where(['active' => true])
    ->orderBy(['name' => 'ASC'])
    ->take(10)
    ->skip(5)
    ->execute();
```

Supports: select, where, groupBy, having, orderBy, take (limit), skip (offset), join, update, delete

---

## Caching

Model instances are cached by default. Control via:

```php
User::isUseCache();           // Check if caching enabled
User::storeToCache($record);  // Manually cache
User::fetchFromCache($id);    // Fetch from cache
```

Cache ID format: `{className}-{recordId}`

---

## Framework Adapters

Located in `Roulette/Tunel/`:
- `TunelAbstract.php` - Base adapter interface
- `Laravel5.php` - Laravel 5 integration
- `Codeigniter3.php` - CodeIgniter 3 integration
- `Phalcon3.php` - Phalcon 3 integration

Adapters handle: database connections, query execution, transaction management.

---

## Key Files to Know

| File | Purpose |
|------|---------|
| `Model.php` | Core ORM logic: CRUD, fields, associations, policies |
| `Data/Value.php` | Field value lifecycle management |
| `Actor.php` | Authorization checking (can/able methods) |
| `Collection.php` | Array wrapper with functional methods |
| `Query/Builder.php` | Query construction API |
| `Model/Field/Field.php` | Field schema & transformations |
| `Validator/ValidatorAbstract.php` | Base validator class |

---

## Known Bugs

All previously documented bugs have been fixed on the `develop` branch.

---

## Testing

Tests are located at `tests/` (inside `v2/`). The suite uses PHPUnit 10 with an SQLite in-memory driver for DB-backed tests — no external database required.

Test structure:

- `tests/Support/DbTestCase.php` — Base class for DB-dependent tests (SQLite in-memory via PDO)
- `tests/Support/UserModel.php` — Shared model fixture
- `tests/Support/SqliteTunel.php` — SQLite tunel adapter for PHPUnit

Run all tests:

```bash
./vendor/bin/phpunit --no-coverage
# Expected: 357 tests, 0 failures, 0 skipped, 0 deprecations
```

---

## Common Tasks

**Define a new model:**
→ Extend `Roulette\Model`, implement `init()` static method

**Add field validation:**
→ Pass validators array in field config, check `Roulette/Validator/`

**Check authorization:**
→ Use `$actor->can('policyName', $record)`

**Get modified fields:**
→ `$record->getModified()` returns array of changed field names

**Output API response:**
→ `$record->getData(['fields' => [...], 'relations' => [...]])`

---

## Author & License

© Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>

See LICENSE file for full copyright and license information.
