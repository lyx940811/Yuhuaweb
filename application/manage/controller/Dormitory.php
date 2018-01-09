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
 * 宿舍管理
 */
class Dormitory extends Base{

    public function index(){

        $info = input('get.');

        $where = [];

        if(!empty($info['name'])){

            $where['name'] = ['like',"%{$info['name']}%"];
        }

        $list = Db::name('dormitory')->field('id,name,code,flag')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $this->assign('list',$list);
        $this->assign('page',$list->render());

        $this->assign('typename','宿舍管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '名称不能为空',
            'name.length' => '名称长度太短',
        ];

        $validate = new Validate([
            'name'  => 'require|length:2,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('dormitory');

        $data = [
            'name' => $info['name'],
            'code' => $info['code'],
            'Flag'=>1,
        ];

        $ok = $role_table->field('name,code,Flag')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'name.require' => '名称不能为空',
            'name.length' => '名称长度太短',
        ];

        $validate = new Validate([
            'name'  => 'require|length:2,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('dormitory');

        $id = $info['rid']+0;

//        $have = $role_table->field('id')->where("id='$id'")->find();
//
//        if(!$have){//如果没这个code
//            return ['error'=>'没有此班级','code'=>'300'];
//        }

        $data = [
            'name' => $info['name'],
            'code' => $info['code'],
        ];

        $ok = $role_table->field('name,code')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('dormitory')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }



}