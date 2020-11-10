<?php
session_start();
header("Content-Type:text/html;charset=UTF-8");
if($_SESSION['account']){
    header('location:../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>登录页</title>
    <link rel="stylesheet" href="./static/layui/css/layui.css">
    <link rel="stylesheet" href="./static/css/style.css">
    <style type="text/css">
        body{
            background-image: url('./static/image/login.jpg')
        }
        .login-main{
            margin-top:250px;
            width:400px;
            height:250px;
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
    </style>
</head>
<body>

<div class="login-main">
    <header class="layui-elip">登录</header>
    <form class="layui-form" action="../php/login.php" method="POST">
        <div class="layui-input-inline">
            <input type="text" name="account" required lay-verify="required" placeholder="用户名" autocomplete="off"
                   class="layui-input">
        </div>
        <div class="layui-input-inline">
            <input type="password" name="password" required lay-verify="required" placeholder="密码" autocomplete="off"
                   class="layui-input">
        </div>
        <div class="layui-input-inline login-btn">
            <button lay-submit lay-filter="login" class="layui-btn">登录</button>
        </div>
        <hr/>
        <p style="text-align: center;"><a href="register.php" style="float:left">没有账号？立即注册</a><a href="./admin/login.php" style="float:right">管理员登录</a></p>
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
    }

    var success = "<?php echo $_GET['success']?$_GET['success']:null; ?>";
    if(success){
        $('.layui-elip').after('<div class="success">'+success+'</div>');
        // alert(error);
    }
</script>
</body>
</html>
