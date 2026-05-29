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
use Roulette\Tunel\Driver\CodeIgniter3\Executor;
use Roulette\Tunel\Driver\CodeIgniter3\Logger;
use Roulette\Tunel\Driver\CodeIgniter3\Transaction;

/**
 * Legacy tunel for CodeIgniter 3.
 *
 * @deprecated Use CodeIgniter4.php instead — it supports CodeIgniter 4.
 *             If still on CI3, this adapter is kept for backwards compatibility.
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Codeigniter3 extends Assembly
{
    /** @var mixed Framework info array keyed by 'framework', 'version', 'tunel' */
    static mixed $frameworkInfo = null;

    /** @return bool */
    static function check(): bool
    {
        if (!function_exists('get_instance')) return false;

        $ci = get_instance();
        if (!$ci || !isset($ci->db)) return false;

        $version = defined('CI_VERSION') ? CI_VERSION : 'unknown';
        $db      = $ci->db;
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
