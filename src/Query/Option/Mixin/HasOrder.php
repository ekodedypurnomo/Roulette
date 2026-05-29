<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasOrder
{
    protected array $order = [];

    function hasOrder(): bool
    {
        return !empty($this->order);
    }

    function getOrder(): array
    {
        return $this->order;
    }

    function orderBy(mixed $field = null, string $direction = "ASC"): static
    {
        if (empty($field)) return $this;

        if (is_array($field))
        {
            foreach ($field as $f => $d)
            {
                if (is_numeric($f)) $f = $d;
                $this->orderBy($f, $d);
            }
            return $this;
        }

        if (!is_array($this->order)) $this->order = [];

        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'ASC'; // ascending as default

        // remove first to reorder position
        if (array_key_exists($field, $this->order)) unset($this->order[$field]);

        $this->order[$field] = $direction;

        return $this;
    }

    function order(mixed ...$args): static
    {
        return $this->orderBy(...$args);
    }

    function sort(mixed ...$args): static
    {
        return $this->orderBy(...$args);
    }

    function resetOrder(): static
    {
        $this->order = [];
        return $this;
    }
}
