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

use Roulette\Query\Operation;

/**
 * Contract for framework adapter (tunel) classes.
 * Any class that bridges Roulette to a specific PHP framework must implement this interface.
 *
 * @package Roulette\Contract
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
interface Tunel
{
    /**
     * Execute a database operation through the framework's query layer.
     *
     * @param  Operation       $operation The query operation to execute
     * @param  callable|null   $callback  Optional callback invoked after execution
     * @return mixed
     */
    public function operate(Operation $operation, ?callable $callback = null): mixed;

    /**
     * Get the underlying framework database connection.
     *
     * @return mixed
     */
    public function getConnection(): mixed;
}
