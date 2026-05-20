<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Data;

use Roulette\Base;
use Roulette\Collection;

use Roulette\Mixin\Configurable;

/**
 * Is a class for helps in manipulating array in a single object.
 * 
 * @package \Roulette\Data
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Option extends Base
{

	use Configurable
	{
		setConfig as protected setConfigTrait;
	}

	public $fields = '*';

	public $render = true; // by default will follow model default render

	public $inline = false;

	public $display = null;

	public $merge = false;

	public $mergeMask = '{field}';

	public $autoLoad = false;

	public $relations = null;

	public function __construct($config = null)
	{
        # boolean mean user set the render into it
        if (is_bool($config))
        {
            $this->render = $config;
            return $this;
        }

        # indicate user input a fields(s) name
        if (is_string($config) or (is_array($config) and !Collection::isAssoc($config)))
        {
            $this->setFields($config);
            return $this;
        }

        $configs = Collection::create($config);

        if (is_string($configs->get('inline')))
        {
        	$configs->set('display', $configs->get('inline'));
        	$configs->set('inline', true);
        }

        if (is_string($configs->get('merge')))
        {
        	$configs->set('mergeMask', $configs->get('merge'));
        	$configs->set('merge', true);
        }

        $this->configure($configs->getAll());

        return $this;
	}

	public function setConfig($config, $value)
	{
		if (is_string($config) and $config == 'relations')
		{
			$this->setRelations($value);
			return $this;
		}

		// throw back to trait
		$this->setConfigTrait($config, $value);

		return $this;
	}

	function getFields()
	{
		return $this->fields;
	}

	function setFields($fields = null)
	{
		if(is_string($fields) and !empty($fields) and ($fields != "*"))
		{
			$fields = array($fields);
		}
		$this->fields = $fields;

		return $this;
	}

	function setRender( $render )
	{
		$this->render = (boolean) $render;

		return $this;
	}

	function isRender()
	{
		return (boolean) $this->render;
	}

	function setAutoLoad( $autoLoad )
	{
		$this->autoLoad = (boolean) $autoLoad;

		return $this;
	}

	function isAutoLoad()
	{
		return (boolean) $this->autoLoad;
	}

	function setInline( $inline )
	{
		$this->inline = (boolean) $inline;

		return $this;
	}

	function isInline()
	{
		return (boolean) $this->inline;
	}

	function setMerge( $merge )
	{
		$this->merge = (boolean) $merge;

		return $this;
	}

	function isMerge()
	{
		return (boolean) $this->merge;
	}

	function setMergeMask($mergeMask = null)
	{
		$this->mergeMask = (string) $mergeMask;

		return $this;
	}

	function getMergeMask()
	{
		return (string) $this->mergeMask;
	}

	function setDisplay($display = null)
	{
		$this->display = (string) $display;
		
		return $this;
	}

	function getDisplay()
	{
		return (string) $this->display;
	}

	function setRelations( $relations = null )
	{
		$_relations = $this->getRelations()->reset();

		if (empty($relations))
        {
            return $this;
        }

		// indicate if only one relation
		if (is_string($relations))
		{
			$_relations->set($relations, $relations);
		}
		// indicate many relations
		elseif (is_array($relations))
		{
			foreach ($relations as $relationName => $relationConfig)
			{
			    if (is_numeric($relationName))
			    {
			        $relationName = $relationConfig;
			        $relationConfig = array('display'=>$relationName);
			    }

			    $_relations->set($relationName, new $this($relationConfig));
			}
		}

		return $this;
	}

	function getRelations()
	{
		if (!($this->relations instanceof Collection)) 
		{
			$this->relations = new Collection();
		}
		return $this->relations;
	}

}