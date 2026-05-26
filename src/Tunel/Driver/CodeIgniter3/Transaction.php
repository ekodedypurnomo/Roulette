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

use Roulette\Tunel\Driver\Transaction as TransactionContract;

/**
 * Transaction control for CodeIgniter 3.
 *
 * @package \Roulette\Tunel\Driver\CodeIgniter3
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Transaction implements TransactionContract
{
    /** @param  mixed  $db  CI3's CI_DB_query_builder instance. */
    public function __construct(private mixed $db) {}

    /** @return bool */
    public function begin(): bool
    {
        $this->db->trans_begin();
        return true;
    }

    /** @return bool */
    public function commit(): bool
    {
        $this->db->trans_commit();
        return true;
    }

    /** @return bool */
    public function rollback(): bool
    {
        $this->db->trans_rollback();
        return true;
    }
}
