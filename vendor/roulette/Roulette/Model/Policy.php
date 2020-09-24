<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please source the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Model;

use Roulette\Base;
use Roulette\Callback;

/**
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Policy extends Base
{
	protected $name = null;

	protected $assertions = array();

	static function create($config = null)
	{
		return new static($config);
	}

	function __construct($name = null)
	{
		$functions = func_get_args(); array_shift($functions);
		$this->name = $name;

		if (!empty($functions))
		{
			foreach ($functions as $i => $func)
			{
				if (is_callable($func))
				{
					$this->addAssertion($func);
				}	
			}
		}
	}

	function addAssertion(callable $assertion)
	{
		if (!is_array($this->assertions)) $this->assertions = array();

		$this->assertions[] = $assertion;
	}

	function reset()
	{
		$this->assertions = array();
	}

	function getAssetions()
	{
		if (!is_array($this->assertions))
		{
			$this->assertions = array();
		}
		return $this->assertions;
	}

	function assert()
	{
		$args = func_get_args();

		foreach ($this->assertions as $i => $assertion)
		{
			if(is_callable($assertion))
	        {
	            if(call_user_func_array($assertion, $args))
	            {
	            	return false;
	            }
	        }
		}
		return true;
	}
}