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
use Roulette\Query\Option\Mixin\HasPatch;

use Roulette\Query\Option\Select;
use Roulette\Query\Option\Insert;
use Roulette\Query\Option\Update;
use Roulette\Query\Option\Delete;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Option extends OptionAbstract
{
    use HasTable;
    use HasSelect;
    use HasWhere;
    use HasOrder;
    use HasGroup;
    use HasLimit;
    use HasPatch;

    static string $action = 'QUERY';

    function reset(): static
    {
        $this->resetTable();
        $this->resetSelect();
        $this->resetWhere();
        $this->resetOrder();
        $this->resetGroup();
        $this->resetLimit();
        $this->resetPatch();
        return $this;
    }

    function toSelect(): Select
    {
        $select = new Select();
        return $select
            ->table($this->getTable())
            ->select($this->getSelect())
            ->setWhere($this->getWhere())
            ->group($this->getGroup())
            ->having($this->getHaving())
            ->order($this->getOrder())
            ->take($this->getLimit())
            ->skip($this->getOffset());
    }

    function toUpdate(): Update
    {
        $update = new Update();
        return $update
            ->table($this->getTable())
            ->where($this->getWhere())
            ->set($this->getPatch());
    }

    function toDelete(): Delete
    {
        $delete = new Delete();
        return $delete
            ->table($this->getTable())
            ->where($this->getWhere());
    }

    function toInsert(): Insert
    {
        $insert = new Insert();
        return $insert
            ->table($this->getTable())
            ->set($this->getPatch());
    }
}
