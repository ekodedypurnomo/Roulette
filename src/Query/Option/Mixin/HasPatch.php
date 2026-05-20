<?php
namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasPatch
{
	protected $patch = [];
	
	function set($column, $value = null)
	{
		if (is_object($column))
		{
			$column = (array) $column;
		}
		if (!is_array($column))
		{
			$column = array($column => $value);
		}

		foreach ($column as $c => $v)
		{
			$this->patch[$c] = $v;
		}
		return $this;
	}

	function addPatch()
	{
		return call_user_func_array(array($this, 'set'), func_get_args());
	}

	function getPatch()
	{
		return $this->patch;
	}
}