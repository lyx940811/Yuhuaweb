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


class Companysize extends Base{

    public function index(){

        $list = Db::table('companysize')->field('id,classname,code,flag')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','企业规模');
        return $this->fetch();
    }

    public function add(){
        $info = input('get.');

        $msg  =   [
            'name.require' => '名称不能为空',
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


        $role_table = Db::name('companysize');

        $is_have = $role_table->field('id,code')->where(['code'=>['eq',$info['code']]])->find();

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

            manage_log('104','003','添加企业规模',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){
        $info = input('get.');

        $msg  =   [
            'name.require' => '名称不能为空',
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


        $role_table = Db::name('companysize');
        $id = $info['rid']+0;
//        $is_have = $role_table->field('id,code')->where(['code'=>['eq',$info['code']]])->find();
        $where['code']=$info['code'];
        $where['id']=array('neq',$id);
        $is_have = $role_table->field('id,code')->where($where)->find();

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

            manage_log('104','003','添加企业规模',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function enable(){
        $id = $_GET['rid']+0;

        $ok = Db::name('companysize')->where("id='$id'")->delete();

        if(is_numeric($ok)){
            manage_log('108','005','删除课程',serialize(['id'=>$id]),0);
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}