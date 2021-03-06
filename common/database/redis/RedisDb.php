<?php
namespace app\common\database\redis;

use app\common\Func\Log;
use app\common\Func\Config;

class RedisDb extends \Redis
{



    private $config = array();

    private $actions = array();


    /**
     * 构造函数
     * RedisDb constructor.
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct();
        if(empty($config)){
            $configs = new Config();
            $this->config = $configs->getConfig('database','redis');
        }else{
            $this->config = $config;
        }
        $this->_connect();
    }


    /**
     * redis连接
     */
    public function _connect()
    {
        $ret = $this->connect($this->config['host'],$this->config['port']);
        if($this->config['password']){
            $this->auth($this->config['password']); //密码验证
        }
        if($ret === false){
            throw new \RuntimeException(sprintf('Failed to connect Redis server: %s', $this->getLastError()));
        }
        if($this->config['password']){
            //检测密码是否正确
            $this->auth($this->config['password']);
        }
        if($this->config['database']){
            //选择数据库数据库索引号 index 用数字值指定，以 0 作为起始索引值。
            $this->select($this->config['database']);
        }

    }


    /**
     * 添加事件
     * @param $func
     * @param $args
     */
    public function addAction($func,$args)
    {
        $this->actions[] = array(
            'func' => $func,
            'args' => $args
        );
    }


    /**
     * 事务批量处理
     */
    public function commitMulti()
    {
        if(empty($this->actions)){
            return false;
        }
        //标记事务块的开始
        $this->multi(\Redis::PIPELINE);
        foreach($this->actions as $action)
        {
            $func = $action['func'];
            $args = $action['args'];
            call_user_func_array(array('parent',$func),$args);
        }
        $this->clear();
        //事务提交
        return $this->exec();
    }


    /************************************* hash表操作开始 **********************************************************/

    /**
     * 返回hash表的key中所有的值
     * @param string $key
     * @param false $encode
     * @return array
     */
    public function hGetAll($key, $encode = false)
    {
        $list = parent::hGetAll($key); // TODO: Change the autogenerated stub
        if(empty($list)){
            return array();
        }
        //判断是否需要json解析
        if($encode){
            foreach($list as &$item){
                $item = json_decode($item,true);
            }
        }
        return $list;
    }


