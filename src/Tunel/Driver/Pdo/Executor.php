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
namespace Roulette\Tunel\Driver\Pdo;

use PDO;
use Roulette\Query\Operation;
use Roulette\Tunel\Driver\Executor as ExecutorContract;

/**
 * Query executor backed by a plain PDO connection.
 *
 * Builds parameterized SQL from Roulette Option objects and executes via
 * PDO prepared statements. Works with any PDO-supported database engine
 * (MySQL, PostgreSQL, SQLite, etc.) without any framework dependency.
 *
 * @package \Roulette\Tunel\Driver\Pdo
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Executor implements ExecutorContract
{
    /** @param  PDO  $pdo */
    public function __construct(private PDO $pdo) {}

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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $operation->result       = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        $patch       = $option->getPatch();
        $columns     = array_keys($patch);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql         = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $option->getTable(),
            implode(', ', $columns),
            $placeholders
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($patch));
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

        $patch    = $option->getPatch();
        $setParts = array_map(fn($col) => "$col = ?", array_keys($patch));
        $bindings = array_values($patch);

        $sql = sprintf('UPDATE %s SET %s', $option->getTable(), implode(', ', $setParts));

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $sql      .= ' WHERE ' . $whereSql;
            $bindings  = array_merge($bindings, $whereBindings);
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $affected                = $stmt->rowCount();
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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $affected                = $stmt->rowCount();
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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            if (preg_match('/^select/i', $sql)) {
                $operation->result      = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $operation->affectedRows = 0;
            } else {
                $operation->result      = true;
                $operation->affectedRows = $stmt->rowCount();
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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            $operation->result       = (int) $stmt->fetchColumn();
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
            $this->pdo->exec('TRUNCATE TABLE ' . $option->getTable());
            $operation->result       = true;
            $operation->success      = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    // -----------------------------------------------------------------------
    // SQL compiler helpers
    // -----------------------------------------------------------------------

    /**
     * @param  mixed  $option
     * @return array{string, array<int, mixed>}
     */
    private function compileSelect(mixed $option): array
    {
        $bindings = [];
        $parts    = ['SELECT'];

        if ($option->hasSelect() && is_array($select = $option->getSelect())) {
            $cols = [];
            foreach ($select as $alias => $col) {
                $cols[] = ($col === $alias) ? $col : "$col AS $alias";
            }
            $parts[] = implode(', ', $cols);
        } else {
            $parts[] = '*';
        }

        $parts[] = 'FROM ' . $option->getTable();

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $parts[]  = 'WHERE ' . $whereSql;
            $bindings = array_merge($bindings, $whereBindings);
        }

        if ($option->hasGroup()) {
            $parts[] = 'GROUP BY ' . implode(', ', $option->getGroup());
            if ($option->hasHaving()) {
                [$havingSql, $havingBindings] = $this->compileHaving($option->getHaving());
                $parts[]  = 'HAVING ' . $havingSql;
                $bindings = array_merge($bindings, $havingBindings);
            }
        }

        if ($option->hasOrder()) {
            $orderParts = [];
            foreach ($option->getOrder() as $col => $dir) {
                $orderParts[] = "$col " . (in_array(strtoupper($dir), ['ASC', 'DESC']) ? $dir : 'ASC');
            }
            $parts[] = 'ORDER BY ' . implode(', ', $orderParts);
        }

        if ($option->hasLimit()) {
            $parts[] = 'LIMIT ' . (int) $option->getLimit();
            if ($option->hasOffset()) {
                $parts[] = 'OFFSET ' . (int) $option->getOffset();
            }
        }

        return [implode(' ', $parts), $bindings];
    }

    /**
     * Recursively builds a parameterized WHERE clause from condition objects.
     *
     * @param  array  $conditions
     * @param  bool   $nested      True when called for a grouped sub-expression.
     * @return array{string, array<int, mixed>}
     */
    private function compileWhere(array $conditions, bool $nested = false): array
    {
        $parts    = [];
        $bindings = [];
        $first    = true;

        foreach ($conditions as $condition) {
            $hook     = $condition->hook ?? 'AND';
            $field    = $condition->field;
            $operator = $condition->operator;
            $value    = $condition->value;
            $prefix   = $first ? '' : " $hook ";
            $first    = false;

            if (is_array($field)) {
                [$subSql, $subBindings] = $this->compileWhere($field, true);
                $parts[]  = $prefix . "($subSql)";
                $bindings = array_merge($bindings, $subBindings);
                continue;
            }

            if (is_null($operator)) {
                $parts[]  = $prefix . $field;
                if (is_array($value)) $bindings = array_merge($bindings, $value);
                continue;
            }

            if (is_null($value) && !is_null($operator)) {
                $value    = $operator;
                $operator = '=';
            }

            $_op = strtoupper(trim((string) $operator));

            if (in_array($_op, ['NULL', 'NOT NULL', 'IS NULL', 'IS NOT NULL'])) {
                $isNot   = str_contains($_op, 'NOT');
                $parts[] = $prefix . "$field IS " . ($isNot ? 'NOT NULL' : 'NULL');
            } elseif (in_array($_op, ['BETWEEN', 'NOT BETWEEN'])) {
                $parts[]  = $prefix . "$field $_op ? AND ?";
                $bindings[] = $value[0];
                $bindings[] = $value[1];
            } elseif (in_array($_op, ['IN', 'NOT IN'])) {
                $ph      = implode(', ', array_fill(0, count((array) $value), '?'));
                $parts[] = $prefix . "$field $_op ($ph)";
                $bindings = array_merge($bindings, (array) $value);
            } elseif (in_array($_op, ['LIKE', 'NOT LIKE'])) {
                $parts[]    = $prefix . "$field $_op ?";
                $bindings[] = $value;
            } else {
                $parts[]    = $prefix . "$field $_op ?";
                $bindings[] = $value;
            }
        }

        return [implode('', $parts), $bindings];
    }

    /**
     * @param  array  $having
     * @return array{string, array<int, mixed>}
     */
    private function compileHaving(array $having): array
    {
        return $this->compileWhere($having);
    }
}
