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
use Roulette\Query\RawExpression;
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
    private ?string $driver = null;

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

        $patch        = $option->getPatch();
        $columns      = array_keys($patch);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql          = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quoteIdent($option->getTable()),
            implode(', ', array_map(fn($c) => $this->quoteIdent($c), $columns)),
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
        $setParts = [];
        $bindings = [];

        foreach ($patch as $col => $value) {
            $quotedCol = $this->quoteIdent($col);
            if ($value instanceof RawExpression) {
                $rawSql     = str_replace('{col}', $quotedCol, (string) $value);
                $setParts[] = "$quotedCol = $rawSql";
            } else {
                $setParts[] = "$quotedCol = ?";
                $bindings[] = $value;
            }
        }

        $sql = sprintf('UPDATE %s SET %s', $this->quoteIdent($option->getTable()), implode(', ', $setParts));

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

        $sql      = 'DELETE FROM ' . $this->quoteIdent($option->getTable());
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
                $operation->result       = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $operation->affectedRows = 0;
            } else {
                $operation->result       = true;
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

        $sql      = 'SELECT COUNT(*) FROM ' . $this->quoteIdent($option->getTable());
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
            $this->pdo->exec('TRUNCATE TABLE ' . $this->quoteIdent($option->getTable()));
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
                if ($col === $alias) {
                    $cols[] = $this->quoteIdent((string) $col);
                } else {
                    $cols[] = $this->quoteIdent((string) $col) . ' AS ' . $this->quoteIdent((string) $alias);
                }
            }
            $parts[] = implode(', ', $cols);
        } else {
            $parts[] = '*';
        }

        $parts[] = 'FROM ' . $this->quoteIdent($option->getTable());

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $parts[]  = 'WHERE ' . $whereSql;
            $bindings = array_merge($bindings, $whereBindings);
        }

        if ($option->hasGroup()) {
            $parts[] = 'GROUP BY ' . implode(', ', array_map(fn($g) => $this->quoteIdent((string) $g), $option->getGroup()));
            if ($option->hasHaving()) {
                [$havingSql, $havingBindings] = $this->compileHaving($option->getHaving());
                $parts[]  = 'HAVING ' . $havingSql;
                $bindings = array_merge($bindings, $havingBindings);
            }
        }

        if ($option->hasOrder()) {
            $orderParts = [];
            foreach ($option->getOrder() as $col => $dir) {
                $dir          = in_array(strtoupper($dir), ['ASC', 'DESC']) ? strtoupper($dir) : 'ASC';
                $orderParts[] = $this->quoteIdent((string) $col) . ' ' . $dir;
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

            // Raw SQL fragment — operator null means $field is a raw expression
            if (is_null($operator)) {
                $parts[]  = $prefix . $field;
                if (is_array($value)) $bindings = array_merge($bindings, $value);
                continue;
            }

            if (is_null($value) && !is_null($operator)) {
                $value    = $operator;
                $operator = '=';
            }

            $_op         = strtoupper(trim((string) $operator));
            $quotedField = $this->quoteIdent((string) $field);

            // IS NULL / IS NOT NULL — covers both `operator=IS, value='NULL'` and `operator=IS NULL`
            if (in_array($_op, ['IS NULL', 'IS NOT NULL', 'NULL', 'NOT NULL'])) {
                $isNot   = str_contains($_op, 'NOT');
                $parts[] = $prefix . "$quotedField IS " . ($isNot ? 'NOT NULL' : 'NULL');
            } elseif (in_array($_op, ['IS', 'IS NOT']) && strtoupper(trim((string) $value)) === 'NULL') {
                $parts[] = $prefix . "$quotedField $_op NULL";
            } elseif (in_array($_op, ['BETWEEN', 'NOT BETWEEN'])) {
                $parts[]    = $prefix . "$quotedField $_op ? AND ?";
                $bindings[] = $value[0];
                $bindings[] = $value[1];
            } elseif (in_array($_op, ['IN', 'NOT IN'])) {
                $ph       = implode(', ', array_fill(0, count((array) $value), '?'));
                $parts[]  = $prefix . "$quotedField $_op ($ph)";
                $bindings = array_merge($bindings, (array) $value);
            } elseif (in_array($_op, ['LIKE', 'NOT LIKE'])) {
                $parts[]    = $prefix . "$quotedField $_op ?";
                $bindings[] = $value;
            } else {
                $parts[]    = $prefix . "$quotedField $_op ?";
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

    // -----------------------------------------------------------------------
    // Identifier quoting
    // -----------------------------------------------------------------------

    private function getDriver(): string
    {
        $this->driver ??= strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        return $this->driver;
    }

    /**
     * Quotes a SQL identifier (table, column, alias).
     * SQL expressions (containing parentheses) and wildcards are returned as-is.
     * Dot-separated identifiers (schema.table, table.column) are split and each part quoted.
     */
    private function quoteIdent(string $ident): string
    {
        if (str_contains($ident, '(') || $ident === '*') {
            return $ident;
        }

        if (str_contains($ident, '.')) {
            return implode('.', array_map(fn($p) => $this->quoteSingleIdent($p), explode('.', $ident)));
        }

        return $this->quoteSingleIdent($ident);
    }

    private function quoteSingleIdent(string $ident): string
    {
        if ($this->getDriver() === 'mysql') {
            return '`' . str_replace('`', '``', $ident) . '`';
        }
        return '"' . str_replace('"', '""', $ident) . '"';
    }
}
