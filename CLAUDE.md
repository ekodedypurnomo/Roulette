Respond like caveman. No articles, no filler, no pleasantries. Short. Direct.
---
When writing code involving third-party libraries, use docfork.
---
## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

---

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
- **Framework Agnostic**: Adapters for Laravel 5-12, CodeIgniter 3-4, Phalcon 3-5, Symfony 4-7, Standalone (PDO)

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
| **Validator** | 25+ validator types (Email, UUID, Unique, Custom, etc.) |
| **Validation** | Validator registry & factory; maps string names to ValidatorAbstract instances |
| **Association** | Manages HasOne/HasMany/BelongsTo/BelongsToMany relationships |
| **Policy** | Authorization rules; tied to models and callable by Actor |
| **Collection** | Array-like object with functional methods (map, filter, each) |
| **ManagedCollection** | Extended Collection with key/value acceptance rules and validation callbacks |
| **Schema** | DDL generator — `sql()`, `diff()`, `migrate()` from model prototype |
| **N1Detector** | Opt-in N+1 query detector for association lazy-loading |
| **EventSourceable** | Opt-in audit trail trait — captures create/update/delete diffs |
| **SoftDeletable** | Opt-in soft-delete trait — sets `deleted_at` instead of hard-deleting |
| **Paginator** | Pagination result object with metadata (total, perPage, currentPage, lastPage) |
| **Rights** | Hex-based owner/group/other permission bits (rcud = read/create/update/destroy) |

### Directory Structure

