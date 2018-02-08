<?php
namespace app\manage\controller;
use app\manage\model\User;
use think\captcha\Captcha;
use think\Controller;
use think\Validate;
use think\Db;

/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/6
 * Time: 11:14
 */
//登陆类
class Login extends Controller{

    //登陆首页
    public function index(){

        return view('index');

    }

    public function login(){

        $info = input('post.');//接收值

        if(!captcha_check($info['captcha'])){
            //验证码错误
            return ['error'=>'验证码错误','code'=>200];
        }
        //错误信息提示
        $msg  =   [
            'username.require' => '登录账号不能为空',
            'password.require' => '密码不能为空',
        ];

        $validate = new Validate([
            'username'  => 'require|length:2,50', //我这里的token是令牌验证
            'password'   => 'require',
        ],$msg);

        $validate->check($info);
        $error = $validate->getError();//打印错误规则

        if(!empty($error)){
            return ['error'=>$error,'code'=>300];
        }

        $user = User::get(['username'=>$info['username']]);//取出的数据

        $userrole = !empty($user->roles)?$user->roles:0;


        if($user['status']==0){
            return ['error'=>'未审核用户','code'=>500];
        }elseif($user){

            if(password_verify($info['password'],$user->password)){
                //success
                session('admin_uid',$user->id);
                session('admin_name',$user->username);
                $role = Db::table('role')->field('name')->where('id='.$userrole)->find();

                session('admin_role',!empty($role['name'])?$role['name']:'其他');

                manage_log('100','008','用户登陆',serialize($info),0);
                //增加登陆日志
                Db::name('user_login_log')->insert(['userid'=>$user['id'],'LoginTime'=>time(),'ip'=>$this->request->ip(),'province'=>getAddressByIp($this->request->ip())]);

                return ['info'=>'登陆成功','code'=>'000','url'=>url('Manage/manage/index')];

            }else{

                return ['error'=>'密码错误','code'=>400];

            }
        }else{
            return ['error'=>'请输入正确的用户名','code'=>600];
        }


    }



    public function findPwd(){

        return view();

    }

    public function findPwdDo($email,$captcha){

        if(!captcha_check($captcha)){
            //验证码错误
            $this->error('验证码错误');
        }
        //错误信息提示
        $msg  =   [
            'email.require' => '邮箱不能为空',
        ];

        $validate = new Validate([
            'email'   => 'require|email|token',
        ],$msg);

        $info = input('post.');
        $validate->check($info);
        $error = $validate->getError();//打印错误规则

        if(!empty($error)){
            $this->error($error);
        }

        $result = Db::table('user')->where('email',$email)->field('id,username,password,email')->find();

        if($result){

//            $code = md5($result['username']+$result['password']+'ygs');
            $code = md5code($result['username'],$result['password']);
            $expire = strtotime('+1 hour');

            $data = ['uid' => $result['id'],'email'=>$result['email'],'code'=>$code,'time'=>time(),'expire'=>$expire];

            $insert_data = Db::table('mail_log')->insert($data);

            if($insert_data){

                $content = '<a href="'.url('Manage/login/resetPwd?e='.$result['email'].'&p='.$code,'','html',true).'">验证链接</a>';

                $ok = send_email($result['email'],'测试发邮件',$content);
                if($ok){

                    $this->success('发送邮件成功，请注意查收');
                }else{

                    $this->error('发送邮件失败',url('Manage/login/index'));
                }
            }

        }else{

            $this->error('没有此用户');
        }


    }

    public function resetPwd($e,$p){

        $expire = Db::table('mail_log')->where('email',$e)->field('expire')->order('expire desc')->find();

        if(!$expire){
            $this->error('没有此用户',url('Manage/login/index'));
        }

        if( $expire['expire']<time()){
            $this->error('验证码过期',url('Manage/login/index'));
        }

        $result = Db::table('user')->where('email',$e)->field('username,password')->find();
        $old = md5code($result['username'],$result['password']);

        if($p == $old){
            return view('resetPwd');
        }else{
            $this->error('验证码过期',url('Manage/login/index'));
        }

    }

    /*
     * 重置密码
     */
    public function resetPwdDo($password,$newpassword,$email){
        $msg  =   [
            'password.require' => '密码不能为空',
            'newpassword.require' => '确认密码不能为空',
        ];

        $validate = new Validate([
            'password'   => 'require',
            'newpassword'   => 'require',
        ],$msg);

        $info = input('post.');
        $validate->check($info);
        $error = $validate->getError();//打印错误规则

        if(!empty($error)){
            $this->error($error);
        }

        if($password!=$newpassword){
            $this->error('密码不一样');
        }

        $result = Db::table('user')->where('email',$email)->field('id')->find();

        if(!$result){
            $this->error('没有此用户');
        }else{

            $pwd_hash = password_hash($password, PASSWORD_DEFAULT);
            $ok = Db::table('user')->where('id',$result['id'])->update(['password'=>$pwd_hash]);

            if($ok){
                $this->success('修改成功',url('Manage/login/index'));
            }else{
                $this->error('修改失败');
            }
        }

    }



    public function logout(){
        $userid = session('admin_uid');
        manage_log('999','009','用户登出',serialize(['userid'=>$userid]),0);

        session('admin_uid',NULL);
        session('admin_user',NULL);
//        $this->success('退出成功',url('Manage/login/index'));

        return ['info'=>'退出成功','code'=>'000'];
    }


    public function capcha_show(){
        $captcha = new Captcha();
        $captcha->fontSize = 30;
        $captcha->length   = 4;
        $captcha->useNoise = true;
        return $captcha->entry();
    }

}