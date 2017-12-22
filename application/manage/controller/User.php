<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/8
 * Time: 17:19
 */
namespace app\manage\controller;

use think\Db;
use think\paginator\driver\Bootstrap;
use think\Validate;

class User extends Base{


    public function index(){

        $lists = Db::name('user')->field('id,username,title,mobile,roles,type,email,createdIp,createdTime')->order('id asc')->paginate(20);


        $roles = Db::name('role')->field('id,name,code')->select();


        $this->assign('list', $lists);
        $this->assign('roles', $roles);
        $this->assign('typename','用户列表');
        $this->assign('page', $lists->render());
        return $this->fetch('index');
    }


    public function edit(){

        //前端先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $user_table = Db::name('user');
            $user = $user_table->field('id,nickname,type,roles')->where("id='$id'")->find();

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

        $id = $info['rid'];

        $have = $user_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此用户','code'=>'300'];
        }

//        var_dump($info);exit;
        $data['nickname'] = $info['user_name'];
        $data['type'] = $info['user_type'];
        $data['roles'] = $info['user_roles'];

        $ok = $user_table->field('nickname,roles,type')->where('id',$id)->update($data);

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