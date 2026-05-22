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

        $relation->associated = true;
        $relation->resource   = $model::load($foreign);

        return $this;
    }

    function patchRelation(Relation $relation, mixed $data = null): static
    {
        $model = $this->getModel();
        $relation->associated = true;
        $relation->resource   = new $model($data);

        return $this;
    }
}
