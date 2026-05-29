<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Collection;
use Roulette\Model\Store;
use Roulette\Model\Association\AssociationAbstract;
use Roulette\Model\Association\BelongsToMany;
use Roulette\Model\Association\Relation;

trait ManagesRelations
{
    protected ?Collection $relations = null;

    function getRelations(): Collection
    {
        if (!($this->relations instanceof Collection))
        {
            $this->relations = new Collection();
        }
        return $this->relations;
    }

    function getRelation(mixed $associationName = null): mixed
    {
        return $this->getRelations()->get($associationName);
    }

    function associate(mixed $association = null, mixed $reload = true, mixed $options = null): mixed
    {
        $association = $this->getAssociation($association, $options);

        if ($association)
        {
            return $association->associate($this, $reload);
        }
    }

    function lookup(mixed $association = null, mixed $reload = false, mixed $options = null): mixed
    {
        $assoc = $this->associate($association, $reload, $options);

        if ($assoc)
        {
            return $assoc->getResource();
        }
    }

    function attach(string $associationName, mixed $relatedId, array $pivotData = []): bool
    {
        $assoc = static::getAssociation($associationName);
        if (!($assoc instanceof BelongsToMany)) {
            throw new \InvalidArgumentException("Association '$associationName' is not a belongsToMany.");
        }
        return $assoc->attach($this, $relatedId, $pivotData);
    }

    function detach(string $associationName, mixed $relatedId = null): int
    {
        $assoc = static::getAssociation($associationName);
        if (!($assoc instanceof BelongsToMany)) {
            throw new \InvalidArgumentException("Association '$associationName' is not a belongsToMany.");
        }
        return $assoc->detach($this, $relatedId);
    }

    function sync(string $associationName, array $relatedIds, array $pivotData = []): void
    {
        $assoc = static::getAssociation($associationName);
        if (!($assoc instanceof BelongsToMany)) {
            throw new \InvalidArgumentException("Association '$associationName' is not a belongsToMany.");
        }
        $assoc->sync($this, $relatedIds, $pivotData);
    }

    public static function applyEagerLoads(Store $store, array $relationNames): void
    {
        if (empty($relationNames) || $store->count() === 0) {
            return;
        }

        foreach ($relationNames as $name) {
            $assoc = static::getAssociations()->get($name);
            if ($assoc) $assoc->eagerLoad($store);
        }
    }
}
