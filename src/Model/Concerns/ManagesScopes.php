<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Query\ModelQueryBuilder;

trait ManagesScopes
{
    /**
     * Disable one or more named scopes for the next query.
     * Returns a ModelQueryBuilder for fluent chaining:
     *   User::withoutScope('active')->where('role', 'admin')->get()
     */
    static function withoutScope(string|array ...$names): ModelQueryBuilder
    {
        $flat = [];
        foreach ($names as $n) {
            $flat = array_merge($flat, is_array($n) ? $n : [$n]);
        }
        return static::query()->disableScopes($flat);
    }

    /**
     * Disable ALL scopes for the next query.
     * Returns a ModelQueryBuilder for fluent chaining:
     *   User::withoutScopes()->get()
     */
    static function withoutScopes(): ModelQueryBuilder
    {
        return static::query()->disableScopes(['*']);
    }

    /**
     * Eager-load one or more associations for the next query.
     * Returns a ModelQueryBuilder for fluent chaining:
     *   Post::with('comments')->where('published', 1)->get()
     */
    static function with(string|array $relations): ModelQueryBuilder
    {
        $flat = is_array($relations) ? $relations : [$relations];
        return static::query()->withEagerLoads($flat);
    }

    static function filter(mixed $registeredFilter = null): string
    {
        return static::class;
    }

    public static function applyScopes(mixed $qop, array $disabled = []): void
    {
        if (in_array('*', $disabled)) return;

        $scopes = static::prototype()->get('scopes');
        if (!is_array($scopes)) return;

        foreach ($scopes as $name => $callable) {
            if (in_array($name, $disabled)) continue;
            if (is_callable($callable)) call_user_func($callable, $qop);
        }
    }
}
