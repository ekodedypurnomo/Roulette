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
namespace Roulette\Tunel\Driver;

use Roulette\Query\Operation;

/**
 * Contract for the query execution layer of a framework driver.
 *
 * Each method receives the Operation object and is responsible for
 * running the query then writing result, success, affectedRows, and
 * error back onto that object.
 *
 * @package \Roulette\Tunel\Driver
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
interface Executor
{
    /**
     * @param  Operation  $operation
     * @return void
     */
    public function select(Operation $operation): void;

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function insert(Operation $operation): void;

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function update(Operation $operation): void;

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function delete(Operation $operation): void;

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function query(Operation $operation): void;

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function exists(Operation $operation): void;

    /**
     * @param  Operation  $operation
     * @return void
     */
    public function truncate(Operation $operation): void;
}
