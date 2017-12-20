<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\login\findpwd.html";i:1512960935;}*/ ?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>后台登录</title>

    <link rel="shortcut icon" href="favicon.ico"> <link href="__CSS__bootstrap.min.css?v=3.3.5" rel="stylesheet">
    <link href="__CSS__font-awesome.min.css?v=4.4.0" rel="stylesheet">

    <link href="__CSS__animate.min.css" rel="stylesheet">
    <link href="__CSS__style.min.css?v=4.0.0" rel="stylesheet">
    <!--[if lt IE 8]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <script>if(window.top !== window.self){ window.top.location = window.location;}</script>
</head>

<body class="gray-bg">

<div class="middle-box text-center loginscreen  animated fadeInDown">
    <div>
        <div>

            <h2>欢迎使用豫化系统-找回密码</h2>

        </div>

        <form class="m-t" role="form" action="<?php echo url('Manage/login/findPwdDo'); ?>" method="post">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="邮箱" required="" name="email">
            </div>
            <div class="form-group">
                <input type="text" name="captcha" placeholder="验证码" class="form-control captcha col-lg-4" style="width: 150px">
                <img src="<?php echo captcha_src(); ?>" onclick="this.src='<?php echo captcha_src(); ?>?'+Math.random();" title="换一张" style="cursor: pointer;width:120px"/>
            </div>
            <input type="hidden" name="__token__" value="<?php echo \think\Request::instance()->token(); ?>" />
            <button type="submit" class="btn btn-primary block full-width m-b">发送邮箱</button>
            <p class="text-muted text-center"> <a href="<?php echo url('Manage/login/index'); ?>"><small>登陆</small></a>
            </p>

        </form>
    </div>
</div>
<script src="__JS__jquery.min.js?v=2.1.4"></script>
<script src="__JS__bootstrap.min.js?v=3.3.5"></script>
</body>

</html>