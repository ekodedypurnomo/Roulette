# Authorization

Roulette uses a Policy + Actor system for record-level access control.

- **Policy** — a named rule declared on a model, evaluated as a callable.
- **Actor** — the entity performing an action; extends `Model` so it can be loaded from the DB.

---

## Declaring Policies

Policies are declared in the prototype under the `'policies'` key:

```php
static::prototype([
    'table'    => 'posts',
    'policies' => [
        'edit'   => fn($actor, $record) => $actor->getId() !== $record->get('user_id'),
        'delete' => fn($actor, $record) => $actor->get('role') !== 'admin',
    ],
]);
```

### Critical: inverted return logic

The policy callable is a **denial condition** — it answers "should this be denied?":

| Callable returns | `can()` returns | Meaning |
| ---------------- | --------------- | ------- |
| `true` | `false` | **DENY** — access blocked |
| `false` | `true` | **ALLOW** — access granted |

```php
// CORRECT — denies non-owners, allows owners
'edit' => fn($actor, $record) => $actor->getId() !== $record->get('user_id'),

// WRONG — would deny the owner and allow everyone else
'edit' => fn($actor, $record) => $actor->getId() === $record->get('user_id'),
```

### Default behavior

If no policy is registered for an action, `can()` returns `true` (open by default).

---

## Actor

`Roulette\Actor` — extends `Model`. Represents the entity performing an action.

```php
use Roulette\Actor;

// Create in memory
$actor = new Actor(['id' => 'user-123', 'role' => 'admin']);

// Load from DB (Actor is a full Model)
$actor = Actor::load('user-123');
```

### can() / able()

```php
$actor->can(string $policyName, mixed $recordOrClass, ...$extraArgs): bool
$actor->able(string $policyName, mixed $recordOrClass, ...$extraArgs): bool
```

`able()` is an identical alias for `can()` — use whichever reads more naturally.

```php
$post = Post::load('post-id');

if ($actor->can('edit', $post)) {
    $post->set('title', 'Updated');
    $post->save();
}

// Check against class (not instance)
if ($actor->can('create', Post::class)) {
    // ...
}

// Pass extra arguments to the policy callable
if ($actor->can('review', $post, 'draft', ['force' => true])) {
    // policy receives: ($actor, $post, 'draft', ['force' => true])
}
```

---

## Policy Management (static methods on Model)

| Method | Returns | Description |
| ------ | ------- | ----------- |
| `Model::getPolicies()` | Collection | All policies on this model |
| `Model::getPolicy(string $name)` | Policy\|null | Single policy by name |
| `Model::setPolicy(string $name, callable $fn)` | string | Register policy at runtime |
| `Model::isUsePolicy()` | bool | True when at least one policy registered |

```php
// Register at runtime
Post::setPolicy('publish', fn($actor, $post) => $actor->get('role') !== 'editor');

// Check registration
Post::isUsePolicy(); // true
Post::getPolicy('edit'); // Policy instance
```

---

## Multiple Assertions

Use a `Policy` object directly for multiple conditions (all must pass to allow):

```php
use Roulette\Model\Policy;

$policy = new Policy('edit');
$policy->addAssertion(fn($actor, $record) => $actor->get('role') !== 'admin');
$policy->addAssertion(fn($actor, $record) => $record->get('locked') !== 0);

Post::setPolicy('edit', $policy);
```

`assert()` iterates all assertions — if **any** returns `true` (deny), access is blocked.

---

## Usage Pattern

```php
use Roulette\Actor;

class Post extends Model
{
    static function init(): void
    {
        static::prototype([
            'table'    => 'posts',
            'fields'   => [...],
            'policies' => [
                // Allow owner to edit
                'edit'   => fn($actor, $post) => $actor->getId() !== $post->get('author_id'),
                // Allow only admins to delete
                'delete' => fn($actor, $post) => $actor->get('role') !== 'admin',
            ],
        ]);
    }
}

$actor = Actor::load($currentUserId);
$post  = Post::load($postId);

if ($actor->can('edit', $post)) {
    $post->set('content', $newContent);
    $post->save();
}

if ($actor->can('delete', $post)) {
    $post->destroy();
}
```
