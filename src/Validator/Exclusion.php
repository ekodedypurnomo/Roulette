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
 * SubClass for validator, will be show message "must be exlude from {rule}"
 *
 * @package \Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Exclusion extends ValidatorAbstract
{
    /**
     * Default validator message for Exclusion
     * @var string|null
     */
	protected ?string $message = 'must be exclude from: {rule}';

    /**
     * Default config for Exclusion Validator
     * @param mixed $rule
     * @param string|null $message
     */
	function __construct(mixed $rule = null, ?string $message = null)
	{
		parent::__construct($rule, $message);

		if (!is_array($this->rule)) $this->rule = [];
	}

    /**
     * Execute the validation process
     * @param  mixed $value variable to be validated
     * @return bool true if the variable is valid
     */
    function test(mixed $value = null): bool
    {
        return !in_array($value, (array) $this->rule);
    }

    /**
     * Take Specified validator
     * @return string
     */
    protected function getRuleString(): string
    {
        if (!is_array($this->rule)) $this->rule = [];
        return implode(',', $this->rule);
    }
}
