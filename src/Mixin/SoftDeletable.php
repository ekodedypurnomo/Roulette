<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Mixin;

use Roulette\Query\Operation;

/**
 * Opt-in soft-delete behaviour for Model subclasses.
 *
 * Apply with `use SoftDeletable` in any Model subclass. Requires a
 * `deleted_at` column (TEXT/DATETIME) in the database table.
 *
 * Behaviour:
 * - `destroy()` sets `deleted_at` to the current timestamp instead of deleting the row.
 * - `find()` and `load()` automatically filter out soft-deleted rows via an
 *   injected scope named `__softDelete`.
 * - `withTrashed()` bypasses the soft-delete scope for the next query.
 * - `restore()` clears `deleted_at`, un-deleting the record.
 * - `forceDelete()` permanently removes the row from the database.
 * - `isTrashed()` returns true when the record has a non-null `deleted_at`.
 *
 * The `deleted_at` field must be declared in the model prototype:
 *
 *   'fields' => [
 *       ...,
 *       ['name' => 'deleted_at', 'nullable' => true, 'update' => true],
 *   ]
 *
 * @package \Roulette\Mixin
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait SoftDeletable
{
    const SOFT_DELETE_SCOPE = '__softDelete';
    const SOFT_DELETE_COLUMN = 'deleted_at';

    /**
     * Override destroy() — sets deleted_at to now instead of deleting the row.
     */
    function destroy(mixed $callback = null): bool
    {
        if (!$this->isAlive(true)) {
            if (is_callable($callback)) $callback(false, null, $this);
            return false;
        }

        $table     = static::getTable();
        $column    = static::resolveDeletedAtColumn();
        $condition = $this->getFields()->mapToSource([
            $this->getPrimary() => $this->get(static::getPrimary(), false)
        ]);
        $timestamp = date('Y-m-d H:i:s');

        $operation = Operation::create('update')->buildQuery(function($qop) use($table, $column, $timestamp, $condition) {
            $qop->table($table)
                ->set([$column => $timestamp])
                ->where($condition);
        })->execute();

        $success = (bool) $operation->getAffectedRows();

        if ($success) {
            $this->set(static::SOFT_DELETE_COLUMN, $timestamp);
            $this->makeAlive(false);
        }

        if (is_callable($callback)) {
            $callback($success, $operation, $this);
        }

        return $success;
    }

    /**
     * Permanently delete the row, bypassing soft-delete.
     */
    function forceDelete(mixed $callback = null): mixed
    {
        return parent::destroy($callback);
    }

    /**
     * Restore a soft-deleted record by clearing deleted_at.
     * Returns false immediately if the record is not soft-deleted.
     */
    function restore(): bool
    {
        if (!$this->isTrashed()) return false;

        $table     = static::getTable();
        $column    = static::resolveDeletedAtColumn();
        $condition = $this->getFields()->mapToSource([
            $this->getPrimary() => $this->get(static::getPrimary(), false)
        ]);

        $operation = Operation::create('update')->buildQuery(function($qop) use($table, $column, $condition) {
            $qop->table($table)
                ->set([$column => null])
                ->where($condition);
        })->execute();

        $success = (bool) $operation->getAffectedRows();

        if ($success) {
            $this->set(static::SOFT_DELETE_COLUMN, null);
            $this->makeAlive(true);
        }

        return $success;
    }

    /**
     * Returns true when this record has been soft-deleted.
     */
    function isTrashed(): bool
    {
        return $this->get(static::SOFT_DELETE_COLUMN) !== null;
    }

    /**
     * Bypass the soft-delete scope for the next find()/load() call.
     * Returns the class name for static chaining.
     */
    static function withTrashed(): \Roulette\Query\ModelQueryBuilder
    {
        return static::withoutScope(static::SOFT_DELETE_SCOPE);
    }

    /**
     * Override applyScopes to inject the soft-delete filter before any user scopes.
     */
    public static function applyScopes(mixed $qop, array $disabled = []): void
    {
        if (!in_array('*', $disabled) && !in_array(static::SOFT_DELETE_SCOPE, $disabled)) {
            $column = static::resolveDeletedAtColumn();
            $qop->whereNull($column);
        }

        parent::applyScopes($qop, $disabled);
    }

    /**
     * Resolve the DB source column name for the deleted_at field.
     */
    private static function resolveDeletedAtColumn(): string
    {
        $mapped = static::getFields()->mapToSource([static::SOFT_DELETE_COLUMN => '']);
        return $mapped ? array_key_first($mapped) : static::SOFT_DELETE_COLUMN;
    }
}
