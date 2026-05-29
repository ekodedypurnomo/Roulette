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
namespace Roulette\Query\Option;

use Roulette\Query\Option\OptionAbstract;
use Roulette\Query\Option\Mixin\HasTable;
use Roulette\Query\Option\Mixin\HasSelect;
use Roulette\Query\Option\Mixin\HasWhere;
use Roulette\Query\Option\Mixin\HasOrder;
use Roulette\Query\Option\Mixin\HasGroup;
use Roulette\Query\Option\Mixin\HasLimit;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Select extends OptionAbstract
{
    use HasTable;
    use HasSelect;
    use HasWhere;
    use HasOrder;
    use HasGroup;
    use HasLimit;

    static string $action = 'SELECT';

    function reset(): static
    {
        $this->resetTable();
        $this->resetSelect();
        $this->resetWhere();
        $this->resetOrder();
        $this->resetGroup();
        $this->resetHaving();
        $this->resetLimit();
        return $this;
    }
}
