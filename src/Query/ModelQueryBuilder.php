<?php

declare(strict_types=1);

namespace Roulette\Query;

use Roulette\Collection;
use Roulette\Model\Store;
use Roulette\Model\Paginator;

/**
 * Model-aware query builder. Extends Builder with hydration — terminators
 * like get() and first() return hydrated Model instances instead of raw arrays.
 *
 * Obtained via Model::query() or the static proxy methods (Model::where(), etc.).
 * Also returned by Model::withoutScope() and Model::with() for fluent chaining.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 */
class ModelQueryBuilder extends Builder
{
    private string $modelClass;
    private array $disabledScopes = [];
    private array $pendingEagerLoads = [];

    static function forModel(string $modelClass, mixed $table = null): static
    {
        $instance             = new static($table ?? $modelClass::getTable());
        $instance->modelClass = $modelClass;
        return $instance;
    }

    function __clone()
    {
        if ($this->option !== null) {
            $this->option = clone $this->option;
        }
    }

    function disableScopes(array $scopes): static
    {
        $this->disabledScopes = array_merge($this->disabledScopes, $scopes);
        return $this;
    }

    function withEagerLoads(array $relations): static
    {
        $this->pendingEagerLoads = array_merge($this->pendingEagerLoads, $relations);
        return $this;
    }

    /**
     * Execute and return a Store of hydrated model instances.
     */
    function get(): Store
    {
        $modelClass     = $this->modelClass;
        $table          = $modelClass::getTable();
        $selectFields   = array_flip($modelClass::getFields()->filterSelectable()->getSource());
        $disabledScopes = $this->disabledScopes;
        $builderWhere   = $this->getOption()->getWhere();
        $builderOrder   = $this->getOption()->getOrder();
        $builderLimit   = $this->getOption()->getLimit();
        $builderOffset  = $this->getOption()->getOffset();

        $operation = Operation::create('select')->buildQuery(
            function($qop) use($table, $selectFields, $disabledScopes, $builderWhere, $builderOrder, $builderLimit, $builderOffset, $modelClass) {
                $qop->table($table)
                    ->select($selectFields)
                    ->setWhere($builderWhere)
                    ->order($builderOrder)
                    ->take($builderLimit)
                    ->skip($builderOffset);
                $modelClass::applyScopes($qop, $disabledScopes);
            }
        )->execute();

        $store = new Store(null, $modelClass);
        Collection::create($operation->getRecords())->each(function($row) use($modelClass, $store) {
            $store->add(new $modelClass((array) $row, true));
        });

        if (!empty($this->pendingEagerLoads)) {
            $modelClass::applyEagerLoads($store, $this->pendingEagerLoads);
        }

        return $store;
    }

    /**
     * Execute and return the first hydrated model instance, or null.
     */
    function first(): mixed
    {
        $modelClass     = $this->modelClass;
        $table          = $modelClass::getTable();
        $selectFields   = array_flip($modelClass::getFields()->filterSelectable()->getSource());
        $disabledScopes = $this->disabledScopes;
        $builderWhere   = $this->getOption()->getWhere();

        $operation = Operation::create('select')->buildQuery(
            function($qop) use($table, $selectFields, $disabledScopes, $builderWhere, $modelClass) {
                $qop->table($table)
                    ->select($selectFields)
                    ->setWhere($builderWhere)
                    ->take(1);
                $modelClass::applyScopes($qop, $disabledScopes);
            }
        )->execute();

        $row = $operation->getRecord();
        return $row ? new $modelClass((array) $row, true) : null;
    }

    /**
     * Execute a COUNT(*) and return the integer result.
     */
    function count(): int
    {
        $modelClass     = $this->modelClass;
        $table          = $modelClass::getTable();
        $disabledScopes = $this->disabledScopes;
        $builderWhere   = $this->getOption()->getWhere();

        $operation = Operation::create('select')->buildQuery(
            function($qop) use($table, $disabledScopes, $builderWhere, $modelClass) {
                $qop->table($table)
                    ->addSelectCount('*', '__count')
                    ->setWhere($builderWhere);
                $modelClass::applyScopes($qop, $disabledScopes);
            }
        )->execute();

        $row = $operation->getRecord();
        return (int) ($row['__count'] ?? 0);
    }

    /**
     * Paginate: runs COUNT + LIMIT/OFFSET, returns Paginator.
     */
    function paginate(int $perPage = 15, int $page = 1): Paginator
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);

        $total    = $this->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $offset   = ($page - 1) * $perPage;

        $items = (clone $this)->take($perPage)->skip($offset)->get();

        return new Paginator(
            items:       $items,
            total:       $total,
            perPage:     $perPage,
            currentPage: $page,
            lastPage:    $lastPage,
        );
    }

    /**
     * Process results in fixed-size batches. Callback receives a Store.
     * Return false from callback to stop early.
     */
    function chunk(int $size, callable $callback): int
    {
        $offset    = 0;
        $processed = 0;

        while (true) {
            $batch = (clone $this)->take($size)->skip($offset)->get();

            if ($batch->count() === 0) break;

            $result     = $callback($batch);
            $processed += $batch->count();
            $offset    += $size;

            if ($result === false) break;
            if ($batch->count() < $size) break;
        }

        return $processed;
    }

    /**
     * Yield one hydrated record at a time via a Generator.
     */
    function cursor(int $batchSize = 100): \Generator
    {
        $offset = 0;

        while (true) {
            $batch = (clone $this)->take($batchSize)->skip($offset)->get();

            if ($batch->count() === 0) break;

            foreach ($batch as $record) {
                yield $record;
            }

            $offset += $batchSize;

            if ($batch->count() < $batchSize) break;
        }
    }
}
