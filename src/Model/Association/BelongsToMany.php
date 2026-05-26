<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Model\Association;

use Roulette\Model;
use Roulette\Model\Store;
use Roulette\Query\Operation;

/**
 * Many-to-many relationship resolved through an intermediate pivot table.
 *
 * Declare in prototype:
 *   'roles' => [
 *       'type'        => 'belongsToMany',
 *       'model'       => 'App\Role',
 *       'pivotTable'  => 'user_roles',
 *       'foreignKey'  => 'user_id',   // pivot column → THIS model
 *       'relatedKey'  => 'role_id',   // pivot column → RELATED model
 *   ]
 *
 * Usage:
 *   $user->lookup('roles')           // Store of Role models
 *   $user->attach('roles', $roleId)  // insert pivot row
 *   $user->detach('roles', $roleId)  // delete pivot row
 *   $user->sync('roles', $roleIds)   // replace all pivot rows
 *
 * @package \Roulette\Model\Association
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class BelongsToMany extends AssociationAbstract
{
    const TYPE = 'BELONGSTOMANY';

    /** Intermediate pivot table name. */
    protected ?string $pivotTable = null;

    /** Pivot column that references THIS model's primary key. */
    protected ?string $foreignKey = null;

    /** Pivot column that references the RELATED model's primary key. */
    protected ?string $relatedKey = null;

    function getPivotTable(): ?string { return $this->pivotTable; }
    function getForeignKey(): ?string { return $this->foreignKey; }
    function getRelatedKey(): ?string { return $this->relatedKey; }

    /**
     * Lazy-load: two queries — fetch related IDs from pivot, then load related records.
     */
    function loadRelation(Relation $relation): static
    {
        $record     = $relation->getRecord();
        $recordId   = $record->getId();
        $model      = $this->getModel();
        $pivot      = $this->pivotTable;
        $fk         = $this->foreignKey;
        $rk         = $this->relatedKey;

        $relatedIds = $this->fetchRelatedIds($pivot, $fk, $rk, $recordId);

        $store = new Store(null, $this->model);
        if (!empty($relatedIds)) {
            $relatedModels = $this->fetchRelatedModels($model, $relatedIds);
            foreach ($relatedModels as $item) {
                $store->add($item);
            }
        }

        $relation->setAssociated(true);
        $relation->setResource($store);

        return $this;
    }

    function patchRelation(Relation $relation, mixed $data = null): static
    {
        $model = $this->getModel();
        $store = new Store(null, $this->model);

        foreach ((array) $data as $item) {
            $store->add(new $model($item));
        }

        $relation->setAssociated(true);
        $relation->setResource($store);

        return $this;
    }

    /**
     * Insert a row into the pivot table linking $ownerRecord to $relatedId.
     * $pivotData can carry extra pivot columns (e.g. 'role' => 'editor').
     */
    function attach(Model $ownerRecord, mixed $relatedId, array $pivotData = []): bool
    {
        $data = array_merge($pivotData, [
            $this->foreignKey => $ownerRecord->getId(),
            $this->relatedKey => $relatedId,
        ]);

        $pivot = $this->pivotTable;
        $op    = Operation::create('insert')->buildQuery(function($qop) use($pivot, $data) {
            $qop->table($pivot)->set($data);
        })->execute();

        return $op->isSuccess();
    }

    /**
     * Remove pivot row(s) linking $ownerRecord to $relatedId.
     * If $relatedId is null, removes ALL pivot rows for $ownerRecord.
     */
    function detach(Model $ownerRecord, mixed $relatedId = null): int
    {
        $pivot     = $this->pivotTable;
        $fk        = $this->foreignKey;
        $rk        = $this->relatedKey;
        $condition = [$fk => $ownerRecord->getId()];

        if (!is_null($relatedId)) {
            $condition[$rk] = $relatedId;
        }

        $op = Operation::create('delete')->buildQuery(function($qop) use($pivot, $condition) {
            $qop->table($pivot)->where($condition);
        })->execute();

        return (int) $op->getAffectedRows();
    }

    /**
     * Replace all pivot rows for $ownerRecord with exactly $relatedIds.
     * Equivalent to detach(all) + attach(each).
     */
    function sync(Model $ownerRecord, array $relatedIds, array $pivotData = []): void
    {
        $this->detach($ownerRecord);
        foreach ($relatedIds as $id) {
            $this->attach($ownerRecord, $id, $pivotData);
        }
    }

    /**
     * Batch-load for eager loading: given a list of owner IDs, return a map of
     * ownerPrimaryKey → array of related Model instances.
     */
    function batchLoad(string $model, array $ownerIds): array
    {
        $pivot = $this->pivotTable;
        $fk    = $this->foreignKey;
        $rk    = $this->relatedKey;

        $pivotRows = $this->fetchPivotRows($pivot, $fk, $rk, $ownerIds);

        // group related IDs by owner
        $ownerToRelated = [];
        $allRelatedIds  = [];
        foreach ($pivotRows as $row) {
            $ownerId   = $row[$fk];
            $relatedId = $row[$rk];
            $ownerToRelated[$ownerId][] = $relatedId;
            $allRelatedIds[]            = $relatedId;
        }

        if (empty($allRelatedIds)) {
            return array_fill_keys($ownerIds, []);
        }

        // batch-fetch related records
        $relatedByKey  = [];
        foreach ($this->fetchRelatedModels($model, array_unique($allRelatedIds)) as $item) {
            $relatedByKey[$item->getId()] = $item;
        }

        // map back to owner IDs
        $result = [];
        foreach ($ownerIds as $ownerId) {
            $ids = $ownerToRelated[$ownerId] ?? [];
            $result[$ownerId] = array_map(fn($id) => $relatedByKey[$id] ?? null, $ids);
            $result[$ownerId] = array_values(array_filter($result[$ownerId]));
        }
        return $result;
    }

    // -------------------------------------------------------------------------

    private function fetchRelatedModels(string $model, array $relatedIds): array
    {
        $table        = $model::getTable();
        $selectFields = array_flip($model::getFields()->filterSelectable()->getSource());
        $pk           = array_key_first($model::getFields()->mapToSource([$model::getPrimary() => '']));

        $op = Operation::create('select')->buildQuery(function($qop) use($table, $selectFields, $pk, $relatedIds) {
            $qop->table($table)->select($selectFields)->whereIn($pk, $relatedIds);
        })->execute();

        $result = [];
        foreach ($op->getRecords() as $row) {
            $result[] = new $model((array) $row, true);
        }
        return $result;
    }

    private function fetchRelatedIds(string $pivot, string $fk, string $rk, mixed $ownerId): array
    {
        $op = Operation::create('select')->buildQuery(function($qop) use($pivot, $fk, $rk, $ownerId) {
            $qop->table($pivot)->select([$rk => $rk])->where([$fk => $ownerId]);
        })->execute();

        return array_column($op->getRecords(), $rk);
    }

    private function fetchPivotRows(string $pivot, string $fk, string $rk, array $ownerIds): array
    {
        if (empty($ownerIds)) return [];

        $op = Operation::create('select')->buildQuery(function($qop) use($pivot, $fk, $rk, $ownerIds) {
            $qop->table($pivot)
                ->select([$fk => $fk, $rk => $rk])
                ->whereIn($fk, $ownerIds);
        })->execute();

        return $op->getRecords();
    }
}
