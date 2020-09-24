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
use Roulette\Model\Field\Field;
use Roulette\Template;

/**
 * Collection class specialy and only to manage Fields
 * 
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Fields extends BaseCollection
{
    /**
     * [__construct description]
     * @param [type] $iterable [description]
     */
    function __construct($iterable = null)
    {
        parent::__construct();

        # reconstruct to allow only instance of field or config of field
        $me = $this;
        BaseCollection::create($iterable)->each(function($v, $k) use($me)
        {
            $me->add($v);
        });

        return $this;
    }

    protected function _set($key = null, $field = null)
    {
        if(!($field instanceof Field))
        {
            $field = new Field($field);
        }

        parent::_set($key, $field);

        return $this;   
    }

    protected function _add($field = null)
    {
        if(!($field instanceof Field))
        {
            $field = new Field($field);
        }

        $this->_set($field->getName(), $field);

        return $this;
    }

	/**
     * Get the attribute for every fields in the model
     * 
     * @param  string $attribute 
     * @param  string|array $fields     
     * @return array           
     */
    function getAttribute($attribute = null)
    {
        # search from current all fields
        $collectedAttribute = array();

        $this->each(function($f) use( &$collectedAttribute, $attribute)
        {
            $collectedAttribute[$f->getName()] = $f->getConfig($attribute);
        });

        return $collectedAttribute;
    }

    /**
     * Get the name of the fields of the model
     * 
     * @param  string $fields 
     * @return array
     */
    function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the source fields of the model
     * 
     * @param string $fields
     * @return array
     */
    function getSource()
    {
        return $this->getAttribute('source');
    }

    /**
     * Get the displayed fields of the model
     * @param  string $fields
     * @return array
     */
    function getDisplay()
    {
        return $this->getAttribute('display');
    }

    /**
     * Find the field that have the private property being set to true from the model
     * @param  boolean $onlyName true to get only name
     * @return array            
     */
    function filterPrivate()
    {
        return $this->filter(function($v, $k, $a, $me)
        {
            return $v->isPrivate();
        });
    }

    /**
     * Find the field that have the privet property being set to false from the model
     * @param  boolean $onlyName true to get only name
     * @return array            
     */
    function filterPublic()
    {
        return $this->filter(function($v, $k, $a, $me)
        {
            return $v->isPublic();
        });
    }

    /**
     * Find fields that have property insert being set to true
     * @param  boolean $onlyName true to get only name
     * @return array            
     */
    function filterInsertable()
    {
        return $this->filter(function($v, $k, $a, $me)
        {
            return $v->isInsertable();
        });
    }

    /**
     * Find field that have the property select being set to true
     * @param  boolean $onlyName true to get only name
     * @return array
     */
    function filterSelectable()
    {
        return $this->filter(function($v, $k, $a, $me)
        {
            return $v->isSelectable();
        });
    }

    /**
     * Find fields that have property update being set to true
     * @param  boolean $onlyName true to get only name
     * @return array            
     */
    function filterUpdatable()
    {
        return $this->filter(function($v, $k, $a, $me)
        {
            return $v->isUpdatable();
        });
    }

    /**
     * Find field that have the property delete being set to true
     * @param  boolean $onlyName true to get only name
     * @return array            
     */
    function filterDeletable()
    {
        return $this->filter(function($v, $k, $a, $me)
        {
            return $v->isDeletable();
        });
    }

    /**
     * Map given array of data ('fieldName'=>'value') into fieldSource ('fieldSource' => 'value')
     * 
     * @param  string|array $data 
     * @return [type]       
     */
    function mapToSource($data = null)
    {
        $fields = $this->getSource();
        $mapped = null;

        if (is_string($data))
        {
            $mapped = Template::compile($data, $fields);
        }
        if (is_array($data))
        {
            $mapped = array();
            foreach ($data as $key => $value) 
            {
                # compile if key contains source 
                $key = array_key_exists($key, $fields) ? $fields[$key] : Template::compile($key)->apply($fields);
                
                # compile if value contains source
                $value = Template::compile($value)->apply($fields);
                
                # reorder all in new array
                $mapped[$key] = $value;
            }
        }

        return $mapped;
    }

    /**
     * Map given array of data ('fieldSource'=>'value') into fieldName ('fieldName' => 'value')
     * 
     * @param  string|array $data 
     * @return [type]       
     */
    function mapToName($data = null)
    {
        # the only changes is to flip the `name=>source` into `source=>key`
        $fields = array_flip($this->getSource());
        $mapped = null;

        if (is_string($data))
        {
            $mapped = Template::compile($data, $fields);
        }
        if (is_array($data))
        {
            $mapped = array();
            foreach ($data as $key => $value) 
            {
                # compile if key contains source
                $key = array_key_exists($key, $fields) ? $fields[$key] : Template::compile($key)->apply($fields);
                
                # compile if value contains source
                $value = Template::compile($value)->apply($fields);
                
                # reorder all in new array
                $mapped[$key] = $value;
            }
        }

        return $mapped;
    }

    function resolveName($fields = null)
    {
        if (empty($fields) or (is_string($fields) and $fields == '*'))
        {
            $fields = $this->filter(function($v, $k)
            {
                return $v->isPublic();
            })->getName();
        }
        elseif (is_string($fields))
        {
            $fields = array($fields => $fields);
        }
        elseif (is_array($fields))
        {
            $_fields = array();
            foreach ($fields as $fieldsName => $fieldsAlias)
            {
                if (is_numeric($fieldsName))
                {
                    $fieldsName = $fieldsAlias;
                }
                $_fields[$fieldsName] = $fieldsAlias;
            }
            $fields = $_fields;
        }

        return $fields;
    }
}