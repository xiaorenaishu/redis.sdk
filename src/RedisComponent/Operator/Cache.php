<?php

namespace RedisComponent\Operator;

use RedisComponent\Exceptions\CacheException;
use Illuminate\Support\Facades\Redis;

abstract class Cache
{
    private $key = null;

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

    public function set($value)
    {
        if (empty($this->key)) {
            throw new CacheException('cache key is empty');
        }

        if ($this->ttl) {
            return Redis::connection($this->connection)
                ->setex($this->key, $this->ttl, json_encode($value));
        }

        return Redis::connection($this->connection)
            ->set($this->key, json_encode($value));
    }

    public function get()
    {
        if (empty($this->key)) {
            throw new CacheException('cache key is empty');
        }

        $res = Redis::connection($this->connection)->get($this->key);
        return $this->formatRespon($res);
    }

    public function mset(array $keyValue)
    {
        $cacheList = [];
        foreach ($keyValue as $key => $value) {
            $cacheList[$this->getKey($key)] = json_encode($value);
        }

        return Redis::connection($this->connection)->mset($cacheList);
    }

    public function mget(array $keys)
    {
        $validKeys = $resList = [];
        foreach ($keys as $key) {
            $validKeys[] = $this->getKey($key);
        }
        $list = Redis::connection($this->connection)->mget($validKeys);
        foreach ($keys as $index => $key) {
            $resList[$key] = $this->formatRespon($list[$index]);
        }

        return $resList;
    }

    private function formatRespon($data)
    {
        return is_null($data) ? $data : json_decode($data, true);
    }
}