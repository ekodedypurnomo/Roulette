<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasPatch
{
    protected array $patch = [];

    function set(mixed $column, mixed $value = null): static
    {
        if (is_object($column))
        {
            $column = (array) $column;
        }
        if (!is_array($column))
        {
            $column = [$column => $value];
        }

        foreach ($column as $c => $v)
        {
            $this->patch[$c] = $v;
        }
        return $this;
    }

    function addPatch(mixed ...$args): static
    {
        return $this->set(...$args);
    }

    function getPatch(): array
    {
        return $this->patch;
    }

    function resetPatch(): static
    {
        $this->patch = [];
        return $this;
    }
}
