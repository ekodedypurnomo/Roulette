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

use Roulette\Model;

/**
 * @package \Roulette
 * @author Eko Dedy Purnomo (eko.dedy.purnomo@gmail.com)
 * @since Version 2.0.0
 */
class Actor extends Model
{
	function can(?string $policyName = null, mixed $recordOrClass = null): bool
	{
		$args = func_get_args();
		array_shift($args);
		array_unshift($args, $this);

		if ($recordOrClass !== null && method_exists($recordOrClass, 'getPolicy'))
		{
			$policy = $recordOrClass::getPolicy($policyName);

			if ($policy)
			{
				return (bool) call_user_func_array(array($policy, 'assert'), $args);
			}
		}

		return true;
	}

	function able(?string $policyName = null, mixed $recordOrClass = null): bool
	{
		return $this->can($policyName, $recordOrClass);
	}
}
