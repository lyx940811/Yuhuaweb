<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:76:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\index.html";i:1513758249;s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\header.html";i:1513928412;s:83:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\left-sidebar.html";i:1514275049;s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\bottom.html";i:1513928377;}*/ ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title><?php echo !empty($typename)?$typename.'-':''; ?>后台管理</title>

    <!--[if lt IE 8]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="__MANAGE_CSS__bootstrap.min.css?v=3.3.5" rel="stylesheet">
    <link href="__MANAGE_CSS__font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="__MANAGE_CSS__animate.min.css" rel="stylesheet">
    <!--<link href="__CSS__style.min.css?v=4.0.0" rel="stylesheet">-->
</head>
<link href="__MANAGE_CSS__style.min.css?v=4.0.0" rel="stylesheet">
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
<div id="wrapper">
    <!--左侧导航开始-->
    <!--左侧导航开始-->
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="nav-close"><i class="fa fa-times-circle"></i>
    </div>
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span><img alt="image" class="img-circle" src="<?php echo $admin_info['img']; ?>" /></span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="clear">
                               <span class="block m-t-xs"><strong class="font-bold"><?php echo $admin_info['name']; ?></strong></span>
                                <span class="text-muted text-xs block">超级管理员<b class="caret"></b></span>
                                </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">

                        <li><a class="J_menuItem" href="reset">重置密码</a>
                        </li>

                        <li class="divider"></li>
                        <li><a href="<?php echo url('Manage/login/logout'); ?>">安全退出</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#">
                    <i class="fa fa-group"></i>
                    <span class="nav-label">用户管理</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/user/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>用户列表</a>
                    </li>
                </ul>

            </li>
            <li>
                <a href="#">
                    <i class="fa fa-outdent"></i>
                    <span class="nav-label">角色管理</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/role/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>角色列表</a>
                        <a class="J_menuItem" href="<?php echo url('Manage/rolefunction/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>权限组</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#">
                    <i class="fa fa-certificate"></i>
                    <span class="nav-label">栏目管理</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/functions/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>栏目功能列表</a>
                    </li>

                </ul>
            </li>
            <li>
                <a href="#">
                    <i class="fa fa-leanpub"></i>
                    <span class="nav-label">日志管理</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/log/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>日志列表</a>
                    </li>

                </ul>
            </li>
            <li>
                <a href="#">
                    <i class="fa fa-leanpub"></i>
                    <span class="nav-label">专业管理</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/category/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>专业列表</a>
                    </li>
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/categorycourse/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>专业课程</a>
                    </li>
                    <li>
                        <a class="J_menuItem" href="<?php echo url('Manage/studentenroll/index'); ?>" data-index="0"><i class="fa fa-angle-double-right"></i>专业报名数据查询</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<!--左侧导航结束-->
    <!--左侧导航结束-->
    <!--右侧部分开始-->
    <div id="page-wrapper" class="gray-bg dashbard-1">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">

                <ul class="nav navbar-top-links navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                            <i class="fa fa-envelope"></i> <span class="label label-warning">16</span>
                        </a>
                        <ul class="dropdown-menu dropdown-messages">
                            <li class="m-t-xs">
                                <div class="dropdown-messages-box">
                                    <a href="#" class="pull-left">
                                        <img alt="image" class="img-circle" src="img/a7.jpg">
                                    </a>
                                    <div class="media-body">
                                        <small class="pull-right">46小时前</small>
                                        <strong>小四</strong> 这个在日本投降书上签字的军官，建国后一定是个不小的干部吧？
                                        <br>
                                        <small class="text-muted">3天前 2014.11.8</small>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <div class="dropdown-messages-box">
                                    <a href="#" class="pull-left">
                                        <img alt="image" class="img-circle" src="img/a4.jpg">
                                    </a>
                                    <div class="media-body ">
                                        <small class="pull-right text-navy">25小时前</small>
                                        <strong>国民岳父</strong> 如何看待“男子不满自己爱犬被称为狗，刺伤路人”？——这人比犬还凶
                                        <br>
                                        <small class="text-muted">昨天</small>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
        <div class="row content-tabs">
            <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i>
            </button>
            <nav class="page-tabs J_menuTabs">
                <div class="page-tabs-content">
                    <a href="javascript:;" class="active J_menuTab" data-id="index_v1.html">首页</a>
                </div>
            </nav>
            <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
            </button>
            <div class="btn-group roll-nav roll-right">
                <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span>

                </button>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li class="J_tabShowActive"><a>定位当前选项卡</a>
                    </li>
                    <li class="divider"></li>
                    <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                    </li>
                    <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                    </li>
                </ul>
            </div>
            <a href="<?php echo url('Manage/login/logout'); ?>" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i> 退出</a>
        </div>
        <div class="row J_mainContent" id="content-main">
            <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="<?php echo url('Manage/manage/right'); ?>" frameborder="0" data-id="<?php echo url('Manage/manage/right'); ?>" seamless></iframe>
        </div>
        <div class="footer">
            <div class="pull-right">&copy; 2014-2015 <a href="#" target="_blank">豫化系统</a>
            </div>
        </div>
    </div>
    <!--右侧部分结束-->

</div>
<script src="__MANAGE_JS__jquery.min.js?v=2.1.4"></script>
<script src="__MANAGE_JS__bootstrap.min.js?v=3.3.5"></script>
<script src="__MANAGE_JS__plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="__MANAGE_JS__plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="__MANAGE_JS__plugins/layer/layer.min.js"></script>
<script src="__MANAGE_JS__hplus.min.js?v=4.0.0"></script>
<script src="__MANAGE_JS__contabs.min.js" type="text/javascript" ></script>
<script src="__MANAGE_JS__plugins/pace/pace.min.js"></script>
</body>

</html>