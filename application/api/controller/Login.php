<?php
namespace app\api\controller;


use think\Loader;
use app\api\model\User;
class Login extends Home
{
    /**
     * 注册
     */
    public function register(){
        //verified token
        $token = $this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if pass token
            $data = $this->request->param();

            //逻辑层实例化，调用其中的方法来获取数据
            $user   = Loader::controller('Login','logic');
            $result = $user->userAdd($data);
            return $result;
        }
        else{
            //token verified error
            return json_data(900,'');
        }
    }
    /**
     * 登陆
     */
    public function login(){
        //verified token
        $token = $this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if pass token
            $data   = $this->request->param();
            $user   = Loader::controller('Login','logic');
            $result = $user->userLogin($data);
            return $result;
        }
        else{
            //token verified error
            return json_data(900,'');
        }
    }

    /**
     * 输入邮箱，发送重置密码页面邮件
     */
    public function sendchemail(){

    }

    /**
     * 重置密码
     */
    public function chemail(){

    }


}
