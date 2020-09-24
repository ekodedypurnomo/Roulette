<?php
namespace Roulette\Query\Option\Mixin;

use Roulette\Query\Condition;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasGroup
{
	protected $group = [];
	protected $having = [];

	function hasGroup()
	{
		return !empty($this->group);
	}

	function getGroup()
	{
		return $this->group;
	}

	function groupBy($group = null)
	{
		if (empty($field)) return $this;

		if (!is_array($this->group)) $this->group = array();

		$this->group[] = $group;

		return $this;
	}

	function group()
	{
		return call_user_func_array(array($this, 'groupBy'), func_get_args());
	}

	function resetGroup()
	{
		$this->group = array();
		return $this;
	}

	function hasHaving()
	{
		return !empty($this->having);
	}

	function getHaving()
	{
		return $this->having;
	}

	function having($field, $operatorOrValue = null, $value = null, $hook = 'AND')
	{
		if (!is_array($this->having)) $this->having = array();

		if (is_callable($field))
		{
			$builder = new $this($this->model);
			$having = call_user_func_array($field, [$builder]);
			$this->having[] = ['AND', $builder->having];
			return $this;
		}
		
		$condition = Condition::create(array(
			'boolean'=> $hook,
			'field'=> $field,
			'operator'=> $operatorOrValue,
			'value'=> $value,
			));
		$this->having[] = $condition;

		return $this;
	}

	function resetHaving()
	{
		$this->having = array();
		return $this;
	}
}