<?php
namespace app\manage\controller;
use app\manage\model\User;
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

    public function login($nickname,$password,$captcha){

        if(!captcha_check($captcha)){
            //验证码错误
            $this->error('验证码错误');
        }
        //错误信息提示
        $msg  =   [
            'nickname.require' => '登录账号不能为空',
            'password.require' => '密码不能为空',
        ];

        $validate = new Validate([
            'nickname'  => 'require|length:2,50|token', //我这里的token是令牌验证
            'password'   => 'require',
        ],$msg);

        $info = input('post.');//接收值
        $validate->check($info);
        $error = $validate->getError();//打印错误规则

        if(!empty($error)){
            $this->error($error);
        }

        $user = User::get(['nickname'=>$nickname]);//取出的数据

        if($user){

            if(password_verify($password,$user->password)){
                //success
                session('admin_uid',$user->id);
                session('admin_name',$user->nickname);
                print_r($_SESSION);

            }else{

                $this->error('密码错误');

            }
        }else{
            $this->error('请输入正确的用户名');
        }



    }



    public function findPwd(){

        return view();

    }

    public function findPwdDo($captcha){

        if(!captcha_check($captcha)){
            //验证码错误
            $this->error('验证码错误');
        }
        //错误信息提示
        $msg  =   [
            'nickname.require' => '登录账号不能为空',
            'email.require' => '邮箱不能为空',
        ];

        $validate = new Validate([
            'nickname'  => 'require|token', //我这里的token是令牌验证
            'email'   => 'require|email',
        ],$msg);

        $info = input('post.');
        $validate->check($info);
        $error = $validate->getError();//打印错误规则

        if(!empty($error)){
            $this->error($error);
        }

        $result = Db::table('user')->where('id',2)->find();

        $content = 'http://www.baidu.com';
        $ok = send_email($result['email'],'测试发邮件',$content);
        if($ok){


            $data = ['' => 'bar', 'bar' => 'foo'];
            Db::table('think_user')->insert($data);
            $this->success('发送邮件成功，请注意查收');
        }else{
            $this->error('发送邮件失败');
        }

    }


}