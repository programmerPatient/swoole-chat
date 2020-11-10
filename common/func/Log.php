<?php
namespace app\common\Func;

define("APP_PATH",str_replace("\\","/",dirname(dirname(__DIR__))));

/**
 * 日志管理
 * Class Log
 * @package common
 */
class Log
{
    /**
     * 日志路径
     * @var string
     */
    static $logPath = APP_PATH."/Log/";


    public static function write($type,$message,$info = array())
    {
        $filename = self::$logPath.date('Y-m-d',time()).'.log';
        $in = "";
        if(!empty($info)){
            $in .= "\r\ninfo:\r\n{";
            foreach ($info as $key => $value){
                $in .= $key.":".$value."\r\n";
            }
            $in .= "}";
        }
        $time = date('H:i:s',time());
        $str = "[$type]:[$time]".$message.$in."\r\n";
        file_put_contents($filename,$str,FILE_APPEND);
    }
}