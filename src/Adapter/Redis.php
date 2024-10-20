<?php

namespace PalePurple\RateLimit\Adapter;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date May 16, 2015
 */
class Redis extends \PalePurple\RateLimit\Adapter
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @throws \RedisException
     */
    public function set(string $key, float $value, int $ttl): bool
    {
        $ret = $this->redis->set($key, (string)$value, $ttl);
        return $ret == true; /* redis returns true OR \Redis (when in multimode). */
    }

    /**
     * @throws \RedisException
     */
    public function get(string $key): float
    {
        $ret = $this->redis->get($key);
        if (is_numeric($ret)) {
            return (float) $ret;
        }
        return (float) 0;
    }

    /**
     * @throws \RedisException
     */
    public function exists(string $key): bool
    {
        return $this->redis->exists($key) == true;
    }

    /**
     * @throws \RedisException
     */
    public function del(string $key): bool
    {
        $ret = $this->redis->del($key);
        if (is_bool($ret)) {
            return $ret;
        }

        if (is_int($ret)) {
            return (bool) $ret;
        }

        return false;
    }
}
