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

use ReflectionClass;

/**
 * Provide a view standart function. Top level parent class for Roulette Classes.
 *
 * @package \Roulette
 * @author Eko Dedy Purnomo (eko.dedy.purnomo@gmail.com)
 * @since Version 2.0.0
 */
class Base
{
    static function create(mixed $config = null): static
    {
        if (static::is($config))
        {
            return $config;
        }

        $reflection = new ReflectionClass(static::class);
        return $reflection->newInstanceArgs(func_get_args());
    }

    static function is(mixed $object = null): bool
    {
        return $object instanceof static;
    }

    static function isNot(mixed $object = null): bool
    {
        return !static::is($object);
    }

    function __construct() {}
}
