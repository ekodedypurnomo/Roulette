<?php

declare(strict_types=1);

namespace Roulette\Tests\Support;

use PDO;
use Roulette\Query\Condition;
use Roulette\Query\Operation;
use Roulette\Query\Option\Delete;
use Roulette\Query\Option\Insert;
use Roulette\Query\Option\Select;
use Roulette\Query\Option\Update;
use Roulette\Query\RawExpression;
use Roulette\Tunel\TunelAbstract;
use Throwable;

/**
 * In-memory SQLite adapter for PHPUnit tests.
 * Implements the Roulette Tunel contract using PDO/SQLite.
 */
class SqliteTunel extends TunelAbstract
{
    private static array $standardOps = [
        '=', '<', '<=', '>', '>=', '<>',
        'IS', 'IS NOT',
        'BETWEEN', 'NOT BETWEEN',
        'LIKE', 'NOT LIKE',
        'IN', 'NOT IN',
    ];

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo ?? new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]));
    }

    public function getPdo(): PDO
    {
        return $this->connection;
    }

    public function exec(string $sql): void
    {
        $this->connection->exec($sql);
    }

    public function operate(Operation $operation, ?callable $callback = null): mixed
    {
        $option = $operation->getOption();
        $mode   = $option::getAction();
        $sql    = null;

        try {
            switch ($mode) {
                case 'SELECT':
                    /** @var Select $option */
                    [$sql, $params] = $this->buildSelect($option);
                    $stmt = $this->connection->prepare($sql);
                    $stmt->execute($params);
                    $operation->result  = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $operation->success = true;
                    break;

                case 'INSERT':
                    /** @var Insert $option */
                    [$sql, $params] = $this->buildInsert($option);
                    $stmt = $this->connection->prepare($sql);
                    $operation->success      = $stmt->execute($params);
                    $operation->affectedRows = $stmt->rowCount();
                    $operation->result       = $this->connection->lastInsertId();
                    break;

                case 'UPDATE':
                    /** @var Update $option */
                    [$sql, $params] = $this->buildUpdate($option);
                    $stmt = $this->connection->prepare($sql);
                    $operation->success      = $stmt->execute($params);
                    $operation->affectedRows = $stmt->rowCount();
                    break;

                case 'DELETE':
                    /** @var Delete $option */
                    [$sql, $params] = $this->buildDelete($option);
                    $stmt = $this->connection->prepare($sql);
                    $operation->success      = $stmt->execute($params);
                    $operation->affectedRows = $stmt->rowCount();
                    break;

                default:
                    $operation->success = false;
            }
        } catch (Throwable $e) {
            $operation->success = false;
            $operation->error   = $e->getMessage();
        }

        $operation->query = $sql;

        if ($callback) {
            call_user_func_array($callback, [$this, $operation]);
        }

        return $this;
    }

    private function buildSelect(Select $option): array
    {
        $table   = $option->getTable();
        $columns = $option->getSelect();

        if ($columns === '*' || !is_array($columns)) {
            $selectClause = '*';
        } else {
            $parts = [];
            foreach ($columns as $alias => $field) {
                $parts[] = ($alias === $field)
                    ? "\"$field\""
                    : "\"$field\" AS \"$alias\"";
            }
            $selectClause = implode(', ', $parts);
        }

        $sql    = "SELECT $selectClause FROM \"$table\"";
        $params = [];

        [$whereSql, $whereParams] = $this->buildWhere($option->getWhere());
        if ($whereSql) {
            $sql   .= " WHERE $whereSql";
            $params = $whereParams;
        }

        if ($option->hasLimit()) {
            $sql .= ' LIMIT ' . (int) $option->getLimit();
            if ($option->hasOffset()) {
                $sql .= ' OFFSET ' . $option->getOffset();
            }
        }

        return [$sql, $params];
    }

    private function buildInsert(Insert $option): array
    {
        $table  = $option->getTable();
        $patch  = $option->getPatch();
        $ignore = $option->isIgnore();

        $columns      = array_keys($patch);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $cols         = implode(', ', array_map(fn($c) => "\"$c\"", $columns));
        $params       = array_values($patch);

        $conflictTarget = $option->getConflictTarget();
        $conflictUpdate = $option->getConflictUpdate();

        if (!empty($conflictTarget)) {
            // ON CONFLICT(...) DO UPDATE SET ...
            $targetCols  = implode(', ', array_map(fn($c) => "\"$c\"", $conflictTarget));
            $updateCols  = empty($conflictUpdate) ? $columns : $conflictUpdate;
            $updateCols  = array_diff($updateCols, $conflictTarget); // skip key cols
            $updateParts = array_map(fn($c) => "\"$c\" = excluded.\"$c\"", $updateCols);
            $sql = "INSERT INTO \"$table\" ($cols) VALUES ($placeholders)"
                 . " ON CONFLICT($targetCols) DO UPDATE SET " . implode(', ', $updateParts);
        } elseif ($ignore) {
            $sql = "INSERT OR IGNORE INTO \"$table\" ($cols) VALUES ($placeholders)";
        } else {
            $sql = "INSERT INTO \"$table\" ($cols) VALUES ($placeholders)";
        }

        return [$sql, $params];
    }

    private function buildUpdate(Update $option): array
    {
        $table  = $option->getTable();
        $patch  = $option->getPatch();
        $parts  = [];
        $params = [];

        foreach ($patch as $col => $val) {
            if ($val instanceof RawExpression) {
                $parts[] = "\"$col\" = $val";
            } else {
                $parts[]  = "\"$col\" = ?";
                $params[] = $val;
            }
        }

        $sql = "UPDATE \"$table\" SET " . implode(', ', $parts);

        [$whereSql, $whereParams] = $this->buildWhere($option->getWhere());
        if ($whereSql) {
            $sql   .= " WHERE $whereSql";
            $params = array_merge($params, $whereParams);
        }

        return [$sql, $params];
    }

    private function buildDelete(Delete $option): array
    {
        $table  = $option->getTable();
        $sql    = "DELETE FROM \"$table\"";
        $params = [];

        [$whereSql, $whereParams] = $this->buildWhere($option->getWhere());
        if ($whereSql) {
            $sql   .= " WHERE $whereSql";
            $params = $whereParams;
        }

        return [$sql, $params];
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    private function buildWhere(array $conditions): array
    {
        if (empty($conditions)) {
            return ['', []];
        }

        $parts  = [];
        $params = [];

        foreach ($conditions as $condition) {
            if (!($condition instanceof Condition)) {
                continue;
            }

            $hook  = strtoupper($condition->hook ?? 'AND');
            $field = $condition->field;
            $op    = $condition->operator;
            $value = $condition->value;

            // Nested grouped conditions
            if (is_array($field)) {
                [$nestedSql, $nestedParams] = $this->buildWhere($field);
                if ($nestedSql) {
                    $prefix  = $parts ? " $hook " : '';
                    $parts[] = $prefix . "($nestedSql)";
                    $params  = array_merge($params, $nestedParams);
                }
                continue;
            }

            // If operator is not a recognized SQL keyword, treat it as the value
            if (!in_array(strtoupper((string) $op), self::$standardOps, true)) {
                $value = $op;
                $op    = '=';
            }

            $prefix  = $parts ? " $hook " : '';
            $opUpper = strtoupper((string) $op);

            if ($opUpper === 'IN' || $opUpper === 'NOT IN') {
                $vals = (array) $value;
                $ph   = implode(', ', array_fill(0, count($vals), '?'));
                $parts[]  = "{$prefix}\"$field\" $op ($ph)";
                $params   = array_merge($params, $vals);
            } elseif (strtoupper((string) $value) === 'NULL') {
                $parts[] = "{$prefix}\"$field\" $op NULL";
            } else {
                $parts[]  = "{$prefix}\"$field\" = ?";
                $params[] = $value;
            }
        }

        return [implode('', $parts), $params];
    }
}
