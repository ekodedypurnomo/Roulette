<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

use Roulette\Base;
use Roulette\Tunel\tunnels;
use Roulette\Tunel\Tunel;
use Roulette\Tunel\Definer;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class FrameworkCheckers extends Base
{	
	/**
	 * [$frameworks description]
	 * @var null
	 */
	protected static $frameworks = null;
	
	/**
	 * [$configLoaded description]
	 * @var boolean
	 */
	protected static $configLoaded = false;

	/**
	 * [getAll description]
	 * @return [type] [description]
	 */
	static function getAll()
	{
		if (!static::$configLoaded)
		{
			$tunnelDir = __DIR__.'/tunnels.php';

			static::$frameworks = require_once(str_replace('/', DIRECTORY_SEPARATOR, $tunnelDir));
			static::$configLoaded = true;
			
			if (!is_array(static::$frameworks)) static::$frameworks = array();
		}
		return static::$frameworks;
	}

	/**
	 * [has description]
	 * @param  [type]  $frameworkName [description]
	 * @return boolean                [description]
	 */
	static function has($frameworkName = null)
	{
		$has = false;
		if (empty($frameworkName)) return false;

		foreach (static::getAll() as $key => $value) 
		{
			$has = preg_match('/'.strtolower($frameworkName).'/', strtolower($key));
			if ($has)break;
		}
		return $has;
	}

	/**
	 * [getInfo description]
	 * @return [type] [description]
	 */
	static function getInfo()
	{
		foreach (static::getAll() as $key => $definer)
		{
			if (!class_exists($definer) and !empty($definer))
			{
				include_once(dirname(__DIR__)."\\".$definer.".php");
			}

			if (class_exists($definer) and is_callable("$definer::check"))
			{
				$valid = $definer::check();
				if ($valid and is_callable("$definer::info"))
				{
					return $definer::info();
				}
				// break manualy if info return is uncallable
				break;
			}
		}
	}
}