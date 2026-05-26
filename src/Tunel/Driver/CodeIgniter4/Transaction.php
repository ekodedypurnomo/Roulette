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

use Roulette\Tunel\Driver\Transaction as TransactionContract;

/**
 * Transaction control for CodeIgniter 4.
 *
 * @package \Roulette\Tunel\Driver\CodeIgniter4
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Transaction implements TransactionContract
{
    /** @param  mixed  $db  CI4's BaseConnection instance. */
    public function __construct(private mixed $db) {}

    /** @return bool */
    public function begin(): bool
    {
        return $this->db->transBegin();
    }

    /** @return bool */
    public function commit(): bool
    {
        return $this->db->transCommit();
    }

    /** @return bool */
    public function rollback(): bool
    {
        return $this->db->transRollback();
    }
}
