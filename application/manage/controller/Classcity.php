<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/28
 * Time: 16:05
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Classcity extends Base{

    public function index(){

        $list = Db::table('classcity')->field('id,classname,code,Flag')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','区域列表');
        return $this->fetch();
    }


    public function add(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '区域名称不能为空',
            'name.length' => '区域名称长度太短',
            'code.require' => '区域代码不能为空',
        ];
        $validate = new Validate([
            'name'  => 'require|length:2,20',
            'code'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('classcity');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'classname' => $info['name'],
            'code' => $info['code'],
            'parentCode' => $info['parentcode'],
            'Flag'=>1,
        ];

        $ok = $role_table->field('classname,code,parentCode,Flag')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){
        //前台先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $have = Db::name('classcity')->field('id,classname')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此专业','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

        $info = input('post.');

        $msg  =   [
            'name.require' => '区域名称不能为空',
            'name.length' => '区域名称长度太短',
            'code.require' => '区域代码不能为空',
        ];
        $validate = new Validate([
            'name'  => 'require|length:2,20',
            'code'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('classcity');

        $id = $info['rid']+0;

        $have = $role_table->field('id,code')->where("id <> $id AND code={$info['code']}")->find();

        if($have){
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'classname' => $info['name'],
            'code' => $info['code'],
            'parentCode' => $info['parentcode'],
            'Flag'=>1,
        ];

        $ok = $role_table->field('name,code,parentCode,Flag')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

}