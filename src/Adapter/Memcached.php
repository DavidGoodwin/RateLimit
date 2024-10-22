<?php

namespace PalePurple\RateLimit\Adapter;

class Memcached extends \PalePurple\RateLimit\Adapter
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    public function __construct(\Memcached $memcached)
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
        if (is_numeric($ret)) {
            return (float) $ret;
        }
        throw new \InvalidArgumentException("Unexpected data type from memcache, expected float, got " . gettype($ret));
    }

    public function exists(string $key): bool
    {
        $ret = $this->memcached->get($key);
        return $ret !== false;
    }

    public function del(string $key): bool
    {
        return $this->memcached->delete($key);
    }
}
