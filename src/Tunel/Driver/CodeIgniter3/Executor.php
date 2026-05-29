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
namespace Roulette\Tunel\Driver\CodeIgniter3;

use Roulette\Query\Operation;
use Roulette\Tunel\Driver\Executor as ExecutorContract;

/**
 * Query executor for CodeIgniter 3.
 *
 * Wraps CI3's $db query builder (CI_DB_query_builder).
 *
 * @package \Roulette\Tunel\Driver\CodeIgniter3
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Executor implements ExecutorContract
{
    /** @param  mixed  $db  CI3's CI_DB_query_builder instance from get_instance()->db. */
    public function __construct(private mixed $db) {}

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function select(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $db = $this->db;

        if ($option->hasSelect()) {
            $select = $option->getSelect();
            $cols   = is_array($select)
                ? array_map(fn($a, $c) => ($c === $a ? $c : "$c AS $a"), array_keys($select), $select)
                : ['*'];
            $db->select(implode(', ', $cols));
        }

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $db);
        if ($option->hasGroup()) $db->group_by($option->getGroup());
        if ($option->hasOrder()) {
            foreach ($option->getOrder() as $field => $dir) {
                $db->order_by($field, $dir);
            }
        }
        if ($option->hasLimit()) {
            $db->limit((int) $option->getLimit(), $option->hasOffset() ? (int) $option->getOffset() : 0);
        }

        try {
            $result                  = $db->get($option->getTable());
            $operation->result       = $result->result_array();
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
            $this->db->insert($option->getTable(), $option->getPatch());
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
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $this->db);

        try {
            $this->db->update($option->getTable(), $option->getPatch());
            $affected                = $this->db->affected_rows();
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
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $this->db);

        try {
            $this->db->delete($option->getTable());
            $affected                = $this->db->affected_rows();
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
            $operation->result       = is_object($result) ? $result->result_array() : $result;
            $operation->success      = true;
            $operation->affectedRows = $this->db->affected_rows();
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
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $db = $this->db;
        $db->from($option->getTable());
        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $db);

        try {
            $operation->result       = $db->count_all_results();
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
            $this->db->truncate($option->getTable());
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
     * @param  mixed  $db          CI3 query builder instance.
     * @return void
     */
    private function buildWhere(array $conditions, mixed $db): void
    {
        foreach ($conditions as $condition) {
            $field    = $condition->field;
            $operator = $condition->operator;
            $value    = $condition->value;
            $hook     = strtolower($condition->hook ?? 'and');

            if (is_null($value) && !is_null($operator)) {
                $value    = $operator;
                $operator = '=';
            }

            $method = $hook === 'or' ? 'or_where' : 'where';
            $_op    = strtoupper(trim((string) $operator));

            if (in_array($_op, ['IN', 'NOT IN'])) {
                $method = $hook === 'or' ? 'or_where_in' : 'where_in';
                if ($_op === 'NOT IN') $method = $hook === 'or' ? 'or_where_not_in' : 'where_not_in';
                $db->$method($field, $value);
            } elseif (in_array($_op, ['NULL', 'IS NULL'])) {
                $db->where("$field IS NULL");
            } elseif (in_array($_op, ['NOT NULL', 'IS NOT NULL'])) {
                $db->where("$field IS NOT NULL");
            } else {
                $db->$method($field . ' ' . ($operator ?? '='), $value);
            }
        }
    }
}
