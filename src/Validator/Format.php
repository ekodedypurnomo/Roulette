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
 * Built-in validators.
 * Validate value with regex as a tester.
 *
 * Example:
 * ```php
 * 	$validator = new Roulette\Validator\validator\Format(
 * 		'/foo/', '"{value}" does not pass the test.'
 * 	);
 * 	$validator->test('bar'); // return `false`
 * 	$validator->getMessage(); // return `"bar" does not pass the test`
 * ```
 *
 * @package Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 * @see  Validator
 */
class Format extends ValidatorAbstract
{
	/**
	 * Default value for format message
	 * @var string|null
	 */
	protected ?string $message = 'invalid format';

	/**
	 * Execute the proses validation
	 *
	 * @param  mixed $value variable to be validated
	 * @return bool true if the variable is valid
	 */
    function test(mixed $value = null): bool
    {
    	return is_string($value) && (bool) preg_match((string) $this->rule, $value);
    }
}
