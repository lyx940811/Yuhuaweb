<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:74:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\user\index.html";i:1513757089;s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\header.html";i:1513757089;s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\bottom.html";i:1513757089;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title>后台管理-<?php echo !empty($typename)?$typename:''; ?></title>

    <!--[if lt IE 8]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="__MANAGE_CSS__bootstrap.min.css?v=3.3.5" rel="stylesheet">
    <link href="__MANAGE_CSS__font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="__MANAGE_CSS__animate.min.css" rel="stylesheet">
    <!--<link href="__CSS__style.min.css?v=4.0.0" rel="stylesheet">-->
</head>
<body>
<div class="container-fluid">
    <?php 
    $uid = session('admin_uid');
    $access = check($uid,['/manage/user/edit','/manage/user/delete','/manage/user/add']);
     ?>

    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>用户ID</th>
            <th>用户名</th>
            <th>用户头像</th>
            <th>手机号</th>
            <th>所属用户组</th>
            <th>类型</th>
            <th>邮箱</th>
            <th>IP</th>
            <th>注册时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <tr>
            <td><?php echo $vo['id']; ?></td>
            <td>
                <strong>
                    <a href="#" onclick="add_edit('edit',<?php echo $vo['id']; ?>);"  data-toggle="modal" data-target="#myModal"><?php echo $vo['username']; ?></a>
                </strong>
            </td>
            <td><img src="__ROOT__/<?php echo $vo['title']; ?>" title="用户头像" width="20px"/></td>
            <td><?php echo $vo['mobile']; ?></td>
            <td><?php echo $vo['roles']; ?></td>
            <td><?php echo $vo['type']; ?></td>
            <td><?php echo $vo['email']; ?></td>
            <td><?php echo $vo['createdIp']; ?></td>
            <td><?php echo $vo['createdTime']; ?></td>
            <td>
                <?php 

                foreach($access as $k=>$v){

                    if($v=='/manage/user/edit'){
                        echo '<a href="#" onclick="add_edit(\'edit\','.$vo['id'].');"  data-toggle="modal" data-target="#myModal">编辑</a>';
                        echo '<strong>/</strong>';
                    }elseif($v=='/manage/user/delete'){
                        echo '<a href="#" onclick="deleteUser('.$vo['id'].');">删除</a>';
                    }
                }
                 ?>

            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        </tbody>

    </table>
    <div class="view">
        <?php 
        foreach($access as $k=>$v){
            if($v=='/manage/user/add'){
                echo '<a class="btn btn-primary" onclick="add_edit(\'add\');"  data-toggle="modal" data-target="#myModal">添加新用户</a>';
            }
        }
         ?>

    </div>
    <ul class="pagination">
        <?php echo $page; ?>
    </ul>

    <!--添加栏目-->
    <!-- 模态框 -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 class="modal-title">添加新用户</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- 模态框主体 -->
                <div class="modal-body">
                    <form class="form-horizontal" role="form" id="form1" method="post" action="<?php echo url('add'); ?>">
                        <div class="form-group">
                            <label for="user_name" class="col-sm-2 control-label">用户名称</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="user_name" name="name"
                                       placeholder="请输入功能栏目名称">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_password" class="col-sm-2 control-label">用户密码</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="user_password" name="code"
                                       placeholder="请输入栏目代码">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_type" class="col-sm-2 control-label">用户类型</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="user_type" name="code"
                                       placeholder="请输入栏目代码">
                            </div>
                        </div>
                        <input type="text" name="rid" value="" id="selfcode"/>
                        <!-- 模态框底部 -->
                        <div class="modal-footer">
                            <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>-->
                            <button type="button" class="btn btn-default" id="add_user" onclick="dopost('add');">添加</button>
                            <button type="button" class="btn btn-default" id="edit_user" style="display:none;" onclick="dopost('edit')">修改</button>
                        </div>
                    </form>
                </div>



            </div>
        </div>
    </div>
    <!--添加功能栏目结束-->
    <!--删除功能提示-->
    <div class="modal fade" id="myModal2">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 class="modal-title">删除用户成功</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- 模态框主体 -->
                <div class="modal-body">

                    删除用户成功！

                </div>



            </div>
        </div>
    </div>
    <!--删除用户提示结束-->
</div>
<script src="__MANAGE_JS__jquery.min.js?v=2.1.4"></script>
<script src="__MANAGE_JS__bootstrap.min.js?v=3.3.5"></script>
<script src="__MANAGE_JS__plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="__MANAGE_JS__plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="__MANAGE_JS__plugins/layer/layer.min.js"></script>
<script src="__MANAGE_JS__hplus.min.js?v=4.0.0"></script>
<script src="__MANAGE_JS__contabs.min.js" type="text/javascript" ></script>
<script src="__MANAGE_JS__plugins/pace/pace.min.js"></script>
<script type="text/javascript">
/*
一共3种方法，添加新用户，修改用户，删除用户
*/
    /*添加*/

    function add_edit(type,id) {

        switch (type){
            case 'add':

                $(" input[ type='text' ] ").val('')
                $(" input[ type='hidden' ] ").val('')
                $('#add_user').show()
                $('#edit_user').hide()

                break;
            case 'edit':
                $('#selfcode').val(id)//如果是修改,id是自己
                $('#add_user').hide()
                $('#edit_user').show()
                $.get("<?php echo url('edit'); ?>?do=get&rid="+id,function (data) {
                    $('#user_name').val(data.info.name)
                    $('#user_code').val(data.info.code)
                    $('#selfcode').val(data.info.id)
                });
                
                break;
            default:
                alert('error')
        }


    }


    function dopost($url) {
        $url=='add'?"<?php echo url('add'); ?>":"<?php echo url('edit'); ?>";

        $.ajax({
            url: $url+"?"+Math.random(),
            type:"post",
            data:$("#form1").serialize(),
            success:function(data){

                if(data.code=='000'){

                    alert(data.info)

                    $('#selfcode').val('')//必须清空

                    $('#myModal').modal('hide')

                }else{
                    alert(data.error)
                }

            },
            error:function(e){
                alert("添加信息错误");
            }
        });
    }

    function deleteUser(id) {
        $.get("<?php echo url('delete'); ?>?rid="+id,function (data) {
            if(data.code=='000'){
                alert(data.info)
                $('#myModal2').modal('hide')
            }else{

            }
        });
    }


</script>
</body>
</html>