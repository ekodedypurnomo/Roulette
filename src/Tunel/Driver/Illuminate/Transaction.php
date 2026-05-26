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
namespace Roulette\Tunel\Driver\Illuminate;

use Roulette\Tunel\Driver\Transaction as TransactionContract;

/**
 * Transaction control for Illuminate (Laravel 5–12, Lumen).
 *
 * @package \Roulette\Tunel\Driver\Illuminate
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Transaction implements TransactionContract
{
    /** @param  string  $dbClass  Fully-qualified Illuminate DB facade class name. */
    public function __construct(private string $dbClass) {}

    /** @return bool */
    public function begin(): bool
    {
        ($this->dbClass)::beginTransaction();
        return true;
    }

    /** @return bool */
    public function commit(): bool
    {
        ($this->dbClass)::commit();
        return true;
    }

    /** @return bool */
    public function rollback(): bool
    {
        ($this->dbClass)::rollBack();
        return true;
    }
}
