<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 10:58
 */
namespace app\manage\controller;

use think\Db;
use think\validate;

class Classcourse extends Base{

    public function index(){
        $info=input('get.');
        $data['flag']='';
        $data['code']='';
        $data['name']='';
        $where='';
        if(!empty($info['code'])){
            $data['code']=$info['code'];
            $where['code']=$info['code'];//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['flag'])){
            $data['flag']=$info['flag'];
            $where['flag']=$info['flag']-1;
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['classname']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
        }
        $list = Db::table('classcourse')->field('id,classname,code,flag')->where($where)->paginate(20,false,['query'=>request()->get()]);

        $this->assign('info',$data);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','课程类型');
        return $this->fetch();
    }

    public function add(){
        $info = input('get.');

        $msg  =   [
            'classname.require' => '类型名称不能为空',
            'code.require' => '类型代码不能为空',
        ];
        $validate = new Validate([
            'classname'  => 'require',
            'code'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('classcourse');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'classname'         => $info['classname'],
            'code'      => $info['code'],
            'Flag'          => $info['flag'],
        ];

        $ok = $role_table->field('classname,code,Flag')->insert($data);
        if(is_numeric($ok)){
            manage_log('108','003','添加课程',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){
        $info = input('get.');

        $msg  =   [
            'classname.require' => '类型名称不能为空',
            'code.require' => '类型代码不能为空',
        ];
        $validate = new Validate([
            'classname'  => 'require',
            'code'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('classcourse');
        $id=$info['rid']+0;
        $where['id']=array('neq',$id);
        $where['code']=$info['code'];
        $is_have = $role_table->field('id')->where($where)->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'classname'         => $info['classname'],
            'code'      => $info['code'],
            'Flag'          => $info['flag'],
        ];

        $ok = $role_table->field('classname,code,Flag')->where('id',$id)->update($data);
        if(is_numeric($ok)){
            manage_log('108','003','添加课程',serialize($data),0);
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'400'];
        }
    }

    public function delete(){
        $id = $_GET['rid']+0;
        $ok = Db::name('classcourse')->where('id',$id)->delete();

        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}