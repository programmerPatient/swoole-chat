<?php
session_start();

if(!$_SESSION['account']){
    header("location:./public/login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>swoole聊天室</title>
    <link rel="stylesheet" href="public/static/layui/css/layui.css">
    <link rel="stylesheet" href="public/static/css/style.css">

    <style type="text/css">
        body{
            background-image: url('public/static/image/login.jpg')
        }
        .login-main{
            margin-top:50px;
            width:1000px;
            height:750px;
            padding:50px;
            background-color: #eaeaea;
        }
        .login-main header{
            margin-top:0px;
        }


        .bottom{
            /*background-color: black;*/
            width:1000px;
            height:100px;
            margin-top:520px;
            position: absolute;
            top:200px;
        }
        .layui-form-item{
            float:left;
        }

        .content{
            overflow:auto;
            height:600px;
            width:1000px;
            padding-left: 10px;
        }
        .li{
            background-color: #f9f9f9;
            width:200px;
            height:100px;
            margin:10px 10px;
            padding:10px;
            box-shadow: 5px 8px #dadada;
            text-align: center;
            line-height: 100px;
            font-size: 20px;
            overflow:hidden;
            float: left;
        }
        button{
            width:100%;
        }

    </style>
</head>
<body>
<div class="login-main">
    <header class="layui-elip">聊天房间室</header>
    <div class="content">

    </div>
    <div class="bottom">
        <button lay-submit lay-filter="login" class="layui-btn" onclick="add()">新建聊天室</button>
    </div>

</div>
</body>
<script type="text/javascript" src="public/static/js/jquery.js"></script>
<script type="text/javascript" src="public/static/layui/layui.js"></script>
<script type="text/javascript">

    // const socket = new WebSocket("ws://192.168.3.213:9501");
    var uid = "<?php echo $_SESSION['account']?>";
    var name = "<?php echo $_SESSION['name']?>";
    $.ajax({
        url:'./php/get_room.php',
        type:'get',
        data:{},
        success:function(data){
            var da = JSON.parse(data);
            for(var i in da){
                var str = '<a href="./public/room.php?roomId='+i+'&room_name='+da[i]+'" target = "_blank"><div class="li">'+da[i]+'</div></a>';
                $('.content').append(str)
            }
            layui.use('layer',function () {
                layer.msg("欢迎"+name+"进入聊天系统");
            });
        },
        complete: function () {
        },
    });

    // socket.onopen = function(event){
    //     sendMessage({"messType":1000});
    //     layui.use('layer',function () {
    //         layer.msg('欢迎进入聊天系统');
    //     });
    // };
    // socket.onclose = function(evt){
    //     alert("服务断开连接，稍后重试！")
    // };
    // socket.onerror = function(event){
    //     alert("服务错误，稍后重试！");
    //     console.log("error:"+event.data);
    // };
    // socket.onmessage = function (evt) {
    //     console.log("接收到数据为：\n");
    //     var data = JSON.parse(evt.data);
    //     console.log(data);
    //     if(data['code'] == -1){
    //         alert(data['message']);
    //         return;
    //     }
    //     for(var i in data['data']){
    //         var str = '<a href="room.php?room_id='+i+'&room_name='+data['data'][i]+'" target = "_blank"><div class="li">'+data['data'][i]+'</div></a>';
    //         $('.content').append(str)
    //     }
    //
    // };

    // //发送消息
    // function sendMessage(data){
    //     data['uid'] = uid;
    //     data = JSON.stringify(data);
    //     socket.send(data);
    // }

    function add(){
        layui.use('layer',function () {
            layer.prompt({title: '输入房间名称，并确认', formType: 3,maxlength: 30}, function(pass, index){
                if(!pass){
                    layer.msg('房间名称不能为空！');
                }
                $.ajax({
                    url:'./php/add_room.php',
                    type:'post',
                    data:{'name':pass},
                    success:function(data){
                        var da = JSON.parse(data);
                        console.log(da)
                        layer.close(index);
                        var str = ''+da['room_id']+'<a href="./public/room.php?room_id=&room_name=" target = "_blank"><div class="li">'+da['room_name']+''+da['room_name']+'</div></a>';
                        $('.content').append(str)
                        window.open('./public/room.php?roomId='+da['room_id']+'&room_name='+da['room_name']);
                    },
                    complete: function () {
                        layer.close(index);
                    },
                    // sendMessage({
                    //     "messType":1001,
                    //     "args":{
                    //         'account':uid,
                    //         "name":pass
                    //     }
                    // });
                    // layer.close(index);
                });

            });
        });
    }
</script>
</html>