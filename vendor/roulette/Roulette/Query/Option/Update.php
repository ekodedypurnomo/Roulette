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

use Roulette\Query\Option\OptionAbstract;
use Roulette\Query\Option\Mixin\HasTable;
use Roulette\Query\Option\Mixin\HasPatch;
use Roulette\Query\Option\Mixin\HasWhere;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Update extends OptionAbstract
{
	use HasTable;
	use HasPatch;
	use HasWhere;
	
	static $action = 'UPDATE';

	function reset()
	{
		$this->resetTable();
		$this->resetPatch();
		$this->resetWhere();
		return $this;
	}
}