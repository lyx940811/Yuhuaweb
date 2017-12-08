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
        //if token passed
        $this->data['createdIp'] = $this->request->ip();
        $result = $this->LogicLogin->userAdd($this->data);
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
        //if token passed
        $result = $this->LogicLogin->sendChEmail($this->data);
        return $result;
    }

    /**
     * 重置密码
     */
    public function chpassword(){

        $result = $this->LogicLogin->ChUserPassword($this->data);
        return $result;

    }


}
