<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/8
 * Time: 17:19
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class User extends Base{


    public function index(){

        $info=input('get.');
        $data['type']='';
        $data['status']='';
        $data['name']='';
        $where='';
        if(!empty($info['type'])){
            $data['type']=$info['type'];
            $where['a.type']=$info['type']-1;//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['status'])){
            $data['status']=$info['status'];
            $where['a.status']=$info['status']-1;
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['username']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
        }

        $lists = Db::name('user a')
            ->join('role b','a.roles=b.id','LEFT')
            ->field('a.id,a.username,a.nickname,a.title,a.mobile,a.locked,a.mobile,a.roles,a.type,a.email,a.createdIp,a.createdTime,a.status,b.name')
            ->where($where)
            ->order('id desc')->paginate(20,false,['query'=>request()->get()]);


        $roles = Db::name('role')->field('id,name,code')->select();
        $types = [['id'=>0,'name'=>'超级管理员'],['id'=>'1','name'=>'管理员'],['id'=>'2','name'=>'教师'],['id'=>'3','name'=>'学员'],['id'=>'4','name'=>'企业']];

        $this->assign('list', $lists);
        $this->assign('roles', $roles);
        $this->assign('types', $types);
        $this->assign('info',$data);
        $this->assign('typename','用户列表');
        $this->assign('page', $lists->render());
        return $this->fetch('index');
    }


    //添加用户
    public function add(){

        $info = input('post.');

        //错误信息提示
        $msg  =   [
            'username.require' => '用户名不能为空',
            'username.length' => '用户名的长度不符合',
            'nickname.require' => '用户昵称不能为空',
            'nickname.length' => '用户昵称的长度不符合',
            'card.require' => '身份证不能为空',
            'type.require' => '用户类型不能为空',
        ];

        $validate = new Validate([
            'username'  => 'require|length:2,20',
            'nickname'  => 'require|length:2,20',
            'card'=>'require',
            'type'=>'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $user_table = Db::name('user');

        $is_user= $user_table->field('username')->where('username',$info['username'])
            ->whereOr('mobile',$info['mobile'])->find();

        if($is_user){
            return ['error'=>'用户名已存在','code'=>'300'];
        }
        $data['username'] = $info['username'];
        $data['nickname'] = $info['nickname'];
        $data['password'] = password_hash('123456',PASSWORD_DEFAULT);
        $data['email'] = $info['email'];
        $data['mobile'] = $info['mobile'];
        $data['type'] = $info['type'];
        $data['roles'] = isset($info['roles'])?$info['roles']:0;
        $data['locked'] = isset($info['user_locked'])?$info['user_locked']:0;
        $data['status'] = isset($info['status'])?$info['status']:0;
        if(!empty($info['userpic'])){
            $data['title'] = $info['userpic'];
        }else{
            $data['title'] ="static/index/images/avatar.png";
        }

        $data['createdIp'] = request()->ip();
        $data['createdTime'] = date('Y-m-d H:i:s' ,time());
        $data['createUserID'] = session('admin_uid');
        Db::startTrans();
        $ok = $user_table->insert($data);

        if($ok){
            $userid = $user_table->getLastInsID();
            if($info['type']==3){//3为学生
                $sdata['userid'] = $userid;
                $sdata['realname'] = $info['nickname'];
                $sdata['mobile'] = $info['mobile'];
                $sdata['idcard']=$info['card'];
                $sdata['cardpic']=serialize(['front_pic'=>'','behind_pic'=>'']);
                $sdata['createdTime'] = date('Y-m-d H:i:s' ,time());
                Db::table('user_profile')->insert($sdata);

            }elseif($info['type']==2){//2为教师

                $sdata['userid'] = $userid;
                $sdata['realname'] = $info['nickname'];
                $sdata['mobile'] = $info['mobile'];
                $sdata['idcard']=$info['card'];
                $sdata['cardpic']=serialize(['front_pic'=>'','behind_pic'=>'']);
                $sdata['createdTime'] = date('Y-m-d H:i:s' ,time());
                Db::table('teacher_info')->insert($sdata);
            }


            manage_log('101','003','添加用户',serialize($info),0);
            Db::commit();
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'添加失败','code'=>'400'];
        }


    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'username.require' => '用户名不能为空',
            'username.length' => '用户名的长度不符合',
            'nickname.require' => '用户昵称不能为空',
            'nickname.length' => '用户昵称的长度不符合',
            'type.require' => '用户类型不能为空',
        ];

        $validate = new Validate([
            'username'  => 'require|length:2,20',
            'nickname'  => 'require|length:2,20',
            'type'=>'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $id = $info['rid']+0;

        $user_table = Db::name('user');

        $where['username']=$info['username'];
        $where['id']=array('neq',$id);
        $is_user= $user_table->field('id,username')->where($where)->find();

        if($is_user){
            return ['error'=>'用户名已存在','code'=>'300'];
        }

        $have = $user_table->field('id')->where("id",$id)->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此用户','code'=>'300'];
        }

        $data['username'] = $info['username'];
        $data['nickname'] = $info['nickname'];
        $data['type'] = $info['type'];
        if(!empty($info['userpic'])){
            $data['title'] = $info['userpic'];
        }else{
            $data['title'] ="static/index/images/avatar.png";
        }

        $data['email'] = $info['email'];
        $data['mobile'] = $info['mobile'];
        $data['roles'] = isset($info['roles'])?$info['roles']:0;
        $data['status'] = isset($info['status'])?$info['status']:0;
        $data['locked'] = isset($info['user_locked'])?$info['user_locked']:0;
        Db::startTrans();
        $ok = $user_table->where('id',$id)->update($data);

        if($ok){

            if($info['type']==3){
                //3为学员
                $sdata = [
                    'mobile'=>$info['mobile'],
                    'realname'=>$info['nickname']
                ];
                Db::table('user_profile')->where('userid',$id)->update($sdata);
            }elseif($info['type']==2){
                //2为教师
                $sdata = [
                    'mobile'=>$info['mobile'],
                    'realname'=>$info['nickname']
                ];
                Db::table('teacher_info')->where('userid',$id)->update($sdata);

            }

            Db::commit();
            manage_log('101','004','修改用户',serialize($info),0);

            return ['info'=>'修改成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('user')->where("id",$id)->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    public function upload(){

        $id = $_GET['id']+0;
        $file = new Upload();
        $res = $file->uploadPic($_FILES,'teacherinfo');

        $res['path'] = $res['newfile'.$id]['path'];
        $res['code'] = $res['newfile'.$id]['code'];
        return $res;
    }

}