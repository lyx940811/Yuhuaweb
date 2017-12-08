<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
class Home extends Controller
{
    protected $access_token = ACCESS_TOKEN;
    protected $token;
    protected $data;
    protected $codeMessage;
    protected $LogicLogin;
    protected $LogicUser;

    public function __construct()
    {
        parent::__construct();

        //define ajax return message
        $this->codeMessage = Config::get('apicode_message');

        //define token
        $this->token = $this->request->param('token');
        $this->verifyToken();

        //controller from dir logic
        $this->LogicLogin = Loader::controller('Login','logic');
        $this->LogicUser = Loader::controller('User','logic');


        if($this->request->param()){
            $this->data = $this->request->param();
            unset($this->data['token']);
        }
    }

    public function verifyToken(){
        if($this->access_token!=$this->token){
            exit(json_encode(json_data(900,$this->codeMessage[900],'')));
        }
    }

}
