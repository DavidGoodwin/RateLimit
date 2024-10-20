<?php

namespace PalePurple\RateLimit\Adapter;

/**
 * Predis adapter
 */
class Predis extends \PalePurple\RateLimit\Adapter
{
    protected \Predis\ClientInterface $redis;

    public function __construct(\Predis\ClientInterface $client)
    {
        $this->redis = $client;
    }

    public function set(string $key, float $value, int $ttl): bool
    {
        $this->redis->set($key, (string)$value, "ex", $ttl);
        return true;
    }

    public function get(string $key): float
    {
        return (float)$this->redis->get($key);
    }

    public function exists(string $key): bool
    {
        return (bool)$this->redis->exists($key);
    }

    public function del(string $key): bool
    {
        return (bool)$this->redis->del([$key]);
    }
}
