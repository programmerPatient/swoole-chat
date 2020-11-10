<?php
namespace app\swoole\logic\room;


/**
 * 房间类，管理房间信息
 * Class Room
 * @package swoole\logic\room
 */
class Room
{
    /**
     * 存放当前房间的用户的fd
     * @var array
     */
    public $user_uid = array();

    /**
     * 当前房间名称
     * @var string
     */
    public $name = '';


    /**
     * 房间的id号
     * @var string
     */
    public $id = '';




    /**
     * 获取房间人数
     */
    public function getUserNum()
    {
        return count($this->user_uid);
    }


    /**
     * 加入房间
     * @param $fd
     */
    public function push($uid)
    {
        if(array_search($uid,$this->user_uid) === false)
            $this->user_uid[] = $uid;
    }

    /**
     * 从房间中移除用户
     * @param $fd
     */
    public function pop($uid)
    {
        $key = array_search($uid,$this->user_uid);
        if($key === false)
            return;
        array_splice($this->user_uid,$key,1);
    }


    /**
     * 获取所有房间玩家fd信息
     */
    public function getAllUserUid()
    {
        return $this->user_uid;
    }


}