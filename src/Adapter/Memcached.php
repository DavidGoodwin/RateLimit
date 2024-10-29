<?php

namespace PalePurple\RateLimit\Adapter;

class Memcached extends \PalePurple\RateLimit\Adapter
{

    protected \Memcached $memcached;


    public function __construct(\Memcached $memcached,)
    {
        $this->memcached = $memcached;
    }

    public function set(string $key, float $value, int $ttl): bool
    {
        return $this->memcached->set($key, $value, $ttl);
    }

    public function get(string $key): float
    {
        $ret = $this->memcached->get($key);
        
        // it's possible for there to be a race condition between the caller using exists() and then calling get().
        // when this happens, we'll be casting false to a float.
        return (float)$ret;
    }

    public function exists(string $key): bool
    {
        $ret = $this->memcached->get($key);

        // why not
        // $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND ??

        return $ret !== false;
    }

    public function del(string $key): bool
    {
        return $this->memcached->delete($key);
    }
}
