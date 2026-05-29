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

use Roulette\Base;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette\Tunel
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class FrameworkCheckers extends Base
{
    /**
     * [$frameworks description]
     * @var null
     */
    protected static mixed $frameworks = null;

    /**
     * [$configLoaded description]
     * @var boolean
     */
    protected static bool $configLoaded = false;

    /**
     * [getAll description]
     * @return [type] [description]
     */
    static function getAll(): array
    {
        if (!static::$configLoaded)
        {
            $tunnelDir = __DIR__ . '/Tunels.php';

            static::$frameworks = require_once(str_replace('/', DIRECTORY_SEPARATOR, $tunnelDir));
            static::$configLoaded = true;

            if (!is_array(static::$frameworks)) static::$frameworks = [];
        }
        return static::$frameworks;
    }

    /**
     * [has description]
     * @param  [type]  $frameworkName [description]
     * @return boolean                [description]
     */
    static function has(mixed $frameworkName = null): bool
    {
        $has = false;
        if (empty($frameworkName)) return false;

        foreach (static::getAll() as $key => $value)
        {
            $has = preg_match('/' . strtolower($frameworkName) . '/', strtolower($key));
            if ($has) break;
        }
        return (bool) $has;
    }

    /**
     * [getInfo description]
     * @return [type] [description]
     */
    static function getInfo(): mixed
    {
        foreach (static::getAll() as $key => $definer)
        {
            if (!class_exists($definer) && !empty($definer))
            {
                include_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . $definer . '.php');
            }

            if (class_exists($definer) && is_callable("$definer::check"))
            {
                $valid = $definer::check();
                if ($valid && is_callable("$definer::info"))
                {
                    return $definer::info();
                }
                // break manualy if info return is uncallable
                break;
            }
        }

        return null;
    }
}
