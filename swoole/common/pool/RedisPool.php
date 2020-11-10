<?php
namespace app\swoole\common\pool;

use app\common\Func\Config;
use app\swoole\common\BasePool;

/**
 * redis连接池
 * Class RedisPool
 * @package swoole\common\pool\redis
 */
class RedisPool extends BasePool
{
    /**
     * 初始化连接池类
     * @return \app\swoole\common\pool\RedisPool|null
     */
    public static function initPool()
    {
        $config = new Config();
        return self::getInstance('redis',$config->getConfig('database','redis'));
    }


    /**
     * 从redis连接池中获取redis连接
     * @return false|mixed
     */
    public function get()
    {
        if($this->getNum() == 0){
            $this->init();
        }
        reset($this->pool);
        $redis = $this->pool[0];
        array_splice($this->pool,0,1);
        if(!$redis->ping()){
            array_splice($this->pool,0,1);
            $this->get();
        }
        return $redis;
    }

    /**
     * 释放该链接
     * @param $value
     */
    public function release($value)
    {
        $this->push($value);
    }
}