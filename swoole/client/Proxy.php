<?php
namespace app\swoole\client;

use app\common\Func\Config;
use app\common\Func\Log;
use app\swoole\common\lib\MessType;
use Swoole\Timer;
use app\swoole\common\lib\Sender;

/**
 * 消息转发
 * Class Proxy
 * @package app\swoole\client
 */
class Proxy
{
    //连接的服务器ip地址
    public $servHost = '';
    //连接的服务器port地址
    public $servPort = '';
    //名稱
    public $name = '';

    public $client;

    public $logic;

    //定时器
    public $ticker;

    public function __construct($logic,$workId)
    {
        $conf = new Config();
        $configs = $conf->getConfig('room','innerServer');
        $this->servHost = $configs['host'];
        $this->servPort = $configs['port'];
        $this->name = $this->servHost.":".$this->servPort."-".(int)$workId;
        $this->logic = $logic;
        $this->init();
    }

    public function init()
    {
        $this->client = new \Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
        $this->client->set([]);
        $this->client->on('connect',[$this,'onConnect']);
        $this->client->on('receive',[$this,'onReceive']);
        $this->client->on('close',[$this,'onClose']);
        $this->client->on('error',[$this,'onError']);
        $res = $this->client->connect($this->servHost,$this->servPort);
        if(!$res){
            Log::write("ERROR","连接地址为：".$this->servHost.":".$this->servPort."的服务器失败");
//           echo "连接地址为：".$this->servHost.":".$this->servPort."的服务器失败";
            exit;
        }
    }

    public function onConnect(\Swoole\Client $client)
    {
        echo "连接服务器成功";
        //同步内部客户端信息
        $this->sendMessage(MessType::CLIENT_SYN,[
            'name' => $this->name,
            'roomNum' => $this->logic->getRooms(),
        ]);
        $this->startInit();
    }

    public function onReceive(\Swoole\Client $client,$data)
    {
        $data = json_decode($data,true);
        $type = $data['messType'];
        switch ($type){
            case MessType::INIT_USER_ROOM_INFO:
                $this->logic->initRoom($data);
                break;
            case MessType::CHAT:
                $this->logic->chat($data);
                break;
            case MessType::USER_OFFLINE:
                $this->logic->offline($data);
                break;
        }
    }

    public function onClose(\Swoole\Client $client)
    {
        echo "服务断开连接";
        $this->clear();
    }

    public function onError(\Swoole\Client $client)
    {
        echo "内部出错";
    }

    public function sendMessage($type,$data)
    {
        switch ($type){
            case MessType::CLIENT_SYN:
                $this->client->send(Sender::package($type,$data));
                break;
            default:
                break;
        }
    }


    public function startInit()
    {
        $this->ticker = Timer::tick(1000,function (){
            $this->client->send(Sender::package(MessType::PING,[]));
        });
    }



    public function clear()
    {
        Timer::clear($this->ticker);
    }
}