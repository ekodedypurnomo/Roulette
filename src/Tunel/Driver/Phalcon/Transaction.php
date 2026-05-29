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

use Roulette\Tunel\Driver\Transaction as TransactionContract;

/**
 * Transaction control for Phalcon 3/4/5.
 *
 * @package \Roulette\Tunel\Driver\Phalcon
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Transaction implements TransactionContract
{
    /** @param  mixed  $db  Phalcon\Db\Adapter instance. */
    public function __construct(private mixed $db) {}

    /** @return bool */
    public function begin(): bool
    {
        return $this->db->begin();
    }

    /** @return bool */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /** @return bool */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
