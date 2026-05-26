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
namespace Roulette\Tunel\Driver\CodeIgniter4;

use Roulette\Tunel\Driver\Logger as LoggerContract;

/**
 * Query logger for CodeIgniter 4.
 *
 * CI4 exposes the last query via $db->getLastQuery().
 *
 * @package \Roulette\Tunel\Driver\CodeIgniter4
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Logger implements LoggerContract
{
    /** @param  mixed  $db  CI4's BaseConnection instance. */
    public function __construct(private mixed $db) {}

    /**
     * @param  callable  $execution
     * @param  callable  $onCapture
     * @return void
     */
    public function capture(callable $execution, callable $onCapture): void
    {
        $execution();
        $query = $this->db->getLastQuery();
        $sql   = $query ? (string) $query : null;
        $onCapture($sql, $query);
    }
}
