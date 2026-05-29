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
        $table     = static::getTable();
        $condition = static::getFields()->mapToSource($condition);
        $col       = static::getFields()->mapToSource([$field => null]);
        $colName   = array_key_first($col);
        $sign      = $amount >= 0 ? '+' : '-';
        $abs       = abs($amount);
        $expr      = new RawExpression("\"$colName\" $sign $abs");

        $operation = Operation::create('update')->buildQuery(function($qop) use($table, $condition, $colName, $expr) {
            $qop->table($table)
                ->set($colName, $expr)
                ->where($condition);
        })->execute();

        return (int) $operation->getAffectedRows();
    }
}
