<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

use Roulette\Query\Condition;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasGroup
{
    protected array $group = [];
    protected array $having = [];

    function hasGroup(): bool
    {
        return !empty($this->group);
    }

    function getGroup(): array
    {
        return $this->group;
    }

    function groupBy(mixed $group = null): static
    {
        if (empty($field)) return $this;

        if (!is_array($this->group)) $this->group = [];

        $this->group[] = $group;

        return $this;
    }

    function group(mixed ...$args): static
    {
        return $this->groupBy(...$args);
    }

    function resetGroup(): static
    {
        $this->group = [];
        return $this;
    }

    function hasHaving(): bool
    {
        return !empty($this->having);
    }

    function getHaving(): array
    {
        return $this->having;
    }

    function having(mixed $field, mixed $operatorOrValue = null, mixed $value = null, string $hook = 'AND'): static
    {
        if (!is_array($this->having)) $this->having = [];

        if (is_callable($field))
        {
            $builder = new $this($this->model);
            $having = call_user_func_array($field, [$builder]);
            $this->having[] = ['AND', $builder->having];
            return $this;
        }

        $condition = Condition::create([
            'boolean'  => $hook,
            'field'    => $field,
            'operator' => $operatorOrValue,
            'value'    => $value,
        ]);
        $this->having[] = $condition;

        return $this;
    }

    function resetHaving(): static
    {
        $this->having = [];
        return $this;
    }
}
