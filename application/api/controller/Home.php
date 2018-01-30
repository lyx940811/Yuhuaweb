<?php
namespace app\api\controller;

use app\index\model\UserProfile;
use think\Controller;
use think\Config;
use think\Loader;
use think\Db;
use app\index\model\User as UserModel;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
class Home extends Controller
{
    protected $token;
    protected $data;
    protected $codeMessage;
    protected $LogicLog;
    public    $user;

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

        $user_token = $this->request->param('user_token');
        if(!empty($user_token)){
            $this->verifyUserToken($user_token);
        }
    }

    //ajax token verifiy
    protected function verifyToken(){
        if($this->access_token!=$this->token){
            exit(json_encode(json_data(900,$this->codeMessage[900],[])));
        }
    }

    public function getusertoken(){

        $LogicUser   = Loader::controller('User','logic');
        $data = [
            'username'  =>  $this->request->param('username'),
            'password'  =>  $this->request->param('password'),
        ];
        $res = $LogicUser->getusertoken($data);

        return $res;

    }



    /**
     * 验证user，通过验证后赋值全局user信息
     * @param $user_token
     */
    protected function verifyUserToken($user_token){
        if(!$user_token){
            //没有token或token为空
            exit(json_encode(json_data(910,$this->codeMessage[910],[])));
        }

        if($user = UserModel::get(['user_token'=>$user_token])){
            //判断过期没
            if(time()>$user['expiretime']){
                //token过期
                exit(json_encode(json_data(910,$this->codeMessage[910],[])));
            }
            unset($this->data['user_token']);
            $this->user = $user;
        }
        else{
            //没有在数据库内找到对应token
            exit(json_encode(json_data(910,$this->codeMessage[910],[])));
        }
    }

    public function createuser(){
        $data = [
            'mobile'    =>  $this->data['mobile'],
            'password'  =>  password_hash($this->data['password'],PASSWORD_DEFAULT),
            'status'    =>  1,
            'type'      =>  3,
        ];
        $user = UserModel::create($data);
        if($user){
            $profile = [
                'userid'    =>  $user->id,
            ];
            UserProfile::create($profile);
            return json_data(0,$this->codeMessage[0],'');
        }
    }





}
