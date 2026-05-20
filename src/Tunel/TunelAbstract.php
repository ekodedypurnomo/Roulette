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
namespace Roulette\Tunel;

use Roulette\Base;
use Roulette\Contract\Tunel;
use Roulette\Query\Operation;

/**
 * Abstract class for Tunel.
 *
 * @package Roulette\Tunel
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
abstract class TunelAbstract extends Base implements Tunel
{
    /**
     * Information of the used framework
     * @var null
     */
    static mixed $frameworkInfo = null;

    /**
     * Get the information about the framework being used
     * @return boolean
     */
    static function info(): mixed
    {
        if (!static::$frameworkInfo)
        {
            static::check();
        }
        return static::$frameworkInfo;
    }

    /**
     * Default value if framework not identified
     * @return boolean
     */
    static function check(): bool
    {
        # static couldn't be an abstract, so we create default return here
        return false;
    }

    static function model(mixed $model): mixed
    {
        # static couldn't be an abstract, so we create default return here
        return $model;
    }



    ///////////////////////
    // begin for object  //
    ///////////////////////

    /**
     * Config connection to the framework
     * @var null
     */
    protected mixed $connection = null;

    /**
     * @ignore
     */
    function __construct(mixed $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Get the connection configuration
     * @return array
     */
    function getConnection(): mixed
    {
        return $this->connection;
    }

    /**
     * Abstract function of operate
     * @param  Operation $operation
     * @param  callable  $callback
     */
    abstract function operate(Operation $operation, ?callable $callback = null): mixed;
}
