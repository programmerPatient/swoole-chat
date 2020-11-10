<?php
namespace app\swoole\common;


use app\common\Func\Config;
use Swoole\Server;

class BaseServer
{

    //redis连接池
    public $redisPool = array();
    //swoole服务器实例
    public $server;

    //服务名称
    public $name = '';

    //服务ip地址
    public $host = '';

    //服务端口
    public $port = 0;

    //服務類型
    public $type = '';

    //连接设置
    public $serverSet = null;

    //内部监听服务器
    public $innerServer;

    //logic
    public $logic;

    //服务的workId
    public $workId = 0;

    public function __construct()
    {
        $nowClassName = strtolower(basename(str_replace("\\","/",get_called_class())));
        $this->type = substr($nowClassName,0,strrpos($nowClassName,'server'));
        $conf = new Config();
        $config = $conf->getConfig($this->type,'connection');
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->serverSet = $config['set'];
        $this->initServer();
    }

    public function initServer()
    {
        $this->server = new \Swoole\Server($this->host,$this->port);

    }

    public function onStart(\Swoole\Server $server)
    {
        $this->logPrint(LOG_INFO,"服务开启");
    }

    /**
     * manager服务启动
     *
     * @param Server $server
     * @return void
     */
    public function onManagerStart(\Swoole\Server $server)
    {
        swoole_set_process_name("swoole $this->type manager");
        $this->logPrint(LOG_INFO, sprintf("master start (listening on %s:%d)", $server->host, $server->port));
    }

    public function onWorkerStart(\Swoole\Server $server,int $workerId)
    {
        $this->logPrint(LOG_INFO,"进程号为：{$workerId}的进程开启");
    }


    public function onWorkerStop(\Swoole\Server $server,int $workerId)
    {
        $this->logPrint(LOG_INFO,"进程号为：{$workerId}的进程关闭了");
    }

    public function onConnect(\Swoole\Server $server,int $fd)
    {
        $this->logPrint(LOG_INFO,"成功连接到fd为：{$fd}的连接");
    }

    public function onClose(\Swoole\Server $server,int $fd,int $reactorId)
    {
        $this->logPrint(LOG_INFO,"连接fd为：{$fd}的连接关闭了");
    }

    public function logPrint($type,$message)
    {
        $time = date('[Y-m-d H:i:s]',microtime(true));
        switch($type){
            case LOG_INFO:
                $str = "\033[36m".'[INFO]'.$time.$message."\033[0m";
                break;
            case LOG_ERR:
                $str = "\033[31m".'[ERROR]'.$time.$message."\033[0m";
                break;
            case LOG_WARNING:
                $str = "\033[33m".'[WARNING]'.$time.$message."\033[0m";
                break;
            default:
                $str = "\033[37m".'[OTHER]'.$time.$message."\033[0m";
                break;
        }
        echo $str."\n";
    }
}