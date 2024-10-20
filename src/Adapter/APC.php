<?php

namespace PalePurple\RateLimit\Adapter;

use PalePurple\RateLimit\Adapter;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date May 16, 2015
 */
class APC extends Adapter
{
    public function set(string $key, float $value, int $ttl)
    {
        return apc_store($key, $value, $ttl);
    }

    public function get(string $key): float
    {
        return (float) apc_fetch($key);
    }

    public function exists(string $key): bool
    {
        return apc_exists($key);
    }

    public function del(string $key): bool
    {
        return apc_delete($key);
    }
}
