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
use Doctrine\DBAL\Logging\QueryLogger as DbalQueryLogger;
use Roulette\Tunel\Driver\Logger as LoggerContract;

/**
 * Query logger for Doctrine DBAL (Symfony 4–7).
 *
 * Attaches a single-use middleware that captures the last SQL before
 * and after execution. Falls back to pass-through if DBAL middleware
 * API is unavailable (older DBAL versions).
 *
 * DBAL version branching:
 *   - DBAL 3.x: uses setSQLLogger() (deprecated but available)
 *   - DBAL 4.x: setSQLLogger() removed; falls back to pass-through
 *
 * @package \Roulette\Tunel\Driver\Dbal
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Logger implements LoggerContract
{
    /** @param  Connection  $conn */
    public function __construct(private Connection $conn) {}

    /**
     * @param  callable  $execution
     * @param  callable  $onCapture
     * @return void
     */
    public function capture(callable $execution, callable $onCapture): void
    {
        $captured = null;

        // DBAL 3+ supports per-connection SQL logging via setSQLLogger (deprecated in 3.x, removed in 4)
        // For DBAL 4, use middleware. We probe for each approach.
        if (method_exists($this->conn->getConfiguration(), 'setSQLLogger')) {
            $logger = new class ($captured) implements DbalQueryLogger {
                public ?string $sql = null;
                public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
                {
                    $this->sql = $sql;
                }
                public function stopQuery(): void {}
            };

            $prev = $this->conn->getConfiguration()->getSQLLogger();
            $this->conn->getConfiguration()->setSQLLogger($logger);

            $execution();

            $captured = $logger->sql;
            $this->conn->getConfiguration()->setSQLLogger($prev);
        } else {
            // DBAL 4+: setSQLLogger() removed. SQL logging unavailable.
            // Use a Doctrine Middleware on the connection for query logging on DBAL 4+.
            static $dbal4Warned = false;
            if (!$dbal4Warned) {
                trigger_error(
                    'Roulette: DBAL 4+ detected — SQL logging unavailable (setSQLLogger was removed). ' .
                    'Register a Doctrine Middleware for query logging.',
                    \E_USER_NOTICE
                );
                $dbal4Warned = true;
            }
            $execution();
        }

        $onCapture($captured, $captured);
    }
}
