<?php
namespace app\swoole\logic\room;

use app\common\Func\Config;
use app\swoole\client\Proxy;
use app\swoole\common\lib\MessType;
use app\swoole\common\lib\Sender;
use app\swoole\logic\room\Room;

class RoomLogic
{
    public $name='';

    public $server;

    public $workId;

    public $client;

    /**
     * 当前服务的房间数
     * @var array
     */
    public $rooms = [];

    public function __construct($server,$workId)
    {
        $this->server = $server;
        $this->workId = $workId;
        $this->client = new Proxy($this,$workId);
    }


    /**
     * 初始化房间
     * @param $data
     */
    public function initRoom($data)
    {
        $fd = $data['fd'];
        $data = $data['data'];
        $account = $data['account'];
        $roomId = $data['roomId'];
        $this->checkConnect($account,$fd);
        $redis = $this->server->redisPool->get();
        $res = $redis->hGet("room:all",$roomId);
        $nickname = $redis->hGet("user:{$account}","name");
        $this->server->redisPool->release($redis);
        $room = $this->getRoomById($roomId);
        $room->push($account);
        $room->name = $res;
        $roomUser = $room->user_uid;
        $roomUser = $this->getUserInfo($roomUser);
        $this->server->logPrint(LOG_INFO,"当前房间{$room->name}的人数为：".$room->getUserNum());
        /*通知房间内的所有玩家该玩家上线了*/
        $data = Sender::sendAllRoomClient($roomId,[
            'code' => 400,
            'message' => "欢迎{$nickname}进入房间",
            'data' => [
                'roomUser' => $roomUser,
            ],
        ]);
        $data['fd'] = $fd;
        $this->server->toOutClient($data);
    }

    public function getUserInfo($uids)
    {
        $redis = $this->server->redisPool->get();
        if(!is_array($uids)){
            $res =  $redis->hGet("user:{$uids}","name");
        }else{
            $res = [];
            foreach ($uids as $uid){
                $res[$uid] = $redis->hGet("user:{$uid}","name");
            }
            $this->server->redisPool->release($redis);
        }
        return $res;
    }

    /**
     * 获取房间是否已经创建,如果没有创建则新建，并返回房间
     * @param $roomId
     */
    public function getRoomById($roomId)
    {
        if(!array_key_exists($roomId,$this->rooms)){
            $room = new Room();
            $room->id = $roomId;
            $room->name = $roomId;
            $this->rooms[$roomId] = $room;
        }
        $room = $this->rooms[$roomId];
        return $room;
    }

    public function checkConnect($account,$fd)
    {
        $old = $this->getFdByAccount($account);
        if($old !==false && $fd != $old){
            //关闭老的连接
            $this->server->server->close($old);
        }
        $this->server->clientFd->set($account,['fd'=>$fd]);
    }
    public function getFdByAccount($account)
    {
        return $this->server->clientFd->get($account,'fd');
    }

    public function chat($args)
    {
        $fd = $args['fd'];
        $args = $args['data'];
        $message = $args['msg'];
        $toUid = $args['toUid'];
        $roomId = $args['roomId'];
        $uid = $args['account'];
        $room = $this->getRoomById($roomId);

        if($toUid == -1){
            $data = Sender::sendAllRoomClient($roomId,[
                'code' => 400,
                'data' => [
                    "from" =>$this->getUserInfo($uid),
                    "msg" => $message
                ],
            ]);
        }else{
            $data = Sender::sendToClient([$this->getFdByUid($toUid), $fd],[
                'code' => 400,
                'data' => [
                    "from" =>$this->getUserInfo($uid),
                    "msg" => $message
                ],
            ]);
        }
        $data['fd'] = $fd;
        $this->server->toOutClient($data);

    }


    /**
     * 用户下线
     * @param $data
     */
    public function offline($data)
    {
        $data = $data['data'];
        $roomId = $data['roomId'];
        $account = $data['account'];
        var_dump($data);
        $room = $this->getRoomById($roomId);
        $room->pop($account);
        $roomUser = $room->user_uid;
        $roomUser = $this->getUserInfo($roomUser);
        $this->server->logPrint(LOG_INFO,"当前房间{$room->name}的人数为：".$room->getUserNum());
        /*通知房间内的所有玩家该玩家上线了*/
        $data = Sender::sendAllRoomClient($roomId,[
            'code' => 400,
            'message' => "玩家{$data['name']}退出了房间",
            'data' => [
                'roomUser' => $roomUser,
            ],
        ]);
        $this->server->toOutClient($data);

    }

    public function getAllFdsByRoom($roomId)
    {
        $room = $this->getRoomById($roomId);
        $uids = $room->user_uid;
        $fds = [];
        foreach ($uids as $uid){
            $fds[] = $this->getFdByUid($uid);
        }
        return $fds;
    }
    public function getFdByUid($uid)
    {
        return $this->server->clientFd->get($uid,'fd');
    }

    public function getRooms()
    {
        return count($this->rooms);
    }
}