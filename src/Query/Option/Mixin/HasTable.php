<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasTable
{
    protected mixed $table = '';

    function table(mixed $table): static
    {
        return $this->setTable($table);
    }

    function hasTable(): bool
    {
        return !empty($this->table);
    }

    function setTable(mixed $table): static
    {
        $this->table = $table;
        return $this;
    }

    function getTable(): mixed
    {
        return $this->table;
    }

    function resetTable(): static
    {
        $this->table = '';
        return $this;
    }
}
