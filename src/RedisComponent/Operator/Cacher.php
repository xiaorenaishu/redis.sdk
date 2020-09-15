<?php

namespace RedisComponent\Operator;

use Illuminate\Support\Facades\Redis;

abstract class Cacher
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
            return null;
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
            return null;
        }

        $res = Redis::connection($this->connection)->get($this->key);
        return $this->formatRespon($res);
    }

    public function mset(array $keyValue)
    {
        $cacheList = [];
        $redis = Redis::connection($this->connection);
        foreach ($keyValue as $key => $value) {
            $cacheList[$this->getKey($key)] = json_encode($value);
        }

        if ($this->ttl) {
            $pipe = $redis->pipeline();
            foreach ($cacheList as $cacheKey => $data) {
                $pipe->setex($cacheKey, $this->ttl, $data);
            }
            return $pipe->execute();
        } else {
            return $redis->mset($cacheList);
        }
    }

    public function mget(array $keys)
    {
        $validKeys = $resList = [];
        foreach ($keys as $key) {
            $validKeys[] = $this->getKey($key);
        }
        $list = Redis::connection($this->connection)->mget($validKeys);
        $unHitKeys = [];
        foreach ($keys as $index => $key) {
            $item = $this->formatRespon($list[$index]);
            if ($item) {
                $resList[$key] = $item;
            } else {
                $unHitKeys[] = $key;
            }
        }

        return [$resList, $unHitKeys];
    }

    public function del($key)
    {
        if (is_array($key)) {
            $validKeys = [];
            foreach ($key as $itemKey) {
                $validKeys[] = $this->getKey($itemKey);
            }
        } else {
            $validKeys = $this->getKey($key);
        }

        return Redis::connection($this->connection)->del($validKeys);
    }

    private function formatRespon($data)
    {
        return is_null($data) ? $data : json_decode($data, true);
    }
}