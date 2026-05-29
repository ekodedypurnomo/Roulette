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
     * Get the information about the framework being used.
     * @return mixed
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
     * Detect whether this tunel's framework is currently running.
     * @return bool
     */
    static function check(): bool
    {
        return false;
    }

    /**
     * @param  mixed  $model
     * @return mixed
     */
    static function model(mixed $model): mixed
    {
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

    function beginTransaction(): bool
    {
        return false;
    }

    function commit(): bool
    {
        return false;
    }

    function rollback(): bool
    {
        return false;
    }
}
