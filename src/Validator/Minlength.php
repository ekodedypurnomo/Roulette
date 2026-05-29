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
 * SubClass for Validator, will be show message "minimum characters length is {rule}"
 *
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Minlength extends ValidatorAbstract
{
	/**
	 * Default validator message for Minlength
	 * @var string|null
	 */
	protected ?string $message = 'minimum characters length is {rule}';

	/**
	 * Execute the prosess validation
	 *
	 * @param  mixed $value variable to be validated
	 * @return bool true if the variable is valid
	 */
    function test(mixed $value = null): bool
    {
    	if (is_numeric($value)) $value = (string) $value;
        return is_string($value) && is_integer($this->rule) && ($this->rule <= strlen($value));
    }
}
