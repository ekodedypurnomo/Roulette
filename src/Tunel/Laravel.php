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
use Roulette\Tunel\Driver\Illuminate\Executor;
use Roulette\Tunel\Driver\Illuminate\Logger;
use Roulette\Tunel\Driver\Illuminate\Transaction;

/**
 * Roulette tunel for Laravel (all major versions: 5–12) and Lumen.
 *
 * Detects any running Laravel/Lumen application, assembles the three
 * Illuminate drivers, and registers itself as the active tunel.
 *
 * No version-number checks are performed. The Illuminate drivers use
 * capability-based probing internally to stay compatible across versions.
 *
 * @package \Roulette\Tunel
 * @since   Version 2.0.0
 * @author  Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Laravel extends Assembly
{
    /** @var mixed Framework info array keyed by 'framework', 'version', 'versionInfo', 'connection', 'tunel' */
    static mixed $frameworkInfo = null;

    /** @return bool */
    static function check(): bool
    {
        if (!function_exists('app')) return false;

        $app = app();
        if (!$app || !defined('LARAVEL_START') && !($app instanceof \Illuminate\Foundation\Application)
            && !($app instanceof \Laravel\Lumen\Application)) {
            return false;
        }

        $dbClass = \Illuminate\Support\Facades\DB::class;
        $version = explode('.', $app::VERSION);

        $tunel = new static(
            new Executor($dbClass),
            new Logger($dbClass),
            new Transaction($dbClass),
        );

        static::$frameworkInfo = [
            'framework'   => $app instanceof \Laravel\Lumen\Application ? 'Lumen' : 'Laravel',
            'version'     => $app::VERSION,
            'versionInfo' => [
                'string' => $app::VERSION,
                'major'  => (int) ($version[0] ?? 0),
                'minor'  => (int) ($version[1] ?? 0),
                'patch'  => (int) ($version[2] ?? 0),
            ],
            'connection'  => $dbClass,
            'tunel'       => $tunel,
        ];

        return true;
    }

    /**
     * @param  mixed  $model  Class name or file path to load.
     * @return mixed          The class name, or null if $model is empty.
     * @throws \RuntimeException  If the file cannot be required.
     */
    static function model(mixed $model = null): mixed
    {
        if (empty($model)) return null;
        if (class_exists($model)) return $model;

        try {
            require_once $model;
            return $model;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Cannot load model '$model'", 0, $e);
        }
    }
}
