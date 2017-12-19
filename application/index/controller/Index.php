<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Controller;
use think\Db;
use think\Exception;

class Index extends Home
{

    public $redis;
    public function index()
    {
        phpinfo();
//        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
    }

    public function test()
    {
        return $this->fetch();
    }
    public function file2()
    {
        return $this->fetch();
    }
    public function excepTion(){
        $res = json_encode(["0"]);
        var_dump(json_decode('["<p>\u95ee\u7b54\u9898-\u7b54\u6848<\/p>\r\n"]'));
//        var_dump(json_decode('{"choices":["<p>\u5355\u9009\u9898-\u9009\u9879A<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879B<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879C<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879D<\/p>\r\n"]}'));
    }
    public function inde(){
        var_dump(method_exists($this,'exception'));
        $u = new \app\index\logic\User();
//        try{
//            echo 1;
//            $this->LogicLog->exception();
//            echo 2;
//        }
//        catch (Exception $e){
//            echo $e->getMessage();
//        }
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
    public function getnav(){
        $nav = Db::name('role')->where('parentcode','0')->field('id,name,code,parentcode')->select();

        $nav = $this->getnac($nav);
        var_dump($nav);
    }
    public function getnac($nav){
        foreach ($nav as &$n){
            if(Db::name('role')->where('parentcode',$n['code'])->field('id,name,code,parentcode')->select()){
                $n['son'] = Db::name('role')->where('parentcode',$n['code'])->field('id,name,code,parentcode')->select();
                $n['son'] = self::getnac($n['son']);
            }
        }
        return $nav;
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
