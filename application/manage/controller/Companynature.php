<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 10:30
 */
namespace app\manage\controller;
use think\Db;
use think\Validate;

class Companynature extends Base{

    public function index(){

        $list = Db::table('companynature')->field('id,classname,code,flag')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','企业性质');
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '区域名称不能为空',
            'code.require' => '代码不能为空',
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


        $role_table = Db::name('companynature');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'classname' => $info['name'],
            'code' => $info['code'],
            'flag'=>$info['flag'],
        ];

        $ok = $role_table->insert($data);

        if($ok){

            manage_log('104','003','添加广告',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '区域名称不能为空',
            'code.require' => '代码不能为空',
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


        $role_table = Db::name('companynature');
        $id = $info['rid']+0;
        $is_have = $role_table->field('id')->where("id <> $id AND code={$info['code']}")->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'classname' => $info['name'],
            'code' => $info['code'],
            'flag'=>$info['flag'],
        ];
        $ok = $role_table->where('id',$id)->update($data);
        if(is_numeric($ok)){

            manage_log('104','003','添加广告',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

}