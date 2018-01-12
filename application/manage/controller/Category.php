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
 * 此为专业管理控制器
 * 还有一个Category控制，那个是栏目功能列表控制器
 */

class Category extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['flag'])){

            $where['Flag'] = ['eq',$info['flag']];
        }
        if(!empty($info['name'])){
            $where['name'] = ['like',"%{$info['name']}%"];
        }

        $list = Db::name('category')
            ->field('id,name,code,studyTimes,point,createtime,Flag,description')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $this->assign('list',$list);
        $this->assign('page',$list->render());


        $this->assign('typename','专业管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '专业名称不能为空',
            'name.length' => '专业名称长度太短',
            'code.require' => '代码不能为空',
            'point.number' => '学分必须为数字',
        ];
        $validate = new Validate([
            'name'  => 'require|length:2,20',
            'code'   => 'require',
            'point'  => 'number'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $role_table = Db::name('category');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'name' => $info['name'],
            'code' => $info['code'],
            'point'=> $info['point'],
            'studyTimes'=>$info['studyTimes'],
            'description'=>$info['description'],
            'createtime'=>date('Y-m-d H:i:s',time()),
//            'Flag'=>1,
        ];

        $ok = $role_table->field('name,code,point,studyTimes,description,createtime,Flag')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'rid.require' => '专业名称rid不能为空',
            'name.require' => '专业名称不能为空',
            'name.length' => '专业名称长度太短',
            'code.require' => '代码不能为空',
            'code.number' => '代码必须为数字',
            'point.number' => '学分必须为数字',
        ];

        $validate = new Validate([
            'rid'    => 'require',
            'name'   => 'require|length:2,20',
            'code'   => 'require|number',
            'point'  => 'number'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('category');

        $id = $info['rid']+0;
        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此专业','code'=>'300'];
        }

        $have = $role_table->field('id,code')->where("id <> $id AND code={$info['code']}")->find();

        if($have){
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'name'=>$info['name'],
            'code'=>$info['code'],
            'point'=>$info['point'],
            'studyTimes'=>$info['studyTimes'],
            'description'=>$info['description'],
//            'createtime'=>date('Y-m-d H:i:s',time())
        ];

        $ok = $role_table->field('name,code,point,studyTimes,description,createtime')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function enable(){

        $id = $_GET['rid']+0;

        $data = ['Flag'=>0,'createtime'=>date('Y-m-d H:i:s',time())];
        $ok = Db::name('category')->field('Flag,createtime')->where("id='$id'")->update($data);

        if($ok){
            return ['info'=>'禁用成功','code'=>'000'];
        }else{
            return ['error'=>'禁用失败','code'=>'200'];
        }
    }



}