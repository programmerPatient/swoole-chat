<?php
session_start();
/*已经登陆过直接跳转到首页*/
if($_SESSION['account']){
    header('location:../index.php');
}
require "../vendor/autoload.php";
use app\common\database\redis\RedisDb;
require "../common/database/redis/RedisDb.php";
$error = '';
$redis = new RedisDb([
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => 'root'
]);
if(!$_POST['account']){
    $error .='账号不能为空！';
}else if(!$_POST['password']){
    $error .='密码不能为空！';
}else{
    $account = $_POST['account'];
    $password = $_POST['password'];
    $userinfo = $redis->hGetAll("user:{$account}");
    if(empty($userinfo) || $userinfo['password'] != md5($password)){
        $error .= "账号或密码错误";
    }else{
        $_SESSION['name'] = $userinfo['name'];
        $_SESSION['account'] = $userinfo['account'];
        header('location:../public/index.php');
    }
}
header('location:../public/login.php?error='.$error);