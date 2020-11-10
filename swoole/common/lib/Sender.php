<?php
namespace app\swoole\common\lib;

use app\swoole\logic\room\RoomServerType;

class Sender
{
    public static function package($type,$data)
    {
        $datas['type'] = $type;
        $datas['data'] = $data;
        return json_encode($datas);
    }

    public static function sendAllClient($data)
    {
        $type = RoomServerType::TO_ALL_CLIENT;
        return self::sendToByType($type,$data);
    }

    public static function sendAllRoomClient($roomId,$data)
    {
        $type = RoomServerType::TO_ROOM_ALL;
        $res = self::sendToByType($type,$data);
        $res['roomId'] = $roomId;
        return $res;
    }

    public static function sendToOwn($data)
    {
        $type = RoomServerType::TO_OWN;
        return self::sendToByType($type,$data);
    }

    public static function sendToClient($fd,$data)
    {
        $type = RoomServerType::TO_CLIENT;
        $res = self::sendToByType($type,$data);
        $res['toFd'] = $fd;
        return $res;
    }

    public static function sendToByType($type,$data)
    {
        $res['type'] = $type;
        $res['data'] = $data;
        return $res;
    }
}