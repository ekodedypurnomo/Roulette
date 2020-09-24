<?php
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
    /**
     * Function to send new data
     * 
     * @param [type] $config
     * @return [type]        
     */
    static function create($config = null)
    {
        if (static::is($config))
        {
            return $config;
        }

        $reflection = new ReflectionClass(static::class);
        return $reflection->newInstanceArgs(func_get_args());
    }

    /**
     * Determines information about the current platform the application is running on
     * 
     * @param  object  $object
     * @return object
     */
    static function is($object = null)
    {
        return $object instanceof static;
    }

    static function isNot($object = null)
    {
        return !static::is($object);
    }

    function __construct(){}
}
