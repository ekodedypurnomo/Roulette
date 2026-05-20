<?php

declare(strict_types=1);

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
	protected mixed $acceptableKeys = null;

	protected mixed $acceptableValues = null;

	protected mixed $beforeSet = null;

	protected mixed $beforeAdd = null;

	function acceptableKey(mixed $key = null): bool
	{
		if (is_array($this->acceptableKeys) && !empty($this->acceptableKeys))
		{
			return in_array($key, $this->acceptableKeys);
		}
		elseif (is_callable($this->acceptableKeys))
		{
			return (bool) call_user_func($this->acceptableKeys, $key);
		}
		elseif ($this->acceptableKeys instanceof Regexp)
		{
			return (bool) $this->acceptableKeys->test($key);
		}

		return true;
	}

	function acceptableValue(mixed $value = null): bool
	{
		if (is_array($this->acceptableValues) && !empty($this->acceptableValues))
		{
			return in_array($value, $this->acceptableValues);
		}
		elseif (is_callable($this->acceptableValues))
		{
			return (bool) call_user_func($this->acceptableValues, $value);
		}
		elseif ($this->acceptableValues instanceof Regexp)
		{
			return (bool) $this->acceptableValues->test($value);
		}

		return true;
	}

	function acceptable(mixed $key = null, mixed $value = null): bool
	{
		return $this->acceptableKey($key) && $this->acceptableValue($value);
	}

	function setAcceptableKeys(mixed $keys): void
	{
		$this->acceptableKeys = $keys;
	}

	function setAcceptableValues(mixed $values): void
	{
		$this->acceptableValues = $values;
	}

	protected function _set(mixed $key = null, mixed $value = null): static
	{
		if ($this->acceptable($key, $value) === false) return $this;

		$items =& $this->items();

		if (is_callable($this->beforeSet))
		{
			if (call_user_func_array($this->beforeSet, [&$value, &$items]) === false)
			{
				return $this;
			}
		}
		$items[$key] = $value;

		return $this;
	}

	protected function _add(mixed $value = null): static
	{
		if ($this->acceptable(null, $value) === false) return $this;

		$items =& $this->items();

		if (is_callable($this->beforeAdd))
		{
			if (call_user_func_array($this->beforeAdd, [&$value, &$items]) === false)
			{
				return $this;
			}
		}

		$items[] = $value;

		return $this;
	}
}
