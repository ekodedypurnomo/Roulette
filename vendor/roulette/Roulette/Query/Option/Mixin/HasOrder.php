<?php
namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasOrder
{

	protected $order = [];

	function hasOrder()
	{
		return !empty($this->order);
	}

	function getOrder()
	{
		return $this->order;
	}

	function orderBy($field = null, $direction = "ASC")
	{
		if (empty($field)) return $this;

		if (is_array($field))
		{
			foreach ($field as $f => $d)
			{
				if (is_numeric($f)) $f = $d;
				$this->orderBy($f, $d);	
			}
			return $this;
		}

		if (!is_array($this->order)) $this->order = array();

		$direction = strtoupper($direction);
		if (!in_array($direction, array('ASC','DESC')) ) $direction = 'ASC'; // ascending as default

		// remove first to reorder position
		if (array_key_exists($field, $this->order)) unset($this->order[$field]);

		$this->order[$field] = $direction;

		return $this;
	}

	function order()
	{
		return call_user_func_array(array($this, 'orderBy'), func_get_args());
	}

	function sort()
	{
		return call_user_func_array(array($this, 'orderBy'), func_get_args());
	}

	function resetOrder()
	{
		$this->order = array();
		return $this;
	}

}