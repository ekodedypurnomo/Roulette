<?php
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
	/**
	 * expresses the relationship between the different models
	 * 
	 * @var null
	 */
	public $association = null;

	/**
	 * Check is true or false model has association
	 * @var boolean
	 */
	public $associated = false;

	/**
	 * a collection of some of the fields are complete
	 * @var null
	 */
	public $record = null;

	/**
	 * link data from record
	 * @var null
	 */
	public $resource = null;
	
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

		return $this;
	}

	/**
	 * Take an existing associations
	 * 
	 * @return array model name that associated with
	 */
	function getAssociation()
	{
		return $this->association;
	}

	/**
	 * Take an existing record
	 * 
	 * @return array record
	 */
	function getRecord()
	{
		return $this->record;
	}

	/**
	 * retrieve data existing associations
	 * 
	 * @return boolean true if it's associated
	 */
	function isAssociated()
	{
		return (boolean) $this->associated;
	}

	/**
	 * Get the associated data from the record
	 * 
	 * @return array associated record 
	 */
	function getResource()
	{
		return $this->resource;
	}

	function reset()
	{
		$this->associated = false;
        $this->resource = null;
        return $this;
	}
}