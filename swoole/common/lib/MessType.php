<?php
namespace app\swoole\common\lib;

/**
 * 消息类型
 * Class MessType
 * @package app\swoole\common\lib
 */
class MessType
{
    //心跳
    const PING = 100;

    //同步内部客户端信息
    const CLIENT_SYN = 101;

    //同步内部客户端的房间数
    const CLIENT_ROOM_NUM = 103;

    //初始化房间信息
    const INIT_USER_ROOM_INFO = 102;

    //聊天
    const CHAT = 104;

    //用戶下綫
    const USER_OFFLINE = 105;
}