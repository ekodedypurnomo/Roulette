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

use Roulette\Query\Operation;
use Roulette\Tunel\TunelAbstract;
use Roulette\Tunel\Driver\Executor;
use Roulette\Tunel\Driver\Logger;
use Roulette\Tunel\Driver\Transaction;

/**
 * Base tunel assembled from three independently-swappable driver components.
 *
 * Concrete entry points (Laravel, CodeIgniter4, Standalone, …) extend this
 * class, inject the appropriate drivers in their check() factory, and gain
 * a fully-wired tunel with zero duplicate logic.
 *
 * Adding support for a new framework means:
 *   1. Create Driver/{Framework}/Executor.php
 *   2. Create Driver/{Framework}/Logger.php
 *   3. Create Driver/{Framework}/Transaction.php
 *   4. Create {Framework}.php entry point that extends Assembly
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
abstract class Assembly extends TunelAbstract
{
    /**
     * @param  Executor|null    $executor     Null produces a no-op on all query calls.
     * @param  Logger|null      $logger       Null skips SQL capture; execution still runs.
     * @param  Transaction|null $transaction  Null makes all transaction methods return false.
     */
    public function __construct(
        private ?Executor    $executor    = null,
        private ?Logger      $logger      = null,
        private ?Transaction $transaction = null,
    ) {
        parent::__construct(null);
    }

    /**
     * Dispatch an Operation through the executor, optionally capturing SQL via the logger.
     *
     * After execution the callback (if given) is invoked as: $callback($tunel, $operation).
     *
     * @param  Operation       $operation
     * @param  callable|null   $callback   Receives ($this, $operation) after execution.
     * @return mixed
     */
    final public function operate(Operation $operation, ?callable $callback = null): mixed
    {
        $execute = function () use ($operation): void {
            if ($this->executor === null) return;
            match (strtolower($operation->getMode() ?? '')) {
                'select'   => $this->executor->select($operation),
                'insert'   => $this->executor->insert($operation),
                'update'   => $this->executor->update($operation),
                'delete'   => $this->executor->delete($operation),
                'query'    => $this->executor->query($operation),
                'exist'    => $this->executor->exists($operation),
                'truncate' => $this->executor->truncate($operation),
                default    => null,
            };
        };

        $capture = function (?string $sql, mixed $rawLog) use ($operation): void {
            $operation->query    = $sql;
            $operation->queryRaw = $rawLog;
        };

        if ($this->logger !== null) {
            $this->logger->capture($execute, $capture);
        } else {
            $execute();
        }

        if (is_callable($callback)) {
            $callback($this, $operation);
        }

        return $this;
    }

    /** @return bool */
    final public function beginTransaction(): bool
    {
        return $this->transaction?->begin() ?? false;
    }

    /** @return bool */
    final public function commit(): bool
    {
        return $this->transaction?->commit() ?? false;
    }

    /** @return bool */
    final public function rollback(): bool
    {
        return $this->transaction?->rollback() ?? false;
    }
}
