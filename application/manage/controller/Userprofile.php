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

class Userprofile extends Base{

    public function index(){

        $info = input('get.');

        $where = [];

        if(!empty($info['username'])){
            $where['a.realname'] = ['like',"%{$info['username']}%"];
        }

        $list = Db::table('user_profile a')
            ->join('user b','a.userid=b.id','LEFT')
            ->field('a.id,a.userid,a.realname,a.sex,a.age,a.mobile,a.createdTime,b.username')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $user = Db::table('user')->field('id,username')->select();
        $this->assign('list',$list);
        $this->assign('user',$user);
        $this->assign('typename','学生列表');
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'userid.require' => '请选择用户',
            'realname.require' => '真实性别不能为空',
            'mobile.number' => '手机方式错误',
            'mobile.between' => '手机太短',
        ];
        $validate = new Validate([
            'userid'  => 'require',
            'realname'   => 'require',
            'mobile'   => 'number|between:1,12',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('user_profile');

        $have = $role_table->field('id')->where("userid={$info['userid']}")->find();

        if($have){
            return ['error'=>'已经有此学生信息','code'=>'300'];
        }

        $data = [
            'userid' => $info['userid'],
            'realname' => $info['realname'],
            'mobile'=> $info['mobile'],
            'sex'=>$info['sex'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('userid,realname,mobile,sex,createdTime')->insert($data);

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

            $have = Db::name('user_profile')->field('id,userid,realname,sex,mobile')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此专业','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

        $info = input('post.');

        $msg  =   [
            'rid.require' => '学生rid不能为空',
            'userid.require' => '请选择用户',
            'realname.require' => '真实性别不能为空',
            'mobile.number' => '手机方式错误',
            'mobile.between' => '手机太短',
        ];
        $validate = new Validate([
            'rid'    => 'require',
            'userid'  => 'require',
            'realname'   => 'require',
            'mobile'   => 'number|between:1,12',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('user_profile');

        $id = $info['rid']+0;

        $data = [
            'userid' => $info['userid'],
            'realname' => $info['realname'],
            'mobile'=> $info['mobile'],
            'sex'=>$info['sex'],
//            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('userid,realname,mobile,sex')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('user_profile')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }


}