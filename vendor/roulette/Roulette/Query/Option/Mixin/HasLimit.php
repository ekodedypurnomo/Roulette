<?php
namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasLimit
{
	protected $limit = false;

	protected $skip = 0;

	function hasLimit()
	{
		return is_numeric($this->limit);
	}

	function getLimit()
	{
		return $this->limit;
	}

	function limit($limit)
	{
		$this->limit = $limit;

		return $this;	
	}

	function take()
	{
		return call_user_func_array(array($this, 'limit'), func_get_args());
	}

	function hasOffset()
	{
		return !empty($this->skip);
	}

	function getOffset()
	{
		return (int) $this->skip;
	}

	function offset($skip = 0)
	{
		$this->skip = $skip;

		return $this;	
	}

	function skip()
	{
		return call_user_func_array(array($this, 'offset'), func_get_args());
	}

	function resetLimit()
	{
		$this->limit = false;
		$this->skip = false;
	}
}