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
namespace Roulette\Tunel\Driver\Pdo;

use PDO;
use Roulette\Tunel\Driver\Logger as LoggerContract;

/**
 * Query logger for PDO-backed connections.
 *
 * PDO has no built-in query log. The executor writes the compiled SQL onto
 * the Operation directly (operation->query = $sql), so this logger is a
 * transparent pass-through — it simply invokes the execution and calls the
 * capture callback with null, letting the executor own SQL capture.
 *
 * @package \Roulette\Tunel\Driver\Pdo
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Logger implements LoggerContract
{
    /** @param  PDO  $pdo */
    public function __construct(private PDO $pdo) {}

    /**
     * onCapture is always called with (null, null); SQL capture is handled
     * by the PDO Executor writing operation->query directly.
     *
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
