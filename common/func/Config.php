<?php


namespace app\common\Func;


//加载配置文件
use app\common\Func\Log;

class Config
{
    /**
     * 配置文件路径
     * @var null
     */
    public static $configPath = "";

    public static $config = array();

    public function __construct($config = null)
    {
        if(empty($config)){
            $configPath  = str_replace("\\","/",dirname(dirname(__DIR__))).'/swoole/config';
        }else{
            $configPath = $config;
        }
        self::$configPath = $configPath;
    }

    /**
     * 获取一个配置文件指定的键值
     * @param string $name
     * @param $key (多级循环  示例：user.name.key)
     */
    public function getConfig($name,$key="")
    {
        if(empty(self::$config[$name])){
            self::$config[$name] = $this->realTimeGetConf($name);
        }
        if(empty($key)){
            return self::$config[$name];
        }
        $keys = explode(".",$key);
        $value = self::$config[$name];
        foreach ($keys as $k => $v){
            $value = $value[$v];
        }
        return $value;
    }

    /**
     * 获取实时的配置项
     * @param $name
     * @param null $key
     * @return array
     */
    public function realTimeGetConf($name,$key=null)
    {
        $configFile = self::$configPath.'/'.$name.'.php';
        if(!is_file($configFile)){
            //配置文件不存在
            Log::write("ERROR","{$configFile}配置文件不存在");
            exit;
        }
        $res = require $configFile;

        return $res;
    }

}