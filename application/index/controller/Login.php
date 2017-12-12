<?php
namespace app\index\controller;

use think\Loader;
use think\Config;
use app\index\model\User;

/**
 * Class Login
 * @package app\index\controller
 * 用户登录注册模块
 */

class Login extends Home
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 注册
     */
    public function register(){
        $data = $this->data;
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
        return json_encode($result);
    }


}
