<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/8
 * Time: 17:19
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class User extends Base{


    public function index(){

        $lists = Db::name('user')->field('id,username,nickname,title,mobile,roles,type,email,createdIp,createdTime')->order('id asc')->paginate(20);


        $roles = Db::name('role')->field('id,name,code')->select();
        $types = [['id'=>0,'name'=>'其他'],['id'=>'3','name'=>'学员']];

        $this->assign('list', $lists);
        $this->assign('roles', $roles);
        $this->assign('types', $types);
        $this->assign('typename','用户列表');
        $this->assign('page', $lists->render());
        return $this->fetch('index');
    }


    //添加用户
    public function add(){

        $info = input('post.');

        //错误信息提示
        $msg  =   [
            'user_name.require' => '用户名不能为空',
            'user_name.length' => '用户名长度太短',
        ];

        $validate = new Validate([
            'user_name'  => 'require|length:2,20', //我这里的token是令牌验证
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $user_table = Db::name('user');


        $data['username'] = $info['user_name'];
        $data['nickname'] = $info['user_name'];
        $data['email'] = $info['user_email'];
        $data['type'] = 3;
        $data['roles'] = $info['user_roles'];
        $data['locked'] = $info['user_locked'];
        $data['title'] = 'static\index\images\avatar.png';
        $data['createdIp'] = request()->ip();
        $data['createdTime'] = date('Y-m-d H:i:s' ,time());
        $data['createUserID'] = session('admin_uid');

        $ok = $user_table->field('nickname,username,email,roles,type,locked,title,createdIp,createdTime,createUserID')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }


    }

    public function edit(){

        //前端先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $user_table = Db::name('user');
            $user = $user_table->field('id,nickname,email,type,roles,locked')->where("id='$id'")->find();

            if(!$user){//如果这个用户有
                return ['error'=>'没有此用户','code'=>'300'];
            }else{

                if($user['roles']){//如果权限组里有相应权限，给前端返回，循环
                    $rolecode = $user['roles'];
                    $user['groups'] = Db::name('role')->where("id =$rolecode")->find();
                }

                return ['info'=>$user,'code'=>'000'];
            }

        }
        //前端获取资料结束



        $info = input('post.');


        $msg  =   [
            'user_name.require' => '用户名称不能为空',
            'user_name.length' => '角色名称太短',
        ];

        $validate = new Validate([
            'user_name'  => 'require|length:1,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $user_table = Db::name('user');

        $id = $info['rid']+0;

        $have = $user_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此用户','code'=>'300'];
        }

//        var_dump($info);exit;
        $data['nickname'] = $info['user_name'];
        $data['type'] = $info['user_type'];
        $data['email'] = $info['user_email'];
        $data['roles'] = $info['user_roles'];
        $data['locked'] = $info['user_locked'];

        $ok = $user_table->field('nickname,email,roles,type,locked')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('user')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

}