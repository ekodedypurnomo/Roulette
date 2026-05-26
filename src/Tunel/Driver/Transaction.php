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
 * Contract for database transaction control in a framework driver.
 *
 * @package \Roulette\Tunel\Driver
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
interface Transaction
{
    /** @return bool */
    public function begin(): bool;

    /** @return bool */
    public function commit(): bool;

    /** @return bool */
    public function rollback(): bool;
}
