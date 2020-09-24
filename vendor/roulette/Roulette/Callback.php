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

/**
 * @package \Roulette
 * @author Eko Dedy Purnomo (eko.dedy.purnomo@gmail.com)
 * @since Version 2.0.0
 */
class Callback
{
	static function call($func = null, $arguments = null, $context = null)
	{
		if (is_callable($func))
		{
			if (is_null($arguments)) $arguments = array();
			if (!is_array($arguments)) $arguments = array($arguments);

			if ($context) $func->bindTo($context);
			return call_user_func_array($func, $arguments);
		}
	}

	static function able($callback = null)
	{
		return is_callable($callback);
	}
}