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
namespace Roulette\Contract;

/**
 * Contract for validator classes.
 * Any class that validates a value must implement this interface.
 *
 * @package Roulette\Contract
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
interface Validatable
{
    /**
     * Validate $value against this validator's rule.
     *
     * @param  mixed $value Value to validate
     * @return bool         True if valid, false otherwise
     */
    public function test(mixed $value = null): bool;

    /**
     * Get the validation error message, optionally interpolated with $data.
     *
     * @param  mixed $data Context data for message template placeholders
     * @return string
     */
    public function getMessage(mixed $data = null): string;
}
