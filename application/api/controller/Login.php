<?php
namespace app\api\controller;

use think\Loader;
use think\captcha\Captcha;
use app\index\model\User as UserModel;
use app\index\model\UserProfile;
/**
 * Class Login
 * @package app\index\controller
 * 用户登录注册模块     迁移至User中
 */

class Login extends Home
{
    protected $LogicLogin;
    public function __construct()
    {
        parent::__construct();
        $this->LogicLogin  = Loader::controller('Login','logic');
    }

    /**
     * 注册
     */
    public function register(){

        $data = $this->data;

        $code = $data['captcha'];
        $id   = $data['captchaid'];
        $captcha = new Captcha();
        if(!$captcha->check($code,$id)){
            return json_data(920,$this->codeMessage[920],'');
        }
        unset($data['captcha'],$data['captchaid']);

        $data['createdIp'] = $this->request->ip();
        $result = $this->LogicLogin->userAdd($data);
        return $result;
    }

    /**
     * 登陆
     */
    public function login(){
        //if token passed
        $result = $this->LogicLogin->userLogin($this->data);
        return $result;
    }

    /**
     * 输入邮箱，发送重置密码页面邮件
     */
    public function sendchemail(){

        $email = $this->data['email'];
        $result = $this->LogicLogin->sendChEmail($email);
        return $result;
    }

    /**
     * 重置密码
     */
    public function chpassword(){
        $data = $this->data;
        if($data['password']!=$data['repassword']){
            return json_data(130,'两次输入密码不一致！','');
        }

        $email    = base64_decode($data['email']);
        $password = $data['password'];
        $result = $this->LogicLogin->ChUserPassword($email,$password);
        return $result;
    }

    /**
     * 发送验证码
     */
    public function sendcode()
    {
        $username = $this->data['username'];

        $user = $this->getuser($username);

        if(!empty($user)){
          //send text
            $PluginController = controller('Plugin');
            $res = $PluginController->sendtext($user['mobile']);
            if($res['code']==0){
                return json_data(0,$this->codeMessage[0],'');
            }else{
                // send text error
                return json_data(1100,$this->codeMessage[1100],'');
            }
        } else{
            //error not find the user
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    /**
     * 找回密码
     */
    public function resetpwd()
    {
        $data = $this->data;
        $username = $data['username'];
        $user = $this->getuser($username);

        if($user){
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $code = $redis->get($user['mobile']);
            if($code!=$data['code']){
                return json_data(188,$this->codeMessage[188],'');
            }
            $redis->del($user['mobile']);

            $user->password = password_hash($data['newpwd'],PASSWORD_DEFAULT);
            $user->save();

            return json_data(0,$this->codeMessage[0],'');
        }else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    /**
     * 通过emaill、手机、用户名、身份证得到用户信息
     */
    private function getuser($username)
    {
        if(preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/',$username)){
            $key = 'email';
        }
        elseif(preg_match('/^[1][3,4,5,7,8][0-9]{9}$/',$username)){
            $key = 'mobile';
        }
        else{
            $key = 'username';
        }
        if(isset($key)){
            return $user = UserModel::get([ $key => $username ]);
        }

        //身份证登陆
        if(preg_match('/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/',$username)){
            $user_profile = UserProfile::get(['idcard'=>$username]);
            return $user = UserModel::get($user_profile['userid']);
        }
    }




}
