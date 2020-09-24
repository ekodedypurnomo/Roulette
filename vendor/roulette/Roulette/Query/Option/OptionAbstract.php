<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Query\Option;

use Roulette\Base;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
abstract class OptionAbstract extends Base
{
	static $action = 'QUERY';

	static function getAction()
	{
		return static::$action;
	}

	function __construct($table = null)
	{
		$this->table($table);
		return $this;
	}

	abstract function reset();
}