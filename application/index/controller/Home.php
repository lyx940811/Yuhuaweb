<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Db;
use app\index\model\User;
class Home extends Controller
{
    protected $codeMessage;
    public $user;
    public function __construct()
    {
        parent::__construct();
        $this->codeMessage = Config::get('apicode_message');

        if(session('userid')){
            define('UID',session('userid'));
            $this->user = User::get(UID);
            $this->assign('user',$this->user);
        }

    }

    public function oo()
    {
        $path = './uploads/2018/01/16/数据库信息.doc';
        $name = preg_replace('/^.+[\\\\\\/]/', '', $path);
        echo $name;
    }


}
