<?php

namespace PalePurple\RateLimit\Tests;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PalePurple\RateLimit\Adapter;
use PalePurple\RateLimit\RateLimit;
use PHPUnit\Framework\TestCase;
use Stash\Invalidation;
use Stash\Pool;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date May 16, 2015
 */
class RateLimitTest extends TestCase
{
    const NAME = "RateLimitTest";
    const MAX_REQUESTS = 10;
    const PERIOD = 2;

    /**
     * @requires extension apc
     */
    public function testCheckAPC()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped("apc extension not installed");
        }
        if (ini_get('apc.enable_cli') == 0) {
            $this->markTestSkipped("apc.enable_cli != 1; can't change at runtime");
        }

        $adapter = new Adapter\APC();
        $this->check($adapter);
    }

    /**
     * @requires extension apcu
     */
    public function testCheckAPCu()
    {
        if (!extension_loaded('apcu')) {
            $this->markTestSkipped("apcu extension not installed");
        }
        if (ini_get('apc.enable_cli') == 0) {
            $this->markTestSkipped("apc.enable_cli != 1; can't change at runtime");
        }
        $adapter = new Adapter\APCu();
        $this->check($adapter);
    }

    /**
     * @requires extension redis
     */
    public function testCheckRedis()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped("redis extension not installed");
        }

        $redis_host = getenv('REDIS_HOST');

        if ($redis_host === false) {
            $redis_host = 'localhost';
        }

        try {
            $redis = new \Redis();
            $redis->connect($redis_host);
            $redis->flushDB(); // clear redis db
        } catch (\RedisException $e) {
            error_log("Failed to connect to redis? " . $e->getMessage());
            $this->markTestSkipped("Failed to connect to redis? " . $e->getMessage());
        }

        $adapter = new Adapter\Redis($redis);
        $this->check($adapter);
    }

    public function testCheckPredis()
    {
        $redis_host = getenv('REDIS_HOST');

        if ($redis_host === false) {
            $redis_host = 'localhost';
        }

        try {
            $predis = new \Predis\Client(
                [
                    'scheme' => 'tcp',
                    'host' => $redis_host,
                    'port' => 6379,
                    'cluster' => false,
                    'database' => 1
                ]
            );
            $predis->flushdb(); // clear redis db.
            $adapter = new Adapter\Predis($predis);
        } catch (\Predis\Connection\ConnectionException $e) {
            error_log("Failed to connect to (p)redis : " . $e->getMessage());
            $this->markTestSkipped("Could not connect to (p)redis");
        }
        $this->check($adapter);
    }

    public function testCheckStash()
    {
        $stash = new \Stash\Pool(); // ephermeral driver by default
        $stash->clear();
        $adapter = new Adapter\Stash($stash);
        $this->check($adapter);
    }

    public function testCheckMemcached()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped("memcached extension not installed");
        }

        $memcache_host = getenv('MEMCACHE_HOST');
        if ($memcache_host === false) {
            $memcache_host = 'localhost';
        }
        $m = new \Memcached();
        $m->addServer($memcache_host, 11211);
        $adapter = new Adapter\Memcached($m);
        $this->check($adapter);
    }

    public function testCacheItemPool()
    {
        $cache = new Adapter\Psr6CacheItemPool(new ArrayCachePool());
        $this->check($cache);

        $stash = new \Stash\Pool(); // ephemeral driver by default
        $stash->setInvalidationMethod(Invalidation::OLD); // stash needs this to behave
        $cache = new Adapter\Psr6CacheItemPool($stash);
        $this->check($cache);
    }

    private function check(Adapter $adapter)
    {
        $label = phpversion() . '-' . uniqid("label", true); // should stop storage conflicts if tests are running in parallel.
        $rateLimit = $this->getRateLimit($adapter, $label);

        $this->assertEquals(self::MAX_REQUESTS, $rateLimit->getAllowance($label));

        // All should work, but bucket will be empty at the end.
        for ($i = 0; $i < self::MAX_REQUESTS; $i++) {
            // Calling check reduces the counter each time.
            $this->assertEquals(self::MAX_REQUESTS - $i, $rateLimit->getAllowance($label));
            $this->assertTrue($rateLimit->check($label));
        }

        // bucket empty.
        $this->assertFalse($rateLimit->check($label), "Bucket should be empty?" . $rateLimit->getAllowance($label));
        $this->assertEquals(0, $rateLimit->getAllowance($label), "Bucket should be empty");

        //Wait for PERIOD seconds, bucket should refill.
        sleep(self::PERIOD);
        $this->assertEquals(self::MAX_REQUESTS, $rateLimit->getAllowance($label));
        $this->assertTrue($rateLimit->check($label));
    }

    private function getRateLimit(Adapter $adapter, $label)
    {
        return new RateLimit($label, self::MAX_REQUESTS, self::PERIOD, $adapter);
    }
}
