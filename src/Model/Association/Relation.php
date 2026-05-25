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

use Roulette\Base;
use Roulette\Model\Association\AssociationAbstract;
use Roulette\Model;

/**
 * AssociationValue is a subsidiary of the association
 *
 * @package \Roulette\Model\Association
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Relation extends Base
{
    protected mixed $association = null;
    protected bool $associated = false;
    protected mixed $record = null;
    protected mixed $resource = null;

    /**
     * __construct for function creates a new object field
     *
     * @param Association $association expresses the relationship between the different models
     * @param Model       $record      models represent multiple objects
     */
    function __construct(AssociationAbstract $association, Model $record)
    {
        $this->association = $association;
        $this->record = $record;
    }

    /**
     * Take an existing associations
     *
     * @return array model name that associated with
     */
    function getAssociation(): mixed
    {
        return $this->association;
    }

    /**
     * Take an existing record
     *
     * @return array record
     */
    function getRecord(): mixed
    {
        return $this->record;
    }

    /**
     * retrieve data existing associations
     *
     * @return boolean true if it's associated
     */
    function isAssociated(): bool
    {
        return (bool) $this->associated;
    }

    /**
     * Get the associated data from the record
     *
     * @return array associated record
     */
    function getResource(): mixed
    {
        return $this->resource;
    }

    function setAssociated(bool $value): static
    {
        $this->associated = $value;
        return $this;
    }

    function setResource(mixed $value): static
    {
        $this->resource = $value;
        return $this;
    }

    function reset(): static
    {
        $this->associated = false;
        $this->resource = null;
        return $this;
    }
}
