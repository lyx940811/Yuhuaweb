<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/20
 * Time: 16:12
 */
namespace app\manage\controller;
use think\Db;
use think\Validate;

class Rolefunction extends Base{

    public function index(){

        $db_func = Db::name('function');

        $list = Db::name('role_function a')
            ->join('role b','a.rolecode=b.id','LEFT')
            ->field('a.*,b.name')
            ->paginate(20);

        $funlist = [];
        foreach ($list as $k=>$v){

            $funlist[$k] = $v;

            if($v['functioncode']){
                $funcode = trim($v['functioncode'],',');
                $funlist[$k]['groups'] = $db_func->field('id,name,url,code')->where("id in ($funcode)")->order('id asc')->select();
            }else{
                $funlist[$k]['groups'] = [];
            }

        }


        $allgroup = $db_func->field('id,code,name,url')->where('Flag=1')->select();

        $roles = Db::table('role')->select();

        $this->assign('list',$funlist);
        $this->assign('allgroup',$allgroup);
        $this->assign('roles',$roles);
        $this->assign('page',$list->render());
        $this->assign('typename','权限组管理');
        return $this->fetch();

    }

    //添加权限组
    public function add(){

        $info = input('post.');

        //错误信息提示
        $msg  =   [
            'name.require' => '权限组名称不能为空',
            'name.length' => '权限组名称长度太短',
        ];

        $validate = new Validate([
            'name'  => 'require|length:1,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('role');
//        $is_have = $role_table->field('id')->where("rolecode='{$info['rolecode']}'")->find();
//
//        if($is_have){//如果这个code有
//            return ['error'=>'已经有此代码','code'=>'300'];
//        }

        $data = [
            'name' =>$info['name'],
            'data' =>$info['data'],
            'flag' =>1,
            'createdUserId'=>session('admin_uid'),
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->insert($data);

        if($ok){
            //添加角色权限

            $sdata['functioncode'] = isset($info['functioncode'])?implode(',',$info['functioncode']):'';
            $sdata['Flag'] = 1;

            Db::table('role_function')->insert($sdata);

            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }


    }

    public function edit(){

        $info = input('post.');

        $msg  =   [
            'rolecode.require' => '权限组code不能为空',
            'rolecode.require' => '角色名称不能为空',
        ];

        $validate = new Validate([
            'rolecode'  => 'require|length:1,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $role_table = Db::name('role_function');

        $id = $info['rid']+0;

        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此角色','code'=>'300'];
        }
        $funcs = isset($info['functioncode'])?implode(',',$info['functioncode']):'';

        $ok = $role_table->field('rolecode,functioncode')->where('id',$id)->update(['rolecode' => $info['rolecode'],'functioncode'=>$funcs]);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }
    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('role_function')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}