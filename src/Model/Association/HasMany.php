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

        $relation->associated = true;
        $relation->resource = $model::find([$field => $recordId]);

        return $this;
    }

    function patchRelation(Relation $relation, mixed $data = null): static
    {
        $modelCollection = new Store();
        $model = $this->getModel();

        foreach ($reload as $key => $data)
        {
            $modelCollection->add(new $model($data));
        }

        $relation->associated = true;
        $relation->resource = $modelCollection;

        return $this;
    }
}
