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

use Doctrine\DBAL\Connection;
use Roulette\Tunel\Assembly;
use Roulette\Tunel\Driver\Dbal\Executor;
use Roulette\Tunel\Driver\Dbal\Logger;
use Roulette\Tunel\Driver\Dbal\Transaction;

/**
 * Roulette tunel for Symfony (4–7) via Doctrine DBAL.
 *
 * Auto-detection is not available for Symfony (no global app() function).
 * Use the static factory instead:
 *
 *   Symfony::fromConnection($doctrine->getConnection());
 *
 * Or register it as a Symfony service and inject the DBAL connection
 * before calling Operation::setOperationTunel().
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Symfony extends Assembly
{
    /** @var mixed Framework info array keyed by 'framework', 'version', 'tunel' */
    static mixed $frameworkInfo = null;

    /**
     * Manually wire a DBAL connection and activate this tunel.
     * Call this once during application bootstrap (e.g. in a Symfony service).
     *
     * @param  Connection  $conn
     * @return static
     */
    static function fromConnection(Connection $conn): static
    {
        $tunel = new static(
            new Executor($conn),
            new Logger($conn),
            new Transaction($conn),
        );

        static::$frameworkInfo = [
            'framework' => 'Symfony',
            'version'   => class_exists('\Symfony\Component\HttpKernel\Kernel')
                ? \Symfony\Component\HttpKernel\Kernel::VERSION
                : 'unknown',
            'tunel'     => $tunel,
        ];

        return $tunel;
    }

    /**
     * Always returns false — Symfony has no global helper for auto-detection.
     * Use fromConnection() to register this tunel manually.
     *
     * @return bool
     */
    static function check(): bool
    {
        return false;
    }
}
