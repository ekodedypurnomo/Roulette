<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Query;

use Roulette\Base;
use Roulette\Mixin\Configurable;
use Roulette\Collection;

/**
 * A Model represents a record from database as an object, that have many crud
 * operation function, including association.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Condition extends Base
{
	static protected $definedOperator = array(
		'=', '<','<=','>','>=', '<>',
		'IS', 'IS NOT',
		'BETWEEN', 'NOT BETWEEN',
		'LIKE', 'NOT LIKE',
		'IN', 'NOT IN'
		);

	public $hook = 'AND';
	public $field = null;
	public $operator = null;
	public $value = null;

	function __construct($hook = null, $field = null, $operator = null, $value = null)
	{
		if(!is_string($hook))
		{
			$config = Collection::create($hook);
			$hook = $config->get('hook'); 
			$field = $config->get('field');
			$operator = $config->get('operator');
			$value = $config->get('value');
		}

		# init hook
		$this->hook = $hook;
		$hook = strtoupper($hook);
		if (!in_array($hook, array('AND','OR')) )
		{
			$hook = 'AND';
		}

		$this->field = $field;

		# init operator
		$this->operator = $operator;
		$_operator = strtoupper($operator);
		if (in_array($_operator, static::$definedOperator))
		{
			$this->operator = $_operator;
		}

		# init value
		$this->value = $value;
		$value = $config->get('value');
		if (is_string($value))
		{
			$value = strtoupper($value);
			if ($value == 'NULL')
			{
				$this->value = $value;
			}
		}
	}
}