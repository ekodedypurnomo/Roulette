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

use Roulette\Base;
use Roulette\Collection;

/**
 * Cache management for model instance to increase speed of load data 
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Cache extends Base
{
    /**
     * Stored cache
     * @var null
     */
    static protected $cache = null;

    static protected $enabled = true;

    /**
     * [cache description]
     * @return object 
     */
    static function cache()
    {
        return static::$cache = Collection::create(static::$cache);
    }

    static function enable()
    {
        static::$enabled = true;
        return static::class;
    }

    static function disable()
    {
        static::$enabled = false;
    }

    static function isEnabled()
    {
        return static::$enabled;
    }

    static function isDisabled()
    {
        return !static::isEnabled();
    }

    /**
     * Adding item into stored cache
     * 
     * @param object $value object type item
     */
    static function add($value = null)
    {
        if (static::isDisabled()) return;

        $id = spl_object_hash($value);
        static::cache()->set($id, $value);
        
        return $id;
    }
    
    /**
     * adding new item into stored cache by referring the `key` and `value`
     * 
     * @param  string $key   
     * @param  string $value 
     * @return [type]        [description]
     */
    static function store($key, $value = null)
    {
        if (static::isDisabled()) return;

        static::cache()->set($key, $value);
        return static::class;
    }
    
    /**
     * Empty the cached item
     * @return [type] [description]
     */
    static function clear()
    {
        static::cache()->reset();
        return static::class;
    }
    
    /**
     * check whether the cached item have the specified `key`
     * 
     * @param  string|integer $key key must be string or integer
     * @return booelan true if item in the cached have the defined key
     */
    static function exist($key = null)
    {
        if (static::isDisabled()) return;

        return static::cache()->hasKey($key);
    }

    /**
     * Check existence of `item` in the cached item
     * 
     * @param  string $item 
     * @return boolean      
     */
    static function has($item = null)
    {
        if (static::isDisabled()) return;

        return static::cache()->hasItem($item);
    }
    
    /**
     * retrieve data in cache
     * 
     * @param  string|int $key the key to compare with key cached item
     * @return array      the item with the specified `key`
     */
    static function fetch($key = null)
    {
        if (static::isDisabled()) return;

        return static::cache()->get($key);
    }

    /**
     * Get an item in the collection specified by the fetch.
     * @return array [description]
     */
    static function get()
    {
        return forward_static_call_array(array('static','fetch'), func_get_args());
    }

    /**
     * Delete a cached item specified by `key`
     * 
     * @param  string|int $key 
     * @return array removed item
     */
    static function remove($key = null)
    {
        return static::cache()->removeOn($key);
    }
}