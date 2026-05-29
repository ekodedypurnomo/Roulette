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

use PDO;
use Roulette\Tunel\Assembly;
use Roulette\Tunel\Driver\Pdo\Executor;
use Roulette\Tunel\Driver\Pdo\Logger;
use Roulette\Tunel\Driver\Pdo\Transaction;

/**
 * Roulette tunel for framework-free PHP via PDO.
 *
 * Works with any PDO-supported database (MySQL, PostgreSQL, SQLite, etc.)
 * without any framework dependency. Suitable for Slim, Mezzio, or plain PHP.
 *
 * Usage:
 *   $pdo   = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
 *   $tunel = Standalone::fromPdo($pdo);
 *   Operation::setOperationTunel($tunel);
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Standalone extends Assembly
{
    /** @var mixed Framework info array keyed by 'framework', 'version', 'tunel' */
    static mixed $frameworkInfo = null;

    /**
     * @param  PDO  $pdo
     * @return static
     */
    static function fromPdo(PDO $pdo): static
    {
        $tunel = new static(
            new Executor($pdo),
            new Logger($pdo),
            new Transaction($pdo),
        );

        static::$frameworkInfo = [
            'framework' => 'Standalone',
            'version'   => 'PDO/' . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'tunel'     => $tunel,
        ];

        return $tunel;
    }

    /**
     * Always returns false — Standalone is opt-in via fromPdo(), not auto-detected.
     *
     * @return bool
     */
    static function check(): bool
    {
        return false;
    }
}
