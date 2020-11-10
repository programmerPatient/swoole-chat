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

echo json_encode($redis->hGetAll("room:all"));
