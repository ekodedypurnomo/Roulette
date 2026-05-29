<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasLimit
{
    protected mixed $limit = false;

    protected mixed $skip = 0;

    function hasLimit(): bool
    {
        return is_numeric($this->limit);
    }

    function getLimit(): mixed
    {
        return $this->limit;
    }

    function limit(mixed $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    function take(mixed ...$args): static
    {
        return $this->limit(...$args);
    }

    function hasOffset(): bool
    {
        return !empty($this->skip);
    }

    function getOffset(): int
    {
        return (int) $this->skip;
    }

    function offset(mixed $skip = 0): static
    {
        $this->skip = $skip;

        return $this;
    }

    function skip(mixed ...$args): static
    {
        return $this->offset(...$args);
    }

    function resetLimit(): void
    {
        $this->limit = false;
        $this->skip = false;
    }
}
