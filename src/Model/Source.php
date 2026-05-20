<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please source the LICENSE
 * file that was distributed with this source code.
 */

/**
 * \Roulette\Model\DataSource is part of the model, which is used to declare a field of that model
 */
namespace Roulette\Model;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Query\Operation;
use Roulette\Model\Store;
use Roulette\Data\Join;

use Roulette\Mixin\Configurable;
use Roulette\Mixin\HasModel;

/**
 *  Source is part of the model, which is used to declare a field of that model
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Source extends Base
{
    use Configurable;
    use HasModel;

    /**
     * Name of field from database to access.
     *
     * @var array
     */
    protected ?string $table = null;

    protected ?Collection $joins = null;

    /**
     * __construct for function creates a new object field.
     * @param object|string|array $config field configuration
     */
    function __construct(mixed $config = null)
    {
        $me = $this;
        if (is_string($config)) $config = ['name' => $config];

        $configs = Collection::create($config);

        # set default table from name
        $configs->setIfNot([
            'table' => $configs->get('name')
        ]);

        $this->configure($configs->getAll(), [
            'except' => ['joins']
        ]);

        # configure validation
        $this->joins = new Collection($configs->get('joins'));
        $this->joins->each(function($v, $k, $a, $c) use($me)
        {
            $join = new Join($v);
            $join->setSource($me);
            // $assoc = $join->getAssociation(true); // this could be done here, several model not loaded yet
            $c->set($k, $join);
        });
    }

    /**
     * Method allows a class to decide how it will react when it is treated like a string.
     * Converting objects without __toString() method to string would cause E_RECOVERABLE_ERROR
     *
     * @return string [any string on name]
     */
    function __toString(): string
    {
        return (string) $this->table;
    }

    /**
     * Take specified Source from Field
     *
     * @return String
     */
    function getTable(): ?string
    {
        return $this->table;
    }

    function load(mixed $id = null): mixed
    {
        $model = Operation::getModel($this->model);

        # check on cache before goes deep
        if ((is_string($id) || is_numeric($id)) && $_c = $model::fetchFromCache($id))
        {
            return $_c;
        }

        $table = $this->getTable();
        $field = '*';
        $condition = $model::getFields()->mapToSource(
            is_array($id) ? $id : [$model::getPrimary() => $id]
        );

        $operation = Operation::createOperation('select')->buildQuery(function($qop) use($table, $field, $condition)
        {
            $qop->table($table)
                ->select($field)
                ->where($condition)
                ->limit(1);
        })->execute();

        if ($operation->success)
        {
            $rawData = (array) $operation->getRecord();
            $data = $model::getFields()->mapToName((array) $rawData);

            # create record
            # use __construct instead, need to pass in `$original = true`
            $record = new $model($data, $original = true);

            # create relations
            $this->joins->each(function($join, $k) use($rawData, $record)
            {
                $join = $join;
                $joinData = $join->fetchData($rawData);
                if ($assoc = $join->getAssociation())
                {
                    $assoc->associate($record, $joinData);
                }
            });

            return $record;
        }

        return null;
    }

    function find(mixed $condition = [], mixed $order = null, mixed $take = null, int $skip = 0, mixed $group = null, mixed $having = null): Store
    {
        $me = $this;
        $model = $this->getModel();
        $operation = Operation::select([
            'table'     => $this->getTable(),
            'fields'    => '*',
            'condition' => $model::getFields()->mapToSource($condition),
            'take'      => $take,
            'skip'      => $skip,
            'order'     => $order,
            'group'     => $group,
            'having'    => $having
        ]);

        $records = new Store($operation->getRecords(), $model);

        $records->each(function($v, $k, $a, $c) use($model, $me)
        {
            $rawData = (array) $v;
            $record = new $model($rawData, true);
            $c->set($k, $record);

            # create relations
            $me->joins->each(function($join, $k) use($rawData, $record)
            {
                $joinData = $join->fetchData($rawData);
                $join->getAssociation()->associate($record, $joinData);
            });
        });

        return $records;
    }
}
