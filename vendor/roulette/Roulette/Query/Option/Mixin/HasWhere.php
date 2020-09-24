<?php
namespace Roulette\Query\Option\Mixin;

use Roulette\Query\Condition;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasWhere
{
	protected $where = [];
	
	function hasWhere()
	{
		return !empty($this->where);
	}

	function getWhere()
	{
		return $this->where;
	}

	function where($field = null, $operatorOrCondition = null, $value = null, $hook = 'AND')
	{
		if (is_array($field))
		{
			foreach ($field as $f => $v)
			{
				$this->where($f, $v);
			}
			return $this;
		}

		# logic

		if (empty($field)) return $this;

		if (!is_array($this->where)) $this->where = array();

		$hook = strtoupper($hook);

		if (is_callable($field))
		{
			$builder = new $this($this->table);
			$where = call_user_func_array($field, array($builder));
			$this->where[] = Condition::create(array(
				'hook'=> $hook, 
				'field'=> $builder->getWhere()
				));
		}
		else
		{
			$condition = Condition::create(array(
				'hook'=> $hook, 
				'field'=> $field, 
				'operator'=> $operatorOrCondition,
				'value'=> $value
				));
			$this->where[] = $condition;
		}
		
		return $this;
	}

	function andWhere($field, $operatorOrCondition = null, $condition = null)
	{
		return call_user_func_array(array($this, 'where'), array($field, $operatorOrCondition, $condition, 'AND'));
	}

	function orWhere($field, $operatorOrCondition = null, $condition = null)
	{
		return call_user_func_array(array($this, 'where'), array($field, $operatorOrCondition, $condition, 'OR'));
	}

	function whereNull($field)
	{
		return call_user_func_array(array($this,'where'), array($field, 'IS','NULL', 'AND'));
	}

	function whereNotNull($field)
	{
		return call_user_func_array(array($this,'where'), array($field, 'IS NOT','NULL', 'AND'));
	}

	function orWhereNull($field)
	{
		return call_user_func_array(array($this,'where'), array($field, 'IS','NULL', 'OR'));
	}

	function orWhereNotNull($field)
	{
		return call_user_func_array(array($this,'where'), array($field, 'IS NOT','NULL', 'OR'));
	}

	function whereBetween($field, $range = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'BETWEEN', $range, 'AND'));	
	}

	function whereNotBetween($field, $range = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'NOT BETWEEN', $range, 'AND'));	
	}

	function orWhereBetween($field, $range = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'BETWEEN', $range, 'OR'));	
	}

	function orWhereNotBetween($field, $range = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'NOT BETWEEN', $range, 'OR'));	
	}

	function whereIn($field, $inclusion = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'IN', $inclusion, 'AND'));	
	}

	function whereNotIn($field, $inclusion = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'NOT IN', $inclusion, 'AND'));	
	}

	function orWhereIn($field, $inclusion = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'IN', $inclusion, 'OR'));	
	}

	function orWhereNotIn($field, $inclusion = array())
	{
		return call_user_func_array(array($this,'where'), array($field, 'NOT IN', $inclusion, 'OR'));	
	}

	function resetWhere()
	{
		$this->where = array();
		return $this;
	}
}