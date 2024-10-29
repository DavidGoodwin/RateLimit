<?php

namespace PalePurple\RateLimit;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date May 16, 2015
 */
class RateLimit
{
    protected string $name;
    protected int $maxRequests;
    protected int $period;

    private Adapter $adapter;

    /**
     * RateLimit constructor.
     * @param string $name - some unique identifying name for the rate limiter
     * @param int $maxRequests how many requests (tokens) in $period before rate limiting kicks in
     * @param int $period seconds
     * @param Adapter $adapter - storage adapter
     */
    public function __construct(string $name, int $maxRequests, int $period, Adapter $adapter)
    {
        $this->name = $name;
        $this->maxRequests = $maxRequests;
        $this->period = $period;
        $this->adapter = $adapter;
    }

    /**
     * Rate Limiting
     * http://stackoverflow.com/a/668327/670662
     * @param string $id - e.g someone's login, ip address or otherwise thing you wish to possibly throttle
     * @param float $use - each call to check uses this many tokens
     * @return boolean - true if you're within your allowance, false if over allowance
     */
    public function check(string $id, float $use = 1.0): bool
    {


        $t_key = $this->keyTime($id);
        $a_key = $this->keyAllow($id);

        if (!$this->adapter->exists($t_key)) {
            // first hit; setup storage; allow.
            $this->adapter->set($t_key, time(), $this->period);
            $this->adapter->set($a_key, ($this->maxRequests - $use), $this->period);
            return true;
        }

        $c_time = time();

        $time_passed = $c_time - $this->adapter->get($t_key);
        $this->adapter->set($t_key, $c_time, $this->period);

        $allowance = $this->adapter->get($a_key);

        $rate = $this->maxRequests / $this->period;

        $allowance += $time_passed * $rate;

        if ($allowance > $this->maxRequests) {
            $allowance = $this->maxRequests; // throttle
        }


        if ($allowance < $use) {
            // need to wait for more 'tokens' to be in the bucket.
            $this->adapter->set($a_key, $allowance, $this->period);
            return false;
        }


        $this->adapter->set($a_key, $allowance - $use, $this->period);
        return true;
    }

    /**
     * @param string $id
     * @return int
     * @deprecated use getAllowance() instead.
     */
    public function getAllow(string $id): int
    {
        return $this->getAllowance($id);
    }


    /**
     * Get allowance left.
     * @return int number of requests that can be made before hitting a limit.
     */
    public function getAllowance(string $id): int
    {
        $this->check($id, 0.0);

        $a_key = $this->keyAllow($id);

        if (!$this->adapter->exists($a_key)) {
            return $this->maxRequests;
        }
        return (int)max(0, floor($this->adapter->get($a_key)));
    }

    /**
     * Purge rate limit record for $id
     */
    public function purge(string $id): void
    {
        $this->adapter->del($this->keyTime($id));
        $this->adapter->del($this->keyAllow($id));
    }

    private function keyTime(string $id): string
    {
        return $this->name . ":" . $id . ":time";
    }

    private function keyAllow(string $id): string
    {
        return $this->name . ":" . $id . ":allow";
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setMaxRequests(int $maxRequests): void
    {
        $this->maxRequests = $maxRequests;
    }

    public function setPeriod(int $period): void
    {
        $this->period = $period;
    }

    public function setAdapter(Adapter $adapter): void
    {
        $this->adapter = $adapter;
    }
}
