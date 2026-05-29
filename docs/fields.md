---
title: Fields
nav_order: 4
---

# Fields

## Declaration

Fields are declared in the prototype's `fields` array. The simplest form is a string; the full form is an array with config keys.

```php
'fields' => [
    'name',                                    // shorthand — field name = column name
    ['name' => 'email', 'type' => 'email'],    // with type validation
    ['name' => 'age',   'type' => 'integer', 'nullable' => true],
    ['name' => 'id',    'update' => false],    // primary key — never updated
]
```

## Config Keys

| Key | Type | Description |
|-----|------|-------------|
| `name` | string | Field name (used in `get`/`set`) |
| `source` | string | DB column name (defaults to `name`) |
| `type` | string | Built-in type validator |
| `nullable` | bool | Whether null is valid (default: `true`) |
| `default` | mixed | Value when DB returns null |
| `private` | bool | Exclude from `getData()` output |
| `readOnly` | bool | Prevent `set()` |
| `update` | bool | Set `false` to skip on UPDATE (e.g. primary key) |
| `reader` | callable | Transform value when reading from DB |
| `writer` | callable | Transform value before writing to DB |
| `converter` | callable | Transform value before validation |
| `renderer` | callable | Transform value for display (`get()`) |
| `validators` | array | Additional validators |
| `compute` | callable | Virtual field — computed at runtime, never persisted |

## Built-in Types

| Type | Validates as |
|------|-------------|
| `string` | string |
| `integer` | integer |
| `float` | float |
| `double` | double |
| `numeric` | numeric |
| `boolean` | boolean |
| `email` | valid email address |
| `url` | valid URL |
| `uuid` | UUID v4 format |
| `date` | date string |
| `datetime` | datetime string |
| `time` | time string |

## Field Lifecycle

Every value goes through a pipeline on read and write:

```
DB → reader() → default → [raw / original]
                                │
                        user calls set()
                                │
                           converter()
                                │
                           validator()
                                │
                            [stored]
                                │
                    user calls get() → renderer() → display
                                │
                    model saves → writer() → DB
```

- **`original`** — value as loaded from DB (baseline for change detection)
- **`raw`** — current value after user sets it
- **`display`** — rendered value returned by `get()`

## Validators

Add inline validators in the field config:

```php
['name' => 'email', 'type' => 'email', 'validators' => [
    ['notblank'],
    ['maxlength', 255],
]]
```

For the full validator reference (parameters, error messages, custom validators), see [docs/validators.md](validators.md).

**Register a custom validator globally:**

```php
use Roulette\Validation;

Validation::addValidator('phone', App\Validator\PhoneValidator::class);
// then use: ['phone'] in any field's validators array
```

## Computed / Virtual Fields

A computed field is evaluated at runtime — it has no DB column and is never included in INSERT/UPDATE.

```php
[
    'name'    => 'full_name',
    'compute' => fn($record) => $record->get('first_name') . ' ' . $record->get('last_name'),
]
```

```php
$user->set('first_name', 'Ada');
$user->set('last_name', 'Lovelace');

$user->get('full_name'); // → 'Ada Lovelace' — reactive to set() changes
```

`Schema::sql()` and `Schema::migrate()` automatically skip computed fields.

## Lifecycle Hooks (per field)

```php
[
    'name'      => 'password',
    'writer'    => fn($v) => password_hash($v, PASSWORD_BCRYPT),  // hash before DB write
    'reader'    => fn($v) => $v,                                   // passthrough on read
    'renderer'  => fn($v) => '***',                                // hide in get()
]
```
