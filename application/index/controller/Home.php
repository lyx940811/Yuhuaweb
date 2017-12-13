<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
class Home extends Controller
{
    protected $access_token = ACCESS_TOKEN;
    protected $token;
    protected $data;
    protected $codeMessage;

    protected $LogicLogin;
    protected $LogicUser;
    protected $LogicCourse;

    public function __construct()
    {
        parent::__construct();

        //define ajax return message
        $this->codeMessage = Config::get('apicode_message');

        //define token
        $this->token = $this->request->param('token');

//        $this->verifyToken();

        //controller from dir logic
        $this->LogicLogin  = Loader::controller('Login','logic');
        $this->LogicUser   = Loader::controller('User','logic');
        $this->LogicCourse = Loader::controller('Course','logic');

        //unset the token which in the post data
        if($this->request->param()){
            $this->data = $this->request->param();
            unset($this->data['token']);
        }
    }

    //ajax token verifiy
    public function verifyToken(){
        if($this->access_token!=$this->token){
            exit(json_encode(json_data(900,$this->codeMessage[900],'')));
        }
    }





}
