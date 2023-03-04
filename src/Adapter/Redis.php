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
     * @param string $key
     * @param float $value
     * @param int $ttl
     * @return bool
     * @throws \RedisException
     */
    public function set($key, $value, $ttl)
    {
        $ret = $this->redis->set($key, (string)$value, $ttl);
        return $ret == true; /* redis returns true OR \Redis (when in multimode). */
    }

    /**
     * @return float
     * @param string $key
     * @throws \RedisException
     */
    public function get($key)
    {
        $ret = $this->redis->get($key);
        if (is_numeric($ret)) {
            return (float) $ret;
        }
        return (float) 0;
    }

    /**
     * @param string $key
     * @return bool
     * @throws \RedisException
     */
    public function exists($key)
    {
        return $this->redis->exists($key) == true;
    }

    /**
     * @param string $key
     * @return  bool
     * @throws \RedisException
     */
    public function del($key)
    {
        return $this->redis->del($key) > 0;
    }
}
