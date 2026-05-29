<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Mixin\Observable;

/**
 * Adds a two-tier event system to Model:
 *
 * - Class-level (static):  User::on('after:save', fn($record) => ...)
 *   Listeners stored in the Prototype; fire for every instance of that class.
 *
 * - Instance-level:        $user->on('after:save', fn($record) => ...)
 *   Listeners stored on the instance via Observable; fire for that record only.
 *
 * Canonical events:
 *   before:save   after:save
 *   before:create after:create
 *   before:update after:update
 *   before:destroy after:destroy
 *   before:validate after:validate
 *   after:load  after:find  after:reload
 */
trait ManagesEvents
{
    use Observable;

    protected static array $modelEvents = [
        'before:save',    'after:save',
        'before:create',  'after:create',
        'before:update',  'after:update',
        'before:destroy', 'after:destroy',
        'before:validate','after:validate',
        'after:load', 'after:find', 'after:reload',
    ];

    // ── Class-level listener registry (stored in Prototype) ──────────────

    static function on(string $event, callable $listener): string
    {
        $all = static::prototype()->get('eventListeners') ?? [];
        $all[$event][] = $listener;
        static::prototype()->set('eventListeners', $all);
        return static::class;
    }

    static function off(string $event, ?callable $listener = null): string
    {
        $all = static::prototype()->get('eventListeners') ?? [];
        if ($listener === null) {
            unset($all[$event]);
        } else {
            $all[$event] = array_values(array_filter(
                $all[$event] ?? [],
                fn($l) => $l !== $listener
            ));
        }
        static::prototype()->set('eventListeners', $all);
        return static::class;
    }

    // ── Internal: initialise Observable events on each new instance ───────

    protected function initModelEvents(): void
    {
        $this->addEvent(static::$modelEvents);
    }

    // ── Internal: fire class-level then instance-level ────────────────────

    /**
     * Fire $event. Class-level listeners run first, then instance-level.
     * Any listener returning false stops propagation and returns false.
     * Returns true when all listeners pass (or no listeners registered).
     */
    protected function fireModelEvent(string $event, mixed ...$args): bool
    {
        // 1. class-level (Prototype)
        $all = static::prototype()->get('eventListeners') ?? [];
        foreach ($all[$event] ?? [] as $fn) {
            if (call_user_func($fn, $this, ...$args) === false) return false;
        }

        // 2. instance-level (Observable::trigger)
        return $this->trigger($event, array_merge([$this], $args));
    }
}
