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
 * SubClass for Validator, will be show  message "maximum characters length is {rule}"
 * 
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Maxlength extends ValidatorAbstract
{
    /**
     * Default validator message for Maxlength
     * @var string
     */
	protected $message = 'maximum characters length is {rule}';
    
    /**
     * Execute the validation process
     * 
     * @param  numeric|boolean $value variable to be validated
     * @return boolean true if the variable is valid
     */
    function test($value = null)
    {
    	if (is_numeric($value) || is_bool($value)) $value = (string) $value;
        
        // only affect on string value, exlude null type, object type
        if (is_string($value))
        {
        	return ($this->rule >= strlen($value));
        }
        return true;
    }
}