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
use Roulette\Model;

/**
 * Assosiation was a description of a relationship between a model one with the other models
 * which in this function there-many relationship model or one model
 *
 * @package \Roulette\Model\Association
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class HasOne extends AssociationAbstract
{
    const TYPE = 'HASONE';

    function loadRelation(Relation $relation): static
    {
        $model = $this->getModel();
        $field = $this->getField();
        $record = $relation->getRecord();
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
}
