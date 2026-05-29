# Changelog

All notable changes to Roulette ORM are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Security

- **Identifier quoting in Pdo/Executor** — table names, column names, ORDER BY, GROUP BY, and WHERE field names are now properly quoted using DB-aware identifiers (MySQL: backtick, PostgreSQL/SQLite: ANSI double-quote). Prevents SQL errors on reserved-word column names and adds defense-in-depth against injection via dynamic field names.
- **`ManagesIncrements` field validation** — `increment()` / `decrement()` / `incrementWhere()` / `decrementWhere()` now throw `\InvalidArgumentException` if the field name is not defined in the model prototype, preventing arbitrary column references.
- **RawExpression cross-DB quoting** — increment/decrement now uses a `{col}` placeholder resolved to a properly quoted identifier by each executor (Pdo, Illuminate, DBAL, Phalcon, CodeIgniter4), fixing broken atomic increment on all non-SQLite drivers.
- **IS NULL fix in Pdo/Executor** — `whereNull()` / `whereNotNull()` now generate correct `IS NULL` / `IS NOT NULL` SQL instead of `field IS 'NULL'` (string literal).

### Added

- **`Field::$fillable`** — new field config option (`'fillable' => false`) protects sensitive fields (e.g. `role`, `is_admin`) from mass assignment via the model constructor. Default: `true` (backward compatible).
- **`ManagesCache::removeFromCache()`** — removes a record from the model instance cache; called automatically on `destroy()` so stale cached entries no longer survive after deletion.
- **`BelongsToMany::sync()` transactional** — `detach` + `attach` loop is now wrapped in a DB transaction; partial pivot state is no longer possible if an `attach` fails mid-sync.
- **`Schema` ADD COLUMN tests** — new test cases cover `migrate()` on an existing table (ADD COLUMN path), extra-column detection, and non-destructive behaviour (never drops columns).
- **`Operation::clearLog()`** — clears the operation log for long-running processes (Octane / Swoole / RoadRunner).
- **`N1Detector::fullReset()`** — resets all N+1 detector state (hits, handler, threshold) for between-request cleanup in long-running processes.
- **`AssociationAbstract::BATCH_FETCH_CHUNK_SIZE`** (protected const, 500) — eager-load batches are now chunked to avoid excessive `IN (...)` clause sizes on large parent stores.
- **`save($reload = true)`** — new `$reload` parameter; set to `false` to skip the post-save `SELECT` and reduce round-trips in write-heavy paths.
- **`saveOrFail()` throws `QueryException`** — previously returned `false` silently on DB failure; now throws `QueryException`, matching the method's contract (save or throw on any failure).
- **`SoftDeletable::restore()` guard** — returns `false` immediately if the record is not currently soft-deleted, avoiding a no-op `UPDATE`.
- **`@deprecated` on legacy adapters** — `Laravel5`, `Phalcon3`, and `Codeigniter3` now carry a `@deprecated` docblock pointing to the preferred replacement adapter.
- **DBAL 4 logging notice** — a one-time `E_USER_NOTICE` is emitted when DBAL 4 is detected and SQL logging is unavailable (`setSQLLogger` was removed in DBAL 4).

### Fixed

- **`Operation::isExecuted()`** — checked `$this->executed` which was never declared (always `null`); now checks `isset($this->executeTime[1])` which is set by the tunnel callback after execution. The `success()`, `failure()`, and `callback()` chain methods now work correctly.
- **`Policy::getAssertions()`** — renamed from `getAssetions()` (typo).
- **`ManagesQueries::load()`** return type changed from `mixed` to `?static`.
- **`ManagesPersistence::save()`** / `destroy()` return type changed from `mixed` to `bool`.
- **`EventSourceable::save()`** / `destroy()` return type changed from `mixed` to `bool`.
- **`SoftDeletable::destroy()`** return type changed from `mixed` to `bool`.
- **`ManagesQueries::find()` order mapping** — `$order` parameter is now passed through `mapToSource()` so model field names are mapped to source column names before building the ORDER BY clause.
- **`batchFetch()` scope bypass** — eager-load queries (`HasMany`, `HasOne`, `BelongsTo`, `BelongsToMany`) now call `applyScopes()`, so soft-deleted records no longer appear in eager-loaded relations.
- **`SqliteTunel` error type** — stores the `Throwable` object instead of `$e->getMessage()` (string), matching all production drivers.
- **`SqliteTunel` WHERE clause** — added support for BETWEEN, NOT BETWEEN, LIKE, NOT LIKE, generic comparison operators (`<`, `>`, `<=`, `>=`, `<>`), and ORDER BY in `buildSelect()`.

### Removed

- **`ManagesQueries::batchFindByField()`** — dead code; eager loading uses `AssociationAbstract::batchFetch()` exclusively.
- **`ManagesScopes::filter()`** — public no-op stub removed.

---

## [2.0.0] — Initial Release

- Core ORM: Model, Field, Value lifecycle, Prototype, Store, Collection
- Associations: HasOne, HasMany, BelongsTo, BelongsToMany
- Query builder: Builder, ModelQueryBuilder, fluent scope/eager-load API
- Validators: 25+ built-in validators
- Adapters: Laravel 5–12, CodeIgniter 3–4, Phalcon 3–5, Symfony 4–7, Standalone PDO
- Opt-in traits: SoftDeletable, EventSourceable, Observable
- Schema DDL generator: sql(), diff(), migrate()
- N+1 Detector
- Paginator, chunk, cursor