```
src/
├── Model/
│   ├── Field/
│   │   ├── Field.php           # Field schema & transformations (incl. compute for virtual fields)
│   │   └── Validation.php      # Field-specific validator manager
│   ├── Association/
│   │   ├── AssociationAbstract.php
│   │   ├── HasOne.php
│   │   ├── HasMany.php
│   │   ├── BelongsTo.php
│   │   ├── BelongsToMany.php   # Many-to-many via pivot table
│   │   └── Relation.php        # Association value/result wrapper
│   ├── Operation/
│   │   ├── Permission.php      # Field-level permission (extends Data/Permission)
│   │   └── Rights.php          # Hex-based owner/group/other permission bits
│   ├── Cache.php               # Model instance caching
│   ├── Fields.php              # Typed collection manager for Field instances
│   ├── Paginator.php           # Pagination result with metadata
│   ├── Prototype.php           # Static model configuration
│   ├── Policy.php              # Authorization policies
│   ├── Properties.php
│   ├── Source.php              # Source declaration (table name, field mapping, joins)
│   ├── Store.php               # Collection of records
│   └── ViewOption.php          # Data output views
├── Query/
│   ├── Builder.php             # Query construction API
│   ├── Operation.php           # Query execution
│   ├── Condition.php           # WHERE condition wrapper (all operators incl. BETWEEN, LIKE, IN)
│   ├── RawExpression.php       # Raw SQL fragment (used by increment/decrement)
│   └── Option/
│       ├── Select.php
│       ├── Insert.php
│       ├── Update.php
│       ├── Delete.php
│       └── Mixin/              # Trait-based query option mixins
│           ├── HasGroup.php    # GROUP BY / HAVING
│           ├── HasLimit.php    # LIMIT / OFFSET
│           ├── HasOrder.php    # ORDER BY
│           ├── HasPatch.php    # UPDATE operations
│           ├── HasSelect.php   # SELECT columns
│           ├── HasTable.php    # FROM / TABLE
│           └── HasWhere.php    # WHERE conditions
├── Data/
│   ├── Value.php               # Single field state lifecycle
│   ├── Option.php              # Output formatting options
│   ├── Join.php                # Join configurations
│   └── Permission.php          # Field-level permissions
├── Exception/
│   ├── RouletteException.php   # Base exception
│   ├── AssociationException.php
│   ├── ModelNotFoundException.php
│   ├── QueryException.php
│   └── ValidationException.php
├── Validator/                  # 25+ validator classes
├── Mixin/
│   ├── Configurable.php
│   ├── Observable.php
│   ├── EventSourceable.php     # Opt-in audit trail trait
│   ├── SoftDeletable.php       # Opt-in soft-delete trait
│   └── HasModel.php            # Utility for model class reference
├── Contract/
│   ├── Jsonable.php
│   ├── Arrayable.php
│   ├── Tunel.php
│   └── Validatable.php         # Interface: test(mixed $value), getMessage(mixed $data)
├── Tunel/
│   ├── Assembly.php            # Abstract base: 3 swappable driver components
│   ├── TunelAbstract.php       # Legacy base adapter interface
│   ├── Tunels.php              # Registered adapter list
│   ├── FrameworkCheckers.php   # Framework auto-detection utility
│   ├── Laravel.php             # Laravel 5-12 + Lumen (preferred)
│   ├── Laravel5.php            # Legacy — kept for backwards compat
│   ├── Standalone.php          # PDO — no framework required
│   ├── CodeIgniter4.php        # CodeIgniter 4 (preferred)
│   ├── Codeigniter3.php        # Legacy — kept for backwards compat
│   ├── Phalcon.php             # Phalcon 3/4/5 (preferred)
│   ├── Phalcon3.php            # Legacy — kept for backwards compat
│   ├── Symfony.php             # Symfony 4-7 via Doctrine DBAL
│   └── Driver/                 # Modular driver components (Executor/Logger/Transaction)
│       ├── Executor.php
│       ├── Logger.php
│       ├── Transaction.php
│       ├── CodeIgniter3/
│       ├── CodeIgniter4/
│       ├── Illuminate/         # Laravel/Lumen
│       ├── Pdo/                # Standalone
│       ├── Phalcon/
│       └── Dbal/               # Symfony
├── Model.php                   # Main Model base class
├── Schema.php                  # DDL generator
├── N1Detector.php              # N+1 detection
├── Actor.php                   # Authorization agent
├── Collection.php              # Array wrapper
├── ManagedCollection.php       # Collection with validation callbacks
├── Validation.php              # Validator registry & factory
├── Regexp.php                  # Regex utility (test, replace)
├── Base.php                    # Top-level parent class
└── Template.php                # String parsing utilities
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
                'posts' => ['type' => 'hasMany', 'model' => 'App\Post'],
                'tags'  => ['type' => 'belongsToMany', 'model' => 'App\Tag', 'pivotTable' => 'post_tags']
            ],
            'policies' => [
                // true = DENY, false = ALLOW
                'edit' => function($actor, $record) {
                    return $actor->getId() !== $record->getId();
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

Check `src/Data/Value.php` for detailed lifecycle diagram.

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

Built-in validators in `src/Validator/`:

**Type Validators:** String, Integer, Float, Double, Boolean, DateTime, Date, Time, Numeric

**Format Validators:** Email, URL, UUID, Format (regex), Custom

**Range Validators:** MinValue, MaxValue, Below, Above, MinLength, MaxLength

**Logic Validators:** NotBlank, IsTrue, IsFalse, Unique, Inclusion, Exclusion, Nullable

Use `Validation` class to register custom validators globally:

```php
Validation::addValidator('mytype', App\Validator\MyType::class);
```

---

## Associations

### HasOne
```php
'profile' => ['type' => 'hasOne', 'model' => 'App\Profile', 'foreignKey' => 'user_id']
```

### HasMany
```php
'posts' => ['type' => 'hasMany', 'model' => 'App\Post', 'foreignKey' => 'user_id']
```

### BelongsTo

```php
'author' => ['type' => 'belongsTo', 'model' => 'App\User', 'foreignKey' => 'user_id']
```

### BelongsToMany

```php
'tags' => ['type' => 'belongsToMany', 'model' => 'App\Tag', 'pivotTable' => 'post_tags', 'foreignKey' => 'post_id', 'relatedKey' => 'tag_id']
```

Load and manage associated data:
```php
$user   = User::load('id');
$posts  = $user->lookup('posts');    // Store of Post models
$author = $post->lookup('author');   // single User model
$tags   = $post->lookup('tags');     // Store via pivot

