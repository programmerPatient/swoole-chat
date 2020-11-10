<?php
session_start();
header("Content-Type:text/html;charset=UTF-8");
if($_SESSION['uid']){
    header('location:../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>个人信息页</title>
    <link rel="stylesheet" href="./static/layui/css/layui.css">
    <link rel="stylesheet" href="./static/css/style.css">
    <style type="text/css">
        body{
            background-image: url('./static/image/register.jpg')
        }
        .login-main{
            margin-top:200px;
            width:400px;
            height:350px;
            padding:50px;
            background-color: #eaeaea;
        }
        .login-main header{
            margin-top:0px;
        }

        #captcha{
            width:45%;
            margin-left:5%;
            height:35px;
        }

        .error,.success{
            width:100%;
            text-align: center;
            padding-bottom:10px;
        }
        .error{
            color:red;
        }
        .success{
            color:green;
        }
        .login-main form .layui-input-inline input{
            width:60%;
        }
    </style>
</head>
<body>
<div class="login-main">
    <header class="layui-elip">注册</header>
    <form class="layui-form" action="../php/register.php" method="POST">
        <div class="layui-input-inline">
            <label class="layui-form-label ">账号：</label>
            <input type="text" name="account" required lay-verify="required" placeholder="账号" autocomplete="off"
                   class="layui-input" style="float:left">
        </div>
        <div class="layui-input-inline">
            <label class="layui-form-label ">昵称：</label>
            <input type="text" name="name" required lay-verify="required" placeholder="用户昵称" autocomplete="off"
                   class="layui-input">
        </div>
        <div class="layui-input-inline">
            <label class="layui-form-label ">密码：</label>
            <input type="password" name="password" lay-verify="required" required placeholder="密码" autocomplete="off"
                   class="layui-input">
        </div>
        <div class="layui-input-inline">
            <label class="layui-form-label ">密码确认：</label>
            <input type="password" name="confim_password" lay-verify="required" required placeholder="确认密码" autocomplete="off"
                   class="layui-input">
        </div>
        <div class="layui-input-inline login-btn">
            <button lay-submit lay-filter="login" class="layui-btn">提交</button>
        </div>
        <hr/>
        <p style="text-align: center;">已有账号？<a href="login.php">登录</a></p>
    </form>
</div>


<script src="./static/layui/layui.js"></script>
<script type="text/javascript" src="./static/js/jquery.js"></script>
<script type="text/javascript">
    function update_captcah(){
        document.getElementById('captcha').src="./php/captcha.php?";
    }
    var error = "<?php echo $_GET['error']?$_GET['error']:null; ?>";
    if(error){
        $('.layui-elip').after('<div class="error">'+error+'</div>');
        // alert(error);
    }
</script>
</body>
</html>