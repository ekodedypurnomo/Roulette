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
use Roulette\Tunel\Driver\Transaction as TransactionContract;

/**
 * Transaction control for PDO connections.
 *
 * @package \Roulette\Tunel\Driver\Pdo
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Transaction implements TransactionContract
{
    /** @param  PDO  $pdo */
    public function __construct(private PDO $pdo) {}

    /** @return bool */
    public function begin(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /** @return bool */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /** @return bool */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
}
