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
namespace Roulette\Tunel\Driver\CodeIgniter4;

use Roulette\Query\Operation;
use Roulette\Tunel\Driver\Executor as ExecutorContract;

/**
 * Query executor for CodeIgniter 4.
 *
 * Wraps CI4's BaseBuilder obtained via db_connect()->table().
 *
 * @package \Roulette\Tunel\Driver\CodeIgniter4
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Executor implements ExecutorContract
{
    /** @param  mixed  $db  CI4's BaseConnection instance from db_connect(). */
    public function __construct(private mixed $db) {}

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function select(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = $this->db->table($option->getTable());

        if ($option->hasSelect()) {
            $select = $option->getSelect();
            if (is_array($select)) {
                $cols = array_map(fn($a, $c) => ($c === $a ? $c : "$c AS $a"), array_keys($select), $select);
                $builder->select(implode(', ', $cols));
            }
        }

        if ($option->hasWhere())  $this->buildWhere($option->getWhere(), $builder);
        if ($option->hasGroup())  $builder->groupBy(implode(', ', $option->getGroup()));
        if ($option->hasOrder()) {
            foreach ($option->getOrder() as $field => $dir) {
                $builder->orderBy($field, $dir);
            }
        }
        if ($option->hasLimit()) {
            $builder->limit((int) $option->getLimit(), $option->hasOffset() ? (int) $option->getOffset() : 0);
        }

        try {
            $result                  = $builder->get();
            $operation->result       = $result->getResultArray();
            $operation->success      = true;
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

        try {
            $this->db->table($option->getTable())->insert($option->getPatch());
            $operation->result       = true;
            $operation->success      = true;
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
        $option  = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = $this->db->table($option->getTable());
        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $builder);

        try {
            $builder->update($option->getPatch());
            $affected                = $this->db->affectedRows();
            $operation->result       = $affected;
            $operation->success      = true;
            $operation->affectedRows = $affected;
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
        $option  = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = $this->db->table($option->getTable());
        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $builder);

        try {
            $builder->delete();
            $affected                = $this->db->affectedRows();
            $operation->result       = $affected;
            $operation->success      = true;
            $operation->affectedRows = $affected;
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

        try {
            $result = $this->db->query($sql);
            $operation->result       = $result ? $result->getResultArray() : true;
            $operation->success      = true;
            $operation->affectedRows = $this->db->affectedRows();
            $operation->query        = $sql;
            $operation->queryRaw     = $sql;
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
        $option  = $operation->getOption();
        if (!$option->hasTable()) return;

        $builder = $this->db->table($option->getTable());
        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $builder);

        try {
            $operation->result       = $builder->countAllResults();
            $operation->success      = true;
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

        try {
            $this->db->table($option->getTable())->truncate();
            $operation->result       = true;
            $operation->success      = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  array  $conditions
     * @param  mixed  $builder     CI4 BaseBuilder instance.
     * @return void
     */
    private function buildWhere(array $conditions, mixed $builder): void
    {
        foreach ($conditions as $condition) {
            $field    = $condition->field;
            $operator = $condition->operator;
            $value    = $condition->value;
            $hook     = strtolower($condition->hook ?? 'and');

            if (is_null($value) && !is_null($operator)) {
                $value = $operator; $operator = '=';
            }

            $_op   = strtoupper(trim((string) $operator));
            $isOr  = $hook === 'or';

            if (in_array($_op, ['IN', 'NOT IN'])) {
                $isNot = $_op === 'NOT IN';
                $isOr ? ($isNot ? $builder->orWhereNotIn($field, $value) : $builder->orWhereIn($field, $value))
                      : ($isNot ? $builder->whereNotIn($field, $value)   : $builder->whereIn($field, $value));
            } elseif (in_array($_op, ['NULL', 'IS NULL'])) {
                $isOr ? $builder->orWhere("$field IS NULL") : $builder->where("$field IS NULL");
            } elseif (in_array($_op, ['NOT NULL', 'IS NOT NULL'])) {
                $isOr ? $builder->orWhere("$field IS NOT NULL") : $builder->where("$field IS NOT NULL");
            } else {
                $isOr ? $builder->orWhere($field . ' ' . ($operator ?? '='), $value)
                      : $builder->where($field . ' ' . ($operator ?? '='), $value);
            }
        }
    }
}
