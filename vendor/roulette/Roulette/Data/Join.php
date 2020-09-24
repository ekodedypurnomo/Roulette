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
use Roulette\Model\Source;
use Roulette\Association as BaseAssociation;
use Roulette\Association\Relation as AssociationValue;
use Roulette\Collection;
use Roulette\Regexp;
use Roulette\Template;

use Roulette\Mixin\Configurable;

/**
 * Assosiation was a description of a relationship between a model one with the other models
 * which in this function there-many relationship model or one model
 * 
 * @package \Roulette\Data
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Join extends Base
{
	use Configurable;

	protected $source = null;

    protected $association = null;

    /**
     * Regex|callable to identify field name is a part of joined field
     * @var null
     */
    protected $identifier = null;

    /**
     * array(Regex,replacer)|callable|Roulete\Template to resolve the real fieldname to call `get` function on Model
     * @var null
     */
    protected $resolver = null;

    function __construct($config = null)
    {
        $configs = Collection::create($config);

        $this->configure($configs->getAll());

        return $this;
    }

    function setSource(Source $source)
    {
    	$this->source = $source;
    	return $this;
    }

    function getSource()
    {
    	return $this->source;
    }

    function setAssociation(Association $association)
    {
    	$this->association = $association;
    	return $this;
    }

    function getAssociation($apply = false)
    {
    	$model = $this->getSource()->getModel();
    	$assoc = $model::getAssociation($this->association);
        return $assoc;
    }

    function identify($rawData = null, $continueResolve = false)
    {
    	if (!is_array($rawData)) return;

    	$identifier = $this->identifier;
    	$identified = array();
    	
        # indicate a custom identifer
        if (is_callable($identifier))
    	{
    		foreach ($rawData as $key => $value)
    		{
    			if ( call_user_func_array($identifier, array($key, $value, $rawData, $this)) )
    			{
    				$identified[$key] = $value;
    			}
    		}
            return $identified;
    	}
	
        # indicate use regex string
		if (is_string($identifier))
        {
            $identifier = new Regexp($identifier);
        }

        # indicate use regex
        if ($identifier instanceof Regexp)
        {
            foreach ($rawData as $key => $value) 
            {
                // skip unmatch field
                if ( ! $identifier->test($key)) continue;
                
                $identified[$key] = $value;
            }
        }

        if ($continueResolve)
        {
            $identified = $this->resolve($identified);
        }

    	return $identified;
    }

    function resolve($rawData = null, $identifyFirst = false)
    {
        if (!is_array($rawData)) return;

        if ($identifyFirst)
        {
            $rawData = $this->identify($rawData);
        }

        $resolver = $this->resolver;
        $resolved = array();
        
        # indicate a custom resolver
        if (is_callable($resolver))
        {
            foreach ($rawData as $key => $value)
            {
                $resolvedFieldString = call_user_func_array($resolver, array($key, $value, $rawData, $this));
                $resolved[$resolvedFieldString] = $value;
            }
            return $resolved;
        }
    
        # indicate use Roulette\Template
        if (is_string($resolver))
        {
            $resolver = new Template($resolver);
        }
        if($resolver instanceof Template)
        {
            foreach ($rawData as $key => $value) 
            {
                $resolvedFieldString = $resolver->apply(array('field'=>$key, 'value'=>$value));
                $resolved[$resolvedFieldString] = $value;
            }
        }

        # indicate use regex replacer
        if (is_array($resolver))
        {
            if (empty($resolver[0])) $resolver[0] = ""; // regex
            if (empty($resolver[1])) $resolver[1] = ""; // replacer

            $resolver = new Regexp($resolver[0], $resolver[1]);
        }
        if ($resolver instanceof Regexp)
        {
            foreach ($rawData as $key => $value) 
            {
                $resolvedFieldString = $resolver->replace($key);
                $resolved[$resolvedFieldString] = $value;
            }
        }

        return $resolved;
    }

    function fetchData($rawData = null)
    {
        return $this->resolve($this->identify($rawData));
    }
}