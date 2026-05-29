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
namespace Roulette\Tunel\Driver\Illuminate;

use Roulette\Query\Operation;
use Roulette\Query\RawExpression;
use Roulette\Tunel\Driver\Executor as ExecutorContract;

/**
 * Query executor for the Illuminate database layer (Laravel 5–12, Lumen).
 *
 * Translates Roulette Option objects into Illuminate query builder calls.
 * Works with all supported Laravel major versions via the DB facade.
 *
 * @package \Roulette\Tunel\Driver\Illuminate
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Executor implements ExecutorContract
{
    /** @param  string  $dbClass  Fully-qualified Illuminate DB facade class name. */
    public function __construct(private string $dbClass) {}

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function select(Operation $operation): void
    {
        $option  = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = ($this->dbClass)::table($option->getTable());

        if ($option->hasSelect())  $this->buildSelect($option->getSelect(), $builder);
        if ($option->hasWhere())   $this->buildWhere($option->getWhere(), $builder);
        if ($option->hasGroup()) {
            $this->buildGroup($option->getGroup(), $builder);
            if ($option->hasHaving()) $this->buildHaving($option->getHaving(), $builder);
        }
        if ($option->hasOrder())  $this->buildOrder($option->getOrder(), $builder);
        if ($option->hasLimit()) {
            $this->buildLimit($option->getLimit(), $builder);
            if ($option->hasOffset()) $this->buildOffset($option->getOffset(), $builder);
        }

        try {
            $operation->result      = $builder->get()->toArray();
            $operation->success     = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function insert(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = ($this->dbClass)::table($option->getTable());
        $patch   = $option->getPatch();

        try {
            $operation->result      = $builder->insert($patch);
            $operation->success     = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function update(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = ($this->dbClass)::table($option->getTable());
        $patch   = $option->getPatch();

        foreach ($patch as $col => &$value) {
            if ($value instanceof RawExpression) {
                $rawSql = str_replace('{col}', $col, (string) $value);
                $value  = ($this->dbClass)::raw($rawSql);
            }
        }
        unset($value);

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $builder);

        try {
            $result                  = $builder->update($patch);
            $operation->result       = $result;
            $operation->success      = true;
            $operation->affectedRows = $result;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function delete(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = ($this->dbClass)::table($option->getTable());

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $builder);

        try {
            $result                  = $builder->delete();
            $operation->result       = $result;
            $operation->success      = true;
            $operation->affectedRows = $result;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function query(Operation $operation): void
    {
        $sql = trim((string) ($operation->getOption()->getOption() ?? ''));
        if (empty($sql)) return;

        $conn = $this->dbClass;
        try {
            if (preg_match('/^select/i', $sql)) {
                $operation->result      = $conn::select($sql);
                $operation->affectedRows = 0;
            } elseif (preg_match('/^insert/i', $sql)) {
                $operation->result      = $conn::insert($sql);
                $operation->affectedRows = 0;
            } elseif (preg_match('/^update/i', $sql)) {
                $operation->result      = $conn::update($sql);
                $operation->affectedRows = $operation->result;
            } elseif (preg_match('/^delete/i', $sql)) {
                $operation->result      = $conn::delete($sql);
                $operation->affectedRows = $operation->result;
            } else {
                $operation->result      = $conn::statement($sql);
                $operation->affectedRows = 0;
            }
            $operation->success  = true;
            $operation->query    = $sql;
            $operation->queryRaw = $sql;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function exists(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = ($this->dbClass)::table($option->getTable());

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $builder);

        try {
            $operation->result      = $builder->count('*');
            $operation->success     = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function truncate(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = ($this->dbClass)::table($option->getTable());

        try {
            $builder->truncate();
            $operation->success     = true;
            $operation->result      = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    // -----------------------------------------------------------------------
    // Builder helpers
    // -----------------------------------------------------------------------

    /**
     * @param  mixed  $select   Column list or '*'.
     * @param  mixed  $builder  Illuminate query builder instance.
     * @return void
     */
    private function buildSelect(mixed $select, mixed $builder): void
    {
        if (is_array($select)) {
            foreach ($select as $alias => $column) {
                if (empty($column)) {
                    $select[$alias] = $alias;
                } elseif ($column !== $alias) {
                    $select[$alias] = $column . ' AS ' . $alias;
                }
            }
        }
        $builder->select($select);
    }

    /**
     * Recursively applies WHERE conditions to the builder.
     * Nested condition groups are handled by passing a sub-array as $field.
     *
     * @param  array  $where    Array of condition objects.
     * @param  mixed  $builder  Illuminate query builder instance.
     * @return void
     */
    private function buildWhere(array $where, mixed $builder): void
    {
        foreach ($where as $condition) {
            $hook     = $condition->hook;
            $field    = $condition->field;
            $operator = $condition->operator;
            $value    = $condition->value;

            if (is_array($field)) {
                $builder->where(fn($b) => $this->buildWhere($field, $b), null, null, $hook);
                continue;
            }

            if (is_null($value) && !is_null($operator)) {
                $value    = $operator;
                $operator = '=';
            }

            $_op = strtoupper(trim((string) $operator));

            if (is_null($operator)) {
                $builder->whereRaw($field, is_array($value) ? $value : [], $hook);
            } elseif (in_array($_op, ['=', '<', '>', '<>', '<=', '>='])) {
                if (preg_match('/^`.*`$/', (string) $value)) {
                    $builder->whereColumn($field, $_op, $value, $hook);
                } elseif (preg_match('/^({column}|{date}|{time}|{month}|{year})(.*)/', (string) $value, $m)) {
                    $v = ltrim($m[2]);
                    match ($m[1]) {
                        '{column}' => $builder->whereColumn($field, $_op, $v, $hook),
                        '{date}'   => $builder->whereDate($field, $_op, $v, $hook),
                        '{time}'   => $builder->whereTime($field, $_op, $v, $hook),
                        '{month}'  => $builder->whereMonth($field, $_op, $v, $hook),
                        '{year}'   => $builder->whereYear($field, $_op, $v, $hook),
                        default    => null,
                    };
                } else {
                    $builder->where($field, $_op, $value, $hook);
                }
            } elseif (in_array($_op, ['BETWEEN', 'NOT BETWEEN'])) {
                $builder->whereBetween($field, $value, $hook, str_starts_with($_op, 'NOT'));
            } elseif (in_array($_op, ['IN', 'NOT IN'])) {
                $builder->whereIn($field, $value, $hook, str_starts_with($_op, 'NOT'));
            } elseif (in_array($_op, ['NULL', 'NOT NULL', 'IS NULL', 'IS NOT NULL'])) {
                $builder->whereNull($field, $hook, str_contains($_op, 'NOT'));
            } else {
                $builder->where($field, $_op, $value, $hook);
            }
        }
    }

    /**
     * @param  array  $group
     * @param  mixed  $builder
     * @return void
     */
    private function buildGroup(array $group, mixed $builder): void
    {
        $builder->groupBy($group);
    }

    /**
     * @param  array  $having
     * @param  mixed  $builder
     * @return void
     */
    private function buildHaving(array $having, mixed $builder): void
    {
        $builder->havingRaw(implode(' ', $having));
    }

    /**
     * @param  array  $order
     * @param  mixed  $builder
     * @return void
     */
    private function buildOrder(array $order, mixed $builder): void
    {
        foreach ($order as $field => $direction) {
            $builder->orderBy($field, in_array(strtoupper($direction), ['ASC', 'DESC']) ? $direction : 'ASC');
        }
    }

    /**
     * @param  mixed  $limit
     * @param  mixed  $builder
     * @return void
     */
    private function buildLimit(mixed $limit, mixed $builder): void
    {
        $builder->take($limit);
    }

    /**
     * @param  mixed  $offset
     * @param  mixed  $builder
     * @return void
     */
    private function buildOffset(mixed $offset, mixed $builder): void
    {
        $builder->skip($offset);
    }
}
