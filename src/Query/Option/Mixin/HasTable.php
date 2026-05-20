<?php
namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasTable
{
	protected $table = '';

	function table($table)
	{
		return $this->setTable($table);
	}

	function hasTable()
	{
		return !empty($this->table);
	}

	function setTable($table)
	{
		$this->table = $table;
		return $this;
	}

	function getTable()
	{
		return $this->table;
	}

	function resetTable()
	{
		$this->table = '';
		return $this;
	}
}