<?php

namespace PalePurple\RateLimit\Adapter;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date June 7, 2016
 */
class APCu extends \PalePurple\RateLimit\Adapter
{
    public function set(string $key, float $value, int $ttl): bool
    {
        return apcu_store($key, $value, $ttl);
    }

    public function get(string $key): float
    {
        return apcu_fetch($key);
    }

    public function exists(string $key): bool
    {
        return apcu_exists($key);
    }

    public function del(string $key): bool
    {
        return apcu_delete($key);
    }
}