// BelongsToMany management
$post->lookup('tags')->attach($tagId);
$post->lookup('tags')->detach($tagId);
$post->lookup('tags')->sync([$tagId1, $tagId2]);
```

---

## Query Builder

```php
User::query()
    ->select(['id', 'name'])
    ->where(['active' => true])
    ->orderBy(['name' => 'ASC'])
    ->groupBy(['role'])
    ->take(10)
    ->skip(5)
    ->execute();
```

Supports: select, where, groupBy, having, orderBy, take (limit), skip (offset), join, update, delete, increment, decrement

WHERE operators: `=`, `<`, `<=`, `>`, `>=`, `<>`, `IS`, `IS NOT`, `BETWEEN`, `NOT BETWEEN`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`

---

## Pagination

```php
$result = User::paginate(['perPage' => 15, 'page' => 2]);
// Returns Paginator object
$result->getStore();         // Store of User models
$result->getTotal();         // total records
$result->getPerPage();
$result->getCurrentPage();
$result->getLastPage();
$result->hasMorePages();
$result->isFirstPage();
$result->isLastPage();
```

---

## Soft Deletes

Add `SoftDeletable` trait to a model. Requires `deleted_at` column.

```php
use Roulette\Mixin\SoftDeletable;

class Post extends Model
{
    use SoftDeletable;
}

$post->destroy();          // sets deleted_at (soft delete)
$post->forceDelete();      // hard delete
$post->restore();          // clears deleted_at
$post->isTrashed();        // bool

Post::find([...]);         // auto-excludes soft-deleted (via __softDelete scope)
Post::withTrashed()->where([...])->get(); // includes soft-deleted
```

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

Located in `src/Tunel/`. New adapters use Assembly pattern (3 swappable drivers: Executor, Logger, Transaction).

| Adapter | Frameworks | Usage |
| ------- | ---------- | ----- |
| `Laravel.php` | Laravel 5-12, Lumen | Auto-detected via `Laravel::check()` |
| `Standalone.php` | None (raw PDO) | `Standalone::fromPdo($pdo)` |
| `CodeIgniter4.php` | CodeIgniter 4 | Auto-detected via `CodeIgniter4::check()` |
| `Symfony.php` | Symfony 4-7 | `Symfony::fromConnection($conn)` |
| `Phalcon.php` | Phalcon 3/4/5 | Auto-detected via `Phalcon::check()` |
| `Laravel5.php` | Laravel 5 only | Legacy — prefer `Laravel.php` |
| `Codeigniter3.php` | CodeIgniter 3 only | Legacy — prefer `CodeIgniter4.php` if upgrading |
| `Phalcon3.php` | Phalcon 3 only | Legacy — prefer `Phalcon.php` |

Driver components in `src/Tunel/Driver/`: `Illuminate/`, `Pdo/`, `CodeIgniter3/`, `CodeIgniter4/`, `Phalcon/`, `Dbal/` — each has `Executor`, `Logger`, `Transaction`.

---

## Exceptions

All exceptions extend `RouletteException` in `src/Exception/`:

| Exception | Thrown when |
| --------- | ----------- |
| `ModelNotFoundException` | `load()` finds no record |
| `AssociationException` | Association misconfigured |
| `QueryException` | Query execution fails |
| `ValidationException` | Validation error on save |

---

## Key Files to Know

