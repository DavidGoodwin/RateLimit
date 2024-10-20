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
        $ret = $this->realGet($key);
        if (is_float($ret)) {
            return $ret;
        }
        throw new \InvalidArgumentException("Unexpected data type from memcache, expected float, got " . gettype($ret));
    }

    private function realGet(string $key): bool|float
    {
        $ret = $this->memcached->get($key);
        if (is_float($ret) || is_bool($ret)) {
            return $ret;
        }
        throw new \InvalidArgumentException("Unsupported data type from memcache: " . gettype($ret));
    }

    public function exists(string $key): bool
    {
        $val = $this->realGet($key);
        return $val !== false;
    }

    public function del(string $key): bool
    {
        return $this->memcached->delete($key);
    }
}
