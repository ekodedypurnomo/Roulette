<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Roulette\Association package contain any class which support relation between models
 */
namespace Roulette\Model\Association;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Model;
use Roulette\Model\Field\Field;
use Roulette\Model\Store;
use Roulette\Model\Association\Relation;

use Roulette\Mixin\Configurable;
use Roulette\Mixin\HasModel;

/**
 * Assosiation was a description of a relationship between a model one with the other models
 * which in this function there-many relationship model or one model
 * 
 * @package \Roulette\Model\Association
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
abstract class AssociationAbstract extends Base
{   
    use Configurable;
    use HasModel;

    /**
     * Type of the Association 
     * only HASMANY | HASONE
     * @var string
     */
    const TYPE = null;

    const HASONE = 1;
    
    const HASMANY = 2;

    /**
     * name of the Association
     * @var String
     */
    protected $name = null;

    protected $pivot = null;
    
    /**
     * Foreign field of the associated model
     * @var null
     */
    protected $field = null;

    /**
     * [__construct description]
     * 
     * @param [type] $config [description]
     */
    function __construct($config = null)
    {
        if (is_string($config)) $config = array('name'=>$config);

        $configs = Collection::create($config);

        $configs->setIfNot(array(
            'name' => $configs->get('model')
        ));

        $this->configure($configs->getAll());

        return $this;
    }

    function setPivot($class = null)
    {
        $this->pivot = $class;
    }

    /**
     * get the Name fo the Association
     * @return String
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * get the foreign field of the assocaited model
     * @return String [description]
     */
    function getField()
    {
        return $this->field;
    }

    protected function getRelationFrom(Model $record)
    {
        $name = $this->getName();
        $recordRelations = $record->getRelations();

        $relation = $recordRelations->get($name);

        if (!$relation)
        {
            $relation = new Relation($this, $record);
            
            $recordRelations->set($name, $relation);
        }

        return $relation;
    }

    /**
     * Get record/s from the model that being associated with
     * 
     * @param  array  $record retrieve data on a model
     * @param  boolean $reload choice whether or not to refresh
     * @return array [description]
     */
    function associate( Model $record, $reload = false )
    {
        $model = $this->getModel();
        $relation = $this->getRelationFrom($record);
        $value = $record->getId();

        # if null for foreignValue so we force remove
        if (is_null($value))
        {
            $this->resetRelation($relation);
        }
        # indicate to reload
        elseif ( $reload === true)
        {
            $this->loadRelation($relation);
        }
        # indicate if reload is collection of record|array
        # for datasource with associotion in raw
        elseif (is_array($reload))
        {
            $this->patchRelation($relation, $value = $reload);
        }

        return $relation;
    }

    function resetRelation(Relation $relation)
    {
        $relation->reset();
        return $this;
    }

    abstract function loadRelation(Relation $relation);

    abstract function patchRelation(Relation $relation, $value = null);

}
