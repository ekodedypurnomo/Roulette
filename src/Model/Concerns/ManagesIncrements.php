<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Query\Operation;
use Roulette\Query\RawExpression;

trait ManagesIncrements
{
    static function incrementWhere(array $condition, string $field, int|float $amount = 1): int
    {
        return static::_atomicAdjust($condition, $field, $amount);
    }

    static function decrementWhere(array $condition, string $field, int|float $amount = 1): int
    {
        return static::_atomicAdjust($condition, $field, -$amount);
    }

    function increment(string $field, int|float $amount = 1): static
    {
        static::_atomicAdjust([static::getPrimary() => $this->getId()], $field, $amount);
        $current = $this->get($field);
        $this->set($field, $current + $amount, commit: true);
        return $this;
    }

    function decrement(string $field, int|float $amount = 1): static
    {
        return $this->increment($field, -$amount);
    }

    static private function _atomicAdjust(array $condition, string $field, int|float $amount): int
    {
        $sourceMap = static::getFields()->getSource();

        if (!array_key_exists($field, $sourceMap)) {
            throw new \InvalidArgumentException(
                sprintf("Field '%s' is not defined on %s.", $field, static::class)
            );
        }

        $table     = static::getTable();
        $condition = static::getFields()->mapToSource($condition);
        $colName   = $sourceMap[$field];
        $sign      = $amount >= 0 ? '+' : '-';
        $abs       = abs($amount);
        // {col} is replaced by the executor with a properly quoted identifier
        $expr      = new RawExpression("{col} $sign $abs");

        $operation = Operation::create('update')->buildQuery(function($qop) use($table, $condition, $colName, $expr) {
            $qop->table($table)
                ->set($colName, $expr)
                ->where($condition);
        })->execute();

        return (int) $operation->getAffectedRows();
    }
}
