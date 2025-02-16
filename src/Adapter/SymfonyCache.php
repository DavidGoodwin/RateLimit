<?php

namespace PalePurple\RateLimit\Adapter;

use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Psr\Cache\InvalidArgumentException;

class SymfonyCache extends \PalePurple\RateLimit\Adapter
{
    private AbstractAdapter $adapter;

    public function __construct(AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }


    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, float $value, int $ttl): bool
    {
        $item = $this->adapter->getItem($key)->expiresAfter($ttl)->set($value);
        return $this->adapter->save($item);
    }

    /**
     * @throws InvalidArgumentException
     * @param string $key
     */
    public function get(string $key): float
    {
        return $this->adapter->getItem($key)->get();
    }

    /**
     * @throws InvalidArgumentException
     * @param string $key
     */
    public function exists(string $key): bool
    {
        return $this->adapter->hasItem($key);
    }

    /**
     * @throws InvalidArgumentException
     * @param string $key
     */
    public function del(string $key): bool
    {
        return $this->adapter->delete($key);
    }
}
