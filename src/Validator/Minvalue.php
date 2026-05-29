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

/**
 * SubClass for Validator, will be show message "minimum value is {rule}"
 *
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Minvalue extends ValidatorAbstract
{
	/**
	 * Default validator message for Minvalue
	 * @var string|null
	 */
	protected ?string $message = 'minimum value is {rule}';

	/**
	 * Execute the prosess validation
	 *
	 * @param  mixed $value variable to be validated
	 * @return bool true if the variable is valid
	 */
    function test(mixed $value = null): bool
    {
    	return is_numeric($value) && is_numeric($this->rule) && ($value >= $this->rule);
    }
}
