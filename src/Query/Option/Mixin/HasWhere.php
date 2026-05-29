<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

use Roulette\Query\Condition;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasWhere
{
    protected array $where = [];

    function hasWhere(): bool
    {
        return !empty($this->where);
    }

    function getWhere(): array
    {
        return $this->where;
    }

    function where(mixed $field = null, mixed $operatorOrCondition = null, mixed $value = null, string $hook = 'AND'): static
    {
        if (is_array($field))
        {
            foreach ($field as $f => $v)
            {
                $this->where($f, $v);
            }
            return $this;
        }

        # logic

        if (empty($field)) return $this;

        if (!is_array($this->where)) $this->where = [];

        $hook = strtoupper($hook);

        if (is_callable($field))
        {
            $builder = new $this($this->table);
            $where = call_user_func_array($field, [$builder]);
            $this->where[] = Condition::create([
                'hook'  => $hook,
                'field' => $builder->getWhere()
            ]);
        }
        else
        {
            $condition = Condition::create([
                'hook'     => $hook,
                'field'    => $field,
                'operator' => $operatorOrCondition,
                'value'    => $value
            ]);
            $this->where[] = $condition;
        }

        return $this;
    }

    function andWhere(mixed $field, mixed $operatorOrCondition = null, mixed $condition = null): static
    {
        return $this->where($field, $operatorOrCondition, $condition, 'AND');
    }

    function orWhere(mixed $field, mixed $operatorOrCondition = null, mixed $condition = null): static
    {
        return $this->where($field, $operatorOrCondition, $condition, 'OR');
    }

    function whereNull(mixed $field): static
    {
        return $this->where($field, 'IS', 'NULL', 'AND');
    }

    function whereNotNull(mixed $field): static
    {
        return $this->where($field, 'IS NOT', 'NULL', 'AND');
    }

    function orWhereNull(mixed $field): static
    {
        return $this->where($field, 'IS', 'NULL', 'OR');
    }

    function orWhereNotNull(mixed $field): static
    {
        return $this->where($field, 'IS NOT', 'NULL', 'OR');
    }

    function whereBetween(mixed $field, mixed $range = []): static
    {
        return $this->where($field, 'BETWEEN', $range, 'AND');
    }

    function whereNotBetween(mixed $field, mixed $range = []): static
    {
        return $this->where($field, 'NOT BETWEEN', $range, 'AND');
    }

    function orWhereBetween(mixed $field, mixed $range = []): static
    {
        return $this->where($field, 'BETWEEN', $range, 'OR');
    }

    function orWhereNotBetween(mixed $field, mixed $range = []): static
    {
        return $this->where($field, 'NOT BETWEEN', $range, 'OR');
    }

    function whereIn(mixed $field, mixed $inclusion = []): static
    {
        return $this->where($field, 'IN', $inclusion, 'AND');
    }

    function whereNotIn(mixed $field, mixed $inclusion = []): static
    {
        return $this->where($field, 'NOT IN', $inclusion, 'AND');
    }

    function orWhereIn(mixed $field, mixed $inclusion = []): static
    {
        return $this->where($field, 'IN', $inclusion, 'OR');
    }

    function orWhereNotIn(mixed $field, mixed $inclusion = []): static
    {
        return $this->where($field, 'NOT IN', $inclusion, 'OR');
    }

    function setWhere(array $conditions): static
    {
        $this->where = $conditions;
        return $this;
    }

    function resetWhere(): static
    {
        $this->where = [];
        return $this;
    }
}
