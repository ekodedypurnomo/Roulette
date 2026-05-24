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
 * Authorization agent. Extend Model to represent the entity performing actions (e.g. a User).
 *
 * Use `can()` / `able()` to evaluate a named policy on a record or model class.
 * The policy callable is looked up from the target model's prototype and called with
 * the actor as the first argument, followed by any extra arguments passed to `can()`.
 *
 * Usage:
 *   $actor = Actor::load($userId);
 *   if ($actor->can('edit', $post)) { ... }
 *
 * Both `can()` and `able()` are identical — `able()` exists as a semantic alias
 * for contexts where "is actor able to..." reads more naturally than "can actor...".
 * When no policy is registered, both return true (open by default).
 *
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
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
