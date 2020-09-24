<?php
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
    function __construct($iterable = null, $model = null)
    {
        parent::__construct();

        $this->setModel($model);

        # reconstruct to allow only instance of field or config of field
        $me = $this;
        BaseCollection::create($iterable)->each(function($v, $k) use($me)
        {
            $me->add($v);
        });

        return $this;
    }

    /**
     * Add field to the model
     */
    function add()
    {
        $args = func_get_args();
        $model = $this->getModel();

        foreach ($args as $i => $r)
        {
            $r = $model::create($r);
            $this->set($r->getId(), $r);
        }

        return $this;
    }

    function commit()
    {
        $this->each(function($id, $record)
        {
            $record->commit();
        });
        return $this;
    }

    function revert()
    {
        $this->each(function($id, $record)
        {
            $record->revert();
        });
        return $this;
    }

    function save()
    {
        $this->each(function($id, $record)
        {
            $record->save();
        });
        return $this;
    }

    function destroy()
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
	function getData($fields = null)
    {
    	$me = $this;
    	$data = array();
        
        $this->each(function($record, $i) use(&$data, $fields, $me)
        {
        	$data[] = $record->getData($fields);
        });

        return $data;
    }
}