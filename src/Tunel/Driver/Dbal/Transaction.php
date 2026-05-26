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
namespace Roulette\Tunel\Driver\Dbal;

use Doctrine\DBAL\Connection;
use Roulette\Tunel\Driver\Transaction as TransactionContract;

/**
 * Transaction control for Doctrine DBAL (Symfony 4–7, standalone Doctrine).
 *
 * @package \Roulette\Tunel\Driver\Dbal
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Transaction implements TransactionContract
{
    /** @param  Connection  $conn */
    public function __construct(private Connection $conn) {}

    /** @return bool */
    public function begin(): bool
    {
        $this->conn->beginTransaction();
        return true;
    }

    /** @return bool */
    public function commit(): bool
    {
        $this->conn->commit();
        return true;
    }

    /** @return bool */
    public function rollback(): bool
    {
        $this->conn->rollBack();
        return true;
    }
}
