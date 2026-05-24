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
 * SubClass for Validator — datetime format validation. Not yet implemented.
 *
 * @package Roulette\Validator
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class DateTime extends ValidatorAbstract
{
    protected mixed $rule = 'Y-m-d H:i:s';
    protected ?string $message = 'value is not a valid datetime, expected format: {rule}';

    function __construct(mixed $rule = null, ?string $message = null)
    {
        parent::__construct($rule ?? $this->rule, $message);
    }

    function test(mixed $value = null): bool
    {
        if (!is_string($value) && !is_numeric($value)) return false;
        $d = \DateTime::createFromFormat((string) $this->rule, (string) $value);
        return $d !== false && $d->format((string) $this->rule) === (string) $value;
    }
}
