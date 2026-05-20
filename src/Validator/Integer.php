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
 * SubClass for Validator, will be show message "value should be an integer numeric"
 * 
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Integer extends ValidatorAbstract
{	
	/**
	 * Default validator message for Integer
	 * @var string
	 */
	protected $message = 'value should be an integer numeric';

	/**
	 * Execute the validation process
	 * 
	 * @param  string $value variable to be validated 
	 * @return boolean true if the variable is valid 
	 */
    function test($value = null)
    {
    	return is_integer($value);
    }
}