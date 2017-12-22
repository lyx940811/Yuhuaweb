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

        $list = Db::name('role_function')->paginate(20);

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

        $allgroup = $db_func->field('id,code,name,url')->where('flag=1')->select();


        $this->assign('list',$funlist);
        $this->assign('allgroup',$allgroup);
        $this->assign('page',$list->render());
        $this->assign('typename','权限组管理');
        return $this->fetch();

    }

    //添加权限组
    public function add(){

        $info = input('post.');

        //错误信息提示
        $msg  =   [
            'role_code.require' => '权限组名称不能为空',
            'role_code.length' => '权限组名称长度太短',
        ];

        $validate = new Validate([
            'role_code'  => 'require|length:1,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('role_function');
        $is_have = $role_table->where("rolecode='{$info['role_code']}'")->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data['rolecode'] = $info['role_code'];
        $data['functioncode'] = isset($info['function_code'])?implode(',',$info['function_code']):'';
        $data['flag'] = 1;

        $ok = $role_table->field('rolecode,functioncode,flag')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }


    }

    public function edit(){

        //前端先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $role_table = Db::name('role_function');
            $func = $role_table->where("id='$id'")->find();

            if(!$func){//如果这个code有
                return ['error'=>'没有此角色','code'=>'300'];
            }else{

                if($func['functioncode']){//如果权限组里有相应权限，给前端返回，循环
                    $funccode = $func['functioncode'];
                    $func['children'] = Db::name('function')->where("id in ($funccode)")->select();
                }

                return ['info'=>$func,'code'=>'000'];
            }

        }
        //前端获取资料结束



        $info = input('post.');


        $msg  =   [
            'role_code.require' => '权限组code不能为空',
            'role_code.require' => '角色名称不能为空',
        ];

        $validate = new Validate([
            'role_code'  => 'require|length:1,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $role_table = Db::name('role_function');

        $id = $info['rid'];

        $have = $role_table->field('id,rolecode')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此角色','code'=>'300'];
        }
        $funcs = isset($info['function_code'])?implode(',',$info['function_code']):'';

        $ok = $role_table->field('rolecode,functioncode')->where('id',$id)->update(['rolecode' => $info['role_code'],'functioncode'=>$funcs]);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }
}