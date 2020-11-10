<?php
namespace app\swoole\logic\room;


class RoomServerType
{
    //转发给自己的消息
    const TO_OWN = 100;
    //转发给所有连接的消息
    const TO_ALL_CLIENT = 101;
    //房间内的消息转发
    const TO_ROOM_ALL = 102;
    //私聊消息
    const TO_CLIENT = 103;
}
