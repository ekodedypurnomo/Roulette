<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

use Roulette\Base;
use Roulette\Regexp;

/**
 * Is a class for helps in manipulating array in a single object.
 * 
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class ManagedCollection extends Collection
{
	protected $acceptableKeys = null;
	
	protected $acceptableValues = null;

	protected $beforeSet = null;

	protected $beforeAdd = null;

	function acceptableKey($key = null)
	{
		if (is_array($this->acceptableKeys) and !empty($this->acceptableKeys))
		{
			return in_array($key, $this->acceptableKeys);
		}
		elseif (is_callable($this->acceptableKeys))
		{
			return call_user_func($this->acceptableKeys, $key);
		}
		elseif ($this->acceptableKeys instanceof Regexp)
		{
			return Regexp::test($key);
		}
		
		return true;
	}

	function acceptableValue($value = null)
	{
		if (is_array($this->acceptableValues) and !empty($this->acceptableValues))
		{
			return in_array($value, $this->acceptableValues);
		}
		elseif (is_callable($this->acceptableValues))
		{
			return call_user_func($this->acceptableValues, $value);
		}
		elseif ($this->acceptableValues instanceof Regexp)
		{
			return Regexp::test($value);
		}
		
		return true;
	}

	function acceptable($key = null, $value = null)
	{
		return ($this->acceptableKey($key) and $this->acceptableValue($value));
	}

	function setAcceptableKeys($keys)
	{
		$this->acceptableKeys = $keys;
	}

	function setAcceptableValues($values)
	{
		$this->acceptableValues = $values;
	}

	protected function _set($key = null, $value = null)
	{	
		if ($this->acceptable($key, $value) === false) return $this;

		$items =& $this->items();

		if(is_callable($this->beforeSet))
		{
			if(call_user_func_array($this->beforeSet, array(&$value, &$items)) === false)
			{
				return $this;
			} 
		}
		$items[$key] = $value;
		
		return $this;
	}

	protected function _add($value = null)
	{
		if ($this->acceptable($key, $value) === false) return $this;

		$items =& $this->items();
		
		if(is_callable($this->beforeAdd))
		{
			if(call_user_func_array($this->beforeAdd, array(&$value, &$items)) === false)
			{
				return $this;
			}
		}

		$items[] = $value;

		return $this;
	}
}