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
namespace Roulette\Validator;

use Roulette\Validator\ValidatorAbstract;
use Roulette\Regexp;

/**
 * SubClass for Validator — validates URL format
 *
 * @package Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Url extends ValidatorAbstract
{
	protected mixed $rule = '/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';

	protected ?string $message = 'value doesnt appear as valid Url format';

	function __construct(mixed $rule = null, ?string $message = null)
	{
		parent::__construct($rule ?? $this->rule, $message);
		$this->rule = new Regexp(is_string($this->rule) ? $this->rule : '');
	}

	function test(mixed $value = null): bool
	{
		return (bool) $this->rule->test($value);
	}
}
