<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/14
 * Time: 15:18
 */
namespace app\manage\controller;

use think\Db;
use think\paginator\driver\Bootstrap;
use think\Validate;

class Role extends Base{


    public function index(){

        $lists = Db::name('role')->field('id,name,code,parentcode,createdUserId,createdTime')->order('id asc')->select();

        $treeL = tree($lists);

        $curpage = input('page') ? input('page') : 1;//当前第x页，有效值为：1,2,3,4,5...

        $listRow = 20;//每页2行记录

        $showdata = array_chunk($treeL, $listRow, true);

        $p = Bootstrap::make($showdata, $listRow, $curpage, count($treeL), false, [
            'var_page' => 'page',
            'path'     => url(),//这里根据需要修改url
            'query'    => [],
            'fragment' => '',
        ]);

        $p->appends($_GET);

        $this->assign('list', $p[$curpage-1]);
        $this->assign('page', $p->render());
        return $this->fetch('index');
    }

    //添加角色
    public function add(){

        $info = input('post.');

        //错误信息提示
        $msg  =   [
            'name.require' => '角色名称不能为空',
            'name.length' => '角色名称长度太短',
            'code.require' => '代码不能为空',
        ];

        $validate = new Validate([
            'name'  => 'require|length:2,20', //我这里的token是令牌验证
            'code'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('role');
        $is_have = $role_table->field('id')->where("code='{$info['code']}'")->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data['name'] = $info['name'];
        $data['code'] = $info['code'];
        $data['data'] = $info['name'];
        $data['createdUserId'] = session('admin_uid');
        $data['createdTime'] = date('Y-m-d H:i:s',time());
        $data['flag'] = 1;
        $data['parentcode'] = empty($info['parentcode'])?0:$info['parentcode'];

        $ok = $role_table->field('name,code,data,createdUserId,createdTime,flag,parentcode')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }


    }

    public function edit(){

        $info = input('post.');

        $msg  =   [
            'rid.require' => '角色rid不能为空',
            'name.require' => '角色名称不能为空',
            'name.length' => '角色名称长度太短',
            'code.require' => '代码不能为空',
            'code.number' => '代码必须为数字',
        ];

        $validate = new Validate([
            'rid'  => 'require',
            'name'  => 'require|length:2,20',
            'code'   => 'require|number',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $role_table = Db::name('role');

        $id = $info['rid']+0;
        $have = $role_table->field('id,code')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此角色','code'=>'300'];
        }

        if($have['code']==$info['code']){

            $ok = $role_table->field('name,data')->where('id',$id)->update(['name' => $info['name'],'data'=>$info['name']]);
        }else{

            $where['id'] = ['neq',$id];
            $where['code'] = $info['code'];

            $have = $role_table->field('id')->where($where)->find();

            if($have){
                return ['error'=>'已经有此代码','code'=>'300'];
            }

            $ok = $role_table->field('name,data,code')->where('id',$id)->update(['name' => $info['name'],'data'=>$info['name'],'code'=>$info['code']]);
        }


        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('role')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }


}