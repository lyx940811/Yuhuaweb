<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 12:01
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Tag extends Base{

    public function index(){
        $info=input('get.');
        $data['flag']='';
        $data['code']='';
        $data['name']='';
        $where='';
        if(!empty($info['flag'])){
            $data['flag']=$info['flag'];
            $where['Flag']=$info['flag']-1;//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['code'])){
            $data['code']=$info['code'];
            $where['code']=$info['code'];
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['name']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
        }

        $list = Db::table('tag')->field('id,name,code,roles,Flag,userid')->where($where)->order('createdTime desc')->paginate(20,false,['query'=>request()->get()]);

        $this->assign('info',$data);
        $this->assign('list',$list);
        $this->assign('typename','标签');
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    public function add(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '标签名称不能为空',
            'name.length' => '标签名称长度太短',
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

        $role_table = Db::name('tag');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'name' => $info['name'],
            'code' => $info['code'],
            'roles'=> $info['role'],
            'userid'=> session('admin_uid'),
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'Flag'=>$info['Flag'],
        ];

        $ok = $role_table->field('name,code,roles,userid,createdTime,Flag')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){

        $info = input('post.');

        $msg  =   [
            'name.require' => '标签名称不能为空',
            'name.length' => '标签名称长度太短',
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

        $role_table = Db::name('tag');

        $id = $info['rid']+0;
        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此标签','code'=>'300'];
        }

        $have = $role_table->field('id,code')->where("id <> $id AND code={$info['code']}")->find();

        if($have){
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'name'=>$info['name'],
            'code'=>$info['code'],
            'roles'=>$info['role'],
            'Flag'=>$info['Flag'],
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if(is_numeric($ok)){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('tag')->where("id='$id'")->delete();

        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}