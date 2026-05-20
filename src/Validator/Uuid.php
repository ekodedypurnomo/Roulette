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

use Roulette\Validator\ValidatorAbstract;

/**
 * SubClass for Validator, will be show message "value should be greater than"
 * 
 * @package Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Uuid extends ValidatorAbstract
{
	protected $rule = '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/';

	protected $message = 'value doesnt appear as valid UUID format';

	function __construct($rule = null, $message = null)
	{
		call_user_func_array(array(parent::class, '__construct'), function_get_args());

		$this->rule = new Regexp($this->rule);

		return $this;
	}

	function test($value = null)
	{
		return $this->rule->test($value);
	}
}