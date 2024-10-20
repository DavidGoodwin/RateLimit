<?php

namespace PalePurple\RateLimit;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date May 16, 2015
 */
abstract class Adapter
{
    /**
     * @return bool
     * @param string $key
     * @param float $value
     * @param int $ttl - seconds after which this entry will expire e.g 50
     */
    abstract public function set(string $key, float $value, int $ttl);

    /**
     * @param string $key
     * @return float - the amount of request allowance left
     */
    abstract public function get(string $key): float;

    abstract public function exists(string $key): bool;

    /**
     * @return bool - true if delete works
     */
    abstract public function del(string $key): bool;
}
