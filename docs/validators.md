# Validators

Validators run during `save()` and `validate()` to check field values.

---

## Usage in field config

```php
'fields' => [
    ['name' => 'email', 'type' => 'email', 'validators' => [
        ['notblank'],
        ['maxlength', 255],
    ]],
    ['name' => 'age', 'type' => 'integer', 'validators' => [
        ['minvalue', 0],
        ['maxvalue', 150],
    ]],
    ['name' => 'status', 'validators' => [
        ['inclusion', ['active', 'inactive', 'banned']],
    ]],
    ['name' => 'slug', 'validators' => [
        ['format', '/^[a-z0-9-]+$/'],
    ]],
    ['name' => 'code', 'validators' => [
        ['custom', fn($v) => str_starts_with($v, 'USR-')],
    ]],
]
```

Syntax: `[$alias]` for no-param validators, `[$alias, $param]` for single-param ones.

---

## Built-in Validator Reference

### Type validators — no parameters

| Alias | Validates | Error message |
| ----- | --------- | ------------- |
| `string` | value is a string | value should be a string |
| `integer` | value is an integer | value should be an integer numeric |
| `float` | value is a float | value should be a float numeric |
| `double` | value is a double | value should be a double numeric |
| `numeric` | value is numeric (int or float string) | value should be a numeric |
| `boolean` | value is a boolean | value should be a boolean |
| `notblank` | value is not null and not empty string | value must not be blank |
| `istrue` | value is strictly `true` | value should be `true` |
| `isfalse` | value is strictly `false` | value should be `false` |

### Format validators — optional regex parameter

| Alias | Parameter | Validates | Error message |
| ----- | --------- | --------- | ------------- |
| `email` | optional regex | valid email address | value doesnt appear as valid Email format |
| `url` | optional regex | valid URL | value doesnt appear as valid Url format |
| `uuid` | optional regex | UUID v4 format | value doesnt appear as valid UUID format |
| `format` | regex string (required) | string matches regex | invalid format |

```php
['format', '/^\d{4}-\d{2}-\d{2}$/']   // date-like pattern
['email']                               // uses built-in email regex
```

### Date/time validators — optional format string

| Alias | Default format | Validates | Error message |
| ----- | -------------- | --------- | ------------- |
| `date` | `Y-m-d` | string matches PHP date format | value is not a valid date, expected format: {rule} |
| `datetime` | `Y-m-d H:i:s` | string matches PHP datetime format | value is not a valid datetime, expected format: {rule} |
| `time` | `H:i:s` | string matches PHP time format | value is not a valid time, expected format: {rule} |

```php
['date']                    // validates "2024-12-31"
['date', 'd/m/Y']           // validates "31/12/2024"
['datetime', 'Y-m-d H:i']  // validates "2024-12-31 23:59"
```

### Range validators — numeric parameter (required)

| Alias | Parameter | Validates | Error message |
| ----- | --------- | --------- | ------------- |
| `minvalue` | numeric | value ≥ param | minimum value is {param} |
| `maxvalue` | numeric | value ≤ param | maximum value is {param} |
| `above` | numeric | value > param | value should be greater than {param} |
| `below` | numeric | value < param | value should be less than {param} |
| `minlength` | int | string length ≥ param | minimum characters length is {param} |
| `maxlength` | int | string length ≤ param | maximum characters length is {param} |

### Logic validators

| Alias | Parameter | Validates | Error message |
| ----- | --------- | --------- | ------------- |
| `nullable` | bool (default: `true`) | if `false`: value must not be null | value can not null |
| `inclusion` | array | value is in the array | must be include in: {list} |
| `exclusion` | array | value is NOT in the array | must be exclude from: {list} |
| `unique` | callable `fn($v): bool` | callable returns true | value must be unique |
| `custom` | callable `fn($v): bool` | callable returns true | value does not passed validation |

```php
['nullable', false]                          // field must not be null
['inclusion', ['draft', 'published']]        // whitelist
['exclusion', ['root', 'admin']]             // blacklist
['unique', fn($v) => !User::load(['email' => $v])]
['custom', fn($v) => strlen($v) % 2 === 0]  // even-length string
```

---

## Registering Custom Validators

Register globally via `Validation::addValidator()`:

```php
use Roulette\Validation;

Validation::addValidator('phone', App\Validator\PhoneValidator::class);
```

Then use by alias in field config:

```php
['name' => 'mobile', 'validators' => [['phone']]]
```

### Implementing a custom validator

Custom validators must implement `Roulette\Contract\Validatable`:

```php
namespace App\Validator;

use Roulette\Contract\Validatable;

class PhoneValidator implements Validatable
{
    public function test(mixed $value): bool
    {
        return (bool) preg_match('/^\+?[0-9]{8,15}$/', (string) $value);
    }

    public function getMessage(mixed $data): string
    {
        return 'value must be a valid phone number';
    }
}
```

`test()` returns `true` if valid, `false` if invalid.
`getMessage()` receives the field data and returns the error string shown on failure.
