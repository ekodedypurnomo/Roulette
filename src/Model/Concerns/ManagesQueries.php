<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Collection;
use Roulette\Model\Store;
use Roulette\Model\Paginator;
use Roulette\Query\ModelQueryBuilder;
use Roulette\Query\Operation;

trait ManagesQueries
{
    static function load(mixed $id = null): ?static
    {
        if ($_c = static::fetchFromCache($id))
        {
            return $_c;
        }

        $table     = static::getTable();
        $field     = array_flip(static::getFields()->filterSelectable()->getSource());
        $condition = static::getFields()->mapToSource(
            is_array($id) ? $id : [static::getPrimary() => $id]
        );
        $class = static::class;

        $operation = Operation::create('select')->buildQuery(function($qop) use($table, $field, $condition, $class)
        {
            $qop->table($table)
                ->select($field)
                ->where($condition);
            $class::applyScopes($qop, []);
        })->execute();

        if ($operation->getRecord())
        {
            return new static((array) $operation->getRecord(), $original = true);
        }

        return null;
    }

    static function loadOrFail(mixed $id = null): static
    {
        $record = static::load($id);
        if ($record === null) {
            throw new \Roulette\Exception\ModelNotFoundException(static::class, $id);
        }
        return $record;
    }

    static function find(mixed $condition = null, mixed $order = null, mixed $take = null, mixed $skip = null, mixed $group = null, mixed $having = null): Store
    {
        $class     = static::class;
        $store     = new Store(null, $class);
        $table     = static::getTable();
        $field     = array_flip(static::getFields()->filterSelectable()->getSource());
        $condition = static::getFields()->mapToSource($condition);
        $order     = static::getFields()->mapToSource($order);

        $operation = Operation::create('select')->buildQuery(function($qop) use($table, $field, $condition, $take, $skip, $order, $group, $having, $class)
        {
            $qop->table($table)
                ->select($field)
                ->where($condition)
                ->take($take)
                ->skip($skip)
                ->groupBy($group)
                ->having($having);
            $class::applyScopes($qop, []);
        })->execute();

        Collection::create($operation->getRecords())->each(function($v, $k, $all, $c) use($class, $store)
        {
            $r = new $class((array) $v, true);
            $store->add($r);
        });

        // Fire after:find on a temporary instance so class-level listeners receive the Store
        if ($store->count() > 0) {
            $store->first()->fireModelEvent('after:find', $store);
        }

        return $store;
    }

    static function count(mixed $condition = null): int
    {
        $table     = static::getTable();
        $condition = static::getFields()->mapToSource($condition);
        $class     = static::class;

        $operation = Operation::create('select')->buildQuery(function($qop) use($table, $condition, $class) {
            $qop->table($table)
                ->addSelectCount('*', '__count')
                ->where($condition);
            $class::applyScopes($qop, []);
        })->execute();

        $row = $operation->getRecord();
        return (int) ($row['__count'] ?? 0);
    }

    static function paginate(int $perPage = 15, int $page = 1, mixed $condition = null, mixed $order = null): Paginator
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);

        $total    = static::count($condition);
        $lastPage = (int) ceil($total / $perPage);
        $lastPage = max(1, $lastPage);
        $offset   = ($page - 1) * $perPage;

        $items = static::find($condition, $order, $perPage, $offset);

        return new Paginator(
            items:       $items,
            total:       $total,
            perPage:     $perPage,
            currentPage: $page,
            lastPage:    $lastPage,
        );
    }

    static function chunk(int $size, callable $callback, mixed $condition = null, mixed $order = null): int
    {
        $offset    = 0;
        $processed = 0;

        while (true) {
            $batch = static::find($condition, $order, $size, $offset);

            if ($batch->count() === 0) break;

            $result     = $callback($batch);
            $processed += $batch->count();
            $offset    += $size;

            if ($result === false) break;
            if ($batch->count() < $size) break;
        }

        return $processed;
    }

    static function cursor(mixed $condition = null, mixed $order = null, int $batchSize = 100): \Generator
    {
        $offset = 0;

        while (true) {
            $batch = static::find($condition, $order, $batchSize, $offset);

            if ($batch->count() === 0) break;

            foreach ($batch as $record) {
                yield $record;
            }

            $offset += $batchSize;

            if ($batch->count() < $batchSize) break;
        }
    }

    static function query(mixed $mode = null): ModelQueryBuilder
    {
        return ModelQueryBuilder::forModel(static::class);
    }

}