    /**
     * 获取hash表中指定的key的指定field属性
     * @param string $key
     * @param string $hashKey
     * @param false $encode
     * @return string|void
     */
    public function hGet($key, $hashKey,$encode = false)
    {
        $list = parent::hGet($key, $hashKey); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }
        if($encode){
            $list = json_decode($list,true);
        }
        return $list;
    }


    /**
     * 获取hash表中指定的key的指定field数组的批量属性
     * @param string $key
     * @param array $hashKeys
     * @return array|void
     */
    public function hMGet($key, $hashKeys,$encode = false)
    {
        $list = parent::hMGet($key, $hashKeys); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }
        if($encode){
            foreach ($list as &$item){
                $item = json_decode($item,true);
            }
        }
        return $list;
    }

    /**
     * 设置hash表的指定的key的值
     * @param string $key
     * @param string $hashKey
     * @param string $value
     * @param false $encode
     * @return bool|int|void
     */
    public function hSet($key, $hashKey, $value,$encode = false)
    {
        if($encode){
            $value = json_encode($value);
        }
        $this->addAction(__FUNCTION__,array($key,$hashKey,$value));
    }


    /**
     * 向hash表的key的$hashKey写入元素，当且仅当该字段不存在时
     * @param string $key
     * @param string $hashKey
     * @param string $value
     * @return bool|void
     */
    public function hSetNx($key, $hashKey, $value,$encode = false)
    {
        if($encode){
            $value = json_encode($value);
        }
        $this->addAction(__FUNCTION__,array($key,$hashKey,$value));
    }


    /**
     *向名称为key的hash表的hashkeys写入元素
     * @param string $key
     * @param array $hashKeys
     * @return bool|void
     */
    public function hMSet($key, array $hashKeys,$encode = false)
    {
        if($encode){
            foreach ($hashKeys as &$item){
                $item = json_encode($item);
            }
        }

        $this->addAction(__FUNCTION__,array($key,$hashKeys));
    }

    /**
     * 删除名称为key的hash表的指定键的值
     * @param string $key
     * @param string $hashKey1
     * @param mixed ...$otherHashKeys
     * @return bool|int|void
     */
    public function hDel($key, $hashKey1, ...$otherHashKeys)
    {
        $args = array_merge($otherHashKeys,array($hashKey1));
        $this->addAction(__FUNCTION__,$args);
    }

    /**
     * 向名称为key的hash中的字段值加上指定增量值。
     * @param string $key
     * @param string $hashKey
     * @param int $value
     * @return int|void
     */
    public function hIncrBy($key, $hashKey, $value)
    {
        $this->addAction(__FUNCTION__,array_merge($key,$hashKey,$value));
    }

    /********************************************** hash表操作结束 ************************************/



    /********************************************** list表操作开始 *************************************/

    /**
     * 向名称为key的list添加一个或多个头元素
     * @param string $key
     * @param array|mixed|string $value
     * @param false $encode
     * @return bool|int|void
     */
    public function lPush($key, $value,$encode = false)
    {
        if($encode){
            if(is_string($value)){
                $value = json_encode($value);
            }
            if(is_array($value)){
                foreach ($value as &$item){
                    $item = json_encode($item);
                }
            }
        }
        $this->addAction(__FUNCTION__,array($key,$value));
    }

    /**
     * 向名称为key的list添加一个或多个尾元素
     * @param string $key
     * @param array|mixed|string $value
     * @param false $encode
     * @return bool|int|void
     */
    public function rPush($key, $value,$encode = false)
    {
        if($encode){
            if(is_string($value)){
                $value = json_encode($value);
            }
            if(is_array($value)){
                foreach ($value as &$item){
                    $item = json_encode($item);
                }
            }
        }
        $this->addAction(__FUNCTION__,array($key,$value));
    }

    /**
     * 从名称为key的list弹出头元素
     * @param string $key
     * @param false $encode
     * @return bool|mixed
     */
    public function lPop($key,$encode = false)
    {
        $val = parent::lPop($key);
        if($encode){
            $val = json_decode($val,true);
        }
        return $val;
    }

    /**
     * 从名称为key的list弹出尾元素
     * @param string $key
     * @param false $encode
     * @return bool|mixed
     */
    public function rPop($key,$encode = false)
    {
        $val = parent::rPop($key);
        if($encode){
            $val = json_decode($val,true);
        }
        return $val;
    }


    /**
     * 返回列表KEY中指定区间内的元素，区间以偏移量start和stop决定
     * @param string $key
     * @param int $start
     * @param int $end
     * @param false $encode
     * @return array|void
     */
    public function lRange($key, $start, $end,$encode = false)
    {
        $list = parent::lRange($key, $start, $end); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }

        if($encode){
            foreach ($list as &$item){
                $item = json_decode($item,true);
            }
        }
        return $list;
    }

    /**
     * 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。
     * @param string $key
     * @param int $start
     * @param int $stop
     * @return array|bool|void
     */
    public function lTrim($key, $start, $stop)
    {
        $this->addAction(__FUNCTION__,array($key,$start,$stop));
    }

    /********************************************** list表操作结束 *************************************/


    /********************************************** set集合操作开始 *************************************/

    /**
     * 添加一个或多个合集元素
     * @param string $key
     * @return bool|int|void
     */
    public function sAdd($key, $value1)
    {
        $args[] = $key;
        $args[] = $value1;
        $this->addAction(__FUNCTION__,$args); // TODO: Change the autogenerated stub
    }


    /**
     * 删除一个或多个集合元素
     * @param string $key
     * @param mixed ...$member1
     * @return int|void
     */
    public function sRem($key, $member1,...$member2)
    {
        $args[] = $key;
        if(count($member1) > 1){
            foreach ($member1 as $item) {
                $args[] = $item;
            }
        }else{
            $args[] = $member1;
        }
        $this->addAction(__FUNCTION__,$args); // TODO: Change the autogenerated stub
    }


    /**
     * 获取集合所有的元素
     * @param string $key
     * @return array|void
     */
    public function sMembers($key)
    {
        return parent::sMembers($key); // TODO: Change the autogenerated stub
    }

    /********************************************** set集合操作结束 *************************************/


    /********************************************** 有序set集合操作开始 *************************************/

    /**
     * 向名称为key的zset中添加成员
     * @param string $key
     * @param array|float $score
     * @param float|mixed|string $member
     * @param false $encode
     * @return int|void
     */
    public function zAdd($key, $score, $member, $encode = false)
    {
        if($encode){
            $member = json_encode($member);
        }
        $this->addAction(__FUNCTION__,array($key, $score, $member));
    }

    /**
     * 返回有序集中，指定区间内的成员
     * @param string $key
     * @param int $start
     * @param int $end
     * @param null $withscore
     * @return array|void
     */
    public function zRevRange($key, $start, $end, $withscore = null,$decode = false)
    {
        $list = parent::zRevRange($key, $start, $end, $withscore); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }
        if($decode){
            $list = $this->arrByDecode($list);
        }
        return $list;
    }


    /**
     *  返回有序集合中指定分数区间的成员列表。有序集成员按分数值递增(从小到大)次序排列。
     * @param string $key
     * @param int $start
     * @param int $end
     * @param array $options
     * @return array|void
     */
    public function zRangeByScore($key, $start, $end, array $options = array(),$decode = false)
    {
        $list = parent::zRangeByScore($key, $start, $end, $options); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }
        if($decode){
            $list = $this->arrByDecode($list);
        }
        return $list;
    }

    /**
     * 返回有序集中指定分数区间内的所有的成员。有序集成员按分数值递减(从大到小)的次序排列。
     * @param string $key
     * @param int $start
     * @param int $end
     * @param array $options
     * @return array|void
     */
    public function zRevRangeByScore($key, $start, $end, array $options = array(),$decode = false)
    {
        $list = parent::zRevRangeByScore($key, $start, $end, $options); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }
        if($decode){
            $list = $this->arrByDecode($list);
        }
        return $list;
    }

    /**
     * 指定区间内的成员。其中成员的位置按分数值递增(从小到大)来排序。
     * @param string $key
     * @param int $start
     * @param int $end
     * @param null $withscores
     * @return array|void
     */
    public function zRange($key, $start, $end, $withscores = null,$decode = false)
    {
        $lsit = parent::zRange($key, $start, $end, $withscores); // TODO: Change the autogenerated stub
        if(empty($list)){
            return null;
        }
        if($decode){
            $list = $this->arrByDecode($list);
        }
        return $list;
    }


    /**
     * 命令用于移除有序集中，指定分数（score）区间内的所有成员
     * @param string $key
     * @param float|string $start
     * @param float|string $end
     * @return int|void
     */
    public function zRemRangeByScore($key, $start, $end)
    {
        $this->addAction(__FUNCTION__,array($key, $start, $end));
    }


    /**
     * 用于移除有序集中，指定排名(rank)区间内的所有成员。
     * @param string $key
     * @param int $start
     * @param int $end
     * @return int|void
     */
    public function zRemRangeByRank($key, $start, $end)
    {
        $this->addAction(__FUNCTION__,array($key, $start, $end)); // TODO: Change the autogenerated stub
    }

    /**
     * 命令用于移除有序集中的一个或多个成员，不存在的成员将被忽略。
     * @param string $key
     * @param mixed|string $member1
     * @param mixed ...$otherMembers
     * @return int|void
     */
    public function zRem($key, $member1, ...$otherMembers)
    {
        $args[] = $key;
        $args[] = $member1;
        if(!empty($otherMembers)){
            foreach ($otherMembers as $otherMember) {
                $args[] = $otherMember;
            }
        }
        $this->addAction(__FUNCTION__,$args); // TODO: Change the autogenerated stub
    }

    /**
     * 对有序集合中指定成员的分数加上增量 increment
     * @param string $key
     * @param float $value
     * @param string $member
     * @return float|void
     */
    public function zIncrBy($key, $value, $member)
    {
        $this->addAction(__FUNCTION__,array($key, $value, $member)); // TODO: Change the autogenerated stub
    }

    /********************************************** 有序set集合操作结束 *************************************/

    /********************************************** 字符串操作开始 *****************************************/

    /**
     *  命令将 key 中储存的数字加1。
     * @param string $key
     * @param int $value
     * @return int|void
     */
    public function incr($key)
    {
        $this->addAction(__FUNCTION__,array($key)); // TODO: Change the autogenerated stub
    }

    /**
     *  命令将 key 中储存的数字加上指定的增量值。
     * @param string $key
     * @param int $value
     * @return int|void
     */
    public function incrBy($key, $value)
    {
        $this->addAction(__FUNCTION__,array($key, $value)); // TODO: Change the autogenerated stub
    }


    /**
     * 将 key 中储存的数字值减一
     * @param string $key
     * @return int|void
     */
    public function decr($key)
    {
        $this->addAction(__FUNCTION__,array($key)); // TODO: Change the autogenerated stub
    }

    /**
     * 将 key 中储存的数字值去指定值
     * @param string $key
     * @return int|void
     */
    public function decrBy($key,$value)
    {
        $this->addAction(__FUNCTION__,array($key,$value)); // TODO: Change the autogenerated stub
    }


    /**
     * 设置key值(string)
     * @param string $key
     * @param mixed|string $value
     * @param null $timeout
     * @return bool|void
     */
    public function set($key, $value, $encode = false, $timeout = null)
    {
        if($encode){
            $value = json_encode($value);
        }
        $this->addAction(__FUNCTION__,array($key, $value, $timeout)); // TODO: Change the autogenerated stub
    }


    /**
     * 批量设置key值(string)
     * @param array $array
     * @return bool|void
     */
    public function mset(array $array,$encode = false)
    {
        if($encode){
            $array = $this->arrToEncode($array);
        }
        $this->addAction(__FUNCTION__,array($array)); // TODO: Change the autogenerated stub
    }

    /**
     *命令为指定的 key 设置值及其过期时间。如果 key 已经存在， SETEX 命令将会替换旧的值
     * @param string $key
     * @param int $ttl 过期时间
     * @param mixed|string $value
     * @param false $encode
     * @return bool|void
     */
    public function setex($key, $ttl, $value,$encode = false)
    {
        if($encode){
            $value = json_encode($value);
        }
        $this->addAction(__FUNCTION__,array($key, $ttl, $value)); // TODO: Change the autogenerated stub
    }


    /**
     * 命令用于获取指定 key 的值。如果 key 不存在，返回 nil 。如果key 储存的值不是字符串类型，返回一个错误
     * @param string $key
     * @return bool|mixed|string|void
     */
    public function get($key,$decode = false)
    {
        $value = parent::get($key); // TODO: Change the autogenerated stub
        if($decode){
            $value = json_decode($value);
        }
        return $value;
    }

    /**
     * 批量获取
     * @see Redis::mGet
     * @param $keys
     * @param $decode
     */
    public function mGet(array $keys, $decode = false) {
        $list = parent::mGet($keys);
        if (empty($list)) {
            return null;
        }
        if ($decode) {
            $list = $this->arrByDecode($list);
        }
        return $list;
    }

    /********************************************** 字符串操作结束 *****************************************/

    /**
     * 删除一个或多个key(string)
     * @see Redis::delete
     * @param $keys
     */
    public function del($key1, ...$otherKeys)
    {
        $args[] = $key1;
        if(count($otherKeys) > 1){
            foreach ($otherKeys as $otherKey){
                $args[] = $otherKey;
            }
        }else{
            $args[] = $otherKeys;
        }
        $this->addAction(__FUNCTION__,$args); // TODO: Change the autogenerated stub
    }

    /**
     * 命令用于设置 key 的过期时间，key 过期后将不再可用。单位以秒计
     * @see Redis::expire
     * @param $key
     * @param $time
     */
    public function expire($key, $time = 60) {
        $this->addActions(__FUNCTION__, array($key, $time));
    }


    /**
     * 将key1中的成员移动到key2中去
     * @see Redis::sMove
     * @param $key1
     * @param $key2
     * @param $member
     */
    public function sMove($key1, $key2, $member) {
        $this->addActions(__FUNCTION__, array($key1, $key2, $member));
    }

    /**
     * 加锁
     * @param string $key
     * @param int $time
     */
    public function lock($key, $time) {
        $res = parent::setnx($key, 1);
        if ($res) {
            parent::expire($key, $time);
        }
        return $res;
    }

    /**
     * 解锁
     * @param type $key
     */
    public function unlock($key) {
        parent::del($key);
    }



    /**
     * 数组json格式化
     * @param $arr
     */
    public function arrToEncode($list)
    {
        foreach ($list as &$item){
            $item = json_encode($item);
        }
        return $list;
    }

    /**
     * 数组json解析
     * @param $arr
     */
    public function arrByDecode($list)
    {
        foreach ($list as &$item){
            $item = json_decode($item);
        }
        return $list;
    }

    /**
     * 清除
     */
    public function clear()
    {
        $this->actions = array();
    }

    /**
     * 获取redis连接的配置信息
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 检查redis的连接状态
     * @param $redis
     */
    public static function checkPing($redis)
    {
        try{
            if($redis->ping() == '+PONG'){
                return true;
            }
            return false;
        }catch (\Exception $e){
            Log::write("ERROR",$e->getMessage(),[
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }



}