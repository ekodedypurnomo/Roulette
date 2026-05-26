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
namespace Roulette\Tunel\Driver\Phalcon;

use Roulette\Tunel\Driver\Logger as LoggerContract;

/**
 * Query logger for Phalcon 3/4/5.
 *
 * Phalcon's DB adapter does not expose a built-in query log per-execution.
 * SQL capture is handled by the executor writing operation->query directly.
 * This logger is a transparent pass-through.
 *
 * @package \Roulette\Tunel\Driver\Phalcon
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Logger implements LoggerContract
{
    /** @param  mixed  $db  Phalcon\Db\Adapter instance. */
    public function __construct(private mixed $db) {}

    /**
     * @param  callable  $execution
     * @param  callable  $onCapture
     * @return void
     */
    public function capture(callable $execution, callable $onCapture): void
    {
        $execution();
        $onCapture(null, null);
    }
}
