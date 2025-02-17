<?php

namespace PalePurple\RateLimit\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class Psr6CacheItemPool extends \PalePurple\RateLimit\Adapter
{
    private CacheItemPoolInterface $pool;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, float $value, int $ttl): bool
    {
        $item = $this->pool->getItem($key)->expiresAfter($ttl)->set($value);
        return $this->pool->save($item);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): float
    {
        return $this->pool->getItem($key)->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function exists(string $key): bool
    {
        return $this->pool->getItem($key)->isHit();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function del(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }
}
