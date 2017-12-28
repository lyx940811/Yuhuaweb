<?php
namespace app\api\controller;

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
    protected $LogicLog;

    public function __construct()
    {
        parent::__construct();

        //define ajax return message
        $this->codeMessage = Config::get('apicode_message');

        //controller from dir logic
        $this->LogicLog  = Loader::controller('Log','logic');
        //define data
        $this->data = $this->request->param();
        //unset the token which in the post data
        if($this->request->param('token')){
            //verify token



            unset($this->data['token']);
        }
    }

    //ajax token verifiy
    protected function verifyToken(){
        if($this->access_token!=$this->token){
            exit(json_encode(json_data(900,$this->codeMessage[900],'')));
        }
    }

    public function getusertoken(){

        $LogicUser   = Loader::controller('User','logic');
        $data = [
            'username'  =>  '312850391@qq.com',//$this->request->param('username'),
            'password'  =>  123456,//$this->request->param('password'),
        ];
        $res = $LogicUser->getusertoken($data);

        return $res;

    }





}
