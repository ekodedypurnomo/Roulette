<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Validator;

use Roulette\Base;
use Roulette\Template;

/**
 * Abstract class for Validator.
 * Any validator classes should extend it for standart Roulette Validator.
 *
 * @package Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
abstract class ValidatorAbstract extends Base
{
	/**
	 * Validation rule.
	 * 
	 * @var mixed
	 */
	protected $rule = null;

	/**
	 * Validator message.
	 * 
	 * @var string
	 */
	protected $message = null;

	/**
	 * Create new Validator.
	 * 
	 * @param boolean $rule Validator rule
	 * @param string  $message Validator message
	 */
	function __construct($rule, $message = null)
	{
		$this->rule = $rule;
		
		if ($message)
		{
			$this->message = $message;
		}
	}

	/**
	 * Get message with custom applied data.
	 * Could be used after value fail the `test()`
	 * 
	 * @param  array $data
	 * @return array
	 */
	function getMessage($data = null)
	{
		if (empty($data)) $data = array('value'=>null);

		if (!is_array($data)) $data = array('value'=>$data);

		return Template::compile($this->message)->apply(array_merge(
			$data,
			array( 
				'rule'=> $this->getRuleString() 
			) 
		));
	}

	protected function getRule()
	{
		return $this->rule;
	}

	/**
	 * Get stringify of rule.
	 * 
	 * @return string
	 */
	protected function getRuleString()
	{
		$rule = "";

		if (is_string($this->rule))
		{
			$rule = $this->rule;
		}
		elseif (is_bool($this->rule))
		{
			$rule = $this->rule ? "true" : "false";
		}
		elseif (is_numeric($this->rule))
		{
			$rule = (string) $this->rule;
		}
		elseif (is_array($this->rule))
		{
			$rule = "[".implode(',', $this->rule)."]";
		}
		elseif (is_a($this->rule, 'Closure'))
		{
			$rule = "[validation formula]";
		}

		return $rule;
	}

	/**
	 * Validate $value.
	 * 
	 * @param  mixed $value Value to validate 
	 * @return boolean       
	 */
	abstract function test($value = null);

}