<?php
namespace Roulette\Query\Option\Mixin;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasSelect
{
	protected $column = "*";

	function hasSelect()
	{
		return !empty($this->column);
	}
	
	function getSelect()
	{
		if (empty($this->column)) return '*'; // as default

		return $this->column;
	}

	function select()
	{
		$args = func_get_args();

		foreach ($args as $select)
		{
			$this->addSelect($select);
		}

		return $this;
	}

	function addSelect($fieldName, $fieldAlias = null)
	{
		if (is_array($fieldName))
		{
			foreach ($fieldName as $f => $a)
			{
				$this->addSelect($f, $a);
			}
			return $this;
		}

		if (!is_array($this->column)) $this->column = array();

		if (empty($fieldAlias)) $fieldAlias = $fieldName;

		$this->column[$fieldAlias] = $fieldName; // alias as key to avoid distinct multiple alias from one field

		return $this;
	}

	function addSelectMax($fieldName, $fieldAlias)
	{
		return $this->addSelect('max('.$fieldName.')', $fieldAlias);		
	}

	function addSelectMin($fieldName, $fieldAlias)
	{
		return $this->addSelect('min('.$fieldName.')', $fieldAlias);		
	}

	function addSelectAvg($fieldName, $fieldAlias)
	{
		return $this->addSelect('avg('.$fieldName.')', $fieldAlias);		
	}

	function addSelectSum($fieldName, $fieldAlias)
	{
		return $this->addSelect('sum('.$fieldName.')', $fieldAlias);		
	}

	function addSelectCount($fieldName, $fieldAlias)
	{
		return $this->addSelect('count('.$fieldName.')', $fieldAlias);		
	}

	function resetSelect()
	{
		$this->column = "*";
		return $this;
	}
}