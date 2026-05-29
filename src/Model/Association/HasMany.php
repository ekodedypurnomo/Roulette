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

/**
 * Roulette\Association package contain any class which support relation between models
 */
namespace Roulette\Model\Association;

use Roulette\Model\Association\AssociationAbstract;
use Roulette\Model\Association\Relation;
use Roulette\Model\Store;
use Roulette\Model;

/**
 * Assosiation was a description of a relationship between a model one with the other models
 * which in this function there-many relationship model or one model
 *
 * @package \Roulette\Model\Association
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class HasMany extends AssociationAbstract
{
    const TYPE = 'HASMANY';

    function loadRelation(Relation $relation): static
    {
        $model = $this->getModel();
        $field = $this->getField();
        $record = $relation->getRecord();
        $recordId = $record->getId();

        $relation->setAssociated(true);
        $relation->setResource($model::find([$field => $recordId]));

        return $this;
    }

    function patchRelation(Relation $relation, mixed $data = null): static
    {
        $modelCollection = new Store();
        $model = $this->getModel();

        foreach ($data as $key => $item)
        {
            $modelCollection->add(new $model($item));
        }

        $relation->setAssociated(true);
        $relation->setResource($modelCollection);

        return $this;
    }

    function eagerLoad(Store $records): void
    {
        $model = $this->getModel();
        $field = $this->getField();

        $ids = [];
        $records->each(function($record) use (&$ids) {
            $id = $record->getId();
            if ($id !== null) $ids[] = $id;
        });

        if (empty($ids)) return;

        $fieldColumn   = array_key_first($model::getFields()->mapToSource([$field => '']));
        $relatedModels = $this->batchFetch($model, $fieldColumn, $ids);

        $grouped = [];
        foreach ($relatedModels as $r) {
            $fk = $r->get($field, false);
            $grouped[$fk][] = $r;
        }

        $assoc = $this;
        $records->each(function($record) use ($assoc, $model, $grouped) {
            $id       = $record->getId();
            $items    = $grouped[$id] ?? [];
            $rel      = new Relation($assoc, $record);
            $relStore = new Store(null, $model);
            foreach ($items as $r) $relStore->add($r);
            $rel->setAssociated(true);
            $rel->setResource($relStore);
            $record->getRelations()->set($assoc->getName(), $rel);
        });
    }
}
