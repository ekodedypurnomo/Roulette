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
namespace Roulette\Tunel\Driver\CodeIgniter3;

use Roulette\Tunel\Driver\Logger as LoggerContract;

/**
 * Query logger for CodeIgniter 3.
 *
 * CI3 exposes the last executed query via $db->last_query() after execution.
 *
 * @package \Roulette\Tunel\Driver\CodeIgniter3
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Logger implements LoggerContract
{
    /** @param  mixed  $db  CI3's CI_DB_query_builder instance. */
    public function __construct(private mixed $db) {}

    /**
     * @param  callable  $execution
     * @param  callable  $onCapture
     * @return void
     */
    public function capture(callable $execution, callable $onCapture): void
    {
        $execution();
        $sql = $this->db->last_query();
        $onCapture($sql ?: null, $sql ?: null);
    }
}
