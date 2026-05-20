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
    static protected ?Collection $cache = null;

    static protected bool $enabled = true;

    /**
     * [cache description]
     * @return object
     */
    static function cache(): Collection
    {
        return static::$cache = Collection::create(static::$cache);
    }

    static function enable(): string
    {
        static::$enabled = true;
        return static::class;
    }

    static function disable(): void
    {
        static::$enabled = false;
    }

    static function isEnabled(): bool
    {
        return static::$enabled;
    }

    static function isDisabled(): bool
    {
        return !static::isEnabled();
    }

    /**
     * Adding item into stored cache
     *
     * @param object $value object type item
     */
    static function add(mixed $value = null): ?string
    {
        if (static::isDisabled()) return null;

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
    static function store(mixed $key, mixed $value = null): ?string
    {
        if (static::isDisabled()) return null;

        static::cache()->set($key, $value);
        return static::class;
    }

    /**
     * Empty the cached item
     * @return [type] [description]
     */
    static function clear(): string
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
    static function exist(mixed $key = null): ?bool
    {
        if (static::isDisabled()) return null;

        return static::cache()->hasKey($key);
    }

    /**
     * Check existence of `item` in the cached item
     *
     * @param  string $item
     * @return boolean
     */
    static function has(mixed $item = null): ?bool
    {
        if (static::isDisabled()) return null;

        return static::cache()->hasItem($item);
    }

    /**
     * retrieve data in cache
     *
     * @param  string|int $key the key to compare with key cached item
     * @return array      the item with the specified `key`
     */
    static function fetch(mixed $key = null): mixed
    {
        if (static::isDisabled()) return null;

        return static::cache()->get($key);
    }

    /**
     * Get an item in the collection specified by the fetch.
     * @return array [description]
     */
    static function get(mixed $key = null): mixed
    {
        return static::fetch($key);
    }

    /**
     * Delete a cached item specified by `key`
     *
     * @param  string|int $key
     * @return array removed item
     */
    static function remove(mixed $key = null): mixed
    {
        return static::cache()->removeOn($key);
    }
}
