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

use Roulette\Tunel\Assembly;
use Roulette\Tunel\Driver\Phalcon\Executor;
use Roulette\Tunel\Driver\Phalcon\Logger;
use Roulette\Tunel\Driver\Phalcon\Transaction;

/**
 * Roulette tunel for Phalcon 3/4/5.
 *
 * Detects a running Phalcon DI container and retrieves the 'db' service.
 * The Phalcon drivers build raw SQL internally and execute via the adapter,
 * ensuring compatibility across major Phalcon versions (3, 4, 5).
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Phalcon extends Assembly
{
    /** @var mixed Framework info array keyed by 'framework', 'version', 'tunel' */
    static mixed $frameworkInfo = null;

    /** @return bool */
    static function check(): bool
    {
        if (!class_exists('\Phalcon\Di\Di') && !class_exists('\Phalcon\Di')) return false;

        $diClass = class_exists('\Phalcon\Di\Di') ? '\Phalcon\Di\Di' : '\Phalcon\Di';

        try {
            $di = $diClass::getDefault();
            if (!$di || !$di->has('db')) return false;
            $db = $di->get('db');
        } catch (\Throwable) {
            return false;
        }

        $version = defined('\Phalcon\Version::VERSION') ? \Phalcon\Version::VERSION : 'unknown';
        $tunel   = new static(
            new Executor($db),
            new Logger($db),
            new Transaction($db),
        );

        static::$frameworkInfo = [
            'framework' => 'Phalcon',
            'version'   => $version,
            'tunel'     => $tunel,
        ];

        return true;
    }
}
