<?php
session_start();
error_reporting(E_ALL&~E_WARNING);
require "../vendor/autoload.php";
use app\common\database\redis\RedisDb;
require "../common/database/redis/RedisDb.php";
$error = '';
$redis = new RedisDb([
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => 'root',
    'database' => '',
]);
if(!$_POST['name']){
    $error .= "房间名不能为空";
    header("location:../public/index.php?error=".$error);
}
$args = [
    'name' => $_POST['name']
];
$res = addRoom($redis,$args);
if($res['code'] == -1){
    $error .= $res['message'];
    header("location:../public/index.php?error=".$error);
}else{
    echo json_encode($res['data']);
}


/**
 * 获取新房间的id
 */
function getNewRoomId()
{
    $key = uniqid();
    return md5($key);
}

/**
 * 添加房间
 * @param $args
 */
function addRoom($redis,$args)
{
    if(!array_key_exists('name',$args) || !isset($args['name'])){
        return $data = [
            'code' => -1,
            'message' => '房间名不存在，请重试',
        ];
    }
    $roomId = getNewRoomId();
    $redis->hSet('room:all',$roomId,$args['name']);
    $re = $redis->commitMulti();
    if($re){
        $data = [
            'code' => 200,
            'message' => 'OK',
            'data' => [
                'room_id' => $roomId,
                'room_name' => $args['name'],
            ]
        ];
    }else{
        $data = [
            'code' => -1,
            'message' => '添加房间失败',
        ];
    }
    return $data;
}