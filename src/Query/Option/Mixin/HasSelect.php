<?php

declare(strict_types=1);

namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasSelect
{
    protected mixed $column = "*";

    function hasSelect(): bool
    {
        return !empty($this->column);
    }

    function getSelect(): mixed
    {
        if (empty($this->column)) return '*'; // as default

        return $this->column;
    }

    function select(mixed ...$args): static
    {
        foreach ($args as $select)
        {
            $this->addSelect($select);
        }

        return $this;
    }

    function addSelect(mixed $fieldName, mixed $fieldAlias = null): static
    {
        if (is_array($fieldName))
        {
            foreach ($fieldName as $f => $a)
            {
                $this->addSelect($f, $a);
            }
            return $this;
        }

        if (!is_array($this->column)) $this->column = [];

        if (empty($fieldAlias)) $fieldAlias = $fieldName;

        $this->column[$fieldAlias] = $fieldName; // alias as key to avoid distinct multiple alias from one field

        return $this;
    }

    function addSelectMax(mixed $fieldName, mixed $fieldAlias): static
    {
        return $this->addSelect('max(' . $fieldName . ')', $fieldAlias);
    }

    function addSelectMin(mixed $fieldName, mixed $fieldAlias): static
    {
        return $this->addSelect('min(' . $fieldName . ')', $fieldAlias);
    }

    function addSelectAvg(mixed $fieldName, mixed $fieldAlias): static
    {
        return $this->addSelect('avg(' . $fieldName . ')', $fieldAlias);
    }

    function addSelectSum(mixed $fieldName, mixed $fieldAlias): static
    {
        return $this->addSelect('sum(' . $fieldName . ')', $fieldAlias);
    }

    function addSelectCount(mixed $fieldName, mixed $fieldAlias): static
    {
        return $this->addSelect('count(' . $fieldName . ')', $fieldAlias);
    }

    function resetSelect(): static
    {
        $this->column = "*";
        return $this;
    }
}
