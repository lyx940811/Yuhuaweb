<?php
namespace app\index\controller;

use think\Loader;
use think\Config;
use app\index\model\User;
use think\Db;
class Course extends Home
{
    protected $LogicLogin;

    public function __construct()
    {
        parent::__construct();
        //controller from app/index/logic/login.php
        $this->LogicLogin = Loader::controller('Course','logic');


    }

    /**
     * 添加课程（教师）
     */
    public function addcourse(){
        //verified token
        $token = 123456;//$this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if token passed
            $data['email']  = '123';
            $data['nickname'] = '222';
            $data['password']       =   password_hash(123456,PASSWORD_DEFAULT);
            $data['title']          =   123;
            $data['type']           =   1;
            $data['createdTime']    =   date('Y-m-d H:i:s');
            $result = User::create($data);
        }
        else{
            //token verified error
            return json_data(900,$this->codeMessage[900],'');
        }
    }


    /**
     * 添加课程（教师）
     */
    public function addcourse2(){
        //verified token
        $token = 123456;//$this->request->param('token');
        if($token==ACCESS_TOKEN){
            //if token passed

        }
        else{
            //token verified error
            return json_data(900,$this->codeMessage[900],'');
        }
    }


}
