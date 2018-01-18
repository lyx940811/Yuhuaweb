<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 10:58
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Tasktype extends Base{

    public function index(){

        $list = Db::table('task_type')->field('id,name,code,Flag,seq')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','任务类型');
        return $this->fetch();
    }
    public function add(){
        $info = input('get.');

        $msg  =   [
            'name.require' => '类型名称不能为空',
            'code.require' => '类型代码不能为空',
            'seq.require' => '排序不能为空',
        ];
        $validate = new Validate([
            'name'  => 'require',
            'code'   => 'require',
            'seq'   =>'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('task_type');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }
        $is_seq = $role_table->field('id')->where(['seq'=>['eq',$info['seq']]])->find();
        if($is_seq){
            return ['error'=>'已经有此排序','code'=>'300'];
        }
        $data = [
            'name'         => $info['name'],
            'code'      => $info['code'],
            'seq'          => $info['seq'],
            'Flag'          => $info['flag'],
        ];

        $ok = $role_table->field('name,code,Flag,seq')->insert($data);
        if(is_numeric($ok)){
            manage_log('108','003','添加任务类型',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){
        $info = input('get.');

        $msg  =   [
            'name.require' => '类型名称不能为空',
            'code.require' => '类型代码不能为空',
            'seq.require' => '排序不能为空',
        ];
        $validate = new Validate([
            'name'  => 'require',
            'code'   => 'require',
            'seq'   =>'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('task_type');

        $id=$info['rid']+0;
        $where['id']=array('neq',$id);
        $where['code']=$info['code'];
        $is_have = $role_table->field('id')->where($where)->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }
        $where1['id']=array('neq',$id);
        $where1['seq']=$info['seq'];
        $is_seq = $role_table->field('id')->where($where1)->find();
        if($is_seq){
            return ['error'=>'已经有此排序','code'=>'300'];
        }
        $data = [
            'name'         => $info['name'],
            'code'      => $info['code'],
            'seq'          => $info['seq'],
            'Flag'          => $info['flag'],
        ];
        $ok = $role_table->field('name,code,Flag,seq')->where('id',$id)->update($data);
        if(is_numeric($ok)){
            manage_log('108','003','修改任务类型',serialize($data),0);
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'400'];
        }
    }

    public function enable(){
        $id = $_GET['rid']+0;
        $ok = Db::name('task_type')->where('id',$id)->delete();

        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}