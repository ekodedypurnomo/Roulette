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

/**
 * Contract for capturing the SQL string produced by a query execution.
 *
 * Wraps the actual execution callable so the driver can intercept the
 * query log before and after and surface a human-readable SQL string.
 *
 * Usage:
 *   $logger->capture(
 *       fn() => $builder->get(),
 *       fn(?string $sql, mixed $raw) => $operation->query = $sql
 *   );
 *
 * @package \Roulette\Tunel\Driver
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
interface Logger
{
    /**
     * @param  callable  $execution  The callable that runs the actual query.
     * @param  callable  $onCapture  Receives (string|null $sql, mixed $rawLog) after execution.
     * @return void
     */
    public function capture(callable $execution, callable $onCapture): void;
}
