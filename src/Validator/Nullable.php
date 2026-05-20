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
 * SubClass for Validator, will be show message "value can not null"
 *
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Nullable extends ValidatorAbstract
{
    /**
     * Default Value for validator `true` (nullable by default)
     * @var mixed
     */
    protected mixed $rule = true;

    /**
     * Default validator message for Nullable
     * @var string|null
     */
	protected ?string $message = 'value can not null';

    /**
     * Execute the validation process
     *
     * @param  mixed $value variable to be validated
     * @return bool true if the variable is valid
     */
    function test(mixed $value = null): bool
    {
    	if (!$this->rule)
    	{
    		return !is_null($value);
    	}

    	return true;
    }
}
