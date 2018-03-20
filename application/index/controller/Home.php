<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Db;
use app\index\model\User;
use PDFConverter\PDFConverter;
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

    //相关题目：一群猴子排成一圈，按1,2,…,n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数，
    // 再数到第m只，在把它踢出去…，如此不停的进行下去， 直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。
    public function josefcircle()
    {
        $data = [
            "name"  =>  "TianChen",
            'age'   =>  24,
        ];
        $redis = new \Redis();
        $redis->connect('127.0.0.1','6379');
        $redis->mset($data);
        $redis->hmset('data',$data);
        $new = ["name"];

//        $redis->hGetAll('data');
//        $redis -> lPush('pats2','dog');
//        $redis -> lPush('pats2','cat');
//        $redis -> lPush('pats2','rabbit');

        var_dump($redis -> lPop('pats2'),$redis -> lLen('pats'));
    }






}
