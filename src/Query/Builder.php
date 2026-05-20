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
namespace Roulette\Query;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Query\Operation;
use Roulette\Query\Option\Select;
use Roulette\Query\Option\Insert;
use Roulette\Query\Option\Update;
use Roulette\Query\Option\Delete;
use Roulette\Query\Option\Option;
use Roulette\Callback;

/**
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Builder extends Base
{
    static function query(mixed $optionMode = 'query', mixed $table = null): static
    {
        return new static($table, $optionMode);
    }

    static function table(mixed $table = null, mixed $optionMode = 'query'): static
    {
        return new static($table, $optionMode);
    }

    protected mixed $option = null;

    public function __call(string $method, array $arguments): static
    {
        if (!method_exists($this, $method))
        {
            call_user_func_array([$this->getOption(), $method], $arguments);
            return $this; // force to return this
        }

        return $this;
    }

    function __construct(mixed $table = null, mixed $optionMode = 'query')
    {
        switch (strtoupper((string) $optionMode))
        {
            case Select::$action:
                $this->option = new Select($table);
                break;

            case Insert::$action:
                $this->option = new Insert($table);
                break;

            case Update::$action:
                $this->option = new Update($table);
                break;

            case Delete::$action:
                $this->option = new Delete($table);
                break;

            case Option::$action:
            default:
                $this->option = new Option($table);
                break;
        }
    }

    function getOption(): mixed
    {
        if (!$this->option) $this->option = new Option();

        return $this->option;
    }

    function get(): Collection
    {
        $option = $this->getOption()->toSelect();
        $operation = Operation::create($option, true, true);
        return Collection::create($operation->getRecords());
    }

    function first(): mixed
    {
        $option = $this->getOption()->toSelect();
        $option->limit(1);
        $operation = Operation::create($option, true, true);
        return $operation->getRecord();
    }

    function update(mixed $patch = null): bool
    {
        $option = $this->getOption()->toUpdate();
        $option->set($patch);
        $operation = Operation::create($option, true, true);
        return $operation->isSuccess();
    }

    function insert(mixed $patch = null): bool
    {
        $option = $this->getOption()->toInsert();
        $option->set($patch);
        $operation = Operation::create($option, true, true);
        return $operation->isSuccess();
    }

    function delete(): bool
    {
        $option = $this->getOption()->toDelete();
        $operation = Operation::create($option, true, true);
        return $operation->isSuccess();
    }
}
