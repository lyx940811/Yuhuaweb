<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:81:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\role\rolefunction.html";i:1513757544;s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\header.html";i:1513757089;s:77:"D:\wamp\www\tp5-yuhuaweb\public/../application/manage\view\manage\bottom.html";i:1513757089;}*/ ?>
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
    $access = check($uid,['/manage/role/edit','/manage/role/delete','/manage/role/add']);
     ?>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>权限组ID</th>
            <th>权限代码</th>
            <th>拥有权限列表</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>

        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>

        <tr>
            <td><?php echo $vo['id']; ?></td>
            <td>
                <strong>
                    <a href="#" onclick="add_edit('edit',<?php echo $vo['id']; ?>);"  data-toggle="modal" data-target="#myModal"><?php echo $vo['rolecode']; ?>组</a>
                </strong>
            </td>
            <td><?php if(is_array($vo['groups']) || $vo['groups'] instanceof \think\Collection || $vo['groups'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['groups'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$g): $mod = ($i % 2 );++$i;?><?php echo $g['name']; ?><strong> | </strong><?php endforeach; endif; else: echo "" ;endif; ?></td>
            <td>
                <?php 

                foreach($access as $k=>$v){

                    if($v=='/manage/role/edit'){
                        echo '<a href="#" onclick="add_edit(\'edit\','.$vo['id'].');"  data-toggle="modal" data-target="#myModal">编辑</a>';
                        echo '<strong>/</strong>';
                    }elseif($v=='/manage/role/delete'){
                        echo '<a href="#" onclick="deleteRole('.$vo['id'].');">删除</a>';
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
            if($v=='/manage/role/add'){
                echo '<a class="btn btn-primary" onclick="add_edit(\'add\');"  data-toggle="modal" data-target="#myModal">添加权限组</a>';
            }
        }
         ?>

    </div>
    <ul class="pagination">
        <?php echo $page; ?>
    </ul>

    <!--添加权限组-->
    <!-- 模态框 -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 class="modal-title">添加权限组</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- 模态框主体 -->
                <div class="modal-body">
                    <form class="form-horizontal" role="form" id="form1" method="post" action="<?php echo url('add'); ?>">
                        <div class="form-group">
                            <label for="role_name" class="col-sm-2 control-label">权限组名称</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="role_name" name="name"
                                       placeholder="请输入角色名称">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="role_code" class="col-sm-2 control-label">角色代码</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="role_code" name="code"
                                       placeholder="请输入角色代码">
                            </div>
                        </div>
                        <input type="hidden" name="parentcode" value="" id="parentcode"/>
                        <input type="hidden" name="rid" value="" id="selfcode"/>
                        <!--<input type="hidden" name="__token__" value="<?php echo \think\Request::instance()->token(); ?>" />-->
                        <!-- 模态框底部 -->
                        <div class="modal-footer">
                            <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>-->
                            <button type="button" class="btn btn-default" id="add_role" onclick="dopost('add');">添加</button>
                            <button type="button" class="btn btn-default" id="edit_role" style="display:none;" onclick="dopost('edit')">修改</button>
                        </div>
                    </form>
                </div>



            </div>
        </div>
    </div>
    <!--添加权限组结束-->
    <!--删除权限组提示-->
    <div class="modal fade" id="myModal2">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- 模态框头部 -->
                <div class="modal-header">
                    <h4 class="modal-title">删除权限组成功</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- 模态框主体 -->
                <div class="modal-body">

                    删除权限成功！

                </div>



            </div>
        </div>
    </div>
    <!--删除权限提示结束-->
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
一共3种方法，添加新权限组，修改权限组，删除权限组
*/
    /*添加*/


    function add_edit(type,id,parentcode) {

        switch (type){
            case 'add':

                $(" input[ type='text' ] ").val('')
                $(" input[ type='hidden' ] ").val('')
                $('#add_role').show()
                $('#edit_role').hide()
                break;

            case 'edit':
                $('#selfcode').val(id)//如果是修改,id是自己
                $('#parentcode').val('')
                $('#add_role').hide()
                $('#edit_role').show()
                $.get("<?php echo url('edit'); ?>?do=get&rid="+id,function (data) {
                    $('#role_name').val(data.info.name)
                    $('#role_code').val(data.info.code)
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
                    $('#parentcode').val('')//清空

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

    function deleteRole(id) {
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
