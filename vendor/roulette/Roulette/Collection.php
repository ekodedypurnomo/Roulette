<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

use Roulette\Base;

use Countable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use Roulette\Contract\Jsonable;
use Roulette\Contract\Arrayable;

/**
 * Is a class for helps in manipulating array in a single object.
 * 
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Collection extends Base implements IteratorAggregate, JsonSerializable, Countable, Jsonable, Arrayable
{
    /**
     * Check if variable is associative array 
     *      
     *      Example:
     *      \Roulette\Collection::isAssoc(array(1,2,3)); // false
     *      
     *      \Roulette\Collection::isAssoc(array('a'=>1,'b'=>2,3)); // true
     *      
     *      \Roulette\Collection::isAssoc(array('1'=>1,'2'=>2,	=>3)); // false
     * 
     * @param mixed $array Array
     * @return Boolean if the variable is association array
     */ 
    static function isAssoc(array $array = null)
    {
        return ( is_array($array) and array_keys($array) !== range(0, count($array) - 1) );
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * @return array
     */
    static function iterable($iterable = null)
    {
    	if (is_null($iterable))
    	{
    		return array();
    	}
        if (is_array($iterable))
        {
            return $iterable;
        } 
        elseif ($iterable instanceof static) 
        {
            return $iterable->toArray();
        } 
        elseif ($iterable instanceof Arrayable) 
        {
            return $iterable->toArray();
        } 
        elseif ($iterable instanceof Jsonable) 
        {
            return json_decode($iterable->toJson(), true);
        } 
        elseif ($iterable instanceof JsonSerializable)
        {
            return $iterable->jsonSerialize();
        }

        return (array) $iterable;
    }

    /**
     * Get a valid defined value in an array
     * if value doesnt defined so it will return default value  
     *      
     *      Example:
     *      \Roulette\Collection::enum('a',array('a','b')); // return 'a'
     * 
     *      \Roulette\Collection::enum('c',array('a','b')); // return null
     * 
     *      \Roulette\Collection::enum('c',array('a','b'),'d'); // return 'd'
     * 
     * @param Mixed $var Value to check existense 
     * @param Array $list Array of defined values
     * @param Mixed $default Default value if $var doens't exist in $list
     * @param boolean $strict 
     * @return Mixed 
     */
    static function enum($var, $list, $default = null, $strict = false)
    {
        if (is_array($list) and in_array($var, $list, $strict))
        {
            return $var;
        } else {
            return $default;
        }
    }

    static function with($iterable = null, callable $callback = null)
    {
    	$collection = new static($iterable);

    	$collection->pipe($callback);

    	return $collection->getAll();
    }


	///////////////////////
	// begin for object  //
	///////////////////////

	/**
	 * Default value items collection
	 * @var array
	 */
	protected $items = array();

	/**
	 * [__construct description]
	 * @param [type] $iterable [description]
	 */
	function __construct($iterable = null, $map = null)
	{
		$iterable = static::iterable($iterable);

		// mapping keys
		if (is_array($map) and !empty($map))
		{
			$_iterableCopy = $iterable;
			$iterable = array();

			foreach ($_iterableCopy as $key => $value)
			{
				if (array_key_exists($key, $map))
				{
					$key = $map[$key];
				}
				$iterable[$key] = $value;
			}
		}

		$this->items = $iterable;

		return $this;
	}

	function __toString()
	{
		return $this->toJson();
	}
	
	/**
	 * Create new intance of Collection.
	 * New instance will be returned.
	 *
	 *		$collection = Collection::create(array(
	 *			'one'=>1,'two'=>2,'three'=>3,'four'=>4
	 *		));
	 *		// now $collection is intance of Collection
	 *		// and able to use any function of Collection
	 *		$collection->get('one'); // will return 1
	 * 
	 * @param  array|string $iterable An array of item.
	 * @return Collection
	 */
	// function create() {}

	/**
	 * Get Items with specified value
	 * @return array
	 */
	protected function &items()
	{
		if ( !is_array($this->items) )
		{
			$this->items = array();
		}
		return $this->items;
	}

	/**
	 * Get the number of item in the collection
	 * @return number the number of items on
	 */
	function getCount()
	{
		return count($this->items());
	}

	/**
	 * Check whether the items is empty or not
	 * @return boolean true if there is no item in the collection
	 */
	function isEmpty()
	{
		return $this->getCount() == 0;
	}

	/**
	 * Get an item in the collection specified by the key.
     *
     *      Example:
     *      $source = array(
     *              'a'=>'A',
     *              'b'=>'B',
     *              'c'=>null,
     *              'd'=>false
     *          );
     *
     *      $matchs = \Roulette\Collection::match(
     *          $source, 
     *          array('a','b')
     *      ); // return: array('a'=>'B','b'=>'B')
     *
     *      $matchs = \Roulette\Collection::match(
     *          $source, 
     *          array('a','b','c','d')
     *      ); // return: array('a'=>'B','b'=>'B','d'=>false)
     *
     *      $matchs = \Roulette\Collection::match(
     *          $source, 
     *          array('a','b','c','d'), 
     *          true
     *      ); // return: array('a'=>'B','b'=>'B','c'=>null,'d'=>false)
     *
     *      $matchs = \Roulette\Collection::match(
     *          $source, 
     *          array('h')
     *      ); // return: array()
     *
     *      $matchs = \Roulette\Collection::match(
     *          $source, 
     *          array('h'), true, true
     *      ); // return: array('h'=>null)
     *
     *      $matchs = \Roulette\Collection::match(
     *          $source, 
     *          array('h'), true, true, 5
     *      ); // return: array('h'=>5)
     * 
	 * @param  number|string|array $key  use number or string to get it by index, array to get it by the value
	 * @param  array $default 
	 * @return array          
	 */
	function get($key = null, $default = null, $skipNull = true)
	{
		$items = $this->items();

		if (is_array($key))
		{
			$data = array();
			foreach ($key as $itemKey => $alias)
			{
				if (is_integer($itemKey))
				{
					$itemKey = $alias;
				}
				$value = $this->get($itemKey);

                # nex loop if skip null
                if (is_null($value) and !$skipNull) continue;

                $data[$alias] = $value;
			}
			return $data;
		}

		if ((is_string($key) or is_int($key)) and array_key_exists($key, $items))
		{
			return $items[$key];
		}
		else
		{
			return $default;
		}
	}

	/**
	 * put items collection based index $at
	 * @param  integer $at      index collection
	 * @param  nul  $default default value
	 * @return Collection
	 */
	function getAt($at = 0, $default = null)
	{
		$items = $this->items();
		$keys = array_keys($items);

		// dd($keys, $items);
		# make sure if index is valid
		if (array_key_exists($at, $keys))
		{
			return $items[$keys[$at]];
		}
		else
		{
			return $default;
		}
	}

	/**
	 * put first value in items collection
	 * @return Collection
	 */
	function getFirst()
	{
		$items = $this->items();
		return reset($items);
	}

	/**
	 * put last value in items Collection
	 * @return Collection
	 */
	function getLast()
	{
		$items = $this->items();
		return end($items);
	}

	/**
	 * Get the key of the items in the collection
	 * @return array 
	 */
	function getKeys()
	{
		return array_keys($this->items());
	}

	/**
	 * Get the values of the items in the collection
	 * @return array 
	 */
	function getValues()
	{
		return array_values($this->items());
	}

	/**
	 * Get all items in the collection
	 * @return array
	 */
	function getAll($config = null)
	{	
		if(is_array($config))
		{
			$items = $this->items();

			// except filter
			if(array_key_exists('except', $config) and is_array($config['except']))
			{
				foreach ($items as $key => $value)
				{
					if(in_array($key, $config['except']))
					{
						unset($items[$key]);
					}
				}
			}

			if(array_key_exists('only', $config) and is_array($config['only']))
			{
				foreach ($items as $key => $value)
				{
					if(!in_array($key, $config['only']))
					{
						unset($items[$key]);
					}
				}
			}

			return $items;
		}

		return $this->items();
	}

	/**
	 * Get the item from the collection in array format
	 * @return array
	 */
	function toArray()
	{
		return call_user_func_array(array($this,'getAll'), func_get_args());
	}

	/**
	 * Get the items from the collection in JSON format
	 * @param  boolean $assoc   
	 * @param  integer $depth   
	 * @param  integer $options 
	 * @return boject           
	 */
	function toJson($assoc = false, $depth = 512, $options = 0)
	{
		return json_encode($this->getAll(), $options, $depth);
	}

	/**
	 * Sets key with the new value.
	 * 
	 * @param object|array|string $key [description]
	 * @param object $value [description]
	 */
	function set($key = null, $value = null)
	{
		if (is_object($key))
		{
			$key = get_object_vars($key);
		}
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->set($k, $v);
			}
			return $this;
		}

		$this->_set($key, $value);
		
		return $this;
	}

	// this function is overridable
	protected function _set($key = null, $value = null)
	{
		$items =& $this->items();

		$items[$key] = $value;
		
		return $this;	
	}

	/**
	 * Sets key with the new value. If only the key is already defined in collection.
	 * 
	 * @param [type]  $key    [description]
	 * @param [type]  $value  [description]
	 * @param boolean $strict [description]
	 */
	function setIf($key = null, $value = null, $strict = false)
	{
		if (is_object($key))
		{
			$key = get_object_vars($key);
		}
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->setIf($k, $v, $strict);
			}
			return $this;
		}

		$items =& $this->items();
		
		if ( $this->has($key, $strict) )
		{
			$this->set($key, $value);
		}
		return $this;
	}

	/**
	 * Sets key with the new value. If only the key is not defined in collection.
	 * 
	 * @param [type]  $key    [description]
	 * @param [type]  $value  [description]
	 * @param boolean $strict [description]
	 */
	function setIfNot($key = null, $value = null, $strict = true)
	{
		if (is_object($key))
		{
			$key = get_object_vars($key);
		}
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->setIfNot($k, $v, $strict);
			}
			return $this;
		}

		$items =& $this->items();

		if ($strict)
		{
			if ( !$this->has($key, $strict) )
			{
				$this->set($key, $value);
			}
		}
		else
		{
			if ( is_null($this->get($key)) )
			{
				$this->set($key, $value);
			}
		}

		return $this;
	}

    /**
     * fill an Iterable with given value on its key/property in defined list if key/property is not exist
     *      
     *      Example:
     *      \Roulette\Collection::fillKey(
     *          '5'
     *          array('a','b'),
     *      ); // return array('a'=>'5', 'b'=>'5')
     * 
     *      \Roulette\Collection::fillKey(
     *          '5'
     *          array('b'),
     *      ); // return array('a'=>null, 'b'=>'5')
     * 
     * @param Array $source Iterable value 
     * @param Array $var defined list key to fill
     * @param Array $value value to fill each defined key/property on list
     * @return Iterable $source
     */
    function fill($value = null, $keys = null)
    {
        if (is_string($keys))
        {
            $keys = array($keys);
        }

        # fill keys with value
        if (is_array($keys))
        {
            foreach ($keys as $i => $key)
            {
                $this->set($key, $value);
            }
        }
        return $this;
    }

    /**
     * fill an Iterable with given value on its key/property in defined list if key/property is not exist
     *      
     *      Example:
     *      \Roulette\Collection::fillKey(
     *          array(),
     *          array('a','b'),
     *          '5'
     *      ); // return array('a'=>'5', 'b'=>'5')
     * 
     *      \Roulette\Collection::fillKey(
     *          array('a'=>null),
     *          array('a','b'),
     *          '5'
     *      ); // return array('a'=>null, 'b'=>'5')
     * 
     * @param Array $source Iterable value 
     * @param Array $var defined list key to fill
     * @param Array $value value to fill each defined key/property on list
     * @return Iterable $source
     */
    function fillIf($value = null, $keys = null, $strict = false)
    {
        if (is_string($keys))
        {
            $keys = array($keys);
        }
        
        # fill keys with value
        if (is_array($keys))
        {
            foreach ($keys as $i => $key)
            {
                if ( !$this->hasKey($key) ) continue;
                
                if ( $strict and is_null($this->get($key)) ) continue; 

                $this->set($key, $value);
            }
        }
        return $this;
    }
    
    /**
     * Filter field with the key if exist
     * 
     * @param  array|object $source
     * @param  array $var
     * @param  array|object $value
     * @return array
     */
    function fillIfNot($value = null, $keys = null, $strict = true)
    {
        if (is_string($keys))
        {
            $keys = array($keys);
        }

        # fill keys with value
        if (is_array($keys))
        {
            foreach ($keys as $i => $key)
            {
                $exist = $this->hasKey($key);
                $existValue = $this->get($key);
                
                if ($strict)
                {
                    if ( !$exist or ($exist and is_null($existValue) ) )
                    {
                        $this->set($key, $value);
                    }
                }
                else
                {
                    if ( !$exist )
                    {
                    	$this->set($key, $value);
                    }    
                }
            }
        }
        return $this;
    }

	/**
	 * Add one or more item to the collection.
	 *
	 * Example:
	 * ```php
	 * 	$coll = new Collection();
	 * 	$coll->add('John'); // will be saved into collection with numeric key
	 * 	$coll->add(true, 1, 'some value'); // all passed arguments will be saved too 
	 * ```
	 * @return Collection
	 */
	function add()
	{
		$args = func_get_args();

		foreach ($args as $key => $value)
		{
			$this->_add($value);
		}

		return $this;
	}

	// this function is overridable
	protected function _add($value = null)
	{
		$items =& $this->items();
		$items[] = $value;
		return $this;
	}

	/**
	 * Adds all elements of an Array or an Object to the collection.
	 * @param array $items an array of item
	 */
	function addAll($items = null)
	{
		if (!is_array($items))
		{
			$items = array($items);
		}

		return call_user_func_array(array($this, 'add'), $items);
	}

	/**
	 * Set collection items into an empty array.
	 * @return Collection
	 */
	function reset()
	{
		$this->items = array();

		return $this;
	}

	/**
	 * Set value to `null` on each items
	 * @return Collection
	 */
	function clear()
	{
		$items =& $this->items();
		
		foreach ($items as $key => $value)
		{
			# forse set value, doesnt use `set` instead
			$items[$key] = null;
		}
		return $this;
	}

	/**
	 * Remove null item on the Collection
	 * @return Collection
	 */
	function clean()
	{
		$items =& $this->items();

		foreach ($items as $key => $value)
		{
			if ($value === null) unset($this->items[$key]);
		}  
		
		return $this;
	}

	/**
	 * Check existense of item in the collection
	 * 
	 * @param  mixed  	$item   Item to check the existense
	 * @param  boolean 	$strict Set `true` will compare with `===` operator and `==` otherwise
	 * @return boolean         	Existense status
	 */
	function contain($value = null, $strict = false)
	{
		$items = &$this->items();

		return in_array($value, $items, $strict);
	}

	/**
	 * Check if one or more of the anyMatchItems are in the Collection 
	 * 
	 * @param  Array 		$anyMatchItems 	Any match item
	 * @param  boolean 		$strict Set `true` will compare with `===` operator and `==` otherwise
	 * @return boolean		Anymatch status
	 */
	function containIn(array $anyMatchItems = null, $strict = false)
	{
		if (empty($anyMatchItems)) return false;

		foreach ($anyMatchItems as $key => $value)
		{
			if ($this->contain($value, $strict)) return true;
		}
		return false;
	}

	/**
	 * Check item has a Key are in the collection
	 * 
	 * @param  string|int $key must be string or integer
	 * @param  boolean 		$strict
	 * @return boolean        
	 */
	function has($key = null, $strict = true)
	{
		if (is_array($key))
		{
			# empty array should not be return false
			if (empty($key)) return false;

			$has = true;
			foreach ($key as $k => $v)
			{
				if (!$this->has($v, $strict))
				{
					return false;
				}
			}
			return $has;
		}

		$items =& $this->items();

		$hasKey = (is_string($key) || is_int($key)) && array_key_exists($key, $items);

		if ($strict)
		{
			$hasKey = ($hasKey and !is_null($items[$key]));
		}

		return $hasKey;
	}

	/**
	 * Check whether the item in the collection has the specified key
	 * 
	 * @param  string|array  $key retrieve data on has
	 * @return boolean      [description]
	 */
	function hasKey($key = null)
	{
		return $this->has($key, false);
	}

	/**
	 * CHeck whether item in the collection has the specified item
	 * 
	 * @param  string|array  $item   must be string or array
	 * @param  boolean $strict [description]
	 * @return boolean         [description]
	 */
	function hasItem($item = null, $strict = false)
	{
		return $this->contain($item, $strict);
	}

	/**
	 * Get the first item in the collection
	 * @return array [description]
	 */
	function first()
	{
		$items = $this->items();

		return reset($items);
	}

	/**
	 * Get the last item in the collection
	 * @return array [description]
	 */
	function last()
	{
		$items = $this->items();
		
		return end($items);
	}

	/**
	 * Returns the maximum value in the Collection.
	 * @return number Maximum value in the Collection.
	 */	
	function max()
	{
		$items = $this->items();

		if (!$this->isEmpty())
		{
			return max($items);
		}
	}

	/**
	 * Returns the minimum value in the Collection.
	 * @return number Minimum value in the Collection.
	 */
	function min()
	{
		$items = $this->items();
		
		if (!$this->isEmpty())
		{
			return min($items);
		}
	}

	/**
	 * Calculates the sum of items in the Collection.
	 * @return number Sum value.
	 */
	function sum()
	{
		$items = $this->items();

		return array_sum($items);
	}

	/**
	 * Returns average value from the Collection.
	 * @return number Average value.
	 */
	function average()
	{
		if (!$this->isEmpty())
		{
			return $this->sum() / $this->getCount();
		}
		return;
	}

	/**
	 * Return the generate sorting results from items
	 * @return Array
	 */
	function sort()
	{
		sort($this->items());

		return $this;
	}

	/**
	 * Return the randomize results from items
	 * @return Array
	 */
	function shuffle()
	{
		shuffle($this->items());

		return $this;
	}

	/**
     * Get the items in the collection that are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
	function diff($items = null)
	{
		return new static(array_diff($this->items, $this::iterable($items)));
	}

	/**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
	function diffKeys($items = null)
	{
		return new static(array_diff_key($this->items, $this::iterable($items)));
	}

	/**
	 * Calls the passed function for each element in this composite.
	 * 
	 * @param  callable $callback
	 * @param  boolean  $reverse
	 * @return boolean
	 */
	function each(callable $callback, $reverse = false)
	{
		$items =& $this->items();

		$_items = $reverse ? array_reverse($items) : $items;

		foreach ($_items as $key => $item) 
		{
			$result = call_user_func_array($callback, array($item, $key, $_items, $this));
			
			if ($result === false) return false;
		}
		return true;
	}

	/**
	 * Apply an invoker function to all value.
	 * Each return value will be replaced for the old.
	 * 
	 * @param  callable $callback
	 * @param  boolean  $reverse
	 * @return boolean
	 */
	function invoke(callable $callback, $reverse = false)
	{
		$items =& $this->items();

		$_items = $reverse ? array_reverse($items) : $items;

		foreach ($_items as $key => $item) 
		{
			$result = call_user_func_array($callback, array($item, $key, $_items));

			$this->set($key, $result);
		}
		return $this;
	}

	/**
	 * Check each callback must be true.
	 * 
	 * @param  callable $callback
	 * @return Boolean
	 */
	function every(callable $callback)
	{
		$items =& $this->items();

		foreach ($items as $key => $item) 
		{
			if (call_user_func_array($callback, array($item, $key, $items, $this)) === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * [some description]
	 * 
	 * @param  callable $callback
	 * @return Boolean
	 */
	function some(callable $callback)
	{
		$items =& $this->items();

		foreach ($items as $key => $item) 
		{
			if (call_user_func_array($callback, array($item, $key, $items, $this)) === true)
			{
				return true;
			}
		}
		return false;
	}

	/**
     * Chunk the underlying collection array.
     *
     * @param  int   $size
     * @return static
     */
    public function chunk($size)
    {
        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Flip the items in the collection.
     *
     * @return static
     */
    public function flip($apply = false)
    {
    	$flipped = array_flip($this->items());

        if ($apply)
        {
        	$this->items = $flipped;
        	return $this;
        }
        return new static($flipped);
    }

	/**
	 * Creating filter data
	 * 		
	 * @param  callable  $filter Filter function, set `$iteration->stop = true` to stop the iteration 
	 * @param  boolean 	$strict
	 * @return \Roulette\Collection new Collection of filtered items
	 */
	function filter($filter, $strict = false)
	{
		$items =& $this->items();
		
		# if has no filter, return the current data state
		if (empty($filter)) return new $this($items); 

		# filtering
		$collection = new $this();

		if (is_callable($filter))
		{
			$iteration = (object) array('stop'=>false);
			
			foreach ($items as $key => $item)
			{
				$result = call_user_func_array($filter, array($item, $key, $items, $this, $iteration));

				if ($result === true)
				{
					$collection->set($key, $item);
				}

				if ($iteration->stop) break;
			}
		}

		# check each attribute of array|object are match with filter
		# and only applied on array
		else if (is_array($filter))
		{
			foreach ($items as $key => $item)
			{
            	if ( ! is_array($item) ) continue;
            	
				$result = true;
				foreach ($filter as $fKey => $fValue) 
				{
					$valid = array_key_exists($fKey, $_item) and $_item[$fKey] == $fValue;
				    
				    if ( ! $valid )
				    {
				        $result == false;
				        break;
				    }
				}
				if ($result === true)
				{
					$collection->set($key, $item);
				}
			}
		}

		return $collection;
	}

	/**
	 * Inverse of filter, will remove filtered item from collection
	 * 
	 * @param  callable $condition
	 * @return Array
	 */
	function reject($condition = null)
	{
		$rejected = array();
		$items =& $this->items();

		if(is_string($condition))
		{
			$condition = array($condition);
		}

		if (!is_callable($condition))
		{
			foreach ($items as $key => $item)
			{
				if (call_user_func_array($condition, array($key, $item)) === true)
				{
					$rejected[$key] = $item;
					unset($items[$key]);
				}
			}
		}
		elseif (is_array($condition))
		{
			foreach ($items as $key => $item)
			{
				if (in_array($key, $condition))
				{
					$rejected[$key] = $item;
					unset($items[$key]);
				}
			}
		}
		return $rejected;
	}

	/**
	 * [implode description]
	 * @param  [type] $glue [description]
	 * @return [type]       [description]
	 */
	function implode($glue)
	{
		return implode($glue, $this->items());
	}

	function intersect($items = null)
	{
		return new static(array_intersect($this->items, $this->iterable($items)));
	}

	function intersectKey($items = null)
	{
		return new static(array_intersect($this->items, $this->iterable($items)));
	}

	function pipe(callable $callback = null)
	{
		if(is_callable($callback))
		{
			call_user_func_array($callback, array($this));
		}
		return $this;
	}

    /**
     * Get the values of a given key.
     *
     * @param  string  $value
     * @param  string|null  $key
     * @return static
     */
    function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

	function reverse()
	{
		return new static(array_reverse($this->items, true));
	}

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param  mixed  $value
     * @param  bool   $strict
     * @return mixed
     */
    
    /**
     * Search the collection for a given value and return the corresponding key if successful.
     * @param  mixed  $value  
     * @param  boolean $strict 
     * @return mixed          
     */
    function search($value, $strict = false)
    {

    }


	/**
     * Get one or more items randomly from the collection.
     *
     * @param  int  $amount
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($amount = 1)
    {
        if ($amount > ($count = $this->count())) {
            throw new InvalidArgumentException("You requested {$amount} items, but there are only {$count} items in the collection");
        }

        $keys = array_rand($this->items, $amount);

        if ($amount == 1) {
            return $this->items[$keys];
        }

        return new static(array_intersect_key($this->items, array_flip($keys)));
    }

	/**
	 * Remove match item from collection.
	 * 
	 * @param  String  $item   Set to `true` to compare item with `===` operator instead of `==`, default is `false`
	 * @param  boolean $strict [description]
	 * @return Array
	 */
	function remove($item, $strict = false)
	{
		$removed = $this->removeIf($item, $strict, true);

		if (count($removed))
		{
			$removedKey = array_keys($removed);
			return $removedKey[0];
		}else{
			return null;
		}
	}

	/**
	 * Deleting records based parameter if
	 * 
	 * @param  array   $item
	 * @param  boolean $strict
	 * @param  boolean $firstFound
	 * @return array
	 */
	function removeIf($item, $strict = false, $firstFound = false)
	{
		$removed = array();
		$items =& $this->items();

		foreach ($items as $key => $value) 
		{
			$passed = false;
			if ($strict)
			{
				$passed = ($item === $value);
			}
			else
			{
				$passed = ($item == $value);
			}

			if ($passed)
			{
				$removed[$key] = $items[$key];
				unset($items[$key]);

				if ($firstFound) break;
			}
		}
		return $removed;	
	}

	/**
	 * Delete records if the record is not included in the if
	 * 
	 * @param  array   $item 
	 * @param  boolean $strict
	 * @return array
	 */
	function removeIfNot($item, $strict = false)
	{
		$removed = array();
		$items =& $this->items();

		foreach ($items as $key => $value)
		{
			$passed = false;
			if ($strict)
			{
				$passed = ($item !== $value);
			}
			else
			{
				$passed = ($item != $value);
			}

			if ($passed)
			{
				$removed[$key] = $this->items[$key];
				unset($this->items[$key]);
			}
		}
		return $removed;	
	}

	/**
	 * Delete records from the conditions on the record
	 * 
	 * @param  array $key
	 * @return array
	 */
	function removeOn($key = null)
	{
		$removedValue = null;
		$items =& $this->items();

		if (array_key_exists($key, $items))
		{
			$removedValue = $items[$key];
			unset($items[$key]);
		}
		return $removedValue;
	}

	function removeKey()
	{
		return call_user_func_array(array($this, 'removeOn'), func_get_args());
	}

	/**
	 * Deleting records based on conditions in
	 * 
	 * @param  array   $condition
	 * @param  boolean $strict
	 * @return array
	 */
	function removeIn($condition = array(), $strict = false)
	{
		if (!is_array($condition)) $condition = array($condition);

		$removed = array();
		$items =& $this->items();

		foreach ($items as $key => $item) 
		{
			if (in_array($item, $condition))
			{
				$removed[$key] = $item;
				unset($items[$key]);
			}
		}

		return $removed;
	}

	/**
	 * [removeEx description]
	 * 
	 * @param  array   $condition it's contents will be inserted condition
	 * @param  boolean $strict
	 * @return array
	 */
	function removeEx($condition = array(), $strict = false)
	{
		if ((!is_array($condition)) or empty($condition))
		{
			$removed = $this->getAll();
			$this->reset();
			return $removed;
		}

		$removed = array();
		$items =& $this->items();

		foreach ($items as $key => $item) 
		{
			if (!in_array($item, $condition))
			{
				$removed[$key] = $item;
				unset($items[$key]);
			}
		}
		return $removed;
	}

	/**
	 * Delete a record with the parameter by
	 * 
	 * @param  callable $condition it's contents will be inserted condition
	 * @return array
	 */
	function removeBy($condition = array(), $strict = false)
	{
		if (!is_array($condition)) $condition = array($condition);

		$removed = array();
		$items =& $this->items();

		foreach ($items as $key => $item) 
		{
			if (in_array($item, $condition))
			{
				$removed[$key] = $item;
				unset($items[$key]);
			}
		}

		return $removed;
	}
	// function removeBy(callable $condition)
	// {
	// 	$removed = array();

	// 	if (!is_callable($condition))
	// 	{	
	// 		return $removed;
	// 	}

	// 	$iteration = (object) array('stop' => false);
	// 	$items = $this->items();

	// 	foreach ($items as $key => $item) 
	// 	{
	// 		$callback = call_user_func_array($condition, array($item, $key, $this, $iteration));
	// 		if ($callback === true)
	// 		{
	// 			$removed[$key] = $item;
	// 			unset($items[$key]);
	// 		}
	// 		if ($iteration->stop === true) break;
	// 	}
	// 	return $removed;
	// }

	/**
	 * [remap description]
	 * 
	 * @param  array  $map A set of key map
	 * @return array
	 */
	function remap(array $map)
	{
		$items =& $this->items();

		$mappedItems = array();
		
		foreach ($items as $key => $value) 
		{
			if (array_key_exists($key, $map) )
			{
				$mappedItems[$map[$key]] = $value;
			}
			else
			{
				$mappedItems[$key] = $value;
			}
		}

		$this->items = $mappedItems;
		return $this;
	}

	///////////////////////
	// IteratorAggregate //
	///////////////////////
	
	/**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    ///////////////
    // Countable //
    ///////////////
    
    public function count()
    {
    	return count($this->items);
    }

    //////////////////////
    // JsonSerializable //
    //////////////////////
    
    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->items);
    }
}