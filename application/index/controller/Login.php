<?php
namespace app\index\controller;

use think\Loader;
use think\Config;
use app\index\model\User;
class Login extends Home
{
    protected $LogicLogin;

    public function __construct()
    {
        parent::__construct();
        //controller from app/index/logic/login.php
        $this->LogicLogin = Loader::controller('Login','logic');
    }

    /**
     * 注册
     */
    public function register(){
        //verified token
        $token = $this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if token passed
            $data = $this->request->param();
            $regiUser = [
                'email'     =>  $data['email'],
                'nickname'  =>  $data['nickname'],
                'password'  =>  $data['password'],
                'createdIp' =>  $this->request->ip(),
            ];
            $result = $this->LogicLogin->userAdd($regiUser);
            return $result;
        }
        else{
            //token verified error
            return json_data(900,$this->codeMessage[900],'');
        }
    }
    /**
     * 登陆
     */
    public function login(){
        //verified token
        $token = $this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if token passed
            $data   = $this->request->param();
            $result = $this->LogicLogin->userLogin($data);
            return $result;
        }
        else{
            //token verified error
            return json_data(900,$this->codeMessage[900],'');
        }
    }

    /**
     * 输入邮箱，发送重置密码页面邮件
     */
    public function sendchemail(){
        //verified token
        $token = $this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if token passed
            $email = $this->request->param('email');
            $result = $this->LogicLogin->sendChEmail($email);
            return $result;
        }
        else{
            //token verified error
            return json_data(900,$this->codeMessage[900],'');
        }
    }

    /**
     * 重置密码
     */
    public function chpassword(){
        //verified token
        $token = $this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if token passed
            $data = [
                'email'     =>  $this->request->param('email'),
                'password'  =>  $this->request->param('password'),
            ];
            $data['email'] = base64_decode($data['email']);
            $result = $this->LogicLogin->ChUserPassword($data);
            return $result;
        }
        else{
            //token verified error
            return json_data(900,$this->codeMessage[900],'');
        }
    }


}
