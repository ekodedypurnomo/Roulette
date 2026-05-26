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

use Roulette\Tunel\Driver\Logger as LoggerContract;

/**
 * Query log capture for Illuminate (Laravel 5–12, Lumen).
 *
 * Uses DB::connection() to access the Connection object directly, which
 * exposes a stable logging/queryLog API across all supported versions.
 * No version-number checks needed — the Connection API has been stable
 * since Laravel 5.
 *
 * @package \Roulette\Tunel\Driver\Illuminate
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Logger implements LoggerContract
{
    /** @param  string  $dbClass  Fully-qualified Illuminate DB facade class name. */
    public function __construct(private string $dbClass) {}

    /**
     * @param  callable  $execution  The callable that runs the actual query.
     * @param  callable  $onCapture  Receives (string|null $sql, array|null $rawLog) after execution.
     * @return void
     */
    public function capture(callable $execution, callable $onCapture): void
    {
        $conn = ($this->dbClass)::connection();

        $wasLogging = $conn->logging();
        $conn->enableQueryLog();
        $before = $conn->getQueryLog();

        $execution();

        $after = $conn->getQueryLog();

        if ($wasLogging) {
            $conn->enableQueryLog();
        } else {
            $conn->disableQueryLog();
        }

        $newLog = count($after) > count($before) ? end($after) : null;
        $sql    = $newLog ? $this->interpolate($newLog) : null;

        $onCapture($sql, $newLog);
    }

    /**
     * Replaces each `?` placeholder with its bound value from the query log entry.
     *
     * @param  array   $log  Single entry from Illuminate's getQueryLog().
     * @return string
     */
    private function interpolate(array $log): string
    {
        $sql = $log['query'];
        foreach ($log['bindings'] as $binding) {
            if (is_string($binding)) $binding = "'" . $binding . "'";
            if (is_null($binding))   $binding = 'NULL';
            $sql = preg_replace('#\?#', (string) $binding, $sql, 1);
        }
        return $sql;
    }
}