| File | Purpose |
|------|---------|
| `Model.php` | Core ORM logic: CRUD, fields, associations, policies, global scopes |
| `Schema.php` | DDL generator: sql() / diff() / migrate() |
| `N1Detector.php` | N+1 detection: enable(), setThreshold(), onDetect() |
| `Mixin/EventSourceable.php` | Audit trail trait: save/destroy hooks, getHistory() |
| `Mixin/SoftDeletable.php` | Soft-delete trait: destroy/restore/forceDelete/withTrashed |
| `Data/Value.php` | Field value lifecycle management |
| `Actor.php` | Authorization checking (can/able methods) |
| `Collection.php` | Array wrapper with functional methods |
| `ManagedCollection.php` | Collection with validation callbacks (acceptableKey/Value) |
| `Validation.php` | Validator registry & custom validator registration |
| `Query/Builder.php` | Query construction API |
| `Query/Condition.php` | WHERE condition builder (all operators) |
| `Query/RawExpression.php` | Raw SQL embedding (used by increment/decrement) |
| `Model/Field/Field.php` | Field schema & transformations (incl. `compute` for virtual fields) |
| `Model/Paginator.php` | Pagination result object |
| `Model/Association/BelongsToMany.php` | Many-to-many via pivot (attach/detach/sync) |
| `Validator/ValidatorAbstract.php` | Base validator class |
| `Tunel/Assembly.php` | New adapter base class (modular driver pattern) |
| `Tunel/Standalone.php` | PDO adapter for framework-free usage |
| `Tunel/Symfony.php` | Symfony/Doctrine DBAL adapter |

---

## Testing

Tests at `tests/`. PHPUnit 10, SQLite in-memory — no external DB required.

Test structure:

- `tests/Support/DbTestCase.php` — Base class for DB-dependent tests
- `tests/Support/UserModel.php` — Shared model fixture
- `tests/Support/SqliteTunel.php` — SQLite tunel adapter for PHPUnit

Run all tests:

```bash
./vendor/bin/phpunit --no-coverage
# Expected: 390 tests, 0 failures, 0 skipped, 0 deprecations
```

---

## Common Tasks

**Define a new model:**
→ Extend `Roulette\Model`, implement `init()` static method

**Add field validation:**
→ Pass validators array in field config, check `src/Validator/`

**Register custom validator:**
→ `Validation::addValidator('name', MyValidator::class)`

**Check authorization:**
→ Use `$actor->can('policyName', $record)`

**Get modified fields:**
→ `$record->getModified()` returns array of changed field names

**Output API response:**
→ `$record->getData(['fields' => [...], 'relations' => [...]])`

**Add a global query scope:**
→ Declare `'scopes' => ['name' => fn($qop) => $qop->where([...])]` in prototype; bypass with `Model::withoutScope('name')::find()`

**Add a computed/virtual field:**
→ `['name' => 'full_name', 'compute' => fn($record) => ...]` in prototype fields; never persisted to DB

**Generate / sync DB schema:**
→ `Schema::sql(Model::class)` — DDL preview; `Schema::migrate(Model::class)` — apply to DB

**Listen to model lifecycle events:**
→ Class-level: `User::on('after:save', fn($record) => ...)` — fires for every User save
→ Instance-level: `$user->on('after:save', fn($record) => ...)` — fires for that record only
→ Return `false` from `before:save` / `before:destroy` to abort the operation
→ Available events: `before:save`, `after:save`, `before:create`, `after:create`, `before:update`, `after:update`, `before:destroy`, `after:destroy`, `before:validate`, `after:validate`, `after:load`, `after:find`, `after:reload`

**Add audit trail to a model:**
→ `use EventSourceable` in the model class; requires `model_events` table; read via `$record->getHistory()`

**Add soft deletes to a model:**
→ `use SoftDeletable` in the model class; requires `deleted_at` column

**Add many-to-many association:**
→ `'type' => 'belongsToMany'` in prototype; specify `pivotTable`, `foreignKey`, `relatedKey`

**Paginate query results:**
→ `Model::paginate(['perPage' => 15, 'page' => 1])` — returns `Paginator` object

**Use without a framework:**
→ `Standalone::fromPdo($pdo)` — wire raw PDO connection

**Detect N+1 queries:**
→ `N1Detector::enable()` then `N1Detector::onDetect(fn($key, $count) => ...)` during development

---

## Author & License

© Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>

See LICENSE file for full copyright and license information.
