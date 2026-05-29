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
namespace Roulette\Tunel\Driver\Dbal;

use Doctrine\DBAL\Connection;
use Roulette\Query\Operation;
use Roulette\Query\RawExpression;
use Roulette\Tunel\Driver\Executor as ExecutorContract;

/**
 * Query executor for Doctrine DBAL (Symfony 4–7, standalone Doctrine).
 *
 * Uses DBAL's QueryBuilder for SELECT and parameterized executeStatement
 * for write operations, ensuring cross-engine compatibility.
 *
 * @package \Roulette\Tunel\Driver\Dbal
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Executor implements ExecutorContract
{
    /** @param  Connection  $conn */
    public function __construct(private Connection $conn) {}

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function select(Operation $operation): void
    {
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        $qb = $this->conn->createQueryBuilder()->from($option->getTable());

        if ($option->hasSelect() && is_array($cols = $option->getSelect())) {
            foreach ($cols as $alias => $col) {
                $qb->addSelect($col === $alias ? $col : "$col AS $alias");
            }
        } else {
            $qb->select('*');
        }

        if ($option->hasWhere()) $this->buildWhere($option->getWhere(), $qb);

        if ($option->hasGroup()) {
            foreach ($option->getGroup() as $col) $qb->addGroupBy($col);
        }

        if ($option->hasOrder()) {
            foreach ($option->getOrder() as $col => $dir) {
                $qb->addOrderBy($col, in_array(strtoupper($dir), ['ASC', 'DESC']) ? $dir : 'ASC');
            }
        }

        if ($option->hasLimit()) {
            $qb->setMaxResults((int) $option->getLimit());
            if ($option->hasOffset()) $qb->setFirstResult((int) $option->getOffset());
        }

        try {
            $result                  = $qb->executeQuery()->fetchAllAssociative();
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
        $option = $operation->getOption();
        if (!$option->hasTable()) return;

        try {
            $this->conn->insert($option->getTable(), $option->getPatch());
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
        $setParts = [];
        $bindings = [];

        foreach ($patch as $col => $value) {
            if ($value instanceof RawExpression) {
                $rawSql     = str_replace('{col}', $col, (string) $value);
                $setParts[] = "$col = $rawSql";
            } else {
                $setParts[] = "$col = ?";
                $bindings[] = $value;
            }
        }

        $sql = sprintf('UPDATE %s SET %s', $option->getTable(), implode(', ', $setParts));

        if ($option->hasWhere()) {
            [$whereSql, $whereBindings] = $this->compileWhere($option->getWhere());
            $sql      .= ' WHERE ' . $whereSql;
            $bindings  = array_merge($bindings, $whereBindings);
        }

        try {
            $affected                = $this->conn->executeStatement($sql, $bindings);
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
            $affected                = $this->conn->executeStatement($sql, $bindings);
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
            if (preg_match('/^select/i', $sql)) {
                $operation->result       = $this->conn->fetchAllAssociative($sql);
                $operation->affectedRows = 0;
            } else {
                $operation->result       = true;
                $operation->affectedRows = $this->conn->executeStatement($sql);
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
            $operation->result       = (int) $this->conn->fetchOne($sql, $bindings);
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
            $platform = $this->conn->getDatabasePlatform();
            $this->conn->executeStatement($platform->getTruncateTableSQL($option->getTable()));
            $operation->result       = true;
            $operation->success      = true;
            $operation->affectedRows = 0;
        } catch (\Throwable $e) {
            $operation->success = false;
            $operation->error   = $e;
        }
    }

    /**
     * Applies WHERE conditions to a DBAL QueryBuilder using typed expressions.
     *
     * @param  array  $conditions
     * @param  mixed  $qb      DBAL QueryBuilder instance.
     * @param  bool   $nested  Unused; reserved for future sub-expression support.
     * @return void
     */
    private function buildWhere(array $conditions, mixed $qb, bool $nested = false): void
    {
        foreach ($conditions as $condition) {
            $hook  = strtolower($condition->hook ?? 'and');
            $field = $condition->field;
            $op    = $condition->operator;
            $value = $condition->value;

            if (is_null($value) && !is_null($op)) { $value = $op; $op = '='; }

            $_op   = strtoupper(trim((string) $op));
            $param = $qb->createNamedParameter($value);

            $expr = match(true) {
                in_array($_op, ['NULL', 'IS NULL'])           => $qb->expr()->isNull($field),
                in_array($_op, ['NOT NULL', 'IS NOT NULL'])   => $qb->expr()->isNotNull($field),
                in_array($_op, ['IN', 'NOT IN'])              => $_op === 'IN'
                    ? $qb->expr()->in($field, $qb->createNamedParameter($value, \Doctrine\DBAL\ArrayParameterType::STRING))
                    : $qb->expr()->notIn($field, $qb->createNamedParameter($value, \Doctrine\DBAL\ArrayParameterType::STRING)),
                default                                       => $qb->expr()->comparison($field, $_op, $param),
            };

            $hook === 'or' ? $qb->orWhere($expr) : $qb->andWhere($expr);
        }
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
            $op     = $c->operator;
            $value  = $c->value;

            if (is_null($value) && !is_null($op)) { $value = $op; $op = '='; }

            $_op = strtoupper(trim((string) $op));

            if (in_array($_op, ['IN', 'NOT IN'])) {
                $ph       = implode(', ', array_fill(0, count((array) $value), '?'));
                $parts[]  = $prefix . "$c->field $_op ($ph)";
                $bindings = array_merge($bindings, (array) $value);
            } elseif (in_array($_op, ['BETWEEN', 'NOT BETWEEN'])) {
                $parts[]    = $prefix . "$c->field $_op ? AND ?";
                $bindings[] = $value[0];
                $bindings[] = $value[1];
            } elseif (in_array($_op, ['NULL', 'IS NULL'])) {
                $parts[] = $prefix . "$c->field IS NULL";
            } elseif (in_array($_op, ['NOT NULL', 'IS NOT NULL'])) {
                $parts[] = $prefix . "$c->field IS NOT NULL";
            } else {
                $parts[]    = $prefix . "$c->field $_op ?";
                $bindings[] = $value;
            }
        }

        return [implode('', $parts), $bindings];
    }
}
