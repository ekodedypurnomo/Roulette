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

use Roulette\Model\Association\AssociationAbstract;
use Roulette\Model\Association\Relation;
use Roulette\Model\Store;
use Roulette\Model;

/**
 * BelongsTo expresses the inverse side of a HasOne/HasMany relationship.
 * The foreign key lives on the current model and points to the parent model's PK.
 *
 * Example: Post belongsTo User via post.author_id → user.id
 *
 * @package \Roulette\Model\Association
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class BelongsTo extends AssociationAbstract
{
    const TYPE = 'BELONGSTO';

    function loadRelation(Relation $relation): static
    {
        $model   = $this->getModel();
        $field   = $this->getField();
        $record  = $relation->getRecord();
        $foreign = $record->get($field, false);

        $relation->setAssociated(true);
        $relation->setResource($model::load($foreign));

        return $this;
    }

    function patchRelation(Relation $relation, mixed $data = null): static
    {
        $model = $this->getModel();
        $relation->setAssociated(true);
        $relation->setResource(new $model($data));

        return $this;
    }

    function eagerLoad(Store $records): void
    {
        $model    = $this->getModel();
        $field    = $this->getField();

        $fkValues = [];
        $records->each(function($record) use ($field, &$fkValues) {
            $fk = $record->get($field, false);
            if ($fk !== null) $fkValues[] = $fk;
        });

        if (empty($fkValues)) return;

        $fkValues = array_unique($fkValues);
        $pk       = $model::getPrimary();
        $pkColumn = array_key_first($model::getFields()->mapToSource([$pk => '']));
        $parents  = $this->batchFetch($model, $pkColumn, $fkValues);

        $indexed = [];
        foreach ($parents as $r) {
            $indexed[$r->getId()] = $r;
        }

        $assoc = $this;
        $records->each(function($record) use ($assoc, $field, $indexed) {
            $fk  = $record->get($field, false);
            $rel = new Relation($assoc, $record);
            $rel->setAssociated(true);
            $rel->setResource($indexed[$fk] ?? null);
            $record->getRelations()->set($assoc->getName(), $rel);
        });
    }
}
