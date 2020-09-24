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
 * SubClass for Validator, will be show message "must be include in {rule}"
 * 
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Inclusion extends ValidatorAbstract
{
    /**
     * Default validator message for Inclusion
     * @var string
     */
	protected $message = 'must be include in: {rule}';

    /**
     * Default Config for Validator message Inclusion
     * 
     * @param Array  $validator
     * @param String $message
     */
	function __construct($validator = null, $message = null)
	{
		call_user_func_array(array(parent::class, '__construct'), func_get_args() );
        
		if (! is_array($this->rule) ) $this->rule = array();
	}

    /**
     * Execute the validation process
     * 
     * @param  String $value variable to be validated
     * @return boolean true if the variable is valid 
     */
    function test($value = null)
    {
        return in_array($value, $this->rule);
    }

    /**
     * Take Specified validator
     * @return String
     */
    function getRuleString()
    {
        if (!is_array($this->rule)) $this->rule = array();
        return implode(',', $this->rule);
    }
}