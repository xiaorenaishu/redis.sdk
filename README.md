## 前置说明
```text
该组件致力于为快速迭代的业务提供统一的redis组件操作方式，降低业务代码的复杂度与冗余度。
```

##安装组件
```bash
COMPOSER_MEMORY_LIMIT=-1 
composer require xiaorenaishu/redis.sdk:dev-master -vvv
```

## 使用方式

- 分布式锁
```php
/**
 * 声明自定义锁
 */
use RedisComponent\Operator\Locker;

class XxxLocker extends Locker
{
    protected $connection = 'default';
    protected $preFix = CacheConst::PREFIX;
    protected $inFix = CacheConst::INFIX;
    protected $ttl = CacheConst::SEC;
}

/**
 * 锁使用
 */
/** @var XxxLocker $locker */
$locker = app(XxxLocker::class);
try {
    $locker->setKey(key)->setTtl(10)->setUniq(uniqid)->lock();
} catch (Exception $e) {

} finally {
    $locker->unlock();
}
```

- 计数器

```php
/**
 * 声明计数器
 */
use RedisComponent\Operator\Counter;

class XxxCounter extends Counter
{
    protected $connection = 'default';
    protected $preFix = CacheConst::PREFIX;
    protected $inFix = CacheConst::INFIX;
    protected $ttl = CacheConst::SEC;
}

/**
 * 执行计数
 */
/** @var XxxCounter $counter */
$counter = app(XxxCounter::class);
$num = $counter->setKey(id)->incr(1);

/**
 * 针对需要不同ttl的计数场景可动态配置
 */
$num = $counter->setKey(id)->setTtl(3600)->incr(1);

```

- 通用缓存基类

```php
/**
 * 声明自定义锁
 */
use RedisComponent\Operator\Cache;

class XxxCache extends Cache
{
    protected $connection = 'default';
    protected $preFix = CacheConst::PREFIX;
    protected $inFix = CacheConst::INFIX;
    protected $ttl = CacheConst::SEC;
}

/** @var XxxCache $cache */
$cache = app(XxxCache::class);

/**
 * 单key读写缓存
 */
$cache->setKey(key)->set(val);
$cache->setKey(key)->get();

/**
 * 多key读写缓存
 */
$cache->mset(keyValueMap);
$cache->mget(keys);

//todo 更多通用操作
```


