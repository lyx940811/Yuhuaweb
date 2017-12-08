<?php
namespace app\manage\controller;
use app\manage\model\User;
use think\Controller;
use think\Validate;

/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/6
 * Time: 11:14
 */
//登陆类
class Login extends Controller{

    protected $rule =   [
        'nickname'  => 'require|length:2,50|token', //我这里的token 是令牌验证
        'password'   => 'require',
    ];

    //登陆首页
    public function index(){

        return view('index');

    }

    public function login($nickname,$password,$captcha){

        echo password_hash('123456');
        $info = input('post.');//接收值

        if(!captcha_check($captcha)){
            //验证码错误
            $this->error('验证码错误');
        }
        //错误信息提示
        $msg  =   [
            'nickname.require' => '登录账号不能为空',
            'password.require' => '密码不能为空',
        ];
        $validate = new Validate($this->rule,$msg);
        $validate->check($info);
        $error = $validate->getError();//打印错误规则

        if(!empty($error)){
            $this->error($error);
        }

        $user = User::get(['nickname'=>$nickname,'password'=>$password]);//取出的数据

        if(!$user){
            $this->error('请输入正确的用户和密码');
        }else{
            //success
            $where['nickname'] = $nickname;
            $where['password'] = $password;
            $result = Db::name('user')->where($where)->find();
            print_r($result);
        }

    }


}