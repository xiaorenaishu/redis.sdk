<?php

namespace RedisComponent\Operator;

use Illuminate\Support\Facades\Redis;

abstract class Counter
{
    private $key = '';

    protected $connection = null;

    protected $preFix = '';

    protected $inFix = '';

    protected $ttl = 0;

    /**
     * 声明key
     *
     * @param $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $this->getKey($key);
        return $this;
    }

    private function getKey($key)
    {
        return $this->preFix . $this->inFix . $key;
    }

    /**
     * 设置计数key缓存有效期
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
     * 计数+
     *
     * @param $num
     *
     * @return int|mixed
     */
    public function incr($num)
    {
        if (empty($this->key)) {
            return null;
        }

        $redis = Redis::connection($this->connection);

        if ($this->ttl <= 0) { //永久有效
            return $redis->incrby($this->key, $num);
        }

        $script = "local n = redis.call('incrby', KEYS[1], ARGV[1]);if n == tonumber(ARGV[1]) then redis.call('expire', KEYS[1], ARGV[2]) end;return n;";
        return $redis->eval($script, 1, $this->key, $num, $this->ttl);
    }

    /**
     * 计数-（回滚计数+）
     *
     * @param $num
     *
     * @return int
     */
    public function decr($num)
    {
        if (empty($this->key)) {
            return null;
        }

        $redis = Redis::connection($this->connection);
        return $redis->decrby($this->key, $num);
    }

    /**
     * 批量读计数
     *
     * @param $keys
     *
     * @return array
     */
    public function mget(array $keys)
    {
        $validKeys = [];
        foreach ($keys as $key) {
            $validKeys[] = $this->getKey($key);
        }
        $redis = Redis::connection($this->connection);
        $list = $redis->mget($validKeys);
        return array_combine($keys, $list);
    }
}