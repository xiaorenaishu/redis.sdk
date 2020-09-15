<?php

namespace RedisComponent\Operator;

use Illuminate\Support\Facades\Redis;

abstract class Locker
{
    private $uniqid = '1';

    private $key = '';

    protected $connection = null;

    protected $ttl = 3;

    protected $preFix = '';

    protected $inFix = '';

    /**
     * 声明key
     *
     * @param $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $this->preFix . $this->inFix . $key;
        return $this;
    }

    /**
     * 设置锁芯
     *
     * @param null $uniqid
     *
     * @return $this
     */
    public function setUniq($uniqid = null)
    {
        $this->uniqid = $uniqid ?: uniqid();
        return $this;
    }

    /**
     * 设置key锁有效期
     *
     * @param $ttl
     *
     * @return $this
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * 锁定
     *
     * @return bool
     */
    public function lock()
    {
        if (empty($this->key)) {
            return null;
        }

        return (bool)Redis::connection($this->connection)->set($this->key, $this->uniqid, "EX", $this->ttl, "NX");
    }

    /**
     * 解锁
     *
     * @return bool
     */
    public function unlock()
    {
        if (empty($this->key)) {
            return null;
        }

        $script = "if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]) else return 0 end";
        return (bool)Redis::connection($this->connection)->eval($script, 1, $this->key, $this->uniqid);
    }
}