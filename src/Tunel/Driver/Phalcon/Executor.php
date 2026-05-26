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
namespace Roulette\Tunel\Driver\Phalcon;

use Roulette\Query\Operation;
use Roulette\Tunel\Driver\Executor as ExecutorContract;

/**
 * Query executor for Phalcon 3/4/5.
 *
 * Phalcon's DB adapter works with raw SQL. This executor compiles
 * Roulette Option objects into parameterized SQL strings and executes
 * them via the adapter's fetchAll / execute methods.
 *
 * @package \Roulette\Tunel\Driver\Phalcon
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Executor implements ExecutorContract
{
    /** @param  mixed  $db  Phalcon\Db\Adapter instance from the DI container. */
    public function __construct(private mixed $db) {}

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function select(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        [$sql, $bindings] = $this->compileSelect($option);

        try {
            $result                  = $this->db->fetchAll($sql, \Phalcon\Db\Enum::FETCH_ASSOC, $bindings);
            $operation->result       = $result;
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
        $option  = $operation->getOption();
        if (!$option->hasTable()) return;

        $patch   = $option->getPatch();
        $columns = array_keys($patch);
        $ph      = implode(', ', array_fill(0, count($columns), '?'));
        $sql     = sprintf('INSERT INTO %s (%s) VALUES (%s)', $option->getTable(), implode(', ', $columns), $ph);

        try {
            $this->db->execute($sql, array_values($patch));
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
        $option   = $operation->getOption();
        if (!$option->hasTable()) return;

        $patch    = $option->getPatch();
        $setParts = array_map(fn($col) => "$col = ?", array_keys($patch));
        $bindings = array_values($patch);
        $sql      = sprintf('UPDATE %s SET %s', $option->getTable(), implode(', ', $setParts));

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $sql      .= ' WHERE ' . $whereSql;
            $bindings  = array_merge($bindings, $whereBindings);
        }

        try {
            $this->db->execute($sql, $bindings);
            $operation->result       = true;
            $operation->success      = true;
            $operation->affectedRows = $this->db->affectedRows();
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
        $option   = $operation->getOption();
        if (!$option->hasTable()) return;

        $sql      = 'DELETE FROM ' . $option->getTable();
        $bindings = [];

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $sql      .= ' WHERE ' . $whereSql;
            $bindings  = $whereBindings;
        }

        try {
            $this->db->execute($sql, $bindings);
            $operation->result       = true;
            $operation->success      = true;
            $operation->affectedRows = $this->db->affectedRows();
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
            if (preg_match('/^select/i', $sql)) {
                $operation->result = $this->db->fetchAll($sql, \Phalcon\Db\Enum::FETCH_ASSOC);
                $operation->affectedRows = 0;
            } else {
                $this->db->execute($sql);
                $operation->result       = true;
                $operation->affectedRows = $this->db->affectedRows();
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
        $option   = $operation->getOption();
        if (!$option->hasTable()) return;

        $sql      = 'SELECT COUNT(*) FROM ' . $option->getTable();
        $bindings = [];

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $sql      .= ' WHERE ' . $whereSql;
            $bindings  = $whereBindings;
        }

        try {
            $operation->result       = (int) $this->db->fetchColumn($sql, $bindings);
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
            $this->db->execute('TRUNCATE TABLE ' . $option->getTable());
            $operation->result       = true;
            $operation->success      = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * @param  mixed  $option
     * @return array{string, array}
     */
    private function compileSelect(mixed $option): array
    {
        $bindings = [];
        $cols     = '*';

        if ($option->hasSelect() && is_array($select = $option->getSelect())) {
            $parts = [];
            foreach ($select as $alias => $col) {
                $parts[] = ($col === $alias) ? $col : "$col AS $alias";
            }
            $cols = implode(', ', $parts);
        }

        $sql = "SELECT $cols FROM " . $option->getTable();

        if ($option->hasWhere()) {
            [$whereSql, $wb] = $this->compileWhere($option->getWhere());
            $sql      .= ' WHERE ' . $whereSql;
            $bindings  = array_merge($bindings, $wb);
        }

        if ($option->hasOrder()) {
            $parts = [];
            foreach ($option->getOrder() as $col => $dir) {
                $parts[] = "$col " . (in_array(strtoupper($dir), ['ASC', 'DESC']) ? $dir : 'ASC');
            }
            $sql .= ' ORDER BY ' . implode(', ', $parts);
        }

        if ($option->hasLimit()) {
            $sql .= ' LIMIT ' . (int) $option->getLimit();
            if ($option->hasOffset()) $sql .= ' OFFSET ' . (int) $option->getOffset();
        }

        return [$sql, $bindings];
    }

    /**
     * @param  array  $conditions
     * @return array{string, array}
     */
    private function compileWhere(array $conditions): array
    {
        $parts    = [];
        $bindings = [];
        $first    = true;

        foreach ($conditions as $c) {
            $hook   = $c->hook ?? 'AND';
            $prefix = $first ? '' : " $hook ";
            $first  = false;

            if (is_array($c->field)) {
                [$sub, $sb] = $this->compileWhere($c->field);
                $parts[]    = $prefix . "($sub)";
                $bindings   = array_merge($bindings, $sb);
                continue;
            }

            $op    = $c->operator;
            $value = $c->value;

            if (is_null($value) && !is_null($op)) { $value = $op; $op = '='; }

            $_op = strtoupper(trim((string) $op));

            if (in_array($_op, ['NULL', 'IS NULL'])) {
                $parts[] = $prefix . "$c->field IS NULL";
            } elseif (in_array($_op, ['NOT NULL', 'IS NOT NULL'])) {
                $parts[] = $prefix . "$c->field IS NOT NULL";
            } elseif (in_array($_op, ['IN', 'NOT IN'])) {
                $ph      = implode(', ', array_fill(0, count((array) $value), '?'));
                $parts[] = $prefix . "$c->field $_op ($ph)";
                $bindings = array_merge($bindings, (array) $value);
            } elseif (in_array($_op, ['BETWEEN', 'NOT BETWEEN'])) {
                $parts[]    = $prefix . "$c->field $_op ? AND ?";
                $bindings[] = $value[0];
                $bindings[] = $value[1];
            } else {
                $parts[]    = $prefix . "$c->field $_op ?";
                $bindings[] = $value;
            }
        }

        return [implode('', $parts), $bindings];
    }
}
