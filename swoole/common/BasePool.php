<?php
namespace app\swoole\common;

use Swoole\ConnectionPool;
use app\common\database\redis\RedisDb;

/**
 * 基础连接池
 * Class BasePool
 * @package swoole\common
 */
class BasePool
{
    /**
     * 当前连接池的名称
     * @var string
     */
    public $type = '';

    /**
     * 最大连接池大小
     * @var int
     */
    public $maxNum=0;

    /**
     * 最小连接池大小
     * @var int
     */
    public $minNum=0;

    /**
     * 连接池容器
     * @var array
     */
    public $pool=[];

    /**
     * 连接池配置信息
     * @var array
     */
    public $config = array();


    public static $instance = null;


    private function __construct($config=null)
    {
        if(!empty($config)){
            $this->config = $config;
        }
    }


    public static function getInstance($type,$config=null)
    {
        if(self::$instance == null){
            $pool = new static($config);
            $pool->type = $type;
            $pool->init();
            self::$instance = $pool;
        }
        return self::$instance;
    }

    public function init()
    {
        $this->maxNum = $this->config['max_num'];
        $this->minNum = $this->config['min_num'];
        for($i = 0; $i < $this->maxNum; $i++){
            switch($this->type){
                case 'redis':
                    $redis = new RedisDb();
                    $this->push($redis);
                    break;
                default:
                    break;
            }
        }
    }



    public function getNum()
    {
        return count($this->pool);
    }


    public function push($value)
    {
        if($this->maxNum > $this->getNum()){
            $this->pool[] = $value;
        }else{
            echo "连接池已经满了";
        }
    }


    public function getAll()
    {
        return $this->pool;
    }





}