<?php
session_start();

require "../vendor/autoload.php";
use app\common\database\redis\RedisDb;
require "../common/database/redis/RedisDb.php";
$error = '';
$redis = new RedisDb([
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => 'root'
]);

$result = $redis->hGet("user:name",$_POST['name']);
$results = $redis->hGetAll("user:{$_POST['account']}");
if($result){
    $error .= "昵称已经存在";
}else if($results){
    $error .= "账号已经存在";
}else if(empty($_POST['name']) || strlen($_POST['name']) > 16 ){
    $error .= '昵称不能为空或长度超过16！';
}else if(empty($_POST['account'])){
    $error .= '账号不能为空！';
}else if(empty($_POST['password']) || strlen($_POST['password']) > 16){
    $error .= '密码不能为空或密码长度超过16！';
}else if($_POST['password'] != $_POST['confim_password']){
    $error .= '两次密码不一致 ';
}
if(!empty($error)){
    header('location:../public/register.php?error='.$error);
}else{
    $data['name'] = $_POST['name'];
    $data['account'] = $_POST['account'];
    $data['password'] = md5($_POST['password']);
    $redis->hMSet("user:{$_POST['account']}",$data);
    $redis->hSet("user:name",$_POST['name'],$_POST['account']);
    $redis->commitMulti();
    header('location:../public/login.php');
}
