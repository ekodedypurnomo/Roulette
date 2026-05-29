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
use Roulette\Tunel\Driver\CodeIgniter4\Executor;
use Roulette\Tunel\Driver\CodeIgniter4\Logger;
use Roulette\Tunel\Driver\CodeIgniter4\Transaction;

/**
 * Roulette tunel for CodeIgniter 4.
 *
 * Detects CI4 via the db_connect() helper, assembles the CI4 drivers,
 * and registers the tunel. Works with CI4's BaseConnection / BaseBuilder.
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class CodeIgniter4 extends Assembly
{
    /** @var mixed Framework info array keyed by 'framework', 'version', 'tunel' */
    static mixed $frameworkInfo = null;

    /** @return bool */
    static function check(): bool
    {
        if (!function_exists('db_connect')) return false;
        if (!defined('APPPATH') || !class_exists('\CodeIgniter\CodeIgniter')) return false;

        $db      = db_connect();
        $version = \CodeIgniter\CodeIgniter::CI_VERSION ?? 'unknown';
        $tunel   = new static(
            new Executor($db),
            new Logger($db),
            new Transaction($db),
        );

        static::$frameworkInfo = [
            'framework' => 'CodeIgniter',
            'version'   => $version,
            'tunel'     => $tunel,
        ];

        return true;
    }
}
