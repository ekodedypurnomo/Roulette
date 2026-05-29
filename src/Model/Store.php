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
namespace Roulette\Model;

use Roulette\Collection as BaseCollection;

use Roulette\Mixin\HasModel;

/**
 * Is a class for helps in manipulating array in a single object.
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Store extends BaseCollection
{
    use HasModel;

    /**
     * [__construct description]
     * @param [type] $iterable [description]
     */
    function __construct(mixed $iterable = null, mixed $model = null)
    {
        parent::__construct();

        $this->setModel($model);

        # reconstruct to allow only instance of field or config of field
        $me = $this;
        BaseCollection::create($iterable)->each(function($v, $k) use($me)
        {
            $me->add($v);
        });
    }

    /**
     * Add field to the model
     */
    function add(mixed ...$args): static
    {
        $model = $this->getModel();

        foreach ($args as $r)
        {
            $r = $model::create($r);
            $this->set($r->getId(), $r);
        }

        return $this;
    }

    function commit(): static
    {
        $this->each(function($id, $record)
        {
            $record->commit();
        });
        return $this;
    }

    function revert(): static
    {
        $this->each(function($id, $record)
        {
            $record->revert();
        });
        return $this;
    }

    function save(): static
    {
        $this->each(function($id, $record)
        {
            $record->save();
        });
        return $this;
    }

    function destroy(): static
    {
        $this->each(function($id, $record)
        {
            $record->destroy();
        });
        return $this;
    }

    /**
     * [getData description]
     * @return [type] [description]
     */
    function getData(mixed $fields = null): array
    {
        $data = [];

        $this->each(function($record, $i) use(&$data, $fields)
        {
            $data[] = $record->getData($fields);
        });

        return $data;
    }
}
