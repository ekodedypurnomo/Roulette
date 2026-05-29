<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Model\Cache;

trait ManagesCache
{
    static protected bool $useCache = true;

    static function isUseCache(): bool
    {
        return !!static::$useCache;
    }

    static function formatCacheId(mixed $id): string
    {
        return get_class().'\\'.static::class.'-'.$id;
    }

    static function storeToCache(mixed $record): string
    {
        if (static::isUseCache() && $record->hasId())
        {
            Cache::store(static::formatCacheId($record->getId()), $record);
        }
        return static::class;
    }

    static function fetchFromCache(mixed $recordId): mixed
    {
        if (!static::isUseCache()) return null;

        if (!(is_string($recordId) || is_numeric($recordId))) return null;

        return Cache::fetch(static::formatCacheId($recordId));
    }
}
