<?php
session_start();
if(!$_SESSION['account']){
    header("location:./login.php");
}
$roomId = $_GET['roomId'];
$room_name = $_GET['room_name'];
$account = $_SESSION['account'];
$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>swoole聊天室</title>
	<link rel="stylesheet" href="./static/layui/css/layui.css">
    <link rel="stylesheet" href="./static/css/style.css">

 	<style type="text/css">
 		body{
 			background-image: url('./static/image/login.jpg')
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
 			height: 50px;
		    line-height: 50px;
		    font-size: 50px;
		    color: #009688;
 		}
 		.content .left,.content .right{
 			
 			height:500px;
 			float:left;
 		}

 		.left{
 			width:300px;
 		}

 		.right{
 			width:700px;
 		}

 		.bottom{
 			/*background-color: black;*/
 			width:1000px;
 			height:100px;
 			margin-top:520px;
 		}
 		.layui-form-item{
 			float:left;
 		}

 		.layui-input,.right{

 			box-shadow:inset 1px 1px 5px 5px #cac5c5;
 		}
 		.left-top{
 			text-align: center;
 			height:45px;
 			padding-top:20px;
 			line-height: 5px;
 			border-radius: 10px;
 			color: #0095ff;
    		font-size: 25px;
 		}
 		.left-bottom{
 			height:455px;
 			overflow: auto;
 		}

 		.all span,.left-bottom span{
			background-color: #ccc;
			padding: 5px 8px;
			display: inline-block;
			border-radius: 4px;
			margin:10px 0 10px 10px;
			position: relative;
			
		}
		.all span::after{
			content: '';
			border: 8px solid #ffffff00;
			border-right: 8px solid #ccc;
			position: absolute;
			top: 6px;
			left: -16px;
		}

		.right{
			overflow:auto;
		}

		.right p{
			word-wrap:break-word;  
		    word-break:break-all;  
		    overflow: hidden;
		}
        .myself{
            margin-right: 7px;
        }

		.myself span {
			background-color: #ccc;
			padding: 5px 8px;
			display: inline-block;
			border-radius: 4px;
			margin:10px 15px 10px 10px;
			position: relative;
			
		}

		.myself span::before{
			content: '';
			border: 8px solid #ffffff00;
			border-left: 8px solid #ccc;
			position: absolute;
			top: 6px;
			right: -15px;
		}

 		button{
 			width:100%;
 		}
 	</style>
</head>
<body>
	<div class="login-main">
	    <header class="layui-elip">聊天室名称:【<?php  echo $room_name; ?>】</header>
	    <div class="content">
	    	<div class="left" >
	    		<p class="left-top layui-input" >当前在线</p>
	    		<div class="layui-input left-bottom">
	    			
	    		</div>
	    	</div>
	    	<div class="right layui-input">
	    	</div>
	    </div>
	    <div class="bottom">
	    	<textarea class="layui-input" id="message" style="height:100px;" placeholder="输入消息"></textarea>
            
		     <select name="tofd" lay-verify="required" class="layui-input select">
		        <option value="-1" selected>全部群聊人员</option>
		      </select>
		     <button lay-submit lay-filter="login" class="layui-btn" onclick="sendMessage()">发送</button>
	    </div>

	</div>
</body>
<script type="text/javascript" src="./static/js/jquery.js"></script>
<script type="text/javascript" src="./static/layui/layui.js"></script>
<script type="text/javascript">
	//定义颜色数组
	//构建websocket服务
	var roomId = "<?php echo $roomId?>";
    var account = "<?php echo $account?>";
    var name = "<?php echo $name ?>";
	const socket = new WebSocket("ws://maliweb.top:9501");

	socket.onopen = function(event){
        socket.send(JSON.stringify({
            'messType':102,
            "data":{
                'roomId':roomId,
                'account':account,
            }
        }));
        setInterval('heart()',3000);
	};
	socket.onclose = function(evt){
        layui.use('layer',function () {
            layer.msg("服务关闭了！");
        });
	};
	socket.onerror = function(event){
		console.log("error:"+event.data);
	}; 
	socket.onmessage = function (evt) {

		var data = JSON.parse(evt.data);
        console.log(data)
		if(data['code'] == -1 || data['code'] == 200){
            layui.use('layer',function () {
                layer.msg(data['message']);
            });
		    return ;
        }

        /*新玩家进入房间*/
        if(data['code'] == 400){
            if(data['message']){
                var message = data['message'];
                layui.use('layer',function () {
                    layer.msg(message);
                });
            }
            data = data['data'];
            if(data['roomUser']){
                updateline(data);
            }
            if(data['msg']){
                if(data['from'] == name){
                    $('.right').append('<p class="myself" style="text-align:right;"><span>'+data['msg']+'</span>我</p>');
                }else{
                    $('.right').append('<p class="all">'+data['from']+'：<span>'+data['msg']+'</span></p>');
                }
            }
        }


        //初始化房间信息
        if(data['code'] == 300){
            data = data['data'];
            updateline(data);
        }


		// if(data['right']){
		// 	if(data['is_myself'] == 1) $('.right').append('<p class="myself" style="text-align:right;"><span>'+data['msg']+'</span></p>');
		// 	else if(data['is_myself'] == 0) $('.right').append('<p class="all">'+data['from']+'：<span>'+data['msg']+'</span></p>');
		// }
        //
		// if(data['close_name']){
		// 	layui.use('layer',function () {
		//  		layer.msg(data['close_name']+'退出房间');
		// 	});
		// }

		var showContent = $(".right");
		showContent[0].scrollTop = showContent[0].scrollHeight;
	};


    /**
     * 更新在线人数
     */
    function updateline(data)
    {
        var str = '';
        var se = '<option value="-1" selected>全部群聊人员</option>';
        console.log(data)
        for(var i in data['roomUser']){
            if(data['roomUser'][i] == name){
                str = '<span style="background-color:red">'+data['roomUser'][i]+'</span>'+str;
            }else{
                str += '<span>'+data['roomUser'][i]+'</span>';
                se += '<option value="'+i+'">'+data['roomUser'][i]+'</option>';
            }
        }
        console.log(str);
        $('.left-bottom').empty();
        $('.left-bottom').append(str);
        $('.select').empty();
        $('.select').append(se);
    }

	//发送消息
	function sendMessage(){
		var msg = document.getElementById("message").value; //获取消息
		if(msg == ''){
			alert('输入不能为空！');
			return;
		}
		var toUid = $('.select').val();
		var data = {
            "messType":104,
            "data":{
                'account':account,
                'msg':msg,
                'toUid':toUid,
                'roomId':roomId
            }
		};
		data = JSON.stringify(data);
		
		document.getElementById("message").value='';//清空数据
		//发送消息
		socket.send(data);
	}
	//心跳检测
	function heart()
    {
        var data = {
            "messType":100,
            "data":{
                'account':account,
                'roomId':roomId
            }
        };
        data = JSON.stringify(data);
        socket.send(data);
    }
</script>
</html>