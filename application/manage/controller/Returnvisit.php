<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/25
 * Time: 11:43
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

/*
 * 生员回访控制器
 */

class Returnvisit extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['content'])){

            $where['content'] = ['like',"%{$info['content']}%"];
        }

        $list = Db::name('return_visit a')
            ->join('user b','a.fromUserID=b.id','LEFT')
            ->where($where)
            ->field('a.*,b.username uname')
            ->order('a.id desc')
            ->paginate(20,false,['query'=>request()->get()]);



        $this->assign('list',$list);
        $this->assign('page',$list->render());


        $this->assign('typename','专业管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'username.require' => '被回访人名不能为空',
            'username.length' => '被回访人名长度太短',
            'phone.require' => '手机不能为空',
        ];
        $validate = new Validate([
            'username'  => 'require|length:2,20',
            'phone'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('return_visit');

        $data = [
            'username'=>$info['username'],
            'phone'=>$info['phone'],
            'content'=>$info['content'],
            'result'=>$info['result'],
            'fromUserID'=>session('admin_uid'),
            'type'=>isset($info['type'])?$info['type']:'',
            'visitTime'=>date('Y-m-d H:i:s',time()),
            'createdTime'=>date('Y-m-d H:i:s',time())
        ];

        $ok = $role_table->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'rid.require' => '被回访人名rid不能为空',
            'username.require' => '被回访人名不能为空',
            'username.length' => '被回访人名长度太短',
            'phone.length' => '手机必须填写',
        ];

        $validate = new Validate([
            'rid'    => 'require',
            'username'   => 'require|length:2,20',
            'phone'    => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('return_visit');

        $id = $info['rid']+0;

        $data = [
            'username'=>$info['username'],
            'phone'=>$info['phone'],
            'content'=>$info['content'],
            'result'=>$info['result'],
            'fromUserID'=>session('admin_uid'),
            'type'=>isset($info['type'])?$info['type']:'',
            'visitTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function enable(){

        $id = $_GET['rid']+0;

        $data = ['Flag'=>0];
        $ok = Db::name('return_visit')->where("id='$id'")->update($data);

        if($ok){
            return ['info'=>'禁用成功','code'=>'000'];
        }else{
            return ['error'=>'禁用失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('return_visit')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }



}