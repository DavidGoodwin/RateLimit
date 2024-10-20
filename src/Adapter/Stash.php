<?php

namespace PalePurple\RateLimit\Adapter;

use PalePurple\RateLimit\Adapter;
use Stash\Invalidation;

/**
 * This could be changed to just require something implmenting PSR6 - i.e. require a \Cache\CacheItemPoolInterface - but
 * Stash seems to require the 'setInvalidationMethod()' to be called on items....
 */
class Stash extends Adapter
{
    private \Stash\Pool $pool;

    public function __construct(\Stash\Pool $pool)
    {
        $this->pool = $pool;
    }

    public function get(string $key): float
    {
        $item = $this->pool->getItem($key);
        $item->setInvalidationMethod(Invalidation::OLD);

        if ($item->isHit()) {
            return $item->get();
        }
        return (float)0;
    }

    public function set(string $key, float $value, int $ttl): bool
    {
        $item = $this->pool->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        return $item->save();
    }

    public function exists(string $key): bool
    {
        $item = $this->pool->getItem($key);
        $item->setInvalidationMethod(Invalidation::OLD);
        return $item->isHit();
    }

    public function del(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }
}
