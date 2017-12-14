<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Controller;
use think\Db;
class Index extends Home
{

    public $redis;
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
    }

    public function test()
    {
        return $this->fetch();
    }
    public function file2()
    {
        return $this->fetch();
    }
    public function inde(){
        echo base64_encode('312850391@qq.com');
    }
    public function rrr()
    {
        //connect redis
//        $redis = new \Redis();
//        $re = $redis->connect('127.0.0.1', 6379);
//        $redis->set('wrong1',1);
//        $re = $redis->get('wrong1');
//        $redis->setex('key', 3600, 'value');
//        var_dump($re);
//        phpinfo();
    }



    public function dir(){
        $uploads_dir = "uploads".DS."pictures".DS.date('Y',time()).DS.date('m',time()).DS.date('d',time());
        $date_dir    = ROOT_PATH."public".DS.$uploads_dir;
        if(!file_exists($date_dir)){
            echo 1;
            mkdir($date_dir,0775,true);
        }
        var_dump($date_dir);
    }
    /**
     *
     */
    public function pwd(){
        $pwd = '123456';
        echo md5($pwd);echo '<br>';
        $hash = '$2y$10$Ngmkefwv7LnEh14SSDZnGuTigiCRI7oEkFdhp7jz5UU704RZiDFCa';
        echo $hash;echo '<br>';
        $string = password_verify('123456',$hash);
        echo $string;echo '<br>';
    }
}
